<?php

namespace Home\Model;

Use Think\Model;
/**
*  质检信息表通知查看表日志表
*/
class AdminuserMessageQueueModel extends Model
{
    /**
     * [addQcInfoCheckQueue 添加日志]
     * @param [type] $save [日志内容]
     */
    public function addAdminuserMessageQueue($save)
    {
        if (empty($save['type']) || empty($save['adminuser_id']) || empty($save['data'])) {
            return false;
        }
        //如果data字段为数组，则转换为json
        if (is_array($save['data'])) {
            $save['data'] = json_encode($save['data']);
        }
        //其它字段
        $save['checked'] = '1';
        $save['add_time'] = time();
        return M('adminuser_message_queue')->add($save);
    }

    /**
     * [editQcInfoCheckQueue 修改日志记录]
     * @param  string $id   [日志ID]
     * @param  [type] $save [日志内容]
     * @return [type]       [description]
     */
    public function editAdminuserMessageQueue($id = '', $save)
    {
        if (empty($id) || empty($save)) {
            return false;
        }
        $map = array(
            'id' => $id
        );
        return M('adminuser_message_queue')->where($map)->save($save);
    }

    /**
     * [getAdminuserMessageQueueById 根据ID获取消息通知查看队列表记录]
     * @param  integer $id [消息ID]
     * @return [type]      [description]
     */
    public function getAdminuserMessageQueueById($id = 0)
    {
        if (empty($id)) {
            return false;
        }
        $map = array(
            'id' => $id
        );
        return M('adminuser_message_queue')->where($map)->find();
    }

    /**
     * [getAdminuserMessageQueue 获取消息通知查看队列表记录]
     * @param  string  $adminuser_id [用户ID]
     * @param  integer $type         [消息类型]
     * @param  integer $checked      [是否已知道该信息]
     * @param  integer $start        [开始位置]
     * @param  integer $end          [获取条数，默认为1条]
     * @return [type]                [description]
     */
    public function getAdminuserMessageQueue($adminuser_id = '', $type = 0, $checked = 0, $start = 0, $end = 1)
    {
        //用户ID
        if (!empty($adminuser_id)) {
            $map['adminuser_id'] = array('EQ', $adminuser_id);
        }

        //消息类型
        if (!empty($type)) {
            $map['type'] = array("IN",$type);
        }

        //是否已知道该信息
        if (!empty($checked)) {
            $map['checked'] = $checked;
        }

        return M('adminuser_message_queue')->where($map)->limit($start, $end)->order('id ASC')->select();
    }
}