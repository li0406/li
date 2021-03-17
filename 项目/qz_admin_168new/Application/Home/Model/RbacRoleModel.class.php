<?php

namespace Home\Model;

use Think\Model;

/**
 * 系统角色表
 */

class RbacRoleModel extends Model {
    public function _initialize(){
        //获取可使用的角色
        $this->role = S("Cache:adminrole");
        if(!$this->role){
            $map = array(
                "stat"=>array("EQ",1),
                "id"=>array("NEQ",1)
                         );
            $this->role = M("rbac_role")->field("id,role_name")->where($map)->order("id")->select();
            S("Cache:adminrole",$this->role,3600*24);
        }
    }

    /**
     * 获取全部角色
     * @return [type] [description]
     */
    public function getAllRoles() {
        return M("rbac_role")->select();
    }

    /**
     * 根据用户角色获取管辖的用户组
     * @return [type] [description]
     */
    public function getMyRoleList($uid) {
        $map = array(
            "id" => array("IN",$uid)
                     );

        return M("rbac_role")->where($map)->field("id,role_name")->select();

    }
    /**
     * [checkRoleExist 检测这个权限模块组是否已经有了]
     * @param  string $role_name [权限模块组的名称]
     * @return [bool]                  [返回是否有]
     */
    public function checkRoleExist($role) {
        //检测是否有这个角色
        return M('rbac_role')->where(array('role_name'=>$role,"stat"=> 1))->find();

    }

    /**
     * [addRole 添加角色]
     * @param array $data [数组]
     */
    public function addRole($data) {
        return M('rbac_role')->add($data);//增加数据
    }


    /**
     * [saveRole 修改角色]
     * @param  array   $data [分组数据]
     * @param  integer $id   [数据id]
     * @return [type]        [返回信息]
     */
    public function saveRole($data, $id)
    {
        if(empty($id)) {
            return false;
        }
        return M('rbac_role')->where(array('id'=>$id))->save($data);//修改数据
    }

    public function getRoleInfo($id)
    {
        foreach ($this->role as $key => $value) {
            if ($value["id"] == $id) {
                $role = $value;
                break;
            }
        }
        return $role;
    }

    /**
     * 获取部门juese
     * @param  [type] $dept [description]
     * @return [type]       [description]
     */
    public function getDpartmentRoles($dept,$name,$deptid)
    {
        $map = array(
            "a.department_id" => array("IN",$dept),
            "b.stat" => array("EQ", 1)
        );

        if (!empty($name)) {
            $map = array(
                "b.role_name" => array("EQ",$name)
            );
        }

        if (!empty($deptid)) {
            $map ["a.department_id"] = array("EQ",$deptid);
        }

        return M("role_department")->where($map)->alias("a")
                                   ->join("join qz_rbac_role b on a.role_id = b.id")
                                   ->field("a.department_id,b.role_name,b.id")
                                   ->select();
    }

    /**
     * 获取部门juese
     * @param  [type] $dept [description]
     * @return [type]       [description]
     */
    public function getDpartmentGradeRoles($dept)
    {
        $map = array(
            "a.department_id" => array("IN", $dept),
            "b.stat" => array("EQ", 1)
        );

        return M("role_department")->where($map)->alias("a")
                                   ->join("join qz_department d on d.id = a.department_id")
                                   ->join("join qz_rbac_role b on a.role_id = b.id")
                                   ->join("left join qz_rbac_role p1 on b.parentid = p1.id")
                                   ->join("left join qz_rbac_role p2 on p1.parentid = p2.id")
                                   ->join("left join qz_rbac_role p3 on p2.parentid = p3.id")
                                   ->join("left join qz_rbac_role c on c.parentid = b.id and c.stat = 1")
                                   ->field([
                                        "b.id", "b.role_name", "a.department_id", "d.name department_name",
                                        "b.parentid", "b.grade", "b.created_at",
                                        "p1.id p1_id", "p1.role_name p1_name", "p2.id p2_id", "p2.role_name p2_name",
                                        "p3.id p3_id", "p3.role_name p3_name",
                                        "concat(IFNULL(p1.id, ''), ',', IFNULL(p2.id, ''), ',', IFNULL(p3.id, '')) parentids",
                                        "count(c.id) childnum"
                                    ])->group("b.id")->select();

    }

    /**
     * 获取每个部门下的角色列表
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getRoleListByDept($deptId = "")
    {
        $map = array(
            "parentid" => array("EQ",0),
            "enabled" => array("EQ",0)
        );

        if (!empty($deptId)) {
            $map["id"] = array("EQ",$deptId);
        }

        $buildSql = M("department")->where($map)->field("id,name")->order("id")->buildSql();

        $buildSql = M("department")->table($buildSql)->alias("t")
                          ->join("left join qz_department b on b.parentid = t.id and b.enabled = 0")
                          ->join("left join qz_department c on c.parentid = b.id and b.enabled = 0")
                          ->field("t.*,b.id as second_id, b.name as second_name,c.id as three_id, c.name as three_name")
                          ->buildSql();

        $buildSql =  M("department")->table($buildSql)->alias("t1")
                           ->join("left join qz_role_department b on b.department_id = t1.id")
                           ->join("left join qz_role_department c on c.department_id = t1.second_id")
                           ->join("left join qz_role_department d on d.department_id = t1.three_id")
                           ->field("t1.id,t1.name,CONCAT(IFNULL(GROUP_CONCAT(DISTINCT b.role_id),''),',',IFNULL(GROUP_CONCAT(DISTINCT c.role_id),''),',',IFNULL(GROUP_CONCAT(DISTINCT d.role_id),'')) as ids")
                           ->group("t1.id")->buildSql();

        return M("department")->table($buildSql)->alias("t2")
                              ->join("join qz_rbac_role r on find_in_set(r.id,t2.ids)")
                              ->field("t2.id,t2.name,r.role_name,r.id as role_id")->select();
    }

    /**
     * 获取部门下所有的角色
     * @return [type] [description]
     */
    public function getAllRoleByEnabled()
    {
        $map = array(
            "stat" => array("EQ",1),
            "id" => array("NEQ",1)
        );
        return  M('rbac_role')->where($map)->select();
    }

    /**
     * 获取部门下的角色ID
     * @DateTime 2019-05-28T09:47:38+0800
     */
    public function getRoleIdsByDept($deptId = ""){
        $map = array(
            "parentid" => array("EQ",0),
            "enabled" => array("EQ",0)
        );

        if (!empty($deptId)) {
            $map["id"] = array("EQ",$deptId);
        }

        $buildSql = M("department")->where($map)->field("id,name")->order("id")->buildSql();

        $buildSql = M("department")->table($buildSql)->alias("t")
                          ->join("left join qz_department b on b.parentid = t.id and b.enabled = 0")
                          ->join("left join qz_department c on c.parentid = b.id and b.enabled = 0")
                          ->field("t.*,b.id as second_id, b.name as second_name,c.id as three_id, c.name as three_name")
                          ->buildSql();

        return M("department")->table($buildSql)->alias("t1")
                           ->join("left join qz_role_department b on b.department_id = t1.id")
                           ->join("left join qz_role_department c on c.department_id = t1.second_id")
                           ->join("left join qz_role_department d on d.department_id = t1.three_id")
                           ->field("t1.id,t1.name,CONCAT(IFNULL(GROUP_CONCAT(DISTINCT b.role_id),''),',',IFNULL(GROUP_CONCAT(DISTINCT c.role_id),''),',',IFNULL(GROUP_CONCAT(DISTINCT d.role_id),'')) as ids")
                           ->group("t1.id")->select();
    }

    /**
     * 通过角色ID获取角色列表
     * @DateTime 2019-05-28T10:00:41+0800
     */
    public function getRoleListByIds($roleIds = ""){
        $map = array(
            "r.stat" => array("EQ", 1)
        );

        if (!empty($roleIds)) {
            $map["r.id"] = array("IN", $roleIds);
        }

        return M('rbac_role')->alias('r')
            ->join('left join qz_rbac_role p1 on r.parentid = p1.id')
            ->join('left join qz_rbac_role p2 on p1.parentid = p2.id')
            ->join('left join qz_rbac_role p3 on p2.parentid = p3.id')
            ->field(['r.id', 'r.role_name', 'p1.id p1_id', 'p1.role_name p1_name', 'p2.id p2_id', 'p2.role_name p2_name', 'p3.id p3_id', 'p3.role_name p3_name'])->where($map)->select();
    }

    /**
     * 获取子级数量
     * @DateTime 2019-05-28T17:37:13+0800
     */
    public function getChildrenCountById($id){
        $map = array(
            "parentid" => array("EQ", $id),
            "stat" => array("EQ", 1)
        );
        return M("rbac_role")->where($map)->count("id");
    }

    /**
     * 获取角色信息
     * @DateTime 2019-05-29T13:17:42+0800
     */
    public function getInfo($id){
        $map = array(
            "stat"=>array("EQ", 1),
            "id"=>array("EQ", $id)
        );
        return M("rbac_role")->where($map)->field('id,role_name,grade')->find();
    }

    /*
    * 获取特定条件的角色信息
    */
    public function getSomeRoleInfo($where = []){
        $map = [
            'stat' => 1
        ];

        $map = array_merge($map, $where);
        return M("rbac_role")->where($map)->field('id,role_name,grade')->select();
    }
}