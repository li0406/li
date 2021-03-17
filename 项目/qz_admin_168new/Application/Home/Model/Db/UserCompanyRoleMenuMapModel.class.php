<?php
namespace Home\Model\Db;

use Think\Model;
/**
 *
 */
class UserCompanyRoleMenuMapModel extends Model
{
    public function getRoleMenu($user_id)
    {
        $map = [
            "role_id" => ["EQ",$user_id]
        ];
        return $this->where($map)->field("menu_id")->select();
    }

    public function delRoleMenu($user_id,$model)
    {
        $map = [
            "role_id" => ["EQ",$user_id],
            "model" => ["EQ",$model]
        ];
        return $this->where($map)->delete();
    }

    public function addAllInfo($data)
    {
        return $this->addAll($data);
    }
}