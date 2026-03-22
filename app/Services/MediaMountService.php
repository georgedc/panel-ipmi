<?php

namespace App\Services;

use App\Repositories\IsoRepository;
use App\Repositories\ServerRepository;
use App\Database\Database;
use RuntimeException;
use Throwable;

class MediaMountService
{
    public function __construct(
        private ?MediaMountManager $mediaManager = null,
        private ?IsoRepository $isos = null,
        private ?ServerRepository $servers = null,
        private ?AuthService $auth = null,
        private ?Database $db = null
    ) {
        $this->mediaManager ??= new MediaMountManager();
        $this->isos ??= new IsoRepository();
        $this->servers ??= new ServerRepository();
        $this->auth ??= new AuthService();
        $this->db ??= Database::getInstance();
    }

    public function mountWorkspace(): array
    {
        $user = $this->requireUser();
        $servers = $this->mediaManager->getIsoMountServers(new \Auth(), $user, $this->db);
        $states = $this->mediaManager->getCachedMountStates(array_map(static fn($server) => (int) $server['id'], $servers), $this->db);

        return [
            'isos' => $this->isos->active(),
            'servers' => $servers,
            'states' => $states,
            'is_admin' => $this->auth->isAdmin(),
            'user' => $user,
        ];
    }

    public function serverDetailContext(int $serverId): array
    {
        $user = $this->requireUser();
        $canManage = $this->mediaManager->canManageIsoForServer($serverId, new \Auth(), $user, $this->db);
        $states = $this->mediaManager->getCachedMountStates([$serverId], $this->db);

        return [
            'can_manage' => $canManage,
            'isos' => $canManage ? $this->isos->active() : [],
            'state' => $states[$serverId] ?? null,
        ];
    }

    public function mount(int $serverId, int $isoId): array
    {
        $user = $this->requireUser();
        if (!$this->mediaManager->canManageIsoForServer($serverId, new \Auth(), $user, $this->db)) {
            throw new RuntimeException(__('iso.permission_mount'));
        }

        $server = $this->servers->find($serverId);
        $iso = $this->isos->find($isoId);
        if (!$server || !$iso) {
            throw new RuntimeException(__('iso.iso_or_server_missing'));
        }
        if ((int) ($iso['is_active'] ?? 0) !== 1) {
            throw new RuntimeException(__('iso.iso_inactive'));
        }

        try {
            $message = $this->mediaManager->mountIsoOnServer($server, $iso);
            $label = __('iso.mounted_label', ['name' => $iso['name']]);
            $this->mediaManager->upsertServerMountState($this->db, $serverId, $isoId, true, $label);

            return [
                'message' => $message,
                'label' => $label,
                'checked_at' => date('Y-m-d H:i:s'),
                'is_mounted' => true,
            ];
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }
    }

    public function unmount(int $serverId): array
    {
        $user = $this->requireUser();
        if (!$this->mediaManager->canManageIsoForServer($serverId, new \Auth(), $user, $this->db)) {
            throw new RuntimeException(__('iso.permission_unmount'));
        }

        $server = $this->servers->find($serverId);
        if (!$server) {
            throw new RuntimeException(__('iso.server_missing'));
        }

        try {
            $message = $this->mediaManager->unmountIsoOnServer($server);
            $label = __('iso.no_iso_mounted');
            $this->mediaManager->upsertServerMountState($this->db, $serverId, null, false, $label);

            return [
                'message' => $message,
                'label' => $label,
                'checked_at' => date('Y-m-d H:i:s'),
                'is_mounted' => false,
            ];
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }
    }

    public function refresh(int $serverId): array
    {
        $user = $this->requireUser();
        if (!$this->mediaManager->canManageIsoForServer($serverId, new \Auth(), $user, $this->db)) {
            throw new RuntimeException(__('iso.permission_check_server'));
        }

        $server = $this->servers->find($serverId);
        if (!$server) {
            throw new RuntimeException(__('iso.server_missing'));
        }

        try {
            return $this->mediaManager->getLiveMountStatus($server, $this->db);
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }
    }

    private function requireUser(): array
    {
        $user = $this->auth->currentUser();
        if (!$user) {
            throw new RuntimeException(__('app.auth_required'));
        }
        return $user;
    }
}
