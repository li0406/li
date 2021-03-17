<?php

namespace app\common\model\db;

use think\Model;

class UserCompanyDeliverArea extends Model {

    protected $autoWriteTimestamp = false;

    public function deleteByCompanyId($company_id){
        return static::where("company_id", $company_id)->delete();
    }

    public function addRecords($rows){
        return static::insertAll($rows);
    }
}