<?php

namespace App\Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrCodeService
{
    private string $storagePath;

    public function __construct()
    {
        $this->storagePath = __DIR__ . '/../../public/assets/qrcodes/';

        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    public function generateForGuest(string $token, int $size = 300): string
    {
        $inviteUrl = rtrim($_ENV['APP_URL'], '/') . '/convite/' . $token;
        $filename  = $token . '.png';
        $filepath  = $this->storagePath . $filename;

        if (file_exists($filepath)) {
            return '/assets/qrcodes/' . $filename;
        }

        $options = new QROptions([
            'outputType'  => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel'    => QRCode::ECC_M,
            'scale'       => 8,
            'imageBase64' => false,
        ]);

        (new QRCode($options))->render($inviteUrl, $filepath);

        return '/assets/qrcodes/' . $filename;
    }

    public function generateBase64(string $token): string
    {
        $inviteUrl = rtrim($_ENV['APP_URL'], '/') . '/convite/' . $token;

        $options = new QROptions([
            'outputType'  => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel'    => QRCode::ECC_M,
            'scale'       => 6,
            'imageBase64' => true,
        ]);

        return (new QRCode($options))->render($inviteUrl);
    }
}
