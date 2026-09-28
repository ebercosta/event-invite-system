<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Database;
use PDO;

class ExportController
{
    public function attendance(string $eventId): void
    {
        Auth::requireLogin();

        $db = Database::getConnection();

        // Verifica ownership
        $stmt = $db->prepare("SELECT title FROM events WHERE id = ? AND user_id = ?");
        $stmt->execute([$eventId, Auth::id()]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            http_response_code(404);
            echo "Evento não encontrado";
            return;
        }

        $stmt = $db->prepare(
            "SELECT name, email, phone, status, confirmed_at, email_sent_at, whatsapp_sent_at, created_at
             FROM guests WHERE event_id = ? ORDER BY name"
        );
        $stmt->execute([$eventId]);
        $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $filename = 'lista-presenca-' . preg_replace('/[^a-z0-9]/i', '-', $event['title']) . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

        fputcsv($out, ['Nome', 'E-mail', 'Telefone', 'Status', 'Confirmado em', 'E-mail enviado em', 'WhatsApp enviado em', 'Cadastrado em'], ';');

        foreach ($guests as $g) {
            fputcsv($out, [
                $g['name'],
                $g['email'],
                $g['phone'] ?? '',
                $g['status'],
                $g['confirmed_at'] ?? '',
                $g['email_sent_at'] ?? '',
                $g['whatsapp_sent_at'] ?? '',
                $g['created_at'],
            ], ';');
        }

        fclose($out);
        exit;
    }

    public function clicks(string $eventId): void
    {
        Auth::requireLogin();

        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT title FROM events WHERE id = ? AND user_id = ?");
        $stmt->execute([$eventId, Auth::id()]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            http_response_code(404);
            echo "Evento não encontrado";
            return;
        }

        $stmt = $db->prepare(
            "SELECT g.name, g.email, COUNT(c.id) as total_clicks, MAX(c.clicked_at) as last_click
             FROM guests g
             LEFT JOIN invite_clicks c ON c.guest_id = g.id
             WHERE g.event_id = ?
             GROUP BY g.id
             ORDER BY total_clicks DESC"
        );
        $stmt->execute([$eventId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $filename = 'cliques-convite-' . preg_replace('/[^a-z0-9]/i', '-', $event['title']) . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($out, ['Nome', 'E-mail', 'Total de Cliques', 'Último Clique'], ';');

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['name'],
                $r['email'],
                $r['total_clicks'],
                $r['last_click'] ?? '',
            ], ';');
        }

        fclose($out);
        exit;
    }
}
