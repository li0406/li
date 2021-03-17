<?php

namespace app\common\model\db;

use think\Db;
use think\Model;
use think\db\Query;

class UserCompanySignback extends Model {

    public function addSignbackInfo($data){
        $data["created_at"] = time();
        $data["updated_at"] = time();
        return $this->insert($data);
    }

}