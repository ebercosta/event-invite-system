#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Services\QueueService;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$maxJobs     = (int) ($argv[1] ?? 50);   // Processa até N jobs por execução
$sleepEmpty  = 3;                       // Segundos de espera quando fila vazia
$processed   = 0;

$queue = new QueueService();
$maxAttempts = (int) ($_ENV['QUEUE_MAX_ATTEMPTS'] ?? 3);

echo "[" . date('Y-m-d H:i:s') . "] Worker iniciado...\n";

while ($processed < $maxJobs) {
    $job = $queue->pop('default');

    if (!$job) {
        sleep($sleepEmpty);
        continue;
    }

    $jobClass = $job['payload']['job'] ?? null;
    $data     = $job['payload']['data'] ?? [];

    echo "[" . date('Y-m-d H:i:s') . "] Processando job #{$job['id']} ({$jobClass})... ";

    try {
        if (!class_exists($jobClass)) {
            throw new RuntimeException("Job class not found: {$jobClass}");
        }

        $instance = new $jobClass(...array_values($data));
        $instance->handle();

        $queue->delete($job['id']);
        echo "OK\n";
        $processed++;

    } catch (Throwable $e) {
        echo "FALHOU: {$e->getMessage()}\n";

        if ($job['attempts'] >= $maxAttempts) {
            $queue->fail($job, $e->getMessage() . "\n" . $e->getTraceAsString());
            echo "  → Movido para failed_jobs\n";
        } else {
            $queue->release($job['id'], 60); // tenta de novo em 60s
            echo "  → Reagendado (tentativa {$job['attempts']}/{$maxAttempts})\n";
        }
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Worker finalizado. Jobs processados: {$processed}\n";
