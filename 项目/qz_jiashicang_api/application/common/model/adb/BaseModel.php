<?php

// +----------------------------------------------------------------------
// | ADB 基础模型
// +----------------------------------------------------------------------

namespace app\common\model\adb;

use think\Db;
use think\Model;
use think\Exception;
use think\facade\Config;

class BaseModel extends Model {

    protected $connection = 'db_config_adb';

    public function link(){
        return Db::connect($this->connection);
    }
}