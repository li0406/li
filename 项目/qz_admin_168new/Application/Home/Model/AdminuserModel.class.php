<?php
/**
 * 后台用户
 */
namespace Home\Model;

use Think\Model;

class AdminuserModel extends Model
{
    protected $autoCheckFields = false;
    protected $_validate = array(
        array('user','require','登陆账号不能为空',1,"",1), //登陆验证
        array('pass','require','登陆密码不能为空',1,"",1), //登陆验证
        array('name','require','昵称不能为空',1,"",2), //编辑个人资料
        array('logo','require','头像不能为空',1,"",4), //修改头像
        array('pass','require','密码不能为空',1,"",5),//修改密码
        array('pass',6,'密码长度不能少于6位',1,'length',5), // 验证确认密码是否和密码一致
        array('confirmpassword','pass','确认密码不正确',1,'confirm',5), //修改密码，验证密码长度
        array('safe_tel','require','电话不能为空',1,"",6), //绑定安全电话/解除绑定安全电话
    );
    /**
     * 根据账号查询账户信息
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public function findUserInfo($name){
        $map = array(
            "a.user"=>array("EQ",$name),
            "a.stat"=>array("EQ",1)
                     );
        return M("adminuser")->where($map)->alias("a")
                             ->join("left join qz_rbac_role as b on b.id = a.uid")
                             ->join("left join qz_role_department as c on c.role_id = a.uid")
                             ->join("left join qz_department as d on d.id = c.department_id")
                             ->join("left join qz_rbac_node_group as e on e.id = b.groups")
                             ->field("a.*,b.role_name,b.level,d.name as department,d.id as department_id,e.role_id as groups")
                             ->find();
    }

    /**
     * 根据ID查询用户信息
     * @param  [type] $id        [用户编号]
     * @param  [type] $solutions [VOIP提供商标识]
     * @return [type]            [description]
     */
    public function  findUserInfoById($id,$solutions = "yuntongxun"){
        $map = array(
            "a.id"=>array("EQ",$id)
                     );
        return M("adminuser")->where($map)->alias("a")
                             ->join("left join qz_admin_voip_tels as b on b.use_id = a.id and solutions = '$solutions'")
                             ->field("a.pass,a.tel_work1,a.qq_work1,a.name,a.tel_customer_ser_num,a.safe_tel,b.voipAccount,
                             a.uid,a.user,a.id,a.state,a.cs,a.kfgroup,a.kfmanager,a.up_id,a.wx_work1,a.css,a.css_user,a.record_css,a.cs_help_user,a.cs_help")
                             ->find();
    }

    /**
     * 编辑用户信息
     * @param  [type] $id   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function editUserInfo($id,$data){
        $map = array(
            "id"=>array("EQ",$id)
                     );
        return M("adminuser")->where($map)->save($data);
    }

    /**
     * 获取全部的注册用户
     * @return [type] [description]
     */
    public function getAllUserList(){
        return M("adminuser")->where($map)->order("field(stat,1,2,0),id")->select();
    }

    /**
     * 获取管辖的用户列表
     * @param  [type] $groups [用户角色]
     * @return [type]         [description]
     */
    public function getMyUserList($groups,$citys,$id){
        $map = array(
            "uid" => array("IN",$groups),
            "id" => array("NEQ",$id)
                     );
        foreach ($citys as $key => $value) {
            $complex[] = array("_string"=>"find_in_set('$value',cs)");
        }
        $complex[] = array("_string" => "(cs is null or cs = '')");
        $complex["_logic"] = "OR";
        $map["_complex"] = $complex;
        return M("adminuser")->where($map)->order("field(stat,1,2,0),id")->select();
    }

    public function getUserDetailsListCount($uids,$id,$role,$subDept,$state,$stat,$time,$kfgroup){
        $map = array(
            "uid" => array("NEQ",1)
        );

        if (!empty($kfgroup)) {
            $map["a.kfgroup"] = array("EQ",$kfgroup);
        }

        if (is_array($uids)) {
            $map["a.uid"] = array("IN",$uids);
        }


        if (!empty($id)) {
            $map["a.id"] = array("EQ",$id);
        }

        if (!empty($role)) {
            $map["a.uid"] = array("EQ",$role);
        }

        if ($state !== "") {
            $map["a.state"] = array("EQ",$state);
        }

        if ($stat !== "") {
            $map["a.stat"] = array("EQ",$stat);
        }

        if (!empty($time)) {
            $map["a.addtime"] = array(
                array("EGT",$time),
                array("LT",date("Y-m-d",strtotime($time)+86400))
            );
        }

        if (!empty($subDept)) {
            $map["d.department_id"] = array("EQ",$subDept);
        }

        return M("adminuser")->where($map)->alias("a")
                      ->join("join qz_rbac_role b on b.id = a.uid")
                      ->join("join qz_role_department as d on d.role_id = a.uid")
                      ->count();
    }

    /**
     * 获取用户的详细信息列表
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public function getUserDetailsList($uids,$id,$role,$subDept,$state,$stat,$time,$kfgroup,$pageIndex, $pageCount)
    {
        $map = array(
            "a.uid" => array("NEQ",1)
        );

        if (!empty($kfgroup)) {
            $map["a.kfgroup"] = array("EQ",$kfgroup);
        }

        if (is_array($uids)) {
            $map["a.uid"] = array("IN",$uids);
        }


        if (!empty($id)) {
            $map["a.id"] = array("EQ",$id);
        }

        if (!empty($role)) {
            $map["a.uid"] = array("EQ",$role);
        }

        if ($state !== "") {
            $map["a.state"] = array("EQ",$state);
        }

        if ($stat !== "") {
            $map["a.stat"] = array("EQ",$stat);
        }

        if (!empty($time)) {
            $map["a.addtime"] = array(
                array("EGT",$time),
                array("LT",date("Y-m-d",strtotime($time)+86400))
            );
        }

        if (!empty($subDept)) {
            $where["d.department_id"] = array("EQ",$subDept);
        }

        $buildSql = M("adminuser")->where($map)->alias("a")
                      ->join("join qz_rbac_role b on b.id = a.uid")
                      ->field("a.id,a.uid,a.name,b.role_name,a.addtime,a.state,a.stat,a.cs,a.user,a.tel_work1,a.qq_work1,a.wx_work1,a.up_id")
                      ->buildSql();

        return M("adminuser")->table($buildSql)->where($where)->alias("t")
                                  ->join("join qz_role_department as d on d.role_id = t.uid")
                                  ->join("left join qz_department e on e.id = d.department_id and e.enabled = 0")
                                  ->join("left join qz_department f on f.id = e.parentid and f.enabled = 0")
                                  ->join("left join qz_department g on g.id = f.parentid and g.enabled = 0")
                                  ->join("left join qz_adminuser as p on p.id = t.up_id")
                                  ->field("t.*,e.id as first_id, e.name as first_name,f.name as second_name,g.name as three_name,p.name up_name")
                                  ->order("f.id,t.stat desc,t.uid,t.id")
                                  ->limit($pageIndex.",".$pageCount)
                                  ->select();

    }

    /**
     * 获取管辖用户的信息
     * @param  [type] $groups [description]
     * @param  [type] $id     [description]
     * @return [type]         [description]
     */
    public function finMyGroupUserInfo($groups,$id){
        $map = array(
            "a.id" => array("EQ",$id)
                     );
        if(count($groups) > 0){
            $map["a.uid"] = array("IN",$groups);
        }

        return  M("adminuser")->where($map)->alias("a")
                              ->join("left join qz_admin_voip_tels as b on b.use_id = a.id")
                              ->join("left join qz_adminuser c on find_in_set(c.id,a.kfmanager)")
                              ->field("a.*,b.voipAccount,GROUP_CONCAT(c.name) as manager")
                              ->group("a.id")
                              ->find();
    }

    /**
     * [getAcitveAdminUserCount 获取活跃登录用户]
     * @param  string $start [开始时间]
     * @param  string $end   [结束时间]
     * @param  array  $uid   [用户UID]
     * @param  string $id    [用户ID]
     * @return [type]        [description]
     */
    public function getAcitveAdminUserCount($start = '', $end = '', $uid = array(), $id = ''){

        if (empty($start) || empty($end)) {
            return false;
        }

        $map['l.time'] = array(array('EGT', $start),array('ELT', $end), 'AND');

        if (!empty($uid)) {
            if (!is_array($uid)) {
                $uid = array(intval($uid));
            }
            $map['a.uid'] = array('IN', $uid);
        }

        if (!empty($id)) {
            $map['a.id'] = array('EQ', intval($id));
        }

        $build = M('adminuser')->alias('a')
                               ->field('a.id')
                               ->join('INNER JOIN qz_admin_logging AS l ON a.id = l.uid')
                               ->where($map)
                               ->group('a.id')
                               ->buildSql();
        $count = M()->table($build)->alias('t')->count();
        return $count;
    }

    /**
     * 根据用户编号组获取用户信息
     * @return [type] [description]
     */
    public function getUserInfoByIds($ids){
        $map = array(
            "a.id" => array("IN",$ids)
                     );
        return  M("adminuser")->where($map)->alias("a")
                              ->select();
    }

    /**
     * 根据用户编号组获取用户信息
     * @return [type] [description]
     */
    public function getAdminUserInfoByIds($ids){
        $map = array(
            "a.id" => array("IN",$ids)
        );
        return  M("adminuser")->where($map)->alias("a")
            ->find();
    }

    public function addUser($data){
        return  M("adminuser")->add($data);
    }

    public function editUser($id,$data){
        $map = array(
            "id" => array("EQ",$id)
        );
        return  M("adminuser")->where($map)->save($data);
    }

    public function findAccountExist($user){
        $map = array(
            "user" => array("EQ",$user)
        );
        return M("adminuser")->where($map)->count();
    }

    public function addUserCitys($uids,$citys){
        if(empty($uids)){
            return false;
        }
        $map = array('uid' => array('IN',$uids));
        $citys = trim($citys,',');
        $data = array('cs' => array('exp' , 'concat(if(cs is null,"",cs), ",'.$citys.'")'));
        return M('adminuser')->where($map)->save($data);
    }

    /**
     * 获取客服列表
     * @param  boolean $select [是否选择首字母]
     * @param  boolean $isStat [是否包括离职人员]
     * @return [type]          [description]
     */
    public function getKfList($select = false,$isStat = false, $help_user = [], $isState = false, $uids = [])
    {
        $map = [
            "uid" => ["EQ",2],
            "stat" => ["EQ",1],
            "state" => ["EQ",1],
        ];

        if ($isStat) {
            unset($map["stat"]);
            unset($map["state"]);
            $map["uid"] = array(
                array("EQ",2),
                array("EQ",84),
                "or"
            );
        }

        if ($isState) {
            unset($map["state"]);
        }

        if (count($uids) > 0) {
            $map["uid"] = array("IN", $uids);
        }

        // 帮打客服
        if (!empty($help_user) && is_array($help_user)) {
            $map["id"] = array("IN", $help_user);
        }

        $result = M('adminuser')->where($map)->field("`name`,id,kfgroup,kfmanager")->order("kfgroup")->select();
        if($select){
            //添加名称首字母
            import('Library.Org.Util.App');
            $app = new \app();
            foreach ($result as $key => $value) {
                $str = $app->getFirstCharter($value["name"]);
                $result[$key]["name"] = $str.' '.$value["name"];
            }
            $result = multi_array_sort($result,'name');
        }
        return $result;
    }

    public function getKfListByGroup($uid,$kfgroup){
        if(!empty($uid)){
            $map['uid'] = ['EQ',$uid];
        }
        if(!empty($kfgroup)){
            $map['kfgroup'] = ['EQ',$kfgroup];
        }

        return M('adminuser')->where($map)->field("`name`,id,kfgroup,kfmanager")->order("kfgroup")->select();
    }
    /**
     * 获取客服量房列表
     * @param  boolean $select [是否选择首字母]
     * @param  boolean $isStat [是否包括离职人员]
     * @return [type]          [description]
     */
    public function getKfLFList()
    {
        $map = array(
            "uid" => array("IN",array(2,31)),
            "stat" => array("EQ",1)
        );

        $result = M('adminuser')->where($map)->field("`name`,id,kfgroup,kfmanager")->order("kfgroup")->select();

        return $result;
    }

    /**
     * [getAdminuserIdsByKfgroup 更加kfgroup字段的值获取客服ID数组]
     * @param  string $kfgroup [kfgroup字段的值]
     * @return [type]          [description]
     */
    public function getAdminuserIdsByKfgroup($kfgroup = '')
    {
        if (empty($kfgroup)) {
            return false;
        }

        $map = array(
            'kfgroup' => $kfgroup,
            'uid'     => '2',
            'stat'    => '1'
        );

        $result = M('adminuser')->field('id')->where($map)->select();
        $ids = array();
        foreach ($result as $key => $value) {
            $ids[] = $value['id'];
        }
        return $ids;
    }

    /**
     * 通过部门获取用户ID
     * @param  array  $department 部门ID数组
     * @return array
     */
    public function getAdminuserIdsByDepartment($department = array())
    {
        if (empty($department)) {
            return false;
        }
        if (!is_array($department)) {
            $department = array(intval($department));
        }
        $map = array(
            'd.department_id' => array('IN', $department)
        );
        $result = M('adminuser')->alias('u')
                                ->field('u.id')
                                ->join('INNER JOIN qz_role_department AS d ON d.role_id = u.uid')
                                ->where($map)
                                ->select();
        foreach ($result as $key => $value) {
            $ids[] = $value['id'];
        }
        return $ids;
    }

    /**
     * 获取客服组简要信息
     * @return array         客服组简要信息数组
     */
    public function getKfGroupBriefList()
    {
        $map = array(
            'uid'     => '2',
            'stat'    => '1'
        );
        $result = M('adminuser')->cache(true,60)->field('id, kfgroup')->where($map)->order("kfgroup")->select();
        $group = array();
        foreach ($result as $key => $value) {
            if (!empty($value['kfgroup'])) {
                $group[$value['kfgroup']]['count']++;
                $group[$value['kfgroup']]['list'][] = array(
                    'id' => $value['id']
                );
            }
        }
        return $group;
    }

    /**
     * [getAdminuserIdsByKfgroup 更加kfgroup字段的值获取客服ID数组]
     * @param  string $kfmanager [kfgroup字段的值]
     * @return [type]          [description]
     */
    public function getAdminuserIdsByKfmanager($kfmanager = '')
    {
        if (empty($kfmanager)) {
            return false;
        }

        $map = array(
            '_string' => 'find_in_set("'.$kfmanager.'",kfmanager)',
            'uid'       => '2',
            'stat'      => '1'
        );

        $result = M('adminuser')->field('id')->where($map)->select();
        $ids = array();
        foreach ($result as $key => $value) {
            $ids[] = $value['id'];
        }
        return $ids;
    }

    /**
     * 根据用户名查询用户信息
     * @param  [type] $name       [用户名称组]
     * @param  [type] $roles      [角色]
     * @return [type]             [description]
     */
    public function findUserByName($name,$roles)
    {
        $map = array(
            "name" => array("IN",$name),
            "uid" => array(
                 array("IN",$roles),
                 array("NEQ",2)
            )

        );
        return  M('adminuser')->where($map)->select();
    }

    /**
     * 查找同组的客服
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function findMyGroupUser($id,$uid)
    {
        $map = array(
            "a.id" => array("EQ",$id),
            "b.uid" => array("IN",$uid)
        );

        return M('adminuser')->where($map)->alias("a")
                             ->join("LEFT JOIN qz_adminuser as b on a.kfgroup = b.kfgroup")
                             ->field("b.id,b.name")->select();
    }

    /**
     * 获取管辖的客服
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function findMyManageUser($id)
    {
        $map = array(
            "a.id" => array("EQ",$id)
        );

        return M('adminuser')->where($map)->alias("a")
                             ->join("LEFT JOIN qz_adminuser as b on find_in_set(a.id,b.kfmanager)")
                             ->field("b.id,b.name")->select();
    }

    /**
     * 获取客服组长信息
     * @return [type] [description]
     */
    public function getKfGroupInfo($uid = 31)
    {
        $map = array(
            "a.stat" => array("EQ",1),
            "a.state" => array("EQ",1),
            "a.uid" => array("EQ", $uid),
            "a.kfgroup" => array("NEQ",0)
        );

        return M('adminuser')->where($map)->alias("a")
            ->join("LEFT JOIN qz_adminuser as b on find_in_set(b.id,a.kfmanager)")
            ->field("a.id,a.name,b.id as manager_id, b.name as manager,a.kfgroup")
            ->order("a.kfgroup")->select();
    }

    /**
     * 获取客服组长信息
     * @return [type] [description]
     */
    public function getKfManagerList()
    {
        $map = array(
            "a.stat" => array("EQ",1),
            "a.state" => array("EQ",1),
            "a.uid" => array("EQ", 30)
        );

        return M('adminuser')->alias("a")
            ->where($map)
            ->field("a.id as manager_id, a.name as manager_name")
            ->order("a.id desc")
            ->select();

        // $map = array(
        //     "a.stat" => array("EQ",1),
        //     "a.state" => array("EQ",1),
        //     "a.kfmanager" => array("NEQ", "")
        // );

        // return M('adminuser')->where($map)->alias("a")
        //     ->join("JOIN qz_adminuser as b on find_in_set(b.id,a.kfmanager)")
        //     ->field("b.id as manager_id, b.name as manager_name")
        //     ->order("b.id desc")->group("b.id")->select();
    }



    /**
     * 获取客服团队
     * @param  boolean $idKey 是否保留客服ID作为初始键值
     * @return array          客服团队数组
     */
    public function getKfTeam($idKey = false)
    {
        //获取客服团队
        $team = array();
        $temp = D("Adminuser")->getKfGroupInfo();
        foreach ($temp as $key => $value) {
            $group = array(
                'id' => $value['kfgroup'],
                'name' => '客服' . $value['kfgroup'] . '团(' . $value['name'] . ')'
            );
            if (empty($team[$value['manager_id']])) {
                if ($idKey == false) {
                    $team[$value['manager_id']] = array(
                        'id' => $value['manager_id'],
                        'name' => $value['manager'] . '师',
                        'children' => array($group)
                    );
                } else {
                    $team[$value['manager_id']] = array(
                        'id' => $value['manager_id'],
                        'name' => $value['manager'],
                        'children' => array($value['id'] => $group)
                    );
                }
            } else {
                if ($idKey == false) {
                    $team[$value['manager_id']]['children'][] = $group;
                } else {
                    $team[$value['manager_id']]['children'][$value['id']] = $group;
                }
            }
        }
        if ($idKey == false) {
            return array_values($team);
        } else {
            return $team;
        }
    }

    /**
     * 客服通话率统计
     * @param  [type] $begin [开始时间]
     * @param  [type] $end   [结束时间]
     * @param  [type] $group [客服组]
     * @param  [type] $id    [客服ID]
     * @return [type]        [description]
     */
    public function custome_service_tel_list($monthBegin,$monthEnd,$begin,$end,$group,$id)
    {
        $param = " where 1 = 1";

        if (!empty($group)) {
            $param .= " and a.kfgroup =".$group;
        }

        if (!empty($id)) {
            $param .= " and a.id =".$id;
        }

        //1.统计客服的管辖城市
        $sql = "call proc_kftj_tel('$monthBegin','$monthEnd','$begin','$end','$param');";
        return M("adminuser")->query($sql);
    }

    /**
     * 获取装修公司查询信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getSellerList($name)
    {
        $map = array(
            "a.name" => array("LIKE","%$name%"),
            "a.stat" => array("EQ",1),
            "c.id" => array("IN",array(5,6))
        );
        return M('adminuser')->where($map)->alias("a")
                         ->join("join qz_role_department b on b.role_id = a.uid")
                         ->join("join qz_department c on b.department_id = c.id")
                         ->field("a.id,a.name,c.name as department")->limit(10)->select();
    }

     /**
     * 查询后台管理用户信息
     * @param  $id 用户id
     * @param  $solutions 电话提供商
     * @return
     */
    public function findUserVoipInfo($id, $solutions='yuntongxun')
    {
        $map = array(
            "a.id"        => array("EQ", $id),
            "b.solutions" => array("EQ", $solutions)
        );
        return M('adminuser')->where($map)->alias("a")
                              ->join("left join qz_admin_voip_tels b on b.use_id = a.id")
                              ->field("a.*,b.voippwd,b.ytx_subaccountsid,b.ytx_friendlyname,b.ytx_subtoken,b.voipaccount")
                              ->find();
    }

    /**
     * 获取客服列表
     * @return [type] [description]
     */
    public function getKfManagerListByIdAndUid($id, $uid, $cs_help_user='')
    {
        //如果id或uid为空返回false
        if (empty($id) || empty($uid)) {
            return false;
        }

        if (!in_array($uid, array(2,31))) {
            //其他人获取所有的客服列表
            $join = 'LEFT JOIN qz_adminuser AS z ON z.id = a.id';
        } else {
            //客服，客服组长，获取自己管辖的人的客服列表（20170308去掉客服主管，客服主管获取全部客服列表）
            switch ($uid) {
                //客服
                case '2':
                    $map['a.id'] = array('IN', array_merge(array_filter(explode(',', $cs_help_user)), array($id)));
                    $join = 'LEFT JOIN qz_adminuser AS z ON z.id = a.id';
                    break;
                //客服组长
                case '31':
                    $map['a.id'] = $id;
                    $join = 'LEFT JOIN qz_adminuser AS z ON z.kfgroup = a.kfgroup';
                    break;
                /*客服主管
                case '30':
                    $map['a.id'] = $id;
                    $join = 'LEFT JOIN qz_adminuser AS z ON FIND_IN_SET(a.id,z.kfmanager)';
                    break;
                */
                default:
                    return false;
                    break;
            }
        }

        //限制为正常在使用的账号
        $map['z.stat'] = 1;
        //限制为只能是客服和客服组长
        $map['z.uid'] = array('IN', array(2,31));

        $result = M('adminuser')->alias('a')
                                ->field('z.id,z.name')
                                ->join($join)
                                ->where($map)
                                ->select();
        //添加名称首字母
        import('Library.Org.Util.App');
        $app = new \app();
        $ids = [];
        foreach ($result as $key => $value) {
            $str = $app->getFirstCharter($value["name"]);
            $result[$key]["cname"] = $str.' '.$value["name"];
            $ids[] = $value['id'];
        }
        $result = multi_array_sort($result,'cname');
        return array('ids' => $ids, 'list' => $result);
    }

    /**
     * 获取客服列表 未量房使用
     * @return [type] [description]
     */
    public function getLiangfangKfManagerList($id, $uid, $cs_help_user)
    {
        //如果id或uid为空返回false
        if (empty($id) || empty($uid)) {
            return false;
        }

        if (!in_array($uid, array(2,31,30))) {
            //其他人获取所有的客服列表
            $join = 'LEFT JOIN qz_adminuser AS z ON z.id = a.id';
        } else {
            //客服，客服组长 可以看到相同权限
            switch ($uid) {
                //客服
                case '2':
                    $map['a.id'] = $id;
                    $join = 'LEFT JOIN qz_adminuser AS z ON z.kfgroup = a.kfgroup';
                    break;
                //客服组长
                case '31':
                    $map['a.id'] = $id;
                    $join = 'LEFT JOIN qz_adminuser AS z ON z.kfgroup = a.kfgroup';
                    break;
                //客服主管
                case '30':
                    $map['a.id'] = $id;
                    $join = 'LEFT JOIN qz_adminuser AS z ON FIND_IN_SET(a.id,z.kfmanager)';
                    break;
                default:
                    return false;
                    break;
            }
        }

        //限制为正常在使用的账号
        $map['z.stat'] = 1;
        //限制为只能是客服和客服组长
        $map['z.uid'] = array('IN', array(2,31));

        $result = M('adminuser')->alias('a')
            ->field('z.id,z.name')
            ->join($join)
            ->where($map)
            ->select();
        //添加名称首字母
        import('Library.Org.Util.App');
        $app = new \app();
        $ids = [];
        foreach ($result as $key => $value) {
            $str = $app->getFirstCharter($value["name"]);
            $result[$key]["cname"] = $str.' '.$value["name"];
            $ids[] = $value['id'];
        }
        $result = multi_array_sort($result,'cname');
        return array('ids' => $ids, 'list' => $result);
    }

    /**
     * 查询客服信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function findKfInfo($id)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M('adminuser')->where($map)->find();
    }

    /**
     * 获取客服登录天数
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getKfLoginDay($id,$group,$manager,$begin,$end)
    {
        $map = array(
            // "a.uid" => array("IN",array(2,31)),
            "a.uid" => array("IN",array(2)),
            "a.stat" => array("EQ",1),
            // "log.time" => array(
            //     array("EGT",$begin),
            //     array("LT",$end)
            // )
        );
        if (!empty($id)) {
            $map["a.id"] = array("EQ",$id);
        }
        if (!empty($group)) {
            $map["a.kfgroup"] = array("EQ",$group);
        }

        if (!empty($manager)) {
            $map["_string"] = "find_in_set($manager,a.kfmanager)";
        }

        $buildSql = M("adminuser")->where($map)->alias("a")
            ->join("left join qz_admin_logging log on log.uid = a.id and log.time >= $begin and log.time <= $end")
            ->field(' a.id,a.name,FROM_UNIXTIME(log.time,"%Y-%m-%d") as date,a.kftype,a.kfgroup,a.kfmanager')
            ->group("id,date")
            ->buildSql();

        return  M("adminuser")->table($buildSql)->alias("t")
            ->join('join qz_adminuser u on u.id = substring_index(t.kfmanager,",",1)')
            ->field("t.id,t.name,t.kftype,t.kfgroup,t.kfmanager,count(t.id) as `day`,u.name as manager")
            ->group("t.id")->select();
    }
    /**
     * 获取客服登录天数量房统计
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getKfLFLoginDay($id,$group,$manager,$begin,$end)
    {
        $map = array(
            "a.uid" => array("IN",array(2,31)),
            "a.stat" => array("EQ",1)
        );
        if (!empty($id)) {
            $map["a.id"] = array("EQ",$id);
        }
        if (!empty($group)) {
            $map["a.kfgroup"] = array("EQ",$group);
        }

        if (!empty($manager)) {
            $map["_string"] = "find_in_set($manager,a.kfmanager)";
        }
        $buildSql = M("adminuser")->where($map)->alias("a")
            ->join("left join qz_admin_logging log on log.uid = a.id and log.time >= ".$begin." and log.time <= ".$end)
            ->field(' a.id,a.name,FROM_UNIXTIME(log.time,"%Y-%m-%d") as date,a.kftype,a.kfgroup,a.kfmanager')
            ->group("id,date")
            ->buildSql();
        return  M("adminuser")->table($buildSql)->alias("t")
            ->join('left join qz_adminuser u on u.id = substring_index(t.kfmanager,",",1)')
            ->field("t.id,t.name,t.kftype,t.kfgroup,t.kfmanager,count(t.date) as `day`,u.name as manager")
            ->group("t.id")->order("t.kfgroup")->select();
    }
    /**
     * 获取后台用户名称
     * @param  [] [description]
     * @return [type]        [description]
     */
    public function getKfNames()
    {
        $map['stat'] = 1;
        $users = M("adminuser")->where($map)->field("id,user,name")->group("name")->select();
        return $users;
    }

    /**
     * 根据ID获取后台用户名称
     * @param  string $idstr [会员id拼接的字符串]
     * @return [type]        [description]
     */
    public function upKfNameByIds($idstr)
    {
        $data["option_value"] = $idstr;
        $users = M("options")->where("option_name = 'kf_admin_order_users'")->save($data);
        return $users;
    }

    /**
     * 根据客服派单来统计客服登录时间
     * @param  [type] $id      [description]
     * @param  [type] $group   [description]
     * @param  [type] $manager [description]
     * @param  [type] $begin   [description]
     * @param  [type] $end     [description]
     * @return [type]          [description]
     */
    public function getKfLoginDayByPool($id,$group,$manager,$begin,$end)
    {
        $map = array(
            "addtime" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            "status" => array("EQ",0)
        );

        if (!empty($id)) {
            $map["op_uid"] = array("EQ",$id);
        }
        $buildSql = M("order_pool")->where($map)->alias("a")
                                   ->field("op_uid,FROM_UNIXTIME(addtime,'%Y-%m-%d') as date,count(if(a.type = 2,1,null)) as orderCount")
                                    ->group("op_uid,date")
                                    ->buildSql();
        $buildSql = M("order_pool")->table($buildSql)->alias("t")
                              ->field("t.op_uid,count(if(orderCount >= 35,1,null)) as 'day'")
                              ->group("t.op_uid")
                              ->buildSql();
        $map = array();
        if (!empty($group)) {
            $map["u.kfgroup"] = array("EQ",$group);
        }

        if (!empty($manager)) {
            $map["_string"] = "find_in_set($manager,u.kfmanager)";
        }
        return M("order_pool")->table($buildSql)->alias("t1")->where($map)
                              ->join("join qz_adminuser u on u.id = t1.op_uid")
                              ->join("join qz_adminuser u2 on u2.kfgroup = u.kfgroup and u2.uid = 31")
                              ->join("join qz_adminuser u1 on u1.id = substring_index(u.kfmanager,',',1)")
                              ->field("u.id,u.name,u.kftype,u.kfmanager,u.kfgroup,t1.day,u1.name as manager,u2.name as groupmanager")
                              ->group("u.id")->order("u.kfgroup")
                              ->select();
    }

    /**
     * 根据客服派单来统计客服量房登录时间
     * @param  [type] $id      [description]
     * @param  [type] $group   [description]
     * @param  [type] $manager [description]
     * @param  [type] $begin   [description]
     * @param  [type] $end     [description]
     * @return [type]          [description]
     */
    public function getKfLoginDayByLFPool($id,$group,$manager,$begin,$end)
    {
        $monthEnd = strtotime(date("Y-m-d", $end) . " 17:30:00");
        $monthBegin = strtotime(date("Y-m-d", strtotime("-1 day", $begin)). " 17:30:00");

        $map = array(
            "addtime" => array(
                array("EGT",$monthBegin),
                array("LT",$monthEnd)
            )
        );

        if (!empty($id)) {
            $map["op_uid"] = array("EQ",$id);
        }

        $buildSql = M("order_pool")->where($map)->alias("a")
            ->field("op_uid,FROM_UNIXTIME(addtime,'%Y-%m-%d') as date,count(if(a.type = 2,1,null)) as orderCount")
            ->group("op_uid,date")
            ->buildSql();

        $buildSql = M("order_pool")->table($buildSql)->alias("t")
            ->field("t.op_uid,count(if(orderCount >= 35,1,null)) as 'day'")
            ->group("t.op_uid")
            ->buildSql();

        $buildSql = M()->table($buildSql)->alias("t")
            ->join("JOIN qz_adminuser a ON a.id = t.op_uid")
            ->field("a.id,a.`name`,a.kftype,a.kfgroup,a.kfmanager,t.*")
            ->buildSql();

        $map = array();
        if (!empty($group)) {
            $map["t1.kfgroup"] = array("EQ",$group);
        }

        if (!empty($manager)) {
            $map["_string"] = "find_in_set($manager,t1.kfmanager)";
        }
        return M()->table($buildSql)->alias("t1")
            ->join("join qz_adminuser u2 on u2.kfgroup = t1.kfgroup and u2.uid = 31")
            ->join("join qz_adminuser u1 on u1.id = substring_index(t1.kfmanager,',',1)")
            ->field("t1.*,u1.`name` as manager,u2.`name` as groupmanager")
            ->group("t1.id")
            ->select();
    }
    /**
     * 获取对接客服列表
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getDockingKfList($select = false)
    {
        $map = array(
            "uid" => array("IN",array(97,31)),
            "stat" => array("EQ",1)
        );

        $result = M('adminuser')->where($map)->field("`name`,id,kfgroup")->order("kfgroup")->select();
        if($select){
            //添加名称首字母
            import('Library.Org.Util.App');
            $app = new \app();
            foreach ($result as $key => $value) {
                $str = $app->getFirstCharter($value["name"]);
                $result[$key]["name"] = $str.' '.$value["name"];
            }
            $result = multi_array_sort($result,'name');
        }
        return $result;
    }

    /**
     * 获取角色人员列表
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getRuleAdminuserList($select = false,$uid = [97,93])
    {
        $map = array(
            "uid" => array("IN",$uid),
            "stat" => array("EQ",1)
        );

        $result = M('adminuser')->where($map)->field("`name`,id,kfgroup")->order("kfgroup")->select();
        if($select){
            //添加名称首字母
            import('Library.Org.Util.App');
            $app = new \app();
            foreach ($result as $key => $value) {
                $str = $app->getFirstCharter($value["name"]);
                $result[$key]["name"] = $str.' '.$value["name"];
            }
            $result = multi_array_sort($result,'name');
        }
        return $result;
    }

    /**
     * [getAdminuserListByUid 获取管理用户列表]
     * @param  [type] $uid           [用户UID]
     * @param  string $stat          [状态是否正常]
     * @return [type]                [description]
     */
    public function getAdminuserListByUid($uid, $stat = '1')
    {
        if (empty($uid)) {
            return false;
        }
        if (!is_array($uid)) {
            $uid = array(intval($uid));
        }
        $map = array(
            'a.uid'  => array('IN', $uid),
            'a.stat' => $stat
        );

        $result = M('adminuser')->where($map)->alias("a")
                                ->join("join qz_role_department b on a.uid = b.role_id")
                                ->join("join qz_department c on c.id = b.department_id")
                                ->field("a.`name`,a.id,a.kfgroup,c.name as deptname,c.id as deptid")->select();
        //添加名称首字母
        import('Library.Org.Util.App');
        $app = new \app();
        foreach ($result as $key => $value) {
            $str = $app->getFirstCharter($value["name"]);
            $result[$key]["char_name"] = $str.' '.$value["name"];
        }
        $result = multi_array_sort($result,'name');
        return $result;
    }

    /*
     * 根据城市查询销售信息
     * @param  [type] $cs [description]
     * @return [type]     [description]
     */
    public function findSellsInfoByCity($cs)
    {
        $map = array(
            "uid" => array("in",array(3,7,36,43,58,71)),
            "_string" => "FIND_IN_SET($cs,cs)",
            "stat" => array("EQ",1)
        );
        $buildSql = M('adminuser')->where($map)->field("id,uid,cs,'$cs' as city,`name`,safe_tel,qq_work1")->buildSql();
        return M('adminuser')->table($buildSql)->alias("t")
                             ->field("t.city,
                                    t.name as first_name,
                                    t.safe_tel as first_tel,
                                    t.qq_work1 as first_qq,
                                    case
                                    when uid = 3 or uid = 43 then  1
                                    when uid = 7 or uid = 36 then  2
                                    when uid = 58 then  3
                                    when uid = 71 or uid = 36 then  4
                                    end type")
                             ->order("type")
                             ->select();
    }

    /**
     * 获取过活动订单的客服登录天数
     * @param  [type] $id      [客服ID]
     * @param  [type] $group   [客服组]
     * @param  [type] $begin   [开始时间]
     * @param  [type] $end     [结束时间]
     * @param  [type] $source  [活动ID]
     * @return array
     */
    public function getActivityLoginDayByPool($id,$group,$manager,$begin,$end,$source)
    {
       $map = array(
            "a.addtime" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            "a.status" => array("EQ",0)
        );

        if (!empty($id)) {
            $map["op_uid"] = array("EQ",$id);
        }

        $buildSql = M("order_pool")->where($map)->alias("a")
                                   ->join("join qz_orders o on o.id = a.orderid")
                                   ->field("op_uid,FROM_UNIXTIME(addtime,'%Y-%m-%d') as date,if(o.source in ($source),1,0) as mark")
                                   ->order("mark desc")
                                   ->buildSql();

        $buildSql = M("order_pool")->table($buildSql)->alias("t")
                                   ->field("t.op_uid,t.date,t.mark")
                                   ->group("t.op_uid,t.date")
                                   ->buildSql();

        $buildSql = M("order_pool")->table($buildSql)->alias("t")
                              ->field("t.op_uid,count(if(t.mark = 1,1,null)) as 'day'")
                              ->group("t.op_uid,t.date")
                              ->buildSql();

        $map = array();
        if (!empty($group)) {
            $map["u.kfgroup"] = array("EQ",$group);
        }

        if (!empty($manager)) {
            $map["_string"] = "find_in_set($manager,u.kfmanager)";
        }
        return M("order_pool")->table($buildSql)->alias("t1")->where($map)
                              ->join("join qz_adminuser u on u.id = t1.op_uid")
                              ->join("join qz_adminuser u2 on u2.kfgroup = u.kfgroup and u2.uid = 31")
                              ->join("join qz_adminuser u1 on u1.id = substring_index(u.kfmanager,',',1)")
                              ->field("u.id,u.name,u.kftype,u.kfmanager,u.kfgroup,sum(t1.day) as day,u1.name as manager,u2.name as groupmanager")
                              ->group("t1.op_uid")
                              ->select();

    }

    /**
     * 根据用户名获取用户信息
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public function getUserInfoByName($name)
    {
         $map = array(
            "a.name"=>array("EQ",$name)
                     );
        return M("adminuser")->where($map)->alias("a")
                             ->find();
    }


    /**
     * 获取部门的角色信息
     * @param $departmentId [string|int|array] 部门id(支持数字，数组，字符串)
     * @return [null|array] [返回null或者部门数量和部门角色]
     */
    public function getDepartmentUidById($departmentId)
    {
        if (empty($departmentId)){
            return null;
        }

        if (is_object($departmentId)){
            return null;
        }

        if (is_int($departmentId)){
            $departmentIds = (string)$departmentId;
        }

        if (is_array($departmentId)){
            $departmentIds = implode(',',$departmentId);
        }

        $map = array(
            "a.id" => ["in",$departmentIds],
            "a.enabled" => 0
        );
        $buildSql =  M("department")->where($map)->alias("a")
                       ->join("left join qz_department b on a.id = b.parentid")
                       ->join("left join qz_department c on b.id = c.parentid")
                       ->field("a.id,a.name,concat(a.id,',',IFNULL(group_concat(b.id),''),',',IFNULL(group_concat(c.id),'')) as ids")
                       ->buildSql();
        return  M("department")->table($buildSql)->alias("t")
                               ->join("join qz_role_department as b on find_in_set(b.department_id,t.ids)")
                               ->field("t.id,t.name,group_concat(b.role_id) as roles")
                               ->group("t.id")
                               ->find();
    }


    /**
     * 获取所有质检人员列表
     * @param  int $departmentId [质检部门ID]
     * @param  boolean $select [是否选择首字母]
     * @param  boolean $isStat [是否包括离职人员]
     * @return [type]          [description]
     */
    public function getQualityCheckingList($departmentId = 13,$select = false,$isStat = false)
    {
        $uidList = $this->getDepartmentUidById($departmentId);
        if (empty($uidList['roles'])){
            return [];
        }
        $where['stat'] = 1;
        $where['uid'] = ['in',$uidList['roles']];
        if ($isStat) {
            unset($where["stat"]);
        }
        $result = M('adminuser')->where($where)->field("`name`,id")->order("uid desc")->select();
        if($select){
            //添加名称首字母
            import('Library.Org.Util.App');
            $app = new \app();
            foreach ($result as $key => $value) {
                $str = $app->getFirstCharter($value["name"]);
                $result[$key]["name"] = $str.' '.$value["name"];
            }
            $result = multi_array_sort($result,'name');
        }
        return $result;
    }

    /**
     * 查询用户列表
     * @return [type] [description]
     */
    public function findUserInfoListByName($name)
    {
        $map = array(
            "a.name" => array("EQ",$name),
            "a.stat" => array("EQ",1)
        );

        return M("adminuser")->where($map)->alias("a")
                             ->join("join qz_rbac_role r on r.id = a.uid")
                             ->join("join qz_role_department rd on rd.role_id = a.uid")
                             ->join("join qz_department d on d.id = rd.department_id")
                             ->field("a.id,a.name,d.name as dept_name,r.role_name")
                             ->find();
    }

    /**
     * 获取角色下的用户数量
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getUserCountByRoleId($role_id)
    {
        $map = array(
            "uid" => array("EQ",$role_id),
            "stat" => 1
        );
        return M("adminuser")->where($map)->count();
    }

    /**
     * 获取用户列表
     * @param  [type] $ids   [角色ID]
     * @param  [type] $state [在职状态]
     * @return [type]        [description]
     */
    public function getUserListByUid($ids,$state)
    {
        $map = array(
            "a.uid" => array("IN", $ids),
            "a.stat" => array("EQ",1)
        );

        if (!empty($state)) {
            $map["a.state"] = array("EQ",$state);
        }

        return M("adminuser")->where($map)->alias("a")
            ->join("join qz_rbac_role as b on a.uid = b.id")
            ->field("a.id,a.name,a.user,a.uid,b.role_name")->order("kfgroup,uid,id")->select();
    }

    public function findUserCountByName($name)
    {
        $map = array(
            "user" => array("EQ",$name)
        );
        return M("adminuser")->where($map)->find();
    }

    /**
     *  根据角色获取客服列表
     * @param  [type] $uid   [角色列表组]
     * @param  [type] $kfmanager [客服师]
     * @param  [type] $group [客服组]
     * @return [type]        [description]
     */
    public function getKFListByUid($uid,$kfmanager,$group)
    {
        $map = array(
            "state" => array("EQ",1),
            "stat" => array("EQ",1)
        );

        if (is_array($uid)) {
            $map["uid"] = array("IN",$uid);
        }else{
            if(!empty($uid)){
                $map["uid"] = array("EQ",$uid);
            }
        }

        if (!empty($group)) {
            $map["kfgroup"] = array("EQ",$group);
        }

        if (!empty($kfmanager)) {
            $map["_string"] = "find_in_set($kfmanager,kfmanager)";
        }

        return M("adminuser")->where($map)->field("id")->select();
    }
}
