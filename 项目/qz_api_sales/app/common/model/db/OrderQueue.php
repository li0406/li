<?php

namespace app\common\model\db;

use think\Db;
use think\Model;

class OrderQueue extends Model {

    // 添加队列
    public function addInfo($data){
        return $this->insertGetId($data);
    }
}