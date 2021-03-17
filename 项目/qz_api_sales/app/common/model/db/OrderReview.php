<?php
// +----------------------------------------------------------------------
// | OrderReview 客服回访订单
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Db;
use think\Model;
use think\db\Query;

class OrderReview extends Model {

    public function findByOrderId($order_id){
        $map = new Query();
        $map->where("order_id", $order_id);

        return $this->where($map)->find();
    }

    public function addData($data){
        return $this->insert($data);
    }

    public function saveData($id, $data){
        return $this->where("id", $id)->update($data);
    }

    public function getReviewInfoByOrderId($field = '*', $where)
    {
        return $this->field($field)->where($where)->find();
    }
}