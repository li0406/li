<?php


namespace Home\Model\Db;
use Think\Model;
class YgjNoticeModel extends Model
{
    public function addAllInfo($data)
    {
        return $this->addAll($data);
    }

    public function addRecord($data) {
        return $this->add($data);
    }
}