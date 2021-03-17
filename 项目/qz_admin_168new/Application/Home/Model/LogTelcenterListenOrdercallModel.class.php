<?php
/**
 *用户听取电话录音记录表
 */

namespace Home\Model;

use Think\Model;

class LogTelcenterListenOrdercallModel extends Model{

    /**
     * [addLogTelcenterListenOrdercall 新增用户听取电话录音记录]
     * @param string $save [存储的数据]
     */
    public function addLogTelcenterListenOrdercall($save = '')
    {
        //电话记录听取人默认为当前用户
        if (empty($save['adminuser_id'])) {
            $save['adminuser_id'] = getAdminUser('id');
        }

        //验证数据完整性
        if (empty($save['orders_id']) || empty($save['callSid'])) {
            return false;
        }

        //保证一个自然日内只记录一次
        $operate_time_start = strtotime(date('Y-m-d'));
        $count = $this->getLogTelcenterListenOrdercallCount($save['orders_id'], $save['callSid'], $save['adminuser_id'],$save['type'], $operate_time_start);
        if ($count > 0) {
            return true;
        }

        //记录时间
        $save['operate_time'] = time();

        //插入数据
        return M('log_telcenter_listen_ordercall')->add($save);
    }

    /**
     * [getLogTelcenterListenOrdercallCount 获取听取电话录音数量]
     * @param  string $orders_id          [订单ID]
     * @param  string $callSid            [callSid]
     * @param  string $adminuser_id       [听取人ID]
     * @param  string $operate_time_start [听取开始时间]
     * @param  string $operate_time_end   [听取结束时间]
     * @return [type]                     [description]
     */
    public function getLogTelcenterListenOrdercallCount($orders_id = '', $callSid = '', $adminuser_id = '',$type = 1, $operate_time_start = '', $operate_time_end = '')
    {
        $map['type'] = $type;
        if (!empty($orders_id)) {
            $map['orders_id'] = $orders_id;
        }

        if (!empty($callSid)) {
            $map['callSid'] = $callSid;
        }

        if (!empty($adminuser_id)) {
            $map['adminuser_id'] = $adminuser_id;
        }

        if (!empty($operate_time_start) && !empty($operate_time_end)) {
            $map['operate_time'] = array(
                array('EGT', $operate_time_start),
                array('LT', $operate_time_end)
            );
        } else if (!empty($operate_time_start) && empty($operate_time_end)) {
            $map['operate_time'] = array('EGT', $operate_time_start);
        } else if (empty($operate_time_start) && !empty($operate_time_end)) {
            $map['operate_time'] = array('LT', $operate_time_end);
        }

        return M('log_telcenter_listen_ordercall')->where($map)->count();
    }
}