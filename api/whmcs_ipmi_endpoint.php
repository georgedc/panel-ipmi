<?php
$allowedOrigin = getenv('WHMCS_ORIGIN') ?: getEnvValue('WHMCS_ORIGIN', '');
header('Access-Control-Allow-Origin: ' . $allowedOrigin);
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['status' => 'ok']);
    exit;
}

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/IPMI.php';
require_once '../includes/Logger.php';

use App\Services\WhmcsIsoBridgeService;

function getAuthorizationHeader(): string {
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        return trim((string) $_SERVER['HTTP_AUTHORIZATION']);
    }

    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        foreach ($headers as $name => $value) {
            if (strcasecmp($name, 'Authorization') === 0) {
                return trim((string) $value);
            }
        }
    }

    return '';
}

function getBearerToken(): string {
    $authHeader = getAuthorizationHeader();
    if (preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
        return trim($matches[1]);
    }

    return '';
}

function getAllowedApiSourceIps(): array {
    $allowed = ['127.0.0.1', '::1'];

    foreach (['SERVER_ADDR', 'LOCAL_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $allowed[] = trim((string) $_SERVER[$key]);
        }
    }

    $appHost = parse_url(APP_URL, PHP_URL_HOST);
    if ($appHost) {
        $resolved = gethostbyname($appHost);
        if ($resolved && $resolved !== $appHost) {
            $allowed[] = $resolved;
        }
    }

    $extra = getenv('INTERNAL_API_ALLOWED_IPS') ?: getEnvValue('INTERNAL_API_ALLOWED_IPS', '');
    if ($extra) {
        foreach (explode(',', $extra) as $ip) {
            $ip = trim($ip);
            if ($ip !== '') {
                $allowed[] = $ip;
            }
        }
    }

    return array_values(array_unique($allowed));
}

function requireAllowedApiSource(): void {
    $remoteAddr = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    if ($remoteAddr === '' || !in_array($remoteAddr, getAllowedApiSourceIps(), true)) {
        http_response_code(403);
        echo json_encode(['error' => 'Origin not allowed']);
        exit;
    }
}

function decryptPassword($encryptedPassword): string {
    if (empty($encryptedPassword)) {
        return '';
    }

    try {
        $encryptionKey = base64_decode(ENCRYPTION_KEY, true);
        $decoded = base64_decode($encryptedPassword, true);
        if ($encryptionKey === false || $decoded === false || strpos($decoded, '::') === false) {
            return '';
        }

        [$encryptedData, $iv] = explode('::', $decoded, 2);
        return (string) openssl_decrypt($encryptedData, 'aes-256-cbc', $encryptionKey, 0, $iv);
    } catch (Throwable $e) {
        return '';
    }
}

$token = getBearerToken();
if ($token === '') {
    http_response_code(401);
    echo json_encode(['error' => 'Authorization token required']);
    exit;
}

$db = Database::getInstance();

$apiEnabled = $db->fetch("SELECT value FROM settings WHERE name = 'api_enabled'");
if (!$apiEnabled || $apiEnabled['value'] !== '1') {
    http_response_code(403);
    echo json_encode(['error' => 'API disabled']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

requireAllowedApiSource();

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['action'], $input['server_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Action and server_id required']);
    exit;
}

$action = trim((string) $input['action']);
$serverId = (int) $input['server_id'];
$server = $db->fetch('SELECT * FROM servers WHERE id = ?', [$serverId]);

if (!$server) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Server not found']);
    exit;
}

if (empty($server['api_token']) || !hash_equals((string) $server['api_token'], $token)) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid token for this server']);
    exit;
}

if ($action === 'console') {
    $ssoSecret = getenv('WHMCS_SSO_SECRET') ?: getEnvValue('WHMCS_SSO_SECRET', '');
    if ($ssoSecret === '') {
        http_response_code(503);
        echo json_encode(['status' => 'error', 'message' => 'SSO not configured on this panel']);
        exit;
    }

    $email = mb_strtolower(trim((string) ($input['email'] ?? '')));
    if ($email === '') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'email is required for console action']);
        exit;
    }

    $panelUser = $db->fetch('SELECT id, email, role FROM users WHERE LOWER(email) = ?', [$email]);
    if (!$panelUser) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Access denied']);
        exit;
    }

    if (($panelUser['role'] ?? '') === 'admin') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Admin SSO is not allowed']);
        exit;
    }

    $userAccess = $db->fetch(
        'SELECT access_level FROM user_servers WHERE user_id = ? AND server_id = ?',
        [(int) $panelUser['id'], $serverId]
    );
    if (!$userAccess) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'User does not have access to this server']);
        exit;
    }

    $expires = time() + 300;
    $nonce   = bin2hex(random_bytes(32));
    $payload = implode('|', [$email, (string) $serverId, (string) $expires, $nonce]);
    $sig     = hash_hmac('sha256', $payload, $ssoSecret);

    $consoleUrl = rtrim(APP_URL, '/') . '/runtime/sso-kvm?' . http_build_query([
        'email'     => $email,
        'server_id' => $serverId,
        'expires'   => $expires,
        'nonce'     => $nonce,
        'sig'       => $sig,
    ]);

    $logger = new Logger();
    $logger->logActivity(null, $serverId, 'whmcs_console', 'SSO KVM link generated for ' . $email);

    echo json_encode(['status' => 'success', 'data' => ['console_url' => $consoleUrl]]);
    exit;
}

$logger = new Logger();

if (in_array($action, ['list_isos', 'mount_iso', 'unmount_iso', 'refresh_iso'], true)) {
    try {
        $bridge = new WhmcsIsoBridgeService();

        switch ($action) {
            case 'list_isos':
                $payload = $bridge->listIsos($serverId);
                break;
            case 'mount_iso':
                $isoId = (int) ($input['iso_id'] ?? 0);
                if ($isoId <= 0) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'iso_id required']);
                    exit;
                }
                $payload = $bridge->mountIso($serverId, $isoId);
                break;
            case 'unmount_iso':
                $payload = $bridge->unmountIso($serverId);
                break;
            case 'refresh_iso':
                $payload = $bridge->refreshIso($serverId);
                break;
            default:
                $payload = [];
                break;
        }

        $logger->logActivity(null, $serverId, 'whmcs_' . $action, 'ISO action executed via WHMCS');
        echo json_encode([
            'status' => 'success',
            'action' => $action,
            'server_id' => $serverId,
            'server_name' => $server['name'],
            'data' => $payload,
        ]);
        exit;
    } catch (Throwable $e) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'action' => $action,
            'server_id' => $serverId,
            'message' => $e->getMessage(),
        ]);
        exit;
    }
}

// ── Read-only data actions (no IPMI call needed) ────────────────────────────

if ($action === 'server_info') {
    echo json_encode([
        'status' => 'success',
        'action' => 'server_info',
        'data'   => [
            'id'           => (int) $server['id'],
            'name'         => $server['name'] ?? '',
            'client_label' => $server['client_label'] ?? '',
            'location'     => $server['location'] ?? '',
            'status'       => $server['status'] ?? '',
            'ipmi_type'    => $server['ipmi_type'] ?? '',
        ],
    ]);
    exit;
}

if ($action === 'hardware') {
    echo json_encode([
        'status' => 'success',
        'action' => 'hardware',
        'data'   => [
            'serial_number' => $server['serial_number'] ?? '',
            'cpu_info'      => $server['cpu_info'] ?? '',
            'ram_gb'        => !empty($server['ram_gb']) ? (int) $server['ram_gb'] : null,
            'disk_info'     => $server['disk_info'] ?? '',
            'switch_port'   => $server['switch_port'] ?? '',
            'notes'         => $server['notes'] ?? '',
        ],
    ]);
    exit;
}

if ($action === 'ip_list') {
    $ips = $db->fetchAll(
        'SELECT ip_address, netmask, gateway, rdns, description FROM server_ips WHERE server_id = ? ORDER BY id ASC',
        [$serverId]
    );
    echo json_encode([
        'status' => 'success',
        'action' => 'ip_list',
        'data'   => $ips,
    ]);
    exit;
}

// ── IPMI actions ──────────────────────────────────────────────────────────────

$ipmiPassword = decryptPassword($server['ipmi_password'] ?? '');
$ipmiInterface = in_array($server['ipmi_type'] ?? '', ['generic', 'tyan', 'asrock'], true) ? 'lan' : 'lanplus';

switch ($action) {
    case 'status':
        $commandSuffix = 'chassis power status';
        break;
    case 'poweron':
        $commandSuffix = 'chassis power on';
        break;
    case 'poweroff':
        $commandSuffix = 'chassis power off';
        break;
    case 'reset':
        $commandSuffix = 'chassis power reset';
        break;
    case 'powercycle':
        $commandSuffix = 'chassis power cycle';
        break;
    case 'bmc_reset':
        $resetType = (string) ($input['reset_type'] ?? 'warm');
        $commandSuffix = $resetType === 'cold' ? 'mc reset cold' : 'mc reset warm';
        break;
    case 'boot_device':
        $device = (string) ($input['device'] ?? '');
        $persistent = !empty($input['persistent']);
        $allowedDevices = ['pxe' => 'pxe', 'disk' => 'disk', 'cdrom' => 'cdrom', 'bios' => 'bios'];
        if (!isset($allowedDevices[$device])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid boot device. Use: pxe, disk, cdrom, bios']);
            exit;
        }
        $commandSuffix = 'chassis bootdev ' . $device . ($persistent ? ' options=persistent' : '');
        break;
    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        exit;
}

$command = sprintf(
    'timeout 10 sudo /usr/bin/ipmitool -I %s -H %s -p %d -U %s -P %s %s',
    $ipmiInterface,
    escapeshellarg(trim((string) ($server['ip_address'] ?? ''))),
    (int) ($server['ipmi_port'] ?? 623),
    escapeshellarg((string) ($server['ipmi_username'] ?? '')),
    escapeshellarg($ipmiPassword),
    $commandSuffix
);

exec($command . ' 2>&1', $output, $returnCode);

$logger->logActivity(null, $serverId, 'whmcs_' . $action, 'Command executed via WHMCS');

echo json_encode([
    'status' => 'success',
    'action' => $action,
    'server_name' => $server['name'],
    'output' => implode("\n", $output),
    'return_code' => $returnCode,
]);
