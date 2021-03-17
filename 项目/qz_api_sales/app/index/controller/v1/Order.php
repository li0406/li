<?php

/**
 * 订单控制器
 * @Author: liuyupeng
 * @Email: lypeng9205@163.com
 * @Date:   2019-06-04 13:35:53
 * @Last Modified by:   liuyupeng
 * @Last Modified time: 2019-06-05 14:05:37
 */

namespace app\index\controller\v1;

use think\Request;
use think\Controller;
use think\model\Collection;

use app\common\model\logic\AdminuserLogic;
use app\common\model\logic\CompanyLogic;
use app\common\model\logic\OrderLogic;
use app\common\model\logic\RemedyLogic;
use app\common\model\logic\RemedyOrderLogic;
use app\common\model\logic\CompanyRoundOrderLogic;

use app\index\enum\OrderCodeEnum;

Class Order extends Controller {

    /**
     * 所有订单 - 列表/导出
     *
     * @param OrderLogic $orderLogic
     * @return mixed
     */
    public function orderList(Request $request, OrderLogic $orderLogic) {
        $input = $request->get();
        $export = $request->param("export");
        $page = $request->get("page", 1, "intval");
        $page_size = $request->param("page_size", 20, "intval");

        // 面积查询
        if ($mianji = $request->param("mianji")) {
            $mianjiList = OrderCodeEnum::getMianji();
            foreach ($mianjiList as $key => $item) {
                if ($item["id"] == $mianji) {
                    $input["mianji_min"] = $item["value_min"];
                    $input["mianji_max"] = $item["value_max"];
                    break;
                }
            }
        }

        // 时间验证
        if (empty($input["date_begin"]) && !empty($input["date_end"])) {
            return sys_response(4000002, "请选择开始时间");
        }

        // 管辖城市
        if ($request->user["role_id"] != 1) {
            $input["citys"] = AdminuserLogic::getCityIds();
            if (empty($input["citys"])) {
                return sys_response(4000001, "暂无权限查看数据");
            } else if (!empty($input["cid"]) && !in_array($input["cid"], $input["citys"])) {
                return sys_response(4000001, "暂无权限查看数据");
            }
        }

        $superAdmin = $request->user["role_id"] == 1;
        $data = $orderLogic->getAllOrdersList($input, $export, $page, $page_size, $superAdmin);

        $response = sys_response(0, "", $data);
        return json($response);
    }

    /**
     * 所有订单 - 查看
     *
     * @return [type]           [description]
     */
    public function orderDetail(OrderLogic $orderLogic)
    {
        $id = $this->request->param("id");
        if (empty($id)) {
            return json(sys_response(4000002));
        }

        $response = $orderLogic->getDetail($id);
        return json($response);
    }

    /**
     * 所有订单 - 获取查询选项
     *
     * @return [type]           [description]
     */
    public function orderOptions()
    {
        $type_fw = OrderCodeEnum::getList("type_fw"); // 分/赠送
        $leixing = OrderCodeEnum::getList("leixing"); // 装修类型
        $mianji = OrderCodeEnum::getMianji(); // 面积

        $yusuan = model("db.jiage")->getJiage(); // 预算
        $fangshi = model("db.fangshi")->getFs(); // 装修方式

        // 移动端增加不限数据
        if ($this->request->isMobile()) {
            $yusuan = Collection::make($yusuan)->toArray();
            $fangshi = Collection::make($fangshi)->toArray();

            array_unshift($type_fw, ['id' => 0, 'name' => '不限']);
            array_unshift($leixing, ['id' => 0, 'name' => '不限']);
            array_unshift($yusuan, ['id' => 0, 'name' => '不限']);
            array_unshift($fangshi, ['id' => 0, 'name' => '不限']);
        }

        $response = sys_response(0, '', [
            'type_fw' => $type_fw,
            'yusuan' => $yusuan,
            'leixing' => $leixing,
            'fangshi' => $fangshi,
            'mianji' => $mianji,
        ]);

        return json($response);
    }

    /**
     * 补单列表
     * @param Request $request
     * @return array
     */
    public function remedyOrder(Request $request) {
        $input = $request->get();
        $page = $request->get("page", 1, "intval");
        $limit = $request->get("page_count", 20, "intval");

        // 管辖城市
        if ($request->user["role_id"] != 1) {
            $input["citys"] = AdminuserLogic::getCityIds();
            if (empty($input["citys"])) {
                return sys_response(4000001, "暂无权限查看数据");
            } else if (!empty($input["cs"]) && !in_array($input["cs"], $input["citys"])) {
                return sys_response(4000001, "暂无权限查看数据");
            }
        }

        // 默认查询时间
        if (empty($input["start"]) && empty($input["end"])) {
            $input["start"] = date("Y-m-d", strtotime("-1 month"));
            $input["end"] = date("Y-m-d");
        } else if (!empty($input["start"]) && empty($input["end"])) {
            $input["end"] = date("Y-m-d", strtotime("+1 month", strtotime($input["start"])));
        } else if (empty($input["start"]) && !empty($input["end"])) {
            $input["start"] = date("Y-m-d", strtotime("-1 month", strtotime($input["end"])));
        }

        if (!empty($input["condition"])) {
            unset($input["start"], $input["end"]);
        }

        // 最大查询时间限制
        if (!empty($input["start"]) && !empty($input["end"])) {
            $mintime = strtotime("-3 months", strtotime($input["end"]));
            if (strtotime($input["start"]) < $mintime) {
                return json(sys_response(4000001, "最多只能查询三个月"));
            }
        }

        $remedyLogic = new RemedyOrderLogic();
        $data = $remedyLogic->getRemedyOrderList($input, $page, $limit);
        return json(sys_response(0, "请求成功", $data));
    }

    /**
     * 订单基本信息
     * @param Request $request
     * @return array
     */
    public function orderInfo(Request $request)
    {
        $order_id = $request->order_id;
        $remedy = new RemedyOrderLogic();
        return $remedy->getOrderInfo($order_id);
    }

    /**
     * 订单装修公司信息(已分配/过期公司)
     * @param Request $request
     * @return array
     */
    public function orderCompanyInfo(Request $request)
    {
        $order_id = $request->order_id;
        $remedy = new RemedyOrderLogic();
        return $remedy->getOrderCompany($order_id);
    }

    public function orderCompanyList(Request $request)
    {
        $order_id = $request->order_id;
        $order = (new \app\common\model\db\Orders())->getOrderInfoById($order_id,'id,cs,lng,lat,type_fw');

        if(empty($order)){
            return sys_response(4000001);
        }
        $comLogic = new CompanyLogic();
        $company = $comLogic->getCompanyListByCity($order['cs'],$order['id'],$order['lng'],$order['lat'],$order["type_fw"]);
        return sys_response(0,'',$company);
    }

    public function RemedyFenCompany(Request $request)
    {
        if (empty($request->order_id) || $request->order_id == '' || count($request->company) == 0) {
            return sys_response(4000002);
        }
        $comLogic = new RemedyOrderLogic();
        return $comLogic->changeOrder($request);
    }

    public function unRemedyOption(Request $request){
        if(count($request->order) == 0){
            return sys_response(4000002);
        }
        $comLogic = new RemedyOrderLogic();
        return $comLogic->saveUnRemedyOrder($request->order, $request->user);
    }

    /**
     * 稽核部已分配订单（最近七天）
     * @param  Request    $request    [description]
     * @param  OrderLogic $orderLogic [description]
     * @return [type]                 [description]
     */
    public function auditOrder(Request $request, OrderLogic $orderLogic){
        $page = $request->param("page", 1);
        $page_size = $request->param("page_size", 20);

        $result = $orderLogic->getAuditOrderList($page, $page_size);

        $response = sys_response(0, "", [
            "list" => $result["list"],
            "page" => sys_paginate($result["count"], $page_size, $page),
        ]);

        return json($response);
    }

    /**
     * 获取小报备签单选择列表
     * @param  Request                $request                [description]
     * @param  CompanyRoundOrderLogic $companyRoundOrderLogic [description]
     * @return [type]                                         [description]
     */
    public function getRoundOrderSignList(Request $request, CompanyRoundOrderLogic $companyRoundOrderLogic){
        $input = $request->get();
        $page = sys_page_format($request->page, 1);
        $limit = sys_page_format($request->limit, 10);

        // 默认查询时间
        if (empty($input["date_begin"]) || empty($input["date_end"])) {
            $input["date_begin"] = date("Y-m-d", strtotime("-3 months"));
            $input["date_end"] = date("Y-m-d");
        }

        $data = $companyRoundOrderLogic->getRoundOrderSignList($input, $page, $limit);

        $response = sys_response(0, "", [
            "list" => $data["list"],
            "page" => $data["page"]
        ]);

        return json($response);
    }

    /**
     * 签单订单详情
     * @param  Request                $request                [description]
     * @param  CompanyRoundOrderLogic $companyRoundOrderLogic [description]
     * @return [type]                                         [description]
     */
    public function getRoundOrderSignInfo(Request $request, CompanyRoundOrderLogic $companyRoundOrderLogic){
        $order_id = $request->get("order_id");
        if (empty($order_id)) {
            return json(sys_response(4000002));
        }

        $orderinfo = $companyRoundOrderLogic->getRoundOrderSignInfo($order_id);
        if (empty($orderinfo)) {
            return json(sys_response(4000001, "签单订单不存在"));
        } else if (empty($orderinfo["qiandan_companyid"])) {
            return json(sys_response(4000001, "未查询到该订单的签单信息"));
        } else if ($orderinfo["qiandan_status"] != 1) {
            return json(sys_response(4000001, "该签单暂未审核，请先审核"));
        }

        $response = sys_response(0, "", [
            "orderinfo" => $orderinfo
        ]);

        return json($response);
    }


    
}