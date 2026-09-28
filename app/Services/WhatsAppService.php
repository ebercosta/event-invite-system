<?php

namespace App\Services;

class WhatsAppService
{
    private string $baseUrl;
    private string $apiKey;
    private string $instance;

    public function __construct()
    {
        $this->baseUrl  = rtrim($_ENV['EVOLUTION_API_URL'] ?? '', '/');
        $this->apiKey   = $_ENV['EVOLUTION_API_KEY'] ?? '';
        $this->instance = $_ENV['EVOLUTION_INSTANCE'] ?? '';
    }

    public function sendText(string $phone, string $message): bool
    {
        $phone = $this->formatPhone($phone);

        $payload = [
            'number' => $phone,
            'text'   => $message,
        ];

        $response = $this->request('POST', "/message/sendText/{$this->instance}", $payload);

        return isset($response['key']['id']);
    }

    public function sendMedia(string $phone, string $mediaUrl, string $caption = '', string $mediaType = 'image'): bool
    {
        $phone = $this->formatPhone($phone);

        $payload = [
            'number'    => $phone,
            'mediatype' => $mediaType,
            'media'     => $mediaUrl,
            'caption'   => $caption,
        ];

        $response = $this->request('POST', "/message/sendMedia/{$this->instance}", $payload);

        return isset($response['key']['id']);
    }

    public function sendInvite(string $phone, string $guestName, string $eventTitle, string $inviteUrl, ?string $imageUrl = null): bool
    {
        $message = "Olá *{$guestName}*!\n\n"
                 . "Você foi convidado(a) para o evento:\n"
                 . "*{$eventTitle}*\n\n"
                 . "Acesse seu convite exclusivo:\n"
                 . "{$inviteUrl}\n\n"
                 . "Esperamos você!";

        if ($imageUrl) {
            return $this->sendMedia($phone, $imageUrl, $message);
        }

        return $this->sendText($phone, $message);
    }

    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        // Já tem DDI
        if (strlen($phone) >= 12 && str_starts_with($phone, '55')) {
            return $phone;
        }

        // Celular brasileiro (11 dígitos)
        if (strlen($phone) === 11) {
            return '55' . $phone;
        }

        // Fixo com DDD
        if (strlen($phone) === 10) {
            return '55' . $phone;
        }

        return $phone;
    }

    private function request(string $method, string $endpoint, array $data = []): array
    {
        if (empty($this->baseUrl) || empty($this->apiKey)) {
            throw new \RuntimeException('Evolution API não configurada');
        }

        $ch = curl_init($this->baseUrl . $endpoint);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'apikey: ' . $this->apiKey,
            ],
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException("cURL Error: {$error}");
        }

        $decoded = json_decode($response, true) ?? [];

        if ($httpCode >= 400) {
            $msg = $decoded['message'] ?? $response;
            throw new \RuntimeException("Evolution API Error ({$httpCode}): {$msg}");
        }

        return $decoded;
    }
}
