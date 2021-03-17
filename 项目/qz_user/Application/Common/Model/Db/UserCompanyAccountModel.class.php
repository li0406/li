<?php

namespace Common\Model\Db;

use Think\Model;

class UserCompanyAccountModel extends Model{
    
    protected $tableName ="user_company_account";

    public function addAccountInfo($data){
        $data["created_at"] = time();
        $data["updated_at"] = time();
        return M("user_company_account")->add($data);
    }
}