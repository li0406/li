<?php

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

/**
 * 用户管理
 */

class AdminusermessagequeueController extends HomeBaseController{
    /**
     * [index 获取用户消息]
     * @return [type] [description]
     */
    public function index()
    {
        $admin_id = getAdminUser('id');

        //获取开始记录位置
        $start = intval(I('get.start'));
        if (empty($start)) {
            $start = 0;
        }

        //获取类型，消息类型必须
        $type = array_filter(explode(",", I('get.type')));
        if (count($type) == 0) {
            $this->ajaxReturn(array('status' => 0));
        }

        $result = D('AdminuserMessageQueue')->getAdminuserMessageQueue($admin_id, $type, 1, $start)[0];
        if (!empty($result)) {
            $data = $this->getMessageContent($result);
            if ($data != false) {
                $this->ajaxReturn(array('status' => 1, 'data' => $data));
            }
        }
        $this->ajaxReturn(array('status' => 0));
    }

    /**
     * [setMessageQueueChecked 标识用户消息为已读]
     */
    public function setMessageQueueChecked()
    {
        $id = I('post.id');
        $admin_id = getAdminUser('id');
        $message = D('AdminuserMessageQueue')->getAdminuserMessageQueueById($id);
        if (!empty($message) && $admin_id == $message['adminuser_id']) {
            $save = array(
                'checked' => '2',
                'checked_time' => time()
            );
            $result = D('AdminuserMessageQueue')->editAdminuserMessageQueue($id, $save);
            if ($result) {
                $this->ajaxReturn(array('status' => 1));
            }
        }
        $this->ajaxReturn(array('status' => 1));
    }

    /**
     * [getMessageContent 经过加工模板获取消息内容]
     * @param  [type] $info [消息信息]
     * @return [type]       [description]
     */
    private function getMessageContent($info)
    {
        switch ($info['type']) {
            case '1':
                //抽检结论提醒：存储的信息为 order_id, sampling_status type
                $data = json_decode($info['data'],true);
                $data['sampling_status'] = $data['sampling_status'] == 1 ? '不合格' : '合格';
                $result = array(
                    'title' => '抽检结论提醒',
                    'content' => '您质检的订单号为：<a toast-data-id="' . $info['id'] . '" data-val="' . $data['order_id'] . '" class="toast-clear toast-data">' . $data['order_id'] . '</a>的录音已经被抽检完成，您的质检结论被评定为：' . $data['sampling_status'] . '，请查阅~'
                );
                break;
            default:
                //这里没有匹配到，直接将该消息设置为已读，避免每次都查询到该消息而查不到后面的信息
                D('AdminuserMessageQueue')->editAdminuserMessageQueue($info['id'], array('checked' => '2'));
                return false;
                break;
        }
        return $result;
    }
}