<?php

// +----------------------------------------------------------------------
// | JiajuUserVip 家具会员合同数据模型
// +----------------------------------------------------------------------

namespace app\index\model\adb;

use think\Db;
use think\db\Query;
use app\common\model\adb\BaseModel;

class JiajuUserVip extends BaseModel {

    // 查询城市全屋定制会员数量
    public function getCityQuanwuUserCount($date, $city_ids = []){
        $map = new Query();
        $map->where("a.start_time", "<=", $date);
        $map->where("a.end_time", ">=", $date);
        $map->where("a.type", "in", [2, 8]);
        $map->where("u.classid", 4);
        $map->where("uc.fake", 0);

        $map->where("a.company_id", "not in", function($query) use ($date) {
            $query->table("qz_jiaju_user_vip")
                ->where("start_time", "<=", $date)
                ->where("end_time", ">=", $date)
                ->where("type", 4)
                ->field(["company_id"]);
        });

        if (!empty($city_ids)) {
            $map->where("u.cs", "in", $city_ids);
        }

        $subSql =  $this->link()->name("jiaju_user_vip")->alias("a")->where($map)
            ->join("user u", "u.id = a.company_id", "inner")
            ->join("jiaju_user_company uc", "uc.company_id = u.id", "inner")
            ->field([
                "u.cs as city_id", "a.company_id", "a.viptype"
            ])
            ->group("a.company_id")
            ->buildSql();

        return $this->link()->table($subSql)->alias("t")
            ->field(["t.city_id", "count(t.company_id) as vip_count", "sum(t.viptype) as vip_num"])
            ->group("t.city_id")
            ->select();
    }

}