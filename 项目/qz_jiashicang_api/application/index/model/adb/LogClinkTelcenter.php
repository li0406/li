<?php

// +----------------------------------------------------------------------
// | 天润电话数据模型
// +----------------------------------------------------------------------

namespace app\index\model\adb;

use think\Db;
use think\db\Query;

use app\common\model\adb\BaseModel;

class LogClinkTelcenter extends BaseModel {

    // 区域订单维度天润电话呼叫行为统计
    public function getAreaOrderTelcenterStatistic($date_begin, $date_end, $city_ids = [], $area_ids = []){
        $map = new Query();
        $map->where("o.time_real", ">=", strtotime($date_begin));
        $map->where("o.time_real", "<=", strtotime($date_end) + 86399);

        // 城市查询
        if (!empty($city_ids)) {
            $map->where("o.cs", "in", $city_ids);
        }

        // 区域查询
        if (!empty($area_ids)) {
            $map->where("o.qx", "in", $area_ids);
        }

        // 先按订单维度聚合
        $subsql = $this->link()->name("orders")->alias("o")->where($map)
            ->join("log_clink_order_telcenter to", "to.order_id = o.id", "left")
            ->join("log_clink_telcenter tc", "tc.call_sid = to.call_sid", "left")
            ->field([
                "o.id as order_id",
                "sum(IF(tc.call_sid is not null, 1, 0)) as yihu_num",
                "sum(IF(tc.call_sid is not null and tc.cdr_bridge_time > 0 and tc.cdr_end_time > 0, 1, 0)) as hutong_num",
            ])
            ->group("o.id")
            ->buildSql();

        $list = $this->link()->table($subsql)->alias("t")
            ->join("orders o", "o.id = t.order_id", "inner")
            ->field([
                "o.cs as city_id", "o.qx as area_id", "concat(o.cs, '-', o.qx) as gkey",
                "count(IF(t.hutong_num > 0, 1, null)) as hutong",
                "count(IF(t.yihu_num > 0, 1, null)) as yihu",
                "count(IF(t.yihu_num = 0, 1, null)) as weihu",
            ])
            ->group("o.cs,o.qx")
            ->select();

        return $list;
    }
}