<?php

namespace App\Services;

final class BmcProxyContext
{
    public int $serverId;
    public string $path;
    public string $panelHost;
    public string $panelOrigin;
    public array $server;
    public array $bmc;
    public string $bmcIp;
    public string $cookieName;
    public string $sessionId;
    public string $csrfToken;
    public string $csrfHeaderName;
    public array $cookieMap;
    public array $viewerStorage;

    public function __construct(
        int $serverId,
        string $path,
        string $panelHost,
        string $panelOrigin,
        array $server,
        array $bmc,
        string $bmcIp,
        string $cookieName,
        string $sessionId,
        string $csrfToken,
        string $csrfHeaderName,
        array $cookieMap,
        array $viewerStorage
    ) {
        $this->serverId = $serverId;
        $this->path = $path;
        $this->panelHost = $panelHost;
        $this->panelOrigin = $panelOrigin;
        $this->server = $server;
        $this->bmc = $bmc;
        $this->bmcIp = $bmcIp;
        $this->cookieName = $cookieName;
        $this->sessionId = $sessionId;
        $this->csrfToken = $csrfToken;
        $this->csrfHeaderName = $csrfHeaderName;
        $this->cookieMap = $cookieMap;
        $this->viewerStorage = $viewerStorage;
    }
}
