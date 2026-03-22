<?php

namespace App\Controllers;

use App\Http\Request;
use App\Services\AuthService;
use App\Services\RateLimiter;
use App\Services\ServerManager;
use App\Services\MediaMountService;
use App\Services\ServerService;
use Exception;
use IPMI;
use RuntimeException;

class ServerController extends Controller
{
    public function __construct(
        private ?ServerService $serverService = null,
        private ?ServerManager $serverManager = null,
        private ?MediaMountService $mediaMountService = null,
        private ?AuthService $authService = null
    ) {
        $this->serverService ??= new ServerService();
        $this->serverManager ??= new ServerManager();
        $this->mediaMountService ??= new MediaMountService();
        $this->authService ??= new AuthService();
    }

    public function index(Request $request)
    {
        return $this->json(['servers' => $this->serverService->listForCurrentContext()]);
    }

    public function page(Request $request)
    {
        return $this->view('servers/index', [
            'title' => 'Servers',
            'servers' => $this->serverService->listForCurrentContext(),
            'isAdmin' => $this->authService->isAdmin(),
            'flash_success' => $this->pullFlash('mvc_servers_success'),
            'flash_error' => $this->pullFlash('mvc_servers_error'),
        ]);
    }

    public function show(Request $request)
    {
        $serverId = (int) $request->query('id', 0);
        if ($serverId <= 0) {
            return $this->redirect(routeUrl('/servers'));
        }

        try {
            $payload = $this->serverService->detailForCurrentContext($serverId);
        } catch (RuntimeException $e) {
            return $this->view('errors/simple', [
                'title' => 'Server Detail',
                'heading' => 'Server access error',
                'message' => $e->getMessage(),
                'back_url' => routeUrl('/servers'),
            ], 403);
        }

        $serverIps = $this->serverManager->getServerIps($serverId);
        $isoContext = $this->mediaMountService->serverDetailContext($serverId);
        if (!empty($isoContext['can_manage'])) {
            try {
                $liveMountState = $this->mediaMountService->refresh($serverId);
                $isoContext['state'] = array_merge($isoContext['state'] ?? [], $liveMountState);
            } catch (Exception) {
            }
        }

        $accessLevel = (string) $payload['access_level'];
        $canPower = $this->authService->isAdmin() || in_array($accessLevel, ['restart', 'full'], true);
        $canPowerFull = $this->authService->isAdmin() || $accessLevel === 'full';

        return $this->view('servers/show', [
            'title' => 'Server Detail',
            'server' => $payload['server'],
            'serverIps' => $serverIps,
            'accessLevel' => $accessLevel,
            'isAdmin' => $this->authService->isAdmin(),
            'canManageIso' => $isoContext['can_manage'],
            'activeIsos' => $isoContext['isos'],
            'mountStateRow' => $isoContext['state'],
            'canPower' => $canPower,
            'canPowerFull' => $canPowerFull,
            'flash_power_success' => $this->pullFlash('mvc_server_power_success'),
            'flash_power_error' => $this->pullFlash('mvc_server_power_error'),
            'flash_iso_success' => $this->pullFlash('mvc_iso_success'),
            'flash_iso_error' => $this->pullFlash('mvc_iso_error'),
        ]);
    }

    public function status(Request $request)
    {
        try {
            return $this->json($this->serverService->refreshStatusForCurrentContext((int) $request->query('id', 0)));
        } catch (RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 403);
        }
    }


    public function power(Request $request)
    {
        \App\Http\Csrf::verifyRequest();

        $serverId = (int) $request->input('server_id', 0);
        $action = (string) $request->input('power_action', '');

        try {
            $actor = $this->authService->currentUser();
            $userId = (int) ($actor['id'] ?? 0);

            $rateLimiter = new RateLimiter();
            $scope = 'power:user:' . $userId . ':server:' . $serverId;
            if (!$rateLimiter->attempt($scope, 10, 60)) {
                $wait = $rateLimiter->retryAfter(60);
                throw new RuntimeException(__('app.rate_limit_exceeded', ['seconds' => $wait]));
            }

            $payload = $this->serverService->detailForCurrentContext($serverId);
            $accessLevel = (string) ($payload['access_level'] ?? 'readonly');
            $isAdmin = $this->authService->isAdmin();

            if (!$isAdmin && !in_array($accessLevel, ['restart', 'full'], true)) {
                throw new RuntimeException(__('app.access_denied'));
            }
            if (!$isAdmin && $accessLevel === 'restart' && !in_array($action, ['cycle', 'reset'], true)) {
                throw new RuntimeException(__('server.restart_only_denied'));
            }
            if (!in_array($action, ['on', 'off', 'reset', 'cycle'], true)) {
                throw new RuntimeException('Invalid power action.');
            }

            $message = $this->serverManager->powerAction($serverId, $action, $userId);
            $this->flash('mvc_server_power_success', $message);
            $this->clearFlash('mvc_server_power_error');
        } catch (Exception $e) {
            $this->clearFlash('mvc_server_power_success');
            $this->flash('mvc_server_power_error', $e->getMessage());
        }

        return $this->redirect(routeUrl('/server', ['id' => $serverId]));
    }

    public function edit(Request $request)
    {
        $server = $this->serverManager->find((int) $request->query('id', 0));
        if (!$server) {
            return $this->redirect(routeUrl('/servers'));
        }

        $serverIps = $this->serverManager->getServerIps((int) $server['id']);

        return $this->view('servers/edit', [
            'title' => 'Edit Server',
            'server' => $server,
            'serverIps' => $serverIps,
            'flash_success' => $this->pullFlash('mvc_servers_success'),
            'flash_error' => $this->pullFlash('mvc_servers_error'),
        ]);
    }

    public function create(Request $request)
    {
        \App\Http\Csrf::verifyRequest();

        try {
            $this->serverManager->add($request->all());
            $this->flash('mvc_servers_success', __('servers.added', [
                'name' => (string) $request->input('name', ''),
            ]));
            $this->clearFlash('mvc_servers_error');
        } catch (Exception $e) {
            $this->clearFlash('mvc_servers_success');
            $this->flash('mvc_servers_error', $e->getMessage());
        }

        return $this->redirect(routeUrl('/servers'));
    }

    public function update(Request $request)
    {
        \App\Http\Csrf::verifyRequest();

        $serverId = (int) $request->input('server_id', 0);

        try {
            $this->serverManager->update($request->all());
            $this->flash('mvc_servers_success', __('servers.updated', [
                'name' => (string) $request->input('name', ''),
            ]));
            $this->clearFlash('mvc_servers_error');
        } catch (Exception $e) {
            $this->clearFlash('mvc_servers_success');
            $this->flash('mvc_servers_error', $e->getMessage());
        }

        return $this->redirect(routeUrl('/servers/edit', ['id' => $serverId]));
    }

    public function rotateToken(Request $request)
    {
        \App\Http\Csrf::verifyRequest();

        $serverId = (int) $request->input('server_id', 0);

        try {
            $actor = $this->authService->currentUser();
            $this->serverManager->rotateApiToken($serverId, (int) ($actor['id'] ?? 0));
            $server = $this->serverManager->find($serverId);
            $this->flash('mvc_servers_success', __('servers.token_rotated', [
                'name' => (string) ($server['name'] ?? ('#' . $serverId)),
            ]));
            $this->clearFlash('mvc_servers_error');
        } catch (Exception $e) {
            $this->clearFlash('mvc_servers_success');
            $this->flash('mvc_servers_error', $e->getMessage());
        }

        return $this->redirect(routeUrl('/servers/edit', ['id' => $serverId]));
    }

    public function delete(Request $request)
    {
        \App\Http\Csrf::verifyRequest();

        try {
            $this->serverManager->delete((int) $request->input('server_id', 0));
            $this->flash('mvc_servers_success', __('servers.deleted'));
            $this->clearFlash('mvc_servers_error');
        } catch (Exception $e) {
            $this->clearFlash('mvc_servers_success');
            $this->flash('mvc_servers_error', $e->getMessage());
        }

        return $this->redirect(routeUrl('/servers'));
    }

    public function addIp(Request $request)
    {
        \App\Http\Csrf::verifyRequest();

        $serverId = (int) $request->input('server_id', 0);

        try {
            $actor = $this->authService->currentUser();
            $message = $this->serverManager->addServerIp($serverId, $request->all(), (int) ($actor['id'] ?? 0));
            $this->flash('mvc_servers_success', $message);
            $this->clearFlash('mvc_servers_error');
        } catch (Exception $e) {
            $this->clearFlash('mvc_servers_success');
            $this->flash('mvc_servers_error', $e->getMessage());
        }

        return $this->redirect(routeUrl('/servers/edit', ['id' => $serverId]));
    }

    public function deleteIp(Request $request)
    {
        \App\Http\Csrf::verifyRequest();

        $serverId = (int) $request->input('server_id', 0);
        $ipId = (int) $request->input('ip_id', 0);

        try {
            $actor = $this->authService->currentUser();
            $message = $this->serverManager->deleteServerIp($ipId, $serverId, (int) ($actor['id'] ?? 0));
            $this->flash('mvc_servers_success', $message);
            $this->clearFlash('mvc_servers_error');
        } catch (Exception $e) {
            $this->clearFlash('mvc_servers_success');
            $this->flash('mvc_servers_error', $e->getMessage());
        }

        return $this->redirect(routeUrl('/servers/edit', ['id' => $serverId]));
    }

    public function bootdev(Request $request)
    {
        \App\Http\Csrf::verifyRequest();

        $serverId = (int) $request->input('server_id', 0);
        $device = (string) $request->input('boot_device', '');
        $persistent = $request->input('persistent', '') === '1';

        try {
            $actor = $this->authService->currentUser();
            $message = $this->serverManager->setBootDevice($serverId, $device, $persistent, (int) ($actor['id'] ?? 0));
            $this->flash('mvc_server_power_success', $message);
            $this->clearFlash('mvc_server_power_error');
        } catch (Exception $e) {
            $this->clearFlash('mvc_server_power_success');
            $this->flash('mvc_server_power_error', $e->getMessage());
        }

        return $this->redirect(routeUrl('/server', ['id' => $serverId]));
    }

    public function bmcReset(Request $request)
    {
        \App\Http\Csrf::verifyRequest();

        $serverId = (int) $request->input('server_id', 0);
        $type = (string) $request->input('reset_type', 'warm');

        try {
            $actor = $this->authService->currentUser();
            $message = $this->serverManager->bmcReset($serverId, $type, (int) ($actor['id'] ?? 0));
            $this->flash('mvc_server_power_success', $message);
            $this->clearFlash('mvc_server_power_error');
        } catch (Exception $e) {
            $this->clearFlash('mvc_server_power_success');
            $this->flash('mvc_server_power_error', $e->getMessage());
        }

        return $this->redirect(routeUrl('/server', ['id' => $serverId]));
    }

    public function ipmiUsers(Request $request)
    {
        $serverId = (int) $request->query('id', 0);
        try {
            $users = $this->serverManager->listIpmiUsers($serverId);
            return $this->json(['users' => $users]);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createIpmiUser(Request $request)
    {
        \App\Http\Csrf::verifyRequest();

        $serverId = (int) $request->input('server_id', 0);
        $username = (string) $request->input('ipmi_username', '');
        $password = (string) $request->input('ipmi_password', '');
        $privLevel = (int) $request->input('priv_level', 2);

        try {
            $actor = $this->authService->currentUser();
            $message = $this->serverManager->createIpmiUser($serverId, $username, $password, $privLevel, (int) ($actor['id'] ?? 0));
            $this->flash('mvc_server_power_success', $message);
            $this->clearFlash('mvc_server_power_error');
        } catch (Exception $e) {
            $this->clearFlash('mvc_server_power_success');
            $this->flash('mvc_server_power_error', $e->getMessage());
        }

        return $this->redirect(routeUrl('/server', ['id' => $serverId]));
    }

    public function deleteIpmiUser(Request $request)
    {
        \App\Http\Csrf::verifyRequest();

        $serverId = (int) $request->input('server_id', 0);
        $ipmiUserId = (int) $request->input('ipmi_user_id', 0);

        try {
            $actor = $this->authService->currentUser();
            $message = $this->serverManager->deleteIpmiUser($serverId, $ipmiUserId, (int) ($actor['id'] ?? 0));
            $this->flash('mvc_server_power_success', $message);
            $this->clearFlash('mvc_server_power_error');
        } catch (Exception $e) {
            $this->clearFlash('mvc_server_power_success');
            $this->flash('mvc_server_power_error', $e->getMessage());
        }

        return $this->redirect(routeUrl('/server', ['id' => $serverId]));
    }

    public function webUiCheck(Request $request)
    {
        $serverId = (int) $request->query('id', 0);
        try {
            $payload = $this->serverService->detailForCurrentContext($serverId);
        } catch (RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 403);
        }

        $server   = $payload['server'];
        $ip       = (string) ($server['ip_address'] ?? '');
        $username = (string) ($server['ipmi_username'] ?? '');
        $password = IPMI::decryptPassword((string) ($server['ipmi_password'] ?? ''));

        if ($ip === '') {
            return $this->json(['status' => 'error', 'message' => 'No IP configured for this server.']);
        }

        // Step 1: check port 443 responds at all
        $base = 'https://' . $ip;
        $ch   = curl_init($base . '/');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_NOBODY         => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => false,
        ]);
        curl_exec($ch);
        $portCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $portErr  = curl_error($ch);
        curl_close($ch);

        if ($portCode === 0) {
            return $this->json([
                'status'  => 'down',
                'message' => "Port 443 not reachable. {$portErr}",
            ]);
        }

        // Step 2: attempt BMC session login — this is what hangs on broken BMCs
        $loginUrl = $base . '/api/session';
        $body     = json_encode(['username' => $username, 'password' => $password]);
        $ch       = curl_init($loginUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 7,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        $resp     = curl_exec($ch);
        $sessCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $elapsed  = round(curl_getinfo($ch, CURLINFO_TOTAL_TIME) * 1000);
        $sessErr  = curl_error($ch);
        curl_close($ch);

        if ($sessCode === 0) {
            // Timed out or no response — session subsystem is hung
            return $this->json([
                'status'  => 'hung',
                'message' => "Port 443 OK but session API not responding (timed out after {$elapsed}ms). BMC web session subsystem appears hung — cold reset recommended.",
            ]);
        }

        // Parse response to check if login was accepted or rejected
        $data = json_decode((string) $resp, true);
        if (isset($data['ok'])) {
            if ((int) $data['ok'] === 1) {
                return $this->json([
                    'status'  => 'ok',
                    'message' => "Session API OK — login accepted in {$elapsed}ms. Web UI is fully functional.",
                ]);
            } else {
                $reason = (string) ($data['reason'] ?? 'invalid credentials or locked');
                return $this->json([
                    'status'  => 'ok',
                    'message' => "Session API OK — responded in {$elapsed}ms (login rejected: {$reason}). Web UI is functional.",
                ]);
            }
        }

        // Got a response but not the expected format — still means session is alive
        return $this->json([
            'status'  => 'ok',
            'message' => "Session API responded (HTTP {$sessCode}) in {$elapsed}ms. Web UI appears functional.",
        ]);
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if (strlen($q) < 2) {
            return $this->json(['results' => []]);
        }

        // Rate limit: 20 searches per 30 seconds per user/IP
        $rateLimiter = new \App\Services\RateLimiter();
        $user = $this->authService->currentUser();
        $scopeKey = 'search:user:' . (int) ($user['id'] ?? 0) . ':ip:' . ($_SERVER['REMOTE_ADDR'] ?? '');
        if (!$rateLimiter->attempt($scopeKey, 20, 30)) {
            return $this->json(['error' => 'Too many requests'], 429);
        }

        $db = \App\Database\Database::getInstance();
        $term = preg_replace('/[^\w\s\.\-]/u', '', $q) . '*';

        if ($this->authService->isAdmin()) {
            $rows = $db->fetchAll(
                'SELECT id, name, ip_address, location, status, serial_number, switch_port FROM servers WHERE MATCH(name, ip_address, location, serial_number) AGAINST (? IN BOOLEAN MODE) LIMIT 10',
                [$term]
            );
        } else {
            $userId = (int) ($user['id'] ?? 0);
            $rows = $db->fetchAll(
                'SELECT s.id, s.name, s.ip_address, s.location, s.status FROM servers s JOIN user_servers us ON us.server_id = s.id AND us.user_id = ? WHERE MATCH(s.name, s.ip_address, s.location, s.serial_number) AGAINST (? IN BOOLEAN MODE) LIMIT 10',
                [$userId, $term]
            );
        }

        return $this->json(['results' => $rows]);
    }

    public function bulkPower(Request $request)
    {
        \App\Http\Csrf::verifyRequest();

        $serverIds = array_map('intval', (array) ($request->input('server_ids') ?? []));
        $action = (string) $request->input('power_action', '');

        if (!in_array($action, ['on', 'off', 'reset', 'cycle'], true) || empty($serverIds)) {
            $this->flash('mvc_servers_error', 'Invalid bulk action or no servers selected.');
            return $this->redirect(routeUrl('/servers'));
        }

        $actor = $this->authService->currentUser();
        $actorId = (int) ($actor['id'] ?? 0);
        $errors = [];
        $success = 0;

        foreach ($serverIds as $id) {
            if ($id <= 0) {
                continue;
            }
            try {
                $this->serverManager->powerAction($id, $action, $actorId);
                $success++;
            } catch (Exception $e) {
                $errors[] = '#' . $id . ': ' . $e->getMessage();
            }
        }

        if ($errors) {
            $this->flash('mvc_servers_error', 'Bulk action completed with errors: ' . implode('; ', $errors));
        } else {
            $this->flash('mvc_servers_success', 'Bulk power ' . strtoupper($action) . ' applied to ' . $success . ' server(s).');
        }

        return $this->redirect(routeUrl('/servers'));
    }
}
