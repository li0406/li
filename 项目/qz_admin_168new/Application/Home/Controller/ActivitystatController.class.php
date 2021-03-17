<?php
//活动统计表

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class ActivitystatController extends HomeBaseController
{
    /**
     * 活动订单数据统计
     */
    public function index()
    {
        //查询条件获取
        $time_start  = I('get.time_start');
        $time_end    = I('get.time_end');
        $activity_id = intval(I('get.activity_id'));
        $src = I('get.src');
        $activity_location = I('get.activity_location', 0);

        $vars['activity'] = D('Activity')->getActivityList();

        //获取活动来源ID
        $source_id_array = array();
        //获取渠道来源
        $srcs = '';
        foreach ($vars['activity'] as $key => $value) {
            //如果活动ID为空，默认第一个活动
            if (empty($activity_id)) {
                $activity_id = $value['id'];
            }
            if ($value['id'] == $activity_id) {
                //如果有筛选 则判断数据中是否存在
                $source_id_array = array_filter(explode(',', $value['source_id']));
                if (empty($time_start)) {
                    $time_start = date('Y-m-d', strtotime($value['start']));
                }
                if (empty($time_end)) {
                    $time_end = date('Y-m-d', strtotime($value['end']));
                    //初始化页面 1.活动开始时间大于活动时间 则默认显示活动开始结束时间 2.否则 再判断结束时间是否大于现在的时间
                    if (strtotime($value['start']) < time()) {
                        if(strtotime($value['end'])>time()){
                            $time_end = date('Y-m-d', time());
                        }
                    }

                }
                $srcs = $value['src'];
            }
        }

        //获取渠道来源对应的数据
        if($srcs){
            $vars['src_data'] = D("order_source")->getSourceBySrc($srcs,'name,src');
        }

        if ($activity_location == '3') {
            $this->statApp($activity_id, $time_start, $time_end, $src, $vars);
            return false;
        }

        if (!empty($source_id_array)) {
            //记录时间
            $time = array();
            $start = strtotime($time_start);
            $end = strtotime($time_end);
            while ($start <= $end) {
                $time[date('Y-m-d', $start)] = date('Y-m-d', $start);
                $start                       = strtotime(date('Y-m-d', $start) . ' +1 day');
            }

            //获取汇总表数据统计
            //暂时注释掉PV和UV
            //$result = D('MarketSummary')->getListBySourceId($time_start, $time_end, $source_id_array);
            $info = array();
            foreach ($result as $key => $value) {
                $info[$value['time']]['pv']       = $info[$value['time']]['pv'] + $value['pv'];
                $info[$value['time']]['uv']       = $info[$value['time']]['uv'] + $value['uv'];
            }

            if ($source_id_array != '') {
                //获取发单量
                $result = D('OrderCsosNew')->getOrderListBySourceId(strtotime($time_start), strtotime($time_end . ' +1 day'), $source_id_array,$src);
                foreach ($result as $key => $value) {
                    //发单量
                    $info[date('Y-m-d', $value['time_real'])]['sum']++;
                }

                //获取有效订单和实际有效订单
                $result = D('OrderCsosNew')->getYouXiaoListBySourceId(strtotime($time_start), strtotime($time_end . ' +1 day'), $source_id_array,$src);

                foreach ($result as $key => $value) {
                    //有效单和真实有效单
                    if ('4' == $value['order_on']) {
                        $info[date('Y-m-d', $value['time_real'])]['youxiao']++;
                        $info[date('Y-m-d', $value['lasttime'])]['real_youxiao']++;
                    }
                    //分单和实际分单
                    if ('4' == $value['order_on'] && '1' == $value['type_fw']) {
                        $info[date('Y-m-d', $value['time_real'])]['fen']++;
                        $info[date('Y-m-d', $value['lasttime'])]['real_fen']++;
                    }
                }
            }

            //获取汇总
            foreach ($time as $key => $value) {
                $alls['pv']           = $alls['pv'] + $info[$value]['pv'];
                $alls['uv']           = $alls['uv'] + $info[$value]['uv'];
                $alls['sum']          = $alls['sum'] + $info[$value]['sum'];
                $alls['fen']          = $alls['fen'] + $info[$value]['fen'];
                $alls['real_fen']     = $alls['real_fen'] + $info[$value]['real_fen'];
                $alls['youxiao']      = $alls['youxiao'] + $info[$value]['youxiao'];
                $alls['real_youxiao'] = $alls['real_youxiao'] + $info[$value]['real_youxiao'];
            }
        }


        //模板赋值
        $vars['info'] = $info;
        $vars['alls'] = $alls;
        $vars['time'] = $time;
        $vars['condition'] = array(
            'time_start'  => $time_start,
            'time_end'    => $time_end,
            'activity_id' => $activity_id,
            'activity_location' => $activity_location
        );

        $this->assign('vars', $vars);
        $this->display();
    }

    // APP活动统计
    public function statApp($activity_id, $time_start, $time_end, $src, $vars){

        $time = array();
        $start = strtotime($time_start);
        $end = strtotime($time_end);
        while ($start <= $end) {
            $time[date('Y-m-d', $start)] = date('Y-m-d', $start);
            $start                       = strtotime(date('Y-m-d', $start) . ' +1 day');
        }

        // 获取所有奖项
        $prizes = D("activity")->getPrizeAll($activity_id, ['id', 'prize_name']);
        $prizes = array_column($prizes, null, 'id');
        foreach ($prizes as $key => $value) {
            $prizes[$key]['stat'] = []; // 初始化统计结果放置键值
        }

        // 海报分享数量
        $shares = D("userShareRecords")->getDayCount($activity_id, strtotime($time_start), $end + 86399);
        $shares = array_column($shares, null, 'date');

        // 获取抽奖记录
        $logList = D("activity")->getActivityUserinfoList($activity_id, strtotime($time_start), $end + 86399);

        $all = [];
        $list = [];
        foreach ($logList as $key => $log) {
            $date = date("Y-m-d", $log['time']);

            if (!isset($list[$date])) {
                $list[$date] = array(
                    "count" => 0,
                    "count_draw" => 0,
                    "user_count" => 0,
                    "users" => [],
                    "prize_rate" => 0
                );
            }

            $all["count"] += 1;
            $list[$date]["count"] += 1; // 抽奖次数
            if ($log['win_status'] == 1) { // 中奖次数
                $list[$date]["count_draw"] += 1;
                $all["count_draw"] += 1;
            }

            // 谢谢惠顾win_status=0也要加1
            if ($log['win_status'] != 2) {
                // 对应奖品中奖次数+1
                $prizes[$log['prize_id']]["stat"][$date] += 1;
            }

            if (!in_array($log['user_id'], $all['users'])) {
                $all['users'][] = $log['user_id'];
                $all["user_count"] += 1;
            }

            if (!in_array($log['user_id'], $list[$date]['users'])) {
                $list[$date]['users'][] = $log['user_id'];
                $list[$date]["user_count"] += 1;
            }

            $list[$date]["prize_rate"] = sprintf("%.2f", $list[$date]["count_draw"] / $list[$date]["count"]);
            $all["prize_rate"] = sprintf("%.2f", $all["count_draw"] / $all["count"]);
        }

        $all['share_count'] = array_sum(array_column($shares, 'share_count'));

        $stats = [
            'time' => $time,
            'prizes' => $prizes,
            'shares' => $shares,
            'list' => $list,
            'all' => $all,
        ];

        $vars['condition'] = array(
            'time_start'  => $time_start,
            'time_end'    => $time_end,
            'activity_id' => $activity_id,
            'activity_location' => 3
        );

        $this->assign('vars', $vars);
        $this->assign('stats', $stats);
        $this->display("statApp");
    }

    public function separateStat()
    {
        if (I("get.kf") !== "") {
            $id = I("get.kf");
        }

        //获取活动客服名单列表
        $kfList = D("Adminuser")->getKfList();
        //获取客服组长列表
        $users = $this->getKfGroupInfo();
        $list = $this->getSeparateStat($id,I("get.groups"),I("get.manager"),I("get.begin"),I("get.end"));
        $this->assign("list",$list[0]);
        $this->assign("grouplist",$list[1]);
        $this->assign("managerlist",$list[2]);
        $this->assign("total",$list[3]);
        $this->assign("sum_ren",$list[4]);
        $this->assign("sum_zu",$list[5]);
        $this->assign("groups",$users["groups"]);
        $this->assign("manager",$users["manager"]);
        $this->assign("kfList",$kfList);
        $this->display();
    }

    private function getSeparateStat($id,$group,$manager,$begin,$end)
    {
        $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        $monthEnd = mktime(23,59,59,date("m"),date("t"),date("Y"));
        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $endDay = $monthEnd = strtotime("+1 day", strtotime($end))-1;
        } else {
            $endDay = strtotime("+1 day", strtotime(date('Y-m-d')));
        }
        $dayCount = ceil(($endDay - $monthStart) / 86400);

        //获取活动ID
        $result = D("Activity")->getActivityIds();
        foreach ($result as $key => $value) {
            $sub = array_filter(explode("," ,$value["source_id"]));
            if (count($ids) == 0) {
                $ids = $sub;
            } else {
               $ids = array_merge($ids,$sub);
            }

        }

        if (count($ids) > 0) {
            // 获取客服活动登录天数
            $kfList = D("Adminuser")->getActivityLoginDayByPool($id,$group,$manager,$monthStart,$monthEnd,implode(",",$ids));

            //获取客服的发单量
            $result = D("OrderPool")->getActivityOrdereffective($id,$group,$manager,$monthStart,$monthEnd,$ids);

            foreach ($result as $key => $value) {
                $list[$value["op_uid"]] = $value;
            }

            $sum_ren = [];
            $sum_zu = [];
            foreach ($kfList as $key => $value) {
                $sum_ren['count'] ++;
                if ($value["day"] > 0) {
                    $kfList[$key]["all"] = $list[$value["id"]]["all"];
                    $kfList[$key]["fen"] = $list[$value["id"]]["fen"];
                    $kfList[$key]["zen"] = $list[$value["id"]]["zen"];
                    $kfList[$key]["fen_zen"] = $list[$value["id"]]["fen"] + $list[$value["id"]]["zen"]/10;
                    $kfList[$key]["fen_rate"] = round($list[$value["id"]]["fen"]/$list[$value["id"]]["all"],6)*100;
                    $kfList[$key]["fen_zen_rate"] = round($kfList[$key]["fen_zen"]/$list[$value["id"]]["all"],6)*100;
                    $kfList[$key]["day_fen"] = round($kfList[$key]["fen"]/$value["day"],4);
                    $kfList[$key]["day_fen_zen"] = round($kfList[$key]["fen_zen"]/$value["day"],4);
                    //统计按人总和
                    $sum_ren['day'] += $value["day"]; //登陆天数
                    $sum_ren['all'] += $kfList[$key]["all"]; //发单量
                    $sum_ren['fen'] += $kfList[$key]["fen"]; //分单量
                    $sum_ren['zen'] += $kfList[$key]["zen"]; //赠单量
                    $sum_ren['fen_zen'] += $kfList[$key]["fen_zen"]; //综合分单量
                    $sum_ren['day_fen'] += $kfList[$key]["day_fen"]; //日均分单量
                    $sum_ren['day_fen_zen'] += $kfList[$key]["day_fen_zen"]; //日均综合分单量

                    //组统计
                    $groups[$value["kfgroup"]]["groupmanager"] = $value["groupmanager"];
                    $groups[$value["kfgroup"]]["kfgroup"] = $value["kfgroup"];
                    $groups[$value["kfgroup"]]["count"] ++;
                    $groups[$value["kfgroup"]]["manager"] = $value["manager"];
                    $groups[$value["kfgroup"]]["day"] += $value["day"];
                    $groups[$value["kfgroup"]]["all"] += $kfList[$key]["all"];
                    $groups[$value["kfgroup"]]["fen"] += $kfList[$key]["fen"];
                    $groups[$value["kfgroup"]]["zen"] += $kfList[$key]["zen"];
                    $groups[$value["kfgroup"]]["fen_zen"] += $kfList[$key]["fen_zen"];
                    $groups[$value["kfgroup"]]["fen_rate"] = round($groups[$value["kfgroup"]]["fen"]/$groups[$value["kfgroup"]]["all"],6)*100;
                    $groups[$value["kfgroup"]]["fen_zen_rate"] = round($groups[$value["kfgroup"]]["fen_zen"]/$groups[$value["kfgroup"]]["all"],6)*100;
                    $groups[$value["kfgroup"]]["day_fen"] = round($groups[$value["kfgroup"]]["fen"]/$dayCount,4);
                    $groups[$value["kfgroup"]]["day_fen_zen"] = round($groups[$value["kfgroup"]]["fen_zen"]/$dayCount,4);
                    //计算组的个数
                    $sum_zu['count'][$value["kfgroup"]] = 1 ;


                    //按师统计
                    $managers[$value["kfmanager"]]["manager"] = $value["manager"];
                    $managers[$value["kfmanager"]]["count"] ++;
                    $managers[$value["kfmanager"]]["day"] += $value["day"];
                    $managers[$value["kfmanager"]]["all"] += $kfList[$key]["all"];
                    $managers[$value["kfmanager"]]["fen"] += $kfList[$key]["fen"];
                    $managers[$value["kfmanager"]]["zen"] += $kfList[$key]["zen"];
                    $managers[$value["kfmanager"]]["fen_zen"] += $kfList[$key]["fen_zen"];
                    $managers[$value["kfmanager"]]["fen_rate"] = round($managers[$value["kfmanager"]]["fen"]/$managers[$value["kfmanager"]]["all"],6)*100;
                    $managers[$value["kfmanager"]]["fen_zen_rate"] = round($managers[$value["kfmanager"]]["fen_zen"]/$managers[$value["kfmanager"]]["all"],6)*100;
                    $managers[$value["kfmanager"]]["day_fen"] = round($managers[$value["kfmanager"]]["fen"]/$dayCount,4);
                    $managers[$value["kfmanager"]]["day_fen_zen"] = round($managers[$value["kfmanager"]]["fen_zen"]/$dayCount,4);
                    $managers[$value["kfmanager"]]["child"][$value["kfgroup"]]= $groups[$value["kfgroup"]];
                }
            }
            //总计，直接从按师统计里获取
            foreach ($managers as $key => $value) {
                    $total['all'] = $total['all'] + $value['all'];
                    $total['fen'] = $total['fen'] + $value['fen'];
                    $total['zen'] = $total['zen'] + $value['zen'];
                    $total['count'] ++;
            }
            $total['fen_zen'] = $total['fen'] + $total['zen'] / 10;
            $total['fen_rate'] = round($total['fen'] / $total['all'], 6)*100;
            $total['fen_zen_rate'] = round($total['fen_zen'] / $total['all'], 6)*100;
            $total["day_fen"] = round($total['fen'] / $dayCount,4);
            $total["day_fen_zen"] = round($total['fen_zen'] / $dayCount,4);
            $total['fen_pre'] = number_format(($total['fen'] / $total['count']) / ($total['all'] / $total['count']), 6)*100; //分单率
            $total['all_pre'] = number_format(($total['fen_zen'] / $total['count']) / ($total['all'] / $total['count']), 6)*100; //综合分单率


            //重新排序
            $edition = array();
            foreach ($kfList as $key => $value) {
                    $edition[] = $value["fen_zen_rate"];
            }
            array_multisort($edition, SORT_DESC,$kfList);

            $edition = array();
            foreach ($groups as $key => $value) {
                $edition[] = $value["fen_zen_rate"];
                //统计按组总和
                $sum_zu['all'] += $value["all"];
                $sum_zu['fen'] += $value["fen"];
                $sum_zu['zen'] += $value["zen"];
                $sum_zu['fen_zen'] += $value["fen_zen"];
                $sum_zu['day_fen'] += $value["day_fen"];
                $sum_zu['day_fen_zen'] += $value["day_fen_zen"];
            }
            //计算按人 平均数
            $sum_ren['fen_pre'] = number_format(($sum_ren['fen'] / $sum_ren['count']) / ($sum_ren['all'] / $sum_ren['count']), 6)*100; //分单率
            $sum_ren['all_pre'] = number_format(($sum_ren['fen_zen'] / $sum_ren['count']) / ($sum_ren['all'] / $sum_ren['count']), 6)*100; //综合分单率
            //计算按组 平均数
            $sum_zu['count'] = count($sum_zu['count']) ;//计算组的个数
            $sum_zu['fen_pre'] = number_format(($sum_zu['fen'] / $sum_zu['count']) / ($sum_zu['all'] / $sum_zu['count']), 6)*100; //分单率
            $sum_zu['all_pre'] = number_format(($sum_zu['fen_zen'] / $sum_zu['count']) / ($sum_zu['all'] / $sum_zu['count']), 6)*100; //综合分单率

            array_multisort($edition, SORT_DESC,$groups);
            return array($kfList,$groups,$managers,$total,$sum_ren,$sum_zu);
        }
    }

    private function getKfGroupInfo()
    {
        $edition = array();
        $result = D("Adminuser")->getKfGroupInfo();
        foreach ($result as $key => $value) {
            $list["groups"][$value["id"]] = $value;
            $edition[] = $value["kfgroup"];
            if (!array_key_exists($value["manager_id"],$list["manager"])) {
                $list["manager"][$value["manager_id"]] = array(
                    "id" => $value["manager_id"],
                    "name" => $value["manager"]
                );
            }
        }
        // array_multisort($edition, SORT_ASC,$list["groups"]);
        return $list;
    }

}