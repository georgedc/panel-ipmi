<?php


require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . APP_URL);
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

function jsonError($message, $code = 400) {
    jsonResponse(['error' => $message], $code);
}

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
    if ($remoteAddr === '') {
        jsonError('Origen no permitido', 403);
    }

    if (!in_array($remoteAddr, getAllowedApiSourceIps(), true)) {
        jsonError('Origen no permitido', 403);
    }
}

function decryptPassword($encryptedData): string {
    if (empty($encryptedData)) {
        return '';
    }

    try {
        $encryptionKey = base64_decode(ENCRYPTION_KEY, true);
        $decoded = base64_decode($encryptedData, true);

        if ($encryptionKey === false || $decoded === false || strpos($decoded, '::') === false) {
            return '';
        }

        [$encryptedValue, $iv] = explode('::', $decoded, 2);
        return (string) openssl_decrypt($encryptedValue, 'aes-256-cbc', $encryptionKey, 0, $iv);
    } catch (Throwable $e) {
        return '';
    }
}

function logApiEvent($userId, $serverId, $action): void {
    $db = Database::getInstance();
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    try {
        $db->insert('server_logs', [
            'user_id' => $userId,
            'server_id' => $serverId,
            'action' => 'API: ' . $action,
            'ip_address' => $ip,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    } catch (Throwable $e) {
        error_log('Error logging API event: ' . $e->getMessage());
    }
}

function executeIPMICommand(array $server, string $command): array {
    $ipmiPassword = decryptPassword($server['ipmi_password'] ?? '');
    $ipmiCommand = sprintf(
        'timeout 10 sudo /usr/bin/ipmitool -I lanplus -H %s  -p %d -U %s -P %s %s 2>&1',
        escapeshellarg(trim((string) ($server['ip_address'] ?? ''))),
        (int) ($server['ipmi_port'] ?? 623),
        escapeshellarg((string) ($server['ipmi_username'] ?? '')),
        escapeshellarg($ipmiPassword),
        $command
    );

    exec($ipmiCommand, $output, $returnCode);

    return [
        'success' => $returnCode === 0,
        'output' => implode("\n", $output),
        'return_code' => $returnCode,
    ];
}

$db = Database::getInstance();
$apiSettings = $db->fetch("SELECT value FROM settings WHERE name = 'api_enabled'");
if (!$apiSettings || $apiSettings['value'] !== '1') {
    jsonError('API deshabilitado', 503);
}

$token = getBearerToken();
if ($token === '') {
    jsonError('Token de autorizacion requerido', 401);
}

$apiUser = ['id' => 0, 'role' => 'admin'];
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathSegments = explode('/', trim((string) $uri, '/'));

try {
    switch ($method) {
        case 'GET':
            if (isset($pathSegments[1]) && $pathSegments[1] === 'servers') {
                jsonError('Listado global deshabilitado por seguridad', 403);
            }

            if (isset($pathSegments[1], $pathSegments[2]) && $pathSegments[1] === 'server') {
                requireAllowedApiSource();

                $serverId = (int) $pathSegments[2];
                $server = $db->fetch(
                    'SELECT id, name, ip_address, status, location, ipmi_port, api_token FROM servers WHERE id = ?',
                    [$serverId]
                );

                if (!$server) {
                    jsonError('Servidor no encontrado', 404);
                }

                if (empty($server['api_token']) || !hash_equals((string) $server['api_token'], $token)) {
                    jsonError('Token invalido para este servidor', 403);
                }

                unset($server['api_token']);
                jsonResponse(['server' => $server]);
            }

            jsonError('Endpoint no encontrado', 404);
            break;

        case 'POST':
            if (!isset($pathSegments[1], $pathSegments[2]) || $pathSegments[1] !== 'server') {
                jsonError('Endpoint no encontrado', 404);
            }

            requireAllowedApiSource();

            $serverId = (int) $pathSegments[2];
            $input = json_decode(file_get_contents('php://input'), true);
            $action = trim((string) ($input['action'] ?? ''));

            if (!in_array($action, ['on', 'off', 'reset', 'cycle', 'status'], true)) {
                jsonError('Accion no valida. Usa: on, off, reset, cycle, status');
            }

            $server = $db->fetch('SELECT id, api_token, ip_address, ipmi_port, ipmi_username, ipmi_password, ipmi_type FROM servers WHERE id = ?', [$serverId]);
            if (!$server) {
                jsonError('Servidor no encontrado', 404);
            }

            if (empty($server['api_token']) || !hash_equals((string) $server['api_token'], $token)) {
                jsonError('Token invalido para este servidor', 403);
            }

            $command = $action === 'status' ? 'chassis power status' : 'chassis power ' . $action;
            $result = executeIPMICommand($server, $command);

            logApiEvent($apiUser['id'], $serverId, 'power_' . $action);

            if ($result['success'] && $action !== 'status') {
                $newStatus = $action === 'on' ? 'online' : 'offline';
                $db->update('servers', ['status' => $newStatus, 'last_checked' => date('Y-m-d H:i:s')], 'id = ?', [$serverId]);
            }

            jsonResponse([
                'success' => $result['success'],
                'action' => $action,
                'server_id' => $serverId,
                'output' => $result['output'],
            ]);
            break;

        default:
            jsonError('Metodo no permitido', 405);
    }
} catch (Throwable $e) {
    error_log('API Error: ' . $e->getMessage());
    jsonError('Error interno del servidor', 500);
}
