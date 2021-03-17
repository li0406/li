<?php
namespace QqOauth;
/* PHP SDK
 * @version 2.0.0
 * @author connect@qq.com
 * @copyright © 2013, Tencent Corporation. All rights reserved.
 */

class Oauth{

    const VERSION = "2.0";
    const GET_AUTH_CODE_URL = "https://graph.qq.com/oauth2.0/authorize";
    const GET_ACCESS_TOKEN_URL = "https://graph.qq.com/oauth2.0/token";
    const GET_OPENID_URL = "https://graph.qq.com/oauth2.0/me";


    public $urlUtils;
    protected $error;
    public $appid;
    public $appkey;
    public $callback;
    public $scope = "get_user_info";



    function __construct($appid,$appkey,$callback){
        $this->urlUtils = new URL();
        $this->error = new ErrorCase();
        $this->appid = $appid;
        $this->appkey = $appkey;
        $this->callback = $callback;
    }

    public function qq_login(){
        $appid = $this->appid;
        $callback = $this->callback;
        $scope = $this->scope;

        //-------生成唯一随机串防CSRF攻击
        $state = md5(uniqid(rand(), TRUE));
        session('oauth_sina_state',$state);
        //-------构造请求参数列表
        $keysArr = array(
            "response_type" => "code",
            "client_id" => $appid,
            "redirect_uri" => $callback,
            "state" => $state,
            "scope" => $scope
        );

        $login_url =  $this->urlUtils->combineURL(self::GET_AUTH_CODE_URL, $keysArr);
        return $login_url;
    }

    public function qq_callback(){
        $state = session('oauth_sina_state');

        //--------验证state防止CSRF攻击
        if(!$state || $_GET['state'] != $state){
            $this->error->showError("30001");
        }

        //-------请求参数列表
        $keysArr = array(
            "grant_type" => "authorization_code",
            "client_id" => $this->appid,
            "redirect_uri" => urlencode($this->callback),
            "client_secret" => $this->appkey,
            "code" => $_GET['code']
        );

        //------构造请求access_token的url
        $token_url = $this->urlUtils->combineURL(self::GET_ACCESS_TOKEN_URL, $keysArr);
        $response = $this->urlUtils->get_contents($token_url);

        if(strpos($response, "callback") !== false){

            $lpos = strpos($response, "(");
            $rpos = strrpos($response, ")");
            $response  = substr($response, $lpos + 1, $rpos - $lpos -1);
            $msg = json_decode($response);

            if(isset($msg->error)){
                $this->error->showError($msg->error, $msg->error_description);
            }
        }

        $params = array();
        parse_str($response, $params);
        session("access_token",$params["access_token"]);
        return $params["access_token"];

    }

    public function get_openid(){

        //-------请求参数列表
        $keysArr = array(
            "access_token" => session("access_token")
        );

        $graph_url = $this->urlUtils->combineURL(self::GET_OPENID_URL, $keysArr);
        $response = $this->urlUtils->get_contents($graph_url);

        //--------检测错误是否发生
        if(strpos($response, "callback") !== false){

            $lpos = strpos($response, "(");
            $rpos = strrpos($response, ")");
            $response = substr($response, $lpos + 1, $rpos - $lpos -1);
        }

        $user = json_decode($response);
        if(isset($user->error)){
            $this->error->showError($user->error, $user->error_description);
        }

        //------记录openid
        session("openid", $user->openid);
        return $user->openid;

    }
}
