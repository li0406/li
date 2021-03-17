<?php

namespace Common\Model\Db;

use Think\Model;

class LogUserOrderChangeModel extends Model
{
    protected $tableName = 'log_user_order_change';

    public function saveOrderChange($save)
    {
        return $this->add($save);
    }
}