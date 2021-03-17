<?php

// +----------------------------------------------------------------------
// | SaleReportPayment 小报备数据模型
// +----------------------------------------------------------------------

namespace app\index\model\adb;

use think\Db;
use think\db\Query;

use app\common\model\adb\BaseModel;

class SaleReportPayment extends BaseModel {

    // 小报备城市业绩统计
    public function getCityAchievementStatistic($date_begin, $date_end, $city_ids = []){
        $map = new Query();
        $map->where("a.is_delete", 1);
        $map->where("a.exam_status", 3);
        $map->where("a.payment_time", ">=", strtotime($date_begin));
        $map->where("a.payment_time", "<=", strtotime($date_end) + 86399);

        if (!empty($city_ids)){
            $map->where("a.city_id", "in", $city_ids);
        }

        $list = $this->link()->name("sale_report_payment")->alias("a")->where($map)
            ->field([
                "a.city_id",
                "sum(a.payment_money) as achievement"
            ])
            ->group("a.city_id")
            ->select();
            
        return $list;
    }

    // 业绩最高的城市
    public function getCityAchievementTop($date_begin, $date_end, $city_ids = [], $limit = 20){
        $map = new Query();
        $map->where("a.is_delete", 1);
        $map->where("a.exam_status", 3);
        $map->where("a.payment_time", ">=", strtotime($date_begin));
        $map->where("a.payment_time", "<=", strtotime($date_end) + 86399);

        if (!empty($city_ids)){
            $map->where("a.city_id", "in", $city_ids);
        }

        $subSql = $this->link()->name("sale_report_payment")->alias("a")->where($map)
            ->field([
                "a.city_id",
                "sum(a.payment_money) as achievement"
            ])
            ->group("a.city_id")
            ->buildSql();

        $list = $this->link()->table("qz_quyu")->alias("q")
            ->where("q.is_open_city", 1)
            ->join([$subSql => "t"], "t.city_id = q.cid", "left")
            ->field(["q.cid as city_id", "q.cname as city_name", "t.achievement"])
            ->order("t.achievement desc,q.cid")
            ->limit($limit)
            ->select();

        return $list;
    }

    // 城市收款金额查询
    public function getCityReportTotalAmount($rtype, $date_begin, $date_end, $city_ids = []){
        $map = new Query();
        $map->where("a.is_delete", 1);
        $map->where("a.exam_status", 3);
        $map->where("a.payment_time", ">=", strtotime($date_begin));
        $map->where("a.payment_time", "<=", strtotime($date_end) + 86399);

        if (!empty($city_ids)){
            $map->where("a.city_id", "in", $city_ids);
        }

        if ($rtype == 1) {
            $map->where(function($query){
                $query->where("a.cooperation_type", "in", [1, 2, 3]);
                $query->whereOr(function($q){
                    $q->where("a.cooperation_type", 11);
                    $q->where("a.payment_type", 7);
                });
            });
        } else if ($rtype == 2) {
            $map->where(function($query){
                $query->where("a.cooperation_type", "in", [6, 10, 13]);
                $query->whereOr(function($q){
                    $q->where("a.cooperation_type", 11);
                    $q->where("a.payment_type", 8);
                });
            });
        }

        return $this->link()->name("sale_report_payment")->alias("a")->where($map)
            ->field([
                "a.city_id",
                "sum(
                    case a.cooperation_type
                        when 13 then a.deposit_to_platform_money
                        when 10 then a.deposit_to_round_money
                        when 11 then a.refund_money * -1
                        else a.payment_total_money end
                ) as total_amount"
            ])
            ->group("a.city_id")
            ->select();
    }


    /**
     * @des:单位时间内，小报备审核通过的总收款，带区域范围，1.0会员总消耗 合作类型（装修会员、独家会员、垄断会员）
    $param['cooperation_type'] = [1, 2, 3];
    $param['or_cooperation_type'] = [
    'cooperation_type' => 11,
    'payment_type' => 7
    ];
     * @param array $param
     * @return mixed
     */
    public function getSaleReportPaymentV1($param = [])
    {
        $map = new Query();
        $map->where("a.is_delete", '=', 1);
        $map->where("a.exam_status", '=', 3);
        $map->where("a.payment_time", "between", [$param['start_time'], $param['end_time']]);
        $map->where(function ($query) {
            $query->where('a.cooperation_type', 'in', [1, 2, 3]);
            $query->whereOr(function ($orWhere) {
                $orWhere->where('a.cooperation_type', '=', 11);
                $orWhere->where('a.payment_type', '=', 7);
            });
        });
        if (isset($param['cs']) && !empty($param['cs']) && is_array($param['cs'])) {
            $map->where('a.city_id', 'in', $param['cs']);
        }
        return $this->link()->table("qz_sale_report_payment")->alias("a")
            ->where($map)
            ->field(["sum(CASE
		            cooperation_type 
		        WHEN 11 THEN -refund_money
		        else  payment_total_money 
	            END) as money
            "])
            ->find();
    }

    /**
     * @des:单位时间内，小报备审核通过的总收款，带区域范围，2.0会员总消耗 合作类型（签返会员、签返会员(保证金转轮单费) ，平台使用费（保证金转） 已找产品确认）
     * @param array $param
     * @return mixed
     */
    public function getSaleReportPaymentV2($param)
    {
        $map = new Query();
        $map->where("is_delete", '=', 1);
        $map->where("exam_status", '=', 3);
        $map->where("payment_time", "between", [$param['start_time'], $param['end_time']]);
        $map->where(function ($query) {
            $query->where('cooperation_type', 'in', [6, 10, 13]);
            $query->whereOr(function ($orWhere) {
                $orWhere->where('cooperation_type', '=', 11);
                $orWhere->where('payment_type', '=', 8);
            });
        });

        if (isset($param['cs']) && !empty($param['cs']) && is_array($param['cs'])) {
            $map->where('city_id', 'in', $param['cs']);
        }
       return $this->link()->table("qz_sale_report_payment")->where($map)
            ->field(["sum(CASE
		            cooperation_type 
		        WHEN 6 THEN
		            payment_total_money 
		        when 10 then deposit_to_round_money 
		        WHEN 11 THEN -refund_money
		        else  deposit_to_platform_money 
	            END) as money
            "])
           ->find();

    }

    /**
     * @des:单位内报备总收款
     * @param array $param
     * @return mixed
     */
    public function getSaleReportPayment($param)
    {
        $map = new Query();
        $map->where("is_delete", '=', 1);
        $map->where("exam_status", '=', 3);
        $map->where("payment_time", "between", [$param['start_time'], $param['end_time']]);

        if (isset($param['cs']) && !empty($param['cs']) && is_array($param['cs'])) {
            $map->where('city_id', 'in', $param['cs']);
        }
        return $this->link()->table("qz_sale_report_payment")->where($map)
            ->field(["sum(CASE
		            cooperation_type 
		        when 10 then deposit_to_round_money 
		        WHEN 11 THEN -refund_money
		        WHEN 13 THEN deposit_to_platform_money
		        else  payment_total_money 
	            END) as money
            "])
            ->find();
    }

    // 查询团队业绩人员
    public function getTeamAchievementMembers($date_begin, $date_end, $team_id){
        $map = new Query();
        $map->where("a.is_delete", 1);
        $map->where("a.exam_status", 3);
        $map->where("p.share_ratio", ">", 0);
        $map->where("a.payment_money", "<>", 0);
        $map->where("a.payment_time", ">=", strtotime($date_begin));
        $map->where("a.payment_time", "<=", strtotime($date_end));
        $map->where("", "exp", "FIND_IN_SET({$team_id}, p.team_ids)");

        $list = $this->link()->name("sale_report_payment")->alias("a")->where($map)
            ->join("sale_report_payment_person p", "p.report_payment_id = a.id", "inner")
            ->field([
                "count(DISTINCT p.saler_id) as member_count",
                "GROUP_CONCAT(DISTINCT p.saler_id) as member_ids",
                "FROM_UNIXTIME(a.payment_time, '%Y%m') as `month`",
            ])
            ->group("month")
            ->select();

        return $list;
    }

    // 查询团队人员业绩
    public function getTeamSalerAchievement($date_begin, $date_end, $team_ids){
        $map = new Query();
        $map->where("a.is_delete", 1);
        $map->where("a.exam_status", 3);
        $map->where("p.share_ratio", ">", 0);
        $map->where("a.payment_money", "<>", 0);
        $map->where("a.payment_time", ">=", strtotime($date_begin));
        $map->where("a.payment_time", "<=", strtotime($date_end));

        $map->where(function($query) use ($team_ids) {
            foreach ($team_ids as $team_id) {
                $query->whereOr("", "exp", "FIND_IN_SET('{$team_id}', p.team_ids)");
            }
        });

        $list = $this->link()->name("sale_report_payment")->alias("a")->where($map)
            ->join("sale_report_payment_person p", "p.report_payment_id = a.id", "inner")
            ->field([
                "p.saler_id",
                "sum(a.payment_money * p.share_ratio / 100) as achievement"
            ])
            ->group("p.saler_id")
            ->select();

        return $list;
    }
}