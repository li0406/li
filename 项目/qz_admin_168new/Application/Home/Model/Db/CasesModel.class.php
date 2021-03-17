<?php

namespace Home\Model\Db;

use Think\Model;

class CasesModel extends Model
{
    public function getCasesByCompany($where, $field = '*')
    {
        return $this->field($field)->where($where)->select();
    }
    public function saveAllCases($save)
    {
        return M()->execute($save);
    }
}