<?php
namespace User\Controller;
use Common\Model\Service\SmsServiceModel;
use User\Common\Controller\UserCenterBaseController;
class GetpasswordController extends UserCenterBaseController{
    public function index(){
        if($_POST){
            //当前第几步
            $step = I("post.step");
            switch ($step) {
                case '1':
                    //第二步
                    //将当前第几步保存到session中,为跳转页面做准备
                    $_SESSION["step"] = $step+1;
                    $name = I("post.name");
                    $code = I("post.code");
                    if(check_verify($code)){
                        $user = D("User")->findUserInfoByUser($name);
                        if(count($user) > 0){
                            if(!empty($user["mail_safe"])){
                                $exp = explode("@", $user["mail_safe"]);
                                $user["mail_safe_show"] =str_replace($exp[0], substr_replace($exp[0],"*****",2,strlen($exp[0])-4), $user["mail_safe"]) ;
                            }
                            if(!empty($user["tel_safe"])){
                                $user["tel_safe_show"] =substr_replace($user["tel_safe"],"*****",3,strlen($user["tel_safe"])-5);
                            }
                            $_SESSION["password_user"] = $user;
                            $this->ajaxReturn(array("data"=>"","info"=>"用户存在","status"=>1));
                        }else{
                            $this->ajaxReturn(array("data"=>"1","info"=>"无效的用户名","status"=>0));
                        }
                    }else{
                        $this->ajaxReturn(array("data"=>"2","info"=>"验证码不匹配","status"=>0));
                    }
                break;
                case '2':
                    //修改密码第二步,发送验证码
                    if(I("post.mode") == 1){
                        //邮件发送
                        $result = $this->sendMail();
                        $send_model = "邮件";
                    }else{
                        if (S("Cache:Geetest:".session_id()) == 1) {
                            //短信发送
                            $result = $this->sendSms();
                            $send_model = "短信";
                            S("Cache:Geetest:".session_id(),null);
                        }
                    }
                    if(!empty($result) && $result !== false){
                        $_SESSION["step"] = $step+1;
                        $_SESSION["mode"] = I("post.mode");

                        $info = "用户以【".$send_model."】方式发送验证码成功！";
                        $this->saveLog($_SESSION["password_user"]["user"],$_SESSION["password_user"]["id"],$info);
                        $this->ajaxReturn(array("data"=>"","info"=>"发送成功!","status"=>1));
                    }else{
                        $info = "用户以【".$send_model."】方式发送验证码失败！";
                        $this->saveLog($_SESSION["password_user"]["user"],$_SESSION["password_user"]["id"],$info);
                        $this->ajaxReturn(array("data"=>"","info"=>"发送失败!","status"=>0));
                    }
                break;
                case '3':
                    $mode = I("post.mode");
                    if(!empty($mode)){
                        //重新获密码
                        if(I("post.mode") == 1){
                            //邮件发送
                            $result = $this->sendMail();
                            $send_model = "邮件";
                        }else{
                            if (S("Cache:Geetest:".session_id()) == 1) {
                                //短信发送
                                $result = $this->sendSms();
                                $send_model = "短信";
                                S("Cache:Geetest:".session_id(),null);
                            }
                        }

                        if(!empty($result) && $result !== false){
                            $info = "用户以【".$send_model."】方式重新发送验证码成功！";
                            $this->saveLog($_SESSION["password_user"]["user"],$_SESSION["password_user"]["id"],$info);
                            $this->ajaxReturn(array("data"=>"","info"=>"发送成功!","status"=>1));
                        }else{
                            $info = "用户以【".$send_model."】方式重新发送验证码失败！";
                            $this->saveLog($_SESSION["password_user"]["user"],$_SESSION["password_user"]["id"],$info);
                            $this->ajaxReturn(array("data"=>"","info"=>"发送失败!","status"=>0));
                        }
                    }else{
                        $result = $this->editPassword();
                        if(gettype($result) != "string") {
                            $_SESSION["step"] = $step+1;
                            $info = "用户修改密码成功！";
                            $this->saveLog($_SESSION["password_user"]["user"],$_SESSION["password_user"]["id"],$info);
                            $this->ajaxReturn(array("data"=>"","info"=>$result,"status"=>1));
                        }else{
                            $info = "用户修改密码失败！";
                            $this->saveLog($_SESSION["password_user"]["user"],$_SESSION["password_user"]["id"],$info);
                            $this->ajaxReturn(array("data"=>"","info"=>$result,"status"=>0));
                        }
                    }
                break;
            }
        }else{
            $step = $_SESSION["step"];
            unset($_SESSION["step"]);
            switch ($step) {
                case '2':
                    //修改密码第二部,发送验证码
                    $user = $_SESSION["password_user"];
                    //判断用户是绑定了邮箱还是绑定了电话,如果都绑定则让用户选择
                    if($user["mail_safe_chk"] && $user["tel_safe_chk"]){
                        //绑定了邮箱和手机,用户可以自主选择通过哪种方式获取验证码
                        $mode = 3;
                    }elseif($user["mail_safe_chk"]){
                        //绑定了邮箱
                        $mode = 2;
                    }elseif($user["tel_safe_chk"]){
                        //绑定了邮箱、
                        $mode = 1;
                    }else{
                        //两者都未绑定
                        $mode = 0;
                    }
                    $this->assign("mode",$mode);
                    $this->assign("userInfo",$user);
                    //产生发送验证的token
                    $rand = substr(md5(time()), 0, 10);
                    $token =authcode($rand.$user["user"],"");
                    $_SESSION["password_token"] = $token;
                    $this->assign("token",$token);
                    $t = "step2";
                    break;
                case '3':
                    //修改密码第三步,修改密码
                    $t = "step3";
                    $_SESSION["getNum"] = 5;
                    //产生发送验证的token
                    $rand = substr(md5(time()), 0, 10);
                    $token =authcode($rand.$user["user"],"");
                    $_SESSION["password_token"] = $token;
                    $this->assign("token",$token);
                    //获取发送验证码的方式
                    $mode = $_SESSION["mode"];
                    $this->assign("mode",$mode);
                    break;
                case '4':
                    //修改密码第四步,修改成功
                    $t = "step4";
                    unset($_SESSION["getNum"]);
                    unset($_SESSION["password_user"]);
                    unset($_SESSION["u_userInfo"]);
                    unset($_SESSION["mode"]);
                    setcookie("w_getpassword",null,-1,'/', '.'.APP_HTTP_DOMAIN);
                    break;
                default:
                    //修改密码第一步
                    $t = "step1";
                    break;
            }
            $this->display($t);
        }
    }

    /**
     * 修改密码
     * @return [type] [description]
     */
    private function editPassword($mode = 2){
        //默认错误5次后本次操作失败
        $num = $_SESSION["getNum"];
        $num --;
        $_SESSION["getNum"] = $num;
        $str = "您还有".$_SESSION["getNum"]."次机会";
        $token = I("post.token");
        if(isset($_SESSION['password_token'])){
            if($_SESSION['password_token'] == $token){
                if(!empty($_COOKIE["w_getpassword"])){
                    $arr = unserialize($_COOKIE["w_getpassword"]);

                    if($arr['mode'] == 2){
                        if($_SESSION["password_user"]["mail_safe"] != $arr["tel"]){
                            return "安全邮箱不匹配,请重新操作";
                        }
                    }else{
                        if($_SESSION["password_user"]["tel_safe"] != $arr["tel"]){
                            return "安全手机不匹配,请重新操作";
                        }
                    }
                    if($_SESSION["getNum"] <= 0){
                        return "您已经输错了5次,请重新操作！";
                    }else{
                        if(strtolower(I("post.code")) != authcode($arr["code"])){
                            return "验证码不正确,请重新输入,".$str;
                        }
                    }

                    $id = $_SESSION["password_user"]["id"];
                    $model = D("User");//实例化user的model
                    //检测新老密码是否一致
                    $check_user_compare_password=$model->check_user_compare_password($id,md5($_POST['password']));
                    if ($check_user_compare_password==='same') {
                        return "重置密码与原始密码一致！";
                    }elseif ($check_user_compare_password===false) {
                        return "传参丢失";
                    }

                    $data = array(
                            "pass"=>$_POST["password"]
                                  );
                    if($model->create($data,6)){
                        unset($_SESSION['password_token']);
                        $data["pass"] = md5($data["pass"]);
                        return $model->edtiUserInfo($id,$data);
                    }
                }
            }
        }
        return "非法提交";
    }

    /**
     * 发送邮件验证码
     * @return [type] [description]
     */
    private function sendMail(){
        $token = I("post.token");
        if(isset($_SESSION['password_token'])){
            if($_SESSION['password_token'] == $token){
                $to = $_SESSION["password_user"]["mail_safe"];
                //产生验证码
                import('Library.Org.Util.App');
                $app = new \App();
                //获取6位数字
                $code = $app->getSafeCode(6,"NUMBER");
                //加密code
                $authcode = authcode($code,'');
                $time = time();
                //设置cookie
                $arr = array("code"=>$authcode,"tel"=>$to,"mode"=>2);
                $serialize = serialize($arr);
                setcookie("w_getpassword",$serialize,$time+1800,'/', '.'.APP_HTTP_DOMAIN);
                $t = T("Common@Email/getPassword");
                $tmp = $this->fetch($t);
                $html = sprintf($tmp,$code);
                $data = array(
                        "api_user"=>OP('SENDCLOUD_ACCOUNT'),
                        "api_key" =>OP('SENDCLOUD_KEY'),
                        "from"=>OP('SENDCLOUD_FROM'),
                        "to"=>$to,
                        "subject"=>'您的验证码（来自：齐装网）',
                        "html"=>$html
                              );
                $url ="http://sendcloud.sohu.com/webapi/mail.send.json";
                //初始化curl
                $ch = curl_init();
                //参数设置
                curl_setopt ($ch, CURLOPT_URL,$url);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt ($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
                // curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
                $result = curl_exec ($ch);
                $saveData = array(
                        "uid"=>$_SESSION["password_user"]["id"],
                        "time"=>time(),
                        "ip"=>$app->get_client_ip(),
                        "contact"=>$_SESSION["password_user"]["mail_safe"],
                        "status" => 0
                                  );
                if($result !== FALSE){
                    $json = json_decode($result,true);
                    $saveData["content"] = $json["message"];
                    if(strtolower($json["message"]) !== "error"){
                        $saveData["status"] = 1;
                        return true;
                    }
                }
                curl_close($ch);
                //记录获取密码的日志
                D("LogGetpassword")->addLog($saveData);
                unset($_SESSION['password_token']);
            }
        }
        return false;
    }

    /**
     * 发送短信验证码
     * @return [type] [description]
     */
    private function sendSms(){
        $token = I("post.token");
        if(isset($_SESSION['password_token'])){
            if($_SESSION['password_token'] == $token){
                $time = time();
                //产生验证码
                import('Library.Org.Util.App');
                $app = new \App();
                //获取6位数字
                $code = $app->getSafeCode(6,"NUMBER");
                $tel = $_SESSION["password_user"]["tel_safe"];//发送的电话号码

                //发送短信
                $smsService = new SmsServiceModel();
                //参数必须是string，否则无法传参
                $step =  OP('SMS_STEP');
                $params = [$code,$step];
                $result = $smsService->sendSms("201908191002",$tel,$params);

                //添加发送短信日志
                $saveData = array(
                    "uid" => $_SESSION["password_user"]["id"],
                    "time" => $time,
                    "ip" => $app->get_client_ip(),
                    "contact"=> $tel,
                    "content"=> $result["error_msg"]
                );

                if ($result["error_code"] == 0) {
                    $saveData["status"] = 1;
                    $smsService->setPasswordCodeCookie($code, $tel, $time,$step); //设置加密后的验证码cookie
                }

                //记录获取密码的日志
                D("LogGetpassword")->addLog($saveData);
                return true;
            }
        }
       return false;
    }

    /**
     * 记录日志
     * @param  [type] $name    [description]
     * @param  [type] $user_id [description]
     * @param  [type] $info    [description]
     * @return [type]          [description]
     */
    private function saveLog($name,$user_id,$info)
    {
        //记录用户日志
        //导入扩展文件
        import('Library.Org.Util.App');
        $app = new \App();
        //记录日志
        $data = array(
              "username" => $name,
              "userid" => $user_id,
              "ip" => $app->get_client_ip(),
              "user_agent"=>$_SERVER["HTTP_USER_AGENT"],
              "info"=> $info,
              "time"=>date("Y-m-d H:i:s"),
              "action"=>CONTROLLER_NAME."/".ACTION_NAME
              );
        D("Loguser")->addLog($data);
    }
}