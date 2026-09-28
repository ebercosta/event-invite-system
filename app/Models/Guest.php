<?php

namespace App\Models;

use App\Helpers\Database;
use PDO;

class Guest
{
    public ?int $id = null;
    public int $event_id;
    public string $name;
    public string $email;
    public ?string $phone = null;
    public string $unique_token;
    public ?string $qrcode_path = null;
    public string $status = 'pending';
    public ?string $email_sent_at = null;
    public ?string $whatsapp_sent_at = null;
    public ?string $confirmed_at = null;

    public static function find(int $id): ?self
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM guests WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? self::fromArray($data) : null;
    }

    public static function findByToken(string $token): ?self
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM guests WHERE unique_token = ?");
        $stmt->execute([$token]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? self::fromArray($data) : null;
    }

    public static function fromArray(array $data): self
    {
        $guest = new self();
        foreach ($data as $key => $value) {
            if (property_exists($guest, $key)) {
                $guest->$key = $value;
            }
        }
        return $guest;
    }

    public function save(): bool
    {
        $db = Database::getConnection();

        if ($this->id) {
            $stmt = $db->prepare(
                "UPDATE guests SET name=?, email=?, phone=?, status=?, email_sent_at=?, whatsapp_sent_at=?, confirmed_at=? WHERE id=?"
            );
            return $stmt->execute([
                $this->name, $this->email, $this->phone, $this->status,
                $this->email_sent_at, $this->whatsapp_sent_at, $this->confirmed_at, $this->id
            ]);
        }

        $stmt = $db->prepare(
            "INSERT INTO guests (event_id, name, email, phone, unique_token, status) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $result = $stmt->execute([
            $this->event_id, $this->name, $this->email, $this->phone, $this->unique_token, $this->status
        ]);

        if ($result) {
            $this->id = (int) $db->lastInsertId();
        }

        return $result;
    }
}
