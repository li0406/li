<?php

namespace app\common\model\logic;

use app\common\model\db\OrderQueue;

class OrderQueueLogic {

    // 加入订单处理队列
    public static function addQueueInfo($order_id, $company_ids = ""){
        if (is_array($company_ids)) {
            $company_ids = implode(",", $company_ids);
        }

        $data = [
            "order_id" => $order_id,
            "company_ids" => $company_ids,
            "created_at" => time(),
            "updated_at" => time()
        ];

        $orderQueueModel = new OrderQueue();
        return $orderQueueModel->addInfo($data);
    }
}