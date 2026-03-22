<?php

namespace App\Controllers;

use App\Http\Request;
use App\Services\AuthService;
use App\Services\IsoCatalogService;
use App\Services\MediaMountService;
use RuntimeException;

class IsoController extends Controller
{
    public function __construct(
        private ?IsoCatalogService $isoCatalogService = null,
        private ?MediaMountService $mediaMountService = null,
        private ?AuthService $authService = null
    ) {
        $this->isoCatalogService ??= new IsoCatalogService();
        $this->mediaMountService ??= new MediaMountService();
        $this->authService ??= new AuthService();
    }

    public function index(Request $request)
    {
        if (!$this->authService->isAdmin()) {
            return $this->json(['error' => __('app.access_denied')], 403);
        }

        return $this->json(['isos' => $this->isoCatalogService->activeCatalog()]);
    }

    public function page(Request $request)
    {
        if (!$this->authService->isAdmin()) {
            return $this->redirect(routeUrl('/servers'));
        }

        return $this->view('isos/index', [
            'title' => 'ISO Mount Workspace',
            'workspace' => $this->mediaMountService->mountWorkspace(),
            'success' => $this->pullFlash('mvc_iso_success'),
            'error' => $this->pullFlash('mvc_iso_error'),
        ]);
    }

    public function mount(Request $request)
    {
        \App\Http\Csrf::verifyRequest();

        try {
            $result = $this->mediaMountService->mount((int) $request->input('server_id', 0), (int) $request->input('iso_id', 0));
            $this->flash('mvc_iso_success', $result['message']);
            $this->clearFlash('mvc_iso_error');
        } catch (RuntimeException $e) {
            $this->clearFlash('mvc_iso_success');
            $this->flash('mvc_iso_error', $e->getMessage());
        }

        return $this->redirect($this->resolveReturnTo($request));
    }

    public function unmount(Request $request)
    {
        \App\Http\Csrf::verifyRequest();

        try {
            $result = $this->mediaMountService->unmount((int) $request->input('server_id', 0));
            $this->flash('mvc_iso_success', $result['message']);
            $this->clearFlash('mvc_iso_error');
        } catch (RuntimeException $e) {
            $this->clearFlash('mvc_iso_success');
            $this->flash('mvc_iso_error', $e->getMessage());
        }

        return $this->redirect($this->resolveReturnTo($request));
    }

    public function refresh(Request $request)
    {
        \App\Http\Csrf::verifyRequest();

        try {
            $result = $this->mediaMountService->refresh((int) $request->input('server_id', 0));
            $this->flash('mvc_iso_success', $result['label']);
            $this->clearFlash('mvc_iso_error');
        } catch (RuntimeException $e) {
            $this->clearFlash('mvc_iso_success');
            $this->flash('mvc_iso_error', $e->getMessage());
        }

        return $this->redirect($this->resolveReturnTo($request));
    }
    private function resolveReturnTo(Request $request): string
    {
        $returnTo = (string) $request->input('return_to', routeUrl('/isos'));
        $isInternalPath = str_starts_with($returnTo, routeUrl('/'));
        if ($returnTo === '' || !$isInternalPath) {
            return routeUrl('/isos');
        }
        return $returnTo;
    }
}
