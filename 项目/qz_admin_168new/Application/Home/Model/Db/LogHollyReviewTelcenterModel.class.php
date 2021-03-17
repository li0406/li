<?php

namespace  Home\Model\Db;

use Think\Model;

class LogHollyReviewTelcenterModel extends Model
{
    public function addLog($data)
    {
        return $this->add($data);
    }


    /**
     * 通过订单ID查询通话记录
     *
     * @param $order_id
     *
     * @return mixed
     */
    public function getOrderTelLogById($order_id, $adminIds = [])
    {
        $map = array(
            "a.order_id" => array("EQ",$order_id)
        );

        if (!empty($adminIds)) {
            $map["a.op_uid"] = array("IN", $adminIds);
        }

        return $this->where($map)->alias("a")
            ->join("join qz_log_holly_telcenter b on a.call_sid = b.call_sid")
            ->field("a.time,a.call_sid,a.op_uname,b.call_state,b.hanguper,b.call_time_length,b.call_no,b.called_no,b.hanguper,b.state")
            ->order("a.id desc")
            ->select();
    }

    /**
     *
     * 通过订单ID查询通话次数
     * @param $order_id
     *
     * @return mixed
     */
    public function getOrderTelRecordCount($order_id)
    {
        $map = array(
            "order_id" => array("IN",$order_id)
        );
        return M("log_holly_review_telcenter")->where($map)->field("order_id as orderid,count(order_id) as count")->group("order_id")->select();

    }
}