<?php

//抢单池

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\RobOrdersLogicModel;

class RoborderController extends HomeBaseController
{

    public function _initialize(){
        parent::_initialize();
    }

    //首页
    public function index()
    {
        //获取城市信息
        $city = D('Quyu')->getQuyuList();
        $status = array(
            "1" => "分单",
            "2" => "赠单"
        );
        $robLogic = new RobOrdersLogicModel();
        $list = $robLogic->getRobOrderInfo(I('get.'));
        $this->assign("status",$status);
        $this->assign('city', $city);
        $this->assign('info', $list);
        $this->display();
    }

    public function detail()
    {
        $id = I('post.id');
        if (empty($id)) {
            $this->ajaxReturn(['code' => 404, 'errmsg' => '未查询到该订单']);
        }
        //获取订单信息
        $order = D("Orders")->getOrderInfoById($id);
        //获取分配/抢单信息
        $robLogic = new RobOrdersLogicModel();
        $info = $robLogic->getOrderPoolDetail($id);
        if ($order) {
            //格式化显示时间
            $order['time'] = date('Y-m-d H:i:s', $order['time']);
            $this->ajaxReturn(['code' => 200, 'info' => $order, 'fen_company' => $info]);
        } else {
            $this->ajaxReturn(['code' => 404, 'errmsg' => '未查询到该订单']);
        }
    }
}