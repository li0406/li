<?php

namespace app\common\model\db;

use think\Model;
use think\db\Query;

class SaleReportPaymentPerson extends Model {

    protected $autoWriteTimestamp = false;

    const TYPE_PERSON_MAIN = 1;
    const TYPE_PERSON_OTHER = 2;

    // 获取其它业绩人
    public function getPaymentPerson($report_payment_id, $type_id = 0){
        $map = new Query();
        if (is_array($report_payment_id)) {
            $map->where("a.report_payment_id", "in", $report_payment_id);
        } else {
            $map->where("a.report_payment_id", $report_payment_id);
        }

        if (!empty($type_id)) {
            $map->where("a.type_id", $type_id);
        }

        return $this->alias("a")->where($map)
            ->join("adminuser u", "u.id = a.saler_id", "left")
            ->field(["a.*", "u.`user` as saler_name"])
            ->order("a.type_id asc, a.list_order asc")
            ->select();
    }

    // 删除业绩人记录
    public function deletePaymentPerson($report_payment_id){
        $map = new Query();
        $map->where("report_payment_id", $report_payment_id);
        return $this->where($map)->delete();
    }

    // 批量添加
    public function addPerson($data) {
        return $this->insert($data);
    }

    // 批量添加
    public function addAll($data) {
        return $this->insertAll($data);
    }
}