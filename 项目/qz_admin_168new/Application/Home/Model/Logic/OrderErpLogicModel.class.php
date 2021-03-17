<?php
// +----------------------------------------------------------------------
// | OrderErpLogicModel  添加erp数据
// +----------------------------------------------------------------------

namespace Home\Model\Logic;


class OrderErpLogicModel
{
    public function addOrderErp($info,$companys_yxb)
    {
        $yxb_orders_logic = D("Home/Logic/YxbOrdersLogic");
        return $yxb_orders_logic->setYxbOrder($info,$companys_yxb);
    }
}