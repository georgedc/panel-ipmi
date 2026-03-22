<?php

namespace App\Services;

final class BmcProxySupport
{
    public static function fail(string $message, int $status = 400): never
    {
        throw new BmcProxyException($message, $status);
    }

    public static function reopenSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function localNoVncAssets(): array
    {
        $basePath = ROOT_PATH . '/public/legacy/novnc/include';
        return [
            'ast2100.js' => $basePath . '/ast2100.js',
            'base64.js' => $basePath . '/base64.js',
            'des.js' => $basePath . '/des.js',
            'display.js' => $basePath . '/display.js',
            'input.js' => $basePath . '/input.js',
            'jsunzip.js' => $basePath . '/jsunzip.js',
            'keyboard.js' => $basePath . '/keyboard.js',
            'keysym.js' => $basePath . '/keysym.js',
            'rfb.js' => $basePath . '/rfb.js',
            'websock.js' => $basePath . '/websock.js',
        ];
    }

    public static function mimeForExtension(string $extension): string
    {
        return [
            'html' => 'text/html',
            'js' => 'application/javascript',
            'css' => 'text/css',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'jpg' => 'image/jpeg',
            'ico' => 'image/x-icon',
            'json' => 'application/json',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
        ][$extension] ?? 'application/octet-stream';
    }
}
