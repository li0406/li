<?php

namespace app\common\model\db;

use think\Model;
class LogOrderCheckSheet extends Model
{
    public function addLog($data)
    {
        return $this->insert($data);
    }
}