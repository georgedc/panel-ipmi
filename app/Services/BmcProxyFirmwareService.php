<?php

namespace App\Services;

final class BmcProxyFirmwareService
{
    public static function apply(
        string $cookieName,
        string $path,
        int $serverId,
        int $httpCode,
        string $contentType,
        string $body
    ): array {
        if ($cookieName === 'QSESSIONID' && $path === '/api/configuration/runtime' && $httpCode === 200) {
            $runtimeData = json_decode($body, true);
            if (is_array($runtimeData)) {
                $requiredFlags = [
                    'LDAP' => 0,
                    'AD' => 0,
                    'RADIUS' => 0,
                    'SINGLE_PORT_APP' => 1,
                    'SD_MEDIA_SUPPORT' => 0,
                ];
                $existingFlags = [];
                foreach ($runtimeData as $entry) {
                    if (!is_array($entry) || !isset($entry['feature'])) {
                        continue;
                    }
                    $existingFlags[(string) $entry['feature']] = true;
                }
                foreach ($requiredFlags as $feature => $enabled) {
                    if (!isset($existingFlags[$feature])) {
                        $runtimeData[] = [
                            'feature' => $feature,
                            'enabled' => $enabled,
                        ];
                    }
                }
                $normalized = json_encode($runtimeData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                if (is_string($normalized)) {
                    $body = $normalized;
                    $contentType = 'application/json';
                }
            }
        }

        if ($cookieName === 'SID' && $path === '/cgi/upgrade_process.cgi') {
            $httpCode = 200;
            $contentType = 'text/xml; charset=utf-8';
            $body = "<?xml version=\"1.0\" encoding=\"utf-8\"?><root>0</root>";
        }

        if ($cookieName === 'SID' && $path === '/cgi/logout.cgi') {
            $httpCode = 200;
            $contentType = 'text/html; charset=utf-8';
            $body = '<!doctype html><html><head><meta charset="utf-8"><meta http-equiv="refresh" content="0;url=/ipmi-panel/bmc/' . $serverId . '/cgi/url_redirect.cgi?url_name=man_ikvm_html5"></head><body></body></html>';
        }

        if ($path === '/js/jquery-3.5.1.min.js' && $httpCode === 404) {
            $localJquery = ROOT_PATH . '/public/legacy/js/jquery-3.5.1.min.js';
            if (is_readable($localJquery)) {
                $body = (string) file_get_contents($localJquery);
                $httpCode = 200;
                $contentType = 'application/javascript';
            }
        }

        if ($path === '/js/lang/undefined/lang_str.js' && $httpCode === 404) {
            $body = 'var lang = window.lang || {};';
            $httpCode = 200;
            $contentType = 'application/javascript';
        }

        if ($cookieName === 'QSESSIONID' && $path === '/app/templates/layouts/main.html' && $httpCode === 404) {
            $body = '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ASRockRack IPMI</title><link rel="stylesheet" href="/styles.min.css"></head><body><div class="processing_bg_outer" id="processing_layout" style="display:none"><div class="processing_bg_inner"></div></div><div class="processing_img_outer" id="processing_image" style="display:none"><div><img class="processing_img_inner" src="images/loading.GIF"></div><div class="processing_content">Processing ... </div></div><main role="main" id="main"></main><script data-main="/app/main" src="/source.min.js"></script></body></html>';
            $httpCode = 200;
            $contentType = 'text/html; charset=utf-8';
        }

        return [$httpCode, $contentType, $body];
    }
}
