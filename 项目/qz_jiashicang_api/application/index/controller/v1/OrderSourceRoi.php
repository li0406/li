<?php

// +----------------------------------------------------------------------
// | 渠道账户Roi统计数据控制器
// +----------------------------------------------------------------------

namespace app\index\controller\v1;

use think\Request;
use think\Controller;

use app\index\model\logic\AdminAuthLogic;
use app\index\model\logic\OrderSourceRoiLogic;
use app\index\validate\OrderSourceRoiValidate;
use app\common\enum\BaseStatusCodeEnum;

class OrderSourceRoi extends Controller {

    // ROI数据总计
    public function getRoiTotal(Request $request, OrderSourceRoiLogic $sourceRoiLogic){
        $input = $request->get();
        $checked = OrderSourceRoiValidate::checkRoiStatsData($input);
        if ($checked !== true) {
            $response = sys_response(4000002, $checked);
            return json($response);
        }

        $input["auth_users"] = AdminAuthLogic::getMarketAuthUsers();
        $total = $sourceRoiLogic->getAccountRoiTotal($input);

        $data = [
            "total" => $total,
            "options" => [
                "date_begin" => $input["date_begin"] ?? "",
                "date_end" => $input["date_end"] ?? ""
            ]
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }

    // ROI数据明细
    public function getRoiDetailed(Request $request, OrderSourceRoiLogic $sourceRoiLogic){
        $input = $request->get();
        $page = sys_page_format($request->page, 1);
        $limit = sys_page_format($request->limit, 20);
        $checked = OrderSourceRoiValidate::checkRoiStatsData($input);
        if ($checked !== true) {
            $response = sys_response(4000002, $checked);
            return json($response);
        }

        $input["auth_users"] = AdminAuthLogic::getMarketAuthUsers();
        $data = $sourceRoiLogic->getAccountRoiPageList($input, $page, $limit);

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }

    // ROI趋势图
    public function getRoiTrendline(Request $request, OrderSourceRoiLogic $sourceRoiLogic){
        $input = $request->get();
        $checked = OrderSourceRoiValidate::checkRoiStatsData($input);
        if ($checked !== true) {
            $response = sys_response(4000002, $checked);
            return json($response);
        }

        // 获取趋势图数据
        $input["auth_users"] = AdminAuthLogic::getMarketAuthUsers();
        $list = $sourceRoiLogic->getRoiTrendList($input);

        $data = [
            "series" => [
                [
                    "name" => "ROI",
                    "type" => "line",
                    "data" => array_column($list, "roi"),
                ]
            ],
            "xAxis" => array_column($list, "dateshow"),
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }
}