<?php

namespace Home\Model\Db;

use Think\Model;

class PnpOrderMapModel extends Model {

    // 虚拟号订单列表查询条件
    public function getOrderMapStatsMap($input){
        $map = array(
            "o.`on`" => array("EQ", 4),
            "o.type_fw" => array("IN", [1, 2])
        );

        // 订单ID查询
        if (!empty($input["order_id"])) {
            $map["a.order_id"] = array("EQ", $input["order_id"]);
        }

        // 公司ID查询
        if (!empty($input["company_id"])) {
            $map["a.company_id"] = array("EQ", $input["company_id"]);
        }

        // 城市
        if (!empty($input["city_id"])) {
            $map["o.cs"] = array("EQ", $input["city_id"]);
        }

        // 订单状态
        if (!empty($input["type_fw"])) {
            $map["o.type_fw"] = array("EQ", $input["type_fw"]);
        }

        // 分配时间
        if (!empty($input["begin"]) && !empty($input["end"])) {
            $map["csos.lasttime"] = array("between", [
                strtotime($input["begin"]),
                strtotime($input["end"]) + 86399
            ]);
        }

        return $map;
    }

    // 虚拟号订单列表查询数量
    public function getOrderMapStatsCount($input) {
        $map = $this->getOrderMapStatsMap($input);

        $subSql = $this->alias("a")->where($map)
            ->join("inner join qz_orders as o on o.id = a.order_id")
            ->join("inner join qz_order_csos_new as csos on csos.order_id = a.order_id")
            ->join("inner join qz_order_info as i on i.`order` = a.order_id and i.com = a.company_id")
            ->field(["a.order_id"])
            ->group("a.order_id")
            ->buildSql();

        $count = M()->table($subSql)->alias("t")
            ->count("t.order_id");

        return $count;
    }

    // 虚拟号订单列表查询
    public function getOrderMapStatsList($input, $offset = 0, $limit = 0) {
        $map = $this->getOrderMapStatsMap($input);

        $subSql = $this->alias("a")->where($map)
            ->join("inner join qz_orders as o on o.id = a.order_id")
            ->join("inner join qz_order_csos_new as csos on csos.order_id = a.order_id")
            ->join("inner join qz_order_info as i on i.`order` = a.order_id and i.com = a.company_id")
            ->field([
                "a.order_id", "a.company_id", "csos.lasttime",
                "o.cs as city_id", "o.qx as area_id", "o.tel", "o.type_fw"
            ])
            ->group("a.order_id")
            ->buildSql();

        $list = M()->table($subSql)->alias("t")
            ->join("left join qz_quyu as q on q.cid = t.city_id")
            ->join("left join qz_area as area on area.qz_areaid = t.area_id")
            ->field(["t.*", "q.cname as city_name", "area.qz_area as area_name"])
            ->order("t.lasttime desc")
            ->limit($offset, $limit)
            ->select();

        return $list;
    }

    // 获取订单呼叫统计
    public function getOrderCallStats($order_ids){
        $map = array(
            "a.order_id" => array("IN", $order_ids),
            "c.calltype" => array("IN", [10, 11])
        );

        $list = $this->alias("a")->where($map)
            ->join("inner join qz_order_info as i on i.`order` = a.order_id and i.com = a.company_id")
            ->join("inner join qz_pnp_tel_callback as c on c.subid = a.sub_id")
            ->field([
                "a.order_id",
                "count(c.id) as call_num",
                "count(if(c.releasetime > c.starttime, 1, null)) as call_on_num"
            ])
            ->group("a.order_id")
            ->select();

        return $list;
    }

    // 订单绑定记录
    public function getOrderMapBindList($order_id){
        $map = array(
            "a.order_id" => array("EQ", $order_id)
        );

        $list = $this->alias("a")->where($map)
            ->join("inner join qz_order_info as i on i.`order` = a.order_id and i.com = a.company_id")
            ->join("left join qz_pnp_tel_callback as c on c.subid = a.sub_id and c.calltype in (10, 11)")
            ->join("left join qz_user as u on u.id = a.company_id")
            ->field([
                "a.id", "a.sub_id", "a.order_id", "a.tel_b", "a.tel_x", "a.expire_time",
                "a.company_id", "a.employee_id", "u.jc as company_jc", "u.qc as company_qc",
                "count(c.id) as call_num",
                "count(IF(c.releasetime > c.starttime, 1, null)) as call_on_num"
            ])
            ->group("a.id")
            ->order("a.id desc")
            ->select();

        return $list;
    }

    // 录音记录
    public function getOrderMapVoliceList($order_id, $company_id, $sub_id){
        $map = array(
            "a.sub_id" => array("EQ", $sub_id),
            "a.order_id" => array("EQ", $order_id),
            "a.company_id" => array("EQ", $company_id),
            "c.calltype" => array("IN", [10, 11])
        );

        $map["_string"] = "c.releasetime > c.starttime";

        $list = $this->alias("a")->where($map)
            ->join("inner join qz_pnp_tel_callback as c on c.subid = a.sub_id")
            ->field([
                "a.sub_id", "a.order_id", "a.company_id", "c.tel_a", "c.tel_b", 
                "c.calltype", "c.releasedir", "c.record_url", "c.record_url_save",
                "c.created_at"
            ])
            ->order("c.created_at desc")
            ->select();

        return $list;
    }

    
}