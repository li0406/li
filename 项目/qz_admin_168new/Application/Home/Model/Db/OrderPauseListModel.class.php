<?php

namespace Home\Model\Db;

use Think\Model;
class OrderPauseListModel extends Model
{
    public function addInfo($data)
    {
        return $this->add($data);
    }

    public function getPauseSummary($begin,$end)
    {
        $map = [
            "pause_time" => [
                ["EGT",$begin],
                ["ELT",$end]
            ]
        ];

        $buildSql = $this->where($map)->order("id desc")->buildSql();

        $buildSql = $this->table($buildSql)->alias("t")->group("t.order_id")
                         ->field("t.order_id,round(t.undetermined_day/30,2) as month")->buildSql();

        return $this->table($buildSql)->alias("t")
                    ->join("join qz_orders o on o.id = t.order_id")
                    ->field("count(t.order_id) as all_count,count(if(o.on = 97,1,null)) as pause_count,count(if(o.on = 4 and o.type_fw = 1,1,null)) as fen_count,count(if(o.on = 4 and o.type_fw = 2,1,null)) as zen_count,
                            count(if(o.on = 5,1,null)) as wx_count,count(if(t.month < 1,1,null)) as step_1,count(if(t.month >=1 and t.month < 3,1,null)) as step_2,count(if(t.month >= 3 and t.month < 6,1,null)) as step_3,count(if(t.month >= 6,1,null)) as step_4 ")
                    ->find();
    }
}