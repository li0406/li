<?php

namespace Home\Model\Logic;

class LogTelcenterOtherordercallLogicModel
{
    public function addLog($data)
    {
        return D("LogTelcenterOtherordercall")->addLog($data);
    }

    public function getOrderTelRecordCount($ids)
    {
        return D("LogTelcenterOtherordercall")->getOrderTelRecordCount($ids);
    }

    public function getOrderCallListByOrderId($orderid)
    {
       return D("LogTelcenterOtherordercall")->getOrderCallListByOrderId($orderid);
    }
}
