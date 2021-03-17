<?php

namespace Home\Model\Db;

use Think\Model;

class UserCompanyExpendModel extends Model{

    protected $autoCheckFields = false;

    // 更新数据信息
    public function updateInfo($company_id, $datetime, $data){
        $map = array(
            "company_id" => array("EQ", $company_id),
            "datetime" => array("EQ", $datetime)
        );
        
        return $this->where($map)->save($data);
    }

    // 删除数据信息
    public function deleteInfo($company_id, $datetime){
        $map = array(
            "company_id" => array("EQ", $company_id),
            "datetime" => array("EQ", $datetime)
        );
        
        return $this->where($map)->delete();
    }
}