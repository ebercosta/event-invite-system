<?php

namespace App\Helpers;

class Logger
{
    private static string $logDir;

    private static function dir(): string
    {
        if (!isset(self::$logDir)) {
            self::$logDir = __DIR__ . '/../../storage/logs';

            if (!is_dir(self::$logDir)) {
                mkdir(self::$logDir, 0755, true);
            }
        }

        return self::$logDir;
    }

    /**
     * Caminho do arquivo de log do dia
     */
    public static function path(string $channel = 'app'): string
    {
        return self::dir() . '/' . $channel . '-' . date('Y-m-d') . '.log';
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
            self::write('DEBUG', $message, $context);
        }
    }

    /**
     * Loga uma Exception/Throwable com stack trace
     */
    public static function exception(\Throwable $e, array $context = []): void
    {
        $context['exception'] = get_class($e);
        $context['file'] = $e->getFile();
        $context['line'] = $e->getLine();
        $context['trace'] = $e->getTraceAsString();

        self::write('ERROR', $e->getMessage(), $context);
    }

    private static function write(string $level, string $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $requestId = $_SERVER['REQUEST_ID'] ?? substr(md5(uniqid('', true)), 0, 8);

        $line = sprintf(
            "[%s] %s.%s: %s",
            $timestamp,
            $level,
            $requestId,
            $message
        );

        if ($context) {
            // Evita contexto enorme
            $safe = [];
            foreach ($context as $k => $v) {
                if (is_string($v) && strlen($v) > 2000) {
                    $v = substr($v, 0, 2000) . '...[truncated]';
                }
                $safe[$k] = $v;
            }
            $line .= ' ' . json_encode($safe, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $line .= PHP_EOL;

        $file = self::path();

        // Lock para escrita concorrente segura
        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}
