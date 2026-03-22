<?php

namespace App\Services;

use App\Database\Database;

class Auth
{
    private Database $db;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    public function login($username, $password): bool
    {
        $user = $this->db->fetch(
            'SELECT * FROM users WHERE username = ?',
            [$username]
        );

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        session_regenerate_id(true);
        $this->db->update(
            'users',
            ['last_login' => date('Y-m-d H:i:s')],
            'id = ?',
            [$user['id']]
        );
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();

        return true;
    }

    public function establishSession(array $user, bool $updateLastLogin = true): bool
    {
        if (empty($user['id']) || empty($user['username']) || empty($user['role'])) {
            return false;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        session_regenerate_id(true);

        if ($updateLastLogin) {
            $this->db->update(
                'users',
                ['last_login' => date('Y-m-d H:i:s')],
                'id = ?',
                [$user['id']]
            );
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();

        return true;
    }

    public function logout(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION = [];
        session_unset();

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        return true;
    }

    public function isLoggedIn(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return false;
        }

        if (time() - $_SESSION['last_activity'] > SESSION_LIFETIME) {
            $this->logout();
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }

    public function getCurrentUser(): ?array
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return $this->db->fetch(
            'SELECT id, username, email, role, created_at, last_login FROM users WHERE id = ?',
            [$_SESSION['user_id']]
        ) ?: null;
    }

    public function isAdmin(): bool
    {
        return $this->isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin';
    }

    public function registerUser($username, $password, $email, $role = 'user')
    {
        $existingUser = $this->db->fetch(
            'SELECT id FROM users WHERE username = ? OR email = ?',
            [$username, $email]
        );

        if ($existingUser) {
            return false;
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);

        return $this->db->insert('users', [
            'username' => $username,
            'password' => $hashedPassword,
            'email' => $email,
            'role' => $role,
        ]);
    }

    public function changePassword($userId, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => HASH_COST]);

        return $this->db->update(
            'users',
            ['password' => $hashedPassword],
            'id = ?',
            [$userId]
        );
    }
}
