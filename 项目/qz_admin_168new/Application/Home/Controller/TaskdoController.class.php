<?php

/**
 * 新版计划任务
 */

namespace Home\Controller;
use Common\Controller\BaseController;

class TaskdoController extends BaseController {

    //平均订单排行
    public function orderAssignAvg(){
        empty($date) && $date = I('get.date');
        empty($date) && $date = date('Y-m-d');
        //如果查询为今天并且小于18点
        if (date('H') < 18 && $date == date('Y-m-d')) {
            $date = date('Y-m-d',strtotime("-1 day $date"));
        }            
        $start = strtotime($date);
        $month = date('m',$start);
        $year = date('Y',$start);
        $monthStart = date('Y-m-d',mktime(0,0,0,$month,1,$year));
        $monthLast = date("Y-m-d",mktime(0,0,0,date("m",$start),date("t",$start),date("Y",$start)));
        $yday = date('Y-m-d',strtotime('-1 day',mktime(0,0,0,date('m',$start),date('d',$start),date('Y',$start))));

        //取当天的所有分单
        $_result = D('Ordersrank')->getCityOrderByDay($date);
        foreach ($_result as $k => $v) {
            $orders[$v['cs']] += $v['count'];
        }

        //查询当天平均值
        $_result = D('Ordersrank')->getOrdersAssignAvg($date);
        foreach ($_result as $key => $value) {
            $todayAvg[$value['city_id']] = $value;
        }

        //如果本月不是第一天，取上一天平均值
        if($date != $monthStart){
            //查询上一天平均值
            $_result = D('Ordersrank')->getOrdersAssignAvg($yday);        
            foreach ($_result as $key => $value) {
                $ydayAvg[$value['city_id']] = $value;
            }
        }

        //获取 所有后台可见城市
        $_result = D('Ordersrank')->getQuyu();
        foreach ($_result as $k => $v) {
            $quyu[$v['cid']] = $v;
        }

        //取当天的会员数量
        $_result = D('Ordersrank')->getCityVipByDay($date);
        foreach ($_result as $k => $v) {
            $v['companys'] = array_filter(explode(",",$v['companys']));
            //合并区域
            if(!empty($quyu[$v['city_id']])){
                $v['cid'] = $v['city_id'];
                $v['cname'] = $quyu[$v['city_id']]['cname'];
                $v['little'] = $quyu[$v['city_id']]['little'];
                $v['manager'] = $quyu[$v['city_id']]['manager'];
            }
            //合并分单
            if(!empty($orders[$v['city_id']])){
                $v['order_num'] = $orders[$v['city_id']];
            }
            $cityData[$v['city_id']] = $v; 
        }

        if(empty($cityData)){
            die('数据为空 empty data');
        }

        //计算每个城市每天的平均分单
        foreach ($cityData as $key => $value) {
            if($key == '000001' OR empty($value['cid'])){
                continue;
            }
            $avg = $ydayAvg[$key]['last_avg'];  

            //每个月的第一天
            if($date == $monthStart){
                $sum = $orders[$key];
            }else{   
                if(empty($ydayAvg) && $key >= '2017-01-01'){
                    die('Error:Avg');
                }
                $sum = $ydayAvg[$key]['sum'];
                //前一天会员数不为空，并且前一天会员数不等于今天
                if($ydayAvg[$key]['vip_num'] != 0 && $ydayAvg[$key]['vip_num'] != $value['vip_num']){
                    $sum = 0; 
                    $avg  = $ydayAvg[$key]['avg'];
                }
                $sum += $orders[$key];

                //如果前一天有平均值，但是当天的会员数为0时，表示该城市无  会员了
                if($avg != 0 && $value['vip_num'] == 0){
                    //将下掉的会员保存
                    if($ydayAvg[$key]['vip_num'] > 0){
                        $cityData[$key]['oldCompanys'] = $ydayAvg[$key]['companys'];
                    }
                }
            }

            $cityData[$key]['count'] = empty($orders[$key]) ? '0' : $orders[$key];
            $cityData[$key]['allCount'] = $sum;
            //当天的平均分单 = 历史平均分单值 + 当前平均分单(订单量 / 会员数)
            $avg_count = $avg + round($sum / $value['vip_num'],2);

            //如果旧装修公司大于0，并且会员数大于0
            if(count($cityData[$key]['oldCompanys']) > 0 && $value['vip_num'] > 0){
                //对比今天与昨天会员数
                $diff = array_diff($cityData[$key]['oldCompanys'],$value['companys']);

                //如果是不同会员则不添加以前的平均分单
                if(count($diff) > 0){
                     $avg_count = round($sum / $value['vip_num'],2);
                }
            }

            $cityData[$key]['avg'] =  $avg_count;

            //入库
            $data['city_id'] = $value['cid'];
            $data['companys'] = implode($value['companys'],',');
            $data['vip_count'] = $value['vip_count'];
            $data['vip_num'] = $value['vip_num'];
            $data['order_num'] = $value['order_num'];
            $data['count'] = $cityData[$key]['count'];
            $data['all_count'] = $cityData[$key]['allCount'];
            $data['sum'] = $sum;
            $data['last_avg'] = $avg;
            $data['avg'] = $avg_count;
            $data['date'] = $date;

            if(empty($todayAvg[$key])){
                M('orders_assign_avg')->add($data);
            }
        }

        die($date.' ok');
    }
}