<?php

namespace Home\Model\Db;

use Think\Model;

class CpaOrderDistanceModel extends Model
{

    public function addAllDistance($data)
    {
        return M("cpa_order_distance")->addAll($data);
    }

    public function delAllDistance($order_id)
    {
        $map = array(
            "order_id" => array("EQ",$order_id)
        );
        return M("cpa_order_distance")->where($map)->delete();
    }
}
