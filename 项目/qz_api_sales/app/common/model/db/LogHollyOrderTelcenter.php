<?php

namespace app\common\model\db;

use think\Model;

class LogHollyOrderTelcenter extends Model
{

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
                        ->field('o.order_id AS id')
                        ->join('log_holly_telcenter t', 'o.call_sid = t.call_sid')
                        ->where('o.order_id', 'IN', $orderIds)
                        ->group('t.call_sid')
                        ->buildSql();

            $result = self::table($build)->alias('z')->field('z.id, count(*) AS repeat_count')->group('z.id')->select();
            return $result;
        }

        return [];
    }
}