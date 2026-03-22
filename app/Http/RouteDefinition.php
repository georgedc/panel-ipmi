<?php

namespace App\Http;

class RouteDefinition
{
    private array $middleware = [];

    public function __construct(
        private Router $router,
        private string $method,
        private string $path
    ) {
    }

    public function middleware(string|array $middleware): self
    {
        $items = is_array($middleware) ? $middleware : [$middleware];
        $this->middleware = array_values(array_unique(array_merge($this->middleware, $items)));
        $this->router->setRouteMiddleware($this->method, $this->path, $this->middleware);
        return $this;
    }
}
