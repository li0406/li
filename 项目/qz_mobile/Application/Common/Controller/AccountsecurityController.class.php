<?php
namespace Common\Controller;
use Think\Controller;
class AccountsecurityController extends Controller{
    private $data = null;
    public function _initialize(){
         //检测请求的域名是否合法
         //合法的域名数组
        $register_url = C("REGISTER_URL");
        $referer= $_SERVER['HTTP_REFERER'];

        if(in_array($referer,$register_url) || preg_match('/([A-za-z])+(.[A-za-z]+)?.qizuang.com/i', $referer)){

        }else{
            $this->ajaxReturn(array("data"=>"","info"=>"不合法的请求,请刷新页面！","status"=>0));
            die();
        }
    }

    /**
     * 账户绑定安全手机/邮箱
     * @return [type] [description]
     */
    public function account(){
        $data = $_POST;
        if(!$data["refresh"]){
            //查询该用户的信息
            $user = D("User")->getSingleUserInfoById($_SESSION["u_userInfo"]["id"]);
            if(count($user) == 0){
                $this->ajaxReturn(array("data"=>"","info"=>"没有该用户信息","status"=>0));
            }

            $user["real_mail"] = authcode($user["mail_safe"],"");
            $user["real_tel"] = authcode($user["tel_safe"],"");
            $user["tel_safe"] =substr_replace($user["tel_safe"],"*****",3,5);
            $mail = strstr($user["mail_safe"],"@",true);
            $prefix = strstr($user["mail_safe"],"@");
            $user["mail_safe"] =substr_replace($mail,"*****",3,5).$prefix;
            $info["user"] = $user;
        }

        switch ($data["type"]) {
            case 'tel':
                //安全电话
                $tmp = "Common@Accountsecurity/telaccount";
                $msg = "更换密保手机";
                $subtitle ="绑定手机";
                $tag ="手机";
                break;
            case 'mail':
               //安全邮箱
                $tmp = "Common@Accountsecurity/telaccount";
                $msg = "更换密保邮箱";
                $subtitle ="绑定邮箱";
                $tag ="邮箱";
                break;
            default:
               break;
        }
        $info["tag"] = $tag;
        $info["subtitle"] = $subtitle;
        $info["type"] = $data["type"];
        $info["title"] = $msg;
        $info["ssid"] = authcode(session_id(),"");
        $this->assign("info",$info);
        $tmp = $this->fetch($tmp);
        $this->ajaxReturn(array("data"=>$tmp,"info"=>"","status"=>1));
        die();
    }


    /**
     * 绑定安全帐号
     * @return [type] [description]
     */
    public function bindaccount(){
        $data = $_POST;
        if($data["type"]  == "tel"){
            $data = array(
                "tel_safe_chk"=>1,
                "tel_safe"=>$data["account"]
                      );
        }elseif($data["type"] == "mail"){
            $data = array(
                "mail_safe_chk"=>1,
                "mail_safe"=>$data["account"]
                      );
        }
        $i = D("User")->edtiUserInfo($_SESSION["u_userInfo"]['id'],$data);
        if($i !== false){
            $this->ajaxReturn(array("data"=>"","info"=>"","status"=>1));
        }
        $this->ajaxReturn(array("data"=>"","info"=>"操作失败,请刷新重试！","status"=>0));
    }


}