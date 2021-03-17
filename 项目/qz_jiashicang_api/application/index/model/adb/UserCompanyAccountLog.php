<?php


namespace app\index\model\adb;


use app\common\model\adb\BaseModel;
use think\Db;
use think\db\Query;

class UserCompanyAccountLog extends BaseModel
{
    // 2.0 累计消耗金额
    public function getCityRoundAmount($date_begin, $date_end, $city_ids = []){
        $map = new Query();
        $map->where("a.account_type", 1);
        $map->where("a.trade_type", "in", [3, 7]);
        $map->where("a.created_at", ">=", strtotime($date_begin));
        $map->where("a.created_at", "<=", strtotime($date_end) + 86399);

        if (!empty($city_ids)) {
            $map->where("u.cs", "in", $city_ids);
        }

        return $this->link()->name("user_company_account_log")->alias("a")->where($map)
            ->join("user u","u.id = a.user_id", "left")
            ->field([
                "u.cs as city_id",
                "count(IF(a.trade_type = 3, 1, null)) as round_num",
                "sum(IF(a.trade_type = 3, a.trade_amount, 0)) as round_amount",
                "count(IF(a.trade_type = 7, 1, null)) as round_foul_num",
                "sum(IF(a.trade_type = 7, a.trade_amount, 0)) as round_foul_amount",
            ])
            ->group("u.cs")
            ->select();
    }

    // 查询城市充值用户
    public function getCityAccountRechargeUserCount($date_begin, $date_end, $city_ids = []){
        $map = new Query();
        $map->where("a.trade_type", 1);
        $map->where("a.account_type", 1);
        $map->where("a.created_at", ">=", strtotime($date_begin));
        $map->where("a.created_at", "<=", strtotime($date_end) + 86399);

        if (!empty($city_ids)) {
            $map->where("u.cs", "in", $city_ids);
        }

        $logSql = $this->link()->name("user_company_account_log")
            ->field(["user_id", "min(created_at) as created_at"])
            ->where("trade_type", 1)->group("user_id")
            ->buildSql();

        $subSql = $this->link()->name("user_company_account_log")->alias("a")->where($map)
            ->join("user u", "u.id = a.user_id", "inner")
            ->join("{$logSql} b", "b.user_id = a.user_id", "left")
            ->field([
                "a.user_id", "u.cs as city_id",
                "FROM_UNIXTIME(a.created_at,'%Y%m') as now_at",
                "FROM_UNIXTIME(b.created_at,'%Y%m') as first_at"
            ])
            ->having("first_at <> now_at")
            ->buildSql();

        $list = $this->link()->table($subSql)->alias("t")
            ->field(["t.city_id", "count(DISTINCT t.user_id) AS recharge_count"])
            ->group("t.city_id")
            ->select();

        return $list;
    }

    // 查询2.0会员消耗、违规补轮次数、违规补轮金额
    public function getCompanyRoundAmount($date_begin, $date_end, $company_ids = []){
        $map = new Query();
        $map->where("a.account_type", 1);
        $map->where("a.trade_type", "in", [3, 7]);
        $map->where("a.created_at", ">=", strtotime($date_begin));
        $map->where("a.created_at", "<=", strtotime($date_end) + 86399);

        if (!empty($company_ids)) {
            $map->where("a.user_id", "in", $company_ids);
        }

        return $this->link()->name("user_company_account_log")->alias("a")->where($map)
            ->field([
                "a.user_id as company_id",
                "count(IF(a.trade_type = 3, 1, null)) as round_num",
                "sum(IF(a.trade_type = 3, a.trade_amount, 0)) as round_amount",
                "count(IF(a.trade_type = 7, 1, null)) as round_foul_num",
                "sum(IF(a.trade_type = 7, a.trade_amount, 0)) as round_foul_amount",
            ])
            ->group("a.user_id")
            ->select();
    }

    /**
     * @des:2.0会员续费
     * @param $date_begin
     * @param $date_end
     * @return mixed
     */
    public function getRenewCompanyAmount($date_begin, $date_end)
    {
        $map = new Query();
        $map->where("a.trade_type", 1);
        $map->where("a.account_type", 1);
        $map->where("u.classid", "in", [3, 6]);
        $map->where('a.created_at','between',[$date_begin,$date_end]);

        $logSql = $this->link()->name("user_company_account_log")
            ->field(["user_id", "min(created_at) as created_at"])
            ->where("trade_type", 1)
            ->group("user_id")
            ->buildSql();
        $subSql = $this->link()->name("user_company_account_log")->alias("a")->where($map)
            ->join("user u", "u.id = a.user_id", "inner")
            ->join("{$logSql} b", "b.user_id = a.user_id", "left")
            ->field([
                "a.user_id", "u.cs as city_id",
                "FROM_UNIXTIME(a.created_at,'%Y%m') as now_at",
                "FROM_UNIXTIME(b.created_at,'%Y%m') as first_at"
            ])
            ->having("first_at <> now_at")
            ->buildSql();

        return $this->link()->table($subSql)->alias("t")
            ->group('t.user_id')
            ->count();
    }

    /**
     * @des:2.0会员续费 按月分组
     * @param $date_begin
     * @param $date_end
     * @return mixed
     */
    public function getRenewAnalysis($date_begin, $date_end)
    {
        $map = new Query();
        $map->where("a.trade_type", 1);
        $map->where("a.account_type", 1);
        $map->where('a.created_at','between',[$date_begin,$date_end]);

        $logSql = $this->link()->name("user_company_account_log")
            ->field(["user_id", "min(created_at) as created_at"])
            ->where("trade_type", 1)
            ->group("user_id")
            ->buildSql();
        $subSql = $this->link()->name("user_company_account_log")->alias("a")->where($map)
            ->join("user u", "u.id = a.user_id", "inner")
            ->join("{$logSql} b", "b.user_id = a.user_id", "left")
            ->field([
                "a.user_id", "u.cs as city_id",
                "FROM_UNIXTIME(a.created_at,'%Y%m') as now_at",
                "FROM_UNIXTIME(b.created_at,'%Y%m') as first_at"
            ])
            ->having("first_at <> now_at")
            ->buildSql();

        return $this->link()->table($subSql)->alias("t")
            ->field(["count(distinct(t.user_id)) as amount,t.now_at"])
            ->group("t.now_at")
            ->select();
    }

}