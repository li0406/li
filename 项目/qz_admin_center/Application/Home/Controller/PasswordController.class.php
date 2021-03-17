<?php
namespace Home\Controller;
use Think\Controller;
class PasswordController extends Controller{
    public function index(){
        $step = 1;
        $forget_step = session("uc_forgetpwd_step");
        if(!empty($forget_step)){
            $step =  $forget_step;
            session("uc_forgetpwd_step",null);
        }
        switch ($step) {
            case '2':
                //获取用户的密码问题
                $userInfo = session("uc_forgetpwd_userInfo");
                $result = D("Adminuserquestion")->getUserQuestion($userInfo["id"]);
                if(count($result) > 0){
                    for ($i=1; $i <= 3 ; $i++) {
                        $questions[] = array(
                            "question"=>$result["question".$i],
                            "answer"=>$result["answer".$i]
                                             );
                    }
                    shuffle($questions);
                    session("uc_forgetpwd_questions",$questions);
                    $this->assign("questions",$questions);
                }
                $this->assign("userinfo",$userInfo);
                $this->display("step2");
                break;
            case '3':
                $this->display("step3");
                break;
            default:
                //获取验证模板
                $t =T('Common@verify/verify');
                $tmp = $this->fetch($t);
                $this->assign("verify",$tmp);
                session("uc_forgetpwd_userInfo",null);
                $this->display();
            break;
        }

    }

    public function step(){
        if($_POST){
            $step = I("post.step");
            $status = 0;
            switch ($step) {
                case '1':
                    $name = I("post.name");
                    $model = D("Adminuser");
                    //验证码
                    $code = I("post.verify");
                    if(!check_verify($code)){
                        $this->ajaxReturn(array("data"=>"","info"=>"验证码填写错误","status"=>0));
                    }
                    $result = $model->findUserInfo($name);
                    if(count($result) > 0){
                        $status = 1;
                        session("uc_forgetpwd_step",2);
                        session("uc_forgetpwd_userInfo",$result);
                    }else{
                        $errMsg = "该账号不存在,请重新输入！";
                    }
                    $this->ajaxReturn(array("data"=>"","info"=>$errMsg,"status"=>$status));
                    break;
                case '2':
                    $userInfo = session("uc_forgetpwd_userInfo");
                    if(!empty($userInfo["safe_tel"])){
                        $code = I("post.code");
                        $tel = $userInfo["safe_tel"];
                        $result = R("Common/Sms/verifysmscode",array($code,$tel));
                        if(!$result["status"]){
                            $this->ajaxReturn(array("data"=>"","info"=>$result["errMsg"],"status"=>$status));
                        }
                        $status = 1;
                        session("uc_forgetpwd_step",3);
                        session("uc_confirm_step",1);
                    }else{
                        //获取密保的问题
                        $questions = session("uc_forgetpwd_questions");
                        //验证密保的问题答案是否正确
                        $count = 0;
                        $index = 0;
                        foreach ($questions as $key => $value) {
                            if($value["answer"] != md5(md5(I("post.answer".($key+1))))){
                                $index = $key+1;
                                break;
                            }
                        }
                        if($index == 0){
                            $status = 1;
                            session("uc_forgetpwd_questions",null);
                            session("uc_forgetpwd_step",3);
                            session("uc_confirm_step",1);
                        }else{
                            $num = array("1"=>"一","2"=>"二","3"=>"三");
                            $errMsg = "密保答案".$num[$index]."的答案错误！";
                        }
                    }
                    $this->ajaxReturn(array("data"=>"","info"=>$errMsg,"status"=>$status));
                    break;
                case '3':
                    $status = 0;
                    $errMsg = "非法操作！";
                    if(session("?uc_confirm_step")){
                        import('Library.Org.Util.App');
                        $app    = new \App();
                        $userInfo = session("uc_forgetpwd_userInfo");
                        $data = array(
                            "pass"=>I("post.password"),
                            "confirmpassword"=>I("post.confirmpassword")
                                      );

                        $model = D("Adminuser");
                        if($model->create($data,5)){
                            $data = array(
                                "pass"=>md5(trim($data["pass"]))
                                          );
                            $i = $model->editUserInfo($userInfo["id"],$data);
                            if($i !== false){
                                $status = 1;
                                $errMsg = "操作成功";
                                session("uc_forgetpwd_userInfo",null);
                                session("uc_confirm_step",null);
                                $logData = array(
                                        "logtype"=>"editadmin",
                                        "time"=>date("Y-m-d H:i:s"),
                                        "username"=>$userInfo["name"],
                                        "userid"=>$userInfo["id"],
                                        "action"=>CONTROLLER_NAME."/".ACTION_NAME,
                                        "ip"=>$app->get_client_ip(),
                                        "info"=>"用户【".$userInfo["id"].$userInfo["name"]."】 重置了登录密码",
                                        "user_agent"=>$_SERVER["HTTP_USER_AGENT"],
                                        "remark"=>"编辑用户:".$userInfo["id"]
                                                 );
                                D("Logadmin")->addLog($logData);

                            }else{
                                $errMsg = "操作失败,请联系技术部门！";
                            }
                        }else{
                            $errMsg = $model->getError();
                        }
                    }
                    $this->ajaxReturn(array("data"=>"","info"=>$errMsg,"status"=>$status));
                    break;
                default:
                    die();
                break;
            }
        }
    }

    /**
     * 重置成功页面
     * @return [type] [description]
     */
    public function successpwd(){
        $this->display();
    }

    /**
     * 重置成功页面
     * @return [type] [description]
     */
    public function getpassaccount(){
        R("Common/Sms/getpassaccount");
    }

}