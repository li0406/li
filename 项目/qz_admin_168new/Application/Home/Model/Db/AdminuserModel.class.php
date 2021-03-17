<?php
namespace Home\Model\Db;
use Think\Model;
class AdminuserModel extends Model
{
    protected static $idsArray = [];
    protected $autoCheckFields = false;
    //设置autoCheckFields属性为false后，就会关闭字段信息的自动检测，因为ThinkPHP采用的是惰性数据库连接，只要你不进行数据库查询操作，是不会连接数据库的。

    /**
     * 根据后台帐号id 获取 管辖城市 代管城市 城市id
     * @param  $admin_id 帐号id
     * @return              $rearr['allcs'] = $gx_city; 合并后的城市id 数组
     *                      $rearr['cs']    = $gx_city_cs; 管辖城市id 数组
     *                      $rearr['css']   = $gx_city_css; 代管城市id 数组
     */
    public function getAdmincscssByid($admin_id) {
        if (empty($admin_id)) {
            return false;
        }
        $map_kf = array(
            "id"=>array("EQ",$admin_id)
        );

        $result_kf = M("adminuser")->where($map_kf)->find();
        if ($result_kf) {
            $gx_city_cs  =  explode(',', trim($result_kf['cs'], ',')); //管辖城市
            $gx_city_css =  explode(',', trim($result_kf['css'], ',')); //代管城市
            if (!empty($gx_city_css)) {
                $gx_city     = array_merge($gx_city_cs, $gx_city_css);
            }else{
                $gx_city = $gx_city_cs;
            }
            
            $gx_city = array_filter($gx_city);

            $rearr['allcs'] = $gx_city;
            $rearr['cs']    = $gx_city_cs;
            $rearr['css']   = $gx_city_css;
            return $rearr;
        }else{
            return false;
        }
    }

    /**
     * 获取关联UID的后台人数
     * $uids uid字符串
     * $cs 城市信息
     * @return [type] [description]
     */
    public function getAdminByUid($uids, $field = '*', $cs = '')
    {
        $map = array(
            'uid' => array('IN', $uids),
            'stat' => array('EQ', 1),
            'state' => array('EQ', 1)
        );

        if(!empty($cs)){
            $map['_string'] = sprintf("FIND_IN_SET('%d', cs)", intval($cs));
        }

        return M('adminuser')->field($field)->where($map)->select();
    }

    /**
     * [addUserCitysByUid 增加用户管辖城市]
     * @param [string] $uids  [用户uid]
     * @param [string] $citys [管辖城市id]
     */
    public function addUserCitysByUid($uids,$citys){
        if(empty($uids)){
            return false;
        }
        if (is_array($uids)) {
            $uids = implode('","', $uids);
        }
        if (is_array($citys)) {
            $citys = implode(',', $citys);
        }
        $sql = 'UPDATE `qz_adminuser` SET `cs`=concat(if(cs is null,"",cs), ",'.$citys.'") WHERE ( `uid` IN ("'.$uids.'"))';
        $result = M()->query($sql);
        return $result;
    }

    /**
     * [delUserCitysByDepartment description]
     * @param  integer $cid        [城市id]
     * @param  integer $department [部门id]
     * @return [type]              [description]
     */
    public function delUserCitysByDepartment($cid = 0, $department = 0){
        if (empty($cid) || empty($department)) {
            return false;
        }
        $sql = 'UPDATE qz_adminuser u SET `cs`=REPLACE (cs,'.intval($cid).',"") WHERE uid IN ( SELECT role_id FROM qz_role_department WHERE department_id = '.intval($department).' )';
        $result = M()->query($sql);
        return $result;
    }

    /**
     * 根据ID获取后台人员信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getAdminInfoById($id){
        $map = array(
                    "id"=>array("EQ",$id),
                    'stat'=>array("EQ",1)
                         );
        return M("adminuser")->where($map)->find();
    }

    /**
     * 根据UID获取后台人员信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getAdminInfoByUid($uid, $stat = 1 , $field = '*')
    {
        if (is_array($uid)) {
            $map = ["uid" => array("in", $uid)];
        } else {
            $map = ["uid" => array("EQ", $uid)];
        }
        if (!empty($stat)) {
            $map['stat'] = ['eq', $stat];
        }
        return M("adminuser")->field($field)->where($map)->order('stat desc,state desc')->select();
    }

    /**
     * 客服分组列表
     * @return 查询结果
     */
    public function getAdminKfgroupIdList()
    {
        return M('adminuser')->field("kfgroup")->group('kfgroup')->order("kfgroup desc")->select();
    }

    /**
     * 获取客服列表 id name 并且按照拼音排序 并加上首字母和空格
     * @return [array] [返回的二维数组]类似如下格式 二维数组中有一位数组的id 和name
     * array(25) { [0] => array(2) { 'id' => string(3) "199" 'name' => string(8) "C 陈玲" } ……}
     */
    public function get_kefu_list($uid = '',$not = "")
    {
        import('@.ORG.Wwek');//载入拼音函数
        //获取客服列表
        if(!empty($uid)){
           $map['uid']= array("IN",$uid);//查询客服
        }
        $map['stat']=1;//帐号还在使用中
        if(!empty($not)){
          $map["uid"] = array(array("EQ",$uid),array("NEQ",$not));
        }
        $kefu_list=M('adminuser')->field(" id ,name ")->where($map)->order("convert(name using gbk)")->select();
        foreach ($kefu_list as $key => &$value)
        {
            $pinyin=getPinYin($value['name']);//取得首字母
            if (empty($pinyin)) {
                $pinyin=strtoupper($value['name']{0});//如果没有首字母 说明是英文开头 获取英文开头的首字母成为大写的拼音首字母
            }
            $value['name']=sprintf("%s %s",$pinyin,$value['name']);//合并成最终名称
            $first_char[]=$value['name'];//取名称作为排序的key
        }
        array_multisort($first_char,SORT_ASC,$kefu_list);//二维数组排序 针对客服名称首字母进行排序
        return $kefu_list;
    }

    public function getAdminUserListByUid($uid){
        //获取客服列表
        if(!empty($uid)){
            $map['uid']= array("IN",$uid);
        }
        $map['stat']=1;//帐号还在使用中
        return M('adminuser')->field("id,name,user")->where($map)->order("convert(user using gbk)")->select();
    }
    /**
     * 获取销售列表
     * @param  string  $cs             [销售管辖的城市]
     * @param  boolean $saler_id_array [是否只返回销售id的数组]
     * @return [type]                  [返回结果]
     */
    public function get_saler_list($cs='',$saler_id_array=false,$filter_myself=false)
    {
        load("extend");
        #获取销售列表
        if($_SESSION["uid"] == 1){
            //管理员
             $where="(uid in(3,7,36,40,41,42,43,45,46,58,59) ) and cs>'' and stat=1 ";
        }else{
           $groups = substr($_SESSION["admin_groups"],0,-1);
           if(!empty($groups)){
                //有群组管理权限的
                $cs_list=explode(',',$cs);
                $manage_saler_list=array();//定义自己所管理的销售列表数组
                $other_saler = array();
                foreach ($cs_list as $key => $value)
                {
                    $id=M('adminuser')->field('id')->where(sprintf("FIND_IN_SET(%s,cs) and uid in (".$groups.") and stat=1",$value))->select();//查询到这个销售 由于不一定只管理一个 所以会返回多行数组
                    if($id)
                    {
                        foreach ($id as $key => $v)
                        {
                            //遍历多行数组 然后取得管辖人
                            $manage_saler_list[]=$v['id'];//如果有记录 把销售加入自己所管理的销售列表
                        }
                    }
                }

                $css = $_SESSION["css"];
                //查询商务部门的角色
                $tel_saler_user_list = D('Adminrole')->getDepartmentAllAdminId('6');//商务部人员列表
                $tel_saler_user_list = explode(',', $tel_saler_user_list['id']);

                //如果CSS字段不为空，则标识有代管城市
                if(!empty($css)){
                    $cs_list=explode(',',$css);
                    foreach ($cs_list as $key => $value)
                    {
                        $id=M('adminuser')->field('id')->where(sprintf("FIND_IN_SET(%s,cs) and uid = 3 and stat=1",$value))->select();//查询到这个销售 由于不一定只管理一个 所以会返回多行数组
                        if($id)
                        {
                            foreach ($id as $key => $v)
                            {
                                //遍历多行数组 然后取得管辖人
                                $other_saler[]=$v['id'];//如果有记录 把销售加入自己所管理的销售列表
                            }
                        }
                    }

                    if (in_array($_SESSION['admin_id'],$tel_saler_user_list)){
                        //商务部的合并所有的销售
                        $manage_saler_list = array_merge($manage_saler_list,$other_saler);
                    }else{
                        //外销的只能看代管的销售
                        $manage_saler_list = $other_saler;
                    }
                }

                if (!$filter_myself){
                    #说明了我还要把自己加上
                    $manage_saler_list[]=$_SESSION['admin_id'];//把自己加入查询列表中
                }

                $manage_saler=implode(',',array_unique($manage_saler_list));//组成查询条件

                $where=sprintf("cs>'' and stat=1 and id in(%s)",$manage_saler);

           }else{
                //普通销售人员
                $where="cs>'' and stat=1 and id=".$_SESSION['admin_id'];
           }
        }

        $saler=M('adminuser')->where($where)
                ->field('id,case when name is NULL or not name then user else name end as name')
                ->order('CONVERT(name USING gbk)')->select();

        if ($saler_id_array)
        {
            #看来是只返回销售id的数组
            foreach ($saler as $key => $v)
            {
                $saler_id_list[]=$v['id'];//获取销售id加入销售id数组
            }
            return $saler_id_list;//返回此数组
        }
        return $saler;
    }

    // 得到客服管辖 和 代管的 的城市
      // [41] =>
      // array(4) {
      //   'id' =>
      //   string(3) "33"
      //   'user' =>
      //   string(6) "客服"
      //   'cs' =>
      //   string(37) "保定 石家庄 廊坊 徐州 三河"
      //   'csid' =>
      //   string(34) "130600,130100,131000,320300,131082"
      // }
    public function get_kf_city($sp=' ') {
        $cs     = array();
        $cs_map = M('adminuser')->where('uid=2 and stat=1')
            ->field('id,user,cs,css')->select(); //得到所有客服的信息
        foreach ($cs_map as &$map) {
            $allcs  = trim($map['cs'], ',') . trim($map['css'], ','); //合并管辖和 代管

            //处理csid
            $map['csid'] = $allcs; //赋值一个新的
        }
        $cs     = M('quyu')->where(array(
                    'xh'    => array('gt', 0),
                    'id'    => array('neq', 1),
                    'type'  => 1,
                    ))->field('cid, cname')->select();

        foreach ($cs_map as &$map) {
            $city   = array();
            foreach (preg_split('/,\s*/', $map['csid']) as $c0) {
                foreach ($cs as $c)
                    if ($c['cid'] == $c0) {
                        $city[] = $c['cname'];
                        break;
                    }
            }
            $map['cs']  = implode($sp, $city);
        }
        unset($map);
        return  $cs_map;
    }

    /**
     * 编辑用户信息
     * @return [type] [description]
     */
    public function  editUserInfo($id,$data){
        $map = array(
                "id"=>$id
                     );
        return M('adminuser')->where($map)->save($data);
    }

    /**
     * 根据用户编号列表获取用户信息
     * @return [type] [description]
     */
    public function getAdminUserInfoByIds($ids, $field = '*')
    {
        $map = array(
            "id" => array("IN", $ids),
            "stat" => array("EQ", 1)
        );
        return M('adminuser')->field($field)->where($map)->select();
    }

    /**
     * 根据ID查询用户信息
     * @param  [type] $id        [用户编号]
     * @param  [type] $solutions [VOIP提供商标识]
     * @return [type]            [description]
     */
    public function  findUserInfoById($id,$solutions='yuntongxun'){
        $map = array(
            "a.id"=>array("EQ",$id)
                     );
        return M("adminuser")->where($map)->alias("a")
                             ->join("left join qz_admin_voip_tels as b on b.use_id = a.id and solutions = '$solutions'")
                             ->field("a.pass,a.tel_work1,a.qq_work1,a.name,a.tel_customer_ser_num,a.safe_tel,b.voipAccount,a.tel_work1")
                             ->find();
    }

    //取列表
    public function getList($map,$orderby,$pagesize= 1,$pageRow = 10){
        $Db = M('user');
        $count  = $Db->where($map)->count();
        $result = $Db->field('id,on,classid,user,name,register_time')
                      ->order($orderby)
                      ->limit($pagesize.",".$pageRow)
                      ->where($map)
                      ->select();
        return array("result"=>$result,"count"=>$count);
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
            "uid" => array("IN",$roles)
        );
        return  M('adminuser')->where($map)->select();
    }

    /**
     * 查询管理后台用户信息
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public function getadminuserinfo($name)
    {
        $map = array(
            "a.name" => array("LIKE","%$name%"),
            "a.stat" => array("EQ",1)
        );

        return  M('adminuser')->where($map)->alias("a")
                              ->join("join qz_role_department b on a.uid = b.role_id")
                              ->join("join qz_department c on c.id = b.department_id")
                              ->limit(10)->field("a.name,c.name as department_name")->select();
    }

    //获取用户部门
    public function getUserDepartment($userid){
        $map['u.id'] = $userid;
        return M('adminuser')->alias("u")
                    ->field('u.id,u.uid,u.name,d.name as depname,r.department_id as depid,r.role_id')
                    ->join("INNER JOIN qz_role_department as r ON r.role_id = u.uid")
                    ->join("INNER JOIN qz_department as d ON d.id = r.department_id")
                    ->where($map)
                    ->find();
    }

    /**
     * 根据条件获取列表
     *
     * @param $map
     * @param string $field
     * @return mixed
     */
    public function getAdminByMap($map,$field = '*')
    {
        return M('adminuser')->where($map)->field($field)->select();
    }

    /**
     * 递归获取所有部门和子部门
     *
     * @param mixed $ids 多个部门ID
     * @return mixed
     */
    protected static function getDepartmentChildIds($ids)
    {
        foreach ($ids as $key => $id) {
            array_push(self::$idsArray, $id);
        }
        $childids = M('department')->where(['parentid' => ['in', $ids]])->getField('id',true);
        if (!empty($childids)) {
            self::getDepartmentChildIds($childids);
        }
        return array_unique(self::$idsArray);
    }

    /**
     * 根据顶级部门获取顶级部门下所有管理员
     *
     * @param $department_id_array
     * @return mixed
     */
    public static function getUserIdsByDepartments($department_id_array)
    {
        // 先获取当前部门下所有子部门
        $department_id_arrays = self::getDepartmentChildIds($department_id_array);
        if (empty($department_id_arrays)) {
            return [];
        }
        // 根据部门获取所有角色
        $role_id_array = M('role_department')->where(['department_id' => ['in', $department_id_arrays]])->getField('role_id',true);
        if (empty($role_id_array)) {
            return [];
        }
        // 根据角色获取所有管理员
        return M('adminuser')->where(['uid' => ['in',$role_id_array]])->getField('id',true);
    }

    /**
     * 根据角色ID获取部门ID
     *
     * @param $role_id
     * @return mixed
     */
    public function getDeptidByRoleid($role_id)
    {
        return M('role_department')->where(['role_id' => $role_id])->getField('department_id');
    }

    // 根据城市和角色获取用户
    public function getAdminuserByUidAndCity($uid, $cs){
        $map = array(
            "uid" => array("EQ", $uid),
            "`stat`" => array("EQ", 1),
            "`state`" => array("EQ", 1),
            "_string" => "FIND_IN_SET($cs, cs)"
        );

        return M("adminuser")->where($map)
            ->field("id,uid,`name`,`user`")
            ->select();
    }

    public function getUserCity($user_id){
        $where = [
            'id'=>['in',$user_id],
            'stat'=>['eq',1],
        ];
        return $this->where($where)->select();
    }

    public function getUserInfoById($u_id)
    {
        $where = [
            'id' => ['in', $u_id]
        ];
        return $this->field('id,name,user')->where($where)->select();
    }
}