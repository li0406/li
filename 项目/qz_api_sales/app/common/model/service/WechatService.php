<?php

namespace app\common\model\service;

use Wechat\WxAppletHelper;
use app\common\model\db\WechatToken;

class WechatService {

    private $wechatHelper;

    public function __construct(){
        if (config("APP_ENV") == "dev") {
            $appid = config("WORKSITE_APPLET_APPID_TEST");
        } else {
            $appid = config("WORKSITE_APPLET_APPID");
        }
        $secret = OP("wxappid_" . $appid);

        $getTokenHandler = [WechatToken::class, "getToken"];
        $setTokenHandler = [WechatToken::class, "setToken"];
        $this->wechatHelper = new WxAppletHelper($appid, $secret, $getTokenHandler, $setTokenHandler);
    }

    public function excute(){
        return $this->wechatHelper;
    }

}