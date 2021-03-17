<?php

namespace app\common\model\db;

use think\Model;

class LogHollyTelcenter extends Model
{

    /**
     * 根据call_sid获取记录
     * @param  [type] $call_sid [description]
     * @return [type]           [description]
     */
    public function getListByCallSid($call_sid){
        return $this->field(['id', 'call_sid', 'call_no', 'called_no', 'call_time_length', 'state', 'call_state', 'hanguper', 'createtime'])
            ->where('call_sid', $call_sid)->order('createtime asc')->select();
    }

    /**
     * 根据order_id获取通话记录
     * @param  [type] $order_id [description]
     * @return [type]           [description]
     */
    public function getListByOrderId($order_id){
        return $this->alias('a')
            ->join('log_holly_order_telcenter t', 'a.call_sid = t.call_sid')
            ->field(['a.id', 'a.call_sid', 'a.call_no', 'a.called_no', 'a.call_time_length', 'a.state', 'a.call_state', 'a.hanguper', 'a.createtime'])
            ->where('t.order_id', $order_id)->order('a.createtime asc')->select();
    }

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