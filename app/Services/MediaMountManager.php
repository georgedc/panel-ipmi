<?php

namespace App\Services;

use App\Database\Database;
use Exception;
use IPMI;

class MediaMountManager
{
    public function upsertServerMountState(Database $db, int $serverId, ?int $isoId, bool $isMounted, string $label): void
    {
        $existing = $db->fetch('SELECT server_id FROM server_media_mounts WHERE server_id = ?', [$serverId]);
        $data = [
            'iso_id' => $isoId,
            'is_mounted' => $isMounted ? 1 : 0,
            'mounted_label' => $label,
            'checked_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $db->update('server_media_mounts', $data, 'server_id = ?', [$serverId]);
            return;
        }

        $data['server_id'] = $serverId;
        $db->insert('server_media_mounts', $data);
    }

    public function getCachedMountStates(array $serverIds, Database $db): array
    {
        $serverIds = array_values(array_filter(array_map('intval', $serverIds)));
        if ($serverIds === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($serverIds), '?'));
        $rows = $db->fetchAll(
            "SELECT sm.server_id, sm.iso_id, sm.is_mounted, sm.mounted_label, sm.checked_at, i.name AS iso_name
             FROM server_media_mounts sm
             LEFT JOIN iso_images i ON i.id = sm.iso_id
             WHERE sm.server_id IN ($placeholders)",
            $serverIds
        );

        $states = [];
        foreach ($rows as $row) {
            $states[(int) $row['server_id']] = [
                'server_id' => (int) $row['server_id'],
                'iso_id' => isset($row['iso_id']) ? (int) $row['iso_id'] : null,
                'is_mounted' => (int) ($row['is_mounted'] ?? 0) === 1,
                'label' => (string) ($row['mounted_label'] ?? ''),
                'checked_at' => (string) ($row['checked_at'] ?? ''),
                'iso_name' => (string) ($row['iso_name'] ?? ''),
            ];
        }

        return $states;
    }

    public function mountIsoOnServer(array $server, array $iso): string
    {
        $serverType = strtolower(trim((string) ($server['ipmi_type'] ?? 'generic')));
        if ($serverType === 'asrock') {
            return $this->mountIsoOnAsrock($server, $iso);
        }
        return $this->mountIsoOnSupermicro($server, $iso);
    }

    public function unmountIsoOnServer(array $server): string
    {
        $serverType = strtolower(trim((string) ($server['ipmi_type'] ?? 'generic')));
        if ($serverType === 'asrock') {
            return $this->unmountIsoOnAsrock($server);
        }
        return $this->unmountIsoOnSupermicro($server);
    }

    public function getLiveMountStatus(array $server, Database $db): array
    {
        $serverType = strtolower(trim((string) ($server['ipmi_type'] ?? 'generic')));

        if ($serverType === 'asrock') {
            $session = $this->getAsrockMediaSession($server);
            $config = $this->asrockApi($session, '/api/settings/media/remote/configurations');
            $current = is_array($config[0] ?? null) ? $config[0] : [];
            $status = (int) ($current['redirection_status'] ?? 0);
            $imageName = trim((string) ($current['image_name'] ?? ''));
            $isMounted = $imageName !== '' && in_array($status, [1, 100, 27], true);
            $label = $isMounted ? __('iso.mounted_label', ['name' => $imageName]) : __('iso.no_iso_mounted');
            $this->upsertServerMountState($db, (int) $server['id'], null, $isMounted, $label);
            return [
                'ok' => true,
                'is_mounted' => $isMounted,
                'label' => $label,
                'checked_at' => date('Y-m-d H:i:s'),
            ];
        }

        $session = $this->getSupermicroMediaSession($server);
        $statusResponse = $this->supermicroMediaOp($session, 'vm_status', [], 150, 15);
        $isMounted = strpos($statusResponse, 'STATUS="1"') !== false;
        $currentState = $this->getCachedMountStates([(int) $server['id']], $db);
        $cached = $currentState[(int) $server['id']] ?? null;
        $label = $isMounted
            ? (($cached['is_mounted'] ?? false) && !empty($cached['label']) ? (string) $cached['label'] : __('iso.virtual_media_mounted'))
            : __('iso.no_iso_mounted');
        $this->upsertServerMountState($db, (int) $server['id'], $isMounted ? ($cached['iso_id'] ?? null) : null, $isMounted, $label);

        return [
            'ok' => true,
            'is_mounted' => $isMounted,
            'label' => $label,
            'checked_at' => date('Y-m-d H:i:s'),
        ];
    }

    public function getIsoMountServers(Auth $auth, array $currentUser, Database $db): array
    {
        if ($auth->isAdmin()) {
            return $db->fetchAll('SELECT id, name, ipmi_type FROM servers ORDER BY name');
        }

        return $db->fetchAll(
            "SELECT s.id, s.name, s.ipmi_type
             FROM servers s
             JOIN user_servers us ON us.server_id = s.id
             WHERE us.user_id = ? AND us.access_level = 'full'
             ORDER BY s.name",
            [$currentUser['id']]
        );
    }

    public function canManageIsoForServer(int $serverId, Auth $auth, array $currentUser, Database $db): bool
    {
        if ($auth->isAdmin()) {
            return true;
        }

        $access = $db->fetch('SELECT access_level FROM user_servers WHERE user_id = ? AND server_id = ?', [$currentUser['id'], $serverId]);
        return is_array($access) && ($access['access_level'] ?? '') === 'full';
    }

    private function getIsoMountSource(array $iso): array
    {
        $sourceType = (string) ($iso['source_type'] ?? 'local');

        if ($sourceType === 'local') {
            $localPath = (string) ($iso['local_path'] ?? '');
            if ($localPath === '' || !is_file($localPath)) {
                throw new Exception('The local ISO no longer exists on the panel.');
            }

            $directory = rtrim(dirname($localPath), '/') . '/';
            return [
                'share_type' => 'nfs',
                'host' => $this->getPanelNfsHost(),
                'full_path' => $localPath,
                'directory' => $directory,
                'file_name' => basename($localPath),
                'username' => '',
                'password' => '',
            ];
        }

        if ($sourceType === 'remote_nfs') {
            $remotePath = (string) ($iso['remote_path'] ?? '');
            return [
                'share_type' => 'nfs',
                'host' => (string) ($iso['remote_host'] ?? ''),
                'full_path' => $remotePath,
                'directory' => rtrim(dirname($remotePath), '/') . '/',
                'file_name' => basename($remotePath),
                'username' => '',
                'password' => '',
            ];
        }

        $remotePath = (string) ($iso['remote_path'] ?? '');
        return [
            'share_type' => 'cifs',
            'host' => (string) ($iso['remote_host'] ?? ''),
            'full_path' => $remotePath,
            'directory' => rtrim(dirname('/' . ltrim($remotePath, '/')), '/') . '/',
            'file_name' => basename($remotePath),
            'username' => (string) ($iso['remote_username'] ?? ''),
            'password' => IPMI::decryptPassword((string) ($iso['remote_password'] ?? '')),
        ];
    }

    private function getPanelNfsHost(): string
    {
        $configured = trim((string) getEnvValue('ISO_LIBRARY_NFS_HOST', ''));
        if ($configured !== '') {
            return $configured;
        }

        $host = parse_url(APP_URL, PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            throw new Exception('Unable to determine the panel NFS host.');
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return $host;
        }

        $resolved = gethostbyname($host);
        if (!filter_var($resolved, FILTER_VALIDATE_IP)) {
            throw new Exception('Unable to resolve the panel NFS host.');
        }

        return $resolved;
    }

    private function parseResponseCookies(string $header): array
    {
        $cookies = [];
        if (preg_match_all('/^Set-Cookie:\s*([^=;\s]+)=([^;]*)/mi', $header, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $row) {
                $cookies[trim($row[1])] = trim($row[2]);
            }
        }
        return $cookies;
    }

    private function buildCookieHeader(array $cookies, array $extra = []): string
    {
        $items = [];
        foreach ($cookies as $key => $value) {
            if ($key !== '' && $value !== '') {
                $items[] = $key . '=' . $value;
            }
        }
        foreach ($extra as $key => $value) {
            if ($key !== '' && $value !== '') {
                $items[] = $key . '=' . $value;
            }
        }
        return implode('; ', $items);
    }

    private function curlResponse(string $url, array $options, int $timeout = 20, int $connectTimeout = 10): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, $options + [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_ENCODING => '',
            CURLOPT_CONNECTTIMEOUT => $connectTimeout,
            CURLOPT_TIMEOUT => $timeout,
        ]);
        $response = curl_exec($ch);
        if ($response === false && in_array(curl_errno($ch), [7, 28, 52, 56], true)) {
            usleep(300000);
            $response = curl_exec($ch);
        }
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception($error !== '' ? $error : 'HTTP connection failed.');
        }
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [
            'http_code' => $httpCode,
            'header' => substr($response, 0, $headerSize),
            'body' => substr($response, $headerSize),
        ];
    }

    private function getSupermicroMediaSession(array $server): array
    {
        $ip = (string) $server['ip_address'];
        $user = (string) $server['ipmi_username'];
        $pass = IPMI::decryptPassword((string) $server['ipmi_password']);
        assertBmcTlsFingerprint($ip, (string) ($server['tls_fingerprint'] ?? ''));

        $login = $this->curlResponse('https://' . $ip . '/cgi/login.cgi', [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(['name' => $user, 'pwd' => $pass]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'X-Requested-With: XMLHttpRequest',
            ],
        ], 30, 10);

        if ($login['http_code'] !== 200) {
            throw new Exception('Failed to authenticate against the Supermicro BMC.');
        }

        $cookies = $this->parseResponseCookies($login['header']);
        if (empty($cookies['SID'])) {
            throw new Exception('The Supermicro BMC did not return an SID cookie.');
        }

        $warm = $this->curlResponse('https://' . $ip . '/cgi/url_redirect.cgi?url_name=mainmenu', [
            CURLOPT_HTTPHEADER => [
                'Cookie: ' . $this->buildCookieHeader($cookies),
                'X-Requested-With: XMLHttpRequest',
            ],
        ], 45, 10);
        $cookies = array_merge($cookies, $this->parseResponseCookies($warm['header']));

        $page = $this->curlResponse('https://' . $ip . '/cgi/url_redirect.cgi?url_name=vm_cdrom', [
            CURLOPT_HTTPHEADER => [
                'Cookie: ' . $this->buildCookieHeader($cookies),
                'X-Requested-With: XMLHttpRequest',
            ],
        ], 60, 10);

        if (!preg_match('/SmcCsrfInsert \("([^"]+)", "([^"]+)"\)/', $page['body'], $m)) {
            throw new Exception(__('iso.supermicro_csrf_error'));
        }

        return [
            'ip' => $ip,
            'cookies' => $cookies,
            'csrf_name' => $m[1],
            'csrf_token' => $m[2],
        ];
    }

    private function supermicroMediaOp(array $session, string $operation, array $data = [], int $timeout = 60, int $connectTimeout = 10): string
    {
        $postData = array_merge(['op' => $operation], $data);
        $vmUrl = 'https://' . $session['ip'] . '/cgi/url_redirect.cgi?url_name=vm_cdrom';
        $response = $this->curlResponse('https://' . $session['ip'] . '/cgi/op.cgi', [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_HTTPHEADER => [
                'Cookie: ' . $this->buildCookieHeader($session['cookies']),
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With: XMLHttpRequest',
                $session['csrf_name'] . ': ' . $session['csrf_token'],
                'Referer: ' . $vmUrl,
                'Origin: https://' . $session['ip'],
            ],
        ], $timeout, $connectTimeout);

        if ($response['http_code'] !== 200) {
            throw new Exception('Supermicro BMC responded with HTTP ' . $response['http_code'] . ' during ' . $operation . '.');
        }

        return $response['body'];
    }

    private function mountIsoOnSupermicro(array $server, array $iso): string
    {
        $source = $this->getIsoMountSource($iso);
        $session = $this->getSupermicroMediaSession($server);

        $host = $source['share_type'] === 'nfs' ? 'nfs://' . $source['host'] : $source['host'];
        $this->supermicroMediaOp($session, 'config_iso', [
            'host' => $host,
            'path' => $source['full_path'],
            'user' => $source['username'],
            'pwd' => $source['password'],
        ]);

        $mountResponse = $this->supermicroMediaOp($session, 'mount_iso');
        sleep(5);
        $statusResponse = $this->supermicroMediaOp($session, 'vm_status', [], 150, 15);

        if (strpos($mountResponse, 'VMCOMCODE=001') === false || strpos($statusResponse, 'STATUS="1"') === false) {
            throw new Exception(__('iso.supermicro_mount_confirm_error'));
        }

        return __('iso.supermicro_mounted');
    }

    private function unmountIsoOnSupermicro(array $server): string
    {
        $session = $this->getSupermicroMediaSession($server);
        $unmountResponse = $this->supermicroMediaOp($session, 'umount_iso');
        sleep(3);
        $statusResponse = $this->supermicroMediaOp($session, 'vm_status', [], 150, 15);

        if (strpos($unmountResponse, 'VMCOMCODE=001') === false || strpos($statusResponse, 'STATUS="0"') === false) {
            throw new Exception(__('iso.supermicro_unmount_confirm_error'));
        }

        return __('iso.supermicro_unmounted');
    }

    private function getAsrockMediaSession(array $server): array
    {
        $serverId = (int) ($server['id'] ?? 0);
        $ip = (string) $server['ip_address'];
        $user = (string) $server['ipmi_username'];
        $pass = IPMI::decryptPassword((string) $server['ipmi_password']);
        assertBmcTlsFingerprint($ip, (string) ($server['tls_fingerprint'] ?? ''));

        $cachedSession = $this->getReusableAsrockSession($serverId, $ip);
        if ($cachedSession !== null) {
            return $cachedSession;
        }

        $login = $this->curlResponse('https://' . $ip . '/api/session', [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'username' => $user,
                'password' => $pass,
                'certlogin' => '0',
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Accept: application/json, text/javascript, */*; q=0.01',
                'X-Requested-With: XMLHttpRequest',
                'User-Agent: Mozilla/5.0',
            ],
        ]);

        if ($login['http_code'] !== 200) {
            $loginData = json_decode($login['body'], true);
            if (is_array($loginData) && (int) ($loginData['code'] ?? 0) === 15000) {
                $cachedSession = $this->getReusableAsrockSession($serverId, $ip);
                if ($cachedSession !== null) {
                    return $cachedSession;
                }
            }
            throw new Exception('Failed to authenticate against the ASRock BMC.');
        }

        $data = json_decode($login['body'], true);
        $cookies = $this->parseResponseCookies($login['header']);
        if (!is_array($data) || empty($data['CSRFToken']) || empty($cookies['QSESSIONID'])) {
            throw new Exception('ASRock did not return a valid media session.');
        }

        $session = [
            'ip' => $ip,
            'cookies' => $cookies,
            'csrf' => (string) $data['CSRFToken'],
        ];
        $this->storeAsrockSession($serverId, $session);
        return $session;
    }

    private function getReusableAsrockSession(int $serverId, string $ip): ?array
    {
        if ($serverId <= 0 || session_status() !== PHP_SESSION_ACTIVE) {
            return null;
        }

        $candidate = $_SESSION['bmc_sessions'][$serverId] ?? null;
        if (!is_array($candidate)) {
            return null;
        }

        if (($candidate['cookie_name'] ?? 'QSESSIONID') !== 'QSESSIONID') {
            return null;
        }

        if (($candidate['ip'] ?? '') !== $ip) {
            return null;
        }

        if ((int) ($candidate['expires'] ?? 0) <= time()) {
            unset($_SESSION['bmc_sessions'][$serverId]);
            return null;
        }

        $cookies = $candidate['cookies'] ?? ['QSESSIONID' => ($candidate['session_id'] ?? '')];
        $csrf = (string) ($candidate['csrf_token'] ?? $candidate['csrf'] ?? '');
        if (!is_array($cookies) || $csrf === '') {
            return null;
        }

        $session = [
            'ip' => $ip,
            'cookies' => $cookies,
            'csrf' => $csrf,
        ];

        try {
            $this->asrockApi($session, '/api/settings/media/general');
            return $session;
        } catch (Exception $e) {
            unset($_SESSION['bmc_sessions'][$serverId]);
            return null;
        }
    }

    private function storeAsrockSession(int $serverId, array $session): void
    {
        if ($serverId <= 0 || session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        if (!isset($_SESSION['bmc_sessions']) || !is_array($_SESSION['bmc_sessions'])) {
            $_SESSION['bmc_sessions'] = [];
        }

        $_SESSION['bmc_sessions'][$serverId] = [
            'ip' => (string) ($session['ip'] ?? ''),
            'session_id' => (string) (($session['cookies']['QSESSIONID'] ?? '')),
            'cookie_name' => 'QSESSIONID',
            'cookies' => $session['cookies'] ?? [],
            'csrf_header_name' => 'X-CSRFTOKEN',
            'csrf_token' => (string) ($session['csrf'] ?? ''),
            'kvm_token' => (string) (($_SESSION['bmc_sessions'][$serverId]['kvm_token'] ?? '')),
            'storage' => $_SESSION['bmc_sessions'][$serverId]['storage'] ?? [],
            'expires' => time() + 1800,
        ];
    }

    private function asrockApi(array $session, string $path, string $method = 'GET', array $data = [], bool $jsonBody = false): array
    {
        $options = [
            CURLOPT_HTTPHEADER => [
                'Cookie: ' . $this->buildCookieHeader($session['cookies'], ['refresh_disable' => '1']),
                'X-CSRFTOKEN: ' . $session['csrf'],
                'X-Requested-With: XMLHttpRequest',
                'Accept: application/json, text/javascript, */*; q=0.01',
                'User-Agent: Mozilla/5.0',
            ],
        ];

        if (in_array($method, ['POST', 'PATCH'], true)) {
            if ($method === 'POST') {
                $options[CURLOPT_POST] = true;
            } else {
                $options[CURLOPT_CUSTOMREQUEST] = 'PATCH';
            }

            if ($jsonBody) {
                $options[CURLOPT_POSTFIELDS] = json_encode($data, JSON_UNESCAPED_SLASHES);
                $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
            } elseif ($data === []) {
                $options[CURLOPT_POSTFIELDS] = '';
                $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
            } else {
                $options[CURLOPT_POSTFIELDS] = http_build_query($data);
                $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
            }
        }

        $response = $this->curlResponse('https://' . $session['ip'] . $path, $options);
        if ($response['http_code'] !== 200) {
             $decodedError = json_decode($response['body'], true);
            if ($path === '/api/settings/media/remote/start-media' && is_array($decodedError) && (int) ($decodedError['code'] ?? 0) === 13410) {
                throw new Exception(__('iso.asrock_start_media_failed_13410'));
            }
            throw new Exception('ASRock responded with HTTP ' . $response['http_code'] . ' on ' . $path . '.');
        }

        $decoded = json_decode($response['body'], true);
        return is_array($decoded) ? $decoded : [];
    }

    private function getAsrockRemoteImages(array $session, int $attempts = 4): array
    {
        $lastError = null;
        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                return $this->asrockApi($session, '/api/settings/media/remote/images');
            } catch (Exception $e) {
                $lastError = $e;
                if (strpos($e->getMessage(), '/api/settings/media/remote/images') === false || $attempt >= $attempts) {
                    throw $e;
                }
                sleep(2);
            }
        }

        if ($lastError) {
            throw $lastError;
        }

        return [];
    }

    private function refreshAsrockRemoteImages(array $session): array
    {
        $lastError = new Exception('Failed to refresh remote ISO images.');
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                $this->asrockApi($session, '/api/settings/media/remote/rmediarefreshlist', 'POST', []);
            } catch (Exception $e) {
                $lastError = $e;
            }
            sleep(2);
            try {
                return $this->getAsrockRemoteImages($session, 2);
            } catch (Exception $e) {
                $lastError = $e;
            }
        }

        throw $lastError;
    }

    private function mountIsoOnAsrock(array $server, array $iso): string
    {
        $source = $this->getIsoMountSource($iso);
        $session = $this->getAsrockMediaSession($server);
        $general = $this->asrockApi($session, '/api/settings/media/general');

        if ((int) ($general['remote_media_support'] ?? 0) !== 1) {
            throw new Exception(__('iso.asrock_remote_media_disabled'));
        }

        $expectedShareType = $source['share_type'];
        $expectedHost = $source['host'];
        $expectedPath = $source['directory'];

        if (
            ($general['cd_remote_share_type'] ?? '') !== $expectedShareType
            || (string) ($general['cd_remote_server_address'] ?? '') !== $expectedHost
            || (string) ($general['cd_remote_source_path'] ?? '') !== $expectedPath
            || (int) ($general['mount_cd'] ?? 0) !== 1
        ) {
            $general['remote_media_support'] = 1;
            $general['cd_remote_share_type'] = $expectedShareType;
            $general['cd_remote_server_address'] = $expectedHost;
            $general['cd_remote_source_path'] = $expectedPath;
            $general['cd_remote_user_name'] = (string) ($source['username'] ?? '');
            $general['mount_cd'] = 1;
            $this->asrockApi($session, '/api/settings/media/general', 'PATCH', $general, true);
        }

        $targetName = trim((string) $source['file_name']);
        $targetNameLower = strtolower($targetName);
        $images = $this->refreshAsrockRemoteImages($session);
        $match = null;
        foreach ($images as $image) {
            if (!is_array($image)) {
                continue;
            }
            $imageName = trim((string) ($image['image_name'] ?? ''));
            $imageNameLower = strtolower($imageName);
            if ($imageNameLower === $targetNameLower || basename($imageNameLower) === basename($targetNameLower)) {
                $match = $image;
                break;
            }
        }

        $config = $this->asrockApi($session, '/api/settings/media/remote/configurations');
        foreach ($config as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $entryName = trim((string) ($entry['image_name'] ?? ''));
            $entryNameLower = strtolower($entryName);
            $entryStatus = (int) ($entry['redirection_status'] ?? 0);
            if (($entryNameLower === $targetNameLower || basename($entryNameLower) === basename($targetNameLower)) && in_array($entryStatus, [1, 27, 100], true)) {
                return __('iso.asrock_already_mounted');
            }
        }

        if (!$match) {
            $images = $this->getAsrockRemoteImages($session);
            foreach ($images as $image) {
                if (!is_array($image)) {
                    continue;
                }
                $imageName = trim((string) ($image['image_name'] ?? ''));
                $imageNameLower = strtolower($imageName);
                if ($imageNameLower === $targetNameLower || basename($imageNameLower) === basename($targetNameLower)) {
                    $match = $image;
                    break;
                }
            }
        }

        if (!$match) {
            throw new Exception(__('iso.asrock_mount_confirm_error', ['suffix' => '']));
        }

        $this->asrockApi($session, '/api/settings/media/remote/start-media', 'POST', [
            'image_name' => $targetName,
            'image_type' => (int) ($match['image_type'] ?? 1),
            'image_redirection' => 1,
            'image_index' => (int) ($match['image_index'] ?? 0),
        ], true);

        $lastStatus = null;
        for ($attempt = 1; $attempt <= 18; $attempt++) {
            sleep($attempt <= 6 ? 2 : 4);
            $config = $this->asrockApi($session, '/api/settings/media/remote/configurations');
            foreach ($config as $entry) {
                if (!is_array($entry)) {
                    continue;
                }
                $entryName = trim((string) ($entry['image_name'] ?? ''));
                $entryNameLower = strtolower($entryName);
                $status = (int) ($entry['redirection_status'] ?? 0);
                if ($entryNameLower === $targetNameLower || basename($entryNameLower) === basename($targetNameLower)) {
                    $lastStatus = $status;
                    if (in_array($status, [1, 27, 100], true)) {
                        return __('iso.asrock_mounted');
                    }
                }
            }
        }

        $suffix = $lastStatus !== null ? ' Final status: ' . $lastStatus . '.' : '';
        throw new Exception(__('iso.asrock_mount_confirm_error', ['suffix' => $suffix]));
    }

    private function unmountIsoOnAsrock(array $server): string
    {
        $session = $this->getAsrockMediaSession($server);
        $config = $this->asrockApi($session, '/api/settings/media/remote/configurations');
        $current = is_array($config[0] ?? null) ? $config[0] : [];
        $this->asrockApi($session, '/api/settings/media/remote/stop-media', 'POST', [
            'image_name' => (string) ($current['image_name'] ?? ''),
            'image_type' => (int) ($current['media_type'] ?? 1),
            'image_index' => (int) ($current['media_index'] ?? 0),
        ], true);
        $config = $this->asrockApi($session, '/api/settings/media/remote/configurations');
        $configJson = json_encode($config);
        if (!is_string($configJson) || strpos($configJson, '"redirection_status":0') === false) {
            throw new Exception(__('iso.asrock_unmount_confirm_error'));
        }
        return __('iso.asrock_unmounted');
    }
}
