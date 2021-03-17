<?php
/**
 * 邮件发送日志
 */
namespace Common\Model;
use Think\Model;
class LogEmailUserSendModel extends Model{
     protected $tableName = "log_email_user_send";
     public function addLog($data){
        return M("log_email_user_send")->add($data);
    }
}