<?php

/**
 *用户听取电话录音
 */

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class LogtelcenterlistenordercallController extends HomeBaseController
{
    /**
     * [addListenOrderCallLog 新增用户听取电话录音记录]
     */
    public function addListenOrderCallLog()
    {
        $save = array(
            'orders_id' => I('post.orders_id'),
            'callSid' => I('post.callSid'),
            'type' => I('post.type',1,'intval')
        );
        $result = D('LogTelcenterListenOrdercall')->addLogTelcenterListenOrdercall($save);
        if ($result) {
            $this->ajaxReturn(array('status' => 1));
        }
        $this->ajaxReturn(array('status' => 0, 'info' => 'ERROR错误！请及时联系技术部'));
    }
}