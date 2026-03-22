<?php

namespace App\Services;

use App\Http\Response;

final class BmcProxyAssetService
{
    public static function tryServeSupermicroLocalAsset(string $cookieName, string $path): ?Response
    {
        if ($cookieName !== 'SID' || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            return null;
        }

        $assetName = basename($path);
        $localNoVncAssets = BmcProxySupport::localNoVncAssets();
        if (!isset($localNoVncAssets[$assetName]) || !is_readable($localNoVncAssets[$assetName])) {
            return null;
        }

        return BmcProxyOutput::sendFile($localNoVncAssets[$assetName], 'application/javascript');
    }

    public static function tryServeSupermicroNavUi(string $cookieName, string $path, string $sessionId, string $bmcIp): ?Response
    {
        if ($cookieName !== 'SID' || !preg_match('#/nav_ui\.js$#', $path)) {
            return null;
        }

        $sessionIdJson = json_encode($sessionId, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $wsPath = json_encode(
            'ipmi-ws/' . $bmcIp . '/kvm?SESSIONID=' . rawurlencode($sessionId) . '&COOKIE=' . rawurlencode($cookieName),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        $body = "var UI;var \$jq=function(){return{dialog:function(){return this;},html:function(){return this;}};};(function(){UI={rfb:null,load:function(callback){UI.start(callback);},start:function(callback){UI.connect();if(typeof callback===\"function\")callback();},updateState:function(rfb,state,oldstate,msg){if(window.console&&msg){console.log('Msg: '+msg);}},updateFps:function(){},FBUComplete:function(){},MessageHandler:function(){},MouseModeHandler:function(){},MouseMoveHandler:function(x,y){return{x:x,y:y};},updateDocumentTitle:function(rfb,name){if(name){document.title=name+' - noVNC';}},initRFB:function(encrypt){try{UI.rfb=new RFB({target:\$D('noVNC_canvas'),encrypt:encrypt,true_color:true,local_cursor:true,shared:true,view_only:false,onUpdateState:UI.updateState,onUpdateFps:UI.updateFps,onFBUComplete:UI.FBUComplete,onMessage:UI.MessageHandler,onMouseMode:UI.MouseModeHandler,onMouseMove:UI.MouseMoveHandler,onDesktopName:UI.updateDocumentTitle});return true;}catch(exc){if(window.console){console.error(exc);}return false;}},connect:function(){var host=window.location.hostname;var port=window.location.port||((window.location.protocol===\"https:\")?443:80);var encrypt=(window.location.protocol===\"https:\");var password=\$D('entry_value').value||" . $sessionIdJson . ";var username=password;var path=" . $wsPath . ";if(!host||!port){throw new Error('Must set host and port');}if(!UI.initRFB(encrypt)){return;}UI.rfb.connect(host,port,username,password,path);},disconnect:function(){if(UI.rfb){UI.rfb.disconnect();}},show_message_window:function(msg){if(window.console&&msg){console.warn(msg);}}};window.onscriptsload=function(){UI.load();};})();";
        return BmcProxyOutput::sendText($body, 'application/javascript');
    }
}
