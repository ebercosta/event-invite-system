<?php

namespace App\Models;

use App\Helpers\Database;
use PDO;

class Event
{
    public ?int $id = null;
    public int $user_id;
    public string $title;
    public ?string $description = null;
    public string $event_date;
    public string $event_time;
    public string $location_name;
    public ?string $location_address = null;
    public ?float $latitude = null;
    public ?float $longitude = null;
    public ?string $image_path = null;
    public ?string $custom_message = null;
    public string $status = 'draft';

    public static function find(int $id): ?self
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? self::fromArray($data) : null;
    }

    public static function fromArray(array $data): self
    {
        $event = new self();
        foreach ($data as $key => $value) {
            if (property_exists($event, $key)) {
                $event->$key = $value;
            }
        }
        return $event;
    }
}
