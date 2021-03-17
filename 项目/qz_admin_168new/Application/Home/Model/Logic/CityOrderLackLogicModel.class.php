<?php

namespace Home\Model\Logic;

use Home\Model\OrdersModel;
use Home\Model\Db\CityOrderLackModel;
use Home\Model\Db\CityOrderActualModel;

class CityOrderLackLogicModel {

    protected $OrdersModel;
    protected $cityOrderLackModel;
    protected $cityOrderActualModel;

    public function __construct(){
        $this->ordersModel = new OrdersModel();
        $this->cityOrderLackModel = new CityOrderLackModel();
        $this->cityOrderActualModel = new CityOrderActualModel();
    }

    // 城市缺单统计
    public function getCityLackOrderStat($input){
        $input["time_begin"] = strtotime($input["begin"]);
        $input["time_end"] = strtotime($input["end"]) + 86399;

        $days = date("t", $input["time_begin"]);
        $tday = date("d", $input["time_end"]);
        $lday = $days - $tday + 1;

        // 查询城市发单量、分单量
        $csOrderStat = $this->ordersModel->getAllCityOrderList($input["time_begin"], $input["time_end"]);
        $csOrderStat = array_column($csOrderStat, null, "city_id");

        // 查询城市实际分单量
        $csOrderRealStat = $this->ordersModel->getAllCityRealOrderList($input["time_begin"], $input["time_end"]);
        $csOrderRealStat = array_column($csOrderRealStat, null, "city_id");

        // 查询城市缺单统计
        $list = $this->cityOrderLackModel->getCityLackOrderStat($input);
        $list = array_column($list, null, "city_id");

        // 获取设置的实际所需分单
        $actualList = $this->cityOrderActualModel->getCityMonthList($input["time_begin"]);
        $actualList = array_column($actualList, null, "city_id");

        // 获取当前日期的城市缺单统计
        $lastdatetime = strtotime($input["end"]);
        $lastList = $this->cityOrderLackModel->getCityLackOrderDayStat($lastdatetime);
        $lastList = array_column($lastList, null, "city_id");

        // 获取当前日期前一天的城市缺单统计
        $yesterdaytime = strtotime($input["end"]) - 86400;
        $yesterdayList = $this->cityOrderLackModel->getCityLackOrderDayStat($yesterdaytime);
        $yesterdayList = array_column($yesterdayList, null, "city_id");

        $all = [
            "actual_orders" => 0,
            "real_miss_order" => 0,
            "month_demand_order" => 0,
            "month_miss_order" => 0,
            "now_demand_order" => 0,
            "now_miss_order" => 0,
            "fen_real_count" => 0,
            "all_count" => 0,
        ];

        foreach ($list as $city_id => &$item) {
            $item["city_level"] = $item["little"] + 1; // 城市级别
            $item["now_demand_order"] = floatval($item["predict_orders_all"] / $days); // 当前所需分单

            $item["near_order_allots"] = floatval($item["near_order_allots"]); // 相邻城市分单
            $item["near_fen_orders"] = floatval($item["near_fen_orders"]); // 按照当前城市会员折算的相邻城市分单

            $item["all_count"] = intval($csOrderStat[$city_id]["all_count"]); // 发单量
            $item["fen_count"] = intval($csOrderStat[$city_id]["fen_count"]); // 分单量
            $item["fen_real_count"] = intval($csOrderRealStat[$city_id]["fen_count"]); // 实际分单量
            $item["fen_real_total_count"] = $item["fen_real_count"] + $item["near_fen_orders"]; // 总实际分单量

            $item["vip_num"] = floatval($lastList[$city_id]["vip_num"]); // 常规会员数
            $item["vip_signback_num"] = floatval($lastList[$city_id]["vip_signback_num"]); // 签返会员数
            $item["vip_total_num"] = floatval($lastList[$city_id]["vip_total_num"]); // 会员总数
            $item["vip_total_count"] = floatval($lastList[$city_id]["vip_total_count"]); // 会员个数
            $vip_change = $item["vip_total_num"] - floatval($yesterdayList[$city_id]["vip_total_num"]); // 会员动态
            $item["vip_dynamic"] = $vip_change > 0 ? "+". $vip_change : $vip_change; // 会员动态

            $item["dynamic_date"] = $lastList[$city_id]["dynamic_date"]; // 最后波动时间
            $item["order_fen_num"] = $lastList[$city_id]["order_fen_num"]; // 订单分单量
            $item["order_allot_num"] = $lastList[$city_id]["order_allot_num"]; // 订单实际分配量
            $item["no_order_days"] = intval($lastList[$city_id]["no_order_days"]); // 当月连续无分单天数
            $item["whole_month"] = intval($lastList[$city_id]["whole_month"]); // 城市类型（1：整月；2：非整月）
            $item["fen_avg"] = floatval($lastList[$city_id]["fen_avg"]); // 平均分几家
            $item["promise_orders"] = floatval($lastList[$city_id]["promise_orders"]); // 城市承诺量
            
            // $item["predict_month_orders"] = floatval($lastList[$city_id]["predict_month_orders"]); 
            $item["predict_month_orders"] = 0; // 按当天的量预估当月剩余所需分单
            if (array_key_exists($city_id, $lastList)) {
                $last = $lastList[$city_id];
                $lastList[$city_id]["predict_orders"] = $last["vip_total_num"] * $last["promise_orders"] / $days / $last["fen_avg"];
                $item["predict_month_orders"] = $lastList[$city_id]["predict_orders"] * $lday;
            }

            $item["month_demand_order"] = round($item["now_demand_order"] + $item["predict_month_orders"] - $lastList[$city_id]["predict_orders"], 2); // 预估当月所需分单（累加T-1所需分单+按当天的量预估当月剩余所需分单）

            // 当月缺单=预估当月所需分单-实际分单量-按照当前城市会员折算的相邻城市分单
            $item["month_miss_order"] = round($item["month_demand_order"] - $item["fen_real_count"] - $item["near_fen_orders"], 2);
            $item["month_miss_order"] = $item["month_miss_order"] > 0 ? $item["month_miss_order"] : 0;

            // 当前缺单=当前所需分单-实际分单-按照当前城市会员折算的相邻城市分单
            $item["now_miss_order"] = round($item["now_demand_order"] - $item["fen_real_count"] - $item["near_fen_orders"], 2);

            // 计算缺单率
            $item["month_miss_order_rate"] = 0;
            if ($item["month_demand_order"] > 0) {
                $item["month_miss_order_rate"] = round($item["month_miss_order"] / $item["month_demand_order"] * 100, 2);
            }
            
            $item["now_demand_order"] = round($item["now_demand_order"], 2);
            $item["predict_month_orders"] = round($item["predict_month_orders"], 2);

            // 实际所需分单
            $item["actual_orders"] = floatval($actualList[$city_id]["actual_orders"]);
            
            $item["real_miss_order"] = 0; // 实际缺单
            $item["real_miss_order_rate"] = 0; // 实际缺单率
            if ($item["actual_orders"] > 0) {
                $item["real_miss_order"] = $item["actual_orders"] - $item["fen_real_total_count"];
                $item["real_miss_order_rate"] = round($item["real_miss_order"] / $item["actual_orders"] * 100, 2);

                $item["real_miss_order"] = $item["real_miss_order"] > 0 ? $item["real_miss_order"] : 0;
                $item["real_miss_order_rate"] = $item["real_miss_order_rate"] > 0 ? $item["real_miss_order_rate"] : 0;
            }

            // 实际所需分单为0时使用预估当月所需分单填充
            $item["has_actual_orders"] = 1;
            if ($item["actual_orders"] == 0) {
                $item["has_actual_orders"] = 0;
                $item["actual_orders"] = $item["month_demand_order"];
                $item["real_miss_order"] = $item["month_miss_order"];
                $item["real_miss_order_rate"] = $item["month_miss_order_rate"];
            }

            if (empty($input["city"])) {
                // 没有会员、预估分单、实际分单的城市从列表中去除
                if ($item["vip_total_num"] == 0 && $item["month_demand_order"] == 0 && $item["fen_real_count"] == 0) {
                    unset($list[$city_id], $item);
                    continue;
                }
            }

            // 实际总所需分单：统计所有城市的实际所需分单；单个城市实际所需分单不为0时，统计该城市的实际所需分单；
            // 城市所需分单为0时，统计该城市的预估当月所需分单；
            // if ($item["actual_orders"] > 0) {
            //     $all["actual_orders"] += $item["actual_orders"];
            // } else {
            //     $all["actual_orders"] += $item["month_demand_order"];
            // }

            // 实际总缺单：统计所有城市的实际缺单总和；单个城市实际缺单不为0时，统计该城市的实际缺单；
            // 城市缺单为0时，统计该城市的预估当月缺单；只累加正数
            // if ($item["real_miss_order"] != 0) {
            //     if ($item["real_miss_order"] > 0) {
            //         $all["real_miss_order"] += $item["real_miss_order"];
            //     }
            // } else if ($item["month_miss_order"] > 0) {
            //     $all["real_miss_order"] += $item["month_miss_order"];
            // }

            $all["actual_orders"] += $item["actual_orders"];
            $all["real_miss_order"] += $item["real_miss_order"];
            $all["month_demand_order"] += $item["month_demand_order"];
            $all["now_demand_order"] += $item["now_demand_order"];
            $all["fen_real_count"] += $item["fen_real_count"];
            $all["all_count"] += $item["all_count"];
            
            // 当月总缺单只累加正值
            if ($item["month_miss_order"] > 0) {
                $all["month_miss_order"] += $item["month_miss_order"];
            }

            // 当前总缺单只累加正值
            if ($item["now_miss_order"] > 0) {
                $all["now_miss_order"] += $item["now_miss_order"];
            }
        }

        // 按照会员数从大到小排序
        $list = multi_array_sort($list, "vip_total_num", SORT_DESC);

        // 计算之后处理精度
        $all["actual_orders"] = round($all["actual_orders"], 2);
        $all["real_miss_order"] = round($all["real_miss_order"], 2);
        $all["month_demand_order"] = round($all["month_demand_order"], 2);
        $all["month_miss_order"] = round($all["month_miss_order"], 2);
        $all["now_demand_order"] = round($all["now_demand_order"], 2);
        $all["now_miss_order"] = round($all["now_miss_order"], 2);
        $all["fen_real_count"] = round($all["fen_real_count"], 2);
        $all["all_count"] = round($all["all_count"], 2);

        return [
            "list" => $list,
            "all" => $all
        ];
    }

    // 设置实际所需分单
    public function setCityOrderActualOrders($city_id, $datetime, $actual_orders, $operator){
        $info = $this->cityOrderActualModel->getInfo($city_id, $datetime);

        $data = [
            "city_id" => $city_id,
            "datetime" => $datetime,
            "actual_orders" => $actual_orders,
            "operator" => $operator,
            "updated_at" => time()
        ];

        if (empty($info)) {
            $data["created_at"] = time();
            $actual_id = $ret = $this->cityOrderActualModel->add($data);
        } else {
            $actual_id = $info["id"];
            $ret = $this->cityOrderActualModel->updateInfo($info["id"], $data);
        }

        if ($ret !== false) {
            $res = $this->cityOrderActualModel->addLog([
                "actual_id" => $actual_id,
                "datetime" => $datetime,
                "city_id" => $city_id,
                "actual_orders" => $actual_orders,
                "last_actual_orders" => $info["actual_orders"] ?? 0,
                "operator" => $operator,
                "created_at" => time(),
                "updated_at" => time()
            ]);
        }

        return $ret;
    }

    // 实际所需分单设置日志
    public function getCityOrderActualLog($city_id, $datetime){
        $list = $this->cityOrderActualModel->getLogList($city_id, $datetime);

        foreach ($list as $key => &$item) {
            $item["month"] = date("Y-m", $item["datetime"]);
            $item["actual_orders"] = floatval($item["actual_orders"]);
            $item["last_actual_orders"] = floatval($item["last_actual_orders"]);
            $item["created_date"] = date("Y-m-d H:i:s", $item["created_at"]);
        }

        return $list;
    }

}