<?php
ob_start();
?>
<h3><?php echo htmlspecialchars(defined('APP_NAME') ? APP_NAME : 'IPMI PANEL'); ?></h3>
<h1><?php echo htmlspecialchars(__('tfa.title')); ?></h1>
<p>
    <?php if (!empty($user['username'])): ?>
        <?php echo htmlspecialchars((string) $user['username']); ?> —
    <?php endif; ?>
    <?php echo htmlspecialchars(__('tfa.instructions')); ?>
</p>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger mb-3"><?php echo htmlspecialchars((string) $error); ?></div>
<?php endif; ?>

<form method="post" action="<?php echo routeUrl('/two-factor'); ?>" class="auth-form-wrap">
    <?php echo \App\Http\Csrf::getHiddenField(); ?>
    <div class="form-group">
        <label for="tfa-code"><?php echo htmlspecialchars(__('tfa.code_label')); ?></label>
        <input type="text" class="form-control" name="code" id="tfa-code"
               required autofocus inputmode="numeric" autocomplete="one-time-code"
               style="letter-spacing:.2em;font-size:1.3rem;text-align:center;">
    </div>
    <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars(__('tfa.submit')); ?></button>
</form>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
