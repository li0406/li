<?php

namespace Home\Model\Db;

use Think\Model;

class LogClinkReviewTelcenterModel extends Model
{
    public function addInfo($data)
    {
        return $this->add($data);
    }

    public function getOrderTelLogById($order_id, $adminIds = [])
    {
        $map = [
            "a.order_id" => ["EQ",$order_id]
        ];

        if (!empty($adminIds)) {
            $map["a.op_uid"] = array("IN", $adminIds);
        }

        return $this->alias("a")->where($map)
            ->join("join qz_log_clink_telcenter b on a.call_sid = b.call_sid")
            ->field("a.op_uname,a.call_sid,b.created_at,b.cdr_status,cdr_customer_number,cust_callee_clid,cdr_bridge_time")
            ->order("a.id desc")
            ->select();
    }

    // 获取天润电话统计
    public function getClinkReviewTelStat($id, $group, $monthStart, $monthEnd, $manager, $userids){
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
            ->join("left join qz_log_clink_review_telcenter b on b.op_uid = a.id and b.time >= $monthStart and b.time < $monthEnd")
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

    public function getCustomerMediumTelStat($begin,$end,$kf,$group)
    {
        $map = array(
            "a.time" => array(
                array("EGT",$begin),
                array("ELT",$end)
            )
        );

        if (!empty($kf)) {
            $map["a.op_uid"] = array("EQ",$kf);
        }

        if (!empty($group)) {
            $map["u.kfgroup"] = array("EQ",$group);
        }

        $buildSql = $this->where($map)->alias("a")
            ->join("join qz_order_review_new r on r.order_id = a.order_id")
            ->join("join qz_adminuser u on u.id = a.op_uid")
            ->field('a.order_id,a.op_uid,a.op_uname,FROM_UNIXTIME(a.time,"%Y-%m-%d") as date')->buildSql();

        $buildSql = $this->table($buildSql)->alias("t")
                         ->field("t.op_uid,t.op_uname,t.date,t.order_id,count(t.order_id) as day_tel_count")
                         ->group("t.order_id,t.date,t.op_uid")->buildSql();
        return $this->table($buildSql)->alias("t")
                    ->group("t.op_uid")->field("t.op_uid,t.op_uname,COUNT(DISTINCT t.order_id,t.date) as day_order_count,count(DISTINCT t.order_id) as order_count,SUM(day_tel_count) as sum_count")
                    ->select();
    }

    public function getCustomerMediumOldTelStat($begin,$end,$kf,$group)
    {
        $map = array(
            "a.time" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            "r.is_delete" => array("EQ",1),
        );

        if (!empty($kf)) {
            $map["a.op_uid"] = array("EQ",$kf);
        }

        if (!empty($group)) {
            $map["u.kfgroup"] = array("EQ",$group);
        }

        $buildSql = $this->where($map)->alias("a")
            ->join("join qz_order_review r on r.order_id = a.order_id")
            ->join("join qz_adminuser u on u.id = a.op_uid")
            ->field('a.order_id,a.op_uid,a.op_uname,FROM_UNIXTIME(a.time,"%Y-%m-%d") as date')->buildSql();

        $buildSql = $this->table($buildSql)->alias("t")
            ->field("t.op_uid,t.op_uname,t.date,t.order_id,count(t.order_id) as day_tel_count")
            ->group("t.order_id,t.date,t.op_uid")->buildSql();

        return $this->table($buildSql)->alias("t")
            ->group("t.op_uid")->field("t.op_uid,t.op_uname,COUNT(DISTINCT t.order_id,t.date) as day_order_count,count(DISTINCT t.order_id) as order_count,SUM(day_tel_count) as sum_count")
            ->select();
    }


    public function getCustomerMediumTelTimeStat($begin,$end,$kf,$group)
    {
        $map = array(
            "a.time" => array(
                array("EGT",$begin),
                array("ELT",$end)
            )
        );

        if (!empty($kf)) {
            $map["a.op_uid"] = array("EQ",$kf);
        }

        if (!empty($group)) {
            $map["u.kfgroup"] = array("EQ",$group);
        }

        $buildSql = $this->where($map)->alias("a")
                         ->join("join qz_adminuser u on u.id = a.op_uid")
                         ->join("join qz_log_clink_telcenter t on t.call_sid = a.call_sid")
                         ->field("a.op_uid,a.op_uname,if(t.cdr_bridge_time = 0,0,t.cdr_end_time - t.cdr_bridge_time) as call_time")
                         ->buildSql();
        return $this->table($buildSql)->alias("t")
                         ->field("t.op_uid,t.op_uname,count(if(call_time > 0,1,null)) as call_count,sum(call_time) as call_time")
                         ->group("t.op_uid")->select();
    }

    public function getCityByTelCount($begin,$end,$city)
    {
        $map = array(
            "a.addtime" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            "o.type_fw" => array("EQ",1),
            "o.on" => array("EQ",4)
        );

        if (!empty($city)) {
            $map["o.cs"] = array("EQ",$city);
        }

        $buildSql = M("order_info")->where($map)->group("a.order")->alias("a")
            ->join("join qz_orders o on o.id = a.order")
            ->group("a.order")
            ->field("a.order,o.cs")
            ->buildSql();


        $buildSql = $this->table($buildSql)->alias("a")
                         ->join("join qz_log_clink_review_telcenter r on r.order_id = a.order")
                         ->join("join qz_log_clink_telcenter t on t.call_sid = r.call_sid")
                         ->join("join qz_orders o on a.order = o.id")
                         ->field("a.cs,a.order as order_id,if(t.cdr_bridge_time = 0,0,t.cdr_end_time - t.cdr_bridge_time) as call_time")
                         ->buildSql();

        $buildSql = $this->table($buildSql)->alias("a")
                         ->field("a.cs,a.order_id,count(if(call_time > 0,1,null)) as call_count")
                         ->group("a.order_id")
                         ->buildSql();

        return $this->table($buildSql)->alias("t")
                    ->join("join qz_quyu q on q.cid = t.cs")
                    ->field("q.cname,q.cid,count(t.order_id) as tel_count,count(if(call_count > 0,1,null)) as call_count")
                    ->group("t.cs")
                    ->select();
    }
}