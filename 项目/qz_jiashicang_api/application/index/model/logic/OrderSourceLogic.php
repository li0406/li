<?php
// +----------------------------------------------------------------------
// | OrderSourceLogic 订单渠道相关数据逻辑层
// +----------------------------------------------------------------------

namespace app\index\model\logic;

use think\facade\Request;
use app\index\model\adb\OrderSource;
use app\index\model\adb\OrdersSource;
use app\index\model\adb\DepartmentIdentify;
use app\index\enum\OrderSourceCodeEnum;
use Util\Page;

class OrderSourceLogic {

    protected $orderSourceModel;
    protected $ordersSourceModel;

    public function __construct(){
        $this->orderSourceModel = new OrderSource();
        $this->ordersSourceModel = new OrdersSource();
    }

    // 获取用户权限部门（非市场中心查看全部）
    public function getAuthDepartmentList($user_id = 0){
        $departmentModel = new DepartmentIdentify();
        $list = $departmentModel->getDepartmentList($user_id);
        return $list;
    }

    // 获取渠道来源组/发单位置列表
    public function getSourceGroupList($type = 1){
        $list = $this->orderSourceModel->getSourceGroupList($type);
        if (count($list) > 0) {
            $list = array_map(function($item){
                if ($item["parent_name"]) {
                    $item["name"] = $item["parent_name"] . "/" . $item["name"];
                }

                return [
                    "id" => $item["id"],
                    "name" => $item["name"]
                ];
            }, $list);
        }

        return $list;
    }

    // 渠道来源/发单位置检索
    public function getSourceSearchList($type, $keyword, $user_id = 0){
        if ($type == OrderSourceCodeEnum::GROUP_TYPE_SOURCE) {
            $list = $this->orderSourceModel->getSourceSearchList($keyword);
        } else {
            $list = $this->orderSourceModel->getSrcSearchList($keyword, $user_id);
        }

        return $list;
    }

    // 获取分组下级ID（包含自己）
    public function getGroupChildIds($group_id, $type = 1){
        $list = $this->orderSourceModel->getGroupChildrenList($group_id, $type);
        if (count($list) > 0) {
            $childIds = array_column($list, "id");
            $childIds[] = $group_id;
            return $childIds;
        }

        return $group_id;
    }

    // 渠道数据统计（按渠道/按城市）
    public function getSrcOrderDetailed($input, $page = 1, $limit = 20){
        if (!empty($input["group_id"])) {
            $input["group_id"] = $this->getGroupChildIds($input["group_id"]);
        }

        $count = 0;
        $offset = 0;
        $pageSize = 0;
        $pageshow = null;
        if ($input["export"] == false) {
            $count = $this->ordersSourceModel->getSrcOrderDetailedCount($input);

            $pageobj = new Page($page, $limit, $count);
            $offset = $pageobj->firstrow;
            $pageSize = $pageobj->pageSize;
            $pageshow = $pageobj->show();
        }

        $list = [];
        if ($input["export"] || $count > 0) {
            $list = $this->ordersSourceModel->getSrcOrderDetailedList($input, $offset, $pageSize);

            foreach ($list as $key => $item) {
                if (!empty($item["group_parent_name"])) {
                    $list[$key]["group_name"] = $item["group_parent_name"] ."/". $item["group_name"];
                }

                $list[$key]["qiandan_amount"] = round($item["qiandan_amount"], 2);
                $list[$key]["round_amount"] = round($item["round_amount"], 2);

                $list[$key] = $this->setOrderDetailedFormatter($list[$key]);
                unset($list[$key]["group_parent_name"]);
            }
        }

        return [
            "list" => $list,
            "page" => $pageshow
        ];
    }

    // 渠道数据统计（按日期）
    public function getSrcOrderDateDetailed($input, $page = 1, $limit = 20){
        if (!empty($input["group_id"])) {
            $input["group_id"] = $this->getGroupChildIds($input["group_id"]);
        }

        $stats = [];

        // 发单量查询
        $fadanList = $this->ordersSourceModel->getDateSrcOrderFadanStats($input);
        $stats["fadanList"] = array_column($fadanList, null, "date");

        // 实际分单量查询
        $csosList = $this->ordersSourceModel->getDateSrcOrderCsosStats($input);
        $stats["csosList"] = array_column($csosList, null, "date");

        // 用户点击量房量查询
        $lfList = $this->ordersSourceModel->getDateSrcOrderLfStats($input);
        $stats["lfList"] = array_column($lfList, null, "date");

        // 签单量查询
        $qiandanList = $this->ordersSourceModel->getDateSrcOrderQiandanStats($input);
        $stats["qiandanList"] = array_column($qiandanList, null, "date");

        // 补轮量查询
        $roundList = $this->ordersSourceModel->getDateSrcOrderRoundStats($input);
        $stats["roundList"] = array_column($roundList, null, "date");

        $list = [];
        $end = strtotime($input["date_end"]);
        $begin = strtotime($input["date_begin"]);
        for ($datetime = $begin; $datetime <= $end; $datetime += 86400) {
            $date = date("Y-m-d", $datetime);
            $item = $this->setOrderDateDetailedFormatter($date, $stats);

            // 当渠道发单量、实际分单量、量房量、签单量均为0时，不显示数据
            if ($item["fadan"] + $item["csos_fendan"] + $item["liangfang"] + $item["qiandan"] > 0) {
                $item = $this->setOrderDetailedFormatter($item);
                $list[] = $item;
            }

            unset($date, $item);
        }

        // 排序
        if (!empty($input["sort_field"]) && !empty($input["sort_rule"])) {
            $list = sys_array_multisort($list, $input["sort_field"], $input["sort_rule"]);
        }

        $pageshow = null;
        if ($input["export"] == false) {
            $count = count($list);
            $pageobj = new Page($page, $limit, $count);
            $pageshow = $pageobj->show();

            $list = array_slice($list, $pageobj->firstrow, $limit);
        }

        return [
            "list" => $list,
            "page" => $pageshow
        ];
    }

    // 发单位置数据统计-按发单位置
    public function getSourceOrderDetailed($input, $page = 1, $limit = 20){
        $count = 0;
        $offset = 0;
        $pageSize = 0;
        $pageshow = null;
        if ($input["export"] == false) {
            $count = $this->orderSourceModel->getSourceOrderDetailedCount($input);

            $pageobj = new Page($page, $limit, $count);
            $offset = $pageobj->firstrow;
            $pageSize = $pageobj->pageSize;
            $pageshow = $pageobj->show();
        }

        $list = [];
        if ($input["export"] || $count > 0) {
            $list = $this->orderSourceModel->getSourceOrderDetailedList($input, $offset, $pageSize);

            foreach ($list as $key => $item) {
                if (!empty($item["group_parent_name"])) {
                    $list[$key]["group_name"] = $item["group_parent_name"] ."/". $item["group_name"];
                }

                $list[$key]["qiandan_amount"] = round($item["qiandan_amount"], 2);
                $list[$key]["round_amount"] = round($item["round_amount"], 2);

                $list[$key] = $this->setOrderDetailedFormatter($list[$key]);
                unset($list[$key]["group_parent_name"]);
            }
        }

        return [
            "list" => $list,
            "page" => $pageshow
        ];
    }

    // 发单位置数据统计（按日期）
    public function getSourceOrderDateDetailed($input, $page = 1, $limit = 20){
        $stats = [];

        // 发单量查询
        $fadanList = $this->orderSourceModel->getDateSourceOrderFadanStats($input);
        $stats["fadanList"] = array_column($fadanList, null, "date");

        // 实际分单量查询
        $csosList = $this->orderSourceModel->getDateSourceOrderCsosStats($input);
        $stats["csosList"] = array_column($csosList, null, "date");

        // 用户点击量房量查询
        $lfList = $this->orderSourceModel->getDateSourceOrderLfStats($input);
        $stats["lfList"] = array_column($lfList, null, "date");

        // 签单量查询
        $qiandanList = $this->orderSourceModel->getDateSourceOrderQiandanStats($input);
        $stats["qiandanList"] = array_column($qiandanList, null, "date");

        // 补轮量查询
        $roundList = $this->orderSourceModel->getDateSourceOrderRoundStats($input);
        $stats["roundList"] = array_column($roundList, null, "date");

        $list = [];
        $end = strtotime($input["date_end"]);
        $begin = strtotime($input["date_begin"]);
        for ($datetime = $begin; $datetime <= $end; $datetime += 86400) {
            $date = date("Y-m-d", $datetime);
            $item = $this->setOrderDateDetailedFormatter($date, $stats);

            // 当渠道发单量、实际分单量、量房量、签单量均为0时，不显示数据
            if ($item["fadan"] + $item["csos_fendan"] + $item["liangfang"] + $item["qiandan"] > 0) {
                $item = $this->setOrderDetailedFormatter($item);
                $list[] = $item;
            }

            unset($date, $item);
        }

        // 排序
        if (!empty($input["sort_field"]) && !empty($input["sort_rule"])) {
            $list = sys_array_multisort($list, $input["sort_field"], $input["sort_rule"]);
        }

        $pageshow = null;
        if ($input["export"] == false) {
            $count = count($list);
            $pageobj = new Page($page, $limit, $count);
            $pageshow = $pageobj->show();

            $list = array_slice($list, $pageobj->firstrow, $limit);
        }

        return [
            "list" => $list,
            "page" => $pageshow
        ];
    }

    // 按日期统计数据处理
    private function setOrderDateDetailedFormatter($date, $stats = []){
        $item = [];
        $item["date"] = $date;

        $item["fadan"] = intval($stats["fadanList"][$date]["fadan"] ?? 0);
        $item["fendan"] = intval($stats["fadanList"][$date]["fendan"] ?? 0);
        $item["csos_fendan"] = intval($stats["csosList"][$date]["csos_fendan"] ?? 0);
        $item["csos_zendan"] = intval($stats["csosList"][$date]["csos_zendan"] ?? 0);
        $item["csos_fen_liangfang"] = intval($stats["csosList"][$date]["csos_fen_liangfang"] ?? 0);
        $item["csos_zen_liangfang"] = intval($stats["csosList"][$date]["csos_zen_liangfang"] ?? 0);
        $item["csos_fen_qiandan"] = intval($stats["csosList"][$date]["csos_fen_qiandan"] ?? 0);
        $item["csos_zen_qiandan"] = intval($stats["csosList"][$date]["csos_zen_qiandan"] ?? 0);
        $item["csos_all"] = $item["csos_fendan"] + $item["csos_zendan"];

        $item["liangfang"] = intval($stats["lfList"][$date]["liangfang"] ?? 0);
        $item["qiandan"] = intval($stats["qiandanList"][$date]["qiandan"] ?? 0);
        $item["qiandan_amount"] = round($stats["qiandanList"][$date]["qiandan_amount"] ?? 0, 2);

        $item["round_num"] = intval($stats["roundList"][$date]["round_num"] ?? 0);
        $item["round_amount"] = round($stats["roundList"][$date]["round_amount"] ?? 0, 2);

        return $item;
    }

    // 数据处理
    private function setOrderDetailedFormatter($item) {
        $item["csos_all"] = $item["csos_fendan"] + $item["csos_zendan"];

        // 实际分单率
        $item["csos_fendan_lv"] = sys_devision($item["csos_fendan"], $item["fadan"], 4) * 100;
        $item["csos_fendan_lv_show"] = $item["csos_fendan_lv"] . "%";

        // 量房率
        $item["liangfang_lv"] = sys_devision($item["liangfang"], $item["csos_all"], 4) * 100;
        $item["liangfang_lv_show"] = $item["liangfang_lv"] . "%";

        // 分单量房率
        $item["csos_fen_lflv"] = sys_devision($item["csos_fen_liangfang"], $item["csos_fendan"], 4) * 100;
        $item["csos_fen_lflv_show"] = $item["csos_fen_lflv"] . "%";

        // 赠单量房率
        $item["csos_zen_lflv"] = sys_devision($item["csos_zen_liangfang"], $item["csos_zendan"], 4) * 100;
        $item["csos_zen_lflv_show"] = $item["csos_zen_lflv"] . "%";

        // 分单签单率
        $item["csos_fen_qdlv"] = sys_devision($item["csos_fen_qiandan"], $item["csos_fendan"], 4) * 100;
        $item["csos_fen_qdlv_show"] = $item["csos_fen_qdlv"] . "%";

        // 赠单签单率
        $item["csos_zen_qdlv"] = sys_devision($item["csos_zen_qiandan"], $item["csos_zendan"], 4) * 100;
        $item["csos_zen_qdlv_show"] = $item["csos_zen_qdlv"] . "%";

        // 签单率
        $item["qiandan_lv"] = sys_devision($item["qiandan"], $item["csos_all"], 4) * 100;
        $item["qiandan_lv_show"] = $item["qiandan_lv"] . "%";

        // 量房签单率
        $item["lf_qiandan_lv"] = sys_devision($item["qiandan"], $item["liangfang"], 4) * 100;
        $item["lf_qiandan_lv_show"] = $item["lf_qiandan_lv"] . "%";

        // 补轮率
        $item["round_lv"] = sys_devision($item["round_num"], $item["csos_fendan"], 4) * 100;
        $item["round_lv_show"] = $item["round_lv"] . "%";

        return $item;
    }
}