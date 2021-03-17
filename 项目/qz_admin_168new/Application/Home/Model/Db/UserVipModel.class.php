<?php

namespace Home\Model\Db;

use Think\Model;

class UserVipModel extends Model
{

    protected $autoCheckFields = false;

    public function getCompanyNewestContract($where)
    {
        $buildSql = $this->field('company_id,start_time,end_time')
            ->where($where)
            ->order('start_time')
            ->buildSql();
        return $this->table($buildSql)->alias('t')
            ->group('t.company_id')
            ->select();
    }
}