<?php
// +----------------------------------------------------------------------
// | AuditCitysLogicModel 质检城市逻辑
// +----------------------------------------------------------------------
namespace Home\Model\Logic;


use Home\Model\Db\AdminuserModel;
use Home\Model\Db\AuditCitysModel;

class AuditCitysLogicModel
{
    public static $role = [
        23 => '质检',
        66 => '质检主管',
        99 => '质检组长',
    ];

    /**
     * 获取质检人员所有管辖城市
     *
     * @return mixed
     */
    public static function getJiheCity($with_cname = true, $with_vip_num = true, $having = false)
    {
        $auditCitysModel = new AuditCitysModel();
        return $auditCitysModel->zhijianCitys($with_cname, $with_vip_num, $having);
    }

    /**
     * 获取质检部门人员及每个人质检城市
     *
     * @param bool $with_cname
     * @return mixed
     */
    public static function getUserCitys()
    {
        $uid = [23,66,99];
        $adminModel = new AdminuserModel();
        $user = $adminModel->getAdminInfoByUid($uid,false);
        $auditOrder = new AuditCitysModel();
        $citys = $auditOrder->getZjRolerCitys(['a.admin_id'=>['in',array_column($user,'id')]]);
        foreach ($user as $k1 => $v1) {
            $user[$k1]['audit_city'] = [];
            foreach ($citys as $k2 => $v2) {
                if ($v1['id'] == $v2['admin_id']) {
                    $user[$k1]['audit_city'][] = $v2;
                }
            }
        }
        return ['list' => $user];
    }

    /**
     * 获取质检部门人员及每个人质检城市
     *
     * @param bool $with_cname
     * @return mixed
     */
    public static function getOneUserCitys($user_id)
    {
        $uid = [23,66,99];
        $adminModel = new AdminuserModel();
        $user = $adminModel->getAdminInfoByUid($uid,false);
        $auditCitysModel = new AuditCitysModel();
        if (!in_array($user_id,array_column($user,'id'))) {
            return [];
        }
        foreach ($user as $k1 => $v1) {
            if ($v1['id'] == $user_id) {
                $select = $v1;
            }
        }
        $select['audit_city'] = $auditCitysModel->getZjRolerCitys(['a.admin_id' => $user_id]);
        return $select;
    }

    /**
     * 增加质检用户城市
     *
     * @param $admin_id
     * @param $city
     * @return bool|string
     */
    public static function saveOneUserCitys($admin_id, $city)
    {
        $auditCitysModel = new AuditCitysModel();
        $auditCitysModel->delAuditCitys(['admin_id' => $admin_id]);
        $data = [];
        $city = is_string($city) ? explode(',',$city) : $city;
        foreach ($city as $key => $v) {
            $data[$key] = [
                'admin_id' => $admin_id,
                'cid' => $v,
            ];
        }
        return $auditCitysModel->addMuchAuditCitys($data);
    }

    /**
     * 清除质检用户城市
     *
     * @param $admin_id
     * @param $city
     * @return bool|string
     */
    public static function clearOneUserCitys($admin_id)
    {
        $auditCitysModel = new AuditCitysModel();
        return $auditCitysModel->delAuditCitys(['admin_id' => $admin_id]);
    }

    /**
     * 获取当前质检可以管辖的城市
     *
     * @param $user_id
     * @return array
     */
    public static function getRealUserCitys($user_id, $role_id, $page_type)
    {
        $auditCitysModel = new AuditCitysModel();
        $zjBuMengCitys = $auditCitysModel->zhijianCitys(true, true, 'vip_num <= 4');
        if ($role_id == 23 || ($page_type == 1 && $role_id == 99)) {
            $zjPersonalCitys = $auditCitysModel->getZjRolerCitys(['a.admin_id' => $user_id]);
            return array_intersect(array_column($zjPersonalCitys, 'cid'), array_column($zjBuMengCitys, 'cid'));
        }
        return array_column($zjBuMengCitys, 'cid');
    }
}