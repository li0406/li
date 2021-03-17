<?php

// +----------------------------------------------------------------------
// | 城市区域数据模型
// +----------------------------------------------------------------------

namespace app\index\model\adb;

use think\Db;
use think\db\Query;

use app\common\model\adb\BaseModel;

class Quyu extends BaseModel {

    // 获取所有开站城市
    public function getAllOpenCity(){
        $map = new Query();
        $map->where("is_open_city", 1);

        return $this->link()->name("quyu")->where($map)
            ->field(["cid as city_id", "cname as city_name", "bm", "px_abc"])
            ->order("px_abc asc")
            ->select();
    }

    // 获取城市列表
    public function getCityList($city_ids = []){
        $map = new Query();
        $map->where("is_open_city", 1);

        if (!empty($city_ids)) {
            $map->where("cid", "in", $city_ids);
        }

        return $this->link()->name("quyu")->where($map)
            ->field(["cid as city_id", "cname as city_name", "bm", "px_abc"])
            ->order("px_abc asc")
            ->select();
    }

    // 获取城市下辖区域
    public function getCityAreaList($city_ids = [], $area_ids = []){
        $map = new Query();
        $map->where("q.is_open_city", 1);

        if (!empty($city_ids)) {
            $map->where("q.cid", "in", $city_ids);
        }

        if (!empty($area_ids)) {
            $map->where("a.qz_areaid", "in", $area_ids);
        }

        $list = $this->link()->name("quyu")
            ->alias("q")->where($map)
            ->join("area a", "a.fatherid = q.cid", "inner")
            ->field([
                "a.qz_areaid as area_id", "a.qz_area as area_name",
                "q.cid as city_id", "q.cname as city_name",
            ])
            ->order("q.px_abc asc,a.orders asc")
            ->select();

        return $list;
    }

    /**
     * @des:统计已开站城市个数
     * @return mixed
     */
    public function getAllOpenCityCount()
    {
        $map = new Query();
        $map->where("is_open_city", 1);
        return $this->link()->name("quyu")->where($map)
            ->field(["cid as city_id", "cname as city_name", "bm", "px_abc"])
            ->order("px_abc asc")
            ->count();
    }

    /**
     * @des:区域开站城市数量统计
     */
    public function getCityOpenByUid()
    {
        $map = new Query();
        $map->where("q.is_open_city", 1);
        $subSql = $this->link()->table('qz_province')
            ->where('qz_bigpart', 'in', [1, 2, 3, 4, 5, 6, 7])
            ->where('qz_bigpart_name', '<>', '台港澳区')
            ->field(["qz_provinceid","qz_bigpart","qz_bigpart_name"])
            ->group("qz_bigpart,qz_provinceid")
            ->buildSql();
        return $this->link()->table($subSql)->alias('a') ->where($map)
            ->join('qz_quyu q','q.uid=a.qz_provinceid','left')
            ->field(["count(q.id) as amount","a.qz_bigpart"])
            ->group("a.qz_bigpart")
            ->order('amount desc')
            ->select();
    }

    /**
     * @des:获取城市签约会员
     */
    public function getCitySignVip()
    {
        $map = new Query();
        $map->where("u.on", '=',2);
        $map->where("u.classid", 'in',[3,6]);
        $map->where("uc.cooperate_mode", 'in',[1,2]);
        $map->where('uc.fake','=',0);
        $map->where('q.uid','>',0);
        $subSql = $this->link()->table('qz_province')
            ->where('qz_bigpart', 'in', [1, 2, 3, 4, 5, 6, 7])
            ->where('qz_bigpart_name', '<>', '台港澳区')
            ->field(["qz_provinceid","qz_bigpart","qz_bigpart_name"])
            ->group("qz_bigpart,qz_provinceid")
            ->buildSql();
        $subSq = $this->link()->table('qz_user')->alias('u')->where($map)
            ->join('qz_user_company uc','uc.userid=u.id')
            ->join('qz_quyu q','q.cid=u.cs')
            ->field(["q.uid","u.id"])
            ->buildSql();

        return $this->link()->table($subSql)->alias('a')
            ->join($subSq." as b",'b.uid=a.qz_provinceid','left')
            ->field(["count(b.id) as vip_amount","a.qz_bigpart","a.qz_bigpart_name as name"])
            ->group("a.qz_bigpart")
            ->select();
    }

    /**
     * @des:获取各省份开站城市总数
     */
    public function getOpenCityCountByUid()
    {
        $map = new Query();
        $map->where("is_open_city", 1);
        return $this->link()->name("quyu")->where($map)
            ->field(["count(cid) as amount","uid"])
            ->group("uid")
            ->order("uid asc")
            ->select();
    }

}