<?php
ob_start();
$editingIso = $editingIso ?? null;
$isos = $isos ?? null;
$sourceType = (string) (($editingIso['source_type'] ?? 'local'));
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><?php echo htmlspecialchars(__('mvc.iso_admin_title')); ?></h1>
        <p class="text-muted mb-0"><?php echo htmlspecialchars(__('mvc.iso_admin_subtitle')); ?></p>
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
    <div class="col-lg-5">
        <div class="section-card">
            <div class="section-head"><h3><?php echo htmlspecialchars($editingIso ? __('mvc.iso_admin_edit') : __('mvc.iso_admin_add')); ?></h3></div>
            <div class="section-body">
                <form method="post" action="<?php echo routeUrl($editingIso ? '/iso-admin/update' : '/iso-admin/store'); ?>">
                    <?php echo \App\Http\Csrf::getHiddenField(); ?>
                    <?php if ($editingIso) :
                        ?><input type="hidden" name="iso_id" value="<?php echo (int) ($editingIso['id'] ?? 0); ?>"><?php
                    endif; ?>
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('mvc.iso_admin_name')); ?></label><input class="form-control" type="text" name="name" value="<?php echo htmlspecialchars((string) ($editingIso['name'] ?? '')); ?>" required></div>
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('mvc.iso_admin_source_type')); ?></label>
                        <select class="form-select" name="source_type">
                            <option value="local" <?php echo $sourceType === 'local' ? 'selected' : ''; ?>>local</option>
                            <option value="remote_nfs" <?php echo $sourceType === 'remote_nfs' ? 'selected' : ''; ?>>remote_nfs</option>
                            <option value="remote_cifs" <?php echo $sourceType === 'remote_cifs' ? 'selected' : ''; ?>>remote_cifs</option>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('mvc.iso_admin_local_path')); ?></label><input class="form-control" type="text" name="local_path" value="<?php echo htmlspecialchars((string) ($editingIso['local_path'] ?? '')); ?>"></div>
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('mvc.iso_admin_download_url')); ?></label><input class="form-control" type="url" name="download_url" placeholder="https://example.com/file.iso"></div>
                    <?php if (!$editingIso) : ?>
                    <div class="mb-3">
                        <button class="btn btn-outline-secondary" formaction="<?php echo routeUrl('/iso-admin/queue-download'); ?>" type="submit"><?php echo htmlspecialchars(__('mvc.iso_admin_queue_download')); ?></button>
                    </div>
                    <?php endif; ?>
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('mvc.iso_admin_remote_host')); ?></label><input class="form-control" type="text" name="remote_host" value="<?php echo htmlspecialchars((string) ($editingIso['remote_host'] ?? '')); ?>"></div>
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('mvc.iso_admin_remote_path')); ?></label><input class="form-control" type="text" name="remote_path" value="<?php echo htmlspecialchars((string) ($editingIso['remote_path'] ?? '')); ?>"></div>
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('mvc.iso_admin_remote_username')); ?></label><input class="form-control" type="text" name="remote_username" value="<?php echo htmlspecialchars((string) ($editingIso['remote_username'] ?? '')); ?>"></div>
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('mvc.iso_admin_remote_password')); ?></label><input class="form-control" type="password" name="remote_password"></div>
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('mvc.iso_admin_notes')); ?></label><textarea class="form-control" name="notes" rows="3"><?php echo htmlspecialchars((string) ($editingIso['notes'] ?? '')); ?></textarea></div>
                    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_active" value="1" <?php echo ((int) ($editingIso['is_active'] ?? 1) === 1) ? 'checked' : ''; ?>><label class="form-check-label"><?php echo htmlspecialchars(__('mvc.iso_admin_active')); ?></label></div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-brand" type="submit"><?php echo htmlspecialchars($editingIso ? __('mvc.iso_admin_save') : __('mvc.iso_admin_create')); ?></button>
                        <?php if ($editingIso) :
                            ?><a class="btn btn-outline-secondary" href="<?php echo routeUrl('/iso-admin'); ?>"><?php echo htmlspecialchars(__('mvc.iso_admin_cancel')); ?></a><?php
                        endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="section-card">
            <div class="section-head"><h3><?php echo htmlspecialchars(__('mvc.iso_admin_registered')); ?></h3></div>
            <div class="table-responsive">
                <table class="table soft-table mb-0">
                    <thead><tr><th><?php echo htmlspecialchars(__('mvc.iso_admin_name')); ?></th><th><?php echo htmlspecialchars(__('mvc.iso_admin_type')); ?></th><th><?php echo htmlspecialchars(__('mvc.iso_admin_path')); ?></th><th><?php echo htmlspecialchars(__('mvc.iso_admin_status')); ?></th><th></th></tr></thead>
                    <tbody>
                    <?php if (!$isos) : ?>
                        <tr><td colspan="5" class="text-center text-muted py-4"><?php echo htmlspecialchars(__('mvc.iso_admin_no_rows')); ?></td></tr>
                    <?php else : ?>
                        <?php foreach ($isos as $iso) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars((string) ($iso['name'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars((string) ($iso['source_type'] ?? 'local')); ?></td>
                                <td><code><?php echo htmlspecialchars((string) ($iso['file_path'] ?? '')); ?></code></td>
                                <td><?php echo ((int) ($iso['is_active'] ?? 0) === 1) ? htmlspecialchars(__('mvc.iso_admin_active_status')) : htmlspecialchars(__('mvc.iso_admin_inactive_status')); ?></td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a class="btn btn-outline-primary" href="<?php echo routeUrl('/iso-admin', ['edit' => (int) ($iso['id'] ?? 0)]); ?>"><?php echo htmlspecialchars(__('mvc.iso_admin_edit_button')); ?></a>
                                        <form method="post" action="<?php echo routeUrl('/iso-admin/delete'); ?>" onsubmit="return confirm('<?php echo htmlspecialchars(__('mvc.iso_admin_delete_confirm')); ?>');">
                                            <?php echo \App\Http\Csrf::getHiddenField(); ?>
                                            <input type="hidden" name="iso_id" value="<?php echo (int) ($iso['id'] ?? 0); ?>">
                                            <button class="btn btn-outline-danger" type="submit"><?php echo htmlspecialchars(__('mvc.iso_admin_delete_button')); ?></button>
                                        </form>
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
<?php if (!empty($jobs)) : ?>
<div class="section-card mt-4">
    <div class="section-head"><h3><?php echo htmlspecialchars(__('mvc.iso_admin_jobs')); ?></h3></div>
    <div class="table-responsive">
        <table class="table soft-table mb-0">
            <thead><tr><th>ID</th><th><?php echo htmlspecialchars(__('mvc.iso_admin_name')); ?></th><th><?php echo htmlspecialchars(__('mvc.iso_admin_job_status')); ?></th><th><?php echo htmlspecialchars(__('mvc.iso_admin_job_progress')); ?></th><th><?php echo htmlspecialchars(__('mvc.iso_admin_job_message')); ?></th><th><?php echo htmlspecialchars(__('mvc.iso_admin_job_created')); ?></th></tr></thead>
            <tbody>
                <?php foreach ($jobs as $job) : ?>
                    <tr>
                        <td><?php echo (int) ($job['id'] ?? 0); ?></td>
                        <td><?php echo htmlspecialchars((string) ($job['name'] ?? '')); ?></td>
                        <td><?php echo htmlspecialchars((string) ($job['status'] ?? '')); ?></td>
                        <td><?php echo (int) ($job['progress'] ?? 0); ?>%</td>
                        <td><?php echo htmlspecialchars((string) (($job['error_text'] ?? '') !== '' ? $job['error_text'] : ($job['message'] ?? ''))); ?></td>
                        <td><?php echo htmlspecialchars((string) ($job['created_at'] ?? '')); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
