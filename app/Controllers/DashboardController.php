<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Database;
use PDO;

class DashboardController
{
    public function index(): void
    {
        Auth::requireLogin();

        $db = Database::getConnection();
        $userId = Auth::id();

        $stmt = $db->prepare("SELECT COUNT(*) FROM events WHERE user_id = ?");
        $stmt->execute([$userId]);
        $totalEvents = (int)$stmt->fetchColumn();

        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM guests g 
             INNER JOIN events e ON e.id = g.event_id 
             WHERE e.user_id = ?"
        );
        $stmt->execute([$userId]);
        $totalGuests = (int)$stmt->fetchColumn();

        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM guests g 
             INNER JOIN events e ON e.id = g.event_id 
             WHERE e.user_id = ? AND g.status = 'confirmed'"
        );
        $stmt->execute([$userId]);
        $confirmedGuests = (int)$stmt->fetchColumn();

        $stmt = $db->prepare(
            "SELECT e.*, 
                    (SELECT COUNT(*) FROM guests WHERE event_id = e.id) as guests_count,
                    (SELECT COUNT(*) FROM guests WHERE event_id = e.id AND status = 'confirmed') as confirmed_count
             FROM events e 
             WHERE e.user_id = ? 
             ORDER BY e.event_date DESC 
             LIMIT 5"
        );
        $stmt->execute([$userId]);
        $recentEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../../views/dashboard/index.php';
    }
}
