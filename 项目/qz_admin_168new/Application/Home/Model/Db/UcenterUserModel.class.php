<?php


namespace Home\Model\Db;


use Think\Model;

class UcenterUserModel extends Model
{
    protected $tableName = "ucenter_user";

    /**
     * 用户管理获取满足条件的用户总量
     * @param $map  条件
     * @return mixed
     */
    public function getAllUserByMapCount($map)
    {
        $list = $this->alias("u")
            ->where($map)
            ->field('u.uuid')
            ->join("qz_ucenter_prop a on a.uuid = u.uuid")
            ->join("qz_ucenter_profile b on b.uuid = u.uuid")
            ->join("qz_ucenter_platform c on c.slot = u.platform")
            ->group("u.uuid")
            ->select();
        return count($list);
    }


    /**
     * 获取满足条件的产品线数据
     * @param $map
     * @param string $source
     * @return int
     */
    public function getPlatformUserListCount($map, $source = '')
    {
        $list = $this->alias("u")
            ->where($map)
            ->field('u.uuid')
            ->join("left join qz_ucenter_prop a on a.uuid = u.uuid and a.platform = '" . $source . "'")
            ->join("qz_ucenter_profile b on b.uuid = u.uuid")
            ->join("qz_ucenter_platform c on c.slot = u.platform")
            ->group("u.uuid")
            ->select();
        return count($list);
    }

    public function getPlatformUserCount($source = '')
    {
        $map = [];
        $map['a.platform'] = $source;
        $list = $this->alias('u')
            ->where($map)
            ->join("qz_ucenter_prop a on a.uuid = u.uuid")
            ->join("qz_ucenter_profile b on b.uuid = u.uuid")
            ->join("qz_ucenter_platform c on c.slot = u.platform")
            ->field('u.uuid')
            ->group('u.uuid')
            ->select();
        return count($list);
    }


    /**
     * 用户管理列表页获取用户列表
     * @param string $map 条件
     * @param int $pageIndex 分页信息
     * @param int $pageCount 分页信息
     * @return mixed
     */
    public function getAllUserByMap($map = "", $pageIndex = 0, $pageCount = 20, $sort = 'u.uuid desc')
    {
        $buildsql = $this->alias("u")
            ->where($map)
            ->field("u.id,u.uuid,u.phone,u.status,u.type,b.avatar,b.nickname,b.gender,max(a.last_actived_at) last_actived_at,b.registered_at,b.decoration_stage,b.is_ordered,c.name laiyuan,b.city")
            ->join("qz_ucenter_prop a on a.uuid = u.uuid")
            ->join("qz_ucenter_profile b on b.uuid = u.uuid")
            ->join("qz_ucenter_platform c on c.slot = u.platform")
            ->limit($pageIndex, $pageCount)
            ->order($sort)
            ->group("u.uuid")
            ->buildSql();

        return $this->table($buildsql)->alias('t')
            ->field('t.*,q.cname u_city')
            ->join("left join qz_quyu q on q.cid = t.city")
            ->select();
    }


    /**
     * 获取产品线下的用户列表
     * @param string $map
     * @param int $pageIndex
     * @param int $pageCount
     * @return mixed
     */
    public function getPlatformUserList($map = "", $pageIndex = 0, $pageCount = 20, $sort = 'u.uuid desc', $sort2 = 't.uuid desc', $source = '')
    {
        $buildsql1 = $this->alias("u")
            ->where($map)
            ->field("u.id,u.uuid,u.type,u.safe_email as email,u.platform,u.status,b.avatar,b.nickname,a.platform as prop_platform,a.last_actived_at last_actived_at,a.first_actived_at")
            ->join("left join qz_ucenter_prop a on a.uuid = u.uuid and a.platform = '" . $source . "'")
            ->join("qz_ucenter_profile b on b.uuid = u.uuid")
            ->join("qz_ucenter_platform c on c.slot = u.platform")
            ->limit($pageIndex, $pageCount)
            ->order($sort)
            ->group("u.uuid")
            ->buildsql();
        $list = M()->table($buildsql1)->alias('t')
            ->field('t.*,GROUP_CONCAT(o.oauth_type) as oauth_typelist')
            ->join("left join qz_ucenter_oauth o on o.uuid = t.uuid and o.platform = t.prop_platform")
            ->order($sort2)
            ->group("t.uuid,t.platform")
            ->select();
        return $list;

    }

    /**
     * 根据id获取用户基本信息
     * @param $id
     * @return mixed
     */
    public function getUserBasicInfoById($id)
    {
        $map = [];
        $map['u.id'] = array("eq", $id);
        $buildsql = $this->alias("u")
            ->where($map)
            ->field("u.id,u.uuid,u.phone,u.status,u.type,u.safe_email as email,b.avatar,b.nickname,b.gender,max(a.last_actived_at) last_actived_at,b.registered_at,b.decoration_stage,b.is_ordered,b.province,b.city,c.name laiyuan")
            ->join("qz_ucenter_prop a on a.uuid = u.uuid")
            ->join("qz_ucenter_profile b on b.uuid = u.uuid")
            ->join("qz_ucenter_platform c on c.slot = u.platform")
            ->group("u.uuid")
            ->buildSql();

        return $this->table($buildsql)->alias('t')
            ->field('t.*,p.qz_province,q.cname u_city')
            ->join("left join qz_province p on p.qz_provinceid = t.province")
            ->join("left join qz_quyu q on q.cid = t.city")
            ->find();

    }

    /**
     * 根据uuid和产品线的slot获取某一产品线的三方绑定信息
     * @param int $uuid
     * @param string $platform
     * @return mixed
     */
    public function getPlatformUserinfo($uuid = 0, $platform = "")
    {
        $map = [];
        $map['p.uuid'] = array('eq', $uuid);
        $map['p.platform'] = array('eq', $platform);
        $list = M('ucenter_prop')->alias('p')
            ->field('GROUP_CONCAT(DISTINCT o.oauth_type) as oauth_typelist,p.first_actived_at,p.last_actived_at')
            ->where($map)
            ->join('left join qz_ucenter_oauth o on o.uuid = p.uuid and o.platform = p.platform')
            ->group("o.uuid")
            ->find();
        return $list;

    }


    /**
     * 根据用户id获取用户的状态    拉黑？ 还是未拉黑
     * @param $id
     */
    public function getUcenterUserstatusById($id)
    {
        $map = [];
        $map['id'] = array("eq", $id);
        return $this->where($map)->field('id,uuid,status')->find();
    }


    /**
     * 修改用户状态
     * @param $map
     * @param $data
     * @return bool
     */
    public function changeUcenterUser($map, $data)
    {
        return $this->where($map)->save($data);
    }

    /**
     * 根据用户id获取手机号
     */
    public function getUserPhoneById($id)
    {
        return $this->where(array('id' => $id))->field('phone')->find();
    }


}