<?php
/**
 * 订单相邻城市 qz_order_city_relation
 */

namespace Common\Model\Db;

use Think\Model;

class OrderCityRelationModel extends Model
{
    protected $tableName = "order_city_relation";

    /**
     * 获取当前城市相邻的城市
     * @param  [type] $cs [description]
     * @return [type]     [description]
     */
    public function getRelationCity($cs)
    {
        $map = array(
            "a.cid" => array("EQ",$cs)
        );
        return M('order_city_relation')->where($map)->alias("a")
            ->join("join qz_quyu as q on find_in_set(q.cid,a.relation)")
            ->field("a.cid as cs,q.cname,q.cid,q.little")
            ->select();

    }
}