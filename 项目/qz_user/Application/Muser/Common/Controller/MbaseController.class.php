<?php
namespace Muser\Common\Controller;
use Think\Controller;
class MbaseController extends Controller{
    public function _initialize(){
        if(!session("?u_userInfo")){
            if(IS_AJAX){
                $this->ajaxReturn(array("data"=>"","info"=>"您的登录已超时,请重新登录","status"=>0));
            }else{
                header("Location: /");
            }
            die();
        }


        if(!in_array(session("u_userInfo.classid"),array(3,6))){
            if(IS_AJAX){
                 $this->ajaxReturn(array("data"=>"","info"=>"无效的请求,请返回用户中心首页","status"=>0));
            }else{
                header("Location: /home/");
            }
            die();
        }
    }

        //空操作
    public function _empty() {
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        $this->display('Public:404');
        die();
    }

    /**
     * [_error description]
     * @return [type] [description]
     */
    public function _error(){
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        $this->assign("headerTmp",$this->fetch($t));
        $this->display('Public:404');
        die();
    }
}