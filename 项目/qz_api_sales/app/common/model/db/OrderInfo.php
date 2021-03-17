<?php
// +----------------------------------------------------------------------
// | OrderInfo 订单分配记录表
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Db;
use think\Model;
use think\db\Where;
use think\db\Query;

class OrderInfo extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_order_info';

    /**
     * 获取具体公司信息
     *
     * @return \think\model\relation\HasOne
     */
    public function company()
    {
        return $this->hasOne('User', 'id', 'com')->field('id,jc as company_name,jc,qc,classid')->bind('jc,qc');
    }

    /**
     * 获取订单发单时间
     *
     * @return \think\model\relation\HasOne
     */
    public function orderTime()
    {
        return $this->hasOne('Orders', 'id', 'order')->field('id,time,time as orders_time')->bind('orders_time');
    }

    /**
     * 阅单时间差获取器
     *
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getDiffTimeAttr($value, $data)
    {
        if (!empty($data['diff_time'])) {
            $timeNum = $data['diff_time'];
            $day = floor($timeNum / 86400);

            $hourNum = $timeNum % 86400;
            $hour = floor($hourNum / 3600);

            $minuteNum = $hourNum % 3600;
            $minute = floor($minuteNum / 60);

            $second = $minuteNum % 60;
            return $day . '天' . $hour . '时' . $minute . '分' . $second . '秒';
        }
        return '';
    }

    /**
     * 根据条件获取商家订单分配数量
     *
     * @param array $map
     * @return array|null|\PDOStatement|string|Model
     */
    public function getOrderInfoCount($map = [], $having = "")
    {
        unset($map['down']);

        $result = self::alias('i')
            ->where(new Where($map))
            ->join('qz_orders o', 'o.id = i.order')
            ->join('qz_user u', 'u.id = i.com');

        if (!isset($map['i.order'])) {
            $result->field('count(IF(l.`status` in (1,2,4),1,NULL)) as choose_liangfang');
            $result->field('count(IF(l.`status` = 3,1,NULL)) as choose_no_liangfang');
            $result->leftJoin('order_company_review l', 'l.orderid = i.order')->group('i.order');

            if (!empty($having)) {
                $result->having($having);
            }
        } else {
            $result->leftJoin('order_company_review l', 'l.orderid = i.order AND i.com = l.comid')->group('i.com');
        }

        return $result->count('o.id');
    }

    /**
     * 根据条件获取商家订单分配记录
     *
     * @param array $map
     * @param null $page
     * @param null $pageSize
     * @param string $field
     * @param array $with
     * @return array|null|\PDOStatement|string|Model
     */
    public function getOrderInfoList($map = [], $page = null, $pageSize = null, $field = 'i.*', $with = [], $having = "")
    {
        unset($map['down']);

        if ($page == 0) {
            $offset = 0;
        } else {
            $offset = $pageSize * ($page - 1);
        }

        $result = self::alias('i')
            ->field($field)
            ->where(new Where($map))
            ->join('orders o', 'o.id = i.order')
            ->join('user u', 'u.id = i.com')
            ->leftJoin('jiage y', 'y.id = o.yusuan')
            ->leftJoin('fangshi f', 'f.id = o.fangshi')
            ->leftJoin('quyu q', 'q.cid = o.cs')
            ->leftJoin('area a', 'a.qz_areaid = o.qx');

        if (!isset($map['i.order'])) {
            $result->field('count(IF(l.`status` in (1,2,4),1,NULL)) as choose_liangfang');
            $result->field('count(IF(l.`status` = 3,1,NULL)) as choose_no_liangfang');
            $result->leftJoin('order_company_review l', 'l.orderid = i.order')->group('i.order');

            if (!empty($having)) {
                $result->having($having);
            }
        } else {
            $result = $result->leftJoin('order_company_review l', 'l.orderid = i.order AND i.com = l.comid')->group('i.com');
        }

        return $result->order('i.addtime desc')->limit($offset, $pageSize)->with($with)->select();
    }

    /**
     * 根据条件获取商家订单分配数量
     *
     * @param array $map
     * @return array|null|\PDOStatement|string|Model
     */
    public function getReadOrderCount($map = [])
    {
        unset($map['orderby']);

        $result = self::alias('i')
            ->where(new Where($map))
            ->join('qz_user u', 'u.id = i.com')
            ->count('i.id');
        return $result;
    }

    /**
     * 获取订单未读数量统计
     * @param $order_id
     */
    public function getOrderReadStatistic($order_id){
        return Db::name("order_info")
            ->where("order", $order_id)
            ->field("count(id) as allot_num,count(if(isread = 0, null, 1)) as read_num")
            ->find();
    }

    /**
     * 根据条件获取商家订单分配记录
     *
     * @param array $map
     * @param null $page
     * @param null $pageSize
     * @param string $field
     * @param array $with
     * @return array|null|\PDOStatement|string|Model
     */
    public function getReadOrderList($map = [], $page = null, $pageSize = null, $field = 'i.*', $with = [])
    {
        if ($page == 0) {
            $offset = 0;
        } else {
            $offset = $pageSize * ($page - 1);
        }

        $order = 'fen_time desc,i.isread asc';
        if (isset($map['orderby'])) {
            $order = $map['orderby'];
        }
        unset($map['orderby']);

        $result = self::alias('i')
            ->field($field)
            ->where(new Where($map))
            ->join('user u', 'u.id = i.com')
            ->leftJoin('quyu q', 'q.cid = u.cs')
            ->limit($offset, $pageSize)
            ->with($with)
            ->order($order)
            ->select();
        return $result;
    }

    // 会员分单统计查询条件
    public function getOrderFenStatisticListQuery($input) {
        $userMap = new Query();
        $orderMap = new Query();
        $qiandanMap = new Query();

        // 管辖城市
        if (!empty($input["cs"])) {
            $userMap->where("u.cs", $input["cs"]);
            $orderMap->where("u.cs", $input["cs"]);
            $qiandanMap->where("u.cs", $input["cs"]);
        } else if (!empty($input["citys"])) {
            $userMap->where("u.cs", "in", $input["cs"]);
            $orderMap->where("u.cs", "in", $input["cs"]);
            $qiandanMap->where("u.cs", "in", $input["cs"]);
        }

        // 公司信息
        if (!empty($input["company_info"])) {
            $userMap->where(function($query) use ($input) {
                $query->where("u.id", $input["company_info"]);
                $query->whereOr("u.jc", "like", "%". $input["company_info"] ."%");
            });

            $orderMap->where(function($query) use ($input) {
                $query->where("u.id", $input["company_info"]);
                $query->whereOr("u.jc", "like", "%". $input["company_info"] ."%");
            });

            $qiandanMap->where(function($query) use ($input) {
                $query->where("u.id", $input["company_info"]);
                $query->whereOr("u.jc", "like", "%". $input["company_info"] ."%");
            });
        }

        // 查询时间
        if (!empty($input["start"]) && !empty($input["end"])) {
            $orderMap->where("i.addtime", "between", [
                strtotime($input["start"]),
                strtotime($input["end"]) + 86399,
            ]);

            $qiandanMap->where("o.qiandan_addtime", "between", [
                strtotime($input["start"]),
                strtotime($input["end"]) + 86399,
            ]);
        } else if (!empty($input["start"])) {
            $orderMap->where("i.addtime", ">=", strtotime($input["start"]));

            $qiandanMap->where("o.qiandan_addtime", ">=", strtotime($input["start"]));
        }

        // 签单时间查询
        if (!empty($input["qdstart"]) && !empty($input["qdend"])) {
            $orderMap->where("o.qiandan_addtime", "between", [
                strtotime($input["qdstart"]),
                strtotime($input["qdend"]) + 86399,
            ]);

            // 签单数量查询
            $qiandanMap->where("o.qiandan_addtime", "between", [
                strtotime($input["qdstart"]),
                strtotime($input["qdend"]) + 86399,
            ]);
        }

        // 订单分单查询
        $orderMap->where("o.on", 4);
        $orderMap->where("i.type_fw", "in", [1, 2]);
        $orderSql = Db::name("orders")->alias("o")->where($orderMap)
            ->join("order_info i", "i.`order` = o.id", "inner")
            ->join("user u", "u.id = i.com", "inner")
            ->field([
                "o.id", "i.com as company_id",
                "count(i.id) as zong",
                "count(if(i.type_fw = 1, 1, null)) as fen",
                "count(if(i.type_fw = 2, 1, null)) as zeng",
                "count(if(i.type_fw = 1 and i.allot_source = 3, 1, null)) as qiang"
            ])
            ->group("i.com")
            ->buildSql();

        // 订单签单查询
        $qiandanMap->where("o.on", 4);
        $qiandanMap->where("i.type_fw", "in", [1, 2]);
        $qiandanSql = Db::name("orders")->alias("o")->where($qiandanMap)
            ->join("order_info i", "i.`order` = o.id and i.com = o.qiandan_companyid", "inner")
            ->join("user u", "u.id = i.com", "inner")
            ->field([
                "i.com as company_id",
                "count(o.qiandan_companyid) as qian",
                "sum(o.qiandan_jine) as qian_money"
            ])
            ->group("i.com")
            ->buildSql();

        // 装修公司查询
        $userMap->where("u.classid", "in", [3, 6]);
        $userMap->where("u.on", "in", [-1, 2, -3, -4]);
        $userMap->where(function($query){
            $query->where("t1.zong", "gt", 0);
            $query->whereOr("t2.qian", "gt", 0);
        });

        $userSql = Db::name("user")->alias("u")->where($userMap)
            ->join("quyu q", "q.cid = u.cs", "left")
            ->join([$orderSql => "t1"], "t1.company_id = u.id", "left")
            ->join([$qiandanSql => "t2"], "t2.company_id = u.id", "left")
            ->field([
                "u.id as com", "u.jc as company_name", "u.classid", "u.on", "u.cs", "q.cid", "q.cname",
                "t1.zong", "t1.fen", "t1.zeng", "t1.qiang",
                "t2.qian", "t2.qian_money"
            ])
            ->buildSql();

        return $userSql;
    }

    // 查询会员分单统计数量
    public function getOrderFenStatisticListCount($input) {
        $subSql = $this->getOrderFenStatisticListQuery($input);

        return Db::table($subSql)->alias("t")
            ->field([
                "count(t.com) as count",
                "sum(t.zeng) as zeng",
                "sum(t.fen) as fen",
                "sum(t.qiang) as qiang",
                "sum(t.qian) as qian",
                "sum(t.qian_money) as qian_money"
            ])
            ->find();
    }

    // 查询会员分单统计列表数据
    public function getOrderFenStatisticList($input, $offset = 0, $limit = 0) {
        $subSql = $this->getOrderFenStatisticListQuery($input);

        return Db::table($subSql)->alias("t")
            ->field("t.*")
            ->order("t.fen desc,t.zeng desc,t.com asc")
            ->limit($offset, $limit)
            ->select();
    }

    public function getUnreadOrdersListCount($map)
    {
        return Db::name('order_info')->alias('i')
            ->join('qz_orders o', 'o.id = i.`order` and o.`on` = 4')
            ->where(new Where($map))
            ->group('i.com')
            ->count();
    }

    public function getUnreadOrdersList($map, $pageIndex = 0, $pageCount = 20)
    {
        return Db::name('order_info')->alias('i')
            ->field('u.id company_id,q.cname,q.cid,u.jc company_name,count(i.id) unread_count,c.saler market')
            ->join('qz_orders o', 'o.id = i.`order` and o.`on` = 4')
            ->join('qz_user u', 'u.id = i.com')
            ->join('qz_user_company c', 'c.userid = u.id')
            ->join('qz_quyu q', 'q.cid = u.cs')
            ->where(new Where($map))
            ->group('i.com')
            ->limit($pageIndex, $pageCount)
            ->select();
    }

    public function getOrderCompany($order_id, $with = ['company'])
    {
        return $this
            ->with($with)
            ->where('order', $order_id)
            ->select();
    }

    /**
     * 根据条件获取分配信息
     * @param $where
     * @param array $with
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOrderCompanyInfo($where, $field = '*', $with = [])
    {
        return $this
            ->field($field)
            ->with($with)
            ->where(new Where($where))
            ->select();
    }

    public function delOrderCompany($where)
    {
        return $this
            ->where(new Where($where))
            ->delete();
    }

    public function getCompanyOrderNum($companys)
    {
        //默认查询2个月数据
        $time = strtotime("-2 month", strtotime(date("Y-m-d")));
        //算 分/抢/赠 只算当月的
        $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        $monthEnd = mktime(23,59,59,date("m"),date("t"),date("Y"));

        //获取公司 , 分/抢/赠
        $infoMap = [
            'i.addtime' => ['gt', $time],
            'i.com' => ['in', $companys]
        ];
        $buildSql = $this->alias('i')
            ->field('i.com id,i.addtime,i.type_fw as fw,i.allot_source,p.order_status')
            ->leftJoin('qz_order_rob_pool p', 'p.order_id = i.`order`')
            ->where(new Where($infoMap))
            ->buildSql();
        return $this->table($buildSql)->alias("t1")
            ->field("t1.id,count(if(t1.addtime >= '$monthStart' and t1.addtime <= '$monthEnd' and t1.fw = 1 and t1.allot_source <> 3,1,null)) as fen,count(if(t1.addtime >= '$monthStart' and t1.addtime <= '$monthEnd' and t1.fw = 2,1,null)) as zen,count(if(t1.addtime >= '$monthStart' and t1.addtime <= '$monthEnd' and t1.allot_source = 3 and  t1.order_status = 1 ,1,null)) as qiang")
            ->group("t1.id")
            ->select();
    }

    /**
     * 获取最新的一次分单信息
     * @param  [type] $cs [description]
     * @return [type]     [description]
     */
    public function getLastTypeFw($id, $cs)
    {
        $map = array(
            "a.order" => array("NEQ", $id),
            "a.type_fw" => array("IN", array(1)),
            "o.cs" => array("EQ", $cs)
        );
        $buildSql = $this->where(new Where($map))->alias("a")
            ->join("qz_orders o FORCE INDEX(idx_cs_on)", "o.id = a.order")
            ->order("a.id desc")
            ->field("order")
            ->limit(1)
            ->buildSql();

        return $this->table($buildSql)->alias("t")
            ->join("qz_order_info info", "info.order = t.order")
            ->join("qz_user u", "u.id = info.com")
            ->field("u.id,u.cs,u.jc")->select();
    }

    // 会员分单明细查询条件
    public function getOrderInfoPageMap($input){
        $map = new Query();

        // 城市查询
        if (!empty($input["cs"])) {
            $map->where("o.cs", $input["cs"]);
        } else if (!empty($input["citys"])) {
            $map->where("o.cs", "in", $input["citys"]);
        }

        // 分配时间查询
        if (!empty($input["start"]) && !empty($input["end"])) {
            $map->where("i.addtime", "between", [
                    strtotime($input["start"]),
                    strtotime($input["end"]) + 86399
                ]);
        }

        // 装修公司查询
        if (!empty($input["company"])) {
            $map->where("i.com", $input["company"]);
        }

        // 订单号查询
        if (!empty($input["order"])) {
            $map->where("i.order", $input["order"]);
        }

        // 订单状态查询
        if (!empty($input["fz_type"])) {
            if ($input["fz_type"] == 3) {
                $map->where("i.type_fw", 1);
                $map->where("i.allot_source", 3);
            } else {
                $map->where("i.type_fw", $input["fz_type"]);
                $map->where("i.allot_source", "<>", 3);
            }
        }

        // 小区名称查询
        if (!empty($input["xq"])) {
            $map->where("o.xiaoqu", "like", "%" .$input["xq"]. "%");
        }

        // 装修类型查询
        if (!empty($input["lx"])) {
            $map->where("o.lx", $input["lx"]);
        }

        // 装修方式查询
        if (!empty($input["fs"])) {
            $map->where("o.fangshi", $input["fs"]);
        }

        // 是否量房
        if (!empty($input["liangfang_status"])) {
            if ($input["liangfang_status"] == 1) {
                $map->where("l.status", "in", [1, 2, 4]);
            } else {
                $map->where("l.status", "not in", [1, 2, 4]);
            }
        }

        return $map;
    }

    // 会员分单明细查询数量
    public function getOrderInfoPageCount($input){
        $map = $this->getOrderInfoPageMap($input);

        $subSql = $this->alias("i")->where($map)
            ->join("orders o", "o.id = i.`order`")
            ->join("user u", "u.id = i.`com`")
            ->join("order_company_review l", "l.orderid = i.`order` and i.`com` = l.comid", "left")
            ->field(["o.id", "u.id as company_id", "i.id as order_info_id"])
            ->buildSql();

        $having = "";
        if (!empty($input["order_liangfang_status"])) {
            if ($input["order_liangfang_status"] == 1) {
                $having = "order_liangfang >= 1";
            } else {
                $having = "order_liangfang = 0";
            }
        }

        $subSql2 = Db::table($subSql)->alias("t")
            ->join("order_info ti", "ti.`order` = t.id")
            ->join("order_company_review tl", "tl.orderid = ti.`order` and ti.`com` = tl.comid", "left")
            ->field([
                "t.*",
                "count(if(tl.`status` in(1,2,4), 1, null)) as order_liangfang",
                "count(if(tl.`status` = 3, 1, null)) as order_no_liangfang",
                "count(ti.id) as order_fen_count"
            ])
            ->group("t.order_info_id")
            ->having($having)
            ->buildSql();

        return Db::table($subSql2)->alias("t2")->count("t2.id");
    }

    // 会员分单明细查询数据
    public function getOrderInfoPageList($input, $offset, $limit){
        $map = $this->getOrderInfoPageMap($input);

        $subSql = $this->alias("i")->where($map)
            ->join("orders o", "o.id = i.`order`")
            ->join("user u", "u.id = i.`com`")
            ->join("order_company_review l", "l.orderid = i.`order` and i.`com` = l.comid", "left")
            ->join("jiage y", "y.id = o.yusuan", "left")
            ->join("fangshi f", "f.id = o.fangshi", "left")
            ->join("quyu q", "q.cid = o.cs" , "left")
            ->join("area a", "a.qz_areaid = o.qx" , "left")
            ->field([
                    "o.id", "o.time_real", "o.xiaoqu", "o.cs", "o.qx", "o.mianji", "o.lx", "o.lxs", "o.type_fw", "o.order2com_allread",
                    "o.qiandan_addtime", "o.qiandan_chktime", "o.qiandan_mianji", "o.qiandan_jine", "o.qiandan_companyid", "o.qiandan_status",
                    "i.addtime as fen_time", "i.allot_source", "i.type_fw as allot_type_fw", "i.isread", "l.status",
                    "q.cname", "a.qz_area as area_name", "y.name as yusuan", "f.name as fangshi",
                    "u.id as company_id", "u.qc", "u.jc", "i.id as order_info_id"
                ])
            ->buildSql();

        $having = "";
        if (!empty($input["order_liangfang_status"])) {
            if ($input["order_liangfang_status"] == 1) {
                $having = "order_liangfang >= 1";
            } else {
                $having = "order_liangfang = 0";
            }
        }

        $subSql2 = Db::table($subSql)->alias("t")
            ->join("order_info ti", "ti.`order` = t.id")
            ->join("order_company_review tl", "tl.orderid = ti.`order` and ti.`com` = tl.comid", "left")
            ->field([
                "t.*",
                "count(if(tl.`status` in(1,2,4), 1, null)) as order_liangfang",
                "count(if(tl.`status` = 3, 1, null)) as order_no_liangfang",
                "count(ti.id) as order_fen_count"
            ])
            ->group("t.order_info_id")
            ->having($having)
            ->buildSql();

        return Db::table($subSql2)->alias("t2")
            ->limit($offset, $limit)
            ->select();
    }


    public function getFirseAllotTime($orderIds){
        return $this->where("order", "in", $orderIds)
            ->field(["`order` as order_id", "min(addtime) as first_addtime"])
            ->group("order")
            ->select();
    }

    // 获取装修公司分单数
    public function getCompanyAllotOrders($company_id, $date_begin, $date_end){
        $map = new Query();
        $map->where("o.on", 4);
        $map->where("i.com", $company_id);
        $map->where("i.addtime", "egt", strtotime($date_begin));
        $map->where("i.addtime", "elt", strtotime($date_end) + 86399);

        return $this->alias("i")->where($map)
            ->join("orders o", "o.id = i.`order`", "inner")
            ->field([
                "count(i.id) as all_orders",
                "count(if(i.type_fw = 1, 1, null)) as fen_orders",
                "count(if(i.type_fw = 2, 1, null)) as zen_orders"
            ])
            ->find();
    }

    // 订单量房统计
    public function getOrderLfStats($order_id){
        $map = new Query();
        $map->where("i.order", $order_id);

        return $this->alias("i")->where($map)
            ->join("order_company_review r", "r.orderid = i.`order` and r.comid = i.com", "left")
            ->field([
                "i.`order` as order_id",
                "count(i.id) as count",
                "count(IF(r.`status` = 1, 1, null)) as lf_num",
                "count(IF(r.`status` = 2, 1, null)) as lf_jm_num",
                "count(IF(r.`status` = 3, 1, null)) as lf_un_num",
                "count(IF(r.`status` = 4, 1, null)) as lf_qiandan_num",
                "count(IF(r.`status` not in(1, 2, 3, 4), 1, null)) as lf_unsign_num",
                "min(IF(r.`status` in (1, 2, 4) and r.lf_time > 0, r.lf_time, null)) as lf_first_time",
            ])
            ->find();
    }
}