<?php

namespace Wechat;

use Exception;

class WxAppletHelper {

    private $appid;
    private $secret;
    private $getTokenHandler;
    private $setTokenHandler;

    public function __construct($appid, $secret, $getTokenHandler = null, $setTokenHandler = null) {
        $this->appid = $appid;
        $this->secret = $secret;
        $this->getTokenHandler = $getTokenHandler;
        $this->setTokenHandler = $setTokenHandler;
    }

    public function getAccessToken(){
        try {
            $access_token = call_user_func($this->getTokenHandler, $this->appid);
            if (empty($access_token)) {
                $params = array(
                    "grant_type" => "client_credential",
                    "appid" => $this->appid,
                    "secret" => $this->secret
                );

                $url = "https://api.weixin.qq.com/cgi-bin/token?" . http_build_query($params);
                $response = curl($url);
                if (!empty($response["access_token"])) {
                    $access_token = $response["access_token"];
                    call_user_func($this->setTokenHandler, $this->appid, $access_token, $response["expires_in"]);
                }
            }
        } catch (Exception $e) {
            $access_token = "";
        }

        return $access_token;
    }

    public function getUnlimited($param = []){
        $access_token = $this->getAccessToken();
        if (!empty($access_token)) {
            try {
                $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $access_token;
                $param = json_encode($param,320);
                $response = curl($url, $param);
                return $response;
            } catch (Exception $e) {
                return ["errcode" => 9999, "errmsg" => "处理失败"];
            }
        }

        return ["errcode" => 9999, "errmsg" => "access_token获取失败"];
    }

    public function getUserInfo($openid){
        $access_token = $this->getAccessToken();
        if (!empty($access_token)) {
            try {
                $param = [
                    "access_token" => $access_token,
                    "openid" => $openid,
                    "lang" => "zh_CN"
                ];

                $url = "https://api.weixin.qq.com/cgi-bin/user/info?" . http_build_query($param);
                $response = curl($url);
                return $response;
            } catch (Exception $e) {
                return ["errcode" => 9999, "errmsg" => "处理失败"];
            }
        }

        return ["errcode" => 9999, "errmsg" => "access_token获取失败"];
    }

    public function getOpenid($code){
        $param = array(
            "appid" => $this->appid,
            "secret" => $this->secret,
            "js_code" => $code,
            "grant_type" => 'authorization_code',
        );

        $url = 'https://api.weixin.qq.com/sns/jscode2session?' . http_build_query($param);
        $info = curl($url);
        if (!empty($info)) {
            return $info;
        }

        return false;
    }

}