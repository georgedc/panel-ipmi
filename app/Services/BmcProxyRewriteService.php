<?php

namespace App\Services;

final class BmcProxyRewriteService
{
    public static function rewrite(
        string $cookieName,
        string $path,
        int $serverId,
        string $body,
        ?string $contentType,
        string $bmcIp,
        string $panelHost,
        array $viewerStorage,
        array $bmc,
        array $query
    ): array {
        $ext = pathinfo(parse_url($path, PHP_URL_PATH), PATHINFO_EXTENSION);

        if ($ext === 'css' && $cookieName === 'QSESSIONID') {
            $base = '/ipmi-panel/bmc/' . $serverId . '/';
            $body = str_replace('../fonts/', $base . 'fonts/', $body);
            $body = str_replace("../fonts/", $base . 'fonts/', $body);
            $body = str_replace('../images/', $base . 'images/', $body);
            $body = preg_replace('#url\((["\']?)/(fonts|images|css|app)/#', 'url($1' . $base . '$2/', $body);
        }

        if ($ext === 'html' || strpos((string) $contentType, 'html') !== false) {
            $base = '/ipmi-panel/bmc/' . $serverId . '/';
            $isHtml5BootstrapPage = ($path === '/cgi/url_redirect.cgi' && (($query['url_name'] ?? '') === 'man_ikvm_html5_bootstrap'));

            $body = preg_replace_callback(
                '#(href|src)\s*=\s*"(/[^"]+)"#i',
                function ($m) use ($base) {
                    return $m[1] . '="' . $base . ltrim($m[2], '/') . '"';
                },
                $body
            );
            $body = preg_replace_callback(
                "#(href|src)\s*=\s*'(/[^']+)'#i",
                function ($m) use ($base) {
                    return $m[1] . "='" . $base . ltrim($m[2], '/') . "'";
                },
                $body
            );
            $body = preg_replace_callback(
                '#(action)\s*=\s*"(/[^"]+)"#i',
                function ($m) use ($base) {
                    return $m[1] . '="' . $base . ltrim($m[2], '/') . '"';
                },
                $body
            );
            $body = preg_replace_callback(
                "#(action)\s*=\s*'(/[^']+)'#i",
                function ($m) use ($base) {
                    return $m[1] . "='" . $base . ltrim($m[2], '/') . "'";
                },
                $body
            );
            $body = str_replace('"/cgi/', '"' . $base . 'cgi/', $body);
            $body = str_replace("'/cgi/", "'" . $base . "cgi/", $body);
            $body = str_replace('"/js/', '"' . $base . 'js/', $body);
            $body = str_replace("'/js/", "'" . $base . "js/", $body);
            $body = str_replace('"/css/', '"' . $base . 'css/', $body);
            $body = str_replace("'/css/", "'" . $base . "css/", $body);
            $body = str_replace('"/images/', '"' . $base . 'images/', $body);
            $body = str_replace("'/images/", "'" . $base . "images/", $body);
            $body = str_replace('"/Java/', '"' . $base . 'Java/', $body);
            $body = str_replace("'/Java/", "'" . $base . "Java/", $body);
            $body = str_replace('"/html/', '"' . $base . 'html/', $body);
            $body = str_replace("'/html/", "'" . $base . "html/", $body);
            $body = str_replace('"/viewer.html', '"' . $base . 'viewer.html', $body);
            $body = str_replace("'/viewer.html", "'" . $base . "viewer.html", $body);
            $body = str_replace('"/launch.jnlp', '"' . $base . 'launch.jnlp', $body);
            $body = str_replace("'/launch.jnlp", "'" . $base . "launch.jnlp", $body);
            $body = str_replace('"/jviewer.jnlp', '"' . $base . 'jviewer.jnlp', $body);
            $body = str_replace("'/jviewer.jnlp", "'" . $base . "jviewer.jnlp", $body);

            if ($isHtml5BootstrapPage) {
                $body = str_replace('../css/', $base . 'css/', $body);
                $body = str_replace('../js/', $base . 'js/', $body);
                $body = str_replace('../novnc/', $base . 'novnc/', $body);
                $body = str_replace('../cgi/', $base . 'cgi/', $body);
                $body = str_replace('../../favicon.ico', '/favicon.ico', $body);
                $body = str_replace('\"jsunzip.js\", \"rfb.js\", \"keysym.js\",\"nav_ui.js\"', '\"jsunzip.js\", \"rfb.js\", \"keysym.js\"', $body);
                $body = str_replace(
                    "window.onscriptsload = function () {\n            }",
                    "window.onscriptsload = function () {\n                var host = window.location.hostname;\n                var port = window.location.port || (window.location.protocol === 'https:' ? 443 : 80);\n                var encrypt = window.location.protocol === 'https:';\n                var password = document.getElementById('entry_value').value;\n                var rfb = new RFB({target: document.getElementById('noVNC_canvas'), encrypt: encrypt, true_color: true, local_cursor: true, shared: true, view_only: false, onUpdateState: function(r, state, oldstate, msg){ if (window.console && msg) console.log(msg); }, onUpdateFps: function(){}, onFBUComplete: function(){}, onMessage: function(){}, onMouseMode: function(){}, onMouseMove: function(){}, onDesktopName: function(r, name){ document.title = name ? name + ' - noVNC' : document.title; }});\n                window.__ipmi_rfb = rfb;\n                rfb.connect(host, port, password, password, 'ipmi-ws/{$bmcIp}/kvm?SESSIONID={$bmc['session_id']}&COOKIE=QSESSIONID');\n            }",
                    $body
                );
            }

            $body = preg_replace('#([("=\'\s,:])/(cgi|js|css|images|Java|html)/#', '$1' . $base . '$2/', $body);
            $body = preg_replace('#([("=\'\s,:])/(viewer\.html|launch\.jnlp|jviewer\.jnlp)([\?"]|[\'\s,:)])#', '$1' . $base . '$2$3', $body);
            if ($cookieName === 'QSESSIONID' && stripos($body, '<base ') === false) {
                $body = preg_replace('#<head([^>]*)>#i', '<head$1><base href="' . $base . '">', $body, 1);
            }

            $body = str_replace('</head>', self::buildInjection($cookieName, $serverId, $bmcIp, $panelHost, $viewerStorage, $bmc, $path, $query) . "\n</head>", $body);
        }

        return [$contentType, $body];
    }

    private static function buildInjection(string $cookieName, int $serverId, string $bmcIp, string $panelHost, array $viewerStorage, array $bmc, string $path, array $query): string
    {
        $wsProxy = "wss://{$panelHost}/ipmi-ws/{$bmcIp}/kvm";
        if ($cookieName === 'QSESSIONID') {
            $viewerStorageData = $viewerStorage;
            if (!isset($viewerStorageData['username']) || $viewerStorageData['username'] === '') {
                $viewerStorageData['username'] = 'root';
            }
            if (!isset($viewerStorageData['privilege']) || $viewerStorageData['privilege'] === '') {
                $viewerStorageData['privilege'] = '4';
            }
            if (!isset($viewerStorageData['privilege_id']) || $viewerStorageData['privilege_id'] === '') {
                $viewerStorageData['privilege_id'] = (string) $viewerStorageData['privilege'];
            }
            if (!isset($viewerStorageData['kvm_access']) || $viewerStorageData['kvm_access'] === '') {
                $viewerStorageData['kvm_access'] = '1';
            }
            if (!isset($viewerStorageData['vmedia_access']) || $viewerStorageData['vmedia_access'] === '') {
                $viewerStorageData['vmedia_access'] = '1';
            }
            if (!isset($viewerStorageData['session_id']) || $viewerStorageData['session_id'] === '') {
                $viewerStorageData['session_id'] = (string) $bmc['session_id'];
            }
            if (!isset($viewerStorageData['garc']) || $viewerStorageData['garc'] === '') {
                $viewerStorageData['garc'] = (string) $bmc['csrf_token'];
            }
            if (!isset($viewerStorageData['CSRFToken']) || $viewerStorageData['CSRFToken'] === '') {
                $viewerStorageData['CSRFToken'] = (string) $bmc['csrf_token'];
            }
            $viewerTokenData = [
                'client_ip' => (string) ($viewerStorageData['client_ip'] ?? ''),
                'token' => (string) ($viewerStorageData['kvm_token'] ?? ''),
                'session' => (string) ($viewerStorageData['viewer_session'] ?? ''),
            ];
            $viewerStorageJson = json_encode($viewerStorageData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $viewerTokenJson = json_encode($viewerTokenData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $proxyQsessionId = json_encode((string) $bmc['session_id'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            return <<<SCRIPT
<script>
(function(){
    var proxiedViewer = '/ipmi-panel/bmc/{$serverId}/viewer.html';
    try {
        var origReplaceState = history.replaceState.bind(history);
        history.replaceState = function(state, title, url) {
            if (url === '/viewer.html') {
                url = proxiedViewer;
            }
            return origReplaceState(state, title, url);
        };
    } catch (e) {}
    var sd = {$viewerStorageJson} || {};
    for (var k in sd) {
        if (!Object.prototype.hasOwnProperty.call(sd, k) || sd[k] == null) continue;
        var value = String(sd[k]);
        sessionStorage.setItem(k, value);
        localStorage.setItem(k, value);
    }
    var tokenObj = {$viewerTokenJson} || {};
    sessionStorage.setItem('token', JSON.stringify(tokenObj));
    localStorage.setItem('token', JSON.stringify(tokenObj));
    sessionStorage.setItem('Viewerlanguage', 'root');
    localStorage.setItem('Viewerlanguage', 'root');
    sessionStorage.removeItem('status');
    sessionStorage.removeItem('stop_reason');
    sessionStorage.removeItem('invalid_sess_reason');
    localStorage.removeItem('isActiveKVM');
})();
(function(){
    var Orig=window.WebSocket,bmcIp="{$bmcIp}",proxy="{$wsProxy}";
    function stringifyWsUrl(u){
        if (typeof u === 'string') return u;
        try { return String(u); } catch (e) { return ''; }
    }
    function rewriteWs(u){
        var raw = stringifyWsUrl(u);
        if(raw === '') return u;
        if(raw.indexOf(proxy) === 0) return raw;
        var normalized = raw.toLowerCase();
        var shouldRewrite = (
            normalized === '/kvm' ||
            normalized === 'kvm' ||
            /(^|:\/\/|\/)(kvm)(\?|$)/.test(normalized) ||
            normalized.indexOf('/kvm?') !== -1 ||
            normalized.endsWith('/kvm') ||
            normalized.indexOf(bmcIp.toLowerCase()) !== -1 ||
            normalized.indexOf('localhost') !== -1
        );
        if(shouldRewrite){
            var sid={$proxyQsessionId};
            var wsUrl=proxy+'?QSESSIONID='+encodeURIComponent(sid);
            console.log('[KVM] WS redirect:',raw,'->',wsUrl);
            return wsUrl;
        }
        return raw;
    }
    window.WebSocket=function(u,p){ return new Orig(rewriteWs(u),p); };
    if (window.MozWebSocket) { window.MozWebSocket = window.WebSocket; }
    window.WebSocket.prototype=Orig.prototype;
    window.WebSocket.CONNECTING=Orig.CONNECTING;window.WebSocket.OPEN=Orig.OPEN;
    window.WebSocket.CLOSING=Orig.CLOSING;window.WebSocket.CLOSED=Orig.CLOSED;
})();
(function(){
    var origOpen=XMLHttpRequest.prototype.open;
    var base='/ipmi-panel/bmc/{$serverId}/';
    function rewrite(url){
        if(typeof url!=='string' || url==='') return url;
        if(url.indexOf(base)===0) return url;
        if(url.indexOf('/api/')===0 || url.indexOf('/app/')===0 || url.indexOf('/fonts/')===0 || url.indexOf('/images/')===0 || url.indexOf('/viewer')===0){
            return base + url.substring(1);
        }
        return url;
    }
    XMLHttpRequest.prototype.open=function(method,url,async,user,pass){ return origOpen.apply(this,[method,rewrite(url),async,user,pass]); };
    if(window.fetch){
        var origFetch=window.fetch;
        window.fetch=function(input, init){
            if(typeof input==='string'){ input = rewrite(input); }
            else if(input && typeof input.url==='string') { input = rewrite(input.url); }
            return origFetch.call(this,input,init);
        };
    }
})();
</script>
SCRIPT;
        }

        $isHtml5IkvmPage = ($path === '/cgi/url_redirect.cgi' && (($query['url_name'] ?? '') === 'man_ikvm_html5'));
        $isHtml5BootstrapPage = ($path === '/cgi/url_redirect.cgi' && (($query['url_name'] ?? '') === 'man_ikvm_html5_bootstrap'));
        $inject = <<<SCRIPT
<script>
(function(){
    try {
        if (!window.top.lang_setting) {
            window.top.lang_setting = 'English';
        }
    } catch(e) {}
})();
(function(){
    var proxyBase = '/ipmi-panel/bmc/{$serverId}/';
    function rewriteUrl(url) {
        if (typeof url !== 'string' || url === '') return url;
        if (url.indexOf(proxyBase) === 0) return url;
        if (/^https?:\/\//i.test(url) || /^wss?:\/\//i.test(url) || url.indexOf('javascript:') === 0 || url.indexOf('#') === 0) return url;
        if (url.charAt(0) === '/') return proxyBase + url.substring(1);
        if (url.indexOf('../') === 0) {
            while (url.indexOf('../') === 0) url = url.substring(3);
            return proxyBase + url;
        }
        if (/^(cgi|js|css|images|Java|html)\//i.test(url) || /^(viewer\.html|launch\.jnlp|jviewer\.jnlp)/i.test(url)) return proxyBase + url;
        return url;
    }
    var origOpen = XMLHttpRequest.prototype.open;
    XMLHttpRequest.prototype.open = function(method, url, async, user, pass){ return origOpen.call(this, method, rewriteUrl(url), async, user, pass); };
    var origWindowOpen = window.open;
    window.open = function(url, name, specs){ return origWindowOpen.call(window, rewriteUrl(url), name, specs); };
    function wrapClickHandler(node) {
        if (!node || typeof node.onclick !== 'function' || node.__ipmiWrapped) return;
        var origClick = node.onclick;
        node.onclick = function(evt) {
            var prevAssign = window.location.assign;
            var prevReplace = window.location.replace;
            window.location.assign = function(url) { return prevAssign.call(window.location, rewriteUrl(url)); };
            window.location.replace = function(url) { return prevReplace.call(window.location, rewriteUrl(url)); };
            try { return origClick.call(this, evt); }
            finally { window.location.assign = prevAssign; window.location.replace = prevReplace; }
        };
        node.__ipmiWrapped = true;
    }
    if (typeof window.GetJNLPRequest === 'function') {
        var origGetJNLPRequest = window.GetJNLPRequest;
        window.GetJNLPRequest = function(node, mode) {
            var result = origGetJNLPRequest.apply(this, arguments);
            try { wrapClickHandler(node); } catch (e) {}
            return result;
        };
    }
    document.addEventListener('DOMContentLoaded', function() { wrapClickHandler(document.getElementById('launchikvm_')); });
    var locAssign = window.location.assign.bind(window.location);
    window.location.assign = function(url){ return locAssign(rewriteUrl(url)); };
    var locReplace = window.location.replace.bind(window.location);
    window.location.replace = function(url){ return locReplace(rewriteUrl(url)); };
    document.addEventListener('click', function(evt){
        var node = evt.target;
        while (node && node.tagName !== 'A') node = node.parentElement;
        if (!node) return;
        var href = node.getAttribute('href');
        var rewritten = rewriteUrl(href);
        if (rewritten !== href) node.setAttribute('href', rewritten);
    }, true);
})();
</script>
SCRIPT;

        if ($isHtml5IkvmPage) {
            $inject .= <<<SCRIPT
<script>
(function() {
    function forceHtml5Launch() {
        var btn = document.getElementById('btnikvmhtml5_bootstrap');
        if (!btn) return;
        btn.disabled = false;
        btn.onclick = function() {
            window.open('/ipmi-panel/bmc/{$serverId}/cgi/url_redirect.cgi?url_name=man_ikvm_html5_bootstrap', '', 'menubar=yes,resizable=yes,scrollbars=yes,channelmode=yes');
        };
    }
    window.GetIKVMStatus = function(cb) {
        if (typeof cb === 'function') cb(true);
        window.setTimeout(forceHtml5Launch, 0);
        return true;
    };
    window.addEventListener('load', forceHtml5Launch);
})();
</script>
SCRIPT;
        }

        if ($isHtml5BootstrapPage) {
            $inject .= <<<SCRIPT
<script>
(function() {
    try {
        if (!window.opener) window.opener = window;
        if (!window.opener.top) window.opener.top = window.opener;
        if (!window.opener.top.topmenu) window.opener.top.topmenu = {};
        if (typeof window.opener.top.topmenu.update_csrf_token !== 'function') {
            window.opener.top.topmenu.update_csrf_token = function(){};
        }
    } catch (e) {}
})();
</script>
SCRIPT;
        }

        return $inject;
    }
}
