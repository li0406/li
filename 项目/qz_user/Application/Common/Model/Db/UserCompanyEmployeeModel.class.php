<?php

namespace Common\Model\Db;

use Think\Model;
/**
 *
 */
class UserCompanyEmployeeModel extends Model
{
    public function getEmployeeInfo($tel)
    {
        $map = [
            "a.state" => ["EQ",1],
            "a.account" => ["EQ",$tel]
        ];
        return $this->where($map)->alias("a")
            ->join("join qz_user u on u.id = a.company_id")
            ->field("a.id as employee_id,account,nick_name,password,company_id,u.name,u.id")->find();
    }

    public function getEmployeeCount($company)
    {
        $map = [
            "company_id" => ["EQ",$company],
        ];
        return $this->where($map)->count();
    }
}