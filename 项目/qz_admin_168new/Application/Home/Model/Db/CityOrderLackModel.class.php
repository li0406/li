<?php

namespace Home\Model\Db;

use Think\Model;

class CityOrderLackModel extends Model {

    // 获取城市缺单统计所有数据
    public function getFixFenAvgAll(){
        $map = array();
        $map[] = "a.fen_avg <> 1";
        $map[] = "a.fen_avg > a.vip_total_count";

        return $this->alias("a")->where($map)
            ->join("inner join qz_quyu as q on q.cid = a.city_id")
            ->field(["a.*", "q.little"])
            ->select();
    }

    // 城市缺单统计
    public function getCityLackOrderStat($input){
        $map = array(
            "q.is_open_city" => array("EQ", 1),
            "a.datetime" => array(
                array("EGT", $input["time_begin"]),
                array("ELT", $input["time_end"])
            )
        );

        if (!empty($input["city"])) {
            $map["q.cid"] = array("EQ", $input["city"]);
        }

        if (!empty($input["level"])) {
            $map["q.little"] = array("EQ", $input["level"] - 1);
        }

        if (!empty($input["whole"])) {
            $map["a.whole_month"] = array("EQ", $input["whole"]);
        }

        return M("city_order_lack")->alias("a")->where($map)
            ->join("left join qz_quyu as q on q.cid = a.city_id")
            ->field([
                "a.city_id", "q.cname as city_name", "q.little",
                // "sum(a.predict_orders) as now_demand_order",
                "sum(a.fen_avg) as fen_avg_count",
                "sum(a.near_order_allot_num) as near_order_allots",
                "sum(a.near_fen_orders) as near_fen_orders",
                "sum(a.vip_total_num * a.promise_orders / a.fen_avg) as predict_orders_all"
            ])
            ->group("a.city_id")
            ->select();
    }

    // 获取当前日期的缺单统计
    public function getCityLackOrderDayStat($datetime){
        $map = array(
            "a.datetime" => array("EQ", $datetime)
        );

        return M("city_order_lack")->alias("a")->where($map)
            ->select();
    }

}