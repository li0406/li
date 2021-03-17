<?php
namespace Common\Controller;

use Common\Model\Logic\UserCompanyLogic;
use Common\Model\Logic\UserLogicModel;
use Common\Model\UsercompanyModel;
use Think\Controller;
use Common\Model\Logic\JwtTokenLogicModel;

class LoginController extends Controller {
    /**
     * 请求登录窗口
     * @return [type] [description]
     */
    public function login(){
      //安全验证码
      $safe = getSafeCode();
      $this->assign("safecode",$safe["safecode"]);
      $this->assign("safekey",$safe["safekey"]);
      $this->assign("ssid",$safe["ssid"]);

      $t = T("Common@Login/login");
      $tmp = $this->fetch($t);
      $this->ajaxReturn(array("data"=>$tmp,"info"=>"请求成功" ,"status"=>1));
    }

    /**
     * 登录操作
     * @return [type] [description]
     */
    public function loginin()
    {
        $data = $_POST;
        if (empty($data)){
            $this->ajaxReturn(array("data" => "", "info" => "页面请求失败了,请刷新页面！", "status" => 0));
            die();
        }
        $info = array(
            "user" => strtolower(trim($data["name"])),
            "pass" => trim($data["password"])
        );
        $model = D("Common/User");
        if ($model->create($info, 5)) {

            $user = $model->findUserInfoByUser($info["user"]);

            if (date("Y-m-d") < "2020-11-20" && (count($user) == 0 || count($user) > 0 && in_array($user["classid"],array(1,2)))) {
                //老的设计师帐号
                //判断是否是电话号码登录
                import('Library.Org.Util.App');
                $app = new \App();
                $result = $app->validate_mobile($info["user"]);

                if ($result) {
                    //如果满足电话号码条件
                    //查询这个号码是否注册再新的装修公司员工表中
                    $logic = new UserCompanyLogic();
                    $newInfo = $logic->getEmployeeInfo($info["user"]);

                    if (count($newInfo) > 0) {
                        if (password_verify($info["pass"], $newInfo["password"])) {
                            //登录成功
                            $token = JwtTokenLogicModel::approvalSubAccountToken($newInfo["employee_id"]);
                            $data =[
                                "token" => $token,
                                "module" => 2 //1 老后台 2.新后台
                            ];

                            $this->addLog(['name' => $newInfo['name'], 'id' => $newInfo['id'],"employee_id" => $newInfo["employee_id"]], '登录后台成功', 2);
                            $this->ajaxReturn(array("data" => $data,  "status" => 1));
                        }
                        //记录日志
                        $this->addLog(['name' => $newInfo['name'], 'id' => $newInfo['id'],"employee_id" => $newInfo["employee_id"]], '用户帐号/密码错误', 2);
                        $this->ajaxReturn(array("data" => "", "info" => "用户帐号/密码错误", "status" => 0));
                    }
                }

            }

            if (date("Y-m-d") >= "2020-11-20" && $user['fake'] !=  1){
                $this->ajaxReturn(array('data' =>[], 'info' => '登录入口已关闭', 'status' => 1000100));
            }

            if($user['classid'] == 1){
                $this->ajaxReturn(array('data' =>[], 'info' => '用户不存在 ', 'status' => 0));
            }



            if (!empty($user)) {
                //start(P2.12.17 PC端提高帐号安全性)
                //如果连续登录出错 , 做一些限制
                import('Library.Org.Util.App');
                $app = new \App();
                $ip = $app->get_client_ip();
                if (S('User:Login:astrict:blocking:' . $ip) == 1) {
                    $relieveblocking = S("User:Login:astrict:relieveblocking:" . $ip);
                    if ($relieveblocking) {
                        $text = '，预计' . $relieveblocking . '后可进行登录';
                        $this->ajaxReturn(array("data" => "", "info" => '密码错误已超过5次'.$text, "status" => 0));
                    }
                }

                //记录日志后再去判断 错误日志有几条 , 来做对应的限制
                //--end(P2.12.17 PC端提高帐号安全性)
                if ($user["pass"] == md5($info["pass"])) {
                    $result = check_pwd($info["pass"]);
                    if (!$result) {
                        $this->ajaxReturn(array('info' => '密码强度过低,请重置后登陆 ', 'status' => 0));
                    }

                    switch ($user["classid"]) {
                        case '3':
                        case '6':
                            //企业用户
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
                                "qc" => $user["qc"],
                                "jc" => $user["jc"],
                                "on" => $user["on"],
                                'blocked' => $user['blocked'],
                                'tel_safe' => $user['tel_safe']
                            );

                            //新签返token
                            $_SESSION["u_userInfo"]["company_token"] = JwtTokenLogicModel::approvalToken();
                            break;
                        case "1":
                        case "2":
                            //普通用户
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
                                'tel_safe' => $user['tel_safe']
                            );
                            break;
                        case "4":
                            //cpa用户
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
                                    "qc" => $user["qc"],
                                    "jc" => $user["jc"],
                                    "on" => $user["on"],
                                    'blocked' => $user['blocked'],
                                    'tel_safe_chk' => $user['tel_safe_chk'],
                                    'tel_safe' => $user['tel_safe']
                                );
                            break;
                    }
                    D('User')->edtiUserInfo($user["id"], array("login_time" => time()));
                    //发送登陆成功短信
                    //$this->sendMessage($user);(2018/7/30 产品确认取消短信)
                    //记录日志
                    $info = $user['cooperate_mode'] == 2 ? '用户登录签返后台成功' : '用户登录后台成功';
                    $this->addLog(['name' => $user['user'], 'id' => $user['id']], $info);
                    $returnData = '';
                    //区分新签返会员跳转新后台
                    if($user["classid"] == 3 && $user["fake"] == 0){
                        $returnData = [
                            "class_id" => $user["classid"],
                            "module" => 2,
                            "token" => $_SESSION["u_userInfo"]["company_token"],
                        ];
                    }

                    $this->ajaxReturn(array("data" => $returnData, "info" => "登录成功", "status" => 1));
                } else {
                    //记录日志
                    $this->addLog(['name' => $user['user'], 'id' => $user['id']], '用户帐号/密码错误', 2);
                    $this->ajaxReturn(array("data" => "", "info" => "用户帐号/密码错误", "status" => 0));
                }
            } else {
                //用户没有查到直接返回错误
                $this->ajaxReturn(array("data" => "", "info" => "用户帐号/密码错误", "status" => 0));
            }
        } else {
            //直接返回错误
            $this->ajaxReturn(array("data" => "", "info" => $model->getError(), "status" => 0));
        }
        die();
    }

    //退出操作
    public function loginout(){
        $bm = $_SESSION["u_userInfo"]["bm"];
        unset($_SESSION["u_userInfo"]);
        if(empty($bm)){
            $url = "http://".C("QZ_YUMINGWWW");
        }else{
            $url = "http://".$bm.".".C("QZ_YUMING");
        }
        if(IS_AJAX){
            $this->ajaxReturn(array("data"=>$url,"info"=>"","status"=>1));
        }else{
            header("location:".$url);
        }

    }

    /**
     * 查询用户是否登录
     * @return [type] [description]
     */
    public function run(){
        if(isset($_SESSION["u_userInfo"])){
           $this->ajaxReturn(array("data"=>"","info"=>"","status"=>1));
        }
        $this->ajaxReturn(array("data"=>"","info"=>"","status"=>0));
    }

    /**
     * 添加用户登录信息
     * @param $user 用户数据
     * @param $info 记录信息
     * @param $status 登录状态
     */
    private function addLog(array $user, $info = "用户登录成功", $status = 1)
    {
        //导入扩展文件
        import('Library.Org.Util.App');
        $app = new \App();
        //记录日志
        $data = array(
            "username" => $user["name"],
            "userid" => $user["id"],
            "employee_id" => empty($user['employee_id'])?"":$user['employee_id'],
            "ip" => $app->get_client_ip(),
            "user_agent" => $_SERVER["HTTP_USER_AGENT"],
            "info" => $info,
            "time" => date("Y-m-d H:i:s"),
            "action" => CONTROLLER_NAME . "/" . ACTION_NAME,
            "status" => $status
        );
        D("Loguser")->addLog($data);
        if ($status != 1 && empty($user['employee_id'])) {
            //获取是否验证 (记录日志后再去判断 错误日志有几条 , 来做对应的限制)
            $returnInfo = $this->loginAstrict($user['id'], $app->get_client_ip());
            if ($returnInfo['blocking'] == 1) {
                $this->ajaxReturn(array("data" => "", "info" => $returnInfo['info'], "status" => 0));
            }
        }
    }

    private function loginAstrict($uid,$ip)
    {
        //获取两个小时内登录错误次数
        $timeStart = date("Y-m-d H:i:s", time() - 2 * 3600);
        $info = D("Loguser")->getLogList($uid, $timeStart, date('Y-m-d H:i:s'),2);
        $count = count($info);
        $info = ['status' => 1, 'info' => '']; //错误提示信息
        if ($count > 1) {
            //当登录错误次数达到3次时，增加验证码验证，同时提示“密码错误，您还可以尝试2次，点此找回密码”
            if ($count == 3) {
                $info = ['status' => 0, 'blocking' => 0, 'info' => '账户名/密码错误，您还可以尝试2次，<a href="http://u.' . C('QZ_YUMING') . '/getpassword/">点此找回密码</a>'];
                S('User:Login:astrict:blocking:' . $ip, 0, 600); //冻结
            }
            if ($count == 4) {
                $info = ['status' => 0, 'blocking' => 0, 'info' => '账户名/密码错误，您还可以尝试1次，<a href="http://u.' . C('QZ_YUMING') . '/getpassword/">点此找回密码</a>'];
                S('User:Login:astrict:blocking:' . $ip, 0, 600); //冻结
            }
            if ($count >= 5) {
                if (S('User:Login:astrict:blocking:' . $ip) != 1){   //冻结后时间不再变化
                    $relieveblocking = date("H:i",(time()+ 660)); //解禁时间
                    S('User:Login:astrict:blocking:' . $ip, 1, 600);     //冻结
                    S('User:Login:astrict:relieveblocking:' . $ip, $relieveblocking, 600);  //解禁时间写入缓存
                }
                $relieveblocking = S('User:Login:astrict:relieveblocking:' . $ip) == false ? date("H:i",(time()+ 660)) : S('User:Login:astrict:relieveblocking:' . $ip);
                $info = ['status' => 0, 'blocking' => 1, 'info' => '密码错误已超过5次，预计' . $relieveblocking . '后可进行登录'];
            }
        }else{
            S('User:Login:astrict:blocking:' . $ip, null);
        }
        $this->assign('blocking', S('User:Login:astrict:blocking:' . $ip));
        return $info;
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
            $smsdata['tpl'] = '【齐装网】您的帐号于'.date('Y-m-d H:i').'在'.$city[0]['province'].$cityInfo['cname'].'登录，如非本公司亲自操作，请立即修改密码。';
            sendSmsQz($smsdata);
        }
    }

    /**
     * 短信验证码登录
     */
    public function smsLoginIn()
    {
        $data = $_POST;
        if (empty($data)){
            $this->ajaxReturn(array("data" => "", "info" => "请求失败了,请刷新页面！", "status" => 0));
        }

        $user = D("Common/User")->findUserInfoByUser($data['name']);

        if (date("Y-m-d") < "2020-11-20" && (count($user) == 0 || count($user) > 0 && $user["classid"] == 2)) {
            //老的设计师帐号
            //判断是否是电话号码登录
            import('Library.Org.Util.App');
            $app = new \App();
            $result = $app->validate_mobile($data["tel"]);
            if ($result) {
                //如果满足电话号码条件
                //查询这个号码是否注册再新的装修公司员工表中
                $logic = new UserCompanyLogic();
                $newInfo = $logic->getEmployeeInfo($data["tel"]);
                if (count($newInfo) > 0) {
                    //登录成功
                    $token = JwtTokenLogicModel::approvalSubAccountToken($newInfo["employee_id"]);
                    $data =[
                        "token" => $token,
                        "module" => 2 //1 老后台 2.新后台
                    ];
                    $this->addLog(['name' => $newInfo['name'], 'id' => $newInfo['id'],"employee_id" => $newInfo["employee_id"]], '手机验证码登陆成功', 2);
                    $this->ajaxReturn(array("data" => $data,  "status" => 1));
                }
                $this->addLog(['name' => $newInfo['name'], 'id' => $newInfo['id'],"employee_id" => $newInfo["employee_id"]], '手机验证码登陆失败', 2);
            }
        }

        $user = D("Common/User")->findUserInfoByUserTelSafe($data['tel']);
        if( empty($user) || $user['classid'] == 1){
            $this->ajaxReturn(array('data' =>[], 'info' => '用户不存在 ', 'status' => 0));
        }

        if (date("Y-m-d") >= "2020-11-20" && $user['fake'] !=  1){
            $this->ajaxReturn(array('data' =>[], 'info' => '登录入口已关闭', 'status' => 1000100));
        }

        $smsCode = $data['code'];
        //验证手机验证码
        $checkCodeResult = $this->smsCheck($smsCode, $user['tel_safe']);
        if ($checkCodeResult !== true) {
            $this->ajaxReturn($checkCodeResult);
            die();
        }
        if (!empty($user)) {
            switch ($user["classid"]) {
                case '3':
                case '6':
                    //企业用户
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
                        "qc" => $user["qc"],
                        "jc" => $user["jc"],
                        "on" => $user["on"],
                        'blocked' => $user['blocked']
                    );
                    //新签返token
                    $_SESSION["u_userInfo"]["company_token"] = JwtTokenLogicModel::approvalToken();
                    break;
                case "1":
                case "2":
                    //普通用户
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
                        'blocked' => $user['blocked']
                    );
                    break;
                case "4":
                    //cpa用户
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
                        "qc" => $user["qc"],
                        "jc" => $user["jc"],
                        "on" => $user["on"],
                        'blocked' => $user['blocked']
                    );
                    break;
            }
            D('User')->edtiUserInfo($user["id"], array("login_time" => time()));
            //记录日志
            $this->addLog(['name' => $user['user'], 'id' => $user['id']]);
            //区分新签返会员跳转新后台

            //真会员跳转CP后台，假会员跳U后台
            if($user["classid"] == 3 && $user["fake"] == 0){
                $returnData = [
                    "class_id" => $user["classid"],
                    "module" => 2,
                    "token" => $_SESSION["u_userInfo"]["company_token"],
                ];
            }
            $this->ajaxReturn(array("data" => $returnData, "info" => "登录成功", "status" => 1));
        } else {
            //记录日志
            $this->addLog(['name' => $user['user'], 'id' => $user['id']], '手机号码未注册/绑定', 2);
            $this->ajaxReturn(array("data" => "", "info" => "登录失败了", "status" => 0));
        }
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
                //验证通过给一个session对象用于回调方法的验证
                $_SESSION["isverify"] = 1;
                setcookie("w_ordersafecode",null,time()-1,'/', '.'.APP_HTTP_DOMAIN);
                return true;
            }else{
                return array("data"=>"","info"=>"亲,验证码失效了！","status"=>0);
            }
        }else{
            return array("data"=>"","info"=>"亲,验证码不正确！","status"=>0);
        }
    }
}