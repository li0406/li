<?php

// +----------------------------------------------------------------------
// | CityOrderLack 城市缺单统计数据模型
// +----------------------------------------------------------------------
// | 城市缺单统计每天中午12:00执行，中午12：00之前查询前一天数据
// +----------------------------------------------------------------------

namespace app\index\model\adb;


use app\common\model\adb\BaseModel;
use think\Db;
use think\db\Query;

class CityOrderLack extends BaseModel
{
    /**
     * @des:会员数落档查询
     * @param array $param
     * @return mixed|null
     */
    public function getUserAmount($param = [])
    {
        $map = new Query();
        $map->where('a.datetime', '=', $param['datetime']);
        if (isset($param['cs']) && !empty($param['cs']) && is_array($param['cs'])) {
            $map->where('a.city_id', 'in', $param['cs']);
        }
        //会有 多城市 汇总 所以需要 sum
       return $this->link()->table('qz_city_order_lack')->alias('a')->where($map)
            ->field(["sum(a.vip_num) amount1,sum(a.vip_signback_num) amount2, sum(a.vip_total_num) amount"])
            ->find();
    }

    // 获取城市缺单统计数据
    public function getCityOrderLackDateInfo($date, $city_ids = []){
        $map = new Query();
        $map->where("datetime", strtotime($date));

        if (!empty($city_ids)){
            $map->where("city_id", "in", $city_ids);
        }

        return $this->link()->name("city_order_lack")->where($map)
            ->field([
                "city_id", "datetime",
                "vip_total_num", "vip_total_count",
                "predict_whole_month_orders"
            ])
            ->select();
    }

    /**
     * @des:获取某个时间的城市会员数，区分城市
     * @param array $param
     * @return mixed
     */
    public function getUserAmountByTime($param = [])
    {
        $map = new Query();
        $map->where('a.datetime', '=', $param['datetime']);
        if (isset($param['cs']) && !empty($param['cs']) && is_array($param['cs'])) {
            $map->where('a.city_id', 'in', $param['cs']);
        }
        return $this->link()->table('qz_city_order_lack')->alias('a')
            ->where($map)
            ->field(["vip_num", "vip_signback_num", "vip_total_num", "vip_count", "vip_total_count", "city_id"])
            ->select();
    }


}