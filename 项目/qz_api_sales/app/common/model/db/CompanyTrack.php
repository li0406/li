<?php

namespace app\common\model\db;

use think\Model;
use think\db\Query;

class CompanyTrack extends Model {

    public function getOrderTrackList($order_id){
        $map = new Query();
        $map->where("t.order_id", $order_id);

        return $this->alias("t")->where($map)
            ->join("user u", "u.id = t.company_id", "inner")
            ->field("t.*,u.qc as company_qc,u.jc as company_jc")
            ->order("t.track_time desc")
            ->select();
    }

    public function getTrackCountByOrderIds($orderIds){
        $map = new Query();
        $map->where("order_id", "IN", $orderIds);

        return $this->where($map)
            ->field("order_id,count(id) as count")
            ->group("order_id")
            ->select();
    }
}