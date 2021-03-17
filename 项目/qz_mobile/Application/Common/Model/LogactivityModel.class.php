<?php
/**
 *  活动日志
 */
namespace Common\Model;
use Think\Model;
class LogactivityModel extends Model{
     protected $tableName = "log_activity";

    /**
     * 添加用户
     */
    public function addLog($data){
        return M("log_activity")->add($data);
    }

    /**
     * 查询参与活动的次数
     * @return [type] [description]
     */
    public function getActivityNowCount($tel){
        $begin = mktime(0,0,0,date("m"),date("d"),date("Y"));
        $end = mktime(23,59,59,date("m"),date("d"),date("Y"));
        $map = array(
                "time"=>array("BETWEEN",array($begin,$end)),
                "tel"=>array("EQ",$tel)
                     );
        return M("log_activity")->where($map)->count();
    }

    /**
     * 获取中奖用户名单
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    public function getPrizeUserList($type,$limit = 10){
        $map = array(
                "status"=>array("EQ",1),
                "type"=>array("EQ",$type)
                     );
        return M("log_activity")->where($map)->order("id desc")->limit($limit)->select();

    }
}