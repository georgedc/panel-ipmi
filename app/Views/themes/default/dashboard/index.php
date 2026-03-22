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
                <span class="brand-mark"><i class="fa-solid fa-network-wired"></i></span>
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
                <a class="btn btn-light" href="<?php echo routeUrl('/servers'); ?>"><i class="fa-solid fa-server me-2"></i><?php echo htmlspecialchars(__('mvc.dashboard_open_servers')); ?></a>
                <?php if ($isAdmin) : ?>
                    <a class="btn btn-outline-light" href="<?php echo routeUrl('/isos'); ?>"><i class="fa-solid fa-compact-disc me-2"></i><?php echo htmlspecialchars(__('mvc.dashboard_iso_mount')); ?></a>
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
                <a class="btn btn-sm btn-outline-secondary" href="<?php echo routeUrl('/servers'); ?>"><?php echo htmlspecialchars(__('mvc.dashboard_view_servers')); ?></a>
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
                    <a class="quick-link" href="<?php echo routeUrl('/servers'); ?>"><span><strong><?php echo htmlspecialchars(__('app.servers')); ?></strong><br><small><?php echo htmlspecialchars(__('mvc.dashboard_servers_link_help')); ?></small></span><i class="fa-solid fa-arrow-right"></i></a>
                    <?php if ($isAdmin) : ?>
                        <a class="quick-link" href="<?php echo routeUrl('/isos'); ?>"><span><strong><?php echo htmlspecialchars(__('mvc.dashboard_iso_mount')); ?></strong><br><small><?php echo htmlspecialchars(__('mvc.dashboard_iso_help')); ?></small></span><i class="fa-solid fa-arrow-right"></i></a>
                        <a class="quick-link" href="<?php echo routeUrl('/iso-admin'); ?>"><span><strong><?php echo htmlspecialchars(__('mvc.iso_admin_title')); ?></strong><br><small><?php echo htmlspecialchars(__('mvc.dashboard_iso_catalog_help')); ?></small></span><i class="fa-solid fa-arrow-right"></i></a>
                        <a class="quick-link" href="<?php echo routeUrl('/users'); ?>"><span><strong><?php echo htmlspecialchars(__('app.users')); ?></strong><br><small><?php echo htmlspecialchars(__('mvc.dashboard_users_help')); ?></small></span><i class="fa-solid fa-arrow-right"></i></a>
                        <a class="quick-link" href="<?php echo routeUrl('/settings'); ?>"><span><strong><?php echo htmlspecialchars(__('app.settings')); ?></strong><br><small><?php echo htmlspecialchars(__('mvc.dashboard_settings_help')); ?></small></span><i class="fa-solid fa-arrow-right"></i></a>
                        <a class="quick-link" href="<?php echo routeUrl('/logs'); ?>"><span><strong><?php echo htmlspecialchars(__('app.logs')); ?></strong><br><small><?php echo htmlspecialchars(__('logs.title')); ?></small></span><i class="fa-solid fa-arrow-right"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
