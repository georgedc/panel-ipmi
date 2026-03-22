<?php
ob_start();
$accessLevel = $accessLevel ?? 'readonly';
$status = strtolower((string) ($server['status'] ?? 'offline'));
$serverIps = $serverIps ?? [];
$displayLabel = !empty($server['client_label']) ? $server['client_label'] : ($server['name'] ?? '');
?>
<div class="hero-banner mb-4">
    <div class="row g-4 align-items-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="brand-mark"><i class="fa-solid fa-microchip"></i></span>
                <div>
                    <h1 class="h3 mb-1 text-white"><?php echo htmlspecialchars((string) ($server['name'] ?? __('dashboard.server'))); ?></h1>
                    <p class="mb-0"><?php echo htmlspecialchars(__('mvc.server_detail_subtitle')); ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 text-lg-end">
            <a class="btn btn-light" href="<?php echo routeUrl('/servers'); ?>"><i class="fa-solid fa-arrow-left me-2"></i><?php echo htmlspecialchars(__('mvc.server_back')); ?></a>
        </div>
    </div>
</div>
<?php if (!empty($flash_power_success)) :
    ?><div class="alert alert-success border-0 shadow-sm mb-4"><?php echo htmlspecialchars($flash_power_success); ?></div><?php
endif; ?>
<?php if (!empty($flash_power_error)) :
    ?><div class="alert alert-danger border-0 shadow-sm mb-4"><?php echo htmlspecialchars($flash_power_error); ?></div><?php
endif; ?>
<?php if (!empty($flash_iso_success)) :
    ?><div class="alert alert-success border-0 shadow-sm mb-4"><?php echo htmlspecialchars($flash_iso_success); ?></div><?php
endif; ?>
<?php if (!empty($flash_iso_error)) :
    ?><div class="alert alert-danger border-0 shadow-sm mb-4"><?php echo htmlspecialchars($flash_iso_error); ?></div><?php
endif; ?>
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
<div class="row g-4 mb-2">
    <div class="col-xl-4">
        <section class="section-card h-100">
            <div class="section-head">
                <h3><?php echo htmlspecialchars(__('servers.server_info')); ?></h3>
            </div>
            <div class="section-body">
                <div class="server-meta">
                    <div class="meta-box"><span><?php echo htmlspecialchars(__('servers.server_id')); ?></span><strong><code>#<?php echo (int) ($server['id'] ?? 0); ?></code></strong></div>
                    <div class="meta-box"><span><?php echo htmlspecialchars(__('servers.server_label')); ?></span><strong><?php echo htmlspecialchars($displayLabel); ?></strong></div>
                    <div class="meta-box"><span><?php echo htmlspecialchars(__('server.location')); ?></span><strong><?php echo htmlspecialchars((string) ($server['location'] ?? __('servers.na'))); ?></strong></div>
                    <div class="meta-box"><span><?php echo htmlspecialchars(__('servers.status')); ?></span><strong><span class="status-pill <?php echo htmlspecialchars($status); ?>"><i class="fa-solid fa-circle"></i> <?php echo htmlspecialchars(strtoupper($status)); ?></span></strong></div>
                </div>
            </div>
        </section>
    </div>
    <div class="col-xl-4">
        <section class="section-card h-100">
            <div class="section-head">
                <h3><?php echo htmlspecialchars(__('servers.inventory')); ?></h3>
            </div>
            <div class="section-body">
                <div class="server-meta">
                    <?php if (!empty($server['cpu_info'])) : ?><div class="meta-box"><span><?php echo htmlspecialchars(__('servers.cpu_info')); ?></span><strong><?php echo htmlspecialchars((string) $server['cpu_info']); ?></strong></div><?php endif; ?>
                    <?php if (!empty($server['ram_gb'])) : ?><div class="meta-box"><span><?php echo htmlspecialchars(__('servers.ram_gb')); ?></span><strong><?php echo htmlspecialchars((string) $server['ram_gb']); ?> GB</strong></div><?php endif; ?>
                    <?php if (!empty($server['disk_info'])) : ?><div class="meta-box"><span><?php echo htmlspecialchars(__('servers.disk_info')); ?></span><strong><?php echo htmlspecialchars((string) $server['disk_info']); ?></strong></div><?php endif; ?>
                    <?php if (!empty($server['serial_number'])) : ?><div class="meta-box"><span><?php echo htmlspecialchars(__('servers.serial_number')); ?></span><strong><?php echo htmlspecialchars((string) $server['serial_number']); ?></strong></div><?php endif; ?>
                    <?php if (empty($server['cpu_info']) && empty($server['ram_gb']) && empty($server['disk_info'])) : ?>
                        <div class="text-muted small"><?php echo htmlspecialchars(__('servers.no_ips')); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
    <div class="col-xl-4">
        <section class="section-card h-100">
            <div class="section-head">
                <h3><?php echo htmlspecialchars(__('servers.ip_allocation')); ?></h3>
            </div>
            <div class="section-body">
                <?php if (empty($serverIps)) : ?>
                    <p class="text-muted small mb-0"><?php echo htmlspecialchars(__('servers.no_ips')); ?></p>
                <?php else : ?>
                    <div class="d-flex flex-column gap-2">
                    <?php foreach ($serverIps as $sip) : ?>
                        <div class="meta-box" style="border-radius:12px;padding:12px">
                            <strong><code><?php echo htmlspecialchars((string) $sip['ip_address']); ?></code></strong>
                            <?php if (!empty($sip['description'])) : ?><span class="badge bg-light text-muted ms-1"><?php echo htmlspecialchars((string) $sip['description']); ?></span><?php endif; ?>
                            <?php if (!empty($sip['netmask'])) : ?><div class="small text-muted mt-1"><?php echo htmlspecialchars(__('servers.netmask')); ?>: <?php echo htmlspecialchars((string) $sip['netmask']); ?></div><?php endif; ?>
                            <?php if (!empty($sip['gateway'])) : ?><div class="small text-muted"><?php echo htmlspecialchars(__('servers.gateway')); ?>: <?php echo htmlspecialchars((string) $sip['gateway']); ?></div><?php endif; ?>
                            <?php if (!empty($sip['rdns'])) : ?><div class="small text-muted"><?php echo htmlspecialchars(__('servers.rdns')); ?>: <?php echo htmlspecialchars((string) $sip['rdns']); ?></div><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <section class="section-card h-100">
            <div class="section-head">
                <div>
                    <h3><?php echo htmlspecialchars(__('mvc.server_profile_title')); ?></h3>
                    <small class="text-muted"><?php echo htmlspecialchars(__('mvc.server_profile_help')); ?></small>
                </div>
                <span id="server-detail-status" class="status-pill <?php echo htmlspecialchars($status); ?>"><i class="fa-solid fa-circle"></i><span id="server-detail-status-label"><?php echo htmlspecialchars(strtoupper($status)); ?></span></span>
            </div>
            <div class="section-body">
                <div class="server-meta">
                    <div class="meta-box"><span><?php echo htmlspecialchars(__('dashboard.ip_address')); ?></span><strong><code><?php echo htmlspecialchars((string) ($server['ip_address'] ?? '')); ?></code></strong></div>
                    <div class="meta-box"><span><?php echo htmlspecialchars(__('server.location')); ?></span><strong><?php echo htmlspecialchars((string) ($server['location'] ?? __('servers.na'))); ?></strong></div>
                    <div class="meta-box"><span><?php echo htmlspecialchars(__('mvc.server_access_level')); ?></span><strong><?php echo htmlspecialchars((string) $accessLevel); ?></strong></div>
                    <div class="meta-box"><span><?php echo htmlspecialchars(__('servers.ipmi_type')); ?></span><strong><?php echo htmlspecialchars((string) ($server['ipmi_type'] ?? 'generic')); ?></strong></div>
                    <div class="meta-box"><span><?php echo htmlspecialchars(__('server.ipmi_user')); ?></span><strong><?php echo htmlspecialchars((string) ($server['ipmi_username'] ?? '')); ?></strong></div>
                    <div class="meta-box"><span><?php echo htmlspecialchars(__('servers.kvm_mode')); ?></span><strong><?php echo htmlspecialchars(str_replace('_', ' ', (string) ($server['kvm_mode'] ?? 'html5'))); ?></strong></div>
                    <div class="meta-box"><span><?php echo htmlspecialchars(__('server.last_check')); ?></span><strong id="server-detail-last-check"><?php echo htmlspecialchars((string) ($server['last_checked'] ?? __('servers.never'))); ?></strong></div>
                    <?php if (!empty($server['serial_number'])) : ?><div class="meta-box"><span><?php echo htmlspecialchars(__('servers.serial_number')); ?></span><strong><?php echo htmlspecialchars((string) $server['serial_number']); ?></strong></div><?php endif; ?>
                    <?php if (!empty($server['cpu_info'])) : ?><div class="meta-box"><span><?php echo htmlspecialchars(__('servers.cpu_info')); ?></span><strong><?php echo htmlspecialchars((string) $server['cpu_info']); ?></strong></div><?php endif; ?>
                    <?php if (!empty($server['ram_gb'])) : ?><div class="meta-box"><span><?php echo htmlspecialchars(__('servers.ram_gb')); ?></span><strong><?php echo htmlspecialchars((string) $server['ram_gb']); ?> GB</strong></div><?php endif; ?>
                    <?php if (!empty($server['disk_info'])) : ?><div class="meta-box"><span><?php echo htmlspecialchars(__('servers.disk_info')); ?></span><strong><?php echo htmlspecialchars((string) $server['disk_info']); ?></strong></div><?php endif; ?>
                    <?php if (!empty($server['switch_port'])) : ?><div class="meta-box"><span><?php echo htmlspecialchars(__('servers.switch_port')); ?></span><strong><code><?php echo htmlspecialchars((string) $server['switch_port']); ?></code></strong></div><?php endif; ?>
                    <?php if (!empty($isAdmin)) : ?>
                        <div class="meta-box meta-box-wide"><span><?php echo htmlspecialchars(__('servers.tls_fingerprint')); ?></span><strong class="meta-value-mono"><?php echo htmlspecialchars((string) ($server['tls_fingerprint'] ?? '')); ?></strong></div>
                        <div class="meta-box meta-box-wide"><span><?php echo htmlspecialchars(__('servers.api_token')); ?></span><strong class="meta-value-mono"><?php echo htmlspecialchars((string) ($server['api_token'] ?? '')); ?></strong></div>
                        <?php if (!empty($server['notes'])) : ?><div class="meta-box meta-box-wide"><span><?php echo htmlspecialchars(__('servers.notes')); ?></span><strong><?php echo nl2br(htmlspecialchars((string) $server['notes'])); ?></strong></div><?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
    <div class="col-xl-4">
        <section class="section-card h-100">
            <div class="section-head">
                <div>
                    <h3><?php echo htmlspecialchars(__('mvc.server_actions_title')); ?></h3>
                    <small class="text-muted"><?php echo htmlspecialchars(__('mvc.server_actions_help')); ?></small>
                </div>
            </div>
            <div class="section-body">
                <div class="quick-grid">
                    <a class="quick-link" target="_blank" href="<?php echo routeUrl('/kvm/open', ['id' => (int) ($server['id'] ?? 0)]); ?>"><span><strong><?php echo htmlspecialchars(__('mvc.server_open_kvm')); ?></strong><br><small><?php echo htmlspecialchars(__('mvc.server_open_kvm_help')); ?></small></span><i class="fa-solid fa-up-right-from-square"></i></a>
                    <?php if (!empty($isAdmin)) : ?>
                        <a class="quick-link" href="<?php echo routeUrl('/isos'); ?>"><span><strong><?php echo htmlspecialchars(__('mvc.server_iso_workspace')); ?></strong><br><small><?php echo htmlspecialchars(__('mvc.server_iso_workspace_help')); ?></small></span><i class="fa-solid fa-arrow-right"></i></a>
                        <a class="quick-link" href="<?php echo routeUrl('/servers/edit', ['id' => (int) ($server['id'] ?? 0)]); ?>"><span><strong><?php echo htmlspecialchars(__('mvc.servers_edit')); ?></strong><br><small><?php echo htmlspecialchars(__('mvc.server_edit_help')); ?></small></span><i class="fa-solid fa-arrow-right"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
    <div class="col-12">
        <div class="row g-4">
            <div class="col-xl-6">
                <section class="section-card h-100">
                    <div class="section-head">
                        <div>
                            <h3><?php echo htmlspecialchars(__('server.power_control')); ?></h3>
                            <small class="text-muted"><?php echo htmlspecialchars(__('mvc.server_power_help')); ?></small>
                        </div>
                    </div>
                    <div class="section-body">
                        <?php if (!empty($canPower)) : ?>
                            <form method="post" action="<?php echo routeUrl('/server/power'); ?>">
                                <?php echo \App\Http\Csrf::getHiddenField(); ?>
                                <input type="hidden" name="server_id" value="<?php echo (int) ($server['id'] ?? 0); ?>">
                                <div class="tool-row">
                                    <?php if (!empty($canPowerFull)) : ?>
                                        <button class="btn btn-success" type="submit" name="power_action" value="on" <?php echo $status === 'online' ? 'disabled' : ''; ?>><?php echo htmlspecialchars(__('server.power_on')); ?></button>
                                        <button class="btn btn-danger" type="submit" name="power_action" value="off" <?php echo $status === 'offline' ? 'disabled' : ''; ?>><?php echo htmlspecialchars(__('server.power_off')); ?></button>
                                    <?php endif; ?>
                                    <button class="btn btn-outline-primary" type="submit" name="power_action" value="cycle" <?php echo $status === 'offline' ? 'disabled' : ''; ?>><?php echo htmlspecialchars(__('server.restart')); ?></button>
                                    <button class="btn btn-warning" type="submit" name="power_action" value="reset" <?php echo $status === 'offline' ? 'disabled' : ''; ?>><?php echo htmlspecialchars(__('server.hard_reset')); ?></button>
                                </div>
                            </form>
                            <div class="small text-muted mt-3"><strong><?php echo htmlspecialchars(__('server.hard_reset')); ?>:</strong> <?php echo htmlspecialchars(__('server.hard_reset_help')); ?></div>
                        <?php else : ?>
                            <div class="alert alert-light border mb-0"><?php echo htmlspecialchars(__('mvc.server_no_power_access')); ?></div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
            <div class="col-xl-6">
                <section class="section-card h-100">
                    <div class="section-head">
                        <div>
                            <h3><?php echo htmlspecialchars(__('server.mounted_iso')); ?></h3>
                            <small class="text-muted"><?php echo htmlspecialchars(__('server.only_active_isos')); ?></small>
                        </div>
                    </div>
                    <div class="section-body">
                        <?php if (!empty($canManageIso)) : ?>
                            <form method="post" action="<?php echo routeUrl('/isos/mount'); ?>" class="d-grid gap-3">
                                <?php echo \App\Http\Csrf::getHiddenField(); ?>
                                <input type="hidden" name="server_id" value="<?php echo (int) ($server['id'] ?? 0); ?>">
                                <input type="hidden" name="return_to" value="<?php echo routeUrl('/server', ['id' => (int) ($server['id'] ?? 0)]); ?>">
                                <div>
                                    <label class="form-label"><?php echo htmlspecialchars(__('server.select_iso')); ?></label>
                                    <select class="form-select" name="iso_id" required>
                                        <option value=""><?php echo htmlspecialchars(__('server.select_active_iso')); ?></option>
                                        <?php foreach (($activeIsos ?? []) as $iso) : ?>
                                            <option value="<?php echo (int) $iso['id']; ?>" <?php echo isset($mountStateRow['iso_id']) && (int) $mountStateRow['iso_id'] === (int) $iso['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars((string) $iso['name']); ?> (<?php echo htmlspecialchars(strtoupper((string) ($iso['source_type'] ?? 'LOCAL'))); ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="tool-row">
                                    <button class="btn btn-brand" type="submit"><?php echo htmlspecialchars(__('server.mount_iso')); ?></button>
                                </div>
                            </form>
                            <form method="post" action="<?php echo routeUrl('/isos/unmount'); ?>" class="mt-3">
                                <?php echo \App\Http\Csrf::getHiddenField(); ?>
                                <input type="hidden" name="server_id" value="<?php echo (int) ($server['id'] ?? 0); ?>">
                                <input type="hidden" name="return_to" value="<?php echo routeUrl('/server', ['id' => (int) ($server['id'] ?? 0)]); ?>">
                                <button class="btn btn-outline-warning" type="submit"><?php echo htmlspecialchars(__('server.unmount')); ?></button>
                            </form>
                            <form method="post" action="<?php echo routeUrl('/isos/refresh'); ?>" class="mt-3">
                                <?php echo \App\Http\Csrf::getHiddenField(); ?>
                                <input type="hidden" name="server_id" value="<?php echo (int) ($server['id'] ?? 0); ?>">
                                <input type="hidden" name="return_to" value="<?php echo routeUrl('/server', ['id' => (int) ($server['id'] ?? 0)]); ?>">
                                <button class="btn btn-outline-secondary" type="submit"><?php echo htmlspecialchars(__('server.refresh_status')); ?></button>
                            </form>
                            <div class="alert alert-light border mt-3 mb-0">
                                <div class="fw-semibold"><?php echo htmlspecialchars(__('server.current_iso_status')); ?></div>
                                <div><?php echo htmlspecialchars((string) (($mountStateRow['label'] ?? $mountStateRow['mounted_label'] ?? __('server.no_cached_status')))); ?></div>
                                <div class="small text-muted mt-1"><?php echo !empty($mountStateRow['checked_at']) ? htmlspecialchars(__('server.last_verification', ['time' => (string) $mountStateRow['checked_at']])) : ''; ?></div>
                            </div>
                        <?php else : ?>
                            <div class="alert alert-light border mb-0"><?php echo htmlspecialchars(__('mvc.server_iso_workspace_help')); ?></div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
<?php if (!empty($isAdmin)) : ?>
<div class="row g-4 mt-0">
    <div class="col-xl-6">
        <section class="section-card h-100">
            <div class="section-head">
                <div>
                    <h3><?php echo htmlspecialchars(__('server.boot_device')); ?></h3>
                    <small class="text-muted"><?php echo htmlspecialchars(__('server.boot_device_help')); ?></small>
                </div>
            </div>
            <div class="section-body">
                <form method="post" action="<?php echo routeUrl('/server/bootdev'); ?>">
                    <?php echo \App\Http\Csrf::getHiddenField(); ?>
                    <input type="hidden" name="server_id" value="<?php echo (int) ($server['id'] ?? 0); ?>">
                    <div class="mb-3">
                        <label class="form-label"><?php echo htmlspecialchars(__('server.boot_device')); ?></label>
                        <select class="form-select" name="boot_device">
                            <option value="pxe"><?php echo htmlspecialchars(__('server.boot_pxe')); ?></option>
                            <option value="disk" selected><?php echo htmlspecialchars(__('server.boot_disk')); ?></option>
                            <option value="cdrom"><?php echo htmlspecialchars(__('server.boot_cdrom')); ?></option>
                            <option value="bios"><?php echo htmlspecialchars(__('server.boot_bios')); ?></option>
                        </select>
                    </div>
                    <div class="mb-3 form-check">
                        <input class="form-check-input" type="checkbox" name="persistent" value="1" id="boot-persistent">
                        <label class="form-check-label" for="boot-persistent"><?php echo htmlspecialchars(__('server.boot_persistent')); ?></label>
                    </div>
                    <button class="btn btn-brand" type="submit"><i class="fa-solid fa-hard-drive me-2"></i><?php echo htmlspecialchars(__('server.boot_device')); ?></button>
                </form>
            </div>
        </section>
    </div>
    <div class="col-xl-6">
        <section class="section-card h-100">
            <div class="section-head">
                <div>
                    <h3><?php echo htmlspecialchars(__('server.bmc_reset')); ?></h3>
                    <small class="text-muted"><?php echo htmlspecialchars(__('server.bmc_reset_help')); ?></small>
                </div>
            </div>
            <div class="section-body">
                <form method="post" action="<?php echo routeUrl('/server/bmc-reset'); ?>" onsubmit="return confirm('<?php echo htmlspecialchars(__('server.bmc_reset_confirm')); ?>');">
                    <?php echo \App\Http\Csrf::getHiddenField(); ?>
                    <input type="hidden" name="server_id" value="<?php echo (int) ($server['id'] ?? 0); ?>">
                    <div class="tool-row">
                        <button class="btn btn-outline-warning" type="submit" name="reset_type" value="warm">
                            <i class="fa-solid fa-rotate me-2"></i><?php echo htmlspecialchars(__('server.bmc_warm_reset')); ?>
                        </button>
                        <button class="btn btn-outline-danger" type="submit" name="reset_type" value="cold">
                            <i class="fa-solid fa-power-off me-2"></i><?php echo htmlspecialchars(__('server.bmc_cold_reset')); ?>
                        </button>
                    </div>
                    <div class="small text-muted mt-3">
                        <strong><?php echo htmlspecialchars(__('server.bmc_warm_reset')); ?>:</strong> <?php echo htmlspecialchars(__('server.bmc_warm_reset_help')); ?><br>
                        <strong><?php echo htmlspecialchars(__('server.bmc_cold_reset')); ?>:</strong> <?php echo htmlspecialchars(__('server.bmc_cold_reset_help')); ?>
                    </div>
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
                    <h3><?php echo htmlspecialchars(__('server.ipmi_users')); ?></h3>
                    <small class="text-muted"><?php echo htmlspecialchars(__('server.ipmi_users_help')); ?></small>
                </div>
                <button class="btn btn-outline-secondary btn-sm" id="btn-load-ipmi-users"><i class="fa-solid fa-users me-2"></i><?php echo htmlspecialchars(__('server.ipmi_users_load')); ?></button>
            </div>
            <div class="section-body">
                <div id="ipmi-users-container">
                    <div class="text-muted small">Click "Load users from BMC" to fetch current IPMI accounts.</div>
                </div>
                <hr>
                <div>
                    <h6 class="mb-3"><?php echo htmlspecialchars(__('server.ipmi_user_add')); ?></h6>
                    <form method="post" action="<?php echo routeUrl('/server/ipmi-users/create'); ?>" class="row g-2 align-items-end">
                        <?php echo \App\Http\Csrf::getHiddenField(); ?>
                        <input type="hidden" name="server_id" value="<?php echo (int) ($server['id'] ?? 0); ?>">
                        <div class="col-md-3">
                            <label class="form-label form-label-sm"><?php echo htmlspecialchars(__('server.ipmi_user_username')); ?></label>
                            <input class="form-control form-control-sm" type="text" name="ipmi_username" required maxlength="16" pattern="[a-zA-Z0-9_\-]+" placeholder="ipmiuser1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label form-label-sm"><?php echo htmlspecialchars(__('server.ipmi_user_password')); ?></label>
                            <input class="form-control form-control-sm" type="password" name="ipmi_password" required minlength="6" maxlength="20">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label form-label-sm"><?php echo htmlspecialchars(__('server.ipmi_user_priv_level')); ?></label>
                            <select class="form-select form-select-sm" name="priv_level">
                                <option value="2"><?php echo htmlspecialchars(__('server.ipmi_user_priv_user')); ?></option>
                                <option value="3"><?php echo htmlspecialchars(__('server.ipmi_user_priv_operator')); ?></option>
                                <option value="4" selected><?php echo htmlspecialchars(__('server.ipmi_user_priv_admin')); ?></option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-brand btn-sm w-100" type="submit"><i class="fa-solid fa-user-plus me-1"></i><?php echo htmlspecialchars(__('server.ipmi_user_add')); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>
<?php endif; ?>

<script>
(function () {
    var btn = document.getElementById('btn-load-ipmi-users');
    var container = document.getElementById('ipmi-users-container');
    if (!btn || !container) return;
    btn.addEventListener('click', function () {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Loading...';
        fetch('<?php echo routeUrl('/server/ipmi-users'); ?>?id=<?php echo (int) ($server['id'] ?? 0); ?>', { credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : Promise.reject(r); })
            .then(function (data) {
                if (data.error) { container.innerHTML = '<div class="alert alert-danger mb-0">' + data.error + '</div>'; return; }
                if (!data.users || data.users.length === 0) { container.innerHTML = '<div class="text-muted small">No users found on this BMC.</div>'; return; }
                var rows = data.users.map(function (u) {
                    var name = u.name || '<em class="text-muted"><?php echo htmlspecialchars(__('server.ipmi_user_empty_slot')); ?></em>';
                    var removeBtn = u.id > 1 && u.name ? '<form method="post" action="<?php echo routeUrl('/server/ipmi-users/delete'); ?>" style="display:inline" onsubmit="return confirm(\'<?php echo htmlspecialchars(__('server.ipmi_user_remove_confirm')); ?>\')"><?php echo \App\Http\Csrf::getHiddenField(); ?><input type="hidden" name="server_id" value="<?php echo (int) ($server['id'] ?? 0); ?>"><input type="hidden" name="ipmi_user_id" value="' + u.id + '"><button class="btn btn-outline-danger btn-sm" type="submit"><?php echo htmlspecialchars(__('server.ipmi_user_remove')); ?></button></form>' : '';
                    return '<tr><td>' + u.id + '</td><td>' + name + '</td><td><code>' + (u.priv || '-') + '</code></td><td>' + removeBtn + '</td></tr>';
                }).join('');
                container.innerHTML = '<table class="table soft-table align-middle mb-0"><thead><tr><th><?php echo htmlspecialchars(__('server.ipmi_user_slot')); ?></th><th><?php echo htmlspecialchars(__('server.ipmi_user_name')); ?></th><th><?php echo htmlspecialchars(__('server.ipmi_user_priv')); ?></th><th></th></tr></thead><tbody>' + rows + '</tbody></table>';
            })
            .catch(function () { container.innerHTML = '<div class="alert alert-danger mb-0">Failed to load IPMI users.</div>'; })
            .finally(function () { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-users me-2"></i><?php echo htmlspecialchars(__('server.ipmi_users_load')); ?>'; });
    });
})();
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var overlay = document.getElementById('iso-processing-overlay');
    document.querySelectorAll('form[action="<?php echo routeUrl('/isos/mount'); ?>"], form[action="<?php echo routeUrl('/isos/unmount'); ?>"], form[action="<?php echo routeUrl('/isos/refresh'); ?>"]').forEach(function (form) {
        form.addEventListener('submit', function () {
            if (overlay) {
                overlay.hidden = false;
            }
        });
    });
    fetch('<?php echo routeUrl('/servers/status', ['id' => (int) ($server['id'] ?? 0)]); ?>', { credentials: 'same-origin' })
        .then(function (response) { return response.ok ? response.json() : null; })
        .then(function (payload) {
            if (!payload || !payload.status) {
                return;
            }
            var pill = document.getElementById('server-detail-status');
            var label = document.getElementById('server-detail-status-label');
            var lastCheck = document.getElementById('server-detail-last-check');
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
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
