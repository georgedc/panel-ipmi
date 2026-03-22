<?php

namespace App\Services;

use App\Repositories\ServerRepository;
use App\Repositories\UserRepository;
use App\Services\Logger;
use RuntimeException;

class SsoKvmService
{
    public function __construct(
        private ?UserRepository $users = null,
        private ?ServerRepository $servers = null,
        private ?Auth $auth = null,
        private ?Logger $logger = null
    ) {
        $this->users ??= new UserRepository();
        $this->servers ??= new ServerRepository();
        $this->auth ??= new Auth();
        $this->logger ??= new Logger();
    }

    public function consumeLink(array $query): string
    {
        $secret = (string) getEnvValue('WHMCS_SSO_SECRET', '');
        if ($secret === '') {
            throw new RuntimeException('SSO is not configured.');
        }

        $email = trim((string) ($query['email'] ?? ''));
        $serverId = (int) ($query['server_id'] ?? 0);
        $expires = (int) ($query['expires'] ?? 0);
        $nonce = trim((string) ($query['nonce'] ?? ''));
        $sig = trim((string) ($query['sig'] ?? ''));

        if ($email === '' || $serverId <= 0 || $expires <= 0 || $nonce === '' || $sig === '') {
            throw new RuntimeException('Missing SSO parameters.');
        }

        if ($expires < time() || $expires > time() + 300) {
            throw new RuntimeException('SSO link expired.');
        }

        if (!preg_match('/^[A-Fa-f0-9]{32,128}$/', $nonce)) {
            throw new RuntimeException('Invalid SSO nonce.');
        }

        $payload = implode('|', [
            mb_strtolower($email),
            (string) $serverId,
            (string) $expires,
            $nonce,
        ]);
        $expectedSig = hash_hmac('sha256', $payload, $secret);
        if (!hash_equals($expectedSig, strtolower($sig))) {
            throw new RuntimeException('Invalid SSO signature.');
        }

        $nonceFile = sys_get_temp_dir() . '/ipmi_sso_' . hash('sha256', $nonce);
        if (file_exists($nonceFile)) {
            throw new RuntimeException('SSO link already used.');
        }
        file_put_contents($nonceFile, (string) time(), LOCK_EX);
        @chmod($nonceFile, 0600);

        $user = $this->users->findByEmailInsensitive($email);
        if (!$user) {
            throw new RuntimeException('No panel user matches this email.');
        }

        if (($user['role'] ?? '') === 'admin') {
            throw new RuntimeException('Admin SSO is not allowed.');
        }

        $server = $this->servers->find($serverId);
        if (!$server) {
            throw new RuntimeException('Server not found.');
        }

        if (!$this->users->hasServerAccess((int) $user['id'], $serverId)) {
            throw new RuntimeException('User does not have access to this server.');
        }

        if (!$this->auth->establishSession($user, true)) {
            throw new RuntimeException('Unable to create panel session.');
        }

        $_SESSION['whmcs_sso'] = [
            'email' => $user['email'],
            'server_id' => $serverId,
            'expires' => time() + 300,
        ];

        $this->logger->logActivity((int) $user['id'], $serverId, 'whmcs_sso_kvm', 'SSO bridge to KVM launched');

        return routeUrl('/runtime/ipmi-kvm', ['id' => $serverId]);
    }
}
