<?php
// +----------------------------------------------------------------------
// | RbacNodeRole
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Model;

class RbacNodeRole extends Model
{
    protected $table = 'qz_rbac_node_role';

    /**
     * 获取菜单
     *
     * @return \think\model\relation\HasOne
     */
    public function menu()
    {
        return $this->hasOne('RbacSystemMenu', 'nodeid','node_id')->bind(['name','enabled','nodeid','px','link','link_type','parentid','version','icon'])->joinType('INNER');
    }
}