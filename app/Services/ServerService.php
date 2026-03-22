<?php

namespace App\Services;

use App\Repositories\ServerRepository;
use IPMI;
use RuntimeException;

class ServerService
{
    public function __construct(private ?ServerRepository $servers = null, private ?AuthService $auth = null)
    {
        $this->servers ??= new ServerRepository();
        $this->auth ??= new AuthService();
    }

    public function listForCurrentContext(): array
    {
        $user = $this->auth->currentUser();
        if ($this->auth->isAdmin()) {
            return $this->servers->all();
        }

        return $this->servers->forUser((int) ($user['id'] ?? 0));
    }

    public function detailForCurrentContext(int $serverId): array
    {
        $user = $this->auth->currentUser();
        if (!$user) {
            throw new RuntimeException(__('app.auth_required'));
        }

        if ($this->auth->isAdmin()) {
            $server = $this->servers->find($serverId);
            if (!$server) {
                throw new RuntimeException('Server not found.');
            }
            return [
                'server' => $server,
                'access_level' => 'full',
            ];
        }

        $server = $this->servers->findForUser($serverId, (int) $user['id']);
        if (!$server) {
            throw new RuntimeException(__('app.access_denied'));
        }

        return [
            'server' => $server,
            'access_level' => (string) ($server['access_level'] ?? 'readonly'),
        ];
    }

    public function refreshStatusForCurrentContext(int $serverId): array
    {
        $payload = $this->detailForCurrentContext($serverId);
        $server = $payload['server'];

        $cacheKey = 'status_checked_' . $serverId;
        $lastCheck = (int) ($_SESSION[$cacheKey] ?? 0);
        if (time() - $lastCheck < 20) {
            return [
                'status' => (string) ($server['status'] ?? 'offline'),
                'last_checked' => $server['last_checked'] ?? null,
            ];
        }

        $_SESSION[$cacheKey] = time();
        $ipmi = new IPMI();
        $ipmi->checkServerStatus($serverId);
        $fresh = $this->servers->find($serverId);

        return [
            'status' => (string) ($fresh['status'] ?? 'offline'),
            'last_checked' => $fresh['last_checked'] ?? null,
        ];
    }
}
