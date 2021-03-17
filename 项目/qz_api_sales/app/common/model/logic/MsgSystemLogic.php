<?php

namespace app\common\model\logic;

use think\Db;
use think\db\Query;
use think\facade\Request;

use app\common\model\db\MsgType;
use app\common\model\db\MsgSystem;
use app\common\model\db\MsgTemplateLink;

class MsgSystemLogic {

    /**
     * 获取未读消息数量
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function getNoReadCount($app_slot, $user_id){
        $msgSystemModel = new MsgSystem();
        return $msgSystemModel->getNoReadCount($app_slot, $user_id);
    }

    /**
     * 获取未读消息
     * @param  [type]  $user_id [description]
     * @param  integer $limit   [description]
     * @return [type]           [description]
     */
    public function getNoReadList($app_slot, $user_id, $limit = 5){
        $msgSystemModel = new MsgSystem();
        $list = $msgSystemModel->getNoReadList($app_slot, $user_id, $limit);

        return $this->setTemplateLink($list->toArray());
    }

    /**
     * 获取消息分页列表
     * @param  [type]  $user_id   [description]
     * @param  integer $page      [description]
     * @param  integer $page_size [description]
     * @return [type]             [description]
     */
    public function getPageList($app_slot, $user_id, $type_id = 0, $page = 1, $page_size = 20){
        $msgSystemModel = new MsgSystem();
        $count = $msgSystemModel->getPageCount($app_slot, $user_id, $type_id);

        $list = [];
        if ($count > 0) {
            $offset = ($page - 1) * $page_size;
            $list = $msgSystemModel->getPageList($app_slot, $user_id, $type_id, $offset, $page_size);
            $list = $this->setTemplateLink($list->toArray());

            // 列表按日期分组
            foreach ($list as $key => $item) {
                $list[$key]["created_date"] = date("Y-m-d H:i:s", $item["created_at"]);
            }

            // 设置为已读
            $msgSystemModel->setIsRead($app_slot, $user_id, $type_id);
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
            $app_slot = config("SMS_APP_SLOT");

            // 获取消息模板ID集合
            $template_ids = array_column($list, "template_id");

            $msgTemplateLinkModel = new MsgTemplateLink();
            $linkList = $msgTemplateLinkModel->getMsgLink($app_slot, $template_ids);
            $link_list = array_column($linkList->toArray(), null, "template_id");

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
     * 设置为已读
     * @param [type] $user_id [description]
     * @param [type] $msg_ids [description]
     */
    public function setMsgRead($app_slot, $user_id, $type_id = 0, $msg_id = 0){
        $msgSystemModel = new MsgSystem();
        return $msgSystemModel->setIsRead($app_slot, $user_id, $type_id, $msg_id);
    }

    /**
     * 获取应用项目消息类型
     * @param $app_slot
     * @return array|string|\think\Collection
     */
    public function getAppMsgType($app_slot, $user_id){
        $msgTypeModel = new MsgType();
        return $msgTypeModel->getAppTypeList($app_slot, $user_id);
    }

    // 获取消息内容
    public function getMsgInfo($app_slot, $user_id, $send_group){
        $msgSystemModel = new MsgSystem();
        $info = $msgSystemModel->getInfoByGroup($app_slot, $user_id, $send_group);

        if (!empty($info)) {
            $info["link_full"] = "";
            if (!empty($info["link"])) {
                $info["link_full"] = $info["link"] . $info["link_param"];
            }
        }

        return $info;
    }

    // 删除用户消息
    public function deleteInfo($user_id, $msg_id){
        try {
            $msgSystemModel = new MsgSystem();
            $ret = $msgSystemModel->deleteMsgRelated($msg_id, $user_id);
            if ($ret === false) {
                throw new Exception("删除失败");
            }

            // 如果消息下没有关联的其它人员删除消息本身
            $count = $msgSystemModel->getMsgRelatedCount($msg_id);
            if ($count == 0) {
                $msgSystemModel->deleteInfo($msg_id);
            }

            $result = true;
        } catch (\Exception $e) {
            $result = $e->getMessage();
        }

        return $result;
    }
}