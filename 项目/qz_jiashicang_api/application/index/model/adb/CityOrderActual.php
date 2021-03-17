<?php

// +----------------------------------------------------------------------
// | CityOrderActual 城市缺单统计:每月实际所需分单数据模型
// +----------------------------------------------------------------------

namespace app\index\model\adb;

use think\Db;
use think\db\Query;
use app\common\model\adb\BaseModel;

class CityOrderActual extends BaseModel {

    public function getCityMonthList($month, $city_ids = []){
        $map = new Query();
        $map->where("datetime", strtotime($month));

        if (!empty($city_ids)) {
            $map->where("city_id", "in", $city_ids);
        }

        return $this->link()->name("city_order_actual")->where($map)
            ->field([
                "city_id", "datetime", "actual_orders"
            ])
            ->select();
    }

}