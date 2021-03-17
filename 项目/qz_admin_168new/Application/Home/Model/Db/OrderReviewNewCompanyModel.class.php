<?php

namespace Home\Model\Db;

use Think\Model;

class OrderReviewNewCompanyModel extends Model
{
    public function addReviewCompanyInfo($save)
    {
        return $this->add($save);
    }

    public function getReviewCompanyInfo($company_id)
    {
        $where = [
            'company_id' => ['eq', $company_id]
        ];
        return $this->where($where)->find();
    }

    public function delReviewCompanyInfo($company_id)
    {
        $where = [
            'company_id' => ['eq', $company_id]
        ];
        return $this->where($where)->delete();
    }
}