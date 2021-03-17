<?php

namespace app\common\model\logic;

use app\common\model\db\OrderReview;
use app\common\model\db\OrderReviewNew;
use app\common\model\db\ReviewCity;

class NewOrderReviewLogic
{
    public function __construct()
    {
        $this->reviewDb = new OrderReviewNew();
    }

    public function addReviewInfo($order_id,$review_time)
    {
        $info = $this->reviewDb->getInfoByOrder($order_id);
        $save = [
            'review_type' => 1,  //状态重置
            'is_delete' => 1,
            'next_visit_time' => $review_time,
            'updated_at' => time(),
        ];
        if ($info) {
            return $this->reviewDb->editReviewInfo($order_id, $save);
        } else {
            $save['order_id'] = $order_id;
            $save['review_record'] = '';
            $save['review_feedback'] = '';
            $save['created_at'] = time();

            //查询老回坊数据是否有回访记录 , 有则同步
            $reviewDb = new OrderReview();
            $where = [
                ['order_id', '=', $order_id],
                ['is_delete', '=', 1],
            ];
            $info = $reviewDb->getReviewInfoByOrderId('id,order_id,review_record,feedback,review_uid,review_name', $where);
            if (!empty($info['review_record'])) {
                $save['review_record'] = $info['review_record'];
            }
            if (!empty($info['feedback'])) {
                $save['review_feedback'] = $info['feedback'];
            }
            if (!empty($info['review_uid'])) {
                $save['review_uid'] = $info['review_uid'];
            }
            if (!empty($info['review_name'])) {
                $save['review_name'] = $info['review_name'];
            }
            $status = $this->reviewDb->addReviewInfo($save);
            if ($status) {
                $save = [
                    'updated_at' => time(),
                    'is_delete' => 2
                ];
                return $reviewDb->saveData($info['id'], $save);
            }
            return false;
        }
    }

    public function delReviewInfo($order_id)
    {
        $save = [
            'is_delete' => 2,
            'updated_at' => time(),
        ];
        return $this->reviewDb->editReviewInfo($order_id, $save);
    }

    public function getReviewCityByCity($cs)
    {
        $cityDb = new ReviewCity();
        return $cityDb->getReviewCityInfo($cs);
    }
}