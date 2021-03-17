<?php
// +----------------------------------------------------------------------
// | AdminuserLogic 管理员逻辑
// +----------------------------------------------------------------------
namespace app\common\model\logic;

use think\db\Where;
use think\Collection;
use think\facade\Request;

use app\common\model\db\Adminuser;
use app\common\model\db\AdminuserCitysRelation;
use app\common\model\db\RoleDepartment;
use app\common\model\db\RbacRole;

use app\index\enum\RbacRoleCodeEnum;
use app\common\enum\CacheKeyCodeEnum;

class AdminuserLogic
{
    /**
     * 销售部门ID
     */
    const SALER_DEPARTMENT_ID = 17;

    /*
    * 角色ID
    */
    const ROLE_ADMIN_SUPER = 1; //超级管理员角色
    const ROLE_SALE_ASSISTANT = 45; //销售助理角色ID
    const ROLE_SALE_MANAGER = 46; //销售副总角色ID
    const ROLE_PRODUCT_MANAGER = 51; //产品经理角色ID

    /**
     * 部门处理返回数据集
     *
     * @var array
     */
    protected static $RET_ARRAY = [];

    /**
     * 获取管辖城市条件
     *
     * @return bool|array
     */
    public function analysisCityMap($admin_id, $using = true)
    {
        $department = $this->department(self::SALER_DEPARTMENT_ID);
        if (!$department) {
            return false;
        }
        $department_ids = array_column($department, 'id');
        array_push(self::$RET_ARRAY, $department_ids);
        $roler = $this->getRoleByDepartment($department_ids);
        if (!$roler) {
            return false;
        }

        $map['u.uid'] = ['in', array_column($roler, 'id')];
        if ($using == true) {
            $map['u.stat'] = 1;
            $map['u.state'] = 1;
        }

        if ($admin_id) {
            $map['u.id'] = $admin_id;
        }
        return $map;
    }

    /**
     * 获取销售及管辖城市列表
     *
     * @param int $admin_id 用户ID
     * @param int $role_id 角色ID
     * @param int $page 分页
     * @param int $pageSize 分页
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getSalerWithCitys($map, $page, $pageSize)
    {
        $adminModel = new Adminuser();
        $list = $adminModel->getList($map, $page, $pageSize,['roler']);
        return array_map(function ($value) {
            $adminuserCitysRelation = new AdminuserCitysRelation();
            return [
                'id' => $value['id'],
                'saler_id' => $value['id'],
                'saler_name' => $value['name'],
                'state' => $value['state'],
                'roler' => !empty($value['roler']['role_name']) ? $value['roler']['role_name'] : '',
                'department' => !empty($value['roler']['department'][0]) ? $value['roler']['department'][0]['name'] : '',
                'citys' => $adminuserCitysRelation->getRelaCitys($value['id'], 'q.cid,q.cname,CONCAT(upper(left(q.px_abc,1)),\' \',q.cname) city_name'),
            ];
        }, $list->toArray());
    }

    /**
     * 获取销售数量
     *
     * @param int $admin_id 用户ID
     * @param int $role_id 角色ID
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getSalerCount($map)
    {
        $adminModel = new Adminuser();
        return $adminModel->getCount($map);
    }

    /**
     * 保存用户管辖城市
     *
     * @param $admin_id
     * @param $citys
     * @return \think\Collection
     */
    public function saveSalerCitys($admin_id, $citys)
    {
        $adminuserCitysRelation = new AdminuserCitysRelation();
        $adminuserCitysRelation->delMuch(['admin_id' => $admin_id]);
        $cityArray = array_map(function ($value) use ($admin_id) {
            return [
                'admin_id' => $admin_id,
                'quyu_id' => $value
            ];
        }, $citys);
        return $adminuserCitysRelation->saveMuch($cityArray);
    }

    /**
     * 删除用户管辖城市
     *
     * @param $admin_id
     * @param $citys
     * @return \think\Collection|bool
     */
    public function clearSalerCitys($admin_id)
    {
        $adminuserCitysRelation = new AdminuserCitysRelation();
        return $adminuserCitysRelation->delMuch(['admin_id' => $admin_id]);
    }

    /**
     * 根据顶级部门ID（销售）获取下级所有部门
     *
     * @param $top_department_id
     * @return array
     */
    public function department($top_department_id = self::SALER_DEPARTMENT_ID)
    {
        $map['parentid'] = ['in', $top_department_id];
        $map['enabled'] = 0;
        $list = db('department')->field('id,name,parentid,enabled')->where(new Where($map))->select();
        if ($list) {
            foreach ($list as $key => $val) {
                array_push(self::$RET_ARRAY, $val);
            }
            $this->department(array_column($list,'id'));
        }
        $result = array_unique(self::$RET_ARRAY,SORT_REGULAR);
        return $result;
    }

    /**
     * 根据部门获取角色
     *
     * @param array $department_id
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getRoleByDepartment($department_ids)
    {
        if ($department_ids) {
            $roleModel = new RbacRole();
            return $roleModel->getRoleByDepartment($department_ids,'a.id,a.role_name as role');
        }
        return [];
    }

    /**
     * 根据角色获取下级角色ID
     * @return [type] [description]
     */
    public static function getChildrenRoleIds($role_id){
        $children = RbacRole::getChildrenByRoleId($role_id);

        $list = $children->toArray();
        $r1_id = array_column($list, "r1_id");
        $r2_id = array_column($list, "r2_id");
        $r3_id = array_column($list, "r3_id");

        $roleIds = array_merge($r1_id, $r2_id, $r3_id);
        $roleIds = array_filter(array_unique($roleIds));
        return $roleIds;
    }

    /**
     * 获取当前用户可以查看的所有用户ID
     * 管理员查看所有，上级查看下级，下级查看自己
     * @Author   liuyupeng
     * @DateTime 2019-05-14T15:21:21+0800
     */
    public static function getAuthUserids()
    {
        $user = Request::instance()->user;

        // 非超级管理员获取下级用户ID
        $authRoles = RbacRoleCodeEnum::getAuthRoles();
        if (!in_array($user["role_id"], $authRoles)) {
            $cache_key = sprintf(CacheKeyCodeEnum::ADMINUSER_PRIV_USERS, $user['user_id']);
            $users = cache($cache_key);
            if (empty($users)) {
                $users = [];
                $uids = static::getChildrenRoleIds($user['role_id']);
                if (!empty($uids)) {
                    $users = Adminuser::getListByUids($uids, "id,user", false);
                }
                cache($cache_key, $users, 900);
            }

            $userids = array_column($users, 'id');
            $userids[] = $user['user_id']; // 加入自己
        } else {
            $userids = 0; // 默认0为全部用户
        }

        return $userids;
    }

    /**
     * 获取用户具体信息
     *
     * @param int $id  用户ID
     * @param int $pageSize 获取数量
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getListUser($id, $pageSize = 10, $field = false, $status = 0)
    {
        if ($field === false) {
            $field = 'u.id,u.name,u.user as value,d.name as depart_name';
        }

        $map = [];
        if (!empty($id)) {
            if (ctype_digit((string)$id)) {
                $map['u.id'] = intval($id);
            } else {
                $map['u.user|u.name'] = ['like', '%' . $id . '%'];
            }
        }

        if ($status == 1) {
            $map['u.stat'] = 1;
            $map['u.state'] = 1;
        } else if ($status == 2) {
            $map['u.stat'] = 1;
        }

        $userModel = new Adminuser();
        $list = $userModel->getList($map, 1,$pageSize,'',$field);
        if (count($list) > 0) {
            $list = $list->toArray();
            array_walk($list,function(&$value){
                if (!empty($value['depart_name'])) {
                    $value["full_name"] = $value["name"]."(".$value["depart_name"].")";
                }
            });
        }

        return $list;
    }

    public function getSaleList($user_name)
    {
        $adminModel = new Adminuser();
        $map['u.uid'] = ['in', [3,7,36,39,40,41,42,43,45,46,56,58,59,61,62,65,67,71,72,73,77,78,126]];
        $map['u.name'] = [ 'like', '%'.$user_name.'%'];
        $field = 'u.id,u.name';
        $sale_list = $adminModel->getList($map,0,20,[],$field);
        return array_map(function ($value) {
            return [
                'user_id' => $value['id'],
                'user_name' => $value['name'],
            ];
        }, $sale_list->toArray());
    }

    /**
     * 获取管理员以及下属人员管辖的所有城市ID
     * @return [type] [description]
     */
    public static function getCityIds(){
        $userid = Request::instance()->user['user_id'];
        $cache_key = sprintf(CacheKeyCodeEnum::ADMINUSER_PRIV_CITYS, $userid);
        $cityIds = cache($cache_key);
        if (empty($cityIds)) {
            $ids = AdminuserLogic::getAuthUserids();
            if (!empty($ids)) {
                $adminModel = new Adminuser();
                $adminList = $adminModel->getList(['u.id' => ['in', $ids], 'u.stat' => 1, 'u.state' => 1]);
                $cityIds = array_column($adminList->toArray(), 'cs');
                $cityIds = explode(",", implode(",", $cityIds));
                $cityIds = array_filter(array_unique($cityIds));
            } else {
                $citys = model('db.quyu')->getAllQuyuOnly();
                $cityIds = array_column($citys, 'cid');
            }
            
            cache($cache_key, $cityIds);
        }

        return $cityIds;
    }
    
    /**
     * 获取管理登录账号信息
     *
     * @param $post
     */
    public function getAmdin($post)
    {
        $map['user'] = $post['user_name'];
        $adminModel = new Adminuser();
        return $adminModel->getAdminUserInfo($map,'id,user,pass,name,logo,uid,stat,state,cs',['roler']);
    }

   
    /**
     * 设置用户关联消息程序信息
     * @param [type] $user_id [description]
     * @param [type] $appid   [description]
     * @param [type] $openid  [description]
     * @param string $unionid [description]
     */
    public function setUserOpenid($user_id, $appid, $openid, $unionid = ""){
        $adminModel = new Adminuser();
        $wxinfo = $adminModel->getOpenid($user_id, $appid, $openid);
        if (empty($wxinfo)) {
            $result = $adminModel->addOpenid($user_id, $appid, $openid, $unionid);
        } else if ($wxinfo["wx_unionid"] != $unionid) {
            $result = $adminModel->updateOpenid($wxinfo["id"], $unionid);
        } else {
            $result = true;
        }

        return $result;
    }

    public function checkBindOpenid($user_id, $appid){
        $adminModel = new Adminuser();
        $count = $adminModel->getUserOpenidCount($user_id, $appid);
        return $count > 0 ? 1 : 2;
    }

    // 获取并验证城市权限
    public static function getAndValidateSaleCity($cs = ""){
        $citys = AdminuserLogic::getCityIds();
        if (!empty($citys)) {
            if (!empty($cs)) {
                if (in_array($cs, $citys)) {
                    return $cs;
                } else {
                    return false;
                }
            }

            return $citys;
        }

        return false;
    }


    public function getUserListByUid($role_id)
    {
        $adminModel = new Adminuser();
        return $adminModel->getListByUids($role_id,"id,user,name",true);
    }

    /**
     * 获取销售其他业绩人
     * @param  array  $filter_ids [description]
     * @return [type]             [description]
     */
    public function getSalerOtherPerson($filter_ids = [], $saler_name = ""){
        $departmentList = $this->department(static::SALER_DEPARTMENT_ID);
        $department_ids = array_column($departmentList, "id");
        $department_ids[] = static::SALER_DEPARTMENT_ID;

        $adminuserModel = new Adminuser();
        $list = $adminuserModel->getSalerOtherPerson($department_ids, $filter_ids, $saler_name);

        $list = array_map(function($item){
            $item["team_name"] = (string) $item["team_name"];
            $item["full_name"] = $item["saler_name"];
            if (!empty($item["team_name"])) {
                $item["full_name"] = sprintf("%s（%s）", $item["saler_name"], $item["team_name"]);
            }
            return $item;
        }, $list->toArray());

        return $list;
    }

    public function getAdminUserByUser($user)
    {
        $model = new Adminuser();
        $map = [];
        $map[] = ["name", "=", $user];
        return $model->getOnce($map);
    }

    // 获取角色所在部门
    public function getRoleDepartments($role_id){
        $cache_key = sprintf(CacheKeyCodeEnum::RBACROLE_DEPARTMENTS, $role_id);
        $ret = cache($cache_key);
        if (empty($ret)) {
            $roleDepartmentModel = new RoleDepartment();
            $info = $roleDepartmentModel->getRoleDepartments($role_id);
            if (!empty($info)) {
                $ret["role_id"] = $role_id;
                $ret["department_id"] = intval($info["d1_id"]);
                $ret["departments"] = array_values(array_filter([
                    $info["d4_id"],
                    $info["d3_id"],
                    $info["d2_id"],
                    $info["d1_id"]
                ]));

                $ret["department_top_id"] = $ret["departments"][0] ?? 0;
                cache($cache_key, $ret);
            }
        }

        return $ret ? : [];
    }

    /*
    * 获取销售中心副总和助理角色ID(含超级管理员、产品经理)
    */
    public static function getSaleManagerRoleIds(){
        return [
            self::ROLE_ADMIN_SUPER, 
            self::ROLE_SALE_MANAGER, 
            self::ROLE_SALE_ASSISTANT,
            self::ROLE_PRODUCT_MANAGER
        ];
    }

    /*
    * 检查是否销售副总或助理
    */
    public static function checkSaleManagerRole(){
        $user = Request::instance()->user;
        $saleManageRoleIds = self::getSaleManagerRoleIds();

        if(in_array($user['role_id'], $saleManageRoleIds)){
            return true;
        }

        return false;
    }
}