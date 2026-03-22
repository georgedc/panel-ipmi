<?php
/** @var string|null $title */
$route = (string) ($_GET['route'] ?? '/dashboard');
$authService = class_exists(\App\Services\AuthService::class) ? new \App\Services\AuthService() : null;
$currentUser = $authService ? $authService->currentUser() : null;
$isAdmin = $authService ? $authService->isAdmin() : false;
$appName = defined('APP_NAME') ? APP_NAME : 'IPMI Panel';
$nav = [
    '/dashboard' => ['label' => __('app.dashboard'), 'icon' => 'fa-grid-2'],
    '/servers' => ['label' => __('app.servers'), 'icon' => 'fa-server'],
];
if ($isAdmin) {
    $nav['/isos'] = ['label' => __('mvc.dashboard_iso_mount'), 'icon' => 'fa-compact-disc'];
    $nav['/iso-admin'] = ['label' => __('mvc.iso_admin_title'), 'icon' => 'fa-folder-tree'];
    $nav['/inventory'] = ['label' => __('inventory.title'), 'icon' => 'fa-boxes-stacked'];
    $nav['/users'] = ['label' => __('app.users'), 'icon' => 'fa-users'];
    $nav['/backup'] = ['label' => __('backup.title'), 'icon' => 'fa-floppy-disk'];
    $nav['/settings'] = ['label' => __('app.settings'), 'icon' => 'fa-sliders'];
    $nav['/logs'] = ['label' => __('app.logs'), 'icon' => 'fa-clipboard-list'];
}
$activeRoute = static function (string $path) use ($route): bool {
    if ($path === '/users' && str_starts_with($route, '/users')) {
        return true;
    }
    if ($path === '/server' && str_starts_with($route, '/server')) {
        return true;
    }
    return $route === $path;
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? $appName); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg: #edf2f7;
            --panel: #ffffff;
            --panel-soft: #f7fafc;
            --line: #d8e1ea;
            --text: #12212f;
            --muted: #5f7388;
            --nav: #0f1722;
            --nav-soft: #172233;
            --nav-text: #d8e2ef;
            --brand: #0e7490;
            --brand-soft: #d7f2f9;
            --ok: #1f8a5b;
            --warn: #c77d1a;
            --danger: #c14545;
            --shadow: 0 18px 45px rgba(15, 23, 34, 0.08);
            --radius: 20px;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background:
                radial-gradient(circle at top left, rgba(14, 116, 144, 0.16), transparent 28%),
                linear-gradient(180deg, #f7fafc 0%, var(--bg) 100%);
            color: var(--text);
            font-family: 'IBM Plex Sans', sans-serif;
            min-height: 100vh;
        }
        .app-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
        }
        .app-sidebar {
            background: linear-gradient(180deg, #0f1722 0%, #182536 100%);
            color: var(--nav-text);
            padding: 28px 22px;
            display: flex;
            flex-direction: column;
            gap: 28px;
            position: sticky;
            top: 0;
            height: 100vh;
        }
        .brand-mark {
            display: inline-flex;
            width: 46px;
            height: 46px;
            border-radius: 14px;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #11b7d8 0%, #0e7490 100%);
            color: #fff;
            box-shadow: 0 12px 24px rgba(17, 183, 216, 0.25);
        }
        .brand-copy h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.15rem;
            margin: 0;
            font-weight: 700;
            color: #fff;
        }
        .brand-copy p {
            margin: 4px 0 0;
            color: rgba(216, 226, 239, 0.72);
            font-size: 0.92rem;
        }
        .sidebar-section-title {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(216, 226, 239, 0.45);
            margin-bottom: 12px;
        }
        .nav-stack {
            display: grid;
            gap: 10px;
        }
        .nav-link-panel {
            color: var(--nav-text);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 14px;
            background: transparent;
            transition: 0.18s ease;
        }
        .nav-link-panel:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.06);
        }
        .nav-link-panel.active {
            background: linear-gradient(135deg, rgba(17, 183, 216, 0.18), rgba(14, 116, 144, 0.28));
            color: #fff;
            box-shadow: inset 0 0 0 1px rgba(17, 183, 216, 0.25);
        }
        .nav-link-panel i {
            width: 18px;
            text-align: center;
        }
        .sidebar-user {
            margin-top: auto;
            border-radius: 18px;
            padding: 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }
        .sidebar-user strong { color: #fff; display: block; }
        .sidebar-user span { color: rgba(216, 226, 239, 0.65); font-size: 0.92rem; }
        .sidebar-user .btn {
            margin-top: 14px;
            border-color: rgba(255,255,255,0.15);
            color: #fff;
        }
        .app-main {
            min-width: 0;
            padding: 28px;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
            margin-bottom: 26px;
        }
        .topbar h2 {
            margin: 0;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
        }
        .topbar p {
            margin: 6px 0 0;
            color: var(--muted);
        }
        .topbar-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-left: auto;
        }
        .chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.7);
            border: 1px solid var(--line);
            color: var(--muted);
            font-size: 0.92rem;
        }
        .page-panel {
            background: rgba(255,255,255,0.76);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(216, 225, 234, 0.92);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        .hero-banner {
            border-radius: var(--radius);
            padding: 22px 24px;
            background: linear-gradient(135deg, #12324b 0%, #0e7490 58%, #1ab0cb 100%);
            color: #fff;
            box-shadow: 0 18px 40px rgba(14, 116, 144, 0.26);
        }
        .hero-banner p { margin: 8px 0 0; color: rgba(255,255,255,0.82); }
        .stat-card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 18px;
            height: 100%;
            box-shadow: 0 10px 28px rgba(15,23,34,0.05);
        }
        .stat-card .label { color: var(--muted); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.08em; }
        .stat-card .value { font-family: 'Space Grotesk', sans-serif; font-size: 2.2rem; font-weight: 700; line-height: 1.05; margin-top: 10px; }
        .stat-card .delta { margin-top: 10px; font-size: 0.9rem; }
        .status-positive { color: var(--ok); }
        .status-warning { color: var(--warn); }
        .status-danger { color: var(--danger); }
        .section-card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 18px;
            box-shadow: 0 10px 28px rgba(15,23,34,0.05);
        }
        .section-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 18px 20px;
            border-bottom: 1px solid var(--line);
        }
        .section-head h3 {
            margin: 0;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.05rem;
        }
        .section-body { padding: 20px; }
        .activity-item + .activity-item { border-top: 1px solid var(--line); margin-top: 14px; padding-top: 14px; }
        .activity-item strong { display: block; }
        .activity-item span { color: var(--muted); font-size: 0.92rem; }
        .soft-table { width: 100%; margin: 0; }
        .soft-table thead th {
            color: var(--muted);
            text-transform: uppercase;
            font-size: 0.78rem;
            letter-spacing: 0.08em;
            background: var(--panel-soft);
            border-bottom: 1px solid var(--line);
        }
        .soft-table > :not(caption) > * > * {
            padding: 14px 16px;
            border-bottom-color: var(--line);
            vertical-align: middle;
        }
        .soft-table tbody tr:hover { background: rgba(14,116,144,0.04); }
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 0.86rem;
            font-weight: 600;
        }
        .status-pill.online { background: rgba(31,138,91,0.12); color: var(--ok); }
        .status-pill.offline { background: rgba(193,69,69,0.12); color: var(--danger); }
        .status-pill.maintenance { background: rgba(199,125,26,0.12); color: var(--warn); }
        .status-pill-label { display: inline-block; }
        .quick-grid { display: grid; gap: 12px; }
        .quick-link {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--text);
            border: 1px solid var(--line);
            background: var(--panel-soft);
            padding: 14px 16px;
            border-radius: 16px;
            transition: 0.18s ease;
        }
        .quick-link:hover {
            transform: translateY(-1px);
            border-color: rgba(14,116,144,0.35);
            color: var(--brand);
        }
        .server-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
        }
        .server-meta .meta-box {
            background: var(--panel-soft);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 14px;
            min-width: 0;
        }
        .server-meta .meta-box span { display: block; color: var(--muted); font-size: 0.88rem; margin-bottom: 6px; }
        .server-meta .meta-box strong {
            display: block;
            min-width: 0;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        .meta-box.meta-box-wide {
            grid-column: 1 / -1;
        }
        .meta-value-mono {
            display: block;
            width: 100%;
            padding: 10px 12px;
            border-radius: 12px;
            background: #fff;
            border: 1px solid var(--line);
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
            font-size: 0.85rem;
            line-height: 1.55;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        .tool-row { display: flex; flex-wrap: wrap; gap: 12px; }
        .btn-brand {
            background: linear-gradient(135deg, #0e7490, #14b8a6);
            border: none;
            color: #fff;
            box-shadow: 0 14px 28px rgba(14, 116, 144, 0.18);
        }
        .btn-brand:hover { color: #fff; opacity: 0.95; }
        .topbar-search { position: relative; }
        .search-dropdown {
            position: absolute; top: calc(100% + 8px); right: 0; min-width: 320px;
            background: var(--panel); border: 1px solid var(--line); border-radius: 16px;
            box-shadow: 0 20px 48px rgba(15,23,34,0.14); overflow: hidden; z-index: 1050;
        }
        .search-result-item {
            display: flex; align-items: center; gap: 10px; padding: 12px 16px;
            color: var(--text); text-decoration: none; border-bottom: 1px solid var(--line);
        }
        .search-result-item:last-child { border-bottom: none; }
        .search-result-item:hover { background: var(--panel-soft); }
        .search-result-name { font-weight: 600; flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .search-result-meta { color: var(--muted); font-size: 0.85rem; white-space: nowrap; }
        .search-no-results { padding: 16px; color: var(--muted); text-align: center; font-size: 0.92rem; }
        .empty-state {
            border: 1px dashed var(--line);
            border-radius: 18px;
            padding: 28px;
            text-align: center;
            color: var(--muted);
            background: var(--panel-soft);
        }
        .auth-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 28px;
        }
        .auth-main {
            width: min(100%, 1120px);
        }
        .auth-stage {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(360px, 460px);
            gap: 28px;
            align-items: stretch;
        }
        .auth-panel {
            background: rgba(255,255,255,0.78);
            border: 1px solid rgba(216, 225, 234, 0.92);
            border-radius: 28px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(16px);
            overflow: hidden;
        }
        .auth-aside {
            background: linear-gradient(145deg, #0f1722 0%, #12324b 52%, #0e7490 100%);
            color: #fff;
            padding: 34px;
            min-height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .auth-aside h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(2rem, 3vw, 3rem);
            line-height: 1;
            margin: 18px 0 14px;
        }
        .auth-aside p {
            color: rgba(255,255,255,0.82);
            max-width: 44ch;
            margin: 0;
        }
        .auth-points {
            display: grid;
            gap: 14px;
            margin-top: 26px;
        }
        .auth-point {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 14px 16px;
            border-radius: 16px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .auth-point i {
            margin-top: 2px;
        }
        .auth-form {
            padding: 34px;
        }
        .auth-form .form-control {
            min-height: 48px;
            border-radius: 14px;
        }
        .auth-form .btn-brand {
            min-height: 50px;
            border-radius: 14px;
            font-weight: 600;
        }
        .auth-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 24px;
        }
        .auth-meta .chip {
            background: rgba(255,255,255,0.72);
        }
        @media (max-width: 1100px) {
            .app-shell { grid-template-columns: 1fr; }
            .app-sidebar { position: relative; height: auto; }
            .auth-stage { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .app-main { padding: 18px; }
            .topbar { flex-direction: column; align-items: flex-start; }
            .section-head { flex-direction: column; align-items: flex-start; }
            .auth-shell { padding: 18px; }
            .auth-form, .auth-aside { padding: 24px; }
        }
    </style>
</head>
<body>
<?php if ($currentUser) : ?>
<div class="app-shell">
        <aside class="app-sidebar">
            <div class="d-flex align-items-center gap-3">
                <span class="brand-mark"><i class="fa-solid fa-terminal"></i></span>
                <div class="brand-copy">
                    <h1><?php echo htmlspecialchars($appName); ?></h1>
                    <p><?php echo htmlspecialchars(__('mvc.theme_subtitle')); ?></p>
                </div>
            </div>
            <div>
                <div class="sidebar-section-title"><?php echo htmlspecialchars(__('mvc.navigation')); ?></div>
                <nav class="nav-stack">
                    <?php foreach ($nav as $path => $item) : ?>
                        <a class="nav-link-panel <?php echo $activeRoute($path) ? 'active' : ''; ?>" href="<?php echo routeUrl($path); ?>">
                            <i class="fa-solid <?php echo htmlspecialchars($item['icon']); ?>"></i>
                            <span><?php echo htmlspecialchars($item['label']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
            <div class="sidebar-user">
                <strong><?php echo htmlspecialchars((string) ($currentUser['username'] ?? '')); ?></strong>
                <span><?php echo htmlspecialchars($isAdmin ? __('mvc.administrator') : __('mvc.client_account')); ?></span>
                <a class="btn btn-sm btn-outline-light w-100" href="<?php echo routeUrl('/logout'); ?>"><?php echo htmlspecialchars(__('mvc.sign_out')); ?></a>
            </div>
        </aside>
    <main class="app-main">
        <div class="topbar">
            <div class="topbar-meta">
                <div class="topbar-search">
                    <input id="global-search" type="text" class="form-control form-control-sm" placeholder="<?php echo htmlspecialchars(__('app.search_placeholder')); ?>" autocomplete="off" style="min-width:220px;">
                    <div id="search-results" class="search-dropdown" hidden></div>
                </div>
                <span class="chip"><i class="fa-regular fa-circle-user"></i> <?php echo htmlspecialchars((string) ($currentUser['email'] ?? $currentUser['username'] ?? '')); ?></span>
            </div>
        </div>
        <?php echo $content ?? ''; ?>
    </main>
</div>
<?php else : ?>
<div class="auth-shell">
    <main class="auth-main">
        <?php echo $content ?? ''; ?>
    </main>
</div>
<?php endif; ?>
<script>
(function () {
    var input = document.getElementById('global-search');
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
                        var meta = esc(s.ip_address) + (s.location ? ' &middot; ' + esc(s.location) : '');
                        return '<a class="search-result-item" href="' + detailUrl + '?id=' + encodeURIComponent(s.id) + '">' +
                            '<span class="search-result-name">' + esc(s.name) + '</span>' +
                            '<span class="search-result-meta">' + meta + '</span>' +
                            '<span class="status-pill ' + esc(s.status) + '" style="padding:4px 8px;font-size:.75rem"><i class="fa-solid fa-circle"></i></span>' +
                            '</a>';
                    }).join('');
                    results.hidden = false;
                })
                .catch(function () { results.hidden = true; });
        }, 280);
    });
    document.addEventListener('click', function (e) {
        if (!input.contains(e.target) && !results.contains(e.target)) { results.hidden = true; }
    });
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { results.hidden = true; input.blur(); }
    });
})();
</script>
</body>
</html>
