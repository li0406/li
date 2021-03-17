<?php

namespace Home\Model\Db;

use Think\Model;

class JiajuCsosNewModel extends Model
{
    protected $tableName = "jiaju_order_csos_new";

    public function saveCsos($save)
    {
        return $this->add($save);
    }

    /**
     * @param $data
     * @return mixed添加订单记录
     */
    public function addJiajuLogOrderCsos($data)
    {
        return M('jiaju_log_order_csos')->add($data);
    }

    /**
     *  新增状态变化记录表
     * @param $data
     * @return mixed
     */
    public function addOrderStatus($data)
    {
        return M('jiaju_orders_status_change')->add($data);
    }

    /**
     * 新增订单客服操作统计日志
     * @param $data
     * @return mixed
     */
    public function addJiajuSwitchStatusLog($data)
    {
        return M('jiaju_log_order_switchstatus')->add($data);
    }
}