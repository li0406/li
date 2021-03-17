<?php

namespace Home\Model\Db;

use Think\Model;

class OrderBackRecordImgModel extends Model
{
    public function addImg($save)
    {
        return $this->addAll($save);
    }
}