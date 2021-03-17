<?php
namespace User\Controller;
use Think\Log;
use User\Common\Controller\UserCenterBaseController;
class IndexController extends UserCenterBaseController {
    public function index(){
        //安全验证码
        $code = substr(md5(time()), 0, 10);
        $safecode = authcode($code,"");
        $_SESSION["safecode"] = $code;
        $this->assign("safecode",$safecode);
        $this->assign("ssid",authcode(session_id(),""));

        /*
        //获取微信登录二维码
        import('Library.Org.Util.Mywechat');
        $Mywechat = new \Mywechat();
        $authorizeURL = $Mywechat->getloginQr();
        $authorizeURL = $authorizeURL;
        dump($authorizeURL);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $authorizeURL);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $tempCont = curl_exec($ch);
        curl_close($ch);
        preg_match('/<img class="qrcode lightBorder" src="(.+?)" \/>/is',$tempCont,$match);
        preg_match('/qrconnect\?uuid\=(.+?)"/is',$tempCont,$uuid);
        preg_match('/\&state\=(.*?)\"/i',$tempCont,$state);
        $this->assign("wxImg",$match['1']);
        $this->assign("wxUUID",$uuid['1']);
        $this->assign("wxState",$state['1']);
        */
        //用ip做缓存,判断当前电脑是否要添加限制
        import('Library.Org.Util.App');
        $app = new \App();
        $ip = $app->get_client_ip();
        $this->assign('islogincheck', S('User:Login:astrict:islogincheck:' . $ip));
        //获取验证模板
        $t =T('Common@verify/verify');
        $tmp = $this->fetch($t);
        $this->assign("verify",$tmp);
        $this->display();
    }


    //封禁用户
    public function blocked(){
        $info['text'] = session('blocked');
        $this->assign("info",$info);
        $this->display();
    }


    /**
     * 用户确认绑定
     * @return [type] [description]
     */
    public function confirmbindaccount()
    {
        header("Content-type:text/html;charset=utf-8");
        $tmp = '<style>.c {text-align: center;  margin-top: 10px;}</style>
                <div class="c" style=""><h1 style="font-size: 68px;">%s</h1></div>';
        if(I("get.code") == ""){
            die("parameter error!!!");
        }

        //根据code（ticket） 查询用户的帐号
        $wxtk =  D("Logwxticket")->getTicketmd5ToUserid(I("get.code"),I("get.sceneid"));
        $str = "绑定失败！";
        if(count($wxtk) > 0){
            //把用户信息绑定到订单通知的关联表中
            //查询用户回调是的记录
            $callback = D("Logwechatcallbak")->checkCallbackTicket(trim($wxtk["wx_ticket_now"]));
            if(count($callback) > 0){
                // 查询该用户是否已经绑定2次
                $count = D("Orderwechar")->getAccountCount($wxtk["userid"], $wxtk["employee_id"]);
                $maxnum = $wxtk["employee_id"] ? 1 : 3;
                if($count >= $maxnum){
                    $str = "绑定失败！一个账号只能绑定".$maxnum."个微信！";
                } else {
                    //查询该用户是否绑定
                    $wechar = D("Orderwechar")->getAccountByOpenId($callback["fromusername"],$wxtk["userid"], '', $wxtk["employee_id"]);
                    if(count($wechar) == 0){
                        //没有绑定记录则添加
                        $data["wx_openid"] = $callback["fromusername"]; //用户的openid
                        $data["comid"] = $wxtk["userid"];
                        $data["employee_id"] = $wxtk["employee_id"];
                        $data["time"] = $data["update_time"] = time();
                        $i = D("Orderwechar")->addAccount($data);
                    } else {
                        $data['is_delete'] = 0;
                        $data["update_time"] = time();
                        //如果已删除绑定则恢复为未删除
                        $i = D("Orderwechar")->editAccount($wechar["wx_openid"],$wxtk["userid"],$data);
                    }
                    $user['id'] = $wxtk["userid"];
                    $this->addLog($user,'微信绑定成功',1);
                    if($i !== false){
                        $_SESSION['u_wx_ticket_need_user_ok'] = 1;
                        $str ="绑定成功！";
                    }
                }
            }else{
                $sql = m()->getlastsql();
                $ticket = $wxtk["wx_ticket_now"];
                if (!empty($wxtk["employee_id"])) {
                    $data = array(
                        "content"=> sprintf("微信帐号绑定失败:【员工编号 %s】票据 【%s】查询语句:【%s】", $wxtk["employee_id"], $ticket, $sql),
                        "time"=>time()
                    );
                } else {
                    $data = array(
                        "content"=>sprintf("微信帐号绑定失败:【员工编号 %s】票据 【%s】查询语句:【%s】", $wxtk["userid"], $ticket, $sql),
                        "time"=>time()
                    );
                }
                M("log_debug")->add($data);
            }
        }
        $tmp = sprintf($tmp,$str);
        echo $tmp;
    }

    /**
     * 短信发送
     * @return [type] [description]
     */
    public function sendsms(){
        if(IS_POST){
            Log::write("【sms】".json_encode($_POST));
            if (S("Cache:Geetest:".session_id()) == 1) {
                S("Cache:Geetest:".session_id(),null);
                R("Common/Sms/sendsms");
            }
        }
        $this->ajaxReturn(array("data" => "", "info" => "验证错误", "status" => 9));
        die();
    }

    /**
     * 登陆短信发送
     * @return [type] [description]
     */
    public function login_sendsms()
    {
        if (IS_POST) {
            if (empty($_POST['tel'])){
                $this->ajaxReturn(array("data" => "", "info" => "手机号不能为空！", "status" => 0));
            }

            if (S("Cache:Geetest:".session_id()) == 1) {
//                $user = D("Common/User")->findUserInfoByUser($_POST['name']);
                $user = D("Common/User")->findUserInfoByUserTelSafe($_POST['tel']);
//                if (empty($user['tel_safe']) || $user['tel_safe_chk'] != 1){
//                    $this->ajaxReturn(array('data' =>[], 'info' => '未绑定安全手机', 'status' => 0));
//                }
                if(empty($user)){
                    $userCompanyEmployee = new \Common\Model\Db\UserCompanyEmployeeModel();
                    $employee = $userCompanyEmployee->getEmployeeInfo($_POST['tel']);
                    if(empty($employee)){
                        $this->ajaxReturn(array('data' =>[], 'info' => '用户不存在', 'status' => 0));
                    }
                    $user['tel_safe'] = $employee['account'];
                }
                unset($_POST['tel']); //清除用户信息，然后发送短信
                $_POST['tel'] = $user['tel_safe'];
                $_POST['authcode'] = 2;
                S("Cache:Geetest:".session_id(),null);
                R("Common/Sms/sendsms");
            }
        }
        die();
    }

     /**
     * 邮件发送
     * @return [type] [description]
     */
    public function sendemail(){
        if(IS_POST){
            if (S("Cache:Geetest:".session_id()) == 1) {
                S("Cache:Geetest:".session_id(),null);
                R("Common/Sms/sendemail");
            }
        }
        $this->ajaxReturn(array("data"=>"","info"=>"验证错误！","status"=>9));
        die();
    }

    public function login_verifysmscode(){
        if($_POST['tel']){
            //将手机号解码
            $_POST['tel'] = authcode($_POST['tel'], 'DECODE');
        }
        $this->verifysmscode();
    }

    /**
     * 验收手机验证码
     */
    public function verifysmscode(){
        R("Common/Sms/verifysmscode");
    }

    /**
    * 获取验证码
    * @return [type] [description]
    */
    public function verify(){
       getVerify("",4,80,32,11,false,false,true,false,'');
    }

    /**
     * 验证验证码
     * @return [type] [description]
     */
    public function check_verify(){
        if(IS_POST){
            $code = strtolower($_POST["code"]);
            if(check_verify($code)){
                 $this->ajaxReturn(array("data"=>"","info"=>"验证码正确！","status"=>1));
            }
            $this->ajaxReturn(array("data"=>"","info"=>"验证码不正确！","status"=>0));
        }
    }

    /**
     * 用户登录
     * @return [type] [description]
     */
    public function login(){
        if (isset($_POST['code'])){  //手机验证登录
            R("Common/Login/smsLoginIn");
        }else{
            if (S("Cache:Geetest:".session_id()) == 1) {
                S("Cache:Geetest:".session_id(),null);
                R("Common/Login/Loginin");
            }
        }
        die();
    }

    /**
     * 后台假会员登录
     * @return [type] [description]
     */
    public function backLogin(){
        if (isset($_GET['company_id'])){
            //实例化redis
            $redis = new \Redis();
            //连接
            $redis->connect(C('REDIS_HOST'), C('REDIS_PORT'));
            if($redis->get('u_userInfo') == $_GET['company_id']){
                $model = D("Common/User");
                $user = $model->getSingleUserInfoById($_GET['company_id']);
                $_SESSION["u_userInfo"] = array(
                    "id" => $user["id"],
                    "name" => $user["name"],
                    "user" => $user["user"],
                    "cs" => $user["cs"],
                    "qx" => $user["qx"],
                    "logo" => $user["logo"],
                    "classid" => $user["classid"],
                    "bm" => $user["bm"],
                    "cname" => $user["cname"],
                    "jc" => $user["name"],
                    "on" => $user["on"],
                    'blocked' => $user['blocked'],
                    'state' => 1
                );
                $this->success('跳转成功', '/home');
            }
        }
    }

    /**
     * 获取用户信息,判断是否需要添加短信认证
     * @return [type] [description]
     */
    public function login_userinfo()
    {
        if (IS_POST) {
            $user = I('post.user');
            $user = D("Common/User")->findUserInfoByUser($user);
            if ($user) {
                //获取当前用户两天内登陆次数最多的 3 个ip作为常用ip
                $start = date("Y-m-d", strtotime(date("Y-m-d H:i:s", strtotime('-1 day')))) . ' 00:00:00';//开始
                $end = date("Y-m-d H:i:s");
                $log_list = D("Common/Loguser")->getLoginSucceedList($user['id'], $start, $end, 1, 'count(id) count,ip', 'count desc', 'ip');
                import('Library.Org.Util.App');
                $app = new \App();
                $ip = $app->get_client_ip();
                //1 判断是否要发送 1:不需要 0.需要
                //1.1 IP白名单不需要验证码
                if (in_array($ip, C('IP_WHITE_LIST'))) {
                    $this->ajaxReturn(['status' => 1, 'info' => '验证通过 , 请登录']);
                }
                if (!in_array($ip, array_column($log_list, 'ip'))) {
                    //判断是否有绑定安全手机
                    if($user['tel_safe_chk'] == 1){
                        $this->ajaxReturn(['status' => 0,'tel'=>authcode($user['tel_safe'],1)]);
                    }else{
                        $this->ajaxReturn(['status' => 1, 'info' => '验证通过 , 请登录']);
                    }
                } else {
                    $this->ajaxReturn(['status' => 1, 'info' => '验证通过 , 请登录']);
                }
            }
            $this->ajaxReturn(['status' => 2, 'info' => '用户帐号/密码错误']);
        }
    }

    /**
     * 用户退出
     * @return [type] [description]
     */
    public function loginout(){
         R("Common/Login/loginout");
         die();
    }

    public function account(){
        R("Common/Accountsecurity/account");
        die();
    }

    public function confirmAccount(){
        R("Common/Accountsecurity/confirmAccount");
        die();
    }

    public function fb_order(){
        R("Common/Zbfb/fb_order");
        die();
    }

    public function cancelcollect(){
        R("Common/Collect/cancelcollect");
        die();
    }

    public function bindaccount(){
        R("Common/Accountsecurity/bindaccount");
        die();
    }

    /**
     * 添加用户登录信息
     * @param $user 用户数据
     * @param $info 记录信息
     * @param $status 登录状态
     */
    private function addLog($user, $info = "", $status = 1)
    {
        //导入扩展文件
        import('Library.Org.Util.App');
        $app = new \App();
        //记录日志
        $data = array(
            'username' => empty($user['jc']) ? $user['qc'] : $user['jc'],
            'userid' => $user['id'],
            'ip' => $app->get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'info' => $info,
            'time' => date('Y-m-d H:i:s'),
            'action' => CONTROLLER_NAME . '/' . ACTION_NAME,
            'status' => $status
        );
        D('Loguser')->addLog($data);
    }
}