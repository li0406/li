<?php

namespace WxApp\Controller;
use WxApp\Common\Controller\WxAppBaseController;

class UserController extends WxAppBaseController{

    /**
     * 移动端微信小程序 -- 登陆功能
     * @param  [string]         $code        微信返回的code
     * @return [array]          $info        [用户信息，主要为用户ID]
     */
    public function login()
    {
        $code           = I("post.code");
        //$wx_unionid     = I("post.unionid");
        $name           = I("post.name");
        $logo           = I("post.logo");
        $appid          = I("post.appid");
        $secret         = OP("wxappid_".$appid);
       
        //$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$code."&grant_type=authorization_code";
        
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".$appid."&secret=".$secret."&js_code=".$code."&grant_type=authorization_code";
        //文档 https://mp.weixin.qq.com/debug/wxadoc/dev/api/api-login.html#wxloginobject

    
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_HEADER, 0);
        curl_setopt($ch,CURLOPT_TIMEOUT, 10);//设置超时
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $hander = curl_exec($ch);
        curl_close($ch);

        $wx_arr = json_decode($hander,true);

        $wx_unionid = $wx_arr['unionid'];
        $wx_openid  = $wx_arr['openid'];

        if( !empty($wx_unionid) || !empty($wx_openid) ){
            //根据微信接口返回的 "unionid 用户在开放平台的唯一标识符" 查询用户账户信息
            $user = D('Userhistory')->getUserInfoByUnionid($wx_unionid);
            //如果通过 unionid没查询到则通过 openid查询
            if (empty($user)) {
                $user = D('Userhistory')->getUserInfoByOpenid($wx_openid);
            }
            if(!empty($user)){
                //存在  返回用户ID
                $this->ajaxReturn(array('data' => $user['id'],'status' => 1));
            }else{
                //不存在  新建一个用户  返回用户ID
                import('Library.Org.Util.App');
                $app = new \App();
                $num = $app->getSafeCode(8,"NUMBER");

                //生成user
                $user_fix = 'u_';
                if (!empty($wx_unionid)) {
                    $user_fix .= $wx_unionid;
                } else {
                    $user_fix .= $wx_openid;
                }
                $user_fix = mbstr($user_fix, 0, 20, "utf-8", false);

                $data = array(
                        "classid"           =>1,
                        "user"              =>$user_fix,
                        "pass"              =>md5($num),
                        "name"              =>remove_xss($name),
                        "wx_unionid"        =>$wx_unionid,
                        "wx_openid"         =>$wx_openid,
                        "logo"              =>empty($logo)?"https://".C("QINIU_DOMAIN")."/".OP("DEFAULT_LOGO"):$logo,
                        "register_time"     =>time()
                );
                $model = D("Common/User");
                if($model->create($data,9)){
                    //验证通过,注册帐号
                    $i = $model->addUser($data);
                    //echo json_encode(array("userid"=>$i));
                    $this->ajaxReturn(array('data' => $i,'status' => 1));
                }else{
                    $this->ajaxReturn(array('data' => '','status' => 0));
                }
            }
        }else{
            $this->ajaxReturn(array('data' => '','status' => 0));
        }
    }

}