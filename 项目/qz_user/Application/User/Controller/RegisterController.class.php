<?php
namespace User\Controller;
use Common\Model\Logic\UserLogicModel;
use User\Common\Controller\UserCenterBaseController;

class RegisterController extends UserCenterBaseController{
    public function index(){
        if(!empty($_COOKIE["w_cityid"])){
            $bm = $_COOKIE["w_cityid"];
            $cityId = D("Common/Quyu")->getCityIdByBm($bm);
            $this->assign("cityId",$cityId);
        }
        $city =  D("Common/Area")->getCityArray("",true);
        $this->assign("citys",json_encode($city));
        $this->display();
    }
    /**
     * 从新获取注册密码
     * @return [type] [description]
     */
    public function repass(){
        if(!empty($_COOKIE["w_company_id"])){
            $id = authcode($_COOKIE["w_company_id"],"DECODE");
            //查询是否有该公司
            $company = D("User")->getSingleUserInfoById($id);
            if(count($company) > 0){
                import('Library.Org.Util.App');
                $app = new \App();
                $code = $app->getSafeCode(8,"NUMBER");
                $data["pass"] = md5($code);
                //重新修改注册公司的登录密码
                $data = array(
                        "pass"=>md5($code)
                              );
                $i = D("User")->edtiUserInfo($id,$data);
                if($i !== false){
                    $result = $this->sendSMS($company["tel_safe"],$code);
                    if($result){
                        $this->ajaxReturn(array("data"=>"","info"=>"","status"=>1));
                    }
                    $this->ajaxReturn(array("data"=>"","info"=>"发送短信失败,请重试！","status"=>0));
                }
            }
        }
        $this->ajaxReturn(array("data"=>"","info"=>"发送失败,请联系客服","status"=>0));
    }
    public function checkVerifyCode(){
        $code = I("post.code");
        if(!check_verify($code)){
            $this->ajaxReturn(array("data"=>"","info"=>"文字验证码输入错误！","status"=>0));
            die();
        }
    }
    /**
     * 注册成功页面
     * @return [type] [description]
     */
    public function registersuccess(){
        $this->display("success");
    }
    /**
     * 注册用户
     * @return [type] [description]
     */
    public function save(){
        if($_POST){
            $code = I("post.code");
            $safeCode = unserialize(cookie("w_ordersafecode"));
            if(strtolower($code) != authcode($safeCode["code"])){
                return $this->ajaxReturn(array("data"=>"","info"=>"亲,您输入的验证码不正确" ,"status"=>0));
            }
            $name = I("post.name");

            if (I("post.classid") == 2) {
                $name = I("post.tel");
            }

            //检查帐号是否注册
            $userLogic = new UserLogicModel();
            $result = $userLogic->checkAccount($name);

            if ($result > 0) {
                return $this->ajaxReturn(array("data"=>"","info"=>"该帐号已被注册!" ,"status"=>0));
            }

            //导入扩展文件
            import('Library.Org.Util.App');
            $app = new \App();
            $data = array(
                "user"          => $_POST["tel"],
                "classid"       => $_POST["classid"],
                "register_time" => time(),
                "ip"            =>$app->get_client_ip()
                          );
            import('Library.Org.Util.Fiftercontact');
            $filter = new \Fiftercontact();
            if(I("post.classid") ==  3){
                //装修公司
                $data["user"]         = trim(I("post.name","","htmlspecialchars"));
                $data["name"]         = $filter->filter_common(I("post.contact","","htmlspecialchars"),array("Sbc2Dbc","filter_script","filter_space",array("filter_sensitive_words",array(2,3,5)),"filter_link","filter_html_url"));
                $data["qc"]           = $filter->filter_common(I("post.qc","","htmlspecialchars"),array("Sbc2Dbc","filter_script","filter_space",array("filter_sensitive_words",array(2,3,5)),"filter_link","filter_html_url"));
                $data["jc"]           = $filter->filter_common(I("post.jc","","htmlspecialchars"),array("Sbc2Dbc","filter_script","filter_space",array("filter_sensitive_words",array(2,3,5)),"filter_link","filter_html_url"));
                $data["tel_safe_chk"] = 1;
                $data["cs"]           = I("post.cs","","htmlspecialchars");
                $data["qx"]           = I("post.qy","","htmlspecialchars");
                $data["tel_safe"]     = I("post.tel","","htmlspecialchars");
                $data["tel_safe_chk"] = 1;
                $data["tel"]          = I("post.tel","","htmlspecialchars");
                //默认头像
                $data["logo"] = C("QZ_YUMING_SCHEME")."://".C("QINIU_DOMAIN")."/".OP("DEFAULT_COMPANY_LOGO");
                //注册随机8位密码
                // import('Library.Org.Util.App');
                // $app = new \App();
                // $code = $app->getSafeCode(8,"NUMBER");
                // $data["pass"] = md5($code);
                $data["pass"] = md5(I("post.password"));
                //如果全称当中不包涵特定打字符，不给注册 强制过滤
                /*if(false == $this->isAllowQc($data["qc"])) {
                    $this->ajaxReturn(array("data"=>"","info"=>'注册失败！0x01',"status"=>0));
                }*/
                //短信验证成功 , 直接将手机号作为安全手机
                if($_SESSION["isverify"] == 1){
                    $data["tel_safe"] = $data["tel"];
                    $data["tel_safe_chk"] = 1;
                }
                $user = D("User");
                if($user->create($data,7)){
                    $user->startTrans();
                    $id = $user->addUser($data);
                    //自动插入装修公司详细信息
                    $saveData = array(
                            "userid"=>$id,
                            "lunxian"=>""
                                    );
                    $result = D("Usercompany")->AddCompanyDetails($saveData);
                    
                    // 添加用户账户
                    $result2 = $userLogic->addCompanyAccountInfo($id);

                    // 添加签返记录
                    $result3 = $userLogic->addCompanySignbackInfo($id);

                    if($result !== false && $result2 !== false && $result3 !== false){
                        $user->commit();
                        // $this->sendSMS($data["tel"],$code);
                        //注销验证码
                        setcookie("w_ordersafecode",$serialize,time()-1,'/', '.'.APP_HTTP_DOMAIN);
                        // //添加装修公司注册成功后的公司ID，用于重新接收密码用
                        // setcookie("w_company_id",authcode($id,""),time()+600,'/', '.'.APP_HTTP_DOMAIN);
                        //查询注册公司城市的信息
                        $cityInfo = D("Area")->getCityById($data["cs"]);

                        $data["name"] =  $filter->filter_common($data['name'],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_link","filter_html_url"));
                        //添加session
                        $_SESSION["u_userInfo"] = array(
                                                "id"      =>  $id,
                                                "name"    =>  $data["name"],
                                                "user"    =>  $data["user"],
                                                "cs"      =>  $data["cs"],
                                                "qx"      =>  $data["qy"],
                                                "logo"    =>  $data["logo"],
                                                "classid" =>  $data["classid"],
                                                "bm"      =>  $cityInfo[0]["bm"],
                                                "cname"   =>  $cityInfo[0]["oldName"],
                                                "qc"      =>  $data["qc"],
                                                "jc"      =>  $data["qc"],
                                                "on"      =>  0
                                                      );
                        $this->ajaxReturn(array("data"=>"","info"=>'注册成功',"status"=>1));
                    }else{
                        $user->rollback();
                        $this->ajaxReturn(array("data"=>"","info"=>'注册失败,请稍后再试！',"status"=>0));
                    }
                }else{
                    $this->ajaxReturn(array("data"=>"","info"=>$user->getError(),"status"=>0));
                }
            }else{
                //业主,设计师
                if(I("post.password") !== ""){
                    $data["pass"] =I("post.password","","htmlspecialchars");
                }
                $data["name"] = $filter->filter_common(trim(I("post.name","","htmlspecialchars")),array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_link","filter_html_url"));
                //传入的tel是否为电话
                if($_POST["istel"] != 2){
                    $data["tel_safe"] = $_POST["tel"];
                    $data["tel_safe_chk"] = 1;
                    $data["tel"] = I("post.tel","","htmlspecialchars");
                }else{
                    $data["mail_safe"] =  I("post.tel","","htmlspecialchars");
                    $data["mail_safe_chk"] = 1;
                    $data["mail"] =  I("post.tel","","htmlspecialchars");
                }
                //默认头像
                $data["logo"] = C("QZ_YUMING_SCHEME")."://".C("QINIU_DOMAIN")."/".OP("DEFAULT_LOGO");
                $data["account_chk"] = 1;
                $user = D("User");
                if($user->create($data,4)){
                    $user->startTrans();
                    $data["pass"] = md5($data["pass"]);
                    $result = $user->addUser($data);
                    if($result !== false){
                        $i = true;
                        if($data["classid"] == 2){
                            //如果是设计师,添加设计师详细数据
                            $saveData = array(
                                        "userid"=>$result
                                              );
                            $i = D("Userdes")->addDes($saveData);
                        }
                        if($i !== false){
                            $user->commit();
                            $_SESSION["u_userInfo"] = array(
                                                "id"      =>  $result,
                                                "name"    =>  $data["name"],
                                                "user"    =>  $data["user"],
                                                "classid" =>  $_SESSION['classid'],
                                                "cs"      =>  $data["cs"],
                                                "qy"      =>  $data["qy"],
                                                "logo"    =>  $data["logo"],
                                                "classid" =>  $data["classid"]
                                                      );
                            setcookie("w_ordersafecode",$serialize,time()-1,'/', '.'.APP_HTTP_DOMAIN);
                            $this->ajaxReturn(array("data"=>"","info"=>'注册成功',"status"=>1));
                        }
                    }else{
                        $user->rollback();
                        $this->ajaxReturn(array("data"=>"1","info"=>'注册失败,请稍后再试！',"status"=>0));
                    }
                }else{
                    $this->ajaxReturn(array("data"=>"2","info"=>$user->getError(),"status"=>0));
                }
            }
        }
    }
    /**
     * 发送短信
     * @param  [type] $tel  [发送的电话]
     * @param  [type] $code [登录密码]
     * @return [type]       [description]
     */
    private function sendSMS($tel,$code){
        //发送短信
        $smsdata['tel']      = $tel;
        $smsdata['type']     = 'password';
        $smsdata['variable'] = (array)$code;
        $result = sendSmsQz($smsdata);
        if($result["errcode"] == 0){
            return true;
        }
        return false;
    }
    /**
     * 检查字符串中是否包涵特定的字符
     * @param string $qcstr 传入字符串
     * @return bool 包涵返回true
    */
    private function isAllowQc($qcstr) {
        if (!empty($qcstr)) {
            $qcArray = array('装修','装饰','工程');
            foreach ($qcArray as $value) {
                if (false !== strstr($qcstr, $value)) {
                    return true;
                }
            }
        unset($value);
        return false;
        }
        return false;
   }
}