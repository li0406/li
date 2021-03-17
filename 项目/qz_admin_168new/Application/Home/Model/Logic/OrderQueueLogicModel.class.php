<?php

namespace Home\Model\Logic;

use Home\Model\Db\OrderQueueModel;

class OrderQueueLogicModel {

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

        $orderQueueModel = new OrderQueueModel();
        return $orderQueueModel->addInfo($data);
    }

}