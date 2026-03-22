<?php
ob_start();
$counts = $data['counts'] ?? ['total' => 0, 'online' => 0, 'offline' => 0, 'maintenance' => 0];
$recent = $data['recent_activity'] ?? [];
$user = $data['user'] ?? null;
$isAdmin = (bool) ($data['is_admin'] ?? false);
?>
<div class="hero-banner mb-4">
    <div class="row g-4 align-items-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="brand-mark"><i class="fas fa-network-wired"></i></span>
                <div>
                    <h1 class="h3 mb-1 text-white"><?php echo htmlspecialchars($isAdmin ? __('mvc.dashboard_title_admin') : __('mvc.dashboard_title_client')); ?></h1>
                    <p class="mb-0">
                        <?php if ($user) : ?>
                            <?php echo htmlspecialchars($isAdmin ? __('mvc.dashboard_signed_in_admin', ['name' => (string) ($user['username'] ?? '')]) : __('mvc.dashboard_signed_in_client', ['name' => (string) ($user['username'] ?? '')])); ?>
                        <?php else : ?>
                            <?php echo htmlspecialchars(__('mvc.dashboard_guest_help')); ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="tool-row justify-content-lg-end">
                <a class="btn panel-dash-btn" href="<?php echo routeUrl('/servers'); ?>">
                    <i class="material-icons-round" style="font-size:18px;vertical-align:middle;">dns</i>
                    <span><?php echo htmlspecialchars(__('mvc.dashboard_open_servers')); ?></span>
                </a>
                <?php if ($isAdmin) : ?>
                    <a class="btn panel-dash-btn" href="<?php echo routeUrl('/isos'); ?>">
                        <i class="material-icons-round" style="font-size:18px;vertical-align:middle;">album</i>
                        <span><?php echo htmlspecialchars(__('mvc.dashboard_iso_mount')); ?></span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="label"><?php echo htmlspecialchars(__('dashboard.total_servers')); ?></div>
            <div class="value"><?php echo (int) $counts['total']; ?></div>
            <div class="delta"><?php echo htmlspecialchars(__('mvc.dashboard_total_help')); ?></div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="label"><?php echo htmlspecialchars(__('dashboard.online')); ?></div>
            <div class="value status-positive"><?php echo (int) $counts['online']; ?></div>
            <div class="delta status-positive"><?php echo htmlspecialchars(__('mvc.dashboard_online_help')); ?></div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="label"><?php echo htmlspecialchars(__('dashboard.offline')); ?></div>
            <div class="value status-danger"><?php echo (int) $counts['offline']; ?></div>
            <div class="delta status-danger"><?php echo htmlspecialchars(__('mvc.dashboard_offline_help')); ?></div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="label"><?php echo htmlspecialchars(__('dashboard.maintenance')); ?></div>
            <div class="value status-warning"><?php echo (int) $counts['maintenance']; ?></div>
            <div class="delta status-warning"><?php echo htmlspecialchars(__('mvc.dashboard_maintenance_help')); ?></div>
        </div>
    </div>
</div>
<div class="row g-4">
    <div class="col-xl-8">
        <section class="section-card h-100">
            <div class="section-head">
                <div>
                    <h3><?php echo htmlspecialchars(__('dashboard.recent_activity')); ?></h3>
                    <small class="text-muted"><?php echo htmlspecialchars(__('mvc.dashboard_activity_help')); ?></small>
                </div>
                <a class="btn panel-dash-btn" style="font-size:.82rem;padding:5px 12px;" href="<?php echo routeUrl('/servers'); ?>"><?php echo htmlspecialchars(__('mvc.dashboard_view_servers')); ?></a>
            </div>
            <div class="section-body">
                <?php if (!$recent) : ?>
                    <div class="empty-state">
                        <i class="fa-regular fa-clock fa-2x mb-3"></i>
                        <div><?php echo htmlspecialchars(__('mvc.dashboard_no_recent_activity')); ?></div>
                    </div>
                <?php else : ?>
                    <?php foreach ($recent as $log) : ?>
                        <div class="activity-item">
                            <strong><?php echo htmlspecialchars((string) ($log['action'] ?? __('mvc.dashboard_system_event'))); ?></strong>
                            <span><?php echo htmlspecialchars((string) ($log['server_name'] ?? __('dashboard.system'))); ?> · <?php echo htmlspecialchars((string) ($log['timestamp'] ?? '')); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
    <div class="col-xl-4">
        <section class="section-card h-100">
            <div class="section-head">
                <div>
                    <h3><?php echo htmlspecialchars(__('dashboard.quick_links')); ?></h3>
                    <small class="text-muted"><?php echo htmlspecialchars(__('mvc.dashboard_quick_links_help')); ?></small>
                </div>
            </div>
            <div class="section-body">
                <div class="quick-grid">
                    <a class="quick-link" href="<?php echo routeUrl('/servers'); ?>">
                        <span class="ql-icon" style="background:#e8f0ff;color:#4b537f;"><i class="material-icons-round">dns</i></span>
                        <span class="ql-text"><strong><?php echo htmlspecialchars(__('app.servers')); ?></strong><small><?php echo htmlspecialchars(__('mvc.dashboard_servers_link_help')); ?></small></span>
                        <i class="material-icons-round ql-arrow">chevron_right</i>
                    </a>
                    <?php if ($isAdmin) : ?>
                        <a class="quick-link" href="<?php echo routeUrl('/isos'); ?>">
                            <span class="ql-icon" style="background:#fff4e6;color:#e67e22;"><i class="material-icons-round">album</i></span>
                            <span class="ql-text"><strong><?php echo htmlspecialchars(__('mvc.dashboard_iso_mount')); ?></strong><small><?php echo htmlspecialchars(__('mvc.dashboard_iso_help')); ?></small></span>
                            <i class="material-icons-round ql-arrow">chevron_right</i>
                        </a>
                        <a class="quick-link" href="<?php echo routeUrl('/iso-admin'); ?>">
                            <span class="ql-icon" style="background:#f0fff4;color:#27ae60;"><i class="material-icons-round">folder_open</i></span>
                            <span class="ql-text"><strong><?php echo htmlspecialchars(__('mvc.iso_admin_title')); ?></strong><small><?php echo htmlspecialchars(__('mvc.dashboard_iso_catalog_help')); ?></small></span>
                            <i class="material-icons-round ql-arrow">chevron_right</i>
                        </a>
                        <a class="quick-link" href="<?php echo routeUrl('/users'); ?>">
                            <span class="ql-icon" style="background:#fdf0ff;color:#8e44ad;"><i class="material-icons-round">group</i></span>
                            <span class="ql-text"><strong><?php echo htmlspecialchars(__('app.users')); ?></strong><small><?php echo htmlspecialchars(__('mvc.dashboard_users_help')); ?></small></span>
                            <i class="material-icons-round ql-arrow">chevron_right</i>
                        </a>
                        <a class="quick-link" href="<?php echo routeUrl('/settings'); ?>">
                            <span class="ql-icon" style="background:#fff8e6;color:#d4a017;"><i class="material-icons-round">settings</i></span>
                            <span class="ql-text"><strong><?php echo htmlspecialchars(__('app.settings')); ?></strong><small><?php echo htmlspecialchars(__('mvc.dashboard_settings_help')); ?></small></span>
                            <i class="material-icons-round ql-arrow">chevron_right</i>
                        </a>
                        <a class="quick-link" href="<?php echo routeUrl('/logs'); ?>">
                            <span class="ql-icon" style="background:#f0f8ff;color:#2980b9;"><i class="material-icons-round">list_alt</i></span>
                            <span class="ql-text"><strong><?php echo htmlspecialchars(__('app.logs')); ?></strong><small><?php echo htmlspecialchars(__('logs.title')); ?></small></span>
                            <i class="material-icons-round ql-arrow">chevron_right</i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
