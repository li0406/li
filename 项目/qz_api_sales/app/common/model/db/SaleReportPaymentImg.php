<?php

namespace app\common\model\db;

use think\Model;
use think\db\Query;

class SaleReportPaymentImg extends Model {

    protected $autoWriteTimestamp = false;

    // 获取汇款凭证
    public function getPaymentAuthImgs($report_payment_id){
        $map = new Query();
        $map->where("img_type", 1);
        if (!is_array($report_payment_id)) {
            $map->where("report_payment_id", $report_payment_id);
        } else {
            $map->where("report_payment_id", "in", $report_payment_id);
        }

        return $this->where($map)->order("img_px asc")->select();
    }

    // 删除汇款凭证
    public function deletePaymentAuthImg($report_payment_id){
        $map = new Query();
        $map->where("img_type", 1);
        $map->where("report_payment_id", $report_payment_id);
        return $this->where($map)->delete();
    }

    // 批量添加
    public function addAll($data) {
        return $this->insertAll($data);
    }
}