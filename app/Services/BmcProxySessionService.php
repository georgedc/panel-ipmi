<?php

namespace App\Services;

class BmcProxySessionService
{
    public function refreshAfterIpmiRedirect(
        string $cookieName,
        string $path,
        string $body,
        int $serverId,
        string $cookieHeader,
        string $referer,
        string $panelOrigin,
        string $bmcIp,
        string $targetUrl,
        string $method,
        array $cookieMap,
        string $csrfHeaderName,
        string $csrfToken,
        ?string $requestBody
    ): array {
        if (
            $cookieName !== 'SID'
            || $path !== '/cgi/ipmi.cgi'
            || strpos($body, 'URL=/') === false
        ) {
            return [
                'handled' => false,
                'cookie_map' => $cookieMap,
                'cookie_header' => $cookieHeader,
                'csrf_header_name' => $csrfHeaderName,
                'csrf_token' => $csrfToken,
            ];
        }

        $refreshHeaders = [
            'Cookie: ' . $cookieHeader,
            'Referer: ' . $referer,
            'Origin: ' . $panelOrigin,
            'X-Requested-With: XMLHttpRequest',
        ];

        $httpClient = new BmcProxyHttpClient();
        $refreshResult = $httpClient->get(
            "https://{$bmcIp}/cgi/url_redirect.cgi?url_name=man_ikvm_html5",
            $refreshHeaders,
            15
        );

        $refreshHeaderRaw = $refreshResult->rawHeaders;
        $refreshBody = $refreshResult->body;

        if (!preg_match('/SmcCsrfInsert \\("([^"]+)", "([^"]+)"\\)/', $refreshBody, $match)) {
            return [
                'handled' => false,
                'cookie_map' => $cookieMap,
                'cookie_header' => $cookieHeader,
                'csrf_header_name' => $csrfHeaderName,
                'csrf_token' => $csrfToken,
            ];
        }

        $csrfHeaderName = $match[1];
        $csrfToken = $match[2];

        if (preg_match_all('/Set-Cookie:\\s*([^=;]+)=([^;]*)/i', $refreshHeaderRaw, $cookieMatches, PREG_SET_ORDER)) {
            BmcProxySupport::reopenSession();
            foreach ($cookieMatches as $cookieMatch) {
                $cookieMap[$cookieMatch[1]] = $cookieMatch[2];
            }

            $pairs = [];
            foreach ($cookieMap as $key => $value) {
                if ($key === '' || $value === '') {
                    continue;
                }
                $pairs[] = $key . '=' . $value;
            }
            if (!empty($pairs)) {
                $cookieHeader = implode('; ', $pairs);
            }
            $_SESSION['bmc_sessions'][$serverId]['cookies'] = $cookieMap;
        }

        $_SESSION['bmc_sessions'][$serverId]['csrf_header_name'] = $csrfHeaderName;
        $_SESSION['bmc_sessions'][$serverId]['csrf_token'] = $csrfToken;

        $retryHeaders = [
            'Cookie: ' . $cookieHeader,
            $csrfHeaderName . ': ' . $csrfToken,
            'Referer: ' . $referer,
            'Origin: ' . $panelOrigin,
            'X-Requested-With: XMLHttpRequest',
        ];
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $retryHeaders[] = 'Content-Type: ' . $_SERVER['CONTENT_TYPE'];
        }

        $retryResult = $httpClient->sendWithBody(
            $targetUrl,
            $method,
            $retryHeaders,
            (string) $requestBody,
            20
        );

        return [
            'handled' => $retryResult->ok,
            'cookie_map' => $cookieMap,
            'cookie_header' => $cookieHeader,
            'csrf_header_name' => $csrfHeaderName,
            'csrf_token' => $csrfToken,
            'result' => $retryResult,
        ];
    }
}
