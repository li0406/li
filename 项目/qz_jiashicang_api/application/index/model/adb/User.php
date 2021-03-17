<?php

namespace app\index\model\adb;

use think\Db;
use think\db\Query;

use app\common\model\adb\BaseModel;
/**
 *
 */
class User extends BaseModel
{
    public function getMembershipCount($city)
    {
        $map[] = ["a.on","IN",[2,-1]];
        $map[] = ["b.fake","EQ",0];
        if (count($city) > 0) {
            $map[] = ["a.cs","IN",$city];
        }

        $buildSql =  $this->link()->table("qz_user")->alias("a")->where($map)
            ->join("qz_user_company b","b.userid = a.id")
            ->field("a.on,a.classid,b.cooperate_mode,count(a.id) as count")
            ->group("a.on,a.classid,b.cooperate_mode")
            ->buildSql();

        $map = [];
        $map[] = ["a.on","IN",[2]];
        $map[] = ["b.fake","EQ",0];
        if (count($city) > 0) {
            $map[] = ["a.cs","IN",$city];
        }

        $nextSql = $this->link()->table("qz_user")->alias("a")->where($map)
            ->join("qz_jiaju_user_company b","b.company_id = a.id and b.fake = 0")
            ->field("a.on,a.classid,1 as cooperate_mode,count(a.id) as count")
            ->group("a.on,a.classid")
            ->buildSql();

        return $this->link()->table($buildSql)->alias("a")
            ->union($nextSql)->field("`on`,classid,cooperate_mode,count")
            ->order("classid,cooperate_mode,on")
            ->select();
    }

    public function getMembershipChange($begin,$end,$step,$city)
    {
        $map[] = ["a.datetime","EGT",$begin];
        $map[] = ["a.datetime","ELT",$end];
        $map[] = ["a.vip_status","EQ",2];

        if (count($city) > 0) {
            $map[] = ["u.cs","IN",$city];
        }

        $map1 = $map;
        $map1[] = ["a.cooperate_mode", "EQ", 1];
        $buildSql = $this->link()->table("qz_user_company_expend")->alias("a")->where($map1)
            ->join("qz_user u","u.id = a.company_id")
            ->field("FROM_UNIXTIME(a.datetime,'%Y-%m-%d') as day,company_id,1 as cooperate_mode,viptype")
            ->buildSql();

        $map2 = $map;
        $map2[] = ["a.cooperate_mode", "EQ", 2];
        $nextSql = $this->link()->table("qz_user_company_vip_status")->alias("a")->where($map2)
            ->join("qz_user u","u.id = a.company_id")
            ->field("FROM_UNIXTIME(a.datetime,'%Y-%m-%d') as day,company_id,a.cooperate_mode,1 as viptype")
            ->buildSql();

        

        $buildSql = $this->link()->table($buildSql)->alias("t")
            ->union($nextSql,true)->field("day,company_id,cooperate_mode,viptype")
            ->buildSql();

        if ($step == 1) {
            $field = "t.day,sum(t.viptype) as all_count,sum(if(t.cooperate_mode = 1,t.viptype,null)) as normal_count,count(if(t.cooperate_mode = 2,1,null)) as qian_count";
        } else {
            $field = "DATE_FORMAT(t.day,'%Y-%m') as day,sum(t.viptype) as all_count,sum(if(t.cooperate_mode = 1,t.viptype,null)) as normal_count,count(if(t.cooperate_mode = 2,1,null)) as qian_count";
        }

        return $this->link()->table($buildSql)->alias("t")
                 ->field($field)
                 ->group("t.day")->order("t.day")->select();
    }

    public function getMembershipNoramlRenewal($begin,$end,$city)
    {
        $map[] = ["a.time","EGT",strtotime($begin)];
        $map[] = ["a.time","ELT",strtotime($end) + 86400 -1];
        $map[] = ["a.cooperate_mode","EQ",1];
        $map[] = ["a.type","IN",[2,8]];
        if (count($city) > 0) {
            $map[] = ["u.cs","IN",$city];
        }

        $nextSql = $this->link()->table("qz_user_vip")->alias("a")
            ->where(" `a`.`type` IN (2, 8)")
            ->field("a.company_id,date_format(min(start_time),'%Y%m') as in_start_time,count(a.id) as count")
            ->group("a.company_id")
            ->buildSql();

        $buildSql = $this->link()->table("qz_user_vip")->alias("a")->where($map)
            ->join("qz_user u","u.id = a.company_id")
            ->field("a.type,a.company_id,FROM_UNIXTIME(a.time,'%Y%m') as date, date_format(start_time,'%Y%m') as start_time")
            ->buildSql();

        $buildSql = $this->link()->table($buildSql)->alias("a")->alias("t")
            ->field("max(t.type) as type ,t.date, t.company_id, min(start_time) as min_start_time")
            ->group("t.company_id,date")
            ->buildSql();

        $buildSql = $this->link()->table($buildSql)->alias("t")
                ->field("t.*,b.in_start_time,b.count")
                ->join("($nextSql) as b","t.company_id = b.company_id")
                ->buildSql();

        $buildSql = $this->link()->table($buildSql)->alias("t")
                         ->where("	t.min_start_time <> t.in_start_time and t.count > 1")
                         ->buildSql();
        return $this->link()->table($buildSql)->alias("t")
            ->field("date,count(t.company_id) as count")
            ->group("t.date")->select();
    }

    public function getMembershipNoramlEndRenewal($begin,$end,$city)
    {
        $map[] = ["a.end_time","EGT",$begin];
        $map[] = ["a.end_time","ELT",$end];
        $map[] = ["a.cooperate_mode","EQ",1];
        $map[] = ["a.type","IN",[2,8]];
        if (count($city) > 0) {
            $map[] = ["u.cs","IN",$city];
        }

        $buildSql = $this->link()->table("qz_user_vip")->alias("a")->where($map)
            ->join("qz_user u","u.id = a.company_id")
            ->field("a.company_id,date_format(end_time,'%Y%m') as in_start_time")
            ->buildSql();

        return  $this->link()->table($buildSql)->alias("t")
            ->field("in_start_time,count(t.company_id) as count")
            ->group("t.in_start_time")->select();
    }

    public function getMembershipPayment($begin,$end,$city)
    {
        $map[] = ["a.created_at","EGT",$begin];
        $map[] = ["a.created_at","ELT",$end];
        $map[] = ["a.trade_type","EQ",1];
        if (count($city) > 0) {
            $map[] = ["u.cs","IN",$city];
        }
        $buildSql = $this->link()->table("qz_user_company_account_log")->alias("a")->where($map)
            ->join("qz_user u","u.id = a.user_id")
            ->field("FROM_UNIXTIME(a.created_at,'%Y%m') as now_at,FROM_UNIXTIME(b.created_at,'%Y%m') as first_at,a.user_id")
            ->join("(select user_id, min(created_at) as created_at from qz_user_company_account_log where trade_type = 1 group by user_id  ) b","b.user_id = a.user_id","left")
            ->buildSql();

        $buildSql = $this->link()->table($buildSql)->alias("t")->where("first_at <> now_at")
                        ->buildSql();
        return $this->link()->table($buildSql)->alias("t")
                    ->field("t.now_at,count(DISTINCT t.user_id) AS count")
                    ->group("t.now_at")->select();
    }

    public function getMembershipNoramlPrice($begin,$end,$city)
    {
        $map[] = ["a.datetime","EGT",$begin];
        $map[] = ["a.datetime","LT",$end];
        $map[] = ["a.vip_status","EQ",2];
        $map[] = ["a.cooperate_mode","EQ",1];

        if (count($city) > 0) {
            $map[] = ["a.city_id","IN",$city];
        }

        $buildSql = $this->link()->table("qz_user_company_expend")->where($map)->alias("a")
            ->field("FROM_UNIXTIME(a.datetime,'%Y%m') as date, SUM(expend_amount) as expend_amount")
            ->group("a.company_id,date")
            ->buildSql();

        return $this->link()->table($buildSql)->alias('t')->field("SUM(t.expend_amount) as expend_amount,date")
                    ->group("t.date")->select();
    }

    public function getMembershipQianPrice($begin,$end,$city)
    {
        $map[] = ["new.lasttime","EGT",$begin];
        $map[] = ["new.lasttime","LT",$end];

        if (count($city) > 0) {
            $map[] = ["o.cs","IN",$city];
        }

        return $this->link()->table("qz_order_csos_new")->alias("new")->where($map)
             ->join("qz_orders o","o.id = new.order_id")
             ->join("qz_order_info a","a.order = new.order_id and a.cooperate_mode = 2 and a.type_fw = 1")
             ->field("FROM_UNIXTIME(new.lasttime,'%Y%m') as date,sum(order_amount) order_amount")
             ->group("date")
             ->select();
    }

    public function getMembershipAccountInsufficientBalance($begin,$end,$city)
    {
        $map[] = ["a.datetime","EGT",$begin];
        $map[] = ["a.datetime","ELT",$end];
        $map[] = ["a.account_amount","ELT",3000];
        $map[] = ["a.vip_status","IN",[-1,2]];

        if (count($city) > 0) {
            $map[] = ["a.city_id","IN",$city];
        }

        $buildSql = $this->link()->table("qz_user_company_vip_status")->where($map)->alias("a")
           ->join("qz_user u","u.id = a.company_id")
            ->join("qz_user_company_account_log b ","a.company_id = b.user_id and b.created_at > a.datetime and b.trade_type = 1","left")
           ->field("a.company_id,FROM_UNIXTIME(a.datetime,'%Y%m') as date,FROM_UNIXTIME(b.created_at,'%Y%m%d') as pay_date")
           ->group("a.id")->buildSql();

        $buildSql = $this->link()->table($buildSql)->alias("t")
            ->field("t.date, CONCAT(t.company_id,if(pay_date is null,'N',pay_date)) as mark")->buildSql();

        return $this->link()->table($buildSql)->alias("t")->field("t.date,count(DISTINCT t.mark) as count")
                    ->group("t.date")->select();
    }

    /**
     * @des:获取省份会员数据
     */
    public function getSfVipAmount()
    {
        $map = new Query();
        $map->where("u.on","=",2);
        $map->where("u.classid","in",[3,6]);
        $map->where("u.cs",">",0);
        $map->where("uc.fake","=",0);
        $map->where("uc.cooperate_mode","in",[1,2]);
        $map->where("q.uid",">",0);

        return $this->link()->table("qz_user")->alias("u")->where($map)
            ->join("qz_user_company uc", "uc.userid=u.id", "inner")
            ->join("qz_quyu q", "q.cid=u.cs", "inner")
            ->join("qz_province p", "p.qz_provinceid=q.uid", "inner")
            ->field(["count(u.id) as vip_amount", "q.uid as sf", "qz_province as name",'0 as color'])
            ->group("q.uid")
            ->order("vip_amount desc")
            ->select();
    }

    /**
     * @des:获取城市会员数
     */
    public function getCsVipAmount()
    {
        $map = new Query();
        $map->where("u.on","=",2);
        $map->where("u.classid","in",[3,6]);
        $map->where("u.cs",">",0);
        $map->where("uc.fake","=",0);
        $map->where("uc.cooperate_mode","in",[1,2]);
        $map->where("q.uid",">",0);

        $subSql = $this->link()->table("qz_user")->alias("u")->where($map)
            ->join("qz_user_company uc","uc.userid=u.id","inner")
            ->join("qz_quyu q","q.cid=u.cs","inner")
            ->join("qz_province p","p.qz_provinceid=q.uid","inner")
            ->field(["count(u.id) as vip_amount","q.uid","u.cs"])
            ->group("u.cs")
            ->order("vip_amount desc")
            ->buildSql();

        return $this->link()->table($subSql)->alias('a')
            ->field(["count(cs) as cs_amount","vip_amount","uid as sf_id"])
            ->group("vip_amount,uid")
            ->order('uid desc')
            ->select();
    }

    /**
     * @des:获取有会员的城市数
     */
    public function getVipCityCount()
    {
        $map = new Query();
        $map->where("u.on","=",2);
        $map->where("u.classid","in",[3,6]);
        $map->where("u.cs",">",0);
        $map->where("uc.fake","=",0);
        $map->where("uc.cooperate_mode","in",[1,2]);

        return $this->link()->table("qz_user")->alias("u")->where($map)
            ->join("qz_user_company uc","uc.userid=u.id","inner")
            ->join("qz_quyu q","q.cid=u.cs","inner")
            ->field("u.cs")
            ->group("u.cs")
            ->count();
    }

    /**
     * @des:获取总签约装企数
     */
    public function getVipCount()
    {
        $map = new Query();
        $map->where("u.on","=",2);
        $map->where("u.classid","in",[3,6]);
        $map->where("u.cs",">",0);
        $map->where("uc.fake","=",0);
        $map->where("uc.cooperate_mode","in",[1,2]);

        return $this->link()->table("qz_user")->alias("u")->where($map)
            ->join("qz_user_company uc","uc.userid=u.id","inner")
            ->join("qz_quyu q","q.cid=u.cs","inner")
            ->count("distinct u.id");
    }
}