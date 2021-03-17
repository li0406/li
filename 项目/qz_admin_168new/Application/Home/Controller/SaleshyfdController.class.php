<?php

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

/**
*  销售系统 会员分单统计
*/
class SaleshyfdController extends HomeBaseController
{
    /*
    *   会员分单统计首页
    */
    public function index() {

        $db = D('SaleStats');

        //取城市列表
        $citys = $db->getHyfzCitys();
        de($citys);

        //城市系数
        $xsMap['type'] = array('EQ','4');
        $xsCategory = array_column($db->getCategory($xsMap),'name','id');

/*
        //会员指标
        $hyzbMap['typeid'] = array('EQ','2');
        $hyzbMap['start'] = array('EQ',date('Y-m').'-01');

        $hyzb = $db->getSettingValue($hyzbMap);
        $hyzb = array_column($db->getSettingValue($hyzbMap),'point','cid');
*/

        //统计会员个数
        $tempMemberCount = $db->getMemberCount();
        foreach ($tempMemberCount as $k => $v) {
            $memberCount[$v['cs']] = $v;
        }
        dump($memberCount);die;

        //获取月份
        $time = explode('-','2016-07-28');
        $theMonthDays = cal_days_in_month(CAL_GREGORIAN,$time['1'],$time['0']);
        $monthStart = $time['0'].'-'.$time['1'].'-01';
        $monthEnd = $time['0'].'-'.$time['1'].'-'.$theMonthDays;

        //统计此月发单量
        $monthOrders = array_column($db->getCityOrders(strtotime($monthStart),strtotime($monthEnd)),'count','cs');

        //统计此日发单量
        $start = strtotime($time['0'].'-'.$time['1'].'-'.$time['2']);
        $end = strtotime($time['0'].'-'.$time['1'].'-'.$time['2']) + 86399;
        $todayOrders = array_column($db->getCityOrders($start,$end),'count','cs');


        //查询出本月不足一整月会员
        $map['start'][] = array('GT',$monthStart);
        $map['start'][] = array('LT',$monthEnd);
        $vipStartGT = $db->getMemberTime($map);
        unset($map);
        $map['end'][] = array('LT',$monthEnd);
        $map['end'][] = array('GT',$monthStart);
        $vipStartLT = $db->getMemberTime($map);
        $_lackMonth = array_merge($vipStartGT,$vipStartLT);
        foreach ($_lackMonth as $key => $v){
            $tempLackMonth[$v['uid']] = $v;
        }

        foreach ($tempLackMonth as $key => $v){
            //本月开始不足一月
            if($v['start_time'] > $monthStart){
                $v['sdays'] = floor((strtotime($monthEnd) - strtotime($v['start_time'])) / 86400) - 1;
            }
            //本月结束不足一月 本月最后一天 - 结束时间
            if($v['end_time'] < $monthEnd){
                $v['edays'] = floor((strtotime($monthEnd) - strtotime($v['end_time'])) / 86400) - 1;
            }

            $v['days'] = empty($v['edays']) ? $v['sdays'] + 1 : ($v['sdays'] - $v['edays']) + 1;

            /*
            中途上会员或中途下会员（或中途暂停）的按天数计算会员，
            计算公式：会员数=该会员在本月的会员天数/月数总天数，保留两位小数。
            如一个会员在9月15号暂停会员，那么这个会员只能算半个会员。
            会员数=该会员在本月的会员天数/月数总天数=15/30=0.5
            */
            $v['member_days'] = $v['days'] / $theMonthDays;

            //如果没有这条城市数据
            if(empty($lackMember[$v['cs']])){
                $lackMember[$v['cs']]['val'] = $v['member_days'];
                $lackMember[$v['cs']]['count'] = 1;
            }else{
                $lackMember[$v['cs']]['val'] = $lackMember[$v['cs']]['val'] + $v['member_days'];
                $lackMember[$v['cs']]['count'] = $lackMember[$v['cs']]['count'] + 1;
            }
            $lackMonth[$v['cs']][] = $v;
        }
        //dump($lackMonth);die;
        //dump($lackMember);die;

        foreach ($citys as $k => $v) {
            //取城市系数
            if(!empty($xsCategory[$v['xspid']])){
                $citys[$k]['xishu'] = $xsCategory[$v['xspid']];
            }
            $citys[$k]['vipnum'] = empty($memberCount[$v['cid']]['vipnum']) ? '0' : $memberCount[$v['cid']]['vipnum'];
            $citys[$k]['monthOrders'] = empty($monthOrders[$v['cid']]) ? '0' : $monthOrders[$v['cid']];
            $citys[$k]['todayOrders'] = empty($todayOrders[$v['cid']]) ? '0' : $todayOrders[$v['cid']];

            //会员数量处理
            if(!empty($lackMember[$v['cid']])){
                $citys[$k]['xishu'] = $xsCategory[$v['xspid']];
                $citys[$k]['newvipnum'] = ($citys[$k]['vipnum'] - $lackMember[$v['cid']]['count']) + $lackMember[$v['cid']]['val'];
            }
        }

        dump($citys);die;


        $this->assign('list',$citys);
        $this->display();
    }

}
