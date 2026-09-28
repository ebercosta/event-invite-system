<?php

namespace App\Services;

use App\Helpers\Database;
use PDO;

class CsvImportService
{
    public function import(int $eventId, string $filepath): array
    {
        $results = [
            'success' => 0,
            'errors'  => [],
            'skipped' => 0,
        ];

        if (!file_exists($filepath)) {
            $results['errors'][] = 'Arquivo não encontrado';
            return $results;
        }

        $handle = fopen($filepath, 'r');
        if (!$handle) {
            $results['errors'][] = 'Não foi possível abrir o arquivo';
            return $results;
        }

        // Detecta delimitador
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = str_contains($firstLine, ';') ? ';' : ',';

        $header = fgetcsv($handle, 0, $delimiter);
        if (!$header) {
            $results['errors'][] = 'CSV vazio ou inválido';
            fclose($handle);
            return $results;
        }

        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        $nameIdx  = $this->findColumn($header, ['nome', 'name']);
        $emailIdx = $this->findColumn($header, ['email', 'e-mail']);
        $phoneIdx = $this->findColumn($header, ['telefone', 'phone', 'celular', 'whatsapp']);

        if ($nameIdx === null || $emailIdx === null) {
            $results['errors'][] = 'Colunas obrigatórias não encontradas (nome, email)';
            fclose($handle);
            return $results;
        }

        $db = Database::getConnection();
        $line = 1;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line++;

            $name  = trim($row[$nameIdx] ?? '');
            $email = trim($row[$emailIdx] ?? '');
            $phone = $phoneIdx !== null ? trim($row[$phoneIdx] ?? '') : null;

            if (empty($name) || empty($email)) {
                $results['skipped']++;
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $results['errors'][] = "Linha {$line}: e-mail inválido ({$email})";
                continue;
            }

            // Verifica duplicata
            $stmt = $db->prepare("SELECT id FROM guests WHERE event_id = ? AND email = ?");
            $stmt->execute([$eventId, $email]);
            if ($stmt->fetch()) {
                $results['skipped']++;
                continue;
            }

            $token = bin2hex(random_bytes(32));

            try {
                $stmt = $db->prepare(
                    "INSERT INTO guests (event_id, name, email, phone, unique_token) VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->execute([$eventId, $name, $email, $phone ?: null, $token]);
                $results['success']++;
            } catch (\Exception $e) {
                $results['errors'][] = "Linha {$line}: " . $e->getMessage();
            }
        }

        fclose($handle);
        return $results;
    }

    private function findColumn(array $header, array $possibleNames): ?int
    {
        foreach ($possibleNames as $name) {
            $idx = array_search($name, $header);
            if ($idx !== false) {
                return $idx;
            }
        }
        return null;
    }
}
