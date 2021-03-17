<?php
// +----------------------------------------------------------------------
// | RbacRole 角色表
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\db\Where;
use think\Model;

class RbacRole extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_rbac_role';

    /**
     * 关联获取角色部门信息
     * @return \think\model\relation\BelongsToMany
     */
    public function department()
    {
        return $this->belongsToMany('Department', '\\app\\common\\model\\db\\RoleDepartment','department_id','role_id');
    }
    
    /**
     * 根据部门获取角色
     *
     * @param array $department_id
     * @param string $field
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getRoleByDepartment($department_id = [], $field = 'a.*')
    {
        $map['b.department_id'] = ['in', $department_id];
        $map['a.stat'] = 1;
        return self::alias('a')
            ->where(new Where($map))
            ->field($field)
            ->join('role_department b', 'b.role_id = a.id')
            ->select()
            ->toArray();
    }

    /**
     * 获取下级角色
     * @return [type] [description]
     */
    public static function getChildrenByRoleId($role_id){
        return static::alias("r1")
            ->join("rbac_role r2", "r1.id = r2.parentid", "left")
            ->join("rbac_role r3", "r2.id = r3.parentid", "left")
            ->where("r1.parentid", $role_id)
            ->field("r1.id as r1_id,r2.id as r2_id,r3.id as r3_id")
            ->select();
    }
}