<?php

namespace Home\Model;
use Think\Model;

/**
 * 权限关联表
 */
class RbacNodeRoleModel extends Model{
    protected $autoCheckFields = false;
    /**
     * 根据菜单编号修改菜单信息
     * @param  [type] $nodeid [description]
     * @param  [type] $data   [description]
     * @return [type]         [description]
     */
    public function editRoleNode($nodeid,$data){
        $map = array(
            "node_id" => array("EQ",$nodeid)
                     );
        return M("rbac_node_role")->where($map)->save($data);
    }

    /**
     * 获取角色的权限菜单
     * @param  [type] $roleId [description]
     * @return [type]         [description]
     */
    public function getUserRoleNode($roleId){
        $map = array(
            "role_id" => array("EQ",$roleId)
                     );
        return M("rbac_node_role")->where($map)->select();
    }

    /**
     * 删除权限
     * @param  [type] $roleId [description]
     * @return [type]         [description]
     */
    public function delUserRoleNode($roleId){
        $map = array(
            "role_id" => array("EQ",$roleId)
                     );
        return M("rbac_node_role")->where($map)->delete();
    }

    /**
     * 添加多个权限
     * @param [type] $data [description]
     */
    public function addAllRoleNode($data){
        return M("rbac_node_role")->addAll($data);
    }
}