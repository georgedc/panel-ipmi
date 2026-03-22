<?php
/** @var list<array<string,mixed>> $servers */
/** @var int $total */
/** @var int $complete */
/** @var int $missing */
ob_start();
?>
<div class="hero-banner mb-4">
    <div class="row g-4 align-items-center">
        <div class="col-lg-8">
            <h1 class="h3 mb-2"><?php echo htmlspecialchars(__('inventory.title')); ?></h1>
            <p class="mb-0"><?php echo htmlspecialchars(__('inventory.subtitle')); ?></p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <a class="btn panel-dash-btn" href="<?php echo routeUrl('/dashboard'); ?>">
                <i class="material-icons-round" style="font-size:18px;vertical-align:middle;">home</i>
                <span><?php echo htmlspecialchars(__('app.dashboard')); ?></span>
            </a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="label"><?php echo htmlspecialchars(__('inventory.stat_total')); ?></div>
            <div class="value"><?php echo (int) $total; ?></div>
            <div class="delta" style="color:var(--panel-muted);">
                <span class="material-icons-round" style="font-size:16px;">dns</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="label"><?php echo htmlspecialchars(__('inventory.stat_complete')); ?></div>
            <div class="value" style="color:var(--panel-ok);"><?php echo (int) $complete; ?></div>
            <div class="delta" style="color:var(--panel-ok);">
                <span class="material-icons-round" style="font-size:16px;">check_circle</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="label"><?php echo htmlspecialchars(__('inventory.stat_missing')); ?></div>
            <div class="value"><?php echo (int) $missing; ?></div>
            <div class="delta" style="color:var(--panel-warn);">
                <span class="material-icons-round" style="font-size:16px;">warning</span>
            </div>
        </div>
    </div>
</div>

<section class="section-card">
    <div class="section-head">
        <div class="w-100">
            <input type="text" id="inv-search" class="form-control" placeholder="<?php echo htmlspecialchars(__('inventory.search_placeholder')); ?>">
        </div>
    </div>
    <div class="section-body p-0">
        <div class="table-responsive">
            <table class="table soft-table align-middle mb-0" id="inv-table">
                <thead>
                    <tr>
                        <th><?php echo htmlspecialchars(__('inventory.col_server')); ?></th>
                        <th><?php echo htmlspecialchars(__('inventory.col_location')); ?></th>
                        <th><?php echo htmlspecialchars(__('inventory.col_serial')); ?></th>
                        <th><?php echo htmlspecialchars(__('inventory.col_cpu')); ?></th>
                        <th><?php echo htmlspecialchars(__('inventory.col_ram')); ?></th>
                        <th><?php echo htmlspecialchars(__('inventory.col_disk')); ?></th>
                        <th><?php echo htmlspecialchars(__('inventory.col_switch')); ?></th>
                        <th><?php echo htmlspecialchars(__('inventory.col_notes')); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($servers)) : ?>
                    <tr><td colspan="9" class="text-muted text-center py-4"><?php echo htmlspecialchars(__('inventory.no_rows')); ?></td></tr>
                    <?php else : ?>
                    <?php foreach ($servers as $s) :
                        $label = !empty($s['client_label']) ? $s['client_label'] : $s['name'];
                        $notSet = htmlspecialchars(__('inventory.not_set'));
                    ?>
                    <tr class="inv-row"
                        data-search="<?php echo htmlspecialchars(strtolower(implode(' ', [
                            $s['name'] ?? '',
                            $s['client_label'] ?? '',
                            $s['location'] ?? '',
                            $s['serial_number'] ?? '',
                            $s['cpu_info'] ?? '',
                            $s['disk_info'] ?? '',
                            $s['switch_port'] ?? '',
                            $s['notes'] ?? '',
                        ]))); ?>">
                        <td>
                            <div class="fw-medium"><?php echo htmlspecialchars($s['name'] ?? ''); ?></div>
                            <?php if (!empty($s['client_label']) && $s['client_label'] !== $s['name']) : ?>
                            <div class="text-muted small"><?php echo htmlspecialchars($s['client_label']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small"><?php echo !empty($s['location']) ? htmlspecialchars($s['location']) : $notSet; ?></td>
                        <td><span class="font-monospace small"><?php echo !empty($s['serial_number']) ? htmlspecialchars($s['serial_number']) : '<span class="text-muted">—</span>'; ?></span></td>
                        <td class="small"><?php echo !empty($s['cpu_info']) ? htmlspecialchars($s['cpu_info']) : $notSet; ?></td>
                        <td class="small"><?php echo !empty($s['ram_gb']) ? htmlspecialchars((int)$s['ram_gb'] . ' GB') : $notSet; ?></td>
                        <td class="small"><?php echo !empty($s['disk_info']) ? htmlspecialchars($s['disk_info']) : $notSet; ?></td>
                        <td><span class="font-monospace small"><?php echo !empty($s['switch_port']) ? htmlspecialchars($s['switch_port']) : '<span class="text-muted">—</span>'; ?></span></td>
                        <td class="text-muted small" style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?php echo htmlspecialchars($s['notes'] ?? ''); ?>">
                            <?php echo !empty($s['notes']) ? htmlspecialchars($s['notes']) : $notSet; ?>
                        </td>
                        <td class="text-end">
                            <div class="panel-actions">
                                <a class="panel-btn" href="<?php echo routeUrl('/servers/edit', ['id' => (int) $s['id']]); ?>" title="<?php echo htmlspecialchars(__('inventory.edit')); ?>">
                                    <i class="material-icons-round">edit</i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div id="inv-no-results" class="text-muted text-center py-4 d-none"><?php echo htmlspecialchars(__('inventory.no_rows')); ?></div>
    </div>
</section>

<script>
(function () {
    var input = document.getElementById('inv-search');
    var rows = document.querySelectorAll('#inv-table .inv-row');
    var noResults = document.getElementById('inv-no-results');

    input.addEventListener('input', function () {
        var q = this.value.toLowerCase().trim();
        var visible = 0;
        rows.forEach(function (row) {
            var match = !q || row.dataset.search.indexOf(q) !== -1;
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        noResults.classList.toggle('d-none', visible > 0 || rows.length === 0);
    });
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
