<?php

/**
 * 平均订单排行
 */

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

class OrdersrankController extends HomeBaseController {

    public function index(){

        //$this->removeCache($date);
        //$this->buildOrdersAssignAvgByDate(I('get.date'));
        //die;

        //定义时间
        $_date = $this->getDateTime();
        $date = $_date['date'];
        $monthStart = $_date['monthStart'];
        $monthEnd = $_date['monthEnd'];
        $monthLast = $_date['monthLast'];
        $monthLastDay = $_date['monthEndDay'];
        $prevMonthLast = $_date['prevMonthLast'];
        $lastDay = $_date['lastDay'];
        $nowDay = $_date['nowDay'];
        $startDate = $_date['dataStartTime'];
        if(date('m',strtotime($startDate)) != date('m',strtotime($nowDay))){
            $needPrevMonth = false;
        }else{//需要取上月数据
            $needPrevMonth = ture;
            $startDate = $prevMonthLast;
        }

        //$deptId = $_SESSION['uc_userinfo']['department_id'];

        $city = I('get.city');//城市
        $deptId = I('get.dept');//部门
        $monthType = I('get.month');//是否全月标识
        $superAdmin = array('1','37','51','68');//超管组

        //非超管 处理权限
        $gid = $_SESSION['uc_userinfo']['uid'];
        if(!in_array($gid,$superAdmin)){
            $_managerCitys = D('Ordersrank')->getManagerCityByGid($gid);
            foreach ($_managerCitys as $key => $value) {
                $managerCitys[] = $value['cid'];
            }
        }
        //定义标红城市 芜湖,合肥,成都,厦门,武汉,天津,福州,北京,南京,贵阳,苏州,温州,杭州,连云港,南宁,无锡,
        $red = array("320500","320100","320200","420100","350200","350100","320700","340100","330100",
                     "120001","340200","450100","510100","520100","330300","110100");
        
        //获取 上个月订单数
        $prevMonthOrder = D('Ordersrank')->getPrevMonthOrder($date);

        //获取 前三个月订单数
        $threeMonthOrder = D('Ordersrank')->getThreeMonthOrder($date);

        //获取 本月订单数
        $thisMonthOrder = D('Ordersrank')->getThisMonthOrder($monthStart);

        //取近5天会员信息
        $fiveVipTrends = D('Ordersrank')->getLastFiveDayVipCoop($date);

        //取近5天上下会员信息
        $downUpVip = $fiveVipTrends['upDownVip'];
        //会员动态
        $vipTrends = $fiveVipTrends['vipTrends'];

        //是否有上下会员
        $expireNewVip = $fiveVipTrends['expireNewVip'];

        //城市帐户信息
        $cityAccounts = D('Ordersrank')->getCityAccounts();

        unset($fiveVipTrends);

        //取时间段内平均分单 如果本月天数小于5天，倒推6天，否则取上月数据        
        $list = $this->getCityData($monthStart,$monthLast,$monthLastDay,$prevMonthLast,$nowDay,$lastDay,$monthEnd,$startDate);

        $prevMonthLastDay = $this->getPrevMonthLastDayOrderRank($prevMonthLast,$list);
        //dump($prevMonthLastDay);
        //die;
 
        foreach ($list as $k => $v) {

            $cid = $v['cid'];  
            //权限处理
            if(!in_array($gid,$superAdmin)){
                if(!in_array($cid,$managerCitys)){
                    unset($list[$k]);
                    continue;
                }
            }

            //外销 1 商务 2 商务外销 3  | manager 管理类别 0商务 1外销 2商务外销
            if(!empty($deptId)){
                //商务
                if($v['manager'] == '0'){
                    $theDept = '2';
                }
                //外销
                if($v['manager'] == '1'){
                    $theDept = '1';
                }
                //商务外销
                if($v['manager'] == '2'){
                    $theDept = '3';
                }
                if($deptId != $theDept){
                    unset($list[$k]);
                    continue;
                }
            }

            //昨天
            if(!empty($todayOrder[$cid])){
                //每日分单量 = 当天分单量/当天会员数
                $list[$k]['todayMRFD'] = round(($todayOrder[$cid]['fendan'] / $vipNum[$cid]) * 100, 2);
                //分单率 每日分单量/发单量*100%
                $list[$k]['todayRate'] = round(($todayOrder[$cid]['fendan'] / $todayOrder[$cid]['num']) * 100, 2);
                //平均分单 = 当天分单量/当天会员数
                $list[$k]['todayAvg'] = round(($todayOrder[$cid]['fendan'] / $vipNum[$cid]), 2);

                $list[$k]['todayNum'] = $todayOrder[$cid]['num'];
                $list[$k]['todayFendan'] = $todayOrder[$cid]['fendan'];
            }

            //上月订单
            if(!empty($prevMonthOrder[$v['cid']])){
                //查询月的上个月的分单率，每日分单量汇总/发单量汇总*100%；
                $list[$k]['prevMonthRate'] = round(($prevMonthOrder[$v['cid']]['fendan'] / $prevMonthOrder[$v['cid']]['num']) * 100, 2);
                $list[$k]['prevMonthOrder'] = $prevMonthOrder[$v['cid']]['num'];
                $list[$k]['prevMonthFendan'] = $prevMonthOrder[$v['cid']]['fendan'];
            }

            //前三月订单
            if(!empty($threeMonthOrder[$cid])){
                $list[$k]['threeMonthOrder'] = $threeMonthOrder[$cid]['fendan'];
            }

            //获取即将到期的城市信息
            if(isset($endCityList[$cid])){
               $list[$k]['endCity'] = '到'.$endCityList[$cid]['day_diff'];
            }

            //获取新上城市信息
            if(isset($newCityList[$cid])){
               $list[$k]['newCity'] = '新'.$newCityList[$cid]['day_diff'];
            }

            //近5天上下会员信息
            if(!empty($downUpVip[$cid])){              
                //$list[$k]['upDownVip'] = $downUpVip[$cid];
            }

            //近5天上下会员信息
            if(!empty($vipTrends[$cid])){
                $list[$k]['vipTrends'] = $vipTrends[$cid]['new'] - $vipTrends[$cid]['expire'];
            }

            //该月1号至查询日为止的分单率 即：1号至查询日的分单量÷发单量。
            if(!empty($thisMonthOrder[$cid])){              
                $list[$k]['thisMonthFDL'] =  round(($thisMonthOrder[$cid]['fendan'] / $thisMonthOrder[$cid]['num']) * 100, 2);
            }            

            //如果没有此城市 新上 数据
            if(empty($expireNewVip[$cid]['new'])){
                if(isset($downUpVip[$cid]['new'])){
                    $list[$k]['upDownVip']['new'] =  $downUpVip[$cid]['new'];
                }
            }

            //如果没有此城市 过期 数据
            if(empty($expireNewVip[$cid]['expire'])){
                if(isset($downUpVip[$cid]['expire'])){
                    $list[$k]['upDownVip']['expire'] =  $downUpVip[$cid]['expire'];
                }
            }

            if(!empty($cityAccounts[$cid])){
                $list[$k]['account'] = $cityAccounts[$cid];
            }
        }

        //按时间段取数据                 
        $prevSevenDay = date('Y-m-d',strtotime('-6 day',strtotime($nowDay)));
        foreach ($list as $key => $value) {            
            foreach ($value['date'] as $k => $v) {
                //取当前前7天的数据
                if($k >= $prevSevenDay AND $k <= $nowDay){
                    $sevenSubData[$k] = $v;
                }

                /*
                当前月的前一个月平均分单量：
                逻辑是月最后一天平均分单数据为整月平均分单数据；
                月平均分单总数据和当城市月分单标准对比

                上月分单率：
                每日分单量汇总/发单量汇总*100%；
                （非实际分单 分单量、发单量按照客服上班时间 前一天的17：30到当天的17：30）
                */ 
                if($k == $prevMonthLast){    
                    $list[$key]['prevMonthOrderAvg'] = $v['avg'];
                    //$list[$key]['color'] = $this->getOrderAvgColor($value['little'],$v['avg']);
                    $prevMonthDaySubData = $v;
                }

                //查询当天
                if($k == $nowDay){
                    //查询日的分单率：查询日的分单量/发单量*100% （非实际分单）
                    $list[$key]['todayRate'] = round($v['fen'] / $v['order_count'] * 100,2);

                    //查询日的平均分单量
                    $list[$key]['todayOrderAvg'] = $v['avg'];
                    $list[$key]['todayColor'] = $this->getOrderAvgColor($value['little'],$v['avg'],$date);
                    $list[$key]['todayVipCount'] = $v['vip_count'];
                    $list[$key]['todayOrderCount'] = $v['all_count'];
                }
            }
            unset($value['date']);
            $sevenData[$value['cid']] = $value;
            $sevenData[$value['cid']]['date'] = $sevenSubData;

            //增加到最近7天数据中 TODO
            $sevenData[$value['cid']]['date'][$prevMonthLast] = $prevMonthDaySubData;

            //重新排序
            ksort($sevenData[$value['cid']]['date']);
        }
  
        //将分类城市区分     
        foreach ($sevenData as $key => $value) {           
            
            //管理类别 0商务 1外销 2商务外销
            $dept = $value['manager'] == '0' ? 'in' : 'out';
            switch ($value["little"]) {
                case '0':
                    //a类城市
                    $ABC = 'A';
                    break;
                case '1':
                    //b类城市
                    $ABC = 'B';
                    break;
                case '2':
                    //c类城市
                    $ABC = 'C';
                    break;
            }

            $cityTypeNum[$dept][$ABC] ++;

            //循环统计每天数据
            foreach ($value['date'] as $k => $v) {
                if(empty($dateArray[$k])){
                    $dateArray[$k] = '1';
                }
                //统计高于标准和低于标准的城市数量
                $endMark = $value['endMark'];
                //如果是上月的，取上月数据           
                if(date('m',strtotime($nowDay)) != date('m',strtotime($v['date']))){
                    $endMark = empty($prevMonthLastDay[$value['cid']]['endMark']) ? '0' : $prevMonthLastDay[$value['cid']]['endMark'];
                }

                if ($endMark == 0) {
                    if ($v["wran"] == 1) {
                        //低于标准值
                        $cityType[$dept][$ABC]['date'][$k]["min"] ++;
                    }elseif ($v["wran"] == 2) {
                        //高于标准值
                        $cityType[$dept][$ABC]['date'][$k]["max"] ++;
                    }
                }
            }
        }

        ksort($cityType['out']['A']['date']);
        ksort($cityType['out']['B']['date']);
        ksort($cityType['out']['C']['date']);
        ksort($cityType['in']['A']['date']);
        ksort($cityType['in']['B']['date']);
        ksort($cityType['in']['C']['date']);
        ksort($dateArray);


        if(empty($cityType['out']['A']['date']) && empty($cityType['out']['B']['date']) && empty($cityType['out']['C']['date'])){
            $info['cityTypeOut'] = 'empty';
        }
        if(empty($cityType['in']['A']['date']) && empty($cityType['in']['B']['date']) && empty($cityType['in']['C']['date'])){
            $info['cityTypeIn'] = 'empty';
        }
        
        //搜索过滤
        if(!empty($city) || !empty($monthType)){            
            foreach ($list as $k => $v) {            
                $filter = $this->searchFilter($v,$city,$monthType,$red);
                if($filter == false){
                    unset($list[$k]);
                    continue;
                }
            }
        }

        $info['monthType'] = $monthType;
        $info['dept'] = I('get.dept');
        $info['cityABC'] = I('get.city');
        $info['prevMonth'] = date('m',strtotime($prevMonthLast));
        $info['date'] = $date;
        //获取前三个月月份
        $info['threeMonth'] =  $this->getThreeMonthStr($date);
        //定义昨天
        $info['today'] = date('d',strtotime($nowDay));

        if(I('get.dl') == '1'){
            $this->downOrdersRank($list,$info);
            die;
        }

        $this->assign('tj_data',$orderList['tj_data']);
        $this->assign('fpdate',$orderList['fpdate']);
        $this->assign('order_list',$orderList['list']);
        $this->assign('cityTypeNum',$cityTypeNum);
        $this->assign('cityType',$cityType);
        $this->assign('red',$red);
        $this->assign('dateArray',$dateArray);
        $this->assign('info',$info);
        $this->assign('list',$list);  
        $this->display();
    }

    /**
     * 取时间段内平均分单
     * 
     * @param  string $value [description]
     * 
     * @return [type]        [description]
     */
    public function getCityData($monthStart,$monthLast,$monthEndDay,$prevMonthLast,$nowDay,$lastDay,$monthEnd,$startDate = ''){     

        if(empty($startDate)){
            $startDate = $monthStart;
        }

        $prevMonthStart = date('Y-m-d',mktime(0,0,0,date('m',strtotime($prevMonthLast)),1,date('Y',strtotime($prevMonthLast))));
        $prevMonthEnd = date('t',strtotime($prevMonthLast));
        //上个月最后一天日期
        $prevMonthLastDate = date('Y-m-d',mktime(0,0,0,date('m',strtotime($prevMonthLast)),date('t',strtotime($prevMonthLast)),date('Y',strtotime($prevMonthLast))));

        //获取 城市会员开始和结束时间
        $vipCompany = D('Ordersrank')->getStartTimeByCity($monthStart,$monthLast);

        //获取 上个月 城市会员开始和结束时间
        $prevVipCompany = D('Ordersrank')->getStartTimeByCity($prevMonthStart,$prevMonthLastDate);

        //计算每个月每个城市的每天发单量
        $_result = D('Ordersrank')->getOrderInfoByCityDay($startDate,$lastDay,$monthEnd);

        $orderCount = $_result['count'];
        $orderInfo = $_result['list'];
 
        //获取 所有后台可见城市
        $_result = D('Ordersrank')->getQuyu();
        foreach ($_result as $key => $value) {
            $quyu[$value['cid']] = $value;
        }

        //取时间段平均分单
        $_result = D('Ordersrank')->getOrdersAssignAvg($startDate,$monthEnd);
        foreach ($_result as $key => $v) {
            $v['companys'] = array_filter(explode(',',$v['companys']));            
            $v['fen'] =  $v['zen'] =  $v['count'] = 0;
            if(!empty($orderInfo[$v['city_id']][$v['date']])){
                $v['fen'] = $orderInfo[$v['city_id']][$v['date']]['fen'];
                $v['zen'] = $orderInfo[$v['city_id']][$v['date']]['zen'];
                $v['order_count'] = $orderInfo[$v['city_id']][$v['date']]['count'];
            }
            $cityData[$v['city_id']]['date'][$v['date']] = $v;                  
        }


        //合并 处理城市数据
        foreach ($cityData as $key => $value) {         

            //每天发单量
            if(!empty($orderCount[$key])){
                $cityData[$key]['rate'] = round($orderCount[$key]['fen'] / $orderCount[$key]['count'] * 100,2);
            }

            //合并城市区域数据
            if(!empty($quyu[$key])){
                $cityData[$key]['cid'] = $quyu[$key]['cid'];
                $cityData[$key]['cname'] = $quyu[$key]['cname'];
                $cityData[$key]['little'] = $quyu[$key]['little'];
                $cityData[$key]['manager'] = $quyu[$key]['manager'];
                $cityData[$key]['point'] = $quyu[$key]['point'];
            }

            //处理整月 非整月
            $cityData[$key]['half'] = '0';
            if(!empty($vipCompany[$key])){
                $cityData[$key]['start'] = $vipCompany[$key]['start'];
                $cityData[$key]['end'] = $vipCompany[$key]['end'];                

                //城市开始时间大于本月初 非整月
                if($cityData[$key]['start'] > $monthStart){
                    $cityData[$key]['half'] = 1;
                }

                //本月结束标识
                if($cityData[$key]['end'] <= $monthEnd){
                    $cityData[$key]['endMonth'] = 1;
                    $cityData[$key]['half'] = 1;
                }
            }

            //处理上个月 整月非整月 half = 0整月 / half = 1 非整月     
            $cityData[$key]['prevHalf'] = '1';
            if(!empty($prevVipCompany[$key])){
                $cityData[$key]['prevHalf'] = '0';
                $cityData[$key]['prevStart'] = $prevVipCompany[$key]['start'];
                $cityData[$key]['prevEnd'] = $prevVipCompany[$key]['end']; 

                //城市开始时间大于本月初 非整月
                if($prevVipCompany[$key]['start'] > $prevMonthStart){
                    $cityData[$key]['prevHalf'] = 1;
                }
                //本月结束标识
                if($prevVipCompany[$key]['end'] <= $prevMonthLastDate){
                    $cityData[$key]['prevEndMonth'] = 1;
                    $cityData[$key]['prevHalf'] = 1;
                }
            }

            ksort($cityData[$key]['date']); 
        }

        //计算每个城市每天的订单量
        foreach ($cityData as $key => $value) {
            foreach ($value["date"] as $k => $val) {
                if($k < $monthStart){
                    continue;
                }
                //每个月的第一天
                if($k == $monthStart){                    
                    if($cityData[$key]["date"][$k]["vip_num"] != 0){
                        $cityData[$key]["endMark"] = 0;
                    }
                }else{
                    //前一天
                    $d = date("Y-m-d",strtotime("-1 day",strtotime($k)));

                    if($k <= $nowDay){
                        //如果前一天的会员数为0时，表示该城市无会员了
                        if($cityData[$key]["date"][$k]["vip_num"] == 0){
                            $cityData[$key]["endMark"] = 1;
                        }

                        //如果当天有会员了，则表示该城市有会员了
                        if($cityData[$key]["date"][$k]["vip_num"] != 0){
                            $cityData[$key]["endMark"] = 0;
                        }

                        //如果前一天没有会员了，当天也没有会员了，表示该城市无会员了
                        if ($cityData[$key]["date"][$k]["vip_num"] == 0 && $cityData[$key]["date"][$d]["vip_num"] == 0) {
                            $cityData[$key]["endMark"] = 1;
                        }
                    }
                }              
            }
        }

        //计算城市的分单是否符合标准
        foreach ($cityData as $key => $value) {
            if(empty($value['cid'])){
                unset($cityData[$key]);
            }

            foreach ($value["date"] as $k => $val) {

                //如果是上月的，取上月时间           
                if(date('m',strtotime($nowDay)) != date('m',strtotime($val['date']))){
                    $_start = $prevMonthStart;
                    $_end = $prevMonthEnd;
                    //如果是整月月的
                    if($value["prevHalf"] == 0){
                        $offset = date("d",strtotime($k));
                    }else{
                        //本月到期
                        if ($cityData[$key]["prevEndMonth"] == 1) {
                            $offset = date("d",strtotime($k)) - date("d",strtotime($_start)) + 1;
                        } else{
                            //本月开始
                            $offset = date("d",strtotime($k)) - date("d",strtotime($value["prevStart"])) + 1;
                        }
                    }
                }else{
                    $_start = $monthStart;
                    $_end = $monthEndDay;
                    //如果是整月月的
                    if($value["half"] == 0){
                        $offset = date("d",strtotime($k));
                    }else{
                        //本月到期
                        if ($cityData[$key]["endMonth"] == 1) {
                            $offset = date("d",strtotime($k)) - date("d",strtotime($_start)) + 1;
                        } else{
                            //本月开始
                            $offset = date("d",strtotime($k)) - date("d",strtotime($value["start"])) + 1;
                        }
                    }
                }                

                if($value["little"] == 0){
                    $max = round((20/$_end)*$offset,2);
                    $min = round((15/$_end)*$offset,2);
                }elseif($value["little"] == 1){
                    $max = round((10/$_end)*$offset,2);
                    $min = round((6/$_end)*$offset,2);
                }elseif($value["little"] == 2){
                    $max = round((5/$_end)*$offset,2);
                    $min = round((3/$_end)*$offset,2);
                }

                $cityData[$key]["date"][$k]["wran"] = 0;
                if($val["avg"] > $max){
                    $cityData[$key]["date"][$k]["wran"] = 2;
                }elseif($val["avg"] < $min){
                    $cityData[$key]["date"][$k]["wran"] = 1;
                }else{
                    $cityData[$key]["date"][$k]["wran"] = 3;
                }                

                if($k == $nowDay){
                    if ($nowDay == date("Y-m-d")) {
                        if (time() <= mktime(18,0,0,date("m",strtotime($nowDay)),date("d",strtotime($nowDay)),date("Y",strtotime($nowDay)))) {
                            $y = date("Y-m-d",strtotime("-1 day",strtotime(date("Y-m-d"))));
                            $cityData[$key]["vip_num"] = $cityData[$key]["date"][$y]["vip_num"];
                        }else{
                            $cityData[$key]["vip_num"] = $val["vip_num"];
                        }
                    } else {
                        $cityData[$key]["vip_num"] = $cityData[$key]["date"][$nowDay]["vip_num"];
                    }
                }
            }
        }

        foreach ($cityData as $key => $value) {
            //将各级别的城市放入到不同的数组中
            switch ($value["little"]) {
                case '0':
                    //a类城市
                $city["type_a"][] = $value;
                    break;
                case '1':
                    //b类城市
                $city["type_b"][] = $value;
                    break;
                case '2':
                    //c类城市
                $city["type_c"][] = $value;
                    break;
            }
        }
        unset($edition);

        //根据不同类别进行排序分组
        foreach ($city["type_a"] as $key => $value) {
            $edition[] = $value["manager"];
        }
        array_multisort($edition, SORT_DESC, $city["type_a"]);
        unset($edition);

        foreach ($city["type_b"] as $key => $value) {
            $edition[] = $value["manager"];
        }
        array_multisort($edition, SORT_DESC, $city["type_b"]);
        unset($edition);

        foreach ($city["type_c"] as $key => $value) {
            $edition[] = $value["manager"];
        }
        array_multisort($edition, SORT_DESC, $city["type_c"]);
        unset($edition);


        $cityData = array_merge($city["type_a"],$city["type_b"]);
        if (count($city["type_c"]) > 0) {
            $cityData = array_merge($city["type_a"],$city["type_b"],$city["type_c"]);
        }

        return $cityData;
    }


    public function getPrevMonthLastDayOrderRank($date,$cityData){
        
        $start = date('Y-m-d',mktime(0,0,0,date('m',strtotime($date)),1,date('Y',strtotime($date))));
        $monthDay = date('t',strtotime($date));
        $end = date('Y-m-d',mktime(0,0,0,date('m',strtotime($date)),date('t',strtotime($date)),date('Y',strtotime($date))));
     
        //取上个月会员数量
        $_result = D('Ordersrank')->getCityVipByDay($start,$end);
        //合并数据
        foreach ($_result as $key => $value) {
            $vipNum[$value['city_id']]['date'][$value['date']] = $value['vip_num'];
        }
        $_prevMonth = date('m',strtotime($start));
        $_prevYear = date('Y',strtotime($start));

        for ($i=1; $i <= $monthDay ; $i++) {
            $_date = date('Y-m-d',mktime(0,0,0,$_prevMonth,$i,$_prevYear));          
            foreach ($vipNum as $key => $value) {      
                if(!array_key_exists($_date,$value['date'])){
                    $vipNum[$key]['date'][$_date] = '0';
                }
            }
        }
        //dump($vipNum);
        //die;

        //计算 上个月 每个城市 整月非整月
        foreach ($vipNum as $key => $value) { 
            ksort($value['date']);

            foreach ($value['date'] as $k => $v) {
                $list[$key]['endMark'] = 1;

                //每个月的第一天
                if($k == $start){                    
                    if($v != 0){
                        $list[$key]['endMark'] = 0;
                    }
                }else{
                    //前一天
                    $d = date('Y-m-d',strtotime('-1 day',strtotime($k)));

                    if($k <= $end){
                        //如果当天的会员数为0时
                        if($v == 0){
                            //该城市无会员
                            $list[$key]['endMark'] = 1;
                        }

                        //该城市有会员
                        if($v != 0){
                            $list[$key]["endMark"] = 0;
                        }

                        //如果当天和前一天都没有会员，该城市无会员
                        if ($v == 0 && $vipNum[$key]['date'][$d] == 0) {
                            $list[$key]['endMark'] = 1;
                        }
                    }
                }              
            }
        }

        return $list;        
    }

    /**
     * Builds an orders assign average by date.
     *
     * @param      <type>  $date   The date
     *
     * @return     <type>  The orders assign average by date.
     */
    public function buildOrdersAssignAvgByDate($date,$autoBuild=''){
        empty($date) && $date = I('get.date');
        empty($date) && $date = date('Y-m-d');
 
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


            //dump($data);
            //M('orders_assign_avg')->add($data);
                    
            if(empty($todayAvg[$key])){
                M('orders_assign_avg')->add($data);
            }
        }

        if($autoBuild == true){
            return 'OK';
        }
        return $cityData;
    }


    public function autoBuildData(){
        $date = I('get.date');
        $today = !empty($date) ? $date : '2017-01-01';
        $nextDay = date('Y-m-d',strtotime("$today +1 day"));    
       
        if($today >= date('Y-m-d')){
            die("全部执行完成");
        }
        echo '开始 '.$today;

        $result = $this->buildOrdersAssignAvgByDate($today,true);

        if($result != 'OK'){
            echo '发生错误';
        }

        header('refresh:2;url=/ordersrank/autoBuildData?date='.$nextDay);
    }

    /**
     * 搜索过滤
     * 
     * @param  array    $v         value
     * @param  string   $city      city Type
     * @param  string   $monthType month Type
     * @param  array    $red       red color city
     * 
     * @return boolean 
     */
    private  function searchFilter($v,$city,$monthType,$red){

        //全月处理
        if(!empty($monthType)){
            if($v['endMark'] == '0'){
                if($v['half'] == '0'){
                    $thisMonthType = '1';//整月
                }else{
                    $thisMonthType = '2';//非整月
                }                    
            }else{
                $thisMonthType = '3';//过期
            }
            if($monthType != $thisMonthType){
               return false;
            }
        }


        //城市类别 1 重点城市 | 2 A类城市 | 3 B类城市 | 4 C类城市
        //little 0 A类 1 B类 2 C类 3 D类
        if(!empty($city)){
            if($city == '1'){
                if(!in_array($v['cid'],$red)){
                    return false;
                }
            }else{
                if($city == '2'){
                    $little = '0';
                }elseif ($city == '3') {
                    $little = '1';
                }elseif ($city == '4') {
                    $little = '2';
                }
                if($v['little'] != $little){
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 定义时间
     *
     * @return     <type>  The date time.
     */
    public function getDateTime(){

        //如果选择时间
        $date = I('get.date');       
        
        if (!empty($date)) {
            //如果查询为今天并且小于18点
            if (date('H') < 18 && $date == date('Y-m-d')) {
                $date = date('Y-m-d',strtotime("-1 day $date"));
            }             
        }else{
            //当前时间
            $date = date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y')));
            //如果今天小于18点，时间为昨天
            if (date('H') < 18) {
                $date = date('Y-m-d',strtotime("-1 day $date"));
            }
        }        

        $date = strtotime($date);
        $date = mktime(23,59,59,date('m',$date),date('d',$date),date('Y',$date));

        //当月开始时间 startDay monthStart
        $monthStart = date('Y-m-d',mktime(0,0,0,date('m',$date),1,date('Y',$date)));
        //当月结束时间 endDay
        $monthEnd = date('Y-m-d',$date);
        //当月最后一天 monthEnd
        $monthLast = date("Y-m-d",mktime(0,0,0,date("m",$date),date("t",$date),date("Y",$date)));
        //当月的最后一天天数 monthEndDay
        $monthEndDay = date("t",$date);
        //上个月最后一天 prevMonthLast
        $prevMonthLast = date("Y-m-d",strtotime("-1 day $monthStart"));
        //最后一天
        $lastDay = date("Y-m-d",strtotime($monthEnd) + 86400);
        //查询数据的起始时间
        $dataStartTime = date("Y-m-d",strtotime('-5 day',$date));
        //当前日期
        $nowDay = date('Y-m-d',$date);

        //定义开始时间
        if(date('H') < 18){
            $prevFiveDayStart = strtotime('-6 day',mktime(0,0,0,date('m',$date),date('d',$date),date('Y',$date)));
            $prevFiveDayEnd = strtotime('-1 day',mktime(0,0,0,date('m',$date),date('d',$date),date('Y',$date)));
        }else{
            $prevFiveDayStart = strtotime('-5 day',mktime(0,0,0,date('m',$date),date('d',$date),date('Y',$date)));
            $prevFiveDayEnd = mktime(18,0,0,date('m',$date),date('d',$date),date('Y',$date));
        }

        return array(
            'monthStart' => $monthStart,            
            'monthEnd' => $monthEnd,
            'monthLast' => $monthLast,
            'monthEndDay' => $monthEndDay,
            'prevMonthLast' => $prevMonthLast,
            'lastDay' => $lastDay,
            'nowDay' => $nowDay,
            'date' => $date,
            'dataStartTime' => $dataStartTime,
            //'prevFiveDayStart' => $prevFiveDayStart,
            // 'prevFiveDayEnd' => $prevFiveDayEnd,
        );
    }

    /**
     * 过期会员城市统计
     * 
     * @return empty
     */
    public function expire(){
        $date = I('get.date');
        if(empty($date)){
            $date = date('Y',time());
        }

        //获取 所有后台可见城市
        $list = D('Ordersrank')->getQuyu();

        //获取 上个月订单数
        $yearOrder = D('Ordersrank')->getYearOrder($date);

        // 获取 当前城市的最后一次会员的开始和结束时间
        $lastVip = D('Ordersrank')->getLastVipTime();


        foreach ($list as $k => $v) {

            $list[$k]['point'] = $this->formatCityPoint($v['point']);

            //订单数和发单数
            if(!empty($yearOrder[$v['cid']])){
                $list[$k]['num'] = $yearOrder[$v['cid']]['num'];
                $list[$k]['fen'] = $yearOrder[$v['cid']]['fen'];
                $list[$k]['rate'] = round(($yearOrder[$v['cid']]['fen'] / $yearOrder[$v['cid']]['num']) * 100, 2);
            }

            //最后一次会员的开始和结束时间
            if(!empty($lastVip[$v['cid']])){
                $list[$k]['start_time'] = $lastVip[$v['cid']]['start_time'];
                $list[$k]['end_time'] = $lastVip[$v['cid']]['end_time'];
            }
        }

        $info['date'] = $date;

        if(I('get.dl') == '1'){
            $this->downExcel($list);
            die;
        }

        $this->assign('info',$info);
        $this->assign('list',$list);
        $this->display();
    }

    /**
     * 城市会员详细
     * 
     * @return empty
     */
    public function citydetail(){
        $cid = I('get.cid');

        $map['cid'] = $cid;
        $info = M('quyu')->field('cname')->where($map)->find();

        $list = D('Ordersrank')->getVipListByCity($cid);
        //dump($list);

        $this->assign('info',$info);
        $this->assign('list',$list);
        $this->display();
    }

    /**
     * 获取平均分单量月标准颜色
     * 低于月标准用红色表示，高于用绿色
     *
     * 按照当前城市的级别类型对比分单标准的多少
     * 15≤A类城市/月≤20单，6≤B类城市/月≤10单，3≤C类城市/月≤5单，按照月自然天数对应分单标准；
     * 如9月份是30天，0.5（15/30）≤A类城市/天≤0.67（20/30）
     *
     * 例如9月1日的最低分单标准是（15/30）*1=0.5  
     * 
     * @param  [type] $type [description]
     * @param  [type] $val  [description]
     * 
     * @return [type]       [description]
     */
    public function getOrderAvgColor($type,$val,$date){        
        $monthDays = date('t',$date);
        $days = date('d',$date);

        if($type == 0){//15 ≤ A类城市/月 ≤ 20单
            $min = (15 / $monthDays) * $days;
            $max = (20 / $monthDays) * $days;            
        }elseif($type == 1){//6 ≤ B类城市/月 ≤ 10单
            $min = (6 / $monthDays) * $days;
            $max = (10 / $monthDays) * $days;
        }elseif($type == 2){//3 ≤ C类城市/月 ≤ 5单
            $min = (3 / $monthDays) * $days;
            $max = (5 / $monthDays) * $days;
        }

        $val < $min && $color = 'red';
        $val > $max && $color = 'green';

        return empty($color) ? 'none' : $color;
    }

    /**
     * Removes a cache.
     *
     * @param      <type>  $date   The date
     * @param      <type>  $key    The key
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    public function removeCache($date,$key){

        if(!empty($key)){
            return S($key,null);
        }

        //所有后台可见区域
        S("C:OrderRank:Quyu:",null);

        //当天订单数
        S("C:OrderRank:TodayOrders:".$date,null);

        //上个月订单数
        S("C:OrderRank:PrevMonthOrders:".$date,null);

        //近三个月订单数
        S("C:OrderRank:ThreeMonthOrders:".$date,null);

        //新上会员城市信息
        S("C:OrderRank:NewVipCity:".$date,null);

        //5天内上会员数量和下会员数量
        //S("C:OrderRank:downUpVipNum:".$end_day);
    }

    /**
     * Gets three month string.
     *
     * @param      <type>  $date   The date
     *
     * @return     <type>  Three month string.
     */
    public function getThreeMonthStr($date){
        $threeMonth['3'] = strtotime('-3 month',mktime(0,0,0,date('m',$date),1,date('Y',$date)));
        $threeMonth['2'] = strtotime('-2 month',mktime(0,0,0,date('m',$date),1,date('Y',$date)));
        $threeMonth['1'] = strtotime('-1 month',mktime(0,0,0,date('m',$date),1,date('Y',$date)));
        return date('n',$threeMonth['3']).'-'.date('n',$threeMonth['2']).'-'.date('n',$threeMonth['1']);
    }


    /**
     * 格式化城市重点系数
     *
     * @param      <type>        $var    The variable
     *
     * @return     array|string  ( description_of_the_return_value )
     */
    public function formatCityPoint($var){
        //重点城市分级：0:非重点城市 1：重点城市1 2：重点城市1.5 3：重点城市2 4：重点城市3
        $point = array(
            '0' => '-',
            '1' => '1',
            '2' => '1.5',
            '3' => '2',
            '4' => '3'
        );

        if(!empty($point[$var])){
            return $point[$var];
        }else{
            return '-';
        }
    }

    //下载Excel
    public function downOrdersRank($list){
        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array ( 'cacheTime' => 300 );
        \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );
        $phpExcel = new \PHPExcel();

        //设置表头
        $title = array(
            '城市',
            '城市类别',
            '会员数',
            '上月平均分单量',
            '平均分单量',
        );

        $cidList = I('get.cidList');
        $cidList = explode(',',$cidList);

        foreach ($list as $key => $value) {
            unset($value['date']);
            $result[$value['cid']] = $value;
        }
        unset($list);

        foreach ($cidList as $k => $v) {
            if(!empty($result[$v])){
                $list[] = $result[$v];
            }
        }
        
        $uid = I('session.uc_userinfo')['uid'];
        if($uid != '1'){
            unset($title['2']);
        }

        $i = 0;
        foreach ($title as $key => $value) {
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . 1;
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value);
        }

        $little = array(
            '0' => 'A','1' => 'B','2' => 'C',
        );

        //设置表内容
        $j = 1;
        foreach ($list as $k => $v) {
            //初始化$i
            $i = 0;
            //城市
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['cname']);

            //城市类别
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$little[$v['little']]);

            if($uid == '1'){ 
                //会员数
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['vip_num']);
            }

            //上月平均分单量
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['prevMonthOrderAvg']);

            //查询天平均分单量
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['todayOrderAvg']);

            $j++;
        }
        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="'.I('get.date').'平均分单数据.xls"');
        header("Content-Transfer-Encoding:binary");
        $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save('php://output');
        exit();
    }


    //-----------------------------------------------------------------------------------------------------------

    /**
     *    平均订单排行-重点城市
     */
    public function pjddphzdcs()
    {
        if ($_POST['quyu_id']) {
            $point = $_POST['point'];
            $quyuids = $_POST['quyu_id'];
            foreach ($quyuids as $quyuid) {
                $id['id'] = $quyuid;
                $data['point'] = $point;
                M('quyu')->where($id)->save($data);
            }
        }
        if($_POST['time'] && $_POST['time']!== date("Y-m")){
            $points = $this->searchcitypoint($_POST['time']);
        }else{
            $points = $this->pointcity();
        }
        $this->assign('points', $points);
        $this->display();
    }

    //指定重点等级的城市
    public function pointcity()
    {
        $pointones = M("quyu")->field('cname,id')->where('point = 1')->select();
        $pointtwos = M("quyu")->field('cname,id')->where('point = 2')->select();
        $pointthrees = M("quyu")->field('cname,id')->where('point = 3')->select();
        $pointfours = M("quyu")->field('cname,id')->where('point = 4')->select();

        foreach($pointones as $pointone ){
            $points[][1] = $pointone;
        }
        foreach($pointtwos as $key => $pointtwo ){
            $points[$key][2] = $pointtwo;
        }
        foreach($pointthrees as $key => $pointthree ){
            $points[$key][3] = $pointthree;
        }
        foreach($pointfours as $key => $pointfour ){
            $points[$key][4] = $pointfour;
        }
        return $points;
    }

    //修改城市重点等级
    public function editpoint()
    {
        if ($_POST && !empty($_POST['point'])) {
            $id['id'] = $_POST['quyu_id'];
            $data['point'] = $_POST['point'];
            M('quyu')->where($id)->save($data);
        }
    }
    //保存当月重点城市信息
//    public function savecitypoint(){
//        $citys = M('quyu')->field('id,cname,point')->select();
//        foreach($citys as $city){
//            $data['quyu_id'] = $city['id'];
//            $data['cname'] = $city['cname'];
//            $data['point'] = $city['point'];
//            $data['time'] =date("Y-m");
//            M('log_order_citypoint')->add($data);
//        }
//    }
    //查找指定月份的重点城市
    public function searchcitypoint($time){
        $dataone['time'] = $time;
        $dataone['point'] = 1 ;
        $datatwo['time'] = $time;
        $datatwo['point'] = 2;
        $datathree['time'] = $time;
        $datathree['point'] = 3 ;
        $datafour['time'] = $time;
        $datafour['point'] = 4 ;
        $pointones = M("log_order_citypoint")->field('cname,quyu_id')->where($dataone)->select();
        $pointtwos = M("log_order_citypoint")->field('cname,quyu_id')->where($datatwo)->select();
        $pointthrees = M("log_order_citypoint")->field('cname,quyu_id')->where($datathree)->select();
        $pointfours = M("log_order_citypoint")->field('cname,quyu_id')->where($datafour)->select();

        foreach($pointones as $pointone ){
            $pointone['id'] = $pointone['quyu_id'];
            $points[][1] = $pointone;
        }
        foreach($pointtwos as $key => $pointtwo ){
            $pointtwo['id'] = $pointtwo['quyu_id'];
            $points[$key][2] = $pointtwo;
        }
        foreach($pointthrees as $key => $pointthree ){
            $pointthree['id'] = $pointthree['quyu_id'];
            $points[$key][3] = $pointthree;
        }
        foreach($pointfours as $key => $pointfour ){
            $pointfour['id'] = $pointfour['quyu_id'];
            $points[$key][4] = $pointfour;
        }
        return $points;
    }
    /**
     *    批量管理
     */
    public function plgl()
    {
        if ($_GET) {
            $data = $_GET;
        } else {
            $data['little'] = 0;
        }
        $citys = M('quyu')->field('cname,id')->where($data)->select();
        if ($data['little'] == 0) {
            $littlecity = "A类城市";
        }
        if ($data['little'] == 1) {
            $littlecity = "B类城市";
        }
        if ($data['little'] == 2) {
            $littlecity = "C类城市";
        }
        $this->assign('little', $littlecity);
        $this->assign('citys', $citys);
        $this->display();
    }

    /**
     *    平均订单排行-权限设置
     */
    public function pjddphqxsz()
    {
        if ($_GET) {
            $role = $_GET['role'];
        }else{
            $role = 0;
        }
        $data['role_id'] = $role;
        $id['id'] = $role;
        $rolequyus = M('role_quyu')->where($data)->select();
        $rolenames = M('rbac_role')->where($id)->select();
        $rolename  = $rolenames[0]['role_name'];
        foreach($rolequyus as $rolequyu){
            $quyu[] = $rolequyu['quyu_id'];
        }
        $quyus = join(",", $quyu);

        $citys = M('quyu')->field('cname,id,manager')->select();
        $this->assign('rolename', $rolename);
        $this->assign('quyus', $quyus);
        $this->assign('citys', $citys);
        $this->assign('role', $role);

        $this->display();
    }

    //添加角色可查看的城市
    public function addrolequyu()
    {
        if($_POST){
            $data['role_id'] = $_POST['role_id'];
            M('role_quyu')->where($data)->delete();

            $quyus = $_POST['quyu_id'];
            foreach($quyus as $quyu){
                $data['quyu_id'] = $quyu;
                $data['time'] = time();
                M('role_quyu')->add($data);
            }
            $this->success('权限管理成功 :)', '/ordersrank/pjddphqxsz?role='.$data['role_id']);

        }

    }

    //下载Excel
    public function downExcel($list){
        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array ( 'cacheTime' => 300 );
        \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );
        $phpExcel = new \PHPExcel();
        //设置表头
        $title = array(
            '城市',
            '重点',
            '级别',
            '分单量',
            '分单率',
            '最近会员开始时间',
            '最近会员结束时间',
        );

        $i = 0;
        foreach ($title as $key => $value) {
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . 1;
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value);
        }
        //设置表内容
        $j = 1;
        foreach ($list as $k => $v) {
            //初始化$i
            $i = 0;

            $level = 'A';
            if($v['abc'] == '1'){
                $level = 'B';
            }
            if($v['abc'] == '2'){
                $level = 'C';
            }
            empty($v['fen']) && $v['fen'] = 0;
            empty($v['rate']) && $v['rate'] = 0;
            empty($v['start_time']) && $v['start_time'] = '-';
            empty($v['end_time']) && $v['end_time'] = '-';


            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['cname']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['point']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$level);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['fen']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['rate']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['start_time']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['end_time']);

            $j++;
        }
        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="过期会员城市统计.xls"');
        header("Content-Transfer-Encoding:binary");
        $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save('php://output');
        exit();
    }

}