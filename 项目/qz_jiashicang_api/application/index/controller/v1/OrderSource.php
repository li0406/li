<?php

// +----------------------------------------------------------------------
// | 订单渠道数据控制器
// +----------------------------------------------------------------------

namespace app\index\controller\v1;

use think\Request;
use think\Controller;

use app\index\model\logic\OrderSourceLogic;
use app\index\validate\OrderSourceValidate;
use app\index\enum\AdminuserCodeEnum;
use app\index\enum\OrderSourceCodeEnum;
use app\common\enum\BaseStatusCodeEnum;

class OrderSource extends Controller {

    private $auth_user_id = 0;

    public function initialize(){
        $user = request()->user;
        if ($user["dept_top_id"] == AdminuserCodeEnum::DEPT_MARKET_CENTER) {
            $this->auth_user_id = $user["user_id"];
        }
    }

    // 渠道数据统计-筛选项
    public function getSrcOptions(Request $request, OrderSourceLogic $orderSourceLogic){
        $actfrom = $request->get("actfrom", 1);
        if ($actfrom == 2) {
            $data["group_list"] = $orderSourceLogic->getSourceGroupList(OrderSourceCodeEnum::GROUP_TYPE_SOURCE);
        } else {
            $data["department_list"] = $orderSourceLogic->getAuthDepartmentList($this->auth_user_id);
            $data["group_list"] = $orderSourceLogic->getSourceGroupList(OrderSourceCodeEnum::GROUP_TYPE_SRC);
        }
        
        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }

    // 渠道数据统计-检索来源
    public function getSrcSearchList(Request $request, OrderSourceLogic $orderSourceLogic){
        $actfrom = $request->get("actfrom", 1);
        $keyword = $request->get("keyword");
        if (empty($keyword)) {
            return json(sys_response(4000002));
        }

        if ($actfrom == 2) {
            $type = OrderSourceCodeEnum::GROUP_TYPE_SOURCE;
            $list = $orderSourceLogic->getSourceSearchList($type, $keyword);
        } else {
            $type = OrderSourceCodeEnum::GROUP_TYPE_SRC;
            $list = $orderSourceLogic->getSourceSearchList($type, $keyword, $this->auth_user_id);
        }

        $data = [
            "list" => $list,
            "count" => count($list)
        ];
        
        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }

    // 渠道数据统计-按渠道/城市统计
    public function getSrcDetailed(Request $request, OrderSourceLogic $orderSourceLogic){
        set_time_limit(0);
        ini_set("memory_limit", "1024M");

        $input = $request->get();
        $page = sys_page_format($request->page, 1);
        $limit = sys_page_format($request->limit, 20);

        // 验证并补全查询数据
        $gtype = OrderSourceCodeEnum::GROUP_TYPE_SRC;
        $checked = OrderSourceValidate::checkOrderSourceDetailedData($input, $gtype);
        if ($checked !== true) {
            return json(sys_response(4000002, $checked));
        }

        // 查询数据
        $input["auth_user_id"] = $this->auth_user_id;
        $data = $orderSourceLogic->getSrcOrderDetailed($input, $page, $limit);

        $data["options"] = [
            "date_begin" => $input["date_begin"],
            "date_end" => $input["date_end"]
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }

    // 渠道数据统计-按日期
    public function getSrcDateDetailed(Request $request, OrderSourceLogic $orderSourceLogic){
        $input = $request->get();
        $page = sys_page_format($request->page, 1);
        $limit = sys_page_format($request->limit, 20);

        // 验证并补全查询数据
        $gtype = OrderSourceCodeEnum::GROUP_TYPE_SRC;
        $checked = OrderSourceValidate::checkOrderSourceDetailedData($input, $gtype, true);
        if ($checked !== true) {
            return json(sys_response(4000002, $checked));
        }

        // 查询数据
        $input["auth_user_id"] = $this->auth_user_id;
        $data = $orderSourceLogic->getSrcOrderDateDetailed($input, $page, $limit);

        $data["options"] = [
            "date_begin" => $input["date_begin"],
            "date_end" => $input["date_end"]
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }

    // 发单位置数据统计-按发单位置
    public function getSourceDetailed(Request $request, OrderSourceLogic $orderSourceLogic){
        set_time_limit(0);
        ini_set("memory_limit", "1024M");

        $input = $request->get();
        $page = sys_page_format($request->page, 1);
        $limit = sys_page_format($request->limit, 20);

        // 验证并补全查询数据
        $gtype = OrderSourceCodeEnum::GROUP_TYPE_SOURCE;
        $checked = OrderSourceValidate::checkOrderSourceDetailedData($input, $gtype);
        if ($checked !== true) {
            return json(sys_response(4000002, $checked));
        }

        // 查询数据
        $data = $orderSourceLogic->getSourceOrderDetailed($input, $page, $limit);

        $data["options"] = [
            "date_begin" => $input["date_begin"],
            "date_end" => $input["date_end"]
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }

    // 渠道数据统计-按日期
    public function getSourceDateDetailed(Request $request, OrderSourceLogic $orderSourceLogic){
        $input = $request->get();
        $page = sys_page_format($request->page, 1);
        $limit = sys_page_format($request->limit, 20);

        // 验证并补全查询数据
        $gtype = OrderSourceCodeEnum::GROUP_TYPE_SOURCE;
        $checked = OrderSourceValidate::checkOrderSourceDetailedData($input, $gtype, true);
        if ($checked !== true) {
            return json(sys_response(4000002, $checked));
        }

        // 查询数据
        $data = $orderSourceLogic->getSourceOrderDateDetailed($input, $page, $limit);

        $data["options"] = [
            "date_begin" => $input["date_begin"],
            "date_end" => $input["date_end"]
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }
}
