<?php
namespace Home\Model;

use Think\Model;

class RbacNodeGroupModel extends Model{
    protected $autoCheckFields = false;
    /**
     * 获取用户组列表
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public function getRoleGroups($name){
        return M("rbac_node_group")->alias("a")
                                   ->join("inner join qz_rbac_role as b on find_in_set(b.id,a.role_id)")
                                   ->group("a.id")
                                   ->field("a.*,group_concat(b.role_name) as groups")
                                   ->select();
    }

    public function setStatus($id, $type = '0'){
        $data['stat'] = $type;
        $map['id'] = $id;
        return M("rbac_node_group")->where($map)->save($data);
    }

    /**
     * 删除
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delRbacGroup($id)
    {
        $map = array(
           "id"=>array("EQ",$id)
        );
        return M("rbac_node_group")->where($map)->delete();
    }


    /**
     * 添加用户组
     */
    public function addRoleGroup($data){
        return M("rbac_node_group")->add($data);
    }

    /**
     * 编辑用户组
     */
    public function editRoleGroup($id,$data){
        $map = array(
                      "id"=>array("EQ",$id)
                     );
        return M("rbac_node_group")->where($map)->save($data);
    }

    /**
     * 获取用户组详细信息
     */
    public function getRoleGroupInfo($id){
        $map = array(
                "id"=>array("EQ",$id)
                     );
        return M("rbac_node_group")->where($map)->find();
    }

    public function getRoleGroupList($name)
    {
        $map = array(
            "a.group_id" => array("NEQ",0)
        );

        if (!empty($name)) {
            $map["a.group_name"] = array("EQ",$name);
        }

        return M("rbac_node_group")->where($map)->alias("a")->select();
    }

    public function findRoleGroupInfoCount($id)
    {
        $map = array(
            "a.id" => array("EQ",$id)
        );
        $buildSql =  M("rbac_node_group")->where($map)->alias("a")
                                         ->join("left join qz_rbac_role b on find_in_set(b.id,a.role_id)")
                                         ->field("b.id,b.role_name")->buildSql();
        return M("rbac_node_group")->table($buildSql)->alias("t")->count();
    }

    public function findRoleGroupInfo($id)
    {
        $map = array(
            "a.id" => array("EQ",$id)
        );

        $buildSql =  M("rbac_node_group")->where($map)->alias("a")
                                         ->join("left join qz_rbac_role b on find_in_set(b.id,a.role_id)")
                                         ->field("b.id,b.role_name")->buildSql();
        return M("rbac_node_group")->table($buildSql)->alias("t")
                            ->join("left join qz_role_department b on t.id = b.role_id")
                            ->join("left join qz_department d on d.id = b.department_id")
                            ->join("left join qz_department e on e.id = d.parentid")
                            ->join("left join qz_department f on f.id = e.parentid")
                            ->field("t.role_name,d.name as first_name,e.name as second_name,f.name as three_name")
                            ->select();

    }

    /**
     * 获取角色管辖的角色信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getRoleGroupInfoByRoleId($roleId)
    {
        $map = array(
            "group_id" => array("EQ",$roleId)
        );
        return M("rbac_node_group")->where($map)->find();
    }
}