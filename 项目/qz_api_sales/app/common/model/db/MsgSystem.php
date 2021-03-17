<?php

namespace app\common\model\db;

use think\Db;
use think\Model;
use think\db\Query;
use think\db\Where;

class MsgSystem extends Model {

    protected $autoWriteTimestamp = false;

    /**
     * 获取未读消息数量
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function getNoReadCount($app_slot, $user_id){
        $map = new Query();
        $map->where("b.is_read", 2);
        $map->where("t.enabled", 1);
        $map->where("b.user_id", $user_id);
        $map->where("app.app_slot", $app_slot);

        return $this->alias("a")->where($map)
            ->join("msg_system_related b", "b.msg_id = a.id", "inner")
            ->join("msg_type t", "t.id = a.type_id", "inner")
            ->join("msg_type_app app", "app.type_id = t.id", "inner")
            ->count("a.id");
    }

    /**
     * 获取未读消息
     * @param  [type]  $user_id [description]
     * @param  integer $limit   [description]
     * @return [type]           [description]
     */
    public function getNoReadList($app_slot, $user_id, $limit = 0){
        $map = new Query();
        $map->where("b.is_read", 2);
        $map->where("t.enabled", 1);
        $map->where("b.user_id", $user_id);
        $map->where("app.app_slot", $app_slot);

        return $this->alias("a")->where($map)
            ->join("msg_system_related b", "b.msg_id = a.id", "inner")
            ->join("msg_type t", "t.id = a.type_id", "inner")
            ->join("msg_type_app app", "app.type_id = t.id", "inner")
            ->field(["a.id", "a.user_id", "a.type_id", "a.title", "a.notice", "a.action_id", "a.link_param", "a.template_id", "b.is_read"])
            ->order("a.created_at desc,a.id desc")
            ->limit($limit)->select();
    }

    /**
     * 获取消息分页数量
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function getPageCount($app_slot, $user_id, $type_id = 0){
        $map = new Query();
        $map->where("t.enabled", 1);
        $map->where("b.user_id", $user_id);
        $map->where("app.app_slot", $app_slot);

        if ($type_id) {
            $map->where("t.id", $type_id);
        }

        return $this->alias("a")->where($map)
            ->join("msg_system_related b", "b.msg_id = a.id", "inner")
            ->join("msg_type t", "t.id = a.type_id", "inner")
            ->join("msg_type_app app", "app.type_id = t.id", "inner")
            ->count("a.id");
    }

    /**
     * 获取分页列表数据
     * @param  [type]  $user_id [description]
     * @param  integer $offset  [description]
     * @param  integer $limit   [description]
     * @return [type]           [description]
     */
    public function getPageList($app_slot, $user_id, $type_id= 0, $offset = 0, $limit = 0){
        $map = new Query();
        $map->where("t.enabled", 1);
        $map->where("b.user_id", $user_id);
        $map->where("app.app_slot", $app_slot);

        if ($type_id) {
            $map->where("t.id", $type_id);
        }

        return $this->alias("a")->where($map)
            ->join("msg_system_related b", "b.msg_id = a.id", "inner")
            ->join("msg_type t", "t.id = a.type_id", "inner")
            ->join("msg_type_app app", "app.type_id = t.id", "inner")
            ->field(["a.*", "b.user_id", "b.is_read"])
            ->order("a.created_at desc,a.id desc")
            ->limit($offset, $limit)
            ->select();
    }

    /**
     * 设置为已读
     * @param [type] $msg_id  [description]
     * @param [type] $user_id [description]
     */
    public function setIsRead($app_slot, $user_id, $type_id = 0, $msg_id = 0){
        $map = new Query();
        $map->where("b.user_id", $user_id);
        $map->where("app.app_slot", $app_slot);
        $map->where("b.is_read", "neq", 1);

        if (!empty($type_id)) {
            $map->where("a.type_id", $type_id);
        }

        if (!empty($msg_id)) {
            if (is_array($msg_id)) {
                $map->where("b.msg_id", "in", $msg_id);
            } else {
                $map->where("b.msg_id", $msg_id);
            }
        }

        $data = [
            "b.is_read" => 1,
            "b.updated_at" => time()
        ];

        return $this->alias("a")->where($map)
            ->join("msg_system_related b", "b.msg_id = a.id", "inner")
            ->join("msg_type_app app", "app.type_id = a.type_id", "inner")
            ->update($data);
    }

    public function getInfoByGroup($app_slot, $user_id, $send_group){
        $map = new Query();
        $map->where("r.user_id", $user_id);
        $map->where("m.send_group", $send_group);

        return $this->alias("m")->where($map)
            ->join("msg_system_related r", "r.msg_id = m.id", "inner")
            ->join("msg_template_link l", "l.template_id = m.template_id and l.app_slot = '$app_slot'", "left")
            ->field("m.id,m.`title`,m.`notice`,m.send_group,m.link_param,l.`link`")
            ->find();
    }

    // 删除消息
    public function deleteInfo($id){
        return $this->where("id", $id)->delete();
    }

    // 删除用户消息关联数据
    public function deleteMsgRelated($msg_id, $user_id){
        $map = new Query();
        $map->where("msg_id", $msg_id);
        $map->where("user_id", $user_id);

        return Db::name("msg_system_related")->where($map)->delete();
    }

    // 获取消息关联数量
    public function getMsgRelatedCount($msg_id){
        $map = new Query();
        $map->where("msg_id", $msg_id);

        return Db::name("msg_system_related")->where($map)->count("id");
    }

}