<?php
/**
 * PHPStan bootstrap: declara las constantes globales que config.php define
 * en tiempo de ejecución, para que el análisis estático las reconozca.
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'ipmi_panel');
define('DB_USER', 'user');
define('DB_PASS', '');
define('ENCRYPTION_KEY', 'cGxhY2Vob2xkZXJfa2V5X2Zvcl9waHBzdGFu');
define('APP_URL', '');
define('APP_NAME', 'IPMI Control Panel');
define('APP_VERSION', '1.0.0');
define('APP_THEME', 'default');
define('ROOT_PATH', __DIR__);
define('LOG_FILE', __DIR__ . '/logs/app.log');
define('HASH_COST', 12);
define('SESSION_LIFETIME', 3600);
define('SESSION_NAME', 'ipmi_session');
// LOG_ACTIONS is optional; not defined here so PHPStan respects the defined() guard
