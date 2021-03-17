<?php
namespace Home\Model\Db;

use Think\Model;
/**
 *
 */
class UserCompanyRoleModel extends Model
{
    public function getRoleList()
    {
        return $this->order("id")->select();
    }

    public function getRoleInfo($id)
    {
        $map = [
            "id" => ["EQ",$id]
        ];
        return $this->where($map)->find();
    }

    public function getRoleInfoByName($role_name)
    {
        $map = [
            "role_name" => ["EQ",$role_name]
        ];
        return $this->where($map)->find();
    }

    public function addRole($data)
    {
        return $this->add($data);
    }

    public function updateRole($id,$data)
    {
        $map = [
            "id" => ["EQ",$id]
        ];
        return $this->where($map)->save($data);
    }
}