<?php

//装修公司活动管理

namespace Home\Controller;

use app\common\model\logic\OrdersLogic;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\OrdersLogicModel;

class CityOrdersController extends HomeBaseController
{
    //首页
    public function index()
    {
        //获取城市信息
        $result = R("Api/getAllCity");
        $city = [];
        foreach ($result as $key => $value) {
            $city[$value['cid']]['cid'] = $value["cid"];
            $city[$value['cid']]['cname'] = $value["cname"];
        }

        $ordersLogic = new OrdersLogicModel();
        //渠道来源组
        $group = D('Home/Logic/OrderSourceLogic')->getSourceGroupList();
        //渠道来源
        $src = D('Home/Logic/OrderSourceLogic')->getSrcByGroup(I('get.'));

        //获取订单数据条件
        $map = $ordersLogic->getCityOrdersMap(I('get.'));

        //返回错误信息
        if($map['error']){
            $this->error($map['error']);
        }
        //获取订单个数
        $count = $ordersLogic->getCityOrdersCount($map['map']);

        //获取订单详情
        $info = $ordersLogic->getCityOrdersList($map, $count , $map['page']);
        
        //获取统计数据
        $statistics = $ordersLogic->getCityOrdersAllList($map);
        //柱状图数据
        $graph = $ordersLogic->getCityOrdersAllList($map,'groupid');
        $this->assign('group', $group);
        $this->assign('src', $src);
        $this->assign('statistics', $statistics[0]);
        $this->assign('graph', json_encode($graph));
        $this->assign('city', $city);
        $this->assign('list', $info['list']);
        $this->assign('page', $info['page']);
        $this->display();
    }

    //详情页
    public function detail()
    {
        //获取时间范围所有订单
        $map = D('Home/Logic/OrdersLogic')->getOrdersDetailMap(I('get.'));
        $info = D('Home/Logic/OrdersLogic')->getOrdersDetailList($map);
        //获取每个订单的通话信息
        $info = D('Home/Logic/LogTelcenterOrdercallLogic')->getOrderCallCountDetailByOrderIds($info);
        $this->assign('list', $info);
        $this->display();
    }

    //详情页
    public function ajaxDetail()
    {
        //获取时间范围所有订单
        $map = D('Home/Logic/OrdersLogic')->getOrdersDetailMap(I('get.'));
        $info = D('Home/Logic/OrdersLogic')->getOrdersDetailList($map);
        //获取每个订单的通话信息
        $info = D('Home/Logic/LogTelcenterOrdercallLogic')->getOrderCallCountDetailByOrderIds($info);
        $this->ajaxReturn(['status' => 1, 'info' => $info]);
    }

    //根据渠道组取渠道
    public function ajaxSrcList()
    {
        $list = D('Home/Logic/OrderSourceLogic')->getSrcByGroup(I('get.'));
        $this->ajaxReturn(['status' => 1, 'info' => $list]);
    }

}