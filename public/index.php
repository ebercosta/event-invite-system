<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Helpers\Router;
use App\Helpers\Auth;
use App\Helpers\Logger;

// Carrega .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// ID único da requisição (aparece nos logs)
$_SERVER['REQUEST_ID'] = substr(bin2hex(random_bytes(4)), 0, 8);

$debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';

// -------------------------------------------------------------------------
// Handlers globais de erro / exceção
// -------------------------------------------------------------------------

set_exception_handler(function (\Throwable $e) use ($debug) {
    Logger::exception($e, [
        'url'    => $_SERVER['REQUEST_URI'] ?? '',
        'method' => $_SERVER['REQUEST_METHOD'] ?? '',
        'ip'     => $_SERVER['REMOTE_ADDR'] ?? '',
    ]);

    http_response_code(500);

    if ($debug) {
        header('Content-Type: text/html; charset=utf-8');
        echo '<h1>Erro 500</h1>';
        echo '<p><strong>' . htmlspecialchars($e->getMessage()) . '</strong></p>';
        echo '<p>' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
        echo '<pre style="background:#1e293b;color:#e2e8f0;padding:16px;border-radius:8px;overflow:auto;">';
        echo htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
        echo '<p style="color:#64748b;font-size:12px;">Log salvo em storage/logs/</p>';
    } else {
        echo 'Erro interno. Tente novamente mais tarde.';
    }

    exit;
});

set_error_handler(function (int $severity, string $message, string $file, int $line) {
    // Converte erros em ErrorException para o exception handler
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

register_shutdown_function(function () use ($debug) {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        Logger::error('Fatal error: ' . $error['message'], [
            'file' => $error['file'],
            'line' => $error['line'],
            'type' => $error['type'],
        ]);

        if (!headers_sent()) {
            http_response_code(500);
        }

        if ($debug) {
            echo '<h1>Erro Fatal</h1>';
            echo '<p>' . htmlspecialchars($error['message']) . '</p>';
            echo '<p>' . htmlspecialchars($error['file']) . ':' . $error['line'] . '</p>';
        }
    }
});

// -------------------------------------------------------------------------
// Bootstrap da aplicação
// -------------------------------------------------------------------------

Auth::startSession();

try {
    $routes = require __DIR__ . '/../config/routes.php';
    $router = new Router($routes);

    $method = $_SERVER['REQUEST_METHOD'];
    $uri    = $_SERVER['REQUEST_URI'];

    $router->dispatch($method, $uri);
} catch (\Throwable $e) {
    // Re-lança para o exception handler
    throw $e;
}
