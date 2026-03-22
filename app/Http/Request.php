<?php

namespace App\Http;

class Request
{
    public function __construct(
        private array $get,
        private array $post,
        private array $server,
        private array $files,
        private array $cookie,
        private array $session
    ) {
    }

    public static function capture(): self
    {
        return new self($_GET, $_POST, $_SERVER, $_FILES, $_COOKIE, $_SESSION ?? []);
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        if (isset($this->get['route']) && is_string($this->get['route']) && $this->get['route'] !== '') {
            return '/' . ltrim($this->get['route'], '/');
        }

        $uri = $this->server['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        if (function_exists('appBasePath')) {
            $basePath = appBasePath();
            if ($basePath !== '' && $path === $basePath) {
                return '/';
            }
            if ($basePath !== '' && str_starts_with($path, $basePath . '/')) {
                $path = substr($path, strlen($basePath)) ?: '/';
            }
        }
        return '/' . ltrim($path, '/');
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->get[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    public function session(): array
    {
        return $this->session;
    }

    public function files(): array
    {
        return $this->files;
    }

    public function cookie(): array
    {
        return $this->cookie;
    }

    public function isAjax(): bool
    {
        return strtolower((string) ($this->server['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
    }
}
