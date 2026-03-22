<?php
ob_start();
$users = $users ?? null;
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><?php echo htmlspecialchars(__('users.page_title')); ?></h1>
        <p class="text-muted mb-0"><?php echo htmlspecialchars(__('mvc.users_subtitle')); ?></p>
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
    <div class="col-lg-4">
        <div class="section-card">
            <div class="section-head"><h3><?php echo htmlspecialchars(__('mvc.users_add_title')); ?></h3></div>
            <div class="section-body">
                <form method="post" action="<?php echo routeUrl('/users/create'); ?>">
                    <?php echo \App\Http\Csrf::getHiddenField(); ?>
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('login.username')); ?></label><input class="form-control" type="text" name="username" required></div>
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('mvc.email')); ?></label><input class="form-control" type="email" name="email" required></div>
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('login.password')); ?></label><input class="form-control" type="password" name="password" required></div>
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('users.role')); ?></label><select class="form-select" name="role"><option value="user"><?php echo htmlspecialchars(__('mvc.users_role_user')); ?></option><option value="admin"><?php echo htmlspecialchars(__('mvc.users_role_admin')); ?></option></select></div>
                    <button class="btn btn-brand" type="submit"><?php echo htmlspecialchars(__('mvc.users_create')); ?></button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="section-card">
            <div class="section-head"><h3><?php echo htmlspecialchars(__('mvc.users_list_title')); ?></h3></div>
            <div class="table-responsive">
                <table class="table soft-table mb-0">
                    <thead>
                        <tr><th><?php echo htmlspecialchars(__('login.username')); ?></th><th><?php echo htmlspecialchars(__('mvc.email')); ?></th><th><?php echo htmlspecialchars(__('users.role')); ?></th><th><?php echo htmlspecialchars(__('users.last_login')); ?></th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php if (!$users) : ?>
                            <tr><td colspan="5" class="text-center text-muted py-4"><?php echo htmlspecialchars(__('mvc.users_no_rows')); ?></td></tr>
                        <?php else : ?>
                            <?php foreach ($users as $user) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars((string) ($user['username'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars((string) ($user['email'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars((string) ($user['role'] ?? 'user')); ?></td>
                                    <td><?php echo htmlspecialchars((string) ($user['last_login'] ?? __('users.never'))); ?></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a class="btn btn-outline-primary" href="<?php echo routeUrl('/users/edit', ['id' => (int) ($user['id'] ?? 0)]); ?>"><?php echo htmlspecialchars(__('users.edit')); ?></a>
                                            <a class="btn btn-outline-warning" href="<?php echo routeUrl('/users/assignments', ['id' => (int) ($user['id'] ?? 0)]); ?>"><?php echo htmlspecialchars(__('users.servers')); ?></a>
                                            <?php if (($user['username'] ?? '') !== 'admin') : ?>
                                                <form method="post" action="<?php echo routeUrl('/users/delete'); ?>" onsubmit="return confirm('<?php echo htmlspecialchars(__('users.delete_confirm')); ?>');">
                                                    <?php echo \App\Http\Csrf::getHiddenField(); ?>
                                                    <input type="hidden" name="user_id" value="<?php echo (int) ($user['id'] ?? 0); ?>">
                                                    <button class="btn btn-outline-danger" type="submit"><?php echo htmlspecialchars(__('users.delete')); ?></button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
