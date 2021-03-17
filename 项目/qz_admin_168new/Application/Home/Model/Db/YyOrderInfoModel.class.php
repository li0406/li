<?php


namespace Home\Model\Db;
use Think\Model;

class YyOrderInfoModel extends Model
{
    protected $autoCheckFields = false;
    /**
     * 统计发单量
     * @param  [type] $ids [description]
     * @return mixed
     */
    public function getIssuanceCountByMonthWithDept($ids, $yearStart, $yearEnd, $is_char = false)
    {
        if (empty($ids)){
            return [];
        }
        $map['s.dept'] = ['in' , $ids];
        $map['s.visible'] = 0;
        $map['s.type'] = 1;
        if ($is_char === 2) {
            $map['s.charge'] = 2;
        } elseif($is_char === 1) {
            $map['s.charge'] = 1;
        }
        $map['o.time_real'] = ['between',[$yearStart,$yearEnd]];
        return M('orders')->alias('o')
            ->force('idx_time_real')
            ->field('s.dept,FROM_UNIXTIME(o.time_real,"%m") as count_time,FROM_UNIXTIME(o.time_real,"%Y-%m") as month_val,count(*) as count1')
            ->join('inner join qz_yy_order_info a ON a.oid = o.id')
            ->join('inner join qz_order_source s ON s.src = a.src')
            ->where($map)
            ->group('s.dept,count_time')
            ->select();
    }

    /**
     * 统计实际分单量
     * @param  [type] $ids [description]
     * @return mixed
     */
    public function getDivideCountByMonthWithDept($ids, $yearStart, $yearEnd, $is_char = false)
    {
        if (empty($ids)){
            return [];
        }
        $map['s.dept'] = ['in' , $ids];
        $map['s.visible'] = 0;
        $map['s.type'] = 1;
        if ($is_char === 2) {
            $map['s.charge'] = 2;
        } elseif($is_char === 1) {
            $map['s.charge'] = 1;
        }
        $map['v.lasttime'] = ['between',[$yearStart,$yearEnd]];
        return M('order_csos_new')->alias('v')
            ->force('idx_csos_new_lasttime')
            ->field('s.dept,FROM_UNIXTIME(v.lasttime,"%m") AS count_time,FROM_UNIXTIME(v.lasttime,"%Y-%m") AS month_val,count(if(v.order_id is not null AND o.on = 4 AND o.type_fw = 1,1,null)) AS count2')
            ->join('INNER JOIN qz_yy_order_info a ON a.oid = v.order_id')
            ->join('INNER JOIN qz_orders o ON o.id = a.oid')
            ->join('INNER JOIN qz_order_source s ON s.src = a.src')
            ->where($map)
            ->group('s.dept,count_time')
            ->select();
    }

    /**
     * 装修订单渠道来源查询
     *
     * @param $map
     * @return mixed
     */
    public function getZxSrcYyOrderInfo($map)
    {
        return M('yy_order_info')
            ->alias('a')
            ->field('o.id,o.on_sub,o.on,o.type_fw,o.name fd_name,o.tel,a.oid,a.src,s.alias qd_alias,s.name source,g.id gid,g.name group_name,g2.name top_group_name,d.id did,d.name dept_name,r.order_id,r.jiaju_order_id')
            ->join('INNER JOIN qz_orders o ON o.id = a.oid')
            ->join('INNER JOIN qz_order_source s ON s.src = a.src')
            ->join('INNER JOIN qz_order_source_group g ON s.groupid = g.id')
            ->join('LEFT JOIN qz_department_identify d ON s.dept = d.id')
            ->join('LEFT JOIN qz_order_source_group g2 ON g2.id = g.parentid')
            ->join('LEFT JOIN qz_log_order_route r ON r.order_id = a.oid')
            ->where($map)
            ->select();
    }

    /**
     * 家具订单渠道来源查询
     *
     * @param $map
     * @return mixed
     */
    public function getJiajuSrcYyOrderInfo($map)
    {
        return M('jiaju_yy_order_info')
            ->alias('a')
            ->field('o.on_sub,o.on,o.type_fw,o.name fd_name,o.tel,a.oid,a.src,s.alias qd_alias,s.name source,g.id gid,g.name group_name,g2.name top_group_name,d.id did,d.name dept_name,r.order_id,r.jiaju_order_id')
            ->join('INNER JOIN qz_jiaju_order o ON o.id = a.oid')
            ->join('INNER JOIN qz_order_source s ON s.src = a.src')
            ->join('INNER JOIN qz_order_source_group g ON s.groupid = g.id')
            ->join('LEFT JOIN qz_department_identify d ON s.dept = d.id')
            ->join('LEFT JOIN qz_order_source_group g2 ON g2.id = g.parentid')
            ->join('LEFT JOIN qz_log_order_route r ON r.jiaju_order_id = a.oid')
            ->where($map)
            ->select();
    }
}
