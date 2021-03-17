<?php

namespace Common\Model\Db;

use Think\Model;

class UserCompanySignbackModel extends Model{
    
    protected $tableName ="user_company_signback";

    public function addSignbackInfo($data){
        $data["created_at"] = time();
        $data["updated_at"] = time();
        return M("user_company_signback")->add($data);
    }
}