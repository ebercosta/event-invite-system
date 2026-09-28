<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use App\Models\Guest;
use App\Models\Event;
use App\Helpers\Database;
use PDO;

class SendWhatsAppInvite
{
    public function __construct(
        public int $guestId,
        public int $eventId
    ) {}

    public function handle(): void
    {
        $guest = Guest::find($this->guestId);
        $event = Event::find($this->eventId);

        if (!$guest || !$event) {
            throw new \RuntimeException('Guest or Event not found');
        }

        if (empty($guest->phone)) {
            return; // Sem telefone
        }

        if ($guest->whatsapp_sent_at) {
            return; // Já enviado
        }

        $inviteUrl = rtrim($_ENV['APP_URL'], '/') . '/convite/' . $guest->unique_token;
        $imageUrl  = $event->image_path
            ? rtrim($_ENV['APP_URL'], '/') . $event->image_path
            : null;

        $whatsapp = new WhatsAppService();
        $success  = $whatsapp->sendInvite(
            $guest->phone,
            $guest->name,
            $event->title,
            $inviteUrl,
            $imageUrl
        );

        if (!$success) {
            throw new \RuntimeException('Failed to send WhatsApp message');
        }

        // Atualiza timestamp
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE guests SET whatsapp_sent_at = NOW() WHERE id = ?");
        $stmt->execute([$guest->id]);
    }
}
