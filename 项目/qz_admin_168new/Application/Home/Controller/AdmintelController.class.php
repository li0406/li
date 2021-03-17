<?php


namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\RbacRoleLogicModel;
use Home\Model\Logic\SelfTelLogicModel;

class AdmintelController extends HomeBaseController
{
    private $call_from = [
        1 => '销售',
        2 => '装修公司',
        3 => '业主',
        4 => '招聘',
        5 => '报到跟进',
        10 => '其他',
    ];

    public function self_tel()
    {
        //根据当前角色获取 能看的所有角色的人员
        $rbac = new RbacRoleLogicModel();
        $users = $rbac->getRoleUserByUser(session('uc_userinfo.uid'));
        //获取对应列表
        $self = new SelfTelLogicModel();
        $info = $self->getTelList(I('get.'),$users,session('uc_userinfo.uid'));
        $this->assign('call_obj', $this->call_from);
        $this->assign('list', $info['list']);
        $this->assign('page', $info['page']);
        $this->display();
    }

    public function get_tel_info()
    {
        $self = new SelfTelLogicModel();
        $data = $self->getTelDetail(I('post.call_sid'));
        $data = $self->setHollyFormatter($data);
        $this->ajaxReturn(['status' => 1, 'info' => $data]);
    }
}