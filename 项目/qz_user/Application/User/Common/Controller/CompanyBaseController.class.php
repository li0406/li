<?php
namespace User\Common\Controller;

class CompanyBaseController extends UserCenterBaseController{
    //装修公司基础信息
    var $baseInfo = null;
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

        if(!in_array($_SESSION["u_userInfo"]["classid"],array(3,6)) ){
            if(IS_AJAX){
                $this->ajaxReturn(array("data"=>"","info"=>"无效的请求,请返回用户中心首页","status"=>0));
            }else{
                header("LOCATION:".C('QZ_YUMING_SCHEME')."://u.qizuang.com/home/");
            }
            die();
        }

        $this->baseInfo = $this->getCompanyInfoByAdmin(session("u_userInfo.id"),session("u_userInfo.cs"));
        $this->baseInfo["unreadsystem"] = $this->getUnSystemNoticeCount(session("u_userInfo.id"));
        $this->baseInfo['deliver_area'] = D("User")->getCompanyDeliverAreaid(session("u_userInfo.id"), 'area_id', true);
    }


    /**
     * 获取装修公司基本信息
     * @param  [type] $comid [description]
     * @param  [type] $cs    [description]
     * @return [type]        [description]
     */
    private function getCompanyInfoByAdmin($comid,$cs){
        $user =  D("User")->getCompanyInfoByAdmin($comid,$cs);
        return $user;
    }

    /**
     * 获取用户的未读信息
     * @param  [type] $comid [公司编号]
     * @param  [type] $cs    [所在城市]
     * @return [type]        [description]
     */
    private function getUnSystemNoticeCount($id){
        $result =  D("Usersystemnotice")->getUnSystemNoticeCount($id);
        return $result;
    }

}