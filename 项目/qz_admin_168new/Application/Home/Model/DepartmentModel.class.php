<?php

namespace Home\Model;
use Think\Model;

/**
 * 部门表
 */
class DepartmentModel extends Model{
    protected $autoCheckFields = false;
    /**
     * 获取部门的角色信息
     * @return [type] [description]
     */
    public function getDepartmentUidById($departmentId){
        $map = array(
            "a.id" => array("EQ",$departmentId),
            "enabled" => array("EQ",0)
                     );

        return M("department")->where($map)->alias("a")
                                 ->join("LEFT JOIN qz_role_department as b on b.department_id = a.id")
                                 ->field("a.id,a.name,GROUP_CONCAT(b.role_id) as roles")
                                 ->group("a.id")
                                 ->find();

    }

    /**
     * 获取多个部门的角色信息
     * @param  [type] $departmentId [description]
     * @return [type]               [description]
     */
    public function getDepartmentUidsById($departmentId){
        $map = array(
            "a.id" => array("IN",$departmentId),
            "enabled" => array("EQ",0)
                     );

        $result = M("department")->where($map)->alias("a")
                                 ->join("LEFT JOIN qz_role_department as b on b.department_id = a.id")
                                 ->field("a.id,a.name,GROUP_CONCAT(b.role_id) as roles")
                                 ->group("a.id")
                                 ->select();
        $roles = array();
        foreach ($result as $key => $value) {
            $explode = array_filter(explode(",",$value["roles"]));
            if (count($explode) > 0) {
                $roles = array_merge($roles,$explode);
            }
        }
        return $roles;
    }


    /**
     * [getDepartment 获取部门]
     * @return [type] [description]
     */
    public function getDepartment(){
        $map = array(
             "enabled" => array("EQ",0)
        );
        return M("department")->where($map)->order("enabled,id")->select();
    }

    /**
     * 获取部门信息
     * @return [type] [description]
     */
    public function getDepartmentsByRoleId($role_id){
        $map = array(
            "role_id" => array("IN",$role_id)
                     );

        return M("role_department")->where($map)->alias("a")
                                        ->join("INNER JOIN qz_department as b on b.id = a.department_id")
                                        ->field("b.name,b.id,a.role_id")
                                        ->select();
    }

    /**
     * 获取部门信息
     * @return [type] [description]
     */
    public function getDepartmentByRoleId($role_id){
        $map = array(
            "role_id" => array("EQ", $role_id)
        );

        return M("role_department")->where($map)->alias("a")
                ->join("INNER JOIN qz_department as b on b.id = a.department_id")
                ->join("LEFT JOIN qz_department as c on b.parentid = c.id")
                ->join("LEFT JOIN qz_department as d on c.parentid = d.id")
                ->field("b.name,b.id,a.role_id,c.id second_id,c.name second_name,d.id three_id, d.name three_name")
                ->find();
    }

    /**
     * 根据部门名称查询部门信息
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public function getDepartmentByName($name)
    {
        $map = array(
            "name" => array("EQ",$name),
            "enabled" => array("EQ",0)
        );
        return M("department")->where($map)->find();
    }

    /**
     * [FunctionName description]
     * @param string $value [description]
     */
    public function addNewDepartment($data)
    {
        return M("department")->add($data);
    }

    /**
     * 根据部门ID查询部门信息
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public function getDepartmentById($id)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("department")->where($map)->find();
    }

    public function editDepartment($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("department")->where($map)->save($data);
    }

    /**
     * 获取子类部门数量
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getDepartmentChildCount($id)
    {
        $map = array(
            "parentid" => array("EQ",$id),
            "enabled" => array("EQ",0)
        );
         return M("department")->where($map)->count();
    }

    public function getDepartmentUserCount($id)
    {
        $map = array(
            "a.id" => array("EQ",$id)
        );

        $buildSql = M("department")->where($map)->alias("a")
                              ->join("join qz_role_department b on a.id = b.department_id")
                              ->join("join qz_rbac_role c on c.id = b.role_id")
                              ->join("join qz_adminuser u on u.uid = c.id")
                              ->field("a.id")
                              ->buildSql();
        return  M("department")->table($buildSql)->alias("a")->count();
    }

    /**
     * 获取部门列表信息
     * @param  [type] $id [父级ID]
     * @return [type]     [description]
     */
    public function getDepartmentByParentId($id)
    {
        $map = array(
            "parentid" => array("EQ",$id),
            "enabled" => array("EQ",0)
        );
        return  M("department")->where($map)->field("id,name")->select();
    }

    /**
     * 获取部门
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getDepartmentIds($id)
    {
        $map = array(
            "a.id" => array("EQ",$id),
            "a.enabled" => array("EQ",0)
        );

        return  M("department")->where($map)->alias("a")
                       ->join("left join qz_department b on a.id = b.parentid and b.enabled = 0")
                       ->join("left join qz_department c on b.id = c.parentid and c.enabled = 0")
                       ->field("a.id,a.name,b.id as second_id,b.name as second_name,c.id as three_id,c.name as three_name,concat(IFNULL(a.id, ''),',',IFNULL(b.id, ''),',',IFNULL(c.id, '')) ids")
                       ->order("a.id,b.id,c.id")
                       ->select();
    }

    /**
     * 获取顶级部门
     * @param  string deptId [部门ID]
     * @return [type]        [description]
     */
    public function getTopDepartmentInfo($deptId)
    {
        $map = array(
            "a.id" => array("EQ",$deptId),
            "a.enabled" => array("EQ",0)
        );

        return  M("department")->where($map)->alias("a")
                       ->join("left join qz_department b on a.parentid = b.id and b.enabled = 0")
                       ->join("left join qz_department c on b.parentid = c.id and c.enabled = 0")
                       ->field("a.id,a.name,b.id as second_id,b.name as second_name,c.id as three_id,c.name as three_name")
                       ->order("a.id,b.id,c.id")
                       ->find();
    }

    /**
     * 获取部门下所有的子部门
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getAllDepartmentByID($deptId)
    {
        $map = array(
            "a.id" => array("EQ",$deptId),
            "a.enabled" => array("EQ",0)
        );

        return  M("department")->where($map)->alias("a")
                       ->join("left join qz_department b on a.id = b.parentid and b.enabled = 0")
                       ->join("left join qz_department c on b.id = c.parentid and c.enabled = 0")
                       ->field("a.id,a.name, b.id as second_id,b.name as second_name,c.id as three_id,c.name as three_name")
                       ->order("b.parentid,id")
                       ->select();
    }

     /**
     * 获取所有部门
     * @param  string deptId [部门ID]
     * @return [type]        [description]
     */
    public function getAllDepartmentList()
    {
        $map = array(
            "a.enabled" => array("EQ",0),
            "a.parentid" => array("EQ",0)
        );

        return  M("department")->where($map)->alias("a")
                       ->join("left join qz_department b on a.id = b.parentid and b.enabled = 0")
                       ->join("left join qz_department c on b.id = c.parentid and c.enabled = 0")
                       ->field("a.id,a.name,b.id as second_id,b.name as second_name,c.id as three_id,c.name as three_name")
                       ->order("a.id,b.id,c.id")
                       ->select();
    }

}