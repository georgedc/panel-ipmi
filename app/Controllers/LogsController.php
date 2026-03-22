<?php

namespace App\Controllers;

use App\Http\Request;
use App\Services\AuthService;
use App\Database\Database;

class LogsController extends Controller
{
    public function __construct(private ?AuthService $authService = null)
    {
        $this->authService ??= new AuthService();
    }

    public function index(Request $request)
    {
        $db = Database::getInstance();
        $perPage = 50;
        $page = max(1, (int) $request->query('page', 1));
        $filterServer = (int) $request->query('server_id', 0);
        $filterUser = (int) $request->query('user_id', 0);
        $filterAction = trim((string) $request->query('action', ''));

        $where = '1=1';
        $params = [];

        if ($filterUser > 0) {
            $where .= ' AND l.user_id = ?';
            $params[] = $filterUser;
        }
        if ($filterServer > 0) {
            $where .= ' AND l.server_id = ?';
            $params[] = $filterServer;
        }
        if ($filterAction !== '') {
            $where .= ' AND l.action LIKE ?';
            $params[] = '%' . $filterAction . '%';
        }

        $totalRows = (int) (($db->fetch("SELECT COUNT(*) AS c FROM activity_logs l WHERE {$where}", $params)['c'] ?? 0));
        $totalPages = max(1, (int) ceil($totalRows / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $logs = $db->fetchAll(
            "SELECT l.*, u.username, s.name AS server_name
             FROM activity_logs l
             LEFT JOIN users u ON u.id = l.user_id
             LEFT JOIN servers s ON s.id = l.server_id
             WHERE {$where}
             ORDER BY l.timestamp DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return $this->view('logs/index', [
            'title' => __('logs.title'),
            'logs' => $logs,
            'servers' => $db->fetchAll('SELECT id, name FROM servers ORDER BY name'),
            'users' => $db->fetchAll('SELECT id, username FROM users ORDER BY username'),
            'filterServer' => $filterServer,
            'filterUser' => $filterUser,
            'filterAction' => $filterAction,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalRows' => $totalRows,
        ]);
    }
}
