<?php

namespace App\Controllers;

use App\Http\Response;
use App\Services\AuthService;
use App\Views\View;

abstract class Controller
{
    private ?AuthService $authService = null;

    protected function view(string $template, array $data = [], int $status = 200): Response
    {
        return Response::make(View::render($template, $data), $status);
    }

    protected function json(array $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function redirect(string $url, int $status = 302): Response
    {
        return Response::redirect($url, $status);
    }

    protected function auth(): AuthService
    {
        return $this->authService ??= new AuthService();
    }

    protected function flash(string $key, string $value): void
    {
        $_SESSION[$key] = $value;
    }

    protected function clearFlash(string $key): void
    {
        unset($_SESSION[$key]);
    }

    protected function pullFlash(string $key): string
    {
        $value = (string) ($_SESSION[$key] ?? '');
        unset($_SESSION[$key]);
        return $value;
    }

    protected function requireLogin(): ?Response
    {
        if (!$this->auth()->isLoggedIn()) {
            return $this->redirect(routeUrl('/login'));
        }

        return null;
    }

    protected function requireAdmin(
        string $title = 'Access denied',
        ?string $message = null,
        string $backUrl = ''
    ): ?Response {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }
        if (!$this->auth()->isAdmin()) {
            return $this->view('errors/simple', [
                'title' => $title,
                'heading' => __('app.access_denied'),
                'message' => $message ?? __('app.admin_only_section'),
                'back_url' => $backUrl !== '' ? $backUrl : routeUrl('/dashboard'),
            ], 403);
        }

        return null;
    }
}
