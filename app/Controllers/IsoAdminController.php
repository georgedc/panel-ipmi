<?php

namespace App\Controllers;

use App\Http\Request;
use App\Services\AuthService;
use App\Services\IsoCatalogManager;
use App\Database\Database;
use Exception;
use IPMI;

class IsoAdminController extends Controller
{
    private Database $db;
    private IPMI $ipmi;

    public function __construct(private ?IsoCatalogManager $catalog = null, private ?AuthService $authService = null)
    {
        $this->catalog ??= new IsoCatalogManager();
        $this->authService ??= new AuthService();
        $this->db = Database::getInstance();
        $this->ipmi = new IPMI();
    }

    public function index(Request $request)
    {
        $editId = (int) $request->query('edit', 0);
        $jobs = $this->db->fetchAll('SELECT id, name, status, progress, message, error_text, created_at FROM iso_download_jobs ORDER BY id DESC LIMIT 10');

        return $this->view('isos/admin', [
            'title' => 'ISO Catalog',
            'isos' => $this->catalog->listAll($this->db),
            'editingIso' => $editId > 0 ? $this->catalog->find($this->db, $editId) : null,
            'jobs' => $jobs,
            'flash_success' => $this->pullFlash('mvc_iso_admin_success'),
            'flash_error' => $this->pullFlash('mvc_iso_admin_error'),
        ]);
    }

    public function store(Request $request)
    {
        \App\Http\Csrf::verifyRequest();
        try {
            $msg = $this->catalog->create($this->db, $request->all(), $this->authService->currentUser() ?? [], $this->ipmi);
            $this->flash('mvc_iso_admin_success', $msg);
            $this->clearFlash('mvc_iso_admin_error');
        } catch (Exception $e) {
            $this->clearFlash('mvc_iso_admin_success');
            $this->flash('mvc_iso_admin_error', $e->getMessage());
        }
        return $this->redirect(routeUrl('/iso-admin'));
    }

    public function queueDownload(Request $request)
    {
        \App\Http\Csrf::verifyRequest();
        try {
            $jobId = $this->catalog->queueDownloadJob($this->db, $request->all(), $this->authService->currentUser() ?? []);
            $this->catalog->launchDownloadWorker($jobId);
            $this->flash('mvc_iso_admin_success', 'ISO download queued as job #' . $jobId . '.');
            $this->clearFlash('mvc_iso_admin_error');
        } catch (Exception $e) {
            $this->clearFlash('mvc_iso_admin_success');
            $this->flash('mvc_iso_admin_error', $e->getMessage());
        }
        return $this->redirect(routeUrl('/iso-admin'));
    }

    public function update(Request $request)
    {
        \App\Http\Csrf::verifyRequest();
        try {
            $msg = $this->catalog->update($this->db, (int) $request->input('iso_id', 0), $request->all(), $this->ipmi);
            $this->flash('mvc_iso_admin_success', $msg);
            $this->clearFlash('mvc_iso_admin_error');
        } catch (Exception $e) {
            $this->clearFlash('mvc_iso_admin_success');
            $this->flash('mvc_iso_admin_error', $e->getMessage());
        }
        return $this->redirect(routeUrl('/iso-admin'));
    }

    public function delete(Request $request)
    {
        \App\Http\Csrf::verifyRequest();
        try {
            $msg = $this->catalog->delete($this->db, (int) $request->input('iso_id', 0));
            $this->flash('mvc_iso_admin_success', $msg);
            $this->clearFlash('mvc_iso_admin_error');
        } catch (Exception $e) {
            $this->clearFlash('mvc_iso_admin_success');
            $this->flash('mvc_iso_admin_error', $e->getMessage());
        }
        return $this->redirect(routeUrl('/iso-admin'));
    }
}
