<?php

namespace Home\Model\Db;

use Think\Model;

class LogHollyTelcenterModel extends Model
{
    /**
     *
     * 通过call_sid获取通话记录
     *
     * @param string $call_sid 通话call_sid
     *
     * @return mixed
     */
    function getCalllogByCallSid($call_sid)
    {
        $map             = [];
        $map['call_sid'] = ['EQ', $call_sid];
        return $this->where($map)->find();
    }


    /**
     *
     * 通过call_sid获取录音文件url
     *
     * @param string $call_sid 通话call_sid
     *
     * @return array|bool
     *                   return array
     *                   file_server http://210.14.88.129:8990
     *                   record_file monitor/1.2.2.108/20190611/20190611-141250_N000000004153__9_a1-sh-108-1560233570.623199.mp3
     *                   record_file_url http://210.14.88.129:8990/monitor/1.2.2.108/20190611/20190611-141250_N000000004153__9_a1-sh-108-1560233570.623199.mp3
     */
    function getRecordFileByCallSid($call_sid)
    {
        $calllog = self::getCalllogByCallSid($call_sid);
        if (!empty($calllog['call_time_length']) && $calllog['call_time_length'] >= 0) {
            $recordFileArr                    = [];
            $recordFileArr['file_server']     = $calllog['file_server'];
            $recordFileArr['record_file']     = $calllog['record_file'];
            $recordFileArr['record_file_url'] = $calllog['file_server'] . '/' . $calllog['record_file'];
            return $recordFileArr;
        }
        return false;
    }
}