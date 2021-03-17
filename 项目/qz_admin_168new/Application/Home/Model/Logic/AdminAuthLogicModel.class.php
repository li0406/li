<?php
namespace Home\Model\Logic;

use Home\Model\Db\AdminuserModel;
use Home\Model\RbacNodeGroupModel;

class AdminAuthLogicModel {

    protected $authInfo;

    public function __construct(){
        $this->authInfo = session("uc_userinfo");
    }

    // 根据角色ID获取下级用户
    public function getAuthCitys(){
        $citys = [];
        if ($this->authInfo["uid"] != 1) {
            $citys = explode(",", $this->authInfo["cs"]);

            $rbacNodeGroupModel = new RbacNodeGroupModel();
            $groupInfo = $rbacNodeGroupModel->getRoleGroupInfoByRoleId($this->authInfo["uid"]);
            if (!empty($groupInfo) && !empty($groupInfo["role_id"])) {
                $adminuserModel = new AdminuserModel();
                $adminList = $adminuserModel->getAdminByUid($groupInfo["role_id"], "id,uid,cs");

                foreach ($adminList as $key => $item) {
                    if (!empty($item["cs"])) {
                        $citys = array_merge($citys, explode(",", $item["cs"]));
                    }
                }
            }

            $citys = array_filter(array_unique($citys));
            $citys = array_values($citys);
            if (empty($citys)) {
                $citys[] = "none";
            }
        }

        return $citys;
    }

}