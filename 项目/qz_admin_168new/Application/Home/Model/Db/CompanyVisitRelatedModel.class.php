<?php

namespace Home\Model\Db;
use Think\Model;

class CompanyVisitRelatedModel extends Model
{
    public function addVisitRelatedInfo($data)
    {
        return $this->addAll($data);
    }
}
