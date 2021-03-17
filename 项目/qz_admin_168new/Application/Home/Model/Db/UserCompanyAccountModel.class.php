<?php

namespace Home\Model\Db;

use Think\Model;

class UserCompanyAccountModel extends Model {

    public function getAccountInfo($user_id){
        $map = array(
            "user_id" => array("EQ", $user_id)
        );

        return $this->where($map)->find();
    }

    public function saveData($user_id, $data){
        $map = array(
            "user_id" => array("EQ", $user_id)
        );
        return $this->where($map)->save($data);
    }

    public function addInfo($data){
        return $this->add($data);
    }

    // 账户余额充值
    public function setAccountInc($user_id, $account_amount){
        $map = array(
            "user_id" => array("EQ", $user_id)
        );

        return $this->where($map)->setInc("account_amount", $account_amount);
    }

    // 保证金余额充值
    public function setDepositInc($user_id, $deposit_money){
        $map = array(
            "user_id" => array("EQ", $user_id)
        );

        return $this->where($map)->setInc("deposit_money", $deposit_money);
    }

    // 账户余额扣除
    public function setAccountDec($user_id, $account_amount){
        $map = array(
            "user_id" => array("EQ", $user_id)
        );

        return $this->where($map)->setDec("account_amount", $account_amount);
    }

    // 保证金余额扣除
    public function setDepositDec($user_id, $deposit_money){
        $map = array(
            "user_id" => array("EQ", $user_id)
        );

        return $this->where($map)->setDec("deposit_money", $deposit_money);
    }

    // 账户余额充值
    public function setRoundOrderNumberInc($user_id, $number = 1){
        $map = array(
            "user_id" => array("EQ", $user_id)
        );

        return $this->where($map)->setInc("round_order_number", $number);
    }

    // 保证金余额充值
    public function setRoundOrderNumberDec($user_id, $number = 1){
        $map = array(
            "user_id" => array("EQ", $user_id)
        );

        return $this->where($map)->setDec("round_order_number", $number);
    }
}