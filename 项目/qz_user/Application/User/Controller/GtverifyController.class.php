<?php
namespace User\Controller;
use Think\Controller;
class GtverifyController extends Controller{
    /**
     * 调用极验证，初始化验证参数
     * @return [type] [description]
     */
    public function getstartverify()
    {
        return D('Common/verifyserver')->startcaptchaservlet();
    }
    /**
     * 极验证二次验证
     * @return [type] [description]
     */
    public function verifylogin()
    {
        $result = D('Common/verifyserver')->verifyloginservlet();
        if ($result) {
            session("geetest_verify",true);
            $this->ajaxReturn(array('data' => '','info' => '验证成功','status'=>1 ));
        }else{
            $this->ajaxReturn(array('data' => '','info' => '验证错误，请刷新页面在尝试！','status'=>0 ));
        }
    }

    /**
     * 新的 极验证二次验证
     * @return [type] [description]
     */
    public function verifyGeetest()
    {
        if (session("gtserver") == 1) {
            import('Library.gt3.lib.GeetestLib', "", ".php");
            import('Library.gt3.config.config', "", ".php");
            import('Library.Org.Util.App');
            $app = new \App();
            $data = array(
                "client_type" => "web", #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
                "ip_address" => $app->get_client_ip() # 请在此处传输用户请求验证时所携带的IP
            );
            $geetest_challenge = I("post.geetest_challenge");
            $geetest_validate = I("post.geetest_validate");
            $geetest_seccode = I("post.geetest_seccode");

            //请求场景 1.登录/找回密码/注册 2.获取验证码
            $scence = I("post.scence");

            switch ($scence){
                case 1:
                    $captcha_id = GEETEST_CAPTCHA_ID;
                    $private_key = GEETEST_PRIVATE_KEY;
                    break;
                case 2:
                    $captcha_id = GEETEST_SMS_CAPTCHA_ID;
                    $private_key = GEETEST_SMS_PRIVATE_KEY;
                    break;
                default:
                    $this->ajaxReturn(array("status" => 0,"info" => "无效的请求"));
                    break;
            }

            //验证通过
            $GtSdk = new \GeetestLib($captcha_id, $private_key);
            $result = $GtSdk->success_validate($geetest_challenge, $geetest_validate, $geetest_seccode, $data);
            if ($result) {
                //验证临时标识
                S("Cache:Geetest:".session_id(),1,606);
                $this->ajaxReturn(array("status" => 1));
            }
        }
        $this->ajaxReturn(array("status" => 0));
    }

    public function initGeet()
    {
        if (IS_AJAX && IS_POST) {
            import('Library.gt3.lib.GeetestLib',"",".php");
            import('Library.gt3.config.config',"",".php");
            import('Library.Org.Util.App');

            //请求场景 1.登录/找回密码/注册 2.获取验证码
            $scence = I("post.scence");

            switch ($scence){
                case 1:
                    $captcha_id = GEETEST_CAPTCHA_ID;
                    $private_key = GEETEST_PRIVATE_KEY;
                    break;
                case 2:
                    $captcha_id = GEETEST_SMS_CAPTCHA_ID;
                    $private_key = GEETEST_SMS_PRIVATE_KEY;
                    break;
                default:
                    $this->ajaxReturn(array("status" => 0,"info" => "无效的请求"));
                    break;
            }

            $GtSdk = new \GeetestLib($captcha_id, $private_key);
            import('Library.Org.Util.App');
            $app = new \App();
            $data = array(
                "client_type" => "web", #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
                "ip_address" => $app->get_client_ip() # 请在此处传输用户请求验证时所携带的IP
            );
            $status = $GtSdk->pre_process($data, 1);
            session("gtserver",$status);
            session("ip_address",$data["ip_address"]);
            $this->ajaxReturn(array("status" => 1,"data" => json_decode($GtSdk->get_response_str(),true)));
        }
    }
}

