<?php
/**
 *  用户系统消息日志
 */
namespace Common\Model;
use Think\Model;
class LogusersystemnoticeModel extends Model{
    protected $tableName ="log_user_system_notice";
    /**
     * 添加日志
     */
    public function addLog($data){
        return M("log_user_system_notice")->add($data);
    }
}