<?php

namespace Home\Model;
Use Think\Model;

/**
* 销售系统设置
*/

class SalesSettingModel extends Model
{
    protected $autoCheckFields = false;



    /**
     * Gets the city manage list.
     *
     * @param      array          $condition  The condition
     * @param      integer|string  $pagesize   The pagesize
     * @param      integer         $pageRow    The page row
     *
     * @return     array          The city manage list.
     */
    public function getCityManageList($map,$pagesize= 0,$pageRow = 10){

        $count  = M('sales_city_manage')->where($map)->count();
        $result = M('sales_city_manage')->field('*')
                      ->order($condition['orderBy'])
                      ->limit($pagesize.",".$pageRow)
                      ->where($map)
                      ->select();
        return array("result"=>$result,"count"=>$count);
    }

    /**
     * 获取 销售系统设置值 列表并分页
     *
     * @param      array          $condition  The condition
     * @param      integer|string  $pagesize   The pagesize
     * @param      integer         $pageRow    The page row
     *
     * @return     array          The setting value list.
     */
    public function getSettingValueList($condition,$pagesize= 1,$pageRow = 10){

        $count  = M('sales_setting_value')->alias("s")
                    ->join("left join qz_sales_city_manage as c on s.manage_id = c.id")
                    ->join("left join qz_adminuser as u on s.uid = u.id")
                    ->where($condition)->count();

        $result = M('sales_setting_value')->alias("s")
                      ->field('s.*,c.city,c.level,c.dept,u.name')
                      ->join("left join qz_sales_city_manage as c on s.manage_id = c.id")
                      ->join("left join qz_adminuser as u on s.uid = u.id")
                      ->order('s.lasttime desc')
                      ->limit($pagesize.",".$pageRow)
                      ->where($condition)
                      ->select();
        return array("result"=>$result,"count"=>$count);
    }

    /**
     * 获取 会员新签续费互转 列表
     *
     * @param      array          $condition  The condition
     * @param      integer|string  $pagesize   The pagesize
     * @param      integer         $pageRow    The page row
     *
     * @return     array          The setting value list.
     */
    public function getHyxqxfhzList($condition,$pagesize= 1,$pageRow = 10){

        $condition['s.module'] = '5';

        $count  = M('sales_setting_value')->alias("s")
                  ->join("LEFT JOIN qz_user as u on u.id = s.manage_id")
                  ->join("LEFT JOIN qz_quyu q on q.cid = u.cs")
                  ->join("LEFT JOIN qz_sales_city_manage m on m.city = q.cname")
                  ->join("LEFT JOIN qz_user_company b on b.userid = u.uid")
                  ->where($condition)
                  ->count();

        $result = M('sales_setting_value')->alias("s")
                  ->field("s.id as sid,s.manage_id,s.point,s.remark,s.uid,s.lasttime,u.`user`,u.jc,u.cs,q.cname,FROM_UNIXTIME(b.contract_start,'%Y-%m-%d') as allstart,FROM_UNIXTIME(b.contract_end,'%Y-%m-%d') as allend,m.*")
                  ->join("LEFT JOIN qz_user as u on u.id = s.manage_id")
                  ->join("LEFT JOIN qz_quyu q on q.cid = u.cs")
                  ->join("LEFT JOIN qz_sales_city_manage m on m.city = q.cname")
                  ->join("LEFT JOIN qz_user_company b on b.userid = u.id")
                  ->where($condition)
                  ->order('s.lasttime desc')
                  ->limit($pagesize.",".$pageRow)
                  ->select();

        return array("result"=>$result,"count"=>$count);
    }

    /**
     * 检测城市是否存在
     *
     * @param      string  $cityname  The cityname
     *
     * @return     array    city array
     */
    public function checkCitys($cityname){
        $map = array(
            'city' => array("EQ",$cityname)
        );
        return M('sales_city_manage')->field('*')->where($map)->find();
    }

    /**
     * 获取会员分单量
     *
     * @param      integer  $startTime  The start time
     * @param      integer  $endTime    The end time
     * @param      array    $map        公司ID（com字段）
     * @return     array    分单信息
     */
    public function getUserFendanNum($startTime,$endTime,$map){

        $map = array(
            'addtime' => array('between',array($startTime,$endTime)),
            'type_fw' => array("EQ",'1')
        );

        return M('order_info')
                ->field('com,count(id) AS number')
                ->where($map)
                ->group('com')
                ->select();
    }





    /**
     * 取销售帐号列表
     * @return     array  The sale users.
     */
    public function getSaleUsers(){
        /*
        7         督导
        56        品牌师
        61        品牌督导
        62        品牌经理
        36        商务拓展督导
        39        商务经理
        40        商务文员
        41        商务品牌督导
        42        商务品牌师
        43        商务拓展城市经理
        45        商务助理
        72        商务拓展经理
        77        商务品牌经理
        71        拓展经理
        58        外销区域经理
        59        外销经理
        65        外销实习
        67        外销助理
        3         销售
        46        销售副总
        73        销售培训
        60        招商经理
        */
        $map = array(
            'uid' => array("IN",'1,7,56,61,62,36,39,40,41,42,43,45,72,77,71,58,59,65,67,3,46,73,60,37'),
            'stat' => array('EQ',1)
        );
        return M('adminuser')->field('id,uid,user,name')->where($map)->order('uid')->select();
    }

    /**
     * 按城市名 取城市职能管辖信息
     *
     * @param      string  $city   The city name
     *
     * @return     array  城市信息
     */
    public function getCityManageByName($city){
        $map = array(
            'city' => array("EQ",$city)
        );
        return M('sales_city_manage')->field('*')->where($map)->find();
    }

    /**
     * 编辑 销售系统设置值表
     *
     * @param      integer  $id     The identifier
     * @param      array    $data   The data
     *
     * @return     mixed    boolean
     */
    public function editSettingValue($id,$data){
        $map = array(
            'id' => array("EQ",$id),
        );
        return  M('sales_city_manage')->where($map)->save($data);
    }

    /**
     * 编辑 销售系统设置值表-计划月分单数
     *
     * @param      integer  $id     The identifier
     * @param      array    $data   The data
     *
     * @return     mixed    boolean
     */
    public function editSettingCityValues($id,$data){
        $map = array(
            'id' => array("EQ",$id),
        );
        return  M('sales_setting_value')->where($map)->save($data);
    }

    /**
     * Gets the manage citys list.
     *
     * @return     array  The manage citys.
     */
    public function getManageCitys(){
        return M('sales_city_manage')->alias("m")
                    ->field('m.*,q.cid,q.cname,q.bm')
                    ->join('left join qz_quyu as q on q.cname = m.city')
                    ->order('bm')
                    ->select();
    }

    /**
     * 按城市取会员数
     *
     * @return     array  城市会员数
     */
    public function getVipUserCountByCity(){
        $map = array(
            'b.fake' => array("EQ",'0'),
            'a.classid' => array("EQ",'3'),
            'a.on' => array("EQ",'2')
        );

        return M('user')->alias("a")
                    ->field('a.cs,sum(if(a.on = 2,b.viptype,null)) as vipcnt,sum(if(b.viptype > 1,(b.viptype-1),null)) as doublecnt,count(if(a.on = 2,a.id,null)) as vipnum')
                    ->join('inner join qz_user_company b on a.id = b.userid')
                    ->where($map)
                    ->group('a.cs')
                    ->order('vipcnt DESC')
                    ->select();
    }

    /**
     * 取会员数
     *
     * @return     array  会员数
     */
    public function getVipUserCount(){
        $map = array(
            'b.fake' => array("EQ",'0'),
            'a.classid' => array("EQ",'3'),
            'a.on' => array("EQ",'2')
        );

        return M('user')->alias("a")
                    ->field('sum(if(a.on = 2,b.viptype,null)) as vipcnt,sum(if(b.viptype > 1,(b.viptype-1),null)) as doublecnt,count(if(a.on = 2,a.id,null)) as vipnum,count(if(b.vip_days >= 26,1,null)) as fullvip')
                    ->join('inner join qz_user_company b on a.id = b.userid')
                    ->where($map)
                    ->order('vipcnt DESC')
                    ->find();
    }

    /**
     * 按销售城市表取会员数
     * @param      array  $dept  部门
     * @param      array  $citys  有权限查看的城市
     * @return     array  城市会员数
     */
    public function getVipUserCountBySalesCity($dept,$citys){
        $map = array(
            'b.fake' => array("EQ",'0'),
            'a.classid' => array("EQ",'3'),
            'a.on' => array("EQ",'2')
        );
        if(!empty($citys)){
            $map['m.id'] = $citys;
        }
        if(!empty($dept)){
            $map['m.dept'] = array('EQ',$dept);
        }

        return M('user')->alias("a")
                    ->field('m.id,a.cs,m.city,m.dept,sum(if(a.on = 2,b.viptype,0)) as vipcnt,sum(if(b.viptype > 1,(b.viptype-1),0)) as doublecnt,count(if(a.on = 2,a.id,0)) as vipnum')
                    ->join('inner join qz_user_company b on a.id = b.userid')
                    ->join('inner join qz_quyu q on q.cid = a.cs')
                    ->join('right join qz_sales_city_manage m on m.city = q.cname')
                    ->where($map)
                    ->group('a.cs')
                    ->order('vipcnt DESC')
                    ->select();
    }

    /*
     *  优化分单量进度的查询速度
     *
     */
    // public function fdljdcx($dept,$citys,$startTime,$endTime)
    // {
    //     //
    //     $map = array(
    //         'b.fake' => array("EQ",'0'),
    //         'a.classid' => array("EQ",'3'),
    //         'a.on' => array("EQ",'2')
    //     );
    //     if(!empty($citys)){
    //         $map['m.id'] = $citys;
    //     }
    //     if(!empty($dept)){
    //         $map['m.dept'] = array('EQ',$dept);
    //     }

    //     $buildSql1 = M('user')->alias("a")
    //                 ->field('m.id,a.cs,m.city,m.dept,sum(if(a.on = 2,b.viptype,0)) as vipcnt,sum(if(b.viptype > 1,(b.viptype-1),0)) as doublecnt,count(if(a.on = 2,a.id,0)) as vipnum')
    //                 ->join('inner join qz_user_company b on a.id = b.userid')
    //                 ->join('inner join qz_quyu q on q.cid = a.cs')
    //                 ->join('right join qz_sales_city_manage m on m.city = q.cname')
    //                 ->where($map)
    //                 ->group('a.cs')
    //                 ->order('vipcnt DESC')
    //                 ->buildSql();
    //     echo $buildSql1;

    //     /*SELECT
    //         o.id,
    //         u.vip_days,
    //         i.com,
    //         u.userid,
    //         m.dept,
    //         q.cname,
    //         q.cid,
    //         o.cs,
    //         FROM_UNIXTIME(addtime, "%Y-%m-%d") AS days
    //     FROM
    //         qz_order_info i
    //     LEFT JOIN qz_orders AS o ON o.id = i.`order`
    //     INNER JOIN qz_user_company AS u ON u.userid = i.com
    //     INNER JOIN qz_quyu AS q ON q.cid = o.cs
    //     INNER JOIN qz_sales_city_manage AS m ON m.city = q.cname
    //     WHERE
    //         i.type_fw = '1'
    //     AND i.addtime BETWEEN 1496246400
    //     AND 1498838399
    //     AND u.vip_days >= '26'*/
    //     $result = M('sales_setting_value')->table($buildSql1)->alias('a')
    //                     ->join('left')


    //     return $result;
    // }

    /**
     * 获取分单数 按日期
     *
     * @param      int  $startTime  The start time
     * @param      int  $endTime    The end time
     * @param      int  $dept       The dept
     *
     * @return     array  获取分单数.
     */
    public function getFendanByDays($startTime,$endTime,$dept = ''){
        /*
        核对数据SQL：

        SELECT o.id,u.vip_days,i.com,u.userid,m.dept,q.cname,q.cid,o.cs,FROM_UNIXTIME(addtime,"%Y-%m-%d") AS days FROM qz_order_info i
        LEFT JOIN qz_orders as o ON o.id = i.order
        INNER JOIN qz_user_company as u on u.userid = i.com
        INNER JOIN qz_quyu as q on q.cid = o.cs
        INNER JOIN qz_sales_city_manage as m ON m.city = q.cname
        WHERE i.type_fw = '1' AND i.addtime BETWEEN 1496246400 AND 1498838399 AND u.vip_days >= '26'

        */
        $map = array(
            'i.type_fw' => array("EQ",'1'),
            'i.addtime' => array('between',array($startTime,$endTime)),
            'u.vip_days' => array("EGT",'26'),
        );

        if(!empty($dept)){
            $map['m.id'] = array('EQ',$dept);
        }
        $result = M('order_info')->alias('i')
                ->field('count(*) as num,FROM_UNIXTIME(addtime,"%Y-%m-%d") AS days')
                ->join('LEFT JOIN qz_orders as o ON o.id = i.order')
                ->join('LEFT JOIN qz_user_company as u on u.userid = i.com')
                ->join('LEFT JOIN qz_quyu as q on q.cid = o.cs')
                ->join('INNER JOIN qz_sales_city_manage as m ON m.city = q.cname')
                ->where($map)
                ->group('days')
                ->select();
        return $result;
    }

    /**
     * 获取计划分单月平均值
     *
     * @param      <type>  $startTime  The start time
     */
    public function getJhFendanAvg(){
        /*$map = array(
            's.start' => array("EQ",date('Y-m-d',$startTime)),
            's.module' => array("EQ",'4'),
        );

        if(!empty($dept)){
            $map['c.dept'] = array('EQ',$dept);
        }
        
        $result = M('sales_city_manage')->alias('c')
                    ->field('c.id,s.point,s.start')
                    ->join('left join qz_sales_setting_value as s on s.manage_id = c.id')
                    ->where($map)
                    ->select();
        return $result;*/

        $map1['module'] = 4;
        $map1['status'] = 1;
        $map1['point'] = array('NEQ','');
        $order1 = 'start desc';
        $buildSql1 = M('sales_setting_value')
                    ->where($map1)
                    ->field('id,manage_id,point,start')
                    ->order($order1)
                    ->buildSql();
        $jhfds = M('sales_setting_value')->table($buildSql1)->alias('a')
                    ->field("a.*")
                    ->group('a.manage_id')
                    ->select();
        return $jhfds;
    }

    /**
     * 获取城市不足分单会员数
     *
     * @param      int  $startTime  The start time
     * @param      int  $endTime    The end time
     * @param      int  $dept       The dept
     *
     * @return     array  获取分单数.
     */
    public function getNotEnoughVip($startTime,$endTime,$city = ''){
        /*
        核对数据SQL：

        SELECT
            count(*) AS num,
            i.com
        FROM
            qz_order_info i
        LEFT JOIN qz_orders AS o ON o.id = i.`order`
        LEFT JOIN qz_user_company AS u ON u.userid = i.com
        LEFT JOIN qz_quyu AS q ON q.cid = o.cs
        INNER JOIN qz_sales_city_manage AS m ON m.city = q.cname
        WHERE
            i.type_fw = '1'
        AND i.addtime BETWEEN '1493568000'
        AND 1496246399
        AND u.vip_days >= '26'
        and m.id = 31
        GROUP BY
            i.com
        */
        $map = array(
            'i.type_fw' => array("EQ",'1'),
            'i.addtime' => array('between',array($startTime,$endTime)),
            'u.vip_days' => array("EGT",'26'),
        );

        if(!empty($city)){
            $map['m.id'] = array('EQ',$city);
        }
        $result = M('order_info')->alias('i')
                ->field('count(*) as num,i.com,u.viptype')
                ->join('LEFT JOIN qz_orders as o ON o.id = i.order')
                ->join('LEFT JOIN qz_user_company as u on u.userid = i.com')
                ->join('LEFT JOIN qz_quyu as q on q.cid = o.cs')
                ->join('INNER JOIN qz_sales_city_manage as m ON m.city = q.cname')
                ->where($map)
                ->group('i.com')
                ->select();
        return $result;
    }

    /**
     * 获取分单数 按城市
     *
     * @param      int  $startTime  The start time
     * @param      int  $endTime    The end time
     * @param      int  $dept       The dept
     *
     * @return     array  获取分单数 按城市
     */
    public function getFendanByCS($startTime,$endTime,$dept = ''){
        $map = array(
            'i.type_fw' => array("EQ",'1'),
            'i.addtime' => array('between',array($startTime,$endTime)),
            'u.vip_days' => array("EGT",'26'),
        );

        if(!empty($dept)){
            $map['m.dept'] = array('EQ',$dept);
        }

        return M('order_info')->alias('i')
                ->field('count(*) as num,o.cs')
                ->join('LEFT JOIN qz_orders as o ON o.id = i.order')
                ->join('LEFT JOIN qz_user_company as u on u.userid = i.com')
                ->join('LEFT JOIN qz_quyu as q on q.cid = o.cs')
                ->join('INNER JOIN qz_sales_city_manage as m ON m.city = q.cname')
                ->where($map)
                ->group('cs')
                ->select();
    }



    /**
     * 取城市分单量满足率
     *
     * @param      integer  $startTime  The start time
     * @param      integer  $endTime    The end time
     *
     * @return     array  The csfdlmzl.
     */
    public function getCsfdlmzl($map,$order='',$start='',$end=''){

        //if(!empty($map['c.city_id'])){
            $where1['m.id'] = $map['c.city_id'];
        //}
        if(!empty($map['m.dept'])){
            $where1['m.dept'] = $map['m.dept'];
        }
        if(!empty($end)){
            $limit = $start.','.$end;
        }

        $count = M('sales_city_manage')->alias("m")
                    ->field('m.*,q.cid,q.cname,q.bm')
                    ->join('left join qz_quyu as q on q.cname = m.city')
                    ->join('qz_sales_city_paixu as p on p.cityid = m.id')
                    ->where($where1)
                    ->count();

        //城市分页
        $result = M('sales_city_manage')->alias("m")
                    ->field('m.*,q.cid,q.cname,q.bm')
                    ->join('left join qz_quyu as q on q.cname = m.city')
                    ->join('qz_sales_city_paixu as p on p.cityid = m.id')
                    ->where($where1)
                    ->order($order)
                    ->limit($limit)
                    ->select();
        if(!empty($result)){
            foreach ($result as $k => $v) {
                $ids[] = $v['id'];
                $city_id[] = $v['cid'];
                if($v['dept'] == 1){
                    $result[$k]['department'] = '商务';
                }else{
                    $result[$k]['department'] = '外销';
                }
            }
        }
        //查询计划月分单量
        //查看所有到期数、实际续费数，并添加到$result
        $map1['module'] = 4;
        $map1['status'] = 1;
        if(!empty($ids)){
            $map1['manage_id'] = array('IN',$ids);
        }
        $map1['point'] = array('NEQ','');
        $order1 = 'start desc';
        $buildSql1 = M('sales_setting_value')
                    ->where($map1)
                    ->field('id,manage_id,point,start')
                    ->order($order1)
                    ->buildSql();
        $jhfds = M('sales_setting_value')->table($buildSql1)->alias('a')
                    ->join("qz_sales_city_paixu as p on p.cityid = a.manage_id")
                    ->field("a.*,p.px5 as px")
                    ->group('a.manage_id')
                    ->select();
        foreach ($result as $k => $v) {
            foreach ($jhfds as $key => $value) {
                if($v['id'] == $value['manage_id']){
                    $result[$k]['jhyfds'] = $value['point'];//到期数
                }
            }
            if(empty($result[$k]['jhyfds'])){
                $result[$k]['jhyfds'] = 0;
            }
        }
        //查询当月会员数（今天的）
        $map2 = array(
            'b.fake' => array("EQ",'0'),
            'a.classid' => array("EQ",'3'),
            'a.on' => array("EQ",'2')
        );
        if(!empty($city_id)){
            $map2['a.cs'] = array('IN',$city_id);
        }
        $users =  M('user')->alias("a")
                    ->field('a.cs,sum(if(a.on = 2,b.viptype,0)) as vipcnt,sum(if(b.viptype > 1,(b.viptype-1),0)) as doublecnt,count(if(a.on = 2,a.id,null)) as vipnum')
                    ->join('inner join qz_user_company b on a.id = b.userid')
                    ->where($map2)
                    ->group('a.cs')
                    ->order('vipcnt DESC')
                    ->select();
        foreach ($users as $key => $value) {
            $vipCount[$value["cs"]] = $value;
        }
        //取本月实际分单量
        //开始时间，本月第一天00:00:00 结束时间为当前时间
        $starttime = mktime(0,0,0,date("m"),1,date("Y"));
        $endtime = time();
        $map3['a.lasttime'] = array('between',array($starttime,$endtime));
        if(!empty($city_id)){
            $map3['o.cs'] = array('IN',$city_id);
        }
        $real_orders = M('order_csos_new')->alias('a')
                            ->field('o.cs,count(if(o.on = 4 and o.type_fw = 1,1,null)) as fen')
                            ->join('qz_orders o on o.id = a.order_id and o.on = 4 and o.type_fw in (1,2)')
                            ->where($map3)
                            ->group('o.cs')
                            ->select();
        foreach ($real_orders as $k => $v) {
            $realFendan[$v["cs"]] = $v;
        }
        //取城市分单量
        $map4['time_real'] = array('between',array($starttime,$endtime));
        $map4['on'] = array('EQ','4');
        $map4['type_fw'] = array('EQ','1');
        if(!empty($city_id)){
            $map4['cs'] = array('IN',$city_id);
        }
        $all_orders = M('orders')
                        ->field("cs,count(*) as fen")
                        ->where($map4)
                        ->group('cs')
                        ->select();
        foreach ($all_orders as $k => $v) {
            $fendan[$v["cs"]] = $v;
        }
        //获取本月会员数
        $realVipCount = $this->getVipUserByTime($starttime,$city_id);
        $today = date('d',time());
        foreach ($result as $key => $v) {
            $v['vipcnt'] = '0';
            $v['doublecnt'] = '0';
            $v['vipnum'] = '0';
            $v['allVipNum'] = 0;

            //时间进度比：当日日期/30天*100%
            $v['time_ratio'] = round(($today / 30) * 100, 1);
            if($v['time_ratio'] >= 100){
                $v['time_ratio'] = 100;
            }
            //实际分单量
            $v['realFendan'] = $realFendan[$v["cid"]]['fen'];

            //分单量
            $v['fendan'] = $fendan[$v["cid"]]['fen'];


            if(!empty($vipCount[$v["cid"]])){
                $v['vipcnt'] = $vipCount[$v["cid"]]['vipcnt'];
                $v['doublecnt'] = $vipCount[$v["cid"]]['doublecnt'];
                $v['vipnum'] = $vipCount[$v["cid"]]['vipnum'];

                //本月全部会员
                $v['allVipNum'] = $realVipCount['all'][$v["cid"]];

                //全月会员
                $v['fullVipNum'] = $realVipCount['full'][$v["cid"]];

                //非全月会员数
                $v['noFullVipNum'] = $v['allVipNum'] - $v['fullVipNum'];

                /*
                实际月分单数均值（全月会员）：
                全月会员 实际累计月分单数均值，北京 =（北京实际月分单数）/ 北京多倍会员数
                */
                $v['realFendanAvgFull'] = round($v['realFendan'] / $v['fullVipNum'], 2);

                /*
                实际月分单数均值（非全月）：
                非全月 实际累计月分单数均值， 北京 =（北京实际月分单数）/ 北京多倍会员数
                */
                if(!empty($v['noFullVipNum'])){
                    $v['realFendanAvgNotFull'] = round($v['realFendan'] / $v['noFullVipNum'], 2);
                }
            }

            //分单满足率：实际月分单数均值（全月会员）/ 计划月分单数均值 / 时间进度比 * 100%
            if(!empty($v['realFendanAvgFull']) && !empty($v['jhyfds'])){
                $v['fdmzl'] = round(($v['realFendanAvgFull'] / $v['jhyfds'] / $v['time_ratio']) * 100, 2);
            }

            $list[$v["cid"]] = $v;
        }
        foreach ($list as $k => $v) {
            //城市
            $data['city']               = $v['city'];
            //城市ID
            $data['city_id']            = $v['id'];
            //城市重点系数
            $data['ratio']            = $v['ratio'];
            //部门
            $data['dept']               = $v['dept'];
            //拓展
            $data['dev_division']       = $v['dev_division'];
            $data['dev_regiment']       = $v['dev_regiment'];
            $data['dev_manage']         = $v['dev_manage'];
            //品牌
            $data['brand_division']     = $v['brand_division'];
            $data['brand_regiment']     = $v['brand_regiment'];
            $data['brand_manage']       = $v['brand_manage'];
            //实际总会员数
            $data['real_vip_num']       = $v['vipcnt'];
            //计划月分单数均值
            $data['jhyfds_avg']         = number_format($v['jhyfds'],1,'.','');
            //实际月分单数均值（全月会员）
            $data['sjyfds_avg_qy']      = number_format($v['realFendanAvgFull'],1,'.','');
            //时间进度比
            $data['sjjdb']              = number_format($v['time_ratio'],1,'.','');
            //分单满足率
            $data['fdmzl']              = number_format($v['fdmzl'],1,'.','');
            //分单总数
            $data['fdzs']               = $v['fendan'];
            //非全月会员数
            $data['no_full_vip_num']    = $v['noFullVipNum'];
            //实际月分单数均值（非全月）
            $data['sjyfds_avg_fqy']     = round($v['realFendanAvgNotFull'],1);

            $csfdl[] = $data;
        }
    
        return array("result"=>$csfdl,"count"=>$count);
    }

    /**
     * 取城市分单量满足率
     *
     * @param      integer  $startTime  The start time
     * @param      integer  $endTime    The end time
     * @param      integer  $pageStart    分页开始
     * @param      integer  $pageEnd      分页结束
     * @param      array    $condition    查询条件
     * @return     array  The csfdlmzl.
     */
    public function getHyfdlmzl($startTime,$endTime,$pageStart,$pageEnd,$condition){
        $map['u.classid'] = array('EQ','3');
        $map['u.on'] = array('EQ','2');
        $map['b.fake'] = array('EQ','0');
        if(!empty($condition['city'])){
            $map['m.id'] = $condition['city'];
        }else{
            $map['m.id'] = $condition['c.cid'];
        }
        if(!empty($condition['dev_division'])){
            $map['m.dev_division'] = $condition['dev_division'];
        }
        if(!empty($condition['dev_regiment'])){
            $map['m.dev_regiment'] = $condition['dev_regiment'];
        }
        if(!empty($condition['dev_manage'])){
            $map['m.dev_manage'] = $condition['dev_manage'];
        }
        if(!empty($condition['brand_division'])){
            $map['m.brand_division'] = $condition['brand_division'];
        }
        if(!empty($condition['brand_regiment'])){
            $map['m.brand_regiment'] = $condition['brand_regiment'];
        }
        if(!empty($condition['brand_manage'])){
            $map['m.brand_manage'] = $condition['brand_manage'];
        }
        if(!empty($condition['user'])){
            $map['u.id'] = $condition['user'];
        }
        if(!empty($condition['user_name'])){
            $map['u.user'] = $condition['user_name'];
        }
        if(!empty($condition['dept'])){
            $map['m.dept'] = $condition['dept'];
        }
        if(!empty($condition['c.cid'])){
            $map['m.id'] = $condition['c.cid'];
        }
        if(!empty($condition['city'])){
            $map['m.id'] = $condition['city'];
        }
        $count =  M('user')->alias('u')
                ->field("u.id as uid,u.jc,u.cs,q.cname,m.id as cityid,m.city,m.level,m.dept,m.ratio,m.dev_division,m.dev_regiment,m.dev_manage,m.brand_division,m.brand_regiment,m.brand_manage,FROM_UNIXTIME(b.contract_start,'%Y-%m-%d') as allstart,FROM_UNIXTIME(b.contract_end,'%Y-%m-%d') as allend")
                ->join('LEFT JOIN qz_quyu q on q.cid = u.cs')
                ->join('LEFT JOIN qz_sales_city_manage m on m.city = q.cname')
                ->join('LEFT JOIN qz_user_company b on b.userid = u.id')
                ->where($map)
                ->count();

        $result =  M('user')->alias('u')
                ->field("u.id as uid,u.jc,u.cs,q.cname,m.id as cityid,m.city,m.level,m.dept,m.ratio,m.dev_division,m.dev_regiment,m.dev_manage,m.brand_division,m.brand_regiment,m.brand_manage,FROM_UNIXTIME(b.contract_start,'%Y-%m-%d') as allstart,FROM_UNIXTIME(b.contract_end,'%Y-%m-%d') as allend")
                ->join('LEFT JOIN qz_quyu q on q.cid = u.cs')
                ->join('LEFT JOIN qz_sales_city_manage m on m.city = q.cname')
                ->join('LEFT JOIN qz_sales_city_paixu p on m.id = p.cityid')
                ->join('LEFT JOIN qz_user_company b on b.userid = u.id')
                ->where($map)
                ->order('p.px8 desc,m.ratio desc,u.id desc')
                ->limit($pageStart,$pageEnd)
                ->select();
        return array('result'=>$result,'count'=>$count);
        

     /*   return  M("user")->table($buildSql)
                ->alias('u')
                ->field("u.*,FROM_UNIXTIME(b.contract_start,'%Y-%m-%d') as allstart,FROM_UNIXTIME(b.contract_end,'%Y-%m-%d') as allend")
                ->join('LEFT JOIN qz_user_company b on u.uid = b.userid')
                ->order('uid')
                ->select();*/
    }

    /**
     * 取 城市会员数完成率
     *
     * @param      integer  $startTime  The start time
     * @param      integer  $endTime    The end time
     *
     * @return     array    城市会员数完成率.
     */
    public function getCshyswcl($startTime,$endTime){

        $hyzbTime = 'AND zb.status = 1 AND zb.time = '.$startTime;

        return M('sales_city_manage')->alias("m")
                ->field('m.*,q.cid,q.cname,q.bm,zb.point,qq.name as qqname,qq.point as qqpoint,qq.num as qqnum')
                ->join('left join qz_quyu as q on q.cname = m.city')
                ->join("left join qz_sale_citypoint as zb on zb.cityid = m.id $hyzbTime")
                ->join("left join qz_sale_qqgroup as qq on qq.cityid = m.id ")
                ->where($map)
                ->order('point desc,qqpoint DESC')
                ->select();
    }

    /**
     * 取 城市会员数完成率
     *
     * @param      integer  $startTime  The start time
     * @param      integer  $endTime    The end time
     *
     * @return     array    城市会员数完成率.
     */
    public function getCityVipConn($map,$order,$start='',$end=''){

        /*$hyzbTime = 'AND zb.status = 1 AND zb.time = '.$startTime;

        return M('sales_city_manage')->alias("m")
                ->field('m.*,q.cid,q.cname,q.bm,zb.point,qq.name as qqname,qq.point as qqpoint,qq.num as qqnum')
                ->join('left join qz_quyu as q on q.cname = m.city')
                ->join("left join qz_sale_citypoint as zb on zb.cityid = m.id $hyzbTime")
                ->join("left join qz_sale_qqgroup as qq on qq.cityid = m.id ")
                ->where($map)
                ->order('point desc,qqpoint DESC')
                ->select();*/

        $search_time = date('Y-m-d',time());

        //if(!empty($map['c.id'])){
            $map2['m.id'] = $map['c.cid'];
        //}
        if(!empty($map['m.dept'])){
            $map2['m.dept'] = $map['m.dept'];
        }
        if(!empty($map['m.dev_division'])){
            $map2['m.dev_division'] = $map['m.dev_division'];
        }
        if(!empty($map['m.dev_regiment'])){
            $map2['m.dev_regiment'] = $map['m.dev_regiment'];
        }
        if(!empty($map['m.dev_manage'])){
            $map2['m.dev_manage'] = $map['m.dev_manage'];
        }
        if(!empty($map['m.brand_division'])){
            $map2['m.brand_division'] = $map['m.brand_division'];
        }
        if(!empty($map['m.brand_regiment'])){
            $map2['m.brand_regiment'] = $map['m.brand_regiment'];
        }
        if(!empty($map['m.brand_manage'])){
            $map2['m.brand_manage'] = $map['m.brand_manage'];
        }
        if(!empty($end)){
            $limit = $start .','. $end;
        }
        $map1['status'] = 0;
        //$map1['time'] = array('ELT',$map['end']);
        //1,先查全部的会员指标
        $zhibiaoSql = M('sale_citypoint')
                    ->field('id,cityid,point,time')
                    ->where($map1)
                    ->group('cityid')
                    ->order('time desc')
                    ->buildSql();
        $citysSql = M('sale_citypoint')->table($zhibiaoSql)->alias("t")
                            ->join("RIGHT JOIN qz_sales_city_manage m on t.cityid = m.id")
                            ->join('qz_sales_city_paixu b ON m.id = b.cityid')
                            ->where($map2)
                            ->field("m.*,t.point,".$order)
                            ->buildSql();

        $count = M('sale_citypoint')->table($zhibiaoSql)->alias("t")
                            ->join("RIGHT JOIN qz_sales_city_manage m on t.cityid = m.id")
                            ->join('qz_sales_city_paixu b ON m.id = b.cityid')
                            ->where($map2)
                            ->field("m.*,t.point,".$order)
                            ->count();


        $citySql = M('sales_city_manage')->table($citysSql)->alias("t")
                            ->join('LEFT JOIN qz_quyu c ON t.city = c.cname ')
                            ->field("t.*,c.cid")
                            ->buildSql();
        $result = M("sales_city_manage")->table($citySql)->alias("m")
                        ->join('LEFT JOIN qz_user AS d ON d.cs = m.cid  and d.classid = 3 and d.`on` = 2')
                        ->join('LEFT JOIN qz_user_company AS e ON d.id = e.userid')
                        ->field("m.*,sum(if(e.fake = 0,e.viptype,0)) as vipcnt,count(if(e.fake = 0,d.id,null)) as realvipnum,IF(m.point = 0, 0, (sum(IF(e.fake = 0, e.viptype, 0)) / m.point)) as hywcl")
                        ->order("m.px desc,hywcl asc,m.id asc")
                        ->group("m.id")
                        ->limit($limit)
                        ->select();

        foreach ($result as $k => $v) {
            $city_ids[] = $v['cid'];
            $ids[] = $v['id'];
            if($v['dept'] == 1){
                $result[$k]['department'] = '商务';
            }else{
                $result[$k]['department'] = '外销';
            }
        }
        //2,查询暂停会员数和退费会员数
        if(!empty($city_ids)){
            $map3['u.cs'] = array('IN',$city_ids);
        }
        $map3['u.classid'] = '3';
        $map3['v.type'] = array('IN','4,7');
        $map3['v.start_time'][] = array('ELT',date('Y-m-t',$map['start']));
        $map3['v.start_time'][] = array('EGT',date('Y-m'.'-01',$map['start']));

        $huiyuan = M('user_vip')->alias('v')
                ->field('u.cs,v.company_id,sum(if(v.type = 4,1,null)) as pausenum,sum(if(v.type = 7,1,null)) as refundnum')
                ->join('left join qz_user u on u.id = v.company_id')
                ->where($map3)
                ->group('v.company_id')
                ->select();

        //3,查询QQ群内容
        if(!empty($ids)){
            $map4['cityid'] = array('IN',$ids);
        }
        $qqgroup = M('sale_qqgroup')->field('cityid,name,num,point')->where($map4)->select();
        foreach ($result as $k => $v) {
            if(!empty($huiyuan)){
                foreach ($huiyuan as $key => $value) {
                    if($v['cid'] == $value['cs']){ 
                        $v['pause'] = $value['pausenum'];//暂停会员数
                        $v['refund'] = $value['refundnum'];//退费
                    }
                }
            }
            foreach ($qqgroup as $key => $value) {
                if($v['id'] == $value['cityid']){
                    $v['qqname'] = $value['name'];//城市QQ群名称
                    $v['qqpoint'] = $value['num'];//实际群成员数
                    if(!empty($value['point'])){
                        $v['qqwcl'] =  number_format(($value['num'] / $v['point'] ) * 100,1,'.','');//城市QQ群成员数完成率
                    } 
                }
            }
            $v['hywcl'] = number_format(($v['hywcl']) * 100,1,'.','');
            $v['hyzb'] = $v['point'];
            $list[] = $v;
        }
        return array('list'=>$list,'count'=>$count);
    }

    /**
     * 取本月的统计城市会员数
     * @param      array $ids       查询管辖的城市ID数组
     * @return     array $result    本月的全瞰会员数统计
     */
    public function getMonthData($ids,$dept)
    {
        if(!empty($dept)){
            $map['m.dept'] = $dept;
        }
        $map['start'] = mktime(0,0,0,date("m"),1,date("Y"));
        $map['end'] = mktime(23,59,59,date('m',$map['start']),date('t',$map['start']),date('Y',$map['start']));
        $map['c.id'] = array('IN',$ids);
        $list = $this->getCityVipConn($map, 'b.px1 AS px');
        // 'csnum' 总城市数
        $result['csnum'] = $list['count'];
        foreach ($list['list'] as $k => $v) {
            // 'hyzb'  会员指标
            $result['hyzb'] += $v['hyzb'];
            // 'vipcnt'  实际总会员数 （算多倍）
            $result['vipcnt'] += $v['vipcnt'];
            // 'doublecnt'  总多倍会员数
            $result['doublecnt'] += ($v['vipcnt'] - $v['realvipnum']);
            // 'month'  月
            $result['month'] = date("m");
            // 'year' 年
            $result['year'] = date("Y");
            if($v['level'] == 1){
                //默认level = 1 为地级市
                // 'djs_csnum'   地级市数量
                $result['djs_csnum'] = $result['djs_csnum'] + 1;
                $result['djs_hyzb'] += $v['hyzb'];
                // 'vipcnt'  实际总会员数 （算多倍）
                $result['djs_vipcnt'] += $v['vipcnt'];
            }
            // 'vipnum'     单倍会员数
            $result['vipnum'] += $v['realvipnum'];
        }
        // 'djs_hywcl'   地级市会员数完成率
        $result['djs_hywcl'] = round(($result['djs_vipcnt'] / $result['djs_hyzb'] ) * 100, 1);
        // 'hyswcl'     会员数完成率
        $result['hyswcl'] = round(($result['vipcnt'] / $result['hyzb'] ) * 100, 1);
        return $result;
    }

    /**
     * 获取 计划月分单数
     *
     * @param      integer  $startTime  The start time 
     * @param      array    $p_map      管辖的城市数组
     *
     * @return     array    计划月分单数列表.
     */
    public function getPlanFendan($p_map){

        /*$map['module'] = array('EQ','4');
        $map['start'] = array('EQ',date('Y-m-d',$startTime));
        $map['manage_id'] = $p_map['id'];
        $map['status'] = 1;
        return M('sales_setting_value')
                ->field('manage_id,point,start')
                ->where($map)
                ->select();*/

        $map1['module'] = 4;
        $map1['status'] = 1;
        $map1['manage_id'] = $p_map['id'];
        $map1['point'] = array('NEQ','');
        $order1 = 'start desc';
        $buildSql1 = M('sales_setting_value')
                    ->where($map1)
                    ->field('id,manage_id,point,start')
                    ->order($order1)
                    ->buildSql();
        $jhfds = M('sales_setting_value')->table($buildSql1)->alias('a')
                    ->join("qz_sales_city_paixu as p on p.cityid = a.manage_id")
                    ->field("a.*,p.px5 as px")
                    ->group('a.manage_id')
                    ->select();
        return $jhfds;
    }

    /**
     * 取会员本月合作天数
     *
     * @param      integer  $start      The start time
     * @param      array    $map        查询条件
     * @return     array  The user list.
     */
    public function getVipCoopDays($start,$map){

        $map['v.type'] = array('IN','2,8');
        $map['v.start_time'] = array('ELT',date('Y-m-t',$start));
        $map['v.end_time'] = array('EGT',date('Y-m'.'-01',$start));

        $result = M('user_vip')->alias('v')
                ->field('v.id,v.company_id,v.company_name,v.type,v.start_time,v.end_time,u.cs,if(b.viptype > 1,(b.viptype-1),0) as doublecnt')
                ->join('INNER JOIN qz_user as u ON u.id = v.company_id')
                ->join('INNER JOIN qz_user_company b on b.userid = v.company_id')
                ->where($map)
                ->select();

        //统计会员在本月数
        foreach ($result as $k => $v) {
            $days = getThisMonthDay($v['start_time'],$v['end_time'],$start);

            //如果有重复公司，累加本月天数
            if(!empty($companys[$v['company_id']])){
                $var['days'] = $companys[$v['company_id']]['days'] + $days;
            }else{
                $var['company_name'] = $v['company_name'];
                $var['cs'] = $v['cs'];
                $var['days'] = $days;
                $var['doublecnt'] = $v['doublecnt'];
            }

            $companys[$v['company_id']] = $var;
        }

        return $companys;
    }



    /**
     * 取城市实际分单
     *
     * @param      integer  $start  The start time
     * @param      integer  $end    The end time
     *
     * @return     array  The city fendan.
     */
    public function getCityRealFendan($start,$end){

        $map['a.lasttime'] = array('between',array($start,$end));

        return M('order_csos_new')->alias('a')
                ->field('o.cs,count(if(o.on = 4 and o.type_fw = 1,1,null)) as fen')
                ->join('qz_orders o on o.id = a.order_id and o.on = 4 and o.type_fw in (1,2)')
                ->where($map)
                ->group('o.cs')
                ->select();
    }

    /**
     * 取城市分单量
     *
     * @param      integer  $start  The start time
     * @param      integer  $end    The end time
     *
     * @return     array  The city fendan.
     */
    public function getCityFendan($start,$end){

        $map['time_real'] = array('between',array($start,$end));
        $map['on'] = array('EQ','4');
        $map['type_fw'] = array('EQ','1');

        return M('orders')
                ->field("cs,count(*) as fen")
                ->where($map)
                ->group('cs')
                ->select();
    }

    /**
     * 按月份取会员数
     *
     * @param      integer  $start      The start time
     *
     * @return     array  The vip user list.
     */
    public function getVipUserByTime($start,$ctiys=''){

        $map['v.type'] = array('IN','2,8');
        $map['v.start_time'] = array('ELT',date('Y-m-t',$start));
        $map['v.end_time'] = array('EGT',date('Y-m'.'-01',$start));
        if(!empty($ctiys)){
            $map['u.cs'] = array('IN',$ctiys);
        }
        $result = M('user_vip')->alias('v')
                ->field('v.id,v.company_id,v.company_name,v.type,v.start_time,v.end_time,u.cs,if(b.viptype > 1,(b.viptype-1),0) as doublecnt')
                ->join('INNER JOIN qz_user as u ON u.id = v.company_id')
                ->join('INNER JOIN qz_user_company b on b.userid = v.company_id')
                ->where($map)
                ->select();

        //统计会员在本月数
        foreach ($result as $k => $v) {
            $days = getThisMonthDay($v['start_time'],$v['end_time'],$start);

            //如果有重复公司，累加本月天数
            if(!empty($companys[$v['company_id']])){
                $var['days'] = $companys[$v['company_id']]['days'] + $days;
            }else{
                $var['company_name'] = $v['company_name'];
                $var['cs'] = $v['cs'];
                $var['days'] = $days;
                $var['doublecnt'] = $v['doublecnt'];
            }

            $companys[$v['company_id']] = $var;
        }


        //统计城市会员数
        foreach ($companys as $k => $v) {
            //echo $k.'|'.$v['cs'].'|'.$v['company_name'].'|'.$v["days"].'|'.$v["doublecnt"].'<br>';

            if(empty($cityVip['all'][$v['cs']])){
                $cityVip['all'][$v['cs']] = '0';
            }

            if(empty($cityVip['full'][$v['cs']])){
                $cityVip['full'][$v['cs']] = '0';
            }

            //全月
            if($v['days'] >= 26){
                $cityVip['full'][$v['cs']] = $cityVip['full'][$v['cs']] + 1 + $v["doublecnt"];
                $cityVip['all'][$v['cs']] = $cityVip['all'][$v['cs']] + 1 + $v["doublecnt"];
            }else{
                $cityVip['all'][$v['cs']] = $cityVip['all'][$v['cs']] + 1 + $v["doublecnt"];
            }
        }

        return $cityVip;
    }

    /**
     * 获取 城市会员数完成率 列表
     *
     * @param      array            $condition  The condition
     * @param      integer|string   $pagesize   The pagesize
     * @param      integer          $pageRow    The page row
     *
     * @return     array            城市会员数完成率 列表.
     */
    public function getCshyswclList($condition,$pagesize= 1,$pageRow = 10){
        $count  = M('sales_cshyswcl')->alias("c")
                  ->join('left join qz_sales_city_manage as m on m.id = c.cid')
                  ->where($condition)
                  ->count();

        $result = M('sales_cshyswcl')->alias("c")
                  ->field('c.*,m.city,m.level,m.dept,m.ratio,m.corps,m.dev_division,m.dev_regiment,m.dev_manage,m.brand_division,m.brand_regiment,m.brand_manage')
                  ->join('left join qz_sales_city_manage as m on m.id = c.cid')
                  ->where($condition)
                  ->order('c.id ')
                  ->limit($pagesize.",".$pageRow)
                  ->select();

        return array("result"=>$result,"count"=>$count);
    }

    /**
     * 获取 全瞰-分单量进度 列表
     *
     * @param      array            $condition  The condition
     * @param      integer|string   $pagesize   The pagesize
     * @param      integer          $pageRow    The page row
     *
     * @return     array            全瞰-分单量进度列表.
     */
    public function getQkFdljdList($condition,$pagesize= 1,$pageRow = 10){
        $nextYear = $condition['year'] + 1;

        $condition[] = array(
                array(
                    'month' => array('NEQ','1'),
                    'year' => array("EQ",$condition['year'])
                ),
                array(
                    'month' => array("ELT",'2'),
                    'year' => array("EQ",$nextYear)
                ),
                '_logic' => 'OR',
        );
        unset($condition['year']);

        return M('sales_qkfdljd')
                  ->field("*,lpad(month,2,'0') as month")
                  ->where($condition)
                  ->order('year,month')
                  ->select();
    }

    /**
     * 获取 城市分单量满足率 列表
     *
     * @param      array            $condition  The condition
     * @param      integer|string   $pagesize   The pagesize
     * @param      integer          $pageRow    The page row
     *
     * @return     array            城市分单量满足率列表.
     */
    public function getCsfdlmzlList($condition,$pagesize= 1,$pageRow = 10){
        
        $count  = M('sales_csfdlmzl')->alias("c")
                  ->join('left join qz_sales_city_manage as m on m.id = c.city_id')
                  ->where($condition)
                  ->count();

        $result = M('sales_csfdlmzl')->alias("c")
                  ->field('c.*,m.city,m.level,m.dept,m.ratio,m.corps,m.dev_division,m.dev_regiment,m.dev_manage,m.brand_division,m.brand_regiment,m.brand_manage')
                  ->join('left join qz_sales_city_manage as m on m.id = c.city_id')
                  ->where($condition)
                  ->order('c.id desc')
                  ->limit($pagesize.",".$pageRow)
                  ->select();

        return array("result"=>$result,"count"=>$count);
    }


    /**
     * 获取 城市分单量满足率 列表
     *
     * @param      array            $condition  The condition
     * @param      integer|string   $pagesize   The pagesize
     * @param      integer          $pageRow    The page row
     *
     * @return     array            城市分单量满足率列表.
     */
    public function getHyfdlmzlList($condition,$pagesize= 1,$pageRow = 10){
        $count  = M('sales_csfdlmzl')->alias("c")
                  ->join('left join qz_sales_city_manage as m on m.id = c.city_id')
                  ->where($condition)
                  ->count();

        $result = M('sales_csfdlmzl')->alias("c")
                  ->field('c.*,m.city,m.level,m.dept,m.ratio,m.corps,m.dev_division,m.dev_regiment,m.dev_manage,m.brand_division,m.brand_regiment,m.brand_manage')
                  ->join('left join qz_sales_city_manage as m on m.id = c.city_id')
                  ->where($condition)
                  ->order('c.id desc')
                  ->limit($pagesize.",".$pageRow)
                  ->select();

        return array("result"=>$result,"count"=>$count);
    }

    /**
     * Gets the pause refund user number.
     *
     * @param      string  $startTime  The start time
     *
     * @return     array  The pause refund user number list.
     */
    public function getPauseRefundUserNum($startTime){

        $map['u.classid'] = '3';
        $map['v.type'] = array('IN','4,7');
        $map['v.start_time'][] = array('ELT',date('Y-m-t',$startTime));
        $map['v.start_time'][] = array('EGT',date('Y-m'.'-01',$startTime));

        return M('user_vip')->alias('v')
                ->field('u.cs,v.company_id,sum(if(v.type = 4,1,null)) as pausenum,sum(if(v.type = 7,1,null)) as refundnum')
                ->join('left join qz_user u on u.id = v.company_id')
                ->where($map)
                ->group('v.company_id')
                ->select();
    }

    /**
     * 全瞰-会员数完成率
     *
     * @param      integer          $times  The times
     * @param      integer          $year   The year
     * @param      string           $dept   The dept
     * @param      array            $condittion   管辖城市ID（查询条件数组）
     * @param      string           $level  The level
     *
     * @return     array            会员数完成率列表.
     */
    public function getQkHyswcl($year,$dept = '',$level = '',$condition = ''){
        if(!empty($dept)){
            $map['m.dept'] = array('EQ',$dept);
        }

        $map[] = array(
            array(
                'c.month' => array('NEQ','1'),
                'c.year' => array("EQ",$year)
            ),
            array(
                'c.month' => array("LT",'2'),
                'c.year' => array("EQ",$year+1)
            ),
            '_logic' => 'OR',
        );

        if(!empty($level)){
            $level = " AND m.level = '1' ";
        }

        //if(!empty($condition)){
            $map['c.cid'] = $condition['c.cid'];
        //}

        return M('sales_cshyswcl')->alias('c')
                ->field("count(c.cid) AS 'csnum',SUM(c.hyzb) AS 'hyzb',SUM(c.vipcnt) AS 'vipcnt',SUM(c.doublecnt) AS 'doublecnt',SUM(c.hywcl) AS 'hywcl',lpad(c.month,2,'0') as month,c.year")
                ->join("left JOIN qz_sales_city_manage as m ON  m.id = c.cid $level")
                ->where($map)
                ->group('month')
                ->order('year,month')
                ->select();
    }

    /**
     * 获取 全瞰-分单量进度 月份数据
     * @param      array            $map        月份、年份数组
     * @param      array            $condition        管辖的城市
     * @return     array            $result     查询结果数组
     */
    public function getVipFenDanByMonth($map,$condition){
        //if(!empty($condition)){
            $map['cid'] = $condition;
        //}
        $result = M("sales_city_fdsxq")->where($map)->select();
        return $result;
    }


    /*
    *  按城市名取城市信息（qz_sales_city_manage）
    * @param    string   $name         城市名称
    * @return   array    $city         城市信息
    */
    public function getCityInCityManage($name){
        $map = array(
            'm.city' => array("EQ",$name)
        );
        return M('sales_city_manage')->alias('m')
                        ->join("left join qz_quyu as q on m.city = q.cname")
                        ->field('m.*,q.cid')->where($map)->find();
    }

    /**
     * 编辑 -城市重点系数表
     * @param      integer  $id     The identifier
     * @param      array    $data   The data
     * @return     mixed    boolean
     */
    public function editSalesCityRatio($id,$data){
        $map = array(
            'manage_id' => array("EQ",$id),
        );
        return  M('sales_city_ratio')->where($map)->save($data);
    }

    /**
     * 添加 -城市重点系数表
     * @param      array    $data   The data
     * @return     mixed    boolean
     */
    public function addSalesCityRatio($data){
        return M('sales_city_ratio')->add($data);
    }

    /**
     * 是否存在 -城市重点系数表
     * @param      array    $map   The data
     * @return     mixed    boolean
     */
    public function hasSalesCityRatio($map){
       return M('sales_city_ratio')->field('*')->where($map)->find();
    }

    /**
     * 获取城市重点系数
     */
    public function getSalesCityRatio(){

        $map = array(
            "a.classid" => array("EQ",3),
            "a.on" => array("EQ",2),
            "b.fake" => array("EQ",0)
        );
        return M('user')->alias("a")
            ->field('count(*) as huiyuan,a.cs,c.manage_id,c.cname,c.ratio')
            ->join("join qz_user_company b on a.id = b.userid")
            ->join("join qz_sales_city_ratio c on c.cid = a.cs")
            ->where($map)
            ->order('c.ratio desc,c.manage_id')
            ->group('cs')
            ->select();
    }
    

    /**
     * 获取城市计划月分单数
     * @return
     */
    public function getSettingPlanValueList($manage_id){
        if(!empty($manage_id)){
            $condition['manage_id'] = array('in',$manage_id);
            $condition['module'] = array('EQ',4);
            $buildSql = M('sales_setting_value')
                ->where($condition)
                ->order('lasttime desc')
                ->buildSql();
            return M('sales_setting_value')->table($buildSql)->alias('a')
                ->field('a.point,a.manage_id')
                ->group('manage_id')
                ->select();
        }
    }

    /**
     * 获取城市实际月分单数(仅会员)
     * @param $cid 城市列表
     *  @param $start_time 开始时间
     *  @param $start_time 结束时间
     * @return
     */
    public function getSettinggFendanList($cid,$start_time,$end_time){
        if(!empty($cid)){
            $map    = array(
                'u.on' => array('EQ', 2),
                'u.cs' => array('IN',  $cid),
                'u.classid' => array('EQ', 3),
                'i.type_fw' =>array('EQ',1),
                'i.addtime' =>array('BETWEEN', array($start_time, $end_time))
            );
            return M('order_info')->alias('i')
                ->field('r.manage_id,count(*) as fendan')
                ->join('INNER JOIN qz_orders o ON o.id = i.order and o.`on` = 4')
                ->join('INNER JOIN qz_user u ON u.id = i.com')
                ->join('INNER JOIN qz_sales_city_ratio r ON r.cid = u.cs')
                ->where($map)
                ->group('u.cs')
                ->select();
        }

    }

    /**
     * 超出20单来自渠道推广的订单
     *  @param $num 超出部分数字
     *  @param $cid 城市id
     *  @param $start_time 开始时间
     *  @param $start_time 结束时间
     * @return
     */
    public function getMoreSrcOrder($num,$cid,$start_time,$end_time){
            $map    = array(
                'u.on' => array('EQ', 2),
                'u.cs' => array('EQ',  $cid),
                'u.classid' => array('EQ', 3),
                'i.type_fw' =>array('EQ',1),
                'i.addtime' =>array('BETWEEN', array($start_time, $end_time))
            );
            $buildSql =  M('order_info')->alias('i')
                ->field('o.id as oid,o.name,o.time_real,s1.name as src_name,o.cs,o.qx')
                ->join('INNER JOIN qz_orders o ON o.id = i.order and o.`on` = 4')
                ->join('INNER JOIN qz_user u ON u.id = i.com')
                ->join('INNER JOIN qz_orders_source s ON s.orderid = o.id')
                ->join('INNER JOIN qz_order_source s1 ON s1.src = s.source_src')
                ->where($map)
                ->order('i.addtime desc')
                ->limit($num)
                ->buildSql();
        $buildSql2 =  M('order_info')->table($buildSql)->alias('y')
            ->field('y.*,q.cname as city,z.qz_area as area ')
            ->join('INNER JOIN qz_quyu q ON q.cid = y.cs')
            ->join('INNER JOIN qz_area z ON z.qz_areaid = y.qx')
            ->buildSql();

            return M('order_info')->table($buildSql2)->alias('a')
                ->field('a.name,FROM_UNIXTIME(a.time_real,"%Y-%m-%d") as order_time,a.src_name,a.city,a.area')
                ->group('a.oid')
                ->select();
    }

}