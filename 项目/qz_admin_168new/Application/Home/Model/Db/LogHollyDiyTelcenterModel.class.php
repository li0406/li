<?php

namespace  Home\Model\Db;

use Think\Model;

class LogHollyDiyTelcenterModel extends Model
{
    public function addLog($data)
    {
        return $this->add($data);
    }
}