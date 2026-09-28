<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Database;
use App\Services\QueueService;
use App\Services\CsvImportService;
use App\Jobs\SendEmailInvite;
use App\Jobs\SendWhatsAppInvite;
use App\Models\Guest;
use PDO;

class GuestController
{
    public function index(string $eventId): void
    {
        Auth::requireLogin();

        $event = $this->findOwnedEvent((int)$eventId);
        if (!$event) {
            http_response_code(404);
            echo "Evento não encontrado";
            return;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM guests WHERE event_id = ? ORDER BY name");
        $stmt->execute([$eventId]);
        $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../../views/guests/index.php';
    }

    public function store(string $eventId): void
    {
        Auth::requireLogin();

        $event = $this->findOwnedEvent((int)$eventId);
        if (!$event) {
            http_response_code(404);
            echo "Evento não encontrado";
            return;
        }

        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '') ?: null;

        if (empty($name) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: /events/{$eventId}/guests?error=dados_invalidos");
            exit;
        }

        $db = Database::getConnection();

        // Verifica duplicata
        $stmt = $db->prepare("SELECT id FROM guests WHERE event_id = ? AND email = ?");
        $stmt->execute([$eventId, $email]);
        if ($stmt->fetch()) {
            header("Location: /events/{$eventId}/guests?error=email_duplicado");
            exit;
        }

        $token = bin2hex(random_bytes(32));

        $stmt = $db->prepare(
            "INSERT INTO guests (event_id, name, email, phone, unique_token) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$eventId, $name, $email, $phone, $token]);

        header("Location: /events/{$eventId}/guests?success=1");
        exit;
    }

    public function import(string $eventId): void
    {
        Auth::requireLogin();

        $event = $this->findOwnedEvent((int)$eventId);
        if (!$event) {
            http_response_code(404);
            echo "Evento não encontrado";
            return;
        }

        if (empty($_FILES['csv']['tmp_name'])) {
            header("Location: /events/{$eventId}/guests?error=arquivo_obrigatorio");
            exit;
        }

        $service = new CsvImportService();
        $results = $service->import((int)$eventId, $_FILES['csv']['tmp_name']);

        $msg = "Importados: {$results['success']} | Ignorados: {$results['skipped']}";
        if ($results['errors']) {
            $msg .= " | Erros: " . count($results['errors']);
        }

        header("Location: /events/{$eventId}/guests?import=" . urlencode($msg));
        exit;
    }

    public function sendInvites(string $eventId): void
    {
        Auth::requireLogin();

        $event = $this->findOwnedEvent((int)$eventId);
        if (!$event) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Evento não encontrado']);
            return;
        }

        $db = Database::getConnection();
        $queue = new QueueService();

        $stmt = $db->prepare(
            "SELECT id, phone FROM guests 
             WHERE event_id = ? 
             AND (email_sent_at IS NULL OR (phone IS NOT NULL AND phone != '' AND whatsapp_sent_at IS NULL))"
        );
        $stmt->execute([$eventId]);
        $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $enqueued = 0;

        foreach ($guests as $g) {
            $queue->push(SendEmailInvite::class, [
                'guestId' => (int)$g['id'],
                'eventId' => (int)$eventId,
            ]);

            if (!empty($g['phone'])) {
                $queue->push(SendWhatsAppInvite::class, [
                    'guestId' => (int)$g['id'],
                    'eventId' => (int)$eventId,
                ]);
            }

            $enqueued++;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => "{$enqueued} convites enfileirados com sucesso",
            'enqueued' => $enqueued,
        ]);
    }

    public function sendSingle(string $guestId): void
    {
        Auth::requireLogin();

        $guest = Guest::find((int)$guestId);
        if (!$guest) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Convidado não encontrado']);
            return;
        }

        // Verifica ownership via event
        $event = $this->findOwnedEvent($guest->event_id);
        if (!$event) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            return;
        }

        $queue = new QueueService();

        $queue->push(SendEmailInvite::class, [
            'guestId' => $guest->id,
            'eventId' => $guest->event_id,
        ]);

        if (!empty($guest->phone)) {
            $queue->push(SendWhatsAppInvite::class, [
                'guestId' => $guest->id,
                'eventId' => $guest->event_id,
            ]);
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Convite enfileirado com sucesso']);
    }

    public function delete(string $guestId): void
    {
        Auth::requireLogin();

        $guest = Guest::find((int)$guestId);
        if (!$guest) {
            header('Location: /events');
            exit;
        }

        $event = $this->findOwnedEvent($guest->event_id);
        if (!$event) {
            header('Location: /events');
            exit;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM guests WHERE id = ?");
        $stmt->execute([$guestId]);

        header("Location: /events/{$guest->event_id}/guests");
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
}
