<?php

namespace App\Services;

use App\Repositories\ServerRepository;
use RuntimeException;

class KvmService
{
    public function __construct(private ?ServerRepository $servers = null, private ?AuthService $auth = null)
    {
        $this->servers ??= new ServerRepository();
        $this->auth ??= new AuthService();
    }

    public function authorizedLaunchUrl(int $serverId): string
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
            return routeUrl('/runtime/ipmi-kvm', ['id' => $serverId]);
        }

        $server = $this->servers->findForUser($serverId, (int) $user['id']);
        if (!$server) {
            throw new RuntimeException(__('app.access_denied'));
        }

        return routeUrl('/runtime/ipmi-kvm', ['id' => $serverId]);
    }
}
