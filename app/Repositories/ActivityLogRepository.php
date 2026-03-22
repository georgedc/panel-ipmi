<?php

namespace App\Repositories;

class ActivityLogRepository extends Repository
{
    public function recent(int $limit = 5, ?int $userId = null): array
    {
        if ($userId) {
            return $this->db->fetchAll(
                'SELECT l.*, u.username, s.name as server_name FROM activity_logs l LEFT JOIN users u ON u.id = l.user_id LEFT JOIN servers s ON s.id = l.server_id WHERE l.user_id = ? ORDER BY l.timestamp DESC LIMIT ' . (int) $limit,
                [$userId]
            );
        }

        return $this->db->fetchAll(
            'SELECT l.*, u.username, s.name as server_name FROM activity_logs l LEFT JOIN users u ON u.id = l.user_id LEFT JOIN servers s ON s.id = l.server_id ORDER BY l.timestamp DESC LIMIT ' . (int) $limit
        );
    }
}
