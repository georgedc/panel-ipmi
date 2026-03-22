<?php
ob_start();
$editingIso = $editingIso ?? null;
$isos       = $isos ?? null;
$jobs       = $jobs ?? [];
$sourceType = (string) (($editingIso['source_type'] ?? 'local'));
?>
<div class="hero-banner mb-4">
    <div class="row g-4 align-items-center">
        <div class="col-lg-8">
            <h1 class="h3 mb-2"><?php echo htmlspecialchars(__('mvc.iso_admin_title')); ?></h1>
            <p class="mb-0"><?php echo htmlspecialchars(__('mvc.iso_admin_subtitle')); ?></p>
        </div>
        <div class="col-lg-4 text-lg-end d-flex gap-2 justify-content-lg-end">
            <button class="btn panel-dash-btn" onclick="isoModalOpen()">
                <i class="material-icons-round" style="font-size:18px;vertical-align:middle;">add</i>
                <span><?php echo htmlspecialchars(__('mvc.iso_admin_add')); ?></span>
            </button>
            <a class="btn panel-dash-btn" href="<?php echo routeUrl('/dashboard'); ?>">
                <i class="material-icons-round" style="font-size:18px;vertical-align:middle;">home</i>
                <span><?php echo htmlspecialchars(__('app.dashboard')); ?></span>
            </a>
        </div>
    </div>
</div>

<?php if (!empty($flash_success)) : ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($flash_success); ?></div>
<?php endif; ?>
<?php if (!empty($flash_error)) : ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($flash_error); ?></div>
<?php endif; ?>

<div class="row g-4">
    <!-- ── ISO list (full width) ──────────────────────────── -->
    <div class="col-12">
        <div class="section-card">
            <div class="section-head"><h3><?php echo htmlspecialchars(__('mvc.iso_admin_registered')); ?></h3></div>
            <div class="table-responsive">
                <table class="table soft-table mb-0">
                    <thead>
                        <tr>
                            <th><?php echo htmlspecialchars(__('mvc.iso_admin_name')); ?></th>
                            <th><?php echo htmlspecialchars(__('mvc.iso_admin_type')); ?></th>
                            <th><?php echo htmlspecialchars(__('mvc.iso_admin_path')); ?></th>
                            <th><?php echo htmlspecialchars(__('mvc.iso_admin_status')); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$isos) : ?>
                        <tr><td colspan="5" class="text-center text-muted py-4"><?php echo htmlspecialchars(__('mvc.iso_admin_no_rows')); ?></td></tr>
                    <?php else : ?>
                        <?php foreach ($isos as $iso) : ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars((string) ($iso['name'] ?? '')); ?></strong></td>
                            <td><span class="panel-tag"><?php echo htmlspecialchars(strtoupper((string) ($iso['source_type'] ?? 'local'))); ?></span></td>
                            <td><span class="font-monospace" style="font-size:.78rem;color:var(--panel-text);"><?php echo htmlspecialchars((string) ($iso['file_path'] ?? '')); ?></span></td>
                            <td>
                                <?php if ((int) ($iso['is_active'] ?? 0) === 1) : ?>
                                    <span class="panel-tag" style="background:#f0fff4;color:#27ae60;"><?php echo htmlspecialchars(__('mvc.iso_admin_active_status')); ?></span>
                                <?php else : ?>
                                    <span class="panel-tag" style="background:#fff0f0;color:#e74c3c;"><?php echo htmlspecialchars(__('mvc.iso_admin_inactive_status')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="panel-actions">
                                    <a class="panel-btn" href="<?php echo routeUrl('/iso-admin', ['edit' => (int) ($iso['id'] ?? 0)]); ?>" title="<?php echo htmlspecialchars(__('mvc.iso_admin_edit_button')); ?>">
                                        <i class="material-icons-round">edit</i>
                                    </a>
                                    <form method="post" action="<?php echo routeUrl('/iso-admin/delete'); ?>" style="display:inline;" onsubmit="return confirm('<?php echo htmlspecialchars(__('mvc.iso_admin_delete_confirm')); ?>');">
                                        <?php echo \App\Http\Csrf::getHiddenField(); ?>
                                        <input type="hidden" name="iso_id" value="<?php echo (int) ($iso['id'] ?? 0); ?>">
                                        <button class="panel-btn panel-btn-danger" type="submit" title="<?php echo htmlspecialchars(__('mvc.iso_admin_delete_button')); ?>">
                                            <i class="material-icons-round">delete</i>
                                        </button>
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

    <!-- ── Download jobs ──────────────────────────────────── -->
    <?php if (!empty($jobs)) : ?>
    <div class="col-12">
        <div class="section-card">
            <div class="section-head">
                <div>
                    <h3><?php echo htmlspecialchars(__('mvc.iso_admin_jobs')); ?></h3>
                    <small class="text-muted"><?php echo htmlspecialchars(__('mvc.iso_admin_jobs_help')); ?></small>
                </div>
                <span class="panel-tag"><?php echo count($jobs); ?> jobs</span>
            </div>
            <div class="table-responsive">
                <table class="table soft-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:35%"><?php echo htmlspecialchars(__('mvc.iso_admin_name')); ?></th>
                            <th style="width:10%"><?php echo htmlspecialchars(__('mvc.iso_admin_job_status')); ?></th>
                            <th style="width:25%"><?php echo htmlspecialchars(__('mvc.iso_admin_job_progress')); ?></th>
                            <th><?php echo htmlspecialchars(__('mvc.iso_admin_job_message')); ?></th>
                            <th style="width:14%"><?php echo htmlspecialchars(__('mvc.iso_admin_job_created')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $job) : ?>
                        <?php
                            $jPct = min(100, max(0, (int) ($job['progress'] ?? 0)));
                            $jMsg = (string) (($job['error_text'] ?? '') !== '' ? $job['error_text'] : ($job['message'] ?? ''));
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars((string) ($job['name'] ?? '')); ?></strong></td>
                            <td>
                                <?php
                                $jStatus = strtolower((string) ($job['status'] ?? ''));
                                $jBadgeStyle = match($jStatus) {
                                    'completed' => 'background:#dcfce7;color:#166534;',
                                    'failed'    => 'background:#fee2e2;color:#991b1b;',
                                    'running'   => 'background:#dbeafe;color:#1d4ed8;',
                                    default     => '',
                                };
                                ?>
                                <span class="panel-tag" style="<?php echo $jBadgeStyle; ?>"><?php echo htmlspecialchars(strtoupper((string) ($job['status'] ?? ''))); ?></span>
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div style="flex:1;height:10px;background:#e8edf3;border-radius:5px;overflow:hidden;">
                                        <div style="height:100%;width:<?php echo $jPct; ?>%;background:<?php echo $jStatus === 'completed' ? '#27ae60' : '#38b6ff'; ?>;border-radius:5px;transition:width .3s;"></div>
                                    </div>
                                    <span style="font-size:.8rem;color:var(--panel-muted);white-space:nowrap;min-width:32px;text-align:right;"><?php echo $jPct; ?>%</span>
                                </div>
                            </td>
                            <td class="text-muted" style="font-size:.85rem;"><?php echo htmlspecialchars($jMsg); ?></td>
                            <td class="text-muted" style="font-size:.82rem;"><?php echo htmlspecialchars((string) ($job['created_at'] ?? '')); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ── Modal Add / Edit ISO ──────────────────────────────── -->
<div id="isoFormModal">
    <div class="iso-modal-box">

        <div class="iso-modal-head">
            <div style="display:flex;align-items:center;gap:10px;">
                <span class="material-icons-round" style="color:var(--panel-blue);font-size:20px;">album</span>
                <span><?php echo htmlspecialchars($editingIso ? __('mvc.iso_admin_edit') : __('mvc.iso_admin_add')); ?></span>
            </div>
            <button type="button" class="iso-modal-close" onclick="isoModalClose()" aria-label="Close">
                <span class="material-icons-round">close</span>
            </button>
        </div>

        <div class="iso-modal-body">
            <form method="post" action="<?php echo routeUrl($editingIso ? '/iso-admin/update' : '/iso-admin/store'); ?>" id="isoForm">
                <?php echo \App\Http\Csrf::getHiddenField(); ?>
                <?php if ($editingIso) : ?>
                    <input type="hidden" name="iso_id" value="<?php echo (int) ($editingIso['id'] ?? 0); ?>">
                <?php endif; ?>

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label"><?php echo htmlspecialchars(__('mvc.iso_admin_name')); ?></label>
                        <input class="form-control" type="text" name="name" value="<?php echo htmlspecialchars((string) ($editingIso['name'] ?? '')); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?php echo htmlspecialchars(__('mvc.iso_admin_source_type')); ?></label>
                        <select class="form-select" name="source_type" id="isoSourceType">
                            <option value="local"       <?php echo $sourceType === 'local'       ? 'selected' : ''; ?>>Local</option>
                            <option value="remote_nfs"  <?php echo $sourceType === 'remote_nfs'  ? 'selected' : ''; ?>>Remote NFS</option>
                            <option value="remote_cifs" <?php echo $sourceType === 'remote_cifs' ? 'selected' : ''; ?>>Remote CIFS</option>
                        </select>
                    </div>

                    <div class="col-12" id="fieldLocalPath">
                        <label class="form-label"><?php echo htmlspecialchars(__('mvc.iso_admin_local_path')); ?></label>
                        <input class="form-control font-monospace" type="text" name="local_path" value="<?php echo htmlspecialchars((string) ($editingIso['local_path'] ?? '')); ?>" placeholder="/mnt/isos/debian.iso">
                    </div>

                    <div class="col-12" id="fieldDownloadUrl">
                        <label class="form-label"><?php echo htmlspecialchars(__('mvc.iso_admin_download_url')); ?></label>
                        <input class="form-control" type="url" name="download_url" placeholder="https://example.com/file.iso">
                    </div>

                    <div class="col-md-6" id="fieldRemoteHost">
                        <label class="form-label"><?php echo htmlspecialchars(__('mvc.iso_admin_remote_host')); ?></label>
                        <input class="form-control" type="text" name="remote_host" value="<?php echo htmlspecialchars((string) ($editingIso['remote_host'] ?? '')); ?>">
                    </div>
                    <div class="col-md-6" id="fieldRemotePath">
                        <label class="form-label"><?php echo htmlspecialchars(__('mvc.iso_admin_remote_path')); ?></label>
                        <input class="form-control font-monospace" type="text" name="remote_path" value="<?php echo htmlspecialchars((string) ($editingIso['remote_path'] ?? '')); ?>">
                    </div>
                    <div class="col-md-6" id="fieldRemoteUser">
                        <label class="form-label"><?php echo htmlspecialchars(__('mvc.iso_admin_remote_username')); ?></label>
                        <input class="form-control" type="text" name="remote_username" value="<?php echo htmlspecialchars((string) ($editingIso['remote_username'] ?? '')); ?>">
                    </div>
                    <div class="col-md-6" id="fieldRemotePass">
                        <label class="form-label"><?php echo htmlspecialchars(__('mvc.iso_admin_remote_password')); ?></label>
                        <input class="form-control" type="password" name="remote_password">
                    </div>

                    <div class="col-12">
                        <label class="form-label"><?php echo htmlspecialchars(__('mvc.iso_admin_notes')); ?></label>
                        <textarea class="form-control" name="notes" rows="2" style="resize:vertical;"><?php echo htmlspecialchars((string) ($editingIso['notes'] ?? '')); ?></textarea>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isoIsActive" value="1" <?php echo ((int) ($editingIso['is_active'] ?? 1) === 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="isoIsActive"><?php echo htmlspecialchars(__('mvc.iso_admin_active')); ?></label>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="iso-modal-foot">
            <!-- Acción primaria -->
            <button class="btn btn-brand px-4" form="isoForm" type="submit">
                <i class="material-icons-round" style="font-size:16px;vertical-align:middle;"><?php echo $editingIso ? 'save' : 'add'; ?></i>
                <span class="ms-1"><?php echo htmlspecialchars($editingIso ? __('mvc.iso_admin_save') : __('mvc.iso_admin_create')); ?></span>
            </button>
            <?php if (!$editingIso) : ?>
            <!-- Acción secundaria: queue -->
            <div style="display:flex;flex-direction:column;gap:2px;">
                <button class="btn panel-dash-btn" form="isoForm" formaction="<?php echo routeUrl('/iso-admin/queue-download'); ?>" type="submit"
                        title="<?php echo htmlspecialchars(__('mvc.iso_admin_queue_download')); ?>">
                    <i class="material-icons-round" style="font-size:16px;vertical-align:middle;">cloud_download</i>
                    <span><?php echo htmlspecialchars(__('mvc.iso_admin_queue_btn')); ?></span>
                </button>
                <span style="font-size:.72rem;color:var(--panel-muted);text-align:center;line-height:1.2;"><?php echo htmlspecialchars(__('mvc.iso_admin_queue_hint')); ?></span>
            </div>
            <?php endif; ?>
            <button type="button" class="btn panel-dash-btn ms-auto" onclick="isoModalClose()"><?php echo htmlspecialchars(__('mvc.iso_admin_cancel')); ?></button>
        </div>

    </div>
</div>

<style>
/* ── Modal overlay ─────────────────────────── */
#isoFormModal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(9,17,29,.5);
    backdrop-filter: blur(3px);
    align-items: center;
    justify-content: center;
    padding: 20px;
}
#isoFormModal.open { display: flex; }

/* ── Modal box ─────────────────────────────── */
.iso-modal-box {
    width: 100%;
    max-width: 560px;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 24px 60px rgba(15,23,42,.22);
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 40px);
    overflow: hidden;
}

/* ── Header ────────────────────────────────── */
.iso-modal-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    border-bottom: 1px solid var(--panel-line);
    background: #f8f9fb;
    border-radius: 14px 14px 0 0;
    font-weight: 600;
    font-size: .95rem;
    color: var(--panel-text);
}
.iso-modal-close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px; height: 30px;
    border-radius: 8px;
    border: 1px solid #d0d7e2;
    background: #eef0f5;
    color: #5a6282;
    cursor: pointer;
    transition: .15s;
    padding: 0;
}
.iso-modal-close:hover { background: #fee; color: var(--panel-danger); border-color: #fcc; }
.iso-modal-close .material-icons-round { font-size: 17px; }

/* ── Body ──────────────────────────────────── */
.iso-modal-body {
    padding: 20px 18px;
    overflow-y: auto;
    flex: 1;
}
.iso-modal-body .form-label {
    font-size: .82rem;
    font-weight: 600;
    color: var(--panel-muted);
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: 5px;
}
.iso-modal-body .form-control,
.iso-modal-body .form-select {
    border: 1px solid var(--panel-line);
    border-radius: 8px;
    font-size: .88rem;
    color: var(--panel-text);
    background: #fafbfc;
    padding: 8px 12px;
    transition: border-color .15s, box-shadow .15s;
}
.iso-modal-body .form-control:focus,
.iso-modal-body .form-select:focus {
    border-color: var(--panel-blue);
    box-shadow: 0 0 0 3px rgba(56,182,255,.15);
    background: #fff;
    outline: none;
}

/* ── Footer ────────────────────────────────── */
.iso-modal-foot {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 18px;
    border-top: 1px solid var(--panel-line);
    background: #f8f9fb;
    border-radius: 0 0 14px 14px;
}
</style>
<script>
function isoModalOpen() {
    document.getElementById('isoFormModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function isoModalClose() {
    document.getElementById('isoFormModal').classList.remove('open');
    document.body.style.overflow = '';
}
document.addEventListener('DOMContentLoaded', function () {
    var m = document.getElementById('isoFormModal');
    if (m) {
        m.addEventListener('click', function (e) { if (e.target === m) isoModalClose(); });
    }
    <?php if ($editingIso) : ?>isoModalOpen();<?php endif; ?>
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
