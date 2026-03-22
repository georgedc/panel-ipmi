<?php

namespace App\Controllers;

use App\Http\Request;
use App\Services\AuthService;
use App\Services\SettingsManager;
use Exception;

class SettingsController extends Controller
{
    public function __construct(private ?SettingsManager $settings = null, private ?AuthService $authService = null)
    {
        $this->settings ??= new SettingsManager();
        $this->authService ??= new AuthService();
    }

    public function index(Request $request)
    {
        $this->settings->ensureTable();

        return $this->view('settings/index', [
            'title' => 'Settings',
            'settings' => $this->settings->all(),
            'currentUser' => $this->authService->currentUser(),
            'tfaSecret' => $this->settings->userTfaSecret((int) (($this->authService->currentUser()['id'] ?? 0))),
            'availableThemes' => function_exists('availableThemes') ? availableThemes() : ['default'],
            'flash_success' => $this->pullFlash('mvc_settings_success'),
            'flash_error' => $this->pullFlash('mvc_settings_error'),
        ]);
    }

    public function update(Request $request)
    {
        \App\Http\Csrf::verifyRequest();

        $this->settings->ensureTable();
        $settings = $this->settings->all();
        $action = (string) $request->input('action', '');

        try {
            $message = match ($action) {
                'update_api_settings' => $this->settings->updateApiSettings($request->all(), $settings),
                'update_tfa_settings' => $this->settings->updateTfaSettings($request->all(), $settings),
                'update_security_settings' => $this->settings->updateSecuritySettings($request->all(), $settings),
                'update_maintenance' => $this->settings->updateMaintenance($request->all(), $settings),
                'update_localization' => $this->settings->updateLocalization($request->all(), $settings),
                'update_theme' => $this->settings->updateTheme($request->all(), $settings),
                'generate_tfa_secret' => $this->generateSecretMessage((int) (($this->authService->currentUser()['id'] ?? 0))),
                default => throw new Exception('Invalid action.'),
            };

            $this->flash('mvc_settings_success', $message);
            $this->clearFlash('mvc_settings_error');
        } catch (Exception $e) {
            $this->clearFlash('mvc_settings_success');
            $this->flash('mvc_settings_error', $e->getMessage());
        }

        return $this->redirect(routeUrl('/settings'));
    }

    private function generateSecretMessage(int $userId): string
    {
        $this->settings->generateTfaSecret($userId);
        return __('settings.tfa_secret_generated');
    }
}
