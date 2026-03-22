<?php

namespace App\Controllers;

use App\Http\Request;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(private ?DashboardService $dashboardService = null)
    {
        $this->dashboardService ??= new DashboardService();
    }

    public function summary(Request $request)
    {
        if (!$this->dashboardService->isAuthenticated()) {
            return $this->json(['error' => __('app.auth_required')], 401);
        }

        return $this->json($this->dashboardService->summary());
    }

    public function page(Request $request)
    {
        return $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'data' => $this->dashboardService->summary(),
        ]);
    }
}
