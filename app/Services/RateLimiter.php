<?php

namespace App\Services;

use App\Database\Database;

class RateLimiter
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Record a hit and return true if the request is allowed, false if rate limited.
     *
     * @param string $scope     Unique identifier (e.g. "power:user:5", "power:server:22")
     * @param int    $maxHits   Maximum allowed hits in the window
     * @param int    $windowSec Window duration in seconds
     */
    public function attempt(string $scope, int $maxHits, int $windowSec): bool
    {
        $now = time();
        $windowStart = (int) floor($now / $windowSec) * $windowSec;
        $key = md5($scope . '|' . $windowStart);

        try {
            // Atomic upsert: insert or increment
            $this->db->query(
                'INSERT INTO rate_limits (scope_key, scope, window_start, hit_count)
                 VALUES (?, ?, ?, 1)
                 ON DUPLICATE KEY UPDATE hit_count = hit_count + 1',
                [$key, $scope, $windowStart]
            );

            $row = $this->db->fetch(
                'SELECT hit_count FROM rate_limits WHERE scope_key = ?',
                [$key]
            );

            return (int) ($row['hit_count'] ?? 1) <= $maxHits;
        } catch (\Throwable $e) {
            error_log('RateLimiter error: ' . $e->getMessage());
            // On DB error, allow the request (fail open) to avoid locking users out
            return true;
        }
    }

    /**
     * Returns seconds until the current window resets.
     */
    public function retryAfter(int $windowSec): int
    {
        $now = time();
        $windowStart = (int) floor($now / $windowSec) * $windowSec;
        return max(1, ($windowStart + $windowSec) - $now);
    }

    /**
     * Check current hit count without incrementing. Returns true if still allowed.
     */
    public function check(string $scope, int $maxHits, int $windowSec): bool
    {
        $now = time();
        $windowStart = (int) floor($now / $windowSec) * $windowSec;
        $key = md5($scope . '|' . $windowStart);

        try {
            $row = $this->db->fetch(
                'SELECT hit_count FROM rate_limits WHERE scope_key = ?',
                [$key]
            );
            return (int) ($row['hit_count'] ?? 0) < $maxHits;
        } catch (\Throwable $e) {
            error_log('RateLimiter error: ' . $e->getMessage());
            return true;
        }
    }

    /**
     * Reset all windows for a given scope prefix (e.g. after successful login).
     */
    public function reset(string $scope): void
    {
        try {
            $this->db->query(
                'DELETE FROM rate_limits WHERE scope = ?',
                [$scope]
            );
        } catch (\Throwable) {
            // Non-critical
        }
    }

    /**
     * Purge expired windows older than 1 hour. Call from a periodic task or cleanup hook.
     */
    public function cleanup(): void
    {
        try {
            $this->db->query(
                'DELETE FROM rate_limits WHERE window_start < ?',
                [time() - 3600]
            );
        } catch (\Throwable) {
            // Non-critical
        }
    }
}
