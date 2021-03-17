<?php
/**
 * 订单和二次回访电话关联表
 */

namespace Home\Model\Db;

use Think\Model;

class CompanyLiangFangTelBackModel extends Model
{
    protected $tableName = "company_liangfang_telback";

    public function addOrderTelBack($save){
        return M('company_liangfang_telback')->add($save);
    }

    /**
     * 获取订单回访通话次数
     * @param $orderid
     * @return mixed
     */
    public function getOrderTelBack($orderid)
    {
        $where['order_id'] = ['eq', $orderid];
        return M('company_liangfang_telback')
            ->field('order_id,GROUP_CONCAT(ordercall_id) as ordercall_id')
            ->where($where)
            ->group('order_id')
            ->find();
    }
}