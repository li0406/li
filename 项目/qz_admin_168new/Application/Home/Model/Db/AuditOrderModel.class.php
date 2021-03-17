<?php
// +----------------------------------------------------------------------
// | AuditOrderModel
// +----------------------------------------------------------------------

/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/7/25
 * Time: 17:05
 */

namespace Home\Model\Db;


use Think\Model;

class AuditOrderModel extends Model
{
    // 是否自动检测数据表字段信息
    protected $autoCheckFields = false;

    private function buildAllotAuditOrdersBaseSql($cs, $qx, $type_fw, $duijie_admin, $begin, $end, $search)
    {
        $map['o.on'] = ['EQ',4];
        $map['o.type_fw'] = ['in', [1,2,3,4]];
        $map['o.cs'] = ['in',$cs];
        // 默认取发单时间是*天之后的订单
//        $map['o.time_real'] = ['gt', strtotime('-3 month')];

        if (!empty($qx)) {
            $map['o.qx'] = array('EQ',$qx);
        }

        if (!empty($type_fw)) {
            $map['o.type_fw'] = array('EQ',$type_fw);
        }

        if (!empty($search)) {
            $map['_complex'] =array(
                'o.id' => array('EQ',$search),
                'o.xiaoqu' => array('LIKE',"%$search%"),
                '_logic' => 'OR'
            );
        }

        if (!empty($begin) && !empty($end)) {
            $map['a.time'] = ['between', [strtotime($begin), strtotime($end)]];
        }

        if (!empty($duijie_admin)) {
            $map['a.op_uid'] = is_array($duijie_admin) ? ['IN', $duijie_admin] : ['EQ', $duijie_admin];
        }

        return M('order_docking')
            ->alias('a FORCE INDEX(idx_time)')
            ->where($map)
            ->join('join qz_orders o on a.order_id = o.id')
            ->join('join qz_order_csos_new new on new.order_id = o.id')
            ->join('join qz_quyu q on q.cid = o.cs')
            ->join('join qz_area area on area.qz_areaid = o.qx')
            ->join('left join qz_order_allot_num an on an.order_id = o.id')
            ->join('left JOIN qz_order_rob_pool orp ON orp.order_id = o.id')
            ->field('a.op_uname,o.source_in, o.id,o.time_real,o.remarks,q.cname,area.qz_area,o.wzd,o.mianji,o.tel,o.on,o.type_fw,a.time,TIMESTAMPDIFF(SECOND,FROM_UNIXTIME(new.lasttime),FROM_UNIXTIME(a.time)) as time_diff,o.source,if(an.allot_num IS NOT NULL,an.allot_num,4) as allot_num,orp.id rob_id,orp.is_rob,orp.rob_num')
            ->order('a.time desc')
            ->buildSql();
    }

    /**
     * 获取已分配对接列表数量
     *
     * @param $cs
     * @param $qx
     * @param $type_fw
     * @param $fen_customer
     * @param $begin
     * @param $end
     * @param $search
     * @return mixed
     */
    public function getAllotAuditOrdersCount($cs, $qx, $type_fw, $fen_customer, $begin, $end, $search)
    {
        $buildSql =  $this->buildAllotAuditOrdersBaseSql($cs, $qx, $type_fw, $fen_customer, $begin, $end, $search);
        //过滤在抢单池中的订单
        $buildSql = M("order_docking")->table($buildSql)->alias('r')->having('r.rob_id IS NULL or r.is_rob = 2')->buildSql();
        //过滤在抢单池中的订单
        return M('order_docking')->table($buildSql)->alias('t1')->count('t1.id');
    }

    /**
     * 获取已分配对接列表数量
     *
     * @param $cs
     * @param $qx
     * @param $type_fw
     * @param $fen_customer
     * @param $begin
     * @param $end
     * @param $search
     * @return mixed
     */
    public function getAllotAuditOrdersLists($cs, $qx, $type_fw, $fen_customer, $begin, $end, $search, $page, $pagecount)
    {
        $buildSql = $this->buildAllotAuditOrdersBaseSql($cs, $qx, $type_fw, $fen_customer, $begin, $end, $search);

        //过滤在抢单池中的订单
        $buildSql = M('order_docking')->table($buildSql)->alias('r')
            ->having('r.rob_id IS NULL or r.is_rob = 2')
            ->order('time desc')
            ->limit($page, $pagecount)
            ->buildSql();

        return M()->table($buildSql)->alias('t1')
            ->field('t1.*,count(i.id) already_allot_num')
            ->join('left join qz_order_info i on i.order = t1.id')
            ->group('t1.id')
            ->order('time desc')
            ->select();
    }

    /**
     * 获取未分配对接列表
     *
     * @param $cs
     * @param $qx
     * @param $type_fw
     * @param $fen_customer
     * @param $begin
     * @param $end
     * @param $search
     * @return mixed
     */
    public function getUnAllotAuditOrdersList($cs, $qx, $type_fw, $fen_customer, $begin, $end, $search, $page, $pagecount)
    {
        $map['o.cs'] = ['IN', $cs];
        $map['o.on'] = ['EQ', 4];
        $map['o.type_fw'] = ['EQ', 1];
        $map['o.fen_customer'] = ['EQ', 0];
        // 默认取发单时间是*天之后的订单
        $map['o.time'] = ['gt', strtotime('-3 month')];

        if (!empty($fen_customer)) {
            $map['o.fen_customer'] = array('EQ', $fen_customer);
        }

        if (!empty($qx)) {
            $map['o.qx'] = ['eq', $qx];
        }

        if (!empty($fen_customer)) {
            if (!empty($begin) && !empty($end)) {
                $map['o.lasttime'] = ['between', [strtotime($begin), strtotime('+1 day', strtotime($end)) - 1]];
            }
        } else {
            if (!empty($begin) && !empty($end)) {
                $map['new.lasttime'] = ['between', [strtotime($begin), strtotime('+1 day', strtotime($end)) - 1]];
            }
        }

        if (!empty($search)) {
            $map['_complex'] = array(
                'o.id' => array('EQ', $search),
                'o.xiaoqu' => array('LIKE',"%$search%"),
                '_logic' => 'OR'
            );
        }

        if (!empty($type_fw)) {
            $map['o.type_fw'] = array('EQ', $type_fw);
        }

        $buildSql =  M('orders')->alias('o')
            ->where($map)
            ->join('join qz_order_csos_new new on new.order_id = o.id')
            ->field('o.id,o.source_in,o.time_real,o.remarks,o.cs,o.qx,o.wzd,o.mianji,o.tel,o.on,o.type_fw,FROM_UNIXTIME(new.lasttime) csos_time,FROM_UNIXTIME(o.lasttime) lasttime,o.source')
            ->join('join qz_order_pool b on b.orderid = o.id and b.status = 0')
            ->order('o.lasttime desc')
            ->limit($page, $pagecount)
            ->buildSql();
        return M('orders')->table($buildSql)->alias('t')
            ->join('left join qz_quyu q on q.cid = t.cs')
            ->join('left join qz_area a on a.qz_areaid = t.qx')
            ->field('t.*,q.cname,a.qz_area')
            ->select();
    }
}