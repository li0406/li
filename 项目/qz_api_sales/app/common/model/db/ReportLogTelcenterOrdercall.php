<?php

namespace app\common\model\db;

use think\Model;

class ReportLogTelcenterOrdercall extends Model
{
    protected $table = 'qz_report_log_telcenter_ordercall';

    public function addLog($data)
    {
        return $this->insertGetId($data);
    }

    public function getVisitTelDetailByCallSid($call_sid)
    {
        return $this->alias('r')
            ->join('log_telcenter l', 'r.callSid = l.callSid')
            ->where('r.callSid', $call_sid)
            ->field([
                'l.id', 'r.id ordercall_id', 'r.callSid as call_sid', 'r.channel',
                'l.action', 'l.type', 'l.caller', 'l.called', 'l.byetype', 'l.duration', 'l.recordurl', 'l.time_add'
            ])->select()->toArray();
    }

    public function saveVisitTel($data)
    {
        return $this->saveAll($data);
    }
}
