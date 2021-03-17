<?php
// +----------------------------------------------------------------------
// | CpaOrdersLogicModel  CPA装修订单主表
// +----------------------------------------------------------------------
namespace Common\Model\Db;

use Think\Model;

class CpaOrdersModel extends Model
{
    protected $tableName = 'cpa_orders';

    /**
     * 根据查询条件查询订单所有数据
     * @param [array]$map 查询条件
     */
    public function getFullOne($map)
    {
        return $this->where($map)->alias("a")
            ->join("LEFT JOIN qz_quyu as q on q.cid = a.cs")
            ->join("LEFT JOIN qz_area as b on b.qz_areaid = a.qx")
            ->join("LEFT JOIN qz_jiage as c on c.id = a.yusuan")
            ->join("LEFT JOIN qz_huxing as d on d.id  = a.huxing")
            ->join("LEFT JOIN qz_fengge as f on f.id  = a.fengge")
            ->join("LEFT JOIN safe_order_tel8 as h on h.orderid  = a.id")
            ->field("a.*,q.cname,b.qz_area,a.fengge as fg,c.name as ysjg,d.name as hxing,f.name as fge,h.tel8 as real_tel")
            ->find();
    }
}