<?php
namespace Home\Common\Controller;
use Common\Controller\BaseController;
class HomeBaseController extends BaseController {
    public function _initialize(){
        parent::_initialize();
        \Think\Hook::add('addAdminLog','Home\\Behaviors\\LogBehavior');
        \Think\Hook::add('addOaLog','Home\\Behaviors\\OalogBehavior');
        $nodes = $this->getmysystemmenu(session("uc_userinfo.uid"));
        $this->assign("menu",$nodes);
    }

    protected function checkmenu($menuid){
        //管理员除外
        if(session("uc_userinfo.uid") != 1){
            $nodes = S("Cache:uc:menu:".session("uc_userinfo.uid"));
            if(!in_array($menuid,$nodes)){
                if(IS_AJAX){
                    if(!strstr($_SERVER["HTTP_ACCEPT"],"text/html")){
                        $this->ajaxReturn(array("data"=>"","info"=>"您无权访问该页面/操作","status"=>0));
                    }else{
                        $this->_error();
                    }
                }else{
                    $this->_error();
                }
                die();
            }
        }
    }

    private function getmysystemmenu($uid){
        $nodes = S("Cache:uc:menu:".$uid);
        if(!$nodes){
            //获取管理权限信息
            $result = D("Rbac")->getRbacRoleByUid($uid);
            foreach ($result as $key => $value) {
                $nodes[] = $value["node_id"];
            }
            S("Cache:uc:menu:".$uid,$nodes,3600*24);
        }
        return $nodes;
    }
}