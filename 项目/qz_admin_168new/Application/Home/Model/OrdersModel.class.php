<?php
//订单表

namespace Home\Model;
Use Think\Model;

class OrdersModel extends Model
{
    protected $autoCheckFields = false;
    protected $_validate = array(
        array('name','require','请填写业主名称',1,"",1),//编辑订单
        array('fangshi','require','请选择装修方式',1,"",1),//编辑订单
        array('yusuan','require','请选择预算金额',1,"",1),//编辑订单
        array('qx','require','请选择订单区县',1,"",1),//编辑订单,
        array('huxing','require','请选择户型结构',1,"",1),//编辑订单
        array('fengge','require','请选择装修风格',1,"",1),//风格
        // array('lx','require','请选择装修类型（公装、家装）',1,"",1), //公装 家装
        // array('lxs','require','请选择装修类型(新房、旧房)',1,"",1), //新房 旧房
    );

        /**
     * [getOrderStatusDescription 获取订单状态描述，附带数据库字段标志信息]
     * @param  boolean $withAll [是否带有‘全部’描述状态]
     * @return [type]           [description]
     */
    public function getOrderStatusDescription($withAll = true,$type = 1){
        //订单状态，备注：该数组的key不是递增的
        if($type == 1){
            $result = array(
                '0'  => array('text' => '全部'),
                '11' => array(
                    'text'  => '新单',
                    'param' =>array('on' => 0, 'on_sub' => 10),
                    'order' =>'p.sort,o.time_real DESC'
                ),
                '12' => array(
                    'text'  => '次新单',
                    'param' =>array('on' => 0, 'on_sub' => 9),
                    'order' =>'p.sort,o.time_real ASC'
                ),
                '13' => array(
                    'text' => '扫单',
                    'param' =>array('on' => 0, 'on_sub' => 8),
                    'order'=>'o.time_real DESC'
                ),
                '14' => array(
                    'text' => '待定单',
                    'param' =>array('on' => 2),
                    'order'=>'`on` ASC, visitime ASC, id DESC'
                ),
                '15' => array(
                    'text' => '未审核(含新单、次新单、扫单)',
                    'param' =>array('on' => 0),
                    'order'=>'o.on_sub DESC,o.time_real DESC'
                ),
                '23' => array(
                    'text' => '已审(有效)',
                    'param' =>array('on' => 4),
                    'order'=>'o.time_real DESC'
                ),
                '16' => array(
                    'text' => '有效未分配',
                    'param' =>array('on' => 4, 'type_fw' => 0),
                    'order'=>'o.time_real DESC'
                ),
                '17' => array(
                    'text' => '分单',
                    'param' =>array('on' => 4, 'type_fw' => 1),
                    'order'=>'o.time_real DESC'
                ),
                '18' => array(
                    'text' => '分没人跟',
                    'param' =>array('on' => 4, 'type_fw' => 3),
                    'order'=>'o.time_real DESC'
                ),
                '19' => array(
                    'text' => '赠单',
                    'param' =>array('on' => 4, 'type_fw' => 2),
                    'order'=>'o.time_real DESC'
                ),
                '21' => array(
                    'text' => '赠没人跟',
                    'param' =>array('on' => 4, 'type_fw' => 4),
                    'order'=>'o.time_real DESC'
                ),
                '22' => array(
                    'text' => '无效',
                    'param' =>array('on' => array(5,6,7,8,9)),
                    'order'=>'o.time_real DESC'
                ),
                '24' => array(
                    'text' => '暂时无效',
                    'param' =>array('on' => 98),
                    'order'=>'o.time_real ASC'
                ),
                '25' => array(
                    'text' => '撤回单',
                    'param' =>array('on' => 99),
                    'order'=>'o.time_real DESC'
                ),
                '26' => array(
                    'text' => '待分配',
                    'param' =>array('on' => 4, 'type_fw' => array(5,6)),
                    'order'=>'o.time_real DESC'
                ),
            );
        }else{
            $result = array(
                '0'  => array('text' => '全部'),
                '11' => array(
                    'text'  => '新单',
                    'param' =>array('on' => 0, 'on_sub' => 10),
                    'order' =>'p.sort,o.time_real DESC'
                ),
                '12' => array(
                    'text'  => '次新单',
                    'param' =>array('on' => 0, 'on_sub' => 9),
                    'order' =>'p.sort,IF(o.remarks="已回访",0,1) ,o.time_real ASC'
                ),
                '13' => array(
                    'text' => '扫单',
                    'param' =>array('on' => 0, 'on_sub' => 8),
                    'order'=>'IF(o.remarks="已回访",0,1) ,o.time_real DESC'
                ),
                '14' => array(
                    'text' => '待定单',
                    'param' =>array('on' => 2),
                    'order'=>'IF(o.remarks="已回访",0,1) ,`on` ASC, visitime ASC, id DESC'
                ),
                '15' => array(
                    'text' => '未审核(含新单、次新单、扫单)',
                    'param' =>array('on' => 0),
                    'order'=>'o.on_sub DESC,o.time_real DESC'
                ),
                '23' => array(
                    'text' => '已审(有效)',
                    'param' =>array('on' => 4),
                    'order'=>'o.time_real DESC'
                ),
                '16' => array(
                    'text' => '有效未分配',
                    'param' =>array('on' => 4, 'type_fw' => 0),
                    'order'=>'o.time_real DESC'
                ),
                '17' => array(
                    'text' => '分单',
                    'param' =>array('on' => 4, 'type_fw' => 1),
                    'order'=>'o.time_real DESC'
                ),
                '18' => array(
                    'text' => '分没人跟',
                    'param' =>array('on' => 4, 'type_fw' => 3),
                    'order'=>'o.time_real DESC'
                ),
                '19' => array(
                    'text' => '赠单',
                    'param' =>array('on' => 4, 'type_fw' => 2),
                    'order'=>'o.time_real DESC'
                ),
                '21' => array(
                    'text' => '赠没人跟',
                    'param' =>array('on' => 4, 'type_fw' => 4),
                    'order'=>'o.time_real DESC'
                ),
                '22' => array(
                    'text' => '无效',
                    'param' =>array('on' => array(5,6,7,8,9)),
                    'order'=>'IF(o.remarks="已回访",0,1) ,o.time_real DESC'
                ),
                '24' => array(
                    'text' => '暂时无效',
                    'param' =>array('on' => 98),
                    'order'=>'o.time_real ASC'
                ),
                '25' => array(
                    'text' => '撤回单',
                    'param' =>array('on' => 99),
                    'order'=>'o.time_real DESC'
                ),
                '26' => array(
                    'text' => '待分配',
                    'param' =>array('on' => 4, 'type_fw' => array(5,6)),
                    'order'=>'o.time_real DESC'
                ),
                '27' => array(
                    'text' => '未分配订单',
                    'param' =>array('on' => 4, 'type_fw' => [1,2]),
                    'order'=>'o.time_real DESC'
                ),
            );
        }



        if ($withAll == false) {
            unset($result['0']);
        }
        return $result;
    }

    public function getOrderSource(){
        $result = array(
            "0" => "-请选择-",
            "4" => "业主发布",
            "1" => "在线客服",
            "2" => "400电话",
            "3" => "QQ咨询",
            "10" => "微信咨询",
            "11" => "推广部",
            "5" => "赠送单生成",
            "100" => "非业主发布"
        );
        return $result;
    }

    public function getOrderZhuanFaRen(){
        $result = array(
            "夏秀秀",
            "解丹丹",
            "白宁宁",
            "孟淑畅",
            "刘玉洁"
        );
        return $result;
    }

    /**
     * [getOrderTelTongji description]
     * @param  [type] $date [description]
     * @return [type]       [description]
     */
    public function getOrderTelTongji($start,$end,$cs)
    {
        $where = "where time_real >= '$start' and time_real < '$end'";
        if (!empty($cs)) {
            $where .= " and cs = ".$cs;
        }

        $sql = 'select
                    q.cname,
                    nowdate,
                    count(*) count,
                    count(if(mark = 1,1,null)) as "3-8",
                    count(if(mark = 2,1,null)) as "8-12",
                    count(if(mark = 3,1,null)) as "12-20",
                    count(if(mark = 4,1,null)) as "20-30",
                    count(if(mark = 5,1,null)) as "30以上"
                from (
                        select t1.*,
                            case when time_add is null then 4
                                     when inside = 1 and time_add <= CONCAT(nowdate," ","10:30:00") then 1
                                     when inside = 2 and time_add <= CONCAT(nowdate," ","14:00:00") THEN 1
                                     when inside = 3 and time_add <= CONCAT(nowdate," ","19:30:00") THEN 1
                                     when inside = 4 and timediff <= 8 then 1
                                     when inside = 4 and timediff > 8 and timediff <= 12 then 2
                                     when inside = 4 and  timediff > 12 and timediff <= 20 then 3
                                     when inside = 4 and  timediff > 20 and timediff <= 30 then 4
                                     when inside = 4 and  timediff > 30 then 5
                                     else 5
                            end as mark
                        from (
                                select t.id,t.cs,
                                        nowdate,
                                        TIMESTAMPDIFF(MINUTE ,t.time_real,b.time_add)+1 as timediff,
                                        b.time_add,
                                        case
                                            when time_real >=   CONCAT(date_sub(nowdate,INTERVAL 1 DAY)," ","21:00:00") and time_real < CONCAT(nowdate," ","10:30:00") then 1
                                            when time_real >=   CONCAT(nowdate," ","12:00:00") and time_real < CONCAT(nowdate," ","14:00:00") then 2
                                            when time_real >=   CONCAT(nowdate," ","17:30:00") and time_real < CONCAT(nowdate," ","19:30:00") then 3
                                            else 4
                                        end AS inside
                                from (
                                        select
                                                o.id,o.cs,o.time_real,
                                                case when   time_real >= CONCAT(date," ","21:00:00") and time_real < CONCAT(date_add," ","10:30:00") then date_add
                                                else date
                                                end as nowdate
                                        from (
                                                SELECT
                                                    id,cs,FROM_UNIXTIME(time_real) as time_real,FROM_UNIXTIME(time_real,"%Y-%m-%d") as date,DATE_ADD(FROM_UNIXTIME(time_real,"%Y-%m-%d"),INTERVAL 1 day) as date_add
                                                from qz_orders
                                                '.$where.'
                                        ) o
                                )t
                                inner join qz_log_telcenter_ordercall a on t.id = a.orderid
                                inner join qz_log_telcenter b on b.callSid = a.callSid and b.action = "CallAuth"
                                GROUP BY id
                        ) t1
                ) t2 inner join qz_quyu as q on q.cid = t2.cs
                group by cs,nowdate;';
        $result = M("Orders")->query($sql);
        return $result;
    }

    /**
     * 获取个人明细列表
     * @param  [type] $start [开始时间]
     * @param  [type] $end   [结束时间]
     * @param  [type] $cs    [城市]
     * @param  [type] $group [客服组]
     * @param  [type] $zz    [客服组长]
     * @param  [type] $name  [客服]
     * @return [type]        [description]
     */
    public function getNewOrderSingerList($start,$end,$cs,$group,$zz,$name)
    {
        $monthStart = date("Y-m-d",strtotime("-1 day",mktime(0,0,0,date("m",$start),1,date("Y",$start))));
        $monthEnd = date("Y-m-d",mktime(0,0,0,date("m",$start),date("t",$start),date("Y",$start)));

        $where = " where 1 = 1";
        if (!empty($group)) {
            $where .= " and t3.kfgroup = $group";
        }

        if (!empty($zz)) {
            $where .= " and t3.zzname = '$zz'";
        }

        if (!empty($name)) {
            $where .= " and t3.id = '$name'";
        }

        if (!empty($cs)) {
            $subWhere = " and o.cs = '$cs'";
        }

        $sql = 'select t3.id,t3.name,t3.kfgroup,t3.zzname,t2.cs,q.cname,t2.nowdate,t2.ordercount,t2.neworder from (
                    select t1.*
                    from (
                                SELECT o.cs,count(o.id) as ordercount,
                                count(if(o.on = 0 and o.on_sub = 10,1,null)) as neworder,
                                case when
                                FROM_UNIXTIME(time_real) >= CONCAT(FROM_UNIXTIME(time_real,"%Y-%m-%d")," ","21:00:00") and FROM_UNIXTIME(time_real) < CONCAT(DATE_ADD(FROM_UNIXTIME(time_real,"%Y-%m-%d"),INTERVAL 1 day)," ","10:30:00") then DATE_ADD(FROM_UNIXTIME(time_real,"%Y-%m-%d"),INTERVAL 1 day)
                                else FROM_UNIXTIME(time_real,"%Y-%m-%d")
                                end as nowdate
                                from qz_orders o
                                where o.time_real >= UNIX_TIMESTAMP("'.$monthStart.' 21:00:00") and o.time_real <= UNIX_TIMESTAMP("'.$monthEnd.' 21:00:00") '.$subWhere.'
                                group by o.cs,nowdate
                    ) t1
        ) t2
        left join
        (
                select t2.*,u1.name as zzname from (
                        select t1.* from (
                                select a.id,a.`name`,a.kfgroup,FROM_UNIXTIME(b.time,"%Y-%m-%d") as logindate,b.cs from qz_adminuser a
                                inner join qz_admin_logging as b on a.id = b.uid and `status` = 1 and b.time >= '.$start.' and b.time < '.$end.'
                                where a.uid = 2 and a.stat = 1 and a.kftype = 1 and a.kfgroup <> 0
                                ORDER BY b.time desc
                        ) t1 group by t1.id,logindate
                ) t2
                left join qz_adminuser as u1 on  u1.uid = 31 and u1.kfgroup = t2.kfgroup  and u1.stat = 1  and kftype = 1 and u1.kfgroup <> 0
        ) t3 on FIND_IN_SET(t2.cs,t3.cs)
        inner join qz_quyu as q on q.cid = t2.cs
        '.$where.'
        order by nowdate';

        return M()->query($sql);
    }

    /**
     * 获取日有效率列表
     * @param  [type] $start [开始日期]
     * @param  [type] $end   [结束日期]
     * @return [type]        [description]
     */
    public function orderseffectiverateList($start,$end,$cs)
    {
        if (!empty($cs)) {
            $where = " and o.cs = '$cs'";
        }

        $sql = 'select
                    q.cname,
                    t4.*
                from (
                        select
                            if(u.cs is not null,1,0) as vip,
                            t3.*,
                            round(fenCount/count,2)*100 as fen_rate,
                            round(fenNowCount/count,2)*100 as fenNow_rate,
                            round(zenCount/count,2)*100 as zen_rate,
                            round(fen_sub_count/count,2)*100 as fen_sub_rate,
                            round(subnew_fen_count/count,2)*100 as subnew_rate,
                            round(dd_fen_count/count,2)*100 as ddfen_rate,
                            round(sd_fen_count/count,2)*100 as sdfen_rate
                        from (
                                select
                                        t2.cs,
                                        SUM(count) as count,
                                        SUM(newfencount) as fenNowCount,
                                        SUM(newCount) as newCount,
                                        SUM(subnewCount) as subnewCount,
                                        SUM(ddCount) as ddCount,
                                        SUM(sdCount) as sdCount,
                                        SUM(wxCount) as wxCount,
                                        SUM(fenCount) as fenCount,
                                        SUM(zenCount) as zenCount,
                                        SUM(fen_sub_Count) as fen_sub_Count,
                                        SUM(zen_sub_Count) as zen_sub_Count,
                                        SUM(khCount) as khCount,
                                        SUM(zxgsCount) as zxgsCount,
                                        SUM(subnewfencount) as subnew_fen_count,
                                        SUM(ddfencount) as dd_fen_count,
                                        SUM(sdfencount) as sd_fen_count,
                                        SUM(othercount) as othercount
                                        from (
                                select
                                    t4.*,
                                    count(if(fencount = 1 and newfencount = 0 and subnewfencount = 0 and sdfencount = 0 and ddfencount = 0,1,null)) as othercount
                                    from (
                                            select
                                            t3.*,
                                            count(if(ddcount = 1 and fencount = 1 and newfencount = 0 and subnewfencount = 0 and sdfencount = 0,1,null)) as ddfencount
                                            from (
                                                    select
                                                    t2.*,
                                                    count(if(sdcount = 1 and fencount = 1 and newfencount = 0 and subnewfencount = 0,1,null)) as sdfencount
                                                    from (
                                                            select
                                                            t1.*,
                                                            count(if(subnewcount = 1 and fencount = 1 and newfencount = 0,1,null)) as subnewfencount
                                                            from (
                                                                    select
                                                                            t.id,
                                                                            t.cs,
                                                                            count(DISTINCT if(nowmark = 1,1,null)) as count,
                                                                            count(if(`on` = 0 and on_sub = 10 and nowmark = 1,1,null)) as newCount,
                                                                            count(if(`on` = 0 and on_sub = 9 ,1,null)) as subnewCount,
                                                                            count(if(`on` = 2,1,null)) as ddCount,
                                                                            count(if(`on` = 0 and on_sub = 8 ,1,null)) as sdCount,
                                                                            count(if(`on` = 5 and nowmark = 1,1,null)) as wxCount,
                                                                            count(if(`on` = 4 and type_fw = 1 and time = "'.$end.'" and time_real = "'.$end.'",1,null)) as newfencount,
                                                                            count(if(`on` = 4 and type_fw = 1 and time = "'.$end.'",1,null)) as fenCount,
                                                                            count(if(`on` = 4 and type_fw = 2,1,null)) as zenCount,
                                                                            count(if(`on` = 4 and type_fw = 3 and time = "'.$end.'",1,null)) as fen_sub_Count,
                                                                            count(if(`on` = 4 and type_fw = 4 and time = "'.$end.'",1,null)) as zen_sub_Count,
                                                                            count(if(`on` = 6,1,null)) as khCount,
                                                                            count(if(`on` = 7,1,null)) as zxgsCount
                                                                        from (
                                                                                select
                                                                                    o.id,
                                                                                    o.cs,
                                                                                    o.type_fw,
                                                                                    ch.on,
                                                                                    ch.on_sub,
                                                                                    FROM_UNIXTIME(time_add,"%Y-%m-%d") as time,
                                                                                    FROM_UNIXTIME(time_real,"%Y-%m-%d") as time_real,
                                                                                    if( (time_add >= UNIX_TIMESTAMP("'.$start.' 21:00:00") and time_add <= UNIX_TIMESTAMP("'.$end.' 21:00:00")) and (time_real >= UNIX_TIMESTAMP("'.$start.' 21:00:00") and time_real <= UNIX_TIMESTAMP("'.$end.' 21:00:00")),1,0) as nowmark
                                                                                from qz_orders_status_change ch
                                                                                inner join qz_orders o on o.id = ch.orderid
                                                                                where time_add >= UNIX_TIMESTAMP("'.$start.' 21:00:00") and time_add <= UNIX_TIMESTAMP("'.$end.' 21:00:00") '.$where.'
                                                                        ) t group by id
                                                                ) t1 group by id
                                                     ) t2 group by id
                                                )t3 group by id
                                        ) t4 group by id
                                    ) t2 group by cs
                                ) t3
                                left join (
                                        select a.cs from qz_user a
                                        inner join qz_user_company b on a.id = b.userid and b.fake = 0
                                        where a.on = 2 GROUP BY a.cs
                                ) u on u.cs= t3.cs
                ) t4 inner join qz_quyu as q on q.cid = t4.cs
                order by cs ';

        return M()->query($sql);
    }


    /**
     * 获取日有效率列表
     * @param  [type] $start [开始日期]
     * @param  [type] $end   [结束日期]
     * @param  [type] $cs    [城市]
     * @return [type]        [description]
     */
    public function orderseffectiveratemonthList($start,$end,$cs)
    {
        if (!empty($cs)) {
            $where = " and o.cs = '$cs'";
        }

        $sql = 'select
                    if(u.cs is not null,1,0) as vip,
                    q.cname,
                    t3.*,
                    round(fencount/count,2)*100 as fen_rate,
                    round(zencount/count,2)*100 as zen_rate,
                    round(fencount/( count - wxcount),2)*100 as fen_not_wx_rate,
                    round(subnewfencount/count,2)*100 as subnew_rate,
                    round(fen_sub_count/count,2)*100 as fen_sub_rate,
                    round((fen_sub_count+zen_sub_count)/(fencount+zencount),2)*100 as fen_zen_rate
            from(
                    select
                            t2.cs,
                            sum(t2.count) as count,
                            sum(t2.newcount) as newcount,
                            sum(t2.subnewcount) as subnewcount,
                            sum(t2.sdcount) as sdcount,
                            sum(t2.ddcount) as ddcount,
                            sum(t2.fencount) as fencount,
                            sum(t2.zencount) as zencount,
                            sum(t2.fen_sub_count) as fen_sub_count,
                            sum(t2.zen_sub_count) as zen_sub_count,
                            sum(t2.wxcount) as wxcount,
                            sum(t2.newfencount) as newfencount,
                            sum(t2.khcount) as khcount,
                            sum(t2.zxgscount) as zxgscount,
                            sum(t2.subnewfencount) as subnewfencount,
                            sum(t2.sdfencount) as sdfencount,
                            sum(t2.ddfencount) as ddfencount,
                            sum(t2.othercount) as othercount
                    from (
                            select
                            t4.*,
                            count(if(fencount = 1 and newfencount = 0 and subnewfencount = 0 and sdfencount = 0 and ddfencount = 0,1,null)) as othercount
                            from (
                                    select
                                    t3.*,
                                    count(if(ddcount = 1 and fencount = 1 and newfencount = 0 and subnewfencount = 0 and sdfencount = 0,1,null)) as ddfencount
                                    from (
                                                select
                                                t2.*,
                                                count(if(sdcount = 1 and fencount = 1 and newfencount = 0 and subnewfencount = 0,1,null)) as sdfencount
                                                from (
                                                        select
                                                        t1.*,
                                                        count(if(subnewcount = 1 and fencount = 1 and newfencount = 0,1,null)) as subnewfencount
                                                        from (
                                                                select
                                                                        t.id,
                                                                        t.cs,
                                                                        count(DISTINCT if(nowmark = 1,1,null)) as count,
                                                                        COUNT(DISTINCT if(`on` = 0 and on_sub = 10 and nowmark = 1,1,null)) as newcount,
                                                                        count(DISTINCT if(`on` = 0 and on_sub = 9,1,null)) as subnewcount,
                                                                        count(DISTINCT if(`on` = 0 and on_sub = 8 ,1,null)) as sdcount,
                                                                        count(DISTINCT if(`on` = 2,1,null)) as ddcount,
                                                                        count(DISTINCT if(`on` = 4 and type_fw = 1,1,null)) as fencount,
                                                                        count(DISTINCT if(`on` = 4 and type_fw = 2,1,null)) as zencount,
                                                                        count(DISTINCT if(`on` = 4 and type_fw = 3,1,null)) as fen_sub_count,
                                                                        count(DISTINCT if(`on` = 4 and type_fw = 4,1,null)) as zen_sub_count,
                                                                        count(DISTINCT if(`on` = 5 and nowmark = 1 ,1,null)) as wxcount,
                                                                        count(DISTINCT if(`on` = 4 and type_fw = 1 and nowmark = 1,1,null)) as newfencount,
                                                                        count(DISTINCT if(`on` = 6,1,null)) as khcount,
                                                                        count(DISTINCT if(`on` = 7,1,null)) as zxgscount
                                                                    from (
                                                                                select o.id,
                                                                                            o.cs,
                                                                                            o.type_fw,
                                                                                            ch.on,
                                                                                            ch.on_sub,
                                                                                            FROM_UNIXTIME(time_real,"%Y-%m-%d") as time_real,
                                                                                            FROM_UNIXTIME(time_add,"%Y-%m-%d") as time_add,
                                                                                            if((time_real >= UNIX_TIMESTAMP("'.$start.' 21:00:00") and time_real <= UNIX_TIMESTAMP("'.$end.' 21:00:00")),1,0) as nowmark
                                                                                from qz_orders_status_change as ch
                                                                                inner join qz_orders as o on o.id = ch.orderid
                                                                                where time_add >= UNIX_TIMESTAMP("'.$start.' 21:00:00") and time_add <= UNIX_TIMESTAMP("'.$end.' 21:00:00") '.$where.'
                                                                    ) t group by id
                                                            ) t1 group by id
                                                    )t2 group by id
                                        )t3 group by id
                            )t4 group by id
                        ) t2 group by cs
                )t3
                left join (
                        select a.cs from qz_user a
                        inner join qz_user_company b on a.id = b.userid and b.fake = 0
                        where a.on = 2 GROUP BY a.cs
                ) u on u.cs= t3.cs
                inner join qz_quyu as q on q.cid = t3.cs';
        return M()->query($sql);
    }


    /**
     * 当日拨打率
     * @param  [type] $start [description]
     * @param  [type] $end   [description]
     * @param  [type] $cs    [description]
     * @return [type]        [description]
     */
    public function callrete($start,$end,$cs)
    {
        if (!empty($cs)) {
            $where = " and o.cs = '$cs'";
        }

        $timeStart = strtotime($start);
        $monthStart = date("Y-m-d",mktime(0,0,0,date("m",$timeStart),1,date("Y",$timeStart)));
        $monthEnd = date("Y-m-d",strtotime("+1 day",strtotime($end)));

        $sql = 'select
                t4.vip,
                t4.cs,
                q.cname,
                sum(monthnew) as monthnew,
                sum(newmark) as newmark,
                sum(telmonthnew) as telmonthnew,
                sum(telnewmark) as telnewmark,
                sum(allsubnew) as allsubnew,
                sum(telmonthsubnewmark) as telmonthsubnewmark,
                sum(subnewmark) as subnewmark,
                sum(telsubnewmark) as telsubnewmark,
                sum(sdmark) as sdmark,
                sum(telsdmark) as telsdmark,
                sum(ddmark) as ddmark,
                sum(telddmark) as telddmark
                from (
            select
                if(u.cs is not null,1,0) as vip,
                t3.*
                from (
                    select
                    t2.orderid,
                    o.cs,
                    if(monthnew > 0,1,0) as monthnew,
                    if(monthnew > 0 and telmark > 0,1,0) as telmonthnew,
                    if(newmark > 0,1,0) as newmark,
                    if(newmark >0 and telsubnewmark > 0,1,0) as telnewmark,
                    if(allsubnew > 0,1,0) as allsubnew,
                    if(telmonthsubnewmark > 0,1,0) as telmonthsubnewmark,
                    if(subnewmark > 0,1,0) as subnewmark,
                    if(telsubnewmark > 0,1,0) as telsubnewmark,
                    if(sdmark > 0,1,0) as sdmark,
                    if(telsdmark > 0,1,0) as telsdmark,
                    if(ddmark > 0,1,0) as ddmark,
                    if(telddmark > 0,1,0) as telddmark
                    from (
                            select
                            t1.orderid,
                            sum(monthnew) as monthnew,
                            sum(newmark) as newmark,
                            sum(allsubnew) as allsubnew,
                            sum(telmonthsubnewmark) as telmonthsubnewmark,
                            sum(subnewmark) as subnewmark,
                            sum(telsubnewmark) as telsubnewmark,
                            SUM(sdmark) as sdmark,
                            SUM(telsdmark) as telsdmark,
                            SUM(ddmark) as ddmark,
                            SUM(telddmark) as telddmark,
                            sum(telmark) as telmark
                            from (
                                select
                                t.orderid,
                                t.callSid,
                                t.on,
                                t.on_sub,
                                t.time_add,
                                t.tel_time_add,
                                if(`on` = 0 and on_sub = 10,1,0) as monthnew,
                                if(time_add >= "'.$start.' 21:00:00" and time_add < "'.$end.' 21:00:00" and `on` = 0 and on_sub = 10 ,1,0) as newmark,
                                if(`on` = 0 and on_sub = 9,1,0) as allsubnew,
                                if(`on` = 0 and on_sub = 9  and time_add is not null,1,0) as telmonthsubnewmark,
                                if(time_add >= "'.$start.' 17:50:00" and time_add < "'.$end.' 17:50:00" and `on` = 0 and on_sub = 9 ,1,0) as subnewmark,
                                if(time_add >= "'.$start.' 17:50:00" and time_add < "'.$end.' 17:50:00" and  tel_time_add is not null and `on` = 0 and on_sub = 9 ,1,0) as telsubnewmark,
                                if(`on` = 0 and on_sub = 8 ,1,0) as sdmark,
                                if(`on` = 0 and on_sub = 8 and tel_time_add is not null ,1,0) as telsdmark,
                                if(`on` = 2 ,1,0) as ddmark,
                                if(`on` = 2 and  tel_time_add is not null,1,0) as telddmark,
                                if(tel_time_add is not null,1,0) as telmark
                                from (
                                    select
                                    t1.*
                                    from (
                                        select
                                        ch.orderid,
                                        tel.callSid,
                                        ch.on,
                                        ch.on_sub,
                                        tel.on as tel_on,
                                        tel.on_sub as tel_on_sub,
                                        FROM_UNIXTIME(ch.time_add) as time_add,
                                        tel.time_add as tel_time_add
                                        from qz_orders_status_change ch
                                        left join  qz_log_telcenter_ordercall tel on tel.orderid = ch.orderid   and tel.time_add >= "'.$monthStart.'"
                                        and tel.time_add < "'.$monthEnd.'" and ( (ch.on = tel.on and ch.on_sub = tel.on_sub) OR (FROM_UNIXTIME(ch.time_add,"%Y%m%d%H") = DATE_FORMAT(tel.time_add,"%Y%m%d%H")))
                                        where ch.time_add >= UNIX_TIMESTAMP("'.$monthStart.'") and ch.time_add < UNIX_TIMESTAMP("'.$monthEnd.'")
                                        order by orderid,tel.time_add desc
                                    ) t1 group by orderid,`on`,on_sub
                                 ) t order by orderid,time_add
                            ) t1 group by orderid
                        ) t2
                        inner join qz_orders o on o.id = t2.orderid
                        inner join qz_quyu as q on q.cid = o.cs
                    ) t3
                    left join
                    (
                            select a.cs from qz_user a
                            inner join qz_user_company b on a.id = b.userid and b.fake = 0
                            where a.on = 2 GROUP BY a.cs
                    ) u on u.cs = t3.cs
                ) t4
                inner join qz_quyu as q on q.cid = t4.cs
                group by cs order by cs';
        return M()->query($sql);
    }


     /**
     * 电话呼出率统计
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function calloutgoingList($begin,$end,$cs)
    {

        if (!empty($cs)) {
            $where  = " where o.cs = '$cs'";
        }
        $timeStart = strtotime($begin);
        $monthStart = date("Y-m-d",mktime(0,0,0,date("m",$timeStart),1,date("Y",$timeStart)));

        $sql = 'select
                cname,
                sum(callcount+uncallcount) as allcount,
                sum(callcount) as callcount,
                sum(uncallcount) as uncallcount,
                sum(nowcall) as nowcall,
                sum(nowcallcount) as nowcallcount,
                sum(nowuncallcount) as nowuncallcount
                from (
                            select
                            q.cname,
                            t1.*,
                            o.cs
                            from (
                                    select
                                    orderid,
                                    time_add,
                                    count(if(calltype = 0,1,null)) as callcount,
                                    count(if(calltype = 1,1,null)) as uncallcount,
                                    count(if(nowcall = 1,1,null)) as nowcall,
                                    count(if(nowcall = 1 and calltype = 0,1,null)) as nowcallcount,
                                    count(if(nowcall = 1 and calltype = 1,1,null)) as nowuncallcount
                                    from (
                                            select
                                            a.orderid,
                                            DATE_FORMAT(a.time_add,"%Y%m%d%H") as time_add,
                                            IF(b.byetype not in (1,2,3,4,-6,-7),1,0) as calltype,
                                            IF(a.time_add >= "'.$begin.'" and a.time_add < "'.$end.'",1,0) as nowcall
                                            from qz_log_telcenter_ordercall  a
                                            join qz_log_telcenter as b on b.callSid = a.callSid and b.action = "Hangup"
                                            where a.time_add >= "'.$monthStart.'" and a.time_add < "'.$end.'"
                                    ) t group by orderid
                            ) t1    join qz_orders o on o.id = t1.orderid
                                    join qz_quyu as q on q.cid = o.cs
                                    '.$where.'
                ) t2 group by cs order by cs';
        return M()->query($sql);
    }

    /**
     * 个人有效率统计
     * @param  [type] $begin [开始时间]
     * @param  [type] $end   [结束时间]
     * @return [type]        [description]
     */
    public function singleseffectiverateList($end,$class,$group,$kf)
    {
        $where = "where 1=1";
        if (!empty($class)) {
           $where .= " and kftype = ".$class;
        }

        if (!empty($group)) {
           $where .= " and kfgroup = ".$group;
        }


        if (!empty($kf)) {
           $where .= " and uid = ".$kf;
        }


        $timeStart = strtotime($end);
        $monthStart = date("Y-m-d",mktime(0,0,0,date("m",$timeStart),1,date("Y",$timeStart)));
        $monthEnd = date("Y-m-d",strtotime("+1 day",strtotime($end)));

        $orderStart = date("Y-m-d",strtotime("-1 day",strtotime($monthStart)));
        $sql = 'select
                t4.uid,t4.username,
                t4.kftype,t4.kfgroup,
                t4.nowdate,
                SUM(fencount) fencount,
                SUM(zencount) zencount,
                sum(count) count
                from (
                        select
                            t2.cs,
                            t2.uid,
                            t2.username,
                            t2.kftype,t2.kfgroup,
                            t2.nowdate,
                            if(t3.fencount is not null,t3.fencount,0) as fencount,
                            if(t3.zencount is not null,t3.zencount,0) as zencount,
                            t2.count
                        from (
                            select
                            t1.cs,
                            t1.nowdate,
                            l.uid,l.username,
                            l.kftype,l.kfgroup,
                            IF(l.kftype = 1,daycount,nightcount) as count
                            from (
                                    select
                                    cs,
                                    nowdate,
                                    count(if(ordertype = 1,1,null)) as daycount,
                                    count(if(ordertype = 0,1,null)) as nightcount
                                    from (
                                        select
                                        t.*,
                                        if(time_real >= UNIX_TIMESTAMP(CONCAT(DATE_SUB(nowdate,INTERVAL 1 DAY)," ","21:00:00")) AND time_real <  UNIX_TIMESTAMP(CONCAT(nowdate," ","17:30:00")),1,0) as ordertype
                                        from (
                                            select cs,
                                            time_real,
                                            case
                                                    when    time_real >= UNIX_TIMESTAMP(CONCAT(FROM_UNIXTIME(time_real,"%Y-%m-%d")," ","21:00:00"))
                                                    and time_real < UNIX_TIMESTAMP(CONCAT(DATE_ADD(FROM_UNIXTIME(time_real,"%Y-%m-%d"),INTERVAL 1 day)," ","23:59:59"))
                                                    then DATE_ADD(FROM_UNIXTIME(time_real,"%Y-%m-%d"),INTERVAL 1 day)
                                            else FROM_UNIXTIME(time_real,"%Y-%m-%d")
                                            end as nowdate
                                            from qz_orders o where o.time_real >= UNIX_TIMESTAMP("'.$orderStart.' 21:00:00") and time_real <= UNIX_TIMESTAMP("'.$end.' 21:00:00")
                                        )t
                                    ) t group by cs,nowdate
                             ) t1
                            join (
                                    select
                                    t.*
                                    from (
                                        select l.uid,u.kfgroup, l.cs,FROM_UNIXTIME(l.time,"%Y-%m-%d") as time,l.username,u.kftype from qz_admin_logging as l
                                        join qz_adminuser u on u.id = l.uid and u.uid = 2
                                        where l.time >= UNIX_TIMESTAMP("'.$monthStart.'") and l.time < UNIX_TIMESTAMP("'.$monthEnd.'") ORDER BY time desc
                                    ) t group by uid,time
                            ) l on FIND_IN_SET(t1.cs,l.cs) and  l.time = t1.nowdate
                    ) t2
                    left join (
                            select
                            cs,
                            user_id,
                            user_name,
                            addtime,
                            count(if(type_fw = 1,1,null)) as fencount,
                            count(if(type_fw = 2,1,null)) as zencount
                            from (
                                    select new.user_id,new.user_name,FROM_UNIXTIME(addtime,"%Y-%m-%d") as addtime,o.type_fw,o.cs from qz_order_csos_new  new
                                    join qz_orders o on o.id = new.order_id
                                    where addtime >= UNIX_TIMESTAMP("'.$monthStart.'") and UNIX_TIMESTAMP("'.$monthEnd.'") and order_on = 4
                            ) t group by user_id,addtime,cs
                    ) t3 on t3.user_id = t2.uid and addtime = nowdate and t3.cs = t2.cs
            ) t4 '.$where.'
            group by uid,nowdate
            ORDER BY nowdate';
        return M()->query($sql);
    }


   /**
    * 月度订单统计数据
    * @param  [type] $begin [开始时间]
    * @param  [type] $end   [结束时间]
    * @param  [type] $name  [用户名]
    * @param  [type] $cs    [城市]
    * @param  [type] $date  [筛选日期]
    * @return [type]        [description]
    */
    public function getMonthOrderStatistics($monthStart,$monthEnd,$begin,$end,$cs,$name,$fen,$fenrate,$id,$group)
    {
        $where = " where 1 = 1";
        if (!empty($cs)) {
            $where .= " and t3.cs in ($cs)";
        }

        if (!empty($name)) {
            $where .= " and id = $name";
        }else{
            if (!empty($id)) {
                $where .= " and id in ($id)";
            }
        }

        if (!empty($fen)) {
             $where .= " and fen >= ".$fen;
        }

        if (!empty($fenrate)) {
             $where .= " and fen_other_rate >= ".round($fenrate/100,2);
        }

         if (!empty($group)) {
             $where .= " and kfgroup = ".$group;
        }

        $sql = "call proc_kftj('$monthStart','$monthEnd','$begin','$end','$where')";
        return $this->db(1,"DB_CONFIG1")->query($sql);
    }

    /**
     * 获取当月的城市分单系数
     * @param  [type] $date [description]
     * @return [type]       [description]
     */
    public function getNowMonthStatistics($date)
    {
        $map = array(
            "date" => array("EQ",$date)
        );
        return M("city_coefficient")->where($map)->count();
    }

     /**
     * 获取客服通话统计
     * @param  [type] $id    [description]
     * @param  [type] $begin [description]
     * @param  [type] $end   [description]
     * @return [type]        [description]
     */
    public function getKfTelCall($id,$begin,$end,$group)
    {
        $where = "";
        if (!empty($id)) {
            $where .= " and t.id in ($id)";
        }

        if (!empty($group)) {
             $where .= " and t.kfgroup = $group";
        }

        $sql = "call proc_kf_tel('$where','$begin','$end')";
        return $this->db(1,"DB_CONFIG1")->query($sql);
    }

    /**
     * 城市审单统计
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getReviewList($begin,$end,$cs,$group,$manager)
    {


        return $this->db(1,"DB_CONFIG1")->query($sql);
    }

    /**
     * [getReviewListGroupByMonth 按月划分订单信息]
     * @param  [type] $begin   [开始时间]
     * @param  [type] $end     [结束时间]
     * @param  [type] $cs      [城市id]
     * @param  [type] $group   [description]
     * @param  [type] $manager [description]
     * @return [type]          [description]
     */
    public function getReviewListGroupByMonth($begin,$end,$cs,$group,$manager)
    {
        $where = 'where 1 = 1';

        if (!empty($cs)) {
            $where .= " and cid = '".$cs."'";
        }

        if (!empty($group)) {
            $where .= " and kf_id = '".$group."'";
        }

        if (!empty($manager)) {
            $where .= " and manager_id = '".$manager."'";
        }

        $sql = "select
                *
                from (
                    select
                    t5.*,
                    t6.kfgroup,
                    t6.manager,
                    t6.manager_id,
                    t6.name,
                    t6.kf_id
                    from(
                        SELECT t3.*,
                        if(t4.day is not null,round(t4.day,2),1) AS day,
                        t3.fen*if(t4.day is not null,round(t4.day,2),1) as 'last_fen',
                        round((t3.fen + ( t3.zen / 10 )) / `all`, 4) * 100 as 'last_fen_rate'
                        from(
                            select t.*,
                            round((fen + zen) / `all`, 4) * 100 as 'fz_rate',
                            round(fen / `all`, 4) * 100 as 'fen_rate'
                            from(
                                select q.cname,
                                q.cid,
                                q.little,
                                q.manager AS citymanager,
                                IF(FROM_UNIXTIME((o.time_real + 23400),'%Y%m') != FROM_UNIXTIME(o.time_real,'%Y%m'),FROM_UNIXTIME((o.time_real + 23400),'%Y-%m'),FROM_UNIXTIME(o.time_real,'%Y-%m')) AS monthtime,
                                count(
                                    if (o.on = 4 and o.type_fw = 1, 1, null)) as 'fen',
                                count(
                                    if (o.on = 4 and o.type_fw = 3, 1, null)) as 'no_fen',
                                count(
                                    if (o.on = 4 and o.type_fw = 2, 1, null)) as 'zen',
                                count(
                                    if (o.on = 4 and o.type_fw = 4, 1, null)) as 'no_zen',
                                count(
                                    if (o.on = 2, 1, null)) as 'dd',
                                count(
                                    if (o.on = 5, 1, null)) as 'wx',
                                count(
                                    if (o.on = 6, 1, null)) as 'kh',
                                count(
                                    if (o.on = 7, 1, null)) as 'zxgs',
                                count(
                                    if (o.on = 8, 1, null)) as 'wxzx',
                                count(
                                    if (o.on = 9, 1, null)) as 'cf',
                                count(
                                    if (o.on = 5 and o.on_sub_wuxiao = 10, 1, null)) as 'jjfw',
                                count(
                                    if (o.on = 5 and o.on_sub_wuxiao = 11, 1, null)) as 'wjt',
                                count(o.id) as 'all'
                                from qz_orders o join qz_quyu as q on q.cid = o.cs where o.time_real >= UNIX_TIMESTAMP(CONCAT(DATE_SUB('$begin',INTERVAL 1 DAY),' ','17:30:00')) and o.time_real < UNIX_TIMESTAMP('$end 17:30:00') and o.cs <> '000001' group by o.cs,monthtime
                            ) t join(
                                select city_id from qz_log_user_real_company where time >= '$begin'
                                and time <= '$end'
                                group by city_id
                            ) t1 on t.cid = t1.city_id
                        ) t3 left join qz_city_coefficient t4 on t4.city_id = t3.cid and date = date_format('$begin','%Y-%m')
                    ) t5 left join(
                        select a.name, a.id as kf_id, a.kfgroup, GROUP_CONCAT(DISTINCT b.name) as manager, GROUP_CONCAT(
                            if (b.uid in (30, 63), b.id, null)) as manager_id, a.cs from qz_adminuser a left join qz_adminuser b on FIND_IN_SET(b.id, a.kfmanager) where a.uid = 31 and a.stat = 1 and a.kfgroup <> 0 group by a.id
                    ) t6 on FIND_IN_SET(t5.cid, t6.cs)
                ) t7 ".$where." GROUP BY cid,monthtime order by cid,monthtime";

        return $this->db(1,"DB_CONFIG1")->query($sql);
    }

    public function orderDayStatistics($begin,$end)
    {
        $time = strtotime($begin);
        $monthStart = date("Y-m-d",mktime(0,0,0,date("m",$time),1,date("Y",$time)));
        $monthEnd = date("Y-m-d",mktime(0,0,0,date("m",$time),date("t",$time),date("Y",$time)));
        $sql = "
                SELECT
                t2.date,
                sum(t2.`all`) as `all`,
                sum(t2.fen) as fen,
                sum(t2.no_fen) as no_fen,
                sum(t2.zen) as zen,
                sum(t2.no_zen) as no_zen,
                sum(t2.dd) as dd,
                sum(t2.wx) as wx,
                sum(t2.kh) as kh,
                sum(t2.zxgs) as zxgs,
                sum(t2.wxzx) as wxzx,
                sum(t2.cf) as cf,
                sum(t2.jjfw) as jjfw,
                sum(t2.wjt) as wjt,
                round((sum(t2.fen)+sum(t2.`zen`))/sum(t2.`all`)*100,2) as 'fz_rate',
                round(sum(t2.last_fen_zen)/sum(t2.`all`)*100,2) as 'last_fen_zen_rate',
                round((sum(t2.fen)+sum(t2.`zen`)/10)/sum(t2.`all`)*100,2) as 'last_fen_rate',
                round(sum(t2.fen)/sum(t2.`all`)*100,2) as 'fen_rate'
                from (
                                select
                                q.cname,
                                t1.cs,
                                t1.date,
                                sum(t1.count) as `all`,
                                sum(t1.fen) as fen,
                                sum(t1.no_fen) as no_fen,
                                sum(t1.zen) as zen,
                                sum(t1.no_zen) as no_zen,
                                sum(t1.dd) as dd,
                                sum(t1.wx) as wx,
                                sum(t1.kh) as kh,
                                sum(t1.zxgs) as zxgs,
                                sum(t1.wxzx) as wxzx,
                                sum(t1.cf) as cf,
                                sum(t1.jjfw) as jjfw,
                                sum(t1.wjt) as wjt,
                                if(t4.day is not null,round(t4.day,2),1) AS `day`,
                                (sum(t1.fen)+sum(t1.zen)/10)/if(t4.day is not null,round(t4.day,2),1) as 'last_fen_zen'
                                from (
                                            select
                                            t.cs,
                                            t.date,
                                            count(t.cs) as count,
                                            count(if (t.on = 4 and t.type_fw = 1, 1, null)) as 'fen',
                                            count(if (t.on = 4 and t.type_fw = 3, 1, null)) as 'no_fen',
                                            count(if (t.on = 4 and t.type_fw = 2, 1, null)) as 'zen',
                                            count(if (t.on = 4 and t.type_fw = 4, 1, null)) as 'no_zen',
                                            count(if (t.on = 2, 1, null)) as 'dd',
                                            count(if (t.on = 5, 1, null)) as 'wx',
                                            count(if (t.on = 6, 1, null)) as 'kh',
                                            count(if (t.on = 7, 1, null)) as 'zxgs',
                                            count(if (t.on = 8, 1, null)) as 'wxzx',
                                            count(if (t.on = 9, 1, null)) as 'cf',
                                            count(if (t.on = 5 and t.on_sub_wuxiao = 10, 1, null)) as 'jjfw',
                                            count(if (t.on = 5 and t.on_sub_wuxiao = 11, 1, null)) as 'wjt'
                                            from (
                                                    select o.cs,o.on,o.type_fw,o.on_sub_wuxiao,
                                                    case
                                                    when o.time_real >= UNIX_TIMESTAMP(CONCAT(FROM_UNIXTIME(o.time_real,'%Y-%m-%d'),' ','17:30:00'))
                                                    and o.time_real < UNIX_TIMESTAMP(CONCAT(FROM_UNIXTIME(o.time_real,'%Y-%m-%d'),' ','23:59:59'))
                                                    then DATE_ADD(FROM_UNIXTIME(o.time_real,'%Y-%m-%d'),INTERVAL 1 day)
                                                    else FROM_UNIXTIME(o.time_real,'%Y-%m-%d')
                                                    end as date
                                                    from qz_orders o
                                                    where o.time_real >= UNIX_TIMESTAMP(CONCAT(DATE_SUB('$begin',INTERVAL 1 DAY),' ','17:30:00')) and o.time_real < UNIX_TIMESTAMP('$end 17:30:00')
                                                    and cs <> '000001'
                                            ) t
                                            group by t.date,t.cs
                                ) t1
                                join qz_log_user_real_company com on com.city_id = t1.cs and com.time = t1.date and com.time >= '$monthStart' and com.time <= '$monthEnd'
                                join qz_quyu as q on q.cid = t1.cs
                                left join qz_city_coefficient t4 on t4.city_id = t1.cs and t4.date = date_format('$begin','%Y-%m')
                                group by t1.date,t1.cs
                 ) t2 group by t2.date order by t2.date desc";

        return $this->db(1,"DB_CONFIG1")->query($sql);
    }

    /**
     * 获取城市有效订单汇总
     * @param  [type] $begin [description]
     * @param  [type] $end   [description]
     * @return [type]        [description]
     */
    public function getOrderEffective($begin,$end)
    {
        $map = array(
            "a.lasttime" => array(
                array("EGT",$begin),
                array("LT",$end)
            ),
            "a.order_on" => array("EQ",4)
        );

        $buildSql = M("order_csos_new")->where($map)->alias("a")
                           ->join("join qz_orders o on o.id = a.order_id")
                           ->join("join qz_quyu q on q.cid = o.cs")
                           ->group("o.id")
                           ->field("q.cname,q.cid,count(if(o.on = 4 and o.type_fw = 1,1,null)) as fen,count(if(o.on = 4 and o.type_fw = 2,1,null)) as zen,FROM_UNIXTIME(a.lasttime,'%Y-%m-%d') as date")
                           ->order("q.cid")
                           ->buildSql();
        return  M("order_csos_new")->table($buildSql)->alias("t")
                                   ->group("t.date")->field("t.date,sum(t.fen) as fen ,sum(t.zen) as zen")->order("date desc")->select();

    }

    /**
     * 获取城市有效订单汇总详细
     * @param  [type] $begin   [操作开始日期]
     * @param  [type] $end     [操作结束日期]
     * @param  [type] $type    [分单类型]
     * @param  [type] $city    [城市]
     * @param  [type] $fangshi [方式]
     * @param  [type] $lx      [装修类型]
     * @param  [type] $lxs     [房屋类型]
     * @param  [type] $yusuan  [预算]
     * @return [type]          [description]
     */
    public function getOrderEffectiveDetails($begin,$end,$type,$city,$fangshi,$lx,$lxs,$yusuan)
    {
        $map = array(
            "a.lasttime" => array(
                array("EGT",$begin),
                array("LT",$end)
            ),
            "a.order_on" => array("EQ",4),
            "o.type_fw" => array("EQ",$type)
        );

        if (!empty($city)) {
           $map["q.cid"] = array("EQ",$city);
        }

        if (!empty($fangshi)) {
           $map["o.fangshi"] = array("EQ",$fangshi);
        }

        if (!empty($lx)) {
           $map["o.lx"] = array("EQ",$lx);
        }

        if (!empty($lxs)) {
           $map["o.lxs"] = array("EQ",$lxs);
        }

        if (!empty($yusuan)) {
           $map["o.yusuan"] = array("EQ",$yusuan);
        }

        return M("order_csos_new")->where($map)->alias("a")
                           ->join("join qz_orders o on o.id = a.order_id")
                           ->join("join qz_quyu q on q.cid = o.cs")
                           ->join("join qz_area area on area.qz_areaid = o.qx")
                           ->join("join qz_jiage j on j.id = o.yusuan")
                           ->field("o.id,q.cid,q.cname,area.qz_area,o.fangshi,o.lx,o.lxs,j.name as yusuan,o.mianji,o.type_fw")
                           ->order("q.cid")
                           ->select();

    }

    /**
     * 每日订单量
     * @param  [type] $begin [description]
     * @param  [type] $end   [description]
     * @return [type]        [description]
     */
    public function getDayOfOrderCount($begin,$end)
    {
        $sql = "select
                t2.date,
                sum(t2.count) as count
                from (
                    select
                    t1.*
                    from (
                            select
                            t.cs,t.date,
                            count(t.id) as count
                            from (
                                    select o.id,o.cs,
                                    case
                                    when o.time_real >= UNIX_TIMESTAMP(CONCAT(FROM_UNIXTIME(o.time_real,'%Y-%m-%d'),' ','17:30:00'))
                                    and o.time_real < UNIX_TIMESTAMP(CONCAT(FROM_UNIXTIME(o.time_real,'%Y-%m-%d'),' ','23:59:59'))
                                    then DATE_ADD(FROM_UNIXTIME(o.time_real,'%Y-%m-%d'),INTERVAL 1 day)
                                    else FROM_UNIXTIME(o.time_real,'%Y-%m-%d')
                                    end as date
                                    from qz_orders o
                                    where o.time_real >= UNIX_TIMESTAMP(CONCAT(DATE_SUB('$begin',INTERVAL 1 DAY),' ','17:30:00')) and o.time_real < UNIX_TIMESTAMP('$end 17:30:00')
                                    and cs <> '000001'
                            ) t group by t.cs,t.date
                    ) t1 join qz_log_user_real_company com on com.city_id = t1.cs and com.time = t1.date and com.time >= '$begin' and com.time <= '$end'
                ) t2 group BY t2.date order by t2.date desc";
        return $this->db(1,"DB_CONFIG1")->query($sql);
    }

    /**
     * [order_tel_encrypt 电话号码加密 手动加盐]
     * @param  [type] $tel [电话号码]
     * @return [type]      [md5($tel.$salt)密文]
     */
    public function getOrdersTelEncrypt($tel) {
        return md5($tel . C('QZ_YUMING'));
    }


    /**
     * [getOrdersRemarks 获取订单备注]
     * @return [type] [description]
     */
    public function getOrdersRemarks(){
        return [
            '无人接听',
            '未接通',
            '通话中',
            '等会联系',
            '过段时间联系',
            '开场挂',
            '核实一半挂机',
            '拒接',
            '拒绝服务',
            '只是看看',
            '寻求其他服务',
            '重复订单',
            '已开站无真会员',
            '地级市50公里以外面积200平以下',
            '关机',
            '空号',
            '停机',
            '假订单',
            '测试单',
            '否认发单',
            '装修公司',
            '材料商',
            '距离远',
            '预算低',
            '面积小',
            '交房时间长',
            '开工时间长',
            '城市未开',
            '需要垫资',
            '不能量房',
            '已回访',
            '精装房',
            '其他'
        ];
    }

    /**
     * [checkTelnumberRepaetByOrderId 获取该订单的电话号码重复数
     * @param  [type] $ids [订单id数组]
     * @return [type]      [description]
     */
    public function getTelnumberRepaetCountByIds($ids){
        if (empty($ids)) {
            return false;
        }
        if (is_array($ids)) {
            $map['z.id'] = array('IN', $ids);
        } else {
            $map['z.id'] = $ids;
        }

        $map['y.deleted'] = array('NEQ', 1);
        $result = M('orders')->alias('z')
                             ->field('z.id, z.tel_encrypt, count(*) AS repeat_count')
                             ->join('inner join qz_orders AS y ON (y.tel_encrypt = z.tel_encrypt)')
                             ->where($map)
                             ->group('z.id')
                             ->select();
        return $result;
    }

    /**
     * [getIpRepaetCountByIds 获取该订单的IP重复数
     * @param  [type] $ids [订单id数组]
     * @return [type]      [description]
     */
    public function getIpRepaetCountByIds($ids){
        if (empty($ids)) {
            return false;
        }
        if (is_array($ids)) {
            $map['z.id'] = array('IN', $ids);
        } else {
            $map['z.id'] = $ids;
        }

        $map['y.ip'] = array("NEQ", "0.0.0.0");
        $map['y.deleted'] = array('NEQ', 1);
        $result = M('orders')->alias('z')
                             ->field('z.id, z.ip, count(*) AS repeat_count')
                             ->join('INNER JOIN qz_orders AS y ON (y.ip = z.ip)')
                             ->where($map)
                             ->group('z.id')
                             ->select();
        return $result;
    }

    public function getOrderSourceById($order_id){
        $map = ['orderid' => ['EQ', $order_id]];
        return M('orders_source')->where($map)->find();
    }

    public function updateSrc($order_id, $data){
        $map = ['orderid' => ['EQ', $order_id]];
        return M('orders_source')->where($map)->save($data);
    }

    public function getSource($src){
        $map = ['src' => ['EQ', $src]];
        return M('order_source')->where($map)->find();
    }

    /**
     * getCompanysByIds 获取订单状态为分单或赠单的分配的装修公司
     * @param $ids
     * @return bool
     */
    public function getCompanysByIds($ids){
        if (empty($ids)) {
            return false;
        }
        if (is_array($ids)) {
            $map['o.id'] = array('IN', $ids);
        }else{
            $map['o.id'] = $ids;
        }
        // 订单状态是分单和赠单时 展示已分配的装修公司信息
        $map['o.`on`'] = 4;
        $map['o.type_fw'] = array('IN', array(1, 2));
        $result = M('orders')->alias('o')
                             ->field('o.id,GROUP_CONCAT(u.jc) as jc')
                             ->join('INNER JOIN qz_order_info as i on i.`order` = o.id')
                             ->join('INNER JOIN qz_user as u on u.id = i.com')
                             ->where($map)
                             ->group('o.id')
                             ->select();

        return $result;


    }

    /**
     * [getOrderById 通过订单id获取订单]
     * @param  [type] $id [订单id]
     * @return [type]     [description]
     */
    public function getOrdersById($id){
        if (!empty($id)) {
            $result = M('orders')->where(array('id' => $id))->find();
            if (!empty($result)) {
                return $result;
            }
        }
        return false;
    }

    /**
     * [getOrdersByTelEncrypt 通过加密后的电话号码查找订单]
     * @param  string $telencrypt [加密后的电话]
     * @return [type]             [description]
     */
    public function getOrdersByTelEncrypt($telencrypt = '', $out = [], $order = 'time_real DESC'){
        if (!empty($telencrypt)) {
            $map['o.tel_encrypt'] = $telencrypt;
            if (!empty($out)) {
                $map['o.id'] = array('NOT IN', $out);
            }
            $order = empty($order) ? 'time_real DESC' : $order;
            $result = M('orders')->alias('o')
                                 ->field('o.id,o.time_real,o.name,o.on,o.on_sub,o.type_fw,q.cname AS city,a.qz_area AS area, p.op_name')
                                 ->join('LEFT JOIN qz_quyu AS q ON q.cid = o.cs')
                                 ->join('LEFT JOIN qz_area AS a ON a.qz_areaid = o.qx')
                                 ->join('LEFT JOIN qz_order_pool AS p ON p.orderid = o.id')
                                 ->where($map)
                                 ->order($order)
                                 ->select();
            if (!empty($result)) {
                return $result;
            }
        }
        return false;
    }

    /**
     * [getOrdersByIp 通过订单IP查找订单]
     * @param  string $telencrypt [加密后的电话]
     * @return [type]             [description]
     */
    public function getOrdersByIp($ip = '', $out = [], $order = 'time_real DESC', $limit = 20){
        if (!empty($ip)) {
            $map['o.ip'] = $ip;
            if (!empty($out)) {
                $map['o.id'] = array('NOT IN', $out);
            }
            $order = empty($order) ? 'time_real DESC' : $order;
            $limit = empty($limit) ? '40' : $limit;
            $result = M('orders')->alias('o')
                                 ->field('o.id,o.time_real,o.name,o.on,o.on_sub,o.type_fw,q.cname AS city,a.qz_area AS area, p.op_name')
                                 ->join('LEFT JOIN qz_quyu AS q ON q.cid = o.cs')
                                 ->join('LEFT JOIN qz_area AS a ON a.qz_areaid = o.qx')
                                 ->join('LEFT JOIN qz_order_pool AS p ON p.orderid = o.id')
                                 ->where($map)
                                 ->order($order)
                                 ->limit($limit)
                                 ->select();
            if (!empty($result)) {
                return $result;
            }
        }
        return false;
    }

    /**
     * [getOrdersListCount 获取数量]
     * @param  integer $id              [订单ID]
     * @param  integer $cs              [订单城市]
     * @param  string  $xiaoqu          [订单小区]
     * @param  string  $ip              [订单IP]
     * @param  string  $tel_encrypt     [订单加密后电话号码]
     * @param  string  $time_start      [修改后发布开始时间]
     * @param  string  $time_end        [修改后发布结束时间]
     * @param  string  $time_real_start [真实发布开始时间]
     * @param  string  $time_real_end   [真实发布结束时间]
     * @param  string  $nf_time_start   [拿房开始时间]
     * @param  string  $nf_time_end     [拿房结束时间]
     * @param  boolean $on              [订单状态]
     * @param  boolean $on_sub          [订单子状态]
     * @param  boolean $type_fw         [分单问单]
     * @param  boolean $remarks         [订单备注]
     * @param  boolean $openeye_st      [显示号码状态]
     * @return [type]                   [description]
     */
    public function getOrdersListCount(
                                       $id = 0,
                                       $cs = 0,
                                       $xiaoqu = '',
                                       $ip = '',
                                       $tel_encrypt = '',
                                       $time_start = '',
                                       $time_end = '',
                                       $time_real_start = '',
                                       $time_real_end = '',
                                       $nf_time_start = '',
                                       $nf_time_end = '',
                                       $on = false,
                                       $on_sub = false,
                                       $type_fw = false,
                                       $remarks = false,
                                       $openeye_st = false,
                                       $isactivity = 0,
                                       $ids
                                       ){
        $admin = getAdminUser();

        //订单id
        if (!empty($id)) {
            $map['o.id'] = $id;
        }

        //订单城市
        if (!empty($cs)) {
            $map['o.cs'] = $cs;
        }

        //订单小区
        if (!empty($xiaoqu)) {
            $map['o.xiaoqu'] = array('LIKE', "%$xiaoqu%");
        }

        //订单ip
        if (!empty($ip)) {
            $map['o.ip'] = $ip;
        }

        //订单电话
        if (!empty($tel_encrypt)) {
            $map['o.tel_encrypt'] = $tel_encrypt;
        }

        //处理修改后的订单发布时间段
        if (!empty($time_start) || !empty($time_end) )
        {
            //如果起止时间都有
            if(!empty($time_start) && !empty($time_end)) {
                $map['o.time'] = array('BETWEEN', [$time_start, $time_end]);
            }
            //如果只有开始时间没有结束时间time
            if(!empty($time_start) && empty($time_end)) {
                $map['o.time'] = array('EGT', $time_start);
            }
            //如果只有结束时间没有开始时间
            if(empty($time_start) && !empty($time_end)) {
                $map['o.time'] = array('ELT', $time_end);
            }
        }

        //处理真实的订单发布时间段
        if (!empty($time_real_start) || !empty($time_real_end) )
        {
            //如果起止时间都有
            if(!empty($time_real_start) && !empty($time_real_end)) {
                $map['o.time_real'] = array('BETWEEN', [$time_real_start, $time_real_end]);
            }
            //如果只有开始时间没有结束时间time
            if(!empty($time_real_start) && empty($time_real_end)) {
                $map['o.time_real'] = array('EGT', $time_real_start);
            }
            //如果只有结束时间没有开始时间
            if(empty($time_real_start) && !empty($time_real_end)) {
                $map['o.time_real'] = array('ELT', $time_real_end);
            }
        }


        //拿房时间处理
        if(!empty($nf_time_start) || !empty($nf_time_end))
        {
            $map['o.order_to_new'] = array('EQ','0');
            //如果起止时间都有
            if (!empty($nf_time_start) && !empty($nf_time_end)) {
                $map['o.nf_time'] = array('BETWEEN', $nf_time_start.','.$nf_time_end);
            }
            //如果只有开始时间没有结束时间time
            if (!empty($nf_time_start) && empty($nf_time_end)) {
                $map['o.nf_time']         = array('EGT', $nf_time_start);
            }
            //如果只有结束时间没有开始时间
            if (empty($nf_time_start) && !empty($nf_time_end)) {
                $map['o.nf_time']         = array('ELT', $nf_time_end);
            }
        }

        //订单状态
        if ($on !== false) {
            if (is_array($on)) {
                $map['o.on'] = array('IN', $on);
            } else {
                $map['o.on'] = intval($on);
            }
        }
        if ($on_sub !== false) {
            $map['o.on_sub'] = intval($on_sub);
        }

        //订单分问
        if ($type_fw !== false) {
            if (is_array($type_fw)) {
                $map['o.type_fw'] = array('IN', $type_fw);
            } else {
                $map['o.type_fw'] = intval($type_fw);
            }
        }

        //显号审核
        if ($openeye_st !== false) {
            if ($openeye_st === 'null') {
                $map['o.openeye_st'] = array('EXP', ' IS NULL ');
            } else {
                $map['o.openeye_st'] = intval($openeye_st);
            }
        }

        //订单备注
        if (!empty($remarks) && $remarks != '全部') {
            if('null' == $remarks){
                $map['o.remarks'] = array('EXP',' IS NULL ');
            }else{
                $remarks = trim($remarks);
                $map['o.remarks'] = array('LIKE', "%".$remarks."%");
            }
        }

        //如果是查询IP，小区，电话，订单号，拿房时间,就不要限定订单时间
        if (!empty($map['o.id']) || !empty($map['o.ip']) || !empty($map['o.xiaoqu']) || !empty($map['o.tel_encrypt']) || !empty($map['o.nf_time'])) {
            unset($map['o.time_real']);
            unset($map['o.time']);
        }

        if (!empty($isactivity)) {
            if (count($ids) > 0) {
                if ($isactivity == 1) {
                    $map['o.source'] = array("IN",$ids);
                } else {
                    $map['o.source'] = array("NOT IN",$ids);
                }
            }
        }

        $db = M('orders');
        //查询电话号码指定索引
        if (!empty($map['_complex']['o.tel_encrypt'])){
            $db = $db->force('idx_tel_id');
        } elseif (!empty($map['o.time_real'])) {
            $db = $db->force('idx_time_real');
        } elseif (!empty($map['o.time'])) {
            $db = $db->force('idx_time_on');
        }
        $db = $db->alias('o')->field('o.tel_encrypt');
        //如果是超级管理员 黑名单的订单也显示 不是超级管理员 黑名单的号码的订单不显示
        if (1 != $admin['uid']) {
            $map['b.status'] = array(array('EQ', 0),array('EXP',' IS NULL '),'OR');
            $db = $db->join('LEFT JOIN qz_order_blacklist AS b ON b.tel_encrypt = o.tel_encrypt');
        }

        $count = $db->where($map)->count();
        return $count;
    }

    /**
     * [getOrdersList 获取订单列表]
     * @param  integer $id              [订单ID]
     * @param  integer $cs              [订单城市]
     * @param  string  $xiaoqu          [订单小区]
     * @param  string  $ip              [订单IP]
     * @param  string  $tel_encrypt     [订单加密后电话号码]
     * @param  string  $time_start      [修改后发布开始时间]
     * @param  string  $time_end        [修改后发布结束时间]
     * @param  string  $time_real_start [真实发布开始时间]
     * @param  string  $time_real_end   [真实发布结束时间]
     * @param  string  $nf_time_start   [拿房开始时间]
     * @param  string  $nf_time_end     [拿房结束时间]
     * @param  boolean $on              [订单状态]
     * @param  boolean $on_sub          [订单子状态]
     * @param  boolean $type_fw         [分单问单]
     * @param  boolean $remarks         [订单备注]
     * @param  boolean $openeye_st      [显示号码状态]
     * @param  boolean $order           [排序]
     * @param  boolean $start           [开始页]
     * @param  boolean $end             [每页查询]
     * @return [type]                   [description]
     */
    public function getOrdersList(
                                  $id = 0,
                                  $cs = 0,
                                  $xiaoqu = '',
                                  $ip = '',
                                  $tel_encrypt = '',
                                  $time_start = '',
                                  $time_end = '',
                                  $time_real_start = '',
                                  $time_real_end = '',
                                  $nf_time_start = '',
                                  $nf_time_end = '',
                                  $on = false,
                                  $on_sub = false,
                                  $type_fw = false,
                                  $remarks = false,
                                  $openeye_st = false,
                                  $order = 'time_real DESC',
                                  $start = '0',
                                  $end = '20',
                                  $isactivity = 0,
                                  $ids
                                  ){
        //获取用户信息
        $admin = getAdminUser();

        //订单id
        if (!empty($id)) {
            $map['o.id'] = $id;
        }

        //订单城市
        if (!empty($cs)) {
            $map['o.cs'] = $cs;
        }

        //订单小区
        if (!empty($xiaoqu)) {
            $map['o.xiaoqu'] = array('LIKE', "%$xiaoqu%");
        }

        //订单ip
        if (!empty($ip)) {
            $map['o.ip'] = $ip;
        }

        //订单电话
        if (!empty($tel_encrypt)) {
            $map['o.tel_encrypt'] = $tel_encrypt;
        }

        //处理修改后的订单发布时间段
        if (!empty($time_start) || !empty($time_end) )
        {
            //如果起止时间都有
            if(!empty($time_start) && !empty($time_end)) {
                $map['o.time'] = array('BETWEEN', [$time_start, $time_end]);
            }
            //如果只有开始时间没有结束时间time
            if(!empty($time_start) && empty($time_end)) {
                $map['o.time'] = array('EGT', $time_start);
            }
            //如果只有结束时间没有开始时间
            if(empty($time_start) && !empty($time_end)) {
                $map['o.time'] = array('ELT', $time_end);
            }
        }

        //处理真实的订单发布时间段
        if (!empty($time_real_start) || !empty($time_real_end) )
        {
            //如果起止时间都有
            if(!empty($time_real_start) && !empty($time_real_end)) {
                $map['o.time_real'] = array('BETWEEN', [$time_real_start, $time_real_end]);
            }
            //如果只有开始时间没有结束时间time
            if(!empty($time_real_start) && empty($time_real_end)) {
                $map['o.time_real'] = array('EGT', $time_real_start);
            }
            //如果只有结束时间没有开始时间
            if(empty($time_real_start) && !empty($time_real_end)) {
                $map['o.time_real'] = array('ELT', $time_real_end);
            }
        }

        //拿房时间处理
        if(!empty($nf_time_start) || !empty($nf_time_end))
        {
            $map['o.order_to_new'] = array('EQ','0');
            //如果起止时间都有
            if (!empty($nf_time_start) && !empty($nf_time_end)) {
                $map['o.nf_time'] = array('BETWEEN', $nf_time_start.','.$nf_time_end);
            }
            //如果只有开始时间没有结束时间time
            if (!empty($nf_time_start) && empty($nf_time_end)) {
                $map['o.nf_time']         = array('EGT', $nf_time_start);
            }
            //如果只有结束时间没有开始时间
            if (empty($nf_time_start) && !empty($nf_time_end)) {
                $map['o.nf_time']         = array('ELT', $nf_time_end);
            }
        }

        //订单状态
        if ($on !== false) {
            if (is_array($on)) {
                $map['o.on'] = array('IN', $on);
            } else {
                $map['o.on'] = intval($on);
            }
        }
        if ($on_sub !== false) {
            $map['o.on_sub'] = intval($on_sub);
        }

        //订单分问
        if ($type_fw !== false) {
            if (is_array($type_fw)) {
                $map['o.type_fw'] = array('IN', $type_fw);
            } else {
                $map['o.type_fw'] = intval($type_fw);
            }
        }

        //显号审核
        if ($openeye_st !== false) {
            if ($openeye_st === 'null') {
                $map['o.openeye_st'] = array('EXP', ' IS NULL ');
            } else {
                $map['o.openeye_st'] = intval($openeye_st);
            }
        }

        //订单备注
        if (!empty($remarks) && $remarks != '全部') {
            if('null' == $remarks){
                $map['o.remarks'] = array('EXP',' IS NULL ');
            }else{
                $remarks = trim($remarks);
                $map['o.remarks'] = array('LIKE', "%".$remarks."%");
            }
        }

        //如果是查询IP，小区，电话，订单号，拿房时间,就不要限定订单时间
        if (!empty($map['o.id']) || !empty($map['o.ip']) || !empty($map['o.xiaoqu']) || !empty($map['o.tel_encrypt']) || !empty($map['o.nf_time'])) {
            unset($map['o.time_real']);
            unset($map['o.time']);
        }

        if (!empty($isactivity)) {
            if (count($ids) > 0) {
                if ($isactivity == 1) {
                    $map['o.source'] = array("IN",$ids);
                } else {
                    $map['o.source'] = array("NOT IN",$ids);
                }
            }
        }

        $db = M('orders');
        //查询电话号码指定索引
        if (!empty($map['_complex']['o.tel_encrypt'])){
            $db = $db->force('idx_tel_id');
        } elseif (!empty($map['o.time_real'])) {
            $db = $db->force('idx_time_real');
        } elseif (!empty($map['o.time'])) {
            $db = $db->force('idx_time_on');
        }

        $field = 'o.id,o.time_real,o.time,o.cs,o.qx,o.tel,o.tel_encrypt,o.nf_time,o.mianji,o.visitime,o.on,o.on_sub,o.type_fw,o.type_zs_sub,o.order2com_allread,o.from_old_orderid,o.remarks,o.lasttime,o.openeye_st,o.openeye_reger,o.openeye_sqly,o.calllong_time,o.callfast_time,o.wzd,o.source';
        if (1 != $admin['uid']) {
            //注意！更改的话下面同样更改;如果是超级管理员 黑名单的订单也显示 不是超级管理员 黑名单的号码的订单不显示,
            $field = $field . ',b.status AS order_blacklist_status';
        }

        $db = $db->field($field)
                 ->alias('o');
        if (1 != $admin['uid']) {
            //如果是超级管理员 黑名单的订单也显示 不是超级管理员 黑名单的号码的订单不显示
            $map['b.status'] = array(array('EQ', 0),array('EXP',' IS NULL '),'OR');
            $db = $db->join('LEFT JOIN qz_order_blacklist AS b ON b.tel_encrypt = o.tel_encrypt');
        }

        $build = $db->where($map)->order($order)->limit($start, $end)->buildSql();
        $result = M()->table($build)->alias('z')
                                    ->field('z.*, q.cname AS city, a.qz_area AS area')
                                    ->join('LEFT JOIN qz_quyu AS q ON q.cid = z.cs')
                                    ->join('LEFT JOIN qz_area AS a ON a.qz_areaid = z.qx')
                                    ->select();
        return $result;
    }


    /**
     * 查询订单信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getOrderInfoWithPoolByOrderId($orderId, $orderPoolUid = [])
    {
        if (empty($orderId)) {
            return false;
        }

        $map = array(
            "o.id" => array("EQ",$orderId)
        );

        if (!empty($orderPoolUid)) {
            if (!is_array($orderPoolUid)) {
                $orderPoolUid = array($orderPoolUid);
            }
            $map['p.uid'] = array('IN', $orderPoolUid);
        }

        $field = 'o.id,o.type_fw,o.time_real,o.ip,o.tel,q.cname,a.qz_area,o.on,o.on_sub,o.on_sub_wuxiao,openeye_st,o.cs,o.qx,o.name,o.xiaoqu,o.mianji,o.huxing,o.yt,o.lx,o.lxs,o.fangshi,o.yusuan,o.fengge,o.start,o.keys,o.lftime,o.nf_time,o.text,o.visitime,o.other_contact,o.dz,o.source_type,o.zxxm,o.remarks,o.sex,o.cs,o.huifan,o.lasttime,o.wzd,o.zhuanfaren,o.time,o.shi,o.order_to_new,u.name as customer,t.tel8,c.user_name AS csos_user_name';

        $result = M('orders')->alias('o')
                             ->field($field)
                             ->join('LEFT JOIN qz_quyu AS q ON q.cid = o.cs')
                             ->join('LEFT JOIN qz_area AS a ON a.qz_areaid = o.qx')
                             ->join('INNER JOIN safe_order_tel8 t on t.orderid = o.id')
                             ->join("LEFT JOIN qz_adminuser AS u on u.id = o.customer")
                             ->join("INNER JOIN qz_order_pool AS p on p.orderid = o.id")
                             ->join("LEFT JOIN qz_order_csos_new AS c on c.order_id = o.id")
                             ->where($map)
                             ->find();
        return $result;
    }

    /**
     * [getOrdersListCountJoinOrderPool 获取数量]
     * @param  integer $id              [订单ID]
     * @param  integer $cs              [订单城市]
     * @param  string  $xiaoqu          [订单小区]
     * @param  string  $ip              [订单IP]
     * @param  string  $tel_encrypt     [订单加密后电话号码]
     * @param  string  $time_start      [修改后发布开始时间]
     * @param  string  $time_end        [修改后发布结束时间]
     * @param  string  $time_real_start [真实发布开始时间]
     * @param  string  $time_real_end   [真实发布结束时间]
     * @param  string  $nf_time_start   [拿房开始时间]
     * @param  string  $nf_time_end     [拿房结束时间]
     * @param  boolean $on              [订单状态]
     * @param  boolean $on_sub          [订单子状态]
     * @param  boolean $type_fw         [分单问单]
     * @param  boolean $remarks         [订单备注]
     * @param  boolean $openeye_st      [显示号码状态]
     * @return [type]                   [description]
     */
    public function getOrdersListCountJoinOrderPool(
                                       $id = 0,
                                       $cs = 0,
                                       $xiaoqu = '',
                                       $ip = '',
                                       $tel_encrypt = '',
                                       $time_start = '',
                                       $time_end = '',
                                       $time_real_start = '',
                                       $time_real_end = '',
                                       $nf_time_start = '',
                                       $nf_time_end = '',
                                       $on = false,
                                       $on_sub = false,
                                       $type_fw = false,
                                       $remarks = false,
                                       $openeye_st = false,
                                       $op_uid = false,
                                       $isactivity = 0,
                                       $ids,$source_in,$area,$fen_customer = false,$lxs
                                       ){
        //订单id
        if (!empty($id)) {
            $map['o.id'] = $id;
        }

        //订单城市
        if (!empty($cs)) {
            $map['o.cs'] = $cs;
        }
        if (!empty($area)) {
            $map['o.qx'] = $area;
        }

        //订单小区
        if (!empty($xiaoqu)) {
            $map['o.xiaoqu'] = array('LIKE', "%$xiaoqu%");
        }

        //订单ip
        if (!empty($ip)) {
            $map['o.ip'] = $ip;
        }

        //订单电话
        if (!empty($tel_encrypt)) {
            $map['o.tel_encrypt'] = $tel_encrypt;
        }

        //处理修改后的订单发布时间段
        if (!empty($time_start) || !empty($time_end) )
        {
            //如果起止时间都有
            if(!empty($time_start) && !empty($time_end)) {
                $map['o.time'] = array('BETWEEN', [$time_start, $time_end]);
            }
            //如果只有开始时间没有结束时间time
            if(!empty($time_start) && empty($time_end)) {
                $map['o.time'] = array('EGT', $time_start);
            }
            //如果只有结束时间没有开始时间
            if(empty($time_start) && !empty($time_end)) {
                $map['o.time'] = array('ELT', $time_end);
            }
        }

        //处理订单发布时间段
        if (!empty($time_real_start) || !empty($time_real_end) )
        {
            //如果起止时间都有
            if(!empty($time_real_start) && !empty($time_real_end)) {
                $map['o.time_real'] = array('BETWEEN', [$time_real_start, $time_real_end]);
            }
            //如果只有开始时间没有结束时间time
            if(!empty($time_real_start) && empty($time_real_end)) {
                $map['o.time_real'] = array('EGT', $time_real_start);
            }
            //如果只有结束时间没有开始时间
            if(empty($time_real_start) && !empty($time_real_end)) {
                $map['o.time_real'] = array('ELT', $time_real_end);
            }
        }


        //拿房时间处理
        if(!empty($nf_time_start) || !empty($nf_time_end))
        {
            $map['o.order_to_new'] = array('EQ','0');
            //如果起止时间都有
            if (!empty($nf_time_start) && !empty($nf_time_end)) {
                $map['o.nf_time'] = array('BETWEEN', $nf_time_start.','.$nf_time_end);
            }
            //如果只有开始时间没有结束时间time
            if (!empty($nf_time_start) && empty($nf_time_end)) {
                $map['o.nf_time']         = array('EGT', $nf_time_start);
            }
            //如果只有结束时间没有开始时间
            if (empty($nf_time_start) && !empty($nf_time_end)) {
                $map['o.nf_time']         = array('ELT', $nf_time_end);
            }
        }

        //订单状态
        if ($on !== false) {
            if (is_array($on)) {
                $map['o.on'] = array('IN', $on);
            } else {
                $map['o.on'] = intval($on);
            }
        }
        if ($on_sub !== false) {
            $map['o.on_sub'] = intval($on_sub);
        }

        //订单分问
        if ($type_fw !== false) {
            if (is_array($type_fw)) {
                $map['o.type_fw'] = array('IN', $type_fw);
            } else {
                $map['o.type_fw'] = intval($type_fw);
            }
        }

        //选择未分配订单 , 添加条件
        if($fen_customer !== false){
            if (is_array($fen_customer)) {
                $map['o.fen_customer'] = array('IN', $fen_customer);
            } else {
                $map['o.fen_customer'] = intval($fen_customer);
                $map['p.status'] = 0;
            }
        }

        //显号审核
        if ($openeye_st !== false) {
            if ($openeye_st === 'null') {
                $map['t.status'] = array('EXP', ' IS NULL ');
            } else {
                $map['t.status'] = intval($openeye_st);
            }
        }

        //订单备注
        if (!empty($remarks) && $remarks != '全部') {
            if('null' == $remarks){
                $map['o.remarks'] = array('EXP',' IS NULL ');
            }else{
                $remarks = trim($remarks);
                $map['o.remarks'] = array('LIKE', "%".$remarks."%");
            }
        }

        //订单认领人
        if ($op_uid !== false) {
            $op_uid = is_array($op_uid) ? $op_uid : array_filter(explode(',', $op_uid));
            if (!empty($op_uid)) {
                $map['p.op_uid'] = array('IN', $op_uid);
            }
        }

        //限定查询最近90天的单子,不为待定或者没选时间段
        if (($map['o.on'] != 2) && empty($map['o.time_real'])) {
            $map['o.time_real'] = array('GT', strtotime("-90 day"));
        }

        //如果是查询IP，小区，电话，订单号，拿房时间,就不要限定订单时间
        if (!empty($map['o.id']) || !empty($map['o.ip']) || !empty($map['o.xiaoqu']) || !empty($map['o.tel_encrypt']) || !empty($map['o.nf_time'])) {
            unset($map['o.time_real']);
        }

        if (!empty($isactivity)) {
            if (count($ids) > 0) {
                if ($isactivity == 1) {
                    $map['o.source'] = array("IN",$ids);
                } else {
                    $map['o.source'] = array("NOT IN",$ids);
                }
            }
        }

        if ($source_in != "") {
            $map['o.source_in'] = array('EQ', $source_in);
        }

        if (!empty($lxs)) {
            $map['o.lxs'] = array('EQ', $lxs);
        }

        //未删除订单
        $map['o.deleted'] = array('NEQ', 1);
        $db = M('orders');

        //如果有电话号码查询，则指定索引
        if (!empty($map['_complex']['o.tel_encrypt'])){
            $db = $db->force('idx_tel_id');
        }

        //如果有订单发布时间字段查询，则指定索引
        if (!empty($map['o.time_real'])){
            $db = $db->force('idx_time_real');
        }

        $db = $db->alias('o')
                 ->field('count(*)')
                 ->join('qz_order_pool AS p ON p.orderid = o.id');

        //如果操作人ID不为空，则JOIN显号表的时候加入操作人条件
        if (!empty($op_uid)) {
            $apply_id = implode($op_uid, ',');
            $db = $db->join('LEFT JOIN qz_orders_apply_tel t ON t.orders_id = o.id AND t.apply_id IN (' . $apply_id . ')');
        } else {
            $db = $db->join('LEFT JOIN qz_orders_apply_tel t ON t.orders_id = o.id');
        }

        $admin = getAdminUser();
        //如果是超级管理员 黑名单的订单也显示 不是超级管理员 黑名单的号码的订单不显示
        if (1 != $admin['uid']) {
            $map['b.status'] = array(array('EQ', 0),array('EXP',' IS NULL '),'OR');
            $db = $db->join('LEFT JOIN qz_order_blacklist AS b ON b.tel_encrypt = o.tel_encrypt');
        }

        $build = $db->where($map)->group('o.id')->buildSql();
        $count = M()->table($build)->alias('z')->count();
        return $count;
    }

    /**
     * [getOrdersListJoinOrderPool 获取订单列表，现阶段以order_pool为主表，请查看OrderPool模型]
     * @param  integer $id              [订单ID]
     * @param  integer $cs              [订单城市]
     * @param  string  $xiaoqu          [订单小区]
     * @param  string  $ip              [订单IP]
     * @param  string  $tel_encrypt     [订单加密后电话号码]
     * @param  string  $time_start      [修改后发布开始时间]
     * @param  string  $time_end        [修改后发布结束时间]
     * @param  string  $time_real_start [真实发布开始时间]
     * @param  string  $time_real_end   [真实发布结束时间]
     * @param  string  $nf_time_start   [拿房开始时间]
     * @param  string  $nf_time_end     [拿房结束时间]
     * @param  boolean $on              [订单状态]
     * @param  boolean $on_sub          [订单子状态]
     * @param  boolean $type_fw         [分单问单]
     * @param  boolean $remarks         [订单备注]
     * @param  boolean $openeye_st      [显示号码状态]
     * @param  boolean $order           [排序]
     * @param  boolean $start           [开始页]
     * @param  boolean $end             [每页查询]
     * @return [type]                   [description]
     */
    public function getOrdersListJoinOrderPool(
                                  $id = 0,
                                  $cs = 0,
                                  $xiaoqu = '',
                                  $ip = '',
                                  $tel_encrypt = '',
                                  $time_start = '',
                                  $time_end = '',
                                  $time_real_start = '',
                                  $time_real_end = '',
                                  $nf_time_start = '',
                                  $nf_time_end = '',
                                  $on = false,
                                  $on_sub = false,
                                  $type_fw = false,
                                  $remarks = false,
                                  $openeye_st = false,
                                  $op_uid = false,
                                  $order = 'time_real DESC',
                                  $start = '0',
                                  $end = '20',
                                  $isactivity = 0,
                                  $ids,$source_in,$area,$fen_customer = false,$lxs
                                  ){
        $admin = getAdminUser();
        //订单id
        if (!empty($id)) {
            $map['o.id'] = $id;
        }

        //订单城市
        if (!empty($cs)) {
            $map['o.cs'] = $cs;
        }

        if (!empty($area)) {
            $map['o.qx'] = $area;
        }
        //订单小区
        if (!empty($xiaoqu)) {
            $map['o.xiaoqu'] = array('LIKE', "%$xiaoqu%");
        }

        //订单ip
        if (!empty($ip)) {
            $map['o.ip'] = $ip;
        }

        //订单电话
        if (!empty($tel_encrypt)) {
            $map['o.tel_encrypt'] = $tel_encrypt;
        }

        //处理修改后的订单发布时间段
        if (!empty($time_start) || !empty($time_end) )
        {
            //如果起止时间都有
            if(!empty($time_start) && !empty($time_end)) {
                $map['o.time'] = array('BETWEEN', [$time_start, $time_end]);
            }
            //如果只有开始时间没有结束时间time
            if(!empty($time_start) && empty($time_end)) {
                $map['o.time'] = array('EGT', $time_start);
            }
            //如果只有结束时间没有开始时间
            if(empty($time_start) && !empty($time_end)) {
                $map['o.time'] = array('ELT', $time_end);
            }
        }

        //处理真实的订单发布时间段
        if (!empty($time_real_start) || !empty($time_real_end) )
        {
            //如果起止时间都有
            if(!empty($time_real_start) && !empty($time_real_end)) {
                $map['o.time_real'] = array('BETWEEN', [$time_real_start, $time_real_end]);
            }
            //如果只有开始时间没有结束时间time
            if(!empty($time_real_start) && empty($time_real_end)) {
                $map['o.time_real'] = array('EGT', $time_real_start);
            }
            //如果只有结束时间没有开始时间
            if(empty($time_real_start) && !empty($time_real_end)) {
                $map['o.time_real'] = array('ELT', $time_real_end);
            }
        }

        //拿房时间处理
        if(!empty($nf_time_start) || !empty($nf_time_end))
        {
            $map['o.order_to_new'] = array('EQ','0');
            //如果起止时间都有
            if (!empty($nf_time_start) && !empty($nf_time_end)) {
                $map['o.nf_time'] = array('BETWEEN', $nf_time_start.','.$nf_time_end);
            }
            //如果只有开始时间没有结束时间time
            if (!empty($nf_time_start) && empty($nf_time_end)) {
                $map['o.nf_time']         = array('EGT', $nf_time_start);
            }
            //如果只有结束时间没有开始时间
            if (empty($nf_time_start) && !empty($nf_time_end)) {
                $map['o.nf_time']         = array('ELT', $nf_time_end);
            }
        }

        //订单状态
        if ($on !== false) {
            if (is_array($on)) {
                $map['o.on'] = array('IN', $on);
            } else {
                $map['o.on'] = intval($on);
            }
        }
        if ($on_sub !== false) {
            $map['o.on_sub'] = intval($on_sub);
        }

        //订单分问
        if ($type_fw !== false) {
            if (is_array($type_fw)) {
                $map['o.type_fw'] = array('IN', $type_fw);
            } else {
                $map['o.type_fw'] = intval($type_fw);
            }
        }

        //选择未分配订单 , 添加条件
        if($fen_customer !== false){
            if (is_array($fen_customer)) {
                $map['o.fen_customer'] = array('IN', $fen_customer);
            } else {
                $map['o.fen_customer'] = intval($fen_customer);
                $map['p.status'] = 0;
            }
        }
        //订单备注
        if (!empty($remarks) && $remarks != '全部') {
            if('null' == $remarks){
                $map['o.remarks'] = array('EXP',' IS NULL ');
            }else{
                $remarks = trim($remarks);
                $map['o.remarks'] = array('LIKE', "%".$remarks."%");
            }
        }

        //订单认领人
        if ($op_uid !== false) {
            $op_uid = is_array($op_uid) ? $op_uid : array_filter(explode(',', $op_uid));
            if (!empty($op_uid)) {
                $map['p.op_uid'] = array('IN', $op_uid);
            }
        }

        //显号审核
        if ($openeye_st !== false) {
            if ($openeye_st === 'null') {
                $map['t.status'] = array('EXP', ' IS NULL ');
            } else {
                $map['t.status'] = intval($openeye_st);
            }
        }

        //限定查询最近90天的单子,不为待定或者没选时间段
        if (($map['o.on'] != 2) && empty($map['o.time_real'])) {
            $map['o.time_real'] = array('GT', strtotime("-90 day"));
        }

        //如果是查询IP，小区，电话，订单号，拿房时间,就不要限定订单时间
        if (!empty($map['o.id']) || !empty($map['o.ip']) || !empty($map['o.xiaoqu']) || !empty($map['o.tel_encrypt']) || !empty($map['o.nf_time'])) {
            unset($map['o.time_real']);
        }

        if (!empty($isactivity)) {
            if (count($ids) > 0) {
                if ($isactivity == 1) {
                    $map['o.source'] = array("IN",$ids);
                } else {
                    $map['o.source'] = array("NOT IN",$ids);
                }
            }
        }

        if ($source_in != "") {
            $map['o.source_in'] = array('EQ', $source_in);
        }

        if (!empty($lxs)) {
            $map['o.lxs'] = array('EQ', $lxs);
        }

        //未删除订单
        $map['o.deleted'] = array('NEQ', 1);

        $db = M('orders');
        $field = 'o.id,o.source_in,o.time_real,o.time,o.cs,o.qx,o.source_type,o.tel,o.tel_encrypt,o.nf_time,o.mianji,o.visitime,o.on,o.on_sub,o.type_fw,o.type_zs_sub,o.order2com_allread,o.from_old_orderid,o.remarks,o.lasttime,o.calllong_time,o.callfast_time,o.wzd,q.cname AS city,a.qz_area AS area,o.wzd,GROUP_CONCAT(t.status) AS apply_tel_status,GROUP_CONCAT(t.apply_id) AS apply_tel_admin,p.op_name,o.source';

        //如果有电话号码查询，则指定索引
        if (!empty($map['_complex']['o.tel_encrypt'])){
            $db = $db->force('idx_tel_id');
        }
        //如果有订单发布时间字段查询，则指定索引
        if (!empty($map['o.time_real'])){
            $db = $db->force('idx_time_real');
        }

        $db = $db->field($field)
                 ->alias('o')
                 ->join('qz_order_pool AS p ON p.orderid = o.id')
                 ->join('LEFT JOIN qz_quyu q ON q.cid = o.cs')
                 ->join('LEFT JOIN qz_area a ON a.qz_areaid = o.qx');

        //如果操作人ID不为空，则JOIN显号表的时候加入操作人条件
        if (!empty($op_uid)) {
            $apply_id = implode($op_uid, ',');
            $db = $db->join('LEFT JOIN qz_orders_apply_tel t ON t.orders_id = o.id AND t.apply_id IN (' . $apply_id . ')');
        } else {
            $db = $db->join('LEFT JOIN qz_orders_apply_tel t ON t.orders_id = o.id');
        }

        if (1 != $admin['uid']) {
            //如果是超级管理员 黑名单的订单也显示 不是超级管理员 黑名单的号码的订单不显示
            $map['b.status'] = array(array('EQ', 0),array('EXP',' IS NULL '),'OR');
            $db = $db->join('LEFT JOIN qz_order_blacklist AS b ON b.tel_encrypt = o.tel_encrypt');
        }

        $result = $db->where($map)->group('o.id')->order($order)->limit($start, $end)->select();
        return $result;
    }

    /**
     * 查询订单信息
     * @param  [type] $id [description]
     * @return mixed
     */
    public function findOrderInfo($id)
    {
        if (empty($id)) {
            return [];
        }

        $map['a.id'] = ['EQ', $id];

        return M("orders")->where($map)->alias("a")
                           ->join("left join qz_quyu q on q.cid = a.cs")
                           ->join("left join qz_area area on area.qz_areaid = a.qx")
                           ->join("join safe_order_tel8 b on b.orderid = a.id")
                           ->join("left join qz_order_rob_pool p on p.order_id = a.id")
                           ->join("left join qz_order_pool op on op.orderid = a.id")
                           ->join("left join qz_adminuser c on c.id = a.customer")
                           ->join("left join qz_adminuser z on z.id = a.chk_customer")
                           ->join("left join qz_adminuser y on y.id = a.fen_customer")
                           ->join("left join  qz_order_csos_new new on new.order_id = a.id")
                           ->join("left join qz_order_allot_num oa on oa.order_id = a.id")
                           ->field("a.id,a.type_fw,FROM_UNIXTIME(a.time_real) as time_real,a.ip,a.tel,q.cname,area.qz_area,
                           a.on,a.on_sub,a.on_sub_wuxiao,openeye_st,a.cs,a.qx,a.name,a.xiaoqu,a.mianji,a.huxing,a.yt,
                           a.lx,a.lxs,a.fangshi,a.yusuan,a.fengge,a.start,a.keys,a.lftime,a.nf_time,a.text,
                           a.visitime,a.other_contact,a.dz,a.source_type,a.zxxm,a.remarks,a.sex,a.cs,
                           b.tel8,a.huifan,FROM_UNIXTIME(a.lasttime,'%Y-%m-%d %H:%i:%S') as lasttime,
                           a.wzd,a.zhuanfaren,FROM_UNIXTIME(a.time) as time,a.shi,c.name as customer,
                           z.name as chk_customer,y.name as fen_customer,a.order_to_new,q.bm,new.lasttime as new_lasttime,
                           a.lng,a.lat,a.tel_encrypt,a.qiandan_remark,a.qiandan_companyid as qiandan_company,
                           a.qiandan_status,a.qiandan_chktime,a.qiandan_remark_lasttime,a.source,a.lat,a.lng,
                           a.qiandan_jine,a.xiaoqu_type,if(oa.allot_num IS NOT NULL,oa.allot_num,4) allot_num,
                           p.is_rob,p.rob_num,a.wx,a.is_wx,a.standby_user,op.op_name,a.wx,a.standby_user,a.is_wx,
                           a.order2com_allread,a.qiandan_addtime,a.review_time,a.source_in,y.id as fen_customer_id")
                           ->find();
    }

    /**
     * 查询订单电话号码
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function findOrderInfoAndTel($id)
    {
        $map = array(
            "a.id" => array("EQ",$id)
        );

        return M("orders")->where($map)->alias("a")
                           ->join("join safe_order_tel8 b on b.orderid = a.id")
                           ->field("a.*,b.tel8")->find();
    }

      /**
       * 添加订单
       * @param [type] $data [description]
       */
      public function addOrder($data)
      {
           return M("orders")->add($data);
      }

      /**
       * t添加订单电话加密
       * @param [type] $data [description]
       */
      public function addTelEncrypt($data)
      {
            return M()->table("safe_order_tel8")->add($data);
      }


    /**
     * 编辑订单
     * @param  [type] $id   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function editOrder($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
        );

        return M("orders")->where($map)->save($data);
    }

    /**
     * 查询显号记录
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function findOrderEyeInfo($id)
    {
        $map = array(
            "id" => array("EQ",$id),
            "openeye_st" => array(
                     array("NEQ",""),
                     array("exp","is not null"),
                     "or"
            )
        );
        return M("orders")->where($map)->count();
    }

    /**
     * 获取签单历史记录
     * @param  [type] $str [description]
     * @return [type]      [description]
     */
    public function getQianDanHistory($xiaoqu,$cs)
    {
        $map = array(
            "a.qiandan_companyid" => array("NEQ",0),
            "a.on" => array("EQ",4),
            "a.qiandan_status" => array("EQ",1),
            "a.cs" => array("EQ",$cs)
        );

        foreach ($xiaoqu as $key => $value) {
            $arr[] = array("Like","%$value%");
        }
        if (count($arr) > 1) {
            $arr[] =  "OR";
        }

        $map["xiaoqu"] = $arr;

        $buildSql = M("orders")->where($map)->alias("a")
                          ->join("join qz_user u on u.id = a.qiandan_companyid")
                          ->field("u.id,u.jc,u.on,a.xiaoqu,a.qiandan_addtime")
                          ->buildSql();

        $buildSql = M("orders")->table($buildSql)->alias("a")
                               ->order("a.qiandan_addtime desc")
                               ->buildSql();

        return M("orders")->table($buildSql)->alias("t")
                          ->field("t.*,count(t.id) as count")
                          ->order("t.qiandan_addtime desc")
                          ->group("t.id,t.xiaoqu")->select();
    }

    /**
     * 获取最新的一次分单信息
     * @param  [type] $cs [description]
     * @return [type]     [description]
     */
    public function getLastTypeFw($id,$cs)
    {
        $map = array(
            "a.id" => array("EQ",$id),
            "b.cs" => array("EQ",$cs),
            "b.on" => array("EQ",4),
            "b.type_fw" => array("EQ",1)
        );
        $buildSql = M("orders")->where($map)->alias("a")
                               ->join("join qz_orders b on b.lasttime < a.lasttime")
                               ->field("b.id")->order("b.lasttime desc")->limit(1)->buildSql();

        return M("orders")->table($buildSql)->alias("t")
                          ->join("join qz_order_info i on i.order = t.id")
                          ->join("join qz_user u on u.id = i.com")
                          ->field("u.jc")->select();
    }

    /**
     * 获取分时段分单数据列表
     * @param  [type] $id    [description]
     * @param  [type] $group [description]
     * @param  [type] $date  [description]
     * @param  [type] $time  [description]
     * @return [type]        [description]
     */
    public function getCustomerOrderStatisticsList($id, $group, $date, $step) {
        // $where = "where 1=1";

        // if (!empty($id)) {
        //     $where .= " and id = ".$id;
        // }

        // if (!empty($group)) {
        //     $where .= " and kfgroup in (".$group.")";
        // }

        // if (!empty($step)) {
        //     $where .= " and step in (".$step.")";
        // }

        // $sql = "call proc_kftj_time('$date','$date','$where')";
        // return $this->query($sql);

        $mainMap = array();
        if (!empty($id)) {
            $mainMap["t1.id"] = array("EQ", $id);
        }

        if (!empty($group)) {
            if (!is_array($group)) {
                $group = explode(",", $group);
            }

            $mainMap["t1.kfgroup"] = array("IN", $group);
        }

        if (!empty($step)) {
            if (!is_array($step)) {
                $step = explode(",", $step);
            }

            $mainMap["t1.step"] = array("IN", $step);
        }

        $begintime = strtotime($date);
        $endtime = strtotime("+1 days", strtotime($date)) - 1;

        // 日期格式查询条件
        $dateMap = array(
            "a.time_add" => array("between", [
                date("Y-m-d", $begintime),
                date("Y-m-d H:i:s", $endtime)
            ])
        );

        // 时间戳格式查询条件
        $timeMap = array(
            "a.time" => array("between", [ $begintime, $endtime ])
        );

        // 电话记录
        $telSql = M("log_telcenter_ordercall")->alias("a")->where($dateMap)
            ->join("inner join qz_adminuser as u on u.id = a.admin_id")
            ->field([
                "a.orderid", "a.admin_id as id", "a.admin_user as `name`", "u.kfgroup",
                "DATE_FORMAT(a.time_add, '%Y-%m-%d') AS `date`",
                "DATE_FORMAT(a.time_add, '%Y-%m-%d %H:%i') AS `time`"
            ])
            ->order("a.orderid,a.admin_id")
            ->buildSql();
        
        // 合力电话记录
        $hollySql = M("log_holly_order_telcenter")->alias("a")->where($timeMap)
            ->join("inner join qz_adminuser as u on u.id = a.op_uid")
            ->field([
                "a.order_id as orderid", "a.op_uid as id", "a.op_uname as `name`", "u.kfgroup",
                "FROM_UNIXTIME(a.time, '%Y-%m-%d') AS `date`",
                "FROM_UNIXTIME(a.time, '%Y-%m-%d %H:%i') AS `time`"
            ])
            ->order("a.order_id,a.op_uid")
            ->buildSql();

        // 天润电话记录
        $clinkSql = M("log_clink_order_telcenter")->alias("a")->where($timeMap)
            ->join("inner join qz_adminuser as u on u.id = a.op_uid")
            ->field([
                "a.order_id as orderid", "a.op_uid as id", "a.op_uname as `name`", "u.kfgroup",
                "FROM_UNIXTIME(a.time, '%Y-%m-%d') AS `date`",
                "FROM_UNIXTIME(a.time, '%Y-%m-%d %H:%i') AS `time`"
            ])
            ->order("a.order_id,a.op_uid")
            ->buildSql();

        // 电话联合SQL
        $unionSql = M()->table($telSql)->alias("t")
            ->union($hollySql, true)
            ->union($clinkSql, true)
            ->buildSql();


        $baseSql = M()->table($unionSql)->alias("a")
            ->join("left join qz_order_csos_new new on a.orderid = new.order_id and a.id = new.user_id")
            ->join("left join qz_orders o FORCE INDEX(PRIMARY) on o.id = a.orderid")
            ->field([
                "a.id", "a.`name`", "a.`date`", "a.`time`", "a.orderid", "o.cs", "a.kfgroup",
                "if(new.order_on = 4 and o.type_fw = 1,1,0) as fen",
                "if(new.order_on = 4 and o.type_fw = 2,1,0) as zen",
            ])
            ->buildSql();

        $subSql = M()->table($baseSql)->alias("t")
            ->field([
                "t.id", "t.`name`", "t.`date`", "t.kfgroup",
                "CASE    
                    when t.time >= CONCAT(t.date,' ','07:30') and t.time < CONCAT(t.date,' ','08:30') then 1
                    when t.time >= CONCAT(t.date,' ','08:30') and t.time < CONCAT(t.date,' ','09:30') then 2
                    when t.time >= CONCAT(t.date,' ','09:30') and t.time < CONCAT(t.date,' ','10:30') then 3      
                    when t.time >= CONCAT(t.date,' ','10:30') and t.time < CONCAT(t.date,' ','11:30') then 4
                    when t.time >= CONCAT(t.date,' ','11:30') and t.time < CONCAT(t.date,' ','12:30') then 5
                    when t.time >= CONCAT(t.date,' ','12:30') and t.time < CONCAT(t.date,' ','13:30') then 6
                    when t.time >= CONCAT(t.date,' ','13:30') and t.time < CONCAT(t.date,' ','14:30') then 7
                    when t.time >= CONCAT(t.date,' ','14:30') and t.time < CONCAT(t.date,' ','15:30') then 8
                    when t.time >= CONCAT(t.date,' ','15:30') and t.time < CONCAT(t.date,' ','16:30') then 9
                    when t.time >= CONCAT(t.date,' ','16:30') and t.time < CONCAT(t.date,' ','17:30') then 10
                    when t.time >= CONCAT(t.date,' ','17:30') and t.time < CONCAT(t.date,' ','18:30') then 11
                ELSE
                    'other'
                END AS 'step'",
                "CASE
                    when t.time >= CONCAT(t.date,' ','07:30') and t.time < CONCAT(t.date,' ','08:30') then '7:30~8:30'
                    when t.time >= CONCAT(t.date,' ','08:30') and t.time < CONCAT(t.date,' ','09:30') then '8:30~9:30'
                    when t.time >= CONCAT(t.date,' ','09:30') and t.time < CONCAT(t.date,' ','10:30') then '9:30~10:30'      
                    when t.time >= CONCAT(t.date,' ','10:30') and t.time < CONCAT(t.date,' ','11:30') then '10:30~11:30'
                    when t.time >= CONCAT(t.date,' ','11:30') and t.time < CONCAT(t.date,' ','12:30') then '11:30~12:30'
                    when t.time >= CONCAT(t.date,' ','12:30') and t.time < CONCAT(t.date,' ','13:30') then '12:30~13:30'
                    when t.time >= CONCAT(t.date,' ','13:30') and t.time < CONCAT(t.date,' ','14:30') then '13:30~14:30'
                    when t.time >= CONCAT(t.date,' ','14:30') and t.time < CONCAT(t.date,' ','15:30') then '14:30~15:30'
                    when t.time >= CONCAT(t.date,' ','15:30') and t.time < CONCAT(t.date,' ','16:30') then '15:30~16:30'
                    when t.time >= CONCAT(t.date,' ','16:30') and t.time < CONCAT(t.date,' ','17:30') then '16:30~17:30'
                    when t.time >= CONCAT(t.date,' ','17:30') and t.time < CONCAT(t.date,' ','18:30') then '17:30~18:30'
                ELSE
                    'other'
                END AS 'time'",
                "t.`time` as ordertime",
                "sum(t.fen) as fen",
                "SUM(t.zen) as zen",
                "count(t.orderid) as `all`"
            ])
            ->group("t.id,step")
            ->buildSql();

        $list = M()->table($subSql)->alias("t1")->where($mainMap)
            ->field("t1.*")
            ->order("t1.ordertime,t1.kfgroup,t1.id")
            ->select();

        return $list;
    }


    /**
     * 获取最新的一次分单信息
     * @param  [type] $cs [description]
     * @return [type]     [description]
     */
    /*public function getLastTypeFw($id,$cs)
    {
        $map = array(
            "a.id" => array("EQ",$id),
            "b.cs" => array("EQ",$cs),
            "b.on" => array("EQ",4),
            "b.type_fw" => array("EQ",1)
        );
        $buildSql = M("orders")->where($map)->alias("a")
                               ->join("join qz_orders b on b.lasttime < a.lasttime")
                               ->field("b.id")->order("b.lasttime desc")->limit(1)->buildSql();

        return M("orders")->table($buildSql)->alias("t")
                          ->join("join qz_order_info i on i.order = t.id")
                          ->join("join qz_user u on u.id = i.com")
                          ->field("u.jc")->select();
    }*/


    /**
     * [order_tel_encrypt 电话号码加密 手动加盐]
     * @param  [type] $tel [电话号码]
     * @return [type]      [md5($tel.$salt)密文]
     */
    public function order_tel_encrypt($tel) {
        return md5($tel . C('QZ_YUMING'));

    }
    /**
     * 转移订单至黑名单
     * @param  [type] $orderId [description]
     * @return [type]          [description]
     */
    public function addAllBlack($data)
    {
        return M("orders_black")->addAll($data);
    }


    /**
     * 获取订单信息
     * @param  [type] $orders [description]
     * @return [type]         [description]
     */
    public function getOrderInfoList($orders)
    {
        $map = array(
            "id" => array("IN",$orders)
        );
        return M("orders")->where($map)->field("`id`,`userid`,`on`,`fengge`,`type`,`name`,`sex`,`tel`,`tel_encrypt`,`other_contact`,`qx`,`sf`,`xiaoqu`,`time_real`,`time`,`lx`,`dz`,`yt`,`huxing`,`fangshi`,`yusuan`,`zxdc`,`start`,`lftime`,`mianji`,`ip`,`pv`,`shi`,`ting`,`wei`,`text`,`cs`,`lxs`,`keys`,`remarks`,`deleted`,`source`,`source_in`,`source_type`,`des`,`order_id`,`huifan`,`zb_num`,`order_company`,`lasttime`,`visitime`,`typess`,`customer`,`chk_customer`,`fen_customer`,`zhuanfaren`,`types`,`yusuans`,`type_fw`,`qiandan_companyid`,`qiandan_mianji`,`qiandan_jine`,`qiandan_status`,`qiandan_addtime`,`qiandan_chktime`,`qiandan_remark`,`qiandan_remark_lasttime`,`qiandan_info`,`last_reviewid`,`openeye_st`,`openeye_reger`,`openeye_passer`,`openeye_sqly`,`verifyCode`,`order2com_opid`,`order2com_opname`,`order2com_allread`,`yangtai`,`chu`,`nf_time`,`callfast_time`,`callfast_admin_id`,`calllong_time`,`calllong_admin_id`,`from_old_orderid`,`order_to_new`,`order_to_new_remak`,`priority`,`call_status`,`calllast_time`,`on_sub`,`on_sub_wuxiao`")->select();
    }

    public function delAllOrders($orders){
        $map = array(
            "id" => array("IN",$orders)
        );
        return M("orders")->where($map)->delete();
    }

    /**
     * 获取黑名单列表
     * @param  [type] $orders [description]
     * @return [type]         [description]
     */
    public function getBlackOrderList($orders)
    {
        $map = array(
            "a.id" => array("IN",$orders)
        );
        $buildSql = M("orders_black")->where($map)->alias("a")
                                ->join("left join qz_log_admin log on log.action_id = a.id")
                                ->field("a.id,log.time,log.username")->order("log.id desc")->buildSql();
        return M("orders_black")->table($buildSql)->alias("t")
                                ->group("t.id")->select();
    }

    public function getBlackOrderInfo($id)
    {
        $map = array(
            "id" => array("EQ",$id)
        );

        return M("orders_black")->where($map)->field("`id`,`userid`,`on`,`fengge`,`type`,`name`,`sex`,`tel`,`tel_encrypt`,`other_contact`,`qx`,`sf`,`xiaoqu`,`time_real`,`time`,`lx`,`dz`,`yt`,`huxing`,`fangshi`,`yusuan`,`zxdc`,`start`,`lftime`,`mianji`,`ip`,`pv`,`shi`,`ting`,`wei`,`text`,`cs`,`lxs`,`keys`,`remarks`,`deleted`,`source`,`source_in`,`source_type`,`des`,`order_id`,`huifan`,`zb_num`,`order_company`,`lasttime`,`visitime`,`typess`,`customer`,`chk_customer`,`fen_customer`,`zhuanfaren`,`types`,`yusuans`,`type_fw`,`qiandan_companyid`,`qiandan_mianji`,`qiandan_jine`,`qiandan_status`,`qiandan_addtime`,`qiandan_chktime`,`qiandan_remark`,`qiandan_remark_lasttime`,`qiandan_info`,`last_reviewid`,`openeye_st`,`openeye_reger`,`openeye_passer`,`openeye_sqly`,`verifyCode`,`order2com_opid`,`order2com_opname`,`order2com_allread`,`yangtai`,`chu`,`nf_time`,`callfast_time`,`callfast_admin_id`,`calllong_time`,`calllong_admin_id`,`from_old_orderid`,`order_to_new`,`order_to_new_remak`,`priority`,`call_status`,`calllast_time`,`on_sub`,`on_sub_wuxiao`")->find();
    }

    /**
     * 删除黑名单
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delOrderBlack($id)
    {
        $map = array(
            "id" => array("EQ",$id)
        );

        return  M("orders_black")->where($map)->delete();
    }


    public function getDockingListCount($cs,$fen_customer,$id,$other_cs,$begin,$end,$status,$user_id,$kf,$source)
    {
        $map = array(
            "o.cs" => array("IN",$cs),
            "o.on" => array("EQ",4),
            "o.type_fw" => array("IN",array(1,2)),
            "o.fen_customer" => array("EQ",0)
        );

        if (!empty($fen_customer)) {
            unset($map["o.fen_customer"]);
            if (!empty($begin) && !empty($end)) {
                $map["o.lasttime"] = array(
                    array("EGT",strtotime($begin)),
                    array("ELT",strtotime("+1 day",strtotime($end))-1)
                );
            }

        } else {
            if (!empty($begin) && !empty($end)) {
                $map["new.lasttime"] = array(
                    array("EGT",strtotime($begin)),
                    array("ELT",strtotime($end))
                );
            }
        }

        if (!empty($id)) {
            $map["_complex"] =array(
                "o.id" => array("EQ",$id),
                "o.xiaoqu" => array("LIKE","%$id%"),
                "_logic" => "OR"
            );
        }

        if (!empty($other_cs)) {
            $map["o.cs"] = array("EQ",$other_cs);
        }

        if (!empty($status)) {
            $map["o.type_fw"] = array("EQ",$status);
        }

        if (!empty($kf)) {
            $map["o.fen_customer"] = array("EQ",$kf);
        }

        if ($source != "") {
            $map["o.source_in"] = array("EQ",$source);
        }

        return M("orders")->where($map)->alias("o")
                          ->join("join qz_order_pool b on b.orderid = o.id and b.status = 0")
                          ->join("join qz_order_csos_new new on new.order_id = o.id")
                          ->count();
    }

    /**
     * 获取对接的订单列表数量
     * @param  [type] $cs [description]
     * @return [type]     [description]
     */
    public function getDockingList($cs,$fen_customer,$id,$other_cs,$pageIndex,$pageCount,$begin,$end,$status,$user_id,$kf,$isactivity = 0,$ids,$source,$qx, $fen_citys)
    {
        if(empty($cs)){
            return [];
        }
        if($fen_customer == 2){
            //待分配
            $fen_customer = 0;
            $map = array(
                "o.cs" => array("IN", $cs),
                "o.on" => array("EQ", 4),
                "o.type_fw" => array("in", array(5, 6))
            );
        } else {
            $map = array(
                "o.cs" => array("IN", $cs),
                "o.on" => array("EQ", 4),
                "o.type_fw" => array("IN", array(1, 2)),
                "o.fen_customer" => array("EQ", 0)
            );

            // k.1.7.5 三方对接流程优化V1.0.0 需求
            // 未分配订单城市会员的判断：订单状态为分单时,只有城市会员>4家||城市会员<=4家&&所属城市不接受质检介入的订单才显示到该页面
            // @author by liuyupeng
            if ($fen_customer == 0) {
                $fencitys = "''";
                if (!empty($fen_citys)) {
                    $fencitys = implode(",", array_map(function($item){
                        return "'$item'";
                    }, $fen_citys));
                }

                $map["_string"] = "o.cs IN ($fencitys) or o.type_fw = 2";
            }
        }

        if (!empty($qx)) {
            $map['o.qx'] = ['eq', $qx];
        }

        if (!empty($fen_customer)) {
            unset($map["o.fen_customer"]);
            if (!empty($begin) && !empty($end)) {
                $map["o.lasttime"] = array(
                    array("EGT",strtotime($begin)),
                    array("ELT",strtotime("+1 day",strtotime($end))-1)
                );
            }
        } else {
            if (!empty($begin) && !empty($end)) {
                $map["new.lasttime"] = array(
                    array("EGT",strtotime($begin)),
                    array("ELT",strtotime("+1 day",strtotime($end))-1)
                );
            }
        }

        if (!empty($id)) {
            $map["_complex"] = array(
                "o.id" => array("EQ",$id),
                "o.xiaoqu" => array("LIKE","%$id%"),
                "_logic" => "OR"
            );
        }

        if (!empty($other_cs)) {
            $map["o.cs"] = array("EQ",$other_cs);
        }

        if (!empty($status)) {
            $map["o.type_fw"] = array("EQ",$status);
        }

        if (!empty($kf)) {
            $map["o.fen_customer"] = array("EQ",$kf);
        }

        if (!empty($isactivity)) {
            if (count($ids) > 0) {
                if ($isactivity == 1) {
                    $map['o.source'] = array("IN",$ids);
                } else {
                    $map['o.source'] = array("NOT IN",$ids);
                }
            }
        }

        if ($source != "") {
            $map["o.source_in"] = array("EQ",$source);
        }

        $buildSql = M("orders")->where($map)->alias("o")
                          ->join("join qz_order_pool b on b.orderid = o.id and b.status = 0")
                          ->join("join qz_order_csos_new new on new.order_id = o.id")
                          ->field("o.id,o.source_in,o.time_real,o.remarks,o.cs,o.qx,o.wzd,o.mianji,o.tel,o.on,o.type_fw,FROM_UNIXTIME(new.lasttime) csos_time,FROM_UNIXTIME(o.lasttime) lasttime,o.source")
                          ->order("o.lasttime desc")
                          ->limit($pageIndex.",".$pageCount)
                          ->buildSql();
        return M("orders")->table($buildSql)->alias("t")
                          ->join("left join qz_quyu q on q.cid = t.cs")
                          ->join("left join qz_area a on a.qz_areaid = t.qx")
                          ->field("t.*,q.cname,a.qz_area")
                          ->select();
    }

    public function getKeFuOrdersListCount($id = 0, $tel_encrypt = '',$city,$begin,$end)
    {
        //获取用户信息
        $admin = getAdminUser();

        //订单id
        if (!empty($id)) {
            $map['o.id'] = $id;
        }
        //订单电话
        if (!empty($tel_encrypt)) {
            $map['o.tel_encrypt'] = $tel_encrypt;
        }

        if (!empty($city)) {
            $map['o.cs'] = array("EQ",$city);
        }

        if (!empty($begin) && !empty($end)) {
            $map['o.time_real'] = array(
                array("EGT",$begin),
                array("ELT",$end)
            );
        }

        $db = M('orders');
        //查询电话号码指定索引
        if (!empty($map['_complex']['o.tel_encrypt'])){
            $db = $db->force('idx_tel_id');
        } elseif (!empty($map['o.time_real'])) {
            $db = $db->force('idx_time_real');
        } elseif (!empty($map['o.time'])) {
            $db = $db->force('idx_time_on');
        }

        $field = 'o.id,o.time_real,o.time,o.cs,o.qx,o.tel,o.tel_encrypt,o.nf_time,o.mianji,o.visitime,o.on,o.on_sub,o.type_fw,o.type_zs_sub,o.order2com_allread,o.from_old_orderid,o.remarks,o.lasttime,o.openeye_st,o.openeye_reger,o.openeye_sqly,o.calllong_time,o.callfast_time,o.wzd,o.zhuanfaren';
        if (1 != $admin['uid']) {
            //注意！更改的话下面同样更改;如果是超级管理员 黑名单的订单也显示 不是超级管理员 黑名单的号码的订单不显示,
            $field = $field . ',b.status AS order_blacklist_status';
        }

        $db = $db->field($field)
            ->alias('o');
        if (1 != $admin['uid']) {
            //如果是超级管理员 黑名单的订单也显示 不是超级管理员 黑名单的号码的订单不显示
            $map['b.status'] = array(array('EQ', 0),array('EXP',' IS NULL '),'OR');
            $db = $db->join('LEFT JOIN qz_order_blacklist AS b ON b.tel_encrypt = o.tel_encrypt');
        }

        return $db->where($map)->count();
    }

    /**
     * [getOrdersList 后台查询已发订单]
     * @param  integer $id              [订单ID]
     * @param  string  $tel_encrypt     [订单加密后电话号码]
     * @return [type]                   [description]
     */
    public function getKeFuOrdersList($id = 0, $tel_encrypt = '',$city,$begin,$end,$pageIndex,$pageCount)
    {
        //获取用户信息
        $admin = getAdminUser();

        //订单id
        if (!empty($id)) {
            $map['o.id'] = $id;
        }
        //订单电话
        if (!empty($tel_encrypt)) {
            $map['o.tel_encrypt'] = $tel_encrypt;
        }

        if (!empty($city)) {
            $map['o.cs'] = array("EQ",$city);
        }

        if (!empty($begin) && !empty($end)) {
            $map['o.time_real'] = array(
                array("EGT",$begin),
                array("ELT",$end)
            );
        }

        $db = M('orders');
        //查询电话号码指定索引
        if (!empty($map['_complex']['o.tel_encrypt'])){
            $db = $db->force('idx_tel_id');
        } elseif (!empty($map['o.time_real'])) {
            $db = $db->force('idx_time_real');
        } elseif (!empty($map['o.time'])) {
            $db = $db->force('idx_time_on');
        }

        $field = 'o.id,o.time_real,o.time,o.cs,o.qx,o.tel,o.tel_encrypt,o.nf_time,o.mianji,o.visitime,o.on,o.on_sub,o.type_fw,o.type_zs_sub,o.order2com_allread,o.from_old_orderid,o.remarks,o.lasttime,o.openeye_st,o.openeye_reger,o.openeye_sqly,o.calllong_time,o.callfast_time,o.wzd,o.zhuanfaren';
        if (1 != $admin['uid']) {
            //注意！更改的话下面同样更改;如果是超级管理员 黑名单的订单也显示 不是超级管理员 黑名单的号码的订单不显示,
            $field = $field . ',b.status AS order_blacklist_status';
        }

        $db = $db->field($field)
                 ->alias('o');
        if (1 != $admin['uid']) {
            //如果是超级管理员 黑名单的订单也显示 不是超级管理员 黑名单的号码的订单不显示
            $map['b.status'] = array(array('EQ', 0),array('EXP',' IS NULL '),'OR');
            $db = $db->join('LEFT JOIN qz_order_blacklist AS b ON b.tel_encrypt = o.tel_encrypt');
        }

        $build = $db->where($map) ->limit($pageIndex,$pageCount)->buildSql();
        $result = M()->table($build)->alias('z')
                                    ->field('z.*, q.cname AS city, a.qz_area AS area ,p.op_name')
                                    ->join('LEFT JOIN qz_quyu AS q ON q.cid = z.cs')
                                    ->join('LEFT JOIN qz_area AS a ON a.qz_areaid = z.qx')
                                    ->join('LEFT JOIN qz_order_pool AS p ON p.orderid = z.id')
                                    ->select();
        return $result;
    }

    /**
     * 获取签单统计列表数量
     * @param  [type] $cs    [城市]
     * @param  [type] $begin [开始时间]
     * @param  [type] $end   [结束时间]
     * @return [type]        [description]
     */
    public function orderQiandanTelListCount($cs,$begin,$end,$type)
    {
        $map = array(
            "new.lasttime" => array(
                array("EGT",$begin),
                array("LT",$end)
            ),
            "o.qiandan_companyid" => array("NEQ",0)
        );

        if (!empty($cs)) {
            $map["o.cs"] = array("EQ",$cs);
        }

        if (!empty($type)) {
            $map["o.type_fw"] = array("EQ",$type);
        }

        return M("order_csos_new")->where($map)->alias("new")
                                  ->join("join qz_orders o force INDEX(primary) on new.order_id = o.id")
                                  ->count();
    }

    /**
     * 获取签单统计列表
     * @param  [type] $cs        [城市]
     * @param  [type] $begin     [开始时间]
     * @param  [type] $end       [结束时间]
     * @param  [type] $pageIndex [description]
     * @param  [type] $pageCount [description]
     * @return [type]            [description]
     */
    public function orderQiandanTelList($cs,$begin,$end,$type)
    {
        $map = array(
            "new.lasttime" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            "o.qiandan_companyid" => array("NEQ",0)
        );

        if (!empty($cs)) {
            $map["o.cs"] = array("EQ",$cs);
        }

        if (!empty($type)) {
            $map["o.type_fw"] = array("EQ",$type);
        }

        $buildSql = M("order_csos_new")->where($map)->alias("new")
                                       ->join("join qz_orders o force INDEX(primary) on new.order_id = o.id")
                                       ->join("join qz_quyu q on q.cid = o.cs")
                                       ->join("join qz_area area on area.qz_areaid = o.qx")
                                       ->field('o.id,FROM_UNIXTIME(o.time_real,"%Y-%m-%d") as time_real,o.xiaoqu,o.mianji,o.qiandan_jine,o.qiandan_companyid,o.type_fw,FROM_UNIXTIME(new.lasttime,"%Y-%m-%d") as lasttime,FROM_UNIXTIME(o.qiandan_addtime,"%Y-%m-%d") as qiandan_addtime,q.cname,area.qz_area as qx')
                                       ->buildSql();
        return M("order_csos_new")->table($buildSql)->alias("t")
                           ->join("left join qz_log_telcenter_ordercall cal on cal.orderid = t.id")
                           ->join("left join qz_log_telcenter tel on tel.callSid = cal.callSid and tel.action = 'Hangup'")
                           ->field("t.*,count(t.id) as count,cal.orderid")->group("t.id")
                           // ->limit($pageIndex.",".$pageCount)
                           ->order("count")
                           ->select();
    }

    /**
     * 获取客服电话行为数据
     * @param  [type] $begin   [开始时间]
     * @param  [type] $end     [结束时间]
     * @param  [type] $kf      [客服ID]
     * @param  [type] $group   [客服组]
     * @param  [type] $manager [客服管理人]
     * @param  [type] $city    [城市]
     * @return [type]          [description]
     */
    public function getCustomerOrderTelstatList($begin, $end, $kf, $group, $manager, $city)
    {
        $map = array(
            "a.addtime" => array(
                array("EGT", $begin),
                array("ELT", $end)
            )
        );

        if (!empty($kf)) {
            $map["a.op_uid"] = array("EQ", $kf);
        }

        if (!empty($group)) {
            $map["u.kfgroup"] = array("EQ", $group);
        }

        if (!empty($manager)) {
            $map["d.id"] = array("EQ", $manager);
        }

        if (!empty($city)) {
            $map["o.cs"] = array("EQ", $city);
        }

        $baseSql = M("order_pool")->where($map)->alias("a force INdex(idx_pool_addtime)")
            ->join("join qz_orders o on a.orderid = o.id")
            ->join("join qz_adminuser u on u.id = a.op_uid")
            ->join("join qz_adminuser d on d.id = substring_index(u.kfmanager,',',1) ")
            ->field("a.op_uid,a.op_name,o.id,o.`on`,o.type_fw,o.cs,u.kfgroup,u.kfmanager,d.`name` as manager")
            ->buildSql();

        $buildSql = M()->table($baseSql)->alias("t")
            ->join("left join qz_log_telcenter_ordercall cal on cal.orderid = t.id")
            ->join('left join qz_log_telcenter tel force INdex(idx_action_callsid) on tel.action = "Hangup" and tel.callSid = cal.callSid ')
            ->field([
                "t.*", "cal.callSid", "tel.starttime", "tel.endtime",
                "TIMESTAMPDIFF(MINUTE, tel.starttime, tel.endtime) + 1 as time_off",
                "IF (endtime >= FROM_UNIXTIME(".$begin.", '%Y%m%d%H%i%s') and endtime <= FROM_UNIXTIME(".$end.", '%Y%m%d%H%i%s') AND starttime != endtime,1,0) AS overMark",
                "if(cal.callSid is not null OR (cal.time_add >= FROM_UNIXTIME(".$begin.", '%Y-%m-%d %H:%i:%s') and cal.time_add < FROM_UNIXTIME(".$end.", '%Y-%m-%d %H:%i:%s')),1,0) as newMark"
            ])
            ->buildSql();

        // 合力电话记录
        $hollySql = M()->table($baseSql)->alias("t")
            ->join("left join qz_log_holly_order_telcenter cal on cal.order_id = t.id")
            ->join("left join qz_log_holly_telcenter tel on tel.call_sid = cal.call_sid")
            ->field([
                "t.*", "cal.call_sid as callSid", "tel.begin as starttime", "tel.end as endtime",
                "TIMESTAMPDIFF(MINUTE, tel.begin, tel.end) + 1 as time_off",
                "IF (tel.end >= FROM_UNIXTIME(".$begin.") AND tel.end <= FROM_UNIXTIME(".$end.") AND tel.begin != '0000-00-00 00:00:00' AND tel.begin != tel.end,1,0) AS overMark",
                "IF(cal.call_sid is not null OR (cal.time >= ".$begin." and cal.time < ".$end."),1,0) as newMark"
            ])
            ->buildSql();

        // 天润电话记录
        $clinkSql = M()->table($baseSql)->alias("t")
            ->join("left join qz_log_clink_order_telcenter as cal on cal.order_id = t.id")
            ->join("left join qz_log_clink_telcenter as tel on tel.call_sid = cal.call_sid")
            ->field([
                "t.*", "cal.call_sid as callSid", "tel.cdr_bridge_time as starttime", "tel.cdr_end_time as endtime",
                "IF(tel.cdr_bridge_time > 0 AND tel.cdr_end_time > 0, TIMESTAMPDIFF(MINUTE, FROM_UNIXTIME(tel.cdr_bridge_time, '%Y-%m-%d %H:%i:%s'), FROM_UNIXTIME(tel.cdr_end_time, '%Y-%m-%d %H:%i:%s')) + 1, 0) as time_off",
                "IF (tel.cdr_end_time >= ".$begin." AND tel.cdr_end_time <= ".$end." AND tel.cdr_bridge_time >= 0 AND tel.cdr_bridge_time <> tel.cdr_end_time,1,0) AS overMark",
                "IF(cal.call_sid is not null OR (cal.time >= ".$begin." and cal.time < ".$end."),1,0) as newMark"
            ])
            ->buildSql();

        $buildSql = M()->table($buildSql)->alias("t")
            ->union($hollySql, true)
            ->union($clinkSql, true)
            ->buildSql();

        return M()->table($buildSql)->alias("t1")
            ->field('t1.id,t1.op_uid,t1.op_name,t1.kfgroup,t1.kfmanager,t1.manager,t1.`on`,t1.type_fw,t1.cs,sum(if(t1.time_off is not null and t1.newMark = 1 and t1.overMark = 1,t1.time_off,0)) as all_time,avg(if(t1.time_off is not null and t1.time_off and t1.overMark = 1 and t1.newMark = 1,t1.time_off,0)) as avg_time,count(if(t1.newMark = 1,1,null)) as count,count(if(t1.newMark = 1 and t1.overMark = 1,1,null)) as overCount,q.cname')
            ->join("join qz_quyu q on q.cid = t1.cs")
            ->group("t1.id,t1.op_uid")
            ->select();
    }


    /**
     * 获取客服下午呼叫行为
     * @param  [type] $kf      [客服ID]
     * @param  [type] $group   [客服组]
     * @param  [type] $manager [客服管理人]
     * @return [type]          [description]
     */
    public function getOrderCallActionList($kf, $group, $manager, $time) {
        if (empty($time)) {
            $time = date("Y-m-d");
        }
        $date_begin = $time . " 12:59:59";
        $date_end = $time . " 17:45:00";
        $time_begin = strtotime($date_begin);
        $time_end = strtotime($date_end);

        // 日期查询条件
        $dateMap = array(
            "time_add" => [
                array("GT", $date_begin),
                array("ELT", $date_end)
            ]
        );

        // 时间戳查询条件
        $timeMap = array(
            "time" => [
                array("GT", $time_begin),
                array("ELT", $time_end)
            ]
        );

        $userMap = [];
        if (!empty($kf)) {
            $userMap["cal.admin_id"] = array("EQ", $kf);
        }

        if (!empty($group)) {
            $userMap["u.kfgroup"] = array("EQ", $group);
        }

        if (!empty($manager)) {
            $userMap["d.id"] = array("EQ", $manager);
        }

        // 电话查询条件
        $telSql = M("log_telcenter_ordercall")->where($dateMap)
            ->field(["callSid as call_sid", "admin_id", "admin_user", "time_add", "DATE_FORMAT(time_add, '%H:%i:%S') first_time"])
            ->buildSql();

        // 合力电话查询
        $hollySql = M("log_holly_order_telcenter")->where($timeMap)
            ->field([
                "call_sid", "op_uid as admin_id", "op_uname as admin_user",
                "FROM_UNIXTIME(time) as time_add",
                "FROM_UNIXTIME(time, '%H:%i:%S') as first_time"
            ])
            ->buildSql();

        // 天润电话查询
        $clinkSql = M("log_clink_order_telcenter")->where($timeMap)
            ->field([
                "call_sid", "op_uid as admin_id", "op_uname as admin_user",
                "FROM_UNIXTIME(time) as time_add",
                "FROM_UNIXTIME(time, '%H:%i:%S') as first_time"
            ])
            ->buildSql();

        // 联合查询
        $unionSql = M()->table($telSql)->alias("tel")
            ->union($hollySql, true)
            ->union($clinkSql, true)
            ->buildSql();

        $firstSql = M()->table($unionSql)->alias("cal")->where($userMap)
            ->join("join qz_adminuser as u on u.id = cal.admin_id")
            ->join("join qz_adminuser as d on d.id = substring_index(u.kfmanager, ',', 1)")
            ->join("join qz_adminuser as g on g.kfgroup = u.kfgroup AND g.uid = 31")
            ->field([
                "u.kfgroup", "cal.admin_id as kfid", "cal.admin_user as kfname",
                "g.`name` as kfgroup_name", "d.`name` as manager",
                "substring_index(u.kfmanager, ',', 1) as kfmanager",
                "cal.time_add", "cal.first_time", "cal.call_sid"
            ])
            ->order("cal.time_add asc")
            ->buildSql();

        // 第一次呼叫时间
        $firstCall = M()->table($firstSql)->alias("t")
            ->group("t.kfid")
            ->select();

        return $firstCall;
    }

    // 获取客服下午呼叫行为(首次接通时间)
    public function getOrderCallConnectActionList($kf, $group, $manager, $time){
        if (empty($time)) {
            $time = date("Y-m-d");
        }
        $date_begin = $time . " 12:59:59";
        $date_end = $time . " 17:45:00";
        $time_begin = strtotime($date_begin);
        $time_end = strtotime($date_end);

        // 日期查询条件
        $dateMap = array(
            "t.time_add" => [
                array("GT", $date_begin),
                array("ELT", $date_end)
            ]
        );

        // 时间戳查询条件
        $timeMap = array(
            "t.time" => [
                array("GT", $time_begin),
                array("ELT", $time_end)
            ]
        );

        $userMap = [];
        if (!empty($kf)) {
            $userMap["cal.kfid"] = array("EQ", $kf);
        }

        if (!empty($group)) {
            $userMap["u.kfgroup"] = array("EQ", $group);
        }

        if (!empty($manager)) {
            $userMap["d.id"] = array("EQ", $manager);
        }
       

        // 电话查询条件
        $telSql = M("log_telcenter_ordercall")->alias("t")->where($dateMap)
            ->join("inner join qz_log_telcenter as tel on tel.callSid = t.callSid")
            ->where("tel.action = 'hangup' and tel.endtime <> '' and tel.starttime <> tel.endtime")
            ->field([
                "t.admin_id as kfid",
                "tel.starttime as firsttime",
                "DATE_FORMAT(tel.starttime, '%H:%i:%S') as starttime",
                "DATE_FORMAT(tel.endtime, '%H:%i:%S') as endtime",
                "TIMESTAMPDIFF(SECOND, tel.starttime, tel.endtime) as time_diff"
            ])
            ->buildSql();

        // 合力电话查询
        $hollySql = M("log_holly_order_telcenter")->alias("t")->where($timeMap)
            ->join("inner join qz_log_holly_telcenter as tel on tel.call_sid = t.call_sid")
            ->where("tel.call_time_length > 0 and tel.call_state in ('Hangup','Unlink') and tel.hanguper in ('caller','callee')")
            ->field([
                "t.op_uid as kfid",
                "tel.begin as firsttime",
                "DATE_FORMAT(tel.begin, '%H:%i:%S') as starttime",
                "DATE_FORMAT(tel.end, '%H:%i:%S') as endtime",
                "tel.call_time_length as time_diff"
            ])
            ->buildSql();

        // 天润电话查询
        $clinkSql = M("log_clink_order_telcenter")->alias("t")->where($timeMap)
            ->join("inner join qz_log_clink_telcenter as tel on tel.call_sid = t.call_sid")
            ->where("tel.cdr_bridge_time > 0 and tel.cdr_end_time > 0 and tel.cdr_bridge_time <> tel.cdr_end_time")
            ->field([
                "t.op_uid as kfid",
                "FROM_UNIXTIME(tel.cdr_bridge_time) as firsttime",
                "FROM_UNIXTIME(tel.cdr_bridge_time, '%H:%i:%S') as starttime",
                "FROM_UNIXTIME(tel.cdr_end_time, '%H:%i:%S') as endtime",
                "(tel.cdr_end_time - tel.cdr_bridge_time) as time_diff"
            ])
            ->buildSql();

        // 联合查询
        $unionSql = M()->table($telSql)->alias("tel")
            ->union($hollySql, true)
            ->union($clinkSql, true)
            ->buildSql();

        $firstSql = M()->table($unionSql)->alias("cal")->where($userMap)
            ->join("join qz_adminuser as u on u.id = cal.kfid")
            ->join("join qz_adminuser as d on d.id = substring_index(u.kfmanager, ',', 1)")
            ->field("cal.*")
            ->order("cal.firsttime asc,cal.endtime asc")
            ->buildSql();

        // 第一次接通时间
        $firstConnect = M()->table($firstSql)->alias("t")
            ->group("t.kfid")
            ->select();

        return $firstConnect;
    }


    /**
     *  对接客服行为统计
     * @param  [type] $id    [对接客服ID]
     * @param  [type] $group [客服组]
     * @param  [type] $begin [开始时间]
     * @param  [type] $end   [结束时间]
     * @param  [type] $end   [是否用于统计]
     * @return [type]        [description]
     */
    public function getCustomerOrderDockingStat($begin,$end,$id,$city)
    {
        $where = "";

        if (!empty($id)) {
            $where = " and u.id =".$id;
        }

        if (!empty($city)) {
            $where = " and t2.cs =".$city;
        }

        $sql = "
            select u.`name`,u.id,u.kfgroup,substring_index(u.kfmanager,',',1) as kfmanager,t2.*
            from qz_adminuser u
            left join (
                select
                t1.*,max(mark) as back_mark,
                count(t1.orderid) AS 'once_count',q.cname
                from (
                    select
                    t.*,IF(csos.new_on = 99, 1, 0)  as mark
                    from (
                        select o.id as orderid, a.time as lasttime,o.on,o.type_fw,o.fen_customer,o.cs, a.op_uid,new.lasttime as firsttime ,
                        TIMESTAMPDIFF(MINUTE, FROM_UNIXTIME(new.lasttime),FROM_UNIXTIME(a.time))+1 as `offset_time`,
                        TIMESTAMPDIFF(HOUR, FROM_UNIXTIME(new.lasttime),FROM_UNIXTIME(a.time)) as time_diff
                        from qz_order_docking  a FORCE index(idx_time)
                        join qz_orders o on a.order_id = o.id
                        join qz_order_csos_new new on new.order_id = o.id
                        where a.time >= $begin and a.time <= $end and ( o.ON = 4 or o.on = 99 )
                    ) t left join qz_log_order_csos csos on csos.orderid = t.orderid and    (   csos.new_on = 4 OR ( csos.old_on = 4 AND csos.new_on = 99) )
                    ORDER BY mark desc
                ) t1
                join qz_quyu q on q.cid = t1.cs and time_diff <= 3
                group by orderid
            ) t2 on t2.op_uid = u.id
            where u.uid in(97,31) and u.stat = 1 $where
        ";

        return $this->db(1,"DB_CONFIG1")->query($sql);
    }

    /**
     * 获取质检列表
     * @param  [type] $begin      [实际发布时间]
     * @param  [type] $end        [实际发布时间]
     * @param  [type] $id         [订单ID等]
     * @param  [type] $type       [订单类型]
     * @param  [type] $cs         [订单城市]
     * @param  [type] $manager    [客服时]
     * @param  [type] $group      [客服组]
     * @param  [type] $user       [客服]
     * @param  [type] $time_start [客服对接时间]
     * @param  [type] $time_end   [客服对接时间]
     * @param  [type] $status     [质检状态]
     * @param  [type] $source     [订单来源]
     * @param  [type] $chk_start     [质检开始时间]
     * @param  [type] $chk_end     [质检结束时间]
     * @return [type]        [description]
     */
    public function getQcListCount($uid,$begin,$end,$id,$type,$cs,$manager,$group,$user,$time_start,$time_end,$status,$source,$chk_start,$chk_end,$src,$ids)
    {
        $map = array(
            "o.time_real" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            "_string" => "(o.on_sub <> 10)",
            "i.order_id" => array("exp","is null"),
        );

        if (count($ids) > 0) {
            $map['o.source'] = array("NOT IN",$ids);
        }

        if (!empty($id)) {
            $map1["_complex"] = array(
               "id" => array("EQ",$id),
               "xiaoqu" => array("LIKE","%$id%"),
               "tel8" => array("EQ",$id),
               "ip" => array("EQ",$id),
               "_logic" => "OR"
            );
        }

        if (!empty($cs)) {
            $map1["cs"] = array("EQ",$cs);
        }
        if (!empty($group)) {
            $map1["kfgroup"] = array("EQ",$group);
        }

        if (!empty($user)) {
            $map1["uid"] = array("EQ",$user);
        }

        if (!empty($manager)) {
            $map1["kfmanager"] = array("EQ",$manager);
        }

        if(!empty($time_start) && !empty($time_end)) {
            $map1["lasttime"] = array(
                  array("EGT",$time_start),
                  array("ELT",$time_end)
            );
        }

        if ($status !== "") {
            switch ($status) {
                case '1':
                    $map1["state"] = array("IN",array(1,2));
                    break;
                case '2':
                    $map1["_complex"] = array(
                         "state" => array("EQ",2),
                         "sampling_status" => array("EQ",2)
                    );
                    break;
                case '3':
                    $map1["_complex"] = array(
                         "state" => array("EQ",2),
                         "sampling_status" => array("EQ",1)
                    );
                    break;
                case '4':
                    $map1["state"] = array("EQ",3);
                    break;
                default:
                    $map1["state"] = array("EQ",$status);
                    break;
            }
        }

        if (!empty($type)) {
            switch ($type) {
                case 1:
                    $map1["on"] = array("EQ",4);
                    $map1["type_fw"] = array("EQ",1);
                    break;
                case 2:
                    $map1["on"] = array("EQ",4);
                    $map1["type_fw"] = array("EQ",2);
                    break;
                case 3:
                    $map1["on"] = array("EQ",0);
                    $map1["on_sub"] = array("EQ",9);
                    break;
                case 4:
                    $map1["on"] = array("EQ",0);
                    $map1["on_sub"] = array("EQ",8);
                    break;
                case 5:
                    $map1["on"] = array("EQ",2);
                    break;
                case 6:
                    $map1["on"] = array("EQ",5);
                    break;
            }
        }

        if (!empty($source)) {
            $map1["source_type"] = array("EQ",$source);
            if ($source == 99) {
                $map1["source_type"] = array("IN",array(0,4));
            }
        }

        if(!empty($chk_start) && !empty($chk_end)) {
            $map1["time"] = array(
                  array("EGT",strtotime($chk_start)),
                  array("ELT",strtotime($chk_end)+86400-1)
            );
        }

        if (!empty($src)) {
            $map1["alias"] = array("EQ",$src);
        }

        $buildSql = M("orders")->where($map)->alias("o")
                   ->join("left join qz_qc_info i on i.order_id = o.id")
                   ->join("left join safe_order_tel8 tel on tel.orderid = o.id ")
                   ->join("left join qz_orders_source s on s.orderid = o.id ")
                   ->join("left join qz_order_source s1 on s1.src =  s.source_src")
                   ->join("left join qz_order_csos_new new ON new.order_id = o.id")
                   ->join("left join qz_adminuser u on u.id = new.user_id")
                   ->field("o.id,o.time_real,o.on,o.type_fw,o.on_sub,u.name as chk_customer,o.fen_customer, '0' as state,'' as op_name,substring_index(u.kfmanager,',',1) as kfmanager,u.kfgroup,u.id as uid,o.cs,o.lasttime,o.xiaoqu,i.sampling_status,tel.tel8,o.source_type,i.time,o.ip,o.source,s.source_src as src,s1.alias")
                   ->union("select o.id,o.time_real,o.on,o.type_fw,o.on_sub,o.chk_customer,o.fen_customer, i.status as state,i.op_name,substring_index(u.kfmanager,',',1) as kfmanager,u.kfgroup,u.id as uid,o.cs,o.lasttime,o.xiaoqu,i.sampling_status,tel.tel8,o.source_type,i.time,o.ip,o.source,s.source_src as src,s1.alias from qz_qc_info i
                        join qz_orders o on o.id = i.order_id
                        left join safe_order_tel8 tel on tel.orderid = o.id
                        left join qz_orders_source s on s.orderid = i.order_id
                        left join qz_order_source s1 on s1.src =  s.source_src
                        left join qz_order_csos_new new ON new.order_id = o.id
                        left join qz_adminuser u on u.id = new.user_id
                        where op_uid = ".$uid."",true)
                   ->buildSql();
        return M("orders")->table($buildSql)->where($map1)->alias("t")->count();
    }

    /**
     * 获取质检列表
     * @param  [type] $begin      [实际发布时间]
     * @param  [type] $end        [实际发布时间]
     * @param  [type] $id         [订单ID等]
     * @param  [type] $type       [订单类型]
     * @param  [type] $cs         [订单城市]
     * @param  [type] $manager    [客服时]
     * @param  [type] $group      [客服组]
     * @param  [type] $user       [客服]
     * @param  [type] $time_start [客服对接时间]
     * @param  [type] $time_end   [客服对接时间]
     * @param  [type] $status     [质检状态]
     * @param  [type] $source     [订单来源]
     * @param  [type] $chk_start     [质检开始时间]
     * @param  [type] $chk_end     [质检结束时间]
     * @return [type]        [description]
     */
    public function getQcList($uid,$begin,$end,$pageIndex,$pageCount,$id,$type,$cs,$manager,$group,$user,$time_start,$time_end,$status,$source,$chk_start,$chk_end,$src,$ids)
    {
        $map = array(
            "o.time_real" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            "i.order_id" => array("exp","is null"),
            "_string" => "(o.on_sub <> 10)",
        );

        if (count($ids) > 0) {
            $map['o.source'] = array("NOT IN",$ids);
        }

        if (!empty($id)) {
            $map1["_complex"] = array(
               "id" => array("EQ",$id),
               "xiaoqu" => array("LIKE","%$id%"),
               "tel8" => array("EQ",$id),
               "ip" => array("EQ",$id),
               "_logic" => "OR"
            );
        }

        if (!empty($cs)) {
            $map1["cs"] = array("EQ",$cs);
        }
        if (!empty($group)) {
            $map1["kfgroup"] = array("EQ",$group);
        }

        if (!empty($user)) {
            $map1["uid"] = array("EQ",$user);
        }

        if (!empty($manager)) {
            $map1["kfmanager"] = array("EQ",$manager);
        }

        if(!empty($time_start) && !empty($time_end)) {
            $map1["lasttime"] = array(
                  array("EGT",$time_start),
                  array("ELT",$time_end)
            );
        }

        if ($status !== "") {
            switch ($status) {
                case '1':
                    $map1["state"] = array("IN",array(1,2));
                    break;
                case '2':
                    $map1["_complex"] = array(
                         "state" => array("EQ",2),
                         "sampling_status" => array("EQ",2)
                    );
                    break;
                case '3':
                    $map1["_complex"] = array(
                         "state" => array("EQ",2),
                         "sampling_status" => array("EQ",1)
                    );
                    break;
                case '4':
                    $map1["state"] = array("EQ",3);
                    break;
                default:
                    $map1["state"] = array("EQ",$status);
                    break;
            }
        }

        if (!empty($type)) {
            switch ($type) {
                case 1:
                    $map1["on"] = array("EQ",4);
                    $map1["type_fw"] = array("EQ",1);
                    break;
                case 2:
                    $map1["on"] = array("EQ",4);
                    $map1["type_fw"] = array("EQ",2);
                    break;
                case 3:
                    $map1["on"] = array("EQ",0);
                    $map1["on_sub"] = array("EQ",9);
                    break;
                case 4:
                    $map1["on"] = array("EQ",0);
                    $map1["on_sub"] = array("EQ",8);
                    break;
                case 5:
                    $map1["on"] = array("EQ",2);
                    break;
                case 6:
                    $map1["on"] = array("EQ",5);
                    break;
            }
        }

        if (!empty($source)) {
            $map1["source_type"] = array("EQ",$source);
            if ($source == 99) {
                $map1["source_type"] = array("IN",array(0,4));
            }
        }

        if(!empty($chk_start) && !empty($chk_end)) {
            $map1["time"] = array(
                  array("EGT",strtotime($chk_start)),
                  array("ELT",strtotime($chk_end)+86400-1)
            );
        }

        if (!empty($src)) {
            $map1["alias"] = array("EQ",$src);
        }

        $buildSql = M("orders")->where($map)->alias("o")
                   ->join("left join qz_qc_info i on i.order_id = o.id")
                   ->join("left join safe_order_tel8 tel on tel.orderid = o.id ")
                   ->join("left join qz_orders_source s on s.orderid =  o.id ")
                   ->join("left join qz_order_source s1 on s1.src =  s.source_src")
                   ->join("left join qz_order_csos_new new ON new.order_id = o.id")
                   ->join("left join qz_adminuser u on u.id = new.user_id")
                   ->field("o.id,o.time_real,o.on,o.type_fw,o.on_sub,u.name as chk_customer,o.fen_customer, '0' as state,'' as op_name,substring_index(u.kfmanager,',',1) as kfmanager,u.kfgroup,u.id as uid,o.cs,o.lasttime, '0' as sampling_status,o.xiaoqu,tel.tel8,o.source_type,i.time,o.ip,o.source,s.source_src as src,s1.name as src_name,s1.alias,u.state as ustate,u.uid as uuid ,new.user_name AS chk_name")
                   ->union("select o.id,o.time_real,o.on,o.type_fw,o.on_sub,u.name as chk_customer,o.fen_customer, i.status as state,i.op_name,substring_index(u.kfmanager,',',1) as kfmanager,u.kfgroup,u.id as uid,o.cs,o.lasttime, i.sampling_status,o.xiaoqu,tel.tel8,o.source_type,i.time,o.ip,o.source,s.source_src as src,s1.name as src_name,s1.alias,u.state as ustate,u.uid as uuid,new.user_name AS chk_name  from qz_qc_info i
                        left join qz_orders o on o.id = i.order_id
                        left join safe_order_tel8 tel on tel.orderid = i.order_id
                        left join qz_orders_source s on s.orderid =  i.order_id
                        left join qz_order_source s1 on s1.src =  s.source_src
                        left join qz_order_csos_new new ON new.order_id = o.id
                        left join qz_adminuser u on u.id = new.user_id
                        where op_uid = ".$uid."",true)
                   ->buildSql();

        $buildSql = M("orders")->table($buildSql)->where($map1)->alias("t")->limit($pageIndex.",".$pageCount)->order("state,time_real desc")->buildSql();

        $buildSql = M("orders")->table($buildSql)->alias("t1")
                          ->join("left join qz_qc_telcenter tel on tel.order_id = t1.id")
                          ->field("t1.*,tel.type as tel_type,tel.status as tel_status")
                          ->order("t1.time_real,tel_status")
                          ->buildSql();
        return  M("orders")->table($buildSql)->alias("t2")->group("t2.id")->order("t2.time_real desc")->select();
    }


    public function getRepeatIpListCount($uid,$begin,$end,$id,$type,$cs,$manager,$group,$user,$time_start,$time_end,$status,$sampling_status,$ids,$having ='')
    {
        $map = array(
            "o.time_real" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            "_string" => "(o.on_sub <> 10)",
            'o.ip' => ['neq','223.112.69.58']
        );

        if (count($ids) > 0) {
            $map['o.source'] = array("NOT IN",$ids);
        }

        if (!empty($id)) {
            $map1["_complex"] = array(
                "id" => array("EQ",$id),
                "ip" => array("EQ",$id),
                "_logic" => "OR"
            );
        }

        if (!empty($cs)) {
            $map1["cs"] = array("EQ",$cs);
        }

        if ($status !== "") {
            switch ($status) {
                case '1':
                    $map1["state"] = array("IN",array(1,2));
                    break;
                case '2':
                    $map1["_complex"] = array(
                        "state" => array("EQ",2),
                        "sampling_status" => array("EQ",2)
                    );
                    break;
                case '3':
                    $map1["_complex"] = array(
                        "state" => array("EQ",2),
                        "sampling_status" => array("EQ",1)
                    );
                    break;
                case '4':
                    $map1["state"] = array("EQ",3);
                    break;
                case '0':
                    $map1["state"] = array(array("EQ", 0), array("EXP", 'is null'), 'or');
                    break;
                default:
                    $map1["state"] = array("EQ",$status);
                    break;
            }
        }

        if(!empty($having)){
            if($having == 1){
                $having = 'num > 5';
            }else{
                $having = 'num <= 5';
            }
        }
        $buildSql = M("orders")->where($map)->alias("o")
            ->join("left join qz_qc_info i on i.order_id = o.id")
            ->join("join qz_quyu qu on qu.cid = o.cs")
            ->field("o.id,o.time_real,o.on,o.type_fw,o.on_sub,o.fen_customer, i.status as state,i.op_name,
            o.cs,o.lasttime, i.sampling_status,o.xiaoqu,o.source_type,i.time,o.ip,o.source,qu.cname,UNIX_TIMESTAMP(DATE_SUB(FROM_UNIXTIME(o.time_real, '%Y-%m-%d'),INTERVAL 1 MONTH))as be ,o.time_real as en")
            ->buildSql();

        $buildSql = M("orders")->table($buildSql)->alias("t")
            ->field('t.*,count(t.id)as num')
            ->join("join qz_orders o ON o.ip = t.ip AND o.time_real >= t.be AND o.time_real <= t.en")
            ->group("t.id")
            ->having($having)
            ->buildSql();
        return M("orders")->table($buildSql)->where($map1)->alias("t")->count();
    }

    /**
     * 查看重复ip数据
     * @param $uid 用户ip
     * @param $begin
     * @param $end
     * @param $pageIndex
     * @param $pageCount
     * @param $id
     * @param $type
     * @param $cs
     * @param $manager
     * @param $group
     * @param $user
     * @param $time_start
     * @param $time_end
     * @param $status
     * @param $source
     * @param $chk_start
     * @param $chk_end
     * @param $ids
     * @return mixed
     */
    public function getRepeatIpList($uid,$begin,$end,$pageIndex,$pageCount,$id,$type,$cs,$manager,$group,$user,$time_start,$time_end,$status,$sampling_status,$ids,$having = '')
    {
        $map = array(
            "o.time_real" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            "_string" => "(o.on_sub <> 10)",
            'o.ip' => ['neq','223.112.69.58'],
        );

        if (count($ids) > 0) {
            $map['o.source'] = array("NOT IN",$ids);
        }

        if (!empty($id)) {
            $map1["_complex"] = array(
                "id" => array("EQ",$id),
                "ip" => array("EQ",$id),
                "_logic" => "OR"
            );
        }

        if (!empty($cs)) {
            $map1["cs"] = array("EQ",$cs);
        }

        if ($status !== "") {
            switch ($status) {
                case '1':
                    $map1["state"] = array("IN",array(1,2));
                    break;
                case '2':
                    $map1["_complex"] = array(
                        "state" => array("EQ",2),
                        "sampling_status" => array("EQ",2)
                    );
                    break;
                case '3':
                    $map1["_complex"] = array(
                        "state" => array("EQ",2),
                        "sampling_status" => array("EQ",1)
                    );
                    break;
                case '4':
                    $map1["state"] = array("EQ",3);
                    break;
                case '0':
                    $map1["state"] = array(array("EQ", 0), array("EXP", 'is null'), 'or');
                    break;
                default:
                    $map1["state"] = array("EQ",$status);
                    break;
            }
        }

        if(!empty($having)){
            if($having == 1){
                $having = 'num > 5';
            }else{
                $having = 'num <= 5';
            }
        }
//
        $buildSql = M("orders")->where($map)->alias("o")
            ->join("left join qz_qc_info i on i.order_id = o.id")
            ->join("join qz_quyu qu on qu.cid = o.cs")
            ->field("o.id,o.time_real,o.on,o.type_fw,o.on_sub,o.fen_customer, i.status as state,i.op_name,
            o.cs,o.lasttime, i.sampling_status,o.xiaoqu,o.source_type,i.time,o.ip,o.source,qu.cname,UNIX_TIMESTAMP(DATE_SUB(FROM_UNIXTIME(o.time_real, '%Y-%m-%d'),INTERVAL 1 MONTH))as be ,o.time_real as en")
            ->buildSql();
        $buildSql = M("orders")->table($buildSql)->alias("t")
            ->field('t.*,count(t.id)as num')
            ->join("join qz_orders o ON o.ip = t.ip AND o.time_real >= t.be AND o.time_real <= t.en")
            ->group("t.id")
            ->having($having)
            ->buildSql();
        return M("orders")->table($buildSql)->where($map1)->alias("t")->limit($pageIndex . "," . $pageCount)->order("state,time_real desc")->select();
    }


    /**
     * 获取质检的订单信息
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getQcOrderInfo($id)
    {
        $map = array(
            "a.id"=>array("EQ",$id)
        );
        $buildSql = M("orders")->where($map)->alias("a")
                          ->join("LEFT JOIN qz_quyu as q on q.cid = a.cs")
                          ->join("LEFT JOIN qz_area as b on b.qz_areaid = a.qx")
                          ->join("LEFT JOIN qz_jiage as c on c.id = a.yusuan")
                          ->join("LEFT JOIN qz_huxing as d on d.id  = a.huxing")
                          ->join("LEFT JOIN qz_fengge as f on f.id  = a.fengge")
                          ->join("LEFT JOIN qz_fangshi as g on g.id  = a.fangshi")
                          ->field("a.*,b.qz_area,c.name as ys,d.name as hxname,f.name as fg,g.name as fshi,q.cname")
                          ->buildSql();
        return   M("orders")->table($buildSql)->alias("t")
                            ->join("left join qz_order_csos_new new on new.order_id = t.id")
                            ->join("left join qz_adminuser u1 on u1.id = t.fen_customer")
                            ->join("left join qz_adminuser u2 on u2.id = t.customer")
                            ->field("t.*,u1.id as fen_id,u1.name as fen_name,new.user_id as chk_id, new.user_name as chk_name,u2.name as cus_name")
                            ->find();
    }

    /**
     * 获取已质检列表
     * @param  [type] $begin      [实际发布时间]
     * @param  [type] $end        [实际发布时间]
     * @param  [type] $id         [订单编号]
     * @param  [type] $type       [订单类型]
     * @param  [type] $cs         [城市]
     * @param  [type] $manager    [师长]
     * @param  [type] $group      [客服组]
     * @param  [type] $user       [客服]
     * @param  [type] $time_start [开始时间]
     * @param  [type] $time_end   [开始时间]
     * @param  [type] $status     [质检状态]
     * @param  [type] $qcuser     [质检员]
     * @return [type]             [description]
     */
    public function getSamplingQcListCount($begin,$end,$id,$type,$cs,$manager,$group,$user,$time_start,$time_end,$status,$qcuser,$sampling_start,$sampling_end)
    {
        $map = array(
             "i.status" => array("IN",array(1,2))
        );

        if(!empty($begin) && !empty($end)) {
            $map["o.time_real"] = array(
                    array("EGT",$begin),
                    array("ELT",$end)
            );
        }

        if (!empty($id)) {
            $map["o.id"] = array("EQ",$id);
        }

        if (!empty($cs)) {
            $map["o.cs"] = array("EQ",$cs);
        }
        if (!empty($group)) {
            $map["kfgroup"] = array("EQ",$group);
        }

        if (!empty($user)) {
            $map["uid"] = array("EQ",$user);
        }

        if (!empty($manager)) {
            $map["kfmanager"] = array("EQ",$manager);
        }

        if(!empty($time_start) && !empty($time_end)) {
            $map["o.lasttime"] = array(
                  array("EGT",$time_start),
                  array("ELT",$time_end)
            );
        }

        if (!empty($status)) {
            $map["i.status"] = array("EQ",$status);
        }

        if (!empty($qcuser)) {
            $map["i.op_uid"] = array("EQ",$qcuser);
        }

        if (!empty($type)) {
            switch ($type) {
                case 1:
                    $map["on"] = array("EQ",4);
                    $map["type_fw"] = array("EQ",1);
                    break;
                case 2:
                    $map["on"] = array("EQ",4);
                    $map["type_fw"] = array("EQ",2);
                    break;
                case 3:
                    $map["on"] = array("EQ",0);
                    $map["on_sub"] = array("EQ",9);
                    break;
                case 4:
                    $map["on"] = array("EQ",0);
                    $map["on_sub"] = array("EQ",8);
                    break;
                case 5:
                    $map["on"] = array("EQ",2);
                    break;
                case 6:
                    $map["on"] = array("EQ",5);
                    break;
            }
        }

        if(!empty($sampling_start) && !empty($sampling_end)) {
            $map["i.sampling_time"] = array(
                  array("EGT",strtotime($sampling_start)),
                  array("ELT",strtotime($sampling_end)+86400-1)
            );
        }

        return M("qc_info")->where($map)->alias("i")
                   ->join("join qz_orders o on o.id = i.order_id")
                   ->join("left join qz_adminuser u on u.id = o.chk_customer")
                   ->count();
    }

       /**
     * 获取已质检列表
     * @param  [type] $begin      [实际发布时间]
     * @param  [type] $end        [实际发布时间]
     * @param  [type] $id         [订单编号]
     * @param  [type] $type       [订单类型]
     * @param  [type] $cs         [城市]
     * @param  [type] $manager    [师长]
     * @param  [type] $group      [客服组]
     * @param  [type] $user       [客服]
     * @param  [type] $time_start [开始时间]
     * @param  [type] $time_end   [开始时间]
     * @param  [type] $status     [质检状态]
     * @param  [type] $qcuser     [质检员]
     * @return [type]             [description]
     */
    public function getSamplingQcList($begin,$end,$id,$type,$cs,$manager,$group,$user,$time_start,$time_end,$status,$qcuser,$pageIndex,$pageCount,$sampling_start,$sampling_end)
    {
        $map = array(
             "i.status" => array("IN",array(1,2))
        );

        if(!empty($begin) && !empty($end)) {
            $map["o.time_real"] = array(
                    array("EGT",$begin),
                    array("ELT",$end)
            );
        }

        if (!empty($id)) {
            $map["o.id"] = array("EQ",$id);
        }

        if (!empty($cs)) {
            $map["o.cs"] = array("EQ",$cs);
        }
        if (!empty($group)) {
            $map["kfgroup"] = array("EQ",$group);
        }

        if (!empty($user)) {
            $map["uid"] = array("EQ",$user);
        }

        if (!empty($manager)) {
            $map["kfmanager"] = array("EQ",$manager);
        }

        if(!empty($time_start) && !empty($time_end)) {
            $map["o.lasttime"] = array(
                  array("EGT",$time_start),
                  array("ELT",$time_end)
            );
        }

        if (!empty($status)) {
            $map["i.status"] = array("EQ",$status);
        }

        if (!empty($qcuser)) {
            $map["i.op_uid"] = array("EQ",$qcuser);
        }

        if (!empty($type)) {
            switch ($type) {
                case 1:
                    $map["on"] = array("EQ",4);
                    $map["type_fw"] = array("EQ",1);
                    break;
                case 2:
                    $map["on"] = array("EQ",4);
                    $map["type_fw"] = array("EQ",2);
                    break;
                case 3:
                    $map["on"] = array("EQ",0);
                    $map["on_sub"] = array("EQ",9);
                    break;
                case 4:
                    $map["on"] = array("EQ",0);
                    $map["on_sub"] = array("EQ",8);
                    break;
                case 5:
                    $map["on"] = array("EQ",2);
                    break;
                case 6:
                    $map["on"] = array("EQ",5);
                    break;
            }
        }

        if(!empty($sampling_start) && !empty($sampling_end)) {
            $map["i.sampling_time"] = array(
                  array("EGT",strtotime($sampling_start)),
                  array("ELT",strtotime($sampling_end)+86400-1)
            );
        }

        return M("qc_info")->where($map)->alias("i")
                   ->join("join qz_orders o on o.id = i.order_id")
                   ->join("left join qz_adminuser u on u.id = o.chk_customer")
                   ->join("left join qz_qc_telcenter tel on tel.order_id = i.order_id")
                   ->field("o.id,o.time_real,o.on,o.type_fw,o.on_sub,u.name as chk_customer,o.fen_customer, i.status as state,i.op_name,substring_index(u.kfmanager,',',1) as kfmanager,u.kfgroup,u.id as uid,o.cs,o.lasttime, i.sampling_status,tel.type as tel_type,tel.status as tel_status,i.sampling_time")
                   ->order("i.status,o.time_real desc")
                   ->limit($pageIndex.",".$pageCount)
                   ->select();
    }

    /**
     * 按客服统计对接信息
     * @param  [int] $kf    [客服ID]
     * @param  [string] $begin [开始时间]
     * @param  [string] $end   [结束时间]
     * @return [type]        [description]
     */
    public function getCustomerDockingStat($kf, $begin, $end)
    {
        $where = "";

        if (!empty($kf)) {
            $where .=" and a.id =".$kf;
        }

        //客服/质检
        $uid = '(97,31,66,23,99)';
        $sql = "select
                t.*,count(t1.id) as no_fen_count
                from (
                    select a.id,a.name,t.*,concat(if( ac.cid IS NOT NULL, ac.cid, '' ),IF( a.cs IS NOT NULL, ',' + a.cs, ''),IF( a.css IS NOT NULL, ',' + a.css, '' )) AS cs,count(IF(t.on = 4 and t.type_fw in (1,2) and t.fen_customer <> 0,1,null)) as count ,
                    count(IF(t.on = 4 and t.type_fw = 1 and t.fen_customer <> 0,1,null)) as fen_count   ,
                    count(IF(t.on = 4 and t.type_fw = 2 and t.fen_customer <> 0,1,null)) as zen_count
                    from qz_adminuser a
                    left join (
                        select a.time,o.on,o.type_fw,o.fen_customer,a.op_uid from qz_order_docking  a FORCE index(idx_time)
                        join qz_orders o on a.order_id = o.id
                        where a.time >= $begin and a.time <= $end
                    ) t on t.op_uid = a.id
                    left join (SELECT admin_id,GROUP_CONCAT(cid) cid from qz_admin_audit_citys GROUP BY admin_id) ac on ac.admin_id = a.id
                    where a.uid in $uid and a.stat = 1 $where
                    group by id
                ) t
                left join (
                    select o.id, o.cs from qz_order_csos_new new
                    join qz_orders o on o.id = new.order_id
                    where new.lasttime >= $begin and new.lasttime <= $end and o.on = 4 and o.type_fw in (1,2)
                    and o.fen_customer = 0
                ) t1 on  find_in_set(t1.cs,t.cs)
                group by t.id";
        return $this->db(1,"DB_CONFIG1")->query($sql);
    }

    /**
     * 按客服统计对接信息
     * @param  [string] $city    [城市ID]
     * @param  [string] $begin [开始时间]
     * @param  [string] $end   [结束时间]
     * @return [type]        [description]
     */
    public function getCustomerDockingStatByCity($city, $begin, $end)
    {
        $where = "";

        if (!empty($city)) {
            $where .=" and o.cs =".$city;
        }

        $sql = "select
                q.cname,t.*,count(t1.id) as no_fen_count
                from (
                        select o.cs,
                        count(IF(o.on = 4 and o.type_fw in (1,2) and o.fen_customer <> 0,1,null)) as count ,
                        count(IF(o.on = 4 and o.type_fw = 1 and o.fen_customer <> 0,1,null)) as fen_count   ,
                        count(IF(o.on = 4 and o.type_fw = 2 and o.fen_customer <> 0,1,null)) as zen_count from qz_order_docking  a FORCE index(idx_time)
                        join qz_orders o on a.order_id = o.id
                        where a.time >= $begin and a.time <= $end
                        and o.on = 4 and o.type_fw in (1,2) $where
                        group by o.cs
                ) t
                left join (
                        select o.id, o.cs from qz_order_csos_new new
                        join qz_orders o on o.id = new.order_id
                        where new.lasttime >= $begin and new.lasttime <= $end and o.on = 4 and o.type_fw in (1,2)
                        and o.fen_customer = 0
                ) t1 on t1.cs = t.cs
                join qz_quyu q on q.cid = t.cs
                group by t.cs";
        return $this->db(1,"DB_CONFIG1")->query($sql);
    }

    /**
     * 获取已对接订单信息
     * @param  [type] $kf       [客服ID]
     * @param  [type] $id       [订单ID]
     * @param  [type] $cs       [管辖城市]
     * @param  [type] $other_cs [城市ID]
     * @param  [type] $status   [订单状态]
     * @param  [type] $begin    [开始时间]
     * @param  [type] $end      [结束时间]
     * @param  [type] $time_diff [对接时间差]
     * @return array
     */
    public function getOrderDockingListCount($kf, $id, $other_cs, $status, $begin, $end,$time_diff,$isactivity,$ids,$source,$qx)
    {
        $map = array(
            "o.on" => array("EQ",4),
            "o.type_fw" => array("in",array(1,2,3,4))
        );

        if (!empty($other_cs)) {
            $map["o.cs"] = array("EQ",$other_cs);
        }

        if (!empty($qx)) {
            $map["o.qx"] = array("EQ",$qx);
        }

        if (!empty($status)) {
            $map["o.type_fw"] = array("EQ",$status);
        }

        if (!empty($id)) {
            $map["_complex"] =array(
                "o.id" => array("EQ",$id),
                "o.xiaoqu" => array("LIKE","%$id%"),
                "_logic" => "OR"
            );
        }

        if (!empty($kf)) {
            $map["a.op_uid"] = array("EQ",$kf);
        }

        if (!empty($begin) && !empty($end)) {
            $map["a.time"] = array(
                array("EGT",strtotime($begin)),
                array("ELT",strtotime($end))
            );
        }

        if (!empty($isactivity)) {
            if (count($ids) > 0) {
                if ($isactivity == 1) {
                    $map['o.source'] = array("IN",$ids);
                } else {
                    $map['o.source'] = array("NOT IN",$ids);
                }
            }
        }

        if (!empty($source)) {
            $map["o.source_in"] = array("EQ",$source);
        }


        $buildSql = M("order_docking")->where($map)->alias("a FORCE INDEX(idx_time)")
                                 ->join("join qz_orders o on a.order_id = o.id")
                                 ->join("join qz_order_csos_new new on new.order_id = o.id")
                                 ->join("left JOIN qz_order_rob_pool orp ON orp.order_id = o.id")
                                 ->field("TIMESTAMPDIFF(SECOND,FROM_UNIXTIME(new.lasttime),FROM_UNIXTIME(a.time)) as time_diff,orp.id rob_id,orp.is_rob")
                                 ->buildSql();
        if (!empty($time_diff)) {
            switch ($time_diff) {
                case 1:
                    $map1["t.time_diff"] = array("ELT",15*60);
                    break;
                case 2:
                    $map1["t.time_diff"] = array("GT",15*60);
                    break;
            }
        }
        //过滤在抢单池中的订单
        $buildSql = M("order_docking")->table($buildSql)->alias('r')->having('r.rob_id IS NULL OR r.is_rob = 2')->buildSql();
        return  M("order_docking")->table($buildSql)->where($map1)->alias("t")
                                    ->count();
    }

     /**
     * 获取已对接订单信息
     * @param  [type] $kf       [客服ID]
     * @param  [type] $id       [订单ID]
     * @param  [type] $other_cs [城市ID]
     * @param  [type] $status   [订单状态]
     * @param  [type] $begin    [开始时间]
     * @param  [type] $end      [结束时间]
     * @param  [type] $time_diff [对接时间差]
     * @return array
     */
    public function getOrderDockingList($kf, $id, $other_cs, $status, $begin, $end,$time_diff,$pageIndex, $pageCount,$isactivity,$ids,$source,$qx)
    {
        $map = array(
            "o.on" => array("EQ",4),
            "o.type_fw" => array("NEQ",0)
        );

        if (!empty($other_cs)) {
            $map["o.cs"] = array("EQ",$other_cs);
        }

        if (!empty($qx)) {
            $map["o.qx"] = array("EQ",$qx);
        }

        if (!empty($status)) {
            $map["o.type_fw"] = array("EQ",$status);
        }

        if (!empty($id)) {
            $map["_complex"] =array(
                "o.id" => array("EQ",$id),
                "o.xiaoqu" => array("LIKE","%$id%"),
                "_logic" => "OR"
            );
        }

        if (!empty($begin) && !empty($end)) {
            $map["a.time"] = array(
                array("EGT",strtotime($begin)),
                array("ELT",strtotime($end))
            );
        }

        if (!empty($kf)) {
            $map["a.op_uid"] = array("EQ",$kf);
        }

        if (!empty($isactivity)) {
            if (count($ids) > 0) {
                if ($isactivity == 1) {
                    $map['o.source'] = array("IN",$ids);
                } else {
                    $map['o.source'] = array("NOT IN",$ids);
                }
            }
        }

        if ($source != "") {
            $map["o.source_in"] = array("EQ",$source);
        }

        $buildSql = M("order_docking")->where($map)->alias("a FORCE INDEX(idx_time)")
                                 ->join("join qz_orders o on a.order_id = o.id")
                                 ->join("join qz_order_csos_new new on new.order_id = o.id")
                                 ->join("join qz_quyu q on q.cid = o.cs")
                                 ->join("join qz_area area on area.qz_areaid = o.qx")
                                 ->join("left join qz_order_allot_num an on an.order_id = o.id")
                                 ->join("left JOIN qz_order_rob_pool orp ON orp.order_id = o.id")
                                 ->field("a.op_uname,o.source_in, o.id,o.time_real,o.remarks,q.cname,area.qz_area,o.wzd,o.mianji,o.tel,o.on,o.type_fw,a.time,TIMESTAMPDIFF(SECOND,FROM_UNIXTIME(new.lasttime),FROM_UNIXTIME(a.time)) as time_diff,o.source,if(an.allot_num IS NOT NULL,an.allot_num,4) as allot_num,orp.id rob_id,orp.is_rob,orp.rob_num")
                                 ->order("a.time desc")
                                 ->buildSql();
        $order = "time desc";
        if (!empty($time_diff)) {
            switch ($time_diff) {
                case 1:
                    $map1["t.time_diff"] = array("ELT",15*60);
                    $order = "time_diff desc";
                    break;
                case 2:
                    $map1["t.time_diff"] = array("GT",15*60);
                    $order = "time_diff desc";
                    break;
            }
        }
        //过滤在抢单池中的订单
        $buildSql = M("order_docking")->table($buildSql)->alias('r')->having('r.rob_id IS NULL OR r.is_rob = 2')->buildSql();

        $buildSql = M("order_docking")->table($buildSql)->where($map1)->alias("t")
                                 ->order($order)
                                 ->limit($pageIndex.",".$pageCount)
                                 ->buildSql();
        return M()->table($buildSql)->alias('t1')
            ->field('t1.*,count(i.id) already_allot_num')
            ->join('left join qz_order_info i on i.order = t1.id')
            ->group('t1.id')
            ->order($order)
            ->select();
    }

     /**
     * 获取发单数据
     * @param  [date] $begin [开始时间]
     * @param  [date] $end   [结束时间]
     * @return array
     */
    public function getSendOrderData($begin,$end)
    {
        $map = array(
            "time_real" => array(
                array("EGT",$begin),
                array("ELT",$end)
            )
        );

        $buildSql =  M('orders')->where($map)->field("FROM_UNIXTIME(time_real,'%H:%i') as time_real,FROM_UNIXTIME(time_real,'%Y-%m-%d') as date")
                                ->buildSql();
        return M('orders')->table($buildSql)->alias("t")
                          ->field("date, count(if(time_real >= '00:00' and time_real < '01:00',1,null)) as time1,
                                    count(if(time_real >= '01:00' and time_real < '02:00',1,null)) as time2,
                                    count(if(time_real >= '02:00' and time_real < '03:00',1,null)) as time3,
                                    count(if(time_real >= '03:00' and time_real < '04:00',1,null)) as time4,
                                    count(if(time_real >= '04:00' and time_real < '05:00',1,null)) as time5,
                                    count(if(time_real >= '05:00' and time_real < '06:00',1,null)) as time6,
                                    count(if(time_real >= '06:00' and time_real < '07:00',1,null)) as time7,
                                    count(if(time_real >= '07:00' and time_real < '08:00',1,null)) as time8,
                                    count(if(time_real >= '08:00' and time_real < '09:00',1,null)) as time9,
                                    count(if(time_real >= '09:00' and time_real < '10:00',1,null)) as time10,
                                    count(if(time_real >= '10:00' and time_real < '11:00',1,null)) as time11,
                                    count(if(time_real >= '11:00' and time_real < '12:00',1,null)) as time12,
                                    count(if(time_real >= '12:00' and time_real < '13:00',1,null)) as time13,
                                    count(if(time_real >= '13:00' and time_real < '14:00',1,null)) as time14,
                                    count(if(time_real >= '14:00' and time_real < '15:00',1,null)) as time15,
                                    count(if(time_real >= '15:00' and time_real < '16:00',1,null)) as time16,
                                    count(if(time_real >= '16:00' and time_real < '17:00',1,null)) as time17,
                                    count(if(time_real >= '17:00' and time_real < '18:00',1,null)) as time18,
                                    count(if(time_real >= '18:00' and time_real < '19:00',1,null)) as time19,
                                    count(if(time_real >= '19:00' and time_real < '20:00',1,null)) as time20,
                                    count(if(time_real >= '20:00' and time_real < '21:00',1,null)) as time21,
                                    count(if(time_real >= '21:00' and time_real < '22:00',1,null)) as time22,
                                    count(if(time_real >= '22:00' and time_real < '23:00',1,null)) as time23,
                                    count(if(time_real >= '23:00' and time_real <= '23:59',1,null)) as time24")
                             ->group("date")->order("date")->select();
    }


    /**
     * 根据通话记录获取分单赠单列表
     * 开始时间和结束时间的时间格式为Y-m-d格式，
     * 举例：开始2017-06-01 结束2017-06-01 查询的是 2017-06-01 00:00:00 到 2017-06-01 23:59:59 中的数据
     * @param  string $start_time 开始时间
     * @param  string $end_time   结束时间
     * @return array              列表数组
     */
    public function getOrderStatDataAnalysisDataList($start_time = '', $end_time = '')
    {
        $map = array(
            "a.lasttime" => array(
                array("EGT",$start_time),
                array("ELT",$end_time)
            ),
            "a.order_on" => array("EQ",4)
        );
        $buildSql =  M("order_csos_new")->where($map)->alias("a")
                           ->join("join qz_orders o on o.id = a.order_id and o.type_fw = 1")
                           ->join("join qz_adminuser u on u.id = a.user_id and u.uid in (2,31)")
                           ->field("a.order_id as orderid,from_unixtime(a.lasttime,'%H:%i') as time_add,u.kfgroup,o.type_fw,a.order_on as `on`")
                           ->buildSql();
        return M("order_csos_new")->table($buildSql)->alias("t")
                                 ->field("t.*,case
                                        when time_add >= '08:30' and time_add < '09:30' then 0
                                        when time_add >= '09:30' and time_add < '10:30' then 1
                                        when time_add >= '10:30' and time_add < '11:30' then 2
                                        when time_add >= '11:30' and time_add < '12:30' then 3
                                        when time_add >= '12:30' and time_add < '13:30' then 4
                                        when time_add >= '13:30' and time_add < '14:30' then 5
                                        when time_add >= '14:30' and time_add < '15:30' then 6
                                        when time_add >= '15:30' and time_add < '16:30' then 7
                                        when time_add >= '16:30' and time_add < '17:30' then 8
                                        when time_add >= '17:30' and time_add < '18:30' then 9
                                        end as mark
                                        ")->select();
    }

    /**
     * 获取订单推送时间
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getOrderPushInfo($id)
    {
        $map = array(
            "o.id" => array("EQ",$id)
        );

        return M("Orders")->where($map)->alias("o")
                          ->join("left join qz_order_docking b on b.order_id = o.id")
                          ->join("left join qz_log_wx_ordersend c on c.orderid = o.id")
                          ->field("TIMESTAMPDIFF(SECOND ,FROM_UNIXTIME(b.time), FROM_UNIXTIME(c.time_add)) as diff_date")
                          ->find();
    }

    /**
     * 赠单原因分析
     * @param  date $begin [开始日期]
     * @param  date $end   [结束日期]
     * @return array
     */
    public function getKfZenStat($begin,$end)
    {
        $sql = "select
                t.*,count(t.mark) as `count`
                from (
                            select u.kfgroup,
                            o.remarks,
                            case o.remarks
                                when '距离远' then 1
                                when '预算低' then 2
                                when '面积小' then 3
                                when '交房时间长' then 4
                                when '开工时间长' then 5
                                when '城市未开' then 6
                                when '需要垫资' then 7
                                when '不能量房' then 8
                                when '改动项目少' then 9
                                when '与装修相关' then 10
                                when '只要设计' then 11
                                when '意向不强' then 12
                            end as mark
                            from qz_orders o
                            join qz_order_csos_new new on new.order_id = o.id
                            join qz_adminuser u on u.id = new.user_id
                            where o.on = 4 and o.type_fw = 2 and time_real >= '$begin' and time_real < '$end' and u.kfgroup <> 0
                ) t
                where t.mark is not null 
                group by t.kfgroup,t.mark
                order by kfgroup, mark ";
        return $this->db(1,"DB_CONFIG1")->query($sql);
    }

    /**
     * 发单量数据分析
     * @param  string $begin [开始时间]
     * @param  string $end   [结束时间]
     * @return array
     */
    public function getOrderTrend($begin,$end)
    {
        $map = array(
        "time_real" => array(
                array("EGT",$begin),
                array("ELT",$end)
        )
        );

        $buildSql =  M("Orders")->where($map)
                                ->field('id,`on`,FROM_UNIXTIME(time_real,"%Y-%m-%d") as date,FROM_UNIXTIME(time_real,"%H:%i") as time_real')
                                ->buildSql();
        return  M("Orders")->table($buildSql)->alias("t")->field("t.date,
                        count(if(time_real >= '00:00' and time_real < '01:00',1,null)) as time1,
                        count(if(time_real >= '01:00' and time_real < '02:00',1,null)) as time2,
                        count(if(time_real >= '02:00' and time_real < '03:00',1,null)) as time3,
                        count(if(time_real >= '03:00' and time_real < '04:00',1,null)) as time4,
                        count(if(time_real >= '04:00' and time_real < '05:00',1,null)) as time5,
                        count(if(time_real >= '05:00' and time_real < '06:00',1,null)) as time6,
                        count(if(time_real >= '06:00' and time_real < '07:00',1,null)) as time7,
                        count(if(time_real >= '07:00' and time_real < '08:00',1,null)) as time8,
                        count(if(time_real >= '08:00' and time_real < '09:00',1,null)) as time9,
                        count(if(time_real >= '09:00' and time_real < '10:00',1,null)) as time10,
                        count(if(time_real >= '10:00' and time_real < '11:00',1,null)) as time11,
                        count(if(time_real >= '11:00' and time_real < '12:00',1,null)) as time12,
                        count(if(time_real >= '12:00' and time_real < '13:00',1,null)) as time13,
                        count(if(time_real >= '13:00' and time_real < '14:00',1,null)) as time14,
                        count(if(time_real >= '14:00' and time_real < '15:00',1,null)) as time15,
                        count(if(time_real >= '15:00' and time_real < '16:00',1,null)) as time16,
                        count(if(time_real >= '16:00' and time_real < '17:00',1,null)) as time17,
                        count(if(time_real >= '17:00' and time_real < '18:00',1,null)) as time18,
                        count(if(time_real >= '18:00' and time_real < '19:00',1,null)) as time19,
                        count(if(time_real >= '19:00' and time_real < '20:00',1,null)) as time20,
                        count(if(time_real >= '20:00' and time_real < '21:00',1,null)) as time21,
                        count(if(time_real >= '21:00' and time_real < '22:00',1,null)) as time22,
                        count(if(time_real >= '22:00' and time_real < '23:00',1,null)) as time23,
                        count(if(time_real >= '23:00' and time_real <= '23:59',1,null)) as time24,
                        count(if(time_real >= '00:00' and time_real < '01:00' and `on` = 4 ,1,null)) as time_fen1,
                        count(if(time_real >= '01:00' and time_real < '02:00' and `on` = 4 ,1,null)) as time_fen2,
                        count(if(time_real >= '02:00' and time_real < '03:00' and `on` = 4 ,1,null)) as time_fen3,
                        count(if(time_real >= '03:00' and time_real < '04:00' and `on` = 4 ,1,null)) as time_fen4,
                        count(if(time_real >= '04:00' and time_real < '05:00' and `on` = 4 ,1,null)) as time_fen5,
                        count(if(time_real >= '05:00' and time_real < '06:00' and `on` = 4 ,1,null)) as time_fen6,
                        count(if(time_real >= '06:00' and time_real < '07:00' and `on` = 4 ,1,null)) as time_fen7,
                        count(if(time_real >= '07:00' and time_real < '08:00' and `on` = 4 ,1,null)) as time_fen8,
                        count(if(time_real >= '08:00' and time_real < '09:00' and `on` = 4 ,1,null)) as time_fen9,
                        count(if(time_real >= '09:00' and time_real < '10:00' and `on` = 4 ,1,null)) as time_fen10,
                        count(if(time_real >= '10:00' and time_real < '11:00' and `on` = 4 ,1,null)) as time_fen11,
                        count(if(time_real >= '11:00' and time_real < '12:00' and `on` = 4 ,1,null)) as time_fen12,
                        count(if(time_real >= '12:00' and time_real < '13:00' and `on` = 4 ,1,null)) as time_fen13,
                        count(if(time_real >= '13:00' and time_real < '14:00' and `on` = 4 ,1,null)) as time_fen14,
                        count(if(time_real >= '14:00' and time_real < '15:00' and `on` = 4 ,1,null)) as time_fen15,
                        count(if(time_real >= '15:00' and time_real < '16:00' and `on` = 4 ,1,null)) as time_fen16,
                        count(if(time_real >= '16:00' and time_real < '17:00' and `on` = 4 ,1,null)) as time_fen17,
                        count(if(time_real >= '17:00' and time_real < '18:00' and `on` = 4 ,1,null)) as time_fen18,
                        count(if(time_real >= '18:00' and time_real < '19:00' and `on` = 4 ,1,null)) as time_fen19,
                        count(if(time_real >= '19:00' and time_real < '20:00' and `on` = 4 ,1,null)) as time_fen20,
                        count(if(time_real >= '20:00' and time_real < '21:00' and `on` = 4 ,1,null)) as time_fen21,
                        count(if(time_real >= '21:00' and time_real < '22:00' and `on` = 4 ,1,null)) as time_fen22,
                        count(if(time_real >= '22:00' and time_real < '23:00' and `on` = 4 ,1,null)) as time_fen23,
                        count(if(time_real >= '23:00' and time_real <= '23:59' and `on` = 4 ,1,null)) as time_fen24")
                                             ->group("t.date")->select();
    }

    /**
     * 客服呼叫量统计
     * @param  [type] $id    [客服ID]
     * @param  [type] $group [客服组]
     * @param  [type] $begin [开始时间]
     * @param  [type] $end   [结束时间]
     * @return [type]        [description]
     */
    public function getTelStat($id, $group, $begin, $end, $manager, $userids)
    {
        $where = "";
        if (!empty($id)) {
            $where .= " and a.id = ".$id;
        }

        if (!empty($group)) {
            $where .= " and a.kfGroup = ".$group;
        }

        if (!empty($manager)) {
            $where .= " and a.kfmanager = ".$manager;
        }

        if (!empty($userids)) {
            $userids = implode(",", $userids);
            $where .= " and a.id in($userids)";
        }

        $sql = 'select
                t1.id,t1.name,
                count(t1.id) as count,
                count(if(t1.type = 1,1,null)) as un_tel_count,
                count(if(t1.type = 2,1,null)) as tel_count,
                sum(time_diff) as sum_time
                from (
                    select
                    t.id,t.name,t.callSid,
                    case
                        when orderid <> "" and t.byetype in (3,4)  then 1
                        when orderid <> "" and t.byetype not in (3,4) then 2
                        when orderid = "" and starttime = endtime  then 1
                        when orderid = "" and starttime <> endtime  then 2
                    end as type,
                    TIMESTAMPDIFF(SECOND,t.starttime,t.endtime) as time_diff
                    from (
                        select a.id,a.name,tel.action, tel.starttime,tel.endtime,b.callSid,tel.orderid,tel.byetype from qz_adminuser a
                        left join qz_log_telcenter_ordercall b FORCE INDex(idx_time_add) on a.id = b.admin_id
                        join qz_log_telcenter tel on tel.callSid = b.callSid and tel.action = "Hangup"
                        where a.uid = 2 and a.stat = 1 and b.time_add >= "'.$begin.'" and b.time_add < "'.$end.'" '.$where.'
                        ORDER BY tel.callSid
                    ) as t
                ) t1 group by t1.id';

        return $this->db(1,"DB_CONFIG1")->query($sql);
    }

    /**
     * 获取分单总量
     * @param  [type] $begin   [开始时间]
     * @param  [type] $end     [结束时间]
     * @param  [type] $type_fw [分单类型]
     * @return [type]          [description]
     */
    public function getOtherOrderTrend($begin,$end,$type_fw = 1)
    {
        $map = array(
            "time_real" => array(
                array("EGT",$begin),
                array("ELT",$end)
            )
        );

        if (!empty($type_fw)) {
            $map["on"] = array("eq",4);
            $map["type_fw"] = array("eq",$type_fw);
        }

        $buildSql =  M("Orders")->where($map)
                                ->field('id,`on`,FROM_UNIXTIME(time_real,"%Y-%m-%d") as date,FROM_UNIXTIME(time_real,"%H:%i") as time_real')
                                ->buildSql();
        return  M("Orders")->table($buildSql)->alias("t")->field("t.date,
                        count(if(time_real >= '00:00' and time_real < '01:00',1,null)) as time1,
                        count(if(time_real >= '01:00' and time_real < '02:00',1,null)) as time2,
                        count(if(time_real >= '02:00' and time_real < '03:00',1,null)) as time3,
                        count(if(time_real >= '03:00' and time_real < '04:00',1,null)) as time4,
                        count(if(time_real >= '04:00' and time_real < '05:00',1,null)) as time5,
                        count(if(time_real >= '05:00' and time_real < '06:00',1,null)) as time6,
                        count(if(time_real >= '06:00' and time_real < '07:00',1,null)) as time7,
                        count(if(time_real >= '07:00' and time_real < '08:00',1,null)) as time8,
                        count(if(time_real >= '08:00' and time_real < '09:00',1,null)) as time9,
                        count(if(time_real >= '09:00' and time_real < '10:00',1,null)) as time10,
                        count(if(time_real >= '10:00' and time_real < '11:00',1,null)) as time11,
                        count(if(time_real >= '11:00' and time_real < '12:00',1,null)) as time12,
                        count(if(time_real >= '12:00' and time_real < '13:00',1,null)) as time13,
                        count(if(time_real >= '13:00' and time_real < '14:00',1,null)) as time14,
                        count(if(time_real >= '14:00' and time_real < '15:00',1,null)) as time15,
                        count(if(time_real >= '15:00' and time_real < '16:00',1,null)) as time16,
                        count(if(time_real >= '16:00' and time_real < '17:00',1,null)) as time17,
                        count(if(time_real >= '17:00' and time_real < '18:00',1,null)) as time18,
                        count(if(time_real >= '18:00' and time_real < '19:00',1,null)) as time19,
                        count(if(time_real >= '19:00' and time_real < '20:00',1,null)) as time20,
                        count(if(time_real >= '20:00' and time_real < '21:00',1,null)) as time21,
                        count(if(time_real >= '21:00' and time_real < '22:00',1,null)) as time22,
                        count(if(time_real >= '22:00' and time_real < '23:00',1,null)) as time23,
                        count(if(time_real >= '23:00' and time_real <= '23:59',1,null)) as time24")
                                             ->group("t.date")->select();

    }


    /**
     * 撤回单统计
     * @param  [type] $begin [开始时间]
     * @param  [type] $end   [结束时间]
     * @return [type]        [description]
     */
    public function getOrderRevokeStat($begin,$end)
    {
        $map = array(
            "a.time" => array(
                array("EGT",$begin),
                array("ELT",$end)
            )
        );
        //->group("a.order_id") count(if(new_on = 99,1,null)) as count
        $buildSql = M("order_docking")->where($map)->alias("a FORCE index(idx_time)")
                                      ->join("left join qz_log_order_csos b on a.order_id = b.orderid ")
                                      ->field("a.order_id,if(new_on = 99 and old_on = 4 and old_type_fw = 1,1,0)    as before_fen_mark,
                                            if(new_on = 4 and old_on = 4 and old_type_fw = 2,1,0) as after_zen_mark,
                                            if(new_on = 99 and old_on = 4 and old_type_fw = 2,1,0) as before_zen_mark,
                                            if(new_on = 4 and old_on = 4 and old_type_fw = 1,1,0) as after_fen_mark,new_on")
                                      ->buildSql();
        $buildSql = M("order_docking")->table($buildSql)->alias("t1")
                                        ->field("t1.order_id, count(if(new_on = 99,1,null)) as count,max(before_fen_mark) as before_fen_mark,max(after_zen_mark) as after_zen_mark,max(before_zen_mark) as before_zen_mark,max(after_fen_mark) as after_fen_mark")
                                        ->group("t1.order_id")
                                        ->buildSql();

        return  M("order_docking")->table($buildSql)->alias("t")
                                  ->join("join qz_orders o on o.id = t.order_id")
                                  ->join("join qz_order_csos_new new on new.order_id = o.id")
                                  ->join("join qz_adminuser u on u.id = new.user_id")
                                  ->join('join qz_adminuser u1 on u1.id = substring_index(u.kfmanager,",",1)')
                                  ->field("t.*,o.on,o.type_fw,u.id,u.`name`,u.kfgroup,u1.name as manager,u1.id as kfmanager,if(t.count > 0,1,0) mark, count as push_count")
                                  ->order("u.kfGroup,u.id")
                                  ->select();
    }

    /**
     * 获取电话号码信息
     * @param  [type] $orders [description]
     * @return [type]         [description]
     */
    public function getTelList($tels)
    {
        $map = array(
            "a.tel8" => array("IN",$tels)
        );

        return  M("order_tel8","safe_")->where($map)->alias("a force index(idx_tel)")
                            ->field("a.tel8,count(a.tel8) as count")
                            ->group("a.tel8")->select();
    }

    /**
     * 根据电话号码获取订单信息
     * @param  [type] $tel [description]
     * @return [type]      [description]
     */
    public function getOrderListWithTelById($id,$begin,$end)
    {
        $map = array(
            "o.id" => array("EQ",$id)
        );
        $buildSql = M("orders")->where($map)->alias("o")
                   ->join("join safe_order_tel8 t on t.orderid = o.id")
                   ->join("join safe_order_tel8 t1 on t1.tel8 = t.tel8")
                   ->field("t1.orderid")
                   ->buildSql();
        return M("orders")->table($buildSql)->alias("t")
                          ->join("join qz_orders o on o.id = t.orderid and o.time_real >= $begin and o.time_real < $end")
                          ->join("join qz_quyu q on q.cid = o.cs")
                          ->join("join qz_area area on area.qz_areaid = o.qx")
                          ->field("o.id,FROM_UNIXTIME(o.time_real,'%Y-%m-%d') as time_real,o.name,o.tel,q.cname,area.qz_area,o.on,o.type_fw")
                          ->select();
    }

        /**
     * 根据电话号码获取订单信息
     * @param  [type] $ip [description]
     * @return [type]      [description]
     */
    public function getOrderListByIp($ip,$begin,$end)
    {
        $map = array(
            "o.ip" => array("EQ",$ip)
        );
        return M("orders")->where($map)->alias("o")
                          ->join("join qz_quyu q on q.cid = o.cs")
                          ->join("join qz_area area on area.qz_areaid = o.qx")
                          ->field("o.id,FROM_UNIXTIME(o.time_real,'%Y-%m-%d') as time_real,o.name,o.tel,q.cname,area.qz_area,o.on,o.type_fw,o.ip")
                          ->select();
    }

    /**
     * 获取当前时段以发单的数量
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getNowOrderCount($begin,$end,$order_on)
    {
        $map = array(
            "time_real" => array(
                array("EGT",$begin),
                array("LT",$end)
            )
        );

        if (!empty($order_on)) {
            $map["on"] = array("EQ",0);
            $map["on_sub"] = array("EQ",10);
        }

        return M("orders")->where($map)->count();
    }

    /**
     * 获取分时段客服订单
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getOrderByTime($begin,$end)
    {
        $map = array(
            "time_real" => array(
                array("EGT",$begin),
                array("ELT",$end)
            )
        );

        $buildSql = M("Orders")->where($map)->field("TIMESTAMPDIFF(DAY,'".date("Y-m-d",$begin)."','".date("Y-m-d",$end)."') day_diff, FROM_UNIXTIME(time_real,'%H') as day_time,count(id) as count ")->group("day_time")->order("day_time")->buildSql();
        return M("Orders")->table($buildSql)->alias("t")->field("t.day_time, t.count, round(t.count/t.day_diff) as avg_order")->select();

    }

    /**
     * 获取城市异常订单列表
     * @param  [type] $begin [description]
     * @param  [type] $end   [description]
     * @return [type]        [description]
     */
    public function getCityOrderExceptionList($begin,$end)
    {
        $map = array(
            "time_real" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            "cs" => array("NEQ","000001")
        );

        $buildSql = M("orders")->where($map)->field("id,cs,from_unixtime(time_real,'%Y-%m-%d') as date")->buildSql();

        return M("orders")->table($buildSql)->alias("t")
            ->join("join qz_quyu q on q.cid = t.cs")
            ->field("cs,count(t.id) as count,date,q.cname")
            ->group("cs,date")
            ->order("cs,date")
            ->select();
    }
    /**
     * 获取手机号归属地与发单地不符记录
     * @param  [string] $begin [description]
     * @param  [string] $end   [description]
     * @return [string] $mobile[description]
     */
    public function getMobileLocaltionExceptionList($begin, $end, $mobile)
    {
        $map['time_real'] = [ ["EGT", $begin], ["ELT", $end] ];
        $map['cs'] = ['NEQ','000001'];
        if (!empty($mobile)) {
            $map['b.tel8'] = $mobile;
        }
        $buildSql = M("orders")->where($map)->alias("o")
            ->join("join safe_order_tel8 b on b.orderid = o.id")
            ->field("o.id,o.cs,FROM_UNIXTIME(o.time_real,'%Y-%m-%d') as date,b.tel8,o.tel")
            ->buildSql();
        $result = M("orders")->table($buildSql)->alias("t")
            ->join("join qz_quyu q on q.cid = t.cs")
            ->field("cs,t.id,tel8,q.cname,tel")
            ->order("tel8")
            ->select();
        return $result;
    }

    /**
     * 获取渠道订单异常详细记录
     * @param  [string] $begin [description]
     * @param  [string] $end   [description]
     * @return [string] $mobile[description]
     */
    public function getChannelOrderDetailedList($begin, $end, $chanel)
    {
        $map['y.time_real'] = [ ["EGT", $begin], ["ELT", $end] ];
        $map2['cs'] = ['NEQ','000001'];
        if (!empty($chanel)) {
            $map2['s.alias'] = ['like', "%$chanel%"];
        }

        $buildSql = M("orders")->alias("y")->where($map)
            ->field("y.id,y.source")
            ->buildSql();
        $buildSql = M("orders")->table($buildSql)->alias("n")
            ->join("JOIN qz_order_source s ON s.type = 1 AND s.id = n.source")
            ->join("join qz_orders t ON t.id = n.id ")
            ->where($map2)
            ->field('t.id,t.cs,t.tel,t.source,s.src,s.name as channel')
            ->buildSql();

        return M("orders")->table($buildSql)->alias("n")
            ->join("JOIN safe_order_tel8 b ON b.orderid = n.id")
            ->join("JOIN qz_quyu q on q.cid = n.cs")
            ->field("n.id,n.cs,n.tel,n.source,n.src,n.channel as channel,b.tel8,q.cname")
            ->select();
    }


    /**
     * 获取渠道发单
     * @param  [string] $begin [description]
     * @param  [string] $end   [description]
     * @return [string] $mobile[description]
     */
    public function getChannelOrderCountdList($begin, $end, $channel = '')
    {
        $map['y.time'] = [["EGT", $begin], ["ELT", $end]];
        $map2 = [];
        if (!empty($channel)) {
            $map2['s.alias'] = ['like', "%$channel%"];
        }
        $buildSql = M("yy_order_info")->alias("y")->where($map)
            ->field("y.oid,y.src,	FROM_UNIXTIME(y.time,'%Y-%m-%d') as date")
            ->buildSql();
        return M("yy_order_info")->table($buildSql)->alias("t")
            ->join("JOIN qz_order_source s ON s.type = 1 AND s.src = t.src")
            ->where($map2)
            ->group("t.src")
            ->field("t.src,count(t.oid) as count,t.date,s.charge,s.`name` AS channel")
            ->select();
    }

    /**
     * 获取渠道分单(实际分单会大于发单量，分单指当前渠道的分单)
     * @param  [string] $begin [description]
     * @param  [string] $end   [description]
     * @return [string] $mobile[description]
     */
    public function getChannelRealOrderCountdList($begin, $end, $channel = '')
    {
        $map['n.lasttime'] = [["EGT", $begin], ["ELT", $end]];
        if (!empty($channel)) {
            $map['s.alias'] = ['like', "%$channel%"];
        }

        return M("order_csos_new")->alias("n")
            ->join("join qz_orders t on t.`on` = 4 AND t.type_fw =1 AND n.order_id = t.id")
            ->join("join qz_yy_order_info i on i.oid = n.order_id")
            ->join("join qz_order_source s on s.type = 1 AND i.src = s.src")
            ->field("count(n.order_id) as count,s.charge,s.src,s.`name` as channel")
            ->where($map)
            ->group('channel')
            ->order("t.id desc")
            ->select();
    }

    /**
     * 获取所有渠道
     */
    public function getAllChannelList($channel = '')
    {
        $map = [];
        if (!empty($channel)) {
            $map['alias'] = ['like', "%$channel%"];
        }
        return M("order_source")
            ->field("src,name,charge,alias")
            ->where($map)
            ->order("src desc")
            ->select();
    }

    /**
     * 获取渠道标识列表数
     * @param  string $start   [开始时间]
     * @param  string $end     [结束时间]
     * @param  string $alias   渠道标识]
     * @param  [type] $depts   [部门ID]
     * @return [type]          [description]
     */
    public function getQcOrderAliasCount($start = '', $end = '',$depts,$alias){
        if (!empty($start) && !empty($end)) {
            $map['o.time_real'] = array('between',array($start,$end));

        } else {
            return false;
        }

        if(count($depts) > 0){
            $map["s1.dept"] = array("IN",$depts);
        }

        if(!empty($alias)){
            $map["s1.alias"] = $alias;
        }


        $buildSql = M('orders')->alias('o')
            ->join("left join qz_orders_source s on s.orderid =  o.id ")
            ->join("left join qz_order_source s1 on s1.id =  s.source_src_id and s1.type = '1'")
            ->where($map)
            ->field('o.on,o.type_fw,s1.name,s1.alias,s1.dept')
            ->buildSql();

        $buildSql =   M("orders")->table($buildSql)->alias("t")
            ->field('t.alias')
            ->group('t.alias')
            ->buildSql();
        return M("orders")->table($buildSql)->alias("l")->count();
    }

    /**
     *  获取渠道标识列表
     * @param  string $start   [开始时间]
     * @param  string $end     [结束时间]
     * @param  [type] $depts   [部门ID]
     * @param  string $alias   [渠道标识]
     * @param string $type  1 分单顺序 2 分单倒序 3.赠单顺序 4 赠单倒序 5 无效单顺序 6 无效单倒序
     * @param  string $pageIndex
     * @param  string $pageCount
     * @return [type]          [description]
     */
    public function getQcOrderAliasList($start = '', $end = '',$depts,$alias,$type='',$pageIndex,$pageCount){
        if (!empty($start) && !empty($end)) {
            $map['o.time_real'] = array('between',array($start,$end));

        } else {
            return false;
        }

        if(count($depts) > 0){
            $map["s1.dept"] = array("IN",$depts);
        }

        if(!empty($alias)){
            $map["s1.alias"] = $alias;
        }

        $buildSql = M('orders')->alias('o')
            ->join("left join qz_orders_source s on s.orderid =  o.id ")
            ->join("left join qz_order_source s1 on s1.id =  s.source_src_id and s1.type = '1'")
            ->join("left join qz_qc_info s2 on s2.order_id =   o.id")
            ->where($map)
            ->field('o.on,o.type_fw,s1.name,s1.alias,s1.dept,s2.status')
            ->buildSql();

        //订单状态
        if (!empty($type)) {
            switch ($type) {
                case 1:
                    $order = 'fen_order desc';
                    break;
                case 2:
                    $order = 'fen_order';
                    break;
                case 3:
                    $order = 'zeng_order desc';
                    break;
                case 4:
                    $order = 'zeng_order';
                    break;
                case 5:
                    $order = 'wuxiao_order desc';
                    break;
                case 6:
                    $order = 'wuxiao_order';
                    break;
                case 7:
                    $order = 'no_zj_fen_order desc';
                    break;
                case 8:
                    $order = 'no_zj_fen_order';
                    break;
                case 9:
                    $order = 'no_zj_zeng_order desc';
                    break;
                case 10:
                    $order = 'no_zj_zeng_order';
                    break;
                case 11:
                    $order = 'no_zj_wuxiao_order desc';
                    break;
                case 12:
                    $order = 'no_zj_wuxiao_order';
                    break;
                case 13:
                    $order = 'zj_order desc';
                    break;
                case 14:
                    $order = 'zj_order';
                    break;
                case 15:
                    $order = 'zj_order_percent desc';
                    break;
                case 16:
                    $order = 'zj_order_percent';
                    break;
            }
        }
        //zhijian_order  已质检订单 zhijian_fen_order
        $buildSql2 =  M("orders")->table($buildSql)->alias("t")
            ->field('
                    t.name,
                    t.alias,
                    count(if(t.on = 4 and type_fw =1,1,null)) as fen_order,
                    count(if(t.on = 4 and type_fw =2,1,null)) as zeng_order,
                    count(if(t.on = 5 ,1,null)) as wuxiao_order,
                    count(if(t.on = 4 and type_fw =1 and (t.status = 1 or t.status = 2),1,null)) as zj_fen_order,
                    count(if(t.on = 4 and type_fw =2 and (t.status = 1 or t.status = 2),1,null)) as zj_zeng_order,
                    count(if(t.on = 5 and (t.status = 1 or t.status = 2),1,null)) as zj_wuxiao_order
                    ')
            ->group('t.alias')
            ->buildSql();

        return   M("orders")->table($buildSql2)->alias("t1")
            ->field('
                    t1.name,
                    t1.alias,
                    t1.fen_order,
                    t1.zeng_order,
                    t1.wuxiao_order,
                    (t1.zj_fen_order+t1.zj_zeng_order+t1.zj_wuxiao_order) as zj_order,
                    (t1.fen_order- t1.zj_fen_order) as no_zj_fen_order,
                    (t1.zeng_order- t1.zj_zeng_order) as no_zj_zeng_order,
                    (t1.wuxiao_order- t1.zj_wuxiao_order) as no_zj_wuxiao_order,
                    ((t1.zj_fen_order+t1.zj_zeng_order+t1.zj_wuxiao_order)/(t1.fen_order+ t1.zeng_order)) as zj_order_percent
                    ')
            ->limit($pageIndex.",".$pageCount)
            ->order($order)
            ->select();
    }

    /**
     *  获取渠道标识总数
     * @param  string $start   [开始时间]
     * @param  string $end     [结束时间]
     * @param  [type] $depts   [部门ID]
     * @param  string $alias   [渠道标识]
     * @return [type]          [description]
     */
    public function getQcOrderAliasAll($start = '', $end = '',$depts,$alias){
        if (!empty($start) && !empty($end)) {
            $map['o.time_real'] = array('between',array($start,$end));

        } else {
            return false;
        }

        if(!empty($depts)){
            $map["s1.dept"] = array("IN",$depts);
        }

        if(!empty($alias)){
            $map["s1.alias"] = $alias;
        }

        $buildSql = M('orders')->alias('o')
            ->join("left join qz_orders_source s on s.orderid =  o.id ")
            ->join("left join qz_order_source s1 on s1.id =  s.source_src_id and s1.type = '1'")
            ->join("left join qz_qc_info s2 on s2.order_id =  o.id")
            ->where($map)
            ->field('o.on,o.type_fw,s1.name,s1.alias,s1.dept,s2.status')
            ->buildSql();

        $buildSql2 =  M("orders")->table($buildSql)->alias("t")
            ->field('
                    count(if(t.on = 4 and type_fw =1,1,null)) as fen_order,
                    count(if(t.on = 4 and type_fw =2,1,null)) as zeng_order,
                    count(if(t.on = 5 ,1,null)) as wuxiao_order,
                    count(if(t.on = 4 and type_fw =1 and (t.status = 1 or t.status = 2),1,null)) as zj_fen_order,
                    count(if(t.on = 4 and type_fw =2 and (t.status = 1 or t.status = 2),1,null)) as zj_zeng_order,
                    count(if(t.on = 5 and (t.status = 1 or t.status = 2),1,null)) as zj_wuxiao_order
                    ')
            ->buildSql();

        $result = M("orders")->table($buildSql2)->alias("t1")
            ->field('
                    t1.fen_order,
                    t1.zeng_order,
                    t1.wuxiao_order,
                    (t1.zj_fen_order+t1.zj_zeng_order+ t1.zj_wuxiao_order) as zj_order,
                    (t1.zj_fen_order+t1.zj_zeng_order+ t1.zj_wuxiao_order)/(t1.fen_order+t1.zeng_order) as zj_order_percent
                    ')
            ->select();

        return $result[0];
    }

	 /**
     * 获取渠道信息
     * @param  [type] $ids [description]
     * @return [type]      [description]
     */
    public function getSrcListByIds($ids)
    {
        $map = array(
            "orderid" => array("IN",$ids)
        );
        return M("orders_source")->where($map)->select();
    }

    /**
     * 获取运营采集渠道信息
     * @param  [type] $ids [description]
     * @return [type]      [description]
     */
    public function getYYSrcListByIds($ids)
    {
        $map = array(
            "oid" => array("IN",$ids)
        );
        return M("yy_order_info")->where($map)->select();
    }

    public function delSrcListByIds($ids)
    {
        $map = array(
            "orderid" => array("IN",$ids)
        );
        return M("orders_source")->where($map)->delete();
    }

    public function delYYSrcListByIds($ids)
    {
        $map = array(
            "oid" => array("IN",$ids)
        );
        return M("yy_order_info")->where($map)->delete();
    }

    public function getOrderCsosNewListById($ids)
    {
        $map = array(
            "a.order_id" => array("IN",$ids),
            "a.order_on" => array("EQ",4)
        );
        return M("order_csos_new")->where($map)->alias("a")
                                     ->join("left join qz_orders o on o.id = a.order_id and o.type_fw = 1")
                                     ->join("left join qz_yy_order_info i on i.oid = a.order_id")
                                     ->field("a.*,i.urlid,i.src,i.ref")
                                     ->select();
    }

    public function addOrdersSource($data)
    {
        return  M("orders_source")->add($data);
    }

    /**
     * 获取媒介订单数量
     * @param  [type] $begin [description]
     * @param  [type] $end   [description]
     * @param  [type] $src   [description]
     * @return [type]        [description]
     */
    public function getMediaOrderCount($begin,$end,$src)
    {
        $map = array(
            "a.time_real" => array(
                array("EGT",$begin),
                array("LT",$end)
            ),
            "b.src" => array("IN",$src)
        );

        $buildSql = M("orders")->where($map)->alias("a")
                               ->join("left join qz_yy_order_info b on b.oid = a.id")
                               ->field("FROM_UNIXTIME(time_real,'%Y-%m-%d') as `date`,b.src")
                               ->buildSql();
        $buildSql = M("orders")->table($buildSql)->alias("t")
                          ->field("t.date,count(t.date) as count,t.src")
                          ->group('t.date,t.src')
                          ->buildSql();
        return M("orders")->table($buildSql)->alias("t1")
                               ->join("left join qz_order_source s on s.src = t1.src")
                               ->join("left join qz_order_source_group g on s.groupid = g.id")
                               ->field("t1.date,g.id,g.name,sum(t1.count) as count")
                               ->group("s.groupid,t1.date")
                               ->order("t1.date")
                               ->select();
    }

    public function getMediaHourOrderCount($begin,$end,$src)
    {
        $map = array(
            "a.time_real" => array(
                array("EGT",$begin),
                array("LT",$end)
            ),
            "b.src" => array("IN",$src)
        );

        $buildSql = M("orders")->where($map)->alias("a")
                               ->join("left join qz_yy_order_info b on b.oid = a.id")
                               ->field("FROM_UNIXTIME(time_real,'%H') as `hour`,b.src")
                               ->buildSql();
        $buildSql = M("orders")->table($buildSql)->alias("t")
                          ->field("t.hour,count(t.hour) as count,t.src")
                          ->group('t.hour,t.src')
                          ->buildSql();
        return M("orders")->table($buildSql)->alias("t1")
                               ->join("left join qz_order_source s on s.src = t1.src")
                               ->join("left join qz_order_source_group g on s.groupid = g.id")
                               ->field("t1.hour,g.id,g.name,sum(t1.count) as count")
                               ->group("s.groupid,t1.hour")
                               ->order("t1.hour")
                               ->select();
    }

    /**
     * 获取渠道订单时间段均值
     * @param  [type] $begin [description]
     * @param  [type] $end   [description]
     * @return [type]        [description]
     */
    public function getOrderAvgTimeCountBySrc($begin,$end,$src)
    {
        $map = array(
            "time_real" => array(
                array("EGT",$begin),
                array("LT",$end)
            )
        );

        if (!empty($src)) {
            $where['i.src'] = array("NOT IN",$src);
        }

        $buildSql = M("orders")->where($map)->field('id, FROM_UNIXTIME(time_real,"%H") as `hour`,FROM_UNIXTIME(time_real,"%Y-%m-%d") as `date`')->buildSql();
        return   M("orders")->table($buildSql)->alias("t")->where($where)
                               ->join("left join qz_yy_order_info i on i.oid = t.id")
                               ->field("t.hour,count(t.id) as count,i.src")
                               ->group("hour,src")
                               ->order("hour")
                               ->select();

    }

    /**
     * 根据电话获取订单已有条数
     * @param  [type] $phone 手机号
     */
    public function getRowsByTel($phone){
        if(!empty($phone)){
            $map['deleted'] = array('neq',1);
            $map['tel_encrypt'] = array('eq',$phone);
            return M("orders")->where($map)->count();
        }
    }

    /**
     * 获取城市订单数据个数
     */
    public function getCityOrdersCount($where, $order, $group)
    {
        if(!$group){
            $group = 't.cs,b.src';
        }
        $buildSql = M('orders')->alias('t')
            ->field('t.cs')
            ->join('JOIN qz_quyu as a on a.cid = t.cs')
            ->join('JOIN qz_yy_order_info as b on b.oid = t.id')
            ->join('JOIN qz_order_source as c on c.src = b.src')
            ->join('JOIN qz_order_source_group as d on c.groupid = d.id')
            ->where($where)
            ->order($order)
            ->group($group)
            ->buildSql();
        return M('orders')->table($buildSql)->alias('d')->count();
    }

    /**
     * 获取城市订单数据
     */
    public function getCityOrdersList($where, $order, $group, $page, $paCount)
    {
        if(!$group){
            $group = 't1.cs,t1.src';
        }
        if(!$order){
            $order = 't1.id desc';
        }
        $start = 0;
        $end = 0;
        //提取 查询开始时间,结束时间,用作计算 在这段时间的量房数据
        if($where['t.time_real']){
            $start = $where['t.time_real'][0][1];
            $end = $where['t.time_real'][1][1];
        }
        $buildSql = M('orders')->alias('t')
            ->field('t.id,t.cs,a.cname,t.on,t.type_fw,t.qiandan_status,e.status,e.lf_time,c.src,c.name,c.groupid,d.name AS group_name
            ,count(if(t.ON = 4 AND t.type_fw = 1 and e.`status`=1,1,NULL)) as fen_liang_all_order,count(if(t.ON = 4 AND t.type_fw = 2 and e.`status`=1,1,NULL)) as zeng_liang_all_order')
            ->join('JOIN qz_quyu as a on a.cid = t.cs')
            ->join('JOIN qz_yy_order_info as b on b.oid = t.id')
            ->join('JOIN qz_order_source as c on c.src = b.src')
            ->join('JOIN qz_order_source_group as d on c.groupid = d.id')
            ->join('LEFT JOIN qz_order_company_review as e on e.orderid = t.id AND e.status = 1')
            ->where($where)
            ->group('t.id')
            ->buildSql();
        //计算当前订单中量房的时间是否在 这段查询时间中
        $buildSql = M('orders')->table($buildSql)->alias('d')
            ->field('d.*,if(d.lf_time >= ' . $start . ' AND d.lf_time <= ' . $end . ',1,0) as liangfang_rel')
            ->buildSql();
        return M('orders')->table($buildSql)->alias('t1')
            ->field('t1.cs,t1.cname,
	count(t1.id) as fa_order,
	count(if(t1.on = 4 and t1.type_fw =1,1,null)) as fen_order,
	count(if(t1.on = 4 and t1.type_fw =2,1,null)) as zeng_order,
	count(if(t1.on = 4 and t1.type_fw =1 AND t1.qiandan_status = 1,1,null)) as fen_qian_order,
	count(if(t1.on = 4 and t1.type_fw =2 AND t1.qiandan_status = 1,1,null)) as zeng_qian_order,
	count(IF (t1.ON = 4 AND t1.type_fw = 1 AND t1.status =1,1,NULL)) AS fen_liang_order,
	count(IF (t1.ON = 4 AND t1.type_fw = 2 AND t1.status =1,1,NULL)) AS zeng_liang_order,
	count(IF (t1.ON = 4 AND t1.type_fw = 1 AND t1.status =1 AND t1.liangfang_rel = 1,1,NULL)) AS fen_liang_rel_order,
	count(IF (t1.ON = 4 AND t1.type_fw = 2 AND t1.status =1 AND t1.liangfang_rel = 1,1,NULL)) AS zeng_liang_rel_order,
	count(if(t1.on = 4 AND t1.qiandan_status = 1,1,null)) as qian_order,t1.src,t1.name,t1.groupid,t1.group_name,
	SUM(t1.fen_liang_all_order) as fen_liang_all_order,SUM(t1.zeng_liang_all_order) as zeng_liang_all_order')
            ->group($group)
            ->order($order)
            ->limit($page,$paCount)
            ->select();
    }

    /**
     * 获取城市订单求和数据
     */
    public function getCityOrdersAllList($where,$group)
    {
        $start = 0;
        $end = 0;
        //提取 查询开始时间,结束时间,用作计算 在这段时间的量房数据
        if($where['t.time_real']){
            $start = $where['t.time_real'][0][1];
            $end = $where['t.time_real'][1][1];
        }
        $buildSql = M('orders')->alias('t')
            ->field('t.id,t.on,t.type_fw,t.qiandan_status,e.status,e.lf_time,cn.lasttime,c.src,c.name,c.groupid,d.name AS group_name')
            ->join('JOIN qz_quyu as a on a.cid = t.cs')
            ->join('JOIN qz_yy_order_info as b on b.oid = t.id')
            ->join('JOIN qz_order_source as c on c.src = b.src')
            ->join('JOIN qz_order_source_group as d on c.groupid = d.id')
            ->join('LEFT JOIN qz_order_csos_new cn on cn.order_id = t.id')
            ->join('LEFT JOIN qz_order_company_review as e on e.orderid = t.id AND e.status = 1')
            ->where($where)
            ->group('t.id')
            ->buildSql();
        //计算当前订单中量房的时间是否在 这段查询时间中
        $buildSql = M('orders')->table($buildSql)->alias('d')
            ->field('d.*,if(d.lf_time >= ' . $start . ' AND d.lf_time <= ' . $end . ',1,0) as liangfang_rel')
            ->buildSql();
        //计算当前订单中真实分单
        $buildSql = M('orders')->table($buildSql)->alias('d1')
            ->field('d1.*,if(d1.lasttime >= ' . $start . ' AND d1.lasttime <= ' . $end . ',1,0) as rel_order')
            ->buildSql();
        return M('orders')->table($buildSql)->alias('t')
            ->field('
    count(t.id) as fa_order,
	count(if(t.on = 4 and t.type_fw =1,1,null)) as fen_order,
	count(if(t.on = 4 and t.type_fw =2,1,null)) as zeng_order,
	count(if(t.on = 4 and t.type_fw =1 AND t.qiandan_status = 1,1,null)) as fen_qian_order,
	count(if(t.on = 4 and t.type_fw =2 AND t.qiandan_status = 1,1,null)) as zeng_qian_order,
	count(IF (t.ON = 4 AND t.type_fw = 1 AND t.status =1,1,NULL)) AS fen_liang_order,
	count(IF (t.ON = 4 AND t.type_fw = 2 AND t.status =1,1,NULL)) AS zeng_liang_order,
	count(IF (t.ON = 4 AND t.type_fw = 1 AND t.status =1 AND t.liangfang_rel = 1,1,NULL)) AS fen_liang_rel_order,
	count(IF (t.ON = 4 AND t.type_fw = 2 AND t.status =1 AND t.liangfang_rel = 1,1,NULL)) AS zeng_liang_rel_order,
	SUM(IF (t.ON = 4 AND t.type_fw = 1 AND t.rel_order = 1,1,NULL)) AS real_fendan,
	count(if(t.on = 4 AND t.qiandan_status = 1,1,null)) as qian_order,t.groupid,t.group_name')
            ->group($group)
            ->select();
    }
    /**
     * 获取城市订单数据
     */
    public function getOrdersDetailList($where, $order = 't.id desc')
    {
        $data = M('yy_order_info')->alias('b')
            ->field('t.time_real,t.id,t.`on`,t.type_fw,qiandan_status,b.src,t.openeye_st')
            ->join('JOIN qz_orders as t on b.oid = t.id')
            ->where($where)
            ->order($order)
            ->select();
        foreach ($data as $k=>$v){
            if($v['on'] == 4 && $v['type_fw'] == 1 ){
                $data[$k]['orders_type'] = '分单';
            }
            if ($v['on'] == 4 && $v['type_fw'] == 2 ){
                $data[$k]['orders_type'] = '赠单';
            }
            if ($v['qiandan_status'] == 1){
                $data[$k]['orders_type'] = '签单';
            }
            //是否 显号
            if($v['openeye_st'] == 1){
                $data[$k]['openeye_st'] = '是';
            }else{
                $data[$k]['openeye_st'] = '否';
            }
        }
        return $data;
    }


    public function getQianDanListCount($city_id = array(),$id,$begin,$end,$status,$state,$city,$company,$qian)
    {
        $map = array(
            "o.qiandan_addtime" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            "o.cs" => array("IN",$city_id),
            "o.qiandan_companyid" => array("GT",0),
            "o.qiandan_status" => array("EGT","0"),
            "o.on" => array("EQ",4),
            "o.type_fw" => array("IN",array(1,2)),

        );

        if (!empty($id)) {
            $map["_complex"] = array(
                "o.id" => array("eq",$id),
                "o.xiaoqu" => array("LIKE","%$id%"),
                "_logic" => "OR"
            );
        }

        if ($status !== "") {
            $map["o.qiandan_status"] = array("EQ",$status);
        }

        if (!empty($state)) {
            $map["o.type_fw"] = array("EQ",$state);
        }

        if (!empty($company)) {
            $map["u.qc"] = array("like","%$company%");
        }

        if (!empty($city)) {
            $map["o.cs"] = array("EQ",$city);
        }

        if (!empty($qian)) {
            if ($qian == 1) {
                $map['_complex'] = array(
                    'u.classid' => ['eq',6],
                    'c.cooperate_mode' => ['eq',2],
                    '_logic' => 'or'
                );
            } else {
                $map["u.classid"] = array("EQ",3);
            }
        }

        return M("orders")->where($map)->alias("o")
                          ->join("join qz_user u on o.qiandan_companyid = u.id")
                          ->join("join qz_user_company c on c.userid = u.id")
                          ->field("o.id")
                          ->count();
    }

    /**
     * 获取签单列表
     * @param  array  $city_id [description]
     * @return [type]          [description]
     */
    public function getQianDanList($city_id = array(),$id,$begin,$end,$status,$state,$city,$company,$qian,$pageIndex,$pageCount)
    {
        $map = array(
            "o.qiandan_addtime" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            "o.qiandan_companyid" => array("GT",0),
            "o.qiandan_status" => array("EGT","0"),
            "o.on" => array("EQ",4),
            "o.type_fw" => array("IN",array(1,2))
        );

        if (count($city_id) > 0) {
            $map["o.cs"] = array("IN",$city_id);
        }

        if (!empty($id)) {
            $map["_complex"] = array(
                "o.id" => array("eq",$id),
                "o.xiaoqu" => array("LIKE","%$id%"),
                 "_logic" => "OR"
            );
        }

        if ($status !== "") {
            $map["o.qiandan_status"] = array("EQ",$status);
        }

        if (!empty($state)) {
            $map["o.type_fw"] = array("EQ",$state);
        }

        if (!empty($city)) {
            $map["o.cs"] = array("EQ",$city);
        }

        if (!empty($company)) {
            $map["u.qc"] = array("like","%$company%");
        }

        if (!empty($qian)) {
            if ($qian == 1) {
                $map['_complex'] = array(
                    'u.classid' => ['eq',6],
                    'c.cooperate_mode' => ['eq',2],
                    '_logic' => 'or'
                );
            } else {
                $map["u.classid"] = array("EQ",3);
            }
        }

        $buildSql = M("orders")->where($map)->alias("o")
                          ->field("o.id,o.qiandan_addtime,o.time_real,o.cs,o.qx,o.on,o.type_fw,o.xiaoqu,o.mianji,o.name,o.sex,o.qiandan_status,o.qiandan_companyid,u.qc,o.qiandan_jine,u.classid,c.cooperate_mode")
                          ->join("join qz_user u on o.qiandan_companyid = u.id")
                            ->join("qz_user_company c on c.userid = u.id")
                          ->limit($pageIndex.",".$pageCount)
                          ->order("qiandan_addtime desc,qiandan_status desc")->buildSql();

        return M("orders")->table($buildSql)->alias("t")
                          ->join("join qz_quyu q on q.cid = t.cs")
                          ->join("join qz_area a on a.qz_areaid = t.qx")
                          ->field("t.*,q.cname,a.qz_area")
                          ->select();
    }

    /**
     * 获取订单信息,只有订单数据
     * @param  [type] $order_id [订单ID]
     * @return [type]           [description]
     */
    public function getOrderInfo($order_id)
    {
        $map = array(
            "a.id" => array("EQ",$order_id)
        );
        return M("orders")->where($map)->alias("a")
                          ->join("join safe_order_tel8 t on t.orderid = a.id")
                          ->field("a.*,t.tel8")
                          ->find();
    }

    /**
     * 添加申请修改数据
     * @param $save
     * @return mixed
     */
    public function addOrdersApplyEdit($save)
    {
        return M('orders_apply_edit')->add($save);
    }

    public function getOrderApplyEdit($id,$type=1){

        if($type == 1){
            $map["orders_id"] = array("EQ",$id);
            return M('orders_apply_edit')->field('status,save_state')->where($map)->find();
        }else{
            $map["a.orders_id"] = array("EQ",$id);
            return M('orders_apply_edit')->alias('a')
                ->field('a.*,u.name as applyname,u1.name as passname')
                ->join('left join qz_adminuser as u on u.id = a.apply_id')
                ->join('left join qz_adminuser as u1 on u1.id = a.pass_id')
                ->where($map)->find();
        }
    }

    public function getOrderApplyEditList($ids){
        if (empty($ids)) {
            return false;
        }
        if (is_array($ids)) {
            $map['orders_id'] = array('IN', $ids);
        } else {
            $map['orders_id'] = $ids;
        }

        return M('orders_apply_edit')->field('orders_id,status')->where($map)->select();
    }

    /**
     * 修改申请状态
     * @param $id
     * @param $data
     * @return bool
     */
    public function changeApplyEdit($id,$data)
    {
        $map = array(
            "orders_id" => array("EQ",$id)
        );
        return M("orders_apply_edit")->where($map)->save($data);
    }
	// 查询是否已导入
    public function searchDdcLog($orderid){
        $map = [];
        $map['order_id'] = $orderid;
        return M('order_to_ddc_log')->where($map)->find();
    }

    /**
     *  根据订单id获取相应的订单信息
     */
    public function showOrderInfoById($id){
        $map['o.id'] = $id;
        $map['q.is_open_city'] = 1;
        return M('orders')->alias('o')
                ->where($map)
                ->field("o.id order_id,o.time_real,date_format(NOW(),'%Y-%m-%d %H:%i:%s') as timeshow,o.type_fw,o.cs,o.dz,o.ip,o.tel,o.tel_encrypt,q.cname quyu,o.`name`,IFNULL(o.sex,'') as sex ,IFNULL(o.other_contact,'') as other_contact,IFNULL(o.qx,'') as qx,o.xiaoqu,o.huxing,o.mianji,o.fengge,o.lx,o.yusuan,t.tel8")
                ->join("join qz_jiaju_quyu q on q.cid = o.cs")
                ->join("join safe_order_tel8 t on t.orderid = o.id")
                ->find();
    }

    /**
     * 添加到家具订单池
     */
     public function addtoJiaJuOrder($orderinfo){
         if(empty($orderinfo) || !isset($orderinfo)){
             return false;
         }
         return M('jiaju_order')->add($orderinfo);
     }

    /**
     *  添加日志表信息
     */
    public function addJiaJuOrderLog($logdata){
        if(empty($logdata) || !isset($logdata)){
            return false;
        }
        return M('order_to_ddc_log')->add($logdata);
    }


    /**
     * 添加家具订单池的电话记录
     */
    public function addJiaJuOrderTelInfo($teldata){
        if(empty($teldata) || !isset($teldata)){
            return false;
        }
        return M()->table('safe_order_tel8')->add($teldata);
    }

    /**
     * 添加家具订单数据到pool表
     * @param $pooldata
     * @return bool|mixed
     */
    public function addJiaJuOrderPoolInfo($pooldata){
        if(empty($pooldata) || !isset($pooldata)){
            return false;
        }
        return M('jiaju_order_pool')->add($pooldata);
    }

    /**
     * [getOrdersListCount 获取数量]
     * @param  integer $id              [订单ID]
     * @param  integer $cs              [订单城市]
     * @param  string  $xiaoqu          [订单小区]
     * @param  string  $ip              [订单IP]
     * @param  string  $tel_encrypt     [订单加密后电话号码]
     * @param  string  $time_start      [修改后发布开始时间]
     * @param  string  $time_end        [修改后发布结束时间]
     * @param  string  $time_real_start [真实发布开始时间]
     * @param  string  $time_real_end   [真实发布结束时间]
     * @param  string  $nf_time_start   [拿房开始时间]
     * @param  string  $nf_time_end     [拿房结束时间]
     * @param  boolean $on              [订单状态]
     * @param  boolean $on_sub          [订单子状态]
     * @param  boolean $type_fw         [分单问单]
     * @param  boolean $remarks         [订单备注]
     * @param  boolean $openeye_st      [显示号码状态]
     * @return [type]                   [description]
     */
    public function getAllOrdersListCount(
        $time_start= '',
        $time_end= '',
        $yusuan= '',
        $type_fw= '',
        $lx= '',
        $fangshi= '',
        $cs= '',
        $qx= '',
        $order= '',
        $each= ''
    ){
        $admin = getAdminUser();

        //处理修改后的订单发布时间段
        if (!empty($time_start) || !empty($time_end) )
        {
            //如果起止时间都有
            if(!empty($time_start) && !empty($time_end)) {
                $map['o.time'] = array('BETWEEN', [$time_start, $time_end]);
            }
            //如果只有开始时间没有结束时间time
            if(!empty($time_start) && empty($time_end)) {
                $map['o.time'] = array('EGT', $time_start);
            }
            //如果只有结束时间没有开始时间
            if(empty($time_start) && !empty($time_end)) {
                $map['o.time'] = array('ELT', $time_end);
            }
        }

        if ($yusuan !== false) {
         $map['o.yusuan'] = $yusuan;
        }

        if ($lx !== false) {
            $map['o.lx'] = $lx;
        }

        if ($fangshi !== false) {
            $map['o.fangshi'] = $fangshi;
        }

        if ($cs !== false) {
            $map['o.cs'] = $cs;
        }

        if ($qx !== false) {
            $map['o.qx'] = $qx;
        }

        //订单分问
        if ($type_fw !== false) {
            if (is_array($type_fw)) {
                $map['o.type_fw'] = array('IN', $type_fw);
            } else {
                $map['o.type_fw'] =  array(['IN',array(1,2,3,4)],['EQ',$type_fw],'and');
            }
        }else{
            $map['o.type_fw'] =  array('IN',array(1,2,3,4));
        }


        $db = M('orders');
        $db = $db->alias('o');
        //如果是超级管理员 黑名单的订单也显示 不是超级管理员 黑名单的号码的订单不显示
        if (1 != $admin['uid']) {
            $map['b.status'] = array(array('EQ', 0),array('EXP',' IS NULL '),'OR');
            $db = $db->join('LEFT JOIN qz_order_blacklist AS b ON b.tel_encrypt = o.tel_encrypt');
        }

        $count = $db->where($map)->count();
        return $count;
    }

    /**
     * [getOrdersList 获取订单列表]
     * @param  integer $id              [订单ID]
     * @param  integer $cs              [订单城市]
     * @param  string  $xiaoqu          [订单小区]
     * @param  string  $ip              [订单IP]
     * @param  string  $tel_encrypt     [订单加密后电话号码]
     * @param  string  $time_start      [修改后发布开始时间]
     * @param  string  $time_end        [修改后发布结束时间]
     * @param  string  $time_real_start [真实发布开始时间]
     * @param  string  $time_real_end   [真实发布结束时间]
     * @param  string  $nf_time_start   [拿房开始时间]
     * @param  string  $nf_time_end     [拿房结束时间]
     * @param  boolean $on              [订单状态]
     * @param  boolean $on_sub          [订单子状态]
     * @param  boolean $type_fw         [分单问单]
     * @param  boolean $remarks         [订单备注]
     * @param  boolean $openeye_st      [显示号码状态]
     * @param  boolean $order           [排序]
     * @param  boolean $start           [开始页]
     * @param  boolean $end             [每页查询]
     * @return [type]                   [description]
     */
    public function getAllOrdersList(
        $time_start= '',
        $time_end= '',
        $yusuan= '',
        $type_fw= '',
        $lx= '',
        $fangshi= '',
        $cs= '',
        $qx= '',
        $order = 'time DESC',
        $start = '0',
        $end = '20'
    ){
        //获取用户信息
        $admin = getAdminUser();

        if ($yusuan !== false) {
            $map['o.yusuan'] = $yusuan;
        }

        if ($lx !== false) {
            $map['o.lx'] = $lx;
        }

        if ($fangshi !== false) {
            $map['o.fangshi'] = $fangshi;
        }

        if ($cs !== false) {
            $map['o.cs'] = $cs;
        }

        if ($qx !== false) {
            $map['o.qx'] = $qx;
        }

        //处理修改后的订单发布时间段
        if (!empty($time_start) || !empty($time_end) )
        {
            //如果起止时间都有
            if(!empty($time_start) && !empty($time_end)) {
                $map['o.time'] = array('BETWEEN', [$time_start, $time_end]);
            }
            //如果只有开始时间没有结束时间time
            if(!empty($time_start) && empty($time_end)) {
                $map['o.time'] = array('EGT', $time_start);
            }
            //如果只有结束时间没有开始时间
            if(empty($time_start) && !empty($time_end)) {
                $map['o.time'] = array('ELT', $time_end);
            }
        }

        //订单分问
        if ($type_fw !== false) {
            if (is_array($type_fw)) {
                $map['o.type_fw'] = array('IN', $type_fw);
            } else {
                $map['o.type_fw'] =  array(['IN',array(1,2,3,4)],['EQ',$type_fw],'and');
            }
        }else{
            $map['o.type_fw'] =  array('IN',array(1,2,3,4));
        }

        $db = M('orders');

        $field = 'o.sex,o.yusuan,o.qiandan_companyid,o.text,o.xiaoqu,o.yt,o.fengge,o.name,o.huxing,o.fangshi,o.lx,(case o.lx when 1 then "家装" when 2 then "公装" else "" end) as lx_name,o.lxs,o.id,o.time_real,o.time,o.cs,o.qx,o.tel,o.tel_encrypt,o.nf_time,o.mianji,o.visitime,o.on,o.on_sub,o.type_fw,(case o.type_fw when 1 then "分单" when 2 then "赠单" when 3 then "分没人跟" when 4 then "赠没人跟" else "其他" end) as type_fw_name,o.type_zs_sub,o.order2com_allread,o.from_old_orderid,o.remarks,o.lasttime,o.openeye_st,o.openeye_reger,o.openeye_sqly,o.calllong_time,o.callfast_time,o.wzd,o.source';
        if (1 != $admin['uid']) {
            //注意！更改的话下面同样更改;如果是超级管理员 黑名单的订单也显示 不是超级管理员 黑名单的号码的订单不显示,
            $field = $field . ',b.status AS order_blacklist_status';
        }

        $db = $db->field($field)
            ->alias('o');
        if (1 != $admin['uid']) {
            //如果是超级管理员 黑名单的订单也显示 不是超级管理员 黑名单的号码的订单不显示
            $map['b.status'] = array(array('EQ', 0),array('EXP',' IS NULL '),'OR');
            $db = $db->join('LEFT JOIN qz_order_blacklist AS b ON b.tel_encrypt = o.tel_encrypt');
        }

        $build = $db->where($map)->order($order)->limit($start, $end)->buildSql();
        $result = M()->table($build)->alias('z')
            ->field('z.*, q.cname AS city,ad.name as fendan_name, a.qz_area AS area,hx.name as huxing_name,f.name as fangshi_name,fg.name as fengge_name,j.name as yusuan_name,hx.name as huxing_name,group_concat(u.qc) as companys')
            ->join('LEFT JOIN qz_quyu AS q ON q.cid = z.cs')
            ->join('LEFT JOIN qz_area AS a ON a.qz_areaid = z.qx')
            ->join('LEFT JOIN qz_order_info AS i ON i.order = z.id')
            ->join('LEFT JOIN qz_user AS u ON u.id = i.com and classid = \'3\'')
            ->join('LEFT JOIN qz_order_csos_new AS c ON c.order_id = z.id')
            ->join('LEFT JOIN qz_adminuser AS ad ON ad.id = c.user_id')
            ->join('LEFT JOIN qz_fangshi AS f ON f.id = z.fangshi')
            ->join('LEFT JOIN qz_fengge AS fg ON fg.id = z.fengge')
            ->join('LEFT JOIN qz_jiage AS j ON j.id = z.yusuan')
            ->join('LEFT JOIN qz_huxing AS hx ON hx.id = z.huxing')
            ->group('z.id')
            ->order($order)
            ->select();
        return $result;
    }

    public function getOrderFieldStatList($begin,$end,$city,$on)
    {
        $map = array(
            "o.time_real" => array(
                array("EGT",$begin),
                array("ELT",$end)
            )
        );

        if (!empty($city)) {
            $map["o.cs"] = array("EQ",$city);
        }

        if (!empty($on)) {
            $map["o.on"] = array("EQ",$on);
        }

        return M("log_order_field")->where($map)->alias("a")
                                   ->join('join qz_orders o on a.order_id = o.id')
                                   ->field("field,count(a.order_id) as all_count, count(if(a.before <> '',1,null)) as before_count,count(if(a.state = 2,1,null)) as after_count,count(if(a.state = 3,1,null)) as update_count")
                                   ->group("field")->order("field(field,'cs','qx','xiaoqu','mianji','yusuan')")->select();
    }

    public function getOrderField($order_id,$field)
    {
        $map = array(
            "a.order_id" => array("EQ",$order_id)
        );
        $model = M("log_order_field");
        $sql = $model->where($map)->alias("a")
                                   ->join('join qz_orders o on a.order_id = o.id')
                                   ->field("field,`before`,`after`,if(state = 3,after,'') as `update`,a.state,a.time")->buildSql();

        $union = [];
        foreach ($field  as $key => $value) {
            $map = array(
                "t.field" => array("EQ",$value)
            );
            switch ($value) {
                case 'cs':
                    $union[] = $model->where($map)->table($sql)->alias("t")
                                   ->join("left join qz_quyu q on q.cid = t.`before` ")
                                   ->join("left join qz_quyu q1 on q1.cid = t.`after`")
                                   ->join("left join qz_quyu q2 on q2.cid = t.`update`")
                                   ->field("t.field,q.cname as `before`,q1.cname as `after`,q2.cname as `update`,t.state,t.time,t.before as before_value")
                                   ->buildSql();
                    break;
                case 'qx':
                    $union[] = $model->where($map)->table($sql)->alias("t")
                                   ->join("left join qz_area q on q.qz_areaid = t.`before`")
                                   ->join("left join qz_area q1 on q1.qz_areaid = t.`after` ")
                                   ->join("left join qz_area q2 on q2.qz_areaid = t.`update`")
                                   ->field("t.field,q.qz_area as `before`,q1.qz_area as `after`,q2.qz_area as `update`,t.state,t.time,t.before as before_value")
                                   ->buildSql();
                    break;
                case 'yusuan':
                    $union[] = $model->where($map)->table($sql)->alias("t")
                                   ->join("left join qz_jiage q on q.id = t.`before`")
                                   ->join("left join qz_jiage q1 on q1.id = t.`after` ")
                                   ->join("left join qz_jiage q2 on q2.id = t.`update`")
                                   ->field("t.field,q.name as `before`,q1.name as `after`,q2.name as `update`,t.state,t.time,t.before as before_value")
                                   ->buildSql();
                    break;
                default:
                    $union[] = $model->where($map)->table($sql)->alias("t")
                                    ->field("t.field,`before` as `before`,`after` as `after`,`update` as `update`,t.state,t.time,t.before as before_value")
                                     ->buildSql();
                    break;
            }
        }

        foreach ($union as $key => $value) {
            if ($key == 0) {
                $model->table($value)->alias("t1");
            } else {
                $model->union($value,true);
            }
        }

        return  $model->select();
    }

    public function editOrderField($order_id,$field,$data)
    {
        $map = array(
            "order_id" => array("EQ",$order_id),
            "field" => array("EQ",$field)
        );
        return M("log_order_field")->where($map)->save($data);
    }

    /**
     * 获取所有城市时间段内发单量/分单量
     * @param  [type] $begin [description]
     * @param  [type] $end   [description]
     * @return [type]        [description]
     */
    public function getAllCityOrderList($begin,$end)
    {
        $map = array(
            "time_real" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
        );
        return M("orders")->where($map)
            ->field("cs as city_id,count(id) as all_count,count(if(`on` = 4 and type_fw = 1,1,null)) as fen_count")
            ->group("cs")
            ->select();
    }

    /**
     * 获取所有城市时间段内发单量/分单量
     * @param  [type] $begin [description]
     * @param  [type] $end   [description]
     * @return [type]        [description]
     */
    public function getAllCityRealOrderList($begin,$end)
    {
        $map = array(
            "a.lasttime" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            "o.on" => array("EQ",4),
            "o.type_fw" => array("EQ",1)
        );
        return M("order_csos_new")->where($map)->alias("a")
                          ->join("join qz_orders o on o.id = a.order_id")
                          ->field("o.cs as city_id,count(o.id) as fen_count")
                          ->group("o.cs")->select();
    }

    /**
     * 获取所有城市时间段内发单量/分单量
     * @param  [type] $begin [description]
     * @param  [type] $end   [description]
     * @return [type]        [description]
     */
    public function getAllCityRealFenOrderList($begin, $end)
    {
        $map = array(
            "i.addtime" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            "o.`on`" => array("EQ",4),
            "o.type_fw" => array("EQ",1)
        );

        return M("orders")->alias("o")->where($map)
            ->join("join qz_order_info as i on i.`order` = o.id")
            ->field("o.cs as city_id,count(distinct o.id) as fen_count")
            ->group("o.cs")
            ->select();
    }

    public function getCityOrderByAllSource($city_id,$begin,$end,$group,$src,$dept)
    {
        $map = array(
            "o.cs" => array("EQ",$city_id),
            "o.time_real" => array(
                array("EGT",$begin),
                array("ELT",$end)
            )
        );

        if (!empty($group)) {
            $where["g.id"] = array("EQ",$group);
        }

        if (!empty($src)) {
            $where["t.src"] = array("EQ",$src);
        }

        if (count($dept) > 0) {
            $where["s.dept"] = array("IN",$dept);
        }

        $buildSql = M("orders")->where($map)->alias("o")
            ->join("left join qz_yy_order_info i on i.oid = o.id")
            ->field("o.*,if(i.src is null OR i.src = '','',i.src) as src")->buildSql();

        return M("orders")->where($where)->table($buildSql)->alias("t")
            ->join("left join qz_order_source s on s.src = t.src")
            ->join("left join qz_order_source_group g on g.id = s.groupid")
            ->join("join qz_quyu q on q.cid = t.cs")
            ->field([
                "t.src", "s.name as source_name", "q.cname", "g.id as group_id", "g.name as group_name",
                "count(t.id) as all_count",
                "count(if(t.on = 4 and t.type_fw in (1,2),1,null)) as yx_count",
                "count(if(t.on = 4 and t.type_fw = 1,1,null)) as fen_count"
            ])
            ->group("t.src")
            ->select();
    }

    /**
     * 获取城市时间段内合作渠道
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getCityAllSource($city_id,$begin,$end)
    {
        $map = array(
            "o.cs" => array("EQ",$city_id),
            "o.time_real" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            // "g.category" => array("EQ",1)
        );

        return M("orders")->alias("o")->where($map)
            ->join("left join qz_yy_order_info i on i.oid = o.id")
            ->join("left join qz_order_source s on s.src = i.src")
            ->join("left join qz_order_source_group g on g.id = s.groupid")
            ->field("i.src,s.name as source_name,s.groupid,g.name")
            ->group("i.src")
            ->select();
    }

    public function getCityRealOrderByAllSource($city_id,$begin,$end,$group,$src)
    {
        $map = array(
            "o.cs" => array("EQ",$city_id),
            "new.lasttime" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            "o.on" => array("EQ",4)
        );

        if (!empty($group)) {
            $map["g.id"] = array("EQ",$group);
        }

        if (!empty($src)) {
            $map["i.src"] = array("EQ",$src);
        }

        return M("order_csos_new")->where($map)->alias("new")
            ->join("left join qz_orders o on o.id = new.order_id")
            ->join("left join qz_yy_order_info i on i.oid = o.id")
            ->join("left join qz_order_source s on s.src = i.src")
            ->join("left join qz_order_source_group g on g.id = s.groupid")
            ->field([
                "i.src",
                "count(o.id) as yx_count",
                "count(if(o.on = 4 and o.type_fw = 1,1,null)) as fen_count",
                "s.name as source_name", "g.id as group_id", "g.`name` as group_name"
            ])
            ->group("i.src")
            ->select();
    }

    public function getCityOrderDetailsListWithSrcCount($city_id,$monthStart,$monthEnd,$src,$status)
    {
        $map = array(
            "o.cs" => array("EQ",$city_id),
            "o.time_real" => array(
                array("EGT",$monthStart),
                array("ELT",$monthEnd)
            ),
        );

        if (!empty($status)) {
            switch ($status) {
                case '1':
                    $map["o.on"] = array("EQ",4);
                    $map["o.type_fw"] = array("EQ",1);
                    break;
                case '2':
                    $map["o.on"] = array("EQ",4);
                    $map["o.type_fw"] = array("EQ",2);
                    break;
                case '3':
                    $map["o.on"] = array("EQ",4);
                    $map["o.type_fw"] = array("EQ",3);
                    break;
                case '4':
                    $map["o.on"] = array("EQ",4);
                    $map["o.type_fw"] = array("EQ",4);
                    break;
                case '5':
                    $map["o.on"] = array("NEQ",4);
                    break;
            }
        }

        $buildSql = M("orders")->where($map)->alias("o")
                      ->join("left join qz_yy_order_info i on i.oid = o.id")
                      ->field("o.*,  if(i.src is null OR i.src = '','',i.src) as src")
                      ->buildSql();
        $map = array(
             "t.src" => array("EQ",$src)
        );
        return M("orders")->table($buildSql)->where($map)->alias("t")
                          ->join("left join qz_order_source s on s.src = t.src")
                          ->count();
    }

    public function getCityOrderDetailsListWithSrc($pageIndex,$pageCount,$city_id,$monthStart,$monthEnd,$src,$status)
    {
        $map = array(
            "o.cs" => array("EQ", $city_id),
            "o.time_real" => array(
                array("EGT", $monthStart),
                array("ELT", $monthEnd)
            ),
        );

        if (!empty($status)) {
            switch ($status) {
                case '1':
                    $map["o.on"] = array("EQ", 4);
                    $map["o.type_fw"] = array("EQ", 1);
                    break;
                case '2':
                    $map["o.on"] = array("EQ", 4);
                    $map["o.type_fw"] = array("EQ", 2);
                    break;
                case '3':
                    $map["o.on"] = array("EQ", 4);
                    $map["o.type_fw"] = array("EQ", 3);
                    break;
                case '4':
                    $map["o.on"] = array("EQ", 4);
                    $map["o.type_fw"] = array("EQ", 4);
                    break;
                case '5':
                    $map["o.on"] = array("NEQ", 4);
                    break;
            }
        }

        $buildSql = M("orders")->where($map)->alias("o")
            ->join("left join qz_yy_order_info i on i.oid = o.id")
            ->field("o.*,  if(i.src is null OR i.src = '','',i.src) as src")
            ->buildSql();
        $map = array(
            "t.src" => array("EQ", $src)
        );

        $buildSql = M("orders")->table($buildSql)->alias("t")->where($map)
            ->join("left join qz_order_source s on s.src = t.src")
            ->join("join qz_quyu q on q.cid = t.cs")
            ->join("join qz_area a on a.qz_areaid = t.qx")
            ->field("t.id,from_unixtime(t.time_real,'%Y-%m-%d %H:%i:%s') as time_real,s.name as source_name,t.remarks,t.tel,t.on,t.type_fw,tel_encrypt,t.ip,q.cname,a.qz_area")
            ->limit($pageIndex . "," . $pageCount)->order("time_real desc")
            ->buildSql();
        $buildSql = M("orders")->table($buildSql)->alias("t")
            ->join("left join qz_orders o on o.tel_encrypt = t.tel_encrypt")
            ->field("t.*,count(t.id) as tel_count")
            ->group("t.id")
            ->buildSql();
        return M("orders")->table($buildSql)->alias("t1")
            ->join("left join qz_orders o on o.ip = t1.ip")
            ->field("t1.*,count(t1.id) as ip_count")
            ->group("t1.id")->select();
    }
    /*
     * 删除黑名单订单
     */
    public function delAllBlack($order_id)
    {
        $map = array(
            "id" => array("IN",$order_id)
        );
        return M("orders_black")->where($map)->delete();
    }
    /**
     * 保存订单分配个数
     * @param $save
     * @return bool
     */
    public function saveOrderAllotNum($save)
    {
        return M('order_allot_num')->add($save);
    }
    /**
     * 保存订单分配个数
     * @param $save
     * @return bool
     */
    public function updateOrderAllotNum($where,$save)
    {
        return M('order_allot_num')->where($where)->save($save);
    }

    /**
     * 获取订单分配个数
     * @param $order_id
     * @return mixed
     */
    public function getOrderAllotNum($order_id)
    {
        return M('order_allot_num')->where(['order_id' => ['eq',$order_id]])->find();
    }

    /**
     * 获取抢单撤回 个数
     * @param  [type] $kf       [客服ID]
     * @param  [type] $id       [订单ID]
     * @param  [type] $cs       [管辖城市]
     * @param  [type] $other_cs [城市ID]
     * @param  [type] $status   [订单状态]
     * @param  [type] $begin    [开始时间]
     * @param  [type] $end      [结束时间]
     * @param  [type] $time_diff [对接时间差]
     * @return array
     */
    public function getOrderRobListCount($kf, $id, $other_cs, $status, $begin, $end,$isactivity,$ids,$source,$rob_type = [],$qx)
    {
        $map = array(
            "o.on" => array("EQ",4),
            "orp.is_rob" => array("EQ",1),
            "o.type_fw" => array("in",array(1,2))
        );


        if (!empty($other_cs)) {
            $map["o.cs"] = array("in",$other_cs);
        }

        if (!empty($qx)) {
            $map["o.qx"] = array("eq",$qx);
        }

        if (!empty($status)) {
            $map["o.type_fw"] = array("EQ",$status);
        }

        if (!empty($id)) {
            $map["_complex"] =array(
                "o.id" => array("EQ",$id),
                "o.xiaoqu" => array("LIKE","%$id%"),
                "_logic" => "OR"
            );
        }

        if (!empty($kf)) {
            $map["a.op_uid"] = array("EQ",$kf);
        }

        if (!empty($begin) && !empty($end)) {
            $map["orp.add_time"] = array(
                array("EGT",strtotime($begin)),
                array("ELT",strtotime($end))
            );
        }

        if (!empty($isactivity)) {
            if (count($ids) > 0) {
                if ($isactivity == 1) {
                    $map['o.source'] = array("IN",$ids);
                } else {
                    $map['o.source'] = array("NOT IN",$ids);
                }
            }
        }

        if ($source != "") {
            $map["o.source_in"] = array("EQ",$source);
        }

        if (!empty($order_type)) {
            $map["orp.order_status"] = ['eq',$order_type];
        }
        return M("order_docking")->where($map)->alias("a")
            ->join("join qz_orders o on a.order_id = o.id")
            ->join("join qz_order_csos_new new on new.order_id = o.id")
            ->join("JOIN qz_order_rob_pool orp ON orp.order_id = o.id")
            ->join("join qz_quyu q on q.cid = o.cs")
            ->join("join qz_area area on area.qz_areaid = o.qx")
            ->field("orp.id rob_id")
            ->count();
    }

    /**
     * 获取已对接订单信息
     * @param  [type] $kf       [客服ID]
     * @param  [type] $id       [订单ID]
     * @param  [type] $other_cs [城市ID]
     * @param  [type] $status   [订单状态]
     * @param  [type] $begin    [开始时间]
     * @param  [type] $end      [结束时间]
     * @param  [type] $time_diff [对接时间差]
     * @return array
     */
    public function getOrderRobList($kf, $id, $other_cs, $status, $begin, $end,$pageIndex, $pageCount,$isactivity,$ids,$source,$rob_type = [],$qx)
    {
        $map = array(
            "o.on" => array("EQ",4),
            "orp.is_rob" => array("EQ",1),
            "o.type_fw" => array("NEQ",0)
        );

        if (!empty($other_cs)) {
            $map["o.cs"] = array("in",$other_cs);
        }

        if (!empty($qx)) {
            $map["o.qx"] = array("eq",$qx);
        }

        if (!empty($status)) {
            $map["o.type_fw"] = array("EQ",$status);
        }

        if (!empty($id)) {
            $map["_complex"] =array(
                "o.id" => array("EQ",$id),
                "o.xiaoqu" => array("LIKE","%$id%"),
                "_logic" => "OR"
            );
        }

        if (!empty($begin) && !empty($end)) {
            $map["orp.add_time"] = array(
                array("EGT",strtotime($begin)),
                array("ELT",strtotime($end))
            );
        }

        if (!empty($kf)) {
            $map["a.op_uid"] = array("EQ",$kf);
        }

        if (!empty($isactivity)) {
            if (count($ids) > 0) {
                if ($isactivity == 1) {
                    $map['o.source'] = array("IN",$ids);
                } else {
                    $map['o.source'] = array("NOT IN",$ids);
                }
            }
        }

        if ($source != "") {
            $map["o.source_in"] = array("EQ",$source);
        }

        if (!empty($rob_type)) {
            $map["orp.order_status"] = ['eq',$rob_type];
        }

        $order = "orp.add_time desc";
        $buildSql = M("order_docking")->where($map)->alias("a")
            ->join("join qz_orders o on a.order_id = o.id")
            ->join("JOIN qz_order_rob_pool orp ON orp.order_id = o.id")
            ->join("join qz_order_csos_new new on new.order_id = o.id")
            ->join("join qz_quyu q on q.cid = o.cs")
            ->join("join qz_area area on area.qz_areaid = o.qx")
            ->join("left join qz_order_allot_num an on an.order_id = o.id")
            ->join("left join qz_jiage jiage on jiage.id = o.yusuan")
            ->field("a.op_uname,o.source_in, o.id,o.time_real,o.name,o.lftime,jiage.name yusuan,o.remarks,q.cname,area.qz_area,o.wzd,o.mianji,o.xiaoqu,o.tel,o.on,o.type_fw,a.time,FROM_UNIXTIME(new.lasttime),o.source,if(an.allot_num IS NOT NULL,an.allot_num,4) as allot_num,orp.id rob_id,orp.add_time rob_time,orp.rob_num,orp.order_status")
            ->order($order)
            ->limit($pageIndex.",".$pageCount)
            ->buildSql();
        return M()->table($buildSql)->alias('t1')
            ->field('t1.*,count(if(i.allot_source = 3,1,null)) already_allot_num')
            ->join('left join qz_order_info i on i.order = t1.id')
            ->group('t1.id')
            ->order('t1.rob_time desc')
            ->select();
    }

    /**
     * 根据订单编号获取订单信息
     * @return [type] [description]
     */
    public function getOrderInfoById($id){
        $map = array(
            "a.id"=>array("EQ",$id)
        );
        return M("orders")->where($map)->alias("a")
            ->join("LEFT JOIN qz_quyu as q on q.cid = a.cs")
            ->join("LEFT JOIN qz_area as b on b.qz_areaid = a.qx")
            ->join("LEFT JOIN qz_jiage as c on c.id = a.yusuan")
            ->join("LEFT JOIN qz_huxing as d on d.id  = a.huxing")
            ->join("LEFT JOIN qz_fengge as f on f.id  = a.fengge")
            ->join("LEFT JOIN qz_fangshi as g on g.id  = a.fangshi")
            ->join("LEFT JOIN safe_order_tel8 as h on h.orderid  = a.id")
            ->field("a.*,a.fengge as fg, b.qz_area,c.name as yusuan,d.name as hxname,f.name as fengge,g.name as fangshi,h.tel8 as tel8,q.cname")
            ->find();
    }

    public function getOrderTelDetails($order_id)
    {
        $map = array(
            "o.id" => array("EQ",$order_id)
        );
        return M("orders")->where($map)->alias("o")
            ->join("join qz_orders o1 on o1.tel_encrypt = o.tel_encrypt")
            ->join("join qz_quyu q on q.cid = o1.cs")
            ->join("join qz_area area on area.qz_areaid = o1.qx")
            ->field("o1.tel,o1.id,o1.time_real,o1.name,o1.ip,q.cname,area.qz_area,o1.on,o1.type_fw")
            ->order("o1.time_real desc")->select();
    }

    /**
     * 将家具订单手机号转到装修订单中
     * @param $order_id
     * @param $jiaju_id
     */
    public function saveOrderTelByJiaju($order_id,$jiaju_id){
        //获取家居订单手机号
        $tel = M()->table('safe_order_tel8')->where(['orderid' => ['eq', $jiaju_id]])->find();
        if ($tel) {
            $save = [
                'orderid' => $order_id,
                'tel8' => $tel['tel8'],
            ];
            M()->table('safe_order_tel8')->add($save);
        }
    }

    //获取家居转装修客服订单
    public function getZhuangxiuOrder($where){
        //开始结束时间
        if(!empty($where['start'])){
            $map["c.lasttime"] = ["between",[strtotime($where['start']),strtotime($where['start'])+30*24*60*60]];
        }
        if(!empty($where['end'])){
            $start = strtotime($where['end'])-30*24*60*60;
            $where['end'] = date('Y-m-d 23:59:59',strtotime($where['end']));
            $map["c.lasttime"]  = ["between",[$start,strtotime($where['end'])]];
        }
        if(!empty($where['start']) && !empty($where['end'])){
            $where['end'] = date('Y-m-d 23:59:59',strtotime($where['end']));
            $map["c.lasttime"] = ["between",[strtotime($where['start']),strtotime($where['end'])]];
        }

        //客服组
        if(isset($where['kfgroup'])&& ($where['kfgroup'] !='')){
            $map['a.kfgroup'] = ['EQ',$where['kfgroup']];
        }

        //客服人员
        if(!empty($where['kf_id'])){
            $map['a.id'] = ['EQ',$where['kf_id']];
        }

        $buildSql = M()->table('qz_jiaju_order_csos_new')->alias('c')
            ->field('a.name as kf_name,a.id as kf_id,
            if(o.type_fw = 1,1,0) as fen,
            if(o.type_fw = 2,1,0) as zeng,
            a.kfgroup')
            ->join('qz_jiaju_order o on c.order_id = o.id and o.on = 4 and o.type_fw in (1,2) and type = 2 and o.order_id is not null')
            ->join('qz_adminuser a on a.id = c.user_id and a.uid in (2,31) and a.stat = 1 and a.state = 1 ')//
            ->where($map)
            ->buildSql();

        $result = M()->table($buildSql)->alias('t1')
            ->field('t1.kf_id,t1.kf_name,t1.kfgroup,sum(t1.fen) as fen_count,sum(t1.zeng) as zeng_count')
            ->group('t1.kf_id')
            ->order('fen_count desc')
            ->select();

        return $result;

    }

    /**
     * 根据公司id获取签单信息
     * @param $comid
     */
    public function getQianDanInfoByComid($comid,$startime,$endtime)
    {
        $map = [];
        $map['o.qiandan_companyid'] = $comid;

        $map['i.com'] = $comid;
        $map['i.addtime'] = array('BETWEEN', array($startime, $endtime +86400-1));

        return M('orders')->alias('o')->where($map)
            ->join('qz_order_info i on i.order = o.id and i.com = o.qiandan_companyid')
            ->field("count(o.qiandan_companyid) as qiandancount,sum(o.qiandan_jine) as qiandanjine")
            ->find();

    }

    /**
     * 获取未接通订单
     *
     * @return mixed
     */
    public function getOrderUncallCount($ordermap = [],$poolmap = [])
    {
        $table  = M('orders')->where($ordermap)->buildSql();
        return M('orders')->table($table)->alias('o')
            ->join('JOIN qz_order_pool as p ON p.orderid  = o.id')
            ->join('JOIN safe_order_tel8 as s ON s.orderid  = o.id')
            ->join('JOIN qz_quyu q ON q.cid = o.cs')
            ->join('JOIN qz_area area ON area.qz_areaid = o.qx')
            ->where($poolmap)
            ->count('o.id');
    }

    /**
     * 获取未接通订单
     *
     * @return mixed
     */
    public function getOrderUncallList($ordermap = [],$poolmap = [], $page = 1, $pageSize = 20)
    {
        $table  = M('orders')->where($ordermap)->buildSql();
        return M('orders')->table($table)->alias('o')
            ->join('JOIN qz_order_pool as p ON p.orderid  = o.id')
            ->join('JOIN safe_order_tel8 as s ON s.orderid  = o.id')
            ->join('LEFT JOIN qz_quyu q ON q.cid = o.cs')
            ->join('LEFT JOIN qz_area area ON area.qz_areaid = o.qx')
            ->where($poolmap)
            ->field('o.time,o.id,o.lasttime,q.cname,area.qz_area as area,s.tel8 as tel,o.on,o.on_sub,o.type_fw,p.op_uid,p.op_name')
            ->order('o.time_real desc')
            ->page($page, $pageSize)
            ->select();
    }


    /**
     * 获取所有未接通订单
     *
     * @return mixed
     */
    public function getOrderUncallListNoPage($ordermap = [],$poolmap = [])
    {
        $table  = M('orders')->where($ordermap)->buildSql();
        return M('orders')->table($table)->alias('o')
            ->join('JOIN qz_order_pool as p ON p.orderid  = o.id')
            ->join('JOIN safe_order_tel8 as s ON s.orderid  = o.id')
            ->join('LEFT JOIN qz_quyu q ON q.cid = o.cs')
            ->join('LEFT JOIN qz_area area ON area.qz_areaid = o.qx')
            ->where($poolmap)
            ->field('o.time,o.id,o.lasttime,q.cname,area.qz_area as area,s.tel8 as tel,o.on,o.on_sub,o.type_fw,p.op_uid,p.op_name')
            ->order('o.time_real desc')
            ->select();
    }


    /**
     * 获取所有未接通订单
     *
     * @return mixed
     */
    public function getUnfenCountByUser($map)
    {
        return M('orders')->alias('o')
            ->where($map)
            ->join('qz_order_pool p on p.orderid = o.id and p.status=0')
            ->count();
    }

    public function getHollyTelStat($id, $group, $monthStart, $monthEnd, $manager, $userids) {
        $map = array(
            "a.`uid`" => array("EQ", 2),
            "a.`stat`" => array("EQ", 1),
            "a.`state`" => array("EQ", 1)
        );

        if (!empty($id)) {
            $map["a.id"] = array("EQ", $id);
        }

        if (!empty($group)) {
            $map["a.kfGroup"] = array("EQ", $group);
        }

        if (!empty($manager)) {
            $map["a.kfmanager"] = array("EQ", $manager);
        }

        if (!empty($userids)) {
            // $userids = implode(",", $userids);
            $map["a.kfmanager"] = array("IN", $userids);
        }

        return M("adminuser")->alias("a")->where($map)
            ->join("left join qz_log_holly_order_telcenter b on b.op_uid = a.id and b.time >= $monthStart and b.time < $monthEnd")
            ->join("left join qz_log_holly_telcenter c on b.call_sid = c.call_sid and c.createtime >= $monthStart and c.createtime < $monthEnd")
            ->field([
                "a.id", "a.`name`",
                "count(IF(c.call_sid is not null ,1,null)) as count",
                "SUM(if(c.call_time_length > 0,c.call_time_length,0)) as sum_time",
                "count(IF(c.call_sid is not null and c.call_time_length > 0 and (c.call_state in ('Hangup','Unlink') and c.hanguper in ('caller','callee')),1,null)) as tel_count"
            ])
            ->group("a.id")
            ->select();
    }

    public function getHollyReviewTelStat($id, $group, $monthStart, $monthEnd, $manager, $userids) {
        $map = array(
            "a.`uid`" => array("EQ", 2),
            "a.`stat`" => array("EQ", 1),
            "a.`state`" => array("EQ", 1)
        );

        if (!empty($id)) {
            $map["a.id"] = array("EQ", $id);
        }

        if (!empty($group)) {
            $map["a.kfGroup"] = array("EQ", $group);
        }

        if (!empty($manager)) {
            $map["a.kfmanager"] = array("EQ", $manager);
        }

        if (!empty($userids)) {
            // $userids = implode(",", $userids);
            $map["a.kfmanager"] = array("IN", $userids);
        }

        return M("adminuser")->alias("a")->where($map)
            ->join("left join qz_log_holly_review_telcenter b on b.op_uid = a.id and b.time >= $monthStart and b.time < $monthEnd")
            ->join("left join qz_log_holly_telcenter c on b.call_sid = c.call_sid and c.createtime >= $monthStart and c.createtime < $monthEnd")
            ->field([
                "a.id", "a.`name`",
                "count(IF(c.call_sid is not null ,1,null)) as count",
                "SUM(if(c.call_time_length > 0,c.call_time_length,0)) as sum_time",
                "count(IF(c.call_sid is not null and c.call_time_length > 0 and (c.call_state in ('Hangup','Unlink') and c.hanguper in ('caller','callee')),1,null)) as tel_count"
            ])
            ->group("a.id")
            ->select();
    }


    public function getOrderHistoryCount($begin,$end)
    {
        $map = array(
            "u.on" => array("EQ",2),
            "b.fake" => array("EQ",0)
        );

        $nextSql = M("user")->where($map)->alias("u")
                            ->join("join qz_user_company b on u.id = b.userid")
                            ->field("u.cs")->group("u.cs")->buildSql();

        $map = array(
            "o.time_real" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            "o.on" => array("EQ",0),
            "o.on_sub" => array("EQ",10)
        );
         return $this->where($map)->alias("o")
             ->join("join ($nextSql) as t on o.cs = t.cs")
             ->join("join qz_order_pool p on p.orderid = o.id and p.op_uid = 0 and p.status = 1")
             ->count();
    }

    public function getAllLiangFangList($id,$group,$manager,$monthStart,$monthEnd, $userids)
    {
        $where[] = array(
            "a.lf_time" => array(
                array("EGT", $monthStart),
                array("ELT", $monthEnd)
            ),
            "a.`status`" => array("IN", "1,2")
        );
        $where[] = array(
            "o.qiandan_chktime" => array(
                array("EGT", $monthStart),
                array("ELT", $monthEnd)
            ),
            "a.`status`" => array("EQ", 4)
        );

        $where["_logic"] = "OR";
        $map["_complex"] = $where;

        // $map = array(
        //     "a.lf_time" => array(
        //         array("EGT", $monthStart),
        //         array("ELT", $monthEnd)
        //     ),
        //    "a.status" => array("IN","1,2,4")
        // );

        $buildSql = M("order_company_review")->where($map)->alias("a")
            ->join("join qz_orders o on o.id = a.orderid")
            ->field("orderid,IF(a.status = 4, from_unixtime(o.qiandan_chktime,'%Y-%m-%d'), from_unixtime(a.lf_time,'%Y-%m-%d')) as date")
            ->group("orderid")
            ->buildSql();

        $map = [];
        if (!empty($id)) {
            $map["u.id"] = array("EQ", $id);
        }

        if (!empty($group)) {
            $map["u.kfgroup"] = array("EQ", $group);
        }

        if (!empty($manager)) {
            $map["_string"] = "find_in_set($manager,u.kfmanager)";
            // $map["u.id"] = array("EQ", $manager);
        }

        if (!empty($userids)) {
            $map["new.user_id"] = array("IN", $userids);
        }

        return M()->table($buildSql)->alias("t")
            ->join("join qz_order_csos_new new on new.order_id = t.orderid")
            ->join("join qz_adminuser u on u.id = new.user_id")
            ->field("u.id,count(t.orderid) as lf_count,u.kfgroup, substring_index(u.kfmanager,',',1) as kfmanager,t.date")
            ->group("u.id")->select();
    }

    /**
     * 获取月订单达标会员公司
     * @param  [type] $company_ids [description]
     * @return [type]              [description]
     */
    public function getOrderMonthReachCompany($company_ids){
        $subMap = array(
            "o.`on`" => array("EQ", 4),
            "i.type_fw" => array("EQ", 1),
            "i.addtime" => array("BETWEEN", [
                strtotime(date("Y-m-01")), strtotime(date("Y-m-t"))
            ])
        );

        $subSql = M("orders")->alias("o")->where($subMap)
            ->join("inner join qz_order_info as i on o.id = i.`order`")
            ->field("i.com,count(i.com) as count_order")
            ->group("i.com")
            ->buildSql();

        $map = array(
            "u.`on`" => array("EQ", 2),
            "c.fake" => array("EQ", 0),
            "c.cooperation_type" => array("EQ", 1),
            "q.little" => array("IN", [0, 1, 2]),
            "q.is_open_city" => array("EQ", 1),
            "u.id" => array("IN", $company_ids)
        );

        return M("user")->alias("u")->where($map)
            ->join("inner join qz_user_company as c on u.id = c.userid")
            ->join("inner join qz_quyu as q on q.cid = u.cs")
            ->join("inner join $subSql as t on t.com = u.id")
            ->field("u.id,t.count_order,(CASE q.little WHEN 0 THEN 20 * c.viptype WHEN 1 THEN 12 * c.viptype WHEN 2 THEN 7 * c.viptype END ) AS fennum")
            ->having("count_order >= fennum")
            ->select();
    }

    public function getQiandanFailListCount($order_id)
    {
        $map = array(
            "type" => array("EQ","fenpei"),
            "action" => array("EQ","qd_chkc")
        );
        if (!empty($order_id)) {
            $map["orderid"] = array("EQ",$order_id);
        }
        return M("log_qiandanchk")->where($map)->count();
    }

    public function getQiandanFailList($order_id,$pageIndex,$pageCount)
    {
        $map = array(
            "type" => array("EQ","fenpei"),
            "action" => array("EQ","qd_chkc")
        );
        if (!empty($order_id)) {
            $map["orderid"] = array("EQ",$order_id);
        }
        return M("log_qiandanchk")->where($map)->order("id desc")->limit($pageIndex,$pageCount)
            ->select();
    }

    public function getInfoByOrderId($order_id, $field = "*"){
        $map = array(
            "id" => array("EQ", $order_id)
        );
        return M("orders")->where($map)->field($field)->find();
    }

    public function getPolicyOrderInfo($order_id)
    {
        $map = array(
            "a.id" => array("EQ", $order_id),
        );

        return $this->where($map)->alias("a")
             ->join("join qz_order_info i on i.order = a.id")
             ->join("join qz_user u on u.id = i.com")
             ->join("left join qz_quyu q on q.cid = a.cs")
             ->join("left join qz_huxing h on h.id = a.huxing")
             ->join("left join qz_fengge f on f.id = a.fengge")
             ->join("left join qz_area area on area.qz_areaid = a.qx")
             ->join("left join qz_user u1 on u1.id = a.qiandan_companyid")
             ->field("a.lx,a.lxs, a.id,area.qz_area,a.qiandan_companyid,a.qiandan_status,a.qiandan_jine,u1.jc as qiandan_company,a.qiandan_status,a.lftime,a.wx,a.id,a.time_real,a.ip,a.tel,a.xiaoqu,a.dz,q.cname,a.name,h.name as huxing,f.name as fengge,a.start,a.text,GROUP_CONCAT(u.jc) as company")
             ->group("a.id")
             ->find();
    }

    public function getPolicyOrderLfInfo($order_id,$company_id)
    {
        $map = array(
            "comid" => array("EQ", $company_id),
            "orderid" => array("EQ",$order_id)
        );

        return M("order_company_review")->where($map)->field("status")->find();
    }

    /**
     * @des:通过合作商账获取合作商统计
     * @param $parent_id
     * @param $begin
     * @param $end
     * @return mixed
     */
    public function getHzsAnalysisByHzsParent($parent_id, $begin, $end)
    {
        $map['s.status'] = array("EQ",1);
        $map['c.classify'] = array("EQ",1);
        $map['c.status'] = array("EQ",1);
        $map['s.status'] = array("EQ", 1);
        $map['c.id'] = $parent_id;

        $sub = $this->table('qz_hzs_company')->alias("c")->where($map)
            ->join("left join qz_hzs_company hc on hc.parent_id=c.id")
            ->join("left join qz_hzs_source s on s.companyid=hc.id")
            ->field(["s.sourceid"])
            ->group("s.sourceid")
            ->buildSql();
        $mapOrder['o.time_real'] = array("between", [$begin, $end]);
        return $this->table("qz_orders")->alias("o")->where($mapOrder)
            ->where('sr.id in'.$sub)
            ->join('inner join qz_orders_source s on s.orderid=o.id')
            ->join('inner join qz_order_source sr on sr.id=s.source_src_id')
            ->field(["count(o.id) as fadan_count","count(if(o.on=4 and o.type_fw=1,1,null)) as fendan_count"])
            ->find();
    }

    //获取有效发单量
    public function getHzsAnalysisFaByHzsParent($parent_id, $begin, $end)
    {
        $map['s.status'] = array("EQ",1);
        $map['c.classify'] = array("EQ",1);
        $map['c.status'] = array("EQ",1);
        $map['s.status'] = array("EQ", 1);
        $map['c.id'] = $parent_id;

        $sub = $this->table('qz_hzs_company')->alias("c")->where($map)
            ->join("left join qz_hzs_company hc on hc.parent_id=c.id")
            ->join("left join qz_hzs_source s on s.companyid=hc.id")
            ->field(["s.sourceid"])
            ->group("s.sourceid")
            ->buildSql();
        $mapOrder['o.time_real'] = array("between", [$begin, $end]);
        $mapOrder['o.on'] = array("EQ", 4);
        $mapOrder['o.type_fw'] = array("EQ", 1);
        return $this->table("qz_orders")->alias("o")->where($mapOrder)
            ->where('sr.id in'.$sub)
            ->join('inner join qz_order_csos_new cs on o.id=cs.order_id')
            ->join('inner join qz_orders_source s on s.orderid=o.id')
            ->join('inner join qz_order_source sr on sr.id=s.source_src_id')
            ->count();
    }


    //获取实际分单
    public function getHzsAnalysisByHzsParentRealFenDan($parent_id, $begin, $end)
    {
        $map['s.status'] = array("EQ",1);
        $map['c.classify'] = array("EQ",1);
        $map['c.status'] = array("EQ",1);
        $map['c.id'] = $parent_id;

        $sub = $this->table('qz_hzs_company')->alias("c")->where($map)
            ->join("left join qz_hzs_company hc on hc.parent_id=c.id")
            ->join("left join qz_hzs_source s on s.companyid=hc.id")
            ->field(["s.sourceid"])
            ->group("s.sourceid")
            ->buildSql();

        $mapOrder['o.on'] = array('EQ', 4);
        $mapOrder['o.type_fw'] = array('EQ', 1);
        $mapOrder["o.is_settlement"] = array("EQ", 1);
        $mapOrder["sr.id"] = array("EXP", ' in '.$sub);
        return $this->table("qz_orders")->alias("o")->where($mapOrder)
            ->where("exists (select order_id from qz_order_csos_new where order_id=o.id and lasttime between {$begin} and {$end})")
            ->join('inner join qz_orders_source s on s.orderid=o.id')
            ->join('inner join qz_order_source sr on sr.id=s.source_src_id')
            ->field(["count(distinct(o.id)) as count"])
            ->find();
    }

    /**
     * @des:通过合作商账户获取合作商数
     * @param $id
     * @param $begin
     * @param $end
     * @return mixed
     */
    public function getHzsAnalysisByHzsId($id, $begin, $end)
    {
        $map['s.status'] = array("EQ", 1);
        $map['c.classify'] = array("EQ", 2);
        $map['c.status'] = array("EQ", 1);
        $map['c.id'] = array("EQ", $id);
        $sub = $this->table('qz_hzs_company')->alias("c")->where($map)
            ->join("left join qz_hzs_source s on s.companyid=c.id")
            ->field(["s.sourceid"])
            ->buildSql();

        $mapOrder['o.time_real'] = array("between", [$begin, $end]);
        return $this->table("qz_orders")->alias("o")->where($mapOrder)
            ->where('sr.id in'.$sub)
            ->join('inner join qz_orders_source s on s.orderid=o.id')
            ->join('inner join qz_order_source sr on sr.id=s.source_src_id')
            ->field(["count(o.id) as fadan_count","count(if(o.on=4 and o.type_fw=1,1,null)) as fendan_count"])
            ->find();
    }

    //合作商账户获取发单数
    public function getHzsAnalysisFaByHzsId($id, $begin, $end)
    {
        $map['c.classify'] = array("EQ", 2);
        $map['c.status'] = array("EQ", 1);
        $map['c.id'] = array("EQ", $id);
        $sub = $this->table('qz_hzs_company')->alias("c")->where($map)
            ->join("left join qz_hzs_source s on s.companyid=c.id")
            ->field(["s.sourceid"])
            ->buildSql();

        $mapOrder['o.time_real'] = array("between", [$begin, $end]);
        $mapOrder['o.on'] = array('EQ', 4);
        $mapOrder['o.type_fw'] = array('EQ', 1);
        return $this->table("qz_orders")->alias("o")->where($mapOrder)
            ->where('sr.id in'.$sub)
            ->join('inner join qz_order_csos_new cs on o.id=cs.order_id')
            ->join('inner join qz_orders_source s on s.orderid=o.id')
            ->join('inner join qz_order_source sr on sr.id=s.source_src_id')
            ->count();
    }

    //合作商账户，实际分单
    public function getHzsAnalysisByHzsIdRealFenDan($id, $begin, $end)
    {
        $map['s.status'] = array("EQ", 1);
        $map['c.classify'] = array("EQ", 2);
        $map['c.status'] = array("EQ", 1);
        $map['c.id'] = array("EQ", $id);
        $sub = $this->table('qz_hzs_company')->alias("c")->where($map)
            ->join("left join qz_hzs_source s on s.companyid=c.id")
            ->field(["s.sourceid"])
            ->buildSql();

        $mapOrder['o.on'] = array('EQ', 4);
        $mapOrder['o.type_fw'] = array('EQ', 1);
        $mapOrder["o.is_settlement"] = array("EQ", 1);
        $mapOrder["sr.id"] = array("EXP", ' in '.$sub);
        return $this->table("qz_orders")->alias("o")->where($mapOrder)
            ->where("exists (select order_id from qz_order_csos_new where order_id=o.id and lasttime between {$begin} and {$end})")
            ->join('inner join qz_orders_source s on s.orderid=o.id')
            ->join('inner join qz_order_source sr on sr.id=s.source_src_id')
            ->field(["count(o.id) as count"])
            ->find();
    }

    /**
     * @des:通过渠道来源统计
     * @param $source_src_id
     * @param $begin
     * @param $end
     * @return mixed
     */
    public function getHzsAnalysisBySource($source_src_id, $begin, $end)
    {
        $map['o.time_real'] = array("between", [$begin, $end]);
        $map['s.source_src_id'] = array("EQ", $source_src_id);
        $subSql = $this->table("qz_orders")->alias("o")->where($map)
            ->join('inner join qz_orders_source s on s.orderid=o.id')
            ->join('inner join qz_order_source sr on sr.id=s.source_src_id')
            ->field("o.*")
            ->group("o.id")
            ->buildSql();
        return $this->table($subSql)->alias('a')
            ->field(["count(distinct(id)) as fadan_count,count(if(`on`=4 and type_fw=1,1,null)) as fendan_count"])
            ->find();
    }

    public function getHzsAnalysisFaBySource($source_src_id, $begin, $end)
    {
        $map['o.time_real'] = array("between", [$begin, $end]);
        $map['s.source_src_id'] = array("EQ", $source_src_id);
        $subSql = $this->table("qz_orders")->alias("o")->where($map)
            ->join('inner join qz_order_csos_new cs on o.id=cs.order_id')
            ->join('inner join qz_orders_source s on s.orderid=o.id')
            ->join('inner join qz_order_source sr on sr.id=s.source_src_id')
            ->field("o.*")
            ->group("o.id")
            ->buildSql();
        return $this->table($subSql)->alias('a')
            ->field(["count(distinct(id)) as fadan_count,count(if(`on`=4 and type_fw=1,1,null)) as fendan_count"])
            ->find();
    }

    //按渠道名称实际分单
    public function getHzsAnalysisBySourceRealFenDan($source_src_id,$begin,$end)
    {
        $map['s.source_src_id'] = array("EQ", $source_src_id);
        $map['o.on'] = array("EQ", 4);
        $map['o.type_fw'] = array("EQ", 1);
        return $this->table("qz_orders")->alias("o")->where($map)
            ->where("exists (select order_id from qz_order_csos_new where order_id=o.id and lasttime between {$begin} and {$end})")
            ->join('inner join qz_orders_source s on s.orderid=o.id')
            ->join('inner join qz_order_source sr on sr.id=s.source_src_id')
            ->field(["count(o.id) as count"])
            ->find();
    }

    /**
     * @des:根据合作商账户，获取订单详情
     * @param $parent_id
     * @param $account
     * @param $source
     * @param $begin
     * @param $end
     * @param $offset
     * @param $limit
     * @return mixed
     */
    public function getHzsAnalysisOrderInfoByHzsParent($parent_id, $account, $source, $begin, $end, $offset, $limit)
    {
        $map['a.classify'] = 1;
        $map['s.status'] = 1;
        if (!empty($parent_id)) {
            $map['a.id'] = array("in", $parent_id);
        }
        if (!empty($account)) {
            $map['hc.id'] = array("EQ", $account);
        }
        if (!empty($source)) {
            $map['o.id'] = array("EQ", $source);
        }
        $subSql = $this->table('qz_hzs_company')->alias("a")->where($map)
            ->join("left join qz_hzs_company hc on hc.parent_id=a.id")
            ->join("left join qz_hzs_source s on s.companyid=hc.id")
            ->join('left join qz_order_source o on o.id = s.sourceid')
            ->field("o.*")
            ->group("o.id")
            ->buildSql();
        $mapOrder['o.time_real'] = array("between", [$begin, $end]);

        return  $this->table("qz_orders")->alias("o")->where($mapOrder)
            ->join('inner join qz_orders_source s on s.orderid=o.id')
            ->join("inner join ".$subSql .' as t on t.id=s.source_src_id')
            ->join("inner join qz_quyu q on q.cid=o.cs")
            ->field("o.id,o.time_real,t.name,o.remarks,o.tel,q.cname,(CASE o.`on` WHEN 0 THEN
                    ( CASE o.on_sub WHEN 8 THEN '扫单' WHEN 9 THEN '次新单' WHEN 10 THEN '新单' ELSE '未审核(子状态未知)' END ) 
                    WHEN 2 THEN '待定' WHEN 4 THEN 
                    (CASE o.type_fw WHEN 0 THEN '有效未分配' WHEN 1 THEN '分单' WHEN 2 THEN '赠单' WHEN 3 THEN '分没人跟' WHEN 4 THEN '赠没人跟' WHEN 5 THEN '有效(待分配)' WHEN 6 THEN '有效(待分配)' ELSE '有效(子状态未知)' END ) WHEN 5 THEN '无效' WHEN 6 THEN '无效(空号)' WHEN 7 THEN '无效(装修公司)' WHEN 8 THEN '无效(无效咨询)' WHEN 97 THEN '压单' WHEN 98 THEN '暂时无效' WHEN 99 THEN '撤回单' ELSE '未知状态' END ) 
                    AS `on`")
            ->limit($offset,$limit)
            ->select();
    }

    //根据合作商账户渠道來源获取订单详情总数
    public function getHzsAnalysisOrderInfoByHzsParentCount($parent_id, $account, $source, $begin, $end)
    {
        $map['a.classify'] = 1;
        $map['s.status'] = 1;
        if (!empty($parent_id)) {
            $map['a.id'] = array("in", $parent_id);
        }
        if (!empty($account)) {
            $map['hc.id'] = array("EQ", $account);
        }
        if(!empty($source)){
            $map['o.id'] = array("EQ",$source);
        }
        $subSql = $this->table('qz_hzs_company')->alias("a")->where($map)
            ->join("left join qz_hzs_company hc on hc.parent_id=a.id")
            ->join("left join qz_hzs_source s on s.companyid=hc.id")
            ->join('left join qz_order_source o on o.id = s.sourceid')
            ->field("o.*")
            ->group("o.id")
            ->buildSql();
        $mapOrder['o.time_real'] = array("between", [$begin, $end]);

        return  $this->table("qz_orders")->alias("o")->where($mapOrder)
            ->join('inner join qz_orders_source s on s.orderid=o.id')
            ->join("inner join ".$subSql .' as t on t.id=s.source_src_id')
            ->count();
    }

    /**
     * @des:通过合作商统计uv
     * @param $parent_id
     * @param $begin
     * @param $end
     * @return mixed
     */
    public function getOrderSourceParent($parent_id, $begin, $end)
    {
        $map['s.status'] = array("EQ",1);
        $map['c.classify'] = array("EQ",1);
        $map['c.status'] = array("EQ",1);
        $map['c.id'] = $parent_id;

        $sub = $this->table('qz_hzs_company')->alias("c")->where($map)
            ->join("left join qz_hzs_company hc on hc.parent_id=c.id")
            ->join("left join qz_hzs_source s on s.companyid=hc.id")
            ->field(["s.sourceid"])
            ->group("s.sourceid")
            ->buildSql();
        $mapOrder['o.time_real'] = array("between", [$begin, $end]);
        $mapOrder['c.dates'] = array('between', [date('Ymd', $begin), date('Ymd', $end)]);
        return $this->table("qz_orders")->alias("o")->where($mapOrder)
            ->where('sr.id in'.$sub)
            ->join('inner join qz_orders_source s on s.orderid=o.id')
            ->join('inner join qz_order_source sr on sr.id=s.source_src_id')
            ->join('inner join qz_url_stats_count c on c.src=sr.src')
            ->field(["sum(c.num) as count"])
            ->find();
    }

    /**
     * @des:通过合作商账户获取uv
     * @param $id
     * @param $begin
     * @param $end
     * @return mixed
     */
    public function getOrderSourceByHzs($id, $begin, $end)
    {
        $map['s.status'] = array("EQ", 1);
        $map['c.classify'] = array("EQ", 2);
        $map['c.status'] = array("EQ", 1);
        $map['c.id'] = array("EQ", $id);
        $sub = $this->table('qz_hzs_company')->alias("c")->where($map)
            ->join("left join qz_hzs_source s on s.companyid=c.id")
            ->field(["s.sourceid"])
            ->buildSql();

        $mapOrder['o.time_real'] = array("between", [$begin, $end]);
        $mapOrder['c.dates'] = array('between', [date('Ymd', $begin), date('Ymd', $end)]);
        return $this->table("qz_orders")->alias("o")->where($mapOrder)
            ->where('sr.id in'.$sub)
            ->join('inner join qz_orders_source s on s.orderid=o.id')
            ->join('inner join qz_order_source sr on sr.id=s.source_src_id')
            ->join('inner join qz_url_stats_count c on c.src=sr.src')
            ->field(["sum(c.num) as count"])
            ->find();
    }

    /**
     * @des:通过合作商来源获取uv
     * @param $source_src_id
     * @param $begin
     * @param $end
     * @return mixed
     */
    public function getOrderSourceUvBySource($source_src_id, $begin, $end)
    {
        $map['o.time_real'] = array("between", [$begin, $end]);
        $map['s.source_src_id'] = array("EQ", $source_src_id);
        $map['c.dates'] = array('between', [date('Ymd', $begin), date('Ymd', $end)]);
        $subSql = $this->table("qz_orders")->alias("o")->where($map)
            ->join('inner join qz_orders_source s on s.orderid=o.id')
            ->join('inner join qz_order_source sr on sr.id=s.source_src_id')
            ->join('left join qz_url_stats_count c on c.src=sr.src')
            ->field("c.num")
            ->buildSql();
        return $this->table($subSql)->alias('a')
            ->field("sum(num) as count")
            ->find();
    }
}
