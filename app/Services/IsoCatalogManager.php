<?php

namespace App\Services;

use App\Database\Database;
use IPMI;
use Exception;

class IsoCatalogManager
{
    public function listAll(Database $db): array
    {
        return $db->fetchAll(
            "SELECT i.*, u.username AS uploaded_by_name
             FROM iso_images i
             LEFT JOIN users u ON i.uploaded_by = u.id
             ORDER BY i.is_active DESC, i.name ASC"
        );
    }

    public function find(Database $db, int $isoId): ?array
    {
        return $db->fetch('SELECT * FROM iso_images WHERE id = ?', [$isoId]);
    }

    public function create(Database $db, array $input, array $currentUser, IPMI $ipmi): string
    {
        $insertData = $this->normalizePayload($input, null, $ipmi);
        $insertData['uploaded_by'] = (int) $currentUser['id'];
        $db->insert('iso_images', $insertData);
        return 'ISO registered successfully.';
    }

    public function update(Database $db, int $isoId, array $input, IPMI $ipmi): string
    {
        $existingIso = $this->find($db, $isoId);
        if (!$existingIso) {
            throw new Exception(__('iso.selected_iso_missing'));
        }
        $updateData = $this->normalizePayload($input, $existingIso, $ipmi);
        $db->update('iso_images', $updateData, 'id = ?', [$isoId]);
        return 'ISO updated successfully.';
    }

    public function delete(Database $db, int $isoId): string
    {
        $existingIso = $this->find($db, $isoId);
        if (!$existingIso) {
            throw new Exception(__('iso.selected_iso_missing'));
        }
        $db->delete('iso_images', 'id = ?', [$isoId]);
        return 'ISO deleted successfully.';
    }

    public function queueDownloadJob(Database $db, array $input, array $currentUser): int
    {
        $name = trim((string) ($input['name'] ?? ''));
        $downloadUrl = trim((string) ($input['download_url'] ?? ''));
        $sourceType = trim((string) ($input['source_type'] ?? 'local'));
        $notes = trim((string) ($input['notes'] ?? ''));
        $isActive = isset($input['is_active']) ? 1 : 0;

        if ($sourceType !== 'local') {
            throw new Exception('Download queue only applies to local ISO images.');
        }
        if ($name === '' || mb_strlen($name) > 255) {
            throw new Exception('Invalid ISO name.');
        }
        if ($downloadUrl === '') {
            throw new Exception('You must provide a download URL.');
        }

        $targetPath = $this->buildLocalIsoTargetPath($name, $downloadUrl);
        $payload = [
            'name' => $name,
            'notes' => $notes,
            'is_active' => $isActive,
        ];

        return (int) $db->insert('iso_download_jobs', [
            'name' => $name,
            'download_url' => $downloadUrl,
            'target_path' => $targetPath,
            'status' => 'pending',
            'progress' => 0,
            'size_downloaded' => 0,
            'total_size' => 0,
            'message' => 'Queued',
            'created_by' => (int) $currentUser['id'],
            'form_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function launchDownloadWorker(int $jobId): void
    {
        $phpBin = trim((string) getEnvValue('PHP_CLI_PATH', '/usr/bin/php'));
        if ($phpBin === '') {
            $phpBin = '/usr/bin/php';
        }

        $worker = ROOT_PATH . '/includes/iso_download_worker.php';
        $encKey = getenv('IPMI_ENCRYPTION_KEY') ?: '';
        $logFile = '/tmp/iso_download_job_' . (int) $jobId . '.log';
        $command = 'nohup setsid env';
        if ($encKey !== '') {
            $command .= ' IPMI_ENCRYPTION_KEY=' . escapeshellarg($encKey);
        }
        $command .= ' ' . escapeshellcmd($phpBin) . ' ' . escapeshellarg($worker) . ' ' . (int) $jobId . ' < /dev/null >> ' . escapeshellarg($logFile) . ' 2>&1 &';
        exec($command);
    }

    public function buildLocalIsoTargetPath(string $name, string $downloadUrl): string
    {
        $path = parse_url($downloadUrl, PHP_URL_PATH);
        $basename = is_string($path) ? basename($path) : '';
        $basename = preg_replace('/[^A-Za-z0-9._-]/', '-', $basename) ?? '';

        if ($basename === '' || stripos($basename, '.iso') === false) {
            $basename = $this->slugifyFilename($name) . '.iso';
        }

        $basename = preg_replace('/\.iso.*$/i', '.iso', $basename) ?? $basename;
        $target = '/mnt/iso/' . $basename;

        if (!file_exists($target)) {
            return $target;
        }

        $info = pathinfo($basename);
        $base = $info['filename'];
        $ext = isset($info['extension']) ? '.' . $info['extension'] : '.iso';
        return '/mnt/iso/' . $base . '-' . date('Ymd-His') . $ext;
    }

    public function downloadIsoToLocal(string $url, string $targetPath): array
    {
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new Exception('The download URL must use HTTP or HTTPS.');
        }

        if (!is_dir('/mnt/iso') || !is_writable('/mnt/iso')) {
            throw new Exception('The /mnt/iso directory is not writable from the panel.');
        }

        $tempPath = $targetPath . '.part';
        $handle = fopen($tempPath, 'wb');
        if ($handle === false) {
            throw new Exception('Unable to create the temporary ISO file.');
        }

        $ch = curl_init($url);
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
        ]);

        $result = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        fclose($handle);

        if ($result === false) {
            @unlink($tempPath);
            throw new Exception('Failed to download the ISO: ' . $error);
        }

        if ($httpCode >= 400) {
            @unlink($tempPath);
            throw new Exception('The ISO URL responded with HTTP ' . $httpCode . '.');
        }

        $size = (int) filesize($tempPath);
        if ($size <= 0) {
            @unlink($tempPath);
            throw new Exception('The ISO download completed with an empty file.');
        }

        if (!rename($tempPath, $targetPath)) {
            @unlink($tempPath);
            throw new Exception('Unable to move the downloaded ISO into place.');
        }

        return [
            'path' => $targetPath,
            'size' => $size,
        ];
    }

    public function normalizePayload(array $input, ?array $existingIso, IPMI $ipmi): array
    {
        $name = trim((string) ($input['name'] ?? ''));
        $sourceType = trim((string) ($input['source_type'] ?? 'local'));
        $notes = trim((string) ($input['notes'] ?? ''));
        $isActive = isset($input['is_active']) ? 1 : 0;

        if ($name === '' || mb_strlen($name) > 255) {
            throw new Exception('Invalid ISO name.');
        }

        if (!in_array($sourceType, ['local', 'remote_nfs', 'remote_cifs'], true)) {
            throw new Exception('Invalid source type.');
        }

        $payload = [
            'name' => $name,
            'source_type' => $sourceType,
            'notes' => $notes !== '' ? $notes : null,
            'is_active' => $isActive,
            'local_path' => null,
            'remote_host' => null,
            'remote_path' => null,
            'remote_username' => null,
            'remote_password' => null,
            'file_path' => '',
            'size' => 0,
        ];

        if ($sourceType === 'local') {
            $localPath = trim((string) ($input['local_path'] ?? ''));
            $downloadUrl = trim((string) ($input['download_url'] ?? ''));

            if ($downloadUrl !== '') {
                $downloaded = $this->downloadIsoToLocal($downloadUrl, $this->buildLocalIsoTargetPath($name, $downloadUrl));
                $payload['local_path'] = $downloaded['path'];
                $payload['file_path'] = $downloaded['path'];
                $payload['size'] = $downloaded['size'];
                return $payload;
            }

            if ($localPath === '' || $localPath[0] !== '/') {
                throw new Exception('The local path must be absolute or you must provide a download URL.');
            }
            if (!is_file($localPath) || !is_readable($localPath)) {
                throw new Exception('The local ISO does not exist or is not readable from the panel.');
            }
            $payload['local_path'] = $localPath;
            $payload['file_path'] = $localPath;
            $payload['size'] = (int) filesize($localPath);
            return $payload;
        }

        $remoteHost = trim((string) ($input['remote_host'] ?? ''));
        $remotePath = trim((string) ($input['remote_path'] ?? ''));

        if ($remoteHost === '') {
            throw new Exception('You must provide the remote host.');
        }
        if ($remotePath === '') {
            throw new Exception('You must provide the remote ISO path.');
        }

        $payload['remote_host'] = $remoteHost;
        $payload['remote_path'] = $remotePath;

        if ($sourceType === 'remote_nfs') {
            if ($remotePath[0] !== '/') {
                throw new Exception('The NFS path must start with /.');
            }
            $payload['file_path'] = 'nfs://' . $remoteHost . $remotePath;
            return $payload;
        }

        $remoteUsername = trim((string) ($input['remote_username'] ?? ''));
        $remotePassword = (string) ($input['remote_password'] ?? '');
        $storedPassword = $existingIso['remote_password'] ?? null;

        $payload['remote_username'] = $remoteUsername !== '' ? $remoteUsername : null;
        $payload['file_path'] = 'cifs://' . $remoteHost . '/' . ltrim($remotePath, '/');
        $payload['remote_password'] = $remotePassword !== '' ? $ipmi->encryptPassword($remotePassword) : $storedPassword;

        return $payload;
    }

    private function slugifyFilename(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9._-]+/', '-', $value) ?? '';
        $value = trim($value, '-._');
        return $value !== '' ? $value : 'iso-image';
    }
}
