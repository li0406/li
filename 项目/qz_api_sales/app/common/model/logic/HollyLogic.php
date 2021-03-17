<?php

namespace app\common\model\logic;

use app\common\model\db\LogHollyReportTelcenter;

class HollyLogic
{
    public function addReportTelLog($data)
    {
        $model = new LogHollyReportTelcenter();
        return $model->addReportTelLog($data);
    }

    public function editReportTelLog($call_sid,$data)
    {
        $model = new LogHollyReportTelcenter();
        return $model->editReportTelLog($call_sid,$data);
    }
}