<?php
/**
 * 已暂停使用该类
 */
namespace Api\Controller;
use Api\Common\Controller\ApiBaseController;
class SmsController extends ApiBaseController {
    private $data = null;
    public function _initialize(){
         //检测请求的域名是否合法
         //合法的域名数组
        $register_url = C("REGISTER_URL");
        $referer= $_SERVER['HTTP_ORIGIN'];
        if(in_array($referer,$register_url) || preg_match('/([A-za-z])+(.[A-za-z]+)?.'.APP_HTTP_DOMAIN.'/i', $referer)){
            header("Access-Control-Allow-Credentials:true");
            header('Access-Control-Allow-Origin:'.$referer);
            if(isset($GLOBALS['HTTP_RAW_POST_DATA'])){
                $exp = explode("&",$GLOBALS['HTTP_RAW_POST_DATA']);
                $exp = array_filter($exp);
                $data = array();
                foreach ($exp as $key => $value) {
                   $e = explode("=", $value);
                   $data[$e[0]] = $e[1];
                }
                $this->data = $data;
                $ssid = urldecode($data["ssid"]);
            }else{
                $ssid = $_POST["ssid"];
                $action = $_POST["action"];
            }
            if(!empty($ssid)){
                $ssid = authcode($ssid);
                session_id($ssid);
            }
            session_start();
            $this->assign("ssid",authcode(session_id(),""));
        }
    }
    /**
     * 发送短信验证码
     * @return [type] [description]
     */
    public function sendsms(){
        if(empty($this->data)){
            $data = $_POST;
        }else{
            $data = $this->data;
        }

        $user = $data["tel"];
        if(!empty($data["name"])){
            $user = $data["name"];
        }

        if($data["checkUser"] == 1){
            //验证用户帐号
            $count = D("User")->checkAccount($user);
            if($count > 0){
               $this->ajaxReturn(array("data"=>"","info"=>"该用户帐号已被注册!","status"=>0));
           }
        }

        $time = time();
        //产生验证码
        import('Library.Org.Util.App');
        $app = new \App();
        //获取6位数字
        $code = $app->getSafeCode(6,"NUMBER");
        $data[] = $code;
        $data[] = OP('SMS_STEP');//短信的有效间隔时间
        $tmp = OP('SMS_ORDERFB_INDEX');//发送的短信模版


        //authcode 是否需要解密
        if($data["authcode"]){
            //如果是IE6789需要URLDECODE
            if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 9.0") || strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 8.0") ||strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 7.0") || strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 6.0")){
                $data["tel"] = urldecode($data["tel"]);
            }
            $tel = authcode($data["tel"],"DECODE");
        }else{
            $tel = $data["tel"];//发送的电话号码
        }
        //发送短信
        import('Library.Org.yuntongxun.Telytx');
        $Telytx = new \Telytx();
        $result = $Telytx->sendTemplateSMS($tel,$data,$tmp);
        $tel_encrypt = substr_replace($tel,"*****",3,5);
        $tel_md5 =  $app->order_tel_encrypt($tel);
        $logsend =  D("Common/LogSmsUserSend");
        //$result["status"] = 1;
        if($result["status"] == 1){
            //加密code
            $authcode = authcode($code,'');
            $time = time();
            //设置cookie
            $arr = array("code"=>$authcode,"tel"=>$tel);
            $serialize = serialize($arr);
            setcookie("w_ordersafecode",$serialize,$time+1800,'/', '.'.APP_HTTP_DOMAIN);
            $smsData = array(
                        "cid"=>$_SESSION["cityId"],
                        "ip"=>$app->get_client_ip(),
                        "tel"=>$tel_encrypt,
                        "smsMessageSid"=>$result["smsMessageSid"],
                        "tel_encrypt"=>$tel_md5,
                        "dateCreated"=>$result["dateCreated"],
                        "remark"=>$result["msg"],
                        "addtime"=>$time,
                        "status"=>1
                             );
            $logsend->addLog($smsData);
        }else{
            $smsData = array(
                    "cid"=>$_SESSION["cityId"],
                    "ip"=>$app->get_client_ip(),
                    "tel"=>$tel_encrypt,
                    "tel_encrypt"=>$tel_md5,
                    "remark"=>$result["msg"],
                    "addtime"=>$time,
                    "status"=>0
                         );
            $logsend->addLog($smsData);
        }
        //是否需要保存订单
        if($data["save"]){
            $controller =  A("Api/Zbfb");
            //删除save标识
            unset($data["save"]);
            //涉及跨域订单提交到共用方法Api/Zbfb/fb_order;
            $controller->saveOrder($data);
        }else{
            if($result["status"] == 1){
                //如果是IE6、IE7，返回页面设置COOKIE
                if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 9.0") ||strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 8.0") ||strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 7.0") || strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 6.0")){
                    $cookie =array("name"=>"w_ordersafecode","value"=>$serialize,"expires"=>($time+1800)*1000);
                }
                $this->ajaxReturn(array("data"=>$cookie,"info"=>"短信发送成功","status"=>1));
            }else{
                $this->ajaxReturn(array("data"=>"","info"=>"短信发送失败".$result["msg"],"status"=>0));
            }
        }
    }

    /**
     * 短信验证码验证
     * @return [type] [description]
     */
    public function verifysmscode(){
        if(empty($this->data)){
            $data = $_POST;
        }else{
            $data = $this->data;
        }
        if(!empty($data["code"])){
            //如果是IE6789,需要获取传参来的值
            if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 9.0") ||strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 8.0") ||strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 7.0") || strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 6.0")){
                $exp = explode("&", urldecode($data["cookie"]));
                foreach ($exp as $key => $value) {
                    $explode = explode("=",$value);
                    $arr[$explode[0]] = $explode[1];
                }
                $w_ordersafecode = $arr["w_ordersafecode"];
                $expires = $arr["expires"];
                // if( (time() - ($expires/1000)) > 1800){
                //     echo json_encode(array("data"=>"","info"=>"亲,验证码失效了！","status"=>0));
                //     die();
                // }
                $data["tel"] = urldecode($data["tel"]);
            }else{
                $w_ordersafecode =$_COOKIE["w_ordersafecode"];
            }

            if(!empty($w_ordersafecode)){
                $arr = unserialize($w_ordersafecode);
                $tel = $data["tel"];
                //是否需要解密电话号码/邮箱
                if($data["authcode"]){
                    $tel = authcode($data["tel"],"DECODE");
                }
                if($tel != $arr["tel"]){
                    echo json_encode(array("data"=>"","info"=>"亲,帐号不符,请输入正确的手机/邮箱","status"=>0));
                    die();
                }
                if(strtolower($data["code"]) != authcode($arr["code"])){
                    echo json_encode(array("data"=>"","info"=>"亲,您填写的验证码不对！","status"=>0));
                    die();
                }
                // setcookie("w_ordersafecode",$serialize,time()-1,'/', '.'.APP_HTTP_DOMAIN);
                echo json_encode(array("data"=>"","info"=>"亲,验证码输对了！","status"=>1));
                die();
            }else{
                echo json_encode(array("data"=>"","info"=>"亲,验证码失效了！","status"=>0));
                die();
            }
        }else{
            echo json_encode(array("data"=>"","info"=>"亲,验证码不正确！","status"=>0));
            die();
        }
    }

    /**
     * 发送邮件
     * @return [type] [description]
     */
    public function sendemail(){
        if(empty($this->data)){
            $data = $_POST;
        }else{
            $data = $this->data;
        }
        if($data["checkUser"] == 1){
            //验证用户帐号
            $count = D("User")->checkAccount($data["email"]);
            if($count > 0){
               $this->ajaxReturn(array("data"=>"","info"=>"该用户帐号已被注册!","status"=>0));
           }
        }
        $to = $data["email"];
        if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 9.0") ||strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 8.0") ||strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 7.0") || strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 6.0")){
            $to = urldecode($to);
        }
        //是否需要解密电话号码/邮箱
        if($data["authcode"]){
            $to = authcode($data["email"],"DECODE");
        }

        //产生验证码
        import('Library.Org.Util.App');
        $app = new \App();
        //获取6位数字
        $code = $app->getSafeCode(6,"NUMBER");
        //加密code
        $authcode = authcode($code,'');
        $time = time();
        //设置cookie
        $arr = array("code"=>$authcode,"tel"=>$to);
        $serialize = serialize($arr);
        setcookie("w_ordersafecode",$serialize,$time+1800,'/', '.'.APP_HTTP_DOMAIN);

        $t = T("Common@Email/registerEmail");
        $tmp = $this->fetch($t);
        $html = sprintf($tmp,$code);
        $data = array(
                "api_user"=>OP('SENDCLOUD_ACCOUNT'),
                "api_key" =>OP('SENDCLOUD_KEY'),
                "from"=>OP('SENDCLOUD_FROM'),
                "to"=>$to,
                "subject"=>'请验证您的邮箱账号（来自：齐装网）',
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
        //errors
        $model = D("LogEmailUserSend");
        $data = array(
                "uid"=>$_SESSION["u_userInfo"]["id"],
                "from"=>OP('SENDCLOUD_FROM'),
                "to"=>$to,
                "status"=>0,
                "remark"=>"邮件发送失败！",
                "time"=>time()
                      );
        if($result !== FALSE){
            $json = json_decode($result,true);
            if(strtolower($json["message"]) !== "error"){
                //如果是IE6、IE7，返回页面设置COOKIE
                if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 9.0") || strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 8.0") ||strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 7.0") || strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 6.0")){
                    $cookie =array("name"=>"w_ordersafecode","value"=>$serialize,"expires"=>($time+1800)*1000);
                }
                $data["status"] = 1;
                $data["remark"] = "邮件发送成功";
                $array = array("data"=>$cookie,"info"=>"发送成功！","status"=>1);
            }else{
                $errMsg = "";
                foreach ($json["errors"] as $key => $value) {
                    $errMsg .= $value."/";
                }
                $data["remark"] = $errMsg;
                $array = array("data"=>"","info"=>"发送失败,请稍后再试！","status"=>0);
            }
        }else{
            $array = array("data"=>"","info"=>"发送失败,请稍后再试！","status"=>0);
        }
        $model->addLog($data);
        curl_close($ch);
        $this->ajaxReturn($array);
    }
}
