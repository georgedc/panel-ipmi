<?php

namespace App\Controllers;

use App\Http\Request;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(private ?AuthService $authService = null)
    {
        $this->authService ??= new AuthService();
    }

    public function me(Request $request)
    {
        $user = $this->authService->currentUser();
        if (!$user) {
            return $this->json(['authenticated' => false], 401);
        }

        return $this->json([
            'authenticated' => true,
            'user' => $user,
        ]);
    }

    public function login(Request $request)
    {
        if ($this->authService->isLoggedIn()) {
            return $this->redirect(routeUrl('/dashboard'));
        }

        return $this->view('auth/login', [
            'title' => __('login.title'),
            'error' => $this->pullFlash('mvc_login_error'),
        ]);
    }

    public function authenticate(Request $request)
    {
        \App\Http\Csrf::verifyRequest();

        $result = $this->authService->attemptLogin(
            (string) $request->input('username', ''),
            (string) $request->input('password', '')
        );

        if (!$result['ok']) {
            $this->flash('mvc_login_error', (string) ($result['error'] ?? __('login.invalid_credentials')));
            return $this->redirect(routeUrl('/login'));
        }

        if (!empty($result['requires_tfa'])) {
            return $this->redirect(routeUrl('/two-factor'));
        }

        return $this->redirect(routeUrl('/dashboard'));
    }

    public function twoFactor(Request $request)
    {
        if (!$this->authService->hasPendingTwoFactor()) {
            return $this->redirect(routeUrl('/login'));
        }

        return $this->view('auth/two_factor', [
            'title' => __('tfa.title'),
            'error' => $this->pullFlash('mvc_tfa_error'),
            'user' => $this->authService->currentTwoFactorUser(),
        ]);
    }

    public function verifyTwoFactor(Request $request)
    {
        \App\Http\Csrf::verifyRequest();

        $result = $this->authService->verifyTwoFactorCode((string) $request->input('code', ''));
        if (!$result['ok']) {
            $this->flash('mvc_tfa_error', (string) ($result['error'] ?? __('tfa.invalid_code')));
            return $this->redirect(routeUrl(!empty($result['missing']) ? '/login' : '/two-factor'));
        }

        return $this->redirect(routeUrl('/dashboard'));
    }

    public function logout(Request $request)
    {
        $this->authService->logout();
        return $this->redirect(routeUrl('/login'));
    }
}
