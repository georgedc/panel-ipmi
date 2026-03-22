<?php

function appSupportedLocales(): array {
    return ['en', 'es'];
}

function normalizeAppLocale(?string $locale): string {
    $locale = strtolower(trim((string) $locale));
    return in_array($locale, appSupportedLocales(), true) ? $locale : 'en';
}

function setAppLocale(?string $locale): void {
    $_SESSION['app_locale'] = normalizeAppLocale($locale);
}

function appConfiguredLocale(): string {
    static $resolved = null;
    if (is_string($resolved) && $resolved !== '') {
        return $resolved;
    }

    $fallback = normalizeAppLocale(getEnvValue('APP_LOCALE', 'en'));

    if (!class_exists('Database')) {
        $resolved = $fallback;
        return $resolved;
    }

    try {
        $db = Database::getInstance();
        $row = $db->fetch('SELECT value FROM settings WHERE name = ?', ['default_language']);
        $resolved = normalizeAppLocale((string) ($row['value'] ?? $fallback));
        return $resolved;
    } catch (Throwable) {
        $resolved = $fallback;
        return $resolved;
    }
}

function appLocale(): string {
    $sessionLocale = $_SESSION['app_locale'] ?? null;
    if (is_string($sessionLocale) && $sessionLocale !== '') {
        return normalizeAppLocale($sessionLocale);
    }

    $locale = appConfiguredLocale();
    $_SESSION['app_locale'] = $locale;
    return $locale;
}

function appTranslations(string $locale): array {
    static $cache = [];
    $locale = normalizeAppLocale($locale);
    if (isset($cache[$locale])) {
        return $cache[$locale];
    }

    $file = __DIR__ . '/lang/' . $locale . '.php';
    $translations = is_file($file) ? require $file : [];
    $cache[$locale] = is_array($translations) ? $translations : [];
    return $cache[$locale];
}


function appLocaleUrl(string $locale): string {
    $query = $_GET;
    $query['lang'] = normalizeAppLocale($locale);
    $path = strtok($_SERVER['REQUEST_URI'] ?? '', '?') ?: '';
    return $path . '?' . http_build_query($query);
}

function __(string $key, array $replace = []): string {
    $translations = appTranslations(appLocale());
    $fallback = appTranslations('en');
    $text = $translations[$key] ?? $fallback[$key] ?? $key;

    foreach ($replace as $name => $value) {
        $text = str_replace(':' . $name, (string) $value, $text);
    }

    return $text;
}
