<?php
// +----------------------------------------------------------------------
// | JiajuOrderLogicModel  CPA装修订单主表
// +----------------------------------------------------------------------
namespace Common\Model\Db;

use Think\Model;

class JiajuOrderModel extends Model
{
    protected $tableName = 'jiaju_order';

    /**
     * 根据查询条件查询订单所有数据
     * @param [array]$map 查询条件
     */
    public function getFullOne($map)
    {
        return $this->where($map)->alias('a')
            ->join('JOIN safe_order_tel8 as h on h.orderid  = a.id')
            ->join('LEFT JOIN qz_jiaju_quyu as q on q.cid = a.cs')
            ->join('LEFT JOIN qz_area as b on b.qz_areaid = a.qx')
            ->join('LEFT JOIN qz_jiaju_yusuan as c on c.id = a.yusuan')
            ->join('LEFT JOIN qz_huxing as d on d.id  = a.huxing')
            ->join('LEFT JOIN qz_fengge as f on f.id  = a.fengge')
            ->field('a.*,q.cname,b.qz_area,a.fengge as fg,c.name as ysjg,d.name as hxing,f.name as fge,h.tel8 as real_tel')
            ->find();
    }

    /**
     * 编辑家具订单信息
     */
    public function editJiajuOrder($id,$data)
    {
        $map = ['id'=>$id];
        return $this->where($map)->save($data);
    }
}