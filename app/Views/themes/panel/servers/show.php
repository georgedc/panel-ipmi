<?php
ob_start();
$accessLevel = $accessLevel ?? 'readonly';
$status = strtolower((string) ($server['status'] ?? 'offline'));
$serverIps = $serverIps ?? [];
$displayLabel = !empty($server['client_label']) ? $server['client_label'] : ($server['name'] ?? '');
?>
<style>
/* Tabs */
.sv-tabs { display: flex; gap: 0; border-bottom: 1px solid var(--panel-line); margin-bottom: 24px; }
.sv-tab-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 11px 18px; border: none; background: transparent;
    color: var(--panel-muted); font-weight: 600; font-size: .88rem;
    border-bottom: 3px solid transparent; cursor: pointer;
    transition: color .15s, border-color .15s;
}
.sv-tab-btn:hover { color: var(--panel-blue); }
.sv-tab-btn.active { color: var(--panel-blue); border-bottom-color: var(--panel-blue); }
.sv-tab-btn .material-icons-round { font-size: 16px; }
.sv-tab-pane { display: none; }
.sv-tab-pane.active { display: block; }
/* Sensitive fields */
.sv-secret { filter: blur(4px); transition: filter .2s; user-select: none; }
/* ISO overlay */
.iso-processing-overlay{position:fixed;inset:0;background:rgba(9,17,29,.55);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;z-index:1200;padding:24px}
.iso-processing-card{width:min(460px,100%);background:#fff;border-radius:18px;padding:28px;box-shadow:0 24px 60px rgba(15,23,42,.22);text-align:center}
.iso-processing-card h3{margin:0 0 10px;font-size:1.15rem;color:#12212f}
.iso-processing-card p{margin:0;color:#526274}
.iso-processing-spinner{width:42px;height:42px;border:4px solid #d7dee7;border-top-color:#0e7490;border-radius:50%;margin:0 auto 18px;animation:iso-spin .9s linear infinite}
@keyframes iso-spin{to{transform:rotate(360deg)}}
</style>

<div class="hero-banner mb-4">
    <div class="row g-4 align-items-center">
        <div class="col-lg-8">
            <h1 class="h3 mb-2 text-white"><?php echo htmlspecialchars((string) ($server['name'] ?? __('dashboard.server'))); ?></h1>
            <p class="mb-0"><?php echo htmlspecialchars(__('mvc.server_detail_subtitle')); ?></p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <a class="btn panel-dash-btn" href="<?php echo routeUrl('/dashboard'); ?>">
                <i class="material-icons-round" style="font-size:18px;vertical-align:middle;">home</i>
                <span><?php echo htmlspecialchars(__('app.dashboard')); ?></span>
            </a>
        </div>
    </div>
</div>

<?php if (!empty($flash_power_success)) : ?>
    <div class="alert alert-success border-0 shadow-sm mb-4"><?php echo htmlspecialchars($flash_power_success); ?></div>
<?php endif; ?>
<?php if (!empty($flash_power_error)) : ?>
    <div class="alert alert-danger border-0 shadow-sm mb-4"><?php echo htmlspecialchars($flash_power_error); ?></div>
<?php endif; ?>
<?php if (!empty($flash_iso_success)) : ?>
    <div class="alert alert-success border-0 shadow-sm mb-4"><?php echo htmlspecialchars($flash_iso_success); ?></div>
<?php endif; ?>
<?php if (!empty($flash_iso_error)) : ?>
    <div class="alert alert-danger border-0 shadow-sm mb-4"><?php echo htmlspecialchars($flash_iso_error); ?></div>
<?php endif; ?>

<div id="iso-processing-overlay" class="iso-processing-overlay" hidden>
    <div class="iso-processing-card">
        <div class="iso-processing-spinner"></div>
        <h3><?php echo htmlspecialchars(__('iso.processing_title')); ?></h3>
        <p><?php echo htmlspecialchars(__('iso.processing_message')); ?></p>
    </div>
</div>

<!-- Tab navigation -->
<div class="sv-tabs" id="sv-tabs">
    <button class="sv-tab-btn active" data-tab="sv-overview">
        <i class="material-icons-round">info</i>
        <?php echo htmlspecialchars(__('servers.server_info')); ?>
    </button>
    <button class="sv-tab-btn" data-tab="sv-profile">
        <i class="material-icons-round">manage_accounts</i>
        <?php echo htmlspecialchars(__('mvc.server_profile_title')); ?>
    </button>
    <button class="sv-tab-btn" data-tab="sv-power">
        <i class="material-icons-round">power_settings_new</i>
        <?php echo htmlspecialchars(__('server.power_control')); ?>
    </button>
    <?php if (!empty($isAdmin)) : ?>
    <button class="sv-tab-btn" data-tab="sv-ipmi">
        <i class="material-icons-round">group</i>
        <?php echo htmlspecialchars(__('server.ipmi_users')); ?>
    </button>
    <?php endif; ?>
</div>

<!-- ── Tab 1: Overview ──────────────────────────────────────── -->
<div id="sv-overview" class="sv-tab-pane active">
    <div class="row g-4">
        <div class="col-xl-4">
            <section class="section-card h-100">
                <div class="section-head"><h3><?php echo htmlspecialchars(__('servers.server_info')); ?></h3></div>
                <div class="section-body">
                    <div class="server-meta">
                        <div class="meta-box">
                            <span><?php echo htmlspecialchars(__('servers.server_id')); ?></span>
                            <strong>#<?php echo (int) ($server['id'] ?? 0); ?></strong>
                        </div>
                        <div class="meta-box">
                            <span><?php echo htmlspecialchars(__('servers.server_label')); ?></span>
                            <strong><?php echo htmlspecialchars($displayLabel); ?></strong>
                        </div>
                        <div class="meta-box">
                            <span><?php echo htmlspecialchars(__('server.location')); ?></span>
                            <strong><?php echo htmlspecialchars((string) ($server['location'] ?? __('servers.na'))); ?></strong>
                        </div>
                        <div class="meta-box">
                            <span><?php echo htmlspecialchars(__('servers.status')); ?></span>
                            <strong>
                                <span class="status-pill <?php echo htmlspecialchars($status); ?>">
                                    <span class="status-dot"></span>
                                    <?php echo htmlspecialchars(strtoupper($status)); ?>
                                </span>
                            </strong>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <div class="col-xl-4">
            <section class="section-card h-100">
                <div class="section-head"><h3><?php echo htmlspecialchars(__('servers.inventory')); ?></h3></div>
                <div class="section-body">
                    <div class="server-meta">
                        <?php if (!empty($server['cpu_info'])) : ?>
                            <div class="meta-box"><span><?php echo htmlspecialchars(__('servers.cpu_info')); ?></span><strong><?php echo htmlspecialchars((string) $server['cpu_info']); ?></strong></div>
                        <?php endif; ?>
                        <?php if (!empty($server['ram_gb'])) : ?>
                            <div class="meta-box"><span><?php echo htmlspecialchars(__('servers.ram_gb')); ?></span><strong><?php echo htmlspecialchars((string) $server['ram_gb']); ?> GB</strong></div>
                        <?php endif; ?>
                        <?php if (!empty($server['disk_info'])) : ?>
                            <div class="meta-box"><span><?php echo htmlspecialchars(__('servers.disk_info')); ?></span><strong><?php echo htmlspecialchars((string) $server['disk_info']); ?></strong></div>
                        <?php endif; ?>
                        <?php if (!empty($server['serial_number'])) : ?>
                            <div class="meta-box"><span><?php echo htmlspecialchars(__('servers.serial_number')); ?></span><strong class="font-monospace" style="color:var(--panel-text);"><?php echo htmlspecialchars((string) $server['serial_number']); ?></strong></div>
                        <?php endif; ?>
                        <?php if (empty($server['cpu_info']) && empty($server['ram_gb']) && empty($server['disk_info'])) : ?>
                            <div class="text-muted small"><?php echo htmlspecialchars(__('servers.no_ips')); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
        <div class="col-xl-4">
            <section class="section-card h-100">
                <div class="section-head"><h3><?php echo htmlspecialchars(__('servers.ip_allocation')); ?></h3></div>
                <div class="section-body">
                    <?php if (empty($serverIps)) : ?>
                        <p class="text-muted small mb-0"><?php echo htmlspecialchars(__('servers.no_ips')); ?></p>
                    <?php else : ?>
                        <div class="d-flex flex-column gap-2">
                        <?php foreach ($serverIps as $sip) : ?>
                            <div class="meta-box" style="border-radius:12px;padding:12px;">
                                <strong class="font-monospace" style="color:var(--panel-text);"><?php echo htmlspecialchars((string) $sip['ip_address']); ?></strong>
                                <?php if (!empty($sip['description']) && $sip['description'] !== $sip['ip_address']) : ?>
                                    <span class="badge bg-light text-muted ms-1"><?php echo htmlspecialchars((string) $sip['description']); ?></span>
                                <?php endif; ?>
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
</div>

<!-- ── Tab 2: Profile & Access ──────────────────────────────── -->
<div id="sv-profile" class="sv-tab-pane">
    <div class="row g-4">
        <div class="col-xl-8">
            <section class="section-card h-100">
                <div class="section-head">
                    <div>
                        <h3><?php echo htmlspecialchars(__('mvc.server_profile_title')); ?></h3>
                        <small class="text-muted"><?php echo htmlspecialchars(__('mvc.server_profile_help')); ?></small>
                    </div>
                    <span id="server-detail-status" class="status-pill <?php echo htmlspecialchars($status); ?>">
                        <span class="status-dot"></span>
                        <span id="server-detail-status-label"><?php echo htmlspecialchars(strtoupper($status)); ?></span>
                    </span>
                </div>
                <div class="section-body">
                    <div class="server-meta">
                        <div class="meta-box">
                            <span><?php echo htmlspecialchars(__('dashboard.ip_address')); ?></span>
                            <strong class="font-monospace" style="color:var(--panel-text);"><?php echo htmlspecialchars((string) ($server['ip_address'] ?? '')); ?></strong>
                        </div>
                        <div class="meta-box">
                            <span><?php echo htmlspecialchars(__('server.location')); ?></span>
                            <strong><?php echo htmlspecialchars((string) ($server['location'] ?? __('servers.na'))); ?></strong>
                        </div>
                        <div class="meta-box">
                            <span><?php echo htmlspecialchars(__('mvc.server_access_level')); ?></span>
                            <strong><?php echo htmlspecialchars((string) $accessLevel); ?></strong>
                        </div>
                        <div class="meta-box">
                            <span><?php echo htmlspecialchars(__('servers.ipmi_type')); ?></span>
                            <strong><?php echo htmlspecialchars((string) ($server['ipmi_type'] ?? 'generic')); ?></strong>
                        </div>
                        <div class="meta-box">
                            <span><?php echo htmlspecialchars(__('server.ipmi_user')); ?></span>
                            <strong><?php echo htmlspecialchars((string) ($server['ipmi_username'] ?? '')); ?></strong>
                        </div>
                        <div class="meta-box">
                            <span><?php echo htmlspecialchars(__('servers.kvm_mode')); ?></span>
                            <strong><?php echo htmlspecialchars(str_replace('_', ' ', (string) ($server['kvm_mode'] ?? 'html5'))); ?></strong>
                        </div>
                        <div class="meta-box">
                            <span><?php echo htmlspecialchars(__('server.last_check')); ?></span>
                            <strong id="server-detail-last-check"><?php echo htmlspecialchars((string) ($server['last_checked'] ?? __('servers.never'))); ?></strong>
                        </div>
                        <?php if (!empty($server['switch_port'])) : ?>
                            <div class="meta-box">
                                <span><?php echo htmlspecialchars(__('servers.switch_port')); ?></span>
                                <strong class="font-monospace" style="color:var(--panel-text);"><?php echo htmlspecialchars((string) $server['switch_port']); ?></strong>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($isAdmin)) : ?>
                            <div class="meta-box meta-box-wide">
                                <span><?php echo htmlspecialchars(__('servers.tls_fingerprint')); ?></span>
                                <div style="position:relative;margin-top:4px;">
                                    <span id="sv-tls" class="meta-value-mono font-monospace sv-secret"><?php echo htmlspecialchars((string) ($server['tls_fingerprint'] ?? '—')); ?></span>
                                    <button type="button" class="sv-secret-btn" data-target="sv-tls"
                                            style="position:absolute;top:6px;right:6px;background:#eef0f5;border:none;border-radius:6px;width:28px;height:28px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--panel-muted);"
                                            aria-label="Show/hide">
                                        <i class="material-icons-round" style="font-size:16px;">visibility_off</i>
                                    </button>
                                </div>
                            </div>
                            <div class="meta-box meta-box-wide">
                                <span><?php echo htmlspecialchars(__('servers.api_token')); ?></span>
                                <div style="position:relative;margin-top:4px;display:flex;gap:6px;align-items:center;">
                                    <span id="sv-token" class="meta-value-mono font-monospace sv-secret" style="flex:1;min-width:0;"><?php echo htmlspecialchars((string) ($server['api_token'] ?? '—')); ?></span>
                                    <div style="display:flex;gap:4px;flex-shrink:0;">
                                        <button type="button" class="sv-secret-btn" data-target="sv-token"
                                                style="background:#eef0f5;border:none;border-radius:6px;width:28px;height:28px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--panel-muted);"
                                                aria-label="Show/hide">
                                            <i class="material-icons-round" style="font-size:16px;">visibility_off</i>
                                        </button>
                                        <button type="button" onclick="svCopy('sv-token',this)"
                                                style="background:#eef0f5;border:none;border-radius:6px;width:28px;height:28px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--panel-muted);"
                                                aria-label="Copy token">
                                            <i class="material-icons-round" style="font-size:16px;">content_copy</i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php if (!empty($server['notes'])) : ?>
                                <div class="meta-box meta-box-wide">
                                    <span><?php echo htmlspecialchars(__('servers.notes')); ?></span>
                                    <strong><?php echo nl2br(htmlspecialchars((string) $server['notes'])); ?></strong>
                                </div>
                            <?php endif; ?>
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
                        <a class="quick-link" target="_blank" href="<?php echo routeUrl('/kvm/open', ['id' => (int) ($server['id'] ?? 0)]); ?>">
                            <span><strong><?php echo htmlspecialchars(__('mvc.server_open_kvm')); ?></strong><br><small><?php echo htmlspecialchars(__('mvc.server_open_kvm_help')); ?></small></span>
                            <i class="material-icons-round ql-arrow">open_in_new</i>
                        </a>
                        <?php if (!empty($isAdmin)) : ?>
                            <a class="quick-link" href="<?php echo routeUrl('/isos'); ?>">
                                <span><strong><?php echo htmlspecialchars(__('mvc.server_iso_workspace')); ?></strong><br><small><?php echo htmlspecialchars(__('mvc.server_iso_workspace_help')); ?></small></span>
                                <i class="material-icons-round ql-arrow">arrow_forward</i>
                            </a>
                            <a class="quick-link" href="<?php echo routeUrl('/servers/edit', ['id' => (int) ($server['id'] ?? 0)]); ?>">
                                <span><strong><?php echo htmlspecialchars(__('mvc.servers_edit')); ?></strong><br><small><?php echo htmlspecialchars(__('mvc.server_edit_help')); ?></small></span>
                                <i class="material-icons-round ql-arrow">arrow_forward</i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<!-- ── Tab 3: Power & ISO ───────────────────────────────────── -->
<div id="sv-power" class="sv-tab-pane">
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
                                    <button class="btn btn-success" type="submit" name="power_action" value="on" <?php echo $status === 'online' ? 'disabled' : ''; ?>>
                                        <i class="material-icons-round" style="font-size:15px;vertical-align:middle;">power</i>
                                        <?php echo htmlspecialchars(__('server.power_on')); ?>
                                    </button>
                                    <button class="btn btn-danger" type="submit" name="power_action" value="off" <?php echo $status === 'offline' ? 'disabled' : ''; ?>>
                                        <i class="material-icons-round" style="font-size:15px;vertical-align:middle;">power_off</i>
                                        <?php echo htmlspecialchars(__('server.power_off')); ?>
                                    </button>
                                <?php endif; ?>
                                <button class="btn btn-outline-primary" type="submit" name="power_action" value="cycle" <?php echo $status === 'offline' ? 'disabled' : ''; ?>>
                                    <i class="material-icons-round" style="font-size:15px;vertical-align:middle;">restart_alt</i>
                                    <?php echo htmlspecialchars(__('server.restart')); ?>
                                </button>
                                <button class="btn btn-outline-warning" type="submit" name="power_action" value="reset"
                                        style="border-color:#e67e22;color:#e67e22;"
                                        <?php echo $status === 'offline' ? 'disabled' : ''; ?>>
                                    <i class="material-icons-round" style="font-size:15px;vertical-align:middle;">warning</i>
                                    <?php echo htmlspecialchars(__('server.hard_reset')); ?>
                                </button>
                            </div>
                        </form>
                        <div class="small text-muted mt-3">
                            <strong><?php echo htmlspecialchars(__('server.hard_reset')); ?>:</strong>
                            <?php echo htmlspecialchars(__('server.hard_reset_help')); ?>
                        </div>
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
                        <?php $serverId = (int) ($server['id'] ?? 0); $returnTo = routeUrl('/server', ['id' => $serverId]); ?>
                        <form method="post" action="<?php echo routeUrl('/isos/mount'); ?>" id="iso-mount-form">
                            <?php echo \App\Http\Csrf::getHiddenField(); ?>
                            <input type="hidden" name="server_id" value="<?php echo $serverId; ?>">
                            <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($returnTo); ?>">
                            <div class="mb-3">
                                <label class="form-label"><?php echo htmlspecialchars(__('server.select_iso')); ?></label>
                                <select class="form-select" name="iso_id" required>
                                    <option value=""><?php echo htmlspecialchars(__('server.select_active_iso')); ?></option>
                                    <?php foreach (($activeIsos ?? []) as $iso) : ?>
                                        <option value="<?php echo (int) $iso['id']; ?>"
                                            <?php echo isset($mountStateRow['iso_id']) && (int) $mountStateRow['iso_id'] === (int) $iso['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars((string) $iso['name']); ?>
                                            (<?php echo htmlspecialchars(strtoupper((string) ($iso['source_type'] ?? 'LOCAL'))); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>
                        <form method="post" action="<?php echo routeUrl('/isos/unmount'); ?>" id="iso-unmount-form">
                            <?php echo \App\Http\Csrf::getHiddenField(); ?>
                            <input type="hidden" name="server_id" value="<?php echo $serverId; ?>">
                            <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($returnTo); ?>">
                        </form>
                        <form method="post" action="<?php echo routeUrl('/isos/refresh'); ?>" id="iso-refresh-form">
                            <?php echo \App\Http\Csrf::getHiddenField(); ?>
                            <input type="hidden" name="server_id" value="<?php echo $serverId; ?>">
                            <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($returnTo); ?>">
                        </form>
                        <div class="tool-row mb-3">
                            <button class="btn btn-brand" type="submit" form="iso-mount-form">
                                <i class="material-icons-round" style="font-size:15px;vertical-align:middle;">play_arrow</i>
                                <?php echo htmlspecialchars(__('server.mount_iso')); ?>
                            </button>
                            <button class="btn btn-outline-warning" type="submit" form="iso-unmount-form">
                                <i class="material-icons-round" style="font-size:15px;vertical-align:middle;">eject</i>
                                <?php echo htmlspecialchars(__('server.unmount')); ?>
                            </button>
                            <button class="btn panel-dash-btn" type="submit" form="iso-refresh-form">
                                <i class="material-icons-round" style="font-size:15px;vertical-align:middle;">refresh</i>
                                <?php echo htmlspecialchars(__('server.refresh_status')); ?>
                            </button>
                        </div>
                        <div class="alert alert-light border mb-0">
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
        <?php if (!empty($isAdmin)) : ?>
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
                        <button class="btn btn-brand" type="submit">
                            <i class="material-icons-round" style="font-size:15px;vertical-align:middle;">storage</i>
                            <?php echo htmlspecialchars(__('server.boot_device')); ?>
                        </button>
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
                    <!-- Web UI health check -->
                    <div class="mb-3 p-3" style="background:#f8fafc;border-radius:8px;border:1px solid var(--panel-line);">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small fw-semibold" style="color:var(--panel-text);">
                                <i class="material-icons-round" style="font-size:15px;vertical-align:middle;margin-right:4px;">language</i>
                                Web UI Status
                            </span>
                            <button class="btn panel-dash-btn btn-sm" id="webui-check-btn"
                                    onclick="checkWebUi(<?php echo (int) ($server['id'] ?? 0); ?>)"
                                    style="font-size:.78rem;padding:3px 10px;">
                                <i class="material-icons-round" style="font-size:14px;vertical-align:middle;">wifi_tethering</i>
                                Check
                            </button>
                        </div>
                        <div id="webui-status" style="font-size:.82rem;color:var(--panel-muted);">
                            Not checked yet — click Check to probe the BMC web interface.
                        </div>
                    </div>
                    <p class="text-muted small mb-3">
                        <strong><?php echo htmlspecialchars(__('server.bmc_warm_reset')); ?>:</strong> <?php echo htmlspecialchars(__('server.bmc_warm_reset_help')); ?><br>
                        <strong><?php echo htmlspecialchars(__('server.bmc_cold_reset')); ?>:</strong> <?php echo htmlspecialchars(__('server.bmc_cold_reset_help')); ?>
                    </p>
                    <form method="post" action="<?php echo routeUrl('/server/bmc-reset'); ?>" onsubmit="return confirm('<?php echo htmlspecialchars(__('server.bmc_reset_confirm')); ?>');">
                        <?php echo \App\Http\Csrf::getHiddenField(); ?>
                        <input type="hidden" name="server_id" value="<?php echo (int) ($server['id'] ?? 0); ?>">
                        <div class="tool-row">
                            <button class="btn btn-outline-warning" type="submit" name="reset_type" value="warm">
                                <i class="material-icons-round" style="font-size:15px;vertical-align:middle;">refresh</i>
                                <?php echo htmlspecialchars(__('server.bmc_warm_reset')); ?>
                            </button>
                            <button class="btn btn-outline-danger" type="submit" name="reset_type" value="cold">
                                <i class="material-icons-round" style="font-size:15px;vertical-align:middle;">power_off</i>
                                <?php echo htmlspecialchars(__('server.bmc_cold_reset')); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Tab 4: IPMI Users ────────────────────────────────────── -->
<?php if (!empty($isAdmin)) : ?>
<div id="sv-ipmi" class="sv-tab-pane">
    <section class="section-card">
        <div class="section-head">
            <div>
                <h3><?php echo htmlspecialchars(__('server.ipmi_users')); ?></h3>
                <small class="text-muted"><?php echo htmlspecialchars(__('server.ipmi_users_help')); ?></small>
            </div>
            <button class="btn panel-dash-btn" id="btn-load-ipmi-users">
                <i class="material-icons-round" style="font-size:16px;vertical-align:middle;">group</i>
                <?php echo htmlspecialchars(__('server.ipmi_users_load')); ?>
            </button>
        </div>
        <div class="section-body">
            <div id="ipmi-users-container">
                <div class="text-muted small">Click "Load users from BMC" to fetch current IPMI accounts.</div>
            </div>
            <hr>
            <h6 class="mb-3"><?php echo htmlspecialchars(__('server.ipmi_user_add')); ?></h6>
            <form method="post" action="<?php echo routeUrl('/server/ipmi-users/create'); ?>">
                <?php echo \App\Http\Csrf::getHiddenField(); ?>
                <input type="hidden" name="server_id" value="<?php echo (int) ($server['id'] ?? 0); ?>">
                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <label class="form-label form-label-sm"><?php echo htmlspecialchars(__('server.ipmi_user_username')); ?></label>
                        <input class="form-control form-control-sm" type="text" name="ipmi_username" required maxlength="16" pattern="[a-zA-Z0-9_\-]+" placeholder="ipmiuser1">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm"><?php echo htmlspecialchars(__('server.ipmi_user_password')); ?></label>
                        <input class="form-control form-control-sm" type="password" name="ipmi_password" required minlength="6" maxlength="20">
                    </div>
                </div>
                <div class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label form-label-sm"><?php echo htmlspecialchars(__('server.ipmi_user_priv_level')); ?></label>
                        <select class="form-select form-select-sm" name="priv_level">
                            <option value="2"><?php echo htmlspecialchars(__('server.ipmi_user_priv_user')); ?></option>
                            <option value="3"><?php echo htmlspecialchars(__('server.ipmi_user_priv_operator')); ?></option>
                            <option value="4" selected><?php echo htmlspecialchars(__('server.ipmi_user_priv_admin')); ?></option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-brand w-100" type="submit">
                            <i class="material-icons-round" style="font-size:15px;vertical-align:middle;">person_add</i>
                            <?php echo htmlspecialchars(__('server.ipmi_user_add')); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>
<?php endif; ?>

<script>
(function () {
    // ── Simple tabs ──────────────────────────────────────────
    var tabBtns  = document.querySelectorAll('.sv-tab-btn');
    var tabPanes = document.querySelectorAll('.sv-tab-pane');

    tabBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = btn.dataset.tab;
            tabBtns.forEach(function (b) { b.classList.remove('active'); });
            tabPanes.forEach(function (p) { p.classList.remove('active'); });
            btn.classList.add('active');
            var pane = document.getElementById(target);
            if (pane) pane.classList.add('active');
        });
    });

    // ── Secret field toggles ─────────────────────────────────
    document.querySelectorAll('.sv-secret-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var el = document.getElementById(btn.dataset.target);
            if (!el) return;
            var masked = el.classList.toggle('sv-secret');
            btn.querySelector('i').textContent = masked ? 'visibility_off' : 'visibility';
        });
    });

    // ── Copy helper ──────────────────────────────────────────
    window.svCopy = function (id, btn) {
        var el = document.getElementById(id);
        if (!el) return;
        navigator.clipboard.writeText(el.textContent.trim()).then(function () {
            var icon = btn.querySelector('i');
            icon.textContent = 'check';
            btn.style.background = 'var(--panel-ok)';
            btn.style.color = '#fff';
            setTimeout(function () { icon.textContent = 'content_copy'; btn.style.background = ''; btn.style.color = ''; }, 1500);
        });
    };

    // ── Web UI health check ──────────────────────────────────
    window.checkWebUi = function (serverId) {
        var btn    = document.getElementById('webui-check-btn');
        var status = document.getElementById('webui-status');
        if (!btn || !status) return;
        btn.disabled = true;
        btn.innerHTML = '<i class="material-icons-round" style="font-size:14px;vertical-align:middle;">hourglass_top</i> Checking...';
        status.innerHTML = '<span style="color:var(--panel-muted);">Probing BMC web interface, please wait…</span>';
        fetch('<?php echo routeUrl('/server/webui-check'); ?>?id=' + serverId, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.status === 'ok') {
                    status.innerHTML = '<span style="color:#166534;font-weight:600;">'
                        + '<i class="material-icons-round" style="font-size:14px;vertical-align:middle;margin-right:3px;">check_circle</i>'
                        + data.message + '</span>';
                } else if (data.status === 'hung') {
                    status.innerHTML = '<span style="color:#b45309;font-weight:600;">'
                        + '<i class="material-icons-round" style="font-size:14px;vertical-align:middle;margin-right:3px;">hourglass_disabled</i>'
                        + data.message + '</span>';
                } else if (data.status === 'down') {
                    status.innerHTML = '<span style="color:#b91c1c;font-weight:600;">'
                        + '<i class="material-icons-round" style="font-size:14px;vertical-align:middle;margin-right:3px;">cancel</i>'
                        + data.message + '</span>';
                } else {
                    status.innerHTML = '<span style="color:#92400e;">'
                        + '<i class="material-icons-round" style="font-size:14px;vertical-align:middle;margin-right:3px;">warning</i>'
                        + (data.message || data.error || 'Unknown error') + '</span>';
                }
            })
            .catch(function () {
                status.innerHTML = '<span style="color:#b91c1c;">Request failed — check panel connectivity.</span>';
            })
            .finally(function () {
                btn.disabled = false;
                btn.innerHTML = '<i class="material-icons-round" style="font-size:14px;vertical-align:middle;">wifi_tethering</i> Check';
            });
    };

    // ── IPMI users loader ────────────────────────────────────
    var loadBtn   = document.getElementById('btn-load-ipmi-users');
    var container = document.getElementById('ipmi-users-container');
    if (loadBtn && container) {
        loadBtn.addEventListener('click', function () {
            loadBtn.disabled = true;
            loadBtn.innerHTML = '<i class="material-icons-round" style="font-size:16px;vertical-align:middle;">hourglass_top</i> Loading...';
            fetch('<?php echo routeUrl('/server/ipmi-users'); ?>?id=<?php echo (int) ($server['id'] ?? 0); ?>', { credentials: 'same-origin' })
                .then(function (r) { return r.ok ? r.json() : Promise.reject(r); })
                .then(function (data) {
                    if (data.error) { container.innerHTML = '<div class="alert alert-danger mb-0">' + data.error + '</div>'; return; }
                    if (!data.users || data.users.length === 0) { container.innerHTML = '<div class="text-muted small">No users found on this BMC.</div>'; return; }
                    var rows = data.users.map(function (u) {
                        var name = u.name || '<em class="text-muted"><?php echo htmlspecialchars(__('server.ipmi_user_empty_slot')); ?></em>';
                        var removeBtn = u.id > 1 && u.name ? '<form method="post" action="<?php echo routeUrl('/server/ipmi-users/delete'); ?>" style="display:inline" onsubmit="return confirm(\'<?php echo htmlspecialchars(__('server.ipmi_user_remove_confirm')); ?>\')"><?php echo \App\Http\Csrf::getHiddenField(); ?><input type="hidden" name="server_id" value="<?php echo (int) ($server['id'] ?? 0); ?>"><input type="hidden" name="ipmi_user_id" value="' + u.id + '"><button class="btn btn-outline-danger btn-sm" type="submit"><?php echo htmlspecialchars(__('server.ipmi_user_remove')); ?></button></form>' : '';
                        return '<tr><td>' + u.id + '</td><td>' + name + '</td><td><span class="font-monospace small">' + (u.priv || '—') + '</span></td><td>' + removeBtn + '</td></tr>';
                    }).join('');
                    container.innerHTML = '<table class="table soft-table align-middle mb-0"><thead><tr><th><?php echo htmlspecialchars(__('server.ipmi_user_slot')); ?></th><th><?php echo htmlspecialchars(__('server.ipmi_user_name')); ?></th><th><?php echo htmlspecialchars(__('server.ipmi_user_priv')); ?></th><th></th></tr></thead><tbody>' + rows + '</tbody></table>';
                })
                .catch(function () { container.innerHTML = '<div class="alert alert-danger mb-0">Failed to load IPMI users.</div>'; })
                .finally(function () {
                    loadBtn.disabled = false;
                    loadBtn.innerHTML = '<i class="material-icons-round" style="font-size:16px;vertical-align:middle;">group</i> <?php echo htmlspecialchars(__('server.ipmi_users_load')); ?>';
                });
        });
    }
})();

document.addEventListener('DOMContentLoaded', function () {
    // ISO overlay
    var overlay = document.getElementById('iso-processing-overlay');
    ['iso-mount-form', 'iso-unmount-form', 'iso-refresh-form'].forEach(function (id) {
        var form = document.getElementById(id);
        if (form) form.addEventListener('submit', function () { if (overlay) overlay.hidden = false; });
    });

    // Live status poll
    fetch('<?php echo routeUrl('/servers/status', ['id' => (int) ($server['id'] ?? 0)]); ?>', { credentials: 'same-origin' })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (p) {
            if (!p || !p.status) return;
            var pill  = document.getElementById('server-detail-status');
            var label = document.getElementById('server-detail-status-label');
            var lc    = document.getElementById('server-detail-last-check');
            if (pill)  { pill.classList.remove('online','offline','maintenance'); pill.classList.add(String(p.status).toLowerCase()); }
            if (label) { label.textContent = String(p.status).toUpperCase(); }
            if (lc && p.last_checked) { lc.textContent = p.last_checked; }
        })
        .catch(function () {});
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
