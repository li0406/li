<?php

namespace Home\Model;
Use Think\Model;

class OrdersrankModel extends Model{

    protected $autoCheckFields = false;

    /**
     * 取出每天的会员数量
     *
     * @param      <type>  $date   The date
     *
     * @return     <type>  The city vip list by day.
     */
    public function getCityVipByDay($start,$end=''){
        if(empty($end)){
            $map['time'] = array('EQ',$start);
        }else{
            $map['time'][] = array('EGT',$start);
            $map['time'][] = array('ELT',$end);
        }

        return M('log_user_real_company')
                    ->field("companys,city_id,vip_count,vip_num,date_format(time,'%Y-%m-%d') as date")
                    ->where($map)
                    ->order('city_id,date')
                    ->select();
    }

    /**
     * 取当天的所有分单
     *
     * @param      string  $date   The date
     *
     * @return     <type>  The city order by day.
     */
    public function getCityOrderByDay($date){

        $start = strtotime($date.' 0:0:0');
        $end = strtotime($date.' 23:59:59');

        $map['o.addtime'][] = array('EGT',$start);
        $map['o.addtime'][] = array('LT',$end);
        $map['o.type_fw'] = array('EQ',1);

        return M('order_info')->alias('o')
                    ->field("com, date_format(FROM_UNIXTIME(addtime),'%Y-%m-%d') as date,u.cs,count(o.id) as count")
                    ->join('inner join qz_user as u on u.id = o.com')
                    ->where($map)
                    ->group('u.cs,date')
                    ->select();
    }

    /**
     * Gets the orders assign average.
     *
     * @param      <type>  $date   The date
     *
     * @return     <type>  The orders assign average.
     */
    public function getOrdersAssignAvg($start,$end = ''){
        
        if(!empty($end)){
            $map['date'][] = array('EGT',$start);
            $map['date'][] = array('ELT',$end);
        }else{
            $map['date'] = array('EQ',$start);
        }

        return M('orders_assign_avg')->field('*')->where($map)->order('city_id')->select();
    }

    /**
     * 计算每个月每个城市的每天发单量
     * 
     * @param  [type] $start [description]
     * @param  [type] $end   [description]
     * @return [type]        [description]
     */
    public function getOrderInfoByCityDay($start,$end,$endDay){
        $sql = 'select
            t.cs,t.date,
            count(if(t.on = 4 and t.type_fw = 1,1,null)) as fen,
            count(if(t.on = 4 and t.type_fw = 2,1,null)) as zen,
            count(t.id) as count
            from (
                select
                o.id,o.type_fw,o.on,
                case
                when o.time_real >= UNIX_TIMESTAMP(CONCAT(FROM_UNIXTIME(o.time_real,"%Y-%m-%d")," ","17:30:00"))
                and o.time_real < UNIX_TIMESTAMP(CONCAT(DATE_ADD(FROM_UNIXTIME(o.time_real,"%Y-%m-%d"),INTERVAL 1 day)," ","23:59:59"))
                then DATE_ADD(FROM_UNIXTIME(o.time_real,"%Y-%m-%d"),INTERVAL 1 day)
                else FROM_UNIXTIME(o.time_real,"%Y-%m-%d")
                end as date,
                o.cs
                from qz_orders o
                where o.time_real >= UNIX_TIMESTAMP(CONCAT(DATE_SUB("'.$start.'",INTERVAL 1 DAY)," ","17:30:00")) and o.time_real <= UNIX_TIMESTAMP(CONCAT("'.$end.'"," ","17:30:00"))
                and o.cs <> "000001"
            ) t
            group by t.cs,t.date
            order by t.cs,t.date';
        $result = M()->query($sql);

        //合并结果集,计算每天的发单量、分单量、分单率
        foreach ($result as $key => $value) {
            $list[$value['cs']][$value['date']] = array(
                'fen' => $value['fen'],'zen' => $value['zen'],'count' => $value['count'],
            );
            if ($endDay >= $value["date"]) {
                $count[$value["cs"]]["fen"] += $value["fen"];
                $count[$value["cs"]]["count"] += $value["count"];
            }
        }

        return array('count'=>$count,'list'=>$list);
    }



    /**
     * Gets the start time by city.
     *
     * @param      string  $start  The start
     * @param      string  $end    The end
     */
    public function getStartTimeByCity($start,$end){

        $userMap['a.ON'] = '2';
        $userMap['a.classid'] = '3';

        $map = array(
            array(
                "v.start_time" => array("LT",$start),
                "v.end_time"   => array("GT",$end)
            ),
            array(
                array(
                    "v.start_time" => array("EGT",$start),
                ),
                array(
                    "v.start_time" => array("ELT",$end),
                ),
            ),
            array(
                array(
                    "v.end_time" => array("EGT",$start),
                ),
                array(
                    "v.end_time" => array("ELT",$end),
                ),
            ),
            "_logic"=>"OR"
        );

        $buildSql = M('user')->alias('a')
                    ->field('a.id,a.cs')
                    ->join('INNER JOIN qz_user_company b ON a.id = b.userid AND b.fake = 0')
                    ->where($userMap)
                    ->buildSql();

        $result = M()->table($buildSql)->alias('t')
                        ->field('t.id,t.cs,v.start_time,v.end_time')
                        ->join('qz_user_vip v on v.company_id = t.id and v.type in (2,8)')
                        ->where($map)
                        ->order('v.id')
                        ->select();

        //将会员合同合并
        foreach ($result as $key => $value) {
            $vip_company[$value['id']][] = $value;
        }

        foreach ($vip_company as $key => $value) {
            //如果一个月内出现多份合同
            if (count($value) > 1) {
                $start_time = null;
                $end_time = null;
                foreach ($value as $k => $val) {
                    if (empty($end_time)) {
                        $start_time = $val["start_time"];
                        //第一份合同的结束时间
                        $end_time = $val["end_time"];
                    } else {
                        //如果上一份合同的结束时间+1天是第二份合同的开始时间，表示有关联
                        if (strtotime("+1 day",strtotime($end_time)) != strtotime($val["start_time"])) {
                            $start_time = $val["start_time"];
                        }
                        $end_time = $val["end_time"];
                        $vip_company[$key]["start"] = $start_time;
                        $vip_company[$key]["end"] = $end_time;
                    }
                }
            } else {
                $vip_company[$key]["start"] = $value[0]["start_time"];
                $vip_company[$key]["end"] = $value[0]["end_time"];
            }
        }

        //合并城市中会员公司的合同时间
        foreach ($vip_company as $key => $value) {
            if (!array_key_exists($value[0]["cs"],$list)) {
                $list[$value[0]["cs"]]["start"] = $value["start"];
                $list[$value[0]["cs"]]["end"] = $value["end"];
            } else {
                if ($value["start"] < $list[$value[0]["cs"]]["start"] ) {
                    $list[$value[0]["cs"]]["start"] = $value["start"];
                }

                if ($value["end"] > $list[$value[0]["cs"]]["end"]) {
                    $list[$value[0]["cs"]]["end"] = $value["end"];
                }
            }
        }
        return $list;
    }






    //-------------------------------------------------------------------------------

    /**
     * Gets the citys vip number.
     *
     * @return     array  The city vip number.
     */
    public function getCityVipNum(){
        $map['a.classid'] = 3;
        $map['b.fake'] = 0;

        $_result = M('user')->alias('a')->field('a.cs,sum(if(a.on = 2,b.viptype,null)) as vipcnt')
                    ->join('inner join qz_user_company b on a.id = b.userid')
                    ->where($map)
                    ->group('a.cs')
                    ->order('vipcnt desc')
                    ->select();

        foreach ($_result as $k => $v) {
            $result[$v['cs']] = $v['vipcnt'];
        }

        return $result;
    }
    
    /**
     * 获取 上个月订单数
     *
     * @param      <type>  $date   The date
     *
     * @return     <type>  The previous month order.
     */
    public function getPrevMonthOrder($date){
        $result = S("C:OrderRank:PrevMonthOrders:".$date);
        if(empty($result)){
            //上个月时间段
            $end = strtotime('-1 day',mktime(23,59,59,date('m',$date),1,date('Y',$date)));
            $start = mktime(0,0,0,date('m',$end),1,date('Y',$end));
            //取上个月订单数
            $result = D('Ordersrank')->getCityOrderNumByTime($start,$end);
            S("C:OrderRank:PrevMonthOrders:".$date,$result,86400);
        }
        return $result;
    }

    /**
     * 获取 上个月订单数
     *
     * @param      <type>  $date   The date
     *
     * @return     <type>  The previous month order.
     */
    public function getThisMonthOrder($start){
        $result = S("C:OrderRank:thisMonthOrder:".$date);
        if(empty($result)){
            //时间段
            $start = strtotime($start);

            $end = I('get.date');            
            if(empty($end)){
                $end = date('Y-m-d');
            }
            $end = strtotime($end);

            //如果查询日等于当天
            if(date('Y',$end).date('m',$end).date('d',$end) == date('Ymd')){
                $end = time();
            }else{
                $end = mktime(23,59,59,date('m',$end),date('d',$end),date('Y',$end));
            }
            //取本月订单数
            $result = D('Ordersrank')->getCityOrderNumByTime($start,$end);        
            S("C:OrderRank:thisMonthOrder:".$date,$result,600);
        }
        return $result;
    }

    /**
     * 获取 5天内上会员数量和下会员数量
     *
     * @param      <type>  $end_day  The end day
     */
    public function getDownUpVipNum($end_day){
        $result = S("C:OrderRank:downUpVipNum:".$end_day);
        if(!empty($result)){
            return $result;
        }

        $dynamic_end = date('Y-m-d',strtotime("+1 day",strtotime($end_day)));
        $dynamic_start = date('Y-m-d',strtotime("-5 day",strtotime($dynamic_end)));
        $upmap = array(
            'v.start_time' => array(
                array('EGT', $dynamic_start),
                array('LT', $dynamic_end),
                'AND'
            ),
            'v.type' => array(
                array('EQ', 2),
                array('EQ', 8),
                'OR'
            )
         );
        $result =  M('user_vip')->alias('v')
                    ->field('u.cs, count(u.id) AS up_count')
                    ->join('INNER JOIN qz_user AS u ON u.id = v.company_id')
                    ->where($upmap)
                    ->group('u.cs,u.id')
                    ->select();
        $upvip = [];
        foreach ($result as $key => $value) {
            $upvip[$value['cs']] = $value;
        }

        $downmap = array(
            'v.end_time' => array(
                array('GT', $dynamic_start),
                array('ELT', $dynamic_end),
                'AND'
            )
        );
        $result =  M('user_vip')->alias('v')
                    ->field('u.cs, count(u.id) AS down_count')
                    ->join('INNER JOIN qz_user AS u ON u.id = v.company_id')
                    ->where($downmap)
                    ->group('u.cs,u.id')
                    ->select();
        $downvip = [];
        foreach ($result as $key => $value) {
            $downvip[$value['cs']] = $value;
        }

        $result = array(
            'downvip' => $downvip,
            'upvip' => $upvip
        );

        S("C:OrderRank:downUpVipNum:".$date,$result,86400);
        return $result;
    }

    /**
     * 获取 近三个月订单数
     *
     * @param      <type>  $date   The date
     *
     * @return     <type>  Three month order.
     */
    public function getThreeMonthOrder($date){
        $result = S("C:OrderRank:ThreeMonthOrders:".$date);
        if(empty($result)){

            $start = strtotime('-3 month',$date);
            $start = mktime(0,0,0,date('m',$start),1,date('Y',$start));
            $end = strtotime('-1 day',mktime(23,59,59,date('m',$date),1,date('Y',$date)));

            $result = D('Ordersrank')->getCityOrderNumByTime($start,$end);
            S("C:OrderRank:ThreeMonthOrders:".$date,$result,86400);
        }
        return $result;
    }

    /**
     * 获取 当天订单数
     *
     * @param      <type>  $date   The date
     *
     * @return     <type>  The previous month order.
     */
    public function getTodayOrder($date){
        $result = S("C:OrderRank:TodayOrders:".$date);
        if(empty($result)){
            //定义当天时间段
            if(date("H") < 18){
                $time['start'] = strtotime('-2 day',mktime(18,0,0,date('m',$date),date('d',$date),date('Y',$date)));
                $time['end'] = strtotime('-1 day',mktime(18,0,0,date('m',$date),date('d',$date),date('Y',$date)));
            }else{
                $time['start'] = strtotime('-1 day',mktime(18,0,0,date('m',$date),date('d',$date),date('Y',$date)));
                $time['end'] = mktime(18,0,0,date('m',$date),date('d',$date),date('Y',$date));
            }

            //取当天订单数
            $result = D('Ordersrank')->getCityOrderNumByTime($time['start'],$time['end']);
            S("C:OrderRank:TodayOrders:".$date,$result,86400);
        }
        return $result;
    }

    /**
     * 获取 当年订单数
     *
     * @param      <type>  $date   The date
     *
     * @return     <type>  The year order.
     */
    public function getYearOrder($date){
        $result = S("C:OrderRank:YearOrders:".$date);
        if(empty($result)){

            //财年时间为2月1日到次年的1月31日为一个完整财年
            $time['start'] = mktime(0,0,0,2,1,$date);
            $time['end'] = mktime(23,59,59,1,31,$date + 1);

            $_result = D('Ordersrank')->getCityOrderNumByYear($time['start'],$time['end']);
            $result = array();
            foreach ($_result as $k => $v) {

                if(empty($result[$v['cs']])){
                    unset($v['year']);
                    $result[$v['cs']] = $v;
                }else{
                    $result[$v['cs']]['num'] = $result[$v['cs']]['num'] + $v['num'];
                    $result[$v['cs']]['fen'] = $result[$v['cs']]['fen'] + $v['fen'];
                }
            }
            unset($_result);
            S("C:OrderRank:YearOrders:".$date,$result,86400);
        }
        return $result;
    }

    /**
     * 获取 城市某个时间段 订单/分单 数
     *
     * @param      <type>  $start  The start time
     * @param      <type>  $end    The end time
     *
     * @return     <type>  The order number by month.
     */
    public function getCityOrderNumByTime($start,$end){

        //$map['on'] = array('EQ',4);
        //$map['type_fw'] = array('EQ',1);
        $map['time_real'][] = array('EGT',$start);
        $map['time_real'][] = array('ELT',$end);

        $sql = M('orders')->field('FROM_UNIXTIME(time_real,"%Y") AS years,count(id) as num,SUM(CASE type_fw WHEN 1 THEN 1 ELSE 0 END) AS fendan,cs')
                            ->where($map)
                            ->group('years,cs')
                            ->buildSql();

        $_result = M()->table($sql)->alias('o')
                        ->field('o.*,q.cname')
                        ->join('LEFT JOIN qz_quyu as q on q.cid = o.cs')
                        ->select();

        //合并分年数据
        $result = array();
        foreach ($_result as $k => $v) {
            if(empty($result[$v['cs']])){
                unset($v['year']);
                $result[$v['cs']] = $v;
            }else{
                $result[$v['cs']]['num'] = $result[$v['cs']]['num'] + $v['num'];
                $result[$v['cs']]['fendan'] = $result[$v['cs']]['fendan'] + $v['fendan'];
            }
        }
        unset($_result);
        return $result;
    }

    /**
     * 获取 财年订单和分单数
     *
     * @param      <type>  $start  The start
     * @param      <type>  $end    The end
     */
    public function getCityOrderNumByYear($start,$end){
        $map['time_real'][] = array('EGT',$start);
        $map['time_real'][] = array('ELT',$end);

        return M('orders')
                ->field('FROM_UNIXTIME(time_real,"%Y") AS year,cs,count(id) as num,SUM(CASE type_fw WHEN 1 THEN 1 ELSE 0 END) AS fen')
                ->where($map)
                ->group('year,cs')
                ->select();
    }

    /**
     * 获取 所有后台可见区域
     *
     * @return     array  The quyu list.
     */
    public function getQuyu(){
        $result = S("C:OrderRank:Quyu");
        if(empty($result)){
            $map['type'] = array('EQ',1);
            $result = M('quyu')->field('cid,cname,bm,px,px_abc,little,mark_red,manager,point')->where($map)->order('px_abc')->select();
            S("C:OrderRank:Quyu",$result,86400);
        }
        return $result;
    }

    /**
     * 取会员 时间段内 合作天数
     *
     * @param      integer  $start      The start time
     * @return     array  The user list.
     */
    public function getLastFiveDayVipCoop($date){
        //$result = S("C:OrderRank:LastFiveDayVipCoop");
        if(!empty($result)){
            return $result;
        }

        $map['v.type'] = array('IN','2,8');

        //定义开始时间 TODO
        if(date("H") < 18 && date('Y-m-d',$date) == date('Y-m-d')){
            $start = strtotime('-5 day',mktime(0,0,0,date('m',$date),date('d',$date),date('Y',$date)));
            $end = strtotime('-1 day',mktime(23,59,59,date('m',$date),date('d',$date),date('Y',$date)));
        }else{
            $start = strtotime('-4 day',mktime(0,0,0,date('m',$date),date('d',$date),date('Y',$date)));
            $end = mktime(0,0,0,date('m',$date),date('d',$date),date('Y',$date));
        }

        $map['v.start_time'] = array('ELT',date('Y-m-d',$end));
        $map['v.end_time'] = array('EGT',date('Y-m-d',$start));

        $_result = M('user_vip')->alias('v')
                ->field('v.company_id,v.type,v.start_time,v.end_time,u.cs')
                ->join('INNER JOIN qz_user as u ON u.id = v.company_id')
                ->where($map)
                ->order('u.cs,v.id')
                ->select();

        foreach ($_result as $k => $v) {
            $theStart = strtotime($v['start_time']);
            $theEnd = strtotime($v['end_time']);

            //2017-12-25 需求：城市会员公司将掉光或新上才标识'新' 和 '到'
            /*
            若某城市中所有会员公司即将掉光（该城市即将没有会员公司），则显示“到”标识，而不是只要有一个即将到期的会员就显示该标识。
            若某城市的新上会员公司将该城市从没有任何会员公司变为拥有会员公司，则显示“新”标识，天数为该会员公司新上天数，而不是只要有新上会员公司就标记“新”。
            */
            
            //取 最后到期 会员天数 - 如果本城市会员结束时间大于本时间段
            if(empty($expireNewVip[$v['cs']]['expire'])){                
                if($theEnd >= $end){
                    $expireNewVip[$v['cs']]['expire'] = 'ok';
                }
            }

            //取 最早开始 会员天数 - 如果会员开始时间小于本时间段
            if(empty($expireNewVip[$v['cs']]['new'])){                
                if($theStart <= $start){
                    $expireNewVip[$v['cs']]['new'] = 'ok';
                }
            }

            //获取城市会员 新 和 到数据
            if(!empty($city[$v['cs']])){
                //取最后到期会员天数 - 如果会员结束时间小于本时间段，并大于本城市会员结束时间
                if($theEnd <= $end && $theEnd > $city[$v['cs']]['expire']){
                    $city[$v['cs']]['expire'] = $theEnd;
                }
                //取 最早 开始会员天数 - 如果会员开始时间小于本时间段，并大于本城市会员结束时间
                if($theStart >= $start && $theStart < $city[$v['cs']]['new']){
                    $city[$v['cs']]['new'] = $theStart;
                }
            }else{
                if($theEnd <= $end){
                    $city[$v['cs']]['expire'] = $theEnd;
                }
                if($theStart >= $start){
                    $city[$v['cs']]['new'] = $theStart;
                }
            }

            if($theEnd <= $end){
                $vipTrends[$v['cs']]['expire']++;
            }
            if($theStart >= $start){
                $vipTrends[$v['cs']]['new']++;
            }

            //获取当前 城市会员数
            $vipNum[$v['cs']] ++ ;
        }

        foreach ($city as $k => $v) {
            if(!empty($v['expire'])){
                $v['expire'] = mktime(18,0,0,date('m',$v['expire']),date('d',$v['expire']),date('Y',$v['expire']));
                $newcity[$k]['expire'] = floor(($end - $v['expire']) / 86400);
            }
            if(!empty($v['new']) && empty($v['expire'])){
                $v['new'] = mktime(18,0,0,date('m',$v['new']),date('d',$v['new']),date('Y',$v['new']));
                $newcity[$k]['new'] = floor(($v['new'] - $start) / 86400);
            }
        }

        $result = array('upDownVip'=>$newcity,'vipTrends'=>$vipTrends,'vipNum'=>$vipNum,'expireNewVip'=>$expireNewVip);

        //S("C:OrderRank:LastFiveDayVipCoop",$result,3600);

        return $result;
    }


    /**
     * Gets the last vip time.
     *
     * @return     <type>  The last vip time.
     */
    public function getLastVipTime(){
        $sql = M('user_vip')->alias('v')
                            ->field('v.start_time,v.end_time,u.cs')
                            ->join('LEFT JOIN qz_user as u on u.id = v.company_id')
                            ->order('v.start_time desc,v.end_time desc')
                            ->buildSql();

        $_result = M()->table($sql)->alias('t')
                        ->field('t.*')
                        ->group('t.cs')
                        ->select();

        foreach ($_result as $k => $v) {
            $result[$v['cs']] = $v;
        }
        return $result;
    }

    /**
     * 获取 城市会员信息
     *
     * @param      <type>  $cid    The cid
     *
     * @return     <type>  The vip list by city.
     */
    public function getVipListByCity($cid){

        $map['a.cs'] = $cid;
        $map['a.classid'] = 3;
        $map['a.on'] = array('NEQ',0);

        return M('user')->alias('a')
                        ->field("a.id,a.jc,a.on,b.saler,b.fake,a.start,a.end,b.contract_start as allstart,b.contract_end as allend")
                        ->join('join qz_user_company b on a.id = b.userid')
                        ->where($map)
                        ->order('b.fake,a.on desc,a.end,a.id')
                        ->select();
    }



    /**
     * 新上会员城市信息
     *
     * @param      <type>  $date   The date
     *
     * @return     <type>  The new vip city.
     */
    public function getNewVipCity($date){
        $result = S("C:OrderRank:NewVipCity:".$date);
        if(!empty($result)){
            return $result;
        }

        $day = date("Y-m-d",$date);
        //当前下午6点之前统计前一天的
        $sql = "select
                t1.city_id,
                if(DATEDIFF(curdate(),t1.`start`) <= 5,1,0) as mark,
                DATEDIFF(curdate(),t1.`start`) as day_diff
                from (
                        select
                        t.city_id,
                        MIN(u.`start`) as `start`
                        from (
                                select a.city_id,a.companys as `new` , b.companys as `old` from qz_log_user_real_company a
                                left join qz_log_user_real_company b on a.city_id = b.city_id and b.time = last_day(DATE_SUB('$day',INTERVAL 1 MONTH))
                                where a.time = '$day' and b.companys is null
                        )t join qz_user u on FIND_IN_SET(u.id,t.new)
                        group by city_id
                ) t1 ";
        $result = M()->query($sql);
        if (count($result) > 0) {
            foreach ($result as $key => $value) {
                if ($value["mark"] == 1) {
                   $list[$value["city_id"]] = $value;
                }
            }
        }

        S("C:OrderRank:NewVipCity:".$date,$list,86400);
        return $list;
    }

    /**
     * 即将结束城市会员信息
     *
     * @param      <type>  $date   The date
     *
     * @return     <type>  The end vip city.
     */
    public function getEndVipCity($date){
        $day = date("Y-m-d",$date);
        $month_start = date("Y-m-d",mktime(0,0,0,date("m",$date),1,date("Y",$date)));
        $month_end = date("Y-m-d",mktime(0,0,0,date("m",$date),date("t",$date),date("Y",$date)));
        $sql = "select
                t.cs as city_id,
                DATEDIFF(t.`end`,curdate()) as day_diff,
                if(DATEDIFF(t.`end`,curdate()) <= 5 and DATEDIFF(t.`end`,curdate()) >= 0,1,0) as mark
                from (
                    select b.cs,b.id,MAX(b.end) as `end`,a.vip_count
                    from qz_log_user_real_company a
                    join qz_user b on FIND_IN_SET(b.id,a.companys) and  b.on = 2
                    where a.time = '$day'
                    group by b.cs
                ) t
                where t.end >= '$month_start' and t.end <= '$month_end' ";
        $result = M()->query($sql);
        if (count($result) > 0) {
            foreach ($result as $key => $value) {
                if ($value["mark"] == 1) {
                   $list[$value["city_id"]] = $value;
                }
            }
        }
        return $list;
    }

    public function getManagerCityByGid($gid){
        $map['role_id'] = array('EQ',$gid);

        return M('role_quyu')->alias('r')
                            ->field('q.cid')
                            ->join('qz_quyu as q ON q.id = r.quyu_id')
                            ->where($map)
                            ->select();
    }

    //获取城市帐户信息
    public function getCityAccounts($map){
        $_result = M('quyu')->field('cid,baidu_account')->order('px_abc')->select();

        //不判断城市是否为空
        foreach ($_result as $key => $value) {
            $result[$value['cid']] = $value['baidu_account'];
        }
      
        return $result;
    }

}