<?php
/**
 *  装修公司查看订单记录日志
 */
namespace Common\Model;
use Think\Model;
class LogorderpassModel extends Model{
    protected $tableName = 'log_order_pass';

    /**
     * 查询装修公司查询订单记录的最后一条被锁记录
     * @param  [type] $comid [公司编号]
     * @return [type]        [description]
     */
    public function getLastLockLog($comid){
        $map = array(
                "company_id"=>array("EQ",$comid)
                     );
        return M("log_order_pass")->where($map)
                                  ->order("act_time desc")
                                  ->find();
    }

    /**
     * 查询该公司上两次的日志记录
     * @param  [type] $comid [公司编号]
     * @return [type]        [description]
     */
    public function getLastTwiceLockStatus($comid){
        $map = array(
                "company_id"=>array("EQ",$comid)
                     );
        $buildSql = M("log_order_pass")->where($map)
                                  ->order("act_time desc")
                                  ->limit(2)
                                  ->buildSql();
        return M("log_order_pass")->table($buildSql)->alias("t")
                                  ->field("count(IF(act_status = 'failed' and is_lock = 0,1,null)) as lockcount")
                                  ->select();
    }

    /**
     * 保存日志
     * @return [type] [description]
     */
    public function saveLog($data){
        return M("log_order_pass")->add($data);
    }
}