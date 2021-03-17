<?php

//城市会员

namespace Home\Model;
Use Think\Model;

class CityvipModel extends Model{

    protected $autoCheckFields = false;

    //取城市会员数
    public function getCityVipCount(){
        $buildSql = M("user")->alias("a")
                            ->field("a.cs,sum(if(a.on = 2,b.viptype,null)) as vipcnt,sum(if(b.viptype > 1,(b.viptype-1),null)) as doublecnt,count(if(a.on = 2,1,null)) as vipnum,count(if(a.on = 2 and (b.viptype > 1),1,null )) as mulitnum,count(if(a.on = 2 and (b.viptype =0.5),1,null )) as halfnum")
                            ->join("inner join qz_user_company b on a.id = b.userid WHERE (a.classid = 3) AND (b.fake = 0) AND (a.on = '2' )")
                            ->group('a.cs')
                            ->order("vipcnt desc")
                            ->buildSql();

        return M()->table($buildSql)
                ->field('t.*,c.cname,c.manager,c.bm')
                ->alias("t")
                ->join("left join qz_quyu as c on c.cid = t.cs")
                ->order("vipcnt desc,bm")
                ->select();
    }


    public function getNewCityVipCount()
    {
        $map = [
            "a.classid" => ["IN",[3,6]],
            "a.on" => ["IN",[2,-1,-4]]
        ];
        $buildSql = M("user")->alias("a")->where($map)
                 ->join("join qz_user_company b on a.id = b.userid and b.fake = 0")
                 ->field("a.id,a.on,a.classid,b.cooperate_mode,b.viptype")->buildSql();

        $buildSql = M("user")->table($buildSql)->alias("t")
                 ->join("left join qz_user_company_account c on c.user_id = t.id and t.cooperate_mode = 2 and t.classid = 3")
                 ->field("t.*,c.account_amount,c.round_order_number")->buildSql();

        $buildSql = M("user")->table($buildSql)->alias("t1")
                 ->join("left join qz_user_company_round_order_amount d on d.company_id = t1.id")
                 ->field("t1.*, 
                            case  
                                 when t1.on = 2 and t1.cooperate_mode = 2 and t1.round_order_number > 0 then 1
                                 when t1.on = 2 and t1.cooperate_mode = 2 and t1.account_amount > min(d.price) and min(d.price) > 0 and t1.account_amount > 0 then 1
                                 when t1.cooperate_mode = 1 and t1.on = 2 then 1 
                                 else 2 
                            end as mark")
                 ->group("t1.id")->buildSql();

        $buildSql =  M("user")->table($buildSql)->alias("t2")
                        ->field("count(if(classid = 3 and  cooperate_mode = 1 and mark = 1,1,null)) as vip_count,sum(if(classid = 3 and cooperate_mode = 1 and mark = 1 ,viptype,null)) as mulit_vip_count,count(if(classid = 6 and mark = 1,1,null)) as qian_count,count(if(cooperate_mode = 2 and mark = 1,1,null)) as new_qian_count,count(if(cooperate_mode = 2 and mark = 2,1,null)) as new_qian_wx_count")
                        ->group("t2.cooperate_mode")->buildSql();

        return M("user")->table($buildSql)->alias("t2")
                        ->field("max(vip_count) vip_count,max(mulit_vip_count) mulit_vip_count,max(qian_count) qian_count,max(new_qian_count) new_qian_count,max(new_qian_wx_count) new_qian_wx_count")
                        ->find();
    }


}

