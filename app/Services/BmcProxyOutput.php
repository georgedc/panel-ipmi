<?php

namespace App\Services;

use App\Http\Response;

final class BmcProxyOutput
{
    public static function sendText(string $body, string $contentType, int $status = 200): Response
    {
        return Response::make($body, $status, [
            'Content-Type' => $contentType,
        ]);
    }

    public static function sendFile(string $path, string $contentType, int $status = 200): Response
    {
        return Response::make((string) file_get_contents($path), $status, [
            'Content-Type' => $contentType,
        ]);
    }

    public static function finalize(string $path, string $body, ?string $contentType, int $httpCode): Response
    {
        $resolvedType = $contentType;
        if (!$resolvedType) {
            $ext = pathinfo((string) parse_url($path, PHP_URL_PATH), PATHINFO_EXTENSION);
            $resolvedType = BmcProxySupport::mimeForExtension($ext);
        }

        return Response::make($body, $httpCode, [
            'Content-Type' => $resolvedType,
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }
}
