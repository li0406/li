<?php

namespace Home\Model;
Use Think\Model;
/**
*
*/
class LogSmsSendModel extends Model
{
    /**
     *
     * 获取订单发送记录
     *
     * @param  int $orderid 订单号
     * @param  int $type 短信类型 1 通知业主分配的装修公司 2 未接通电话短信通知
     * @param  string $smschannel 指定短信渠道
     *
     * @return int or bool
     *
     */
    public function getOrderSendLogCount($orderid, $type = 1, $smschannel='')
    {
        if ( empty($orderid) ) {
            return false;
        }

        $map = array();
        $map['orderid'] = array("EQ",$orderid);
        if (!empty($smschannel)) {
            $map['api_type'] = array("EQ", $smschannel);
        }
        if (is_numeric($type)) {
            $map['type'] = array("EQ", $type);
        }

        return M("log_sms_send")->where($map)->count();
    }


    /**
     * 添加日志
     * @param [type] $data [description]
     */
    public function addLog($data)
    {
        return M("log_sms_send")->add($data);
    }

    // 查询发送次数
    public function getOrderTypeDayCount($orderid, $type, $date){
        $map = array(
            "orderid" => array("EQ", $orderid),
            "type" => array("EQ", $type),
            "addtime" => array("BETWEEN", [
                date("Y-m-d", strtotime($date)),
                date("Y-m-d 23:59:59", strtotime($date))
            ])
        );

        return M("log_sms_send")->where($map)->count("id");
    }
}