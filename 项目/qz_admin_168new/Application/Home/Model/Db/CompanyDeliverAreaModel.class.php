<?php
// +----------------------------------------------------------------------
// | CompanyDeliverAreaMode  装修公司派单县区
// +----------------------------------------------------------------------

namespace Home\Model\Db;

use Think\Model;

class CompanyDeliverAreaModel extends Model
{
    protected $tableName = 'user_company_deliver_area';

    public function getCompanyListByArea($where, $field = 'd.*')
    {
        return $this->alias('d')
            ->field($field)
            ->join('qz_user a on a.id = d.company_id')
            ->join("join qz_user_company b on a.id = b.userid and b.fake = 0")
            ->join("left join qz_company_order_option op on a.id = op.company_id")
            ->where($where)
            ->select();
    }
}

