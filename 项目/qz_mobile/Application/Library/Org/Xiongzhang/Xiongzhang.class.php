<?php

namespace Xiongzhang;

class Xiongzhang
{
    const APPID = 'i0rmlcMsuL4X5Lmf6gG2VAwbrqjKaZwv';
    const SECRET = 'GSHWnKAQlvPnonV4CUa8mnCGo07FietL';
    const XIAOGNZHANGID = "1575859058891466";

    public function getCode()
    {
        $redirect_uri =  urlencode('http://kk1314.free.ngrok.cc/baidu');
        $url = 'https://openapi.baidu.com/oauth/2.0/authorize?response_type=code&client_id='.self::APPID.'&redirect_uri='. $redirect_uri.'&scope=snsapi_userinfo&state=qizuang';



    }

    /**
     * 获取token
     * @return [type] [description]
     */
    public function getToken()
    {
        $token = S("Cache:XZH:TOKEN");
        if (!$token) {
            $url = "https://openapi.baidu.com/oauth/2.0/token?grant_type=client_credentials&client_id=".self::APPID."&client_secret=".self::SECRET;
            $response = $this->http($url);
            $response = json_decode($response,true);
            if (!empty($response["access_token"])) {
                $token = $response["access_token"];
                S("Cache:XZH:TOKEN",$token,60*90);
            }
        }
        return $token;
    }

    /**
     * 获取ticket
     * @param  [type] $token [description]
     * @return [type]        [description]
     */
    public function getTicket($token)
    {
        $ticket = S("Cache:XZH:TICKET");
        if (!$ticket) {
            $url = "https://openapi.baidu.com/rest/2.0/cambrian/jssdk/getticket?access_token=".$token;
            $response = $this->http($url);
            $response = json_decode($response,true);
            if (!empty($response["ticket"])) {
                $ticket = $response["ticket"];
                S("Cache:XZH:TICKET",$ticket,60*90);
            }
        }
        return $ticket;
    }

   /**
    * 获取签名
    * @param  [type] $nonce_str [随机字符串]
    * @param  [type] $ticket    [门票]
    * @param  [type] $url       [当前的URL]
    * @return [type]            [description]
    */
    public function getSignature($nonce_str,$ticket,$url, $timestamp)
    {
        $str = "jsapi_ticket=".$ticket."&nonce_str=".$nonce_str."&timestamp=".$timestamp."&url=".$url;
        return  sha1($str);
    }

    /**
     * 获取请求脚本
     * @param  [type] $timestamp [description]
     * @param  [type] $nonce_str [description]
     * @return [type]            [description]
     */
    public function getSrcipt($timestamp,$nonce_str,$signature,$url)
    {
        $script = "<script src='https://xiongzhang.baidu.com/sdk/c.js?appid=".self::XIAOGNZHANGID."&timestamp=$timestamp&nonce_str=$nonce_str&signature=$signature&url=$url'></script>";
        return $script;
    }


    public function getBaiDuCode()
    {
        $url = 'https://openapi.baidu.com/oauth/2.0/authorize?response_type=code&client_id='.self::APPID.'&redirect_uri=REDIRECT_URI&scope=snsapi_userinfo';


    }

    private function http($url)
    {
        $ch = curl_init();
        //参数设置
        curl_setopt ($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt ($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}