<?php

//订单类型

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\LogAdminLogicModel;
use Home\Model\Logic\OrderSourceInLogicModel;

class OrderSourceInController extends HomeBaseController
{
    public function index()
    {
        $sourceIn = new OrderSourceInLogicModel();
        $info = $sourceIn->getSourceIn();
        $this->assign('list', $info['list']);
        $this->assign('page', $info['page']);
        return $this->display();
    }

    public function getSourceInInfo()
    {
        $id = I('get.id');
        $sourceIn = new OrderSourceInLogicModel();
        $info = $sourceIn->getSourceInInfo($id);
        if (!empty($info)) {
            $this->ajaxReturn(['error_code' => 0, 'info' => $info]);
        } else {
            $this->ajaxReturn(['error_code' => 400, 'error_msg' => '未查询到数据']);
        }
    }

    public function saveSourceInInfo()
    {
        $data = I('post.');
        if (empty($data['type_name'])) {
            $this->ajaxReturn(['error_code' => 400, 'error_msg' => '请输入订单类型']);
        }
        $sourceIn = new OrderSourceInLogicModel();
        $info = $sourceIn->checkSourceIn($data);
        if ($info['error_code'] != 0) {
            $this->ajaxReturn($info);
        }
        $status = $sourceIn->saveSourceIn($data, $this->User);
        $log = new LogAdminLogicModel();
        if ($status) {
            $log->addLog('操作成功', 'edit_source_in', $data, $data['id'] ?: '');
            $this->ajaxReturn(['error_code' => 0, 'error_msg' => '操作成功']);
        } else {
            $log->addLog('操作失败', 'edit_source_in', $data, $data['id'] ?: '');
            $this->ajaxReturn(['error_code' => 400, 'error_msg' => '操作失败,请刷新后再试']);
        }
    }
}
