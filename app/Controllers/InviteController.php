<?php

namespace App\Controllers;

use App\Models\Guest;
use App\Models\Event;
use App\Services\QrCodeService;
use App\Helpers\Database;
use PDO;

class InviteController
{
    public function show(string $token): void
    {
        $guest = Guest::findByToken($token);

        if (!$guest) {
            http_response_code(404);
            echo "Convite não encontrado";
            return;
        }

        $event = Event::find($guest->event_id);

        if (!$event) {
            http_response_code(404);
            echo "Evento não encontrado";
            return;
        }

        // Registra clique
        $this->logClick($guest->id);

        // Gera QR Code
        $qrService = new QrCodeService();
        $qrCodePath = $qrService->generateForGuest($token);

        // Renderiza view
        $inviteUrl = rtrim($_ENV['APP_URL'], '/') . '/convite/' . $token;

        include __DIR__ . '/../../views/invites/confirm.php';
    }

    public function confirm(string $token): void
    {
        $guest = Guest::findByToken($token);

        if (!$guest) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Convite não encontrado']);
            return;
        }

        $guest->status = 'confirmed';
        $guest->confirmed_at = date('Y-m-d H:i:s');
        $guest->save();

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Presença confirmada com sucesso!']);
    }

    public function decline(string $token): void
    {
        $guest = Guest::findByToken($token);

        if (!$guest) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Convite não encontrado']);
            return;
        }

        $guest->status = 'declined';
        $guest->confirmed_at = date('Y-m-d H:i:s');
        $guest->save();

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Resposta registrada. Que pena que não poderá ir!']);
    }

    private function logClick(int $guestId): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "INSERT INTO invite_clicks (guest_id, ip_address, user_agent) VALUES (?, ?, ?)"
        );
        $stmt->execute([
            $guestId,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }
}
