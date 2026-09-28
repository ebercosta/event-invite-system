<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Database;
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

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $eventDate = $_POST['event_date'] ?? '';
        $eventTime = $_POST['event_time'] ?? '';
        $locationName = trim($_POST['location_name'] ?? '');
        $locationAddress = trim($_POST['location_address'] ?? '');
        $latitude = $_POST['latitude'] ?? null;
        $longitude = $_POST['longitude'] ?? null;
        $customMessage = trim($_POST['custom_message'] ?? '');
        $status = $_POST['status'] ?? 'draft';

        $imagePath = null;
        if (!empty($_FILES['image']['name'])) {
            $imagePath = $this->uploadImage($_FILES['image']);
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            "INSERT INTO events (user_id, title, description, event_date, event_time, location_name, location_address, latitude, longitude, image_path, custom_message, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            Auth::id(), $title, $description, $eventDate, $eventTime,
            $locationName, $locationAddress, $latitude, $longitude,
            $imagePath, $customMessage, $status
        ]);

        header('Location: /events');
        exit;
    }

    public function show(string $id): void
    {
        Auth::requireLogin();
        $event = $this->findOwnedEvent((int)$id);
        if (!$event) {
            http_response_code(404);
            echo "Evento não encontrado";
            return;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM guests WHERE event_id = ? ORDER BY name");
        $stmt->execute([$id]);
        $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../../views/events/show.php';
    }

    public function edit(string $id): void
    {
        Auth::requireLogin();
        $event = $this->findOwnedEvent((int)$id);
        if (!$event) {
            http_response_code(404);
            echo "Evento não encontrado";
            return;
        }
        include __DIR__ . '/../../views/events/edit.php';
    }

    public function update(string $id): void
    {
        Auth::requireLogin();
        $event = $this->findOwnedEvent((int)$id);
        if (!$event) {
            http_response_code(404);
            echo "Evento não encontrado";
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $eventDate = $_POST['event_date'] ?? '';
        $eventTime = $_POST['event_time'] ?? '';
        $locationName = trim($_POST['location_name'] ?? '');
        $locationAddress = trim($_POST['location_address'] ?? '');
        $latitude = $_POST['latitude'] ?? null;
        $longitude = $_POST['longitude'] ?? null;
        $customMessage = trim($_POST['custom_message'] ?? '');
        $status = $_POST['status'] ?? 'draft';

        $imagePath = $event['image_path'];
        if (!empty($_FILES['image']['name'])) {
            $imagePath = $this->uploadImage($_FILES['image']);
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            "UPDATE events SET title=?, description=?, event_date=?, event_time=?, location_name=?, location_address=?, latitude=?, longitude=?, image_path=?, custom_message=?, status=? WHERE id=? AND user_id=?"
        );
        $stmt->execute([
            $title, $description, $eventDate, $eventTime,
            $locationName, $locationAddress, $latitude, $longitude,
            $imagePath, $customMessage, $status, $id, Auth::id()
        ]);

        header("Location: /events/{$id}");
        exit;
    }

    public function delete(string $id): void
    {
        Auth::requireLogin();
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM events WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, Auth::id()]);
        header('Location: /events');
        exit;
    }

    private function findOwnedEvent(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM events WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, Auth::id()]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        return $event ?: null;
    }

    private function uploadImage(array $file): ?string
    {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowed)) {
            return null;
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            return null;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('event_') . '.' . $ext;
        $dest = __DIR__ . '/../../public/assets/uploads/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return '/assets/uploads/' . $filename;
        }
        return null;
    }
}
