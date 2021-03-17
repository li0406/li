<?php

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

/**
*  销售系统 统计
*/

class SalestatsController extends HomeBaseController {

    /**
     * 全瞰-分单量进度
     */
    public function qkfdljd(){
        $dept = I('get.dept');
        $condition['dept'] = 0;
        if(!empty($dept)){
            $condition['dept'] = $dept;
            $info['dept'] = '$("#searchform").find("select[name=dept]").val("'.$dept.'");';
        }else{
            $dept = getUserDepartment();
        }
        
        $year = I('get.date');
        if(!empty($year)){
            $info['date'] = $year;
        }else{
            $year = date('Y');
        }
        $condition['year'] = $year;
        $this->assign('keyword',$condition);        

        //获取所有城市列表
        $cityList = getUserSaleCitys();
        $ids = '';
        foreach ($cityList as $k => $v) {
            $ids[] = $v['id']; 
        }
        if(!empty($ids)){
            $condition['city_id'] = array('IN',$ids); 
            //生成数据，计划任务中不执行
            //生成月份数组
            $preyear = $year -1;
            $month = date('m');
            if($month == 1){
                $preyear = $preyear - 1;
                $year = $year - 1;
            }
            if($year < date('Y')){
                $month = 12;
            }
            for ($i=1; $i <= 12; $i++) {
                $preMonthList[] = date('Y-m',strtotime("$preyear-01-01 +$i month"));//去年月份数组
                if(($i < $month)){
                    $curMonthList[] = date('Y-m',strtotime("$year-01-01 +$i month"));//查询当年月份数组
                }
                if($month == 1){
                    $curMonthList[] = date('Y-m',strtotime("$year-01-01 +$i month"));//查询当年月份数组
                }
            }
            if($year < date('Y')){
                $curMonthList[] = ($year+1).'-01';
            }
            //获取查询当年数据
            foreach ($curMonthList as $k => $v) {
                $map['month'] = date('m',strtotime($v));
                $map['year'] = date('Y',strtotime($v));
                if($condition['dept'] != 0){
                    $map['dept'] = $condition['dept'];
                }
                //查询该月的会员分单信息
                //if($map['month'] == $month){
                    //查询了本月
                    //$result = $this->buildFdljd($map,$condition['city_id']);
                //}else{
                    //查询历史
                $result = D("SalesSetting")->getVipFenDanByMonth($map,$condition['city_id']);
                //var_dump(M()->getLastSql());
                //}
                if(!empty($result)){
                    $totalJhfds = 0;
                    $totalFds5  = 0;
                    $totalFds10 = 0;
                    $totalFds15 = 0;
                    $totalFds20 = 0;
                    $totalFds25 = 0;
                    $totalFds30 = 0;
                    $cityNum = count($result);
                    foreach ($result as $key => $value) {
                        
                        $thisMonth['time'] = $v;//月份
                        $thisMonth['vipnum'] = $thisMonth['vipnum'] + $value['vipnum'];//实际会员数
                        if($value['isenough'] == 0){
                            $thisMonth['isenough'] = $thisMonth['isenough']+1;//不足分单城市数
                        }
                        $thisMonth['notenough'] = $thisMonth['notenough'] + $value['notenough'];//不足分单会员数
                        $totalJhfds = $totalJhfds + $value['jhfds'];//总计划分单数
                        $thisMonth['avg_jhfds'] = number_format(($totalJhfds/$cityNum),1,'.','');//计划分单数均值
                        $totalFds5 = $totalFds5 + $value['fds5']; 
                        $thisMonth['avg_fds5'] = number_format(($totalFds5/$cityNum),1,'.','');//5日分单数均值
                        $totalFds10 = $totalFds10 + $value['fds10']; 
                        $thisMonth['avg_fds10'] = number_format(($totalFds10/$cityNum),1,'.','');//10日分单数均值
                        $totalFds15 = $totalFds15 + $value['fds15']; 
                        $thisMonth['avg_fds15'] = number_format(($totalFds15/$cityNum),1,'.','');//15日分单数均值
                        $totalFds20 = $totalFds20 + $value['fds20']; 
                        $thisMonth['avg_fds20'] = number_format(($totalFds20/$cityNum),1,'.','');//20日分单数均值
                        $totalFds25 = $totalFds25 + $value['fds25']; 
                        $thisMonth['avg_fds25'] = number_format(($totalFds25/$cityNum),1,'.','');//25日分单数均值
                        $totalFds30 = $totalFds30 + $value['fds30']; 
                        $thisMonth['avg_fds30'] = number_format(($totalFds30/$cityNum),1,'.','');//30日分单数均值
                    }
                }else{
                    $thisMonth['time'] = $v;//月份
                }

                $curData[$k] = $thisMonth;
                if($dept == 0){
                    $curData[$k]['dept'] = '营销中心';
                }elseif($dept == 1){
                    $curData[$k]['dept'] = '商务';
                }else{
                    $curData[$k]['dept'] = '外销';
                }
                unset($thisMonth);
                unset($result);
            }

            //查询去年数据
            foreach ($preMonthList as $k => $v) {
                $map['month'] = date('m',strtotime($v));
                $map['year'] = date('Y',strtotime($v));
                //查询该月的会员分单信息
                $result = D("SalesSetting")->getVipFenDanByMonth($map,$condition['city_id']);
                if(!empty($result)){
                    foreach ($result as $key => $value) {
                        $cityNum = count($value);
                        $thisMonth['time'] = $v;//月份
                        $thisMonth['dept'] = $condition['dept'];//部门
                        $thisMonth['vipnum'] = $thisMonth['vipnum'] + $value['vipnum'];//实际会员数
                        if($value['isenough'] == 0){
                            $thisMonth['isenough'] = $thisMonth['isenough']+1;//不足分单城市数
                        }
                        $thisMonth['notenough'] = $thisMonth['notenough'] + $value['notenough'];//不足分单会员数
                        $totalJhfds = $totalJhfds + $value['jhfds'];//总计划分单数
                        $thisMonth['avg_jhfds'] = number_format(($totalJhfds/$cityNum),1,'.','');//计划分单数均值
                        $totalFds5 = $totalFds5 + $value['fds5']; 
                        $thisMonth['avg_fds5'] = number_format(($totalFds5/$cityNum),1,'.','');//5日分单数均值
                        $totalFds10 = $totalFds10 + $value['fds10']; 
                        $thisMonth['avg_fds10'] = number_format(($totalFds10/$cityNum),1,'.','');//10日分单数均值
                        $totalFds15 = $totalFds15 + $value['fds15']; 
                        $thisMonth['avg_fds15'] = number_format(($totalFds15/$cityNum),1,'.','');//15日分单数均值
                        $totalFds20 = $totalFds20 + $value['fds20']; 
                        $thisMonth['avg_fds20'] = number_format(($totalFds20/$cityNum),1,'.','');//20日分单数均值
                        $totalFds25 = $totalFds25 + $value['fds25']; 
                        $thisMonth['avg_fds25'] = number_format(($totalFds25/$cityNum),1,'.','');//25日分单数均值
                        $totalFds30 = $totalFds30 + $value['fds30']; 
                        $thisMonth['avg_fds30'] = number_format(($totalFds30/$cityNum),1,'.','');//30日分单数均值
                    }
                }else{
                    $thisMonth['time'] = $v;//月份
                }
                $preData[$k] = $thisMonth;
                unset($thisMonth);
            }

            //取分单量环比
            $year = date('Y');
            if($condition['year'] == $year){
                //是今年
                $month = date('m');
                $thismonth = date('Y-m');
                $lastMonth = date('Y-m',strtotime('-1 month'));
                $lastTwoMonth = date('Y-m',strtotime('-2 month'));
                if($month == '02'){
                    $data['time'] = [$preMonthList[10],$preMonthList[11],$curMonthList[0]];
                    $data['fds'] = [
                        [$preData[10]['avg_fds5'],$preData[10]['avg_fds10'],$preData[10]['avg_fds15'],$preData[10]['avg_fds20'],$preData[10]['avg_fds25'],$preData[10]['avg_fds30']],
                        [$preData[11]['avg_fds5'],$preData[11]['avg_fds10'],$preData[11]['avg_fds15'],$preData[11]['avg_fds20'],$preData[11]['avg_fds25'],$preData[11]['avg_fds30']],
                        [$curData[0]['avg_fds5'],$curData[0]['avg_fds10'],$curData[0]['avg_fds15'],$curData[0]['avg_fds20'],$curData[0]['avg_fds25'],$curData[0]['avg_fds30']]
                    ]; 
                }elseif($month == '03'){
                    $data['time'] = [$preMonthList[11],$curMonthList[0],$curMonthList[1]];
                    $data['fds'] = [
                        [$preData[11]['avg_fds5'],$preData[11]['avg_fds10'],$preData[11]['avg_fds15'],$preData[11]['avg_fds20'],$preData[11]['avg_fds25'],$preData[11]['avg_fds30']],
                        [$curData[0]['avg_fds5'],$curData[0]['avg_fds10'],$curData[0]['avg_fds15'],$curData[0]['avg_fds20'],$curData[0]['avg_fds25'],$curData[0]['avg_fds30']],
                        [$curData[1]['avg_fds5'],$curData[1]['avg_fds10'],$curData[1]['avg_fds15'],$curData[1]['avg_fds20'],$curData[1]['avg_fds25'],$curData[1]['avg_fds30']]
                    ]; 
                }else{
                    $data['time'] = [$lastTwoMonth,$lastMonth,$thismonth];
                    foreach ($data['time'] as $k => $v) {
                        foreach ($curData as $key => $val) {
                            if($val['time'] == $v){
                                $data['fds'][] = [$val['fds5'],$val['fds10'],$val['fds15'],$val['fds20'],$val['fds25'],$val['fds30']];
                            }
                        }
                    }     
                }
            }
            foreach ($data['fds'] as $key => $value) {
               foreach ($value as $k => $v) {
                   if($v == ''){
                        $data['fds'][$key][$k] = 0;
                   }
               }
            }
            foreach ($data['fds'] as $key => $value) {
                $data['fds'][$key] = implode(',', $value);
            }
            //去分单不足城市同比
            $data1['time'] = [$preyear,$year];
            //上一年分单不足城市数
            foreach ($preData as $k => $v) {
                if($v['isenough'] == ''){
                    $v['isenough'] = 0;
                }
                $str1 .= $v['isenough'].',';
            }
            $data1['city'][0] = substr($str1,0,-1);
            //当年分单不足城市数
            foreach ($curData as $k => $v) {
                if($v['isenough'] == ''){
                    $v['isenough'] = 0;
                }
                $str2 .= $v['isenough'].',';
            }
            $data1['city'][1] = substr($str2,0,-1);
        }
        //$this->buildFdljd('1485878400','','');//2月数据-----生成数据计划任务
        //die;

        
        
        $userdept = getUserDepartment();
        $this->assign('department',$userdept);
        $this->assign('data',$data);
        $this->assign('data1',$data1);
        $this->assign("list",$curData);
        $this->display();
    }

    /**
     * 全瞰-会员数完成率
     */
    public function qkhyswcl(){

        $dept = I('get.dept');
        $year = I('get.date');
        if(empty($year)){
            $year = date('Y');
        }

        $cityList = getUserSaleCitys();
        $ids = '';
        foreach ($cityList as $k => $v) {
            $ids[] = $v['id']; 
        }
        if(!empty($ids)){
            $condition['c.cid'] = array('IN',$ids); 
            
            //生成月份数组
            $preyear = $year -1;
            $month = date('m');
            if($month == 1){
                $preyear = $preyear - 1;
                $year = $year - 1;
            }
            if($year < date('Y')){
                $month = 12;
            }
            for ($i=1; $i <= 12; $i++) {
                $perMonthList[] = date('Y-m',strtotime("$preyear-01-01 +$i month"));//去年月份数组
                if(($i < $month)){
                    $curMonthList[] = date('Y-m',strtotime("$year-01-01 +$i month"));//查询当年月份数组
                }
                if($month == 1){
                    $curMonthList[] = date('Y-m',strtotime("$year-01-01 +$i month"));//查询当年月份数组
                }
            }
            if($year < date('Y')){
                $curMonthList[] = ($year+1).'-01';
            }
            //生成今年数据
            $list = D('SalesSetting')->getQkHyswcl($year,$dept,'',$condition);
            $_levelList = D('SalesSetting')->getQkHyswcl($year,$dept,1,$condition);//地级市统计数据数组
            foreach ($_levelList as $k => $v) {
                $levelList[$v['year'].$v['month']] = $v;
            }
            foreach ($list as $key => $v) {
                $list[$key]['djs_csnum'] = $levelList[$v['year'].$v['month']]['csnum'];
                //地级市会员完成率 地级市实际总会员数/地级市会员指标*100%
                $list[$key]['djs_hywcl'] = round(($levelList[$v['year'].$v['month']]['vipcnt'] / $levelList[$v['year'].$v['month']]['hyzb']) * 100, 1);

                $list[$key]['hyswcl'] = round(($v['vipcnt'] / $v['hyzb'] ) * 100, 1);
                $list[$key]['vipnum'] = $v['vipcnt'] - $v['doublecnt'];
            }
            //如果是今年，最后一条数据查询本月
            $thisYear = date('Y',time());
            if($thisYear == $year){
                //查询城市基本信息
                $monthData = D('SalesSetting')->getMonthData($ids,$dept);
                $list[] = $monthData;
            }
            foreach ($curMonthList as $k => $v) {
                $m = date('m',strtotime($v));
                foreach ($list as $key => $value) {
                    if($value['month'] == $m){
                        $result[$k] = $value;
                    }
                }
                $result[$k]['time'] = $v;
            }

            //开始图表处理
            $curYearList = D('SalesSetting')->getQkHyswcl($year,$dept,'',$condition);
            if($thisYear == $year){
                $curYearList[] = $monthData;
            }
            foreach ($curYearList as $k => $v) {
                $curList[$v['year'].$v['month']] = $v;
            }

            $perYearList = D('SalesSetting')->getQkHyswcl($year-1,$dept,'',$condition);
            foreach ($perYearList as $k => $v) {
                $perList[$v['year'].$v['month']] = $v;
            }


            /*$peryear = $year -1;
            for ($i=1; $i <= 12; $i++) {
                $perMonthList[] = date('Y-m',strtotime("$peryear-01-01 +$i month"));
                $curMonthList[] = date('Y-m',strtotime("$year-01-01 +$i month"));
            }*/

            foreach ($curMonthList as $k => $v) {
                $date = str_replace('-','',$v);
                $chart['cur']['vipcnt'][] = empty($curList[$date]['vipcnt']) ? '0' : $curList[$date]['vipcnt'];
                $chart['cur']['hyzb'][] = empty($curList[$date]['hyzb']) ? '0' : $curList[$date]['hyzb'];
                $chart['cur']['hywcl'][] = round(($curList[$date]['vipcnt'] / $curList[$date]['hyzb']) * 100, 1);
            }

            foreach ($perMonthList as $k => $v) {
                $date = str_replace('-','',$v);
                $chart['per']['vipcnt'][] = empty($perList[$date]['vipcnt']) ? '0' : $perList[$date]['vipcnt'];
                $chart['per']['hyzb'][] = empty($perList[$date]['hyzb']) ? '0' : $perList[$date]['hyzb'];
                $chart['per']['hywcl'][] = round(($perList[$date]['vipcnt'] / $perList[$date]['hyzb']) * 100, 1);
            }

            foreach ($chart as $key => $value) {
                $liteChart[$key]['vipcnt'] = implode(',',$value['vipcnt']);
                $liteChart[$key]['hyzb'] = implode(',',$value['hyzb']);
                $liteChart[$key]['hywcl'] = implode(',',$value['hywcl']);
            }

            $info['curYear'] = date('Y');
            $info['perYear'] = date('Y') - 1;

            if(!empty($dept)){
                $info['deptinfo'] = '$("#searchform").find("select[name=dept]").val("'.$dept.'");';
            }
            if(!empty($year)){
                $info['time'] = $year;
            }
        }
        $dept = getUserDepartment();
        $this->assign('department',$dept);
        $this->assign('list',$result);
        $this->assign('chart',$liteChart);
        $this->assign('info',$info);
        $this->display();
    }


    /**
     * 城市分单量满足率
     */
    public function csfdlmzl(){
        //分页
        $pageIndex = 1;
        $pageCount = 20;
        if(!empty($_GET["p"])){
            $pageIndex = $_GET["p"];
        }

        $condition['c.year'] = array('EQ',date('Y'));
        $condition['c.month'] = array('EQ',date('m'));

        $city = I('get.city');
        /*if(!empty($city)){
            $condition['c.city_id'] = $city;
            $info['cityinfo'] = '$("#searchBox").find("select[name=city]").val("'.$city.'");';
        }*/

        //$cityList = D('SalesSetting')->getManageCitys();
        //获取所有城市列表
        $cityList = getUserSaleCitys();
        $ids = '';
        foreach ($cityList as $k => $v) {
            $ids[] = $v['id']; 
        }
        if($ids !== ''){
            if(!empty($city) && in_array($city, $ids)){
                $condition['c.city_id'] = $city;
                $info['cityinfo'] = '$("#searchBox").find("select[name=city]").val("'.$city.'");';
            }else{
                $idstr = implode(',', $ids);
                $condition['c.city_id'] = array('IN',$idstr); 
            }
        }

        $dept = I('get.dept');
        if(!empty($dept)){
            $condition['m.dept'] = $dept;
            $info['deptinfo'] = '$("#searchBox").find("select[name=dept]").val("'.$dept.'");';
        }

        $date = I('get.date');
        if(!empty($date)){
            $exp_date = explode('-',$date);
            $condition['c.year'] = array('EQ',$exp_date['0']);
            $condition['c.month'] = array('EQ',$exp_date['1']);
            $info['date'] = $date;
        }

        $time_now = date("Y-m",time());
        if($time_now != $date && $date != ''){
            //查询历史
            $result = $this->getCsfdlmzlList($condition,$pageIndex,$pageCount);
        }else{
            //查询本月
            $result = $this->buildCsfdlmzlData($condition,$pageIndex,$pageCount);
        }
        $user = session('uc_userinfo');
        $info['pageCount'] = $result['pageCount'];
        $info['today'] = date('Y-m-d',time());
        $info['username'] = $user['name'];
        $dept = getUserDepartment();
        $this->assign('total',$result['total']);
        $this->assign('heji',$result['heji']);
        $this->assign('department',$dept);
        $this->assign('info',$info);
        $this->assign("list",$result['list']);
        $this->assign('page',$result['page']);
        $this->assign('cityList',$cityList);
        $this->display();
    }

    /**
     * 会员分单量满足率
     */
    public function hyfdlmzl(){
        $search = $_GET;
        $this->assign('search',$search);
        $date = I("get.date");
        if(!empty($date)){
            $starttime = strtotime($date.'-01 00:00:00');
            $endtime = date('Y-m-d', strtotime("$date +1 month -1 day"));
            $endtime = strtotime($endtime.' 23:59:59');
            unset($search['date']);
        }
        //获取所有城市列表
        $cityList = getUserSaleCitys();
        $ids = '';
        foreach ($cityList as $k => $v) {
            $ids[] = $v['id']; 
        }
        if($ids !== ''){
            $idstr = implode(',', $ids);
            $search['c.cid'] = array('IN',$idstr); 
        }
        //分页
        $pageIndex = 1;
        $pageCount = 20;
        if(!empty($_GET["p"])){
            $pageIndex = $_GET["p"];
        }
        //查询所有，算总计
        $all = $this->buildHyfdlmzlData($starttime,$endtime,'','',$search);
        $list = $this->buildHyfdlmzlData($starttime,$endtime,$pageIndex,$pageCount,$search);
        $cache = $this->buildHyfdlmzlData($starttime,$endtime,1,1000,$search);//查询导出用1000条
        S("Cache:AllHuiyuanFendanMzl",$cache['list'],3600);//生成缓存下载使用
        //取所有销售帐号
        $saleUsers = D('Salecount')->getAdminsByPosition();
        $user = session('uc_userinfo');
        $info['pageCount'] = $result['pageCount'];
        $info['today'] = date('Y-m-d',time());
        $info['username'] = $user['name'];
        $dept = getUserDepartment();
        //添加总计、合计
        $all_num = count($all['list']);
        foreach ($all['list'] as $k => $v) {
            $total['uid'] = '本项合计';// 会员ID    
            $total['jc'] = '-';// 会员简称    
            $total['dept'] = '-';// 部门  
            $total['city'] = '-';// 城市  
            $total['brand_division'] = '-';// 品师长     
            $total['brand_regiment'] = '-';// 品团长     
            $total['brand_manage'] = '-';// 品牌师     
            $total['dev_division'] = '-';// 拓师长     
            $total['dev_regiment'] = '-';// 拓团长     
            $total['dev_manage'] = '-';// 城市经理    
            $total['hyzt'] = '-';// 会员状态    
            $total['planFendan'] += $v['planFendan'];// 计划月分单数     
            $total['fendan'] += $v['fendan'];// 实际月分单数     
            $total['coopDays'] += $v['coopDays'];// 当前累计合作天数    
            $total['timeProgress'] = number_format($v['timeProgress'],1,'.','');// 时间进度    
            $total['fdmzl'] += $v['fdmzl'];// 分单满足率     
            $total['byqds'] += $v['byqds'];// 本月缺单数     
            $total['allstart'] = '-';// 本次合同开始  
            $total['allend'] = '-';// 本次合同结束
        }
        $total['fdmzl'] = number_format($total['fdmzl']/$all_num,1,'.','');
        $list_num = count($list['list']);
        foreach ($list['list'] as $k => $v) {
            $heji['uid'] = '本页合计';// 会员ID    
            $heji['jc'] = '-';// 会员简称    
            $heji['dept'] = '-';// 部门  
            $heji['city'] = '-';// 城市  
            $heji['brand_division'] = '-';// 品师长     
            $heji['brand_regiment'] = '-';// 品团长     
            $heji['brand_manage'] = '-';// 品牌师     
            $heji['dev_division'] = '-';// 拓师长     
            $heji['dev_regiment'] = '-';// 拓团长     
            $heji['dev_manage'] = '-';// 城市经理    
            $heji['hyzt'] = '-';// 会员状态    
            $heji['planFendan'] += $v['planFendan'];// 计划月分单数     
            $heji['fendan'] += $v['fendan'];// 实际月分单数     
            $heji['coopDays'] += $v['coopDays'];// 当前累计合作天数    
            $heji['timeProgress'] = number_format($v['timeProgress'],1,'.','');// 时间进度    
            $heji['fdmzl'] += $v['fdmzl'];// 分单满足率     
            $heji['byqds'] += $v['byqds'];// 本月缺单数     
            $heji['allstart'] = '-';// 本次合同开始  
            $heji['allend'] = '-';// 本次合同结束
        }
        $heji['fdmzl'] = number_format($heji['fdmzl']/$all_num,1,'.','');
        $this->assign('total',$total);
        $this->assign('heji',$heji);
        $this->assign('department',$dept);
        $this->assign('page',$list['page']);
        $this->assign('info',$info);
        $this->assign("list",$list['list']);
        $this->assign('saleUsers',$saleUsers);
        $this->assign('cityList',$cityList);
        $this->display();
    }

    /**
     * 城市会员数完成率
     */
    public function cshyswcl(){
        //获取所有的品牌师长、团长、品牌师
        $brand = D('Salecount')->getAdminsByPosition();
        //var_dump($brand);
        //分页
        $pageIndex = 1;
        $pageCount = 20;
        if(!empty($_GET["p"])){
            $pageIndex = $_GET["p"];
        }

        $condition['c.year'] = array('EQ',date('Y'));
        $condition['c.month'] = array('EQ',date('m'));

        $dept = I('get.dept');
        if(!empty($dept)){
            $condition['m.dept'] = $dept;
            $info['deptinfo'] = '$("#searchBox").find("select[name=dept]").val("'.$dept.'");';
        }

        $date = I('get.date');
        if(!empty($date)){
            $exp_date = explode('-',$date);
            $condition['c.year'] = array('EQ',$exp_date['0']);
            $condition['c.month'] = array('EQ',intval($exp_date['1']));
            $info['date'] = $date;
        }
        $city = I('get.city');
        $cityList = getUserSaleCitys();
        $ids = '';
        foreach ($cityList as $k => $v) {
            $ids[] = $v['id']; 
        }
        if($ids !== ''){
            if(!empty($city) && in_array($city, $ids)){
                $condition['c.cid'] = array('EQ',$city);
                $info['cityinfo'] = '$("#searchBox").find("select[name=city]").val("'.$city.'");';
                //unset($condition['city']);
            }else{
                $idstr = implode(',', $ids);
                $condition['c.cid'] = array('IN',$idstr); 
                //unset($condition['city']);
            }
        }


        $deptRequest = array('dev_division','dev_regiment','dev_manage','brand_division','brand_regiment','brand_manage');
        $getList = I('get.');

        foreach ($deptRequest as $k => $v) {
            if(!empty($getList[$v])){
                $condition['m.'.$v] = array('EQ',$getList[$v]);
                $info['manageinfo'] .= '$("#searchBox").find("select[name='.$v.']").val("'.$getList[$v].'");';
            }
        }

        //下载Excel
        $time_now = date("Y-m",time());
        if(I('get.dl') == '1'){
            $pageCount = 1200;
            //设置表头
            $title = array(
                '城市','部门','城市重点系数','品师长','品团长','品牌师','拓师长',
                '拓团长','城市经理','会员指标','实际总会员数','会员数完成率','多倍会员数','战略会员数',
                '暂停会员数','退费会员数','城市QQ群名称','实际群成员数','成员数完成率'
            );

            //设置表列
            $column = array(
                'city','dept','ratio','brand_division','brand_regiment','brand_manage','dev_division',
                'dev_regiment','dev_manage','hyzb','vipcnt','hywcl','doublecnt',
                'pause','refund','qqname','qqpoint','qqwcl',
            );

            //$result = $this->getCshyswclList($condition,$pageIndex,$pageCount);
            if($time_now != $date && $date != ''){
                //查询历史
                $result = $this->getCshyswclList($condition,$pageIndex,$pageCount);
            }else{
                //查询本月
                $result = $this->getCshyswclData($condition,$pageIndex,$pageCount);
            }
            $this->downExcel($title,$column,$result['list'],'城市会员数完成率');
            die;
        }else{
            if($time_now != $date && $date != ''){
                //查询历史
                $result = $this->getCshyswclList($condition,$pageIndex,$pageCount);
            }else{
                //查询本月
                $result = $this->getCshyswclData($condition,$pageIndex,$pageCount);
            }   
        }

        //var_dump($);
        // foreach ($result['list'] as $k => $v) {
            
        // }


        $user = session('uc_userinfo');

        $info['pageCount'] = $result['pageCount'];
        $info['today'] = date('Y-m-d',time());
        $info['username'] = $user['name'];
        $userdept = getUserDepartment();
        //添加合计、总计
        $this->assign('total',$result['total']);
        $this->assign('heji',$result['heji']);

        $this->assign('department',$userdept);
        $this->assign('info',$info);
        $this->assign("list",$result['list']);
        $this->assign('page',$result['page']);
        $this->assign('saleUsers',$brand);
        $this->assign('cityList',$cityList);
        $this->display();
    }

    /**
     * 生成 - 分单量进度
     *
     * @param      int  $startTime  The start time
     * @param      int  $endTime    The end time
     * @param      int  $dept       部门
     *
     * @return     mixed
     */
    public function buildFdljd($map,$condition){
        if(empty($startTime)){
            $startTime = mktime(0,0,0,date("m"),1,date("Y"));
        }
        if(empty($endTime)){
            $endTime = mktime(23,59,59,date('m',$startTime),date('t',$startTime),date('Y',$startTime));
        }
    
        //获取会员数
        //$vipCount = D('SalesSetting')->getVipUserCount();
        $vipCount = D('SalesSetting')->getVipUserCountBySalesCity($map['dept'],$condition);

        //dump($vipCount);
        //$info = $vipCount;
        
        //计划分单数
        $JHFDAvg = D('SalesSetting')->getJhFendanAvg();
        //dump($JHFDAvg);
        
        //获取所有的城市分单量
        // $result = D('SalesSetting')->fdljdcx($map['dept'],$condition,$startTime,$endTime);
        // var_dump($result);
        //获取分单数按日期
        foreach ($vipCount as $k => $v) {
            foreach ($JHFDAvg as $key => $value) {
                if($v['id'] == $value['manage_id']){
                    $vipCount[$k]['jhfds'] = $value['point'];
                }
            }
            if($vipCount[$k]['jhfds'] == ''){
                $vipCount[$k]['jhfds'] = 0;
            }   
            $vipCount[$k]['city_jhfds'] = $vipCount[$k]['jhfds']*$v['vipcnt'];
            //根据城市ID获取分单量
            $fendans = D('SalesSetting')->getFendanByDays($startTime,$endTime,$v['id']);
            //分单数均值
            foreach ($fendans as $key => $val) {
                $day = date('d',strtotime($val['days']));
                if($day <= '05'){
                    $vipCount[$k]['fds5'] = $vipCount[$k]['fds5'] + $val['num'];
                }
                if($day <= '10'){
                    $vipCount[$k]['fds10'] = $vipCount[$k]['fds10'] + $val['num'];
                }
                if($day <= '15'){
                    $vipCount[$k]['fds15'] = $vipCount[$k]['fds15'] + $val['num'];
                }
                if($day <= '20'){
                    $vipCount[$k]['fds20'] = $vipCount[$k]['fds20'] + $val['num'];
                }
                if($day <= '25'){
                    $vipCount[$k]['fds25'] = $vipCount[$k]['fds25'] + $val['num'];
                }
                if($day <= '30'){
                    $vipCount[$k]['fds30'] = $vipCount[$k]['fds30'] + $val['num'];
                }
                $vipCount[$k]['fdl'] = $vipCount[$k]['fdl'] + $val['num'];//分单量
            }
            if($vipCount[$k]['city_jhfds'] > $vipCount[$k]['fdl']){
                $vipCount[$k]['isenough'] = 0;
            }else{
                $vipCount[$k]['isenough'] = 1;
            }
            //获取城市分单不足会员数
            $vipFenDans = D('SalesSetting')->getNotEnoughVip($startTime,$endTime,$v['id']);
            $vipCount[$k]['notenough'] = 0;
            foreach ($vipFenDans as $key => $value) {
                if($value['num'] < ($vipCount[$k]['jhfds']*$value['viptype'])){
                    $vipCount[$k]['notenough']++;
                }
            }

            //------------------ 数据入库 -----------------
            //城市
            $data['cid'] = $vipCount[$k]['id'];
            //部门
            $data['dept'] = $vipCount[$k]['dept'];
            //实际总会员数
            $data['vipnum'] = $vipCount[$k]['vipcnt'];
            //计划月分单数
            $data['jhfds'] = $vipCount[$k]['city_jhfds'];
            //月分单
            $data['fdl'] = $vipCount[$k]['fdl'];
            //5日分单数均值
            $data['fds5'] = $vipCount[$k]['fds5'];
            //10日分单数均值
            $data['fds10'] = $vipCount[$k]['fds10'];
            //15日分单数均值
            $data['fds15'] = $vipCount[$k]['fds15'];
            //20日分单数均值
            $data['fds20'] = $vipCount[$k]['fds20'];
            //25日分单数均值
            $data['fds25'] = $vipCount[$k]['fds25'];
            //30日分单数均值
            $data['fds30'] = $vipCount[$k]['fds30'];
            //不足分单城市数
            $data['isenough'] = $vipCount[$k]['isenough'];
            //不足分单会员数
            $data['notenough'] = $vipCount[$k]['notenough'];
            //月份
            $data['month'] = date('m',$startTime);
            //年份
            $data['year'] = date('Y',$startTime);

            //查询是否有本月数据
            // $map['year'] = $data['year'];
            // $map['month'] = $data['month'];
            // $map['dept'] = $data['dept'];
            // $map['cid'] = $data['cid'];
            // $isHaveTheMonthData = M('sales_city_fdsxq')->field('id')->where($map)->find();
            // //var_dump($data);
            // if(empty($isHaveTheMonthData)){
            //     M('sales_city_fdsxq')->add($data);
            // }else{
            //     M('sales_city_fdsxq')->where($map)->save($data);
            // }
            //var_dump($data);
            $fdljd[] = $data;
        }

        return $fdljd; 
    }

    /**
     * Builds a this month vip number.
     *
     * @param      <type>  $startTime  The start time
     */
    public function buildThisMonthVipNum($start=null){
        if (empty($start)) {
            $start = mktime(0,0,0,date("m"),1,date("Y"));
            $end = mktime(23,59,59,date('m',$start),date('t',$start),date('Y',$start));
        }

        //取会员本月合作天数
        $vipCoopDays = D('SalesSetting')->getVipCoopDays($start);


        foreach ($vipCoopDays as $k => $v) {
            $data['vip_days'] = $v['days'];
            $map = array(
                'userid' => array("EQ",$k),
            );
            M('user_company')->where($map)->save($data);
        }

        echo '写入vipdays成功';

        /*
        //获取本月会员数
        $_pauseRefundVip = D('SalesSetting')->getPauseRefundUserNum($start);
        foreach ($_pauseRefundVip as $key => $value) {
            if(!empty($value['pausenum'])){
                $pauseRefundVip['pause'][$value["cs"]] =  $pauseRefundVip['pause'][$value["cs"]] + 1;
            }

            if(!empty($value['refundnum'])){
                $pauseRefundVip['refund'][$value["cs"]] = $pauseRefundVip['refund'][$value["cs"]] + 1;
            }
        }
        */
    }

    /**
     * *****
     *
     * @param      integer  $start  The start time
     * @param      integer  $end    The end time
     *
     * @return     mixed
     */
    private function buildCshyswclData($start = ''){

        if (empty($start)) {
            $start = mktime(0,0,0,date("m"),1,date("Y"));
        }
        $end = mktime(23,59,59,date('m',$start),date('t',$start),date('Y',$start));

        //获取会员数
        $result = D('SalesSetting')->getVipUserCountByCity();
        foreach ($result as $key => $value) {
            $vipCount[$value["cs"]] = $value;
        }

        //获取本月会员数
        $_pauseRefundVip = D('SalesSetting')->getPauseRefundUserNum($start);
        foreach ($_pauseRefundVip as $key => $value) {
            if(!empty($value['pausenum'])){
                $pauseRefundVip['pause'][$value["cs"]] =  $pauseRefundVip['pause'][$value["cs"]] + 1;
            }

            if(!empty($value['refundnum'])){
                $pauseRefundVip['refund'][$value["cs"]] = $pauseRefundVip['refund'][$value["cs"]] + 1;
            }
        }

        //取 城市会员数完成率
        $result = D('SalesSetting')->getCshyswcl($start,$end);

        foreach ($result as $key => $v) {
            $v['vipcnt'] = '0';
            $v['doublecnt'] = '0';
            $v['vipnum'] = '0';
            $v['allVipNum'] = '0';
            $v['pause'] = '0';
            $v['refund'] = '0';
            $v['qqwcl'] = '0';
            $v['hywcl'] = '0';

            if(!empty($vipCount[$v["cid"]])){
                $v['vipcnt'] = $vipCount[$v["cid"]]['vipcnt'];
                $v['vipnum'] = $vipCount[$v["cid"]]['vipnum'];
                if(!empty($vipCount[$v["cid"]]['doublecnt'])){
                    $v['doublecnt'] = $vipCount[$v["cid"]]['doublecnt'];
                }
            }
            if(!empty($pauseRefundVip['pause'][$v["cid"]])){
                $v['pause'] = $pauseRefundVip['pause'][$v["cid"]];
            }
            if(!empty($pauseRefundVip['refund'][$v["cid"]])){
                $v['refund'] = $pauseRefundVip['refund'][$v["cid"]];
            }
            //会员数完成率：实际总会员数 / 会员指标*100%
            if(!empty($v['point'])){
                $v['hywcl'] =  round(($v['vipcnt'] / $v['point'] ) * 100, 1);
            }
            //成员数完成率：实际群成员数 / 群成员数指标*100%
            if(!empty($v['qqpoint']) && !empty($v['qqnum'])){
                $v['qqwcl'] =  round(($v['qqnum'] / $v['qqpoint'] ) * 100, 1);
            }

            $list[$v["cid"]] = $v;
        }
        //var_dump($list);
        //die;

        //数据入库
        foreach ($list as $k => $v) {
            //城市ID
            $data['cid'] = $v['id'];
            //城市区域编码
            $data['cs'] = $v['cid'];
            //城市系数
            $data['csxs'] = $v['ratio'];
            //会员指标
            $data['hyzb'] = $v['point'];
            //实际总会员数
            $data['vipcnt'] = $v['vipcnt'];
            //会员数完成率
            $data['hywcl'] = $v['hywcl'];
            //多倍会员数
            $data['doublecnt'] = $v['doublecnt'];
            //战略会员数
            $data['zhanlue'] = 0;
            //暂停会员数
            $data['pause'] = $v['pause'];
            //退费会员数
            $data['refund'] = $v['refund'];
            //城市QQ群名称
            $data['qqname'] = $v['qqname'];
            //实际群成员数
            $data['qqpoint'] = $v['qqnum'];
            //成员数完成率
            $data['qqwcl'] = $v['qqwcl'];
            //天数
            $data['days'] = date('d',$start);
            //月份
            $data['month'] = date('m',$start);
            //年份
            $data['year'] = date('Y',$start);

            //查询是否有本月数据
            $map['year'] = $data['year'];
            $map['month'] = $data['month'];
            $map['cid'] = $v['id'];

            $isHaveTheMonthData = M('sales_cshyswcl')->field('*')->where($map)->find();

            if(empty($isHaveTheMonthData)){
                M('sales_cshyswcl')->add($data);
            }else{
                $data['days'] = date('d');
                $map['city_id'] = array("EQ",$v['id']);
                M('sales_cshyswcl')->where($map)->save($data);
            }
        }

        return $list;
    }

    /**
     * 生成 城市会员数完成率 并入库
     *
     * @param      integer  $start  The start time
     * @param      integer  $end    The end time
     *
     * @return     mixed
     */
    private function getCshyswclData($map,$pageIndex,$pageCount){

        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $map['start'] = mktime(0,0,0,date("m"),1,date("Y"));
        $map['end'] = mktime(23,59,59,date('m',$map['start']),date('t',$map['start']),date('Y',$map['start']));
        //查询城市基本信息
        $list = D('SalesSetting')->getCityVipConn($map, 'b.px1 AS px',($pageIndex-1)*$pageCount,$pageCount);
        if($list['count'] > $pageCount){
            import('Library.Org.Util.Page');
            $page = new \Page($list['count'],$pageCount);
            $result['page'] =  $page->show();
        }
        $result['list'] = $list['list'];
        $result['totalnum'] = $list['count'];
        //计算总计
        $all = D('SalesSetting')->getCityVipConn($map, 'b.px1 AS px','','');
        $num = count($all['list']);
        foreach ($all['list'] as $k => $v) {
            $result['total']['name'] = '本项合计';// 城市 
            $result['total']['dept'] = '-';// 部门 
            $result['total']['level'] = '-';// 城市重点系数  
            $result['total']['brand_division'] = '-';// 品师长
            $result['total']['brand_regiment'] = '-';// 品团长
            $result['total']['brand_manage'] = '-';// 品牌师 
            $result['total']['dev_division'] = '-';// 拓师长 
            $result['total']['dev_regiment'] = '-';// 拓团长
            $result['total']['dev_manage'] = '-';// 城市经理 
            $result['total']['hyzb'] += $v['hyzb'];// 会员指标    
            $result['total']['vipcnt'] += $v['vipcnt'];// 实际总会员数  
            $result['total']['hywcl'] += ($v['hywcl']/100);
            $result['total']['doublecnt'] += $v['doublecnt'];// 多倍会员数  
            $result['total']['zt'] = '-';// 战略会员数   
            $result['total']['pause'] += $v['pause'];// 暂停会员数
            $result['total']['refund'] += $v['refund'];// 退费会员数
            $result['total']['qqname'] = '-';// 城市QQ群名称  
            $result['total']['qqpoint'] += $v['qqpoint'];// 实际群成员数
            $result['total']['qqwcl'] += ($v['qqwcl']/100);
        }
        $result['total']['hywcl'] = ($result['total']['hywcl'] / $num)*100;// 会员数完成率
        $result['total']['hywcl'] = number_format($result['total']['hywcl'],1,'.','').'%'; 
        $result['total']['qqwcl'] = ($result['total']['qqwcl']/$num)*100;// 成员数完成率
        $result['total']['qqwcl'] = number_format($result['total']['qqwcl'],1,'.','').'%'; 
        //计算本页合计
        $heji_num = count($result['list']);
        foreach ($result['list'] as $k => $v) {
            $result['heji']['name'] = '本页合计';// 城市 
            $result['heji']['dept'] = '-';// 部门 
            $result['heji']['level'] = '-';// 城市重点系数  
            $result['heji']['brand_division'] = '-';// 品师长
            $result['heji']['brand_regiment'] = '-';// 品团长
            $result['heji']['brand_manage'] = '-';// 品牌师 
            $result['heji']['dev_division'] = '-';// 拓师长 
            $result['heji']['dev_regiment'] = '-';// 拓团长
            $result['heji']['dev_manage'] = '-';// 城市经理 
            $result['heji']['hyzb'] += $v['hyzb'];// 会员指标    
            $result['heji']['vipcnt'] += $v['vipcnt'];// 实际总会员数  
            $result['heji']['hywcl'] += ($v['hywcl']/100);
            $result['heji']['doublecnt'] += $v['doublecnt'];// 多倍会员数  
            $result['heji']['zt'] = '-';// 战略会员数   
            $result['heji']['pause'] += $v['pause'];// 暂停会员数
            $result['heji']['refund'] += $v['refund'];// 退费会员数
            $result['heji']['qqname'] = '-';// 城市QQ群名称  
            $result['heji']['qqpoint'] += $v['qqpoint'];// 实际群成员数
            $result['heji']['qqwcl'] += ($v['qqwcl']/100);
        }
        $result['heji']['hywcl'] = ($result['heji']['hywcl'] / $num)*100;// 会员数完成率
        $result['heji']['hywcl'] = number_format($result['heji']['hywcl'],1,'.','').'%'; 
        $result['heji']['qqwcl'] = ($result['heji']['qqwcl']/$num)*100;// 成员数完成率
        $result['heji']['qqwcl'] = number_format($result['heji']['qqwcl'],1,'.','').'%';
        return $result;

        //数据入库
        /*foreach ($list as $k => $v) {
            //城市ID
            $data['cid'] = $v['id'];
            //城市区域编码
            $data['cs'] = $v['cid'];
            //城市系数
            $data['csxs'] = $v['ratio'];
            //会员指标
            $data['hyzb'] = $v['point'];
            //实际总会员数
            $data['vipcnt'] = $v['vipcnt'];
            //会员数完成率
            $data['hywcl'] = $v['hywcl'];
            //多倍会员数
            $data['doublecnt'] = $v['doublecnt'];
            //战略会员数
            $data['zhanlue'] = 0;
            //暂停会员数
            $data['pause'] = $v['pause'];
            //退费会员数
            $data['refund'] = $v['refund'];
            //城市QQ群名称
            $data['qqname'] = $v['qqname'];
            //实际群成员数
            $data['qqpoint'] = $v['qqnum'];
            //成员数完成率
            $data['qqwcl'] = $v['qqwcl'];
            //天数
            $data['days'] = date('d',$start);
            //月份
            $data['month'] = date('m',$start);
            //年份
            $data['year'] = date('Y',$start);

            //查询是否有本月数据
            $map['year'] = $data['year'];
            $map['month'] = $data['month'];
            $map['cid'] = $v['id'];

            $isHaveTheMonthData = M('sales_cshyswcl')->field('*')->where($map)->find();

            if(empty($isHaveTheMonthData)){
                M('sales_cshyswcl')->add($data);
            }else{
                $data['days'] = date('d');
                $map['city_id'] = array("EQ",$v['id']);
                M('sales_cshyswcl')->where($map)->save($data);
            }
        }*/
    }

     /**
     * 生成 会员分单量满足率 并入库
     *
     * @param      integer  $start  The start time
     * @param      integer  $end    The end time 
     * @param      integer  $pageIndex    页码
     * @param      integer  $pageCount    分页条数
     * @param      array    $condition    查询条件数组
     * @return     mixed
     */
    private function buildHyfdlmzlData($start = '', $end, $pageIndex = 1,$pageCount = 20,$condition){
        if (empty($start)) {
            $start = mktime(0,0,0,date("m"),1,date("Y"));
            $end = mktime(23,59,59,date('m',$start),date('t',$start),date('Y',$start));   
        }
        $this_month = date('m');
        $find_month = date('m',$start);
        //取 会员 分单量
        if(!empty($condition['user'])){
            $u_map['com'] = $condition['user'];
        }
        $result = D('SalesSetting')->getUserFendanNum($start,$end,$u_map);
        foreach ($result as $k => $v) {
            $userFendan[$v['com']] = $v['number'];
        }

        //获取 计划月分单数
        if(!empty($condition['c.cid'])){
            $p_map['id'] = $condition['c.cid'];

        }
        $result = D('SalesSetting')->getPlanFendan($p_map);
        foreach ($result as $k => $v) {
            $planFendan[$v['manage_id']] = $v['point'];
        }
        
        //取会员本月合作天数
        if(!empty($condition['user'])){
            $v_map['v.company_id'] = $condition['user'];
        }
        if(!empty($condition['user_name'])){
            $v_map['v.company_name'] = $condition['user_name'];
        }
        $vipCoopDays = D('SalesSetting')->getVipCoopDays($start,$v_map);
        //取城市分单量
        //$result = D('SalesSetting')->getCityFendan($start,$end);
        import('Library.Org.Page.Page');
        //取 城市分单量满足率
        $result = D('SalesSetting')->getHyfdlmzl($start,$end,($pageIndex-1) * $pageCount,$pageCount,$condition);
        $count = $result['count'];
        $list = $result['result'];
        $config  = array("prev","first","last","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        $mzl_data['page'] = $pageTmp;
        $today = date('d');
        foreach ($result['result'] as $key => $v) {
            //dump($v);
            //die;

            //会员实际月分单数
            $v['fendan'] = $userFendan[$v['uid']];

            //计划月分单数
            $v['planFendan'] = $planFendan[$v['cityid']];

            //时间进度比
            if($this_month != $find_month){
                $v['timeProgress'] = '100.0';
            }else{
                $v['timeProgress'] = round(($today / 30) * 100, 1);
                if($v['timeProgress'] > 100){
                    $v['timeProgress'] = 100;
                }
            }
            $v['timeProgress'] = number_format($v['timeProgress'],1,'.','');
            //分单满足率：实际月分单数 / 计划月分单数 / 时间进度比 * 100%
            $v['fdmzl'] = ($v['fendan'] / $v['planFendan'])/($v['timeProgress']/100)*100;
            $v['fdmzl'] = number_format($v['fdmzl'], 1,'.','');

            //本月缺单数：读取当月1日凌晨0：10到次月1日凌晨0：10的实际月份单数-相同时间的计划分单数，数值为正负整数
            $v['byqds'] = round($v['planFendan']-$v['fendan'], 1);

            $v['coopDays'] = $vipCoopDays[$v['uid']]['days'];

            $v['brand_division'] = getSaleUserName($v['brand_division']);
            $v['brand_regiment'] = getSaleUserName($v['brand_regiment']);
            $v['brand_manage'] = getSaleUserName($v['brand_manage']);
            $v['dev_division'] = getSaleUserName($v['dev_division']);
            $v['dev_regiment'] = getSaleUserName($v['dev_regiment']);
            $v['dev_manage'] = getSaleUserName($v['dev_manage']);
            //var_dump($v);
            $mzl_data['list'][$v['uid']] = $v;
        }
        return $mzl_data;
    }

    /**
     * 生成本月城市分单量满足率
     *
     * @param      integer  $start  The start time
     * @param      integer  $end    The end time
     *
     * @return     mixed
     */
    private function buildCsfdlmzlData($condition,$pageIndex,$pageCount){
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
        //查询城市数据
        $result = D('SalesSetting')->getCsfdlmzl($condition,'p.px7 DESC,m.ratio desc,m.id asc',($pageIndex-1)*$pageCount,$pageCount);
        $cache = D('SalesSetting')->getCsfdlmzl($condition,'p.px7 DESC,m.ratio desc,m.id asc',0,1200);
        S("Cache:AllcityFendanMzl",$cache['result'],3600);//生成缓存下载使用
        $count = $result['count'];
        $list = $result['result'];
        import('Library.Org.Page.Page');
        $config  = array("prev","first","last","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        //添加总计、合计
        $all_num = count($cache['result']);
        foreach ($cache['result'] as $k => $v) {
            $total['city'] = '本项合计';// 城市  
            $total['dept'] = '-';// 部门  
            $total['ratio'] = '-';// 城市重点系数  
            $total['brand_division'] = '-';// 品师长     
            $total['brand_regiment'] = '-';// 品团长     
            $total['brand_manage'] = '-';// 品牌师     
            $total['dev_division'] = '-';// 拓师长     
            $total['dev_regiment'] = '-';// 拓团长     
            $total['dev_manage'] = '-';// 城市经理    
            $total['real_vip_num'] += $v['real_vip_num'];// 实际总会员数  
            $total['jhyfds_avg'] += $v['jhyfds_avg'];// 计划月分单数均值
            $total['jhyfds_avg'] = number_format($total['jhyfds_avg'],1,'.','');
            $total['sjyfds_avg_qy'] += $v['sjyfds_avg_qy'];// 实际月分单数均值(全月会员) 
            $total['sjyfds_avg_qy'] = number_format($total['sjyfds_avg_qy'],1,'.','');    
            $total['sjjdb'] = $v['sjjdb'];// 时间进度比   
            $total['fdmzl'] += $v['fdmzl'];// 分单满足率   
            $total['fdzs'] += $v['fdzs'];// 分单总数    
            $total['no_full_vip_num'] += $v['no_full_vip_num'];// 非全月会员数     
            $total['sjyfds_avg_fqy'] += $v['sjyfds_avg_fqy'];// 实际月分单数均值(非全月)
        }
        $total['fdmzl'] = number_format($total['fdmzl']/$all_num,1,'.','');
        $list_num = count($result['result']);
        foreach ($result['result'] as $k => $v) {
            $heji['city'] = '本项合计';// 城市  
            $heji['dept'] = '-';// 部门  
            $heji['ratio'] = '-';// 城市重点系数  
            $heji['brand_division'] = '-';// 品师长     
            $heji['brand_regiment'] = '-';// 品团长     
            $heji['brand_manage'] = '-';// 品牌师     
            $heji['dev_division'] = '-';// 拓师长     
            $heji['dev_regiment'] = '-';// 拓团长     
            $heji['dev_manage'] = '-';// 城市经理    
            $heji['real_vip_num'] += $v['real_vip_num'];// 实际总会员数  
            $heji['jhyfds_avg'] += $v['jhyfds_avg'];// 计划月分单数均值
            $heji['jhyfds_avg'] = number_format($heji['jhyfds_avg'],1,'.','');  
            $heji['sjyfds_avg_qy'] += $v['sjyfds_avg_qy'];// 实际月分单数均值(全月会员)   
            $heji['sjyfds_avg_qy'] = number_format($heji['sjyfds_avg_qy'],1,'.','');  
            $heji['sjjdb'] = $v['sjjdb'];// 时间进度比   
            $heji['fdmzl'] += $v['fdmzl'];// 分单满足率   
            $heji['fdzs'] += $v['fdzs'];// 分单总数    
            $heji['no_full_vip_num'] += $v['no_full_vip_num'];// 非全月会员数     
            $heji['sjyfds_avg_fqy'] += $v['sjyfds_avg_fqy'];// 实际月分单数均值(非全月)
        }
        $heji['fdmzl'] = number_format($heji['fdmzl']/$list_num,1,'.','');

        return array("list"=>$list,"page"=>$pageTmp,"pageCount"=>$count,'total'=>$total,'heji'=>$heji);
    }

    /**
     * 获取历史城市会员数完成率 列表并分页
     *
     * @param      array   $condition  The condition
     * @param      integer  $pageIndex  The page index
     * @param      integer  $pageCount  The page count
     *
     * @return     array   城市列表.
     */
    private function getCshyswclList($condition,$pageIndex = 1,$pageCount = 10){
        import('Library.Org.Page.Page');
        $result = D("SalesSetting")->getCshyswclList($condition,($pageIndex-1) * $pageCount,$pageCount);
        $count = $result['count'];
        $list = $result['result'];
        foreach ($list as $k => $v) {
            $list[$k]['hywcl'] = number_format($v['hywcl'],1,'.','');
            $list[$k]['qqwcl'] = number_format($v['qqwcl'],1,'.','');
        }
        $config  = array("prev","first","last","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();

        //计算总计
        $all = D('SalesSetting')->getCshyswclList($condition,'','');
        $num = count($all['result']);
        foreach ($all['result'] as $k => $v) {
            $result['total']['name'] = '本项合计';// 城市 
            $result['total']['dept'] = '-';// 部门 
            $result['total']['level'] = '-';// 城市重点系数  
            $result['total']['brand_division'] = '-';// 品师长
            $result['total']['brand_regiment'] = '-';// 品团长
            $result['total']['brand_manage'] = '-';// 品牌师 
            $result['total']['dev_division'] = '-';// 拓师长 
            $result['total']['dev_regiment'] = '-';// 拓团长
            $result['total']['dev_manage'] = '-';// 城市经理 
            $result['total']['hyzb'] += $v['hyzb'];// 会员指标    
            $result['total']['vipcnt'] += $v['vipcnt'];// 实际总会员数  
            $result['total']['hywcl'] += ($v['hywcl']/100);
            $result['total']['doublecnt'] += $v['doublecnt'];// 多倍会员数  
            $result['total']['zt'] = '-';// 战略会员数   
            $result['total']['pause'] += $v['pause'];// 暂停会员数
            $result['total']['refund'] += $v['refund'];// 退费会员数
            $result['total']['qqname'] = '-';// 城市QQ群名称  
            $result['total']['qqpoint'] += $v['qqpoint'];// 实际群成员数
            $result['total']['qqwcl'] += ($v['qqwcl']/100);
        }
        $result['total']['hywcl'] = ($result['total']['hywcl'] / $num)*100;// 会员数完成率
        $result['total']['hywcl'] = number_format($result['total']['hywcl'],1,'.','').'%'; 
        $result['total']['qqwcl'] = ($result['total']['qqwcl']/$num)*100;// 成员数完成率
        $result['total']['qqwcl'] = number_format($result['total']['qqwcl'],1,'.','').'%'; 
        //计算本页合计
        $heji_num = count($result['result']);
        foreach ($result['result'] as $k => $v) {
            $result['heji']['name'] = '本页合计';// 城市 
            $result['heji']['dept'] = '-';// 部门 
            $result['heji']['level'] = '-';// 城市重点系数  
            $result['heji']['brand_division'] = '-';// 品师长
            $result['heji']['brand_regiment'] = '-';// 品团长
            $result['heji']['brand_manage'] = '-';// 品牌师 
            $result['heji']['dev_division'] = '-';// 拓师长 
            $result['heji']['dev_regiment'] = '-';// 拓团长
            $result['heji']['dev_manage'] = '-';// 城市经理 
            $result['heji']['hyzb'] += $v['hyzb'];// 会员指标    
            $result['heji']['vipcnt'] += $v['vipcnt'];// 实际总会员数  
            $result['heji']['hywcl'] += ($v['hywcl']/100);
            $result['heji']['doublecnt'] += $v['doublecnt'];// 多倍会员数  
            $result['heji']['zt'] = '-';// 战略会员数   
            $result['heji']['pause'] += $v['pause'];// 暂停会员数
            $result['heji']['refund'] += $v['refund'];// 退费会员数
            $result['heji']['qqname'] = '-';// 城市QQ群名称  
            $result['heji']['qqpoint'] += $v['qqpoint'];// 实际群成员数
            $result['heji']['qqwcl'] += ($v['qqwcl']/100);
        }
        $result['heji']['hywcl'] = ($result['heji']['hywcl'] / $num)*100;// 会员数完成率
        $result['heji']['hywcl'] = number_format($result['heji']['hywcl'],1,'.','').'%'; 
        $result['heji']['qqwcl'] = ($result['heji']['qqwcl']/$num)*100;// 成员数完成率
        $result['heji']['qqwcl'] = number_format($result['heji']['qqwcl'],1,'.','').'%'; 

        return array("list"=>$list,"page"=>$pageTmp,"pageCount"=>$count,'total'=>$result['total'],'heji'=>$result['heji']);
    }

    /**
     * 获取 城市分单量满足率 列表并分页
     *
     * @param      array   $condition  The condition
     * @param      integer  $pageIndex  The page index
     * @param      integer  $pageCount  The page count
     *
     * @return     array   城市列表.
     */
    private function getCsfdlmzlList($condition,$pageIndex = 1,$pageCount = 10){
        import('Library.Org.Page.Page');
        $result = D("SalesSetting")->getCsfdlmzlList($condition,($pageIndex-1) * $pageCount,$pageCount);
        $cache = D("SalesSetting")->getCsfdlmzlList($condition,0,1200);
        S("Cache:AllcityFendanMzl",$cache,3600);//生成缓存下载使用
        $count = $result['count'];
        $list = $result['result'];
        foreach ($list as $k => $v) {
            $list[$k]['jhyfds_avg'] = number_format($v['jhyfds_avg'],1,'.','');
            $list[$k]['sjyfds_avg_qy'] = number_format($v['sjyfds_avg_qy'],1,'.','');
            $list[$k]['sjyfds_avg_fqy'] = number_format($v['sjyfds_avg_fqy'],1,'.','');
            $list[$k]['sjjdb'] = '100.0';// 时间进度比  
            $list[$k]['fdmzl'] = number_format($v['fdmzl'],1,'.','');
        }
        $config  = array("prev","first","last","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();

        //添加总计、合计
        $all_num = count($cache['result']);
        foreach ($cache['result'] as $k => $v) {
            $total['city'] = '本项合计';// 城市  
            $total['dept'] = '-';// 部门  
            $total['ratio'] = '-';// 城市重点系数  
            $total['brand_division'] = '-';// 品师长     
            $total['brand_regiment'] = '-';// 品团长     
            $total['brand_manage'] = '-';// 品牌师     
            $total['dev_division'] = '-';// 拓师长     
            $total['dev_regiment'] = '-';// 拓团长     
            $total['dev_manage'] = '-';// 城市经理    
            $total['real_vip_num'] += $v['real_vip_num'];// 实际总会员数  
            $total['jhyfds_avg'] += $v['jhyfds_avg'];// 计划月分单数均值  
            
            $total['sjyfds_avg_qy'] += $v['sjyfds_avg_qy'];// 实际月分单数均值(全月会员)  
            $total['sjjdb'] = '100.0';// 时间进度比   
            $total['fdmzl'] += $v['fdmzl'];// 分单满足率   
            $total['fdzs'] += $v['fdzs'];// 分单总数    
            $total['no_full_vip_num'] += $v['no_full_vip_num'];// 非全月会员数     
            $total['sjyfds_avg_fqy'] += $v['sjyfds_avg_fqy'];// 实际月分单数均值(非全月)
        }
        $total['fdmzl'] = number_format($total['fdmzl']/$all_num,1,'.','');
        $total['sjyfds_avg_fqy'] = number_format($total['sjyfds_avg_fqy']/$all_num,1,'.','');
        $total['jhyfds_avg'] = number_format($total['jhyfds_avg']/$all_num,1,'.','');
        $total['sjyfds_avg_qy'] = number_format($total['sjyfds_avg_qy']/$all_num,1,'.','');
        $list_num = count($result['result']);
        foreach ($result['result'] as $k => $v) {
            $heji['city'] = '本项合计';// 城市  
            $heji['dept'] = '-';// 部门  
            $heji['ratio'] = '-';// 城市重点系数  
            $heji['brand_division'] = '-';// 品师长     
            $heji['brand_regiment'] = '-';// 品团长     
            $heji['brand_manage'] = '-';// 品牌师     
            $heji['dev_division'] = '-';// 拓师长     
            $heji['dev_regiment'] = '-';// 拓团长     
            $heji['dev_manage'] = '-';// 城市经理    
            $heji['real_vip_num'] += $v['real_vip_num'];// 实际总会员数  
            $heji['jhyfds_avg'] += $v['jhyfds_avg'];// 计划月分单数均值  
            
            $heji['sjyfds_avg_qy'] += $v['sjyfds_avg_qy'];// 实际月分单数均值(全月会员) 
            $heji['sjjdb'] = '100.0';// 时间进度比   
            $heji['fdmzl'] += $v['fdmzl'];// 分单满足率   
            $heji['fdzs'] += $v['fdzs'];// 分单总数    
            $heji['no_full_vip_num'] += $v['no_full_vip_num'];// 非全月会员数     
            $heji['sjyfds_avg_fqy'] += $v['sjyfds_avg_fqy'];// 实际月分单数均值(非全月)
        }
        $heji['fdmzl'] = number_format($heji['fdmzl']/$list_num,1,'.','');
        $heji['sjyfds_avg_fqy'] = number_format($heji['sjyfds_avg_fqy']/$list_num,1,'.','');
        $heji['jhyfds_avg'] = number_format($heji['jhyfds_avg']/$list_num,1,'.','');
        $heji['sjyfds_avg_qy'] = number_format($heji['sjyfds_avg_qy']/$list_num,1,'.','');
        return array("list"=>$list,"page"=>$pageTmp,"pageCount"=>$count,'total'=>$total,'heji'=>$heji);
    }

    /**
     * 下载Excel
     *
     * @param      array  $title     The title
     * @param      array  $column    The column
     * @param      array  $list      The list
     * @param      string  $filename  The filename
     *
     * @return     mixed
     */
    private function downExcel($title,$column,$list,$filename){
        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array ( 'cacheTime' => 300 );
        \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );
        $phpExcel = new \PHPExcel();

        $i = 0;
        foreach ($title as $key => $value) {
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . 1;
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value);
        }

        $dept = array('1'=>'商务','2'=>'外销');
        $brands = array('brand_division','brand_regiment','brand_manage','dev_division','dev_regiment','dev_manage');

        //设置表内容
        $j = 1;
        foreach ($list as $k => $v) {
            //初始化$i
            $i = 0;

            if(!empty($v['dept'])){
                $v['dept'] = $dept[$v['dept']];
            }

            foreach ($v as $key => $value) {
                if(in_array($key,$brands)){
                    $v[$key] = getSaleUserName($value);
                }
            }

            foreach ($column as $key => $value) {
                $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
                $phpExcel->getActiveSheet()->setCellValue($num,(string)$v[$value]);
            }

            $j++;
        }



        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="'.$filename.'.xlsx"');
        header("Content-Transfer-Encoding:binary");
        $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save('php://output');
        exit();
    }
}