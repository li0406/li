<?php


namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\OrderBackHistoryLogicModel;

class OrderBackHistoryController extends HomeBaseController
{
    public function index()
    {
        $backLogic = new OrderBackHistoryLogicModel();
        $info = $backLogic->getBackHistory(I('get.'));

        // 跳转订单详情权限控制
        $order_priv = check_user_menu_auth("/orders");
        $this->assign("order_priv", $order_priv);

        //获取所有分站
        $city = D("Quyu")->getAllQuyuOnly();
        $this->assign("citys", $city);
        $this->assign("list", $info['list']);
        $this->assign("page", $info['page']);
        $this->assign("search", $info['search']);
        $this->assign("reason", OrderBackHistoryLogicModel::$reason);
        $this->display();
    }

    public function back_record()
    {
        $order_id = I('get.order_id');
        $backLogic = new OrderBackHistoryLogicModel();
        $info = $backLogic->getOrderBackRecordList($order_id);
        $this->ajaxReturn(['error_code' => 0, 'data' => $info]);
    }

    public function check_record()
    {
        $post = I('post.');
        if (empty($post['id']) || empty($post['status'])) {
            $this->ajaxReturn(['error_code' => 400, 'error_msg' => '操作失败!']);
        }
        $backLogic = new OrderBackHistoryLogicModel();
        $status = $backLogic->checkRecord($post, session("uc_userinfo.id"));
        if ($status) {
            $this->ajaxReturn(['error_code' => 0]);
        } else {
            $this->ajaxReturn(['error_code' => 400, 'error_msg' => '操作失败!']);
        }
    }
}