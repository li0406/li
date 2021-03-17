<?php

namespace Home\Model;
use Think\Model;

/**
 * VIP用户日志表
 */
class LogVipModel extends Model
{
    /**
     * 添加日志
     * @param [type] $data [description]
     */
    public function addLog($data)
    {
        return $this->add($data);
    }
}