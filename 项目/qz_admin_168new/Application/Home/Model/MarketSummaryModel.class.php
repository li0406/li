<?php

namespace Home\Model;

Use Think\Model;

class MarketSummaryModel extends Model
{

    /**
     * 获取汇总列表
     * @param  string $start_time 开始时间
     * @param  string $end_time   结束时间
     * @return array              数据集
     */
    public function getList($start_time = '', $end_time = '')
    {
        if (empty($start_time) || empty($end_time)) {
            return false;
        }
        $map = array(
            'm.time' => array(
                array('EGT', $start_time),
                array('ELT', $end_time)
            )
        );
        return M('market_summary')->alias('m')
                                  ->field('m.*, s.groupid, s.charge')
                                  ->join('LEFT JOIN qz_order_source AS s ON s.id = m.source_id')
                                  ->where($map)
                                  ->order('time ASC')
                                  ->select();
    }

    /**
     * 运营中心月订单
     * @param  [date] $begin [开始时间]
     * @param  [date] $end   [结束时间]
     * @return array
     */
    public function getCenterMonthOrder($begin,$end)
    {
        $map = array(
            "time" => array(
                array('EGT', $begin),
                array('LT', $end)
            ),
            "dept_id" => array("IN",array(1,2,6,7))
        );

        $buildSql = M('market_summary')->where($map)->field("if(dept_id != 2,1,2) as dept_id,pv,uv,order_count,order_fen_count,real_fen_count")
                                       ->buildSql();
        return M('market_summary')->table($buildSql)->alias("t")
                                  ->field("dept_id, sum(pv) as pv,sum(uv) as uv, sum(order_count) as order_count , sum(order_fen_count) as order_fen_count,sum(real_fen_count) as real_fen_count")
                                  ->group("dept_id")->select();
    }

    /**
     * 运营中心UV统计
     * @param  [date] $begin [开始时间]
     * @param  [date] $end   [结束时间]
     * @return array
     */
    public function getCenterMonthUv($begin,$end)
    {
        $map = array(
            "time" => array(
                array('EGT', $begin),
                array('LT', $end)
            ),
            "dept_id" => array("IN",array(2))
        );

        $buildSql = M('market_summary')->where($map)->field("if(dept_id != 2,1,2) as dept_id,uv,time")->buildSql();
        return M('market_summary')->table($buildSql)->alias("t")->field("dept_id,sum(uv) as uv,time")
                                  ->group("time")->select();
    }

    /**
     * 统计各部门付费免费分单量
     * @param  [date] $begin [开始时间]
     * @param  [date] $end   [结束时间]
     * @return array
     */
    public function getCenterMonthChannelOrder($begin,$end)
    {
        $map = array(
            "time" => array(
                array('EGT', $begin),
                array('LT', $end)
            ),
            "dept_id" => array("IN",array(1,2,6,7))
        );
        $buildSql = M('market_summary')->where($map)
                                       ->field("if(dept_id != 2,1,2) as dept_id,real_fen_count,source_id")
                                       ->buildSql();

        return M('market_summary')->table($buildSql)->alias("a")
                           ->join("left join qz_order_source b on a.source_id = b.id and b.visible = 0")
                           ->field(" dept_id, sum(real_fen_count) as real_fen_count,if(b.charge is null ,1,b.charge) as charges")
                           ->group("dept_id,charges")->select();

    }

    /**
     * 统计4大渠道当月分单量和UV量
     * @param  [date] $begin [开始时间]
     * @param  [date] $end   [结束时间]
     * @return array
     */
    public function getCenterMonthChannelOrderByGroup($begin,$end)
    {
        $map = array(
            // "b.dept" => array("IN",array(1,2,6,7)),
            "c.id" => array("IN",array(3,7,8,40)),
            "b.visible" => array("EQ",0)
        );

        return M("order_source_group")->where($map)->alias("c")
                                  ->join("LEFT JOIN qz_order_source b ON c.id = b.groupid")
                                  ->join("LEFT JOIN qz_market_summary a ON a.source_id = b.id and a.time >= '$begin' and a.time < '$end'")
                                  ->field("sum(real_fen_count) as real_fen_count,sum(uv) as uv")->find();
    }


    /**
     * 统计4大渠道分单量
     * @param  [date] $begin [开始时间]
     * @param  [date] $end   [结束时间]
     * @return array
     */
    public function getChannelOrderByGroup($begin,$end)
    {
        $map = array(
            // "b.dept" => array("IN",array(1,2,6,7)),
            "c.id" => array("IN",array(3,7,8,40)),
            "b.visible" => array("EQ",0),
            "a.source_id" => array("exp","is not null")
        );

        return M("order_source_group")->where($map)->alias("c")
                                  ->join("LEFT JOIN qz_order_source b ON c.id = b.groupid")
                                  ->join("LEFT JOIN qz_market_summary a ON a.source_id = b.id and a.time >= '$begin' and a.time < '$end'")
                                  ->field("sum(real_fen_count) as real_fen_count,sum(uv) as uv,date_format(a.time,'%Y-%m') as date")
                                  ->group("date")
                                  ->select();
    }


    /**
     * 运营中心财年分单
     * @param  [date] $begin [开始时间]
     * @param  [date] $end   [结束时间]
     * @return [type]       [description]
     */
    public function getCenterYearData($begin,$end)
    {
        $map = array(
           "time" => array(
                array('EGT', $begin),
                array('LT', $end)
            ),
            "dept_id" => array("IN",array(1,2,6,7))
        );

        $buildSql = M('market_summary')->where($map)->field("DATE_FORMAT(time,'%Y-%m') as time,real_fen_count,uv,dept_id")->buildSql();
        return M('market_summary')->table($buildSql)->alias("t")->field("time,sum(real_fen_count) real_fen_count,sum(uv) as uv, sum(if(dept_id = 2,uv,0)) as normal_uv")->group("time")->order("time")->select();

    }

    /**
     * 统计各部门付费免费渠道数据
     * @param  [date] $begin [开始时间]
     * @param  [date] $end   [结束时间]
     * @return array
     */
    public function getCenterChannelOrder($begin,$end)
    {
        $map = array(
            "a.time" => array(
                array('EGT', $begin),
                array('LT', $end)
            ),
            "a.dept_id" => array("IN",array(1,2,6,7))
        );

        $buildSql = M('market_summary')->where($map)->alias("a")
                                    ->field("if(a.dept_id != 2,1,2) as dept_id,real_fen_count,uv,source_name,order_count,order_fen_count,time,source_id")
                                    ->buildSql();
        return M('market_summary')->table($buildSql)->alias("a")
                           ->join("left join qz_order_source b on a.source_id = b.id and b.visible = 0")
                           ->field("sum(real_fen_count) as real_fen_count,if(b.charge is null ,1,b.charge) as charges,sum(uv) as uv,source_name,a.time,sum(order_count) as order_count,sum(order_fen_count) as order_fen_count,dept_id")
                           ->group("a.dept_id,source_name,charges,a.time")->select();

    }

    /**
     * 获取部门月订单数据
     * @param  [int] $dept_id [部门ID]
     * @param  [date] $begin [开始时间]
     * @param  [date] $end   [结束时间]
     * @return array
     */
    public function getDeptMonthOrder($dept_id,$begin,$end)
    {
         $map = array(
            "a.time" => array(
                array('EGT', $begin),
                array('LT', $end)
            ),
            "a.dept_id" => array("IN",$dept_id)
        );

        return M('market_summary')->where($map)->alias("a")
                                  ->join("LEFT JOIN qz_order_source b ON a.source_id = b.id")
                                  ->field("dept_id,if(b.charge is null ,1,b.charge) as charges, sum(pv) as pv,sum(uv) as uv, sum(order_count) as order_count , sum(order_fen_count) as order_fen_count,sum(real_fen_count) as real_fen_count")
                                  ->group("a.dept_id,source_id")->select();
    }

     /**
     * 运营中心财年分单
     * @param  [date] $begin [开始时间]
     * @param  [date] $end   [结束时间]
     * @return [type]       [description]
     */
    public function getDeptYearData($begin,$end,$dept_id)
    {
        $map = array(
           "time" => array(
                array('EGT', $begin),
                array('LT', $end)
            ),
            "dept_id" => array("IN",$dept_id)
        );

        $buildSql = M('market_summary')->where($map)->field("DATE_FORMAT(time,'%Y-%m') as time,real_fen_count,uv,source_id")->buildSql();
        return M('market_summary')->table($buildSql)->alias("t")
                                  ->join("LEFT JOIN qz_order_source b ON t.source_id = b.id")
                                  ->field("time,sum(real_fen_count) real_fen_count,sum(uv) as uv,if(b.charge is null ,1,b.charge) as charges")
                                  ->group("time,charges")->order("time")->select();

    }

    /**
     * 部门渠道数据
     * @param  [type] $begin [description]
     * @param  [type] $end   [description]
     * @param  [int] $state [渠道]
     * @param  [int] $source[来源]
     * @return [type]        [description]
     */
    public function getDeptChannelOrder($begin,$end,$state,$source)
    {
        $map = array(
            "a.time" => array(
                array('EGT', $begin),
                array('LT', $end)
            ),
            "a.dept_id" => array("IN",array(1,6,7))
        );

        if (!empty($source)) {
            $map1["a.dept_id"] = array("EQ",$source);
        }

        if (!empty($state)) {
            $map1["b.charge"] = array("EQ",$state);
        }

        $buildSql = M('market_summary')->where($map)->alias("a")
                                    ->field("dept_id,real_fen_count,uv,source_name,order_count,order_fen_count,time,source_id")
                                    ->buildSql();

        return M('market_summary')->table($buildSql)->where($map1)->alias("a")
                           ->join("left join qz_order_source b on a.source_id = b.id and b.visible = 0")
                           ->field("source_id, source_name,sum(real_fen_count) as real_fen_count,if(b.charge is null ,1,b.charge) as charges,sum(uv) as uv,source_name,a.time,sum(order_count) as order_count,sum(order_fen_count) as order_fen_count,dept_id")
                           ->group("a.dept_id,source_name,charges,a.time")->select();
    }

    /**
     * 订单详细信息
     * @param  [int] $source_id [来源ID]
     * @param  [date] $begin     [开始时间]
     * @param  [date] $end       [结束时间]
     * @return array
     */
    public function getOrderDetailsList($source_id,$begin,$end,$pageIndex,$pageCount)
    {
        $sql =" select
                t1.*,q.cname,area.qz_area
                from (
                    select m.order_id, o.on,o.type_fw,o.on_sub,m.tag, FROM_UNIXTIME(o.time_real,'%Y-%m-%d %H-%i-%s') as time_real, FROM_UNIXTIME(new.lasttime,'%Y-%m-%d %H-%i-%s') as lasttime,o.wzd,o.cs,o.qx from qz_market_orders m
                    join qz_orders o on m.order_id = o.id
                    left join qz_order_csos_new new on new.order_id = o.id
                    where m.time >= '$begin' and m.time < '$end'
                ) t1
                join qz_order_source s FORCE INDEX(idx_src) on s.src = t1.tag
                join qz_quyu q on q.cid = t1.cs
                join qz_area area on area.qz_areaid = t1.qx
                where s.id = ".$source_id ." limit $pageIndex , $pageCount";
        return M()->query($sql);

    }

    public function getOrderDetailsListCount($source_id,$begin,$end)
    {
        $sql ="
                select
                count(*) as count
                from (
                    select
                    t1.*,q.cname,area.qz_area
                    from (
                        select m.order_id, o.on,o.type_fw,o.on_sub,m.tag, FROM_UNIXTIME(o.time_real,'%Y-%m-%d %H-%i-%s') as time_real, FROM_UNIXTIME(new.lasttime,'%Y-%m-%d %H-%i-%s') as lasttime,o.wzd,o.cs,o.qx from qz_market_orders m
                        join qz_orders o on m.order_id = o.id
                        left join qz_order_csos_new new on new.order_id = o.id
                        where m.time >= '$begin' and m.time < '$end'
                    ) t1
                    join qz_order_source s FORCE INDEX(idx_src) on s.src = t1.tag
                    join qz_quyu q on q.cid = t1.cs
                    join qz_area area on area.qz_areaid = t1.qx
                    where s.id = ".$source_id.") t limit 1";

        return M()->query($sql);
    }

    /**
     * 获取2017年2-6月份运营中心数据
     * @param  string $department [部门]
     * @return array
     */
    public function getBeforeData($department)
    {

        //初始数据
        $data = array(
            "tgb" => array(
                "mianfei" => array(
                    "2017-02" => "811",
                    "2017-03" => "955",
                    "2017-04" => "740",
                    "2017-05" => "1007",
                    "2017-06" => "1180",
                ),
                "fufei" => array(
                    "2017-02" => "565",
                    "2017-03" => "509",
                    "2017-04" => "118",
                    "2017-05" => "300",
                    "2017-06" => "971",
                )
            ),
            "llb" => array(
                "mianfei" => array(
                    "2017-02" => "1043",
                    "2017-03" => "862",
                    "2017-04" => "679",
                    "2017-05" => "681",
                    "2017-06" => "712",
                ),
                "uv" => array(
                    "2017-02" => "579986",
                    "2017-03" => "782958",
                    "2017-04" => "855558",
                    "2017-05" => "851704",
                    "2017-06" => "951193",
                )
            ),
            'yyzx' => array(
                'turn_rate_order' => array(
                    "2017-02" => "5970",
                    "2017-03" => "7814",
                    "2017-04" => "6854",
                    "2017-05" => "7859",
                    "2017-06" => "7711",
                ),
                'turn_rate_uv' => array(
                    "2017-02" => "1135751",
                    "2017-03" => "2070549",
                    "2017-04" => "2214421",
                    "2017-05" => "2380075",
                    "2017-06" => "2346733"
                )
            ),
            'cpb' => array(
                "uv" => array(
                    "2017-02" => "579986",
                    "2017-03" => "782958",
                    "2017-04" => "855558",
                    "2017-05" => "851704",
                    "2017-06" => "951193",
                ),
                'turn_rate_order' => array(
                    "2017-02" => "5970",
                    "2017-03" => "7814",
                    "2017-04" => "6854",
                    "2017-05" => "7859",
                    "2017-06" => "7711",
                ),
                'turn_rate_uv' => array(
                    "2017-02" => "1135751",
                    "2017-03" => "2070549",
                    "2017-04" => "2214421",
                    "2017-05" => "2380075",
                    "2017-06" => "2346733"
                )
            )
        );
        switch ($department) {
            case 'tgb':
                $list["fufei"] = $data["tgb"]["fufei"];
                $list["mianfei"] = $data["tgb"]["mianfei"];
                break;
            case 'llb':
                $list["uv"] = $data["llb"]["uv"];
                $list["mianfei"] = $data["llb"]["mianfei"];
                break;
            case 'cpb':
                $list["uv"] = $data["cpb"]["uv"];
                $list["turn_rate_order"] = $data["cpb"]["turn_rate_order"];
                $list["turn_rate_uv"] = $data["cpb"]["turn_rate_uv"];
                break;
            default:
                foreach ($data as $value) {
                    foreach ($value as $key => $val) {
                        foreach ($val as $k => $v) {
                            $list[$key][$k] += $v;
                        }
                    }
                }
                break;
        }

        return $list;
    }

    /**
     * 根据来源ID获取列表
     * @param  string $time_start 开始时间
     * @param  string $time_end   结束时间
     * @param  array  $source_id  来源ID数组
     * @return array
     */
    public function getListBySourceId($time_start = '', $time_end = '', $source_id = array())
    {
        if (empty($time_start) || empty($time_end)) {
            return false;
        }
        $map['time'] = array(
            array('EGT', $time_start),
            array('ELT', $time_end)
        );

        if (!is_array($source_id)) {
            $source_id = array($source_id);
        }
        if (!empty($source_id)) {
            $map['source_id'] = array('IN', $source_id);
        }
        return M('market_summary')->where($map)->select();
    }

    /**
     * 获取时间段内发单量最高的50个城市
     * @param  date $begin [开始时间]
     * @param  date $end   [结束时间]
     * @return array
     */
    public function getOrderCity($begin,$end)
    {
        $sql = 'select count(o.id) count,FROM_UNIXTIME(time_real,"%Y-%m") as date,q.cname,q.cid from qz_orders o
            join qz_quyu q on q.cid = o.cs
            where time_real >= UNIX_TIMESTAMP("'.$begin.' 17:30:00") and time_real < UNIX_TIMESTAMP("'.$end.' 17:30:00")
            group by date,cs
            order by count desc,cs
            limit 50;';
        return  $this->db(1,"DB_CONFIG1")->query($sql);
    }

    /**
     * 每月访客按城市分析
     * @param  date $begin [开始时间]
     * @param  date $end   [结束时间]
     * @param  string $citys [城市]
     * @return array
     */
    public function getCityOrder($begin,$end,$citys)
    {
        $sql = 'select q.cname,q.cid,
                case
                when o.time_real >= UNIX_TIMESTAMP(CONCAT(FROM_UNIXTIME(o.time_real,"%Y-%m-%d")," ","17:30:00"))
                and o.time_real < UNIX_TIMESTAMP(CONCAT(FROM_UNIXTIME(o.time_real,"%Y-%m-%d")," ","23:59:59"))
                then DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(o.time_real,"%Y-%m-%d"),INTERVAL 1 day),"%Y/%m")
                else FROM_UNIXTIME(o.time_real,"%Y/%m")
                end as date, count(o.id) count from qz_orders o
                join qz_quyu q on q.cid = o.cs
                where time_real >= UNIX_TIMESTAMP("'.$begin.' 17:30:00") and time_real < UNIX_TIMESTAMP("'.$end.' 17:30:00")
                and cs in ('.$citys.')
                group by date,cs
                ORDER BY cs,date';
        return  $this->db(1,"DB_CONFIG1")->query($sql);
    }

     /**
     * 每月访客按城市实际分单量
     * @param  date $begin [开始时间]
     * @param  date $end   [结束时间]
     * @param  string $citys [城市]
     * @return array
     */
    public function getCityRealOrder($begin,$end,$citys)
    {
        $sql = 'select q.cname,q.cid,FROM_UNIXTIME(new.lasttime,"%Y/%m") as date,count(new.order_id) as count from qz_order_csos_new new
            join qz_orders o on o.id = new.order_id
            join qz_quyu q FORCE INDEX(idx_cid) on q.cid = o.cs
            where new.lasttime >= UNIX_TIMESTAMP("'.$begin.'") and new.lasttime < UNIX_TIMESTAMP("'.$end.'")
            and o.cs in ('.$citys.')
            and new.order_on = 4 and o.type_fw = 1
            group by date,cs
            ORDER BY cs,date';
        return  $this->db(1,"DB_CONFIG1")->query($sql);
    }

    /**
     * 获取问答数据
     * @return [type] [description]
     */
    public function getWenDaList($begin,$end)
    {
        $map = array(
            "day" => array(
                array("EGT",date("Y-m-d",$begin)),
                array("LT",date("Y-m-d",$end))
            ),
            "url" => array("LIKE","%wenda/x%")
        );

        return M("yy_summary")->where($map)->field("url,pv,uv,order_count,real_order_count,day")->select();
    }

    public function getBaikeList($begin,$end)
    {
        $map = array(
            "day" => array(
                array("EGT",date("Y-m-d",$begin)),
                array("LT",date("Y-m-d",$end))
            ),
            "url" => array(
                array("LIKE","m.qizuang.com/baike/%"),
                array("LIKE","www.qizuang.com/baike/%"),
                "or"
            )
        );

        return M("yy_summary")->where($map)->field("url,pv,uv,order_count,real_order_count,day")->select();
    }
}