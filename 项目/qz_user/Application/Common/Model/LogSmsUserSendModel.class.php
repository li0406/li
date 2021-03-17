<?php
/**
 * 短信发送日志表 Log_sms_user_send
 */
namespace Common\Model;
use Think\Model;
class LogSmsUserSendModel extends Model{
    protected $tableName = "log_sms_user_send";
    public function addLog($data){
        return M("log_sms_user_send")->add($data);
    }
}