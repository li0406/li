<?php

namespace Home\Model\Logic;
use Home\Model\Db\LogHollyDiyTelcenterModel;
use Home\Model\Db\LogHollyOrderTelcenterModel;
use Home\Model\Db\LogHollyOtherTelcenterModel;
use Home\Model\Db\LogHollyReviewTelcenterModel;

/**
 *
 */
class HollyLogicModel
{
    public function addOrderTelLog($data)
    {
        $model = new LogHollyOrderTelcenterModel();
        return $model->addLog($data);
    }

    public function addOrderReviewTelLog($data)
    {
        $model = new LogHollyReviewTelcenterModel();
        return $model->addLog($data);
    }

    public function addOrderOtherTelLog($data)
    {
        $model = new LogHollyOtherTelcenterModel();
        return $model->addLog($data);
    }
    public function addDiyTelLog($data)
    {
        $model = new LogHollyDiyTelcenterModel();
        return $model->addLog($data);
    }

    public function getOrderTelLogById($order_id)
    {
        $model = new LogHollyOrderTelcenterModel();
        $result = $model->getOrderTelLogById($order_id);
        $list = [];
        foreach ($result as $key => $value) {
            $action = "";
            $byetype = "";

            switch ($value["state"]) {
                case "notDeal":
                    $byetype = "振铃未接听";
                    break;
                case "dealing":
                    $byetype = "已接听";
                    break;
                case "voicemail":
                    $byetype = "已留言";
                    break;
                case "blackList":
                    $byetype = "黑名单";
                    break;
                case "queueLeak":
                    $byetype = "排队放弃";
                    break;
                case "leak":
                    $byetype = "ivr";
                    break;
            }

            if ($value["call_state"] == "Hangup" || $value["call_state"] == "Unlink") {
                if ($value["hanguper"] == "caller") {
                    $action = "主叫挂断";
                } elseif ($value["hanguper"] == "callee"){
                    $action = "被叫挂断";
                }
            }

            $list[] = [
                "order_id" => $value["order_id"],
                "action" => $action,
                "time_add" => date("Y-m-d H:i:s",$value["time"]),
                "caller" => $value["call_no"],
                "called" => $value["called_no"],
                "admin_user" => $value["op_uname"],
                "byetype" => $byetype,
                "callsid" => $value["call_sid"],
                "TelCenter_Channel" => "holly",
                "call_time_length" => $value["call_time_length"]
            ];
        }
        return $list;
    }

    public function getOrderOtherTelLogById($order_id)
    {
        $model = new LogHollyOtherTelcenterModel();
        $result = $model->getOrderTelLogById($order_id);
        $list = [];
        foreach ($result as $key => $value) {
            $action = "";
            $byetype = "";

            switch ($value["state"]) {
                case "notDeal":
                    $byetype = "振铃未接听";
                    break;
                case "dealing":
                    $byetype = "已接听";
                    break;
                case "voicemail":
                    $byetype = "已留言";
                    break;
                case "blackList":
                    $byetype = "黑名单";
                    break;
                case "queueLeak":
                    $byetype = "排队放弃";
                    break;
                case "leak":
                    $byetype = "ivr";
                    break;
            }

            if ($value["call_state"] == "Hangup" || $value["call_state"] == "Unlink") {
                if ($value["hanguper"] == "caller") {
                    $action = "主叫挂断";
                } elseif ($value["hanguper"] == "callee"){
                    $action = "被叫挂断";
                }
            }

            $list[] = [
                "action" => $action,
                "time_add" => date("Y-m-d H:i:s",$value["time"]),
                "caller" => $value["call_no"],
                "called" => $value["called_no"],
                "admin_user" => $value["op_uname"],
                "byetype" => $byetype,
                "callsid" => $value["call_sid"],
                "TelCenter_Channel" => "holly",
                "call_time_length" => $value["call_time_length"]
            ];
        }
        return $list;
    }

    /** 订单回访  */
    public function getOrderReviewTelLogById($order_id, $adminIds = [])
    {
        $model = new LogHollyReviewTelcenterModel();
        $result = $model->getOrderTelLogById($order_id, $adminIds);
        $list = [];
        foreach ($result as $key => $value) {
            $action = "";
            $byetype = "";

            switch ($value["state"]) {
                case "notDeal":
                    $byetype = "振铃未接听";
                    break;
                case "dealing":
                    $byetype = "已接听";
                    break;
                case "voicemail":
                    $byetype = "已留言";
                    break;
                case "blackList":
                    $byetype = "黑名单";
                    break;
                case "queueLeak":
                    $byetype = "排队放弃";
                    break;
                case "leak":
                    $byetype = "ivr";
                    break;
            }

            if ($value["call_state"] == "Hangup" || $value["call_state"] == "Unlink") {
                if ($value["hanguper"] == "caller") {
                    $action = "主叫挂断";
                } elseif ($value["hanguper"] == "callee"){
                    $action = "被叫挂断";
                }
            }

            $list[] = [
                "action" => $action,
                "time_add" => date("Y-m-d H:i:s",$value["time"]),
                "caller" => $value["call_no"],
                "called" => $value["called_no"],
                "admin_user" => $value["op_uname"],
                "byetype" => $byetype,
                "callsid" => $value["call_sid"],
                "TelCenter_Channel" => "holly",
                "call_time_length" => $value["call_time_length"]
            ];
        }
        return $list;
    }

}