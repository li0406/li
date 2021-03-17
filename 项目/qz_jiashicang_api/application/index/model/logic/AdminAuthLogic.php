<?php
// +----------------------------------------------------------------------
// | AdminAuthLogic 管理员权限逻辑层
// +----------------------------------------------------------------------

namespace app\index\model\logic;

use think\facade\Request;
use app\index\model\adb\Adminuser;
use app\index\enum\CacheKeyCodeEnum;
use app\index\enum\AdminuserCodeEnum;

class AdminAuthLogic {

    // 获取销售管理团队成员
    public static function getSalerAuthUsers(){
        $salerIds = [];
        $authuser = Request::instance()->user;
        if ($authuser["role_id"] != AdminuserCodeEnum::RBAC_ROLE_ADMIN) {
            $cachekey = sprintf(CacheKeyCodeEnum::AUTH_TEAM_USERS, $authuser["user_id"]);
            $salerIds = cache($cachekey) ? : [];
            if (empty($salerIds)) {
                $teamLogic = new TeamLogic();
                $teamIds = $teamLogic->getAuthTeamIds($authuser["user_id"]);
                if (count($teamIds) > 0) {
                    $childIds = $teamLogic->getTeamChildIds($teamIds);
                    $teamIds = array_merge($teamIds, $childIds);

                    // 查询团队成员
                    $salerIds = $teamLogic->getTeamMemberIds($teamIds);
                }

                $salerIds[] = $authuser["user_id"]; // 加入自己
                $salerIds = array_filter(array_unique($salerIds));
                cache($cachekey, $salerIds, rand(300, 600));
            }
        }

        return $salerIds;
    }

    // 获取销售管辖城市
    public static function getSalerAuthCitys(){
        $cityIds = [];
        $authuser = Request::instance()->user;
        if ($authuser["role_id"] != AdminuserCodeEnum::RBAC_ROLE_ADMIN) {
            $cachekey = sprintf(CacheKeyCodeEnum::AUTH_TEAM_CITYS, $authuser["user_id"]);
            $cityIds = cache($cachekey) ? : [];
            if (empty($cityIds)) {
                // 获取管辖的团队成员
                $userIds = static::getSalerAuthUsers();

                $adminuserModel = new Adminuser();
                $adminList = $adminuserModel->getAdminCitys($userIds);
                foreach ($adminList as $admin) {
                    $cityIds = array_merge($cityIds, explode(",", $admin["cs"]));
                }

                $cityIds = array_filter(array_unique($cityIds));
                cache($cachekey, $cityIds, rand(300, 600));
            }
        }

        return $cityIds;
    }


    // 获取市场中心权限用户
    public static function getMarketAuthUsers(){
        $marketDepts = [
            AdminuserCodeEnum::DEPT_MARKEY_QUDAO,
            AdminuserCodeEnum::DEPT_MARKEY_MEIJIE,
        ];

        $userIds = [];
        $authuser = Request::instance()->user;
        if($authuser["role_id"] == AdminuserCodeEnum::RBAC_ROLE_QDZY) {
            // 渠道专员只能查看自己的数据
            $userIds[] = $authuser["user_id"];
        } else if (in_array($authuser["dept_id"], $marketDepts)) {
            // 特定部门只能看自己部门内部数据
            $cachekey = sprintf(CacheKeyCodeEnum::DEPT_USERS, $authuser["dept_id"]);
            $userIds = cache($cachekey);
            if (empty($userIds)){
                $adminuserModel = new Adminuser();
                $userList = $adminuserModel->getDepartmentUserAll($authuser["dept_id"]);
                $userIds = array_column($userList, "id");
                cache($cachekey, $userIds, rand(300, 600));
            }
        }

        return $userIds;
    }
}