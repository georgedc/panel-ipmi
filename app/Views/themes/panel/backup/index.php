<?php
/** @var array<string,string> $settings */
/** @var list<array<string,mixed>> $backups */
/** @var string|null $flash_success */
/** @var string|null $flash_error */
ob_start();
$cronCommand = 'php ' . ROOT_PATH . '/bin/backup.php >> /var/log/ipmi-backup.log 2>&1';
?>
<div class="hero-banner mb-4">
    <div class="row g-4 align-items-center">
        <div class="col-lg-8">
            <h1 class="h3 mb-2"><?php echo htmlspecialchars(__('backup.title')); ?></h1>
            <p class="mb-0"><?php echo htmlspecialchars(__('backup.subtitle')); ?></p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <a class="btn panel-dash-btn" href="<?php echo routeUrl('/dashboard'); ?>">
                <i class="material-icons-round" style="font-size:18px;vertical-align:middle;">home</i>
                <span><?php echo htmlspecialchars(__('app.dashboard')); ?></span>
            </a>
        </div>
    </div>
</div>

<?php if (!empty($flash_success)) : ?>
<div class="alert alert-success border-0 shadow-sm"><?php echo htmlspecialchars($flash_success); ?></div>
<?php endif; ?>
<?php if (!empty($flash_error)) : ?>
<div class="alert alert-danger border-0 shadow-sm"><?php echo htmlspecialchars($flash_error); ?></div>
<?php endif; ?>

<div class="row g-4">


    <div class="col-xl-8">

    
        <section class="section-card mb-4">
            <div class="section-head"><h3><?php echo htmlspecialchars(__('backup.run_title')); ?></h3></div>
            <div class="section-body">
                <form method="post" action="<?php echo routeUrl('/backup/run'); ?>">
                    <?php echo \App\Http\Csrf::getHiddenField(); ?>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="include_files" value="1" id="include_files">
                        <label class="form-check-label" for="include_files">
                            <?php echo htmlspecialchars(__('backup.include_files')); ?>
                        </label>
                        <div class="text-muted small mt-1"><?php echo htmlspecialchars(__('backup.include_files_help')); ?></div>
                    </div>
                    <button class="btn btn-brand" type="submit">
                        <i class="material-icons-round" style="font-size:16px;vertical-align:middle;">save</i>
                        <span class="ms-1"><?php echo htmlspecialchars(__('backup.run_button')); ?></span>
                    </button>
                </form>
            </div>
        </section>

    
        <section class="section-card mb-4">
            <div class="section-head"><h3><?php echo htmlspecialchars(__('backup.list_title')); ?></h3></div>
            <div class="section-body p-0">
                <?php if (empty($backups)) : ?>
                <p class="text-muted small m-4"><?php echo htmlspecialchars(__('backup.no_backups')); ?></p>
                <?php else : ?>
                <div class="table-responsive">
                    <table class="table soft-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th><?php echo htmlspecialchars(__('backup.col_file')); ?></th>
                                <th><?php echo htmlspecialchars(__('backup.col_type')); ?></th>
                                <th><?php echo htmlspecialchars(__('backup.col_size')); ?></th>
                                <th><?php echo htmlspecialchars(__('backup.col_date')); ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($backups as $bk) : ?>
                            <tr>
                                <td><span class="font-monospace small" style="color:var(--panel-text);"><?php echo htmlspecialchars($bk['filename']); ?></span></td>
                                <td>
                                    <?php if ($bk['type'] === 'database') : ?>
                                    <span class="panel-tag" style="background:#eef4ff;color:#4b537f;">
                                        <i class="material-icons-round" style="font-size:13px;vertical-align:middle;">storage</i>
                                        <?php echo htmlspecialchars(__('backup.type_database')); ?>
                                    </span>
                                    <?php else : ?>
                                    <span class="panel-tag">
                                        <i class="material-icons-round" style="font-size:13px;vertical-align:middle;">folder</i>
                                        <?php echo htmlspecialchars(__('backup.type_files')); ?>
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small"><?php echo htmlspecialchars((new \App\Services\BackupService())->formatSize((int) $bk['size'])); ?></td>
                                <td class="text-muted small"><?php echo date('Y-m-d H:i', (int) $bk['mtime']); ?></td>
                                <td class="text-end">
                                    <div class="panel-actions">
                                        <a class="panel-btn panel-btn-mount" href="<?php echo routeUrl('/backup/download', ['file' => $bk['filename']]); ?>" title="<?php echo htmlspecialchars(__('backup.download')); ?>">
                                            <i class="material-icons-round">download</i>
                                        </a>
                                        <form method="post" action="<?php echo routeUrl('/backup/delete'); ?>"
                                              onsubmit="return confirm('<?php echo htmlspecialchars(__('backup.delete_confirm')); ?>');" style="display:inline;">
                                            <?php echo \App\Http\Csrf::getHiddenField(); ?>
                                            <input type="hidden" name="filename" value="<?php echo htmlspecialchars($bk['filename']); ?>">
                                            <button class="panel-btn panel-btn-danger" type="submit" title="<?php echo htmlspecialchars(__('backup.delete')); ?>">
                                                <i class="material-icons-round">delete</i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </section>

    
        <section class="section-card">
            <div class="section-head"><h3><?php echo htmlspecialchars(__('backup.cron_title')); ?></h3></div>
            <div class="section-body">
                <p class="text-muted small mb-3"><?php echo htmlspecialchars(__('backup.cron_help')); ?></p>
                <div class="mb-2">
                    <div class="mb-1" style="font-size:.78rem;color:var(--panel-muted);">
                        <i class="material-icons-round" style="font-size:13px;vertical-align:middle;">schedule</i>
                        Daily at 2:00 AM
                    </div>
                    <div class="input-group">
                        <span class="input-group-text font-monospace" style="font-size:.8rem;background:#f0f8ff;border-color:var(--panel-line);color:#4b537f;border-right:none;">0 2 * * *</span>
                        <input type="text" class="form-control font-monospace" readonly
                               style="font-size:.8rem;background:#fafbfc;border-color:var(--panel-line);color:var(--panel-text);"
                               value="<?php echo htmlspecialchars($cronCommand); ?>"
                               id="cron-cmd">
                        <button class="panel-btn" id="cron-copy-btn" type="button" onclick="copyText('cron-cmd', this)"
                                style="border-radius:0 8px 8px 0;width:38px;height:auto;">
                            <i class="material-icons-round" style="font-size:17px;">content_copy</i>
                        </button>
                    </div>
                </div>
                <p class="text-muted small mb-0">Run: <code style="font-size:.8rem;background:#f0f8ff;padding:1px 6px;border-radius:4px;color:#4b537f;">crontab -e</code></p>
            </div>
        </section>

    </div>


    <div class="col-xl-4">

        <section class="section-card mb-4">
            <div class="section-head"><h3><?php echo htmlspecialchars(__('backup.storage_settings_title')); ?></h3></div>
            <div class="section-body">
                <form method="post" action="<?php echo routeUrl('/backup/settings'); ?>" id="backup-settings-form">
                    <?php echo \App\Http\Csrf::getHiddenField(); ?>
                    <div class="mb-3">
                        <label class="form-label"><?php echo htmlspecialchars(__('backup.local_path')); ?></label>
                        <input class="form-control font-monospace" type="text" name="backup_local_path"
                               value="<?php echo htmlspecialchars($settings['backup_local_path']); ?>">
                    </div>
                    <hr class="my-3">
                    <h6 class="mb-3"><?php echo htmlspecialchars(__('backup.remote_settings_title')); ?></h6>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="backup_remote_enabled" value="1"
                               id="remote_enabled" <?php echo $settings['backup_remote_enabled'] === '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="remote_enabled">
                            <?php echo htmlspecialchars(__('backup.remote_enabled')); ?>
                        </label>
                    </div>
                    <div class="mb-2">
                        <label class="form-label form-label-sm"><?php echo htmlspecialchars(__('backup.remote_host')); ?></label>
                        <input class="form-control form-control-sm" type="text" name="backup_remote_host"
                               value="<?php echo htmlspecialchars($settings['backup_remote_host']); ?>"
                               placeholder="backup.example.com">
                    </div>
                    <div class="d-flex gap-2 mb-2 align-items-end">
                        <div style="flex:5 1 0;min-width:0;">
                            <label class="form-label form-label-sm"><?php echo htmlspecialchars(__('backup.remote_port')); ?></label>
                            <input class="form-control form-control-sm" type="number" name="backup_remote_port"
                                   value="<?php echo (int) $settings['backup_remote_port']; ?>" min="1" max="65535"
                                   style="-moz-appearance:textfield;appearance:textfield;">
                        </div>
                        <div style="flex:7 1 0;min-width:0;">
                            <label class="form-label form-label-sm"><?php echo htmlspecialchars(__('backup.remote_user')); ?></label>
                            <input class="form-control form-control-sm" type="text" name="backup_remote_user"
                                   value="<?php echo htmlspecialchars($settings['backup_remote_user']); ?>"
                                   placeholder="backupuser">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label form-label-sm"><?php echo htmlspecialchars(__('backup.remote_path')); ?></label>
                        <input class="form-control form-control-sm font-monospace" type="text" name="backup_remote_path"
                               value="<?php echo htmlspecialchars($settings['backup_remote_path']); ?>"
                               placeholder="/home/backupuser/ipmi-backups">
                    </div>
                    <div class="mb-3">
                        <label class="form-label form-label-sm"><?php echo htmlspecialchars(__('backup.ssh_key')); ?></label>
                        <div style="position:relative;">
                            <textarea class="form-control font-monospace ssh-key-masked" name="backup_ssh_private_key" rows="5"
                                      id="ssh-key-field"
                                      placeholder="-----BEGIN OPENSSH PRIVATE KEY-----&#10;...&#10;-----END OPENSSH PRIVATE KEY-----"></textarea>
                            <button type="button" id="ssh-key-toggle"
                                    style="position:absolute;top:6px;right:6px;background:#eef0f5;border:none;border-radius:6px;width:28px;height:28px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--panel-muted);"
                                    title="Show / hide key" aria-label="Show / hide key">
                                <i class="material-icons-round" style="font-size:16px;">visibility_off</i>
                            </button>
                        </div>
                        <small class="text-muted d-block mt-1"><?php echo htmlspecialchars(__('backup.ssh_key_help')); ?></small>
                        <?php if ($settings['backup_ssh_key_set']) : ?>
                        <small class="d-block mt-1" style="color:var(--panel-ok);"><i class="material-icons-round" style="font-size:14px;vertical-align:middle;">check_circle</i> <?php echo htmlspecialchars(__('backup.ssh_key_set')); ?></small>
                        <?php else : ?>
                        <small class="d-block mt-1" style="color:var(--panel-warn);"><i class="material-icons-round" style="font-size:14px;vertical-align:middle;">warning</i> <?php echo htmlspecialchars(__('backup.ssh_key_not_set')); ?></small>
                        <?php endif; ?>
                    </div>
                    <button class="btn btn-brand w-100" type="submit">
                        <i class="material-icons-round" style="font-size:16px;vertical-align:middle;">save</i>
                        <span class="ms-1"><?php echo htmlspecialchars(__('backup.save_settings')); ?></span>
                    </button>
                </form>
            </div>
        </section>

    </div>
</div>

<style>
.ssh-key-masked { filter: blur(3px); transition: filter .2s; user-select: none; }
.ssh-key-masked:focus { filter: none; }
</style>
<script>
(function () {
    var field = document.getElementById('ssh-key-field');
    var btn = document.getElementById('ssh-key-toggle');
    if (!field || !btn) return;
    btn.addEventListener('click', function () {
        var masked = field.classList.toggle('ssh-key-masked');
        btn.querySelector('i').textContent = masked ? 'visibility_off' : 'visibility';
    });
    field.addEventListener('focus', function () {
        field.classList.remove('ssh-key-masked');
        btn.querySelector('i').textContent = 'visibility';
    });
})();

function copyText(inputId, btn) {
    var input = document.getElementById(inputId);
    navigator.clipboard.writeText(input.value).then(function () {
        var icon = btn.querySelector('i');
        icon.textContent = 'check';
        btn.style.background = 'var(--panel-ok)';
        setTimeout(function () {
            icon.textContent = 'content_copy';
            btn.style.background = '';
        }, 1500);
    });
}
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
