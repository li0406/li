<?php


namespace Home\Model\Db;


use Think\Model;

class UcenterBlockModel extends Model
{
    protected $tableName = "ucenter_block";

    //添加黑名单
    public function addblock($data)
    {
        return $this->add($data);
    }

}