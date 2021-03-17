<?php
// +----------------------------------------------------------------------
// | OrderPool ¶©µ¥³Ø
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Db;
use think\Model;
use think\db\Query;
use think\db\Where;

class OrderPool extends Model {

    protected $autoWriteTimestamp = false;

    public function getOrderPoolInfo($where = [],$field = '*'){
        return  $this->field($field)->where(new Where($where))->find();
    }

    public function findByOrderId($orderid){
        $map = new Query();
        $map->where("orderid", $orderid);

        return $this->where($map)->find();
    }


}