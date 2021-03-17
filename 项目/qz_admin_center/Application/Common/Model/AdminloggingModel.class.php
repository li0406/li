<?php
/**
 * 后台登录日志
 */
namespace Common\Model;
use Think\Model;
class AdminloggingModel extends Model{
    protected $tableName = "admin_logging";

    /**
     * 添加日志
     */
    public function addLog($data){
        return M("admin_logging")->add($data);
    }

    /**
     * 获取错误的登录次数
     * @return [type] [description]
     */
    public function getFailLogin($name){
        $map = array(
                "username"=>array("EQ",$name)
                     );
        $buildSql = M("admin_logging")->where($map)->order("id desc")->limit(2)->buildSql();
        return  M("admin_logging")->table($buildSql)->alias("t")
                                  ->field("count(if(t.status <> 1,1,null))  as count ")
                                  ->find();
    }

    /**
     * 获取登录日志
     * @param  [type] $username   [用户编号]
     * @param  [type] $start [开始时间]
     * @param  [type] $end   [结束时间]
     * @return [type]        [description]
     */
    public function getLogList($username,$start,$end){
        $map = array(
            "username"=>array("EQ",$username),
            "time"=>array("BETWEEN",array($start,$end))
                     );
        return M("admin_logging")->where($map)->order("id desc")->select();
    }

    /**
     * 获取最近一周的登录日志
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public function getLastLogWeekList($name){
        $map = array(
            "username"=>array("EQ",$name),
            "time"=>array("EGT",strtotime("-7 day",time()))
                     );
        return  M("admin_logging")->where($map)->order("id desc")->select();
    }
}