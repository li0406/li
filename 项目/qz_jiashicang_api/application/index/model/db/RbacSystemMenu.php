<?php
namespace app\index\model\db;
use think\Model;
/**
 *
 */
class RbacSystemMenu extends Model
{
    public function getAllMenu()
    {
        $map[] = ["enabled","EQ",1];
        $map[] = ["version","EQ",10];

        return $this->where($map)
            ->field("nodeid,parentid,name,link,component,icon,level,link_type")
            ->order("px,parentid,nodeid")->select();
    }

    public function getRoleMenu($role_id)
    {
        $map[] = ["a.enabled","EQ",1];
        $map[] = ["a.version","EQ",10];
        $map[] = ["b.role_id","EQ",$role_id];

        $list = $this->where($map)->alias("a")
             ->join("rbac_node_role b","b.node_id = a.nodeid")
             ->field("nodeid,parentid,name,link,component,icon,level,link_type")
             ->order("a.px,parentid,nodeid")
             ->select();

        return $list;
    }
}