<?php
/**
 * 用户日志
 */
namespace Common\Model;
use Think\Model;
class LogadminModel extends Model{
    protected $tableName = "log_admin";

    public function addLog($data){
        return M("log_admin")->add($data);
    }
}