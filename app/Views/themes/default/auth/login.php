<?php
ob_start();
?>
<div class="auth-stage auth-panel">
    <section class="auth-aside">
        <div>
            <span class="brand-mark"><i class="fa-solid fa-terminal"></i></span>
            <h1><?php echo htmlspecialchars(APP_NAME); ?></h1>
            <p><?php echo htmlspecialchars(__('mvc.login_help')); ?></p>
            <div class="auth-points">
                <div class="auth-point">
                    <i class="fa-solid fa-shield-halved"></i>
                    <div><?php echo htmlspecialchars(__('mvc.login_point_secure')); ?></div>
                </div>
                <div class="auth-point">
                    <i class="fa-solid fa-server"></i>
                    <div><?php echo htmlspecialchars(__('mvc.login_point_servers')); ?></div>
                </div>
                <div class="auth-point">
                    <i class="fa-solid fa-compact-disc"></i>
                    <div><?php echo htmlspecialchars(__('mvc.login_point_iso')); ?></div>
                </div>
            </div>
        </div>
        <div class="small text-white-50"><?php echo htmlspecialchars(__('mvc.theme_subtitle')); ?></div>
    </section>
    <section class="auth-form">
        <div class="auth-meta">
            <div>
                <h2 class="h3 mb-1"><?php echo htmlspecialchars(__('login.title')); ?></h2>
                <p class="text-muted mb-0"><?php echo htmlspecialchars(__('mvc.login_heading')); ?></p>
            </div>
            <span class="chip"><i class="fa-solid fa-palette"></i> <?php echo htmlspecialchars(__('mvc.theme_label')); ?>: <?php echo htmlspecialchars((string) (defined('APP_THEME') ? APP_THEME : 'default')); ?></span>
        </div>
        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars((string) $error); ?></div>
        <?php endif; ?>
        <form method="post" action="<?php echo routeUrl('/login'); ?>" class="d-grid gap-3">
            <?php echo \App\Http\Csrf::getHiddenField(); ?>
            <div>
                <label class="form-label"><?php echo htmlspecialchars(__('login.username')); ?></label>
                <input type="text" class="form-control" name="username" required autocomplete="username">
            </div>
            <div>
                <label class="form-label"><?php echo htmlspecialchars(__('login.password')); ?></label>
                <input type="password" class="form-control" name="password" required autocomplete="current-password">
            </div>
            <button class="btn btn-brand" type="submit"><?php echo htmlspecialchars(__('login.submit')); ?></button>
        </form>
    </section>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
