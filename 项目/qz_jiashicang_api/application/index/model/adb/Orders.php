<?php

// +----------------------------------------------------------------------
// | 订单数据模型
// +----------------------------------------------------------------------

namespace app\index\model\adb;

use think\Db;
use think\db\Query;

use app\common\model\adb\BaseModel;

class Orders extends BaseModel
{
    /**
     * @des:城市签单距离排行榜查询,城市签单数量以装企申请签单时间为准
     * @param $param
     * @return mixed
     */
    public function getSignRanking($param)
    {
        $map = new Query();
        $map->where('o.on', '=', 4);
        $map->where('o.type_fw', 'in', [1, 2]);
        $map->where('q.is_open_city', '=', 1);
        if (isset($param['start_time']) && isset($param['end_time'])) {
            $map->where('o.qiandan_addtime', 'between', [$param['start_time'], $param['end_time']]);
        }
        if (isset($param['cs']) && is_array($param['cs'])) {
            $map->where('o.cs', 'in', $param['cs']);
        }
        return $this->link()->name('orders')->alias('o')->where($map)
            ->join("qz_quyu q", 'q.cid=o.cs', 'left')
            ->field(["count(o.id) sign_amount", "q.cname city_name"])
            ->order(['sign_amount' => 'desc','city_name'=>'desc'])
            ->group("q.cid")
            ->limit(20)
            ->select();
    }

    /**
     * @des:获取签单总量
     * @param $param
     * @return mixed|null
     */
    public function getSignNum($param)
    {
        $map = new Query();
        $map->where('o.on','=',4);
        $map->whereIn('o.type_fw',[1,2]);
        if (isset($param['cs']) && is_array($param['cs'])) {
            $map->where('o.cs', 'in',$param['cs']);
        }
        if (isset($param['start_time']) && isset($param['end_time'])) {
            $map->whereBetween('o.qiandan_addtime', [$param['start_time'], $param['end_time']]);
        }
        return  $this->link()->name('orders')->alias('o')->where($map)
            ->count('o.id');
    }

    /**
     * @des:总单量获取（分配时间维度）
     * @param $param
     * @return mixed
     */
    public function getAssignTotalNum($param)
    {
        $map = new Query();
        $map->where('o.on', '=', 4);
        $map->where('o.type_fw', 'in', [1,2]);
        if (isset($param['cs']) && is_array($param['cs'])) {
            $map->where('o.cs', 'in',$param['cs']);
        }
        if (isset($param['start_time']) && isset($param['end_time'])) {
            $map->whereBetween('n.lasttime', [$param['start_time'], $param['end_time']]);
        }
        return $this->link()->table('qz_order_csos_new')->alias('n')->where($map)
            ->join('qz_orders o','o.id=n.order_id','inner')
            ->count('distinct o.id');
    }

    /**
     * @des:获取签单数据，根据签单距离,用尽量最简单的sql查询，区间返回的交给程序处理，减少数据库读取字段逻辑
     * @param $param
     * @return mixed
     */
    public function getSignNumByDistance($param)
    {
        $signMap = new Query();
        $signMap->where('o.on', '=', 4);
        $signMap->whereIn('o.type_fw', [1, 2]);

        if (isset($param['start_time']) && isset($param['end_time'])) {
            $signMap->whereBetween('o.qiandan_addtime', [$param['start_time'], $param['end_time']]);
        }
        if (isset($param['cs']) && is_array($param['cs'])) {
            $signMap->where('o.cs', 'in',$param['cs']);
        }
        $sql_common =$this->link()->name('orders')->alias('o')->where($signMap)
            ->field(["(
                    CASE 
                        WHEN o.qiandan_distance < 3 THEN  3 
                        WHEN o.qiandan_distance>=3 and o.qiandan_distance<5 THEN 5 
                        WHEN  o.qiandan_distance>=5 and o.qiandan_distance<10 THEN 10
		                WHEN  o.qiandan_distance>=10 and o.qiandan_distance<15 THEN 15
		                WHEN  o.qiandan_distance>=15 and o.qiandan_distance<20 THEN 20
		                ELSE 100 
		                END
		                ) 
		                as distance"])
            ->group("o.id")
            ->buildSql();

        return $this->link()->table($sql_common)->alias('basic')
            ->field(["
                count(distance) amount,
                count(if(distance=3,1, null)) qiandan_3km,
                count(if(distance=5,1, null)) qiandan_5km ,
                count(if(distance=10,1, null)) qiandan_10km,
                count(if(distance=15,1, null)) qiandan_15km,
                count(if(distance=20,1, null)) qiandan_20km,
                count(if(distance=100,1, null)) qiandan_extra
                "])
            ->find();
    }

    public function getCityOrderCount($begin,$end,$type,$city)
    {
        $map[] = ["a.lasttime","EGT",$begin];
        $map[] = ["a.lasttime","LT",$end];
        $map[] = ["o.on","EQ",4];
        $map[] = ["o.type_fw","IN",[1,2]];
        if (count($city) > 0) {
            $map[] = ["o.cs","IN",$city];
        }

        if (!empty($type)) {
            $map[] = ["o.type_fw","EQ",$type];
        }

        return $this->link()->table("qz_order_csos_new")->where($map)->alias("a")
            ->join("orders o","o.id = a.order_id","inner")
            ->count();
    }

    public function getOrderData($begin,$end,$city)
    {
        $map[] = ["o.time_real","EGT",$begin];
        $map[] = ["o.time_real","LT",$end];

        if (count($city) > 0) {
            $map[] = ["o.cs","IN",$city];
        }

        return  $this->link()->table("qz_orders")->where($map)->alias("o")
            ->join("qz_order_pool p","p.orderid = o.id and p.status = 0","left")
            ->field("count(o.id) as all_count,count(if(p.orderid is not null,1,null)) as p_count,
            count(if(o.on = 4 ,1,null)) as yx_count,count(if(o.on = 4 and o.type_fw = 1,1,null)) as fen_count,
            count(if(o.qiandan_companyid <> 0 and o.on = 4 and o.type_fw = 1,1,null )) as qian_count")
            ->find();
    }


    public function getOrderFenCount($mode = 1,$begin = 0,$end = 0,$city = [])
    {

        $map[] = ["new.lasttime","EGT",$begin];
        $map[] = ["new.lasttime","LT",$end];
        $map[] = ["o.on","EQ",4];
        $map[] = ["o.type_fw","EQ",1];

        if (count($city) > 0) {
            $map[] = ["o.cs","IN",$city];
        }

        $buildSql =  $this->link()->table("qz_order_csos_new")->where($map)->alias("new")
                ->join("qz_orders o","o.id = new.order_id","inner")
                ->join("qz_order_info i","i.order = new.order_id and i.cooperate_mode = $mode","inner")
                ->field("new.order_id")
                ->group("new.order_id")
                ->buildSql();
        return $this->link()->table($buildSql)->alias("t")->count();
    }

    // 区域维度发单量相关统计
    public function getAreaFadanStatistic($date_begin, $date_end, $city_ids = [], $area_ids = []){
        $map = $this->getAreaStatisticMap(1, $date_begin, $date_end, $city_ids, $area_ids);

        $list = $this->link()->name("orders")->alias("o")->where($map)
            ->join("order_pool p", "p.orderid = o.id", "left")
            ->field([
                "o.cs as city_id", "o.qx as area_id", "concat(o.cs, '-', o.qx) as gkey",
                "count(o.id) as fadan",
                "count(IF(o.`on` = 4 and o.type_fw = 1, 1, null)) as fendan",
                "count(IF(o.`on` = 4 and o.type_fw = 2, 1, null)) as zendan",
                "count(IF(p.orderid is not null and p.op_uid <> 0, 1, null)) as paidan"
            ])
            ->group("o.cs,o.qx")
            ->select();

        return $list;
    }

    // 区域维度分单量相关统计
    public function getAreaFendanStatistic($date_begin, $date_end, $city_ids = [], $area_ids = []){
        $map = $this->getAreaStatisticMap(2, $date_begin, $date_end, $city_ids, $area_ids);

        return $this->link()->name("orders")->alias("o")->where($map)
            ->join("order_csos_new c", "c.order_id = o.id", "inner")
            ->field([
                "o.cs as city_id", "o.qx as area_id", "concat(o.cs, '-', o.qx) as gkey",
                "count(IF(o.type_fw in(1, 2), 1, null)) as csos_all",
                "count(IF(o.type_fw = 1, 1, null)) as csos_fendan",
                "count(IF(o.type_fw = 2, 1, null)) as csos_zendan",
                "count(IF(o.type_fw = 1 and o.qiandan_status >= 0 and o.qiandan_companyid > 0, 1, null)) as csos_fen_qiandan",
                "count(IF(o.type_fw = 2 and o.qiandan_status >= 0 and o.qiandan_companyid > 0, 1, null)) as csos_zen_qiandan",
                "count(IF(o.type_fw = 1 and o.lx = '1' and o.lxs = 3, 1, null)) as csos_jugai",
                "count(IF(o.type_fw in(1, 2) and o.lx = '1', 1, null)) as csos_jiazhuang",
                "count(IF(o.type_fw in(1, 2) and o.lx = '2', 1, null)) as csos_gongzhuang",
                "count(IF(o.type_fw in(1, 2) and o.fangshi = '29', 1, null)) as csos_banbao",
                "count(IF(o.type_fw in(1, 2) and o.fangshi = '30', 1, null)) as csos_quanbao",
                "count(IF(o.type_fw in(1, 2) and o.fangshi = '31', 1, null)) as csos_qingbao",
                "count(IF(o.type_fw in(1, 2) and o.fangshi = '32', 1, null)) as csos_mianyi",
            ])
            ->group("o.cs,o.qx")
            ->select();
    }

    // 按分单时间统计量房量
    public function getAreaFenLfStatistic($date_begin, $date_end, $city_ids = [], $area_ids = []){
        $map = $this->getAreaStatisticMap(2, $date_begin, $date_end, $city_ids, $area_ids);

        $subSql = $this->link()->name("orders")->alias("o")->where($map)
                ->join("order_csos_new c", "c.order_id = o.id", "inner")
                ->join("order_info i", "i.`order` = o.id", "inner")
                ->join("order_company_review r", "r.orderid = i.`order` and r.comid = i.com", "left")
                ->field([
                    "o.id as order_id",
                    "count(IF(r.`status` in(1, 2, 4), 1, null)) as lfnum"
                ])
                ->group("o.id")
                ->buildSql();

        $list = $this->link()->table($subSql)->alias("t")
            ->join("orders o", "o.id = t.order_id", "inner")
            ->field([
                "o.cs as city_id", "o.qx as area_id", "concat(o.cs, '-', o.qx) as gkey",
                "count(IF(t.lfnum > 0, 1, null)) as csos_lfnum",
                "count(IF(o.type_fw = 1 and t.lfnum > 0, 1, null)) as csos_fen_lfnum",
                "count(IF(o.type_fw = 2 and t.lfnum > 0, 1, null)) as csos_zen_lfnum",
            ])
            ->group("o.cs,o.qx")
            ->select();

        return $list;
    }

    // 按用户点击量房时间统计量房量
    public function getAreaRealLfStatistic($date_begin, $date_end, $city_ids = [], $area_ids = []){
        $map = $this->getAreaStatisticMap(3, $date_begin, $date_end, $city_ids, $area_ids);

        $subSql = $this->link()->name("orders")->alias("o")->where($map)
            ->join("order_info i", "i.`order` = o.id", "inner")
            ->join("order_company_review r", "r.orderid = i.`order` and r.comid = i.com", "left")
            ->field([
                "o.id as order_id",
                "count(IF(r.`status` not in(1, 2, 4), 1, null)) as unlfnum",
                "count(IF(r.`status` in(1, 2, 4), 1, null)) as lfnum",
            ])
            ->group("o.id")
            ->buildSql();

        $list = $this->link()->table($subSql)->alias("t")
            ->join("orders o", "o.id = t.order_id", "inner")
            ->field([
                "o.cs as city_id", "o.qx as area_id", "concat(o.cs, '-', o.qx) as gkey",
                "count(IF(t.lfnum = 0, 1, null)) as real_unlfnum",
                "count(IF(t.lfnum > 0, 1, null)) as real_lfnum",
            ])
            ->group("o.cs,o.qx")
            ->select();

        return $list;
    }

    // 按签单申请时间计算签单量
    public function getAreaQiandanStatistic($date_begin, $date_end, $city_ids = [], $area_ids = []){
        $map = $this->getAreaStatisticMap(4, $date_begin, $date_end, $city_ids, $area_ids);

        $list = $this->link()->name("orders")->alias("o")->where($map)
            ->field([
                "o.cs as city_id", "o.qx as area_id", "concat(o.cs, '-', o.qx) as gkey",
                "count(o.id) as qiandan",
            ])
            ->group("o.cs,o.qx")
            ->select();

        return $list;
    }


    // 分单时间查询订单明细
    public function getAreaFendanDetailed($date_begin, $date_end, $city_ids = [], $area_ids = []){
        $map = $this->getAreaStatisticMap(2, $date_begin, $date_end, $city_ids, $area_ids);

        $list = $this->link()->name("orders")->alias("o")->where($map)
            ->join("order_csos_new c", "c.order_id = o.id", "inner")
            ->field([
                "o.id as order_id",
                "o.cs as city_id", "o.qx as area_id",
                "o.type_fw", "o.mianji"
            ])
            ->select();

        return $list;
    }

    // 按区域统计查询条件
    public function getAreaStatisticMap($otype, $date_begin, $date_end, $city_ids = [], $area_ids = []){
        $map = new Query();

        if ($otype == 1){ // 发单维度
            $map->where("o.time_real", ">=", strtotime($date_begin));
            $map->where("o.time_real", "<=", strtotime($date_end) + 86399);
        } else if ($otype == 2) { // 分单维度
            $map->where("o.on", 4);
            $map->where("c.lasttime", ">=", strtotime($date_begin));
            $map->where("c.lasttime", "<=", strtotime($date_end) + 86399);
        } else if ($otype == 3) { // 量房维度
            $map->where("o.on", 4);
            $map->where("r.lf_time", ">=", strtotime($date_begin));
            $map->where("r.lf_time", "<=", strtotime($date_end) + 86399);
        } else if ($otype == 4) { // 签单维度
            $map->where("o.on", 4);
            $map->where("o.qiandan_status", ">=", 0);
            $map->where("o.qiandan_companyid", ">", 0);
            $map->where("o.qiandan_addtime", ">=", strtotime($date_begin));
            $map->where("o.qiandan_addtime", "<=", strtotime($date_end) + 86399);
        }

        // 城市查询
        if (!empty($city_ids)) {
            $map->where("o.cs", "in", $city_ids);
        }

        // 区域查询
        if (!empty($area_ids)) {
            $map->where("o.qx", "in", $area_ids);
        }

        return $map;
    }

    // 查询城市2.0会员分单量
    public function getCitySbackFendan($date_begin, $date_end, $city_ids = []){
        $map = new Query();
        $map->where("o.on", 4);
        $map->where("o.type_fw", 1);
        $map->where("c.lasttime", ">=", strtotime($date_begin));
        $map->where("c.lasttime", "<=", strtotime($date_end) + 86399);
        $map->where("i.cooperate_mode", 2);
        $map->where("i.type_fw", 1);

        // 城市查询
        if (!empty($city_ids)) {
            $map->where("o.cs", "in", $city_ids);
        }

        return $this->link()->name("orders")->alias("o")->where($map)
            ->join("order_csos_new c", "c.order_id = o.id", "inner")
            ->join("order_info i", "i.`order` = o.id", "inner")
            ->field([
                "o.cs as city_id",
                "count(distinct o.id) as fendan",
            ])
            ->group("o.cs")
            ->select();
    }

	// 撤回单量查询
    public function getOrderRecallStatistic($date_begin, $date_end, $city_ids = []){
        $map = new Query();
        $map->where("o.on", 99);
        $map->where("c.lasttime", ">=", strtotime($date_begin));
        $map->where("c.lasttime", "<=", strtotime($date_end) + 86399);

        // 城市查询
        if (!empty($city_ids)) {
            $map->where("o.cs", "in", $city_ids);
        }

        $list = $this->link()->name("orders")->alias("o")->where($map)
            ->join("order_csos_new c", "c.order_id = o.id", "inner")
            ->field([
                "o.cs as city_id",
                "count(o.id) as count",
                "count(IF(o.type_fw = 1, 1, null)) as recall_fen_num",
                "count(IF(o.type_fw = 2, 1, null)) as recall_zen_num",
            ])
            ->group("o.cs")
            ->select();

        return $list;
    }

    // 获取城市会员签单金额
    public function getCityUserQiandanAmount($utype, $date_begin, $date_end, $city_ids = []){
        $map = new Query();
        $map->where("o.on", 4);
        $map->where("o.qiandan_status", ">=", 0);
        $map->where("o.qiandan_companyid", ">", 0);
        $map->where("o.qiandan_addtime", ">=", strtotime($date_begin));
        $map->where("o.qiandan_addtime", "<=", strtotime($date_end) + 86399);

        if (!empty($city_ids)){
            $map->where("o.cs", "in", $city_ids);
        }

        if ($utype == 1) {
            $map->where("i.cooperate_mode", 1);
        } else if ($utype == 2) {
            $map->where("i.cooperate_mode", 2);
        }

        return $this->link()->name("orders")->alias("o")->where($map)
            ->join("order_info i", "i.order = o.id and i.com = o.qiandan_companyid", "inner")
            ->field(["o.cs as city_id", "sum(o.qiandan_jine) as qiandan_jine"])
            ->group("o.cs")
            ->select();
    }

    public function getFullOrderFen($begin,$end,$city)
    {
        $map[] = ["i.type_fw","EQ",1];
        $map[] = ["i.cooperate_mode","EQ",2];
        $map[] = ["new.lasttime","EGT",$begin];
        $map[] = ["new.lasttime","LT",$end];
        $map[] = ["o.on","EQ",4];
        $map[] = ["o.type_fw","EQ",1];

        if (count($city) > 0) {
            $map[] = ["o.cs","IN",$city];
        }

        $list = $this->link()->table("qz_order_csos_new")->where($map)->alias("new")
            ->join("qz_orders o","o.id = new.order_id","inner")
            ->join("qz_order_info i","i.order = new.order_id","inner")
            ->field("FROM_UNIXTIME(new.lasttime,'%m') as month,count(distinct new.order_id) as count")
            ->group("month")
            ->select();

        return $list;
    }

    public function getOrderAllocation($begin,$end,$city)
    {
        $map[] = ["new.lasttime","EGT",$begin];
        $map[] = ["new.lasttime","LT",$end];
        $map[] = ["o.on","EQ",4];
        $map[] = ["o.type_fw","EQ",1];

        if (count($city) > 0) {
            $map[] = ["o.cs","IN",$city];
        }

        $buildSql = $this->link()->table("qz_order_csos_new")->where($map)->alias("new")
            ->join("qz_orders o","o.id = new.order_id")
            ->join("qz_order_allot_info b","b.order_id = new.order_id")
            ->field("FROM_UNIXTIME(new.lasttime,'%Y%m') as month, b.order_type 
            ,if(b.city_vip_num > 4,4,b.city_vip_num) as city_vip_num,b.allot_fen_num")
            ->buildSql();

        return $this->link()->table($buildSql)->alias("t")
            ->field("t.month,t.order_type,sum(t.allot_fen_num) as allot_fen_num,sum(city_vip_num) as city_vip_num")
            ->group("t.month,t.order_type")->order("t.month,t.order_type")->select();
    }

    public function getOrderAreaRate($begin,$end,$city)
    {
        $map[] = ["new.lasttime","EGT",$begin];
        $map[] = ["new.lasttime","LT",$end];
        $map[] = ["o.on","EQ",4];
        $map[] = ["o.type_fw","IN",[1,2]];

        if (count($city) > 0) {
            $map[] = ["o.cs","IN",$city];
        }

        $buildSql = $this->link()->table("qz_order_csos_new")->where($map)->alias("new")
            ->join("qz_orders o","o.id = new.order_id")
            ->field("o.id, CAST(o.mianji AS SIGNED) as mianji")->buildSql();

        $buildSql =  $this->link()->table($buildSql)->alias("t")
                    ->field("case 
                            when mianji < 40 then 1
                            when mianji >= 40 and mianji < 60 then 2
                            when mianji >= 60 and mianji < 80 then 3
                            when mianji >= 80 and mianji < 100 then 4
                            when mianji >= 100 and mianji < 120 then 5
                            when mianji >= 120 and mianji < 150 then 6
                            when mianji >= 150 and mianji < 200 then 7
                            when mianji >= 200 then 8
                            else 99
                           end  as type,t.id
                            ")->buildSql();
        return $this->link()->table($buildSql)->alias("t")->group("t.type")
                            ->field("t.type,count(t.id) as count")
                            ->select();
    }


    public function getCityOrderActual($begin,$end,$city)
    {
        $map[] = ["a.datetime","EGT",$begin];
        $map[] = ["a.datetime","LT",$end];

        if (count($city) > 0) {
            $map[] = ["a.city_id","IN",$city];
        }

        return $this->link()->table("qz_city_order_actual")->where($map)->alias("a")
            ->field("a.city_id,FROM_UNIXTIME(a.datetime,'%Y%m') as month,actual_orders")
            ->order("a.city_id,month")
            ->select();
    }

    public function getMissCityOrderActual($begin,$end,$city)
    {
        $map[] = ["datetime","EGT",$begin];
        $map[] = ["datetime","LT",$end];

        $nextSql = $this->link()->table("qz_city_order_lack")->where($map)
                        ->field("SUBSTRING_INDEX(GROUP_CONCAT(id ORDER BY datetime DESC),',',1) as id,FROM_UNIXTIME(datetime,'%Y%m') ,city_id")
                        ->group("city_id,FROM_UNIXTIME(datetime,'%Y%m')")
                        ->buildSql();
        $map1 = [];
        if (count($city) > 0) {
            $map1[] = ["a.city_id","IN",$city];
        }
        return  $this->link()->table("qz_city_order_lack")->alias("a")->where($map1)
                     ->join("$nextSql t","t.id = a.id")
                     ->field("a.city_id,FROM_UNIXTIME(a.datetime,'%Y%m') as month,a.predict_whole_month_orders")
                     ->select();
    }

    public function getOrderMonthActual($begin,$end,$city)
    {
        $map[] = ["new.lasttime","EGT",$begin];
        $map[] = ["new.lasttime","LT",$end];
        $map[] = ["o.on","EQ",4];
        $map[] = ["o.type_fw","EQ",1];

        if (count($city) > 0) {
            $map[] = ["o.cs","IN",$city];
        }

        $buildSql = $this->link()->table("qz_order_csos_new")->where($map)->alias("new")
            ->join("qz_orders o","o.id = new.order_id")
            ->field("o.id,o.cs as city_id,FROM_UNIXTIME(new.lasttime,'%Y%m') as month")
            ->buildSql();

        return $this->link()->table($buildSql)->alias("t")
                ->field("t.city_id,t.month,count(t.id) as count")
                ->group("t.city_id,t.month")->order("t.city_id,t.month")->select();
    }

    public function getOrderActualCountWithMonth($begin,$end,$city)
    {
        $map[] = ["new.lasttime", "EGT", $begin];
        $map[] = ["new.lasttime", "LT", $end];

        if (count($city) > 0) {
            $map[] = ["o.cs","IN",$city];
        }

        $buildSql = $this->link()->table("qz_order_csos_new")->where($map)->alias("new")
            ->join("qz_orders o","o.id = new.order_id")
            ->join("qz_order_info i", "i.order = new.order_id and i.type_fw = 1")
            ->field("i.order as order_id,new.lasttime,i.cooperate_mode")
            ->buildSql();
        return $this->link()->table($buildSql)->alias("t")
            ->field("FROM_UNIXTIME(t.lasttime,'%Y%m') as date,count(t.order_id) as count,count(if(t.cooperate_mode = 2,1,null)) as qian_count")
            ->group("date")->select();
    }

    /**
     * @des:签单申请时间维度，获取签单总金额
     * @param array $param
     * @return mixed
     */
    public function getSignMoneyTotal($param = [])
    {
        $signMap = new Query();
        $signMap->where('o.on', '=', 4);
        $signMap->whereIn('o.type_fw', [1, 2]);
        if (isset($param['start_time']) && isset($param['end_time'])) {
            $signMap->where('o.qiandan_addtime', 'between', [$param['start_time'], $param['end_time']]);
        }
        if (isset($param['cs']) && !empty($param['cs']) && is_array($param['cs'])) {
            $signMap->where('o.cs', 'in', $param['cs']);
        }
        $child = $this->link()->table('qz_orders')->alias('o')->where($signMap)
            ->field(["o.qiandan_jine", "o.id"])
            ->group('o.id')
            ->buildSql();

        return $this->link()->table($child)->alias('basic')
            ->field("
              sum(if(qiandan_jine>1000,qiandan_jine/10000,qiandan_jine)) as amount,
              count(id) as number
            ")
            ->find();
    }

    /**
     * @des:1.0会员单位时间内的签单总金额
     * @param array $param
     * @return mixed
     */
    public function getSignMoneyV1($param = [])
    {
        $signMap = new Query();
        $signMap->where('o.on', '=', 4);
        $signMap->where('i.cooperate_mode', '=', 1);//常规会员1.0
        $signMap->where('o.type_fw', 'in', [1, 2]);
        if (isset($param['cs']) && is_array($param['cs'])) {
            $signMap->where('o.cs', 'in', $param['cs']);
        }
        if (isset($param['start_time']) && isset($param['end_time'])) {
            $signMap->where('o.qiandan_addtime', 'between', [$param['start_time'], $param['end_time']]);
        }
        $child = $this->link()->table('qz_orders')->alias('o')->where($signMap)
            ->join('qz_order_info i', 'i.order = o.id', 'inner')
            ->field(["distinct(o.id) id","o.qiandan_jine"])
            ->buildSql();
        return $this->link()->table($child)->alias('a')
            ->field(["sum(if(qiandan_jine>1000,qiandan_jine/10000,qiandan_jine)) as amount"])
            ->find();
    }

    /**
     * @des:2.0会员单位时间内的签单总金额,申请签单金额
     * @param array $param
     * @return mixed
     */
    public function getSignMoneyV2($param = [])
    {
        $signMap = new Query();
        $signMap->where('o.on', '=', 4);
        $signMap->where('i.cooperate_mode', '=', 2);
        $signMap->where('i.type_fw', 'in', [1, 2]);
        if (isset($param['cs']) && is_array($param['cs'])) {
            $signMap->where('o.cs', 'in', $param['cs']);
        }
        if (isset($param['start_time']) && isset($param['end_time'])) {
            $signMap->where('o.qiandan_addtime', 'between', [$param['start_time'], $param['end_time']]);
        }
        $child =  $this->link()->table('qz_orders')->alias('o')->where($signMap)
            ->join('qz_order_info i', 'i.order = o.id', 'inner')
            ->field(["distinct(o.id) as id","o.qiandan_jine"])
            ->buildSql();
        return $this->link()->table($child)->alias('a')
            ->field(["sum(if(qiandan_jine>1000,qiandan_jine/10000,qiandan_jine)) as amount"])
            ->find();
    }


    /**
     * @des:获取分配分单量不对订单主表去重
     * @param array $param
     * @return mixed
     */
    public function getOrderNumByAddTime($param = [])
    {
        $map = new Query();
        $map->where('o.on', '=', 4);
        $map->where('i.type_fw', '=', 1);
        if (isset($param['cs']) && is_array($param['cs']) && !empty($param['cs'])) {
            $map->where('o.cs', 'in', $param['cs']);
        }
        if (isset($param['start_time']) && isset($param['end_time'])) {
            $map->whereBetween('n.lasttime', [$param['start_time'], $param['end_time']]);
        }

        return $this->link()->table('qz_order_csos_new')->alias('n')->where($map)
            ->join('qz_order_info i', 'i.order=n.order_id', 'left')
            ->join('qz_orders o', 'o.id=n.order_id', 'left')
            ->count('i.id');
    }

    /**
     * @des:获取分配订单数
     * @param array $param
     * @return mixed
     */
    public function getDistributeOrderNumber($param = [])
    {
        $map = new Query();
        $map->where('o.on', '=', 4);
        $map->where('o.type_fw', '=', 1);
        if (isset($param['cs']) && is_array($param['cs']) && !empty($param['cs'])) {
            $map->where('o.cs', 'in', $param['cs']);
        }
        if (isset($param['start_time']) && isset($param['end_time'])) {
            $map->whereBetween('n.lasttime', [$param['start_time'], $param['end_time']]);
        }

        return $this->link()->table('qz_orders')->alias('o')->where($map)
            ->join('qz_order_csos_new n', 'n.order_id=o.id', 'left')
            ->count('distinct o.id');
    }

    // 查询会员量房统计（按用户点击量房时间进行统计）
    public function getCompanyLfCount($date_begin, $date_end, $company_ids = []){
        $map = new Query();
        $map->where("o.on", 4);
        $map->where("r.status", "in", [1, 2, 4]);
        $map->where("r.lf_time", ">=", strtotime($date_begin));
        $map->where("r.lf_time", "<=", strtotime($date_end) + 86399);

        if (!empty($company_ids)) {
            $map->where("i.com", "in", $company_ids);
        }

        return $this->link()->name("orders")->alias("o")->where($map)
            ->join("order_info i", "i.order = o.id", "inner")
            ->join("order_company_review r", "r.orderid = i.`order` and r.comid = i.com", "inner")
            ->field(["i.com as company_id", "count(i.id) as lf_num"])
            ->group("i.com")
            ->select();
    }

    // 查询会员公司签单统计（签单申请时间）
    public function getCompanyQiandanCount($date_begin, $date_end, $company_ids = []){
        $map = new Query();
        $map->where("o.on", 4);
        $map->where("o.qiandan_status", ">=", 0);
        $map->where("o.qiandan_addtime", ">=", strtotime($date_begin));
        $map->where("o.qiandan_addtime", "<=", strtotime($date_end) + 86399);

        if (!empty($company_ids)) {
            $map->where("o.qiandan_companyid", "in", $company_ids);
        } else {
            $map->where("o.qiandan_companyid", ">", 0);
        }

        return $this->link()->name("orders")->alias("o")->where($map)
            ->join("order_info i", "i.order = o.id and i.com = o.qiandan_companyid", "inner")
            ->field([
                "o.qiandan_companyid as company_id",
                "count(o.id) as qiandan_num",
                "sum(o.qiandan_jine) as qiandan_amount"
            ])
            ->group("o.qiandan_companyid")
            ->select();
    }

    /**
     * @des:违规补轮次数求和
     * @param array $param
     * @return mixed
     */
    public function getViolateApply($param = [])
    {
        $map = new Query();
        $map->where('a.trade_type','=',7);
        $map->where('a.operation_type','=',2);

        if (isset($param['start_time']) && isset($param['end_time'])) {
            $map->where('a.created_at', 'between', [$param['start_time'], $param['end_time']]);
        }
        if (isset($param['cs']) && is_array($param['cs'])) {
            $map->where('u.cs', 'in', $param['cs']);
        }

        return $this->link()->table('qz_user_company_account_log')->alias('a')->where($map)
            ->join('qz_user u','u.id=a.user_id','left')
            ->count('a.id');
    }

    /**
     * @des:违规补轮详情，创建时间维度
     * @param array $param
     * @return mixed
     */
    public function getViolateApplyDetail($param = [])
    {
        $map = new Query();
        $map->where('a.trade_type','=',7);
        $map->where('a.operation_type','=',2);
        if (isset($param['start_time']) && isset($param['end_time'])) {
            $map->where('a.created_at', 'between', [$param['start_time'], $param['end_time']]);
        }
        if (isset($param['cs']) && is_array($param['cs'])) {
            $map->where('u.cs', 'in', $param['cs']);
        }
        $pageSize = $param['page_size'] ?? 20;
        $page['page'] = $param['page'] ?? 1;

        return $this->link()->table('qz_user_company_account_log')->alias('a')->where($map)
            ->join('qz_user u', 'u.id=a.user_id', 'left')
            ->join('qz_quyu q', 'q.cid=u.cs', 'left')
            ->field(["q.cname", "count(a.id) as number", "u.cs"])
            ->group("u.cs")
            ->paginate($pageSize,false,$page);
    }

    /**
     * @des:获取违规公司信息，根据时间，城市维度
     * @param $cs
     * @param $start_time
     * @param $end_time
     * @return mixed
     */
    public function getViolateOrderCompanyByCs($cs, $start_time, $end_time)
    {
        $map = new Query();
        $map->where('a.trade_type', '=', 7);
        $map->where('a.operation_type', '=', 2);
        $map->where('a.created_at', 'between', [$start_time, $end_time]);
        $map->where('u.cs', '=', $cs);

        return  $this->link()->table('qz_user_company_account_log')->alias('a')->where($map)
            ->join('qz_user u', 'u.id=a.user_id', 'left')
            ->field(["u.jc as company_name", "count(a.id) as number"])
            ->group("a.user_id")
            ->select();
    }

    /**
     * @des:2.0会员消耗，order_info的分配时间维度
     * @param array $param
     * @return mixed
     */
    public function getUserConsumptionV2($param = [])
    {

        $map = new Query();
        $map->where('o.on','=', 4);
        $map->where('i.cooperate_mode','=', 2);
        if (isset($param['start_time']) && isset($param['end_time'])) {
            $map->whereBetween('csos.lasttime', [$param['start_time'], $param['end_time']]);
        }
        if (isset($param['cs']) && is_array($param['cs']) && !empty($param['cs'])) {
            $map->where('o.cs', 'in', $param['cs']);
        }
        return $this->link()->table('qz_orders')->alias('o')->where($map)
            ->join("order_csos_new csos", "csos.order_id = o.id", "inner")
            ->join("order_info i", "i.order = o.id", "inner")
            ->sum('i.order_amount');
    }

    // 查询城市2.0消耗金额
    public function getCityOrderAmount($date_begin, $date_end, $city_ids = []){
        $map = new Query();
        $map->where("o.on", 4);
        $map->where("o.type_fw", 1);
        $map->where("i.cooperate_mode", 2);
        $map->where("csos.lasttime",">=", strtotime($date_begin));
        $map->where("csos.lasttime","<=", strtotime($date_end) + 86399);

        if (!empty($city_ids)) {
            $map->where("o.cs", "in", $city_ids);
        }

        $list = $this->link()->name("orders")->alias("o")->where($map)
            ->join("order_csos_new csos", "csos.order_id = o.id", "inner")
            ->join("order_info i", "i.order = o.id", "inner")
            ->field([
                "o.cs as city_id",
                "sum(i.order_amount) as order_amount",
            ])
            ->group("o.cs")
            ->select();

        return $list;
    }

    // 查询会员2.0消耗金额
    public function getCompanyOrderAmount($date_begin, $date_end, $company_ids = []){
        $map = new Query();
        $map->where("o.on", 4);
        $map->where("o.type_fw", 1);
        $map->where("i.cooperate_mode", 2);
        $map->where("i.addtime",">=", strtotime($date_begin));
        $map->where("i.addtime","<=", strtotime($date_end) + 86399);

        if (!empty($company_ids)) {
            $map->where("i.com", "in", $company_ids);
        }

        $list = $this->link()->name("orders")->alias("o")->where($map)
            ->join("order_info i", "i.order = o.id", "inner")
            ->field([
                "i.com as company_id",
                "sum(i.order_amount) as order_amount",
            ])
            ->group("i.com")
            ->select();

        return $list;
    }

    // 查询城市级别数据统计
    public function getCityLittleOrderFawasteStatistic($date_begin, $date_end){
        $map = new Query();
        $map->where("o.time_real", "between", [
            strtotime($date_begin),
            strtotime($date_end)  + 86399
        ]);

        $subSql = $this->link()->name("orders")->alias("o")->where($map)
            ->join("order_pool p", "p.orderid = o.id", "left")
            ->join("quyu q", "q.cid = o.cs", "left")
            ->field([
                "o.id as order_id", "o.cs as city_id",
                "IF(p.op_uid > 0, 1, 2) as waste_status",
                "CASE q.little
                    WHEN 0 THEN 'A'
                    WHEN 1 THEN 'B'
                    WHEN 2 THEN 'C'
                    ELSE 'T' END
                AS city_little"
            ])
            ->buildSql();

        $list = $this->link()->table($subSql)->alias("t")
            ->field([
                "t.city_little",
                "count(t.order_id) as fa_count",
                "count(IF(t.waste_status = 2, 1, null)) as waste_count"
            ])
            ->group("city_little")
            ->select();

        return $list;
    }

    // 查询城市级别数据统计
    public function getCityLittleOrderFenwasteStatistic($date_begin, $date_end){
        $map = new Query();
        $map->where("o.on", 4);
        $map->where("o.type_fw", 1);
        $map->where("csos.lasttime", "between", [
            strtotime($date_begin),
            strtotime($date_end)  + 86399
        ]);

        $subSql = $this->link()->name("orders")->alias("o")->where($map)
            ->join("order_csos_new csos", "csos.order_id = o.id", "inner")
            ->join("order_allot_info a", "a.order_id = o.id", "left")
            ->join("quyu q", "q.cid = o.cs", "left")
            ->field([
                "o.id as order_id", "o.cs as city_id",
                "a.allot_fen_num", "IF(a.city_vip_num >= 4, 4, a.city_vip_num) as max_fennum",
                "CASE q.little
                    WHEN 0 THEN 'A'
                    WHEN 1 THEN 'B'
                    WHEN 2 THEN 'C'
                    ELSE 'T' END
                AS city_little"
            ])
            ->buildSql();

        $list = $this->link()->table($subSql)->alias("t")
            ->field([
                "t.city_little",
                "count(t.order_id) as csos_fennum",
                "sum(t.max_fennum) as max_fennum",
                "sum(t.allot_fen_num) as allot_fennum"
            ])
            ->group("city_little")
            ->select();

        return $list;
    }

    /**
     * @des:获取月维度的总单量
     * @param $begin
     * @param $end
     * @return mixed
     */
    public function getOrderCountByMonth($begin, $end)
    {
        $map[] = ["a.lasttime","EGT",$begin];
        $map[] = ["a.lasttime","LT",$end];
        $map[] = ["o.on","EQ",4];
        $map[] = ["o.type_fw","IN",[1,2]];

        return $this->link()->table("qz_order_csos_new")->where($map)->alias("a")
            ->join("orders o", "o.id = a.order_id", "inner")
            ->field(["count(*) as count", "FROM_UNIXTIME(a.lasttime,'%Y%m') as time"])
            ->group("FROM_UNIXTIME(a.lasttime,'%Y%m')")
            ->order('time asc')
            ->select();
    }

    /**
     * @des:按月维度获取总签单量
     * @param $begin
     * @param $end
     * @return mixed
     */
    public function getOrderSignCountByMonth($begin, $end)
    {
        $map = new Query();
        $map->where('o.on','=',4);
        $map->whereIn('o.type_fw',[1,2]);
        $map->whereBetween('o.qiandan_addtime', [$begin, $end]);
        return  $this->link()->name('orders')->alias('o')->where($map)
            ->field(["count(*) as count","FROM_UNIXTIME(o.qiandan_addtime,'%Y%m') as time"])
            ->group("FROM_UNIXTIME(o.qiandan_addtime,'%Y%m')")
            ->order("time asc")
            ->select();
    }

    /**
     * @des:城市发单浪费率，城市排行
     * @param $begin
     * @param $end
     * @param array $group_id
     * @return mixed
     */
    public function getFaDanWasteCity($begin, $end, $group_id = [])
    {
        $map = new Query();
        $map->where('o.time_real','between',[$begin,$end]);
        $map->where('q.is_open_city',1);
        if (!empty($group_id)) {
            $map->where('sr.dept', 'in', $group_id);
        }
        $subSql = $this->link()->table('qz_orders')->alias('o')->where($map)
            ->join('qz_order_pool p', 'p.orderid=o.id', 'left')
            ->join('qz_quyu q', 'q.cid=o.cs', 'inner');
        if (!empty($group_id)) {
            $subSql->join('qz_orders_source s', 's.orderid=o.id', 'inner')
                ->join('qz_order_source sr', 'sr.id=s.source_src_id', 'inner')
                ->join('qz_department_identify d', 'd.id=sr.dept', 'inner');
        }
        $subSql = $subSql->field("count(o.id) as all_count,count(if(p.op_uid<1,1,null)) as waste_count,q.cname")
            ->group("o.cs")->buildSql();

        return $this->link()->table($subSql)->alias('t')
            ->field("waste_count/all_count*100 as waste_rate,cname")
            ->order('waste_rate desc,cname desc')
            ->limit(20)
            ->select();
    }

    /**
     * @des:获取每小时时间段内的，发单情况，分单情况
     * @param $begin
     * @param $end
     * @param $group_id
     * @return mixed
     */
    public function getFaDanTimeAnalysis($begin, $end, $group_id = [])
    {
        $map = new Query();
        $map->where('o.time_real','between',[$begin,$end]);
        if (!empty($group_id)) {
            $map->where('sr.dept', 'in', $group_id);
        }

        $subSql = $this->link()->table('qz_orders')->alias('o')->where($map)
            ->join('qz_order_pool p', 'p.orderid=o.id', 'left');
        if (!empty($group_id)) {
            $subSql->join('qz_orders_source s', 's.orderid=o.id', 'inner')
                ->join('qz_order_source sr', 'sr.id=s.source_src_id', 'inner')
                ->join('qz_department_identify d', 'd.id=sr.dept', 'inner');
        }
        return $subSql->field("count(o.id) as fadan_count,count(IF(o.`on` = 4 and o.type_fw = 1, 1, null)) as fendan_count,FROM_UNIXTIME(o.time_real,'%H') as time")
            ->group("FROM_UNIXTIME(o.time_real,'%H')")
            ->order("time asc")
            ->select();
    }

    /**
     * @des:异常发单
     * @param $begin
     * @param $end
     * @param array $group_id
     * @return mixed
     */
    public function getAbnormalAnalysis($begin, $end, $group_id = [])
    {
        $map = new Query();
        $map->where('o.time_real', 'between', [$begin, $end]);
        if (!empty($group_id)) {
            $map->where('sr.dept', 'in', $group_id);
        }
        $subSql = $this->link()->table('qz_orders')->alias('o')->where($map);
        if (!empty($group_id)) {
            $subSql->join('qz_orders_source s', 's.orderid=o.id', 'inner')
                ->join('qz_order_source sr', 'sr.id=s.source_src_id', 'inner')
                ->join('qz_department_identify d', 'd.id=sr.dept', 'inner');
        }
        $subSql = $subSql->field("o.id,(case when ISNULL(o.remarks)  THEN 1 
            WHEN  o.remarks = '改动项目少' THEN 2
            WHEN  o.remarks = '需要垫资' THEN 3
            WHEN  o.remarks = '过段时间联系' THEN 4
            WHEN  o.remarks = '测试单' THEN 5
            WHEN  o.remarks = '假订单' THEN 6
            WHEN  o.remarks = '没交房' or o.remarks = '人在外地' THEN 7
            ELSE 8 END ) as remarks_status")
            ->buildSql();
        return $this->link()->table($subSql)->alias('t')
            ->field(["count(id) as count_id", "remarks_status"])
            ->group("remarks_status")
            ->select();
    }

    /**
     * @des:各部门订单浪费率（顶级渠道部门分组）
     * @param $begin
     * @param $end
     * @param array $group_id
     * @return mixed
     */
    public function getDepartWaste($begin, $end, $group_id = [])
    {
        $mapFaDan = new Query();
        $mapFaDan->where('o.time_real', 'between', [$begin, $end]);
        $mapNoPhone = new Query();
        $mapNoPhone->where('o.time_real', 'between', [$begin, $end]);
        $mapNoPhone->where(function ($query) {
            $query->where('p.op_uid', 0);
            $query->whereOr('p.op_uid', null);
        });
        //查询有会员的城市
        $mapNoPhone->where("EXISTS (select * from qz_user u 
        inner join qz_user_company c on c.userid=u.id 
        left join qz_quyu q on q.cid=u.cs where o.cs=u.cs and q.is_open_city=1 and c.fake=0)");
        $mapNoOpenCity = new Query();
        $mapNoOpenCity->where('o.time_real', 'between', [$begin, $end]);
        $mapNoOpenCity->where('q.is_open_city', 0);
        $mapNoVip = new Query();
        $mapNoVip->where('o.time_real', 'between', [$begin, $end]);
        $mapNoVip->where('q.is_open_city', 1);
        $mapNoVip->where(' NOT EXISTS (select * from qz_user u 
        inner join qz_user_company c on c.userid=u.id 
        left join qz_quyu q on q.cid=u.cs where o.cs=u.cs and q.is_open_city=1 and c.fake=0 and u.on = 2 and u.classid in (3,6))');
        //需要带渠道部门权限
        if (!empty($group_id)) {
            $mapFaDan->where('sr.dept', 'in', $group_id);
            $mapNoPhone->where('sr.dept', 'in', $group_id);
            $mapNoOpenCity->where('sr.dept', 'in', $group_id);
            $mapNoVip->where('sr.dept', 'in', $group_id);
        }
        //发单sql
        $faDanSql = $this->link()->table('qz_orders')->alias('o')->where($mapFaDan)
            ->join('qz_orders_source s', 's.orderid=o.id', 'inner')
            ->join('qz_order_source sr', 'sr.id=s.source_src_id', 'inner')
            ->join('qz_department_identify d', 'd.id=sr.dept', 'inner')
            ->field(["count(o.id) as count", "d.dept_belong as deptname","0 as value_type"])
            ->group("d.dept_belong")
            ->buildSql();
        //未拨打单
        $noPhoneSql = $this->link()->table('qz_orders')->alias('o')->where($mapNoPhone)
            ->join('qz_order_pool p', 'p.orderid=o.id', 'left')
            ->join('qz_orders_source s', 's.orderid=o.id', 'inner')
            ->join('qz_order_source sr', 'sr.id=s.source_src_id', 'inner')
            ->join('qz_department_identify d', 'd.id=sr.dept', 'inner')
            ->field(["count(o.id) as count", "d.dept_belong as deptname","1 as value_type"])
            ->group("d.dept_belong")
            ->buildSql();
        //未开站发单
        $noOpenCityFaDanSql = $this->link()->table('qz_orders')->alias('o')->where($mapNoOpenCity)
            ->join('qz_orders_source s', 's.orderid=o.id', 'inner')
            ->join('qz_order_source sr', 'sr.id=s.source_src_id', 'inner')
            ->join('qz_department_identify d', 'd.id=sr.dept', 'inner')
            ->join('qz_quyu q', 'q.cid=o.cs')
            ->field(["count(o.id) as count", "d.dept_belong as deptname","2 as value_type"])
            ->group("d.dept_belong")
            ->buildSql();
        //无会员发单
        $noVipSql =  $this->link()->table('qz_orders')->alias('o')->where($mapNoVip)
            ->join('qz_order_pool p', 'p.orderid=o.id', 'left')
            ->join('qz_orders_source s', 's.orderid=o.id', 'inner')
            ->join('qz_order_source sr', 'sr.id=s.source_src_id', 'inner')
            ->join('qz_department_identify d', 'd.id=sr.dept', 'inner')
            ->join('qz_quyu q', 'q.cid=o.cs','inner')
            ->field(["count(o.id) as count", "d.dept_belong as deptname","3 as value_type"])
            ->group("d.dept_belong")
            ->buildSql();

        return $this->link()->table($faDanSql)
            ->union($noPhoneSql)
            ->union($noOpenCityFaDanSql)
            ->union($noVipSql)
            ->field(["count","deptname","value_type"])
            ->order("value_type asc,deptname asc")
            ->select();
    }

    /**
     * @des:各部门订单转化（顶级渠道部门分组）
     * @param $begin
     * @param $end
     * @param array $group_id
     * @return mixed
     */
    public function getDepartTransFormOrder($begin, $end, $group_id = [])
    {
        $mapFaDan = new Query();
        $mapFaDan->where('o.time_real', 'between', [$begin, $end]);
        $mapFenDan = new Query();
        $mapFenDan->where('o.on',4);
        $mapFenDan->where('o.type_fw',1);
        $mapFenDan->where('c.lasttime', 'between', [$begin, $end]);
        $mapLiangFang = new Query();
        $mapLiangFang->where('r.status', 'in', [1, 2, 4]);
        $mapLiangFang->where('r.lf_time', 'between', [$begin, $end]);
        $mapQianDan = new Query();
        $mapQianDan->where('o.on', 4);
        $mapQianDan->where('o.type_fw', 'in',[1,2]);
        $mapQianDan->where('o.qiandan_addtime', 'between', [$begin, $end]);
        //需要带渠道部门权限
        if (!empty($group_id)) {
            $mapFaDan->where('sr.dept', 'in', $group_id);
            $mapFenDan->where('sr.dept', 'in', $group_id);
            $mapLiangFang->where('sr.dept', 'in', $group_id);
            $mapQianDan->where('sr.dept', 'in', $group_id);
        }
        //发单sql
        $faDanSql = $this->link()->table('qz_orders')->alias('o')->where($mapFaDan)
            ->join('qz_orders_source s', 's.orderid=o.id', 'inner')
            ->join('qz_order_source sr', 'sr.id=s.source_src_id', 'inner')
            ->join('qz_department_identify d', 'd.id=sr.dept', 'inner')
            ->field(["count(o.id) as count", "d.dept_belong as deptname", "0 as value_type"])
            ->group("d.dept_belong")
            ->buildSql();
        //分单sql
        $fenDanSql = $this->link()->table('qz_orders')->alias('o')->where($mapFenDan)
            ->join('qz_order_csos_new c','c.order_id=o.id','left')
            ->join('qz_orders_source s', 's.orderid=o.id','inner')
            ->join('qz_order_source sr','sr.id=s.source_src_id','inner')
            ->join('qz_department_identify d','d.id=sr.dept','inner')
            ->field(["count(distinct(o.id)) as count", "d.dept_belong as deptname", "1 as value_type"])
            ->group("d.dept_belong")
            ->buildSql();
        //量房sql
        $liangFangSql = $this->link()->table('qz_orders')->alias('o')->where($mapLiangFang)
            ->join('qz_order_company_review r', 'r.orderid=o.id', 'left')
            ->join('qz_orders_source s', 's.orderid=o.id', 'inner')
            ->join('qz_order_source sr', 'sr.id=s.source_src_id', 'inner')
            ->join('qz_department_identify d', 'd.id=sr.dept', 'inner')
            ->field(["count(distinct(o.id)) as count", "d.dept_belong as deptname", "2 as value_type"])
            ->group("d.dept_belong")
            ->buildSql();
        //签单sql
        $qianDanSql = $this->link()->table('qz_orders')->alias('o')->where($mapQianDan)
            ->join('qz_orders_source s', 's.orderid=o.id', 'inner')
            ->join('qz_order_source sr', 'sr.id=s.source_src_id', 'inner')
            ->join('qz_department_identify d', 'd.id=sr.dept', 'inner')
            ->field(["count(o.id) as count", "d.dept_belong as deptname", "3 as value_type"])
            ->group("d.dept_belong")
            ->buildSql();
        return $this->link()->table($faDanSql)
            ->union($fenDanSql)
            ->union($liangFangSql)
            ->union($qianDanSql)
            ->field(["count","deptname","value_type"])
            ->select();
    }

}