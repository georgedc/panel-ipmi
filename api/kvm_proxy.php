<?php
/**
 * KVM Proxy WebSocket
 * 
 * Acts as a WebSocket proxy between the client browser and the IPMI server.
 * Enhanced version with security and compatibility improvements.
 */

// Load configuration and dependencies
require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/Auth.php';
require_once '../includes/IPMI.php';
require_once '../includes/Logger.php';

// Initialize session
session_name(SESSION_NAME);
session_start();

// JSON response helper
function jsonResponse($data, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// JSON error helper
function jsonError($message, $code = 400) {
    error_log("KVM Proxy Error: $message");
    jsonResponse(['status' => 'error', 'message' => $message], $code);
}

// Check authentication
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    jsonError('Unauthenticated', 401);
}

$currentUser = $auth->getCurrentUser();
if (!$currentUser) {
    jsonError('Invalid user', 401);
}

// Validate parameters
if (!isset($_GET['token']) || !isset($_GET['server']) || !is_numeric($_GET['server'])) {
    jsonError('Token or server not provided', 400);
}

$token = $_GET['token'];
$serverId = (int)$_GET['server'];

// Verify token is valid and not expired
if (!isset($_SESSION['kvm_tokens'][$token])) {
    jsonError('Token not found', 403);
}

$tokenData = $_SESSION['kvm_tokens'][$token];

if ($tokenData['server_id'] != $serverId || $tokenData['expires'] < time()) {
    // Clean up expired token
    unset($_SESSION['kvm_tokens'][$token]);
    jsonError('Invalid or expired token', 403);
}

if ($tokenData['user_id'] != $currentUser['id']) {
    jsonError('Token does not belong to current user', 403);
}

// Get server information
$db = Database::getInstance();
$server = $db->fetch("SELECT * FROM servers WHERE id = ?", [$serverId]);

if (!$server) {
    jsonError('Server not found', 404);
}

// Check user permissions for this server
if (!$auth->isAdmin()) {
    $userAccess = $db->fetch(
        "SELECT access_level FROM user_servers WHERE user_id = ? AND server_id = ?",
        [$currentUser['id'], $serverId]
    );
    
    if (!$userAccess) {
        jsonError('Access denied to this server', 403);
    }
    
    if ($userAccess['access_level'] === 'readonly') {
        jsonError('Acceso de solo lectura insuficiente para KVM', 403);
    }
}

// Log connection start
$logger = new Logger();
$logger->logActivity($currentUser['id'], $serverId, 'kvm_proxy_start', 'KVM proxy token validated');

// Decrypt IPMI password
function decryptPassword($encryptedPassword) {
    if (empty($encryptedPassword)) return '';
    try {
        $encryption_key = base64_decode(ENCRYPTION_KEY);
        $decoded = base64_decode($encryptedPassword);
        if (strpos($decoded, '::') === false) return '';
        list($encrypted_data, $iv) = explode('::', $decoded, 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
    } catch (Exception $e) { return ''; }
}

function execCommand(array $command, int $timeoutSeconds = 8): array {
    $timeoutPrefix = sprintf('timeout %d ', max(1, $timeoutSeconds));
    $commandString = $timeoutPrefix . implode(' ', $command) . ' 2>&1';
    $output = [];
    $returnCode = 0;
    exec($commandString, $output, $returnCode);
    return [$output, $returnCode];
}

function isTcpPortOpen(string $host, int $port, int $timeoutSeconds = 3): bool {
    $errno = 0;
    $errstr = '';
    $socket = @fsockopen($host, $port, $errno, $errstr, $timeoutSeconds);
    if (!is_resource($socket)) {
        return false;
    }
    fclose($socket);
    return true;
}

// Get connection parameters
$ipmiHost = $server['ip_address'];
$ipmiPort = $server['ipmi_port'] ?? 623;
$ipmiUser = $server['ipmi_username'];
$ipmiPass = decryptPassword($server['ipmi_password']);

// Determine server type and KVM configuration
$serverType = $server['ipmi_type'] ?? 'generic';
$vncPort = 5900; // Base VNC port
$kvmMethod = 'vnc'; // Default method

// Server type specific configuration
switch ($serverType) {
    case 'supermicro':
        $vncPort = 5900;
        $kvmMethod = 'vnc';
        break;
        
    case 'dell':
    case 'idrac':
        $vncPort = 5900;
        $kvmMethod = 'idrac';
        break;
        
    case 'hp':
    case 'ilo':
        $vncPort = 5900;
        $kvmMethod = 'ilo';
        break;
        
    case 'asrock':
        $vncPort = 5900;
        $kvmMethod = 'asrock';
        break;
        
    default:
        $vncPort = 5900;
        $kvmMethod = 'generic';
        break;
}

try {
    $output = ["Simulated IPMI connection successful"];
    $returnCode = 0;
    $safetyMode = 'simulated';
    $extraConnectionInfo = [];

    // Supermicro: validate real connectivity in safe read-only mode.
    if ($serverType === 'supermicro') {
        $ipmitoolBinary = is_executable('/usr/bin/ipmitool') ? '/usr/bin/ipmitool' : 'ipmitool';
        [$output, $returnCode] = execCommand([
            escapeshellcmd($ipmitoolBinary),
            '-I', 'lanplus',
            '-H', escapeshellarg($ipmiHost),
            '-p', (string)(int)$ipmiPort,
            '-U', escapeshellarg($ipmiUser),
            '-P', escapeshellarg($ipmiPass),
            'chassis', 'power', 'status'
        ], 10);

        if ($returnCode !== 0) {
            $safeReason = trim($output[0] ?? 'Sin detalle');
            throw new Exception("Supermicro no respondió al chequeo seguro IPMI ({$safeReason})");
        }

        $vncPortOpen = isTcpPortOpen($ipmiHost, $vncPort, 3);
        $safetyMode = 'supermicro_read_only_probe';
        $extraConnectionInfo = [
            'ipmi_check' => trim(implode(' ', $output)),
            'vnc_port_open' => $vncPortOpen
        ];
    }
    
    // Log successful KVM session
    $logger->logActivity($currentUser['id'], $serverId, 'kvm_session_start', "Method: {$kvmMethod}");
    
    // Build WebSocket URL
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'wss' : 'ws';
    $host = $_SERVER['HTTP_HOST'];
    

    $vncUrl = "/ipmi-panel/vendor/novnc/vnc.html?host={$ipmiHost}&port={$vncPort}&autoconnect=true";
    
    // Success response
    jsonResponse([
        'status' => 'success',
        'message' => 'KVM session started successfully',
        'server_id' => $serverId,
        'server_name' => $server['name'],
        'method' => $kvmMethod,
        'vnc_url' => $vncUrl,
        'connection_info' => [
            'host' => $ipmiHost,
            'port' => $vncPort,
            'type' => $serverType,
            'safety_mode' => $safetyMode
        ],
        'expires' => $tokenData['expires'],
        'probe' => $extraConnectionInfo
    ]);
    
} catch (Exception $e) {
    // Log error
    $logger->logActivity($currentUser['id'], $serverId, 'kvm_session_error', $e->getMessage());
    
    jsonError($e->getMessage(), 500);
}


?>
