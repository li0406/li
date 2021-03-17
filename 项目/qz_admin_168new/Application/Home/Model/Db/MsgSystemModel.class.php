<?php

namespace Home\Model\Db;

use Think\Model;

class MsgSystemModel extends Model {

    /**
     * 获取未读消息数量
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function getNoReadCount($app_slot, $user_id){
        $map = array(
            "b.is_read" => array("EQ", 2),
            "t.enabled" => array("EQ", 1),
            "b.user_id" => array("EQ", $user_id),
            "app.app_slot" => array("EQ", $app_slot)
        );

        return $this->alias("a")->where($map)
            ->join("inner join qz_msg_system_related as b on b.msg_id = a.id")
            ->join("inner join qz_msg_type as t on a.type_id = t.id")
            ->join("inner join qz_msg_type_app as app on app.type_id = t.id")
            ->count("a.id");
    }

    /**
     * 获取未读消息
     * @param  [type]  $user_id [description]
     * @param  integer $limit   [description]
     * @return [type]           [description]
     */
    public function getNoReadList($app_slot, $user_id, $limit = 0){
        $map = array(
            "b.is_read" => array("EQ", 2),
            "t.enabled" => array("EQ", 1),
            "b.user_id" => array("EQ", $user_id),
            "app.app_slot" => array("EQ", $app_slot)
        );

        return $this->alias("a")->where($map)
            ->join("inner join qz_msg_system_related as b on b.msg_id = a.id")
            ->join("inner join qz_msg_type as t on a.type_id = t.id")
            ->join("inner join qz_msg_type_app as app on app.type_id = t.id")
            ->order("a.created_at desc,a.id desc")
            ->field(["a.id", "a.user_id", "a.type_id", "a.title", "a.notice", "a.action_id", "a.link_param", "a.template_id", "b.is_read"])
            ->limit($limit)->select();
    }

    /**
     * 获取消息分页数量
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function getPageCount($app_slot, $user_id, $type_id = 0){
        $map = array(
            "t.enabled" => array("EQ", 1),
            "b.user_id" => array("EQ", $user_id),
            "app.app_slot" => array("EQ", $app_slot)
        );

        if ($type_id) {
            $map["t.id"] = array("EQ", $type_id);
        }

        return $this->alias("a")->where($map)
            ->join("inner join qz_msg_system_related as b on b.msg_id = a.id")
            ->join("inner join qz_msg_type as t on a.type_id = t.id")
            ->join("inner join qz_msg_type_app as app on app.type_id = t.id")
            ->count("a.id");
    }

    /**
     * 获取分页列表数据
     * @param  [type]  $user_id [description]
     * @param  integer $offset  [description]
     * @param  integer $limit   [description]
     * @return [type]           [description]
     */
    public function getPageList($app_slot, $user_id, $type_id = 0, $offset = 0, $limit = 0){
        $map = array(
            "t.enabled" => array("EQ", 1),
            "b.user_id" => array("EQ", $user_id),
            "app.app_slot" => array("EQ", $app_slot)
        );

        if ($type_id) {
            $map["t.id"] = array("EQ", $type_id);
        }

        return $this->alias("a")->where($map)
            ->join("inner join qz_msg_system_related as b on b.msg_id = a.id")
            ->join("inner join qz_msg_type as t on a.type_id = t.id")
            ->join("inner join qz_msg_type_app as app on app.type_id = t.id")
            ->order("a.created_at desc,a.id desc")
            ->field(["a.*", "b.user_id", "b.is_read"])
            ->limit($offset, $limit)
            ->select();
    }

    // 获取未读消息关联数据ID
    public function getUnReadRelatedList($app_slot, $user_id, $type_id = "", $msg_id = ""){
        $map = array(
            "app.app_slot" => array("EQ", $app_slot),
            "b.user_id" => array("EQ", $user_id),
            "b.is_read" => array("NEQ", 1)
        );

        if (!empty($type_id)) {
            $map["a.type_id"] = array("EQ", $type_id);
        }

        if (!empty($msg_id)) {
            if (is_array($msg_id)) {
                $map["b.msg_id"] = array("IN", $msg_id);
            } else {
                $map["b.msg_id"] = array("EQ", $msg_id);
            }
        }

        return $this->alias("a")->where($map)
            ->join("inner join qz_msg_system_related as b on b.msg_id = a.id")
            ->join("inner join qz_msg_type_app as app on app.type_id = a.type_id")
            ->field("b.id")
            ->select();
    }
    
    /**
     * 设置为已读
     * @param [type] $user_id [description]
     * @param string $msg_id  [description]
     */
    public function setIsRead($ids){
        $map = array(
            "id" => array("IN", $ids)
        );

        $data = [
            "is_read" => 1,
            "updated_at" => time()
        ];
        return M("msg_system_related")->where($map)->save($data);
    }

    public function getInfoByGroup($app_slot, $user_id, $send_group){
        $map = array(
            "m.send_group" => array("EQ", $send_group),
            "r.user_id" => array("EQ", $user_id),
        );

        return $this->alias("m")->where($map)
            ->join("inner join qz_msg_system_related as r on r.msg_id = m.id")
            ->join("left join qz_msg_template_link as l on l.template_id = m.template_id and l.app_slot = '$app_slot'")
            ->field("m.id,m.`title`,m.`notice`,m.send_group,m.link_param,l.`link`")
            ->find();
    }

    // 删除消息
    public function deleteInfo($id){
        return $this->where(["id" => $id])->delete();
    }

    // 删除用户消息关联数据
    public function deleteMsgRelated($msg_id, $user_id){
        $map = array(
            "msg_id" => array("EQ", $msg_id),
            "user_id" => array("EQ", $user_id),
        );

        return M("msg_system_related")->where($map)->delete();
    }

    // 获取消息关联数量
    public function getMsgRelatedCount($msg_id){
        $map = array(
            "msg_id" => array("EQ", $msg_id)
        );

        return M("msg_system_related")->where($map)->count("id");
    }
}