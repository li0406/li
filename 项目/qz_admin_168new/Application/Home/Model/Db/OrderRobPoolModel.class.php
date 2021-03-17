<?php
/**
 * 抢单池
 */

namespace Home\Model\Db;

use Think\Model;

class OrderRobPoolModel extends Model
{
    protected $tableName = "order_rob_pool";

    public function getRobInfo($order_id)
    {
        return $this->where(['order_id' => ['eq', $order_id]])->find();
    }

    public function getLockRobInfo($order_id)
    {
        return $this->lock(true)->where(['order_id' => ['eq', $order_id]])->find();
    }

    public function addOrderRob($save)
    {
        return $this->add($save);
    }

    public function editOrderRob($where, $save)
    {
        return $this->where($where)->save($save);
    }

    public function getOrderRobDetailById($id)
    {
        return $this->alias('p')
            ->field('p.*,i.opid,i.opname,i.allot_source,u.jc,u.qc')
            ->where(['p.order_id' => ['eq', $id]])
            ->join('left join qz_order_info i on i.order = p.order_id')
            ->join('qz_user u on u.id = i.com')
            ->select();
    }

    public function delRobInfo($order_id)
    {
        return $this->where(['order_id' => ['eq', $order_id]])->delete();
    }

    public function getRobOrderCount($map, $group = 'id')
    {
        $lf_where = [];
        if($map['lf_status']){
            if($map['lf_status'] == 1){
                $lf_where['lf'] = ['eq',1];
            }else{
                $lf_where['no_lf'] = ['eq',1];
            }
            unset($map['lf_status']);
        }
        $buildSql = $this->alias('p')
            ->field('o.id,p.allot_num')
            ->join('qz_orders o on p.order_id = o.id')
            ->join('left join qz_order_info i on i.`order` = o.id')
            ->join('qz_order_pool po on po.orderid = o.id')
            ->join('qz_adminuser u on po.op_uid = u.id')
            ->join('qz_quyu q on q.cid = o.cs')
            ->where($map)
            ->group($group)
            ->buildSql();
        $buildSql = $this->table($buildSql)->alias('t')
            ->field('t.*,CASE r.`status` WHEN 1 THEN 1 WHEN 2 THEN 1 WHEN 4 THEN 1 ELSE 0 END lf,sum(IF(r.`status` = 3,1,0)) no_liangfang')
            ->join('left join qz_order_company_review r on r.orderid = t.id')
            ->group('id')
            ->buildSql();
        $buildSql = $this->table($buildSql)->alias('tt')
            ->field('tt.*,if(tt.lf <> 1 AND (tt.allot_num -1) = tt.no_liangfang,1,0) no_lf')
            ->buildSql();
        $buildSql = $this->table($buildSql)->alias('t1')
            ->where($lf_where)
            ->group('t1.id')
            ->buildSql();
        return $this->table($buildSql)->alias('t2')
            ->count();
    }

    public function getRobOrderList($map, $page = 0, $pageCount = 0, $group = 'id')
    {
        $lf_where = [];
        if($map['lf_status']){
            if($map['lf_status'] == 1){
                $lf_where['lf'] = ['eq',1];
            }else{
                $lf_where['no_lf'] = ['eq',1];
            }
            unset($map['lf_status']);
        }
        $buildSql = $this->alias('p')
            ->field('o.time_real,o.id,o.type_fw,p.allot_num,p.rob_num,SUM(if(i.allot_source = 3,1,0)) qiang_num,po.op_uid,po.op_name,p.is_rob,p.`status`,q.cname,u.kfgroup')
            ->join('qz_orders o on p.order_id = o.id')
            ->join('left join qz_order_info i on i.`order` = o.id')
            ->join('qz_order_pool po on po.orderid = o.id')
            ->join('qz_adminuser u on po.op_uid = u.id')
            ->join('qz_quyu q on q.cid = o.cs')
            ->where($map)
            ->group($group)->buildSql();
        $buildSql = $this->table($buildSql)->alias('t')
            ->field('t.*,CASE r.`status` WHEN 1 THEN 1 WHEN 2 THEN 1 WHEN 4 THEN 1 ELSE 0 END lf,sum(IF(r.`status` = 3,1,0)) no_liangfang')
            ->join('left join qz_order_company_review r on r.orderid = t.id')
            ->group('id')
            ->buildSql();
        $buildSql = $this->table($buildSql)->alias('tt')
            ->field('tt.*,if(tt.lf <> 1 AND (tt.rob_num -1) = tt.no_liangfang,1,0) no_lf')
            ->buildSql();
        return $this->table($buildSql)->alias('t1')
            ->limit($page, $pageCount)
            ->where($lf_where)
            ->group('t1.id')
            ->order('t1.time_real desc')
            ->select();
    }

    /**
     * 获取抢单池已抢的订单编号
     *
     * @param $map
     * @return mixed
     */
    public function getRobOrderId($map = [])
    {
        $map['is_rob'] = 2;
        return $this->where($map)->getField('order_id',true);
    }
}