<?php

namespace app\common\model\db;

use think\Db;
use think\Model;
use think\db\Query;

class UserCompanyExpend extends Model {

    protected $autoWriteTimestamp = false;

    public function addQueueInfo($data){
        Db::name("user_company_expend_queue")->insert($data);
    }

}