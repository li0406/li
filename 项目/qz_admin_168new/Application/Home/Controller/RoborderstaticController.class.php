<?php

//抢单池统计

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\RobOrdersLogicModel;

class RoborderstaticController extends HomeBaseController
{

    public function _initialize(){
        parent::_initialize();
    }

    //首页
    public function index()
    {
        $admin = getAdminUser();
        $robLogic = new RobOrdersLogicModel();
        //客服组
        $group = D("Adminuser")->getKfGroupInfo();
        //获取所有分站
        $city = D("Quyu")->getAllQuyuOnly();
        //归属人
        $operaters = D('Adminuser')->getKfManagerListByIdAndUid($admin['id'], $admin['uid'], $admin['cs_help_user']);
        //列表
        $list = $robLogic->getRobOrderList(I('get.'));
        $this->assign('list',$list['list']);
        $this->assign('page',$list['page']);
        $this->assign('group',$group);
        $this->assign("citys",$city);
        $this->assign('operate',$operaters['list']);
        $this->display();
    }
}