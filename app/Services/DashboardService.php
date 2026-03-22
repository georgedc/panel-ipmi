<?php

namespace App\Services;

use App\Repositories\ActivityLogRepository;
use App\Repositories\ServerRepository;

class DashboardService
{
    public function __construct(
        private ?ServerRepository $servers = null,
        private ?ActivityLogRepository $activityLogs = null,
        private ?AuthService $auth = null
    ) {
        $this->servers ??= new ServerRepository();
        $this->activityLogs ??= new ActivityLogRepository();
        $this->auth ??= new AuthService();
    }

    public function summary(): array
    {
        $user = $this->auth->currentUser();
        $userId = $this->auth->isAdmin() ? null : ($user['id'] ?? null);

        return [
            'counts' => $this->servers->statusCounts($userId),
            'recent_activity' => $this->activityLogs->recent(5, $userId),
            'user' => $user,
            'is_admin' => $this->auth->isAdmin(),
        ];
    }

    public function isAuthenticated(): bool
    {
        return $this->auth->isLoggedIn();
    }
}
