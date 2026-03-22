<?php

namespace App\Http;

use App\Services\AuthService;
use Closure;
use RuntimeException;

class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler): RouteDefinition
    {
        return $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): RouteDefinition
    {
        return $this->add('POST', $path, $handler);
    }

    public function match(array $methods, string $path, callable|array $handler): RouteDefinition
    {
        $last = null;
        foreach ($methods as $method) {
            $last = $this->add(strtoupper($method), $path, $handler);
        }
        return $last ?? new RouteDefinition($this, 'GET', $this->normalize($path));
    }

    private function add(string $method, string $path, callable|array $handler): RouteDefinition
    {
        $normalized = $this->normalize($path);
        $this->routes[$method][$normalized] = [
            'handler' => $handler,
            'middleware' => [],
        ];
        return new RouteDefinition($this, $method, $normalized);
    }

    public function setRouteMiddleware(string $method, string $path, array $middleware): void
    {
        $normalized = $this->normalize($path);
        if (!isset($this->routes[$method][$normalized])) {
            throw new RuntimeException('Route is not registered.');
        }
        $this->routes[$method][$normalized]['middleware'] = $middleware;
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path = $this->normalize($request->path());
        $route = $this->routes[$method][$path] ?? null;

        if (!$route) {
            return Response::make('Not Found', 404);
        }

        if ($middlewareResponse = $this->runMiddleware($route['middleware'] ?? [], $request)) {
            return $middlewareResponse;
        }

        $handler = $route['handler'];

        if (is_array($handler) && count($handler) === 2) {
            [$class, $action] = $handler;
            $instance = new $class();
            $result = $instance->{$action}($request);
        } else {
            $result = $handler($request);
        }

        if ($result instanceof Response) {
            return $result;
        }

        if (is_array($result)) {
            return Response::json($result);
        }

        if (is_string($result)) {
            return Response::make($result);
        }

        if ($result instanceof Closure) {
            throw new RuntimeException('Route handler returned unsupported closure response.');
        }

        return Response::make('');
    }

    private function runMiddleware(array $middleware, Request $request): ?Response
    {
        if ($middleware === []) {
            return null;
        }

        $auth = new AuthService();

        foreach ($middleware as $name) {
            if ($name === 'auth' && !$auth->isLoggedIn()) {
                if ($request->isAjax() || str_starts_with($request->path(), '/api/')) {
                    return Response::json(['error' => __('app.auth_required')], 401);
                }
                return Response::redirect(routeUrl('/login'));
            }

            if ($name === 'admin') {
                if (!$auth->isLoggedIn()) {
                    if ($request->isAjax() || str_starts_with($request->path(), '/api/')) {
                        return Response::json(['error' => __('app.auth_required')], 401);
                    }
                    return Response::redirect(routeUrl('/login'));
                }
                if (!$auth->isAdmin()) {
                    if ($request->isAjax() || str_starts_with($request->path(), '/api/')) {
                        return Response::json(['error' => __('app.access_denied')], 403);
                    }
                    return Response::make(__('app.admin_only_section'), 403);
                }
            }
        }

        return null;
    }

    private function normalize(string $path): string
    {
        $normalized = '/' . trim($path, '/');
        return $normalized === '/' ? '/' : rtrim($normalized, '/');
    }
}
