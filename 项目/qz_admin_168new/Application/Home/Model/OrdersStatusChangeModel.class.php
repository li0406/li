<?php

namespace Home\Model;
Use Think\Model;

/**
*
*/
class OrdersStatusChangeModel extends Model
{
        /**
         * 编辑
         * @param  [type] $id   [description]
         * @param  [type] $data [description]
         * @return [type]       [description]
         */
        public function editOrderStatus($id,$data)
        {
            $map = array(
                 "orderid" => array("EQ",$id)
            );
            return M("orders_status_change")->where($map)->save($data);
        }

        /**
         * 新增
         * @param [type] $data [description]
         */
        public function addOrderStatus($data)
        {
                return M("orders_status_change")->add($data);
        }

        /**
         * 查询订单状态信息
         * @param  [type] $orderid [description]
         * @return [type]          [description]
         */
        public function findOrderStatus($orderid,$on,$on_sub,$on_sub_wuxiao)
        {
                $map = array(
                     "orderid" => array("EQ",$orderid),
                     "on" => array("EQ",$on),
                     "on_sub" => array("EQ",$on_sub),
                     "on_sub_wuxiao" => array("EQ",$on_sub_wuxiao)
                );

                return M("orders_status_change")->where($map)->find();
        }

}