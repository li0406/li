<?php

namespace app\common\model\db;

use think\Db;
use think\Model;

class UserCompanyRoundOrderAmount extends Model
{
    public function getRoundOrderAmountInfo($company_id)
    {
        return $this->alias('a')
            ->field('t.type gz_type,t.price gz_price,t1.type jg_type,t1.price jg_price,t2.type mjmin_type,t2.mianji mjmin_mianji,t2.price mjmin_price,t3.type mjmax_type,t3.mianji mjmax_mianji,t3.price mjmax_price')
            ->join('user_company_round_order_amount t', 't.company_id = a.company_id and t.type = 1', 'left')
            ->join('user_company_round_order_amount t1', 't1.company_id = a.company_id and t1.type = 2', 'left')
            ->join('user_company_round_order_amount t2', 't2.company_id = a.company_id and t2.type = 3', 'left')
            ->join('user_company_round_order_amount t3', 't3.company_id = a.company_id and t3.type = 4', 'left')
            ->where([['a.company_id', '=', $company_id]])
            ->group('a.company_id')
            ->find();
    }

    public function delRoundOrderAmountInfo($company_id)
    {
        return $this->where([['company_id', '=', $company_id]])->delete();
    }

    public function addRoundOrderAmount($save)
    {
        return $this->saveAll($save);
    }

}