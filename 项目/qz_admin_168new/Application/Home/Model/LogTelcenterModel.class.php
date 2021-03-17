<?php

namespace Home\Model;

Use Think\Model;

class LogTelcenterModel extends Model{

    protected $autoCheckFields = false;

    /**
     * 通过callSid数组获取列表
     * @param  array  $callSid callSid数组
     * @return
     */
    public function getLogTelcenterByCallSid($callSid = array())
    {
        if (empty($callSid)) {
            return false;
        }

        if (!is_array($callSid)) {
            $callSid = array($callSid);
        }

        return M('log_telcenter')->where(array('callSid' => array('IN', $callSid)))->select();
    }
}