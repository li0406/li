<?php
// +----------------------------------------------------------------------
// | OrderStats  订单回访记录表
// +----------------------------------------------------------------------

namespace app\common\model\db;

use think\Model;
use think\db\Query;

class OrderStats extends Model {

    // 获取订单统计数据
    public function getOrderStatsInfo($order_id){
        $map = new Query();
        $map->where("order_id", $order_id);

        return $this->where($map)->find();
    }

    // 更新订单统计数据
    public function updateInfo($order_id, $data){
        $map = new Query();
        $map->where("order_id", $order_id);

        return $this->where($map)->update($data);
    }

    // 新增订单统计数据
    public function addInfo($data){
        return $this->insert($data);
    }

}