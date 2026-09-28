<?php

namespace App\Jobs;

use App\Services\EmailService;
use App\Models\Guest;
use App\Models\Event;

class SendEmailInvite
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

        if ($guest->email_sent_at) {
            return; // Já enviado
        }

        $emailService = new EmailService();
        $success = $emailService->sendInvite($guest, $event);

        if (!$success) {
            throw new \RuntimeException('Failed to send email');
        }
    }
}
