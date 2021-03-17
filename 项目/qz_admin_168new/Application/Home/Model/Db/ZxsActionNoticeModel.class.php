<?php

namespace Home\Model\Db;
use Think\Model;
class ZxsActionNoticeModel extends Model
{
    public function addAllInfo($data)
    {
        return $this->addAll($data);
    }
}