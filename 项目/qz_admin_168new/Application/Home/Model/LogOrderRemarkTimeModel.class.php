<?php

namespace Home\Model;
Use Think\Model;
/**
*   订单状态日志表
*/
class LogOrderRemarkTimeModel extends Model
{
    /**
     * [addLogOrderRemarkTime 新增添加订单备注时间]
     * @param [type] $save [description]
     */
    public function addLogOrderRemarkTime($save){
        if (empty($save)) {
            return false;
        }
        $result = M('log_order_remark_time')->add($save);
        return $result;
    }

    /**
     * [getLastLogOrderRemarkTimeByOrderIds 根据订单ID获取订单最近的订单备注的添加时间]
     * @param  [type] $orderIds [订单IDs]
     * @return [type]           [description]
     */
    public function getLastLogOrderRemarkTimeByOrderIds($orderIds = []){
        if (empty($orderIds)) {
            return false;
        }
        if (!is_array($orderIds)) {
            $orderIds = array($orderIds);
        }
        $map['order_id'] = array('IN', $orderIds);
        $build = M('log_order_remark_time')->field('order_id,remark_time')->where($map)->order('id DESC')->buildSql();
        $result = M()->table($build)->alias('z')->group('z.order_id')->select();
        return $result;
    }
}