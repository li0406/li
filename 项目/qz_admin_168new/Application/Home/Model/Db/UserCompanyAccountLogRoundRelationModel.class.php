<?php

namespace Home\Model\Db;

use Think\Model;

class UserCompanyAccountLogRoundRelationModel extends Model
{
    public function addLogRound($data = [])
    {
        return $this->add($data);
    }
}