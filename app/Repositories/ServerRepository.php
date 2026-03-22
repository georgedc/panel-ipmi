<?php

namespace App\Repositories;

class ServerRepository extends Repository
{
    public function all(): array
    {
        return $this->db->fetchAll('SELECT * FROM servers ORDER BY name');
    }

    public function find(int $serverId): ?array
    {
        return $this->db->fetch('SELECT * FROM servers WHERE id = ?', [$serverId]);
    }

    public function findForUser(int $serverId, int $userId): ?array
    {
        return $this->db->fetch(
            'SELECT s.*, us.access_level FROM servers s INNER JOIN user_servers us ON us.server_id = s.id WHERE s.id = ? AND us.user_id = ?',
            [$serverId, $userId]
        );
    }

    public function forUser(int $userId): array
    {
        return $this->db->fetchAll(
            'SELECT s.* FROM servers s INNER JOIN user_servers us ON us.server_id = s.id WHERE us.user_id = ? ORDER BY s.name',
            [$userId]
        );
    }

    public function statusCounts(?int $userId = null): array
    {
        if ($userId) {
            $rows = $this->db->fetchAll(
                'SELECT s.status, COUNT(*) as total FROM servers s INNER JOIN user_servers us ON us.server_id = s.id WHERE us.user_id = ? GROUP BY s.status',
                [$userId]
            );
        } else {
            $rows = $this->db->fetchAll('SELECT status, COUNT(*) as total FROM servers GROUP BY status');
        }

        $counts = ['total' => 0, 'online' => 0, 'offline' => 0, 'maintenance' => 0];
        foreach ($rows as $row) {
            $status = strtolower((string) ($row['status'] ?? 'offline'));
            $total = (int) ($row['total'] ?? 0);
            $counts['total'] += $total;
            if (isset($counts[$status])) {
                $counts[$status] += $total;
            }
        }

        return $counts;
    }
}
