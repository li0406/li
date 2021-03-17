<?php

/***
 * 订单导入任务
 */

namespace Home\Model;

use Think\Model;

class OrderImportTaskModel extends Model
{
    protected $autoCheckFields = false;

    //执行状态
    public $execute_status = [
        0 => '请选择',
        1 => '标识未设置',
        2 => '待执行',
        3 => '立即执行',
        4 => '执行中',
        5 => '执行完成'
    ];

    /***
     *
     * 增加任务数据
     *
     * @param $data
     * @return mixed
     */
    public function addTask($data)
    {
        return M('order_import_task')->add($data);
    }


    /**
     * 通过task_id 删除本任务 （禁用）
     * @param $task_id
     * @return mixed
     */
    public function disableTask($task_id)
    {
        $data = [];
        $data['status'] = 1;
        $map['id']  = ['EQ',$task_id];

        return M('order_import_task')->where($map)->save($data);
    }

    /**
     * 通过task_id 设置渠道来源src
     * @param $task_id
     * @param $src
     * @return mixed
     */
    public function setTaskSrc($task_id, $src)
    {
        $data = [];
        $data['src'] = $src;
        $data['execute_status'] = 2; //待执行
        $map['id']  = ['EQ',$task_id];
        return M('order_import_task')->where($map)->save($data);
    }

    /**
     * 通过task_id 设置多种标识信息
     * @param $task_id
     * @param $src
     * @param $source
     * @return mixed
     */
    public function setTaskMark($task_id, $src ,$source=0)
    {
        $data = [];
        $data['src'] = $src;
        $data['source'] = $source;
        $data['execute_status'] = 2; //待执行
        $map['id']  = ['EQ',$task_id];
        return M('order_import_task')->where($map)->save($data);
    }

    /**
     * 通过task_id 设置任务可执行
     * @param $task_id
     * @return mixed
     */
    public function setTaskDo($task_id)
    {
        $data = [];
        $data['execute_status'] = 3; //执行中
        $map['id']  = ['EQ',$task_id];
        return M('order_import_task')->where($map)->save($data);
    }


    /**
     * 通过task_id查询任务
     * @param $task_id
     * @return mixed
     */
    public function getTaskBytaskId($task_id)
    {
        return M('order_import_task')->where(['id'=>['EQ',$task_id]])->find();
    }


    /*----分页和搜索----*/
    /**
     * 分页计算数量
     * @param $param
     * @return mixed
     */
    public function getOrderImportTaskCount($param)
    {
        $db = self::getOrderImportTaskObj($param);
        return $db->count();
    }

    /**
     * 分页数据
     * @param $param
     * @return mixed
     */
    public function getOrderImportTask($param)
    {
        $db = self::getOrderImportTaskObj($param);
        return $db->limit($param['limit']['start'], $param['limit']['end'])
                  ->order('id DESC')->select();
    }

    /**
     * 带参数的翻页对象
     * @param $param
     * @return Model
     */
    public function getOrderImportTaskObj($param)
    {
        $admin = getAdminUser();

        $map = [];

        if (!empty($param['op_name'])) {
            $map['op_name'] = ['LIKE', $param['op_name'] . '%'];
        }

        if (!empty($param['execute_status'])) {
            $map['execute_status'] = ['EQ', $param['execute_status']];
        }

        if (!empty($param['execute_start_time'])) {
            $exp = explode('-', $param['execute_start_time']);
            $start_day = trim($exp[0]);
            $end_day = trim($exp[1]);
            $current_start = strtotime($start_day);
            $current_end = strtotime($end_day . ' 23:59:59');
            $map['execute_start_time'] = ['BETWEEN', [$current_start, $current_end]];
        }

        if (!empty($param['execute_end_time'])) {
            $exp = explode('-', $param['execute_end_time']);
            $start_day = trim($exp[0]);
            $end_day = trim($exp[1]);
            $current_start = strtotime($start_day);
            $current_end = strtotime($end_day . ' 23:59:59');
            $map['execute_end_time'] = ['BETWEEN', [$current_start, $current_end]];
        }

        /*---权限控制---*/
        //产品经理，运营总监可以看到所有导入的记录
        //推广一部主管仅能看到其下属操作的所有导入记录
        //推广二部主管仅能看到其下属操作的所有导入记录
        //其余人员仅能看到自己操作的所有导入记录

        //默认只能看自己
        $map['op_id'] = ['EQ', $admin['id']];

        //看所有
        $roleLookAll = false;
        if (in_array($admin['uid'], [1,51,68]) ) $roleLookAll = true; //51产品经理 68运营总监
        if ($roleLookAll) {
            unset($map['op_id']);
        }

        //看自己管辖 目前不支持
        /*$roleLookPrecinct  = false;
        if (in_array($admin['uid'], [70,75]) ) $roleLookPrecinct = true; //70流量部门主管（推广二部） 75推广主管（推广一部）
        if ($roleLookPrecinct) {
            $precinctListId = '';
            $map['op_id'] = ['IN', $precinctListId];
        }*/


        $map['status'] = 0; //未删除

        $db = M('order_import_task');
        return $db->where($map);
    }

}