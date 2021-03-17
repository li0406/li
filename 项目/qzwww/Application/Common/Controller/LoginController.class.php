<?php
namespace Common\Controller;
use Think\Controller;
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
    public function loginin(){
        $data = $_POST;
        // if($_SESSION["safecode"] == authcode($data["safecode"])){
            $info = array(
                    "user"=>strtolower(trim($data["name"])),
                    "pass"=>trim($data["password"])
                          );
            $model = D("Common/User");
            if($model->create($info,5)){
                $user = $model->findUserInfoByUser($info["user"]);
                if(count($user)>0){
                    if($user["pass"] == md5($info["pass"])){
                      switch ($user["classid"]) {
                        case '3':
                          //企业用户
                          $_SESSION["u_userInfo"] = array(
                                                "id"=>$user["id"],
                                                "name"=>$user["name"],
                                                "user"=>$user["user"],
                                                "cs"=>$user["cs"],
                                                "qx"=>$user["qx"],
                                                "logo"=>$user["logo"],
                                                "classid"=>$user["classid"],
                                                "bm"=>$user["bm"],
                                                "cname"=>$user["cname"],
                                                "qc"=>$user["qc"],
                                                "jc"=>$user["jc"],
                                                "on"=>$user["on"]
                                                      );
                          break;
                        case "1":
                        case "2":
                            //普通用户
                            $_SESSION["u_userInfo"] = array(
                                            "id"=>$user["id"],
                                            "name"=>$user["name"],
                                            "user"=>$user["user"],
                                            "cs"=>$user["cs"],
                                            "qx"=>$user["qx"],
                                            "logo"=>$user["logo"],
                                            "classid"=>$user["classid"],
                                            "bm"=>$user["bm"],
                                            "cname"=>$user["cname"],
                                            "jc"=>$user["name"],
                                            "on"=>$user["on"]
                                                  );

                          break;
                      }

                        //导入扩展文件
                        import('Library.Org.Util.App');
                        $app = new \App();
                        //记录日志
                        $data = array(
                              "username"=>$user["name"],
                              "userid"=>$user["id"],
                              "ip"=>$app->get_client_ip(),
                              "user_agent"=>$_SERVER["HTTP_USER_AGENT"],
                              "info"=>"用户登录成功",
                              "time"=>date("Y-m-d H:i:s"),
                              "action"=>CONTROLLER_NAME."/".ACTION_NAME
                              );
                        D("Loguser")->addLog($data);
                        D('User')->edtiUserInfo($user["id"],array("login_time"=> time()));
                        $this->ajaxReturn(array("data"=>"","info"=>"登录成功" ,"status"=>1));
                    }else{
                        $this->ajaxReturn(array("data"=>"","info"=>"用户帐号/密码错误" ,"status"=>0));
                    }
                }else{
                    $this->ajaxReturn(array("data"=>"","info"=>"用户帐号/密码错误" ,"status"=>0));
                }
            }else{
                 $this->ajaxReturn(array("data"=>"","info"=>$model->getError(),"status"=>0));
            }
        // }
        $this->ajaxReturn(array("data"=>"","info"=>"页面请求失败了,请刷新页面！","status"=>0));
    }

    //手机号登录
    public function mobileLogin(){
        $data = $_POST;

        //验证手机验证码
        if(empty($data["code"])){
            echo json_encode(array("data"=>"","info"=>"亲,验证码不正确！","status"=>0));
            die();
        }

        $w_ordersafecode = $_COOKIE["w_ordersafecode"];
        if(empty($w_ordersafecode)){
            echo json_encode(array("data"=>"","info"=>"验证码已失效，请重新获取","status"=>0));
            die();
        }

        $arr = unserialize($w_ordersafecode);
        $tel = $data["tel"];
        if($tel != $arr["tel"]){
            echo json_encode(array("data"=>"","info"=>"请输入正确的手机号","status"=>0));
            die();
        }
        if(strtolower($data["code"]) != authcode($arr["code"])){
            echo json_encode(array("data"=>"","info"=>"亲,您输入的验证码不正确","status"=>0));
            die();
        }

        setcookie("w_ordersafecode",$serialize,time()-1,'/', '.'.APP_HTTP_DOMAIN);

        $model = D("Common/User");
        $user = $model->findUserInfoByTel($info["user"]);
        //手机号存在 自动登录
        if(count($user)>0){
            if($user["classid"] == '3'){
                $_SESSION["u_userInfo"] = array(
                    "id"=>$user["id"],
                    "name"=>$user["name"],
                    "user"=>$user["user"],
                    "cs"=>$user["cs"],
                    "qx"=>$user["qx"],
                    "logo"=>$user["logo"],
                    "classid"=>$user["classid"],
                    "bm"=>$user["bm"],
                    "cname"=>$user["cname"],
                    "qc"=>$user["qc"],
                    "jc"=>$user["jc"],
                    "on"=>$user["on"]
                );
            }else{
                $_SESSION["u_userInfo"] = array(
                    "id"=>$user["id"],
                    "name"=>$user["name"],
                    "user"=>$user["user"],
                    "cs"=>$user["cs"],
                    "qx"=>$user["qx"],
                    "logo"=>$user["logo"],
                    "classid"=>$user["classid"],
                    "bm"=>$user["bm"],
                    "cname"=>$user["cname"],
                    "jc"=>$user["name"],
                    "on"=>$user["on"]
                );
            }
            //兼容老版的SESSION
            //老版session ubm里面有id cs bm cname 等几个键
            $_SESSION['ubm']      = array('id' =>$user['id'],'cs' =>$user['cs'],'bm' =>$user['bm'],'cname' =>$user['cname']);
            $_SESSION['id']       =     $user['id'];
            $_SESSION['classid']  =     $user['classid'];
            $_SESSION['name']     =     $user['name'];
            $_SESSION['user_jc']  =     $user['jc'];
            $_SESSION['time']     =     $user['login_time'];
            $_SESSION['admin']    =     $user['admin'];
            $_SESSION['logo']     =     $user['logo'];

            //导入扩展文件
            import('Library.Org.Util.App');
            $app = new \App();
            //记录日志
            $data = array(
                  "username"=>$user["name"],
                  "userid"=>$user["id"],
                  "ip"=>$app->get_client_ip(),
                  "user_agent"=>$_SERVER["HTTP_USER_AGENT"],
                  "info"=>"用户登录成功",
                  "time"=>date("Y-m-d H:i:s"),
                  "action"=>CONTROLLER_NAME."/".ACTION_NAME
            );
            D("Loguser")->addLog($data);
            $this->ajaxReturn(array("data"=>"","info"=>"登录成功" ,"status"=>1));
        }else{
            //手机号不存在，自动注册业主帐号

            $data["pass"] = '12345678';
            $data["user"] = 'm_'.$data["tel"];
            $data["name"] = '齐装网网友';
            $data["tel_safe"] = $data["tel"];
            $data["tel_safe_chk"] = 1;
            $data["tel"] = $data["tel"];
            $data["logo"] = "https://".C("QINIU_DOMAIN")."/".OP("DEFAULT_LOGO");
            $data["account_chk"] = 1;
            $data["classid"] = '2';

            $data["register_time"] = time();
            $data["ip"] = get_client_ip('0',true);
            $data["login_time"] = time();

            $user = D("User");

            $result = $user->addUser($data);
            if($result !== false){
                $user->commit();
                $_SESSION["u_userInfo"] = array(
                    "id"      =>  $result,
                    "name"    =>  $data["name"],
                    "user"    =>  $data["user"],
                    "classid" =>  '2',
                    "logo"    =>  $data["logo"],
                );
                $this->ajaxReturn(array("data"=>"","info"=>'注册成功',"status"=>1));
            }

        }
        $this->ajaxReturn(array("data"=>"","info"=>"页面请求失败了,请刷新页面！","status"=>0));
    }


    //退出操作
    public function loginout(){
        $schemeHost = getSchemeAndHost();
        $bm = $_SESSION["u_userInfo"]["bm"];
        unset($_SESSION["u_userInfo"]);
        setcookie("center_password_token",1,time()-3600,'/', '.'.APP_HTTP_DOMAIN);
        if(empty($bm)){
           $url = $schemeHost["scheme_full"].C("QZ_YUMINGWWW");
        }else{
           $url = $schemeHost["scheme_full"].$bm.".".C("QZ_YUMING");
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
}
