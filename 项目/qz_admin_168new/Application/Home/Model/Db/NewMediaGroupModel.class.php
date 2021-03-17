<?php
// +----------------------------------------------------------------------
// | NewMediaGroupModel
// +----------------------------------------------------------------------
namespace Home\Model\Db;

use Think\Model;

class NewMediaGroupModel extends Model
{
    protected $trueTableName = 'qz_new_media_group';

    public function getList($map, $offset, $length, $with_team_admin_id = false, $with_op_admin_id = false)
    {
        $result = M('new_media_group')->where($map)->limit($offset, $length)->order('create_time desc,id desc')->select();
        if ($with_team_admin_id) {
            $team_leader_ids = array_column($result, 'team_admin_id');
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

        if ($with_op_admin_id) {
            $op_admins_ids = array_column($result, 'op_admin_id');
            $op_admins = [];
            if ($op_admins_ids) {
                $op_admins = AdminuserModel::getAdminByMap(['id' => ['in', $op_admins_ids]], 'id,user,name');
            }

            foreach ($result as $k => $v) {
                $result[$k]['op_admin'] = [];
                foreach ($op_admins as $k1 => $v1) {
                    if ($v['op_admin_id'] == $v1['id']) {
                        $result[$k]['op_admin'] = $v1;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 统计考核组数量
     *
     * @param $map
     * @return mixed
     */
    public function getCount($map)
    {
        $count = M('new_media_group')->where($map)->count('id');
        return $count;
    }

    /**
     * 删除考核组
     *
     * @param $map
     * @return mixed
     */
    public function delGroup($map)
    {
        return M('new_media_group')->where($map)->delete();
    }

    /**
     * 添加用户考核组
     *
     * @param $data
     * @param $map
     * @return bool|mixed
     */
    public function addGroup($data,$map)
    {
        if (!empty($map)) {
            return M('new_media_group')->where($map)->save($data);
        }
        return M('new_media_group')->add($data);
    }

    /**
     * 获取一条考核组
     *
     * @param $map
     * @return mixed
     */
    public function getOne($map)
    {
        return M('new_media_group')->where($map)->find();
    }

    public function getGroupListByTeam($team_id, $field = '*')
    {
        $where = [
            'team_admin_id' => ['in', $team_id]
        ];
        return $this->field($field)->where($where)->select();
    }




    /**
     * 获取新媒体组
     * @return [type] [description]
     */
    public function getMediaTeamGroup($roleId, $team_id = 0, $group_id = 0, $month_time = 0){
        $map = array(
            "u.stat" => array("EQ", 1),
            "u.state" => array("EQ", 1)
        );

        if (is_array($roleId)) {
            $map["u.uid"] = array("IN", $roleId);
        } else {
            $map["u.uid"] = array("EQ", $roleId);
        }

        if (!empty($team_id)) {
            $map["mg.team_admin_id"] = array("EQ", $team_id);
        }

        if (!empty($group_id)) {
            $map["mg.id"] = array("EQ", $group_id);
        }

        $subSql = M("new_media_group")->alias("mg")->where($map)
            ->join("inner join qz_adminuser as u on u.id = mg.team_admin_id")
            ->join("left join qz_new_media_user_reletion as ur on ur.group_id = mg.id")
            ->join("left join qz_new_media_target as mt on mt.group_id = mg.id and mt.`month` = $month_time")
            ->field("mg.id,mg.`name`,mg.team_admin_id,u.`user` as team_name,GROUP_CONCAT(ur.assess_admin_id) as children_ids,IF(mt.target_num is null, 0, mt.target_num) target_num")
            ->group("mg.id")
            ->buildSql();

            $result = M()->table($subSql)->alias("t")
                ->order("t.team_admin_id asc, t.id asc")
                ->limit($offset, $limit)
                ->select();

        return $result;
    }

    /**
     * 组月目标查询
     * @param  [type] $groupIds [description]
     * @return [type]           [description]
     */
    public function getGroupMonthTarget($groupIds, $month){
        $map = array(
            "g.id" => array("IN", $groupIds)
        );

        return M("new_media_group")->alias("g")->where($map)
            ->join("left join qz_new_media_target as t on t.group_id = g.id and t.month = $month")
            ->join("left join qz_new_media_user_reletion as ur on ur.group_id = g.id")
            ->field("g.id,g.name,IF(t.target_num is null, 0, t.target_num) as target_num,count(ur.id) as user_count")
            ->group("g.id")
            ->select();
    }
}