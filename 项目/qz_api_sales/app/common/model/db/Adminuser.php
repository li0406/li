<?php
// +----------------------------------------------------------------------
// | Adminuser
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Db;
use think\Model;
use think\db\Query;
use think\db\Where;

class Adminuser extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_adminuser';

    /**
     * 关联获取管理员角色
     *
     * @return \think\model\relation\HasOne
     */
    public function roler()
    {
        return $this->hasOne('RbacRole','id','uid')->field('id,role_name')->with('department');
    }

    /**
     * 获取列表
     *
     * @param array $map 查询条件
     * @param array $with 关联信息
     * @param null $page 分页
     * @param null $pageSize 分页
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getList($map, $page = null, $pageSize = null, $with = [], $field = 'u.*')
    {
        if ($page == 0) {
            $offset = 0;
        } else {
            $offset = $pageSize * ($page - 1);
        }
        return self::where(new Where($map))->alias('u')
            ->field($field)
            ->join('qz_rbac_role r','r.id = u.uid')
            ->leftJoin('qz_role_department rd','rd.role_id = r.id')
            ->leftJoin('qz_department d','d.id = rd.department_id')
            ->leftJoin('qz_department b','b.id = d.parentid')
            ->leftJoin('qz_department c','c.id = b.parentid')
            ->limit($offset, $pageSize)
            ->order('u.id asc')
            ->with($with)
            ->select();
    }

    /**
     * 获取列表
     *
     * @param array $map 查询条件
     * @param array $with 关联信息
     * @param null $page 分页
     * @param null $pageSize 分页
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getCount($map)
    {
        return self::where(new Where($map))->alias('u')->join('qz_rbac_role r','r.id = u.uid')->count();
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

        return $this->where(new Where($map))->alias("a")
            ->leftJoin("qz_admin_voip_tels b","b.use_id = a.id")
            ->field("a.*,b.voippwd,b.ytx_subaccountsid,b.ytx_friendlyname,b.ytx_subtoken,b.voipaccount")
            ->find();
    }

    /**
     * 根据ID查找管理用户信息
     * @Author   liuyupeng
     * @DateTime 2019-05-17T15:34:43+0800
     */
    public static function findInfoById($id, $field = '*')
    {
        return static::where('id', $id)->field($field)->find();
    }

    /**
     * 根据管理用户角色获取下级用户
     * @Author   liuyupeng
     * @DateTime 2019-05-17T15:49:42+0800
     */
    public static function findChildrenByUid($uid){
        return static::alias('u')
            ->join('rbac_node_group g', 'FIND_IN_SET(u.uid,g.role_id)')
            ->where('g.group_id', $uid)
            ->field('u.id,u.user')
            ->select()
            ->toArray();

        // 子查询方式
        // return static::alias('u')->whereExists(function($query) use ($uid){
        //             $query->name('rbac_node_group')->alias('g')
        //                 ->where('g.group_id', $uid)->where('FIND_IN_SET(u.uid,g.role_id)');
        //         })->field('u.id,u.user')->select()->toArray();
    }

    /**
     * 根据条件获取管理员信息
     *
     * @param $map
     * @param string $field
     * @return array|null|\PDOStatement|string|Model
     */
    public function getAdminUserInfo($map, $field = 'user,pass,uid,logo', $with = [])
    {
        return self::alias('u')->field($field)->where(new Where($map))->with($with)->find();
    }
    
    /**
     * 根据角色ID获取用户
     *
     * @param  [type] $uids [description]
     * @return [type]       [description]
     */
    public static function getListByUids($uids, $field = "id,user", $using = true)
    {
        $map['uid'] = ['in', $uids];
        if ($using == true) {
            $map['stat'] = 1;
            $map['state'] = 1;
        }
        return static::where(new Where($map))->field($field)->select()->toArray();
    }
    /**
     * 根据用户搜索关键词获取城市负责人
     * @param  [type] $city_manager [description]
     * @return [type]               [description]
     */
    public static function getCityManagersByKeyword($keyword){
        $map = new Query();
        $map->where("u.stat", 1);
        $map->where("u.state", 1);
        
        if (!empty($keyword)) {
            $map->where(function($query) use ($keyword) {
                $query->where("u.id", $keyword)
                    ->whereOr("u.name", "like", "%".$keyword."%");
            });
        }

        return static::alias("u")->where($map)
            ->join("rbac_role r", "r.id = u.uid and r.grade = 4")
            ->field("u.id,u.name,u.cs,u.name as value")
            ->select()->toArray();
    }

    /**
     * 根据城市ID获取城市管理者
     * @return [type] [description]
     */
    public static function getCityManagersByCitys($citys){
        $map = new Query();
        $map->where("u.stat", 1);
        $map->where("u.state", 1);

        $wherecs = "";
        foreach ($citys as $key => $cs) {
            $wherecs .= " FIND_IN_SET($cs, u.cs) or";
        }
        
        $wherecs = trim($wherecs, "or");
        return static::alias("u")->where($map)->where($wherecs)
            ->join("rbac_role r", "r.id = u.uid and r.grade = 4")
            ->field("u.id,u.name,u.cs")
            ->select()->toArray();
    }

    /**
     * 根据部门和角色获取用户
     * @param  [type] $dept_id [description]
     * @param  [type] $role_id [description]
     * @return [type]          [description]
     */
    public function getUserByDeptAndRole($dept_id, $role_id){
        $map = new Query();
        $map->where("u.stat", 1);
        $map->where("u.state", 1);
        $map->where("u.uid", $role_id);
        $map->where("rd.department_id", $dept_id);

        return static::alias("u")->where($map)
            ->join("role_department rd", "rd.role_id = u.uid")
            ->field("u.id,u.`user`,u.`name`")
            ->select()->toArray();
    }

    /**
     * 获取用户openid记录
     * @param  [type] $user_id [description]
     * @param  [type] $appid   [description]
     * @param  [type] $openid  [description]
     * @return [type]          [description]
     */
    public function getOpenid($user_id, $appid, $openid){
        $map = new Query();
        $map->where("user_id", $user_id);
        $map->where("wx_appid", $appid);
        $map->where("wx_openid", $openid);

        return Db::name("adminuser_openid")->where($map)->find();
    }

    /**
     * 获取用户openid记录
     * @param  [type] $user_id [description]
     * @param  [type] $appid   [description]
     * @param  [type] $openid  [description]
     * @return [type]          [description]
     */
    public function getUserOpenidCount($user_id, $appid){
        $map = new Query();
        $map->where("wx_appid", $appid);
        $map->where("user_id", $user_id);
        $map->where("wx_unionid", "<>", "");

        return Db::name("adminuser_openid")->where($map)->count("id");
    }


    /**
     * 添加用户openid记录
     * @param [type] $user_id [description]
     * @param [type] $appid   [description]
     * @param [type] $openid  [description]
     * @param string $unionid [description]
     */
    public function addOpenid($user_id, $appid, $openid, $unionid = ""){
        $data = array(
            "user_id" => $user_id,
            "wx_appid" => $appid,
            "wx_openid" => $openid,
            "wx_unionid" => $unionid,
            "created_at" => time(),
            "updated_at" => time()
        );

        return Db::name("adminuser_openid")->insert($data);
    }

    /**
     * 更新用户openid记录
     * @param  [type] $id      [description]
     * @param  [type] $unionid [description]
     * @return [type]          [description]
     */
    public function updateOpenid($id, $unionid){
        $data = array(
            "wx_unionid" => $unionid,
            "updated_at" => time()
        );

        return Db::name("adminuser_openid")->where("id", $id)->update($data);
    }

    /**
     * 获取销售其他业绩人
     * @param  [type] $department_id [description]
     * @param  [type] $filter_ids    [description]
     * @return [type]                [description]
     */
    public function getSalerOtherPerson($department_id, $filter_ids = [], $saler_name = ""){
        $map = new Query();
        $map->where("u.stat", 1);
        $map->where("u.state", 1);
        if (is_array($department_id)) {
            $map->where("d.department_id", "in", $department_id);
        } else {
            $map->where("d.department_id", $department_id);
        }

        if (!empty($filter_ids)) {
            $map->where("u.id", "not in", $filter_ids);
        }

        if (!empty($saler_name)) {
            $map->where("u.user", "like", "%".$saler_name."%");
        }

        return $this->alias("u")->where($map)
            ->join("role_department d", "d.role_id = u.uid")
            ->join("rbac_role r", "r.id = u.uid and r.`stat` = 1", "inner")
            ->join("sale_team_map m", "m.current_id = u.id and m.`module` = 2", "left")
            ->join("sale_team t", "t.id = m.top_id and t.`status` = 1", "left")
            ->field(["u.id as saler_id", "u.`user` as saler_name", "t.team_name", "d.role_id", "d.department_id"])
            ->order("u.id desc")
            ->select();
    }

    public function getOnce($map, $field = "user,pass,uid,logo")
    {
        return $this->where($map)->field($field)->find();
    }

    /**
     * 获取代管客服的管辖城市
     * @param $ids
     * @param string $field
     * @return array|\PDOStatement|string|\think\Collection|\think\model\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCssUsers($ids, $field = "cs")
    {
        $map = [];
        if (is_array($ids)) {
            $map[] = ["id", "in", $ids];
        } else {
            $map[] = ["id", "=", $ids];
        }
        return $this->where($map)
            ->field($field)
            ->select();
    }


}