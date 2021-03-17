<?php
namespace Home\Model;
Use Think\Model;

/**
*  微信推送日志
*/
class LogWxOrdersendModel extends Model
{

    /**
     * 添加日志
     * @param [type] $data [description]
     */
    public function addLog($data)
    {
        return M("log_wx_ordersend")->add($data);
    }

    /**
     * 获取微信发送日志
     * @param  [type] $orderid [description]
     * @return [type]          [description]
     */
    public function getWeixinLog($orderid)
    {
        $map = array(
            "orderid" => array("EQ",$orderid)
        );

        return M("log_wx_ordersend")->where($map)->field("userid")->select();
    }
}