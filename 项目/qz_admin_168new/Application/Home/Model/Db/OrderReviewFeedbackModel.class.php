<?php
/**
 * 订单分配装修公司表
 */

namespace Home\Model\Db;

use Think\Model;

class OrderReviewFeedbackModel extends Model
{
    protected $tableName = "order_review_feedback";

    public function getReviewCompany($orderno){
        $where['f.order_id'] = ['eq',$orderno];
        return $this->alias('f')
            ->field('f.com_id as comid,u.jc')
            ->join('qz_user u on u.id = f.com_id')
            ->where($where)
            ->select();
    }

    public function deleteRow($map){
       return $this->where($map)->delete();
    }

}