<?php
/**
 * 订单分配装修公司表
 */

namespace Home\Model\Db;

use Think\Model;

class OrderReviewNewHistoryModel extends Model
{
    protected $tableName = 'order_review_new_history';

    public function getMap(array $cond)
    {
        $map = [];
        if ($cond['order_id']) {
            $map['t.order_id'] = $cond['order_id'];
        }
        return $map;
    }

    public function getAll(array $cond)
    {
        $map = $this->getMap($cond);
        return $this->alias('t')
            ->field('t.*,nr.name as remark_type_name,au.name review_name')
            ->join('LEFT JOIN qz_order_review_new_remark AS nr ON nr.id = t.remark_type')
            ->join('LEFT JOIN qz_adminuser as au ON au.id = t.review_uid')
            ->where($map)
            ->order('id DESC')
            ->select();
    }

    public function getLastReviewInfo($order_id)
    {
        $map = $this->getMap(["order_id" => $order_id]);
        $buildSql = $this->where($map)->alias("t")->field("review_feedback")
                    ->order("t.id desc")->buildSql();

        return $this->table($buildSql)->alias("t1")->find();
    }
}