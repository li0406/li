<?php

// +----------------------------------------------------------------------
// | OrderAllotInfo 订单分配信息数据模型
// +----------------------------------------------------------------------

namespace app\index\model\adb;

use think\Db;
use think\db\Query;
use app\common\model\adb\BaseModel;

class OrderAllotInfo extends BaseModel {

    public function getCityOrderAllotList($date_begin, $date_end, $city_ids = []){

        $map = new Query();
        $map->where("o.on", 4);
        $map->where("o.type_fw", 1);
        $map->where("csos.lasttime",">=", strtotime($date_begin));
        $map->where("csos.lasttime","<=", strtotime($date_end) + 86399);

        if (!empty($city_ids)) {
            $map->where("o.cs", "in", $city_ids);
        }

        $list = $this->link()->name("orders")->alias("o")->where($map)
            ->join("order_csos_new csos", "csos.order_id = o.id", "inner")
            ->join("order_allot_info allot", "allot.order_id = o.id", "inner")
            ->field([
                "o.cs as city_id",
                "sum(if(allot.city_vip_num >=4, 4, allot.city_vip_num)) as allot_max_num",
                "sum(allot.allot_fen_num) as allot_fen_num",
                "sum(allot.allot_fen_user_num) as allot_fen_user_num",
                "sum(allot.allot_fen_newuser_num) as allot_fen_newuser_num",
            ])
            ->group("o.cs")
            ->select();

        return $list;
    }

}