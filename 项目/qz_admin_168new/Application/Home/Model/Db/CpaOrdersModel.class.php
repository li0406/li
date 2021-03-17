<?php

namespace Home\Model\Db;
Use Think\Model;
class CpaOrdersModel extends Model
{
    /**
     * 添加订单
     * @param [type] $data [description]
     */
    public function addOrder($data)
    {
        return M("cpa_orders")->add($data);
    }

    public function editOrder($order_id,$data)
    {
        $map = array(
            "id" => array("EQ",$order_id)
        );
        return M("cpa_orders")->where($map)->save($data);
    }

    /**
     * 添加电话
     * @param [type] $data [description]
     */
    public function addSafeTel($data)
    {
         return  M()->table('safe_order_tel8')->add($data);
    }

    public function findOrderCount($order_id = "")
    {
        $map = array(
            "order_id" => array("EQ",$order_id)
        );
        return M("cpa_orders")->where($map)->count();
    }
}