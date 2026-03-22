<?php
ob_start();
?>
<div class="auth-stage auth-panel">
    <section class="auth-aside">
        <div>
            <span class="brand-mark"><i class="fa-solid fa-shield-halved"></i></span>
            <h1><?php echo htmlspecialchars(__('tfa.heading')); ?></h1>
            <p><?php echo htmlspecialchars(__('tfa.instructions')); ?></p>
            <div class="auth-points">
                <div class="auth-point">
                    <i class="fa-solid fa-mobile-screen-button"></i>
                    <div><?php echo htmlspecialchars(__('mvc.tfa_point_app')); ?></div>
                </div>
                <div class="auth-point">
                    <i class="fa-solid fa-key"></i>
                    <div><?php echo htmlspecialchars(__('mvc.tfa_point_code')); ?></div>
                </div>
            </div>
        </div>
        <div class="small text-white-50"><?php echo htmlspecialchars(APP_NAME); ?></div>
    </section>
    <section class="auth-form">
        <div class="auth-meta">
            <div>
                <h2 class="h3 mb-1"><?php echo htmlspecialchars(__('tfa.title')); ?></h2>
                <?php if (!empty($user['username'])) : ?>
                    <p class="text-muted mb-0"><?php echo htmlspecialchars((string) $user['username']); ?></p>
                <?php else : ?>
                    <p class="text-muted mb-0"><?php echo htmlspecialchars(__('mvc.login_heading')); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars((string) $error); ?></div>
        <?php endif; ?>
        <form method="post" action="<?php echo routeUrl('/two-factor'); ?>" class="d-grid gap-3">
            <?php echo \App\Http\Csrf::getHiddenField(); ?>
            <div>
                <label class="form-label"><?php echo htmlspecialchars(__('tfa.code_label')); ?></label>
                <input type="text" class="form-control" name="code" required autofocus inputmode="numeric" autocomplete="one-time-code">
            </div>
            <button class="btn btn-brand" type="submit"><?php echo htmlspecialchars(__('tfa.submit')); ?></button>
        </form>
    </section>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
