<?php

namespace Common\Model\Db;

use Think\Model;

class UserCompanyAccountLogModel extends Model
{

    protected $autoCheckFields = false;

    public function getStatisticsCashDepositByCom($company_id)
    {
        if (count($company_id) == 0) {
            return [];
        }

        $where = [
            'user_id' => ['in', $company_id],
            'trade_type' => ['eq', 11]
        ];
        return $this
            ->field('sum(trade_amount) cash_deposit,user_id')
            ->where($where)
            ->group('user_id')
            ->select();
    }

    public function getCashDepositInfoByCom($company_id)
    {
        if (empty($company_id)) {
            return [];
        }

        $where = [
            'user_id' => ['eq', $company_id],
            'trade_type' => ['eq', 11]
        ];
        return $this
            ->field('sum(trade_amount) cash_deposit,user_id')
            ->where($where)
            ->find();
    }
}