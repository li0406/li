<?php
//新媒体
namespace Home\Model\Logic;

use Home\Model\Db\AdminuserModel;
use Home\Model\Db\NewMediaGroupModel;
use Home\Model\Db\NewMediaTargetModel;
use Home\Model\Db\NewMediaUserSrcLogModel;
use Home\Model\Db\NewMediaUserSrcModel;
use Home\Model\Db\NewMediaUserReletionModel;
use Home\Model\Db\OrderSourceModel;

class NewMediaLogicModel
{

    /**
     * 团队角色ID
     * @var array
     */
    private static $teamRoleId = [108, 111];

    /**
     * 渠道部门ID
     * @var array
     */
    protected static $srcDepartmentId = [6, 11, 7, 12, 18, 19, 27, 28];

    /**
     * 人员部门ID
     * @var array
     */
    protected static $userDepartmentId = [26, 27];

    /**
     * 根据用户信息获取团信息
     */
    public function getTeamByUser()
    {
        $user = session('uc_userinfo');
        $admin = new AdminuserModel();
        //如果是 [视频督导][自媒体督导] 的角色, 则查询自己的团信息
        $field = 'id,uid,user,name';
        if (in_array($user['uid'], static::$teamRoleId)) {
            return $admin->getAdminByUid($user['uid'], $field);
        } else {
            return $admin->getAdminByUid(static::$teamRoleId, $field);
        }
    }

    /**
     * 根据团id获取所有组信息
     * @param $team_id
     * @return mixed
     */
    public function getGroupByTeam($team_id)
    {
        $group = new NewMediaGroupModel();
        return $group->getGroupListByTeam($team_id, 'id,name');
    }

    /**
     * 获取带分页组列表
     *
     * @return array
     */
    public function getGroupList()
    {
        $map = [];
        if (in_array(session('uc_userinfo.uid'), static::$teamRoleId)) {
            $map['team_admin_id'] = ['eq', session('uc_userinfo.id')];
        }
        $count = NewMediaGroupModel::getCount($map);
        if (!empty($count)) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, 20);
            $show = $p->show();
            $list = NewMediaGroupModel::getList($map, $p->firstRow, $p->listRows, true, true);
            return ['list' => $list, 'page' => $show];
        }
        return ['list' => [], 'page' => ''];
    }

    /**
     * 删除考核组
     *
     * @param $id
     * @return bool|mixed
     */
    public function delGroup($id)
    {
        if (empty($id)) {
            return false;
        }
        $Model = M();
        $Model->startTrans();
        try {
            $map['id'] = $id;
            // 删除用户关联
            NewMediaUserReletionModel::delUser(['group_id' => $id]);
            // 删除考核组设置
            NewMediaGroupModel::delGroup($map);
            // 删除组目标设置
            NewMediaTargetModel::delTargetByMap(['group_id' => $id]);
        }catch (\Exception $e) {
            $Model->rollback();
            return false;
        }
        $Model->commit();
        return true;
    }

    /**
     * 添加考核组
     *
     * @param $post
     * @return bool|mixed|string
     */
    public function addGroup($post)
    {
        $findWhere = $post;
        unset($findWhere['id']);
        if (!empty($post['id'])) {
            $findWhere['id'] = ['neq', $post['id']];
        }

        if (NewMediaGroupModel::getOne($findWhere)) {
            return '组名称重复';
        }
        $map = [];
        if (!empty($post['id'])) {
            $map['id'] = $post['id'];
        } else {
            $post['create_time'] = time();
        }
        $post['op_admin_id'] = session('uc_userinfo.id');
        $post['update_time'] = time();
        return NewMediaGroupModel::addGroup($post, $map);
    }

    /**
     * 获取考核人员列表
     *
     * @param $param
     * @return array
     */
    public function getUser($param, $pageSize = 20)
    {
        $map['u.stat'] = ['eq', 1];
        $map['u.id'] = ['in', ($user_id_array = self::getNewMediaUserIds()) ?: ''];

        // 考核人员管理 筛选
        if (!empty($param['name'])) {
            $map['u.user'] = ['like', '%' . $param['name'] . '%'];
        }

        if (!empty($param['team'])) {
            $map['g.team_admin_id'] = ['eq', $param['team']];
        }

        if (in_array(session('uc_userinfo.uid'), static::$teamRoleId)) {
            $map['g.team_admin_id'] = ['eq', session('uc_userinfo.id')];
        }

        if (!empty($param['group'])) {
            $map['a.group_id'] = ['eq', $param['group']];
        }

        if (!empty($param['state'])) {
            if ($param['state'] == 2) {
                $map['u.state'] = ['eq', 0];
            } else {
                $map['u.state'] = ['eq', $param['state']];
            }
        }

        // 考核人员/渠道设置 筛选
        if (!empty($param['u_id'])) {
            $map['a.assess_admin_id'] = ['IN', $param['u_id']];
        }

        if (!empty($param['src'])) {
            $user_ids = NewMediaUserSrcModel::getUsedSrc(['src' => $param['src']], 'assess_admin_id');
            if (!empty($param['u_id'])) {
                if (!in_array($param['u_id'], $user_ids)) {
                    return ['list' => [], 'page' => ''];
                } else {
                    $map['a.assess_admin_id'] = ['IN', $param['u_id']];
                }
            } else {
                if (empty($user_ids)) {
                    return ['list' => [], 'page' => ''];
                }
                $map['a.assess_admin_id'] = ['IN', $user_ids];
            }
        }

        $count = NewMediaUserReletionModel::getCount($map);
        if (!empty($count)) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, $pageSize);
            $show = $p->show();
            $list = NewMediaUserReletionModel::getList($map, $p->firstRow, $p->listRows, true);
            return ['list' => $list, 'page' => $show];
        }
        return ['list' => [], 'page' => ''];
    }

    /**
     * 添加考核人员关联
     *
     * @param $post
     * @return bool|mixed|string
     */
    public function addUser($post)
    {
        $findWhere['assess_admin_id'] = $post['assess_admin_id'];
        unset($findWhere['id']);
        if (!empty($post['id'])) {
            $findWhere['id'] = ['neq', $post['id']];
        }

        if (NewMediaUserReletionModel::getOne($findWhere)) {
            return '考核人员已存在';
        }
        $map = [];

        if (!empty($post['id'])) {
            $map['id'] = $post['id'];
        } else {
            $post['create_time'] = time();
        }
        $post['op_admin_id'] = session('uc_userinfo.id');
        $post['update_time'] = time();
        //添加人员渠道转移
        if(!empty($post['dimission_user_id'])){
            //转移当前人员的渠道
            $save = [
                'assess_admin_id' => $post['assess_admin_id'],
                'update_time' => time(),
            ];
            (new NewMediaUserSrcModel())->updateUserSrc(['assess_admin_id' => ['eq', $post['dimission_user_id']]], $save);
            unset($post['dimission_user_id']);
        }
        return NewMediaUserReletionModel::addUser($post, $map);
    }

    /**
     * 获取转移人员列表
     *
     * @return mixed
     */
    public function getTransferUser()
    {
        $map['u.stat'] = ['eq', 1];
        $map['u.id'] = ['in', ($user_id_array = self::getNewMediaUserIds()) ?: ''];
        $map['_complex'] = [
            'a.group_id' => ['exp', 'IS NULL'],
            't.user' => ['exp', 'IS NULL'],
            '_logic' => 'OR'
        ];
        return NewMediaUserReletionModel::getNoneRelationList($map);
    }

    /**
     * 获取设置过的离职人员列表
     *
     * @return mixed
     */
    public function getDimissionTransferUser()
    {
        $map['u.stat'] = ['eq', 1];
        $map['u.state'] = ['eq', 0];
        $map['u.id'] = ['in', ($user_id_array = self::getNewMediaUserIds()) ?: ''];
        return NewMediaUserReletionModel::getDimissionRelationList($map);
    }

    /**
     * 获取转移人员详细信息
     *
     * @return mixed
     */
    public function getTransferUserdetail($user_id, $reletion_id)
    {
        $map['u.stat'] = ['eq', 1];
        if (!empty($user_id)) {
            $map['u.id'] = ['eq', $user_id];
        }
        if (!empty($reletion_id)) {
            $map['a.id'] = ['eq', $reletion_id];
        }
        return NewMediaUserReletionModel::getUserRelation($map);
    }

    public function getUserSrc()
    {
        $map['u.stat'] = ['eq', 1];
        $map['u.id'] = ['in', ($user_id_array = self::getNewMediaUserIds()) ?: ''];
        if (!empty($param['name'])) {
            $map['u.name'] = ['like', '%' . $param['name'] . '%'];
        }

        if (!empty($param['team'])) {
            if (in_array(session('uc_userinfo.uid'), [108, 111])) {
                $map['g.team_admin_id'] = ['in', session('uc_userinfo.id')];
            } else {
                $map['g.team_admin_id'] = ['in', $param['team']];
            }
        }

        if (!empty($param['group'])) {
            $map['a.group_id'] = ['eq', $param['group']];
        }

        if (!empty($param['state'])) {
            if ($param['state'] == 2) {
                $map['u.state'] = ['eq', 0];
            } else {
                $map['u.state'] = ['eq', $param['state']];
            }
        }

        $count = NewMediaUserReletionModel::getCount($map);
        if (!empty($count)) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, 20);
            $show = $p->show();
            $list = NewMediaUserReletionModel::getList($map, $p->firstRow, $p->listRows, true);
            return ['list' => $list, 'page' => $show];
        }
        return ['list' => [], 'page' => ''];
    }

    /**
     * 根据顶级部门ID获取部门下所有管理人员ID
     *
     * @return mixed
     */
    public static function getNewMediaUserIds()
    {
        $user_id_array = S('Cache:NewMedia:UserIdArray');
        if (empty($user_id_array)) {
            $user_id_array = AdminuserModel::getUserIdsByDepartments(self::$userDepartmentId);
            S('Cache:NewMedia:UserIdArray', $user_id_array, 60);
        }
        return $user_id_array;
    }

    public function getTargetList($get, $team)
    {
        $where = [];
        $team = array_column($team, 'id');
        $pageIndex = I("p", 1);
        $pageCount = I("limit", 20);

        if (!empty($get['team_id'])) {
            if (!in_array($get['team_id'], $team)) {
                return [];
            }
            //获取所有管辖的组
            $groupList = $this->getGroupByTeam($get['team_id']);
            //如果有查询团就要将查询出来的组数据提取
            $returnData['group'] = $groupList;
        } else {
            //获取所有管辖的组
            $groupList = $this->getGroupByTeam($team);
        }
        $group = array_column($groupList, 'id');
        if (!empty($get['group_id'])) {
            if (!in_array($get['group_id'], $group)) {
                return [];
            }
            $group = $get['group_id'];
        }
        if (count($group) == 0) {
            return [];
        }
        if (!empty($get['time'])) {
            $time = date('Y-m', strtotime($get['time'])) . '-1 00:00:00';
            $where['t.month'] = ['eq',strtotime($time)];
        }
        $where['t.group_id'] = ['in', $group];
        $target = new NewMediaTargetModel();
        import('Library.Org.Util.Page');
        $count = $target->getTargetCount($where);
        if ($count > 0) {
            $p = new \Page($count, $pageCount);
            $returnData['list'] = $target->getTargetList($where, ($pageIndex - 1) * $pageCount, $pageCount);
            $returnData['page'] = $p->show();
        }
        return $returnData;
    }

    /**
     * 获取筛选人员
     * @return [type] [description]
     */
    public function getSearchUsers(){
        $user = session('uc_userinfo');
        $roleId = static::$teamRoleId;
        if (in_array($user['uid'], $roleId)) {
            $roleId = $user["uid"];
        }

        return NewMediaUserReletionModel::getSearchUsers($roleId);
    }

    public function getTargetInfo($id)
    {
        $target = new NewMediaTargetModel();
        $where = [
            't.id' => ['eq', $id]
        ];
        return $target->getTargetInfo($where);
    }

    /**
     * 新媒体业绩统计 - 按组统计
     * @param  integer $team_id [description]
     * @param  integer $group_id [description]
     * @param  [type]  $date_begin [description]
     * @param  [type]  $date_end   [description]
     * @return [type]              [description]
     */
    public function getStatisticsByGroup($team_id = 0, $group_id = 0, $date_begin, $date_end)
    {
        $time_begin = strtotime($date_begin);
        $time_end = strtotime($date_end) + 86399;
        $month_time = strtotime(date("Y-m-01", $time_begin));

        $user = session('uc_userinfo');
        $roleId = static::$teamRoleId;
        if (in_array($user['uid'], $roleId)) {
            $roleId = $user["uid"];
        }

        $newMediaGroupModel = new NewMediaGroupModel();
        $list = $newMediaGroupModel->getMediaTeamGroup($roleId, $team_id, $group_id, $month_time);

        $all = array(
            "target_num" => 0,
            "finish_count" => 0,
            "send_count" => 0,
            "send_rate" => "0%",
            "finish_rate" => "0%",
            "date_rate" => "0%",
            "diff_rate" => "0%",
        );

        if (!empty($list)) {
            // 组内的所有用户ID
            $children_ids = implode(",", array_column($list, "children_ids"));
            $children_ids = array_filter(array_unique(explode(",", $children_ids)));
            $userIds = array_values($children_ids);

            // 订单发单量统计
            $result_send = NewMediaUserSrcModel::getOrderSendStatistics($userIds, $time_begin, $time_end);
            $result_send = self::changeOrderSendStatistics($result_send,'send_orders');

            // 订单完成量统计
            $result_finish = NewMediaUserSrcModel::getOrderFinishStatistics($userIds, $time_begin, $time_end);
            $result_finish = self::changeOrderSendStatistics($result_finish,'finish_orders');

            // 时间进度
            $search_days = getTwoDateDays($date_begin, $date_end);
            $month_days = getTwoDateDays(date("Y-m-01", $time_begin), date("Y-m-t", $time_begin));
            $date_rate = sprintf("%.4f", $search_days / $month_days) * 100;

            foreach ($list as $key => $item) {
                $send_count = 0;
                $finish_count = 0;

                // 根据用户分配订单发单量和完成量
                if ($item["children_ids"]) {
                    $children_ids = explode(",", $item["children_ids"]);
                    foreach ($children_ids as $k => $userid) {
                        if (array_key_exists($userid, $result_send)) {
                            $send_count += $result_send[$userid]["send_orders"];
                        }

                        if (array_key_exists($userid, $result_finish)) {
                            $finish_count += $result_finish[$userid]["finish_orders"];
                        }
                    }
                }

                $list[$key]["send_count"] = $send_count;
                $list[$key]["finish_count"] = $finish_count;
                $list[$key]["send_rate"] = (sprintf("%.4f", $finish_count / $send_count) * 100) . "%";

                $finish_rate = sprintf("%.4f", $finish_count / $item["target_num"]) * 100;
                $list[$key]["diff_rate"] = ($date_rate - $finish_rate) . "%";
                $list[$key]["finish_rate"] = $finish_rate . "%";
                $list[$key]["date_rate"] = $date_rate . "%";

                $all["send_count"] += $send_count;
                $all["finish_count"] += $finish_count;
                $all["target_num"] += $item["target_num"];

                unset($send_count, $finish_count, $children_ids, $finish_rate);
            }

            $all["date_rate"] = $date_rate . "%";
            $all["send_rate"] = (sprintf("%.4f", $all["finish_count"] / $all["send_count"]) * 100) . "%";
            $all_finish_rate = sprintf("%.4f", $all["finish_count"] / $all["target_num"]) * 100;
            $all["diff_rate"] = ($date_rate - $all_finish_rate) . "%";
            $all["finish_rate"] = $all_finish_rate . "%";
        }

        return ["all" => $all, "list" => $list];
    }

	/**
     * 新媒体业绩统计 - 按人统计
     * @return [type] [description]
     */
    public function getStatisticsByUser($user_id, $team_id, $group_id, $date){
        $time_begin = strtotime($date);
        $time_end = strtotime($date) + 86399;
        $month_begin = strtotime(date("Y-m-01", $time_begin));

        $user = session('uc_userinfo');
        $roleId = static::$teamRoleId;
        if (in_array($user['uid'], $roleId)) {
            $roleId = $user["uid"];
        }

        $list = NewMediaUserReletionModel::getStatisticUser($roleId, $user_id, $team_id, $group_id);

        if (!empty($list)) {
            $userIds = array_column($list, "user_id");
            $groupIds = array_values(array_unique(array_column($list, "group_id")));
            // 组月目标查询
            $groups = NewMediaGroupModel::getGroupMonthTarget($groupIds, $month_begin);
            $groups = array_map(function($item){
                $item["target_avg"] = sprintf("%.2f", $item["target_num"] / $item["user_count"]);
                return $item;
            }, array_column($groups, null, "id"));

            // 订单发单量统计（当天）
            $result_send = NewMediaUserSrcModel::getOrderSendStatistics($userIds, $time_begin, $time_end);
            $result_send = self::changeOrderSendStatistics($result_send,'send_orders');

            // 订单完成量统计（当天）
            $result_finish = NewMediaUserSrcModel::getOrderFinishStatistics($userIds, $time_begin, $time_end);
            $result_finish = self::changeOrderSendStatistics($result_finish, 'finish_orders');

            // 订单完成量统计（当月）
            $month_finish = NewMediaUserSrcModel::getOrderFinishStatistics($userIds, $month_begin, $time_end);
            $month_finish = self::changeOrderSendStatistics($month_finish, 'finish_orders');

            // 计算天数
            $month_days = getTwoDateDays(date("Y-m-01", $time_begin), date("Y-m-t", $time_begin));
            $now_days = getTwoDateDays(date("Y-m-01", $time_begin), $date);
            $left_days = $month_days - $now_days;

            $all = array(
                "send_count" => 0,
                "finish_count" => 0,
                "left_day_count" => 0,
                "month_send_yugu" => 0,
                "send_rate" => "0%"
            );

            foreach ($list as $key => $item) {
                $send_count = 0; // 当天发单量
                if (array_key_exists($item["user_id"], $result_send)) {
                    $send_count = $result_send[$item["user_id"]]["send_orders"];
                }

                $finish_count = 0; // 当天完成量
                if (array_key_exists($item["user_id"], $result_finish)) {
                    $finish_count = $result_finish[$item["user_id"]]["finish_orders"];
                }

                $month_finish_count = 0; // 当月完成量
                if (array_key_exists($item["user_id"], $month_finish)) {
                    $month_finish_count = $month_finish[$item["user_id"]]["finish_orders"];
                }

                $list[$key]["send_count"] = $send_count;
                $list[$key]["finish_count"] = $finish_count;
                $list[$key]["send_rate"] = sprintf("%.4f", $finish_count / $send_count) * 100 . "%";

                // 月目标总量（平均到人）
                $month_target = $groups[$item["group_id"]]["target_avg"];

                // 剩余每天完成量
                if ($left_days > 0) {
                    $left_day_count = ($month_target - $month_finish_count) / $left_days;
                    $left_day_count = sprintf("%.2f", $left_day_count);
                    $list[$key]["left_day_count"] = $left_day_count;
                } else {
                    $left_day_count = 0;
                    $list[$key]["left_day_count"] = $left_day_count;
                }

                // 当月分单预估
                $month_send_yugu = ($month_finish_count / $now_days) * $month_days;
                $month_send_yugu = sprintf("%.2f", $month_send_yugu);
                $list[$key]["month_send_yugu"] = $month_send_yugu;

                // 当月剩余天数
                $list[$key]["left_days"] = $left_days;


                // 统计
                $all["send_count"] += $send_count;
                $all["finish_count"] += $finish_count;
                $all["left_day_count"] += $left_day_count;
                $all["month_send_yugu"] += $month_send_yugu;

                unset($send_count, $finish_count, $month_finish_count, $month_target, $month_send);
            }

            // 当天分单率
            $all["send_rate"] = sprintf("%.4f", $all["finish_count"] / $all["send_count"]) * 100 . "%";
        }

        return ["list" => $list, "all" => $all];
    }

    /**
     * 新媒体业绩统计 - 业绩排行榜
     * @param  [type]  $month [description]
     * @param  integer $page  [description]
     * @param  integer $limit [description]
     * @return [type]         [description]
     */
    public function getStatisticsRanking($month, $page = 1, $limit = 20, $export = false){
        $user = session('uc_userinfo');
        $roleId = static::$teamRoleId;
        if (in_array($user['uid'], $roleId)) {
            $roleId = $user["uid"];
        }

        if ($export == true) {
            $list = NewMediaUserReletionModel::getStatisticRankingList("list", $roleId, $month);
            $list = self::changeOrderSendStatistics($list,'finish_orders');
            $count = count($list);
        } else {
            $count = NewMediaUserReletionModel::getStatisticRankingList("count", $roleId);
            if ($count > 0) {
                $list = NewMediaUserReletionModel::getStatisticRankingList("list", $roleId, $month);
                $list = self::changeOrderSendStatistics($list,'finish_orders');
            }
        }
        $all['finish_count'] = 0;
        if (!empty($list)) {
            foreach ($list as $key => $item) {
                $list[$key]["month"] = date("m月", strtotime($month));
                $all['finish_count'] += $item['finish_orders'];
            }
        }

        return ["list" => $list, "count" => $count,'all'=>$all];
    }

    /**
     * 获取未分配渠道
     *
     * @return mixed
     */
    public function getSource($with_no_used = false,$assess_admin_id)
    {
        $map['dept'] = ['IN', self::$srcDepartmentId];
        if ($with_no_used) {
            $usedSrc = NewMediaUserSrcModel::getUsedSrc(['assess_admin_id' => ['NEQ',$assess_admin_id]]);
            $usedSrc = array_filter(array_unique($usedSrc));
            $map['src'] = ['NOT IN', $usedSrc ?: ''];
        }
        $map['visible'] = ['EQ', 0];
        return OrderSourceModel::getSourceList($map);
    }

    /**
     * 绑定当前用户src
     *
     * @param $list
     * @return mixed
     */
    public function bindSrc($list)
    {
        $user_ids = array_column($list, 'assess_admin_id');
        $userSrc = [];
        if (!empty($user_ids)) {
            $map['us.assess_admin_id '] = ['IN', $user_ids];
            $userSrc = NewMediaUserSrcModel::getUserSrc($map);
        }
        foreach ($list as $k => $v) {
            $list[$k]['source'] = [];
            foreach ($userSrc as $k1 => $v1) {
                if ($v['assess_admin_id'] == $v1['assess_admin_id']) {
                    $list[$k]['source'][] = $v1;
                }
            }
        }
        return $list;
    }

    /**
     * 获取所有已分配人员
     *
     * @return mixed
     */
    public function getRelationUsers()
    {
        return NewMediaUserReletionModel::getList([], null, null, false);
    }

    /**
     * 获取操作日志
     *
     * @param $user_id
     * @return array
     */
    public function getSrcLog($user_id)
    {
        $map['assess_admin_id'] = $user_id;
        $count = NewMediaUserSrcLogModel::getCount($map);
        if (!empty($count)) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, 10);
            $show = $p->show();
            $list = NewMediaUserSrcLogModel::getList($map, $p->firstRow, $p->listRows, true);
            foreach ($list as $k => $v) {
                $list[$k]['content'] = json_decode($v['content'], true);
                $list[$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            }
            return ['list' => $list, 'page' => $show];
        }
        return ['list' => [], 'page' => ''];
    }

    /**
     * 设置用户SRC
     *
     * @param $assess_admin_id
     * @param $src_list
     * @param $assess_admin_name
     * @return bool
     */
    public function setSrc($assess_admin_id, $src_list, $assess_admin_name)
    {
        $Model = M();
        $Model->startTrans();
        try {

            $now = time();
            $userSrc = $srcNameList = [];

            if (!empty($src_list)) {
                // 查找是否有分配记录
                $findWhere['us.assess_admin_id '] = ['EQ', $assess_admin_id];
                $userSrc = NewMediaUserSrcModel::getUserSrc($findWhere);
                // 根据src 获取要分配的src和名称
                $srcNameList = OrderSourceModel::getSourceList(['src' => ['IN', $src_list]], null, null, 'a.id,a.src,a.name,a.alias,dept.name dept_name,dept.dept_belong,sg.name group_name');
            }
            // 添加新增/编辑/删除的日志
            NewMediaUserSrcLogModel::addLog([
                'assess_admin_id' => $assess_admin_id,
                'op_admin_id' => session('uc_userinfo.id'),
                'action' => empty($src_list) ? '清除' : (empty($userSrc) ? '新增' : '更新'),
                'content' => json_encode([
                    'assess_admin_id' => $assess_admin_id,
                    'assess_admin_name' => $assess_admin_name,
                    'src_list' => $srcNameList
                ]),
                'create_time' => $now,
            ]);

            // 删除原有记录/增加新记录 (一对一的数据)
            NewMediaUserSrcModel::delUserSrc(['assess_admin_id' => ['eq', $assess_admin_id], 'type' => ['eq', 1]]);
            $list = [];
            foreach ($src_list as $v) {
                $list[] = [
                    'assess_admin_id' => $assess_admin_id,
                    'src' => $v,
                    'type' => 1,
                    'create_time' => $now,
                    'update_time' => $now,
                ];
            }

            if (!empty($list)) {
                NewMediaUserSrcModel::addAllUserSrc($list);
            }

        } catch (\Exception $e) {
            $Model->rollback();
            return false;
        }
        $Model->commit();
        return true;
    }

    public function saveTargetInfo($data, $user)
    {
        $save = [
            'target_num' => $data['month_num']
        ];
        $target = new NewMediaTargetModel();
        if (!empty($data['edit_id'])) {
            //只能设置该月和未来的数据
            $info = $target->getTargetInfo(['t.id' => ['eq', $data['edit_id']]]);
            if ((strtotime(date('Y-m') . '-1 00:00:00') - 1) >= $info['month_int']) {
                return ['status' => 0, 'info' => '无法设置当月之前的月目标!'];
            }
            //编辑
            $status = $target->saveTargetInfo($save, $data['edit_id']);
            if (empty($status)) {
                return ['status' => 0, 'info' => '请勿重复设置该组的月目标!'];
            }
        } else {
            //验证是否添加过月设置
            $where = [
                't.group_id' => ['eq', $data['group']],
                't.month' => ['eq', strtotime($data['months'] . '-01')],
            ];
            $info = $target->getTargetInfo($where);
            if ($info) {
                return ['status' => 0, 'info' => '已设置过该组的月目标!'];
            }
            //新增
            $save['group_id'] = $data['group'];
            $save['month'] = strtotime($data['months'] . '-01');
            $save['op_uid'] = $user['id'];
            $save['op_name'] = $user['name'];
            $save['add_time'] = time();
            $status = $target->addTargetInfo($save);
            if (empty($status)) {
                return ['status' => 0, 'info' => '添加失败 , 刷新后再试!'];
            }
        }
        return ['status' => 1, 'info' => ''];
    }

    public function delTarget($where){
        $target = new NewMediaTargetModel();
        return $target->delTarget($where);
    }

    /**
     * 根据公摊src获取设置的用户
     * @param $src
     * @return array
     */
    public function getUserByCommonSrc($src)
    {
        $where = [
            'src' => ['eq', $src],
        ];
        $db = new NewMediaUserSrcModel();
        $user = $db->getUserCommonSrc($where,'assess_admin_id');
        if(count($user) > 0){
            return array_column($user,'assess_admin_id');
        }
        return [];
    }

    /**
     * 获取公摊列表页
     * @return array
     */
    public function getUserCommonSrcList($src = [])
    {
        $uid = self::getNewMediaUserIds();
        if (count($uid) == 0) {
            return [];
        }
        $where = [
            'us.assess_admin_id' => ['in', $uid],
            'us.type' => ['eq', 2],
        ];
        if ($src) {
            $where['us.src'] = ['eq', $src];
        }
        $db = new NewMediaUserSrcModel();
        $count = $db->getUserCommonCount($where);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, 20);
            $user = $db->getUserCommonList($where, 'us.*,u.`user`,s.`name`', $p->firstRow, $p->listRows);
            foreach ($user as $k => $v) {
                $user[$v['src']]['src'] = $v['src'];
                $user[$v['src']]['name'] = $v['name'];
                $user[$v['src']]['user'][] = [
                    'user' => $v['user'],
                ];
                unset($user[$k]);
            }
            $show = $p->show();
            return ['list' => $user, 'page' => $show];
        }
        return [];
    }

    /**
     * 获取渠道用户信息
     * @return mixed
     */
    public function getNewMediaUsers(){
        return (new AdminuserModel())->getAdminUserInfoByIds(self::getNewMediaUserIds(),'id,user,name');
    }

    /**
     * 保存公摊渠道
     * @param $data
     * @return array
     */
    public function saveCommonSrc($data){
        if (empty($data['src'])) {
            return ['error_code' => 1, 'error_msg' => '缺少参数!'];
        }
        //删除所有设置过的用户
        $db = new NewMediaUserSrcModel();
        $db->delUserSrc(['src' => $data['src']]);
        if (count($data['user_list'])) {
            //添加公摊用户src
            $save = [];
            foreach ($data['user_list'] as $k => $v) {
                $save[] = [
                    'assess_admin_id' => $v,
                    'src' => $data['src'],
                    'type' => 2,
                    'create_time' => time(),
                    'update_time' => time(),
                ];
            }
            $db->addCommonSrc($save);
        }
        return ['error_code' => 0, 'error_msg' => '操作成功!'];
    }

    /**
     * 删除公摊渠道
     * @param $data
     * @return array
     */
    public function delCommonSrc($data)
    {
        if (empty($data['src'])) {
            return ['error_code' => 1, 'error_msg' => '缺少参数!'];
        }
        //删除所有设置过的用户
        $db = new NewMediaUserSrcModel();
        $status = $db->delUserSrc(['src' => $data['src']]);
        if ($status) {
            return ['error_code' => 0, 'error_msg' => '删除成功!'];
        } else {
            return ['error_code' => 1, 'error_msg' => '删除失败!'];
        }
    }

    /**
     * 给一对一用户数据添加公摊数据
     * @param $static
     * @param $field 查询出字段,用于循环塞数据
     * @return array
     */
    public static function changeOrderSendStatistics($static,$field){
        if(count($static) > 0){
            $userList = [];
            $commonSrc = [];
            foreach ($static as $k=>$v){
                $userList[$v['assess_admin_id']]['user_name'] = $v['user_name']?:'';
                //一对一
                if($v['type'] == 1){
                    $userList[$v['assess_admin_id']][$field] += $v[$field];
                }
                //多对多
                if($v['type'] == 2){
                    $userList[$v['assess_admin_id']]['common_src'][] = $v['src'];
                    //可能存在用户只设置了多对多 , 则将用户添加进去
                    if(array_key_exists($v['assess_admin_id'],$userList) == false){
                        $userList[] = $v['assess_admin_id'][$field] = 0;
                    }
                    //计算每一个多对多src 设置了几个人
                    $commonSrc[$v['src']]['user_count'] += 1;
                    $commonSrc[$v['src']][$field] = $v[$field];
                }
            }
            //循环将多对多数据 对应到用户
            foreach ($userList as $k => $v) {
                foreach ($commonSrc as $key => $value) {
                    if (!empty($v['common_src']) && in_array($key,$v['common_src'])) {
                        $userList[$k][$field] += round($value[$field]/$value['user_count'],2);
                    }
                }
            }
            return $userList;
        }
    }
}
