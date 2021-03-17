<?php

namespace Home\Model\Db;

use Think\Model;

class OrderQueueModel extends Model {

    // 添加队列
    public function addInfo($data){
        return $this->add($data);
    }
}