<?php
/**
 * 已暂停使用该类
 */
namespace Api\Controller;
use Api\Common\Controller\ApiBaseController;
class LoginController extends ApiBaseController {
    private $data = null;
    public function _initialize(){
         //检测请求的域名是否合法
         //合法的域名数组
        $register_url = C("REGISTER_URL");
        $referer= $_SERVER['HTTP_ORIGIN'];
        if(in_array($referer,$register_url) || preg_match('/([A-za-z])+(.[A-za-z]+)?.'.APP_HTTP_DOMAIN.'/i', $referer)){
            header("Access-Control-Allow-Credentials:true");
            header('Access-Control-Allow-Origin:*');
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
            }

            if(!empty($ssid)){
                $ssid = authcode($ssid);
                session_id($ssid);
            }
            session_start();
        }else{
            $this->ajaxReturn(array("data"=>"","info"=>"不合法的登录,请刷新页面！","status"=>0));
            die();
        }
    }

    /**
     * 请求登录窗口
     * @return [type] [description]
     */
    public function login(){
       //安全验证码
     $code = substr(md5(time()), 0, 10);
     $safecode = authcode($code,"");
     $_SESSION["safecode"] = $code;
     $this->assign("safecode",$safecode);

      $t = T("login");
      $tmp = $this->fetch($t);
      $this->ajaxReturn(array("data"=>$tmp,"info"=>"请求成功" ,"status"=>1));
    }

    /**
     * 登录操作
     * @return [type] [description]
     */
    public function loginin(){
        $data = null;
        if(empty($this->data)){
            $data = $_POST;
        }else{
            $data = $this->data;
        }
        //ie6/7/8 需要用urldecode解析
        if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 9.0") || strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 8.0") || strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 7.0") || strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 6.0")){
           $data["safecode"] = urldecode($data["safecode"]);
           $data["name"] = urldecode($data["name"]);
           $data["password"] = urldecode($data["password"]);
        }

        if($_SESSION["safecode"] == authcode($data["safecode"])){
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

                      //兼容老版的SESSION
                      //老版session ubm里面有id cs bm cname 等几个键
                      $_SESSION['ubm']      = array(
                                                    'id' =>$user['id'],
                                                    'cs' =>$user['cs'],
                                                    'bm' =>$user['bm'],
                                                    'cname' =>$user['cname']
                                                  );
                      $_SESSION['id']       =     $user['id'];
                      $_SESSION['classid']  =     $user['classid'];
                      $_SESSION['name']     =     $user['name'];
                      $_SESSION['user_jc']  =     $user['jc'];
                      $_SESSION['time']     =     $user['login_time'];
                      $_SESSION['admin']    =     $user['admin'];
                      $_SESSION['logo']     =      $user['logo'];


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
                        $this->ajaxReturn(array("data"=>"","info"=>"用户帐号/密码错误" ,"status"=>0));
                    }
                }else{
                    $this->ajaxReturn(array("data"=>"","info"=>"用户帐号/密码错误" ,"status"=>0));
                }
            }else{
                 $this->ajaxReturn(array("data"=>"","info"=>$model->getError(),"status"=>0));
            }
        }
        $this->ajaxReturn(array("data"=>"","info"=>"页面请求失败了,请刷新页面！","status"=>0));
    }

    //退出操作
    public function loginout(){
        $bm = $_SESSION["u_userInfo"]["bm"];
        unset($_SESSION["u_userInfo"]);
        if(empty($bm)){
           $url = "https://".C("QZ_YUMINGWWW");
        }else{
           $url = "https://".$bm.".".C("QZ_YUMING");
        }

        $this->ajaxReturn(array("data"=>$url,"info"=>"","status"=>1));
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