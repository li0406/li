<?php
/**
 * 已暂停使用该类
 */
namespace Api\Controller;
use Api\Common\Controller\ApiBaseController;
class CollectController extends ApiBaseController{
    private $data = null;
    public function _initialize(){
         //检测请求的域名是否合法
         //合法的域名数组
        $register_url = C("REGISTER_URL");
        $referer= $_SERVER['HTTP_ORIGIN'];
        if(in_array($referer,$register_url) || preg_match('/([A-za-z])+(.[A-za-z]+)?.'.APP_HTTP_DOMAIN.'/i', $referer)){
            header("Access-Control-Allow-Credentials:true");
            header('Access-Control-Allow-Origin:'.$referer);
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
            $this->ajaxReturn(array("data"=>"","info"=>"不合法的请求,请刷新页面！","status"=>0));
            die();
        }
    }

    /**
     * 添加收藏
     */
    public function setCollect(){
        $data = null;
        if(empty($this->data)){
            $data = $_POST;
        }else{
            $data = $this->data;
        }
        if(isset($_SESSION["u_userInfo"])){
            $saveData = array(
                    "classtype"=>remove_xss($data["classtype"]),
                    "classid"=>remove_xss($data["classid"]),
                    "userid"=>$_SESSION["u_userInfo"]["id"],
                    "time"=>time()
                          );
            $i = D("Usercollect")->addCollect($saveData);
            if($i !== false){
                $this->ajaxReturn(array("data"=>"","info"=>"","status"=>1));
            }
            $this->ajaxReturn(array("data"=>"","info"=>"操作失败,请刷新重新！","status"=>0));
        }
        $this->ajaxReturn(array("data"=>"","info"=>"请先登录后收藏！","status"=>0));

    }

    /**
     * 取消收藏
     * @return [type] [description]
     */
    public function cancelcollect(){
        if($_POST){
            $id = I("post.id");
            $i = D("Usercollect")->delcollect($id,$_SESSION["u_userInfo"]["id"]);
            if($i !== false){
                $this->ajaxReturn(array("data"=>"","info"=>"","status"=>1));
            }
            $this->ajaxReturn(array("data"=>"","info"=>"操作失败,请刷新重新！","status"=>0));
        }
    }
}