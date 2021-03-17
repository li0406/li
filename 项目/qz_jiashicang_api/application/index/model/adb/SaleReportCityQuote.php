<?php

// +----------------------------------------------------------------------
// | SaleReportCityQuote 城市报价数据模型
// +----------------------------------------------------------------------

namespace app\index\model\adb;

use think\Db;
use think\db\Query;

use app\common\model\adb\BaseModel;

class SaleReportCityQuote extends BaseModel {

    // 获取城市报价
    public function getCityQuoteList($date, $city_ids = []){
        $map = new Query();
        $map->where("a.is_delete", 1);
        $map->where("a.create_time", "<=", strtotime($date) + 86399);

        if (!empty($city_ids)){
            $map->where("q.cid", "in", $city_ids);
        }

        $subSql = $this->link()->name("sale_report_city_quote")->alias("a")->where($map)
            ->join("quyu q", "q.cname = a.city_name", "inner")
            ->field([
                "q.cid as city_id", "max(a.id) as quote_id"
            ])
            ->group("q.cid")
            ->buildSql();

        return $this->link()->table($subSql)->alias("t")
            ->join("sale_report_city_quote a", "a.id = t.quote_id")
            ->field([
                "t.city_id", "a.round_order_money", "a.year_quote"
            ])
            ->select();
    }
}