<?php

namespace App\Services;

use App\Http\Response;
use App\Database\Database;
use IPMI;
use RuntimeException;
use Throwable;

class KvmLaunchService
{
    private Auth $auth;
    private IPMI $ipmi;
    private Database $db;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->ipmi = new IPMI();
        $this->db = Database::getInstance();
    }

    public function handle(int $serverId): Response
    {
        try {
            if (!$this->auth->isLoggedIn()) {
                return $this->errorResponse('You must sign in before opening the console.', 401);
            }

            if ($serverId <= 0) {
                return $this->errorResponse('No server was selected.', 400);
            }

            $currentUser = $this->auth->getCurrentUser();
            $server = $this->ipmi->getServer($serverId);
            if (!$server) {
                return $this->errorResponse('Server not found.', 404);
            }

            if (!$this->auth->isAdmin()) {
                $access = $this->db->fetch(
                    'SELECT access_level FROM user_servers WHERE user_id = ? AND server_id = ?',
                    [$currentUser['id'], $serverId]
                );

                if (!$access) {
                    return $this->errorResponse('Access denied for this server.', 403);
                }

                if (($access['access_level'] ?? '') === 'readonly') {
                    return $this->errorResponse('Your access level does not allow opening the console.', 403);
                }
            }

            $ipmiTypeRaw = strtolower(trim((string) ($server['ipmi_type'] ?? 'generic')));
            $ipmiType = preg_replace('/[^a-z0-9_-]/', '', $ipmiTypeRaw);
            $ipmiIp = (string) $server['ip_address'];
            $ipmiUser = (string) $server['ipmi_username'];
            $ipmiPass = IPMI::decryptPassword($server['ipmi_password']);
            $tlsFingerprint = (string) ($server['tls_fingerprint'] ?? '');
            $kvmMode = $this->normalizeKvmMode((string) ($server['kvm_mode'] ?? 'html5'));

            assertBmcTlsFingerprint($ipmiIp, $tlsFingerprint);

            if (in_array($ipmiType, ['supermicro', 'smc', 'generic'], true)) {
                return $this->handleSupermicro($serverId, $ipmiIp, $ipmiUser, $ipmiPass, $kvmMode);
            }

            if ($ipmiType === 'asrock') {
                return $this->handleAsrock($serverId, $ipmiIp, $ipmiUser, $ipmiPass);
            }

            return $this->handleTyan($ipmiIp, $ipmiUser, $ipmiPass);
        } catch (Throwable $e) {
            error_log('KVM launch failed for server ' . $serverId . ': ' . $e->getMessage());
            return $this->errorResponse('Unable to launch the console right now.', 502);
        }
    }

    private function handleSupermicro(int $serverId, string $ipmiIp, string $ipmiUser, string $ipmiPass, string $kvmMode): Response
    {
        if ($kvmMode === 'vnc_classic') {
            return Response::redirect($this->buildNoVncUrl($this->startWebsockifyProxy($ipmiIp, 5900, $serverId)));
        }

        if ($kvmMode === 'java_classic') {
            unset($_SESSION['bmc_sessions'][$serverId]);
            $smLogin = $this->loginSupermicroWithRetries($ipmiIp, $ipmiUser, $ipmiPass);
            if (!isset($smLogin['sid'])) {
                return $this->errorResponse('Supermicro console login failed. Try again in a moment.', 502);
            }
            $_SESSION['bmc_sessions'][$serverId] = [
                'ip' => $ipmiIp,
                'session_id' => $smLogin['sid'],
                'cookie_name' => 'SID',
                'cookies' => $smLogin['cookies'] ?? ['SID' => $smLogin['sid']],
                'csrf_header_name' => '',
                'csrf_token' => '',
                'kvm_token' => '',
                'expires' => time() + 1800,
            ];
            session_write_close();
            return Response::redirect(routeUrl('/bmc/' . $serverId . '/cgi/url_redirect.cgi', [
                'url_name' => 'man_ikvm',
            ]));
        }

        $existingSession = $_SESSION['bmc_sessions'][$serverId] ?? null;
        $canReuseSession = is_array($existingSession)
            && (($existingSession['ip'] ?? '') === $ipmiIp)
            && (($existingSession['cookie_name'] ?? '') === 'SID')
            && !empty($existingSession['session_id'])
            && (int) ($existingSession['expires'] ?? 0) > (time() + 60);

        if (!$canReuseSession) {
            $smLogin = $this->loginSupermicroWithRetries($ipmiIp, $ipmiUser, $ipmiPass);
            if (!isset($smLogin['sid'])) {
                return $this->errorResponse('Supermicro console login failed. Try again in a moment.', 502);
            }
            $_SESSION['bmc_sessions'][$serverId] = [
                'ip' => $ipmiIp,
                'session_id' => $smLogin['sid'],
                'cookie_name' => 'SID',
                'cookies' => $smLogin['cookies'] ?? ['SID' => $smLogin['sid']],
                'csrf_header_name' => '',
                'csrf_token' => '',
                'kvm_token' => '',
                'expires' => time() + 1800,
            ];
        }

        session_write_close();
        return Response::redirect(routeUrl('/bmc/' . $serverId . '/cgi/url_redirect.cgi', [
            'url_name' => 'man_ikvm_html5_bootstrap',
        ]));
    }

    private function handleAsrock(int $serverId, string $ipmiIp, string $ipmiUser, string $ipmiPass): Response
    {
        $session = $this->getAsrockKvmSession($serverId, $ipmiIp, $ipmiUser, $ipmiPass);
        if (!$session) {
            return $this->errorResponse('Console authentication failed for this server.', 502);
        }

        $_SESSION['bmc_sessions'][$serverId] = [
            'ip' => $ipmiIp,
            'session_id' => $session['session_id'],
            'cookie_name' => 'QSESSIONID',
            'cookies' => $session['cookies'] ?? ['QSESSIONID' => $session['session_id']],
            'csrf_header_name' => 'X-CSRFTOKEN',
            'csrf_token' => $session['csrf'],
            'kvm_token' => $session['kvm_token'] ?? '',
            'storage' => $session['storage'] ?? [],
            'expires' => time() + 1800,
        ];
        session_write_close();
        return Response::redirect(routeUrl('/bmc/' . $serverId . '/viewer.html'));
    }

    private function handleTyan(string $ipmiIp, string $ipmiUser, string $ipmiPass): Response
    {
        $session = $this->getTyanKvmSession($ipmiIp, $ipmiUser, $ipmiPass);
        if (!$session) {
            return $this->errorResponse('Console authentication failed for this server.', 502);
        }

        $cookies = [];
        foreach ($session['cookies'] as $cookie) {
            $cookies[] = $cookie . '; path=/; SameSite=Lax';
        }
        $cookies[] = 'X-CSRFToken=' . $session['csrf'] . '; path=/; SameSite=Lax';

        return Response::make('', 302, [
            'Location' => 'https://' . $ipmiIp . '/viewer.html',
            'Set-Cookie' => $cookies,
        ]);
    }

    private function errorResponse(string $message, int $status): Response
    {
        $html = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>KVM Error</title><style>body{font-family:Arial,sans-serif;background:#f4f7fb;color:#12212f;margin:0;padding:32px}.panel{max-width:640px;margin:40px auto;background:#fff;border:1px solid #d8e1ea;border-radius:16px;padding:24px;box-shadow:0 10px 24px rgba(15,23,34,.08)}h1{margin:0 0 12px;font-size:1.4rem}p{margin:0 0 18px;color:#4b5c6d}a{display:inline-block;padding:10px 14px;border:1px solid #0e7490;border-radius:10px;color:#0e7490;text-decoration:none}</style></head><body><div class="panel"><h1>KVM launch error</h1><p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p><a href="' . htmlspecialchars(routeUrl('/servers'), ENT_QUOTES, 'UTF-8') . '">Back to servers</a></div></body></html>';
        return Response::make($html, $status);
    }

    private function normalizeKvmMode(string $kvmMode): string
    {
        return match (strtolower(trim($kvmMode))) {
            'novnc_legacy', 'vnc_classic' => 'vnc_classic',
            'jnlp_legacy', 'java_classic' => 'java_classic',
            default => 'html5',
        };
    }

    private function shellExecSafe(string $command): string
    {
        $result = shell_exec($command);
        return is_string($result) ? trim($result) : '';
    }

    private function isLocalPortOpen(int $port): bool
    {
        $errno = 0;
        $errstr = '';
        $socket = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
        if (!is_resource($socket)) {
            return false;
        }
        fclose($socket);
        return true;
    }

    private function isPidRunning(int $pid): bool
    {
        return $pid > 0 && file_exists('/proc/' . $pid);
    }

    private function getProcessCmdline(int $pid): string
    {
        if ($pid <= 0) {
            return '';
        }
        $path = '/proc/' . $pid . '/cmdline';
        if (!is_readable($path)) {
            return '';
        }
        $raw = @file_get_contents($path);
        if (!is_string($raw) || $raw === '') {
            return '';
        }
        return str_replace("\0", ' ', $raw);
    }

    private function pickFreePort(int $min = 6100, int $max = 6999): int
    {
        for ($i = 0; $i < 40; $i++) {
            $port = random_int($min, $max);
            if (!$this->isLocalPortOpen($port)) {
                return $port;
            }
        }
        throw new RuntimeException('No free proxy port available.');
    }

    private function startWebsockifyProxy(string $targetIp, int $targetPort, int $serverId): int
    {
        $pidKey = 'websockify_pid_' . $serverId;
        $portKey = 'websockify_port_' . $serverId;
        $existingPid = isset($_SESSION[$pidKey]) ? (int) $_SESSION[$pidKey] : 0;
        $existingPort = isset($_SESSION[$portKey]) ? (int) $_SESSION[$portKey] : 0;

        if ($existingPid > 0 && $existingPort > 0 && $this->isPidRunning($existingPid) && $this->isLocalPortOpen($existingPort)) {
            $existingCmd = $this->getProcessCmdline($existingPid);
            $expectedLocalBind = '127.0.0.1:' . $existingPort;
            if (strpos($existingCmd, $expectedLocalBind) !== false) {
                return $existingPort;
            }
            if (function_exists('posix_kill')) {
                @posix_kill($existingPid, 15);
                usleep(200000);
                if (file_exists('/proc/' . $existingPid)) {
                    @posix_kill($existingPid, 9);
                }
            } else {
                $this->shellExecSafe('kill ' . (int) $existingPid . ' 2>/dev/null');
            }
            unset($_SESSION[$pidKey], $_SESSION[$portKey]);
        }

        $websockifyBin = '/usr/local/bin/websockify';
        if (!is_executable($websockifyBin)) {
            throw new RuntimeException('websockify is not installed at /usr/local/bin/websockify');
        }

        $proxyPort = $this->pickFreePort();
        $command = 'nohup ' . escapeshellarg($websockifyBin)
            . ' --verbose --log-file /tmp/websockify_' . (int) $serverId . '.log '
            . '127.0.0.1:' . (string) $proxyPort . ' '
            . escapeshellarg($targetIp . ':' . $targetPort)
            . ' > /tmp/websockify_' . (int) $serverId . '.log 2>&1 & echo $!';
        $pid = (int) $this->shellExecSafe($command);

        if ($pid <= 0) {
            throw new RuntimeException('Failed to start websockify process.');
        }

        usleep(300000);
        if (!$this->isPidRunning($pid) || !$this->isLocalPortOpen($proxyPort)) {
            throw new RuntimeException('websockify started but proxy port did not open.');
        }

        $_SESSION[$pidKey] = $pid;
        $_SESSION[$portKey] = $proxyPort;
        return $proxyPort;
    }

    private function buildNoVncUrl(int $proxyPort): string
    {
        $host = preg_replace('/:\\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? ''));
        if ($host === '') {
            $host = $_SERVER['SERVER_NAME'] ?? 'localhost';
        }
        $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        $wsPath = 'ipmi-panel/vnc-ws/' . $proxyPort;
        return '/ipmi-panel/vendor/novnc/vnc.html?autoconnect=true&reconnect=true&resize=scale'
            . '&host=' . rawurlencode($host)
            . '&port=' . ($isHttps ? '443' : '80')
            . '&encrypt=' . ($isHttps ? '1' : '0')
            . '&path=' . rawurlencode($wsPath);
    }

    private function parseResponseCookies(string $header): array
    {
        $cookies = [];
        if (preg_match_all('/^Set-Cookie:\s*([^=;\s]+)=([^;]*)/mi', $header, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $row) {
                $name = trim($row[1]);
                $value = trim($row[2]);
                if ($name !== '' && $value !== '') {
                    $cookies[$name] = $value;
                }
            }
        }
        return $cookies;
    }

    private function loginSupermicroWithRetries(string $ip, string $user, string $pass): array
    {
        $smLogin = ['error' => 'Unknown Supermicro login error'];
        for ($i = 0; $i < 3; $i++) {
            $smLogin = $this->supermicroWebLogin($ip, $user, $pass);
            if (isset($smLogin['sid'])) {
                break;
            }
            usleep((int) (400000 * ($i + 1)));
        }
        return $smLogin;
    }

    private function supermicroWebLogin(string $ip, string $user, string $pass): array
    {
        $response = false;
        $httpCode = 0;
        $header = '';
        $error = 'Unknown Supermicro login error';

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://' . $ip . '/cgi/login.cgi',
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query(['name' => $user, 'pwd' => $pass]),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_ENCODING => '',
                CURLOPT_CONNECTTIMEOUT => 12,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded', 'X-Requested-With: XMLHttpRequest'],
            ]);
            $response = curl_exec($ch);
            if ($response !== false) {
                $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $header = substr($response, 0, $headerSize);
                curl_close($ch);
                break;
            }
            $error = curl_error($ch);
            curl_close($ch);
            usleep((int) (300000 * $attempt));
        }

        if ($response === false) {
            return ['error' => 'Login request failed: ' . $error];
        }
        if ($httpCode !== 200) {
            return ['error' => 'Login HTTP ' . $httpCode];
        }
        if (!preg_match('/Set-Cookie:\s*SID=([^;]+)/i', $header, $m)) {
            return ['error' => 'No SID cookie returned by Supermicro login'];
        }

        $cookies = $this->parseResponseCookies($header);
        $cookieHeader = [];
        foreach ($cookies as $k => $v) {
            $cookieHeader[] = $k . '=' . $v;
        }
        $ch2 = curl_init();
        curl_setopt_array($ch2, [
            CURLOPT_URL => 'https://' . $ip . '/cgi/url_redirect.cgi?url_name=mainmenu',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_ENCODING => '',
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => ['Cookie: ' . implode('; ', $cookieHeader), 'X-Requested-With: XMLHttpRequest'],
        ]);
        $resp2 = curl_exec($ch2);
        if ($resp2 !== false) {
            $h2 = substr($resp2, 0, (int) curl_getinfo($ch2, CURLINFO_HEADER_SIZE));
            $cookies = array_merge($cookies, $this->parseResponseCookies($h2));
        }
        curl_close($ch2);

        return ['sid' => $m[1], 'cookies' => $cookies];
    }

    private function getTyanKvmSession(string $ip, string $user, string $pass): ?array
    {
        $payload = json_encode(['username' => $user, 'password' => $pass]);
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://' . $ip . '/api/session',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_ENCODING => '',
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'X-Requested-With: XMLHttpRequest'],
            CURLOPT_TIMEOUT => 20,
        ]);
        $response = curl_exec($ch);
        if ($response === false) {
            curl_close($ch);
            return null;
        }
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        curl_close($ch);
        $data = json_decode($body, true);
        if (!isset($data['CSRFToken'])) {
            return null;
        }
        preg_match_all('/Set-Cookie:\s*([^;]+)/i', $header, $matches);
        return ['cookies' => $matches[1], 'csrf' => $data['CSRFToken']];
    }

    private function getAsrockKvmToken(string $ip, array $cookies, string $csrf): array
    {
        $cookieHeader = [];
        foreach ($cookies as $k => $v) {
            if ($k === '' || $v === '') {
                continue;
            }
            $cookieHeader[] = $k . '=' . $v;
        }
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://' . $ip . '/api/kvm/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_ENCODING => '',
            CURLOPT_HTTPHEADER => [
                'Cookie: ' . implode('; ', $cookieHeader),
                'X-CSRFTOKEN: ' . $csrf,
                'X-Requested-With: XMLHttpRequest',
                'Accept: application/json, text/javascript, */*; q=0.01',
                'User-Agent: Mozilla/5.0',
            ],
            CURLOPT_TIMEOUT => 20,
        ]);
        $response = curl_exec($ch);
        if ($response === false) {
            curl_close($ch);
            return [];
        }
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 200) {
            return [];
        }
        $data = json_decode($response, true);
        if (!is_array($data)) {
            return [];
        }
        return [
            'token' => (string) ($data['token'] ?? ''),
            'client_ip' => (string) ($data['client_ip'] ?? ''),
            'session' => (string) ($data['session'] ?? ''),
        ];
    }

    private function getReusableAsrockKvmSession(int $serverId, string $ip): ?array
    {
        $candidate = $_SESSION['bmc_sessions'][$serverId] ?? null;
        if (!is_array($candidate)) {
            return null;
        }
        if (($candidate['cookie_name'] ?? '') !== 'QSESSIONID' || ($candidate['ip'] ?? '') !== $ip) {
            return null;
        }
        if ((int) ($candidate['expires'] ?? 0) <= time()) {
            unset($_SESSION['bmc_sessions'][$serverId]);
            return null;
        }
        $cookies = $candidate['cookies'] ?? ['QSESSIONID' => ($candidate['session_id'] ?? '')];
        $csrf = (string) ($candidate['csrf_token'] ?? '');
        if (!is_array($cookies) || empty($cookies['QSESSIONID']) || $csrf === '') {
            return null;
        }
        return [
            'session_id' => (string) $cookies['QSESSIONID'],
            'csrf' => $csrf,
            'cookies' => $cookies,
            'kvm_token' => (string) ($candidate['kvm_token'] ?? ''),
            'viewer_session' => (string) (($candidate['storage']['viewer_session'] ?? '')),
            'storage' => is_array($candidate['storage'] ?? null) ? $candidate['storage'] : [],
        ];
    }

    private function getAsrockKvmSession(int $serverId, string $ip, string $user, string $pass): ?array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://' . $ip . '/api/session',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(['username' => $user, 'password' => $pass, 'certlogin' => '0']),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_ENCODING => '',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Accept: application/json, text/javascript, */*; q=0.01',
                'X-Requested-With: XMLHttpRequest',
                'User-Agent: Mozilla/5.0',
            ],
            CURLOPT_TIMEOUT => 20,
        ]);
        $response = curl_exec($ch);
        if ($response === false) {
            curl_close($ch);
            return null;
        }
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        curl_close($ch);
        if ($httpCode !== 200) {
            $errorData = json_decode($body, true);
            if (is_array($errorData) && (int) ($errorData['code'] ?? 0) === 15000) {
                return $this->getReusableAsrockKvmSession($serverId, $ip);
            }
            return null;
        }
        $data = json_decode($body, true);
        if (!is_array($data) || !isset($data['CSRFToken'])) {
            return null;
        }
        $cookies = $this->parseResponseCookies($header);
        $sid = (string) ($cookies['QSESSIONID'] ?? '');
        if ($sid === '') {
            return null;
        }
        $features = $data['features'] ?? '';
        if (is_array($features)) {
            $features = implode(',', array_map('strval', $features));
        } else {
            $features = (string) $features;
        }
        $kvm = $this->getAsrockKvmToken($ip, $cookies, (string) $data['CSRFToken']);
        $storage = [
            'username' => (string) ($data['username'] ?? $user),
            'garc' => (string) $data['CSRFToken'],
            'CSRFToken' => (string) $data['CSRFToken'],
            'privilege' => (string) ($data['privilege'] ?? '4'),
            'privilege_id' => (string) ($data['privilege'] ?? '4'),
            'extended_privilege' => (string) ($data['extendedpriv'] ?? ''),
            'session_id' => (string) ($data['racsession_id'] ?? $sid),
            'server_addr' => (string) ($data['server_addr'] ?? $ip),
            'since' => (string) ($data['since'] ?? ''),
            'id' => (string) ($data['id'] ?? ''),
            'kvm_access' => (string) ($data['kvm_access'] ?? '1'),
            'vmedia_access' => (string) ($data['vmedia_access'] ?? '1'),
            'features' => $features,
            'kvm_token' => (string) ($kvm['token'] ?? ''),
            'client_ip' => (string) ($kvm['client_ip'] ?? ''),
            'viewer_session' => (string) ($kvm['session'] ?? ''),
        ];
        return [
            'session_id' => $sid,
            'csrf' => (string) $data['CSRFToken'],
            'cookies' => $cookies,
            'kvm_token' => (string) ($kvm['token'] ?? ''),
            'viewer_session' => (string) ($kvm['session'] ?? ''),
            'storage' => $storage,
        ];
    }
}
