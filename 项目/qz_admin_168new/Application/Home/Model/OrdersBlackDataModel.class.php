<?php

namespace Home\Model;
Use Think\Model;
class OrdersBlackDataModel extends Model
{
    public function addAllData($data)
    {
        return M("orders_black_data")->addAll($data);
    }

    public function delData($ids)
    {
        $map = array(
            "order_id" => array("IN",$ids)
        );

        return M("orders_black_data")->where($map)->delete();
    }

    public function getOrderInfo($order_id)
    {
        $map = array(
            "order_id" => array("EQ",$order_id)
        );
        return M("orders_black_data")->where($map)->select();
    }
}
