<?php
// +----------------------------------------------------------------------
// | Users 用户表
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Db;
use think\Model;
use think\db\Query;
use think\db\Where;

class User extends Model
{
    protected $table = 'qz_user';

    /**
     * 关联获取用户城市
     *
     * @return \think\model\relation\HasOne
     */
    public function city()
    {
        return $this->hasOne('Quyu', 'cid', 'cs')->field('cid,cname');
    }

    /**
     * 关联获取用户所属区域
     *
     * @return \think\model\relation\HasOne
     */
    public function area()
    {
        return $this->hasOne('Area', 'qz_areaid', 'qx')->field('qz_areaid,qz_area as aname');
    }

    /**
     * 关联获取用户账户
     *
     * @return \think\model\relation\HasOne
     */
    public function account(){
        return $this->hasOne('UserCompanyAccount', 'user_id', 'id')->field('user_id,deposit_money');
    }

    /**
     * 获取列表
     *
     * @param array $map 查询条件
     * @param array $with 关联信息
     * @param null $page 分页
     * @param null $pageSize 分页
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getList($field = '*',$map, $with = [], $page = null, $pageSize = null)
    {
        if ($page == 0) {
            $offset = 0;
        } else {
            $offset = $pageSize * ($page - 1);
        }
        return self::field($field)->where($map)->limit($offset, $pageSize)->with($with)->select();
    }

    /**
     * 获取会员公司合同
     * @Author   liuyupeng
     * @DateTime 2019-05-20T14:29:44+0800
     */
    public function getContract($user_id){
        return self::alias('a')
            ->join('user_company b', 'a.id = b.userid')
            ->field([
                'a.id', 'a.start', 'a.end',
                'FROM_UNIXTIME( b.contract_start, "%Y-%m-%d") AS allstart',
                'FROM_UNIXTIME(b.contract_end, "%Y-%m-%d") AS allend'
            ])->where('a.id', $user_id)->find();
    }

    /**
     * 获取会员公司信息
     *
     * @param array $map 查询条件
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getInfo($map){
        return self::alias('a')
            ->field([
                'a.id', 'a.cs', 'a.qx'
            ])->where($map)->find();
    }

    public function getCompanyList($map,$company_info, $field = '*')
    {
        return Db::name('user')->field($field)->where(new Where($map))->where(function ($d) use ($company_info) {
            if (!empty($company_info)) {
                $where[] = ["id", "=", $company_info];
                $where[] = ["jc", "LIKE", "%$company_info%"];
                $d->whereOr($where);
            }
        })->select();
    }

    public function getCompanyByName($map, $field = 'id')
    {
        $where = [];
        if (!empty($map['company_name'])) {
            $where['jc'] = ['like', '%' . $map['company_name'] . '%'];
        }
        if (empty($where)) {
            return [];
        }
        return $this->field($field)->where(new Where($where))->select();
    }
	/**
     * 获取全称简称匹配到的装修公司
     *
     * @param $map
     * @param string $field
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getCompanyByNameMatchAll($map, $field = 'id')
    {
        $where = [];
        if (!empty($map['company_name'])) {
            $where['jc|qc'] = ['like', '%' . $map['company_name'] . '%'];
        }
        if (empty($where)) {
            return [];
        }
        return $this->field($field)->where(new Where($where))->select();
    }

    /**
     * 会员到期提醒PC端
     *
     * @param array $map 查询条件
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getVipUser($map)
    {
        $start = date('Y-m-d');
        $whereBase['a.classid'] = ["IN",[3,6]];
        $whereBase['a.on'] = ['<>', 0];
        $whereBase['a.start'] = ['<>', ''];
        $whereBase['a.end'] = ['<>', ''];
        if (!empty($map['cs'])) {
            $whereBase['a.cs'] = $map['cs'];
        }

        if (!empty($map['classid'])) {
            $whereBase['a.classid'] = $map['classid'];
        }

        //1.找出所有的合作的会员公司
        $buildBase = self::table('qz_user')
            ->alias('a')
            ->field('a.id,a.on,a.classid,a.jc,a.qc,a.cs,a.start,a.end,if(v.id >0,1,0) AS renew,DATEDIFF(a.end,a.start)+1 as endoffset,DATEDIFF(a.end,"'.$start.'")+1 as lastday')
            ->leftJoin('vip_renew v','v.comid = a.id and v.`start` >= a.end')
            ->join('qz_user_company c','a.id = c.userid and c.fake = 0 and a.end >= "2014-01-01"')
            ->where(new Where($whereBase));

        if (!empty($map['company'])) {
            if (ctype_digit((string)$map['company'])) {
                $buildBase = $buildBase->whereRaw('a.id = :id', ['id' => $map['company']]);
            } else {
                $buildBase = $buildBase->whereRaw('a.qc like :likeone OR a.jc like :liketwo', [
                        'likeone' => '%' . $map['company'] . '%',
                        'liketwo' => '%' . $map['company'] . '%'
                    ]
                );
            }
        }

        if (!empty($map['start']) && !empty($map['end'])) {
            $buildBase = $buildBase->
            whereRaw('a.end >= :start and a.end <= :end', ['start' => $map['start'], 'end' => $map['end']]);
        }
        $sql = $buildBase->buildSql();

        //2.将所有数据进行规整，分类出2个大类，相同的会员时间和不同的会员时间
        $sql = self::table($sql)->alias('t')
            ->field([
                't.*',
                'if (
                ( 
                  DATE_FORMAT(c.`start`,"%Y-%m-%d") = t.`start` 
                  OR 
                  DATE_FORMAT(c.`start`,"%Y-%m-%d") =  date_add(t.`start`, interval -1 day) 
                  OR 
                  DATE_FORMAT(c.`start`,"%Y-%m-%d") =  date_add(t.`start`, interval 1 day)
                ) 
                and 
                (
                  DATE_FORMAT(c.`end`,"%Y-%m-%d") = t.`end` 
                  OR 
                  DATE_FORMAT(c.`end`,"%Y-%m-%d") =  date_add(t.`end`, interval -1 day) 
                  OR 
                  DATE_FORMAT(c.`end`,"%Y-%m-%d") =  date_add(t.`end`, interval 1 day)
                ),"same","other") type',
                'c.comid',
                'c.start logstart',
                'c.end logend',
                'c.new_state',
                'c.old_state',
                'c.optime'
            ])
            ->join('qz_log_vip c','c.comid = t.id and c.start <> "" and c.end <> ""')
            ->order('optime desc')
            ->buildSql();

        $sql = self::table($sql)->alias('t1')
            ->group('t1.id,t1.type')
            ->order('t1.id,t1.optime desc')
            ->buildSql();

        // 不同会员时间关联正常会员时间，获取两次会员变动状态
        $sql = self::table($sql)->alias('t2')
            ->field('t2.*,t3.logstart old_logstart ,t3.logend old_logend')
            ->leftJoin($sql.' t3','t3.id = t2.id and t3.type = "other" and t3.on = 2')
            ->group('t2.id')
            ->buildSql();

        $sql = self::table($sql)->alias('t4')
            ->field([
                'CASE 
                WHEN (old_logstart is null and t4.on = 2) THEN "new" 
                WHEN (old_logstart is not null and t4.on = 2)  THEN "normal" 
                END mark',
                't4.*'
            ])->buildSql();

        // 根据两次会员变动时间 判断是不是续费会员
        $sql = self::table($sql)->alias('t5')
            ->field([
                'CASE 
                WHEN (mark is not null and mark ="normal" and `start` > `old_logstart`) THEN "out" 
                WHEN (mark is not null and mark ="normal" and `end` <> old_logend and `start` = `old_logstart`) THEN "in" ELSE ""
                END `status`',
                't5.*'
            ])->order('t5.on desc,t5.lastday,t5.endoffset,t5.id')
            ->buildSql();

        // 对查询到的结果关联城市
        $resultObj = self::table($sql)
            ->alias('t6')
            ->field([
                't6.id',
                't6.classid',
                't6.jc',
                't6.qc',
                'if(DATEDIFF(t6.end,"'.$start.'") + 1 <= 15 and t6.on = 2,1,0) as isend',
                't6.mark',
                't6.status',
                't6.start',
                't6.end',
                't6.renew',
                't6.endoffset',
                'if(t6.lastday > 0,t6.lastday,0) as lastday',
                't6.cs',
                'q.cname'
            ]);

        //  如果有会员状态的查询条件，在结果中获取不同会员状态的数据
        if(!empty($map['type'])){
            switch ($map['type']) {
                case 1:
                    //新会员
                    $where = 't6.mark = "new" and t6.on = 2';
                    break;
                case 2:
                    //常规会员
                    $where = 't6.mark = "normal" and t6.on = 2';
                    break;
                case 3:
                    //即将到期会员
                    $where = 'DATEDIFF(t6.end,"'.$start.'")+1 <= 15 and t6.on = 2';
                    break;
                case 4:
                    //过期会员
                    $where = 't6.on = -1';
                    break;
                case 5:
                    //到期续费
                    $where ='t6.status = "in" and t6.on = 2';
                    break;
                case 6:
                    //过期续费
                    $where ='t6.status = "out" and t6.on = 2';
                    break;
            }
        }

        if (!empty($where)) {
            $resultObj = $resultObj->whereRaw($where);
        }

        // 剩余天数
        if (!empty($map["lastday"])) {
            $resultObj->where("t6.lastday", "elt", $map["lastday"]);
        }

        return $resultObj
            ->join('quyu q','q.cid = t6.cs')
            ->order('isend desc,lastday asc,id asc')
            ->select();
    }

    /**
     * 获取城市会员数量
     * @return [type] [description]
     */
    public function getCityUserCount(){
        return static::name("user")->alias("u")
            ->join("user_company c", "u.id = c.userid")
            ->where("u.on", 2)->where("u.classid", 3)->where("c.fake", 0)
            ->field("u.cs,count(u.id) as user_count")
            ->group("u.cs")
            ->select();
    }

    /**
     * 获取会员公司列表
     * @param  string $module [description]
     * @return [type]       [description]
     */
    public function getUserCompanyList($module = "list", $options = [], $offset = 0, $limit = 0){
        $map = new Query();
        $map->where("u.on", "in", [-1, 2, -4]);
        // $map->where("u.on", 2);
        $map->where("c.fake", 0);

        // 城市
        if (!empty($options["citys"])) {
            if (is_array($options["citys"])) {
                $map->where("u.cs", "in", $options["citys"]);
            } else {
                $map->where("u.cs", $options["citys"]);
            }
        }

        // 区县
        if (!empty($options["qx"])) {
            $map->where("u.qx", $options["qx"]);
        }

        // 经纬度
        if (!empty($options["latlng"])) {
            if ($options["latlng"] == 1) {
                $map->where("c.lat", "neq", "")->where("c.lng", "neq", "");
            } else {
                $map->where("c.lat", "eq", "")->where("c.lng", "eq", "");
            }
        }
        
        // 会员名称（会员ID/会员简称）
        if (!empty($options["user_keyword"])) {
            $user_keyword = $options["user_keyword"];
            $map->where(function($query) use ($user_keyword) {
                $query->where("u.id", $user_keyword)
                    ->whereOr("u.jc", "like", "%".$user_keyword."%");
            });
        }

        // 半包/全包
        if (!empty($options["contract"])) {
            if ($options["contract"] == 99) {
                $map->where("c.contract", 0);
            } else {
                $map->where("c.contract", $options["contract"]);
            }
        }

        // 公装/家装
        if (!empty($options["leixing"])) {
            if ($options["leixing"] == 99) {
                $map->where("c.lx", 0);
            } else {
                $map->where("c.lx", $options["leixing"]);
            }
        }

        // 是否接收赠单
        if (!empty($options["gift_order"])) {
            $map->where("c.get_gift_order", $options["gift_order"]);
        }

        // 会员状态
        if (!empty($options["user_on"])) {
            $map->where("u.on", $options["user_on"]);
        }

        // 合作模式
        if (!empty($options["cooperate_mode"])) {
            $map->where("c.cooperate_mode", $options["cooperate_mode"]);
        }

        // 虚拟号开通状态
        if (!empty($options["pnp_on"])) {
            $map->where("c.pnp_on", $options["pnp_on"]);
        }

        // 装企虚拟号绑定状态
        if(!empty($options["bind_status"])){
            if($options["bind_status"] == 1){
                $map->where("t.id", "NOT NULL");
            }else{
                $map->where("t.id", "NULL");
            }
        }

        $baseSql = static::alias("u")->where($map)
            ->field([
                "u.id", "u.jc", "u.cs", "u.qx", "u.dz", "u.register_time", "u.`on` as user_on",
                "c.lat", "c.lng", "c.contract", "c.lx", "c.other_id", "c.saler","c.cooperate_mode",
                "c.lxs", "c.mianji", "c.fen_mianji", "c.fen_special_needs", "c.get_gift_order",
                "c.pnp_on","COUNT(t.id) AS bind_num"
            ])
            ->join("user_company c", "u.id = c.userid")
            ->join("user_company_pnp_tel t", "u.id=t.company_id AND enabled = 1", "LEFT")
            ->group("u.id")
            ->buildSql();

        $dbQuery = static::table($baseSql)->alias("uu");

        if ($module == "count") {
            $result = $dbQuery->count("uu.id");
        } else {
            // 会员公司查询
            $subSql = $dbQuery->join("quyu q", "q.cid = uu.cs", "left")
                ->join("area a", "a.qz_areaid = uu.qx", "left")
                ->field([
                    "uu.*",
                    "q.cname as city_name", "a.qz_area as area_name"
                ])
                ->order("uu.register_time desc")
                ->limit($offset, $limit)
                ->buildSql();

            // 接单区域统计查询
            $result = static::table($subSql)->alias("t")
                ->join("user_company_deliver_area d", "d.company_id = t.id", "left")
                ->field("t.*,count(d.company_id) as deliver_area_count")
                ->order("t.register_time desc")
                ->group("t.id")
                ->select();
        }

        return $result;
    }

    /**
     * 获取会员公司详情
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getUserCompanyDetail($id){
        return static::alias("u")
            ->join("user_company c", "u.id = c.userid")
            ->join("quyu q", "q.cid = u.cs", "left")
            ->join("user_company_signback s", "s.user_id = u.id", "left")
            ->where("u.id", $id)
            ->field([
                "u.id", "u.jc", "u.cs", "u.qx", "u.dz", "u.tel_safe",
                "c.lat", "c.lng", "c.contract", "c.lx", "c.other_id", "c.saler", "c.cooperate_mode",
                "c.lxs", "c.mianji", "c.fen_mianji", "c.fen_special_needs", "c.get_gift_order", "s.back_ratio", "s.order_round_amount",
                "q.parent_city", "q.parent_city1", "q.parent_city2", "q.parent_city3", "q.parent_city4",
                "c.pnp_on", "c.pnp_days", "c.pnp_tel_num"
            ])->find();
    }

    public function getQianDanUserList($city_id,$user_type)
    {
        $map = array(
            "u.classid" => array("EQ",6),
            "u.on" => array("EQ",$user_type)
        );

        if (is_array($city_id)) {
            $map["u.cs"] = array("IN",$city_id);
        } else {
            $map["u.cs"] = array("EQ",$city_id);
        }

        $buildSql = $this->where(new Where($map))->alias("u")
            ->join('user_company c','u.id = c.userid','left')
            ->join('user_company_signback b','u.id = b.user_id','left')
            ->field("u.id,u.classid,u.jc,u.cs,u.qx,if(b.order_stop_status is null,1,b.order_stop_status) as order_stop_status,c.lx,c.lxs,c.mianji,c.contract,c.fen_mianji,c.other_id")->buildSql();

        return $this->table($buildSql)->alias("t")
            ->join("quyu q","q.cid = t.cs")
            ->join("area a ","a.qz_areaid = t.qx")
            ->field("t.*,t.id,q.cname,a.qz_area")
            ->select();
    }

    /**
     * @param $company_id [装修公司ID]
     * @param $type [订单包类型]
     * @return mixed
     */
    public function getUserOrderPackageInfo($company_id,$type)
    {
        $map["a.company_id"] = array("IN",$company_id);

        $buildSql = Db::name("company_package")->where(new Where($map))->alias("a")
            ->field("a.id,a.company_id,a.use_status as package_status")
            ->buildSql();
        $map = array(
            "b.package_type"=>array("EQ",$type)
        );
        $exp = new \think\db\Expression('field(a.package_status,2,1,3),a.id,b.use_status,b.id desc');
        $buildSql = Db::name("company_package")->table($buildSql)->where($map)->alias("a")
            ->join("qz_company_package_info b","b.package_id = a.id","left")
            ->field("a.id,a.company_id, b.send_number,b.use_status,a.package_status,b.total_number,b.id as package_info_id")
            ->order($exp)
            ->buildSql();
        return Db::name("company_package")->table($buildSql)->alias("t")
            ->group("t.company_id")->select();
    }

    public function getUsersInfo($id)
    {
        $map = new Query();
        if (is_array($id)) {
            $map->where("u.id", "in", $id);
        } else {
            $map->where("u.id", $id);
        }

        return $this->alias("u")->where($map)
            ->join("user_company c", "c.userid = u.id", "left")
            ->field("u.id,u.classid,c.cooperate_mode")
            ->select();
    }

    public function editPackageInfo($id,$data)
    {
        $map = array(
            "id" => array("EQ", $id)
        );
        return Db::name("company_package_info")->where(new Where($map))->update($data);
    }

    public function getNextPackageInfo($company_id,$package_id,$package_type)
    {
        $map = array(
            "a.company_id" => array("EQ",$company_id),
            "a.id" => array("GT",$package_id)
        );
        $buildSql = Db::name("company_package")->where(new Where($map))->alias("a")->field("a.id as package_id")->limit(1)
            ->buildSql();
        $map = array(
            "b.package_type"=>array("EQ",$package_type)
        );
        return Db::name("company_package")->table($buildSql)->alias("a")->where(new Where($map))
            ->join("left JOIN qz_company_package_info b ON b.package_id = a.package_id ")
            ->field("a.package_id,b.id as package_info_id")->find();
    }

    public function editPackage($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return Db::name("company_package")->where(new Where($map))->update($data);
    }

    public function addPackageOrderRelation($data)
    {
        return Db::name("company_package_order")->insert($data);
    }

    public function editPackageOrderRelation($where,$data)
    {
        return Db::name("company_package_order")->where(new Where($where))->update($data);
    }


    /**
     * 获取会员公司标签列表
     * @param  string $module [description]
     * @return [type]       [description]
     */
    public function getUserCompanyTagList($module = "list", $options = [], $offset = 0, $limit = 0){
        $where = [
            'u.on'=>['eq',2],
            'u.classid'=>['IN',array(3,6)],
            'c.fake'=>['eq',0],
        ];
        // 城市
        if (!empty($options["citys"])) {
            if (is_array($options["citys"])) {
                $where['u.cs'] = ['in', $options["citys"]];
            } else {
                $where['u.cs'] = ['eq', $options["citys"]];
            }
        }

        // 区县
        if (!empty($options["qx"])) {
            $where['u.qx'] = ['eq', $options["qx"]];
        }

        // 会员id
        if (!empty($options["company_id"])) {
            $where['u.id'] = ['eq', $options["company_id"]];
        }
        // 会员简称
        if (!empty($options["jc"])) {
            $where['u.jc'] = ['like', '%' . $options["jc"] . '%'];
        }

        // 标识
        $tag_rel_where = [];
        if (!empty($options["tag"])) {
            if($options["tag"] == 1){
                $tag_rel_where = "rel.tag_id is not null";
            }elseif ($options["tag"] == 2){
                $tag_rel_where = "rel.tag_id is null";
            }
        }

        if($module == 'count'){
            $sql = $this->alias('u')
                ->field('u.id')
                ->where(new Where($where))
                ->join('qz_user_company c', 'c.userid = u.id')
                ->buildSql();
            return $this->table($sql)->alias('u')
                ->field('u.*,rel.tag_id')
                ->leftJoin('qz_sub_tag_company rel', 'rel.company_id = u.id')
                ->group('u.id')
                ->where($tag_rel_where)
                ->count();
        } else {
            $sql = $this->alias('u')
                ->field('u.id,u.jc,u.cs,u.qx,c.lx,c.lxs,c.contract')
                ->where(new Where($where))
                ->join('qz_user_company c', 'c.userid = u.id')
                ->order('u.id desc')
                ->buildSql();
            $sql = $this->table($sql)->alias('u')
                ->field('u.*,rel.tag_id')
                ->leftJoin('qz_sub_tag_company rel', 'rel.company_id = u.id')
                ->where($tag_rel_where)
                ->buildSql();
            return $this->table($sql)->alias('u')
                ->field('u.*,q.cname city_name,a.qz_area area_name,GROUP_CONCAT(tag.name) tag_name')
                ->join('qz_quyu q', 'q.cid = u.cs')
                ->join('qz_area a', 'a.qz_areaid = u.qx')
                ->leftJoin('qz_sub_tag tag', 'tag.id = u.tag_id')
                ->group('u.id')
                ->order('u.id desc')
                ->limit($offset, $limit)
                ->select();
        }
    }

}
