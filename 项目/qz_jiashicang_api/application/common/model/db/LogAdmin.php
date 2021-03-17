<?php
// +----------------------------------------------------------------------
// | LogAdmin
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Db;
use think\Model;

class LogAdmin extends Model {

    public function addActionLog($save){
        return $this->insertGetId($save);
    }
}