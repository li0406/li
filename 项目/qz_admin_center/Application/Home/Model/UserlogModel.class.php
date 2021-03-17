<?php
/**
 * OA系统管理日志
 */
namespace Common\Model;
use Think\Model;
class UserlogModel extends Model{
    protected $connection = 'OA_CONFIG';
    protected $tableName = "user_log";

    public function addLog($data){
        return M("user_log")->add($data);
    }
}