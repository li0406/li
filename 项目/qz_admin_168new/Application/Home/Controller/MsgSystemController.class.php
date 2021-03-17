<?php

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\MsgSystemLogicModel;

class MsgSystemController extends HomeBaseController{

    /**
     * 未读消息
     * @return [type] [description]
     */
    public function remind(){
        $admin = getAdminUser();
        $app_slot = C("SMS_APP_SLOT");
        $msgSystemLogic = new MsgSystemLogicModel();
        $data = $msgSystemLogic->getRemind($app_slot, $admin["id"]);
        $this->ajaxReturn(["status" => 1, "info" => "ok", "data" => $data]);
    }

    /**
     * 消息中心消息列表
     * @return [type] [description]
     */
    public function msglist(){
        $admin = getAdminUser();
        $app_slot = C("SMS_APP_SLOT");

        $msgSystemLogic = new MsgSystemLogicModel();
        $typeList = $msgSystemLogic->getMsgType($app_slot, $admin["id"]);

        $page = I("get.p", "1");
        $limit = I("get.limit", 20);

        $type_id = I("get.type_id");
        if (empty($type_id) && count($typeList) > 0) {
            $type_id = $typeList[0]["id"];
        }

        $data = $msgSystemLogic->getPageList($app_slot, $admin["id"], $type_id, $page, $limit);
        $msgSystemLogic->setMsgRead($app_slot, $admin["id"], $type_id); // 设置为已读

        import('Library.Org.Util.Page');
        $page = new \Page($data["count"], $limit);
        $data["pageshow"] = $page->show();

        $this->assign("data", $data);
        $this->assign("type_id", $type_id);
        $this->assign("type_list", $typeList);
        $this->display();
    }

    /**
     * 标记为已读
     * @return [type] [description]
     */
    public function setread(){
        $id = I("param.id");
        $admin = getAdminUser();
        $app_slot = C("SMS_APP_SLOT");

        $msgSystemLogic = new MsgSystemLogicModel();
        $result = $msgSystemLogic->setMsgRead($app_slot, $admin["id"], $id);
        if (empty($result)) {
            $this->ajaxReturn(["status" => 0, "info" => "操作失败"]);
        }

        $this->ajaxReturn(["status" => 1, "info" => "ok"]);
    }

    public function getMsgInfo(){
        $admin = getAdminUser();
        $app_slot = C("SMS_APP_SLOT");
        $send_group = I("param.send_group");

        $msgSystemLogic = new MsgSystemLogicModel();
        $info = $msgSystemLogic->getMsgInfo($app_slot, $admin["id"], $send_group);

        if (!empty($info)) {
            $this->ajaxReturn([
                "error_code" => 0,
                "error_msg" => "fetch:ok",
                "data" => ["info" => $info]
            ]);
        }

        $this->ajaxReturn(["error_code" => 5000002, "error_msg" => "消息不存在"]);
    }

    // 删除消息
    public function deleteMsg(){
        if (IS_POST) {
            $admin = getAdminUser();
            $msg_id = I("post.msg_id");

            $msgSystemLogic = new MsgSystemLogicModel();
            $ret = $msgSystemLogic->deleteMsg($admin["id"], $msg_id);

            if ($ret !== true) {
                $this->ajaxReturn(["error_code" => 5000002, "error_msg" => $ret]);
            }

            $this->ajaxReturn(["error_code" => 0, "error_msg" => "删除成功"]);
        }
    }
}