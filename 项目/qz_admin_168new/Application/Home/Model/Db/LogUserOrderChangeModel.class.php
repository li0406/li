<?php

namespace Home\Model\Db;

use Think\Model;

class LogUserOrderChangeModel extends Model
{
    protected $autoCheckFields = false;

    public function getOrderChange($order_id)
    {
        $where = [
            'l.order_id' => $order_id,
        ];
        return $this->alias('l')->where($where)
            ->field('l.company_id,l.order_id,l.status,l.created_at track_time,u.jc,2 record_type')
            ->join('qz_user u on u.id = l.company_id')
            ->order('l.id desc')
            ->select();
    }
}