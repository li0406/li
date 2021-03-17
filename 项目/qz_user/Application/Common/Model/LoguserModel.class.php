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
    public function getLogList($uid,$begin,$end,$status = ''){
        $map = array(
                "userid"=>array("EQ",$uid),
                "time"=>array(array("EGT",$begin),array("ELT",$end))
                     );
        if($status){
            $map['status'] = ['EQ',$status];
        }
        return M("log_user")->where($map)->order("id desc")
                            ->select();
    }

    /**
     * 添加日志
     * @param [type] $data [description]
     */
    public function addLog($data){
        if($_SESSION["u_userInfo"]["state"] == 1){
            return true;
        }
        return M("log_user")->add($data);
    }


    /**
     *获取用户最后的登陆日志
     * @param  [type]  $ip    [description]
     * @param  integer $limit [description]
     * @return [type]         [description]
     */
    public function getLastLoginLog($ip,$limit = 3)
    {
        $map = array(
            "ip"=>array("EQ",$ip),
            "action"=>array("EQ","muser/dologin")
        );
        // M("log_user")->where($map)->limit(3)->order("id desc")->select();
        // $sql = M()->getlastsql();
        // //return M("log_user")->table($sql)->alias("t")->field("count(if(`status` = 1,1,null)) as 'success'")->find();
        // $sql = "select count(if(`status` = 0,1,null)) as 'error' from ($sql) as t limit 1";
        $buildSql = M("log_user")->where($map)->limit($limit)->order("id desc")->buildSql();
        return M("log_user")->table($buildSql)->alias("a")
                            ->field("count(if(`status` = 0,1,null)) as 'error'")
                            ->find();


    }

    /**
     * 获取用户登陆成功的日志列表
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function getLoginSucceedList($uid, $begin, $end, $status = 1, $field, $order, $group, $limit = 3,$action = '')
    {
        $map = array(
            "userid" => array("EQ", $uid),
            "time" => array(array("EGT", $begin), array("ELT", $end)),
        );
        if (!empty($action)){
            $map['action'] = array('EQ',$action);
        }
        if ($status) {
            $map['status'] = ['EQ', $status];
        }
        return M("log_user")->where($map)
            ->field($field)
            ->group($group)
            ->order($order)
            ->limit($limit)
            ->select();
    }

    /**
     *获取用户最近一次登陆成功的登陆日志
     * @param  [type]  $ip    [description]
     * @param  integer $limit [description]
     * @return [type]         [description]
     */
    public function getLastSuccessLoginLog($uid)
    {
        $map = array(
            "userid"=>array("EQ",$uid),
            "action"=>array("EQ","Index/login")
        );
        return M("log_user")->where($map)->order("id desc")->find();
    }

    /**
     * 获取用户最近一次日志
     * @param int $uid 用户ID
     * @param string $action 请求类型
     * @param string $info 备注内容
     * @return mixed
     */
    public function getLastLogWithAction($uid, $action = '',$info = '')
    {
        $map = [
            'userid' => ['EQ', $uid],
        ];
        if (!empty($action)) {
            $map['action'] = array('EQ', $action);
        }
        if (!empty($info)) {
            $map['info'] = array('like', '%' . $info . '%');
        }
        return M("log_user")->where($map)->order("id desc")->find();
    }
}