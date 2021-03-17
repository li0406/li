<?php

namespace Home\Model;
use Think\Model;

/**
 * 系统菜单
 */
class SystemMenuModel extends Model{
    protected $autoCheckFields = false;
    protected $tableName = "rbac_system_menu";

    /**
     * 获取全部的菜单信息
     * @return [type] [description]
     */
    public function getMenuList(){
        return M("rbac_system_menu")->order("level,px,enabled desc,id")->select();
    }

    /**
     * 根据ID获取菜单信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getMenuInfoById($id){
        $map = array(
            "id" => array("EQ",$id)
                     );
        $buildSql = M("rbac_system_menu")->where($map)->buildSql();
        return  M("rbac_system_menu")->table($buildSql)->alias("a")
                                     ->join("LEFT JOIN qz_rbac_system_menu as b on a.parentid = b.nodeid")
                                     ->field("a.*,b.name as pname,b.id as pid,b.level as plevel")
                                     ->find();
    }


    /**
     * 获取父节点最新的子节点ID
     * @param  [type] $parentid [description]
     * @return [type]           [description]
     */
    public function getMenuNodeMaxId($parentid){
        $map = array(
            "parentid" => array("EQ",$parentid)
                     );
        return M("rbac_system_menu")->where($map)->order("time desc,id desc")->field("nodeid")->find();

    }

    /**
     * 添加菜单
     * @param [type] $data [description]
     */
    public function addMenu($data){
        return M("rbac_system_menu")->add($data);
    }

    /**
     * 编辑菜单
     * @param  [type] $id   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function editMenu($id,$data){
        $map = array(
            "id" => array("EQ",$id)
                     );
        return M("rbac_system_menu")->where($map)->save($data);
    }

    /**
     * 获取子菜单信息
     * @param  [type] $nodeid [description]
     * @return [type]         [description]
     */
    public function getChildNodeList($nodeid){
        $map = array(
            "parentid" => array("EQ",$nodeid)
                     );
        $buildSql = M("rbac_system_menu")->where($map)->buildSql();
        return  M("rbac_system_menu")->table($buildSql)->alias("a")
                                     ->join("LEFT JOIN qz_rbac_system_menu as b on a.nodeid = b.parentid")
                                     ->field("b.*,a.name as pname,a.id as pid,a.nodeid as pnodeid")
                                     ->select();
    }
}