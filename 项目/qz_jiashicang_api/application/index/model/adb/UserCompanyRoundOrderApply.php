<?php
// +----------------------------------------------------------------------
// | UserCompanyRoundOrderApply 补轮申请数据模型
// +----------------------------------------------------------------------

namespace app\index\model\adb;

use think\Db;
use think\db\Query;
use app\common\model\adb\BaseModel;

class UserCompanyRoundOrderApply extends BaseModel
{
   public function getOrderApplyCount($begin,$end,$city)
   {
       $map[] = ["t.created_at","EGT",$begin];
       $map[] = ["t.created_at","LT",$end];

       if (count($city) > 0) {
           $map[] = ["o.cs","IN",$city];
       }

       $buildSql = $this->link()->table("qz_user_company_round_order_apply")->group("order_id")
            ->field("min(created_at) as created_at,order_id")->buildSql();

       return $this->link()->table($buildSql)->alias("t")->where($map)
                    ->join("qz_orders o","o.id = t.order_id")
           ->count();
   }

   public function getApplyPassOrderCount($begin,$end,$city)
   {
       $map1[] = ["exam_status","EQ",2];

       $buildSql = $this->link()->table("qz_user_company_round_order_apply")->where($map1)->group("order_id")
           ->field("min(exam_time) as created_at,order_id")->buildSql();

       $map[] = ["t.created_at","EGT",$begin];
       $map[] = ["t.created_at","LT",$end];
       if (count($city) > 0) {
           $map[] = ["o.cs","IN",$city];
       }
       return $this->link()->table($buildSql)->alias("t")->where($map)
           ->join("qz_orders o","o.id = t.order_id")
           ->count();
   }


    public function getApplyPassFull($begin,$end,$city)
    {
        $map1[] = ["a.exam_status","EQ",2];
        if (count($city) > 0) {
            $map1[] = ["o.cs","IN",$city];
        }

        $buildSql = $this->link()->table("qz_user_company_round_order_apply")->where($map1)->alias("a")->group("a.order_id")
            ->join("qz_orders o","o.id = a.order_id")
            ->field("min(a.exam_time) as created_at,a.order_id")->buildSql();

        $map[] = ["t.created_at","EGT",$begin];
        $map[] = ["t.created_at","LT",$end];

        $list = $this->link()->table($buildSql)->alias("t")->where($map)
            ->field("FROM_UNIXTIME(t.created_at,'%m') as month,count(distinct t.order_id) as count")
            ->group("month")
            ->select();
            
        return $list;
    }

    // 获取城市订单补轮统计
    public function getCityOrderApplyStatistic($date_begin, $date_end, $city_ids = []){
        $map = new Query();
        $map->where("a.created_at", ">=", strtotime($date_begin));
        $map->where("a.created_at", "<=", strtotime($date_end) + 86399);

        if (!empty($city_ids)) {
            $map->where("a.city_id", "in", $city_ids);
        }

        $subSql = $this->link()->name("orders")->alias("o")
            ->join("order_info i", "i.order = o.id")
            ->join("user_company_round_order r", "r.order_id = i.`order` and r.user_id = i.com", "inner")
            ->join("user_company_round_order_apply a", "a.order_id = r.order_id and a.company_id = r.user_id", "inner")
            ->field(["o.cs as city_id", "a.order_id", "min(a.created_at) as created_at"])
            ->group("a.order_id")
            ->buildSql();

        $list = $this->link()->table($subSql)->alias("a")->where($map)
            ->field(["a.city_id", "count(a.order_id) as apply_order_num"])
            ->group("a.city_id")
            ->select();

        return $list;
    }

    // 获取城市订单分配公司补轮统计
    public function getCityCompanyApplyStatistic($date_begin, $date_end, $city_ids = []){
        $map = new Query();
        $map->where("a.created_at", ">=", strtotime($date_begin));
        $map->where("a.created_at", "<=", strtotime($date_end) + 86399);

        if (!empty($city_ids)) {
            $map->where("o.cs", "in", $city_ids);
        }

        $list = $this->link()->name("orders")->alias("o")->where($map)
            ->join("order_info i", "i.order = o.id")
            ->join("user_company_round_order r", "r.order_id = i.`order` and r.user_id = i.com", "inner")
            ->join("user_company_round_order_apply a", "a.id = r.apply_id", "inner")
            ->field(["o.cs as city_id", "count(a.id) as apply_company_num"])
            ->group("o.cs")
            ->select();

        return $list;
    }

    // 补轮量：第一次补轮通过时间在查询时间段内的审核通过的补轮单；（，按照客服操作时间进行统计，订单维度）
    public function getCityOrderApplyPassStatistic($date_begin, $date_end, $city_ids = []){
        $map = new Query();
        $map->where("a.exam_time", ">=", strtotime($date_begin));
        $map->where("a.exam_time", "<=", strtotime($date_end) + 86399);

        if (!empty($city_ids)) {
            $map->where("a.city_id", "in", $city_ids);
        }

        $subSql = $this->link()->name("orders")->alias("o")
            ->join("order_info i", "i.order = o.id")
            ->join("user_company_round_order r", "r.order_id = i.`order` and r.user_id = i.com", "inner")
            ->join("user_company_round_order_apply a", "a.id = r.apply_id and a.exam_status = 2", "inner")
            ->field(["o.cs as city_id", "a.order_id", "min(a.exam_time) as exam_time"])
            ->group("a.order_id")
            ->buildSql();

        $list = $this->link()->table($subSql)->alias("a")->where($map)
            ->field(["a.city_id", "count(a.order_id) as apply_pass_num"])
            ->group("a.city_id")
            ->select();

        return $list;
    }

    // 补轮量：第一次补轮通过时间在查询时间段内的审核通过的补轮单；（，按照客服操作时间进行统计，订单维度）
    public function getCityCompanyApplyPassStatistic($date_begin, $date_end, $city_ids = []){
        $map = new Query();
        $map->where("a.exam_time", ">=", strtotime($date_begin));
        $map->where("a.exam_time", "<=", strtotime($date_end) + 86399);

        if (!empty($city_ids)) {
            $map->where("o.cs", "in", $city_ids);
        }

        $list = $this->link()->name("orders")->alias("o")->where($map)
            ->join("order_info i", "i.order = o.id")
            ->join("user_company_round_order r", "r.order_id = i.`order` and r.user_id = i.com", "inner")
            ->join("user_company_round_order_apply a", "a.id = r.apply_id and a.exam_status = 2", "inner")
            ->field([
                "o.cs as city_id",
                "count(a.id) as apply_company_pass_num",
                "sum(r.round_money) as round_pass_money"
            ])
            ->group("o.cs")
            ->select();

        return $list;
    }

    // 补轮驳回单量：第一次审核不通过时间在查询时间段内的补轮不通过单；（按照客服操作时间进行统计，订单维度）
    public function getCityOrderApplyUnpassStatistic($date_begin, $date_end, $city_ids = []){
        $map = new Query();
        $map->where("a.exam_time", ">=", strtotime($date_begin));
        $map->where("a.exam_time", "<=", strtotime($date_end) + 86399);

        if (!empty($city_ids)) {
            $map->where("a.city_id", "in", $city_ids);
        }

        $subSql = $this->link()->name("orders")->alias("o")
            ->join("order_info i", "i.order = o.id")
            ->join("user_company_round_order r", "r.order_id = i.`order` and r.user_id = i.com", "inner")
            ->join("user_company_round_order_apply a", "a.id = r.apply_id and a.exam_status = 3", "inner")
            ->field(["o.cs as city_id", "a.order_id", "min(a.exam_time) as exam_time"])
            ->group("a.order_id")
            ->buildSql();

        $list = $this->link()->table($subSql)->alias("a")->where($map)
            ->field(["a.city_id", "count(a.order_id) as apply_unpass_num"])
            ->group("a.city_id")
            ->select();

        return $list;
    }

    // 补轮驳回次数：查询时间段内，审核不通过的补轮次数；（按照审核通过时间进行统计，分配维度）；
    public function getCityCompanyApplyUnpassStatistic($date_begin, $date_end, $city_ids = []){
        $map = new Query();
        $map->where("a.exam_time", ">=", strtotime($date_begin));
        $map->where("a.exam_time", "<=", strtotime($date_end) + 86399);

        if (!empty($city_ids)) {
            $map->where("o.cs", "in", $city_ids);
        }

        $list = $this->link()->name("orders")->alias("o")->where($map)
            ->join("order_info i", "i.order = o.id")
            ->join("user_company_round_order r", "r.order_id = i.`order` and r.user_id = i.com", "inner")
            ->join("user_company_round_order_apply a", "a.id = r.apply_id and a.exam_status = 3", "inner")
            ->field([
                "o.cs as city_id",
                "count(a.id) as apply_company_unpass_num",
                "sum(r.round_money) as round_unpass_money"
            ])
            ->group("o.cs")
            ->select();

        return $list;
    }

    // 申请补轮量：查询时间段内，该会员申请的补轮订单量（按照会员申请补轮的时间进行查询）
    public function getCompanyOrderApplyCount($date_begin, $date_end, $company_ids = []){
        $map = new Query();
        $map->where("a.created_at", ">=", strtotime($date_begin));
        $map->where("a.created_at", "<=", strtotime($date_end) + 86399);

        if (!empty($company_ids)) {
            $map->where("a.company_id", "in", $company_ids);
        }

        return $this->link()->name("orders")->alias("o")->where($map)
            ->join("order_info i", "i.order = o.id")
            ->join("user_company_round_order r", "r.order_id = i.`order` and r.user_id = i.com", "inner")
            ->join("user_company_round_order_apply a", "a.id = r.apply_id", "inner")
            ->field(["a.company_id", "count(o.id) as apply_num"])
            ->group("a.company_id")
            ->select();
    }

    // 补轮量：查询时间段内，该会员补轮通过的订单；（按照客服审核时间查询）
    // 补轮驳回量：查询时间段内，该会员补轮不通过的订单；（按照客服审核时间查询）
    public function getCompanyApplyExamCount($date_begin, $date_end, $company_ids = []){
        $map = new Query();
        $map->where("a.exam_status", "in", [2, 3]);
        $map->where("a.exam_time", ">=", strtotime($date_begin));
        $map->where("a.exam_time", "<=", strtotime($date_end) + 86399);

        if (!empty($company_ids)) {
            $map->where("a.company_id", "in", $company_ids);
        }

        return $this->link()->name("orders")->alias("o")->where($map)
            ->join("order_info i", "i.order = o.id")
            ->join("user_company_round_order r", "r.order_id = i.`order` and r.user_id = i.com", "inner")
            ->join("user_company_round_order_apply a", "a.id = r.apply_id", "inner")
            ->field([
                "a.company_id",
                "count(IF(a.exam_status = 2, 1, null)) as apply_pass_num",
                "sum(IF(a.exam_status = 2, r.round_money, 0)) as apply_pass_amount",
                "count(IF(a.exam_status = 3, 1, null)) as apply_unpass_num",
            ])
            ->group("a.company_id")
            ->select();
    }

    // 补轮明细查询条件
    public function getRoundApplyDetailedMap($input){
        $map = new Query();
        $map->where("o.on", 4);

        // 订单查询
        if (!empty($input["order_id"])){
            $map->where("a.order_id", $input["order_id"]);
        }

        // 会员公司查询
        if (!empty($input["company_id"])){
            $map->where("a.company_id", $input["company_id"]);
        }

        // 城市查询
        if (!empty($input["city_ids"])){
            $map->where("u.cs", "in", $input["city_ids"]);
        }

        // 分单时间查询
        if (!empty($input["fen_begin"]) && !empty($input["fen_end"])) {
            $map->where("i.addtime", ">=", strtotime($input["fen_begin"]));
            $map->where("i.addtime", "<=", strtotime($input["fen_end"]) + 86399);
        }

        // 补轮申请时间查询
        if (!empty($input["apply_begin"]) && !empty($input["apply_end"])) {
            $map->where("a.created_at", ">=", strtotime($input["apply_begin"]));
            $map->where("a.created_at", "<=", strtotime($input["apply_end"]) + 86399);
        }

        return $map;
    }

    // 查询补轮明细列表数量
    public function getRoundApplyDetailedCount($input){
        $map = $this->getRoundApplyDetailedMap($input);

        return $this->link()->name("orders")->alias("o")->where($map)
            ->join("order_info i", "i.order = o.id", "inner")
            ->join("user_company_round_order r","r.order_id = i.`order` and r.user_id = i.com", "inner")
            ->join("user_company_round_order_apply a", "a.id = r.apply_id", "inner")
            ->join("user u", "u.id = a.company_id", "inner")
            ->count("a.id");
    }

    // 查询补轮明细列表数据
    public function getRoundApplyDetailedList($input, $offset = 0, $limit = 0){
        $map = $this->getRoundApplyDetailedMap($input);

        return $this->link()->name("orders")->alias("o")->where($map)
            ->join("order_info i", "i.order = o.id", "inner")
            ->join("user_company_round_order r","r.order_id = i.`order` and r.user_id = i.com", "inner")
            ->join("user_company_round_order_apply a", "a.id = r.apply_id", "inner")
            ->join("user u", "u.id = a.company_id", "inner")
            ->join("quyu q", "q.cid = u.cs", "left")
            ->field([
                "a.id as apply_id", "r.id as round_id", "a.order_id", "r.round_money", "r.allot_type", "i.addtime",
                "a.company_id", "u.jc as company_jc", "u.qc as company_qc", "u.cs as city_id", "q.cname as city_name",
                "a.created_at", "a.apply_reason", "a.apply_remark", "a.exam_status", "a.exam_remark", "a.exam_time"
            ])
            ->order("a.company_id desc,a.id desc")
            ->limit($offset, $limit)
            ->select();

    }
}