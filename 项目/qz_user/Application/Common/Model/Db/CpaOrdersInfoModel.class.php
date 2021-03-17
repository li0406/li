<?php
// +----------------------------------------------------------------------
// | CpaOrdersLogicModel  订单和商家关联表
// +----------------------------------------------------------------------
namespace Common\Model\Db;

use Think\Model;

class CpaOrdersInfoModel extends Model
{
    protected $tableName = 'cpa_orders_info';

    /**
     * 根据查询条件查询数据
     * @param [array]$map 查询条件
     */
    public function getOne($map)
    {
        return $this->where($map)->find();
    }

    /**
     * 根据查询条件修改是否已读
     * @param [array]$map 查询条件
     */
    public function changeRead($map)
    {
        $data['isread'] = 1;
        $data['readtime'] = time();
        return $this->where($map)->save($data);
    }

    /**
     * 根据条件获取总数
     */
    public function getCount($map)
    {
        return $this->where($map)->alias('t')
            ->join('inner join qz_cpa_orders as b on t.order_id = b.id AND b.on = 4')
            ->count(1);
    }

    /**
     * 根据条件获取列表
     */
    public function getList($map,$page,$pageSize)
    {
        $buildSql = $this->alias('t')
            ->join('inner join qz_cpa_orders as b on t.order_id = b.id AND b.on = 4')
            ->where($map)
            ->order('csos_time desc')
            ->field('t.*,b.id,b.order_id as oid,b.on,b.name,b.cs,b.qx,b.xiaoqu,b.yusuan,b.csos_time,b.time_real')
            ->page($page . ',' . $pageSize)
            ->buildSql();
        return $this->table($buildSql)->alias('a')
            ->join('inner join qz_cpa_quyu as cs on cs.cid = a.cs AND cs.is_open_city = 1')
            ->join('inner join qz_area as c on c.qz_areaid = a.qx')
            ->join('inner join qz_jiage as d on d.id = a.yusuan')
            ->join('inner join safe_order_tel8 as t on t.orderid = a.id')
            ->order('a.csos_time desc,a.time_real desc')
            ->field('a.*,cs.cname,c.qz_area,d.name as jiage,t.tel8 as real_tel')
            ->select();
    }
}