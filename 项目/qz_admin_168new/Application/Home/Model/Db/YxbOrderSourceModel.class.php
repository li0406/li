<?php
/**
 * Created by PhpStorm.
 * author: mcj
 * Date: 2018/8/22
 * Time: 13:16
 */

namespace Home\Model\Db;

use Think\Model;

class YxbOrderSourceModel extends Model
{
    protected $tableName = "yxb_order_source";

    public function getOrderSource($data)
    {
        return M('yxb_order_source')->where("company_id = '".$data."' and default_rule = '1'")->getField('id');
    }
}