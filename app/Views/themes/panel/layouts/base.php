<?php
/** @var string|null $title */
$route = (string) ($_GET['route'] ?? '/dashboard');
$authService = class_exists(\App\Services\AuthService::class) ? new \App\Services\AuthService() : null;
$currentUser = $authService ? $authService->currentUser() : null;
$isAdmin = $authService ? $authService->isAdmin() : false;
$appName = defined('APP_NAME') ? APP_NAME : 'IPMI Panel';
$themeBase = rtrim(appBasePath(), '/') . '/themes/panel';

$nav = [
    '/dashboard' => ['label' => __('app.dashboard'),        'icon' => 'home',          'class' => 'sidebar-home'],
    '/servers'   => ['label' => __('app.servers'),           'icon' => 'cloud_queue',   'class' => 'sidebar-services'],
];
if ($isAdmin) {
    $nav['/isos']      = ['label' => __('mvc.dashboard_iso_mount'), 'icon' => 'album',        'class' => 'sidebar-services'];
    $nav['/iso-admin'] = ['label' => __('mvc.iso_admin_title'),     'icon' => 'folder_open',  'class' => 'sidebar-support'];
    $nav['/inventory'] = ['label' => __('inventory.title'),         'icon' => 'inventory_2',  'class' => 'sidebar-services'];
    $nav['/users']     = ['label' => __('app.users'),               'icon' => 'group',        'class' => 'sidebar-support'];
    $nav['/backup']    = ['label' => __('backup.title'),            'icon' => 'backup',       'class' => 'sidebar-billing'];
    $nav['/settings']  = ['label' => __('app.settings'),            'icon' => 'settings',     'class' => 'sidebar-billing'];
    $nav['/logs']      = ['label' => __('app.logs'),                'icon' => 'list_alt',     'class' => 'sidebar-affiliates'];
}
$activeRoute = static function (string $path) use ($route): bool {
    if ($path === '/users'  && str_starts_with($route, '/users'))  return true;
    if ($path === '/server' && str_starts_with($route, '/server')) return true;
    return $route === $path;
};

$userInitials = '';
if ($currentUser) {
    $name = (string) ($currentUser['username'] ?? '');
    $userInitials = mb_strtoupper(mb_substr($name, 0, 2));
}
?><!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(appLocale()); ?>">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
    <title><?php echo htmlspecialchars($title ?? $appName); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($description ?? $appName . ' — IPMI server management panel'); ?>">

    <!-- Fonts & icons -->
    <link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Round|Material+Icons+Outlined" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Chettan+2:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.14.0/css/all.css">

    <!-- Theme CSS -->
    <link rel="stylesheet" href="<?php echo $themeBase; ?>/css/styles.css">
    <link rel="stylesheet" href="<?php echo $themeBase; ?>/css/styles_responsive.css">
    <?php if (!$currentUser): ?>
    <link rel="stylesheet" href="<?php echo $themeBase; ?>/css/styles_responsive_not_loggedin.css">
    <?php endif; ?>

    <link rel="icon" href="<?php echo $themeBase; ?>/images/icons/control.ico" type="image/x-icon">
    <style>
        /* ─── Reset body to full-width panel layout ─────────────── */
        body {
            max-width: 100%;
            padding: 0;
            margin: 0;
            font-family: 'Baloo Chettan 2', cursive;
        }
        /* Override panel header: it styles a WHMCS header we don't use */
        header { all: unset; display: block; }

        /* ─── Shell ─────────────────────────────────────────────── */
        .panel-shell {
            display: flex;
            min-height: 100vh;
            background: #ffffff;
        }

        /* ─── Sidebar tweaks (panel already styles .sidebar) ─ */
        .sidebar {
            width: 260px;
            min-width: 260px;
            flex-shrink: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--panel-line);
            box-shadow: 2px 0 8px rgba(0,0,0,.03);
        }
        .sidebar .side_menu { flex: 1; }

        /* Brand row inside sidebar */
        .sidebar-brand {
            padding: 22px 20px 14px;
            border-bottom: 1px solid rgba(0,0,0,.07);
        }
        .sidebar-brand a {
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-brand-icon {
            width: 36px; height: 36px;
            background: #38b6ff;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-size: 18px;
            flex-shrink: 0;
        }
        .sidebar-brand-name {
            font-family: 'Baloo Chettan 2', cursive;
            font-weight: 700;
            font-size: 1rem;
            color: #242c3f;
            line-height: 1.2;
        }
        .sidebar-brand-sub {
            font-size: 0.72rem;
            color: #848eab;
            display: block;
        }

        /* Signed-in user at bottom of sidebar */
        .sidebar-foot {
            padding: 14px 20px;
            border-top: 1px solid rgba(0,0,0,.07);
            background: #fff;
        }
        .sidebar-foot strong { display: block; font-size: 0.9rem; color: #242c3f; }
        .sidebar-foot span   { font-size: 0.78rem; color: #848eab; }
        .sidebar-foot a {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 0.82rem; color: #38b6ff; text-decoration: none;
            margin-top: 8px;
        }
        .sidebar-foot a:hover { color: #1e8ed9; }

        /* ─── Top bar (inside panel-main) ───────────────────────── */
        .panel-topbar {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 10px 28px;
        }
        .panel-topbar-search { position: relative; margin-left: auto; }
        .panel-topbar-search input {
            border: 1px solid #e0e5ec;
            border-radius: 8px;
            padding: 7px 14px 7px 36px;
            font-size: 0.88rem;
            min-width: 210px;
            font-family: 'Baloo Chettan 2', cursive;
            background: #f7f7f7;
            color: #242c3f;
            outline: none;
            transition: border-color .15s;
        }
        .panel-topbar-search input:focus { border-color: #38b6ff; background: #fff; }
        .panel-topbar-search .search-icon {
            position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
            font-size: 18px; color: #848eab; pointer-events: none;
        }
        .search-dropdown {
            position: absolute; top: calc(100% + 6px); right: 0; min-width: 300px;
            background: #fff; border: 1px solid #e0e5ec; border-radius: 10px;
            box-shadow: 0 8px 28px rgba(0,0,0,0.10); overflow: hidden; z-index: 1050;
        }
        .search-result-item {
            display: flex; align-items: center; gap: 10px; padding: 10px 14px;
            color: #242c3f; text-decoration: none; border-bottom: 1px solid #f0f0f0;
            font-size: 0.9rem;
        }
        .search-result-item:last-child { border-bottom: none; }
        .search-result-item:hover { background: #f7f9fc; }
        .search-result-name { flex: 1; font-weight: 600; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .search-result-meta { color: #848eab; font-size: 0.8rem; white-space: nowrap; }
        .search-no-results   { padding: 14px; color: #848eab; text-align: center; font-size: 0.88rem; }

        /* ─── Main content area ──────────────────────────────────── */
        .panel-main {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            background: #ffffff;
        }
        .content-area { padding: 28px 32px 48px; }

        /* ─── Content component overrides (panel flavour) ──── */
        :root {
            --panel-blue:  #38b6ff;
            --panel-blue2: #1e8ed9;
            --panel-text:  #242c3f;
            --panel-muted: #5e6880;
            --panel-line:  #e8edf3;
            --panel-bg:    #ffffff;
            --panel-white: #ffffff;
            --panel-ok:    #27ae60;
            --panel-warn:  #e67e22;
            --panel-danger:#e74c3c;
            --panel-radius: 10px;
        }

        /* hero-banner: replace dark default gradient with panel light style */
        .hero-banner {
            background: linear-gradient(135deg, #e8f6ff 0%, #d0eeff 100%);
            border: 1px solid #b8dfff;
            border-radius: var(--panel-radius);
            padding: 22px 26px;
            color: var(--panel-text);
            box-shadow: none;
        }
        .hero-banner h1,
        .hero-banner .h3 { color: var(--panel-text) !important; }
        .hero-banner p { color: var(--panel-muted) !important; margin: 6px 0 0; }
        .hero-banner .btn-light {
            background: #fff; border: 1px solid #b8dfff; color: var(--panel-blue2);
        }
        .hero-banner .btn-outline-light {
            border-color: #b8dfff; color: var(--panel-blue2);
        }
        .hero-banner .btn-outline-light:hover,
        .hero-banner .btn-light:hover { background: var(--panel-blue); color: #fff; border-color: var(--panel-blue); }

        /* stat-card */
        .stat-card {
            background: var(--panel-white);
            border: 1px solid var(--panel-line);
            border-radius: var(--panel-radius);
            padding: 20px;
            height: 100%;
            box-shadow: 0 2px 8px rgba(0,0,0,.04);
        }
        .stat-card .label {
            font-size: 0.78rem; text-transform: uppercase;
            letter-spacing: .08em; color: var(--panel-muted);
        }
        .stat-card .value {
            font-family: 'Baloo Chettan 2', cursive;
            font-size: 2.2rem; font-weight: 700; line-height: 1.1; margin-top: 8px;
            color: var(--panel-text);
        }
        .stat-card .delta { margin-top: 6px; font-size: 0.86rem; }

        .status-positive { color: var(--panel-ok); }
        /* Dentro de stat-card anular el fondo/borde que pone styles.css del tema */
        .stat-card .status-warning,
        .stat-card .status-danger {
            background: transparent !important;
            border: none !important;
            padding: 0 !important;
        }
        .stat-card .status-warning { color: var(--panel-warn) !important; }
        .stat-card .status-danger  { color: var(--panel-danger) !important; }

        /* section-card */
        .section-card {
            background: var(--panel-white);
            border: 1px solid #e5e7eb;
            border-radius: var(--panel-radius);
            box-shadow: 0 1px 4px rgba(0,0,0,.10);
        }
        .section-head {
            display: flex; justify-content: space-between; align-items: center; gap: 12px;
            padding: 14px 20px;
            border-bottom: 1px solid var(--panel-line);
        }
        .section-head h3 {
            margin: 0;
            font-family: 'Baloo Chettan 2', cursive;
            font-size: 1.05rem; font-weight: 700; color: var(--panel-text);
        }
        .section-body { padding: 20px; }

        /* activity feed */
        .activity-item + .activity-item { border-top: 1px solid var(--panel-line); margin-top: 18px; padding-top: 18px; }
        .activity-item strong { display: block; font-size: 0.9rem; color: var(--panel-text); }
        .activity-item span   { color: var(--panel-muted); font-size: 0.84rem; }

        /* table */
        .soft-table { width: 100%; margin: 0; }
        .soft-table thead th {
            color: var(--panel-muted); text-transform: uppercase;
            font-size: 0.74rem; letter-spacing: .08em;
            background: #fafbfc; border-bottom: 1px solid var(--panel-line);
            font-family: 'Baloo Chettan 2', cursive;
        }
        .soft-table > :not(caption) > * > * { padding: 12px 16px; border-bottom-color: var(--panel-line); vertical-align: middle; }
        .soft-table tbody tr:hover { background: #f0f8ff; }

        /* status pills */
        .status-pill {
            display: inline-flex; align-items: center; gap: 6px;
            border-radius: 999px; padding: 4px 12px;
            font-size: 0.82rem; font-weight: 600;
        }
        .status-pill.online      { background: rgba(39,174,96,.10);  color: var(--panel-ok); }
        .status-pill.offline     { background: rgba(231,76,60,.10);  color: var(--panel-danger); }
        .status-pill.maintenance { background: rgba(230,126,34,.10); color: var(--panel-warn); }
        /* semantic dot inside status-pill */
        .status-dot {
            width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
            display: inline-block;
        }
        .status-pill.online      .status-dot { background: var(--panel-ok); box-shadow: 0 0 0 2px rgba(39,174,96,.25); }
        .status-pill.offline     .status-dot { background: var(--panel-danger); }
        .status-pill.maintenance .status-dot { background: var(--panel-warn); }

        /* quick links */
        .quick-grid { display: grid; gap: 8px; }
        .quick-link {
            display: flex; justify-content: space-between; align-items: center; gap: 10px;
            text-decoration: none; color: var(--panel-text);
            border: 1px solid var(--panel-line); background: #fafbfc;
            padding: 12px 16px; border-radius: var(--panel-radius); transition: .15s;
            font-size: 0.9rem;
        }
        .quick-link:hover { border-color: var(--panel-blue); color: var(--panel-blue); background: #f0f8ff; }

        /* server meta boxes */
        .server-meta { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; }
        .server-meta .meta-box {
            background: #fafbfc; border: 1px solid var(--panel-line);
            border-radius: var(--panel-radius); padding: 13px; min-width: 0;
        }
        .server-meta .meta-box span  { display: block; color: var(--panel-muted); font-size: 0.82rem; margin-bottom: 4px; }
        .server-meta .meta-box strong { display: block; overflow-wrap: anywhere; word-break: break-word; color: var(--panel-text); }
        .meta-box.meta-box-wide { grid-column: 1 / -1; }
        .meta-value-mono {
            display: block; width: 100%; padding: 9px 12px; border-radius: 8px;
            background: #fff; border: 1px solid var(--panel-line);
            font-family: ui-monospace, Menlo, Monaco, Consolas, monospace;
            font-size: 0.82rem; line-height: 1.5; overflow-wrap: anywhere; word-break: break-word;
        }

        /* tool row */
        .tool-row { display: flex; flex-wrap: wrap; gap: 10px; }

        /* btn-brand: panel blue style */
        .btn-brand {
            background: var(--panel-blue);
            border: none; color: #fff;
            font-family: 'Baloo Chettan 2', cursive;
            font-weight: 600;
            border-radius: 8px;
            box-shadow: 0 4px 14px rgba(56,182,255,.25);
            transition: .15s;
        }
        .btn-brand:hover { background: var(--panel-blue2); color: #fff; box-shadow: 0 6px 18px rgba(56,182,255,.30); }

        /* brand-mark icon */
        .brand-mark {
            display: inline-flex; width: 36px; height: 36px; border-radius: 8px;
            align-items: center; justify-content: center;
            background: var(--panel-blue); color: #fff; font-size: 16px;
        }

        /* Normalizar altura: el theme solo aplica padding:14px a text/password/etc
           pero no a number ni a form-select. Los igualamos aquí. */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; appearance: textfield; }
        input[type=number].form-control { padding: 14px; }
        select.form-select {
            padding-top: 14px;
            padding-bottom: 14px;
            padding-left: 14px;
        }

        /* empty state */
        .empty-state {
            border: 1px dashed var(--panel-line); border-radius: var(--panel-radius);
            padding: 32px; text-align: center; color: var(--panel-muted); background: #fafbfc;
        }

        /* ─── Quick links ────────────────────────────────────────────── */
        .quick-link {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--panel-text);
            border: 1px solid var(--panel-line);
            background: #fafbfc;
            padding: 10px 14px;
            border-radius: var(--panel-radius);
            transition: .15s;
        }
        .quick-link:hover { border-color: var(--panel-blue); background: #f0f8ff; color: var(--panel-text); }
        .quick-link:hover .ql-arrow { color: var(--panel-blue); }
        .ql-icon {
            width: 36px; height: 36px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .ql-icon i { font-size: 20px; }
        .ql-text {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .ql-text strong { font-size: 0.9rem; font-weight: 600; }
        .ql-text small  { font-size: 0.78rem; color: var(--panel-muted); }
        .ql-arrow { color: #c8d0da; font-size: 20px !important; flex-shrink: 0; }

        /* ─── Dashboard hero buttons ────────────────────────────────── */
        .panel-dash-btn {
            display: inline-flex; align-items: center; gap: 7px;
            background: #4b537f; color: #fff; border: none;
            font-family: 'Baloo Chettan 2', cursive; font-weight: 700;
            border-radius: 8px; padding: 8px 18px;
            box-shadow: 0 4px 12px rgba(75,83,127,.30);
            transition: background .15s;
        }
        .panel-dash-btn:hover { background: #3a4168; color: #fff; }
        .panel-dash-btn-iso {
            display: inline-flex; align-items: center; gap: 7px;
            background: #e67e22; color: #fff; border: none;
            font-family: 'Baloo Chettan 2', cursive; font-weight: 700;
            border-radius: 8px; padding: 8px 18px;
            box-shadow: 0 4px 12px rgba(230,126,34,.35);
            transition: background .15s;
        }
        .panel-dash-btn-iso:hover { background: #cf6d17; color: #fff; }

        /* ─── Icon action buttons para tablas ───────────────────────── */
        .panel-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 4px;
        }
        .panel-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px; height: 44px;
            border-radius: 8px;
            border: none;
            background: #4b537f;
            color: #fff;
            cursor: pointer;
            text-decoration: none;
            transition: background .15s;
            padding: 0;
        }
        .panel-btn i { font-size: 18px; line-height: 1; }
        .panel-btn:hover { background: #3a4168; color: #fff; }
        .panel-btn-danger   { background: #4b537f; }
        .panel-btn-danger:hover   { background: #c0392b; }
        .panel-btn-mount    { background: #27ae60; }
        .panel-btn-mount:hover    { background: #1e8449; }
        .panel-btn-unmount  { background: #e67e22; }
        .panel-btn-unmount:hover  { background: #ca6f1e; }
        .panel-btn-refresh  { background: #4b537f; }
        .panel-btn-refresh:hover  { background: #3a4168; }
        /* Source type tag */
        .panel-tag {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 6px;
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: .05em;
            background: #e8f0ff;
            color: #4b537f;
        }

        /* ─── btn-outline-secondary / primary: mismo estilo que .btn del tema ─ */
        .btn-outline-secondary,
        .btn-outline-primary {
            background: #4b537f !important;
            background-color: #4b537f !important;
            color: #fff !important;
            border-color: #4b537f !important;
            font-weight: bold !important;
        }
        .btn-outline-secondary:hover,
        .btn-outline-secondary:focus,
        .btn-outline-primary:hover,
        .btn-outline-primary:focus {
            background: #3a4168 !important;
            background-color: #3a4168 !important;
            border-color: #3a4168 !important;
            color: #fff !important;
        }

        /* ─── Responsive ─────────────────────────────────────────── */
        @media (max-width: 900px) {
            .sidebar { width: 230px; min-width: 230px; }
        }

        /* ─── Hamburger button (mobile/tablet only) ────── */
        .topbar-hamburger {
            display: none;
            align-items: center;
            justify-content: center;
            width: 38px; height: 38px;
            border: none; background: none;
            border-radius: 8px;
            cursor: pointer;
            color: var(--panel-text);
            flex-shrink: 0;
            padding: 0;
        }
        .topbar-hamburger:hover { background: #f0f4f8; }
        .topbar-hamburger .bar {
            display: block; width: 20px; height: 2px;
            background: var(--panel-text); border-radius: 2px;
            transition: transform .25s, opacity .25s;
        }
        .topbar-hamburger .bar + .bar { margin-top: 5px; }

        /* ─── Overlay backdrop ───────────────────────────────────── */
        .sidebar-backdrop {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,.45);
            z-index: 1039;
        }

        @media (max-width: 768px) {
            .topbar-hamburger  { display: flex; }

            /* Sidebar off-canvas: oculto a la izquierda por defecto.
               display:flex !important anula el display:none que pone styles_responsive.css */
            .sidebar {
                display: flex !important;
                flex-direction: column;
                position: fixed;
                top: 0; left: 0;
                height: 100vh;
                width: 270px !important;
                min-width: 270px !important;
                z-index: 1040;
                background: #fff !important;
                box-shadow: 4px 0 24px rgba(0,0,0,.18);
                transform: translateX(-100%);
                transition: transform .28s ease;
                overflow-y: auto;
            }

            /* When sidebar is open */
            .panel-shell.sidebar-open .sidebar          { transform: translateX(0); }
            .panel-shell.sidebar-open .sidebar-backdrop { display: block; }

            .content-area  { padding: 16px 16px 32px; }
            .panel-topbar  { padding: 10px 16px; }
        }

        /* ─── Fix tablas del panel: anular el hide agresivo del tema ─ */
        /* styles_responsive.css oculta td/th a ≤760px dejando solo 2 cols.
           Para nuestras tablas (.soft-table) usamos scroll horizontal en su lugar. */
        @media (max-width: 760px) {
            .soft-table                    { table-layout: auto !important; }
            .soft-table th,
            .soft-table td                 { display: table-cell !important; width: auto !important; }
            .soft-table th:first-child,
            .soft-table td:first-child     { width: auto !important; text-align: left !important; }
            .soft-table th:nth-child(2),
            .soft-table td:nth-child(2)    { width: auto !important; text-align: left !important; }
        }
    </style>
</head>
<body>
<?php if ($currentUser): ?>
<div class="panel-shell">

    <!-- ── Sidebar backdrop (móvil) ─────────────────────────────── -->
    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

    <!-- ── Sidebar ──────────────────────────────────────────────── -->
    <div class="sidebar" id="panel-sidebar">

        <div class="sidebar-brand">
            <a href="<?php echo routeUrl('/dashboard'); ?>">
                <div class="sidebar-brand-icon">
                    <span class="material-icons-round" style="font-size:18px;">terminal</span>
                </div>
                <div>
                    <span class="sidebar-brand-name"><?php echo htmlspecialchars($appName); ?></span>
                    <span class="sidebar-brand-sub">IPMI Control</span>
                </div>
            </a>
        </div>

        <div class="side_menu">
            <ul>
                <?php foreach ($nav as $path => $item): ?>
                    <li<?php if ($activeRoute($path)) echo ' class="sidebar_selected"'; ?>>
                        <a href="<?php echo routeUrl($path); ?>">
                            <i class="material-icons-round <?php echo htmlspecialchars($item['class']); ?>"><?php echo htmlspecialchars($item['icon']); ?></i>
                            <span><?php echo htmlspecialchars($item['label']); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

    </div>

    <!-- ── Main area ─────────────────────────────────────────────── -->
    <div class="panel-main">
        <div class="panel-topbar">
            <button class="topbar-hamburger" id="sidebar-toggle" aria-label="Menú">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
            <div class="panel-topbar-search">
                <span class="material-icons-round search-icon">search</span>
                <input id="global-search" type="text"
                    placeholder="<?php echo htmlspecialchars(__('app.search_placeholder')); ?>"
                    autocomplete="off">
                <div id="search-results" class="search-dropdown" hidden></div>
            </div>
            <div class="profile" title="<?php echo htmlspecialchars((string) ($currentUser['email'] ?? $currentUser['username'] ?? '')); ?>">
                <span class="noselect"><?php echo htmlspecialchars($userInitials); ?></span>
            </div>
            <div class="popup_menu">
                <ul>
                    <li><a href="<?php echo routeUrl('/dashboard'); ?>">
                        <span class="material-icons-round sidebar-home">home</span>
                        <?php echo htmlspecialchars(__('app.dashboard')); ?>
                    </a></li>
                    <li><a href="<?php echo routeUrl('/servers'); ?>">
                        <span class="material-icons-round sidebar-services">cloud_queue</span>
                        <?php echo htmlspecialchars(__('app.servers')); ?>
                    </a></li>
                    <li><a href="<?php echo routeUrl('/logout'); ?>">
                        <span class="material-icons">logout</span>
                        <?php echo htmlspecialchars(__('mvc.sign_out')); ?>
                    </a></li>
                </ul>
            </div>
        </div>

        <main class="content-area">
            <?php echo $content ?? ''; ?>
        </main>
    </div>
</div>

<?php else: ?>
<div class="auth_main">
    <div class="auth_page">
        <div class="auth_page_wrap">
            <div class="auth-design auth-design-login"></div>
            <div class="auth_form_wrap">
                <div class="auth_form">
                    <?php echo $content ?? ''; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    // ── Off-canvas sidebar ───────────────────────────────────────
    var shell    = document.querySelector('.panel-shell');
    var toggle   = document.getElementById('sidebar-toggle');
    var backdrop = document.getElementById('sidebar-backdrop');

    function openSidebar()  { shell && shell.classList.add('sidebar-open'); }
    function closeSidebar() { shell && shell.classList.remove('sidebar-open'); }

    if (toggle)   toggle.addEventListener('click', function () {
        shell && shell.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
    });
    if (backdrop) backdrop.addEventListener('click', closeSidebar);

    // Close when navigating on mobile
    document.querySelectorAll('.sidebar a').forEach(function (a) {
        a.addEventListener('click', function () {
            if (window.innerWidth <= 768) closeSidebar();
        });
    });

    // ── Profile popup menu ─────────────────────────────────────
    $('.profile').on('click', function (e) {
        e.stopPropagation();
        $('.popup_menu').css({visibility: 'visible', opacity: '1'});
    });
    $('html').on('click', function () {
        $('.popup_menu').css({visibility: 'hidden', opacity: '0'});
    });
    $('.popup_menu').on('click', function (e) { e.stopPropagation(); });
})();
</script>
<script>
(function () {
    var input   = document.getElementById('global-search');
    var results = document.getElementById('search-results');
    if (!input || !results) return;
    var timer = null;
    var searchUrl = '<?php echo routeUrl('/api/search'); ?>';
    var detailUrl = '<?php echo routeUrl('/server'); ?>';
    input.addEventListener('input', function () {
        clearTimeout(timer);
        var q = input.value.trim();
        if (q.length < 2) { results.hidden = true; return; }
        timer = setTimeout(function () {
            fetch(searchUrl + '?q=' + encodeURIComponent(q), { credentials: 'same-origin' })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (data) {
                    if (!data || !data.results) { results.hidden = true; return; }
                    if (data.results.length === 0) {
                        results.innerHTML = '<div class="search-no-results"><?php echo htmlspecialchars(__('app.search_no_results')); ?></div>';
                        results.hidden = false;
                        return;
                    }
                    results.innerHTML = data.results.map(function (s) {
                        var esc = function (v) { return String(v || '').replace(/&/g,'&amp;').replace(/</g,'&lt;'); };
                        var meta = esc(s.ip_address) + (s.location ? ' · ' + esc(s.location) : '');
                        return '<a class="search-result-item" href="' + detailUrl + '?id=' + encodeURIComponent(s.id) + '">'
                            + '<span class="search-result-name">' + esc(s.name) + '</span>'
                            + '<span class="search-result-meta">' + meta + '</span>'
                            + '</a>';
                    }).join('');
                    results.hidden = false;
                })
                .catch(function () { results.hidden = true; });
        }, 280);
    });
    document.addEventListener('click', function (e) {
        if (!input.contains(e.target) && !results.contains(e.target)) results.hidden = true;
    });
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { results.hidden = true; input.blur(); }
    });
})();
</script>
</body>
</html>
