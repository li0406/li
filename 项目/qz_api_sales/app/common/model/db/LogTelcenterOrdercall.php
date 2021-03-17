<?php

/**
 * @Author: liuyupeng
 * @Email: lypeng9205@163.com
 * @Date:   2019-06-05 11:35:05
 * @Last Modified by:   liuyupeng
 * @Last Modified time: 2019-06-06 09:05:24
 */

namespace app\common\model\db;

use think\Model;

class LogTelcenterOrdercall extends Model
{
    protected $autoWriteTimestamp = false;

    /**
     * 通过订单号获取 通话次数
     *
     * @param  array $orderIds 订单号
     * @return mixed  通话列表 或 false
     */
    public function getOrderCallCountByOrderIds($orderIds)
    {
        if (!empty($orderIds)) {
            if (!is_array($orderIds)) {
                $orderIds = explode(",", $orderIds);
            }

            $build = self::alias('o')
                        ->field('o.orderid AS id')
                        ->join('log_telcenter t', 'o.callSid = t.callSid')
                        ->where('o.orderid', 'IN', $orderIds)
                        ->group('t.callSid')
                        ->buildSql();

            $result = self::table($build)->alias('z')->field('z.id, count(*) AS repeat_count')->group('z.id')->select();
            return $result;
        }

        return [];
    }

    /**
     * 通过订单号获取通话记录
     *
     * @param  array $orderIds 订单号
     * @return mixed  通话列表 或 false
     */
    public function getOrderCallListByOrderId($orderId){
        return self::alias('o')
                ->join('log_telcenter t', 'o.callSid = t.callSid')
                ->where('o.orderid', $orderId)
                ->field([
                    'o.id', 'o.calltype', 'o.orderid as orders_id', 'o.callSid as call_sid', 'o.channel', 'o.time_add',
                    't.caller', 't.called', 't.byetype', 't.action', 't.duration', 't.recordurl'
                ])->order('t.time_add')
                ->select()->toArray();
    }
}