<?php
ob_start();
$isos = $workspace['isos'] ?? [];
$servers = $workspace['servers'] ?? [];
$states = $workspace['states'] ?? [];
$stateMap = [];
foreach ($states as $state) {
    $stateMap[(int) ($state['server_id'] ?? 0)] = (string) ($state['label'] ?? __('mvc.isos_no_cached_status'));
}
?>
<div class="hero-banner mb-4">
    <div class="row g-4 align-items-center">
        <div class="col-lg-8">
            <h1 class="h3 mb-2 text-white"><?php echo htmlspecialchars(__('mvc.isos_title')); ?></h1>
            <p class="mb-0"><?php echo htmlspecialchars(__('mvc.isos_subtitle')); ?></p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <a class="btn btn-light" href="<?php echo routeUrl('/dashboard'); ?>"><i class="fa-solid fa-arrow-left me-2"></i><?php echo htmlspecialchars(__('mvc.isos_back')); ?></a>
        </div>
    </div>
</div>
<?php if (!empty($success)) : ?>
    <div class="alert alert-success border-0 shadow-sm"><?php echo htmlspecialchars((string) $success); ?></div>
<?php endif; ?>
<?php if (!empty($error)) : ?>
    <div class="alert alert-danger border-0 shadow-sm"><?php echo htmlspecialchars((string) $error); ?></div>
<?php endif; ?>
<div id="iso-processing-overlay" class="iso-processing-overlay" hidden>
    <div class="iso-processing-card">
        <div class="iso-processing-spinner"></div>
        <h3><?php echo htmlspecialchars(__('iso.processing_title')); ?></h3>
        <p><?php echo htmlspecialchars(__('iso.processing_message')); ?></p>
    </div>
</div>
<style>
.iso-processing-overlay{position:fixed;inset:0;background:rgba(9,17,29,.55);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;z-index:1200;padding:24px}
.iso-processing-card{width:min(460px,100%);background:#fff;border-radius:18px;padding:28px;box-shadow:0 24px 60px rgba(15,23,42,.22);text-align:center}
.iso-processing-card h3{margin:0 0 10px;font-size:1.15rem;color:#12212f}
.iso-processing-card p{margin:0;color:#526274}
.iso-processing-spinner{width:42px;height:42px;border:4px solid #d7dee7;border-top-color:#0e7490;border-radius:50%;margin:0 auto 18px;animation:iso-spin .9s linear infinite}
@keyframes iso-spin{to{transform:rotate(360deg)}}
</style>
<section class="section-card">
    <div class="section-head">
        <div>
            <h3><?php echo htmlspecialchars(__('mvc.isos_control_title')); ?></h3>
            <small class="text-muted"><?php echo htmlspecialchars(__('mvc.isos_control_help')); ?></small>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table soft-table align-middle">
            <thead>
                <tr>
                    <th><?php echo htmlspecialchars(__('mvc.iso_admin_name')); ?></th>
                    <th><?php echo htmlspecialchars(__('mvc.iso_admin_source_type')); ?></th>
                    <th><?php echo htmlspecialchars(__('mvc.iso_admin_notes')); ?></th>
                    <th><?php echo htmlspecialchars(__('mvc.isos_target_server')); ?></th>
                    <th><?php echo htmlspecialchars(__('mvc.isos_cached_status')); ?></th>
                    <th class="text-end"><?php echo htmlspecialchars(__('mvc.isos_actions')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$isos) : ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-state my-3">
                                <i class="fa-solid fa-compact-disc fa-2x mb-3"></i>
                                <div><?php echo htmlspecialchars(__('mvc.isos_no_rows')); ?></div>
                            </div>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($isos as $iso) : ?>
                        <?php $rowId = 'iso-row-' . (int) ($iso['id'] ?? 0); ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars((string) ($iso['name'] ?? '')); ?></strong>
                            </td>
                            <td><span class="chip"><?php echo htmlspecialchars(strtoupper((string) ($iso['source_type'] ?? 'local'))); ?></span></td>
                            <td class="text-muted"><?php echo htmlspecialchars((string) ($iso['notes'] ?? '')); ?></td>
                            <td>
                                <form method="post" action="<?php echo routeUrl('/isos/mount'); ?>" id="<?php echo $rowId; ?>" class="d-flex gap-2 align-items-center">
                                    <?php echo \App\Http\Csrf::getHiddenField(); ?>
                                    <input type="hidden" name="iso_id" value="<?php echo (int) ($iso['id'] ?? 0); ?>">
                                    <select name="server_id" class="form-select form-select-sm" required>
                                        <option value=""><?php echo htmlspecialchars(__('mvc.isos_select_server')); ?></option>
                                        <?php foreach ($servers as $server) : ?>
                                            <option value="<?php echo (int) $server['id']; ?>"><?php echo htmlspecialchars((string) $server['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <div class="small text-muted">
                                    <?php if ($stateMap) : ?>
                                        <?php foreach ($stateMap as $serverId => $label) : ?>
                                            <div><strong>#<?php echo (int) $serverId; ?></strong> · <?php echo htmlspecialchars($label); ?></div>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <?php echo htmlspecialchars(__('mvc.isos_no_cached_status')); ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="submit" form="<?php echo $rowId; ?>" class="btn btn-success"><?php echo htmlspecialchars(__('mvc.isos_mount')); ?></button>
                                    <button type="submit" formaction="<?php echo routeUrl('/isos/unmount'); ?>" form="<?php echo $rowId; ?>" class="btn btn-warning"><?php echo htmlspecialchars(__('mvc.isos_unmount')); ?></button>
                                    <button type="submit" formaction="<?php echo routeUrl('/isos/refresh'); ?>" form="<?php echo $rowId; ?>" class="btn btn-outline-secondary"><?php echo htmlspecialchars(__('mvc.isos_refresh')); ?></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var overlay = document.getElementById('iso-processing-overlay');
    document.querySelectorAll('form[action="<?php echo routeUrl('/isos/mount'); ?>"]').forEach(function (form) {
        form.addEventListener('submit', function () {
            if (overlay) {
                overlay.hidden = false;
            }
        });
    });
    document.querySelectorAll('button[formaction="<?php echo routeUrl('/isos/unmount'); ?>"], button[formaction="<?php echo routeUrl('/isos/refresh'); ?>"]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (overlay) {
                overlay.hidden = false;
            }
        });
    });
});
</script>
