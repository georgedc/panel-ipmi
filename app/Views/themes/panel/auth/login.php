<?php
ob_start();
?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger mb-3"><?php echo htmlspecialchars((string) $error); ?></div>
<?php endif; ?>

<h3><?php echo htmlspecialchars(defined('APP_NAME') ? APP_NAME : 'IPMI PANEL'); ?></h3>
<h1><?php echo htmlspecialchars(__('login.title')); ?></h1>
<p><?php echo htmlspecialchars(__('mvc.login_heading')); ?></p>

<form method="post" action="<?php echo routeUrl('/login'); ?>" class="auth-form-wrap">
    <?php echo \App\Http\Csrf::getHiddenField(); ?>
    <div class="form-group">
        <label for="inputUser"><?php echo htmlspecialchars(__('login.username')); ?></label>
        <input type="text" name="username" class="form-control" id="inputUser" required autocomplete="username">
    </div>
    <div class="form-group">
        <label for="inputPassword"><?php echo htmlspecialchars(__('login.password')); ?></label>
        <input type="password" name="password" class="form-control" id="inputPassword" required autocomplete="current-password">
    </div>
    <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars(__('login.submit')); ?></button>
</form>

<p class="lg-margin-top mt-3" style="font-family:'Baloo Chettan 2',cursive;font-size:.9rem;color:#848eab;">
    <?php echo htmlspecialchars(__('mvc.login_help')); ?>
</p>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
