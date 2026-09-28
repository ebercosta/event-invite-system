<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Database;
use App\Helpers\Logger;
use PDO;

class EventController
{
    public function index(): void
    {
        Auth::requireLogin();
        $db = Database::getConnection();

        $stmt = $db->prepare(
            "SELECT e.*, 
                    (SELECT COUNT(*) FROM guests WHERE event_id = e.id) as guests_count,
                    (SELECT COUNT(*) FROM guests WHERE event_id = e.id AND status = 'confirmed') as confirmed_count
             FROM events e 
             WHERE e.user_id = ? 
             ORDER BY e.event_date DESC"
        );
        $stmt->execute([Auth::id()]);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../../views/events/index.php';
    }

    public function create(): void
    {
        Auth::requireLogin();
        include __DIR__ . '/../../views/events/create.php';
    }

    public function store(): void
    {
        Auth::requireLogin();

        try {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '') ?: null;
            $eventDate = $_POST['event_date'] ?? '';
            $eventTime = $_POST['event_time'] ?? '';
            $locationName = trim($_POST['location_name'] ?? '');
            $locationAddress = trim($_POST['location_address'] ?? '') ?: null;
            $customMessage = trim($_POST['custom_message'] ?? '') ?: null;
            $status = $_POST['status'] ?? 'draft';

            // DECIMAL não aceita string vazia — converte para NULL
            $latitude  = $this->nullableDecimal($_POST['latitude'] ?? null);
            $longitude = $this->nullableDecimal($_POST['longitude'] ?? null);

            if ($title === '' || $eventDate === '' || $eventTime === '' || $locationName === '') {
                Logger::warning('Event store: campos obrigatórios faltando', $_POST);
                header('Location: /events/create?error=campos_obrigatorios');
                exit;
            }

            $imagePath = null;
            if (!empty($_FILES['image']['name'])) {
                $imagePath = $this->uploadImage($_FILES['image']);
            }

            $db = Database::getConnection();
            $stmt = $db->prepare(
                "INSERT INTO events (
                    user_id, title, description, event_date, event_time,
                    location_name, location_address, latitude, longitude,
                    image_path, custom_message, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                Auth::id(),
                $title,
                $description,
                $eventDate,
                $eventTime,
                $locationName,
                $locationAddress,
                $latitude,
                $longitude,
                $imagePath,
                $customMessage,
                $status,
            ]);

            $eventId = (int) $db->lastInsertId();
            Logger::info('Evento criado', ['event_id' => $eventId, 'title' => $title]);

            header('Location: /events');
            exit;
        } catch (\Throwable $e) {
            Logger::exception($e, [
                'action' => 'EventController@store',
                'post'   => array_diff_key($_POST, ['password' => 1]),
            ]);
            throw $e; // deixe o handler global responder
        }
    }

    public function show(string $id): void
    {
        Auth::requireLogin();
        $event = $this->findOwnedEvent((int) $id);
        if (!$event) {
            http_response_code(404);
            echo 'Evento não encontrado';
            return;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM guests WHERE event_id = ? ORDER BY name');
        $stmt->execute([$id]);
        $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../../views/events/show.php';
    }

    public function edit(string $id): void
    {
        Auth::requireLogin();
        $event = $this->findOwnedEvent((int) $id);
        if (!$event) {
            http_response_code(404);
            echo 'Evento não encontrado';
            return;
        }
        include __DIR__ . '/../../views/events/edit.php';
    }

    public function update(string $id): void
    {
        Auth::requireLogin();

        try {
            $event = $this->findOwnedEvent((int) $id);
            if (!$event) {
                http_response_code(404);
                echo 'Evento não encontrado';
                return;
            }

            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '') ?: null;
            $eventDate = $_POST['event_date'] ?? '';
            $eventTime = $_POST['event_time'] ?? '';
            $locationName = trim($_POST['location_name'] ?? '');
            $locationAddress = trim($_POST['location_address'] ?? '') ?: null;
            $customMessage = trim($_POST['custom_message'] ?? '') ?: null;
            $status = $_POST['status'] ?? 'draft';

            $latitude  = $this->nullableDecimal($_POST['latitude'] ?? null);
            $longitude = $this->nullableDecimal($_POST['longitude'] ?? null);

            $imagePath = $event['image_path'];
            if (!empty($_FILES['image']['name'])) {
                $imagePath = $this->uploadImage($_FILES['image']);
            }

            $db = Database::getConnection();
            $stmt = $db->prepare(
                "UPDATE events SET
                    title = ?, description = ?, event_date = ?, event_time = ?,
                    location_name = ?, location_address = ?, latitude = ?, longitude = ?,
                    image_path = ?, custom_message = ?, status = ?
                 WHERE id = ? AND user_id = ?"
            );

            $stmt->execute([
                $title,
                $description,
                $eventDate,
                $eventTime,
                $locationName,
                $locationAddress,
                $latitude,
                $longitude,
                $imagePath,
                $customMessage,
                $status,
                $id,
                Auth::id(),
            ]);

            Logger::info('Evento atualizado', ['event_id' => $id]);

            header("Location: /events/{$id}");
            exit;
        } catch (\Throwable $e) {
            Logger::exception($e, ['action' => 'EventController@update', 'event_id' => $id]);
            throw $e;
        }
    }

    public function delete(string $id): void
    {
        Auth::requireLogin();
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM events WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, Auth::id()]);

        Logger::info('Evento excluído', ['event_id' => $id]);

        header('Location: /events');
        exit;
    }

    private function findOwnedEvent(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM events WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, Auth::id()]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        return $event ?: null;
    }

    /**
     * Converte string vazia / inválida em NULL (necessário para colunas DECIMAL)
     */
    private function nullableDecimal(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        return (string) $value;
    }

    private function uploadImage(array $file): ?string
    {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($file['type'] ?? '', $allowed, true)) {
            Logger::warning('Upload rejeitado: tipo inválido', ['type' => $file['type'] ?? '']);
            return null;
        }

        if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
            Logger::warning('Upload rejeitado: arquivo muito grande', ['size' => $file['size'] ?? 0]);
            return null;
        }

        $uploadDir = __DIR__ . '/../../public/assets/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid('event_', true) . '.' . $ext;
        $dest = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            Logger::error('Falha ao mover upload', ['dest' => $dest]);
            return null;
        }

        return '/assets/uploads/' . $filename;
    }
}
