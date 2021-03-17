<?php

namespace Home\Model\Logic;

use Home\Model\Db\MsgTypeModel;
use Home\Model\Db\MsgSystemModel;
use Home\Model\Db\MsgTemplateModel;

class MsgSystemLogicModel {

    /**
     * 获取未读消息
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function getRemind($app_slot, $user_id){
        $msgSystemModel = new MsgSystemModel();
        $no_read_count = $msgSystemModel->getNoReadCount($app_slot, $user_id);
        $no_read_list = $msgSystemModel->getNoReadList($app_slot, $user_id, 5);
        $no_read_list = $this->setTemplateLink($no_read_list);

        return [
            "no_read_count" => $no_read_count,
            "no_read_list" => $no_read_list
        ];
    }

    /**
     * 获取消息分页列表
     * @param  [type]  $app_slot [description]
     * @param  [type]  $user_id  [description]
     * @param  [type]  $type_id  [description]
     * @param  integer $page     [description]
     * @param  integer $limit    [description]
     * @return [type]            [description]
     */
    public function getPageList($app_slot, $user_id, $type_id, $page = 1, $limit = 30){
        $msgSystemModel = new MsgSystemModel();
        $count = $msgSystemModel->getPageCount($app_slot, $user_id, $type_id);

        $list = [];
        if ($count > 0) {
            $offset = ($page - 1) * $limit;
            $list = $msgSystemModel->getPageList($app_slot, $user_id, $type_id, $offset, $limit);
            $list = $this->setTemplateLink($list);

            foreach ($list as $key => $item) {
                $list[$key]["created_date"] = date("Y-m-d H:i:s", $item["created_at"]);
            }
        }

        return ["count" => $count, "list" => $list];
    }


    /**
     * 设置消息跳转链接
     * @param [type] $list [description]
     */
    public function setTemplateLink($list){
        if (!empty($list)) {
            // APP唯一标识
            $app_slot = C("SMS_APP_SLOT");

            // 获取消息模板ID集合
            $template_ids = array_column($list, "template_id");

            $msgTemplateModel = new MsgTemplateModel();
            $linkList = $msgTemplateModel->getMsgLink($app_slot, $template_ids);
            $link_list = array_column($linkList, null, "template_id");

            foreach ($list as $key => $item) {
                $list[$key]["link_url"] = "";
                $template_id = $item["template_id"];
                if (array_key_exists($template_id, $link_list)) {
                    $list[$key]["link_url"] = $link_list[$template_id]["link"] . $item["link_param"];
                }
            }
        }

        return $list;
    }

    /**
     * 标记为已读
     * @param [type] $user_id [description]
     * @param string $msg_id  [description]
     */
    public function setMsgRead($app_slot, $user_id, $type_id = "", $msg_id = ""){
        $msgSystemModel = new MsgSystemModel();
        $relatedList = $msgSystemModel->getUnReadRelatedList($app_slot, $user_id, $type_id, $msg_id);

        $result = true;
        if (!empty($relatedList)) {
            $relatedIds = array_column($relatedList, "id");
            $result = $msgSystemModel->setIsRead($relatedIds);
        }

        return $result;
    }

    public function getMsgType($app_slot, $admin_id = 0){
        $msgTypeModel = new MsgTypeModel();
        return $msgTypeModel->getAppMsgType($app_slot, $admin_id);
    }

    // 获取消息内容
    public function getMsgInfo($app_slot, $user_id, $send_group){
        $msgSystemModel = new MsgSystemModel();
        $info = $msgSystemModel->getInfoByGroup($app_slot, $user_id, $send_group);

        if (!empty($info)) {
            $info["link_full"] = "";
            if (!empty($info["link"])) {
                $info["link_full"] = $info["link"] . $info["link_param"];
            }
        }

        return $info;
    }

    // 删除消息
    public function deleteMsg($user_id, $msg_id){
        try {
            $msgSystemModel = new MsgSystemModel();
            $ret = $msgSystemModel->deleteMsgRelated($msg_id, $user_id);

            // 如果消息下没有关联的其它人员删除消息本身
            $count = $msgSystemModel->getMsgRelatedCount($msg_id);
            if ($count == 0) {
                $msgSystemModel->deleteInfo($msg_id);
            }

            $result = true;
        } catch (\Exception $e) {
            $result = "删除失败";
        }

        return $result;
    }
}
