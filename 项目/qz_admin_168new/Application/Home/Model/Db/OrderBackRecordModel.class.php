<?php

namespace Home\Model\Db;

use Think\Model;

class OrderBackRecordModel extends Model
{
    public function addRecord($save)
    {
        return $this->add($save);
    }

    public function getOrderLastNewRecord($order_id)
    {
        $where = [
            'r.order_id' => $order_id,
            'o.on' => 99,
        ];
        return M('orders')->alias('o')
            ->field('r.*')
            ->where($where)
            ->join('qz_order_back_record r on r.order_id = o.id')
            ->order('r.id desc')
            ->find();
    }

    public function getBackRecordCount($where)
    {
        $buildSql = $this->getBackRecord($where['start'], $where['end']);
        //时间
        $where['t.created_at'] = ['between', [$where['start'], $where['end']]];
        unset($where['start'], $where['end']);

        return $this->table($buildSql)->alias('t')
            ->where($where)
            ->count('t.order_id');
    }

    public function getBackRecordList($where, $offset, $limit)
    {
        $buildSql = $this->getBackRecord($where['start'], $where['end']);
        //时间
        $where['t.created_at'] = ['between', [$where['start'], $where['end']]];
        unset($where['start'], $where['end']);

        $buildSql = $this->table($buildSql)->alias('t')
            ->where($where)
            ->order('t.id desc')
            ->limit($offset, $limit)
            ->buildSql();
        return $this->table($buildSql)->alias('t')
            ->field('t.*,u.user push_name,u1.user check_name,s.alias src_alias,q.cname,p.op_name belonger')
            ->join('qz_order_pool p on p.orderid = t.order_id')
            ->join('left join qz_quyu q on q.cid = t.cs')
            ->join('left join qz_adminuser u on u.id = t.push_uid')
            ->join('left join qz_adminuser u1 on u1.id = t.check_uid')
            ->join('left join qz_order_source s on s.src = t.src')
            ->select();
    }

    /**
     * 获取数据源
     * @param $start
     * @param $end
     * @return mixed
     */
    public function getBackRecord($start, $end)
    {
        $where = [
            'r.created_at' => ['between', [$start, $end]]
        ];
        $buildSql = $this->alias('r')
            ->where($where)
            ->field('r.*,o.cs,s.src,s.alias')
            ->join('qz_orders o on o.id = r.order_id')
            ->join('LEFT JOIN qz_orders_source AS os ON os.orderid = o.id')
            ->join('LEFT JOIN qz_order_source s ON s.src = os.source_src')
            ->order('r.id desc')
            ->buildSql();
        return $this->table($buildSql)->alias('t')
            ->group('t.order_id')
            ->buildSql();
    }

    public function getOrderBackRecord($order_id)
    {
        $where = [
            't.order_id' => ['eq', $order_id]
        ];
        return $this->alias('t')
            ->where($where)
            ->field('t.*,u.user push_name,u1.user check_name')
            ->join('left join qz_adminuser u on u.id = t.push_uid')
            ->join('left join qz_adminuser u1 on u1.id = t.check_uid')
            ->order('t.id desc')
            ->select();
    }

    public function getBackRecordImg($recordIds)
    {
        $where = [
            'record_id' => ['in', $recordIds]
        ];
        return M('order_back_record_img')->where($where)->select();
    }

    public function editRecord($id, $save)
    {
        return $this->where(['id'=>['eq',$id]])->save($save);
    }
}
