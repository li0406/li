<?php
// +----------------------------------------------------------------------
// | OrderSourceRoiLogic 渠道账户ROI相关统计数据逻辑层
// +----------------------------------------------------------------------

namespace app\index\model\logic;

use think\Exception;
use app\index\model\adb\OrderSourceAccount;
use app\index\model\adb\OrderSourceAccountExpend;
use app\index\enum\CacheKeyCodeEnum;
use Util\Page;

class OrderSourceRoiLogic {

    protected $accountModel;
    protected $expendModel;

    public function __construct(){
        $this->accountModel = new OrderSourceAccount();
        $this->expendModel = new OrderSourceAccountExpend();
    }

    // 获取渠道账户ROI数据统计汇总
    public function getAccountRoiTotal($input){
        $authUsers = $input["auth_users"] ?? [];
        $accountIds = $input["account_ids"] ?? [];
        $begin = $input["date_begin"];
        $end = $input["date_end"];

        $total = [];

        // 查询发单量、分单量、订单收入
        $orderList = $this->accountModel->getAccountOrderStats($begin, $end, $accountIds, $authUsers)->toArray();
        $total["order_user_amount"] = sys_array_sum($orderList, "order_user_amount", 2); // 1.0会员订单收入
        $total["order_sback_amount"] = sys_array_sum($orderList, "order_sback_amount", 2); // 2.0会员订单收入
        $total["order_amount"] = round($total["order_user_amount"] + $total["order_sback_amount"], 2); // 订单收入

        $total["fadan"] = sys_array_sum($orderList, "fadan"); // 发单量
        $total["fendan"] = sys_array_sum($orderList, "fendan"); // 分单量
        $total["fendan_lv"] = sys_devision($total["fendan"], $total["fadan"], 4);
        $total["fendan_lv_show"] = ($total["fendan_lv"] * 100) . "%";

        // 查询渠道账户消耗
        $expendList = $this->expendModel->getAccountExpendStats($begin, $end, $accountIds, $authUsers)->toArray();
        $total["expend_amount"] = sys_array_sum($expendList, "expend_amount", 2); // 渠道消耗

        // 投资回报率
        $total["roi"] = sys_devision($total["order_amount"], $total["expend_amount"], 4);
        $total["roi_show"] = ($total["roi"] * 100)  . "%";

        return $total;
    }

    // 获取渠道账户ROI数据统计
    public function getAccountRoiPageList($input, $page = 1, $limit = 20){
        $count = 0;
        $offset = 0;
        $pageSize = 0;
        $pageshow = null;
        if ($input["export"] == false) {
            $count = $this->accountModel->getAccountRoiPageCount($input);

            $pageobj = new Page($page, $limit, $count);
            $offset = $pageobj->firstrow;
            $pageSize = $pageobj->pageSize;
            $pageshow = $pageobj->show();
        }

        if ($input["export"] || $count > 0) {
            $authUsers = $input["auth_users"] ?? [];
            $accountIds = $input["account_ids"] ?? [];
            $begin = $input["date_begin"];
            $end = $input["date_end"];

            // 查询渠道账户
            $list = $this->accountModel->getAccountRoiPageList($input, $offset, $pageSize);
            if ($input["export"] == false) {
                $accountIds = array_column($list->toArray(), "id");
            }

            // 查询关联的渠道标识
            $realtedList = $this->accountModel->getAccountRelatedStats($accountIds, $authUsers);
            $realtedList = array_column($realtedList->toArray(), null, "account_id");

            // 查询发单量、分单量、订单收入
            $orderList = $this->accountModel->getAccountOrderStats($begin, $end, $accountIds, $authUsers);
            $orderList = array_column($orderList->toArray(), null, "account_id");

            // 查询渠道账户消耗
            $expendList = $this->expendModel->getAccountExpendStats($begin, $end, $accountIds, $authUsers);
            $expendList = array_column($expendList->toArray(), null, "account_id");

            foreach ($list as $key => $item) {
                $account_id = $item["id"];
                $list[$key]["src_number"] = intval($realtedList[$account_id]["src_number"] ?? 0);
                $list[$key]["expend_amount"] = floatval($expendList[$account_id]["expend_amount"] ?? 0);

                $list[$key]["fadan"] = intval($orderList[$account_id]["fadan"] ?? 0);
                $list[$key]["fendan"] = intval($orderList[$account_id]["fendan"] ?? 0);
                $list[$key]["order_user_amount"] = floatval($orderList[$account_id]["order_user_amount"] ?? 0);
                $list[$key]["order_sback_amount"] = floatval($orderList[$account_id]["order_sback_amount"] ?? 0);
                $list[$key]["order_amount"] = round($list[$key]["order_user_amount"] + $list[$key]["order_sback_amount"], 2);

                $list[$key]["fendan_lv"] = sys_devision($list[$key]["fendan"], $list[$key]["fadan"], 4);
                $list[$key]["fendan_lv_show"] = ($list[$key]["fendan_lv"] * 100) . "%";

                // 投资回报率
                $list[$key]["roi"] = sys_devision($list[$key]["order_amount"], $list[$key]["expend_amount"], 4);
                $list[$key]["roi_show"] = ($list[$key]["roi"] * 100)  . "%";
            }
        }

        return [
            "list" => $list ?? [],
            "page" => $pageshow
        ];
    }

    // 获取ROI趋势图数据
    public function getRoiTrendList($input){
        $authUsers = $input["auth_users"] ?? [];
        $accountIds = $input["account_ids"] ?? [];
        $begin = $input["date_begin"];
        $end = $input["date_end"];

        // 查询发单量、分单量、订单收入
        $orderList = $this->accountModel->getAccountOrderDateStats($begin, $end, $accountIds, $authUsers);
        $orderList = array_column($orderList->toArray(), null, "date");

        // 查询渠道账户日消耗
        $expendList = $this->expendModel->getAccountExpendDateStats($begin, $end, $accountIds, $authUsers);
        $expendList = array_column($expendList->toArray(), null, "date");

        $list = [];
        $begintime = strtotime($begin);
        $todaytime = strtotime(date("Y-m-d"));
        $endtime = strtotime($end) > $todaytime ? $todaytime : strtotime($end);

        // 每日数据
        for ($datetime = $begintime; $datetime <= $endtime; $datetime += 86400) {
            $date = date("Y-m-d", $datetime);

            $item = [
                "date" => $date,
                "dateshow" => date("n/j", $datetime),
                "expend_amount" => floatval($expendList[$date]["expend_amount"] ?? 0),
                "order_user_amount" => floatval($orderList[$date]["order_user_amount"] ?? 0),
                "order_sback_amount" => floatval($orderList[$date]["order_sback_amount"] ?? 0),
            ];

            $item["order_amount"] = round($item["order_user_amount"] + $item["order_sback_amount"], 2);
            $item["roi"] = sys_devision($item["order_amount"], $item["expend_amount"], 4) * 100;

            $list[] = $item;
        }

        return $list;
    }
}