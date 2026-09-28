<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Models\Guest;
use App\Models\Event;
use App\Helpers\Database;

class EmailService
{
    private PHPMailer $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);

        $this->mailer->isSMTP();
        $this->mailer->Host       = $_ENV['MAIL_HOST'] ?? 'localhost';
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = $_ENV['MAIL_USERNAME'] ?? '';
        $this->mailer->Password   = $_ENV['MAIL_PASSWORD'] ?? '';
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port       = (int) ($_ENV['MAIL_PORT'] ?? 587);
        $this->mailer->CharSet    = 'UTF-8';

        $this->mailer->setFrom(
            $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@localhost',
            $_ENV['MAIL_FROM_NAME'] ?? 'Event Invite'
        );
    }

    public function sendInvite(Guest $guest, Event $event): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($guest->email, $guest->name);

            $inviteUrl = rtrim($_ENV['APP_URL'], '/') . '/convite/' . $guest->unique_token;

            // Gera QR Code
            $qrService = new QrCodeService();
            $qrCodeUrl = $qrService->generateForGuest($guest->unique_token);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = "Convite: {$event->title}";

            $this->mailer->Body = $this->renderTemplate('invite', [
                'guest_name'     => $guest->name,
                'event_title'    => $event->title,
                'event_date'     => date('d/m/Y', strtotime($event->event_date)),
                'event_time'     => date('H:i', strtotime($event->event_time)),
                'location'       => $event->location_name,
                'custom_message' => $event->custom_message ?? '',
                'invite_url'     => $inviteUrl,
                'qrcode_url'     => rtrim($_ENV['APP_URL'], '/') . $qrCodeUrl,
                'image_url'      => $event->image_path ? rtrim($_ENV['APP_URL'], '/') . $event->image_path : null,
            ]);

            $this->mailer->AltBody = strip_tags(str_replace(['<br>', '<br/>'], "\n", $this->mailer->Body));

            $this->mailer->send();

            // Atualiza guest
            $db = Database::getConnection();
            $stmt = $db->prepare("UPDATE guests SET email_sent_at = NOW() WHERE id = ?");
            $stmt->execute([$guest->id]);

            $this->logEmail($guest->id, 'invite', 'sent');

            return true;
        } catch (Exception $e) {
            $this->logEmail($guest->id, 'invite', 'failed', $this->mailer->ErrorInfo);
            return false;
        }
    }

    private function renderTemplate(string $name, array $data): string
    {
        extract($data);
        ob_start();
        include __DIR__ . "/../Mail/templates/{$name}.html";
        return ob_get_clean();
    }

    private function logEmail(int $guestId, string $type, string $status, ?string $error = null): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "INSERT INTO email_logs (guest_id, type, status, error_message) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$guestId, $type, $status, $error]);
    }
}
