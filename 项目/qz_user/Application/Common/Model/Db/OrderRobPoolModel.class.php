<?php
/**
 * 抢单池
 */
namespace Common\Model\Db;

use Think\Model;

class OrderRobPoolModel extends Model
{
    protected $tableName = "order_rob_pool";

    public function getRobInfo($order_id)
    {
        return $this->where(['order_id' => ['eq', $order_id]])->find();
    }

    /**
     * 获取抢单池数量
     * @param array $map
     * @param $page
     * @param $pageCount
     * @return array
     */
    public function getOrderRobList($map = [],$com_id = '', $page, $pageCount)
    {
        if (empty($map['qx'])) {
            return [];
        }
        $where['o.qx'] = $map['qx'];
        $where['r.is_rob'] = ['eq', 1];
        $where['r.order_status'] = $map['order_status'];

        if ($map['yusuan']) {
            $where['o.yusuan'] = $map['yusuan'];
        }
        if ($map['lx']) {
            $where['o.lx'] = $map['lx'];
        }

        if ($map['lxs']) {
            $where['o.lxs'] = $map['lxs'];
        }
        if ($map['mianji']) {
            $where['o.mianji'] = $map['mianji'];
        }

        if ($map['fangshi']) {
            $where['o.fangshi'] = $map['fangshi'];
        }
        if ($map['passOrder']) {
            $where['r.order_id'] = ['not in', $map['passOrder']];
        }
        $buildSql = $this->alias('r')
            ->field('o.id,r.cs,q.cname,area.qz_area,o.xiaoqu,o.mianji,o.lx,o.fangshi,j.name yusuan,r.add_time,o.time,if(find_in_set(' . $com_id . ',c.other_id) <> 0,1,0) as pass_order')
            ->join('qz_orders o on o.id = r.order_id')
            ->join('qz_quyu q on q.cid = r.cs')
            ->join('qz_area area on area.qz_areaid = o.qx')
            ->join('left join qz_jiage AS j ON j.id = o.yusuan')
            ->join('left join qz_order_info i on i.order = o.id')
            ->join('LEFT JOIN qz_user_company c on i.`com` = c.userid')
            ->where($where)->buildSql();
        $buildSql = $this->table($buildSql)->alias('t')->group('t.id')->buildSql();
        //过滤已分配的分单中 , 已分配的装修公司 设置的对立公司 包含自己
        $buildSql = $this->table($buildSql)->alias('t')->where(['pass_order' => ['neq', 1]])->buildSql();
        return $this->table($buildSql)->alias('t')->order('t.add_time desc')
            ->limit($page, $pageCount)
            ->select();

    }

    /**
     * 获取抢单池数量
     * @param array $map
     * @return array
     */
    public function getOrderRobCount($map = [],$com_id = '')
    {
        if (empty($map['qx'])) {
            return [];
        }
        $where['o.qx'] = $map['qx'];
        $where['r.is_rob'] = ['eq', 1];
        if(!empty($map['order_status'])){
            $where['r.order_status'] = $map['order_status'];
        }

        if ($map['yusuan']) {
            $where['o.yusuan'] = $map['yusuan'];
        }
        if ($map['lx']) {
            $where['o.lx'] = $map['lx'];
        }

        if ($map['lxs']) {
            $where['o.lxs'] = $map['lxs'];
        }
        if ($map['mianji']) {
            $where['o.mianji'] = $map['mianji'];
        }

        if ($map['fangshi']) {
            $where['o.fangshi'] = $map['fangshi'];
        }
        if ($map['passOrder']) {
            $where['r.order_id'] = ['not in', $map['passOrder']];
        }
        $buildSql = $this->alias('r')
            ->field('r.id,r.order_id,i.com,if(find_in_set(' . $com_id . ',c.other_id) <> 0,1,0) as pass_order')
            ->join('qz_orders o on o.id = r.order_id')
            ->join('left join qz_order_info i on i.order = o.id')
            ->join('LEFT JOIN qz_user_company c on i.`com` = c.userid')
            ->where($where)
            ->order('pass_order desc')
            ->buildSql();
        $buildSql = $this->table($buildSql)->alias('t')->group('t.order_id')->buildSql();
        //过滤已分配的分单中 , 已分配的装修公司 设置的对立公司 包含自己
        $buildSql = $this->table($buildSql)->alias('t')->where(['pass_order'=>['neq',1]])->buildSql();
        return $this->table($buildSql)->alias('t')->count();
    }

    /**
     * 当前公司在抢单池中已经处理过(抢单/赠单)的订单
     *
     * @param array $map
     * @return array
     */
    public function getPassOrderRobList($map = [])
    {
        if (empty($map['qx'])) {
            return [];
        }
        $where['o.qx'] = $map['qx'];
        $where['r.is_rob'] = ['eq', 1];

        if ($map['company']) {
            $where['i.com'] = $map['company'];
        }

        return $this->alias('r')
            ->field('o.id,i.com')
            ->join('join qz_orders o on o.id = r.order_id')
            ->join('join qz_order_info i on i.order = o.id')
            ->where($where)
            ->select();
    }

    /**
     * 抢单具体信息
     *
     * @param $map
     * @return mixed
     */
    public function getRobOrderInfo($map)
    {
        $where = [];
        if($map['order_id']){
            $where['order_id'] = ['eq',$map['order_id']];
        }
        return $this->where($where)->find();
    }
}