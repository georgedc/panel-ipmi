<?php

namespace App\Services;

use Exception;

class BackupService
{
    private string $backupDir;
    private SettingsManager $settings;

    // Allowed parent for backup directory — prevents path escape
    private const SAFE_PARENT = ROOT_PATH;

    public function __construct(?SettingsManager $settings = null)
    {
        $this->settings = $settings ?? new SettingsManager();
        $this->backupDir = $this->resolveBackupDir(
            $this->settings->get('backup_local_path', ROOT_PATH . '/storage/backups')
        );
    }

    private function resolveBackupDir(string $path): string
    {
        $path = rtrim($path, '/');
        // If directory already exists, canonicalize it; otherwise canonicalize parent
        $real = realpath($path) ?: realpath(dirname($path));
        if ($real === false || !str_starts_with($real, self::SAFE_PARENT)) {
            return ROOT_PATH . '/storage/backups';
        }
        return $path;
    }

    public function createBackup(bool $includeFiles = false): array
    {
        if (!is_dir($this->backupDir)) {
            if (!mkdir($this->backupDir, 0750, true)) {
                throw new Exception('Cannot create backup directory.');
            }
        }
        if (!is_writable($this->backupDir)) {
            throw new Exception('Backup directory is not writable.');
        }

        $timestamp = date('Y-m-d_H-i-s');
        $results = [];

        $dbFile = $this->backupDir . '/db_' . $timestamp . '.sql.gz';
        $this->dumpDatabase($dbFile);
        $results['db'] = $dbFile;

        if ($includeFiles) {
            $filesFile = $this->backupDir . '/files_' . $timestamp . '.tar.gz';
            $this->dumpFiles($filesFile);
            $results['files'] = $filesFile;
        }

        if ($this->settings->get('backup_remote_enabled', '0') === '1') {
            foreach ($results as $type => $localPath) {
                $this->copyToRemote($localPath);
                $results[$type . '_remote'] = true;
            }
        }

        return $results;
    }

    private function dumpDatabase(string $outputFile): void
    {
        $host   = defined('DB_HOST') ? DB_HOST : 'localhost';
        $dbName = defined('DB_NAME') ? DB_NAME : 'ipmi_panel';
        $user   = defined('DB_USER') ? DB_USER : '';
        $pass   = defined('DB_PASS') ? DB_PASS : '';

        $cmd = sprintf(
            'mysqldump --single-transaction --routines --triggers -h %s -u %s %s %s 2>/dev/null | gzip > %s',
            escapeshellarg($host),
            escapeshellarg($user),
            $pass !== '' ? '-p' . escapeshellarg($pass) : '',
            escapeshellarg($dbName),
            escapeshellarg($outputFile)
        );

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($outputFile) || filesize($outputFile) < 100) {
            @unlink($outputFile);
            error_log('BackupService: mysqldump failed (exit ' . $exitCode . '): ' . implode(' ', $output));
            throw new Exception('Database dump failed. Check server logs for details.');
        }
    }

    private function dumpFiles(string $outputFile): void
    {
        $panelPath = ROOT_PATH;

        $cmd = sprintf(
            'tar --exclude=%s --exclude=%s/vendor -czf %s %s 2>/dev/null',
            escapeshellarg($this->backupDir),
            escapeshellarg($panelPath),
            escapeshellarg($outputFile),
            escapeshellarg($panelPath)
        );

        exec($cmd, $output, $exitCode);

        if ($exitCode > 1) {
            @unlink($outputFile);
            error_log('BackupService: tar failed (exit ' . $exitCode . ')');
            throw new Exception('Files backup failed. Check server logs for details.');
        }
    }

    public function copyToRemote(string $localFile): void
    {
        $host    = $this->settings->get('backup_remote_host', '');
        $user    = $this->settings->get('backup_remote_user', '');
        $path    = rtrim($this->settings->get('backup_remote_path', '/backups'), '/');
        $port    = (int) $this->settings->get('backup_remote_port', '22');
        $sshKey  = $this->settings->get('backup_ssh_private_key', '');

        if (empty($host) || empty($user)) {
            throw new Exception('Remote backup not configured. Set host and user first.');
        }

        $keyFile = null;
        try {
            if ($sshKey !== '') {
                // Write key to temp file with permissions set before data written
                $keyFile = tempnam(sys_get_temp_dir(), 'ipmi_bk_');
                if ($keyFile === false) {
                    throw new Exception('Could not create temporary key file.');
                }
                chmod($keyFile, 0600);
                file_put_contents($keyFile, $sshKey);
            }

            $keyArg = $keyFile ? '-i ' . escapeshellarg($keyFile) : '';

            $cmd = sprintf(
                'scp -q -P %d %s -o StrictHostKeyChecking=no -o BatchMode=yes %s %s@%s:%s/ 2>/dev/null',
                $port,
                $keyArg,
                escapeshellarg($localFile),
                escapeshellarg($user),
                escapeshellarg($host),
                escapeshellarg($path)
            );

            exec($cmd, $output, $exitCode);

            if ($exitCode !== 0) {
                error_log('BackupService: scp failed (exit ' . $exitCode . ') to ' . $user . '@' . $host);
                throw new Exception('Remote copy failed. Check server logs for details.');
            }
        } finally {
            if ($keyFile && file_exists($keyFile)) {
                unlink($keyFile);
            }
        }
    }

    public function listBackups(): array
    {
        if (!is_dir($this->backupDir)) {
            return [];
        }

        $files = glob($this->backupDir . '/*.gz');
        if (!$files) {
            return [];
        }

        $backups = [];
        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'path'     => $file,
                'size'     => filesize($file),
                'mtime'    => filemtime($file),
                'type'     => str_starts_with(basename($file), 'db_') ? 'database' : 'files',
            ];
        }

        usort($backups, fn ($a, $b) => $b['mtime'] <=> $a['mtime']);

        return $backups;
    }

    public function deleteBackup(string $filename): void
    {
        $filename = basename($filename);
        if (!preg_match('/^(db|files)_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.(?:sql\.gz|tar\.gz)$/', $filename)) {
            throw new Exception('Invalid backup filename.');
        }

        $path = $this->backupDir . '/' . $filename;
        if (!file_exists($path)) {
            throw new Exception('Backup file not found.');
        }

        if (!unlink($path)) {
            throw new Exception('Could not delete backup file.');
        }
    }

    public function getFilePath(string $filename): string
    {
        $filename = basename($filename);
        if (!preg_match('/^(db|files)_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.(?:sql\.gz|tar\.gz)$/', $filename)) {
            throw new Exception('Invalid backup filename.');
        }

        $path = $this->backupDir . '/' . $filename;
        if (!file_exists($path)) {
            throw new Exception('Backup file not found.');
        }

        return $path;
    }

    public function formatSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}
