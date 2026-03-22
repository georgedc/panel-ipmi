<?php
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><?php echo htmlspecialchars(__('settings.page_title')); ?></h1>
        <p class="text-muted mb-0"><?php echo htmlspecialchars(__('mvc.settings_subtitle')); ?></p>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primary" href="<?php echo routeUrl('/dashboard'); ?>"><?php echo htmlspecialchars(__('app.dashboard')); ?></a>
    </div>
</div>
<?php if (!empty($flash_success)) :
    ?><div class="alert alert-success"><?php echo htmlspecialchars($flash_success); ?></div><?php
endif; ?>
<?php if (!empty($flash_error)) :
    ?><div class="alert alert-danger"><?php echo htmlspecialchars($flash_error); ?></div><?php
endif; ?>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="section-card h-100">
            <div class="section-head"><h3><?php echo htmlspecialchars(__('mvc.settings_language_title')); ?></h3></div>
            <div class="section-body">
                <p class="text-muted"><?php echo htmlspecialchars(__('mvc.settings_language_subtitle')); ?></p>
                <form method="post" action="<?php echo routeUrl('/settings/update'); ?>">
                    <?php echo \App\Http\Csrf::getHiddenField(); ?>
                    <input type="hidden" name="action" value="update_localization">
                    <div class="mb-3">
                        <label class="form-label"><?php echo htmlspecialchars(__('mvc.settings_default_language')); ?></label>
                        <select class="form-select" name="default_language">
                            <option value="en" <?php echo ((string) ($settings['default_language'] ?? 'en')) === 'en' ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('mvc.language_en')); ?></option>
                            <option value="es" <?php echo ((string) ($settings['default_language'] ?? 'en')) === 'es' ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('mvc.language_es')); ?></option>
                        </select>
                    </div>
                    <button class="btn btn-brand" type="submit"><?php echo htmlspecialchars(__('mvc.settings_save_language')); ?></button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="section-card h-100">
            <div class="section-head"><h3><?php echo htmlspecialchars(__('mvc.settings_theme_title')); ?></h3></div>
            <div class="section-body">
                <p class="text-muted"><?php echo htmlspecialchars(__('mvc.settings_theme_subtitle')); ?></p>
                <form method="post" action="<?php echo routeUrl('/settings/update'); ?>">
                    <?php echo \App\Http\Csrf::getHiddenField(); ?>
                    <input type="hidden" name="action" value="update_theme">
                    <div class="mb-3">
                        <label class="form-label"><?php echo htmlspecialchars(__('mvc.settings_active_theme')); ?></label>
                        <select class="form-select" name="app_theme">
                            <?php foreach (($availableThemes ?? ['default']) as $t): ?>
                                <option value="<?php echo htmlspecialchars($t); ?>" <?php echo ((string) ($settings['app_theme'] ?? 'default')) === $t ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(ucfirst($t)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-brand" type="submit"><?php echo htmlspecialchars(__('mvc.settings_save_theme')); ?></button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="section-card h-100">
            <div class="section-head"><h3><?php echo htmlspecialchars(__('settings.api')); ?></h3></div>
            <div class="section-body">
                <form method="post" action="<?php echo routeUrl('/settings/update'); ?>">
                    <?php echo \App\Http\Csrf::getHiddenField(); ?>
                    <input type="hidden" name="action" value="update_api_settings">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="api_enabled" <?php echo ((string) ($settings['api_enabled'] ?? '0')) === '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label"><?php echo htmlspecialchars(__('mvc.settings_api_enable')); ?></label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo htmlspecialchars(__('settings.global_api_token')); ?></label>
                        <input class="form-control font-monospace" type="text" value="<?php echo htmlspecialchars((string) ($settings['api_token'] ?? '')); ?>" readonly>
                        <div class="form-text"><?php echo htmlspecialchars(__('settings.api_global_help')); ?></div>
                        <div class="form-text"><?php echo htmlspecialchars(__('settings.api_internal_help')); ?></div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="regenerate_token" value="1" id="regenerate_token">
                        <label class="form-check-label" for="regenerate_token"><?php echo htmlspecialchars(__('mvc.settings_regen_global')); ?></label>
                    </div>
                    <button class="btn btn-brand" type="submit"><?php echo htmlspecialchars(__('mvc.settings_save_api')); ?></button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="section-card h-100">
            <div class="section-head"><h3><?php echo htmlspecialchars(__('settings.security')); ?></h3></div>
            <div class="section-body">
                <form method="post" action="<?php echo routeUrl('/settings/update'); ?>">
                    <?php echo \App\Http\Csrf::getHiddenField(); ?>
                    <input type="hidden" name="action" value="update_security_settings">
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('mvc.settings_session_timeout')); ?></label><input class="form-control" type="number" name="session_timeout" value="<?php echo htmlspecialchars((string) ($settings['session_timeout'] ?? '3600')); ?>" min="300" max="86400"></div>
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('mvc.settings_login_attempts')); ?></label><input class="form-control" type="number" name="max_login_attempts" value="<?php echo htmlspecialchars((string) ($settings['max_login_attempts'] ?? '5')); ?>" min="1" max="20"></div>
                    <button class="btn btn-brand" type="submit"><?php echo htmlspecialchars(__('mvc.settings_save_security')); ?></button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="section-card h-100">
            <div class="section-head"><h3><?php echo htmlspecialchars(__('settings.tfa')); ?></h3></div>
            <div class="section-body d-grid gap-3">
                <form method="post" action="<?php echo routeUrl('/settings/update'); ?>">
                    <?php echo \App\Http\Csrf::getHiddenField(); ?>
                    <input type="hidden" name="action" value="update_tfa_settings">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="tfa_enabled_admin" <?php echo ((string) ($settings['tfa_enabled_admin'] ?? '0')) === '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label"><?php echo htmlspecialchars(__('mvc.settings_enable_admin_tfa')); ?></label>
                    </div>
                    <button class="btn btn-brand" type="submit"><?php echo htmlspecialchars(__('mvc.settings_save_tfa')); ?></button>
                </form>
                <form method="post" action="<?php echo routeUrl('/settings/update'); ?>">
                    <?php echo \App\Http\Csrf::getHiddenField(); ?>
                    <input type="hidden" name="action" value="generate_tfa_secret">
                    <button class="btn btn-outline-secondary" type="submit"><?php echo htmlspecialchars(__('mvc.settings_generate_tfa')); ?></button>
                </form>
                <div class="alert alert-info mb-0">
                    <strong><?php echo htmlspecialchars(__('mvc.settings_tfa_personal')); ?></strong><br>
                    <?php if (!empty($tfaSecret)) : ?>
                        <span><?php echo htmlspecialchars(__('mvc.settings_tfa_secret')); ?>:</span>
                        <code class="d-block mt-2"><?php echo htmlspecialchars((string) $tfaSecret); ?></code>
                    <?php else : ?>
                        <?php echo htmlspecialchars(__('mvc.settings_tfa_empty')); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="section-card h-100">
            <div class="section-head"><h3><?php echo htmlspecialchars(__('settings.maintenance_mode')); ?></h3></div>
            <div class="section-body">
                <form method="post" action="<?php echo routeUrl('/settings/update'); ?>">
                    <?php echo \App\Http\Csrf::getHiddenField(); ?>
                    <input type="hidden" name="action" value="update_maintenance">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="maintenance_mode" <?php echo ((string) ($settings['maintenance_mode'] ?? '0')) === '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label"><?php echo htmlspecialchars(__('mvc.settings_enable_maintenance')); ?></label>
                    </div>
                    <button class="btn btn-brand" type="submit"><?php echo htmlspecialchars(__('mvc.settings_save_maintenance')); ?></button>
                </form>
                <?php if ((string) ($settings['maintenance_mode'] ?? '0') === '1') : ?>
                    <div class="alert alert-warning mt-3 mb-0"><?php echo htmlspecialchars(__('settings.maintenance_active')); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="section-card h-100">
            <div class="section-head"><h3><?php echo htmlspecialchars(__('mvc.settings_system_title')); ?></h3></div>
            <div class="section-body">
                <div class="server-meta">
                    <div class="meta-box"><span><?php echo htmlspecialchars(__('settings.version')); ?></span><strong><?php echo htmlspecialchars(APP_VERSION); ?></strong></div>
                    <div class="meta-box"><span><?php echo htmlspecialchars(__('settings.php_version')); ?></span><strong><?php echo htmlspecialchars((string) phpversion()); ?></strong></div>
                    <div class="meta-box"><span><?php echo htmlspecialchars(__('mvc.settings_current_user')); ?></span><strong><?php echo htmlspecialchars((string) (($currentUser['username'] ?? ''))); ?></strong></div>
                    <div class="meta-box"><span><?php echo htmlspecialchars(__('settings.database')); ?></span><strong><?php echo htmlspecialchars(DB_NAME); ?></strong></div>
                    <div class="meta-box"><span><?php echo htmlspecialchars(__('settings.current_session')); ?></span><strong class="font-monospace"><?php echo htmlspecialchars((string) session_id()); ?></strong></div>
                    <div class="meta-box"><span><?php echo htmlspecialchars(__('mvc.settings_last_configuration')); ?></span><strong><?php echo htmlspecialchars(date('Y-m-d H:i:s')); ?></strong></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
