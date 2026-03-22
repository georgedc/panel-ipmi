<?php

namespace App\Services;

use App\Database\Database;
use App\Services\Logger;
use RobThree\Auth\TwoFactorAuth;

class AuthService
{
    private Auth $auth;
    private Database $db;
    private Logger $logger;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->db = Database::getInstance();
        $this->logger = new Logger();
    }

    public function currentUser(): ?array
    {
        return $this->auth->getCurrentUser();
    }

    public function isAdmin(): bool
    {
        return $this->auth->isAdmin();
    }

    public function isLoggedIn(): bool
    {
        return $this->auth->isLoggedIn();
    }

    public function logout(): bool
    {
        return $this->auth->logout();
    }

    public function attemptLogin(string $username, string $password): array
    {
        $username = trim($username);
        $password = trim($password);

        if ($username === '' || $password === '') {
            return ['ok' => false, 'error' => __('login.missing_credentials')];
        }

        $clientIp = trim((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'));
        $normalizedUsername = mb_strtolower($username);
        $attemptWindow = 900;
        $maxAttempts = (int) ($this->db->fetch("SELECT value FROM settings WHERE name = 'max_login_attempts'")['value'] ?? 5);

        $rateLimiter = new RateLimiter();
        $ipScope   = 'login:ip:' . $clientIp;
        $userScope = 'login:ip_user:' . $clientIp . '|' . $normalizedUsername;

        if (!$rateLimiter->check($ipScope, $maxAttempts, $attemptWindow) ||
            !$rateLimiter->check($userScope, $maxAttempts, $attemptWindow)) {
            $retryAfter = $rateLimiter->retryAfter($attemptWindow);
            $minutesRemaining = max(1, (int) ceil($retryAfter / 60));
            return ['ok' => false, 'error' => __('login.too_many_attempts', ['minutes' => $minutesRemaining])];
        }

        if (!$this->auth->login($username, $password)) {
            $rateLimiter->attempt($ipScope, $maxAttempts, $attemptWindow);
            $rateLimiter->attempt($userScope, $maxAttempts, $attemptWindow);

            $this->logger->logActivity(null, null, 'login_failed', 'User: ' . $username);
            return ['ok' => false, 'error' => __('login.invalid_credentials')];
        }

        $rateLimiter->reset($ipScope);
        $rateLimiter->reset($userScope);

        $user = $this->auth->getCurrentUser();
        if (!$user) {
            return ['ok' => false, 'error' => __('login.invalid_credentials')];
        }

        $this->logger->logActivity((int) ($_SESSION['user_id'] ?? 0), null, 'login', '');
        $tfaEnabled = (int) ($this->db->fetch("SELECT value FROM settings WHERE name = 'tfa_enabled_admin'")['value'] ?? 0);

        if (($user['role'] ?? '') === 'admin' && $tfaEnabled === 1) {
            $_SESSION['tfa_pending'] = true;
            $_SESSION['tfa_user'] = (int) $user['id'];
            unset($_SESSION['logged_in']);

            return ['ok' => true, 'requires_tfa' => true];
        }

        return ['ok' => true, 'requires_tfa' => false];
    }

    public function hasPendingTwoFactor(): bool
    {
        return isset($_SESSION['tfa_pending'], $_SESSION['tfa_user']) && $_SESSION['tfa_pending'] === true;
    }

    public function currentTwoFactorUser(): ?array
    {
        if (!$this->hasPendingTwoFactor()) {
            return null;
        }

        return $this->db->fetch(
            'SELECT id, username, email, role FROM users WHERE id = ?',
            [(int) $_SESSION['tfa_user']]
        );
    }

    public function verifyTwoFactorCode(string $code): array
    {
        if (!$this->hasPendingTwoFactor()) {
            return ['ok' => false, 'error' => __('tfa.secret_missing'), 'missing' => true];
        }

        $userId = (int) $_SESSION['tfa_user'];
        $user = $this->db->fetch('SELECT * FROM users WHERE id = ?', [$userId]);
        if (!$user || empty($user['tfa_secret'])) {
            return ['ok' => false, 'error' => __('tfa.secret_missing')];
        }

        $tfa = new TwoFactorAuth(APP_NAME);
        if (!$tfa->verifyCode((string) $user['tfa_secret'], trim($code))) {
            return ['ok' => false, 'error' => __('tfa.invalid_code')];
        }

        unset($_SESSION['tfa_pending'], $_SESSION['tfa_user']);
        $this->auth->establishSession($user, false);

        return ['ok' => true];
    }

}
