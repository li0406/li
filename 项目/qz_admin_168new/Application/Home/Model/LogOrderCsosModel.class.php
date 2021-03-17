<?php
/**
 * 客服订单操作日志，Csos
 */
namespace Home\Model;
use Think\Model;
class LogOrderCsosModel extends Model{
    protected $autoCheckFields = false;

    /**
     * [getRecentOperateLogByUserId 获取用户最近操作记录]
     * @param  [type] $user_id [用户id]
     * @return [type]          [description]
     */
    public function getRecentOperateLogByUserId($user_id, $limit = 12){
        if (!empty($user_id)) {
            $map['c.user_id'] = $user_id;
            $limit = empty($limit) ? 20 : intval($limit);
            $result = M('log_order_csos')->alias('c')
                                         ->field('c.time,o.id,o.on,o.on_sub,o.type_fw,q.cname AS city,a.qz_area AS area')
                                         ->join('INNER JOIN qz_orders AS o ON o.id = c.orderid')
                                         ->join('INNER JOIN qz_quyu AS q ON q.cid = o.cs')
                                         ->join('INNER JOIN qz_area AS a ON a.qz_areaid = o.qx')
                                         ->where($map)
                                         ->limit($limit)
                                         ->order('c.time DESC')
                                         ->group('c.orderid')
                                         ->select();
            return $result;
        }
        return false;
    }
}