<?php
namespace User\Common\Controller;
use User\Common\Controller\UserCenterBaseController;
class UserBaseController extends UserCenterBaseController{
     public $baseInfo = null;
     public function _initialize(){
        parent::_initialize();
        if(!isset($_SESSION["u_userInfo"])){
            if(IS_AJAX){
                $this->ajaxReturn(array("data"=>"","info"=>"您的登录已超时,请重新登录","status"=>0));
            }else{
                header("LOCATION:".C('QZ_YUMING_SCHEME')."://u.qizuang.com");
            }
            die();
        }

        if($_SESSION["u_userInfo"]["classid"] != 1){
            if(IS_AJAX){
                $this->ajaxReturn(array("data"=>"","info"=>"无效的请求,请返回用户中心首页","status"=>0));
            }else{
                header("LOCATION:".C('QZ_YUMING_SCHEME')."://u.qizuang.com/home/");
            }
            die();
        }

        //如果城市字段为空，则先选择所在城市
        if(empty($_SESSION["u_userInfo"]["cs"])){
            //获取城市信息
            //$citys = getCityArray();
            //$this->assign("citys", json_encode($citys));
            $citytmp = $this->fetch("Home/citytmp");
            $this->assign("citytmp",$citytmp);
        }

        $this->baseInfo = $this->getUserInfoByAdmin($_SESSION["u_userInfo"]["id"],$_SESSION["u_userInfo"]["cs"]);
        $count = $this->getUnSystemNoticeCount($_SESSION["u_userInfo"]["id"],$_SESSION["u_userInfo"]["cs"]);
        $this->baseInfo["unreadsystem"] = $count;
        $keys["title"] = "用户中心";
        $keys["description"] = "用户中心";
        $keys["keywords"] = "用户中心";
        $this->assign("static_host","");
        $this->assign("keys",$keys);
        $this->assign("title","齐装网");
    }

    /**
     * 获取业主的信息
     * @return [type] [description]
     */
    public function getUserInfoByAdmin($id,$cs){
        $user = D("User")->getUserInfoByAdmin($id,$cs);
        return $user;
    }

     /**
     * 获取用户的未读信息
     * @param  [type] $comid [公司编号]
     * @param  [type] $cs    [所在城市]
     * @return [type]        [description]
     */
    private function getUnSystemNoticeCount($id){
        $count = D("Usersystemnotice")->getUnSystemNoticeCount($id);
        if(count($count)> 0){
            return $count["unreadsystem"];
        }
        return 0;
    }
}