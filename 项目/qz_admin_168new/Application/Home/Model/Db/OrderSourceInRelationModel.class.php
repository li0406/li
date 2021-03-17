<?php

namespace Home\Model\Db;

use Think\Model;

class  OrderSourceInRelationModel extends Model
{
    public function addSourceInRelation($save)
    {
        return $this->add($save);
    }

    public function delSourceInRelation($where)
    {
        return $this->where($where)->delete();
    }

}