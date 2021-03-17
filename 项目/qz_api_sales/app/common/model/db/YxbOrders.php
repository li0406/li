<?php
// +----------------------------------------------------------------------
// | YxbOrders
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Model;

class YxbOrders extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_yxb_orders';

    public function selectYxbOrder($order_no)
    {
        return $this->where('qz_order', $order_no)->select();
    }

    public function addOrders($data){
        return $this->saveAll($data);
    }
}