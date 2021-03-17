<?php
namespace Common\Controller;
use Think\Controller;
class SmsController extends Controller {
    private $data = null;

    /**
     * 获取账号密码短信
     * @return [type] [description]
     */
    public function getpassaccount(){
        if($_POST){
            $smsChannel = I("post.channel");
            $tel = session("uc_forgetpwd_userInfo.safe_tel");
            $user = session("uc_forgetpwd_userInfo.name");
            $id = session("uc_forgetpwd_userInfo.id");
            $this->sms($tel,$user,$id,$smsChannel,"【重置账号密码】 ","editadmin");
        }
    }

    /**
     * 绑定安全电话
     * @return [type] [description]
     */
    public function sendaccountsms(){
        if($_POST){
            $smsChannel = I("post.channel");
            $tel = I("post.tel");
            $user = session("uc_userinfo.name");
            $id = session("uc_userinfo.id");
            $this->sms($tel,$user,$id,$smsChannel,"【绑定用户安全手机】 ","editadmin");
        }
    }

    /**
     * 解除绑定安全电话
     * @return [type] [description]
     */
    public function sendunaccountsms(){
        if($_POST){
            $smsChannel = I("post.channel");
            $tel = session("uc_userinfo.safe_tel");
            $user = session("uc_userinfo.name");
             $id = session("uc_userinfo.id");
            $this->sms($tel,$user,$id,$smsChannel,"【解除绑定用户安全手机】 ","editadmin");
        }
    }

    public function sendsms(){
        if($_POST){
            if(session("uc_userinfo.safe_tel") == ""){
                 $this->ajaxReturn(array("data"=>"", "info"=>"短信发送失败,绑定号码不存在", "status"=>0));
            }
            $smsChannel = I("post.channel");
            $tel = session("uc_userinfo.safe_tel");
            $user = session("uc_userinfo.name");
            $id = session("uc_userinfo.id");
            $this->sms($tel,$user,$id,$channel,"【修改用户密码】 ","editadmin");
        }
    }

    /**
     * 发送短信验证码
     * @param tel 号码
     * @param user 用户名称
     * @param id 用户id
     * @param 短信渠道
     * @param 定义消息
     * @param 定义分类
     * @return [type] [description]
     */
    private function sms($tel,$user,$id,$smsChannel,$errMsg = "",$type){
        $time   = time();
        //产生短信验证码
        import('Library.Org.Util.App');
        $app    = new \App();
        //获取6位数字
        $code   = $app->getSafeCode(6,"NUMBER");
        //加密code
        $authcode = authcode($code,'');

        //统一渠道接口后忽略传入的渠道
        unset($smsChannel);

        $time          =  time(); //取当前时间
        $smsSendStatus = ''; //定义短信发送后的接口返回状态
        $smsSendMsg    = ''; //定义短信发送后接口返回提示信息

        //发送短信
        $smsdatav[]          = $code; //验证码
        $smsdatav[]          = OP('SMS_STEP');//短信的有效时间
        $smsdata['tel']      = $tel;
        $smsdata['type']     = 'yzm';
        $smsdata['variable'] = $smsdatav;
        //扩展信息,传入后可打日志
        $smsdata['extend']['op_user'] = (session("uc_userinfo.user") ? :'');
        $smsdata['extend']['op_id']   = (session("uc_userinfo.id") ? :'');
        $smsdata['extend']['orderid'] = (I("post.orderid") ? :'');

        $result              = sendSmsQz($smsdata);

        $smsSendMsg    = $result["errmsg"]; //发送返回信息
        if($result["errcode"] == 0){ //发送状态为成功
            $smsSendStatus = 1; //标识发送成功下面逻辑会用到
            $this->setSafeCodeCookie($authcode, $tel, $time); //设置加密后的验证码cookie
            $errMsg .= "验证码成功！";
        }else{
            $errMsg .= "验证码失败:".$result["errmsg"];
        }

        //导入扩展文件
        import('Library.Org.Util.App');
        $app = new \App();
        // 记录额外的日志
        $logData = array(
                "logtype"=>$type,
                "time"=>date("Y-m-d H:i:s"),
                "username"=>$user,
                "userid"=>$id,
                "action"=>CONTROLLER_NAME."/".ACTION_NAME,
                "ip"=>$app->get_client_ip(),
                "info"=>"发送用户:".$id.":".$smsdata['type'],
                "user_agent"=>$_SERVER["HTTP_USER_AGENT"],
                "remark"=>$errMsg
                         );
        D("Logadmin")->addLog($logData);

        if($smsSendStatus == 1){
            //发送状态为1的
            $this->ajaxReturn(array("data"=>"", "info"=>"短信发送成功", "status"=>1));
        }else{
            $this->ajaxReturn(array("data"=>"", "info"=>"短信发送失败".$smsSendMsg, "status"=>0));
        }
    }

    /**
     * 短信验证码验证
     * @return [type] [description]
     */
    public function verifysmscode($code,$tel){
        $status = 0;
        if(!empty($code)){
            $w_ordersafecode = $_COOKIE["uc_qizuang_sms"];
            if(!empty($w_ordersafecode)){
                $arr = unserialize($w_ordersafecode);

                if($tel != $arr["tel"]){
                    $errMsg = "电话号码不符,请重新验证！";
                }else{
                    if(strtolower($code) != authcode($arr["code"])){
                        $errMsg = "验证码不符,请重新输入！";
                    }else{
                        //验证通过给一个session对象用于回调方法的验证
                        //setcookie("uc_qizuang_sms",$serialize,time()-1,'/', '.'.C('QZ_YUMING'));
                        $status = 1;
                    }
                }
            }else{
                $errMsg = "验证码已过期,请重新验证";
            }
        }else{
            $errMsg = "验证码不存在,请重填写！";
        }

        return array("status"=>$status,"errMsg"=>$errMsg);
    }

    /**
     * 设置加密后的验证码cookie
     * @param  $code 加密后的验证码
     * @param  $tel  手机号
     * @param  $time 当前时间
     * @return 无
     */
    private function setSafeCodeCookie ($authcode, $tel, $time) {
        //设置cookie
        $arr       = array("code"=>$authcode,"tel"=>$tel); //做一个数组记录 加密的验证码 和 手机号码
        $serialize = serialize($arr); //序列化
        setcookie("uc_qizuang_sms", $serialize, $time+1800,'/', '.'.C('QZ_YUMING')); //放入cookie
    }
}
