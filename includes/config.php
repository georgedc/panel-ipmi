<?php
$isHttps = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
);

if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://use.fontawesome.com; font-src 'self' https://fonts.gstatic.com https://use.fontawesome.com https://cdnjs.cloudflare.com; img-src 'self' data: blob:; connect-src 'self' https://cdnjs.cloudflare.com; frame-ancestors 'self'; object-src 'none';");
    if ($isHttps) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_secure', $isHttps ? '1' : '0');

if (session_status() === PHP_SESSION_NONE) {
    session_name('ipmi_session');
    session_start();
}

function normalizeTlsFingerprint(?string $fingerprint): string {
    $value = strtoupper((string) $fingerprint);
    return preg_replace('/[^A-F0-9]/', '', $value) ?? '';
}

function getPeerTlsFingerprint(string $host, int $port = 443, int $timeout = 12): string {
    $attempts = [max(10, $timeout), max(16, $timeout + 6), max(22, $timeout + 10)];
    $lastError = 'TLS connection failed for ' . $host . ':' . $port;

    foreach ($attempts as $attemptTimeout) {
        $context = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => true,
                'verify_peer' => false,
                'verify_peer_name' => false,
                'SNI_enabled' => true,
                'peer_name' => $host,
            ],
        ]);

        $client = @stream_socket_client(
            'ssl://' . $host . ':' . $port,
            $errno,
            $errstr,
            $attemptTimeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!is_resource($client)) {
            $lastError = 'TLS connection failed for ' . $host . ':' . $port . ($errstr ? ' (' . $errstr . ')' : '');
            continue;
        }

        $params = stream_context_get_params($client);
        fclose($client);

        $cert = $params['options']['ssl']['peer_certificate'] ?? null;
        if (!$cert) {
            $lastError = 'No TLS certificate received from ' . $host . ':' . $port;
            continue;
        }

        $fingerprint = openssl_x509_fingerprint($cert, 'sha256');
        if (!is_string($fingerprint) || $fingerprint === '') {
            $lastError = 'Unable to calculate TLS fingerprint for ' . $host . ':' . $port;
            continue;
        }

        return normalizeTlsFingerprint($fingerprint);
    }

    $opensslCommand = sprintf(
        "timeout %d bash -lc 'echo | openssl s_client -connect %s:%d -servername %s 2>/dev/null | openssl x509 -noout -fingerprint -sha256'",
        max(18, $timeout + 6),
        escapeshellarg($host),
        $port,
        escapeshellarg($host)
    );
    $opensslOutput = [];
    $opensslReturnCode = 1;
    exec($opensslCommand, $opensslOutput, $opensslReturnCode);
    if ($opensslReturnCode === 0) {
        $line = trim(implode("\n", $opensslOutput));
        if (preg_match('/Fingerprint=([A-Fa-f0-9:]+)/', $line, $matches)) {
            $fingerprint = normalizeTlsFingerprint($matches[1]);
            if ($fingerprint !== '') {
                return $fingerprint;
            }
        }
    }

    throw new RuntimeException($lastError);
}

function assertBmcTlsFingerprint(string $host, ?string $expectedFingerprint): void {
    $expected = normalizeTlsFingerprint($expectedFingerprint);
    if ($expected === '') {
        return;
    }

    $cacheKey = 'bmc_tls_fp_' . md5($host . '|' . $expected);
    $cached = $_SESSION[$cacheKey] ?? null;
    if (is_array($cached) && (int) ($cached['expires'] ?? 0) > time() && ($cached['fingerprint'] ?? '') === $expected) {
        return;
    }

    $actual = getPeerTlsFingerprint($host, 443, 12);
    if (!hash_equals($expected, $actual)) {
        throw new RuntimeException('TLS fingerprint mismatch for BMC ' . $host);
    }

    $_SESSION[$cacheKey] = [
        'fingerprint' => $actual,
        'expires' => time() + 300,
    ];
}

function getEnvValue($key, $default = null) {
    $envFile = __DIR__ . "/../.env";
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            $parts = explode('=', $line, 2);
            if (count($parts) === 2 && trim($parts[0]) === $key) return trim($parts[1]);
        }
    }
    return $default;
}

define('DB_HOST', getEnvValue('DB_HOST', 'localhost'));
define('DB_NAME', getEnvValue('DB_NAME', 'ipmi_panel'));
define('DB_USER', getEnvValue('DB_USER', 'usuario_db')); 
define('DB_PASS', getEnvValue('DB_PASS', ''));

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/I18n.php';
if (isset($_GET['lang'])) {
    setAppLocale((string) $_GET['lang']);
}
appLocale();

$_encKey = getenv('IPMI_ENCRYPTION_KEY') ?: getEnvValue('ENCRYPTION_KEY', '');
if (empty($_encKey)) {
    error_log('CRITICAL: ENCRYPTION_KEY is not configured.');
    die('Server configuration error.');
}
define('ENCRYPTION_KEY', $_encKey);
unset($_encKey);
define('HASH_COST', 12);
define('SESSION_LIFETIME', 3600);

define('APP_NAME', 'IPMI Control Panel');
define('APP_URL', getEnvValue('APP_URL', 'http://localhost'));
define('APP_VERSION', getEnvValue('APP_VERSION', '1.0.0'));
define('APP_THEME', getEnvValue('APP_THEME', 'default'));

/**
 * Returns the active theme, reading from the DB settings table first.
 * Falls back to the APP_THEME constant (env or default).
 */
function activeTheme(): string
{
    static $resolved = null;
    if ($resolved !== null) {
        return $resolved;
    }
    try {
        $db = \App\Database\Database::getInstance();
        $row = $db->fetch('SELECT value FROM settings WHERE name = ?', ['app_theme']);
        if ($row && !empty($row['value'])) {
            $resolved = (string) $row['value'];
            return $resolved;
        }
    } catch (\Throwable) {
        // settings table may not exist yet
    }
    $resolved = APP_THEME;
    return $resolved;
}

/**
 * Returns the list of installed themes (subdirs of app/Views/themes/).
 */
function availableThemes(): array
{
    $dir = dirname(__DIR__) . '/app/Views/themes';
    if (!is_dir($dir)) {
        return ['default'];
    }
    $themes = [];
    foreach (scandir($dir) as $entry) {
        if ($entry[0] === '.') continue;
        if (is_dir($dir . '/' . $entry) && is_file($dir . '/' . $entry . '/layouts/base.php')) {
            $themes[] = $entry;
        }
    }
    return $themes ?: ['default'];
}
define('SESSION_NAME', getEnvValue('SESSION_NAME', 'ipmi_session'));

define('ROOT_PATH', dirname(__DIR__));
define('LOG_FILE', ROOT_PATH . '/logs/app.log');

date_default_timezone_set('UTC');

function appBasePath(): string {
    static $basePath = null;

    if ($basePath !== null) {
        return $basePath;
    }

    $configuredPath = (string) (parse_url(APP_URL, PHP_URL_PATH) ?? '');
    if ($configuredPath !== '' && $configuredPath !== '/') {
        $basePath = rtrim($configuredPath, '/');
        return $basePath;
    }

    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $directory = str_replace('\\', '/', dirname($scriptName));
    $basePath = ($directory === '/' || $directory === '.') ? '' : rtrim($directory, '/');
    return $basePath;
}

function routeUrl(string $path = '/', array $query = []): string {
    $normalizedPath = '/' . ltrim($path, '/');
    $basePath = appBasePath();
    $url = ($normalizedPath === '/')
        ? ($basePath !== '' ? $basePath . '/' : '/')
        : ($basePath !== '' ? $basePath : '') . $normalizedPath;

    if ($query === []) {
        return $url;
    }

    return $url . '?' . http_build_query($query);
}
