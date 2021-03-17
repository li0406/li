<?php
namespace Home\Controller;
use Library\Qizuang\JWTAuth\Src\Facade\Auth;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        if(session("?islogincheck")){
            //获取验证模板
            $t =T('Common@verify/verify');
            $tmp = $this->fetch($t);
            $this->assign("verify",$tmp);
        }

        $this->display();
    }

    public function login(){
        if($_POST){
            //导入扩展文件
            import('Library.Org.Util.App');
            $app = new \App();

            $name = trim(I("post.name"));
            $pwd = trim(I("post.pwd"));
            $model = D("Adminuser");
            $data = array(
                        "user"=> $name,
                        "pass"=>$pwd
            );

            if(session("?islogincheck")){
                $code = I("post.verify");
                if(!check_verify($code)){
                    $this->ajaxReturn(array("data"=>"","info"=>"验证码填写错误","status"=>0));
                }
            }
            $status = 0;//登录状态
            $ischeck = false;//验证是否需要出现验证码
            if($model->create($data,1)){
                $result         = $model->findUserInfo($name);
                $onlyadmin      = D('Options')->getOptionNameCC('onlyadmin'); //获取仅超管登录控制开关
                //特殊例外的账号 op为onlyadmin_and_with_otherthings
                $otheradmin     = D('Options')->getOptionNameCC('onlyadmin_and_with_otherthings');
                $otheradminids    = explode(',', $otheradmin['option_value']); //特殊账号ID数组
                $onlyadminvalue = $onlyadmin['option_value']; //取值
                if(count($result) > 0){
                    if(md5($pwd) == $result["pass"]){
                        $topdept = $model->getTopDepartment($result["department_id"]);
                        
                        //只允许超管登录，包含超管之外的特殊账号
                        if ( 1 == $onlyadminvalue && 1 != $result["uid"] && !in_array($result['id'], $otheradminids)) {
                            $errMsg = "现在后台为不允许登录状态!!!";
                            $this->ajaxReturn(array("data"=>'',"info"=>$errMsg,"status"=>3));
                        }
                        $userInfo = array(
                            "id"=>$result["id"],
                            "name"=>$result["name"],
                            "time_login"=>$result["time_login"],
                            "qq_work1"=>$result["qq_work1"],
                            "tel_work1"=>$result["tel_work1"],
                            "safe_tel"=>$result["safe_tel"],
                            "logo"=>$result["logo"],
                            "role_name"=>$result["role_name"],
                            "department"=>$result["department"],
                            "department_id"=>$result["department_id"],
                            "department_top_id"=>$topdept["top_id"],
                            "ip"=>$result["ip"],
                            "uid"=>$result["uid"],
                            "user"=>$result["user"],
                            "level"=>$result["level"],
                            "groups"=>array_filter(explode(",",$result["groups"])),
                            "cs"=>$result["cs"],
                            "css"=>$result["css"],
                            "cs_help"=>$result["cs_help"],
                            "cs_help_user"=>$result["cs_help_user"],
                            "showmenu" => true,
                            "kfgroup" => $result["kfgroup"]
                        );

                        $status = 1;
                        $ischeck = false;
                        session("uc_userinfo",$userInfo);
                        //添加token
                        $token = $this->approvalToken(true);
                        session('uc_userinfo.sales_token', $token);
                        session("islogincheck",null);

                        //修改登录的IP地址
                        $data = array(
                                "ip"=>$app->get_client_ip()
                                     );
                        $model->editUserInfo($result["id"],$data);
                    }else{
                        $status = 3;
                    }
                }else{
                    //不存在的用户直接判断为恶意登录，需要出现验证码
                    $ischeck = true;
                    $status = 3;
                }
                $errMsg = "账号/密码错误!";

                if($status != 1){
                    //如果登录出错，判断前2次的登录状态是否成功，3次以上出现验证码
                    $count = D("Adminlogging")->getFailLogin($name);
                    if($count["count"] == 2){
                        $ischeck = true;
                        session("islogincheck",1);
                    }
                }

                if (!empty($result["id"])) {
                    //记录登录日志到logging
                    $cs = implode(",", array_unique(array_filter(explode(",",$result['cs'].",".$result['css']))));
                    $log = array(
                        "uid" => $result["id"],
                        "username"=>$name,
                        "ip"=>$app->get_client_ip(),
                        "user_agent"=>$_SERVER['HTTP_USER_AGENT'],
                        "time"=>time(),
                        "status"=>$status,
                        "cs" =>$cs
                                 );
                    D("Adminlogging")->addLog($log);
                }

            }else{
                $errMsg = $model->getError();
            }

            //如果登录失败次数过多，需要出现验证码
            if($ischeck){
                //获取验证模板
                $t =T('Common@verify/verify');
                $tmp = $this->fetch($t);
            }
            $this->ajaxReturn(array("data"=>$tmp,"info"=>$errMsg,"status"=>$status));
        }
        $this->error();
    }

    /**
     * 退出登录
     * @return [type] [description]
     */
    public function loginout(){
        session("uc_userinfo",null);
        header("location:".C("UC_URL"));
    }

    /**
     * 验证码路由
     */
    public function Verify(){
        ob_clean();
        getVerify('',4,120,30);

    }

    /**
     * 发送短信
     * @return [type] [description]
     */
    public function sms(){
        R("Common/Sms/sendsms");
    }


    //下发去装修说后台的token
    public function approvalToken($diretc_use = false)
    {
        $user = session('uc_userinfo');
        if(empty($user)){
            return ['error_code' => 4000005, 'error_msg' =>'未获得后台授权'];
        }
        $user_profile = $token_data = [
            'nick_name' => $user['name'],
            'user_name' => $user['user'],
            'role_name' => $user['role_name'],
            'logo' => $user['logo'],
            'user_id' => $user['id'],
        ];
        $token_data['token_type'] = 2;
        $token_data['role_id'] = $user['uid'];
        //下发Token
        $jwt_token = Auth::getToken($token_data);
        if($diretc_use){
            return $jwt_token;
        }
        $response = [
            'error_code' => 0,
            'data' => [
                'info' => $user_profile,
                'jwt_token' => $jwt_token
            ]
        ];
        $this->ajaxReturn($response);
    }
}