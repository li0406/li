<?php

namespace Home\Model\Logic;

use Home\Model\Db\OrderReviewModel;
use Home\Model\Db\OrderReviewNewModel;
use Home\Model\Db\ReviewCityModel;

class NewReviewLogicModel
{
    protected $reviewDb;

    public function __construct()
    {
        $this->reviewDb = new OrderReviewNewModel();
    }

    public function addReviewInfo($order_id,$review_time)
    {
        $info = $this->getReviewInfo($order_id);
        $save = [
            'review_type' => 1,  //状态重置
            'is_delete' => 1,
            'read_mark' => 1,
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
            $reviewDb = new OrderReviewModel();
            $where = ['order_id' => ['eq', $order_id], 'is_delete' => ['eq', 1]];
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
                return $reviewDb->editReviewOrderInfo($info['id'], $save);
            }
            return false;
        }
    }

    public function getReviewInfo($order_id)
    {
        return $this->reviewDb->getInfoByOrder($order_id);
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
        $cityDb = new ReviewCityModel();
        return $cityDb->getReviewCityInfo($cs);
    }
}
