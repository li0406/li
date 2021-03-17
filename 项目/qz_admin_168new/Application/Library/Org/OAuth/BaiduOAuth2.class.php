<?php

namespace Library\Org\OAuth;
use Library\Org\OAuth\BaseClient;


class BaiduOAuth2 extends BaseClient
{
    use OAuth2Trait;

    public $client_id;

    public $client_secret;

    public function accessTokenURL(){
        return 'https://openapi.baidu.com/oauth/2.0/token?';
    }

    public function authorizeURL(){
        return 'https://openapi.baidu.com/oauth/2.0/authorize';
    }

    public function getAuthorizeURL(array $params)
    {
        $defaults = array(
            'client_id' => $this->client_id,
            'response_type'=> 'code',
            "scope" => "snsapi_userinfo"
        );

        return $this->authorizeURL() . "?" . http_build_query($params + $defaults);
    }



    /**
     * 获取百度用户的信息
     * @return [type] [description]
     */
    public function getHttp($url = "", $method = "",$postfields = null, $headers = array()){
        $response = $this->http($url,$method,$postfields,$headers);
        return $this->_tokenFilter($response);
    }
}
