<?php
/**
 * Created by PhpStorm.
 * author: mcj
 * Date: 2018/8/22
 * Time: 13:16
 */

namespace Home\Model\Db;

use Think\Model;

class YxbOrdersManageModel extends Model
{
    protected $tableName = "yxb_orders_manage";

    public function addOrders($data)
    {
        return M('yxb_orders_manage')->addAll($data);
    }
	public function addReceptionLog($data)
	{
		return M('yxb_reception')->addAll($data);
	}
}