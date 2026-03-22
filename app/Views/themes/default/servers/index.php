<?php
ob_start();
$servers = $servers ?? null;
?>
<div class="hero-banner mb-4">
    <div class="row g-4 align-items-center">
        <div class="col-lg-8">
            <h1 class="h3 mb-2 text-white"><?php echo htmlspecialchars(__('servers.page_title')); ?></h1>
            <p class="mb-0"><?php echo htmlspecialchars(__('mvc.servers_subtitle')); ?></p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <a class="btn btn-light" href="<?php echo routeUrl('/dashboard'); ?>"><i class="fa-solid fa-arrow-left me-2"></i><?php echo htmlspecialchars(__('app.dashboard')); ?></a>
        </div>
    </div>
</div>
<?php if (!empty($flash_success)) :
    ?><div class="alert alert-success border-0 shadow-sm"><?php echo htmlspecialchars($flash_success); ?></div><?php
endif; ?>
<?php if (!empty($flash_error)) :
    ?><div class="alert alert-danger border-0 shadow-sm"><?php echo htmlspecialchars($flash_error); ?></div><?php
endif; ?>
<div class="row g-4">
    <?php if (!empty($isAdmin)) : ?>
    <div class="col-xl-4">
        <section class="section-card h-100">
            <div class="section-head"><h3><?php echo htmlspecialchars(__('mvc.servers_add_title')); ?></h3></div>
            <div class="section-body">
                <form method="post" action="<?php echo routeUrl('/servers/create'); ?>">
                    <?php echo \App\Http\Csrf::getHiddenField(); ?>
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('servers.name')); ?></label><input class="form-control" type="text" name="server_name" required></div>
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('servers.ip_or_host')); ?></label><input class="form-control" type="text" name="ip_address" required></div>
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('servers.ipmi_user')); ?></label><input class="form-control" type="text" name="ipmi_username" required></div>
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('servers.ipmi_password')); ?></label><input class="form-control" type="password" name="ipmi_password" required></div>
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label"><?php echo htmlspecialchars(__('servers.ipmi_port')); ?></label><input class="form-control" type="number" name="ipmi_port" value="623"></div>
                        <div class="col-md-4"><label class="form-label"><?php echo htmlspecialchars(__('servers.ipmi_type')); ?></label><select class="form-select" name="ipmi_type"><option value="generic"><?php echo htmlspecialchars(__('servers.generic')); ?></option><option value="asrock">ASRock</option><option value="supermicro">Supermicro</option><option value="dell">Dell IDRAC</option><option value="tyan">TYAN/AMI MegaRAC</option></select></div>
                        <div class="col-md-4"><label class="form-label"><?php echo htmlspecialchars(__('servers.kvm_mode')); ?></label><select class="form-select" name="kvm_mode"><option value="html5" selected><?php echo htmlspecialchars(__('servers.kvm_mode_html5')); ?></option><option value="vnc_classic"><?php echo htmlspecialchars(__('servers.kvm_mode_vnc')); ?></option><option value="java_classic"><?php echo htmlspecialchars(__('servers.kvm_mode_java')); ?></option></select></div>
                    </div>
                    <div class="mt-3 mb-3"><label class="form-label"><?php echo htmlspecialchars(__('servers.location')); ?></label><input class="form-control" type="text" name="location"></div>
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('servers.tls_fingerprint')); ?></label><input class="form-control font-monospace" type="text" name="tls_fingerprint"></div>
                    <button class="btn btn-brand" type="submit"><?php echo htmlspecialchars(__('servers.add_server')); ?></button>
                </form>
            </div>
        </section>
    </div>
    <div class="col-xl-8">
    <?php else : ?>
    <div class="col-12">
    <?php endif; ?>
        <section class="section-card">
            <div class="section-head">
                <div>
                    <h3><?php echo htmlspecialchars(__('mvc.servers_list_title')); ?></h3>
                    <small class="text-muted"><?php echo htmlspecialchars(__('mvc.servers_scope_help')); ?></small>
                </div>
            </div>
            <div class="px-3 py-3 border-bottom d-flex flex-wrap gap-2 align-items-center">
                <input id="filter-search" type="text" class="form-control form-control-sm" style="max-width:260px" placeholder="<?php echo htmlspecialchars(__('servers.filter_search')); ?>">
                <select id="filter-status" class="form-select form-select-sm" style="max-width:160px">
                    <option value=""><?php echo htmlspecialchars(__('servers.filter_status')); ?></option>
                    <option value="online">Online</option>
                    <option value="offline">Offline</option>
                    <option value="maintenance">Maintenance</option>
                </select>
                <span id="filter-count" class="text-muted small ms-auto"></span>
            </div>
            <?php if (!empty($isAdmin)) : ?>
            <div id="bulk-bar" class="px-3 py-2 border-bottom bg-light d-none align-items-center gap-2 flex-wrap">
                <span id="bulk-count" class="small fw-semibold text-muted"><?php echo htmlspecialchars(__('servers.bulk_selected', ['count' => '0'])); ?></span>
                <form id="bulk-form" method="post" action="<?php echo routeUrl('/servers/bulk-power'); ?>">
                    <?php echo \App\Http\Csrf::getHiddenField(); ?>
                    <div id="bulk-ids"></div>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-success" type="submit" name="power_action" value="on" onclick="return confirmBulk('<?php echo htmlspecialchars(__('servers.bulk_power_on')); ?>')"><?php echo htmlspecialchars(__('servers.bulk_power_on')); ?></button>
                        <button class="btn btn-outline-danger" type="submit" name="power_action" value="off" onclick="return confirmBulk('<?php echo htmlspecialchars(__('servers.bulk_power_off')); ?>')"><?php echo htmlspecialchars(__('servers.bulk_power_off')); ?></button>
                        <button class="btn btn-outline-warning" type="submit" name="power_action" value="reset" onclick="return confirmBulk('<?php echo htmlspecialchars(__('servers.bulk_reset')); ?>')"><?php echo htmlspecialchars(__('servers.bulk_reset')); ?></button>
                        <button class="btn btn-outline-primary" type="submit" name="power_action" value="cycle" onclick="return confirmBulk('<?php echo htmlspecialchars(__('servers.bulk_cycle')); ?>')"><?php echo htmlspecialchars(__('servers.bulk_cycle')); ?></button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
            <div class="table-responsive">
                <table class="table soft-table align-middle" id="servers-table">
                    <thead>
                        <tr>
                            <?php if (!empty($isAdmin)) : ?><th style="width:36px"><input type="checkbox" id="select-all" title="<?php echo htmlspecialchars(__('servers.select_all')); ?>"></th><?php endif; ?>
                            <th><?php echo htmlspecialchars(__('servers.name')); ?></th>
                            <th><?php echo htmlspecialchars(__('dashboard.ip_address')); ?></th>
                            <th><?php echo htmlspecialchars(__('servers.status')); ?></th>
                            <th><?php echo htmlspecialchars(__('servers.last_check')); ?></th>
                            <th><?php echo htmlspecialchars(__('servers.location')); ?></th>
                            <th class="text-end"><?php echo htmlspecialchars(__('servers.actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$servers) : ?>
                            <tr>
                                <td colspan="<?php echo !empty($isAdmin) ? 7 : 6; ?>">
                                    <div class="empty-state my-3">
                                        <i class="fa-solid fa-server fa-2x mb-3"></i>
                                        <div><?php echo htmlspecialchars(__('mvc.servers_no_rows')); ?></div>
                                    </div>
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($servers as $server) : ?>
                                <?php $status = strtolower((string) ($server['status'] ?? 'offline')); ?>
                                <tr class="server-row"
                                    data-name="<?php echo htmlspecialchars(strtolower((string) ($server['name'] ?? ''))); ?>"
                                    data-ip="<?php echo htmlspecialchars(strtolower((string) ($server['ip_address'] ?? ''))); ?>"
                                    data-location="<?php echo htmlspecialchars(strtolower((string) ($server['location'] ?? ''))); ?>"
                                    data-status="<?php echo htmlspecialchars($status); ?>">
                                    <?php if (!empty($isAdmin)) : ?>
                                    <td><input type="checkbox" class="row-check" value="<?php echo (int) ($server['id'] ?? 0); ?>"></td>
                                    <?php endif; ?>
                                    <td><strong><?php echo htmlspecialchars((string) ($server['name'] ?? '')); ?></strong></td>
                                    <td><code><?php echo htmlspecialchars((string) ($server['ip_address'] ?? '')); ?></code></td>
                                    <td><span id="server-status-<?php echo (int) ($server['id'] ?? 0); ?>" class="status-pill <?php echo htmlspecialchars($status); ?>"><i class="fa-solid fa-circle"></i><span class="status-pill-label"><?php echo htmlspecialchars(strtoupper($status)); ?></span></span></td>
                                    <td><small id="server-last-check-<?php echo (int) ($server['id'] ?? 0); ?>" class="text-muted"><?php echo htmlspecialchars((string) ($server['last_checked'] ?? __('servers.never'))); ?></small></td>
                                    <td><?php echo htmlspecialchars((string) ($server['location'] ?? __('servers.na'))); ?></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a class="btn btn-outline-secondary" href="<?php echo routeUrl('/server', ['id' => (int) ($server['id'] ?? 0)]); ?>"><?php echo htmlspecialchars(__('mvc.servers_open_detail')); ?></a>
                                            <?php if (!empty($isAdmin)) : ?>
                                                <a class="btn btn-outline-primary" href="<?php echo routeUrl('/servers/edit', ['id' => (int) ($server['id'] ?? 0)]); ?>"><?php echo htmlspecialchars(__('mvc.servers_edit')); ?></a>
                                                <form method="post" action="<?php echo routeUrl('/servers/delete'); ?>" onsubmit="return confirm('<?php echo htmlspecialchars(__('mvc.servers_delete_confirm')); ?>');">
                                                    <?php echo \App\Http\Csrf::getHiddenField(); ?>
                                                    <input type="hidden" name="server_id" value="<?php echo (int) ($server['id'] ?? 0); ?>">
                                                    <button class="btn btn-outline-danger" type="submit"><?php echo htmlspecialchars(__('mvc.servers_delete')); ?></button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<script>
function confirmBulk(action) {
    var count = document.querySelectorAll('.row-check:checked').length;
    if (count === 0) { alert('Select at least one server.'); return false; }
    if (!confirm(action + ' on ' + count + ' server(s)?')) return false;
    var form = document.getElementById('bulk-form');
    var container = document.getElementById('bulk-ids');
    container.innerHTML = '';
    document.querySelectorAll('.row-check:checked').forEach(function (cb) {
        var inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'server_ids[]'; inp.value = cb.value;
        container.appendChild(inp);
    });
    return true;
}

document.addEventListener('DOMContentLoaded', function () {
    var filterSearch = document.getElementById('filter-search');
    var filterStatus = document.getElementById('filter-status');
    var filterCount = document.getElementById('filter-count');
    var bulkBar = document.getElementById('bulk-bar');
    var bulkCount = document.getElementById('bulk-count');
    var selectAll = document.getElementById('select-all');
    var allRows = document.querySelectorAll('.server-row');

    function applyFilters() {
        var q = filterSearch ? filterSearch.value.toLowerCase().trim() : '';
        var st = filterStatus ? filterStatus.value.toLowerCase() : '';
        var visible = 0;
        allRows.forEach(function (row) {
            var name = row.dataset.name || '';
            var ip = row.dataset.ip || '';
            var loc = row.dataset.location || '';
            var status = row.dataset.status || '';
            var matchText = !q || name.includes(q) || ip.includes(q) || loc.includes(q);
            var matchStatus = !st || status === st;
            var show = matchText && matchStatus;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (filterCount) filterCount.textContent = visible + ' / ' + allRows.length + ' servers';
    }

    function updateBulkBar() {
        if (!bulkBar) return;
        var checked = document.querySelectorAll('.row-check:checked').length;
        if (checked > 0) {
            bulkBar.classList.remove('d-none');
            bulkBar.classList.add('d-flex');
            if (bulkCount) bulkCount.textContent = checked + ' selected';
        } else {
            bulkBar.classList.add('d-none');
            bulkBar.classList.remove('d-flex');
        }
        if (selectAll) selectAll.indeterminate = checked > 0 && checked < document.querySelectorAll('.row-check').length;
    }

    if (filterSearch) filterSearch.addEventListener('input', applyFilters);
    if (filterStatus) filterStatus.addEventListener('change', applyFilters);
    applyFilters();

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.row-check').forEach(function (cb) { cb.checked = selectAll.checked; });
            updateBulkBar();
        });
    }
    document.querySelectorAll('.row-check').forEach(function (cb) {
        cb.addEventListener('change', updateBulkBar);
    });
});

document.addEventListener('DOMContentLoaded', function () {
    var rows = document.querySelectorAll('[id^="server-status-"]');
    rows.forEach(function (node, index) {
        var id = node.id.replace('server-status-', '');
        window.setTimeout(function () {
            fetch('<?php echo routeUrl('/servers/status'); ?>?id=' + encodeURIComponent(id), { credentials: 'same-origin' })
                .then(function (response) { return response.ok ? response.json() : null; })
                .then(function (payload) {
                    if (!payload || !payload.status) {
                        return;
                    }
                    var pill = document.getElementById('server-status-' + id);
                    var label = pill ? pill.querySelector('.status-pill-label') : null;
                    var lastCheck = document.getElementById('server-last-check-' + id);
                    if (pill) {
                        pill.classList.remove('online', 'offline', 'maintenance');
                        pill.classList.add(String(payload.status).toLowerCase());
                    }
                    if (label) {
                        label.textContent = String(payload.status).toUpperCase();
                    }
                    if (lastCheck && payload.last_checked) {
                        lastCheck.textContent = payload.last_checked;
                    }
                })
                .catch(function () {});
        }, index * 250);
    });
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
