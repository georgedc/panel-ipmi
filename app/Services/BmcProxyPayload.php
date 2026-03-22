<?php

namespace App\Services;

use App\Http\Response;
use Throwable;

class BmcProxyPayload
{
    public function handle(array $query, array $serverState): Response
    {
        $serverId = 0;

        try {
            $context = (new BmcProxyContextFactory())->create($query, $serverState);
            $serverId = $context->serverId;
            $path = $context->path;
            $bmc = $context->bmc;
            $bmcIp = $context->bmcIp;
            $cookieName = $context->cookieName;
            $sessionId = $context->sessionId;
            $csrfToken = $context->csrfToken;
            $csrfHeaderName = $context->csrfHeaderName;
            $cookieMap = $context->cookieMap;
            $viewerStorage = $context->viewerStorage;
            $panelHost = $context->panelHost;
            $panelOrigin = $context->panelOrigin;
            $method = $serverState['REQUEST_METHOD'] ?? 'GET';

            session_write_close();

            if ($cookieName === 'SID' && $method === 'GET') {
                $assetResponse = BmcProxyAssetService::tryServeSupermicroLocalAsset($cookieName, $path);
                if ($assetResponse instanceof Response) {
                    return $assetResponse;
                }
                $navUiResponse = BmcProxyAssetService::tryServeSupermicroNavUi($cookieName, $path, (string) $sessionId, (string) $bmcIp);
                if ($navUiResponse instanceof Response) {
                    return $navUiResponse;
                }
            }

            $this->assertOrigin($method, $serverState, $panelOrigin);

            $targetUrl = $this->buildTargetUrl($bmcIp, $path, $query);
            $requestBody = in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)
                ? file_get_contents('php://input')
                : null;
            $cookieHeader = $this->buildCookieHeader($cookieName, $sessionId, $cookieMap);
            $headers = $this->buildHeaders(
                $serverId,
                $path,
                $method,
                $serverState,
                $cookieHeader,
                $csrfHeaderName,
                $csrfToken,
                $panelOrigin
            );

            $isSlowViewerRequest = $this->isSlowViewerRequest($cookieName, $path, $method);
            $result = (new BmcProxyHttpClient())->execute(
                $targetUrl,
                $method,
                $headers,
                $requestBody,
                $isSlowViewerRequest ? 12 : 5,
                $isSlowViewerRequest ? 75 : 30,
                $isSlowViewerRequest ? 3 : 2
            );

            if (!$result->ok) {
                if ($isSlowViewerRequest && $result->curlErrno === 28) {
                    BmcProxySupport::fail('BMC viewer request timed out. Try again in a moment.', 504);
                }
                BmcProxySupport::fail('BMC unreachable right now.', 502);
            }

            $httpCode = $result->status;
            $body = $result->body;
            $contentType = $result->contentType;
            $referer = $this->buildReferer($serverId, $path, $cookieName, $panelOrigin);

            if (preg_match('/SmcCsrfInsert \\("([^"]+)", "([^"]+)"\\)/', $body, $csrfMatch)) {
                BmcProxySupport::reopenSession();
                if (!isset($_SESSION['bmc_sessions'][$serverId])) {
                    $_SESSION['bmc_sessions'][$serverId] = $bmc;
                }
                $_SESSION['bmc_sessions'][$serverId]['csrf_header_name'] = $csrfMatch[1];
                $_SESSION['bmc_sessions'][$serverId]['csrf_token'] = $csrfMatch[2];
                $bmc['csrf_header_name'] = $csrfMatch[1];
                $bmc['csrf_token'] = $csrfMatch[2];
                $csrfHeaderName = $csrfMatch[1];
                $csrfToken = $csrfMatch[2];
            }

            $refresh = (new BmcProxySessionService())->refreshAfterIpmiRedirect(
                $cookieName,
                $path,
                $body,
                $serverId,
                $cookieHeader,
                $referer,
                $panelOrigin,
                $bmcIp,
                $targetUrl,
                $method,
                $cookieMap,
                $csrfHeaderName,
                $csrfToken,
                $requestBody
            );
            $cookieMap = $refresh['cookie_map'];
            $cookieHeader = $refresh['cookie_header'];
            $csrfHeaderName = $refresh['csrf_header_name'];
            $csrfToken = $refresh['csrf_token'];
            if (!empty($refresh['handled']) && isset($refresh['result'])) {
                $httpCode = $refresh['result']->status;
                $body = $refresh['result']->body;
                $contentType = $refresh['result']->contentType;
            }

            [$httpCode, $contentType, $body] = BmcProxyFirmwareService::apply(
                $cookieName,
                $path,
                $serverId,
                $httpCode,
                (string) $contentType,
                $body
            );

            [$contentType, $body] = (new BmcProxyResponsePatchService())->apply(
                $cookieName,
                $path,
                $serverId,
                (string) $sessionId,
                (string) $bmcIp,
                $body,
                (string) $contentType
            );

            [$contentType, $body] = BmcProxyRewriteService::rewrite(
                $cookieName,
                $path,
                $serverId,
                $body,
                $contentType,
                $bmcIp,
                $panelHost,
                $viewerStorage,
                $bmc,
                $query
            );

            return BmcProxyOutput::finalize($path, $body, $contentType ?: null, $httpCode);
        } catch (BmcProxyException $e) {
            return Response::make($e->getMessage(), $e->getCode() > 0 ? $e->getCode() : 502, [
                'Content-Type' => 'text/plain; charset=UTF-8',
            ]);
        } catch (Throwable $e) {
            error_log('BMC proxy failed for server ' . $serverId . ': ' . $e->getMessage());
            return Response::make('BMC proxy unavailable right now.', 502, [
                'Content-Type' => 'text/plain; charset=UTF-8',
            ]);
        }
    }

    private function assertOrigin(string $method, array $serverState, string $panelOrigin): void
    {
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        $origin = (string) ($serverState['HTTP_ORIGIN'] ?? '');
        $referer = (string) ($serverState['HTTP_REFERER'] ?? '');
        $originAllowed = ($origin !== '' && stripos($origin, $panelOrigin) === 0);
        $refererAllowed = ($referer !== '' && stripos($referer, $panelOrigin . '/ipmi-panel/') === 0);

        if (!$originAllowed && !$refererAllowed) {
            BmcProxySupport::fail('Invalid request origin', 403);
        }
    }

    private function buildTargetUrl(string $bmcIp, string $path, array $query): string
    {
        $targetUrl = "https://{$bmcIp}{$path}";
        unset($query['server_id'], $query['path']);
        if (!empty($query)) {
            $targetUrl .= '?' . http_build_query($query);
        }
        return $targetUrl;
    }

    private function buildCookieHeader(string $cookieName, string $sessionId, array $cookieMap): string
    {
        $cookieHeader = "{$cookieName}={$sessionId}";
        if (empty($cookieMap)) {
            return $cookieHeader;
        }

        $pairs = [];
        foreach ($cookieMap as $key => $value) {
            if ($key === '' || $value === '') {
                continue;
            }
            $pairs[] = $key . '=' . $value;
        }

        return empty($pairs) ? $cookieHeader : implode('; ', $pairs);
    }

    private function buildHeaders(
        int $serverId,
        string $path,
        string $method,
        array $serverState,
        string $cookieHeader,
        string $csrfHeaderName,
        string $csrfToken,
        string $panelOrigin
    ): array {
        $headers = [
            'Cookie: ' . $cookieHeader,
            'Referer: ' . $this->buildReferer($serverId, $path, explode('=', $cookieHeader, 2)[0], $panelOrigin),
            'Origin: ' . $panelOrigin,
        ];

        if ($csrfToken !== '') {
            $headers[] = ($csrfHeaderName !== '' ? $csrfHeaderName : 'X-CSRFTOKEN') . ': ' . $csrfToken;
        }
        if (isset($serverState['CONTENT_TYPE'])) {
            $headers[] = 'Content-Type: ' . $serverState['CONTENT_TYPE'];
        }
        if (isset($serverState['HTTP_USER_AGENT'])) {
            $headers[] = 'User-Agent: ' . $serverState['HTTP_USER_AGENT'];
        }
        if (isset($serverState['HTTP_ACCEPT'])) {
            $headers[] = 'Accept: ' . $serverState['HTTP_ACCEPT'];
        }
        if (isset($serverState['HTTP_ACCEPT_LANGUAGE'])) {
            $headers[] = 'Accept-Language: ' . $serverState['HTTP_ACCEPT_LANGUAGE'];
        }
        if ($this->isApiLike($path, $method)) {
            $headers[] = 'X-Requested-With: XMLHttpRequest';
        }
        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $name => $value) {
                if (!is_string($name) || !is_string($value) || $value === '') {
                    continue;
                }
                $lowerName = strtolower($name);
                $isCsrfHeader = ($csrfHeaderName !== '' && strcasecmp($name, $csrfHeaderName) === 0)
                    || strpos($lowerName, 'csrf') !== false;
                if ($isCsrfHeader) {
                    $headers[] = $name . ': ' . $value;
                }
            }
        }
        return $headers;
    }

    private function buildReferer(int $serverId, string $path, string $cookieName, string $panelOrigin): string
    {
        if ($cookieName === 'SID') {
            return $panelOrigin . "/ipmi-panel/bmc/{$serverId}/cgi/url_redirect.cgi?url_name=man_ikvm_html5";
        }
        return $panelOrigin . '/ipmi-panel/bmc/' . $serverId . $path;
    }

    private function isApiLike(string $path, string $method): bool
    {
        return strpos($path, '/api/') === 0 || (substr($path, -4) === '.cgi' && $method !== 'GET');
    }

    private function isSlowViewerRequest(string $cookieName, string $path, string $method): bool
    {
        return $cookieName === 'SID'
            && $method === 'GET'
            && (
                preg_match('#^/(js|css|images|html|novnc)/#', $path)
                || $path === '/viewer.html'
                || $path === '/cgi/url_redirect.cgi'
                || $path === '/cgi/ipmi.cgi'
            );
    }
}
