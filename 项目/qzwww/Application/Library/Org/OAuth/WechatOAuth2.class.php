<?php
namespace Library\Org\OAuth;
use Library\Org\OAuth\BaseClient;
class WechatOAuth2 extends BaseClient{
    use OAuth2Trait;
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
            'appid' => $this->client_id,
            'response_type'=> 'code',
            'scope'=>'snsapi_login'
        );
        return $this->authorizeURL() . "?" . http_build_query($params + $defaults);
    }
}