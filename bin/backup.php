#!/usr/bin/env php
<?php
/**
 * IPMI Panel — Automated Backup Script
 * Usage:   php /var/www/html/ipmi-panel/bin/backup.php [--files]
 * Cron:    0 2 * * * php /var/www/html/ipmi-panel/bin/backup.php >> /var/log/ipmi-backup.log 2>&1
 */

define('BASE_PATH', dirname(__DIR__));
define('RUNNING_CLI', true);

// Load .env for CLI (Apache env vars not available here)
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if ($line === '' || $line[0] === '#') continue;
        if (!str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        putenv(trim($k) . '=' . trim($v));
        $_ENV[trim($k)] = trim($v);
    }
}
// Map ENCRYPTION_KEY from .env if IPMI_ENCRYPTION_KEY not set
if (!getenv('IPMI_ENCRYPTION_KEY') && getenv('ENCRYPTION_KEY')) {
    putenv('IPMI_ENCRYPTION_KEY=' . getenv('ENCRYPTION_KEY'));
}

// Fallback: read directly from Apache SSL conf for CLI runs
if (!getenv('IPMI_ENCRYPTION_KEY')) {
    $apacheConf = getenv('APACHE_VHOST_CONF') ?: '/etc/httpd/conf.d/ipmi-panel-ssl.conf';
    if (file_exists($apacheConf)) {
        preg_match('/SetEnv\s+IPMI_ENCRYPTION_KEY\s+"([^"]+)"/', file_get_contents($apacheConf), $m);
        if (!empty($m[1])) {
            putenv('IPMI_ENCRYPTION_KEY=' . $m[1]);
        }
    }
}

require BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/includes/config.php';
require_once BASE_PATH . '/includes/Database.php';
require_once BASE_PATH . '/includes/Auth.php';
require_once BASE_PATH . '/includes/Logger.php';
require_once BASE_PATH . '/includes/CSRF.php';
require_once BASE_PATH . '/includes/IPMI.php';

use App\Services\BackupService;

$includeFiles = in_array('--files', $argv ?? [], true);

$log = static function (string $message): void {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
};

$log('Starting backup' . ($includeFiles ? ' (with files)' : ' (database only)') . '...');

try {
    $service = new BackupService();
    $results = $service->createBackup($includeFiles);

    foreach ($results as $key => $value) {
        if (str_ends_with($key, '_remote') && $value === true) {
            continue;
        }
        if (is_string($value) && file_exists($value)) {
            $size = $service->formatSize((int) filesize($value));
            $log('Created: ' . basename($value) . ' (' . $size . ')');
        }
    }

    if (!empty($results['db_remote']) || !empty($results['files_remote'])) {
        $log('Remote copy completed.');
    }

    $log('Backup finished successfully.');
    exit(0);
} catch (Throwable $e) {
    $log('ERROR: ' . $e->getMessage());
    exit(1);
}
