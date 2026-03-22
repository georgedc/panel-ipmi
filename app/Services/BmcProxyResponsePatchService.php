<?php

namespace App\Services;

class BmcProxyResponsePatchService
{
    public function apply(
        string $cookieName,
        string $path,
        int $serverId,
        string $sessionId,
        string $bmcIp,
        string $body,
        string $contentType
    ): array {
        $ext = pathinfo((string) parse_url($path, PHP_URL_PATH), PATHINFO_EXTENSION);

        if ($ext === 'css' && $cookieName === 'QSESSIONID') {
            $base = '/ipmi-panel/bmc/' . $serverId . '/';
            $body = str_replace('../fonts/', $base . 'fonts/', $body);
            $body = str_replace("../fonts/", $base . 'fonts/', $body);
            $body = str_replace('../images/', $base . 'images/', $body);
            $body = preg_replace('#url\((["\']?)/(fonts|images|css|app)/#', 'url($1' . $base . '$2/', $body);
        }

        if ($ext === 'js' && $path === '/novnc/include/util.js') {
            $body = str_replace(
                "function SmcCsrfInsert (name, token)\n{\n    _doCsrfInsert (name, token);\n    if (window.opener.top.topmenu.update_csrf_token) {\n        window.opener.top.topmenu.update_csrf_token (name, token);\n    }\n}",
                "function SmcCsrfInsert (name, token)\n{\n    _doCsrfInsert (name, token);\n    try {\n        if (window.opener && window.opener.top && window.opener.top.topmenu && typeof window.opener.top.topmenu.update_csrf_token === 'function') {\n            window.opener.top.topmenu.update_csrf_token (name, token);\n        }\n    } catch (e) {}\n}",
                $body
            );
        }

        if ($ext === 'js' && $cookieName === 'SID' && preg_match('#/nav_ui\.js$#', $path)) {
            $sessionIdJson = json_encode($sessionId, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $wsPath = json_encode(
                'ipmi-ws/' . $bmcIp . '/kvm?SESSIONID=' . rawurlencode($sessionId) . '&COOKIE=' . rawurlencode($cookieName),
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            );
            $body = "var UI;var \$jq=function(){return{dialog:function(){return this;},html:function(){return this;}};};(function(){UI={rfb:null,load:function(callback){UI.start(callback);},start:function(callback){UI.connect();if(typeof callback===\"function\")callback();},updateState:function(rfb,state,oldstate,msg){if(window.console&&msg){console.log('Msg: '+msg);}},updateFps:function(){},FBUComplete:function(){},MessageHandler:function(){},MouseModeHandler:function(){},MouseMoveHandler:function(x,y){return{x:x,y:y};},updateDocumentTitle:function(rfb,name){if(name){document.title=name+' - noVNC';}},initRFB:function(encrypt){try{UI.rfb=new RFB({target:\$D('noVNC_canvas'),encrypt:encrypt,true_color:true,local_cursor:true,shared:true,view_only:false,onUpdateState:UI.updateState,onUpdateFps:UI.updateFps,onFBUComplete:UI.FBUComplete,onMessage:UI.MessageHandler,onMouseMode:UI.MouseModeHandler,onMouseMove:UI.MouseMoveHandler,onDesktopName:UI.updateDocumentTitle});return true;}catch(exc){if(window.console){console.error(exc);}return false;}},connect:function(){var host=window.location.hostname;var port=window.location.port||((window.location.protocol===\"https:\")?443:80);var encrypt=(window.location.protocol===\"https:\");var password=\$D('entry_value').value||" . $sessionIdJson . ";var username=password;var path=" . $wsPath . ";if(!host||!port){throw new Error('Must set host and port');}if(!UI.initRFB(encrypt)){return;}UI.rfb.connect(host,port,username,password,path);},disconnect:function(){if(UI.rfb){UI.rfb.disconnect();}},show_message_window:function(msg){if(window.console&&msg){console.warn(msg);}}};window.onscriptsload=function(){UI.load();};})();";
            $contentType = 'application/javascript';
        }

        return [$contentType, $body];
    }
}
