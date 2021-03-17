<?php
/**
 * 用户日志
 */
namespace Home\Model;
use Think\Model;
class LogAdminEditorModel extends Model{
    protected $autoCheckFields = false;
    /**
     * [addLogAdminEditor 新增编辑人员操作记录]
     * @param [type] $module    [description]
     * @param [type] $action    [description]
     * @param [type] $action_id [description]
     */
    public function addLogAdminEditor($module, $action, $action_id){
        if(empty($module) || empty($action) || empty($action_id)){
            return false;
        }
        $admin = getAdminUser();
        $data = array(
                        'time' => date("Y-m-d H:i:s"),
                        'module' => $module,
                        'action' => $action,
                        'user_id' => $admin['id'],
                        'action_id' => $action_id
                    );
        return M('log_admin_editor')->add($data);
    }

    /**
     * [getLogAdminEditorList 获取日志]
     * @param  [type]  $id      [操作对象ID]
     * @param  [type]  $logtype [日志类型]
     * @param  integer $limit   [获取数量]
     * @return [type]           [description]
     */
    public function getLogAdminEditorList($map,$limit = 20){
        if(empty($map)){
            $map = array('id'=>array('GT',0));
        }
        return M('log_admin_editor')->where($map)
                                    ->order('id DESC')
                                    ->limit($limit)
                                    ->select();
    }
}