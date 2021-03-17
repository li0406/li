<?php
// +----------------------------------------------------------------------
// | OrderPool ¶©µ¥³Ø
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Db;
use think\Model;
use think\db\Query;
use think\db\Where;

class OrderCsosNew extends Model {

    protected $autoWriteTimestamp = false;

    public function findByOrderId($order_id){
        $map = new Query();
        $map->where("order_id", $order_id);

        return $this->where($map)->find();
    }


}