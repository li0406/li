<?php
// +----------------------------------------------------------------------
// |  ERP营销用户
// +----------------------------------------------------------------------
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\ErpUserLogicModel;

class ErpuserController extends HomeBaseController
{
    /**
     * ERP 表单用户列表
     *
     * @return void
     */
    public function registerStat()
    {
        $model = new ErpUserLogicModel();
        $list = $model->getRegisterList();
        $this->assign('list',$list);
        $this->display();
    }
}