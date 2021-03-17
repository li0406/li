<?php

namespace Home\Model\Logic;

use Home\Model\Db\AdminuserModel;
use Home\Model\Db\RbacRoleModel;
use Home\Model\RbacRoleModel as RbacRoleOldModel;
use Home\Model\RbacNodeGroupModel;

class RbacRoleLogicModel {

    /**
     * 根据角色ID获取角色列表
     * @DateTime 2019-05-28T10:41:03+0800
     */
    public function getRoleListByUids($uids){
        $roleList = D("RbacRole")->getRoleListByIds($uids);

        $roleTreeList = [];
        foreach ($roleList as $key => $item) {
            $role_name = $item["role_name"];

            if (!empty($item['p1_id'])) {
                $role_name = $item["p1_name"] . "/" . $role_name;
            }

            if (!empty($item['p2_id'])) {
                $role_name = $item["p2_name"] . "/" . $role_name;
            }

            if (!empty($item['p3_id'])) {
                $role_name = $item["p3_name"] . "/" . $role_name;
            }

            $roleTreeList[$key] = [
                'role_id' => $item['id'],
                'role_name' => $role_name
            ];
        }

        return $roleTreeList;
    }

    /**
     * 获取部门下所有的角色信息
     * @DateTime 2019-05-28T09:56:06+0800
     */
    public function getRoleTreeByDept($dept_id){
        // 获取部门角色
        $deptList = D("RbacRole")->getRoleIdsByDept($dept_id);

        $roleIds = [];
        foreach ($deptList as $key => $value) {
            $ids = explode(",", $value['ids']);
            $roleIds = array_merge($roleIds, $ids);
            $roleIds = array_filter(array_unique($roleIds));
        }

        return $this->getRoleListByUids($roleIds);
    }

    public function getRoleUserByUser($role_id)
    {
        //获取当前角色所能看到的角色
        if ($role_id != 1) {
            $rbacGroup = new RbacNodeGroupModel();
            $deptList = $rbacGroup->getRoleGroupInfoByRoleId($role_id);
            //如果没有查询自身
            if (!empty($deptList)) {
                $roles = explode(',', $deptList['role_id']);
                if(count($roles) > 0){
                    $roles = array_merge($roles,[$role_id]);
                }else{
                    $roles = [$role_id];
                }
            } else {
                $roles = [$role_id];
            }
            //根据角色获取用户信息
            $user = new AdminuserModel();
            $list = $user->getAdminInfoByUid($roles,'','id');
            if(count($list) > 0){
                return array_column($list,'id');
            }
        }
        return [];
    }

    public function getRoleInfo(){
        $roleModel = new RbacRoleOldModel();
        $where = [
            'role_name' => ['LIKE', '%编辑%']
        ];
        return $getEditorRole = $roleModel->getSomeRoleInfo($where);
    }
}