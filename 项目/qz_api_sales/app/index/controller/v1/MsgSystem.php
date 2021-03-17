<?php

namespace app\index\controller\v1;

use think\Request;
use think\Controller;

use app\common\model\logic\MsgSystemLogic;

use app\common\enum\BaseStatusCodeEnum;

Class MsgSystem extends Controller {

    private $app_slot;

    public function initialize(){
        $this->app_slot = config("SMS_APP_SLOT");
    }

    /**
     * 获取未读消息提醒数据
     * @param  Request        $request        [description]
     * @param  MsgSystemLogic $msgSystemLogic [description]
     * @return [type]                         [description]
     */
    public function getRemind(Request $request, MsgSystemLogic $msgSystemLogic){
        $user = $request->user;
        $no_read_count = $msgSystemLogic->getNoReadCount($this->app_slot, $user["user_id"]);

        $response = sys_response(0, "", [
            "no_read_count" => $no_read_count,
        ]);
        
        return json($response);
    }

    /**
     * 获取消息列表
     * @param  Request        $request        [description]
     * @param  MsgSystemLogic $msgSystemLogic [description]
     * @return [type]                         [description]
     */
    public function getList(Request $request, MsgSystemLogic $msgSystemLogic){
        $page = sys_page_format($request->page, 1);
        $page_size = sys_page_format($request->page_size, 20);
        $type_id = $request->param("type_id");

        $user = $request->user;
        $data = $msgSystemLogic->getPageList($this->app_slot, $user["user_id"], $type_id, $page, $page_size);

        $response = sys_response(0, "", [
            "list" => $data["list"],
            "page" => sys_paginate($data["count"], $page_size, $page)
        ]);
        
        return json($response);
    }

    /**
     * 设置为已读
     * @param  Request        $request        [description]
     * @param  MsgSystemLogic $msgSystemLogic [description]
     * @return [type]                         [description]
     */
    public function setRead(Request $request, MsgSystemLogic $msgSystemLogic){
        $id = $request->param("id");
        if (empty($id)) {
            return json(sys_response(4000002));
        }

        $user = $request->user;
        $ret = $msgSystemLogic->setMsgRead($this->app_slot, $user["user_id"], 0, $id);
        if ($ret === false) {
            return json(sys_response(5000002, "操作失败"));
        }

        return json(sys_response(0));
    }

    /**
     * 消息类型列表
     * @param Request $request
     * @param MsgSystemLogic $msgSystemLogic
     */
    public function getTypeList(Request $request, MsgSystemLogic $msgSystemLogic){
        $user = $request->user;
        $type_list = $msgSystemLogic->getAppMsgType($this->app_slot, $user["user_id"]);

        $response = sys_response(0, "", [
            "list" => $type_list
        ]);

        return json($response);
    }

    /**
     * 根据组ID获取消息详情
     * @param Request $request
     * @param MsgSystemLogic $msgSystemLogic
     */
    public function getInfo(Request $request, MsgSystemLogic $msgSystemLogic){
        $send_group = $request->param("send_group");

        $user_id = $request->user["user_id"];
        $info = $msgSystemLogic->getMsgInfo($this->app_slot, $user_id, $send_group);

        $response = sys_response(4000002, "消息不存在");
        if (!empty($info)) {
            $response = sys_response(0, "", [
                "info" => $info
            ]);
        }

        return json($response);
    }

    /**
     * 删除消息
     * @param  Request        $request        [description]
     * @param  MsgSystemLogic $msgSystemLogic [description]
     * @return [type]                         [description]
     */
    public function deleteInfo(Request $request, MsgSystemLogic $msgSystemLogic){
        $msg_id = $request->post("msg_id");
        if (empty($msg_id)) {
            return json(sys_response(4000002));
        }

        $user_id = $request->user["user_id"];
        $ret = $msgSystemLogic->deleteInfo($user_id, $msg_id);
        if ($ret !== true) {
            $response = sys_response(5000002, $ret);
        } else {
           $response = sys_response(0);
        }

        return json($response);
    }

}