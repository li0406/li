<?php

namespace Home\Model\Db;

use Think\Model;
class OrderPausePoolModel extends Model
{
    public function addAllInfo($data)
    {
        return $this->addAll($data);
    }

    public function getPauseOrderCount($order_id,$visittime)
    {
        $map = [
            "order_id" => ["EQ",$order_id],
            "visitime" => ["EQ",$visittime]
        ];
        return $this->where($map)->count();
    }

    public function delPauseOrder($order_id)
    {
        $map = [
            "order_id" => ["EQ",$order_id]
        ];
        return $this->where($map)->delete();
    }

    public function getPauseListCountByStatus($ids,$status)
    {
        $map = [
            "a.op_uid" => ["IN",$ids],
            "o.on" => array("EQ",97),
            "status" => ["EQ",$status]
        ];
        $buildSql =  $this->where($map)->group("a.order_id")->alias("a")
                     ->join("join qz_orders o on o.id = a.order_id")->field('a.id')->buildSql();
        return $this->table($buildSql)->alias("a")->count();
    }

    public function getPauseOrderPoolListCount($ids,$condition,$start,$end,$time_real_start,$time_real_end,$city,$area,$remarks,$op_uid,$source,$orderlx)
    {
        $map = [
            "a.op_uid" => ["IN",$ids],
            "o.on" => array("EQ",97)
        ];

        if (!empty($condition)) {
            $map["_complex"] = [
                "o.id" => ["EQ",$condition],
                "o.xiaoqu" => ["LIKE","%".$condition."%"],
                "o.tel_encrypt" => ["EQ",order_tel_encrypt($condition)],
                "_logic" => "OR",
            ];
        }

        if (!empty($start) && !empty($end)) {
            $map["o.time"] = [
                ["EGT",strtotime($start)],
                ["ELT",strtotime($end) + 86400]
            ];
        }

        if (!empty($time_real_start) && !empty($time_real_end)) {
            $map["o.time_real"] = [
                ["EGT",strtotime($time_real_start)],
                ["ELT",strtotime($time_real_end) + 86400]
            ];
        }

        if (!empty($city)) {
            $map["o.cs"] = ["EQ",$city];
        }


        if (!empty($area)) {
            $map["o.qx"] = ["EQ",$area];
        }

        if (!empty($remarks)) {
            $map["o.remarks"] = ["EQ",$remarks];
        }

        if (!empty($op_uid)) {
            $map["a.op_uid"] = ["EQ",$op_uid];
        }

        if (!empty($source)) {
            $map["o.source_in"] = ["EQ",$source];
        }

        if (!empty($orderlx)) {
            $map['o.lxs'] = array('EQ', $orderlx);
        }

        $buildSql = $this->where($map)->group("a.order_id")->alias("a")
                         ->join("join qz_orders o on o.id = a.order_id")->field("a.id")
                         ->buildSql();
        return $this->table($buildSql)->alias("t")->count();

    }

    public function getPauseOrderPoolList($ids,$condition,$start,$end,$time_real_start,$time_real_end,$city,$area,$remarks,$op_uid,$source,$orderlx,$pageIndex,$pageCount)
    {
        $map = [
            "op_uid" => ["IN",$ids],
        ];

        if (!empty($op_uid)) {
            $map["op_uid"] = ["EQ",$op_uid];
        }

        $buildSql = $this->where($map)->order("find_in_set(status,'2,1,3'),appointed_time,order_id")->buildSql();
        $buildSql = $this->table($buildSql)->alias("t")->field("t.*,max(appointed_time) as max_appointed_time")
                         ->group("t.order_id")->order(" find_in_set(status,'2,1,3'),max_appointed_time desc,order_id")->buildSql();

        $where = [
            "o.on" => array("EQ",97)
        ];
        if (!empty($condition)) {
            $where["_complex"] = [
                "o.id" => ["EQ",$condition],
                "o.xiaoqu" => ["LIKE","%".$condition."%"],
                "o.tel_encrypt" => ["EQ",order_tel_encrypt($condition)],
                "_logic" => "OR",
            ];
        }

        if (!empty($start) && !empty($end)) {
            $where["o.time"] = [
                ["EGT",strtotime($start)],
                ["ELT",strtotime($end) + 86400]
            ];
        }

        if (!empty($time_real_start) && !empty($time_real_end)) {
            $where["o.time_real"] = [
                ["EGT",strtotime($time_real_start)],
                ["ELT",strtotime($time_real_end) + 86400]
            ];
        }

        if (!empty($city)) {
            $where["cs"] = ["EQ",$city];
        }


        if (!empty($area)) {
            $where["o.qx"] = ["EQ",$area];
        }

        if (!empty($remarks)) {
            $where["o.remarks"] = ["EQ",$remarks];
        }



        if (!empty($source)) {
            $where["o.source_in"] = ["EQ",$source];
        }

        if (!empty($orderlx)) {
            $where['o.lxs'] = array('EQ', $orderlx);
        }

        return $this->where($where)->table($buildSql)->alias("t")
                         ->join("join qz_orders o on o.id = t.order_id")
                         ->join("join qz_quyu q on q.cid = o.cs")
                         ->join("join qz_area area on area.qz_areaid = o.qx")
                         ->field("o.visitime,t.owner_name,t.max_appointed_time, t.order_id ,t.status,t.step,t.appointed_time,o.time,o.remarks,q.cname as city,area.qz_area as area,o.mianji,o.tel,o.wzd,o.on,o.time_real,o.lasttime")
                         ->limit($pageIndex,$pageCount)->select();
    }

    public function getOrderPauseInfo($order_id)
    {
        $map = [
            "order_id" => ["EQ",$order_id],
            "appointed_time" => ["ELT",date("Y-m-d")],
        ];

        return $this->where($map)->field("max(id) id,max(step) step")->find();
    }

    public function pauseOrderUp($id,$data)
    {
        $map = [
            "id" => ["EQ",$id],
        ];
        return $this->where($map)->save($data);
    }
    public function getOrderMaxPauseInfo($order_id)
    {
        $map = [
            "order_id" => ["EQ",$order_id]
        ];

        return $this->where($map)->field("max(step) step")->find();
    }

    public function getPauseOrderStat($kf,$group,$manager,$state)
    {
        $buildSql = $this->group("order_id")->field("order_id,owner_id")->buildSql();

        $buildSql = $this->table($buildSql)->alias("t")
                         ->join("join qz_orders o on t.order_id = o.id")
                         ->group("t.owner_id")
                         ->field("t.owner_id,count(t.order_id) as all_count,count(if(o.on = 97,1,null)) as pause_count,count(if(o.on = 4 and o.type_fw = 1,1,null)) as fen_count,count(if(o.on = 4 and o.type_fw = 2,1,null)) as zen_count,count(if(o.on = 5,1,null)) as wx_count")->buildSql();

        $map["u.stat"] = ["EQ",1];
        $map["u.state"] = ["EQ",1];
        if (!empty($kf)) {
            $map["t.owner_id"] = ["EQ",$kf];
        }

        if (!empty($group)) {
            $map["u.kfgroup"] = ["EQ",$group];
        }

        if (!empty($manager)) {
            $map["u2.id"] = ["EQ",$manager];
        }

        if (!empty($state)) {
            if ($state == 2) {
                $state = 0;
            }
            $map["u.state"] = ["EQ",$state];
            if ($state == 3) {
               unset($map["u.state"]);
            }
        }
        return $this->table($buildSql)->where($map)->alias("t")
                    ->join("join qz_adminuser u on u.id = t.owner_id")
                    ->join("join qz_adminuser u1 on u1.kfgroup = u.kfgroup and u1.uid = 31 and u1.stat = 1 and u1.state = 1")
                    ->join("join qz_adminuser u2 on u2.id = substring_index(u.kfmanager,',',1)")
                    ->field("t.*,u.name,u1.name as group_name,u2.name as manager_name")->select();
    }
}