<?php
/** @var array<string,string> $settings */
/** @var list<array<string,mixed>> $backups */
/** @var string|null $flash_success */
/** @var string|null $flash_error */
ob_start();
$cronCommand = 'php ' . ROOT_PATH . '/bin/backup.php >> /var/log/ipmi-backup.log 2>&1';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><?php echo htmlspecialchars(__('backup.title')); ?></h1>
        <p class="text-muted mb-0"><?php echo htmlspecialchars(__('backup.subtitle')); ?></p>
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
                        <i class="fa-solid fa-floppy-disk me-2"></i><?php echo htmlspecialchars(__('backup.run_button')); ?>
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
                                <td><code class="small"><?php echo htmlspecialchars($bk['filename']); ?></code></td>
                                <td>
                                    <?php if ($bk['type'] === 'database') : ?>
                                    <span class="badge" style="background:var(--brand-soft);color:var(--brand);">
                                        <i class="fa-solid fa-database me-1"></i><?php echo htmlspecialchars(__('backup.type_database')); ?>
                                    </span>
                                    <?php else : ?>
                                    <span class="badge bg-secondary">
                                        <i class="fa-solid fa-folder me-1"></i><?php echo htmlspecialchars(__('backup.type_files')); ?>
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small"><?php echo htmlspecialchars((new \App\Services\BackupService())->formatSize((int) $bk['size'])); ?></td>
                                <td class="text-muted small"><?php echo date('Y-m-d H:i', (int) $bk['mtime']); ?></td>
                                <td class="text-end d-flex gap-2 justify-content-end">
                                    <a class="btn btn-outline-secondary btn-sm"
                                       href="<?php echo routeUrl('/backup/download', ['file' => $bk['filename']]); ?>">
                                        <i class="fa-solid fa-download me-1"></i><?php echo htmlspecialchars(__('backup.download')); ?>
                                    </a>
                                    <form method="post" action="<?php echo routeUrl('/backup/delete'); ?>"
                                          onsubmit="return confirm('<?php echo htmlspecialchars(__('backup.delete_confirm')); ?>');">
                                        <?php echo \App\Http\Csrf::getHiddenField(); ?>
                                        <input type="hidden" name="filename" value="<?php echo htmlspecialchars($bk['filename']); ?>">
                                        <button class="btn btn-outline-danger btn-sm" type="submit">
                                            <i class="fa-solid fa-trash me-1"></i><?php echo htmlspecialchars(__('backup.delete')); ?>
                                        </button>
                                    </form>
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
                    <label class="form-label form-label-sm text-muted">Daily at 2:00 AM</label>
                    <div class="input-group">
                        <span class="input-group-text font-monospace small bg-transparent text-muted">0 2 * * *</span>
                        <input type="text" class="form-control font-monospace small" readonly
                               value="<?php echo htmlspecialchars($cronCommand); ?>"
                               id="cron-cmd">
                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="copyText('cron-cmd', this)">
                            <i class="fa-solid fa-copy"></i>
                        </button>
                    </div>
                </div>
                <p class="text-muted small mb-0"><?php echo htmlspecialchars(__('backup.cron_edit')); ?></p>
            </div>
        </section>

    </div>


    <div class="col-xl-4">

        <section class="section-card mb-4">
            <div class="section-head"><h3><?php echo htmlspecialchars(__('backup.local_settings_title')); ?></h3></div>
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
                    <div class="row g-2 mb-2">
                        <div class="col-4">
                            <label class="form-label form-label-sm"><?php echo htmlspecialchars(__('backup.remote_port')); ?></label>
                            <input class="form-control form-control-sm" type="number" name="backup_remote_port"
                                   value="<?php echo (int) $settings['backup_remote_port']; ?>" min="1" max="65535">
                        </div>
                        <div class="col-8">
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
                        <textarea class="form-control font-monospace" name="backup_ssh_private_key" rows="5"
                                  placeholder="-----BEGIN OPENSSH PRIVATE KEY-----&#10;...&#10;-----END OPENSSH PRIVATE KEY-----"></textarea>
                        <small class="text-muted">
                            <?php echo htmlspecialchars(__('backup.ssh_key_help')); ?>
                            <?php if ($settings['backup_ssh_key_set']) : ?>
                            <span class="text-success ms-2"><i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars(__('backup.ssh_key_set')); ?></span>
                            <?php else : ?>
                            <span class="text-warning ms-2"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars(__('backup.ssh_key_not_set')); ?></span>
                            <?php endif; ?>
                        </small>
                    </div>
                    <button class="btn btn-brand w-100" type="submit">
                        <i class="fa-solid fa-floppy-disk me-2"></i><?php echo htmlspecialchars(__('backup.save_settings')); ?>
                    </button>
                </form>
            </div>
        </section>

    </div>
</div>

<script>
function copyText(inputId, btn) {
    var input = document.getElementById(inputId);
    navigator.clipboard.writeText(input.value).then(function () {
        var orig = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check"></i>';
        btn.classList.replace('btn-outline-secondary', 'btn-success');
        setTimeout(function () {
            btn.innerHTML = orig;
            btn.classList.replace('btn-success', 'btn-outline-secondary');
        }, 1500);
    });
}
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
