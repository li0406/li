<?php

namespace Home\Model;
Use Think\Model;

/**
* 渠道来源 和 订单推广 统计
*/
class OrderSourceStatsOldModel extends Model
{
    protected $autoCheckFields = false;

    //取单个
    public function getById($id){
        $map = array(
            'id' => array("EQ",$id)
        );
        return M('order_source')->field('*')->where($map)->find();
    }

    //按时间获取列表
    public function getOrderLocationList($condition){
        $buildSql = M('orders')->field('FROM_UNIXTIME(time_real,"%Y-%m-%d") AS days,`on`,source,type_fw,count(*) AS num')
                            ->where($condition)
                            ->group('days,source,`on`,type_fw')
                            ->order('days')
                            ->buildSql();

        return M("orders")->table($buildSql)->alias("t1")
                            ->field("t1.*,s.name as sourcename,s.groupid,g.name as group_name")
                            ->join("left join qz_order_source as s on s.src = t1.source and s.type = '2' ")
                            ->join("left join qz_order_source_group as g on  g.id = s.groupid ")
                            ->order('t1.days')
                            ->select();
    }

    //获取分组列表
    public function getOrderLocationGroupList($condition){
        $buildSql = M('orders')->field('`on`,source,type_fw,count(*) AS num')
                            ->where($condition)
                            ->group('source,`on`,type_fw')
                            ->order('source')
                            ->buildSql();

        return M("orders")->table($buildSql)->alias("t1")
                            ->field("t1.*,s.name as sourcename,s.groupid,g.name as group_name")
                            ->join("left join qz_order_source as s on s.src = t1.source and s.type = '2' ")
                            ->join("left join qz_order_source_group as g on  g.id = s.groupid ")
                            ->order('t1.source')
                            ->select();
    }

    //取当前时段总数据
    public function getOrderLoactionsByDay($condition){
        return M("orders")->field('FROM_UNIXTIME(time_real,"%Y-%m-%d") AS days,source,count(*) AS num')
                            ->where($condition)
                            ->group('days,source')
                            ->order('days')
                            ->select();
    }

    //取当前时段总数据 有分组数据
    public function getOrderLoactionsAllByDay($condition){
        $buildSql = M('orders')->field('FROM_UNIXTIME(time_real,"%Y-%m-%d") AS days,source,count(*) AS num')
                            ->where($condition)
                            ->group('days,source')
                            ->order('days DESC')
                            ->buildSql();

        return M("orders")->table($buildSql)->alias("t1")
                            ->field("t1.*,s.name as sourcename,s.groupid,g.name as group_name")
                            ->join("left join qz_order_source as s on s.src = t1.source and s.type = '2' ")
                            ->join("left join qz_order_source_group as g on  g.id = s.groupid ")
                            ->order('t1.days')
                            ->select();
    }

    //获取推广来源组
    public function getSourceGroup($type){
        $map['type'] = $type;
        return M('order_source_group')->alias("c")->where($map)->order('addtime desc')->select();
    }


    ///////////////////////////// 下面是推广来源 /////////////////////////////////////////

    //按时间获取列表
    public function getOrderSrcList($start = '', $end = ''){
        $map = [];
        if (!empty($start) && !empty($end)) {
            $map['o.time_real'] = array('between',array($start,$end));
        } else {
            return false;
        }

        //$map['o.source'] = array('neq',999);

        $buildSql = M('orders')->alias("o")
                        ->field('FROM_UNIXTIME(o.time_real,"%Y-%m-%d") AS days,o.`on`,s.source_src_id as source,o.type_fw,count(*) AS num,SUM(IF(v.lasttime BETWEEN '.$start.' AND '.$end.' ,1,0)) AS current_num')
                        ->join("LEFT JOIN qz_orders_source as s on s.orderid = o.id  ")
                        ->join("LEFT JOIN qz_order_csos_new AS v ON v.order_id = o.id")
                        ->where($map)
                        ->group('days,source,`on`,type_fw')
                        ->order('days')
                        ->buildSql();

        return M("orders")->table($buildSql)->alias("t1")
                            ->field("t1.*,s.src,s.name as sourcename,s.groupid,s.dept,s.charge,g.name as group_name")
                            ->join("left join qz_order_source as s on s.id = t1.source and s.type = '1' ")
                            ->join("left join qz_order_source_group as g on g.id = s.groupid ")
                            ->order('t1.days')
                            ->select();
    }


    public function getOrderStatFromOrderCsosNew($start='',$end='',$dept){
        $map = [];
        if (!empty($start) && !empty($end)) {
            $map['z.lasttime'] = array('between',array($start,$end));
        } else {
            return false;
        }

        $map['o.source'] = array('neq',999);

        $field = 'FROM_UNIXTIME(z.lasttime,"%Y-%m-%d") AS days,o.`on`,s.source_src_id as source,o.type_fw,count(*) AS real_num,y.src,y.name as sourcename,y.groupid,y.dept,y.charge,g.name as group_name';

        $result = M('order_csos_new')->alias('z')
                                    ->field($field)
                                    ->join('qz_orders AS o ON o.id = z.order_id')
                                    ->join("LEFT JOIN qz_orders_source as s on s.orderid = o.id")
                                    ->join("left join qz_order_source as y on y.id = s.source_src_id and y.type = '1' $dept ")
                                    ->join("left join qz_order_source_group as g on g.id = y.groupid ")
                                    ->where($map)
                                    ->group('days,source,`on`,type_fw')
                                    ->order('days')
                                    ->select();

        return $result;
    }

    public function getOrderStatFromOrderCsosNewWithBack($start = '', $end = ''){
        $map = [];
        if (!empty($start) && !empty($end)) {
            $map['z.lasttime'] = array('between',array($start,$end));
        } else {
            return false;
        }

        $map['o.source'] = array('eq',999);

        $field = 'FROM_UNIXTIME(z.lasttime,"%Y-%m-%d") AS days,o.`on`,s.source_src_id as source,o.type_fw,count(*) AS real_num,y.src,y.name as sourcename,y.groupid,y.dept,y.charge,g.name as group_name,o.source as laiyuan';

        $result = M('order_csos_new')->alias('z')
                                    ->field($field)
                                    ->join('qz_orders AS o ON o.id = z.order_id')
                                    ->join("LEFT JOIN qz_orders_source as s on s.orderid = o.id")
                                    ->join("left join qz_order_source as y on y.id = s.source_src_id and y.type = '1' ")
                                    ->join("left join qz_order_source_group as g on g.id = y.groupid ")
                                    ->where($map)
                                    ->group('days,source,`on`,type_fw')
                                    ->order('days')
                                    ->select();
        return $result;
    }



    //取当前时段总数据
    public function getOrderSrcByDay($condition){
        return M("orders")->alias("o")
                        ->field('FROM_UNIXTIME(time_real,"%Y-%m-%d") AS days,s.source_src_id as source,count(*) AS num')
                        ->join("left JOIN qz_orders_source as s on s.orderid = o.id ")
                        ->where($condition)
                        ->group('days,source')
                        ->order('days')
                        ->select();
    }

    //取当前时段总数据 有分组数据
    public function getOrderSrcAllByDay($condition){
        $buildSql = M('orders')->alias("o")
                            ->field('FROM_UNIXTIME(time_real,"%Y-%m-%d") AS days,s.source_src_id as source,count(*) AS num')
                            ->join("INNER JOIN qz_orders_source as s on s.orderid = o.id  ")
                            ->where($condition)
                            ->group('days,source')
                            ->order('days DESC')
                            ->buildSql();

        return M("orders")->table($buildSql)->alias("t1")
                            ->field("t1.*,s.name as sourcename,s.groupid,g.name as group_name")
                            ->join("left join qz_order_source as s on s.src = t1.source and s.type = '1' ")
                            ->join("left join qz_order_source_group as g on  g.id = s.groupid ")
                            ->order('t1.days')
                            ->select();
    }

    //获取时间段内所有 Source 列表
    public function getAllSrcByTime($condition){

        $start_time = $condition['o.time_real']['1']['0'];
        $end_time = $condition['o.time_real']['1']['1'];

        if(!empty($start_time)){
            $map['dates'][] = array("EGT",date('Ymd',$start_time));
        }
        if(!empty($end_time)){
            $map['dates'][] = array("ELT",date('Ymd',$end_time));
        }
        return M('url_stats_count')->field('src,sum(num) AS num,sum(ip_num) AS ip_num')->where($map)->group('src')->select();
    }

    //取当前时段总UV数据 有来源分组数据
    public function getAllUVByDay($condition){

        $start_time = $condition['time_real']['1']['0'];
        $end_time = $condition['time_real']['1']['1'];

        if(!empty($start_time)){
            $map['dates'][] = array("EGT",date('Ymd',$start_time));
        }
        if(!empty($end_time)){
            $map['dates'][] = array("ELT",date('Ymd',$end_time));
        }

        $buildSql = M('url_stats_count')
                            ->field("CONCAT_WS('-',`year`,LPAD(`month`,2,'0'),LPAD(`days`,2,'0')) AS days,`src`,sum(num) AS num,sum(ip_num) AS ip_num")
                            ->where($map)
                            ->group('days,src')
                            ->order('days')
                            ->buildSql();

        return M("orders")->table($buildSql)->alias("t1")
                            ->field("t1.*,s.name as sourcename,s.groupid,g.name as group_name")
                            ->join("left join qz_order_source as s on s.src = t1.src and s.type = '1' ")
                            ->join("left join qz_order_source_group as g on  g.id = s.groupid ")
                            ->order('t1.days')
                            ->select();
    }

}