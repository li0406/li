<?php
/**
 *  订单表
 */
namespace Common\Model\Db;
use Think\Model;
class OrdersModel extends Model
{
    protected $tableName = "orders";

    //获取订单信息
    public function getBaojiaOrdersList($field="*",$where,$limit){
       $result = $this->field($field)
           ->where($where)
           ->order('time_real desc')
           ->limit($limit)
           ->select();
        return $result;
    }

    public function saveToutiaoOrderRelation($data)
    {
        return M("jrtt_ad_relation")->add($data);
    }

    public function saveOrderRelation($data)
    {
        return M("wechat_ad_relation")->add($data);
    }
}