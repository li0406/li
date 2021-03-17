<?php

namespace Home\Model\Db;

use Think\Model;

class JiajuOrderPoolModel extends Model
{
    protected $tableName = "jiaju_order_pool";

    public function saveOrderPool($save){
        return $this->add($save);
    }
}