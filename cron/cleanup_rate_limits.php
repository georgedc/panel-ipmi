<?php
// Purge expired rate limit windows from the DB
// Run via cron: */30 * * * * /usr/bin/php /var/www/html/ipmi-panel/cron/cleanup_rate_limits.php

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/app/Database/Database.php';

use App\Database\Database;

try {
    $db = Database::getInstance();
    $deleted = $db->query(
        'DELETE FROM rate_limits WHERE window_start < ?',
        [time() - 3600]
    )->rowCount();
    echo date('Y-m-d H:i:s') . " - Cleaned $deleted expired rate limit rows\n";
} catch (Throwable $e) {
    echo date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n";
}
