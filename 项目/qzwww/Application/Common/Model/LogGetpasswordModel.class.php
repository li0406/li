<?php
/**
 * 取回密码日志表 qz_log_getpassword
 */
namespace Common\Model;
use Think\Model;
class LogGetpasswordModel extends  Model{
    /**
     * 添加日志
     * @param [type] $data [添加数据]
     */
    public function addLog($data){
        return M("log_getpassword")->add($data);
    }
}