<?php
namespace Muser\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        if(session("?login_verify")){
            $tmp = $this->fetch("login_verify");
            $this->assign("verify",$tmp);
        }
        $this->display();
    }

    /**
     * 验证码
     */
    public function Verify(){
        getVerify("",4,120,30);
    }

    /**
     * 登陆
     * @return [type] [description]
     */
    public function dologin(){
        if($_POST){
            //判断是否做限制(如果记录时间距离当前时间在10分钟以内 , 就做限制)
            if (session('blocking_time') && (time() - session('blocking_time')) < 600) {
                $this->ajaxReturn(array("data" => '', "info" => '密码错误5次 , 您可以前往PC端找回密码 , 或10分钟后再试', "status" => 0, 'type' => 1));
            }

            $model = D("User");
            $data = array(
                'user' => I("post.name"),
                'pwd'  => I("post.pwd")
                    );
            $errMsg = "";
            $status = 0;
             // session("login_verify",null);
            if(session("?login_verify")){
                $code = I("post.verify");
                if(!check_verify($code)){
                   $this->ajaxReturn(array("data"=>"","info"=>"验证码错误！","status"=>0));
                }
            }
        
            if($model->create($data,99)){
                //记录登陆的用户名
                setcookie("u_cookie_login", $data["user"], time()+315360000, '/', '.'.APP_HTTP_DOMAIN);
                $user = $model->loginMobile($data["user"]);

                if ($user["pass"] == md5("123456")) {
                    $this->ajaxReturn(array("info"=>"为了您的订单安全，请先登录PC端进行密码修改后再登录","status"=>0));
                }

                if(count($user) > 0 && $user["pass"] == md5($data["pwd"])){
                    $userInfo = array(
                            "id"=>$user["id"],
                            "name"=>$user["name"],
                            "user"=>$user["user"],
                            "cs"=>$user["cs"],
                            "qx"=>$user["qx"],
                            "logo"=>$user["logo"],
                            "classid"=>$user["classid"],
                            "bm"=>$user["bm"],
                            "cname"=>$user["cname"],
                            "tel_safe"=>$user["tel_safe"],
                            "tel_safe_chk"=>$user["tel_safe_chk"],
                            "qc"=>$user["qc"],
                            "jc"=>$user["jc"],
                            "on"=>$user["on"]
                    );
                    session("u_userInfo",$userInfo);
                    $info = "移动端【用户登陆成功】";
                    $status = 1;
                    session("login_verify",null);
                    session("blocking",null);

                    //发送登陆成功短信
//                    $this->sendMessage($userInfo);(2018/7/30 产品确认取消短信)
                }else{
                    $errMsg = "用户名/订单密码错误！";
                    $info = "移动端【用户名/订单密码错误!】";
                }
                //导入扩展文件
                import('Library.Org.Util.App');
                $app = new \App();
                $ip = $app->get_client_ip();

                //记录日志
                $data = array(
                      "username"=>$data["user"],
                      "userid"=>$user["id"],
                      "ip"=>$ip,
                      "user_agent"=>$_SERVER["HTTP_USER_AGENT"],
                      "info"=>$info,
                      "time"=>date("Y-m-d H:i:s"),
                      "action"=>"muser/dologin",
                      "status"=>$status
                );
                D("Loguser")->addLog($data);

                if(!$status){
                    //根据IP查询前几次的是否登陆成功
                    $logs =  D("Loguser")->getLastLoginLog($ip);
                    if(count($logs) > 0){
                        //连续错误3次及以上，显示验证码
                        if($logs["error"] == 3){
                            $tmp = $this->fetch("login_verify");
                            session("login_verify",1);
                        }
                    }
                    $logs =  D("Loguser")->getLastLoginLog($ip,5);
                    $errorType = 0;
                    if(count($logs) > 0){
                        //连续错误5次及以上，禁止登陆
                        if($logs["error"] == 5){
                            $errorType = 1;
                            $errMsg = '密码错误5次 , 您可以前往PC端找回密码 , 或10分钟后再试';
                            session("blocking_time",time());
                        }
                    }
                }
            } else {
                $errMsg = $model->getError();
                $info = "移动端【" . $errMsg . "】";
            }
            $this->ajaxReturn(array("data"=>$tmp,"info"=>$errMsg,"status"=>$status , 'type'=>$errorType));
        }
    }

    public function loginout(){
        session("u_userInfo",null);
        header("location: /");
        die();
    }

    public function orderinfo(){
        $id = I("get.id");
        header( "HTTP/1.1 301 Moved Permanently");
        header("Location: /orderdetails?id=".$id);
        die();
    }

    /**
     * 发送登陆成功日志
     * @param $userInfo
     */
    private function sendMessage($userInfo)
    {
        //获取当前城市信息
        $cityInfo = getUserPosition();
        $city = D('Area')->getCityById($cityInfo['id']);
        //如果手机号已经验证,则发送登陆成功提醒
        if ($userInfo['tel_safe_chk'] == 1) {
            $info = '';
            $tel = $userInfo["tel_safe"];
            //发送短信
            $smsdata['tel'] = $tel; //手机号码
            $smsdata['type'] = 'login_success'; //文案模板
            $smsdata['sms_channel'] = 'yunrongt';//短信类型
            $smsdata['tpl'] = '【齐装网】您的帐号于' . date('Y-m-d H:i') . '在' . $city[0]['province'] . $cityInfo['cname'] . '登录，如非本公司亲自操作，请立即修改密码。';
            sendSmsQz($smsdata);
        }
    }
}