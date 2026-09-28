<?php

namespace App\Controllers;

use App\Services\QueueService;
use App\Jobs\SendEmailInvite;
use App\Jobs\SendWhatsAppInvite;
use App\Models\Guest;
use App\Models\Event;
use App\Helpers\Database;
use PDO;

class GuestController
{
    /**
     * Enfileira envio de convites (e-mail + WhatsApp)
     */
    public function sendInvites(int $eventId): array
    {
        $db = Database::getConnection();
        $queue = new QueueService();

        // Busca convidados ainda não enviados
        $stmt = $db->prepare(
            "SELECT id FROM guests 
             WHERE event_id = ? 
             AND (email_sent_at IS NULL OR (phone IS NOT NULL AND whatsapp_sent_at IS NULL))"
        );
        $stmt->execute([$eventId]);
        $guests = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $enqueued = 0;

        foreach ($guests as $guestId) {
            // E-mail
            $queue->push(SendEmailInvite::class, [
                'guestId' => (int) $guestId,
                'eventId' => $eventId,
            ]);

            // WhatsApp (só se tiver telefone)
            $guest = Guest::find((int) $guestId);
            if ($guest && !empty($guest->phone)) {
                $queue->push(SendWhatsAppInvite::class, [
                    'guestId' => (int) $guestId,
                    'eventId' => $eventId,
                ]);
            }

            $enqueued++;
        }

        return [
            'success' => true,
            'message' => "{$enqueued} convites enfileirados com sucesso",
            'enqueued' => $enqueued,
        ];
    }

    public function sendSingle(int $guestId): array
    {
        $guest = Guest::find($guestId);
        if (!$guest) {
            return ['success' => false, 'message' => 'Convidado não encontrado'];
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

        return [
            'success' => true,
            'message' => 'Convite enfileirado com sucesso',
        ];
    }
}
