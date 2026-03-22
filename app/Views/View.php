<?php

namespace App\Views;

use RuntimeException;

class View
{
    public static function render(string $template, array $context = []): string
    {
        $relative = ltrim($template, '/') . '.php';
        $theme = function_exists('activeTheme') ? activeTheme() : (defined('APP_THEME') ? (string) APP_THEME : 'default');
        $file = dirname(__DIR__) . '/Views/themes/' . $theme . '/' . $relative;

        if (!is_file($file)) {
            throw new RuntimeException('View not found: ' . $template . ' for theme ' . $theme);
        }

        extract($context, EXTR_SKIP);
        ob_start();
        require $file;
        return (string) ob_get_clean();
    }
}
