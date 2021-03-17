<?php


namespace app\common\model\db;


use think\Model;

class OrderReviewNewHistory extends Model
{
    protected $table = 'qz_order_review_new_history';

    public function getAll($orderId)
    {
        $cond[] = ['order_id', '=', $orderId];
        $cond[] = ['a.review_type', 'in', [3, 4, 5, 6, 7, 8]];
        $cond[] = ['is_delete', '=', 1];
        $ret = $this->alias('a')
            ->leftJoin('qz_order_review_new_remark nr', 'a.remark_type=nr.id')
            ->field('a.*,nr.name as remark_type_name')
            ->where($cond)
            ->order('a.id', 'desc')
            ->select();
        return $ret->toArray() ?: [];
    }

    public function hasOrders($ordersId)
    {
        $cond[] = ['order_id', 'in', $ordersId];
        $cond[] = ['is_delete', '=', 1];
        $data = $this->alias('a')
            ->field("count(*) count,order_id")
            ->where($cond)
            ->group('order_id')
            ->select();
        return $data->toArray() ?: [];
    }
}