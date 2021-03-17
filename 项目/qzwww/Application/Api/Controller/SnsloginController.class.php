<?php
namespace Api\Controller;
use Api\Common\Controller\ApiBaseController;
class SnsloginController extends ApiBaseController{
    public function _initialize(){
    }


    /**
     * 做一个代理 让微信登录回调支持多个回调域名
     */
    public function wxproxy()
    {

        if (isset($_GET['device'])) {
            $device = $_GET['device'];
        }

        $authUrl = '';
        if ($device == 'pc') {
            $authUrl = 'https://open.weixin.qq.com/connect/qrconnect';
        } else {
            $authUrl = 'https://open.weixin.qq.com/connect/oauth2/authorize';
        }

        if (isset($_GET['code'])) {
            header('Location: ' . $_COOKIE['return_uri'] . '?code=' . $_GET['code'] . '&state=' . $_GET['state']);
        } else {
            $protocol = is_ssl() ? 'https://' : 'http://';
            $params   = array(
                'appid'         => $_GET['appid'],
                'redirect_uri'  => $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                'response_type' => $_GET['response_type'],
                'scope'         => $_GET['scope'],
                'state'         => $_GET['state'],
            );
            setcookie('return_uri', urldecode($_GET['return_uri']), $_SERVER['REQUEST_TIME'] + 60, '/');
            header('Location: ' . $authUrl . '?' . http_build_query($params) . '#wechat_redirect');
        }
    }

}