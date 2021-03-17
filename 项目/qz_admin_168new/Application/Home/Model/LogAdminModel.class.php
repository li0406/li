<?php
/**
 * 用户日志
 */
namespace Home\Model;
use Think\Model;
class LogAdminModel extends Model{
    protected $autoCheckFields = false;
    /**
     * [addLog description]
     * @param [type] $info [array] array(
     *                     					'action_id' => '操作对象ID',
     *                     					'remark' => '备注',
     *                     					'logtype' => '日志类型，记录炒作的数据',
     *                     					'info' => '操作的数据的数组'
     *                     				)
     */
    public function addLog($info){
    	$info['info'] = json_encode($info['info']);
    	$admin = getAdminUser();
        import('Library.Org.Util.App');
        $app = new \App();
        $ip =  $app->get_client_ip();
    	$extra = array(
                        'time' => date("Y-m-d H:i:s"),
                        'username' => $admin['name'],
                        'userid' => $admin['id'],
                        'action' => CONTROLLER_NAME.'/'.ACTION_NAME,
                        'ip' => $ip,
                        'user_agent' => $_SERVER["HTTP_USER_AGENT"],
                    );
    	$data = array_merge($info,$extra);
        return M('log_admin')->add($data);
    }

    public function addTypeLog($type = '', $info = '')
    {
        $info['info'] = json_encode($info);
        $admin = getAdminUser();
        import('Library.Org.Util.App');
        $app = new \App();
        $ip = $app->get_client_ip();
        $extra = array(
            'logtype' => $type,
            'time' => date("Y-m-d H:i:s"),
            'username' => $admin['name'],
            'userid' => $admin['id'],
            'action' => CONTROLLER_NAME . '/' . ACTION_NAME,
            'ip' => $ip,
            'user_agent' => $_SERVER["HTTP_USER_AGENT"],
        );
        if($info){
            $data = array_merge($info, $extra);
        }else{
            $data = $extra;
        }
        return M('log_admin')->add($data);
    }

    /**
     * 批量添加日志
     * @param [type] $data [description]
     */
    public function addAllLog($data)
    {
        return M('log_admin')->addAll($data);
    }

    /**
     * [getLogListsById 用于AJAX获取日志列表]
     * @param  [type]  $id      [操作对象ID]
     * @param  [type]  $logtype [日志类型]
     * @param  integer $limit   [获取数量]
     * @return [type]           [description]
     */
    public function getLogListsById($id,$logtype,$limit = 20){
        return M('log_admin')->where(array('action_id' => $id,'logtype'=>$logtype))
                             ->order('id DESC')
                             ->limit($limit)
                             ->select();
    }

    /**
     * 根据类型获取日志
     * @param  [type]  $logtype [日志类型]
     * @return [type]           [description]
     */
    public function getLogListByLogType($logtype,$starttime,$endtime)
    {
        $map = array(
            "logtype" => array("EQ",$logtype),
            "time" => array(
                array("EGT",$starttime),
                array("ELT",$endtime)
            )
        );
        return M('log_admin')->where($map)->field("time,username,remark")->order("id desc")->select();
    }
}