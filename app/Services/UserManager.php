<?php

namespace App\Services;

use App\Database\Database;
use Exception;

class UserManager
{
    public function __construct(private ?Database $db = null)
    {
        $this->db ??= Database::getInstance();
    }

    public function create(array $input): string
    {
        $username = trim((string) ($input['username'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $password = (string) ($input['password'] ?? '');
        $role = (string) ($input['role'] ?? 'user');

        if ($username === '' || !preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
            throw new Exception(__('users.invalid_username'));
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception(__('users.invalid_email'));
        }
        if (strlen($password) < 6) {
            throw new Exception(__('users.password_min'));
        }
        if (!in_array($role, ['admin', 'user'], true)) {
            throw new Exception(__('users.invalid_role'));
        }

        $existingUser = $this->db->fetch('SELECT id FROM users WHERE username = ? OR email = ?', [$username, $email]);
        if ($existingUser) {
            throw new Exception(__('users.duplicate'));
        }

        $this->db->insert('users', [
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]),
            'role' => $role,
        ]);

        return __('users.created');
    }

    public function update(array $input): string
    {
        $userId = (int) ($input['user_id'] ?? 0);
        $newEmail = trim((string) ($input['email'] ?? ''));
        $newRole = (string) ($input['role'] ?? 'user');

        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception(__('users.invalid_email'));
        }
        if (!in_array($newRole, ['admin', 'user'], true)) {
            throw new Exception(__('users.invalid_role'));
        }

        $existingUser = $this->db->fetch('SELECT id, username FROM users WHERE id = ?', [$userId]);
        if (!$existingUser) {
            throw new Exception(__('users.not_found'));
        }

        $updateData = [
            'email' => $newEmail,
            'role' => $newRole,
        ];

        $password = (string) ($input['password'] ?? '');
        if ($password !== '') {
            if (strlen($password) < 6) {
                throw new Exception(__('users.password_min'));
            }
            $updateData['password'] = password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
        }

        $this->db->update('users', $updateData, 'id = ?', [$userId]);
        return __('users.updated');
    }

    public function delete(int $userId): string
    {
        $user = $this->db->fetch('SELECT username FROM users WHERE id = ?', [$userId]);
        if (!$user) {
            throw new Exception(__('users.not_found'));
        }
        if (($user['username'] ?? '') === 'admin') {
            throw new Exception(__('users.delete_blocked_admin'));
        }

        $this->db->delete('user_servers', 'user_id = ?', [$userId]);
        $result = $this->db->delete('users', 'id = ?', [$userId]);
        if (!$result) {
            throw new Exception(__('users.delete_failed'));
        }

        return __('users.deleted');
    }

    public function assignServers(int $userId, array $selectedServers): string
    {
        $user = $this->db->fetch('SELECT id FROM users WHERE id = ?', [$userId]);
        if (!$user) {
            throw new Exception(__('users.not_found'));
        }

        $this->db->delete('user_servers', 'user_id = ?', [$userId]);

        foreach ($selectedServers as $serverIdWithAccess) {
            if (strpos((string) $serverIdWithAccess, ':') === false) {
                continue;
            }

            [$serverId, $accessLevel] = explode(':', (string) $serverIdWithAccess, 2);
            $serverId = (int) $serverId;
            if (!in_array($accessLevel, ['readonly', 'restart', 'full'], true)) {
                $accessLevel = 'readonly';
            }

            $server = $this->db->fetch('SELECT id FROM servers WHERE id = ?', [$serverId]);
            if ($server) {
                $this->db->insert('user_servers', [
                    'user_id' => $userId,
                    'server_id' => $serverId,
                    'access_level' => $accessLevel,
                ]);
            }
        }

        return __('users.assigned');
    }

    public function listUsers(): array
    {
        return $this->db->fetchAll('SELECT id, username, email, role, created_at, last_login FROM users ORDER BY username');
    }

    public function listServers(): array
    {
        return $this->db->fetchAll('SELECT id, name, ip_address FROM servers ORDER BY name');
    }

    public function assignedServersMap(int $userId): array
    {
        $assignedServers = $this->db->fetchAll('SELECT server_id, access_level FROM user_servers WHERE user_id = ?', [$userId]);
        $result = [];
        foreach ($assignedServers as $server) {
            $result[(int) $server['server_id']] = (string) $server['access_level'];
        }
        return $result;
    }

    public function find(int $userId): ?array
    {
        return $this->db->fetch('SELECT id, username, email, role, created_at, last_login FROM users WHERE id = ?', [$userId]);
    }
}
