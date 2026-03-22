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
            <h1 class="h3 mb-2"><?php echo htmlspecialchars(__('mvc.isos_title')); ?></h1>
            <p class="mb-0"><?php echo htmlspecialchars(__('mvc.isos_subtitle')); ?></p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <a class="btn panel-dash-btn" href="<?php echo routeUrl('/dashboard'); ?>">
                <i class="material-icons-round" style="font-size:18px;vertical-align:middle;">home</i>
                <span><?php echo htmlspecialchars(__('app.dashboard')); ?></span>
            </a>
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
.iso-processing-card h3{margin:0 0 10px;font-size:1.15rem;color:#242c3f}
.iso-processing-card p{margin:0;color:#848eab}
.iso-processing-spinner{width:42px;height:42px;border:4px solid #d7dee7;border-top-color:#38b6ff;border-radius:50%;margin:0 auto 18px;animation:iso-spin .9s linear infinite}
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
                                <i class="material-icons-round" style="font-size:2rem;display:block;margin-bottom:10px;">album</i>
                                <div><?php echo htmlspecialchars(__('mvc.isos_no_rows')); ?></div>
                            </div>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($isos as $iso) : ?>
                        <?php
                        $rowId  = 'iso-row-' . (int) ($iso['id'] ?? 0);
                        $isoName = strtolower(trim((string) ($iso['name'] ?? '')));
                        // Filtrar stateMap: solo servidores que tienen montado ESTE iso
                        $matchedStates = [];
                        $activeCount   = 0;
                        foreach ($stateMap as $serverId => $label) {
                            if (stripos($label, $isoName) !== false) {
                                $matchedStates[$serverId] = $label;
                                $activeCount++;
                            }
                        }
                        $tooltipLines = [];
                        foreach ($matchedStates as $sid => $lbl) {
                            $tooltipLines[] = '#' . $sid . ': ' . $lbl;
                        }
                        $tooltipText = implode(' | ', $tooltipLines);
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars((string) ($iso['name'] ?? '')); ?></strong></td>
                            <td>
                                <span class="panel-tag" style="background:#dbeafe;color:#1d4ed8;font-weight:700;">
                                    <?php echo htmlspecialchars(strtoupper((string) ($iso['source_type'] ?? 'local'))); ?>
                                </span>
                            </td>
                            <td style="max-width:160px;">
                                <span style="display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--panel-muted);font-size:.85rem;"
                                      title="<?php echo htmlspecialchars((string) ($iso['notes'] ?? '')); ?>">
                                    <?php echo htmlspecialchars((string) ($iso['notes'] ?? '—')); ?>
                                </span>
                            </td>
                            <td>
                                <form method="post" action="<?php echo routeUrl('/isos/mount'); ?>" id="<?php echo $rowId; ?>">
                                    <?php echo \App\Http\Csrf::getHiddenField(); ?>
                                    <input type="hidden" name="iso_id" value="<?php echo (int) ($iso['id'] ?? 0); ?>">
                                    <select name="server_id" class="form-select" required style="min-width:140px;">
                                        <option value=""><?php echo htmlspecialchars(__('mvc.isos_select_server')); ?></option>
                                        <?php foreach ($servers as $server) : ?>
                                            <option value="<?php echo (int) $server['id']; ?>"><?php echo htmlspecialchars((string) $server['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <?php if ($activeCount > 0) : ?>
                                    <span class="panel-tag" style="background:#dcfce7;color:#166534;cursor:default;"
                                          title="<?php echo htmlspecialchars($tooltipText); ?>">
                                        <i class="material-icons-round" style="font-size:13px;vertical-align:middle;">check_circle</i>
                                        <?php echo $activeCount; ?> mounted
                                    </span>
                                <?php else : ?>
                                    <span class="panel-tag" style="background:#f1f5f9;color:var(--panel-muted);">
                                        <i class="material-icons-round" style="font-size:13px;vertical-align:middle;">radio_button_unchecked</i>
                                        None
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="panel-actions">
                                    <button type="submit" form="<?php echo $rowId; ?>"
                                            class="panel-btn panel-btn-mount"
                                            title="<?php echo htmlspecialchars(__('mvc.isos_mount')); ?>"
                                            aria-label="<?php echo htmlspecialchars(__('mvc.isos_mount')); ?>">
                                        <i class="material-icons-round">play_arrow</i>
                                    </button>
                                    <button type="submit" formaction="<?php echo routeUrl('/isos/unmount'); ?>" form="<?php echo $rowId; ?>"
                                            class="panel-btn panel-btn-unmount"
                                            title="<?php echo htmlspecialchars(__('mvc.isos_unmount')); ?>"
                                            aria-label="<?php echo htmlspecialchars(__('mvc.isos_unmount')); ?>">
                                        <i class="material-icons-round">eject</i>
                                    </button>
                                    <button type="submit" formaction="<?php echo routeUrl('/isos/refresh'); ?>" form="<?php echo $rowId; ?>"
                                            class="panel-btn panel-btn-refresh"
                                            title="<?php echo htmlspecialchars(__('mvc.isos_refresh')); ?>"
                                            aria-label="<?php echo htmlspecialchars(__('mvc.isos_refresh')); ?>">
                                        <i class="material-icons-round">refresh</i>
                                    </button>
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
        form.addEventListener('submit', function () { if (overlay) overlay.hidden = false; });
    });
    document.querySelectorAll('button[formaction="<?php echo routeUrl('/isos/unmount'); ?>"], button[formaction="<?php echo routeUrl('/isos/refresh'); ?>"]').forEach(function (button) {
        button.addEventListener('click', function () { if (overlay) overlay.hidden = false; });
    });
});
</script>
