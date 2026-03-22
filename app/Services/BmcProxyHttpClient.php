<?php

namespace App\Services;

final class BmcProxyHttpClient
{
    public function execute(
        string $url,
        string $method,
        array $headers,
        ?string $body,
        int $connectTimeout,
        int $timeout,
        int $retryAttempts
    ): BmcProxyHttpResult {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_ENCODING => '',
            CURLOPT_CONNECTTIMEOUT => $connectTimeout,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method,
        ]);

        if ($body !== null && in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        $curlErrno = curl_errno($ch);

        if ($response === false && in_array($curlErrno, [7, 28, 52, 56], true)) {
            for ($attempt = 1; $attempt < $retryAttempts; $attempt++) {
                usleep((int) (250000 * $attempt));
                $response = curl_exec($ch);
                $curlErrno = curl_errno($ch);
                if ($response !== false) {
                    break;
                }
            }
        }

        if ($response === false) {
            curl_close($ch);
            return new BmcProxyHttpResult(0, '', '', '', false, $curlErrno);
        }

        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $rawHeaders = substr($response, 0, $headerSize);
        $responseBody = substr($response, $headerSize);
        $contentType = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        return new BmcProxyHttpResult(
            $status,
            $rawHeaders,
            $responseBody,
            $contentType,
            true
        );
    }

    public function get(string $url, array $headers, int $timeout = 20): BmcProxyHttpResult
    {
        return $this->execute($url, 'GET', $headers, null, 8, $timeout, 1);
    }

    public function sendWithBody(string $url, string $method, array $headers, string $body, int $timeout = 20): BmcProxyHttpResult
    {
        return $this->execute($url, $method, $headers, $body, 8, $timeout, 1);
    }
}
