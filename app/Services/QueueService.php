<?php

namespace App\Services;

use PDO;
use App\Helpers\Database;

class QueueService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function push(string $jobClass, array $data, string $queue = 'default', int $delaySeconds = 0): int
    {
        $payload = json_encode([
            'job'  => $jobClass,
            'data' => $data,
        ]);

        $availableAt = date('Y-m-d H:i:s', time() + $delaySeconds);

        $stmt = $this->db->prepare(
            "INSERT INTO jobs (queue, payload, available_at) VALUES (?, ?, ?)"
        );
        $stmt->execute([$queue, $payload, $availableAt]);

        return (int) $this->db->lastInsertId();
    }

    public function pop(string $queue = 'default'): ?array
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM jobs 
                 WHERE queue = ? AND available_at <= NOW() AND reserved_at IS NULL
                 ORDER BY id ASC LIMIT 1 FOR UPDATE"
            );
            $stmt->execute([$queue]);
            $job = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$job) {
                $this->db->commit();
                return null;
            }

            $update = $this->db->prepare(
                "UPDATE jobs SET reserved_at = NOW(), attempts = attempts + 1 WHERE id = ?"
            );
            $update->execute([$job['id']]);

            $this->db->commit();

            $job['payload'] = json_decode($job['payload'], true);
            return $job;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete(int $jobId): void
    {
        $stmt = $this->db->prepare("DELETE FROM jobs WHERE id = ?");
        $stmt->execute([$jobId]);
    }

    public function fail(array $job, string $exception): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO failed_jobs (payload, exception) VALUES (?, ?)"
        );
        $stmt->execute([json_encode($job['payload']), $exception]);

        $this->delete($job['id']);
    }

    public function release(int $jobId, int $delaySeconds = 30): void
    {
        $stmt = $this->db->prepare(
            "UPDATE jobs SET reserved_at = NULL, available_at = DATE_ADD(NOW(), INTERVAL ? SECOND) WHERE id = ?"
        );
        $stmt->execute([$delaySeconds, $jobId]);
    }
}
