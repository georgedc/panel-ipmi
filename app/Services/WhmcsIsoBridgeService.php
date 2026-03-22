<?php

namespace App\Services;

use App\Database\Database;
use App\Repositories\IsoRepository;
use App\Repositories\ServerRepository;
use RuntimeException;
use Throwable;

class WhmcsIsoBridgeService
{
    public function __construct(
        private ?MediaMountManager $mediaManager = null,
        private ?IsoRepository $isos = null,
        private ?ServerRepository $servers = null,
        private ?Database $db = null
    ) {
        $this->mediaManager ??= new MediaMountManager();
        $this->isos ??= new IsoRepository();
        $this->servers ??= new ServerRepository();
        $this->db ??= Database::getInstance();
    }

    public function listIsos(int $serverId): array
    {
        $server = $this->requireServer($serverId);

        // Refresh from the real BMC so the cached state is up to date
        try {
            $this->mediaManager->getLiveMountStatus($server, $this->db);
        } catch (Throwable $e) {
            // If the live check fails, fall back to whatever is cached
        }

        $state = $this->resolveState($serverId);

        return [
            'isos' => array_map(
                static fn(array $iso): array => [
                    'id' => (int) $iso['id'],
                    'name' => (string) ($iso['name'] ?? ''),
                ],
                $this->isos->active()
            ),
            'mount_state' => $state,
        ];
    }

    public function mountIso(int $serverId, int $isoId): array
    {
        $server = $this->requireServer($serverId);
        $iso = $this->isos->find($isoId);

        if (!$iso) {
            throw new RuntimeException(__('iso.selected_iso_missing'));
        }
        if ((int) ($iso['is_active'] ?? 0) !== 1) {
            throw new RuntimeException(__('iso.iso_inactive'));
        }

        try {
            $message = $this->mediaManager->mountIsoOnServer($server, $iso);
            $label = __('iso.mounted_label', ['name' => $iso['name']]);
            $this->mediaManager->upsertServerMountState($this->db, $serverId, $isoId, true, $label);
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }

        return [
            'message' => $message,
            'mount_state' => $this->resolveState($serverId),
        ];
    }

    public function unmountIso(int $serverId): array
    {
        $server = $this->requireServer($serverId);

        try {
            $message = $this->mediaManager->unmountIsoOnServer($server);
            $label = __('iso.no_iso_mounted');
            $this->mediaManager->upsertServerMountState($this->db, $serverId, null, false, $label);
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }

        return [
            'message' => $message,
            'mount_state' => $this->resolveState($serverId),
        ];
    }

    public function refreshIso(int $serverId): array
    {
        $server = $this->requireServer($serverId);

        try {
            $this->mediaManager->getLiveMountStatus($server, $this->db);
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }

        return [
            'message' => __('iso.status_checked'),
            'mount_state' => $this->resolveState($serverId),
        ];
    }

    private function requireServer(int $serverId): array
    {
        $server = $this->servers->find($serverId);
        if (!$server) {
            throw new RuntimeException(__('iso.server_missing'));
        }

        return $server;
    }

    private function resolveState(int $serverId): array
    {
        $state = $this->mediaManager->getCachedMountStates([$serverId], $this->db)[$serverId] ?? null;

        if (!$state) {
            return [
                'server_id' => $serverId,
                'iso_id' => null,
                'is_mounted' => false,
                'label' => __('iso.no_iso_mounted'),
                'checked_at' => '',
                'iso_name' => '',
            ];
        }

        return [
            'server_id' => (int) ($state['server_id'] ?? $serverId),
            'iso_id' => isset($state['iso_id']) ? (int) $state['iso_id'] : null,
            'is_mounted' => (bool) ($state['is_mounted'] ?? false),
            'label' => (string) ($state['label'] ?? __('iso.no_iso_mounted')),
            'checked_at' => (string) ($state['checked_at'] ?? ''),
            'iso_name' => (string) ($state['iso_name'] ?? ''),
        ];
    }
}
