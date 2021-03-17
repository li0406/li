<?php

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

/**
*  会员统计
*/
class SalesmemberstatisticsController extends HomeBaseController
{
    /**
     * [memberDevelop 会员发展统计概览]
     * @return [type] [description]
     */
    public function memberDevelop() {
        $type = I('get.type');
        switch ($type) {
            case '1':
                $map['type'] = 1;
                break;
            case '2':
                $map['type'] = 2;
                break;
            default:
                break;
        }
        $year = I('get.year');
        if (empty($year)) {
            $start = date('Y',strtotime('-1 year'));
            $end = date('Y',time());
        }else{
            $start =  $year - 1;
            $end = $year;
        }

        //定义12月份数组
        $months = ['01','02','03','04','05','06','07','08','09','10','11','12'];

        //统计上一年的(计算同比)：实际会员,普通会员,多倍会员,90天以下会员占比,90-180,180天以上占比
        $lastyear = $this->getMonthListByYear($start);
        $previous = [];
        foreach ($lastyear as $key => $value) {
            $previous[$value] = $this->getMemberStatisticsByMonth($value);
        }

        //统计今年的：实际会员,普通会员,多倍会员,90天以下会员占比,90-180,180天以上占比
        $thisyear = $this->getMonthListByYear($end);
        $currents = [];
        foreach ($thisyear as $key => $value) {
            $currents[$value] = $this->getMemberStatisticsByMonth($value);
        }

        //上会员数(old_state, new_state),下会员数(old_state, new_state)，基于操作时间的，可一条sql语句查出来
        $results  = M('log_vip')->alias('v')
                                ->field([
                                            'FROM_UNIXTIME(optime, "%Y-%m") AS time',
                                            'SUM(
                                               IF (((v.old_state = 0 and v.new_state = 1) or (v.old_state = -2 and v.new_state = 1)), 1, 0)
                                            ) AS up_vip',
                                            'SUM(
                                               IF (((v.old_state = 2 and v.new_state = 1) or (v.old_state = 2 and v.new_state = 1)), 1, 0)
                                            ) AS down_vip'
                                        ])
                                ->where(['optime' => ['between',[strtotime($start.'-01-01'), strtotime(($end + 1).'-01-01')]]])
                                ->group('FROM_UNIXTIME(optime, "%Y-%m")')
                                ->select();
        $updowns = [];
        foreach ($results as $key => $value) {
            $updowns[$value['time']] = $value;
        }

        //每月会员到期数，需要事先对comid聚合，取最新的一条记录 基于end字段，可一条sql语句查出来
        //where条件加上 optime 防止 到期后有人为操作或者续费情况
        $build = M('log_vip')->alias('z')
                             ->field([
                                        'LEFT(end, "7") AS time',
                                        'z.comid',
                                        'z.old_state',
                                        'z.new_state',
                                        'z.`start`',
                                        'z.end',
                                        'z.optime',
                                        'z.viptype',
                                     ])
                             ->where([
                                        'end' => ['between',[($start . '-01-01'), (($end + 1) . '-01-01')]],
                                        '_logic' => 'or',
                                        'optime' =>['between',[strtotime($start . '-01-01'), strtotime(($end + 1) . '-01-01')]]
                                    ])
                             ->order('optime DESC')
                             ->buildSql();
        $build = M()->table($build)->alias('y')->where('y.optime >= UNIX_TIMESTAMP(y.end)')->group('comid')->buildSql();
        $results = M()->table($build)->alias('v')
                                    ->field([
                                              'time',
                                              'SUM(
                                                 IF ((v.old_state = 2 and v.new_state = -1), 1, 0)
                                              ) AS expire'
                                            ])
                                    ->group('time')
                                    ->select();
        $expire = [];
        foreach ($results as $key => $value) {
            $expire[$value['time']] = $value;
        }

        //续费会员，vip_renew
        $build = M('vip_renew')->alias('z')
                               ->field([
                                          'z.id',
                                          'z.comid',
                                          'z.time',
                                          'z.start',
                                          'z.end',
                                          'FROM_UNIXTIME(time, "%Y-%m-%d") AS opdate',
                                          'FROM_UNIXTIME(time, "%Y-%m") AS optime',
                                       ])
                               ->where([
                                          'time' => ['between',[strtotime(($start . '-01-01')), strtotime((($end + 1) . '-01-01'))]]
                                      ])
                               ->order('id DESC')
                               ->buildSql();
        $build = M()->table($build)->alias('y')->group('comid,optime')->buildSql();
        $build = M()->table($build)
                    ->alias('r')
                    ->field([
                            //判断 opdate 记录状态
                              'r.id',
                              'r.comid',
                              'r.time',
                              'r.start',
                              'r.end',
                              'r.opdate',
                              'r.optime',
                              'v.old_state',
                              'v.new_state'
                           ])
                    ->join('qz_log_vip AS v ON v.comid = r.comid AND 0 <= DATEDIFF(r.opdate,v.end) AND DATEDIFF(r.opdate,v.end) <= 46 AND v.optime <= r.time')
                    ->order('r.id DESC')
                    ->buildSql();
        $results = M()->table($build)
                     ->alias('x')
                     ->field('optime,count(x.id) AS renew')
                     ->where([
                                'x.old_state' => '2',
                                'x.new_state' => '-1'
                             ])
                     ->group('optime')
                     ->select();
        $renews = [];
        foreach ($results as $key => $value) {
            $renews[$value['optime']] = $value;
        }

        $result = [];
        foreach ($months as $key => $value) {
            $k = $end . '-' . $value;
            $result[$k] = $currents[$k];
            $result[$k]['up_vip'] = $updowns[$k]['up_vip'];
            $result[$k]['down_vip'] = $updowns[$k]['down_vip'];
            $result[$k]['expire'] = $expire[$k]['expire'];
            $result[$k]['renew'] = $renews[$k]['renew'];
            $result[$k]['alltotal'] = $result[$k]['ninety'] + $result[$k]['one_eighty'] + $result[$k]['over_one_eighty'];
        }
        $main['info'] = $result;

        $sheets = [];
        foreach ($months as $key => $month) {
            /*S-会员发展相关*/
            //实际会员
            $sheets['develop']['actual'][] = intval($result[$end . '-' .$month]['actual']);
            //同比
            $sheets['develop']['tongbi'][] = round($result[$end . '-' .$month]['actual'] / $previous[$start.'-'.$month]['actual'],2);
            //环比，判断是否是1月，是的化取上一年的12月
            if($month == '01'){
                $sheets['develop']['huangbi'][] = round($result[$end . '-' .$month]['actual'] / $previous[$start . '-12']['actual'],2);
            }else{
                $sheets['develop']['huangbi'][] = round($result[$end . '-' .$month]['actual'] / $result[date('Y-m',(strtotime($end . '-' .$month) - 3600))]['actual'],2);
            }
            /*E-会员发展相关*/

            /*S-会员续费相关*/
            //到期会员
            $sheets['renews']['expire'][] = intval($result[$end . '-' .$month]['expire']);
            //续费会员
            $sheets['renews']['renew'][] = intval($result[$end . '-' .$month]['renew']);
            //同比
            $sheets['renews']['tongbi'][] = round($result[$end . '-' .$month]['renew'] / $expire[$start.'-'.$month]['expire'],2);

            //环比，判断是否是1月，是的化取上一年的12月
            if($month == '01'){
                $sheets['renews']['huangbi'][] = round($result[$end . '-' .$month]['renew'] / $renews[$start . '-12']['renew'],2);
            }else{
                $sheets['renews']['huangbi'][] = round($result[$end . '-' .$month]['renew'] / $renews[date('Y-m',(strtotime($end . '-' .$month) - 3600))]['renew'],2);
            }
            /*E-会员续费相关*/
        }

        foreach ($sheets['develop'] as $key => $value) {
            $sheets['develop'][$key] = json_encode($value);
        }
        foreach ($sheets['renews'] as $key => $value) {
            $sheets['renews'][$key] = json_encode($value);
        }

        $main['sheets'] = $sheets;
        $main['months'] = json_encode($months);
        $this->assign('main', $main);
        $this->display();
    }


    /**
     * [getMemberStatisticsByMonth 统计每个月的：实际会员,普通会员,多倍会员,90天以下会员占比,90-180,180天以上占比]
     * @param  [type] $month [description]
     * @return [type]        [description]
     */
    public function getMemberStatisticsByMonth($month){
        $build = M('log_vip')->where(['optime' => ['LT', strtotime("$month +1 month")]])->order('optime DESC')->buildSql();
        $build = M()->table($build)->alias('z')->group('comid')->buildSql();
        $build = M()->table($build)
                    ->alias('v')
                    ->field([
                                'v.comid',
                                'v.old_state',
                                'v.new_state',
                                'v.`start`',
                                'v.end',
                                'FROM_UNIXTIME(v.optime) optime',
                                'v.viptype',
                                'IF ((DATE_FORMAT("'.$month.'-01", "%Y-%m-01") = DATE_FORMAT(v.start,"%Y-%m-01")) , 1, 0) AS currently',
                            ])
                    ->join('qz_user u on u.id = v.comid')
                    ->join('qz_user_company AS c on c.userid = u.id and c.fake = 0')
                    ->where([
                            'v.start' => ['ELT', date('Y-m-t',strtotime($month))],
                            'v.end' => ['EGT', date('Y-m-t',strtotime($month))],
                            'new_state' => ['EQ', 2]
                            ])
                    ->buildSql();

        $result = M()->table($build)
                     ->alias('a')
                     ->field([
                                'SUM(a.viptype) AS actual', //实际会员，后台报备的会员总数+多倍会员超出数
                                'SUM(IF((a.viptype = 1), 1, 0)) AS ordinary', //普通会员数
                                'SUM(IF((a.viptype > 1), (a.viptype - 1), 0)) AS multiple', //多倍会员数
                                'SUM(
                                       IF ((a.currently = 1) AND (DATEDIFF(a.end,a.start) <= 90) , 1, 0)
                                    ) AS ninety', //90天以下会员数
                                'SUM(
                                       IF ((a.currently = 1) AND (90 < DATEDIFF(a.end,a.start) <= 180) , 1, 0)
                                    ) AS one_eighty', //90-180天会员数
                                'SUM(
                                       IF ((a.currently = 1) AND (180 < DATEDIFF(a.end,a.start)) , 1, 0)
                                    ) AS over_one_eighty' //180天以上会员数
                             ])
                     ->find();
        return $result;
    }









































    /**
     * [getMonthListByYear 获取某年的所有月份]
     * @param  string $year  [年份]
     * @param  string $space [分割线]
     * @return [type]        [description]
     */
    private function getMonthListByYear($year = '2015', $space = '-'){
        if(!empty($year)){
            $array = ['01','02','03','04','05','06','07','08','09','10','11','12'];
            $result = [];
            foreach ($array as $key => $value) {
                $result[] = $year . $space . $value;
            }
            return $result;
        }
        return false;
    }
}