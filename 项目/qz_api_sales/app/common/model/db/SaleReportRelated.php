<?php

namespace app\common\model\db;

use think\Db;
use think\Model;
use think\db\Query;

class SaleReportRelated extends Model {

    protected $autoWriteTimestamp = false;

    // 获取大报备关联的小报备次数、总金额
    public function getPaymentStatistic($report_id, $report_cooperation_type){
        $map = new Query();
        $map->where("b.is_delete", 1);
        $map->where("b.exam_status", 3);
        $map->where("a.report_id", $report_id);
        $map->where("a.report_cooperation_type", $report_cooperation_type);

        return $this->alias("a")->where($map)
            ->join("sale_report_payment b", "b.id = a.report_payment_id")
            ->field([
                    "count(b.id)" => "report_payment_number",
                    "sum(b.payment_total_money)" => "report_payment_money",
                    "sum(b.deposit_to_round_money)" => "deposit_to_round_money",
                    "sum(b.platform_money)" => "platform_money",
                    "GROUP_CONCAT(b.id)" => "report_payment_id",
                ])
            ->find();
    }

    // 获取大报备关联的小报备次数、总金额
    public function getPaymentNumberList($report_ids, $cooperation_types){
        $map = new Query();
        $map->where("b.is_delete", 1);
        $map->where("b.exam_status", 3);
        $map->where("a.report_id", "in", $report_ids);
        $map->where("a.report_cooperation_type", "in", $cooperation_types);

        return $this->alias("a")->where($map)
            ->join("sale_report_payment b", "b.id = a.report_payment_id")
            ->field([
                    "a.report_id", "a.report_cooperation_type",
                    "count(b.id)" => "report_payment_number",
                    "sum(b.payment_money)" => "report_payment_money",
                    "concat(a.report_id, '_', a.report_cooperation_type) as group_key"
                ])
            ->group("a.report_id,a.report_cooperation_type")
            ->select();
    }

    // 删除小报备关联
    public function deletePaymentRelated($report_payment_id){
        return $this->where("report_payment_id", $report_payment_id)->delete();
    }

    // 删除大报备关联
    public function deletePeportRelated($report_id, $report_cooperation_type){
        $map = new Query();
        $map->where("report_id", $report_id);
        $map->where("report_cooperation_type", $report_cooperation_type);

        return $this->where($map)->delete();
    }

    // 添加关联
    public function addRelated($report_payment_id, $report_id, $report_cooperation_type){
        return $this->insert([
                "report_cooperation_type" => $report_cooperation_type,
                "report_payment_id" => $report_payment_id,
                "report_id" => $report_id,
                "created_at" => time()
            ]);
    }

    // 获取大报备关联记录
    public function getReportRelated($report_id, $report_cooperation_type){
        $map = new Query();
        $map->where("report_id", $report_id);
        $map->where("report_cooperation_type", $report_cooperation_type);
        return $this->where($map)->select();
    }

    // 获取关联信息
    public function getReportRelatedInfo($report_payment_id){
        $map = new Query();
        $map->where("r.report_payment_id", $report_payment_id);

        return $this->alias("r")->where($map)
            ->join("sale_report_zx_company a", "a.id = r.report_id and a.cooperation_type = r.report_cooperation_type", "left")
            ->join("sale_report_swj_company b", "b.id = r.report_id and b.cooperation_type = r.report_cooperation_type", "left")
            ->field([
                    "r.report_id", "r.report_payment_id", "r.report_cooperation_type", 
                    "if(a.id is null, b.`status`, a.`status`) as status"
                ])
            ->find();
    }
}