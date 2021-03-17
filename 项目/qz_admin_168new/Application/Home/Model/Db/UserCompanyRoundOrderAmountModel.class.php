<?php

namespace Home\Model\Db;

use Think\Model;

class UserCompanyRoundOrderAmountModel extends Model
{
    public function getAmountByCompany($company_id)
    {
        if (count($company_id) == 0) {
            return [];
        }
        return $this->alias('a')
            ->field('a.company_id,t.price gz_price,t1.price jg_price,t2.mianji mjmin_mianji,t2.price mjmin_price,t3.mianji mjmax_mianji,t3.price mjmax_price')
            ->join('left join qz_user_company_round_order_amount t on t.company_id = a.company_id and t.type = 1')
            ->join('left join qz_user_company_round_order_amount t1 on t1.company_id = a.company_id and t1.type = 2')
            ->join('left join qz_user_company_round_order_amount t2 on t2.company_id = a.company_id and t2.type = 3')
            ->join('left join qz_user_company_round_order_amount t3 on t3.company_id = a.company_id and t3.type = 4')
            ->where(['a.company_id' => ['in', $company_id]])
            ->group('a.company_id')
            ->select();
    }

    public function getMaxAmountByCompany($company_id)
    {
        $map = [
            "company_id" => ["IN",$company_id]
        ];
        return $this->where($map)->field("company_id,max(price) as price")->group("company_id")->select();
    }
}