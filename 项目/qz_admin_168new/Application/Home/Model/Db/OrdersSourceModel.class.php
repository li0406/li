<?php
namespace Home\Model\Db;


use Think\Model;
/**
 *
 */
class OrdersSourceModel extends Model
{
    public function getOrderSrc($order_id)
    {
        $map = [
            "a.orderid" => ["EQ",$order_id]
        ];
        return $this->where($map)->alias("a")
                ->join("left join qz_order_source s on s.src = a.source_src")
                ->join("left join qz_hzs_source hs on hs.sourceid = s.id")
                ->join("left join qz_hzs_company hc on hc.id = hs.companyid")
                ->join("left join qz_order_csos_new new on new.order_id = a.orderid")
                ->field("source_src,hc.id as hzs_id,hc.arrears_order,hc.account,new.lasttime")->find();
    }
}