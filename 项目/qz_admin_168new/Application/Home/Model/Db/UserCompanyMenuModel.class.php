<?php

namespace Home\Model\Db;

use Think\Model;
/**
 *
 */
class UserCompanyMenuModel extends Model
{
    public function getDropDownMenu($menu_model)
    {
        $map = [
            "is_enabled" => ["EQ",1],
            "parent_node_id" => 0,
            "model" => ["EQ",$menu_model]
        ];
        return $this->where($map)->field("id,menu_name,menu_node_id")->order("px,id")->select();
    }

    public function getDropDownMenuChild($menu_model)
    {
        $map = [
            "a.is_enabled" => ["EQ",1],
            "a.parent_node_id" => 0,
            "b.is_enabled" => ["EQ",1],
        ];

        if (!empty($menu_model)) {
            $map["a.model"] = ["EQ",$menu_model];
        }

        return $this->where($map)->alias("a")
                    ->join("left join qz_user_company_menu b on a.menu_node_id = b.parent_node_id")
                    ->field("a.menu_name,a.menu_node_id,b.menu_name as c_menu_name,  b.menu_node_id as c_menu_node_id,b.id  as c_id,b.is_public as c_is_pulbic")->order("a.px,b.px,a.id,b.id")->select();

    }

    public function getMenuInfo($id)
    {
        $map = [
            "id" => ["EQ",$id],
        ];
        return $this->where($map)->find();
    }

    public function getMenuInfoByName($menu_name,$level,$model)
    {
        $map = [
            "menu_name" => ["EQ",$menu_name],
            "menu_level" => ["EQ",$level],
            "model" => ["EQ",$model]
        ];
        return $this->where($map)->find();
    }

    public function getParentLastChildNodeId($node_id)
    {
        $map = [
            "a.parent_node_id" => ["EQ",$node_id],
        ];
        return $this->where($map)->alias("a")
                    ->field("a.menu_node_id,a.parent_node_id")
                    ->order("a.id desc")->find();
    }

    public function updateMenu($id,$data)
    {
        $map["id"] = ["IN",$id];
        return $this->where($map)->save($data);
    }

    public function addMenu($data)
    {
        return $this->add($data);
    }

    public function getMenuList()
    {
        return $this->order("px,id")->select();
    }

    public function getRoleMenuList()
    {
        $map = [
            "is_enabled" => ["EQ",1]
        ];
        return $this->where($map)->order("px,created_at")->select();
    }

    public function updateCommonMenu($id,$data)
    {
        if (count($id) > 0) {
            $map["id"] = ["IN",$id];
        } else {
            $map = [
                "is_public" => ["EQ",1]
            ];
        }

        return $this->where($map)->save($data);
    }

    public function getParentNodeId($menu_id)
    {
        $map["a.id"] = ["IN",$menu_id];
        return $this->where($map)->alias("a")
                    ->join("join qz_user_company_menu b on a.parent_node_id = b.menu_node_id")
                    ->field("b.id")->select();
    }

    public function getMenuInfoById($menu_id)
    {
        $map["id"] = ["EQ",$menu_id];
        return $this->where($map)->field("parent_node_id")->find();
    }

    public function getNodeIds($menu_id)
    {
        $map["a.id"] = ["EQ",$menu_id];
        return $this->where($map)->alias("a")
             ->join("left join qz_user_company_menu b on a.menu_node_id = b.parent_node_id")
             ->join("left join qz_user_company_menu c on b.menu_node_id = c.parent_node_id")
             ->field("a.id as first,b.id  as second,c.id as third")->select();

    }
}