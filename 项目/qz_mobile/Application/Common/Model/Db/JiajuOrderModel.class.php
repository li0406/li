<?php

namespace Common\Model\Db;
use Think\Model;

class JiajuOrderModel extends Model
{
    public function addOrder($data)
    {
        return M("jiaju_order")->add($data);
    }


    public function editInfo($id,$data)
    {
        $map = ["id" => ["EQ",$id]];
        return M("jiaju_order")->where($map)->save($data);
    }

    public function getLastPublishOrderInfo($tel_encrypt)
    {
        $map = ["tel_encrypt"=> ["EQ",$tel_encrypt]];
        return M("jiaju_order")->where($map)->order("time_real desc")->find();
    }

    public function getOrderCountByIp($ip,$begin,$end,$whiteIp)
    {
        $map = array(
            "ip" => array(
                array("EQ",$ip),
                array("NOT IN",$whiteIp)
            ),
            "time_real" => array(
                array("EGT",$begin),
                array("ELT",$end)
            )
        );
        return M("jiaju_order")->where($map)->count();
    }

    /**
     * 获取订单信息
     * @param  [type] $order_id [description]
     * @return [type]           [description]
     */
    public function getOrderInfoById($order_id)
    {
        $map = ["id" => ["EQ",$order_id]];
        return M("jiaju_order")->where($map)->find();
    }

    /**
     * 添加安全表信息
     * @param [type] $data [description]
     */
    public function addTelSafe($data)
    {
        return M()->table('safe_order_tel8')->add($data);
    }

    /**
     * 根据条件查询订单具体信息
     * @param [array] $map查询条件
     * @return array|mixed
     */
    public function getOrderByMap($map)
    {
        return M("jiaju_order")->where($map)->find();
    }
}