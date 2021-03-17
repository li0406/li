<?php
// +----------------------------------------------------------------------
// | Orders 订单表
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Db;
use think\Model;
use think\db\Where;
use think\db\Query;
use app\index\enum\OrderCodeEnum;

class Orders extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_orders';

    /**
     * 关联获取分配用户信息
     *
     * @return \think\model\relation\HasMany
     */
    public function companys()
    {
        return $this->hasMany(OrderInfo::class, 'order', 'id')->field('order,com as company_id,com,isread,allot_source,addtime')->with('company');
    }

    public function companyReview()
    {
        return $this->hasMany(OrderCompanyReview::class, 'orderid', 'id')->with('company');
    }

    /**
     * 关联订单城市
     *
     * @return \think\model\relation\HasOne
     */
    public function city()
    {
        return $this->hasOne(Quyu::class, 'cid', 'cs')->field('cid,cname');
    }

    public function allotNum()
    {
        return $this->hasOne(Allotnum::class, 'order_id', 'id');
    }

    /**
     * 关联订单城市
     *
     * @return \think\model\relation\HasOne
     */
    public function area()
    {
        return $this->hasOne(Area::class, 'qz_areaid', 'qx')->field('qz_areaid,qz_area');
    }

    /**
     * 关联获取分配用户信息
     *
     * @return \think\model\relation\HasOne
     */
    public function qdcompanys()
    {
        return $this->hasOne(User::class, 'id', 'qiandan_companyid')->field('id,jc,qc');
    }

    /**
     * 关联获取订单方式
     *
     * @return \think\model\relation\HasOne
     */
    public function fangshi()
    {
        return $this->hasOne(Fangshi::class, 'id', 'fangshi');
    }

    /**
     * 关联获取订单被操作过量房状态的装修公司
     *
     * @return \think\model\relation\HasMany
     */
    public function orderCompanyInfo()
    {
        return $this->hasMany(OrderInfo::class, 'order', 'id')->field('order,com,allot_source');
    }


    /**
     * 关联获取订单被操作过量房状态的装修公司
     *
     * @return \think\model\relation\HasMany
     */
    public function liangfangCompanys()
    {
        return $this->hasMany(OrderCompanyReview::class, 'orderid', 'id')->field('orderid,comid,status,time,lf_time')->where(new Where(['status' => ['in', [1, 2, 4]]]))->with('company');
    }

    /**
     * 关联获取订单量房所有信息
     *
     * @return \think\model\relation\HasMany
     */
    public function liangfanglist()
    {
        return $this->hasMany(OrderCompanyReview::class, 'orderid', 'id')->field('orderid,comid,status,liangfang,time,lf_time');
    }

    public function OrderCityInfo()
    {
        return $this->hasOne(OrderCityInfo::class, 'city_id', 'cs');
    }

    public function OrderTel()
    {
        return $this->hasOne(OrderTel8::class, 'orderid', 'id')->bind('tel8');
    }

    public function getOrderInfoById($order_id, $field = '*', $with = [])
    {
        return $this->field($field)->with($with)->where('id', $order_id)->find();
    }

    private function buildQiandanBaseSql($map)
    {
        unset($map['_complex'], $map['down']);
        $qiandanMap = [
            'qiandan_companyid' => ['gt', 0],
            'qiandan_addtime' => ['gt', 0],
            'type_fw' => ['in', [1, 2]],
            'on' => 4,
        ];

        if (isset($map['o.type_fw'])) {
            $qiandanMap['type_fw'] = $map['o.type_fw'];
            unset($map['o.type_fw']);
        }

        if (isset($map['o.qiandan_companyid']) && !empty($map['o.qiandan_companyid'])) {
            $qiandanMap['qiandan_companyid'] = $map['o.qiandan_companyid'];
            unset($map['o.qiandan_companyid']);
        }

        if (isset($map['o.qiandan_addtime'])) {
            $qiandanMap['qiandan_addtime'] = $map['o.qiandan_addtime'];
            unset($map['o.qiandan_addtime']);
        }

        if (isset($map['o.qiandan_chktime'])) {
            $qiandanMap['qiandan_chktime'] = $map['o.qiandan_chktime'];
            unset($map['o.qiandan_chktime']);
        }

        if (isset($map['o.time_real'])) {
            $qiandanMap['time_real'] = $map['o.time_real'];
            unset($map['o.time_real']);
        }

        if (isset($map['o.cs'])) {
            $qiandanMap['cs'] = $map['o.cs'];
            unset($map['o.cs']);
        }

        return [
            'buildSql' => db('orders')->where(new Where($qiandanMap))->order('time_real desc')->buildSql(),
            'map' => $map
        ];
    }

    /**
     * 获取签单列表
     *
     * @param $map
     * @param null $page
     * @param null $pageSize
     * @param string $field
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getQiandanList($map, $page = null, $pageSize = null, $with = [], $field = 'o.*')
    {
        if ($page == 0) {
            $offset = 0;
        } else {
            $offset = $pageSize * ($page - 1);
        }
        $baseResult = $this->buildQiandanBaseSql($map);
        return db('orders')
            ->table($baseResult['buildSql'])
            ->alias('o')
            ->field($field)
            ->where(new Where($baseResult['map']))
            ->join('user u', 'u.id = o.qiandan_companyid')
            ->join('order_csos_new ck', 'ck.order_id = o.id')
            ->leftJoin('quyu q', 'q.cid = o.cs')
            ->leftJoin('area a', 'a.qz_areaid = o.qx')
            ->with($with)
            ->limit($offset, $pageSize)
            ->order('publish_time desc')
            ->select();
    }

    /**
     * 获取签单数量
     *
     * @param $map
     * @return float|string
     */
    public function getQiandanCount($map)
    {
        $baseResult = $this->buildQiandanBaseSql($map);
        return db('orders')
            ->table($baseResult['buildSql'])
            ->alias('o')
            ->where(new Where($baseResult['map']))
            ->join('user u', 'u.id = o.qiandan_companyid')
            ->join('order_csos_new ck', 'ck.order_id = o.id')
            ->count('o.id');
    }

    /**
     * 获取签单订单数量
     *
     * @param $map
     * @return float|string
     */
    public function qdOrdersCount($map)
    {
        $map['o.on'] = 4;
        if (!isset($map['o.type_fw'])) {
            $map['o.type_fw'] = ['in', [1, 2]];
        }
        if (!isset($map['o.qiandan_companyid'])) {
            $map['o.qiandan_companyid'] = ['gt', 0];
        }
        if (!isset($map['o.qiandan_addtime'])) {
            $map['o.qiandan_addtime'] = ['gt', 0];
        }
        if (!isset($map['o.qiandan_status'])) {
            $map['o.qiandan_status'] = ['egt', 0];
        }

        if (isset($map['search'])) {
            $search = $map['search'];
            unset($map['search']);
        }

        if (isset($search)) {
            if (ctype_digit((string)$search)) {
                if (strlen($search) > 15) {             // 搜索订单（15位以上）
                    $map['o.id'] = $search;
                } else {                               // 装修公司ID
                    $map['u.id'] = $search;
                }
            } else {
                $map['u.jc|u.qc|o.xiaoqu'] = ['like', '%' . $search . '%'];
            }
        }

        $count = $this->alias('o')->force("idx_qdcom_cs_qdstatus_qdaddtime")->where(new Where($map));
        return $count
            ->join('user u', 'u.id = o.qiandan_companyid')
            ->count('o.id');
    }

    /**
     * 获取签单订单列表
     *
     * @param $map
     * @param null $page
     * @param null $pageSize
     * @param array $with
     * @param string $field
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function qdOrdersList($map, $page = null, $pageSize = null, $with = [], $field = 'o.*')
    {
        if ($page == 0) {
            $offset = 0;
        } else {
            $offset = $pageSize * ($page - 1);
        }
        $map['o.on'] = 4;
        if (!isset($map['o.type_fw'])) {
            $map['o.type_fw'] = ['in', [1, 2]];
        }
        if (!isset($map['o.qiandan_companyid'])) {
            $map['o.qiandan_companyid'] = ['gt', 0];
        }
        if (!isset($map['o.qiandan_addtime'])) {
            $map['o.qiandan_addtime'] = ['gt', 0];
        }
        if (!isset($map['o.qiandan_status'])) {
            $map['o.qiandan_status'] = ['egt', 0];
        }

        if (isset($map['search'])) {
            $search = $map['search'];
            unset($map['search']);
        }

        if (isset($search)) {
            if (ctype_digit((string)$search)) {
                if (strlen($search) > 15) {             // 搜索订单（15位以上）
                    $map['o.id'] = $search;
                } else {                               // 装修公司ID
                    $map['u.id'] = $search;
                }
            } else {
                $map['u.jc|u.qc|o.xiaoqu'] = ['like', '%' . $search . '%'];
            }
        }

        $result = $this->alias('o')->force("idx_qdcom_cs_qdstatus_qdaddtime")->where(new Where($map));
        return $result
            ->field($field)
            ->join('user u', 'u.id = o.qiandan_companyid')
            ->with($with)
            ->limit($offset, $pageSize)
            ->order('qiandan_addtime desc')
            ->select();
    }

    /**
     * 获取订单详细信息
     *
     * @param $map
     * @param array $with
     * @return array|null|\PDOStatement|string|Model
     */
    public function orderinfo($map, $with = [], $field = false)
    {
        if ($field === false) {
            $field = ['o.id', 'o.name', 'o.sex', 'o.time_real', 'o.time_real time', 'FROM_UNIXTIME(o.time,"%Y-%m-%d %H:%i:%s") time_pub', 'o.tel', 'o.xiaoqu', 'q.cname as cs', 'a.qz_area as area', 'o.mianji', 'o.yt', 'o.fen_customer', 'au.name fen_customer_name', 'o.text', 'hx.name huxing', 'fg.name fengge', 'jg.name yusuan', 'fs.name fangshi', 'o.lx', 'o.lxs', 'o.type_fw', 'u.jc company_name', 'qiandan_companyid', 'u.jc qiandan_company_jc', 'u.qc qiandan_company_qc', 'u.classid as qiandan_company_classid', 'uc.cooperate_mode as qiandan_company_cooperate_mode', 'o.qiandan_jine', 'o.qiandan_mianji', 'o.qiandan_status', 'o.qiandan_addtime', 'o.qiandan_chktime', 'o.qiandan_remark', 'o.qiandan_remark_lasttime', 'o.qiandan_info','o.lng','o.lat'];
        }

        return self::alias('o')
            ->field($field)
            ->where($map)
            ->leftJoin('user u', 'u.id = qiandan_companyid')
            ->leftJoin('user_company uc', 'uc.userid = u.id')
            ->leftJoin('quyu q', 'q.cid = o.cs')
            ->leftJoin('area a', 'a.qz_areaid = o.qx')
            ->leftJoin('adminuser au', 'au.id = o.fen_customer')
            ->leftJoin('huxing hx', 'hx.id = o.huxing')
            ->leftJoin('fengge fg', 'fg.id = o.fengge')
            ->leftJoin('jiage jg', 'jg.id = o.yusuan')
            ->leftJoin('fangshi fs', 'fs.id = o.fangshi')
            ->with($with)
            ->find();
    }

    public function getOrderInfo($order_id, $with = [])
    {
        $field = [
            'o.id', 'o.name', 'o.sex', 'o.time_real', 'o.tel', 'o.xiaoqu', 'o.mianji', 'o.yt', 'o.text', 'o.ip', 'o.shi',
            'o.lx', 'o.lxs', 'o.type_fw', 'o.wx', 'o.other_contact', 'o.standby_user', 'o.dz', 'o.xiaoqu_type',
            'o.lat', 'o.lng', 'o.nf_time', 'o.keys', 'o.lftime', 'o.start', 'o.visitime', 'o.lasttime', 'wzd',
            'q.cname as city_name', 'a.qz_area as area_name', 'u.`name` as customer_name', 'new.user_name as chk_name',
            'hx.`name` huxing_name', 'fg.`name` fengge_name', 'jg.`name` yusuan_name', 'fs.`name` fangshi_name',
            'IF(allot.allot_num = 0 or allot.allot_num is null, 4, allot.allot_num) as allot_num'
        ];

        return self::alias('o')->where("o.id", $order_id)
            ->field($field)
            ->leftJoin('quyu q', 'q.cid = o.cs')
            ->leftJoin('area a', 'a.qz_areaid = o.qx')
            ->leftJoin('huxing hx', 'hx.id = o.huxing')
            ->leftJoin('fengge fg', 'fg.id = o.fengge')
            ->leftJoin('jiage jg', 'jg.id = o.yusuan')
            ->leftJoin('fangshi fs', 'fs.id = o.fangshi')
            ->leftJoin('order_allot_num allot', 'allot.order_id = o.id')
            ->leftJoin('adminuser u', 'u.id = o.customer')
            ->leftJoin('order_csos_new new', 'new.order_id = o.id')
            ->with($with)
            ->find();
    }

    // 全部订单查询条件
    public function getAllOrdersListMap($input){
        $map = new Query();
        $map->where("o.type_fw", "in", [1, 2, 3, 4]);

        // 城市
        if (!empty($input["cid"])) {
            $map->where("o.cs", $input["cid"]);
        } else if (!empty($input["citys"])) {
            $map->where("o.cs", "in", $input["citys"]);
        }

        // 日期
        if (!empty($input["date_begin"]) && !empty($input["date_end"])) {
            $map->where("o.time_real", "BETWEEN", [
                strtotime($input["date_begin"]),
                strtotime($input["date_end"]) + 86399
            ]);
        } else if (!empty($input["date_begin"])) {
            $map->where("o.time_real", "EGT", strtotime($input["date_begin"]));
        } else if (!empty($input["date_end"])) {
            $map->where("o.time_real", "ELT", strtotime($input["date_end"]) + 86399);
        }

        // 订单搜索
        if (!empty($input["order_search"])) {
            $numcal = ctype_digit($input["order_search"]);
            if ($numcal && strlen($input["order_search"]) > 15) {
                $map->where("o.id", $input["order_search"]);
            } else if ($numcal && strlen($input["order_search"]) == 11) {
                $map->where("o.tel_encrypt", telEncrypt($input["order_search"]));
            } else {
                $map->where("o.id|o.xiaoqu", "like", "%{$input['order_search']}%");
            }
        }

        // 装修公司搜索
        if (!empty($input["company_search"])) {
            $map->where("o.id", "in", function($query) use ($input) {
                $userMap = new Query();
                if (ctype_digit($input["company_search"])) {
                    $userMap->where("u.id", $input["company_search"]);
                } else {
                    $userMap->whereOr("u.jc|u.qc", "like", "%{$input['company_search']}%");
                }

                $query->table("qz_order_info")->alias("i")->where($userMap)
                    ->join("user u", "u.id = i.com", "inner")
                    ->field(["DISTINCT i.order"]);
            });
        }

        // 订单ID
        if (!empty($input["id"])) {
            $map->where("o.id", $input["id"]);
        }

        // 预算
        if (!empty($input["yusuan"])) {
            if (!is_array($input["yusuan"])) {
                $input["yusuan"] = explode(",", $input["yusuan"]);
            }

            $map->where("o.yusuan", "in", $input["yusuan"]);
        }

        // 分/赠送
        if (!empty($input["type_fw"])) {
            $map->where("o.type_fw", $input["type_fw"]);
        }

        // 装修类型
        if (!empty($input["leixing"])) {
            $map->where("o.lx", $input["leixing"]);
        }

        // 装修类型
        if (!empty($input["lxs"])) {
            $map->where("o.lxs", $input["lxs"]);
        }

        // 装修方式
        if (!empty($input["fangshi"])) {
            if (!is_array($input["fangshi"])) {
                $input["fangshi"] = explode(",", $input["fangshi"]);
            }

            $map->where("o.fangshi", "in", $input["fangshi"]);
        }

        // 区县
        if (!empty($input["area_id"])) {
            $map->where("o.qx", $input["area_id"]);
        }

        // 面积
        if (!empty($input["mianji_min"])) {
            $map->where("o.`mianji` >= " . intval($input["mianji_min"]));
        }

        // 订单面积
        if (!empty($input["mianji_max"])) {
            $map->where("o.`mianji` <= " . intval($input["mianji_max"]));
        }

        // 量房状态
        if (!empty($input["liangfang_status"])) {
            if ($input["liangfang_status"] == 1) {
                $map->where("s.lf_status", 2);
            } else {
                $map->where(function($query) use ($input) {
                    $query->where("s.lf_status", "in", [1, 3]);
                    $query->whereOr("s.lf_status", "exp", "is null");
                });
            }
        }

        // 跟单状态
        if (!empty($input["track_status"])) {
            if ($input["track_status"] == 1) {
                $map->where("s.track_num", ">", 0);
            } else {
                $map->where(function($query) use ($input) {
                    $query->where("s.track_num", 0);
                    $query->whereOr("s.track_num", "exp", "is null");
                });
            }
        }

        return $map;
    }


    /**
     * 获取所有订单-销售 订单总数
     * @param  [type]  $map        [description]
     * @param boolean $superAdmin [description]
     * @return [type]              [description]
     */
    public function getAllOrdersListCount($input, $superAdmin = false){
        $map = $this->getAllOrdersListMap($input);

        $dbQuery = $this->alias("o")->where($map)
            ->join("order_stats s", "s.order_id = o.id", "left");

        // 如果是超级管理员 黑名单的订单也显示
        // 不是超级管理员 黑名单的号码的订单不显示
        // if ($superAdmin == false) {
        //     $dbQuery->join("order_blacklist b", "b.tel_encrypt = o.tel_encrypt and b.status = 0", "left");
        // }
        
        // 如果存在查询时间则使用强制索引
        if (!empty($input["date_begin"]) && !empty($input["date_end"])) {
            if (!empty($input["cid"]) || !empty($input["citys"])) {
               $dbQuery->force("idx_time_real_cs");
            } else {
                $dbQuery->force("idx_time_real");
            }
        }

        return $dbQuery->count("o.id");
    }

    /**
     * 获取所有订单-销售 订单总数
     * @param  [type]  $map        [description]
     * @param boolean $superAdmin [description]
     * @return [type]              [description]
     */
    public function getAllOrdersList($input, $superAdmin = false, $offset = 0, $length = 0){
        $map = $this->getAllOrdersListMap($input);

        $dbQuery = $this->alias("o")->where($map)
            ->join("order_stats s", "s.order_id = o.id", "left");

        // 如果是超级管理员 黑名单的订单也显示
        // 不是超级管理员 黑名单的号码的订单不显示
        // if ($superAdmin == false) {
        //     $dbQuery->join("order_blacklist b", "b.tel_encrypt = o.tel_encrypt and b.status = 0", "left");
        // }

        // 如果存在查询时间则使用强制索引
        if (!empty($input["date_begin"]) && !empty($input["date_end"])) {
            if (!empty($input["cid"]) || !empty($input["citys"])) {
               $dbQuery->force("idx_time_real_cs");
            } else {
                $dbQuery->force("idx_time_real");
            }
        }

        $order = "o.time_real desc";
        $order2 = "t.time_real desc";
        if (!empty($input["order_by"])) {
            if ($input["order_by"] == 2) {
                $order = "s.track_new_time desc,o.time_real desc";
                $order2 = "t.track_new_time desc,t.time_real desc";
            }
        }

        $subSql = $dbQuery->field([
                "o.id", "o.fengge", "o.type", "o.name", "o.sex", "o.tel", "o.qx", "o.time_real", "o.time_real time",
                "o.lx", "o.huxing", "o.fangshi", "o.yusuan", "o.mianji", "o.xiaoqu", "o.type_fw", "o.qiandan_status",
                "o.cs", "o.qx","o.lxs", "s.lf_status", "s.track_num as track_count", "s.track_new_time"
            ])
            ->limit($offset, $length)
            ->order($order)
            ->buildSql();

        return $this->table($subSql)->alias("t")
            ->join("quyu q", "q.cid = t.cs", "left")
            ->join("area a", "a.qz_areaid = t.qx", "left")
            ->join("fangshi f", "f.id = t.fangshi", "left")
            ->join("jiage j", "j.id = t.yusuan", "left")
            ->field([
                "t.*",
                "q.cname city_name", "a.qz_area area_name",
                "f.`name` fangshi_name", "j.`name` yusuan_name"
            ])
            ->order($order2)
            ->select();
    }

    /**
     * 获取订单数量
     *
     * @param $map
     * @return float|string
     */
    public function getOrdersCount($map)
    {
        $dbQuery = self::alias("o")->where(new Where($map));
        return $dbQuery
            ->join('quyu q', 'q.cid = o.cs')
            ->count('o.id');
    }

    /**
     * 获取订单列表
     *
     * @param array $map
     * @param array $with
     * @param int $offset
     * @param int $length
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getOrdersList($map, $with = [], $page = null, $pageSize = null, $field = 'o.id,o.on,o.on_sub,o.type_fw,o.fengge,o.type,o.name,o.sex,o.tel,o.qx,o.time_real,o.time,o.lx,o.mianji,o.xiaoqu,q.cname,a.qz_area')
    {
        if ($page == 0) {
            $offset = 0;
        } else {
            $offset = $pageSize * ($page - 1);
        }

        $dbQuery = self::alias('o')->where(new Where($map));

        return $dbQuery->with($with)
            ->field($field)
            ->join('quyu q', 'q.cid = o.cs')
            ->join('area a', 'a.qz_areaid = o.qx', 'LEFT')
            ->order('o.time desc')
            ->limit($offset, $pageSize)
            ->select();
    }

    public function getOrdersCountByCity($city, $limit = 5, $order = 'o.time_real desc', $field = 'o.id order_id,o.time_real order_time')
    {
        if (empty($city)) {
            return [];
        }
        $where[] = [
            'o.time_real', 'between', strtotime('-30 day') . ',' . time()
        ];
        $where[] = ['o.on', '=', 4,];
        $where[] = ['o.type_fw', 'in', [1, 2],];
        if (is_array($city)) {
            $where[] = ['cs', 'in', $city];
        } else {
            $where[] = ['cs', '=', $city];
        }
        return $this->alias('o')
            ->field($field)
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->select();
    }

    public function getQianOrdersOrdersByCity($city, $limit = 5, $order = 'o.time_real desc', $field = 'o.id order_id,o.qiandan_status,o.qiandan_companyid,o.qiandan_addtime order_time')
    {
        if (empty($city)) {
            return [];
        }
        $where[] = ['o.time_real', 'between', strtotime('-100 day') . ',' . time()];
        $where[] = ['o.qiandan_status', '>=', 0];
        $where[] = ['o.qiandan_companyid', '>', 0];
        $where[] = ['o.on', '=', 4,];
        $where[] = ['o.type_fw', 'in', [1, 2]];
        if (is_array($city)) {
            $where[] = ['cs', 'in', $city];
        } else {
            $where[] = ['cs', '=', $city];
        }
        return $this->alias('o')
            ->field($field)
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->select();
    }

    public function getRemedyOrderMap($input){
        $map = new Query();
        $map->where("o.on", 4);

        $force_csos = "";

        // 城市
        if (!empty($input["cs"])) {
            $map->where("o.cs", $input["cs"]);
        } else if (!empty($input["citys"])) {
            $map->where("o.cs", "in", $input["citys"]);
        }

        // 区县
        if (!empty($input["qx"])) {
            $map->where("o.qx", $input["qx"]);
        }

        // 查询时间
        if (!empty($input["start"]) && !empty($input["end"])) {
            $map->where("csos.lasttime", "between", [
                strtotime($input["start"]),
                strtotime($input["end"]) + 86399
            ]);

            $force_csos = " force index(idx_csos_new_lasttime)";
        }

        // 查询订单状态
        if (!empty($input["status"])) {
            $map->where("o.type_fw", $input["status"]);
        } else {
            $map->where("o.type_fw", "in", [1, 2]);
        }

        // 类型
        if (!empty($input["lx"])) {
            $map->where("o.lx", $input["lx"]);
        }

        // 其它查询条件
        if (!empty($input["condition"])) {
            if (is_numeric($input["condition"])) {
                $map->where("o.id", $input["condition"]);
            } else {
                $map->where("o.xiaoqu", "like", $input["condition"] . "%");
            }
        }

        $subMap = new Query();
        $subMap->whereColumn("n.allot_num", ">", "t.fen_com");
        $subMap->where("r.order_id", "exp", "is null");

        return [
            "map" => $map,
            "subMap" => $subMap,
            "force_csos" => $force_csos
        ];
    }

    // 获取销售补单数量
    public function getRemedyOrderCount($input){
        $map = $this->getRemedyOrderMap($input);

        $subSql = $this->alias("o")->where($map["map"])
            ->join("qz_order_csos_new csos". $map["force_csos"], "csos.order_id = o.id", "inner")
            ->join("order_info i", "i.order = o.id", "inner")
            ->field(["o.id", "count(i.com) as fen_com"])
            ->group("o.id")
            ->buildSql();

        $count = $this->table($subSql)->alias("t")->where($map["subMap"])
            ->join("order_allot_num n", "n.order_id = t.id", "inner")
            ->join("order_unremedy r", "r.order_id = t.id", "left")
            ->field(["t.*", "n.allot_num"])
            ->count("t.id");

        return $count;
    }

    // 获取销售补单数据
    public function getRemedyOrderList($input, $offset = 0, $limit = 0){
        $map = $this->getRemedyOrderMap($input);

        $subSql = $this->alias("o")->where($map["map"])
            ->join("qz_order_csos_new csos" . $map["force_csos"], "csos.order_id = o.id", "inner")
            ->join("order_info i", "i.order = o.id", "inner")
            ->field([
                "o.id", "count(i.com) as fen_com", "csos.lasttime as push_time",
                "o.time_real", "o.xiaoqu", "o.`name`", "o.lx", "o.yusuan", "o.mianji", "o.lftime", "o.type_fw",
                "o.cs", "o.qx",
            ])
            ->group("o.id")
            ->buildSql();

        $subSql = $this->table($subSql)->alias("t")->where($map["subMap"])
            ->join("order_allot_num n", "n.order_id = t.id", "inner")
            ->join("order_unremedy r", "r.order_id = t.id", "left")
            ->field(["t.*", "n.allot_num"])
            ->order("t.time_real desc")
            ->limit($offset, $limit)
            ->buildSql();

        $list = $this->table($subSql)->alias("t2")
            ->join("quyu q", "q.cid = t2.cs", "left")
            ->join("area area", "area.qz_areaid = t2.qx", "left")
            ->join("jiage jg", "jg.id = t2.yusuan", "left")
            ->field([
                "t2.*", "concat(q.cname, area.qz_area) as city",
                "jg.`name` as yusuan"
            ])
            ->order("t2.time_real desc")
            ->select();

        return $list;
    }

    public function getOrdersInfo($where, $field = '*', $with = [])
    {
        return $this->field($field)->where($where)->with($with)->select();
    }

    /**
     * 质检已分配订单列表
     * @param  [type]  $type       [description]
     * @param  [type]  $time_begin [description]
     * @param  [type]  $time_end   [description]
     * @param integer $offset [description]
     * @param integer $limit [description]
     * @return [type]              [description]
     */
    public function getAuditOrderList($type, $cityIds, $time_begin, $time_end, $offset = 0, $limit = 0)
    {
        $map = new Query();
        $map->where("o.on", 4);
        $map->where("o.type_fw", "IN", [1, 2, 3, 4]);
        $map->where("o.cs", "in", $cityIds);
        $map->where("a.time", "BETWEEN", [$time_begin, $time_end]);
        $map->where("orp.id is null or orp.is_rob = 2");

        $dbQeury = db("order_docking")->alias("a")->where($map)
            ->join("orders o", "a.order_id = o.id", "inner")
            ->join("order_csos_new new", "new.order_id = o.id", "inner")
            ->join("quyu q", "q.cid = o.cs", "inner")
            ->join("area area", "area.qz_areaid = o.qx", "inner")
            ->join("order_allot_num an", "an.order_id = o.id", "left")
            ->join("order_rob_pool orp", "orp.order_id = o.id", "left");

        if ($type == "count") {
            $result = $dbQeury->count("o.id");
        } else {
            $fields = [
                "o.id", "o.time_real", "o.xiaoqu", "o.name", "o.sex", "o.type_fw",
                "o.lx", "o.huxing", "o.fangshi", "o.yusuan", "o.mianji",
                "o.lftime", "a.time", "new.lasttime",
                "q.cname as city_name", "area.qz_area as area_name",
                "if(an.allot_num is null, 4, an.allot_num) as allot_num"
            ];

            $subSql = $dbQeury->field($fields)
                ->order("a.time desc")
                ->limit($offset, $limit)
                ->buildSql();

            $result = db()->table($subSql)->alias("t")
                ->join("order_info i", "i.`order` = t.id", "left")
                ->join("fangshi f", "f.id = t.fangshi", "left")
                ->join("jiage j", "j.id = t.yusuan", "left")
                ->field("t.*,count(i.id) already_allot_num,f.name as fangshi_name,j.name as yusuan_name")
                ->order("t.time desc")
                ->group("t.id")
                ->select();
        }

        return $result;
    }

    // 更新订单已读状态
    public function updateComAllread($order_id, $order2com_allread = 0)
    {
        $this->where("id", $order_id)->data("order2com_allread", $order2com_allread)->update();
    }


    public function getCityOrderStat($city,$begin,$end,$yusuan,$lx,$fangshi )
    {
        $map[] = ["q.cname","IN",$city];
        $map[] = ["a.time_real","EGT",$begin];
        $map[] = ["a.time_real","ELT",$end];


        $field = "a.cs,q.cname,count(a.id) as count,count(if(a.on = 4,1,null)) as fen_count";
        if (!empty($yusuan)) {
            $field .=",count(if(a.yusuan = ".$yusuan.",1,null)) as yusuan_count";
        }

        if (!empty($lx)) {
            $field .=",count(if(a.lx = ".$lx.",1,null)) as lx_count";
        }

        if (!empty($fangshi)) {
            $field .=",count(if(a.fangshi = ".$fangshi.",1,null)) as fangshi_count";
        }

        return  $this->where($map)->alias("a")
             ->join("quyu q","q.cid = a.cs")
             ->field($field)
             ->group("a.cs")->select();
    }

    /**
     * 城市分单统计
     * @param int $cityid
     * @param int $starttime
     * @param int $endtime
     */
    public function orderReview($cityid = 0, $starttime = 0, $endtime = 0) {
        $cityMap = new Query();
        $orderMap = new Query();
        $qiandanMap = new Query();
        // 城市查询
        if (!empty($cityid)) {
            $cityMap->where("q.cid", $cityid);
            $orderMap->where("o.cs", $cityid);
            $qiandanMap->where("o.cs", $cityid);
        }

        // 时间查询
        if ($starttime && $endtime) {
            $orderMap->where("o.time_real", "between", [
                strtotime($starttime),
                strtotime($endtime) + 86399
            ]);

            $qiandanMap->where("i.addtime", "between", [
                strtotime($starttime),
                strtotime($endtime) + 86399
            ]);
        } else if ($starttime && !$endtime) {
            $orderMap->where("o.time_real", ">=", strtotime($starttime));

            $qiandanMap->where("i.addtime", ">=", strtotime($starttime));
        } elseif (!$starttime && $endtime) {
            $orderMap->where("o.time_real", "<=", strtotime($endtime) + 86399);

            $qiandanMap->where("i.addtime", "<=", strtotime($endtime) + 86399);
        }

        // 发单查询
        $qiandanMap->where("o.on", 4);
        $qiandanMap->where("i.type_fw", "in", [1, 2]);
        $orderSql = Db::name("orders")->alias("o")->where($orderMap)
            ->field([
                "o.cs",
                "count(o.id) fadan",
                "count(IF (o.`on` = 4 AND o.type_fw = 1, 1, NULL)) AS fen",
                "count(IF (o.`on` = 4 AND o.type_fw = 2, 1, NULL)) AS zen",
                "count(IF (o.`on` = 4 AND o.type_fw = 3, 1, NULL)) AS fen_other",
                "count(IF (o.`on` = 4 AND o.type_fw = 4, 1, NULL)) AS zen_other",
                "count(IF (o.`on` = 0 AND o.on_sub = 10, 1, NULL)) AS xin",
                "count(IF (o.`on` = 0 AND o.on_sub = 9, 1, NULL)) AS cixin"
            ])
            ->group("o.cs")
            ->buildSql();

        // 签单查询
        $qiandanSql = Db::name("orders")->alias("o")->where($qiandanMap)
            ->join("order_info i", "i.`order` = o.id", "inner")
            ->field([
                "o.cs",
                "count(distinct o.id) as validnum",
                "count(distinct if(o.qiandan_companyid = i.com, o.id, null)) as qiandan",
                "count(distinct if(o.type_fw = 1, o.id, null)) as csos_fendan",
                "count(distinct if(o.type_fw = 2, o.id, null)) as csos_zendan"

            ])
            ->group("o.cs")
            ->buildSql();

        // 基础城市查询
        $cityMap->where("q.is_open_city", 1);
        $cityMap->where(function($query){
            $query->where("t1.fadan", "gt", 0);
            $query->whereOr("t2.validnum", "gt", 0);
        });

        $list = Db::name("quyu")->alias("q")->where($cityMap)
            ->join([$orderSql => "t1"], "t1.cs = q.cid", "left")
            ->join([$qiandanSql => "t2"], "t2.cs = q.cid", "left")
            ->field([
                "q.cid as cs", "q.cname", "q.px_abc",
                "t1.fadan", "t1.fen", "t1.zen", "t1.fen_other", "t1.zen_other", "t1.xin", "t1.cixin",
                "t2.validnum", "t2.qiandan","t2.csos_fendan","t2.csos_zendan"
            ])
            ->order("q.px_abc asc")
            ->select();

        return $list;
    }

    public function getQianDanOrderCount($cs, $input)
    {
        $map = $this->getQianDanMap($cs, $input);
        if (empty($map)) {
            return [];
        }

        $force = "";
        if (empty($input["condition"])) {
            $force = "idx_qdcom_cs_qdstatus_qdaddtime";
        }

        return $this->alias("o")->force($force)->where($map)
            ->join("qz_user u", "o.qiandan_companyid = u.id")
            ->join("qz_user_company c", "c.userid = u.id")
            ->count("o.id");
    }

    public function getQianDanOrderList($cs, $input, $offset, $limit)
    {
        $map = $this->getQianDanMap($cs, $input);
        if (empty($map)) {
            return [];
        }

        $force = "";
        if (empty($input["condition"])) {
            $force = "idx_qdcom_cs_qdstatus_qdaddtime";
        }

        $buildSql = $this->alias("o")->force($force)->where($map)
            ->field("o.id,o.qiandan_addtime,o.time_real,o.cs,o.qx,o.on,o.type_fw,o.xiaoqu,o.mianji,o.name,o.sex,o.qiandan_status,o.qiandan_companyid,u.jc,u.qc,o.qiandan_jine,u.classid,c.cooperate_mode")
            ->join("qz_user u", "o.qiandan_companyid = u.id")
            ->join("qz_user_company c", "c.userid = u.id")
            ->limit($offset, $limit)
            ->order("qiandan_addtime desc,qiandan_status desc")->buildSql();

        return $this->table($buildSql)->alias("t")
            ->join("qz_quyu q", "q.cid = t.cs")
            ->join("qz_area a", "a.qz_areaid = t.qx")
            ->field("t.*,q.cname,a.qz_area")
            ->select();
    }

    public function getQianDanMap($cs, $input)
    {
        $query = new Query();
        if (empty($input['condition'])) {
            $query->where('o.qiandan_addtime', 'between', [$input['start'], $input['end']]);
        }

        $query->where('o.qiandan_companyid', '>', 0);
        $query->where('o.qiandan_status', '>=', 0);
        $query->where('o.on', 4);
        $query->where('o.type_fw', 'in', [1, 2]);

        if (!empty($input['condition'])) {
            $condition = $input['condition'];
            $query->where(function ($q) use ($condition) {
                $map[] = ["o.id", "=", trim($condition)];
                $map[] = ["o.xiaoqu", "like", '%' . trim($condition) . '%'];
                $q->whereOr($map);
            });
        }

        if (!empty($input['status'])) {
            //empty判断区分不了空和0 , 所以需要传值后重置
            if($input['status'] == 3){
                $input['status'] = 0;
            }
            $query->where('o.qiandan_status', $input['status']);
        }

        if (!empty($input['state'])) {
            $query->where('o.type_fw', $input['state']);
        }

        if (!empty($input['company'])) {
            $condition = $input['company'];
            $query->where(function ($q) use ($condition) {
                $map[] = ["u.qc", "like", '%' . trim($condition) . '%'];
                $map[] = ["u.jc", "like", '%' . trim($condition) . '%'];
                $q->whereOr($map);
            });
        }

        if (!empty($input['cs'])) {
            if (in_array($input['cs'], $cs)) {
                $query->where('o.cs', $input['cs']);
            } else {
                return [];
            }
        } else {
            $query->where('o.cs', 'in', $cs);
        }

        if (!empty($input['qian'])) {
            if ($input['qian'] == 1) {
                $query->where(function ($q) {
                    $map[] = ["u.classid", '=', 6];
                    $map[] = ["c.cooperate_mode", '=', 2];
                    $q->whereOr($map);
                });
            } else {
                $query->where('u.classid', 3);
                $query->where('c.cooperate_mode', 1);
            }
        }
        return $query;
    }


    public function getQiandanOrderInfo($order_id, $with = [])
    {
        $field = [
            'o.id', 'o.name', 'o.sex', 'o.time_real', 'o.tel', 'o.xiaoqu', 'o.mianji', 'o.yt', 'o.text', 'o.shi','o.qiandan_addtime',
            'o.lx', 'o.lxs', 'o.type_fw', 'o.wx', 'o.other_contact', 'o.dz', 'o.xiaoqu',
            'o.lat', 'o.lng', 'o.nf_time', 'o.keys', 'o.qiandan_jine', 'o.qiandan_info', 'o.qiandan_chktime', 'o.qiandan_remark_lasttime',
            'q.cname as city_name', 'a.qz_area as area_name', 'o.qiandan_status',
            'hx.`name` huxing_name', 'fg.`name` fengge_name', 'jg.`name` yusuan_name', 'fs.`name` fangshi_name','o.qiandan_companyid'
        ];

        return self::alias('o')->where("o.id", $order_id)
            ->field($field)
            ->leftJoin('quyu q', 'q.cid = o.cs')
            ->leftJoin('area a', 'a.qz_areaid = o.qx')
            ->leftJoin('huxing hx', 'hx.id = o.huxing')
            ->leftJoin('fengge fg', 'fg.id = o.fengge')
            ->leftJoin('jiage jg', 'jg.id = o.yusuan')
            ->leftJoin('fangshi fs', 'fs.id = o.fangshi')
            ->leftJoin('adminuser u', 'u.id = o.customer')
            ->with($with)
            ->find();
    }


    public function getOrderByCompany($search, $start, $end)
    {
        $where = [
            ['time_real', 'between', [$start, $end]],
            ['on', '=', 4],
            ['type_fw', 'in', [1, 2, 3, 4]],
        ];
        $buildSql = $this->field('id,xiaoqu')->where($where)->buildSql();
        return $this->table($buildSql)->alias('o')
            ->field('o.id')
            ->join('qz_order_info i', 'i.order = o.id', 'left')
            ->join('qz_user u', 'u.id = i.com', 'left')
            ->where([['u.qc|u.jc|o.xiaoqu', 'like', '%' . $search . '%']])
            ->group('o.id')
            ->select();
    }
}