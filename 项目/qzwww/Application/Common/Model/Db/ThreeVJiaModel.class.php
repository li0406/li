<?php

namespace Common\Model\Db;
use Think\Model;

class ThreeVJiaModel extends Model
{
    protected $tableName = 'threevjia_user_register';

    public function addInfo($data)
    {
        return $this->add($data);
    }
}