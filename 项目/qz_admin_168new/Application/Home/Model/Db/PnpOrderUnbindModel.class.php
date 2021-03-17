<?php

namespace Home\Model\Db;

use Think\Model;

class PnpOrderUnbindModel extends Model {

    public function getOrderUnbindLog($order_id){
        $map = array(
            "order_id" => array("EQ", $order_id)
        );

        return $this->where($map)->find();
    }

    public function addLogInfo($data){
        return $this->add($data);
    }
}