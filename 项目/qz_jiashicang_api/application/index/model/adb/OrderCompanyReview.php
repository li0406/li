<?php

namespace app\index\model\adb;


use app\common\model\adb\BaseModel;

class OrderCompanyReview extends BaseModel
{
    public function getLiangFangCount($begin,$end,$city)
    {
        $map[] = ["a.lf_time","EGT",$begin];
        $map[] = ["a.lf_time","LT",$end];

        if (count($city) > 0) {
            $map[] = ["o.cs","IN",$city];
        }

        return  $this->link()->table("qz_order_company_review")->where($map)->alias("a")
                    ->join("qz_orders o","o.id = a.orderid")
            ->field("count(DISTINCT if(a.status in (1,2,4),a.orderid,null)) as count,count(DISTINCT if(a.status not in (1,2,4),a.orderid,null)) as un_count")->find();
    }

    /**
     * @des:月维度获取量房量
     */
    public function getLiangfangDataByMonth($begin,$end)
    {
        $map[] = ["a.lf_time","EGT",$begin];
        $map[] = ["a.lf_time","LT",$end];

        return  $this->link()->table("qz_order_company_review")->where($map)->alias("a")
            ->join("qz_orders o","o.id = a.orderid")
            ->field(["count(DISTINCT if(a.status in (1,2,4),a.orderid,null)) as count","FROM_UNIXTIME(a.lf_time,'%Y%m') as time"])
            ->group("FROM_UNIXTIME(a.lf_time,'%Y%m')")
            ->order('time asc')
            ->select();
    }
}