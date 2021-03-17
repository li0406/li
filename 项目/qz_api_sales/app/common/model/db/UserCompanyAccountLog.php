<?php

namespace app\common\model\db;

use think\Model;
use think\db\Query;

class UserCompanyAccountLog extends Model {

    protected $autoWriteTimestamp = false;

    // 获取个人加款扣款统计
    public function getAccountStatistic($user_ids){
        $map = new Query();
        $map->where("user_id", "in", $user_ids);
        $map->where("trade_type", "in", [1, 2]);

        return $this->where($map)
            ->field([
                    "user_id",
                    "sum(if(trade_type = 1, trade_amount, 0)) as trade_recharge_amount",
                    "sum(if(trade_type = 2, trade_amount, 0)) as trade_deduction_amount",
                ])
            ->group("user_id")
            ->select();
    }

    // 查询装修公司账户交易明细数量
    public function getCompanyAccountLogCount($company_id, $input){
        $map = $this->getCompanyAccountLogMap($company_id, $input);

        return $this->alias("a")->where($map)
            ->join("orders o", "o.id = a.order_id", "left")
            ->count("a.id");
    }

    // 查询装修公司账户交易明细数据
    public function getCompanyAccountLogList($company_id, $input, $offset = 0, $limit = 0){
        $map = $this->getCompanyAccountLogMap($company_id, $input);

        return $this->alias("a")->where($map)
            ->join("orders o", "o.id = a.order_id", "left")
            ->join("user_company_account_log_round_relation r", "r.log_id = a.id", "left")
            ->field([
                    "a.id", "a.order_id", "a.trade_type", "a.created_at", "a.trade_amount",
                    "a.operation_type", "a.trade_no", "r.round_amount_type"
                ])
            ->order("a.created_at desc,a.id desc")
            ->limit($offset, $limit)
            ->select();
    }

    // 装修公司账户交易明细查询条件
    public function getCompanyAccountLogMap($company_id, $input){
        $map = new Query();
        $map->where("a.user_id", $company_id);
        $map->where("a.trade_type", "in", $input["trade_type"]);

        // 收支类型
        if (!empty($input["operation_type"])) {
            $map->where("a.operation_type", $input["operation_type"]);
        }

        // 订单编号
        if (!empty($input["order_id"])) {
            $map->where("a.order_id", $input["order_id"]);
        }

        // 交易开始时间
        if (!empty($input["date_begin"])) {
            $map->where("a.created_at", ">=", strtotime($input["date_begin"]));
        }

        // 交易结束时间
        if (!empty($input["date_end"])) {
            $map->where("a.created_at", "<=", strtotime($input["date_end"]) + 86399);
        }

        return $map;
    }

    // 获取装修公司各项轮单价消耗
    public function getCompanyRoundAmountTotal($company_id){
        $map = new Query();
        $map->where("a.user_id", $company_id);
        $map->where("a.trade_type", "in", [3, 5]);

        return $this->alias("a")->where($map)
            ->join("user_company_account_log_round_relation b", "b.log_id = a.id", "inner")
            ->field([
                    "b.round_amount_type",
                    "sum(if(a.operation_type = 2, a.trade_amount, 0)) as deduction",
                    "sum(if(a.operation_type = 1, a.trade_amount, 0)) as back"
                ])
            ->group("b.round_amount_type")
            ->select();
    }


}