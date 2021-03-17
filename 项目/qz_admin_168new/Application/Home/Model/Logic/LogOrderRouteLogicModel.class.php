<?php

namespace Home\Model\Logic;

use Home\Model\Db\LogOrderRouteModel;

class LogOrderRouteLogicModel
{
    public function getOrderLog($jiaju_order, $order_id = '', $type = 1)
    {
        if (!$order_id && !$jiaju_order) {
            return [];
        }
        $where = [
            'type' => ['eq', $type]
        ];
        if ($jiaju_order) {
            $where['jiaju_order_id'] = ['eq', $jiaju_order];
        } else {
            $where['order_id'] = ['eq', $order_id];
        }
        $logDb = new LogOrderRouteModel();
        return $logDb->getRouteInfo($where);
    }

    public function saveOrderLog($order_id, $jiaju_id, $type = 1)
    {
        if (!$order_id || !$jiaju_id) {
            return [];
        }
        $save = [
            'type' => $type,
            'jiaju_order_id' => $jiaju_id,
            'order_id' => $order_id,
            'addtime' => time(),
        ];
        $logDb = new LogOrderRouteModel();
        return $logDb->saveRouteInfo($save);
    }
}
