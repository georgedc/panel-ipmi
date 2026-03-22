<?php
if (PHP_SAPI !== 'cli') {
    exit(1);
}

require_once dirname(__DIR__) . '/vendor/autoload.php';

function workerEnvValue(string $key, ?string $default = null): ?string
{
    static $env = null;
    if ($env === null) {
        $env = [];
        $envFile = dirname(__DIR__) . '/.env';
        if (is_file($envFile)) {
            foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if ($line === '' || str_starts_with(trim($line), '#') || strpos($line, '=') === false) {
                    continue;
                }
                [$name, $value] = explode('=', $line, 2);
                $env[trim($name)] = trim($value);
            }
        }
    }

    return $env[$key] ?? $default;
}

define('DB_HOST', (static function (): string {
    $host = (string) workerEnvValue('DB_HOST', '127.0.0.1');
    return $host === 'localhost' ? '127.0.0.1' : $host;
})());
define('DB_NAME', (string) workerEnvValue('DB_NAME', ''));
define('DB_USER', (string) workerEnvValue('DB_USER', ''));
define('DB_PASS', (string) workerEnvValue('DB_PASS', ''));
define('APP_VERSION', (string) workerEnvValue('APP_VERSION', '1.0.0'));
require_once __DIR__ . '/Database.php';

function updateJob(\App\Database\Database $db, int $jobId, array $data): void {
    $db->update('iso_download_jobs', $data, 'id = ?', [$jobId]);
}

function workerLog(int $jobId, string $message): void
{
    $logFile = '/tmp/iso_download_job_' . $jobId . '.log';
    @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, FILE_APPEND);
}

$jobId = isset($argv[1]) ? (int) $argv[1] : 0;
if ($jobId <= 0) {
    exit(1);
}

$db = Database::getInstance();
$job = $db->fetch('SELECT * FROM iso_download_jobs WHERE id = ?', [$jobId]);
if (!$job) {
    exit(1);
}

$jobFinished = false;
register_shutdown_function(static function () use ($db, $jobId, &$jobFinished): void {
    // @phpstan-ignore-next-line (booleanAnd.alwaysFalse: $jobFinished is mutated to true by reference further down)
    if ($jobFinished) {
        return;
    }

    $lastError = error_get_last();
    if ($lastError === null) {
        return;
    }

    workerLog($jobId, 'Fatal shutdown: ' . $lastError['message']);
    updateJob($db, $jobId, [
        'status' => 'failed',
        'message' => 'Download failed.',
        'error_text' => 'Worker terminated unexpectedly: ' . $lastError['message'] . '.',
    ]);
});

if (!in_array((string) $job['status'], ['pending', 'running'], true)) {
    exit(0);
}

$payload = json_decode((string) $job['form_payload'], true);
if (!is_array($payload)) {
    updateJob($db, $jobId, [
        'status' => 'failed',
        'progress' => 0,
        'message' => 'Invalid payload.',
        'error_text' => 'Could not parse the download configuration.',
    ]);
    exit(1);
}

$targetPath = (string) $job['target_path'];
$tempPath = $targetPath . '.part';
$targetDir = dirname($targetPath);
workerLog($jobId, 'Starting download for ' . $targetPath);

if (!is_dir($targetDir) || !is_writable($targetDir)) {
    workerLog($jobId, 'Target directory is not writable: ' . $targetDir);
    updateJob($db, $jobId, [
        'status' => 'failed',
        'message' => 'Local path is not writable.',
        'error_text' => 'The destination folder is not ready for writing.',
    ]);
    $jobFinished = true;
    exit(1);
}

if (is_file($tempPath)) {
    if (!is_writable($tempPath)) {
        workerLog($jobId, 'Stale partial ISO is not writable: ' . $tempPath);
        updateJob($db, $jobId, [
            'status' => 'failed',
            'message' => 'Previous download is stuck.',
            'error_text' => 'The existing temporary file is not writable by the worker. Remove the previous download and try again.',
        ]);
        $jobFinished = true;
        exit(1);
    }

    if (!@unlink($tempPath)) {
        workerLog($jobId, 'Failed to remove stale partial ISO: ' . $tempPath);
        updateJob($db, $jobId, [
            'status' => 'failed',
            'message' => 'Previous download could not be restarted.',
            'error_text' => 'Could not clean the existing temporary file before restarting the download.',
        ]);
        $jobFinished = true;
        exit(1);
    }
}

$handle = fopen($tempPath, 'wb');
if ($handle === false) {
    workerLog($jobId, 'Failed to open temp file for writing: ' . $tempPath);
    updateJob($db, $jobId, [
        'status' => 'failed',
        'message' => 'Could not create the temporary file.',
        'error_text' => 'Could not prepare the download on disk.',
    ]);
    $jobFinished = true;
    exit(1);
}

updateJob($db, $jobId, [
    'status' => 'running',
    'progress' => 0,
    'message' => 'Starting download...',
    'error_text' => null,
    'size_downloaded' => 0,
    'total_size' => 0,
]);
workerLog($jobId, 'Download started from ' . (string) $job['download_url']);

$lastProgress = -1;
$lastTick = 0.0;
$ch = curl_init((string) $job['download_url']);
curl_setopt_array($ch, [
    CURLOPT_FILE => $handle,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_MAXREDIRS => 5,
    CURLOPT_FAILONERROR => true,
    CURLOPT_USERAGENT => 'IPMI-Control-Panel/' . APP_VERSION,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_NOPROGRESS => false,
    CURLOPT_PROGRESSFUNCTION => static function ($resource, float $downloadTotal, float $downloadedNow) use ($db, $jobId, &$lastProgress, &$lastTick) {
        $now = microtime(true);
        $progress = $downloadTotal > 0 ? (int) floor(($downloadedNow / $downloadTotal) * 100) : 0;
        $progress = max(0, min(99, $progress));

        if ($progress === $lastProgress && ($now - $lastTick) < 1.5) {
            return 0;
        }

        $lastProgress = $progress;
        $lastTick = $now;
        updateJob($db, $jobId, [
            'status' => 'running',
            'progress' => $progress,
            'size_downloaded' => (int) $downloadedNow,
            'total_size' => (int) max(0, $downloadTotal),
            'message' => $downloadTotal > 0 ? 'Downloading ISO... ' . $progress . '%' : 'Downloading ISO...',
        ]);
        return 0;
    },
]);

$result = curl_exec($ch);
$error = curl_error($ch);
$httpCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
curl_close($ch);
fclose($handle);

if ($result === false) {
    workerLog($jobId, 'curl_exec failed: ' . ($error !== '' ? $error : 'unknown error'));
    @unlink($tempPath);
    updateJob($db, $jobId, [
        'status' => 'failed',
        'message' => 'Download failed.',
        'error_text' => $error !== '' ? $error : 'Unknown error during download.',
    ]);
    $jobFinished = true;
    exit(1);
}

if ($httpCode >= 400) {
    workerLog($jobId, 'Download failed with HTTP ' . $httpCode);
    @unlink($tempPath);
    updateJob($db, $jobId, [
        'status' => 'failed',
        'message' => 'Download failed.',
        'error_text' => 'The URL responded with HTTP ' . $httpCode . '.',
    ]);
    $jobFinished = true;
    exit(1);
}

$size = is_file($tempPath) ? (int) filesize($tempPath) : 0;
if ($size <= 0) {
    workerLog($jobId, 'Downloaded file is empty');
    @unlink($tempPath);
    updateJob($db, $jobId, [
        'status' => 'failed',
        'message' => 'Download was empty.',
        'error_text' => 'The downloaded file contains no data.',
    ]);
    $jobFinished = true;
    exit(1);
}

if (!rename($tempPath, $targetPath)) {
    workerLog($jobId, 'Failed to move ISO into final path');
    @unlink($tempPath);
    updateJob($db, $jobId, [
        'status' => 'failed',
        'message' => 'Could not finalize the download.',
        'error_text' => 'Could not move the downloaded ISO to its final path.',
    ]);
    $jobFinished = true;
    exit(1);
}

$isoId = (int) $db->insert('iso_images', [
    'name' => (string) ($payload['name'] ?? $job['name']),
    'source_type' => 'local',
    'notes' => !empty($payload['notes']) ? (string) $payload['notes'] : null,
    'is_active' => !empty($payload['is_active']) ? 1 : 0,
    'local_path' => $targetPath,
    'remote_host' => null,
    'remote_path' => null,
    'remote_username' => null,
    'remote_password' => null,
    'file_path' => $targetPath,
    'size' => $size,
    'uploaded_by' => (int) $job['created_by'],
]);

updateJob($db, $jobId, [
    'status' => 'completed',
    'progress' => 100,
    'size_downloaded' => $size,
    'total_size' => $size,
    'message' => 'Download completed.',
    'iso_id' => $isoId,
    'error_text' => null,
]);
$jobFinished = true;
workerLog($jobId, 'Download completed successfully with ISO #' . $isoId);
