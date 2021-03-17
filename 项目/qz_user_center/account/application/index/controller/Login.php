<?php

namespace app\index\controller;

use QqOauth\Oauth;
use QqOauth\QC;
use QqOauth\URL;
use SinaOauth\SaeTClientV2;
use SinaOauth\SaeTOAuthV2;
use think\Controller;
use think\Request;
use WxOauth\WechatOAuth2;


class Login extends Base
{
    //登陆页面
    public function index(Request $request)
    {
        return $this->fetch();
    }

    //注册页面
    public function register()
    {
        return $this->fetch();
    }

    public function sinalogin()
    {
        $o = new SaeTOAuthV2(OP("sina_appid"), OP("sina_secret"));
        $code =input('get.code');
        if (!isset($code)) {
            $authorizeURL = $o->getAuthorizeURL(OP("sina_redirect_url"));
            $this->redirect($authorizeURL);
        } else {
            //授权参数
            $options = array(
                "redirect_uri" => OP("sina_redirect_url"),
                "code" => $code
            );

            $token = $o->getAccessToken('code', $options);
            if ($token) {
                //获取用户基本信息
                $c = new saetclientv2(OP("sina_appid"), OP("sina_secret"), $token['access_token']);
                $usr_info = $c->show_user_by_id($token['uid']); //微博sdk方法获取用户的信息

                $user['logo'] = $usr_info['profile_image_url'];
                $user['nickname'] = $usr_info['screen_name'];
                $user['unionid'] = $token['uid'];
                $user['type'] = 2;
                $user['third_name'] = '微博';
                $this->assign('user', $user); //三方登陆类型,1:微信;2:微博;3:qq
                return $this->fetch('three');
            } else {
                echo "Account verification failed!";
                die();
            }
        }

    }

    public function qqlogin()
    {
        $o = new Oauth(OP("qq_appid"), OP("qq_secret"), OP("qq_redirect_url"));
        $code = input('get.code');
        if (!isset($code)) {
            $this->redirect($o->qq_login());
        } else {
            $access_token = $o->qq_callback();
            $openId = $o->get_openid();
            if (!empty($openId)) {
                $URL = new URL();
                $keysArr = [
                    'access_token' => $access_token,
                    'oauth_consumer_key' => OP("qq_appid"),
                    'openid' => $openId
                ];

                $baseURL = 'https://graph.qq.com/user/get_user_info';
                $res = $URL->get($baseURL, $keysArr);
                $user_info = json_decode($res, true);

                $user['logo'] = $user_info['figureurl_qq_2'];
                $user['nickname'] = strip_tags($user_info['nickname']);
                $user['unionid'] = $openId;
                $user['type'] = 3;//三方登陆类型,1:微信;2:微博;3:qq
                $user['third_name'] = 'QQ';
                if($this->hasBind($user['unionid'],$user['type'])){
                    $this->redirect(config('app.SPACE_HOST'));
                }else{
                    $this->assign('user', $user);
                    return $this->fetch('three');
                }
            } else {
                echo "Account verification failed!";
                die();
            }

        }
    }

    public function wxlogin()
    {
        $o = new WechatOAuth2(OP("open_wechat_appid"), OP("open_wechat_secret"), OP("open_wechat_redirect_url"));
        $code = input('get.code');
        if (!isset($code)) {
            $url = $o->getloginQr();
            $this->redirect($url);
        } else {
            if (input('get.state') == urldecode(session("oauth_wx_safecode"))) {
                $result = $o->getOpenToken($code);
                $user_info = $o->getUserInfoByToken($result["access_token"], $result["unionid"]);

                $user['logo'] = $user_info['headimgurl'];
                $user['nickname'] = strip_tags($user_info['nickname']);
                $user['unionid'] = $result["unionid"];
                $user['type'] = 1;//三方登陆类型,1:微信;2:微博;3:qq
                $user['third_name'] = '微信';
                if($this->hasBind( $user['unionid'],$user['type'])){
                    $this->redirect(config('app.SPACE_HOST'));
                }else{
                    $this->assign('user', $user);
                    return $this->fetch('three');
                }

            } else {
                echo "Account authorization failed!";
            }
        }
    }

    // 忘记密码
    public function resetpwd()
    {
        return $this->fetch();
    }

 

    //该账号是否绑定过
    private function hasBind($id,$type){
        $url = config('app.API_HOST')."/uc/v1/third/party/login";
        $data = [
            "uid" => $id,
            "third_type" => $type
        ];
        $headers = [
            'Content-Type: application/json;charset=utf-8;',
            'App-From:9f6910a77bc9c8d4cf9db31ed41af490',
            'Addr: PC_QZ',
            'App-env:platform=WEB'
        ];
        $data = json_encode($data,256);
        $result = curl($url,$data,$headers);
        if($result['error_code'] == 0){
            if($result['data']['success_code'] == 1){
                setcookie("center_password_token",$result['data']['jwt_token'],time()+3600*24*30,'/', '.qizuang.com');
                return true;
            }
        }
        return false;
    }
}