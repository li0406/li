<?php

namespace app\common\model\logic;

use think\Db;
use think\facade\Request;
use app\common\model\db\OrderReview;

class OrderReviewLogic {

    // 更新客服回访订单
    public function updateOrderReview($order_id, $order_lf_status){
        $orderReviewModel = new OrderReview();
        $orderReview = $orderReviewModel->findByOrderId($order_id);

        $result = true;
        if (empty($orderReview)) {
            $data = array(
                "order_id" => $order_id,
                "lf_state" => $order_lf_status,
                "state" => 2,
                "review_type" => 1,
                "toreview_at" => time(),
                "created_at" => time(),
                "updated_at" => time()
            );
            $result = $orderReviewModel->addData($data);
        } else if ( in_array($orderReview->review_type, [1, 4, 5]) ) {
            $data = array(
                "state" => 2,
                "review_type" => 1,
                "updated_at" => time(),
                "toreview_at" => time()
            );
            $result = $orderReviewModel->saveData($orderReview["id"], $data);
        }

        return $result;
    }

}