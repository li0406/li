<?php

namespace Home\Model\Db;

use Think\Model;

class UserCompanyAccountLogModel extends Model {

    const LOG_TYPE_RECHARGE = 1; // 加款
    const LOG_TYPE_DEDUCTION = 2; // 扣款
    const LOG_TYPE_ROUND_ORDER = 3; // 轮单扣款
    const LOG_TYPE_OTHER = 4; // 其它类型扣款
    const LOG_TYPE_ROUND_BACK= 5; // 轮单费返还
    const LOG_TYPE_DEDUCTION_NOTICE = 6;//补轮主动告知扣费
    const LOG_TYPE_DEDUCTION_VIOLATION = 7;//补轮违规扣费

    const LOG_TYPE_BZJ_RECHARGE = 11; // 保证金加款
    const LOG_TYPE_BZJ_DEDUCTION = 12; // 保证金扣款
    const LOG_TYPE_BZJ_BACK_PART = 13; // 保证金部分返还
    const LOG_TYPE_BZJ_BACK_ALL = 14; // 保证金全部返还
    const LOG_TYPE_BZJ_TO_ROUND = 15; // 保证金转轮单费
    const LOG_TYPE_BZJ_TO_PLATMONEY = 16; //保证金转平台使用费
    const LOG_TYPE_BZJ_DEC_REBATE = 17; //保证金扣返点

    const LOG_TYPE_LDS_INC = 21; // 轮单数增加 
    const LOG_TYPE_LDS_DEC = 22; // 补单数扣除
    const LOG_TYPE_LDS_BACK = 23; // 补单数返还



    public static function validateTradeNo($trade_no){
        $map = array(
            "trade_no" => array("EQ", $trade_no)
        );

        return M("user_company_account_log")->where($map)->count();
    }

    public function getLastRechargeList($user_ids){
        $map = array(
            "user_id" => array("IN", $user_ids),
            "trade_type" => array("EQ", static::LOG_TYPE_RECHARGE)
        );

        $subSql = M("user_company_account_log")->where($map)
            ->field(["user_id", "trade_no", "created_at"])
            ->order("created_at desc,id desc")
            ->buildSql();

        return M()->table($subSql)->alias("t")
            ->group("t.user_id")
            ->select();
    }

    // 获取个人加款扣款统计
    public function getAccountStatistic($user_ids){
        $map = array(
            "user_id" => array("IN", $user_ids),
            "trade_type" => array("IN", [static::LOG_TYPE_RECHARGE, static::LOG_TYPE_DEDUCTION])
        );

        return M("user_company_account_log")->where($map)
            ->field([
                    "user_id",
                    "sum(if(trade_type = 1, trade_amount, 0)) as trade_recharge_amount",
                    "sum(if(trade_type = 2, trade_amount, 0)) as trade_deduction_amount",
                ])
            ->group("user_id")
            ->select();
    }

    public static function addAccountLog($data){
        M("user_company_account_log")->add($data);
        return M("user_company_account_log")->getLastInsID();
    }

    public static function updateTradeNo($id, $trade_no){
        $map = array(
            "id" => array("EQ", $id)
        );

        $data = array(
            "trade_no" => $trade_no
        );
        return M("user_company_account_log")->where($map)->save($data);
    }

    public static function addLogTypeAll($data){
        return M("user_company_account_log_type")->addAll($data);
    }

    // 根据日志Id获取支付方式类型
    public function getLogTypeByLogIds($logIds){
        $map = array(
            "log_id" => array("IN", $logIds)
        );

        return M("user_company_account_log_type")->where($map)->select();
    }

    // 交易日志
    public function getAccountLogList($user_id, $trade_types, $begin = "", $end = ""){
        $map = array(
            "a.user_id" => array("EQ", $user_id),
            "a.trade_type" => array("IN", $trade_types)
        );

        if (!empty($begin)) {
            $map["a.created_at"][] = array("EGT", strtotime($begin));
        }

        if (!empty($end)) {
            $map["a.created_at"][] = array("ELT", strtotime($end) + 86399);
        }

        return $this->alias("a")->where($map)
            ->join("left join qz_adminuser as u on u.id = a.operator")
            ->field(["a.*", "u.`user` as operator_name"])
            ->order("a.created_at desc")
            ->select();
    } 

    // 交易明细查询条件
    public function getTradeLogMap($user_id, $input){
        $map = array(
            "a.user_id" => array("EQ", $user_id),
            "a.trade_type" => array("IN", $input["trade_types"])
        );

        // 账单类型
        if (!empty($input["trade_type"])) {
            $map[]["a.trade_type"] = array("EQ", $input["trade_type"]);
        }

        // 订单编号
        if (!empty($input["order_id"])) {
            $map["a.order_id"] = array("LIKE", "%" . $input["order_id"] . "%");
        }

        // 交易开始时间
        if (!empty($input["begin"])) {
            $map["a.created_at"][] = array("EGT", strtotime($input["begin"]));
        }

        // 交易结束时间
        if (!empty($input["end"])) {
            $map["a.created_at"][] = array("ELT", strtotime($input["end"]) + 86399);
        }

        // 收支类型
        if (!empty($input["operation_type"])) {
            $map["a.operation_type"] = array("EQ", $input["operation_type"]);
        }

        return $map;
    }

    // 交易明细查询总数
    public function getTradeLogCount($user_id, $input){
        $map = $this->getTradeLogMap($user_id, $input);

        return $this->alias("a")->where($map)
            ->join("left join qz_orders as o on o.id = a.order_id")
            ->count("a.id");
    }

    // 加款明细查询列表
    public function getTradeLogList($user_id, $input, $offset = 0, $limit = 0){
        $map = $this->getTradeLogMap($user_id, $input);

        return $this->alias("a")->where($map)
            ->join("left join qz_orders as o on o.id = a.order_id")
            ->join("left join qz_adminuser as u on u.id = a.operator")
            ->join("left join qz_quyu as q on q.cid = o.cs")
            ->join("left join qz_area as area on area.qz_areaid = o.qx")
            ->join("left join qz_fangshi as fs on fs.id = o.fangshi")
            ->join("left join qz_huxing as hx on hx.id = o.huxing")
            ->join("left join qz_user_company_account_log_round_relation as r on r.log_id = a.id")
            ->field([
                    "a.*", "u.`user` as operator_name",
                    "o.xiaoqu", "o.`name` as yz_name", "o.mianji", "o.shi", "o.ting", "o.wei",
                    "o.fangshi", "fs.`name` as fangshi_name", "o.huxing", "hx.`name` as huxing_name",
                    "o.cs as city_id", "q.cname as city_name", "o.qx as area_id", "area.qz_area as area_name",
                    "r.round_amount_type","r.round_amount_mianji","r.price","r.mianji initial_mianji","o.lx","o.lxs"
                ])
            ->order("a.created_at desc,a.id desc")
            ->limit($offset, $limit)
            ->select();
    }

    /**
     * 获取最新的交易类型日志
     * @param int $company_id
     * @param int $order_id
     * @param int $trade_type
     */
    public function getNewestAccountLog($company_id,$order_id,$trade_type = self::LOG_TYPE_ROUND_ORDER)
    {
        $where = [
            'l.user_id'=>$company_id,
            'l.order_id'=>$order_id,
            'l.trade_type'=>$trade_type,
        ];
        return $this->alias('l')
            ->field('r.*')
            ->where($where)
            ->join('left join qz_user_company_account_log_round_relation r on r.log_id = l.id')
            ->order('l.id desc')
            ->find();
    }
}