<?php
/**
 *用户操作日志 qz_log_user
 */
namespace Common\Model;
use Think\Model;
class LoguserModel extends Model{
    protected $tableName ="log_user";

    /**
     * 获取用户的日志列表
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function getLogList($uid,$begin,$end){
        $map = array(
                "userid"=>array("EQ",$uid),
                "time"=>array(array("EGT",$begin),array("ELT",$end))
                     );
        return M("log_user")->where($map)->order("id desc")
                            ->select();
    }

    /**
     * 添加日志
     * @param [type] $data [description]
     */
    public function addLog($data){
        return M("log_user")->add($data);
    }
}