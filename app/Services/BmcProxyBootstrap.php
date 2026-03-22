<?php

namespace App\Services;

class BmcProxyBootstrap
{
    private static bool $loaded = false;

    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        require_once dirname(__DIR__, 2) . '/includes/config.php';
        require_once dirname(__DIR__, 2) . '/includes/Database.php';
        require_once dirname(__DIR__, 2) . '/includes/Auth.php';

        self::$loaded = true;
    }
}
