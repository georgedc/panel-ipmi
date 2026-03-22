<?php

namespace App\Services;

final class BmcProxyHttpResult
{
    public int $status;
    public string $rawHeaders;
    public string $body;
    public string $contentType;
    public bool $ok;
    public int $curlErrno;

    public function __construct(
        int $status,
        string $rawHeaders,
        string $body,
        string $contentType,
        bool $ok,
        int $curlErrno = 0
    ) {
        $this->status = $status;
        $this->rawHeaders = $rawHeaders;
        $this->body = $body;
        $this->contentType = $contentType;
        $this->ok = $ok;
        $this->curlErrno = $curlErrno;
    }
}
