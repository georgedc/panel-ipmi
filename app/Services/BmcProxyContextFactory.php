<?php

namespace App\Services;

use App\Database\Database;

final class BmcProxyContextFactory
{
    private Auth $auth;
    private Database $db;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->db = Database::getInstance();
    }

    public function create(array $query, array $serverVars): BmcProxyContext
    {
        if (!$this->auth->isLoggedIn()) {
            BmcProxySupport::fail('Not authenticated', 401);
        }

        $currentUser = $this->auth->getCurrentUser();
        $serverId = isset($query['server_id']) ? (int) $query['server_id'] : 0;
        $path = $query['path'] ?? '/';
        if ($serverId <= 0) {
            BmcProxySupport::fail('Missing server_id', 400);
        }

        $server = $this->db->fetch('SELECT id, ip_address, tls_fingerprint FROM servers WHERE id = ?', [$serverId]);
        if (!$server) {
            BmcProxySupport::fail('Server not found', 404);
        }

        if (!$this->auth->isAdmin()) {
            $access = $this->db->fetch(
                'SELECT access_level FROM user_servers WHERE user_id = ? AND server_id = ?',
                [$currentUser['id'], $serverId]
            );

            if (!$access) {
                BmcProxySupport::fail('Access denied for this server.', 403);
            }

            if (($access['access_level'] ?? '') === 'readonly') {
                BmcProxySupport::fail('Read-only access cannot use BMC proxy.', 403);
            }
        }

        assertBmcTlsFingerprint((string) $server['ip_address'], (string) ($server['tls_fingerprint'] ?? ''));

        if (!isset($_SESSION['bmc_sessions'][$serverId])) {
            BmcProxySupport::fail('No BMC session active. Launch KVM first.', 403);
        }

        $bmc = $_SESSION['bmc_sessions'][$serverId];
        if (($bmc['expires'] ?? 0) < time()) {
            unset($_SESSION['bmc_sessions'][$serverId]);
            BmcProxySupport::fail('BMC session expired.', 403);
        }

        $panelHost = (string) ($serverVars['HTTP_HOST'] ?? 'localhost');
        if (!preg_match('/^[A-Za-z0-9.-]+(?::[0-9]+)?$/', $panelHost)) {
            $panelHost = 'localhost';
        }

        $normalizedPath = '/' . ltrim((string) $path, '/');
        $normalizedPath = preg_replace('#\.\.#', '', $normalizedPath);
        if (!preg_match('#^/[A-Za-z0-9._~!$&\'()*+,;=:@%/-]*$#', $normalizedPath)) {
            BmcProxySupport::fail('Invalid path', 400);
        }

        return new BmcProxyContext(
            $serverId,
            $normalizedPath,
            $panelHost,
            'https://' . $panelHost,
            $server,
            $bmc,
            (string) $bmc['ip'],
            (string) ($bmc['cookie_name'] ?? 'QSESSIONID'),
            (string) ($bmc['session_id'] ?? ''),
            (string) ($bmc['csrf_token'] ?? ''),
            (string) ($bmc['csrf_header_name'] ?? ''),
            is_array($bmc['cookies'] ?? null) ? $bmc['cookies'] : [],
            is_array($bmc['storage'] ?? null) ? $bmc['storage'] : []
        );
    }
}
