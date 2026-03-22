<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/Database.php';
require_once dirname(__DIR__) . '/includes/Auth.php';
require_once dirname(__DIR__) . '/includes/Logger.php';
require_once dirname(__DIR__) . '/includes/CSRF.php';
require_once dirname(__DIR__) . '/includes/IPMI.php';

use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use App\Views\View;

$router = new Router();
require dirname(__DIR__) . '/routes/web.php';

return static function () use ($router): void {
    $request = Request::capture();
    try {
        $response = $router->dispatch($request);
    } catch (Throwable $e) {
        error_log('MVC request failed: ' . $e->getMessage());
        if ($request->isAjax() || str_starts_with($request->path(), '/api/')) {
            $response = Response::json([
                'error' => 'The request could not be completed.',
            ], 500);
        } else {
            $response = Response::make(View::render('errors/simple', [
                'title' => 'Error',
                'heading' => 'Request error',
                'message' => 'The request could not be completed right now.',
                'back_url' => routeUrl('/dashboard'),
            ]), 500);
        }
    }
    $response->send();
};
