<?php

/**
 * 分站标识表
 */

namespace app\common\model\db;

use think\Db;
use think\Model;

class SubTag extends Model {

    protected $autoWriteTimestamp = false;

    public function getTagListByName($name)
    {
        $map[] = [
            ['name', 'in', $name]
        ];
        return $this->field('id,name')->where($map)->select();
    }

    public function addSubTag($save){
        return $this->saveAll($save);
    }
}