<?php
// +----------------------------------------------------------------------
// | OrderPondServiceLogicModel  订单池所属客服逻辑层
// +----------------------------------------------------------------------

namespace Home\Model\Logic;


class OrderPondServiceLogicModel
{
    /**
     * 查询已分配客服数量
     */
    public function getKfNum()
    {
        return D('Home/Db/OrderPondService')->getKfNum();
    }

    /**
     * 获取订单池数据
     * @return [type] [description]
     */
    public function getOrderPondCityList()
    {
        //获取订单池城市数据
        $result = D('Home/Db/OrderPond')->getPondCity();

        foreach ($result as $key => $value) {
            $ponds[$value["id"]]["city"][] = $value["city_id"];
        }

        //获取订单池客服数据
        $result = D('Home/Db/OrderPond')->getPondKf();

        foreach ($result as $key => $value) {
            $ponds[$value["id"]]["kf"][] = array(
                "id" => $value["kf_id"],
                "name" => $value["name"]
            );
        }

        return $ponds;
    }

    /**
     * 分配订单
     * @param [type] $order_id [description]
     * @param [type] $cs       [description]
     * @param [type] $ponds    [description]
     */
    public function setToOrder($order_id, $cs, $ponds)
    {
        //根据订单城市优先配给订单池城市客服
        foreach ($ponds as $key => $value) {
            foreach ($value["city"] as $k => $val) {
                if ($val == $cs) {
                    $kfList = $value["kf"];
                    break;
                }
            }

            if (count($kfList) > 0) {
                break;
            }
        }

        //随机分配给订单池客服
        if (count($kfList) > 0) {
            //打乱客服
            shuffle($kfList);
            $info = array_shift($kfList);

            //订单插入订单池
            $data = [
                "orderid" => $order_id,
                "time" => time(),
                "type" => 3,
                "status" => 0,
                "op_uid" => $info["id"],
                "op_name" => $info["name"],
                "addtime" => time()
            ];
            D("OrderPool")->addOrder($data);
        }
        return false;
    }
}