<?php

namespace Home\Model\Db;

use Think\Model;

class HzsRealOrderModel extends Model
{
    public function addInfo($data)
    {
        return $this->add($data);
    }

    public function getRealOrderCount($order_id)
    {
        $map = [
            "order_id" => $order_id
        ];
        return $this->where($map)->find();
    }

    public function editInfo($id,$data)
    {
        $map = [
            "id" => $id
        ];
        return $this->where($map)->save($data);
    }
}