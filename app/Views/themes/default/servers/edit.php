<?php
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><?php echo htmlspecialchars(__('mvc.servers_edit_title')); ?></h1>
        <p class="text-muted mb-0"><?php echo htmlspecialchars(__('mvc.servers_edit_subtitle')); ?></p>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary" href="<?php echo routeUrl('/servers'); ?>"><?php echo htmlspecialchars(__('mvc.servers_back')); ?></a>
    </div>
</div>
<?php if (!empty($flash_success)) :
    ?><div class="alert alert-success border-0 shadow-sm"><?php echo htmlspecialchars($flash_success); ?></div><?php
endif; ?>
<?php if (!empty($flash_error)) :
    ?><div class="alert alert-danger border-0 shadow-sm"><?php echo htmlspecialchars($flash_error); ?></div><?php
endif; ?>
<div class="row g-4">
    <div class="col-xl-8">
        <section class="section-card">
            <div class="section-head"><h3><?php echo htmlspecialchars(__('servers.edit_server')); ?></h3></div>
            <div class="section-body">
                <form id="server-edit-form" method="post" action="<?php echo routeUrl('/servers/update'); ?>">
                    <?php echo \App\Http\Csrf::getHiddenField(); ?>
                    <input type="hidden" name="server_id" value="<?php echo (int) ($server['id'] ?? 0); ?>">
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('servers.name')); ?></label><input class="form-control" type="text" name="server_name" value="<?php echo htmlspecialchars((string) ($server['name'] ?? '')); ?>" required></div>
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('servers.client_label')); ?></label><input class="form-control" type="text" name="client_label" value="<?php echo htmlspecialchars((string) ($server['client_label'] ?? '')); ?>" placeholder="<?php echo htmlspecialchars(__('servers.client_label_help')); ?>"></div>
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('servers.ip_or_host')); ?></label><input class="form-control" type="text" name="ip_address" value="<?php echo htmlspecialchars((string) ($server['ip_address'] ?? '')); ?>" required></div>
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('servers.ipmi_user')); ?></label><input class="form-control" type="text" name="ipmi_username" value="<?php echo htmlspecialchars((string) ($server['ipmi_username'] ?? '')); ?>" required></div>
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('servers.ipmi_password')); ?></label><input class="form-control" type="password" name="ipmi_password" placeholder="<?php echo htmlspecialchars(__('servers.leave_blank_password')); ?>"></div>
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label"><?php echo htmlspecialchars(__('servers.ipmi_port')); ?></label><input class="form-control" type="number" name="ipmi_port" value="<?php echo (int) ($server['ipmi_port'] ?? 623); ?>"></div>
                        <div class="col-md-4"><label class="form-label"><?php echo htmlspecialchars(__('servers.ipmi_type')); ?></label><select class="form-select" name="ipmi_type"><option value="generic" <?php echo (($server['ipmi_type'] ?? '') === 'generic') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('servers.generic')); ?></option><option value="asrock" <?php echo (($server['ipmi_type'] ?? '') === 'asrock') ? 'selected' : ''; ?>>ASRock</option><option value="supermicro" <?php echo (($server['ipmi_type'] ?? '') === 'supermicro') ? 'selected' : ''; ?>>Supermicro</option><option value="dell" <?php echo (($server['ipmi_type'] ?? '') === 'dell') ? 'selected' : ''; ?>>Dell IDRAC</option><option value="tyan" <?php echo (($server['ipmi_type'] ?? '') === 'tyan') ? 'selected' : ''; ?>>TYAN/AMI MegaRAC</option></select></div>
                        <?php $selectedKvmMode = (string) ($server['kvm_mode'] ?? 'html5'); ?>
                        <div class="col-md-4"><label class="form-label"><?php echo htmlspecialchars(__('servers.kvm_mode')); ?></label><select class="form-select" name="kvm_mode"><option value="html5" <?php echo ($selectedKvmMode === 'html5') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('servers.kvm_mode_html5')); ?></option><option value="vnc_classic" <?php echo ($selectedKvmMode === 'vnc_classic') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('servers.kvm_mode_vnc')); ?></option><option value="java_classic" <?php echo ($selectedKvmMode === 'java_classic') ? 'selected' : ''; ?>><?php echo htmlspecialchars(__('servers.kvm_mode_java')); ?></option></select></div>
                    </div>
                    <div class="mt-3 mb-3"><label class="form-label"><?php echo htmlspecialchars(__('servers.location')); ?></label><input class="form-control" type="text" name="location" value="<?php echo htmlspecialchars((string) ($server['location'] ?? '')); ?>"></div>
                    <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('servers.tls_fingerprint')); ?></label><input class="form-control font-monospace" type="text" name="tls_fingerprint" value="<?php echo htmlspecialchars((string) ($server['tls_fingerprint'] ?? '')); ?>"></div>
                    <button class="btn btn-brand" type="submit"><?php echo htmlspecialchars(__('servers.save_changes')); ?></button>
                </form>
            </div>
        </section>
    </div>
    <div class="col-xl-4">
        <section class="section-card mb-4">
            <div class="section-head"><h3><?php echo htmlspecialchars(__('servers.inventory')); ?></h3><small class="text-muted"><?php echo htmlspecialchars(__('servers.inventory_help')); ?></small></div>
            <div class="section-body">
                <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('servers.serial_number')); ?></label><input class="form-control" type="text" form="server-edit-form" name="serial_number" value="<?php echo htmlspecialchars((string) ($server['serial_number'] ?? '')); ?>" placeholder="e.g. SRV-2024-001"></div>
                <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('servers.cpu_info')); ?></label><input class="form-control" type="text" form="server-edit-form" name="cpu_info" value="<?php echo htmlspecialchars((string) ($server['cpu_info'] ?? '')); ?>" placeholder="e.g. 2x Intel Xeon E5-2680v4"></div>
                <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('servers.ram_gb')); ?></label><input class="form-control" type="number" min="1" form="server-edit-form" name="ram_gb" value="<?php echo htmlspecialchars((string) ($server['ram_gb'] ?? '')); ?>" placeholder="e.g. 128"></div>
                <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('servers.disk_info')); ?></label><input class="form-control" type="text" form="server-edit-form" name="disk_info" value="<?php echo htmlspecialchars((string) ($server['disk_info'] ?? '')); ?>" placeholder="e.g. 2x 960GB SSD RAID1"></div>
                <div class="mb-3"><label class="form-label"><?php echo htmlspecialchars(__('servers.switch_port')); ?></label><input class="form-control" type="text" form="server-edit-form" name="switch_port" value="<?php echo htmlspecialchars((string) ($server['switch_port'] ?? '')); ?>" placeholder="e.g. sw01:ge-0/0/5"></div>
                <div class="mb-0"><label class="form-label"><?php echo htmlspecialchars(__('servers.notes')); ?></label><textarea class="form-control" rows="3" form="server-edit-form" name="notes" placeholder="Internal notes..."><?php echo htmlspecialchars((string) ($server['notes'] ?? '')); ?></textarea></div>
            </div>
        </section>
        <section class="section-card">
            <div class="section-head"><h3><?php echo htmlspecialchars(__('servers.api_token')); ?></h3></div>
            <div class="section-body">
                <div class="mb-3">
                    <label class="form-label"><?php echo htmlspecialchars(__('servers.api_token')); ?></label>
                    <input class="form-control font-monospace" type="text" readonly value="<?php echo htmlspecialchars((string) ($server['api_token'] ?? '')); ?>">
                    <small class="text-muted"><?php echo htmlspecialchars(__('mvc.servers_api_token_help')); ?></small>
                </div>
                <form method="post" action="<?php echo routeUrl('/servers/rotate-token'); ?>" onsubmit="return confirm('<?php echo htmlspecialchars(__('servers.rotate_confirm')); ?>');">
                    <?php echo \App\Http\Csrf::getHiddenField(); ?>
                    <input type="hidden" name="server_id" value="<?php echo (int) ($server['id'] ?? 0); ?>">
                    <button class="btn btn-outline-warning" type="submit"><?php echo htmlspecialchars(__('mvc.servers_rotate_token')); ?></button>
                </form>
            </div>
        </section>
    </div>
</div>
<div class="row g-4 mt-0">
    <div class="col-12">
        <section class="section-card">
            <div class="section-head">
                <div>
                    <h3><?php echo htmlspecialchars(__('servers.ip_allocation')); ?></h3>
                    <small class="text-muted"><?php echo htmlspecialchars(__('servers.ip_allocation_help')); ?></small>
                </div>
            </div>
            <div class="section-body">
                <?php if (!empty($serverIps)) : ?>
                <div class="table-responsive mb-4">
                    <table class="table soft-table align-middle">
                        <thead>
                            <tr>
                                <th><?php echo htmlspecialchars(__('servers.ip_address_col')); ?></th>
                                <th><?php echo htmlspecialchars(__('servers.netmask')); ?></th>
                                <th><?php echo htmlspecialchars(__('servers.gateway')); ?></th>
                                <th><?php echo htmlspecialchars(__('servers.rdns')); ?></th>
                                <th><?php echo htmlspecialchars(__('servers.description')); ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($serverIps as $sip) : ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars((string) ($sip['ip_address'] ?? '')); ?></code></td>
                                <td><?php echo htmlspecialchars((string) ($sip['netmask'] ?? '—')); ?></td>
                                <td><?php echo htmlspecialchars((string) ($sip['gateway'] ?? '—')); ?></td>
                                <td><?php echo htmlspecialchars((string) ($sip['rdns'] ?? '—')); ?></td>
                                <td><?php echo htmlspecialchars((string) ($sip['description'] ?? '')); ?></td>
                                <td class="text-end">
                                    <form method="post" action="<?php echo routeUrl('/server/ips/delete'); ?>" onsubmit="return confirm('<?php echo htmlspecialchars(__('servers.delete_ip_confirm')); ?>');">
                                        <?php echo \App\Http\Csrf::getHiddenField(); ?>
                                        <input type="hidden" name="server_id" value="<?php echo (int) ($server['id'] ?? 0); ?>">
                                        <input type="hidden" name="ip_id" value="<?php echo (int) $sip['id']; ?>">
                                        <button class="btn btn-outline-danger btn-sm" type="submit"><?php echo htmlspecialchars(__('servers.delete_ip')); ?></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else : ?>
                <p class="text-muted small mb-4"><?php echo htmlspecialchars(__('servers.no_ips')); ?></p>
                <?php endif; ?>

                <h6 class="mb-3"><?php echo htmlspecialchars(__('servers.add_ip')); ?></h6>
                <form method="post" action="<?php echo routeUrl('/server/ips/add'); ?>" class="row g-2 align-items-end">
                    <?php echo \App\Http\Csrf::getHiddenField(); ?>
                    <input type="hidden" name="server_id" value="<?php echo (int) ($server['id'] ?? 0); ?>">
                    <div class="col-md-2">
                        <label class="form-label form-label-sm"><?php echo htmlspecialchars(__('servers.ip_address_col')); ?> *</label>
                        <input class="form-control form-control-sm" type="text" name="ip_address" required placeholder="192.168.1.10">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label form-label-sm"><?php echo htmlspecialchars(__('servers.netmask')); ?></label>
                        <input class="form-control form-control-sm" type="text" name="netmask" placeholder="255.255.255.0">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label form-label-sm"><?php echo htmlspecialchars(__('servers.gateway')); ?></label>
                        <input class="form-control form-control-sm" type="text" name="gateway" placeholder="192.168.1.1">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label form-label-sm"><?php echo htmlspecialchars(__('servers.rdns')); ?></label>
                        <input class="form-control form-control-sm" type="text" name="rdns" placeholder="srv1.example.com">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label form-label-sm"><?php echo htmlspecialchars(__('servers.description')); ?></label>
                        <input class="form-control form-control-sm" type="text" name="description" placeholder="Main IP">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-brand btn-sm w-100" type="submit"><i class="fa-solid fa-plus me-1"></i><?php echo htmlspecialchars(__('servers.add_ip')); ?></button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
