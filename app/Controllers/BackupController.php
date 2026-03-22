<?php

namespace App\Controllers;

use App\Http\Request;
use App\Services\BackupService;
use App\Services\SettingsManager;
use Exception;

class BackupController extends Controller
{
    public function __construct(
        private ?BackupService $backupService = null,
        private ?SettingsManager $settings = null
    ) {
        $this->backupService ??= new BackupService();
        $this->settings ??= new SettingsManager();
    }

    public function index(Request $request)
    {
        return $this->view('backup/index', [
            'title' => __('backup.title'),
            'backups' => $this->backupService->listBackups(),
            'settings' => $this->backupSettings(),
            'flash_success' => $this->pullFlash('mvc_backup_success'),
            'flash_error' => $this->pullFlash('mvc_backup_error'),
        ]);
    }

    public function run(Request $request)
    {
        \App\Http\Csrf::verifyRequest();

        $includeFiles = $request->input('include_files', '') === '1';

        try {
            $results = $this->backupService->createBackup($includeFiles);

            $parts = [];
            if (isset($results['db'])) {
                $parts[] = 'Database: ' . basename($results['db']);
            }
            if (isset($results['files'])) {
                $parts[] = 'Files: ' . basename($results['files']);
            }
            $remote = !empty($results['db_remote']) || !empty($results['files_remote']);
            if ($remote) {
                $parts[] = __('backup.copied_remote');
            }

            $this->flash('mvc_backup_success', __('backup.created') . ' — ' . implode(', ', $parts));
            $this->clearFlash('mvc_backup_error');
        } catch (Exception $e) {
            $this->clearFlash('mvc_backup_success');
            $this->flash('mvc_backup_error', $e->getMessage());
        }

        return $this->redirect(routeUrl('/backup'));
    }

    public function download(Request $request)
    {
        $filename = (string) $request->query('file', '');

        try {
            $path = $this->backupService->getFilePath($filename);
        } catch (Exception $e) {
            return $this->view('errors/simple', [
                'title'   => 'Backup',
                'heading' => 'Download error',
                'message' => $e->getMessage(),
                'back_url' => routeUrl('/backup'),
            ], 404);
        }

        $size = filesize($path);
        header('Content-Type: application/gzip');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Length: ' . $size);
        header('Cache-Control: no-store');
        readfile($path);
        exit;
    }

    public function delete(Request $request)
    {
        \App\Http\Csrf::verifyRequest();

        $filename = (string) $request->input('filename', '');

        try {
            $this->backupService->deleteBackup($filename);
            $this->flash('mvc_backup_success', __('backup.deleted') . ': ' . $filename);
            $this->clearFlash('mvc_backup_error');
        } catch (Exception $e) {
            $this->clearFlash('mvc_backup_success');
            $this->flash('mvc_backup_error', $e->getMessage());
        }

        return $this->redirect(routeUrl('/backup'));
    }

    public function saveSettings(Request $request)
    {
        \App\Http\Csrf::verifyRequest();

        try {
            // Validate and restrict backup path to within the project root
            $localPath = trim((string) $request->input('backup_local_path', ROOT_PATH . '/storage/backups'));
            $realParent = realpath(dirname($localPath)) ?: realpath($localPath);
            if ($realParent === false || !str_starts_with($realParent, ROOT_PATH)) {
                throw new Exception('Backup path must be inside the application directory.');
            }
            $this->settings->set('backup_local_path', $localPath);

            $this->settings->set('backup_remote_enabled', $request->input('backup_remote_enabled', '') === '1' ? '1' : '0');
            $this->settings->set('backup_remote_host', trim((string) $request->input('backup_remote_host', '')));

            $port = (int) $request->input('backup_remote_port', 22);
            if ($port < 1 || $port > 65535) {
                throw new Exception('Invalid SSH port number.');
            }
            $this->settings->set('backup_remote_port', (string) $port);
            $this->settings->set('backup_remote_user', trim((string) $request->input('backup_remote_user', '')));
            $this->settings->set('backup_remote_path', trim((string) $request->input('backup_remote_path', '/backups')));

            $newKey = trim((string) $request->input('backup_ssh_private_key', ''));
            if ($newKey !== '') {
                // Validate it looks like a PEM private key — reject anything suspicious
                if (!preg_match('/^-----BEGIN [A-Z ]+PRIVATE KEY-----/', $newKey) ||
                    !preg_match('/-----END [A-Z ]+PRIVATE KEY-----\s*$/', $newKey)) {
                    throw new Exception('Invalid SSH private key format. Must be a PEM key.');
                }
                $this->settings->set('backup_ssh_private_key', $newKey);
            }

            $this->flash('mvc_backup_success', __('backup.settings_saved'));
            $this->clearFlash('mvc_backup_error');
        } catch (Exception $e) {
            $this->clearFlash('mvc_backup_success');
            $this->flash('mvc_backup_error', $e->getMessage());
        }

        return $this->redirect(routeUrl('/backup'));
    }

    private function backupSettings(): array
    {
        return [
            'backup_local_path'       => $this->settings->get('backup_local_path', ROOT_PATH . '/storage/backups'),
            'backup_remote_enabled'   => $this->settings->get('backup_remote_enabled', '0'),
            'backup_remote_host'      => $this->settings->get('backup_remote_host', ''),
            'backup_remote_port'      => $this->settings->get('backup_remote_port', '22'),
            'backup_remote_user'      => $this->settings->get('backup_remote_user', ''),
            'backup_remote_path'      => $this->settings->get('backup_remote_path', '/backups'),
            'backup_ssh_key_set'      => $this->settings->get('backup_ssh_private_key', '') !== '',
        ];
    }
}
