<?php

namespace Home\Model\Db;

use Think\Model;

class SafeOrderTel8Model extends Model
{

    protected $autoCheckFields = false;
    public function getOrderTel($where)
    {
        return M()->table('safe_order_tel8')->where($where)->find();
    }

    public function saveOrderTel($save)
    {
        return M()->table('safe_order_tel8')->add($save);
    }
}