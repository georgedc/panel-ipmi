<?php

namespace App\Services;

use App\Database\Database;

class Logger
{
    private Database $db;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    public function logActivity($userId, $serverId, $action, $details = '')
    {
        if (defined('LOG_ACTIONS') && !LOG_ACTIONS) {
            return true;
        }

        return $this->db->insert('activity_logs', [
            'user_id' => $userId,
            'server_id' => $serverId,
            'action' => $action,
            'details' => $details,
            'ip_address' => $this->getClientIp(),
        ]);
    }

    public function getActivityLogs($limit = 100, $userId = null, $serverId = null)
    {
        $sql = "SELECT l.*, u.username, s.name as server_name
                FROM activity_logs l
                LEFT JOIN users u ON l.user_id = u.id
                LEFT JOIN servers s ON l.server_id = s.id
                WHERE 1=1";

        $params = [];

        if ($userId !== null) {
            $sql .= " AND l.user_id = ?";
            $params[] = $userId;
        }

        if ($serverId !== null) {
            $sql .= " AND l.server_id = ?";
            $params[] = $serverId;
        }

        $sql .= " ORDER BY l.timestamp DESC LIMIT " . (int) $limit;

        return $this->db->fetchAll($sql, $params);
    }

    public function logToFile($message, $level = 'INFO')
    {
        if (!defined('LOG_FILE')) {
            return false;
        }

        $logDir = dirname(LOG_FILE);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $logMessage = '[' . $timestamp . '] [' . $level . '] ' . $message . PHP_EOL;

        return file_put_contents(LOG_FILE, $logMessage, FILE_APPEND);
    }

    private function getClientIp(): string
    {
        $remoteAddr = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

        // Only trust X-Forwarded-For if the connection comes from a known trusted proxy
        $trustedProxies = defined('TRUSTED_PROXIES') ? TRUSTED_PROXIES : [];
        if (!empty($trustedProxies) && in_array($remoteAddr, (array) $trustedProxies, true)) {
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                // Take only the first IP (client), ignore proxy chain
                $forwarded = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
                $forwarded = trim($forwarded);
                if (filter_var($forwarded, FILTER_VALIDATE_IP)) {
                    return $forwarded;
                }
            }
        }

        return $remoteAddr;
    }
}
