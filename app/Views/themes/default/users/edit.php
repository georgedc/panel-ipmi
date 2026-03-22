<?php
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><?php echo htmlspecialchars(__('mvc.users_edit_title')); ?></h1>
        <p class="text-muted mb-0"><?php echo htmlspecialchars(__('mvc.users_edit_subtitle')); ?></p>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primary" href="<?php echo routeUrl('/users'); ?>"><?php echo htmlspecialchars(__('mvc.users_back')); ?></a>
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
                <a class="btn btn-outline-secondary" href="<?php echo routeUrl('/users'); ?>"><?php echo htmlspecialchars(__('users.cancel')); ?></a>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
