<?php

namespace Home\Model\Db;
use Think\Model;
/**
 *
 */
class QcOrdersApplyTelModel extends Model
{
    public function getLastOrderApplyTelInfo($order_id)
    {
        $map = array(
            "order_id" => array("EQ",$order_id),
        );
        return $this->where($map)->field("apply_time,status")->order("id desc")->find();
    }

    public function editOrderApplyTelInfo($order_id,$data)
    {
        $map = array(
            "order_id" => array("EQ",$order_id)
        );
        return $this->where($map)->save($data);
    }

    public function editApplyTelInfo($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return $this->where($map)->save($data);
    }

    public function addOrderApplyTelInfo($data)
    {
        return $this->add($data);
    }

    public function getOrderApplyTelList($ids)
    {
        $map = array(
            "order_id" => array("IN",$ids)
        );
        return $this->where($map)->field("order_id,status,apply_time")->select();
    }

    public function getapplylist($order_id)
    {
        $map = array(
            "a.order_id" => array("EQ",$order_id)
        );
        return $this->where($map)->alias("a")
                    ->join("left join qz_adminuser u on u.id = a.apply_id")
                    ->join("left join qz_adminuser u1 on u1.id = a.passer_id")
                    ->field("a.*,u.name as apply_name,u1.name as passer_name")
                    ->order("id desc")->select();
    }

    public function editApplyTel($id,$data)
    {
        # code ...
    }
}