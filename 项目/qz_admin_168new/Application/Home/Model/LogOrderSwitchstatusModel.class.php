<?php

namespace Home\Model;
Use Think\Model;
/**
*   订单状态日志表
*/
class LogOrderSwitchstatusModel extends Model
{

    /**
     * 添加日志
     * @param [type] $data [description]
     */
    public function addLog($data)
    {
        return M('log_order_switchstatus')->add($data);
    }

    /**
     * 获取最进的一次订单记录
     * @param  [type] $orderid [description]
     * @return [type]          [description]
     */
    public function getLastOrderLog($orderid)
    {
        $map = array(
            "orderid" => array("EQ",$orderid)
        );
        return M("log_order_switchstatus")->where($map)->order("id desc")->find();
    }
}