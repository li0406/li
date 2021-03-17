<?php
// +----------------------------------------------------------------------
// | QdstatisticsController   渠道分单率
// +----------------------------------------------------------------------
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class QdstatisticsController extends HomeBaseController
{
    /**
     * 渠道分单率统计
     */
    public function index()
    {
        $year = I('get.year',date('Y'));
        if (!empty($year)) {
            $yearStart = strtotime($year.'-01-01 00:00:00');
            $yearEnd = strtotime($year.'-12-31 23:59:59');
        } else {
            $yearStart = strtotime(date('Y-01-01 00:00:00'));
            $yearEnd = strtotime(date('Y-12-31 23:59:59'));
        }
        if ($year > date('Y')) {
            $this->assign('list',[]);
        } else {
            $freeDept = [18, 6, 11, 13, 19, 16, 7, 12]; //'长视频部','自媒体一部','自媒体二部','微信部','短视频部','微博部','视频一部','视频二部'
            $channelDept = [10, 20, 21, 22, 23];        //'渠道部','渠道部1组','渠道部2组','渠道部3组','渠道部4组'
            $mediaDept = [1, 4];                        //'市场中心','媒介部'
            //按月份和部门获取所有发单和实际分单数据
            $allResult = D('Home/Logic/QdstatisticsLogic')->rateList(array_merge($freeDept,$channelDept,$mediaDept),$yearStart,$yearEnd);
            //分单数据
            $issuance = $allResult['issuance'];
            //分单数据
            $divide = $allResult['divide'];
            //释放内存
            unset($allResult);
            //获取第几个月
            if ($year < date('Y')){
                $month = 12;
            } else {
                $month = intval(date('m'));
            }
            $freeRateList = [];
            $channelRateList = [];
            $mediaRateList = [];
            //分单数据按照月份汇总
            foreach ($issuance as $kk1 => $vv1) {
                //进行数据处理
                for ($x = 1; $x <= $month; $x++) {
                    if ($vv1['count_time'] == $x) {
                        if (in_array($vv1['dept'], $freeDept)) {
                            $freeRateList[$x]['issuance'][] = $vv1['count1'];
                        }
                        if (in_array($vv1['dept'], $channelDept)) {
                            $channelRateList[$x]['issuance'][] = $vv1['count1'];
                        }
                        if (in_array($vv1['dept'], $mediaDept)) {
                            $mediaRateList[$x]['issuance'][] = $vv1['count1'];
                        }
                    }
                }
            }
            //真实发单数据按照月份汇总
            foreach ($divide as $kk2 => $vv2) {
                //进行数据处理
                for ($x = 1; $x <= $month; $x++) {
                    if ($vv2['count_time'] == $x) {
                        if (in_array($vv2['dept'], $freeDept)) {
                            $freeRateList[$x]['divide'][] = $vv2['count2'];
                        }
                        if (in_array($vv2['dept'], $channelDept)) {
                            $channelRateList[$x]['divide'][] = $vv2['count2'];
                        }
                        if (in_array($vv2['dept'], $mediaDept)) {
                            $mediaRateList[$x]['divide'][] = $vv2['count2'];
                        }
                    }
                }
            }

            $result = [];
            //进行最终的数据处理
            for ($x = 1; $x <= $month; $x++) {
                //初始化默认值
                $result[$x] = [
                    'month' =>$year.'-'.$x,
                    
                    'freeIssuance' => 0,
                    'freeDivide' => 0,
                    'freeRate' => 0.00,

                    'channelIssuance' => 0,
                    'channelDivide' => 0,
                    'channelRate' => 0.00,

                    'mediaIssuance' => 0,
                    'mediaDivide' => 0,
                    'mediaRate' => 0.00,
                ];
                //计算月份长度
                switch ($x){
                    case 2:
                        $result[$x]['length'] = ($year % 400 == 0 && $year % 4 == 0) ? 29 : 28;
                        break;
                    case 4:
                    case 6:
                    case 9:
                    case 12:
                        $result[$x]['length'] = 31;
                        break;
                    default:
                        $result[$x]['length'] = 30;
                        break;
                }
                //免费计算
                foreach ($freeRateList as $k1 => $v1) {
                    if ($k1 == $x) {
                        $result[$x]['freeIssuance'] = array_sum($v1['issuance']);
                        $result[$x]['freeDivide'] = array_sum($v1['divide']);
                        $result[$x]['freeRate'] = array_sum($v1['issuance']) == 0 ? 0 : round(array_sum($v1['divide']) / array_sum($v1['issuance']), 4);
                    }
                }
                //渠道计算
                foreach ($channelRateList as $k2 => $v2) {
                    if ($k2 == $x) {
                        $result[$x]['channelIssuance'] = array_sum($v2['issuance']);
                        $result[$x]['channelDivide'] = array_sum($v2['divide']);
                        $result[$x]['channelRate'] = array_sum($v2['issuance']) == 0 ? 0 : round(array_sum($v2['divide']) / array_sum($v2['issuance']), 4);
                    }
                }
                //媒介计算
                foreach ($mediaRateList as $k3 => $v3) {
                    if ($k3 == $x) {
                        $result[$x]['mediaIssuance'] = array_sum($v3['issuance']);
                        $result[$x]['mediaDivide'] = array_sum($v3['divide']);
                        $result[$x]['mediaRate'] = array_sum($v3['issuance']) == 0 ? 0 : round(array_sum($v3['divide']) / array_sum($v3['issuance']), 4);
                    }
                }
            }
            $this->assign('list',$result);
        }

        $this->display();
    }
}