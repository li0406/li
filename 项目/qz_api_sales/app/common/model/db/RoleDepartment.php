<?php
// +----------------------------------------------------------------------
// | RoleDepartment  角色部门关联表
// +----------------------------------------------------------------------

namespace app\common\model\db;

use think\Db;
use think\model\Pivot;

class RoleDepartment extends Pivot
{
    protected $table = 'qz_role_department';

    public function getDepartmentByRoleId($role_id){
        return $this->alias("a")
            ->where("a.role_id", $role_id)
            ->join("qz_department d", "a.department_id = d.id")
            ->field("a.department_id,a.role_id,d.name")
            ->find();
    }

    public function getRoleDepartments($role_id){
        return Db::name("rbac_role")->alias("a")->where("a.id", $role_id)
            ->join("role_department r", "r.role_id = a.id", "left")
            ->join("department d1", "r.department_id = d1.id", "left")
            ->join("department d2", "d2.id = d1.parentid", "left")
            ->join("department d3", "d3.id = d2.parentid", "left")
            ->join("department d4", "d4.id = d3.parentid", "left")
            ->field(["a.id as role_id", "d1.id as d1_id", "d2.id as d2_id", "d3.id as d3_id", "d4.id as d4_id"])
            ->find();
    }
}