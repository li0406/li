<?php
namespace Home\Model\Logic;

use Home\Model\AreaModel;
use Home\Model\Db\AdminuserModel;
use Home\Model\RbacRoleModel;
use Home\Model\RbacNodeGroupModel;
use Home\Model\RoleDepartmentModel;

class AdminuserLogicModel {
    
    public function getKfList()
    {
        $result = D("Adminuser")->getKfList();
        $kflist = array_map(function($value) {
            return [
                'name' => $value['name'],
                'id' => $value['id']
            ];
        }, $result);
        return $kflist;
    }

    public function getKFListByGroup($uid,$kfgroup)
    {
        $result = D("Adminuser")->getKFListByGroup($uid,$kfgroup);
        $kflist = array_map(function($value) {
            return [
                'name' => $value['name'],
                'id' => $value['id']
            ];
        }, $result);
        return $kflist;
    }

    /**
     * 获取初级SEO用户
     *
     * @return mixed
     */
    public function getSeoList()
    {
        $adminModel = new AdminuserModel();
        $arr =  $adminModel->getAdminByUid([47]);
        $seolist = array_map(function($value){
            return [
                'name' => $value['name'],
                'id' => $value['id']
            ];
        }, $arr);
        return $seolist;
    }

    public function getAdminUserByDepartment($dept_id,$field = 'id,uid,user', $cs='')
    {
        if(empty($dept_id)){
            return [];
        }
        $returnData = [];
        $rbacDb = new RbacRoleModel();
        $data = $rbacDb->getRoleIdsByDept($dept_id);
        if (count($data) > 0) {
            //获取查询的所有角色
            $ids = '';
            foreach ($data as $k => $v) {
                if (!empty($v['ids'])) {
                    $ids .= $v['ids'];
                }
            }
            if(!empty($ids)){
                $adminDb = new AdminuserModel();
                $returnData = $adminDb->getAdminByUid($ids, $field, $cs);
            }
        }
        return $returnData;
    }

    // 根据角色ID获取下级用户
    public function getChildrenIds($uid){
        $adminIds = [];

        $rbacNodeGroupModel = new RbacNodeGroupModel();
        $groupInfo = $rbacNodeGroupModel->getRoleGroupInfoByRoleId($uid);
        if (!empty($groupInfo) && !empty($groupInfo["role_id"])) {
            $adminuserModel = new AdminuserModel();
            $adminList = $adminuserModel->getAdminByUid($groupInfo["role_id"], "id,uid");
            $adminIds = array_column($adminList, "id");
        }

        return $adminIds;
    }

    // 根据角色获取用户
    public function getUserListByUid($uid){
        if (empty($uid)) {
            return [];
        }
        $adminuserModel = new AdminuserModel();
        $list = $adminuserModel->getAdminByUid($uid, "id,uid,`user`");
        return $list;
    }

    // 获取城市下的销售
    public function getSalersByCity($cs){
        $adminuserModel = new AdminuserModel();
        $salerList = $adminuserModel->getAdminuserByUidAndCity(3, $cs);
        return $salerList;
    }

    /**
     * 根据部门id获取角色id
     * @param $department_id
     * @return array
     */
    public function getDepartmentAllUid($department_id){
        if(count($department_id) == 0){
            return [];
        }
        $db = new RoleDepartmentModel();
        $list = $db->getDepartmentRoleById($department_id);
        if (count($list) > 0) {
            return array_column($list, 'role_id');
        } else {
            return [];
        }
    }

    public function getCityListByUser($uer_id){
        $db = new AdminuserModel();
        $list = $db->getUserCity($uer_id);
        $citys = [];
        if(count($list) > 0){
            foreach ($list as $k => $v) {
                if(!empty($v['cs'])){
                    $citys = array_merge($citys,explode(',',$v['cs']));
                }
            }
        }
        return array_unique($citys);
    }

    /**
     * 根据城市id获取城市信息
     * @param $cid
     * @return array
     */
    public function getCityInfoByCid($cid){
        $areaDb = new AreaModel();
        $list = $areaDb->getCityInfoByCid($cid);
        if(count($list) > 0){
            import("Library.Org.Util.App");
            $app = new \App();
            foreach ($list as $k=>$v){
                $str = $app->getFirstCharter($v["cname"]);
                $list[$k]['cname'] = $str." ".$v['cname'];
            }
            array_multisort(array_column($list,'cname'),SORT_ASC,$list);
        }
        return $list;
    }

    public function getAdminUserByUid($uid){
        if (empty($uid)) {
            return [];
        }
        $adminuserModel = new AdminuserModel();
        $list = $adminuserModel->getAdminUserListByUid($uid);
        return $list;
    }

    public function getAdminuserInfo($user)
    {
        $where = [
            'user' => ['like', '%' . $user . '%'],
            'state' => ['eq', 1]
        ];
        $field = 'id,uid,user,name';
        $userDb = new AdminuserModel();
        return $userDb->getAdminByMap($where, $field);
    }

    // 获取部门的下级用户
    public function getAdminuserByDeptId($dept_id){
        $roleDepartmentModel = new RoleDepartmentModel();
        $list = $roleDepartmentModel->getDepartmentUser($dept_id);

        if (!empty($list)) {
            // 添加名称首字母
            import('Library.Org.Util.App');
            $app = new \App();

            foreach ($list as $key => $value) {
                $list[$key]["first_abc"] = $app->getFirstCharter($value["user"]);
            }
        }

        return $list;
    }
}