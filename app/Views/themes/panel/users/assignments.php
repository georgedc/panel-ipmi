<?php
ob_start();
$servers = $servers ?? [];
?>
<div class="hero-banner mb-4">
    <div class="row g-4 align-items-center">
        <div class="col-lg-8">
            <h1 class="h3 mb-2"><?php echo htmlspecialchars(__('mvc.users_assign_title')); ?></h1>
            <p class="mb-0"><?php echo htmlspecialchars(__('mvc.users_assign_subtitle', ['user' => (string) ($user['username'] ?? '')])); ?></p>
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
        <form method="post" action="<?php echo routeUrl('/users/assignments/save'); ?>">
            <?php echo \App\Http\Csrf::getHiddenField(); ?>
            <input type="hidden" name="user_id" value="<?php echo (int) ($user['id'] ?? 0); ?>">
            <div class="alert alert-info">
                <strong><?php echo htmlspecialchars(__('users.access_levels')); ?></strong><br>
                <strong><?php echo htmlspecialchars(__('users.readonly')); ?>:</strong> <?php echo htmlspecialchars(__('users.readonly_help')); ?><br>
                <strong><?php echo htmlspecialchars(__('users.restart')); ?>:</strong> <?php echo htmlspecialchars(__('users.restart_help')); ?><br>
                <strong><?php echo htmlspecialchars(__('users.full')); ?>:</strong> <?php echo htmlspecialchars(__('users.full_help')); ?>
            </div>
            <div class="row g-3">
                <?php foreach ($servers as $server) : ?>
                    <?php $assignedLevel = $assigned[(int) $server['id']] ?? null; ?>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="form-check mb-2">
                                    <input class="form-check-input assignment-checkbox" type="checkbox" id="server_<?php echo (int) $server['id']; ?>" <?php echo $assignedLevel ? 'checked' : ''; ?> data-target="select_<?php echo (int) $server['id']; ?>">
                                    <label class="form-check-label" for="server_<?php echo (int) $server['id']; ?>">
                                        <strong><?php echo htmlspecialchars((string) ($server['name'] ?? '')); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars((string) ($server['ip_address'] ?? '')); ?></small>
                                    </label>
                                </div>
                                <select id="select_<?php echo (int) $server['id']; ?>" class="form-select form-select-sm assignment-level" <?php echo $assignedLevel ? '' : 'disabled'; ?> name="servers[]">
                                    <option value="<?php echo (int) $server['id']; ?>:readonly" <?php echo $assignedLevel === 'readonly' ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('users.readonly')); ?></option>
                                    <option value="<?php echo (int) $server['id']; ?>:restart" <?php echo $assignedLevel === 'restart' ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('users.restart')); ?></option>
                                    <option value="<?php echo (int) $server['id']; ?>:full" <?php echo $assignedLevel === 'full' ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('users.full')); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-brand" type="submit"><?php echo htmlspecialchars(__('mvc.users_save_assignments')); ?></button>
                <a class="btn panel-dash-btn" href="<?php echo routeUrl('/dashboard'); ?>"><?php echo htmlspecialchars(__('users.cancel')); ?></a>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('change', function (event) {
    if (!event.target.classList.contains('assignment-checkbox')) {
        return;
    }
    var selectId = event.target.getAttribute('data-target');
    var select = document.getElementById(selectId);
    if (!select) {
        return;
    }
    select.disabled = !event.target.checked;
    if (!event.target.checked) {
        select.removeAttribute('name');
    } else {
        select.setAttribute('name', 'servers[]');
    }
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
