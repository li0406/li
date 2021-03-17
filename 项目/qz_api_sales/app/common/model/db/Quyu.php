<?php
// +----------------------------------------------------------------------
// | Quyu 装修城市
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\db\Query;
use think\db\Where;
use think\Model;

class Quyu extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_quyu';
    protected $pk = 'cid';

    /**
     * 关联获取城市下面的地区
     *
     * @return \think\model\relation\HasMany
     */
    public function areas()
    {
        return  $this->hasMany('Area','fatherid','cid');
    }

    /**
     * 关联获取城市上级省份
     *
     * @return \think\model\relation\HasOne
     */
    public function province()
    {
        return $this->hasOne('Province','qz_provinceid','uid');
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
    public function getList($map, $with = [], $page = null, $pageSize = null, $field = '*')
    {
        if ($page == 0) {
            $offset = 0;
        } else {
            $offset = $pageSize * ($page - 1);
        }
        return self::field($field)->with($with)->where(new Where($map))->limit($offset, $pageSize)->order('px_abc asc,cid desc')->select();
    }

    /**
     * 根据名称获取
     *
     * @param array $map 查询条件
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getInfoByName($map)
    {
        return $this->where($map)->find();
    }

    /**
     * [getAllQuyu 获取已开城市的简单信息，排除掉总站]
     * @return [type] [description]
     */
    public function getAllQuyuOnly()
    {
        return self::alias("a")
               ->where("a.type=1 AND cid != 000001")
               ->field("a.*,upper(left(a.px_abc,1)) AS first_abc")
               ->order("a.bm,xh")
               ->select()
               ->toArray();
    }

    /**
     * 获取无会员城市列表
     *
     * @return mixed
     */
    public function getNoVipList($quyuMap = [], $orderMap = [], $order = 'px_abc asc')
    {
        $buildSql = db('user')->alias('u')
            ->field('u.cs,count(IF(u.on = 2 and b.fake = 0 ,1,NULL)) as count')
            ->join('user_company b', 'u.id = b.userid')
            ->where('cs', '<>', '""')
            ->group('u.cs')
            ->buildSql();

        $buildSql = db('quyu', [], false)->table($buildSql)->alias('t')
            ->field('t.cs,q.little,q.is_open_city,q.px_abc,upper(left(q.px_abc,1)) AS first_abc,q.cname,q.parent_city,q.parent_city1,q.parent_city2,q.parent_city3,q.parent_city4,q.other_city')
            ->join('quyu q', 'q.cid = t.cs')
            ->where(new Where($quyuMap))
            ->where('t.count', '=', 0)
            ->buildSql();
        return db('quyu', [], false)->table($buildSql)->alias('t1')
            ->field('t1.*,count(o.id) fa,count(IF(o.`on` = 4 AND o.type_fw = 1,1,NULL)) AS feng,count(IF(o.`on` = 4 AND o.type_fw = 2,1,NULL)) AS zeng,count(IF(o.`on` = 4 AND (o.type_fw = 1 OR o.type_fw = 2),1,NULL)) AS fengandzeng')
            ->leftJoin('orders o ', 'o.cs = t1.cs')
            ->where(new Where($orderMap))
            ->group('t1.cs')
            ->order($order)
            ->select();
    }

    /**
     * 获取无会员城市列表
     *
     * @param $map
     * @param string $order
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getNoVipCitys($map, $order = 'px_abc asc')
    {
        $userMap = [
            'c.fake' => 0,
            'u.on' => 2
        ];
        $userBuildSql = db('user', [], false)
            ->alias('u')
            ->field('u.cs,count(u.id) vipcount')
            ->join('qz_user_company c', 'c.userid = u.id')
            ->where(new Where($userMap))
            ->group('u.cs')
            ->buildSql();

        $map['b.vipcount'] = ['exp', db('', [], false)->raw('is null')];
        return db('quyu', [], false)
            ->alias('a')
            ->field('a.cid,a.cname,a.bm,a.px_abc,upper(left(a.px_abc,1)) AS first_abc')
            ->leftJoin($userBuildSql . ' b', 'b.cs = a.cid')
            ->where(new Where($map))
            ->order($order)
            ->select();
    }

    /**
     * 根据cid获取城市
     * @param  [type] $cids [description]
     * @return [type]       [description]
     */
    public function getCitysByCids($cids){
        $string = implode(",", $cids);
        $orderExp = new \think\db\Expression("FIELD(cid, $string)");

        return static::where("cid", "in", $cids)
            ->field("cid,cname")
            ->order($orderExp)
            ->select();
    }

    /**
     * 获取质检城市
     * 城市会员数小于4家并且销售分配给质检的城市
     * @return [type] [description]
     */
    public function getAuditCitys(){
        $subMap = new Query();
        $subMap->where("u.on", 2);
        $subMap->where("u.classid", 3);
        $subMap->where("uc.fake", 0);

        $subSql = db("user")->alias("u")->where($subMap)
            ->join("user_company uc", "uc.userid = u.id", "inner")
            ->field(["u.cs", "count(u.id) as user_count"])
            ->group("u.cs")
            ->buildSql();

        $map = new Query();
        $map->where("q.is_open_city", 1);
        $map->where("", "not exists", function($query){
            return $query->name("sales_quyu")->where("to", "jihebu")->where("q.`cid` = `cs`")->field("cs");
        });

        return static::alias("q")->where($map)
            ->join("$subSql t", "t.cs = q.cid", "left")
            ->field(["q.cid", "q.cname", "IF(t.user_count is null, 0, t.user_count) as vip_num"])
            ->having("vip_num <= 4")
            ->select();
    }


    // 获取所有已开通城市
    public function getAllQuyu()
    {
        return $this->order('bm')->field('cid, cname, uid, bm, type, map_name, vic, px_abc')->select();
    }

    public function getAllQuyu2($map)
    {
        return $this->where($map)->order('bm')->field('cid, cname, uid, bm, type, map_name, vic, px_abc')->select();
    }


    public function getquyuList()
    {
        $list = $this->alias("a")
            ->field("a.cid as cid,a.cname,a.uid,a.bm,b.qz_areaid,b.qz_area,b.orders,a.type,a.px,a.px_abc,a.is_open_city,
                    a.mark_red,a.parent_city,a.parent_city1,a.parent_city2,a.parent_city3,a.parent_city4,a.other_city,c.qz_provinceid,c.qz_province,a.little")
            ->join("qz_area b", "a.cid = b.fatherid")
            ->join("qz_province c", "c.qz_provinceid = a.uid")
            ->order("a.bm")
            ->select();
        return $list ? $list : [];
    }

}