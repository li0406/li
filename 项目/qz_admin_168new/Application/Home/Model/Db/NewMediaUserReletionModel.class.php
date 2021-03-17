<?php
// +----------------------------------------------------------------------
// | NewMediaUserReletionModel
// +----------------------------------------------------------------------
namespace Home\Model\Db;

use Think\Model;

class NewMediaUserReletionModel extends Model
{
    protected $trueTableName = 'qz_new_media_user_reletion';

    /**
     * 获取绑定人员列表
     *
     * @param $map
     * @param $offset
     * @param $length
     * @param bool $with_team_admin_id
     * @return mixed
     */
    public function getList($map, $offset = null, $length = null, $with_team_admin_id = false)
    {
        $result = M('adminuser')->alias('u')
            ->join('qz_new_media_user_reletion a on u.id = a.assess_admin_id')
            ->join('qz_new_media_group g on g.id = a.group_id')
            ->field('u.uid,u.name,u.user,u.stat,u.state,a.id,a.assess_admin_id,a.group_id,a.create_time,g.name as group_name,g.team_admin_id')
            ->where($map)
            ->limit($offset, $length)
            ->order('a.create_time desc,id desc')
            ->select();
        if ($with_team_admin_id) {
            $team_leader_ids = array_filter(array_column($result, 'team_admin_id'));
            $team_leaders = [];
            if ($team_leader_ids) {
                $team_leaders = AdminuserModel::getAdminByMap([
                    'id' => ['in', $team_leader_ids],
                    'stat' => 1,
                    'state' => 1,
                ], 'id,user,name');
            }

            foreach ($result as $k => $v) {
                $result[$k]['team_admin'] = [];
                foreach ($team_leaders as $k1 => $v1) {
                    if ($v['team_admin_id'] == $v1['id']) {
                        $result[$k]['team_admin'] = $v1;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * 获取绑定人员数量
     *
     * @param $map
     * @return mixed
     */
    public function getCount($map)
    {
        $count = M('adminuser')->alias('u')
            ->join('qz_new_media_user_reletion a on u.id = a.assess_admin_id')
            ->join('qz_new_media_group g on g.id = a.group_id')
            ->where($map)
            ->count('u.id');
        return $count;
    }

    /**
     * 添加用户关联
     *
     * @param $data
     * @param $map
     * @return bool|mixed
     */
    public function addUser($data,$map)
    {
        if (!empty($map)) {
            return M('new_media_user_reletion')->where($map)->save($data);
        }
        return M('new_media_user_reletion')->add($data);
    }

    /**
     * 获取一条记录
     *
     * @param $map
     * @return mixed
     */
    public function getOne($map)
    {
        return M('new_media_user_reletion')->where($map)->find();
    }

    /**
     * 删除用户关联
     *
     * @param $map
     * @return mixed
     */
    public function delUser($map)
    {
        return M('new_media_user_reletion')->where($map)->delete();
    }

    public function getGroupListByTeam($team_id, $field = '*')
    {
        $where = [
            'team_admin_id' => ['eq', $team_id]
        ];
        return $this->field($field)->where($where)->select();
    }

    /**
     * 获取绑定人员列表
     *
     * @param $map
     * @param $offset
     * @param $length
     * @param bool $with_team_admin_id
     * @return mixed
     */
    public function getNoneRelationList($map)
    {
        $result = M('adminuser')->alias('u')
            ->join('left join qz_new_media_user_reletion a on u.id = a.assess_admin_id')
            ->join('left join qz_new_media_group g on g.id = a.group_id')
            ->join('left join qz_adminuser t on t.id = g.team_admin_id and (t.stat = 1 and t.state = 1)')
            ->field('u.id,u.uid,u.name,u.user,u.stat,u.state,g.name as group_name,t.user team_admin_name')
            ->where($map)
            ->order('id desc')
            ->select();
        return $result;
    }

    /**
     * 获取设置过的离职人员列表
     * @param $map
     * @return mixed
     */
    public function getDimissionRelationList($map)
    {
        $result = M('adminuser')->alias('u')
            ->join('qz_new_media_user_reletion a on u.id = a.assess_admin_id')
            ->join('qz_new_media_group g on g.id = a.group_id')
            ->field('u.id,u.uid,u.name,u.user,u.stat,u.state,g.name as group_name')
            ->where($map)
            ->order('id desc')
            ->select();
        return $result;
    }

    /**
     * 获取绑定人员列表
     *
     * @param $map
     * @param $offset
     * @param $length
     * @param bool $with_team_admin_id
     * @return mixed
     */
    public function getUserRelation($map)
    {
        $result = M('adminuser')->alias('u')
            ->join('left join qz_new_media_user_reletion a on u.id = a.assess_admin_id')
            ->join('left join qz_new_media_group g on g.id = a.group_id')
            ->field('u.id as admin_id,u.uid,u.name,u.user,u.stat,a.id as relation_id,u.state,a.assess_admin_id,a.group_id,g.name as group_name,g.team_admin_id')
            ->where($map)
            ->find();
        return $result;
    }

    /**
     * 获取查询人员
     * @return [type] [description]
     */
    public static function getSearchUsers($roleId){
        $map = array(
            "u.stat" => array("EQ", 1),
            "u.state" => array("EQ", 1),
        );

        if (is_array($roleId)) {
            $map["tu.uid"] = array("IN", $roleId);
        } else {
            $map["tu.uid"] = array("EQ", $roleId);
        }

        return M("new_media_user_reletion")->alias("ur")->where($map)
            ->join("inner join qz_adminuser as u on u.id = ur.assess_admin_id")
            ->join("inner join qz_new_media_group as mg on mg.id = ur.group_id")
            ->join("inner join qz_adminuser as tu on tu.id = mg.team_admin_id")
            ->field("u.id as user_id,u.`user` as user_name")
            ->order("mg.team_admin_id asc,mg.id asc,ur.id desc")
            ->select();
    }

    /**
     * 获取统计人员
     * @return [type] [description]
     */
    public static function getStatisticUser($roleId, $user_id = 0, $team_id = 0, $group_id = 0){
        $map = array(
            "u.stat" => array("EQ", 1),
            "u.state" => array("EQ", 1),
        );

        if (is_array($roleId)) {
            $map["tu.uid"] = array("IN", $roleId);
        } else {
            $map["tu.uid"] = array("EQ", $roleId);
        }

        if (!empty($user_id)) {
            $map["ur.assess_admin_id"] = array("EQ", $user_id);
        }

        if (!empty($group_id)) {
            $map["ur.group_id"] = array("EQ", $group_id);
        }

        if (!empty($team_id)) {
            $map["mg.team_admin_id"] = array("EQ", $team_id);
        }

        return M("new_media_user_reletion")->alias("ur")->where($map)
            ->join("inner join qz_adminuser as u on u.id = ur.assess_admin_id")
            ->join("inner join qz_new_media_group as mg on mg.id = ur.group_id")
            ->join("inner join qz_adminuser as tu on tu.id = mg.team_admin_id")
            ->field("ur.id,u.id as user_id,u.`user` as user_name,ur.group_id,mg.`name` as group_name,mg.team_admin_id,tu.`user` as team_name")
            ->order("mg.team_admin_id asc,ur.group_id asc,ur.id desc")
            ->select();
    }

    /**
     * 新媒体业绩统计 - 业绩排行榜
     * @param  [type] $roleId [description]
     * @param  string $month  [description]
     * @return [type]         [description]
     */
    public static function getStatisticRankingList($type = "list", $roleId, $month, $offset = 0, $limit = 0){
        $map = array(
            "u.stat" => array("EQ", 1),
            "u.state" => array("EQ", 1),
        );

        if (is_array($roleId)) {
            $map["tu.uid"] = array("IN", $roleId);
        } else {
            $map["tu.uid"] = array("EQ", $roleId);
        }

        $dbQuery = M("new_media_user_reletion")->alias("ur")->where($map)
            ->join("inner join qz_adminuser as u on u.id = ur.assess_admin_id")
            ->join("inner join qz_new_media_group as mg on mg.id = ur.group_id")
            ->join("inner join qz_adminuser as tu on tu.id = mg.team_admin_id");

        if ($type == "count") {
            $result = $dbQuery->count("ur.id");
        } else {
            $month_time = strtotime($month);
            $month_begin = strtotime(date("Y-m-01", $month_time));
            $month_end = strtotime(date("Y-m-t", $month_time)) + 86399;

            $subMap = array(
                "cn.lasttime" => array("BETWEEN", [$month_begin, $month_end]),
                "o.type_fw" => array("EQ", 1)
            );

            $subSql = M("new_media_user_src")->alias("us")->where($subMap)
                ->join("inner join qz_yy_order_info as yy on yy.src = us.src")
                ->join("inner join qz_orders as o on o.id = yy.oid and o.`on` = 4")
                ->join("inner join qz_order_csos_new as cn on cn.order_id = o.id")
                ->field("us.assess_admin_id,count(us.assess_admin_id) finish_orders,us.type,us.src")
                ->group("us.assess_admin_id,us.type,us.src")
                ->buildSql();

            $result = $dbQuery->join("left join $subSql as t on t.assess_admin_id = ur.assess_admin_id")
                ->field("u.id as user_id,u.`user` as user_name, IF(t.finish_orders is null, 0, t.finish_orders) finish_orders,t.type,t.src,t.assess_admin_id")
                ->order("finish_orders desc,mg.team_admin_id asc,mg.id asc,u.id desc")
                ->select();
        }

        return $result;
    }
}