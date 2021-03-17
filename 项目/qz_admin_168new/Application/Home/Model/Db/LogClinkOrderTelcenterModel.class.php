<?php

namespace Home\Model\Db;

use Think\Model;

class LogClinkOrderTelcenterModel extends Model {

    public function addInfo($data)
    {
        return $this->add($data);
    }

    public function getOrderTelLogById($order_id)
    {
         $map = [
             "a.order_id" => ["EQ",$order_id]
         ];
         return $this->where($map)->alias("a")
            ->join("join qz_log_clink_telcenter b on a.call_sid = b.call_sid")
            ->field("a.op_uname,a.call_sid,b.created_at,b.cdr_status,cdr_customer_number,cust_callee_clid,cdr_bridge_time,cdr_end_time")
            ->order("a.id desc")
            ->select();
    }

    // 获取天润电话统计
    public function getClinkOrderTelStat($id, $group, $monthStart, $monthEnd, $manager, $userids){
        $map = array(
            "a.`uid`" => array("EQ", 2),
            "a.`stat`" => array("EQ", 1),
            "a.`state`" => array("EQ", 1)
        );

        if (!empty($id)) {
            $map["a.id"] = array("EQ", $id);
        }

        if (!empty($group)) {
            $map["a.kfGroup"] = array("EQ", $group);
        }

        if (!empty($manager)) {
            $map["a.kfmanager"] = array("EQ", $manager);
        }

        if (!empty($userids)) {
            $map["a.kfmanager"] = array("IN", $userids);
        }

        return M("adminuser")->alias("a")->where($map)
            ->join("left join qz_log_clink_order_telcenter b on b.op_uid = a.id and b.time >= $monthStart and b.time < $monthEnd")
            ->join("left join qz_log_clink_telcenter c on b.call_sid = c.call_sid and c.created_at >= $monthStart and c.created_at < $monthEnd")
            ->field([
                "a.id", "a.`name`",
                "count(IF(c.call_sid is not null, 1, null)) as count",
                "count(IF(c.call_sid is not null and c.cdr_bridge_time > 0 and c.cdr_end_time > 0, 1, null)) as tel_count",
                "SUM(if(c.call_sid is not null and c.cdr_bridge_time > 0 and c.cdr_end_time > 0, c.cdr_end_time - c.cdr_bridge_time, 0)) as sum_time"
            ])
            ->group("a.id")
            ->select();
    }
}