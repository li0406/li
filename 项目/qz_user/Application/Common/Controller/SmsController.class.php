<?php
namespace Common\Controller;
use Common\Model\Service\SmsServiceModel;
use Think\Controller;
use Think\Log;

class SmsController extends Controller {
    private $data = null;

    /**
     * 发送短信验证码
     * @return [type] [description]
     */
    public function sendsms()
    {
        $data = $_POST; //整个post赋值到data
        Log::write("【sms-post】".json_encode($data));

        $time = time();
        //产生短信验证码
        import('Library.Org.Util.App');
        $app = new \App();
        //获取6位数字
        $code = $app->getSafeCode(6, "NUMBER");

        $tel = $data["tel"];//发送的电话号码

        //发送短信
        $smsService = new SmsServiceModel();
        //参数必须是string，否则无法传参
        $step =  OP('SMS_STEP');
        $params = [$code,$step];
        $result = $smsService->sendSms("201908191002",$tel,$params);

        if ($result["error_code"] == 0) {
            $smsSendStatus = 1; //发送状态为成功
            $smsService->setSafeCodeCookie($code, $tel, $time,$step); //设置加密后的验证码cookie
        } else {
            $smsSendMsg = $result["error_msg"];
        }

        if ($smsSendStatus == 1) { //发送状态为1的
            $this->ajaxReturn(array("data" => "", "info" => "短信发送成功", "status" => 1));
        } else {
            $this->ajaxReturn(array("data" => "", "info" => "短信发送失败" . $smsSendMsg, "status" => 0));
        }
    }

    /**
     * 短信验证码验证
     * @return [type] [description]
     */
    public function verifysmscode(){
        $data = $_POST;
        if(!empty($data["code"])){
            $w_ordersafecode = $_COOKIE["w_ordersafecode"];

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
                    echo json_encode(array("data"=>"","info"=>"亲,您输入的验证码不正确","status"=>0));
                    die();
                }
                //验证通过给一个session对象用于回调方法的验证
                $_SESSION["isverify"] = 1;
                setcookie("w_ordersafecode",$serialize,time()-1,'/', '.'.APP_HTTP_DOMAIN);
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
        $data = $_POST;
        $to = $data["email"];

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
                $data["status"] = 1;
                $data["remark"] = "邮件发送成功";
                $array = array("data"=>"","info"=>"发送成功！","status"=>1);
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

    /**
     * 验证手机验证码
     * @param $code
     * @param $tel
     */
    public function smsCheck($code,$tel)
    {
        if(!empty($code)){
            $w_ordersafecode = $_COOKIE["w_ordersafecode"];
            if(!empty($w_ordersafecode)){
                $arr = unserialize($w_ordersafecode);
                if($tel != $arr["tel"]){
                    return array("data"=>"","info"=>"亲,帐号不符,请输入正确的手机/邮箱" ,"status"=>0);
                }
                if(strtolower($code) != authcode($arr["code"])){
                    return array("data"=>"","info"=>"亲,您输入的验证码不正确" ,"status"=>0);
                }
                return true;
            }else{
                return array("data"=>"","info"=>"亲,验证码失效了！","status"=>0);
            }
        }else{
            return array("data"=>"","info"=>"亲,验证码不正确！","status"=>0);
        }
    }
}
