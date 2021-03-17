<?php
namespace User\Common\Controller;
use User\Common\Controller\UserCenterBaseController;
class DesignerBaseController extends UserCenterBaseController{
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

        if($_SESSION["u_userInfo"]["classid"] != 2){
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
           // $this->assign("citys", json_encode($citys));
            $citytmp = $this->fetch("Home/citytmp");
            $this->assign("citytmp",$citytmp);
        }

        $this->baseInfo = $this->getDesingerInfoByAdmin($_SESSION["u_userInfo"]["id"],$_SESSION["u_userInfo"]["cs"]);
        //用户未读系统消息
        $count = $this->getUnSystemNoticeCount($_SESSION["u_userInfo"]["id"],$_SESSION["u_userInfo"]["cs"]);
        $this->baseInfo["unreadsystem"] = $count;

        //查询该设计师是否已入住装修公司,没入住的话显示用户未查看装修公司邀请信息数量
        $team = D("Team")->getUserTeamInfo($_SESSION["u_userInfo"]["id"]);
        $this->baseInfo["unreadinvite"] = 0;
        if(count($team) <= 0){
            $this->baseInfo["unreadinvite"] =  D("Team")->getInviteCompanyCount($_SESSION["u_userInfo"]["id"]);
        }
        $keys["title"] = "用户中心";
        $keys["description"] = "用户中心";
        $keys["keywords"] = "用户中心";
        $this->assign("static_host","");
        $this->assign("keys",$keys);
        $this->assign("title","齐装网");
    }

    /**
     * 获取用户的信息
     * @return [type] [description]
     */
    public function getDesingerInfoByAdmin($id,$cs){
        $user = D("User")->getDesingerInfoByAdmin($id,$cs,2);
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