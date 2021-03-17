<?php
// +----------------------------------------------------------------------
// | OrderReviewLogic
// +----------------------------------------------------------------------
namespace Common\Model\Logic;

use Common\Model\Db\OrderReviewModel;

class OrderReviewLogicModel
{
    /**
     * 保存装修公司回访信息
     *
     * @param $order_id
     * @return mixed
     */
    public static function saveOrderReview($order_id)
    {
        $now = time();
        $model = new OrderReviewModel();
        $map['order_id'] = $order_id;
        $order_review = $model->getOne($map);
        if (empty($order_review)) {
            $orderReview = [
                'order_id' => $order_id,
                'review_type' => 1,
                'state' => 3,   //1:自动 2:被动 3:会员量房
                'lf_state' => 2,
                'created_at' => $now + 86400,
                'updated_at' => $now + 86400,
                'toreview_at' => $now + 86400,
            ];
            return $model->addOne($orderReview);
        } else {
            if ($order_review['review_type'] == 4) {
                $orderReview = [
                    'review_type' => 1,
                    'review_uid' => 0,
                    'review_name' => '',
                    'lf_state' => 2,
                    'state' => 3,   //1:自动 2:被动 3:会员量房
                    'created_at' => $now + 86400,
                    'updated_at' => $now + 86400,
                    'toreview_at' => $now + 86400,
                ];
                return $model->upOne($map,$orderReview);
            }
            return true;
        }
    }
}