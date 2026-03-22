<?php

namespace App\Services;

use App\Database\Database;
use Exception;

class SettingsManager
{
    public function __construct(private ?Database $db = null)
    {
        $this->db ??= Database::getInstance();
    }

    public function ensureTable(): void
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) UNIQUE NOT NULL,
            value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
    }

    public function get(string $name, string $default = ''): string
    {
        try {
            $result = $this->db->fetch('SELECT value FROM settings WHERE name = ?', [$name]);
            return (string) ($result['value'] ?? $default);
        } catch (Exception) {
            return $default;
        }
    }

    public function set(string $name, string $value): bool
    {
        try {
            $existing = $this->db->fetch('SELECT name FROM settings WHERE name = ?', [$name]);
            if ($existing) {
                $this->db->update('settings', ['value' => $value], 'name = ?', [$name]);
            } else {
                $this->db->insert('settings', ['name' => $name, 'value' => $value]);
            }
            return true;
        } catch (Exception $e) {
            error_log('Error setting config ' . $name . ': ' . $e->getMessage());
            return false;
        }
    }

    public function all(): array
    {
        return [
            'api_enabled' => $this->get('api_enabled', '0'),
            'api_token' => $this->get('api_token', ''),
            'tfa_enabled_admin' => $this->get('tfa_enabled_admin', '0'),
            'session_timeout' => $this->get('session_timeout', '3600'),
            'max_login_attempts' => $this->get('max_login_attempts', '5'),
            'maintenance_mode' => $this->get('maintenance_mode', '0'),
            'default_language' => $this->get('default_language', 'en'),
            'app_theme' => $this->get('app_theme', 'default'),
        ];
    }

    public function updateApiSettings(array $input, array &$settings): string
    {
        $apiEnabled = isset($input['api_enabled']) ? '1' : '0';
        $regenerateToken = isset($input['regenerate_token']);

        if (!$this->set('api_enabled', $apiEnabled)) {
            throw new Exception('Error saving API state');
        }

        if ($regenerateToken || empty($settings['api_token'])) {
            $newToken = bin2hex(random_bytes(32));
            if (!$this->set('api_token', $newToken)) {
                throw new Exception('Error generating API token');
            }
            $settings['api_token'] = $newToken;
        }

        $settings['api_enabled'] = $apiEnabled;
        return __('settings.api') . ' updated successfully.';
    }

    public function updateTfaSettings(array $input, array &$settings): string
    {
        $tfaEnabled = isset($input['tfa_enabled_admin']) ? '1' : '0';

        if (!$this->set('tfa_enabled_admin', $tfaEnabled)) {
            throw new Exception('Error saving 2FA settings.');
        }

        $settings['tfa_enabled_admin'] = $tfaEnabled;
        return __('settings.tfa') . ' updated successfully.';
    }

    public function updateSecuritySettings(array $input, array &$settings): string
    {
        $sessionTimeout = (int) ($input['session_timeout'] ?? 3600);
        $maxLoginAttempts = (int) ($input['max_login_attempts'] ?? 5);

        if ($sessionTimeout < 300 || $sessionTimeout > 86400) {
            throw new Exception('Session timeout must be between 5 minutes and 24 hours.');
        }
        if ($maxLoginAttempts < 1 || $maxLoginAttempts > 20) {
            throw new Exception('Maximum login attempts must be between 1 and 20.');
        }

        if (!$this->set('session_timeout', (string) $sessionTimeout)) {
            throw new Exception('Error saving session timeout.');
        }
        if (!$this->set('max_login_attempts', (string) $maxLoginAttempts)) {
            throw new Exception('Error saving maximum login attempts.');
        }

        $settings['session_timeout'] = $sessionTimeout;
        $settings['max_login_attempts'] = $maxLoginAttempts;
        return __('settings.security') . ' updated successfully.';
    }

    public function updateMaintenance(array $input, array &$settings): string
    {
        $maintenanceMode = isset($input['maintenance_mode']) ? '1' : '0';

        if (!$this->set('maintenance_mode', $maintenanceMode)) {
            throw new Exception('Error saving maintenance mode.');
        }

        $settings['maintenance_mode'] = $maintenanceMode;
        return __('settings.maintenance_mode') . ' updated successfully.';
    }

    public function updateLocalization(array $input, array &$settings): string
    {
        $language = normalizeAppLocale((string) ($input['default_language'] ?? 'en'));

        if (!$this->set('default_language', $language)) {
            throw new Exception('Error saving default language.');
        }

        $settings['default_language'] = $language;
        setAppLocale($language);
        return __('settings.language') . ' updated successfully.';
    }

    public function generateTfaSecret(int $userId): string
    {
        $secret = strtoupper(substr(str_replace(['/', '+', '='], '', base64_encode(random_bytes(20))), 0, 16));
        $result = $this->db->update('users', ['tfa_secret' => $secret], 'id = ?', [$userId]);
        if (!$result) {
            throw new Exception('Error saving the 2FA secret.');
        }

        return $secret;
    }

    public function userTfaSecret(int $userId): string
    {
        $row = $this->db->fetch('SELECT tfa_secret FROM users WHERE id = ?', [$userId]);
        return (string) ($row['tfa_secret'] ?? '');
    }

    public function updateTheme(array $input, array &$settings): string
    {
        $allowed = function_exists('availableThemes') ? availableThemes() : ['default', 'panel'];
        $theme = (string) ($input['app_theme'] ?? 'default');

        if (!in_array($theme, $allowed, true)) {
            throw new Exception('Invalid theme selected.');
        }

        if (!$this->set('app_theme', $theme)) {
            throw new Exception('Error saving theme setting.');
        }

        $settings['app_theme'] = $theme;
        return 'Theme updated to "' . $theme . '".';
    }
}
