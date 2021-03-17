<?php

// +----------------------------------------------------------------------
// | UserCompanyVipStatus 2.0会员状态落档数据模型
// +----------------------------------------------------------------------

namespace app\index\model\adb;

use think\Db;
use think\db\Query;
use app\common\model\adb\BaseModel;

class UserCompanyVipStatus extends BaseModel {

    // 城市会员数量
    public function getCityNewUserCount($date, $city_ids = []){
        $map = new Query();
        $map->where("cooperate_mode", 2);
        $map->where("vip_status", "in", [-1, 2]);
        $map->where("datetime", strtotime($date));

        if (!empty($city_ids)) {
            $map->where("city_id", "in", $city_ids);
        }

        return $this->link()->name("user_company_vip_status")->where($map)
            ->field([
                "city_id",
                "count(if(vip_status = 2, 1, null)) as vip_valid_count",
                "count(if(vip_status = -1, 1, null)) as vip_invalid_count",
            ])
            ->group("city_id")
            ->select();
    }

    // 查询余额不足会员
    public function getCityAmountNotEnoughUserCount($date_begin, $date_end, $city_ids = [], $amount = 3000){
        $map = new Query();
        $map->where("a.cooperate_mode", 2);
        $map->where("a.vip_status", "in", [-1, 2]);
        $map->where("a.account_amount", "<=", $amount);
        $map->where("a.datetime", ">=", strtotime($date_begin));
        $map->where("a.datetime", "<=", strtotime($date_end));

        if (!empty($city_ids)) {
            $map->where("a.city_id", "in", $city_ids);
        }

        $subSql = $this->link()->name("user_company_vip_status")->alias("a")->where($map)
           ->join("qz_user_company_account_log b","a.company_id = b.user_id and b.created_at > a.datetime and b.trade_type = 1", "left")
           ->field([
                "a.company_id", "a.city_id",
                "FROM_UNIXTIME(b.created_at,'%Y%m%d') as pay_date"
            ])
           ->group("a.id")
           ->buildSql();

        $subSql = $this->link()->table($subSql)->alias("t")
            ->field([
                "t.city_id",
                "CONCAT(t.company_id, '-', if(pay_date is null, 'N', pay_date)) as mark"
            ])
            ->buildSql();

        $list = $this->link()->table($subSql)->alias("t")
            ->field(["t.city_id", "count(DISTINCT t.mark) as not_enough_count"])
            ->group("t.city_id")
            ->select();

        return $list;
    }

    /**
     * @des:落档2.0城市会员数量
     * @param array $param
     * @return mixed
     */
    public function getUserAmountV2($param=[])
    {
        $map = new Query();
        $map->where("cooperate_mode", 2);
        $map->where("vip_status", '=',  2);
        if (isset($param['datetime']) && !empty($param['datetime'])) {
            $map->where("datetime", $param['datetime']);
        }
        if (isset($param['cs']) && is_array($param['cs'])) {
            $map->where("city_id", "in", $param['cs']);
        }

        return $this->link()->name("user_company_vip_status")->where($map)
            ->count();
    }

    /**
     * @des:到期2.0会员，时间段内过期的会员
     * @param $date_begin
     * @param $date_end
     * @return mixed
     */
    public function getMatureAmount($date_begin, $date_end)
    {
        $map = new Query();
        $map->where("a.cooperate_mode", 2);
        $map->where("a.vip_status", "=", -1);
        $map->where('a.datetime', '=', $date_end - 86399);
        $map->where(" NOT EXISTS (select * from qz_user_company_vip_status where datetime = " . ($date_begin - 86400) . ") and vip_status=2");

        return $this->link()->name("user_company_vip_status")->alias("a")->where($map)->count();
    }

    /**
     * @des:到期2.0会员，城市时间维度
     * @param $date_begin
     * @param $date_end
     * @return mixed
     */
    public function getMatureCity($date_begin,$date_end)
    {
        $map = new Query();
        $map->where("a.cooperate_mode", 2);
        $map->where("a.vip_status", "=", -1);
        $map->where('a.datetime', '=', $date_end - 86399);
        $map->where(" NOT EXISTS (select * from qz_user_company_vip_status where datetime = " . ($date_begin - 86400) . ") and vip_status=2");
        $subSql = $this->link()->name("user_company_vip_status")->alias("a")->where($map)->buildSql();
        return $this->link()->table($subSql)
            ->field(["count(company_id) as company_amount", "city_id as cs"])
            ->group('city_id')
            ->select();
    }

}