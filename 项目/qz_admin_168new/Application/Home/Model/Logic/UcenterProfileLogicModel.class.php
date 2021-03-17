<?php


namespace Home\Model\Logic;


use Home\Model\Db\ScoreLevelModel;
use Home\Model\Db\ScoreLogGrowthModel;
use Home\Model\Db\ScoreLogTaskModel;
use Home\Model\Db\ScoreTaskModel;
use Home\Model\Db\UcenterProfileModel;
use Think\Db;

class UcenterProfileLogicModel
{
    public function get_member($param)
    {
        $ucenterprofilemodel = new UcenterProfileModel();

        $map = $this->setmap($param);
        $map['p.growth'] = array('gt', 0);

        //排序
        $sort = 'sum_score desc,first_score_time desc,uuid desc';
        if ($param['rhtime'] == 2) {                //入会时间
            $sort = 'sum_score desc,first_score_time asc,uuid desc';
        } elseif ($param['rhtime'] == 1) {
            $sort = 'sum_score desc,first_score_time desc,uuid desc';
        } elseif ($param['levelsort'] == 2) {       //等级
            $sort = 'sum_score desc,level asc,uuid desc';
        } elseif ($param['levelsort'] == 1) {
            $sort = 'sum_score desc,level desc,uuid desc';
        } elseif ($param['growthsort'] == 2) {      //成长值
            $sort = 'sum_score desc,growth asc,uuid desc';
        } elseif ($param['growthsort'] == 1) {
            $sort = 'sum_score desc,growth desc,uuid desc';
        } elseif ($param['totalmaxsort'] == 2) {    //总积分
            $sort = 'sum_score desc,all_score asc,uuid desc';
        } elseif ($param['totalmaxsort'] == 1) {
            $sort = 'sum_score desc,all_score desc,uuid desc';
        } elseif ($param['totalnowsort'] == 2) {    //剩余积分
            $sort = 'sum_score desc,score asc,uuid desc';
        } elseif ($param['totalnowsort'] == 1) {
            $sort = 'sum_score desc,score desc,uuid desc';
        } elseif ($param['changedsort'] == 2) {     //兑换次数
            $sort = 'sum_score desc,meo_count asc,uuid desc';
        } elseif ($param['changedsort'] == 1) {
            $sort = 'sum_score desc,meo_count desc,uuid desc';
        } elseif ($param['recomendsort'] == 2) {    //推荐人数
            $sort = 'sum_score desc,sr_count asc,uuid desc';
        } elseif ($param['recomendsort'] == 1) {
            $sort = 'sum_score desc,sr_count desc,uuid desc';
        }


        $count = $ucenterprofilemodel->getAllUserByMapCount($map);
        $pageCount = 20;
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, $pageCount);
            $show = $p->show();

            $list = $ucenterprofilemodel->get_member($map, $sort, intval($p->nowPage - 1) * $pageCount, $p->listRows);
            foreach ($list as $key => $val) {
                $list[$key]['avatar'] = $val['avatar'] ? changeImgUrl($val['avatar'], 2) : 'https://zxsqn.qizuang.com/ucenter/20191109/FgaaGcPAjBrdNm2Hp1uLimD1g7HB';
            }
        }

        $return = [];
        $return['page'] = $show ? $show : "";
        $return["list"] = $list ? $list : [];
        return $return ? $return : [];
    }


    //设置map
    private function setmap($param)
    {
        $map = [];
        //入会时间
        if ($param['join_start'] && $param["join_end"]) {       //CCONTROLLER做处理，如果没有值就都没有值，如果一个有值就都有值，
            $map["p.first_score_time"] = array("between", array($param['join_start'], $param["join_end"]));
        }

        //昵称
        if ($param["nickname"] || $param["nickname"] != "") {
            $map["p.nickname"] = array("like", "%" . $param["nickname"] . "%");
        }

        //等级
        if ($param['level1'] && $param['level2']) {
            $map['p.level'] = array("between", array($param['level1'], $param["level2"]));
        } elseif ($param['level1'] && !$param['level2']) {
            $map['p.level'] = array('egt', $param['level1']);
        } elseif (!$param['level1'] && $param['level2']) {
            $map['p.level'] = array('elt', $param['level2']);
        }

        //积分
        if ($param['score_min'] && $param['score_max']) {
            $map['p.score'] = array("between", array($param['score_min'], $param["score_max"]));
        } elseif ($param['score_min'] && !$param['score_max']) {
            $map['p.score'] = array('egt', $param['score_min']);
        } elseif (!$param['score_min'] && $param['score_max']) {
            $map['p.score'] = array('elt', $param['score_max']);
        }


        return $map;

    }

    //获取所有会员统计数据
    public function getAllMember($param)
    {
        $ucenterprofilemodel = new UcenterProfileModel();

        $map = $this->setmap($param);
        $map['p.growth'] = array('gt', 0);

        //排序
        $sort = 'sum_score desc,first_score_time desc';
        if ($param['rhtime'] == 2) {                //入会时间
            $sort = 'sum_score desc,first_score_time asc';
        } elseif ($param['rhtime'] == 1) {
            $sort = 'sum_score desc,first_score_time desc';
        } elseif ($param['levelsort'] == 2) {       //等级
            $sort = 'sum_score desc,level asc';
        } elseif ($param['levelsort'] == 1) {
            $sort = 'sum_score desc,level desc';
        } elseif ($param['growthsort'] == 2) {      //成长值
            $sort = 'sum_score desc,growth asc';
        } elseif ($param['growthsort'] == 1) {
            $sort = 'sum_score desc,growth desc';
        } elseif ($param['totalmaxsort'] == 2) {    //总积分
            $sort = 'sum_score desc,all_score asc';
        } elseif ($param['totalmaxsort'] == 1) {
            $sort = 'sum_score desc,all_score desc';
        } elseif ($param['totalnowsort'] == 2) {    //剩余积分
            $sort = 'sum_score desc,score asc';
        } elseif ($param['totalnowsort'] == 1) {
            $sort = 'sum_score desc,score desc';
        } elseif ($param['changedsort'] == 2) {     //兑换次数
            $sort = 'sum_score desc,meo_count asc';
        } elseif ($param['changedsort'] == 1) {
            $sort = 'sum_score desc,meo_count desc';
        } elseif ($param['recomendsort'] == 2) {    //推荐人数
            $sort = 'sum_score desc,sr_count asc';
        } elseif ($param['recomendsort'] == 1) {
            $sort = 'sum_score desc,sr_count desc';
        }

        $list = $ucenterprofilemodel->getAllMember($map, $sort);

        return $list ? $list : [];
    }



    // 增加用户积分/总积分/成长值/会员等级

    /**
     * @param $task_id
     * @param $uuid
     * @param string $to_id
     * @param string $to_type   类型 1.案例 2.品牌榜单 3.分类榜单 4.热门话题 5.对比广场 6.避坑指南 7:攻略管理 8.视频 9.美图 10.心得 11.装修公司 12.装修公司评论
     * @return bool
     */
    public function addTaskScore($task_id, $uuid, $to_id = '', $to_type = '')
    {
        $scoretaskmodel = new ScoreTaskModel();
        $scorelogtaskModel = new ScoreLogTaskModel();
        $ucenterprofileModel = new UcenterProfileModel();
        $map = [];
        $map["id"] = array("eq",$task_id);
        $list = $scoretaskmodel->getTaskListByMap($map);
        if (count($list) > 0) {
            $taskinfo = $list[0];
        } else {
            return false;       //taskid有误，查询失败
        }

        //查询次数
        $map = [];
        if ($uuid) {
            $map["uuid"] = array("eq",$uuid);
        }
        $map["task_id"] = array("eq",$task_id);
        if (intval($taskinfo['task_type']) != 1) {
            $timestart = strtotime(date('Y-m-d', time()));
            $timeend = strtotime(date('Y-m-d', time()) . ' 23:59:59');
            $map["created_at"] = array("between",array($timestart,$timeend));
        }
        $list = $scorelogtaskModel->taskdonetimes($map);

        if (count($list) > 0) {
            $taskdoneinfo = $list[0];

            //如果查询有内容了表示已完成过，返回false
            if (!empty($to_id) && !empty($to_type)) {
                $where = [];
                if ($uuid) {
                    $where['uuid'] = array('eq',$uuid);
                }
                $where['task_id'] = array('eq',$task_id);
                if (intval($taskinfo['task_type']) != 1) {
                    $timestart = strtotime(date('Y-m-d', time()));
                    $timeend = strtotime(date('Y-m-d', time()) . ' 23:59:59');
                    $where['task_id'] = array('between',array($timestart,$timeend));
                }

                $where['to_id'] = array('eq',$to_id);
                $where['to_type'] = array('eq',$to_type);
                $had = $scorelogtaskModel->checkdonetask($where);
                if ($had) {
                    return false;
                }
            }

            if ($taskdoneinfo['counts'] >= $taskinfo['need_did']) {
                return false;       //超限不增加积分
            }
        }


        //添加操作
        M()->startTrans(); // 开启事务
        try{
            //增加积分
            $result = $ucenterprofileModel->adduserScore($uuid, $taskinfo['score']);
            if ($result !== false) {
                $result2 = $ucenterprofileModel->addUserAllScore($uuid, $taskinfo['score']);//添加总积分
            }

            //增加成长值
            $this->addUserGrowth($uuid, 1, $taskinfo['score'], $task_id);


            //添加日志
            if ($result !== false && $result2 !== false) {
                $scorereceiverecordlogic = new ScoreReceiveRecordLogicModel();
                $scorereceiverecordlogic->addScoreReceiveRecord($uuid, 3, $taskinfo['score'], $task_id);
                //添加任务完成日志
                $scorelogtaskModel->addTaskLog($task_id, $uuid, $to_id, $to_type);

            }else{
                M()->rollback(); // 事务回滚
                return false;
            }


            M()->commit(); // 事务提交
        } catch (\Exception $e) {
            // 回滚事务
            M()->rollback(); // 事务回滚
            return false;
        }


    }

    /**
     * 增加用户成长值/更新会员等级
     * @param $uuid   用户id
     * @param $type   类型,1:积分任务同步加成长值;
     * @param $score    积分？  / 增加的成长值
     * @param $task_id  积分任务id
     */
    public function addUserGrowth($uuid, $type, $score = 0, $task_id = 0)
    {
        $ucenterprofilemodel = new UcenterProfileModel();
        $nowtime = time();

        if ($uuid) {
            $growthgactor = $this->getGrowthFactor($uuid);

            $score = $score * $growthgactor;
            $result = $ucenterprofilemodel->addUserGrowth($uuid, $score);

            if ($result !== false) {
                //添加日志
                $logdata = [];
                if ($type == 1) {
                    $logdata['task_id'] = $task_id;
                }
                $logdata['type'] = $type;
                $logdata['uuid'] = $uuid;
                $logdata['growth'] = $score;
                $logdata['created_at'] = $nowtime;
                $logdata['updated_at'] = $nowtime;
                $scoreloggrowthmodel = new ScoreLogGrowthModel();
                $scoreloggrowthmodel->addLogGrowth($logdata);

                //更新会员等级
                $userinfo = $ucenterprofilemodel->getUserScoreInfo($uuid);
                $growth = $userinfo['growth'];
                $scorelevelmodel = new ScoreLevelModel();
                $levelinfo = $scorelevelmodel->getlevelByGrowth($growth);

                if (intval($userinfo['level']) != intval($levelinfo['level'])) {
                    $map = [];
                    $map['uuid'] = array('eq',$uuid);
                    $savedata = [];
                    $savedata['level'] = $levelinfo['level'];
                    $ucenterprofilemodel->updateUserProfile($map, $savedata);
                }
                return true;

            }else{
                return false;
            }

        }else{
            return false;
        }

    }






    //根据uuid获取成长系数
    public function getGrowthFactor($uuid)
    {
        //todo 积分体系第一版没有扣除积分逻辑， 所以成长系数返回都是1，后续有逻辑了在此添加逻辑
        return 1;
    }


}