<?php
// +----------------------------------------------------------------------
// | UserUnrobModel  会员公司不抢订单关联表(勾选不抢后 , 不需要赠送)
// +----------------------------------------------------------------------
namespace Common\Model\Db;

use Think\Model;

class UserUnrobModel extends Model
{
    protected $tableName = 'user_unrob_relation';

    public function getNoRobOrderList($where, $field = '*')
    {
        return $this->alias('u')
            ->field($field)
            ->join('qz_order_rob_pool p on p.order_id = u.order_id')
            ->where($where)
            ->select();
    }

    public function getNoRobOrderInfo($where, $field = '*')
    {
        return $this->alias('u')
            ->field($field)
            ->where($where)
            ->find();
    }

    public function saveUnRobOrder($data){
        return $this->add($data);
    }
}