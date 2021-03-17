<?php
// +----------------------------------------------------------------------
// | OrderCityRelation
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Model;

class OrderCityRelation extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_order_city_relation';

    public function getRelationCityList($cs)
    {
        return $this->alias("a")
            ->where("a.cid", $cs)
            ->where("q.cid", "neq", $cs)
            ->join("qz_quyu q", "find_in_set(q.cid,a.relation)")
            ->field("a.cid as cs,q.cname,q.cid")
            ->select();
    }
}