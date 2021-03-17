<?php

namespace Home\Model\Db;

use Think\Model;

class SaleReportPaymentModel extends Model {

    public function getOrderTotal($orderIds){
        $map = array(
            "order_id" => array("IN", $orderIds),
            "cooperation_type" => array("EQ", 8),
            "exam_status" => array("EQ", 3),
            "is_delete" => array("EQ", 1)
        );

        return $this->where($map)
            ->field([
                    "order_id",
                    "count(id) as payment_num",
                    "sum(payment_total_money) as payment_amount",
                    "sum(deposit_money) as deposit_amount",
                    "sum(round_order_money) as round_order_amount"
                ])
            ->group("order_id")
            ->select();
    }
}