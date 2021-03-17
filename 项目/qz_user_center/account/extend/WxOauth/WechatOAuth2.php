<?php
namespace WxOauth;


class WechatOAuth2{

    protected $_curlHandle;
    public $appid;
    public $appkey;
    public $callback;
    public $debug = FALSE;
    public $http_header = array();
    /**
     *
     * @var array
     */
    protected $_curlOptions = array(
        CURLOPT_HTTP_VERSION	=> CURL_HTTP_VERSION_1_0,
        CURLOPT_USERAGENT		=> 'ZenAPI v0.3',
        CURLOPT_CONNECTTIMEOUT	=> 5,
        CURLOPT_TIMEOUT			=> 5,
        CURLOPT_SSL_VERIFYPEER	=> FALSE,
    );

    function __construct($appid,$appkey,$callback){
        $this->appid = $appid;
        $this->appkey = $appkey;
        $this->callback = $callback;
    }



    /**
     * 微信认证的code
     * @return [type] [description]
     */
    public function authorizeURL(){
        return 'https://open.weixin.qq.com/connect/qrconnect';
    }

    /**
     * 获取token的url
     * @return [type] [description]
     */
    public function accessTokenURL(){
        return 'https://api.weibo.com/oauth2/access_token';
    }

    /**
     * authorize接口
     * 对应API： https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1419316505&token=&lang=zh_CN
     * @param  array  $params [description]
     * @return [type]         [description]
     */
    public function getAuthorizeURL(array $params) {
        $defaults = array(
            'appid' => $this->appid,
            'response_type'=> 'code',
            'scope'=>'snsapi_login'
        );
        return $this->authorizeURL() . "?" . http_build_query($params + $defaults);
    }

    //获取授权地址
    public function getloginQr(){
        //生成随机验证码
        $code = md5(uniqid(rand(), TRUE));
        session("oauth_wx_safecode",urlencode($code));
        //授权参数
        $options = array(
            "redirect_uri"=>trim( $this->callback),
            "state"=> urlencode($code)
        );
        $url = $this->getAuthorizeURL( $options);
        return $url;
    }

    /**
     * 获取开放平台授权的token
     * code 授权的code
     * @return [type] [description]
     */
    public function getOpenToken($code){
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$this->appid&secret=$this->appkey&code=$code&grant_type=authorization_code";
        $result = $this->http($url,"GET");
        $json = json_decode($result,true);
        if(!empty($json)){
            if(!isset($json["errcode"])){
                return $json;
            }
        }
        return null;
    }

    public function getUserInfoByToken($token,$unionid){
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=$token&openid=$unionid";
        $result = $this->http($url,"GET");
        $json = json_decode($result,true);
        if(!empty($json)){
            if(!isset($json["errcode"])){
                return $json;
            }
        }
        return null;
    }


    public function http($url, $method, $postfields = NULL, $headers = array()) {
        if ($this->_curlHandle !== null){
            curl_close($this->_curlHandle);
            $this->http_header = array();
        }

        $this->_curlHandle = $ci = curl_init();

        /* Curl settings */
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_ENCODING, "");
        curl_setopt($ci, CURLOPT_HEADERFUNCTION, function($ch, $header){
            $this->http_header[] = $header;
            return strlen($header);
        });
        curl_setopt($ci, CURLOPT_HEADER, FALSE);
        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt_array($ci, $this->_curlOptions);

        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                break;
            case 'GET':
                curl_setopt($ci, CURLOPT_POST, FALSE);
                break;
            default:
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method);
        }

        if (!empty($postfields)){
            curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
        }

        $response = curl_exec($ci);
        if ($response === false){
            //throw new \Think\Exception("curl error");
        }

        if ($this->debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);

            echo '=====info====='."\r\n";
            print_r( curl_getinfo($this->_curlHandle) );

            echo '=====$response====='."\r\n";
            print_r( $response );
        }
        return $response;
    }


}