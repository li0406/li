<?php
/**
 * 装修单<->家具单 订单转换记录表
 */

namespace Home\Model\Db;

use Think\Model;

class LogOrderRouteModel extends Model
{
    protected $tableName = "log_order_route";

    public function getRouteInfo($where)
    {
        return $this->where($where)->find();
    }

    public function saveRouteInfo($save)
    {
        return $this->add($save);
    }
}