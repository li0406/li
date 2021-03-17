<?php

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\OrdersLogicModel;
use Home\Model\Logic\PnpLogicModel;

class PnpController extends HomeBaseController
{
    public function index()
    {
        //获取列表
        $tel = I("get.tel");
        $city = I("get.city");
        $status = I("get.status");
        $page = I("get.p",1);

        $logic = new PnpLogicModel();
        $list = $logic->getPnpList($tel,$city,$status,$page);
        $city = $logic->getDropDowCity();
        $this->assign("list",$list);
        $this->assign("city",$city);
        $this->display();
    }


    public function history()
    {
        $tel = I("get.tel");
        $order_id = I("get.order_id");
        $page = I("get.p",1);

        $logic = new PnpLogicModel();
        $list = $logic->getHistoryList($tel,$order_id,$page);
        $this->assign("list",$list);
        $this->display();
    }

    public function volice()
    {
        $order_id = I("get.order_id");
        $logic = new PnpLogicModel();
        $list = $logic->getVoliceList($order_id);

        $this->assign("list",$list);
        $this->display();
    }

    public function unbindtel()
    {
        if (IS_POST) {
            $tel = I("post.tel");
            $logic = new PnpLogicModel();
            $logic->unBindTelByTelx($tel);
            $this->ajaxReturn(["error_code"  => 0 ,"error_msg" => "操作成功"]);
        }
    }

    // AXB模式-订单录音管理
    public function axborderlist(){
        $input = I("get.");
        $limit = I("get.limit", 20);

        // 默认查询时间
        if (empty($input["begin"]) && empty($input["end"])) {
            $input["begin"] = date("Y-m-d", strtotime("-3 months"));
            $input["end"] = date("Y-m-d");
        } else if (!empty($input["begin"]) && empty($input["end"])) {
            $input["end"] = date("Y-m-t", strtotime($input["begin"]));
        } else if (empty($input["begin"]) && !empty($input["end"])) {
            $input["begin"] = date("Y-m-01", strtotime($input["end"]));
        }

        // 查询订单时去除查询时间
        if (!empty($input["order_id"])) {
            unset($input["begin"], $input["end"]);
        }

        $pnpLogic = new PnpLogicModel();
        $data = $pnpLogic->getOrderMapStatsList($input, $limit);

        // 城市下拉数据
        $citys = getUserCitys(false);

        $this->assign("citys", $citys);
        $this->assign("input", $input);
        $this->assign("data", $data);
        $this->display();
    }

    // AXB模式-订单绑定记录
    public function axborderlog(){
        $order_id = I("get.order_id");

        $pnpLogic = new PnpLogicModel();
        $data = $pnpLogic->getOrderCompanyMapList($order_id);

        $orderLoigc = new OrdersLogicModel();
        $orderdetail = $orderLoigc->getOrderDetail($order_id);

        $this->assign("glist", $data["glist"]);
        $this->assign("stats", $data["stats"]);
        $this->assign("orderdetail", $orderdetail);
        $this->display();
    }

    // AXB模式-录音记录
    public function axbvolice(){
        $sub_id = I("get.sub_id");
        $order_id = I("get.order_id");
        $company_id = I("get.company_id");

        $pnpLogic = new PnpLogicModel();
        $list = $pnpLogic->getOrderVoliceLog($order_id, $company_id, $sub_id);

        $this->assign("list", $list);
        $template = $this->fetch("axbvolice");

        $this->ajaxReturn([
            "error_code" => 0,
            "error_msg" => "请求成功",
            "data" => [
                "template" => $template
            ]
        ]);
    }

    // AXB模式-订单解绑
    public function axborderunbind(){
        $order_id = I("post.order_id");
        if (empty($order_id)) {
            $this->ajaxReturn([
                "error_code" => 4000002,
                "error_msg" => "参数错误",
            ]);
        }

        $pnpLogic = new PnpLogicModel();
        $ret = $pnpLogic->unbindOrderMap($order_id);

        $this->ajaxReturn([
            "error_code" => $ret["errcode"],
            "error_msg" => $ret["errmsg"]
        ]);
    }
}