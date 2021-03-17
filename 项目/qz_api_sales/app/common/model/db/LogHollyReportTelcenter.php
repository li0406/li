<?php

namespace app\common\model\db;

use think\Model;

class LogHollyReportTelcenter extends Model
{
    public function addReportTelLog($data)
    {
        return $this->allowField(true)->save($data);
    }

    public function editReportTelLog($call_sid,$data)
    {
        $map[] = ["call_sid","EQ",$call_sid];
        return $this->save($data,$map);
    }
}