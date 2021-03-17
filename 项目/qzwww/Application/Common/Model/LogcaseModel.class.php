<?php
/**
 *  案例irzhi
 */
namespace Common\Model;
use Think\Model;
class LogcaseModel extends Model{
    protected $tableName = "log_case";

    /**
     * 添加日志
     * @param [type] $data [添加数据]
     */
    public function addLog($data){
        return M("log_case")->add($data);
    }
}