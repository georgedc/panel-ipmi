<?php
ob_start();
?>
<div class="hero-banner mb-4">
    <div class="row g-4 align-items-center">
        <div class="col-lg-8">
            <h1 class="h3 mb-2"><?php echo htmlspecialchars(__('mvc.users_edit_title')); ?></h1>
            <p class="mb-0"><?php echo htmlspecialchars(__('mvc.users_edit_subtitle')); ?></p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <a class="btn panel-dash-btn" href="<?php echo routeUrl('/dashboard'); ?>">
                <i class="material-icons-round" style="font-size:18px;vertical-align:middle;">home</i>
                <span><?php echo htmlspecialchars(__('app.dashboard')); ?></span>
            </a>
        </div>
    </div>
</div>
<?php if (!empty($flash_success)) :
    ?><div class="alert alert-success"><?php echo htmlspecialchars($flash_success); ?></div><?php
endif; ?>
<?php if (!empty($flash_error)) :
    ?><div class="alert alert-danger"><?php echo htmlspecialchars($flash_error); ?></div><?php
endif; ?>
<div class="section-card">
    <div class="section-body">
        <form method="post" action="<?php echo routeUrl('/users/update'); ?>">
            <?php echo \App\Http\Csrf::getHiddenField(); ?>
            <input type="hidden" name="user_id" value="<?php echo (int) ($user['id'] ?? 0); ?>">
            <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('login.username')); ?></label><input class="form-control" type="text" value="<?php echo htmlspecialchars((string) ($user['username'] ?? '')); ?>" disabled></div>
            <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('mvc.email')); ?></label><input class="form-control" type="email" name="email" value="<?php echo htmlspecialchars((string) ($user['email'] ?? '')); ?>" required></div>
            <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('users.role')); ?></label><select class="form-select" name="role"><option value="user" <?php echo (($user['role'] ?? '') === 'user') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('mvc.users_role_user')); ?></option><option value="admin" <?php echo (($user['role'] ?? '') === 'admin') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('mvc.users_role_admin')); ?></option></select></div>
            <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('mvc.users_new_password')); ?></label><input class="form-control" type="password" name="password" placeholder="<?php echo htmlspecialchars(__('users.keep_password')); ?>"></div>
            <div class="d-flex gap-2">
                <button class="btn btn-brand" type="submit"><?php echo htmlspecialchars(__('servers.save_changes')); ?></button>
                <a class="btn panel-dash-btn" href="<?php echo routeUrl('/dashboard'); ?>"><?php echo htmlspecialchars(__('users.cancel')); ?></a>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
