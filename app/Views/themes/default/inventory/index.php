<?php
/** @var list<array<string,mixed>> $servers */
/** @var int $total */
/** @var int $complete */
/** @var int $missing */
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><?php echo htmlspecialchars(__('inventory.title')); ?></h1>
        <p class="text-muted mb-0"><?php echo htmlspecialchars(__('inventory.subtitle')); ?></p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="section-card h-100">
            <div class="section-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:var(--brand-soft);color:var(--brand);font-size:1.25rem;flex-shrink:0;">
                    <i class="fa-solid fa-server"></i>
                </div>
                <div>
                    <div class="fw-semibold fs-4"><?php echo (int) $total; ?></div>
                    <div class="text-muted small"><?php echo htmlspecialchars(__('inventory.stat_total')); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="section-card h-100">
            <div class="section-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#d1fae5;color:var(--ok);font-size:1.25rem;flex-shrink:0;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <div class="fw-semibold fs-4"><?php echo (int) $complete; ?></div>
                    <div class="text-muted small"><?php echo htmlspecialchars(__('inventory.stat_complete')); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="section-card h-100">
            <div class="section-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#fef9c3;color:var(--warn);font-size:1.25rem;flex-shrink:0;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <div class="fw-semibold fs-4"><?php echo (int) $missing; ?></div>
                    <div class="text-muted small"><?php echo htmlspecialchars(__('inventory.stat_missing')); ?></div>
                </div>
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
                        <td><code class="small"><?php echo !empty($s['serial_number']) ? htmlspecialchars($s['serial_number']) : $notSet; ?></code></td>
                        <td class="small"><?php echo !empty($s['cpu_info']) ? htmlspecialchars($s['cpu_info']) : $notSet; ?></td>
                        <td class="small"><?php echo !empty($s['ram_gb']) ? htmlspecialchars((int)$s['ram_gb'] . ' GB') : $notSet; ?></td>
                        <td class="small"><?php echo !empty($s['disk_info']) ? htmlspecialchars($s['disk_info']) : $notSet; ?></td>
                        <td><code class="small"><?php echo !empty($s['switch_port']) ? htmlspecialchars($s['switch_port']) : $notSet; ?></code></td>
                        <td class="text-muted small" style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?php echo htmlspecialchars($s['notes'] ?? ''); ?>">
                            <?php echo !empty($s['notes']) ? htmlspecialchars($s['notes']) : $notSet; ?>
                        </td>
                        <td class="text-end">
                            <a class="btn btn-outline-secondary btn-sm" href="<?php echo routeUrl('/servers/edit', ['id' => (int) $s['id']]); ?>">
                                <i class="fa-solid fa-pen-to-square me-1"></i><?php echo htmlspecialchars(__('inventory.edit')); ?>
                            </a>
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
