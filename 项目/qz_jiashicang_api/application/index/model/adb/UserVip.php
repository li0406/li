<?php

// +----------------------------------------------------------------------
// | UserVip 会员合同数据模型
// +----------------------------------------------------------------------

namespace app\index\model\adb;

use think\Db;
use think\db\Query;
use app\common\model\adb\BaseModel;

class UserVip extends BaseModel {

    // 获取城市常规会员数量
    public function getCityZxUserCount($date, $city_ids = []){
        $map = new Query();
        $map->where("a.start_time", "<=", $date);
        $map->where("a.end_time", ">=", $date);
        $map->where("a.type", "in", [2, 8]);
        $map->where("a.cooperate_mode", 1);
        $map->where("u.classid", "in", [3, 6]);
        $map->where("uc.fake", 0);

        $map->where("a.company_id", "not in", function($query) use ($date) {
            $query->table("qz_user_vip")
                ->where("start_time", "<=", $date)
                ->where("end_time", ">=", $date)
                ->where("cooperate_mode", 1)
                ->where("type", 4)
                ->field(["company_id"]);
        });

        if (!empty($city_ids)) {
            $map->where("u.cs", "in", $city_ids);
        }

        $subSql = $this->link()->name("user_vip")->alias("a")->where($map)
            ->join("user u", "u.id = a.company_id", "inner")
            ->join("user_company uc", "uc.userid = u.id", "inner")
            ->field([
                "u.cs as city_id", "a.company_id", "a.viptype"
            ])
            ->group("a.company_id")
            ->buildSql();

        return $this->link()->table($subSql)->alias("t")
            ->field(["t.city_id", "count(t.company_id) as vip_count", "sum(t.viptype) as vip_num"])
            ->group("t.city_id")
            ->select();
    }

    // 获取城市1.0会员续费数量
    public function getCityVipRenewCount($date_begin, $date_end, $city_ids = []){
        $map = new Query();
        $map->where("a.type", "in", [2, 8]);
        $map->where("a.cooperate_mode", 1);
        $map->where("u.classid", "in", [3, 6]);
        $map->where("uc.fake", 0);
        $map->where("a.time", ">=", strtotime($date_begin));
        $map->where("a.time", "<=", strtotime($date_end));

        // 排除当月新上合同的公司
        $map->where("a.company_id", "not in", function($query) use ($date_begin, $date_end) {
            $query->table("qz_user_vip")
                ->where("type", "in", [2, 8])
                ->field(["company_id"])
                ->group("company_id")
                ->having("min(start_time) >= '{$date_begin}' and min(start_time) <= '{$date_end}'");
        });

        if (!empty($city_ids)) {
            $map->where("u.cs", "in", $city_ids);
        }

        $subSql = $this->link()->name("user_vip")->alias("a")->where($map)
            ->join("user u", "u.id = a.company_id", "inner")
            ->join("user_company uc", "uc.userid = u.id", "inner")
            ->field([
                "u.cs as city_id", "a.company_id", "a.viptype"
            ])
            ->group("a.company_id")
            ->buildSql();

        $list = $this->link()->table($subSql)->alias("t")
            ->field(["t.city_id", "count(t.company_id) as renew_count"])
            ->group("t.city_id")
            ->select();
            
        return $list;
    }

    // 查询城市1.0会员到期数量
    public function getCityVipExpireCount($date_begin, $date_end, $city_ids = []){
        $map = new Query();
        $map->where("a.type", "in", [2, 8]);
        $map->where("a.cooperate_mode", 1);
        $map->where("u.classid", "in", [3, 6]);
        $map->where("uc.fake", 0);
        $map->where("a.end_time", ">=", $date_begin);
        $map->where("a.end_time", "<=", $date_end);

        if (!empty($city_ids)) {
            $map->where("u.cs", "in", $city_ids);
        }

        $subSql = $this->link()->name("user_vip")->alias("a")->where($map)
            ->join("user u", "u.id = a.company_id", "inner")
            ->join("user_company uc", "uc.userid = u.id", "inner")
            ->field([
                "u.cs as city_id", "a.company_id", "a.viptype"
            ])
            ->group("a.company_id")
            ->buildSql();

        $list = $this->link()->table($subSql)->alias("t")
            ->field(["t.city_id", "count(t.company_id) as expire_count"])
            ->group("t.city_id")
            ->select();

        return $list;
    }

    /**
     * @des:获取新签企业数量
     * @param $date_begin
     * @param $date_end
     * @return mixed
     */
    public function getNewSignVipAmount($date_begin,$date_end)
    {
        $map = new Query();
        $map->where("a.type", "=", 2);
        $map->where("u.classid", "in", [3, 6]);
        $map->where("uc.fake", 0);
        $map->where("u.cs",'>', 0);
        $map->where("a.start_time","between",[date('Y-m-d',$date_begin),date('Y-m-d',$date_end)]);
        $map->where(" NOT EXISTS (select * from qz_user_vip where type = 8 and  time BETWEEN ".$date_begin." and ".$date_end." and company_id=a.company_id)");
        $map->where(" NOT EXISTS (select * from qz_user_vip where type = 2 and start_time < ".date('Y-m-d',$date_begin)." and company_id=a.company_id)");
        return $this->link()->name("user_vip")->alias("a")->where($map)
            ->join("user u", "u.id = a.company_id", "inner")
            ->join("user_company uc", "uc.userid = u.id", "inner")
            ->field(["a.id"])
            ->count();
    }

    /**
     * @des:1.0续费会员数，根据操作时间维度
     * @param $date_begin
     * @param $date_end
     * @return mixed
     */
    public function getRenewVipAmount($date_begin,$date_end)
    {
        $map = new Query();
        $map->where("a.type", "=", 8);
        $map->where("a.cooperate_mode", 1);
        $map->where("u.classid", "in", [3, 6]);
        $map->where("uc.fake", 0);
        $map->where("a.time","between",[$date_begin,$date_end]);
        return $this->link()->name("user_vip")->alias("a")->where($map)
            ->join("user u", "u.id = a.company_id", "inner")
            ->join("user_company uc", "uc.userid = u.id", "inner")
            ->count();
    }

    /**
     * @des:1.0到期企业，时间维度
     * @param $date_begin
     * @param $date_end
     * @return mixed
     */
    public function getMatureAmount($date_begin,$date_end)
    {
        $map = new Query();
        $map->where("a.type", "in", [2, 8]);
        $map->where("u.classid", "in", [3, 6]);
        $map->where("a.cooperate_mode", 1);
        $map->where("uc.fake", 0);
        $map->where("a.end_time", "between", [date('Y-m-d', $date_begin), date('Y-m-d', $date_end)]);
        $map->where(" NOT EXISTS (select * from qz_user where `on`=2 and classid in (3,6) and id=u.id)");
        return $this->link()->name("user_vip")->alias("a")->where($map)
            ->join("user u", "u.id = a.company_id", "inner")
            ->join("user_company uc", "uc.userid = u.id", "inner")
            ->count();
    }

    /**
     * @des:城市新签企业数量，城市时间维度
     * @param $date_begin
     * @param $date_end
     * @return mixed
     */
    public function getNewSignVipCity($date_begin,$date_end)
    {
        $map = new Query();
        $map->where("a.type", "=", 2);
        $map->where("u.classid", "in", [3, 6]);
        $map->where("uc.fake", 0);
        $map->where("u.cs",">",0);
        $map->where("a.start_time","between",[date('Y-m-d',$date_begin),date('Y-m-d',$date_end)]);
        $map->where(" NOT EXISTS (select * from qz_user_vip where type = 8 and  time BETWEEN ".$date_begin." and ".$date_end." and company_id=a.company_id)");
        $map->where(" NOT EXISTS (select * from qz_user_vip where type = 2 and start_time < ".date('Y-m-d',$date_begin)." and company_id=a.company_id)");
        $subSql = $this->link()->name("user_vip")->alias("a")->where($map)
            ->join("user u", "u.id = a.company_id", "inner")
            ->join("user_company uc", "uc.userid = u.id", "inner")
            ->field(["a.company_id","u.cs"])
            ->buildSql();
        $subSq = $this->link()->table($subSql)->alias('b')->field(["count(company_id) as company_amount",'cs'])
            ->group("cs")->buildSql();

        return $this->link()->table($subSq)->alias('c')->field(["count(cs) as count","company_amount"])
            ->group("company_amount")
            ->order('company_amount desc')
            ->select();
    }

    /**
     * @des:1.0到期企业数量，城市时间维度
     * @param $date_begin
     * @param $date_end
     * @return mixed
     */
    public function getMatureCity($date_begin,$date_end)
    {
        $map = new Query();
        $map->where("a.type", "in", [2, 8]);
        $map->where("u.classid", "in", [3, 6]);
        $map->where("a.cooperate_mode", 1);
        $map->where("uc.fake", 0);
        $map->where('u.cs','>',0);
        $map->where("a.end_time", "between", [date('Y-m-d', $date_begin), date('Y-m-d', $date_end)]);
        $map->where("NOT EXISTS (select * from qz_user where `on`=2 and classid in (3,6) and id=u.id)");
        $subSql =  $this->link()->name("user_vip")->alias("a")->where($map)
            ->join("user u", "u.id = a.company_id", "inner")
            ->join("user_company uc", "uc.userid = u.id", "inner")
            ->field(['a.company_id','u.cs'])
            ->buildSql();
        return $this->link()->table($subSql)->alias('b')
            ->field(["count(company_id) as company_amount","cs"])
            ->group('cs')
            ->select();
    }

}