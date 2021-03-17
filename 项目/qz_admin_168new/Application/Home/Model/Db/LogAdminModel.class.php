<?php
/**
 * 未量房订单/二次回访订单
 */

namespace Home\Model\Db;

use Think\Model;

class LogAdminModel extends Model
{
    protected $tableName = "log_admin";

    public function addLogAdmin($data)
    {
        return M('log_admin')->add($data);
    }

}