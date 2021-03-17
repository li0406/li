<?php

namespace Common\Model\Db;
use Think\Model;

class LogMainModel extends Model
{
    protected $tableName = 'log_main';

    public function addLog($data)
    {
        return $this->add($data);
    }


}