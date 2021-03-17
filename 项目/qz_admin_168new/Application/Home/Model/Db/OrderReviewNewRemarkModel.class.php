<?php
/**
 * 订单分配装修公司表
 */

namespace Home\Model\Db;

use Think\Model;

class OrderReviewNewRemarkModel extends Model
{
    protected $tableName = 'order_review_new_remark';

    public function getAll($reviewType)
    {
        $field = 'id,review_type,name';
        $map['is_on'] = 1;
        if (!empty($reviewType)) {
            $map['review_type'] = ['eq', $reviewType];
        }
        $data = $this->field($field)
            ->where($map)
            ->order('review_type asc,sort desc')
            ->select();
        return $data;

    }
}