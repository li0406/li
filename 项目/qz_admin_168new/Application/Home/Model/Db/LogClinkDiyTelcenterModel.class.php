<?php

namespace Home\Model\Db;

use Think\Model;
class LogClinkDiyTelcenterModel extends Model
{
    public function addInfo($data)
    {
        return $this->add($data);
    }

}