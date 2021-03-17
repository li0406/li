<?php
/**
 * Created by PhpStorm.
 * author: mcj
 * Date: 2018/8/22
 * Time: 13:16
 */

namespace Home\Model\Db;

use Think\Model;

class YxbOrdersModel extends Model
{
    protected $tableName = "yxb_orders";

    public function selectYxbOrder($order_no){
        return M('yxb_orders')->where(['qz_order'=>$order_no])->select();
    }
    public function addOrders($data)
    {
        return M('yxb_orders')->addAll($data);
    }
}