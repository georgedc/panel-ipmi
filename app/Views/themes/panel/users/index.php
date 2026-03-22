<?php
ob_start();
$users = $users ?? null;
?>
<div class="hero-banner mb-4">
    <div class="row g-4 align-items-center">
        <div class="col-lg-8">
            <h1 class="h3 mb-2"><?php echo htmlspecialchars(__('users.page_title')); ?></h1>
            <p class="mb-0"><?php echo htmlspecialchars(__('mvc.users_subtitle')); ?></p>
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
                                    <td>
                                        <?php if (($user['role'] ?? 'user') === 'admin') : ?>
                                            <span class="panel-tag" style="background:#dbeafe;color:#1d4ed8;font-weight:700;"><?php echo htmlspecialchars(__('mvc.users_role_admin')); ?></span>
                                        <?php else : ?>
                                            <span class="panel-tag" style="background:#f1f5f9;color:var(--panel-muted);"><?php echo htmlspecialchars(__('mvc.users_role_user')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars((string) ($user['last_login'] ?? __('users.never'))); ?></td>
                                    <td class="text-end">
                                        <div class="panel-actions">
                                            <a class="panel-btn" href="<?php echo routeUrl('/users/edit', ['id' => (int) ($user['id'] ?? 0)]); ?>" title="<?php echo htmlspecialchars(__('users.edit')); ?>">
                                                <i class="material-icons-round">edit</i>
                                            </a>
                                            <a class="panel-btn panel-btn-mount" href="<?php echo routeUrl('/users/assignments', ['id' => (int) ($user['id'] ?? 0)]); ?>"
                                               title="<?php echo htmlspecialchars(__('users.servers')); ?>"
                                               aria-label="<?php echo htmlspecialchars(__('users.servers')); ?>">
                                                <i class="material-icons-round">dns</i>
                                            </a>
                                            <?php if (($user['username'] ?? '') !== 'admin') : ?>
                                                <form method="post" action="<?php echo routeUrl('/users/delete'); ?>" onsubmit="return confirm('<?php echo htmlspecialchars(__('users.delete_confirm')); ?>');" style="display:inline;">
                                                    <?php echo \App\Http\Csrf::getHiddenField(); ?>
                                                    <input type="hidden" name="user_id" value="<?php echo (int) ($user['id'] ?? 0); ?>">
                                                    <button class="panel-btn panel-btn-danger" type="submit"
                                                            title="<?php echo htmlspecialchars(__('users.delete')); ?>"
                                                            aria-label="<?php echo htmlspecialchars(__('users.delete')); ?>">
                                                        <i class="material-icons-round">delete</i>
                                                    </button>
                                                </form>
                                            <?php else : ?>
                                                <button class="panel-btn panel-btn-danger" type="button" disabled
                                                        title="<?php echo htmlspecialchars(__('users.delete_blocked_admin')); ?>"
                                                        aria-label="<?php echo htmlspecialchars(__('users.delete_blocked_admin')); ?>"
                                                        style="opacity:0.35;cursor:not-allowed;">
                                                    <i class="material-icons-round">delete</i>
                                                </button>
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
