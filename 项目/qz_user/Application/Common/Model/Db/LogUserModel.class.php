<?php
/**
 * 用户日志表 log_user
 */

namespace Common\Model\Db;

use Think\Model;

class LogUserModel extends Model
{
    protected $tableName = "log_user";

    public function getLogList($map,$limit = 10)
    {
        return$this->where($map)->limit($limit)->order('time desc')->select();
    }
}