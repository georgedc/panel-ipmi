<?php

namespace App\Repositories;

class UserRepository extends Repository
{
    public function findByEmail(string $email): ?array
    {
        return $this->db->fetch('SELECT * FROM users WHERE email = ?', [$email]);
    }

    public function findByEmailInsensitive(string $email): ?array
    {
        return $this->db->fetch(
            'SELECT id, username, email, role, tfa_secret FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1',
            [$email]
        );
    }

    public function hasServerAccess(int $userId, int $serverId): bool
    {
        return (bool) $this->db->fetch(
            'SELECT access_level FROM user_servers WHERE user_id = ? AND server_id = ?',
            [$userId, $serverId]
        );
    }
}
