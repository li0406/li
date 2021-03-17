<?php


namespace Home\Model\Db;


use Think\Model;

class ScoreLogTaskModel extends Model
{
    protected $tableName = "score_log_task";

    /**
     * 根据条件获取任务完成次数
     * @param $map   查询条件
     * @return mixed
     */
    public function taskdonetimes($map)
    {
        return $this->where($map)->field('task_id,count(task_id) as counts')->group('task_id')->select();
    }


    /**
     * 根据条件获取任务是否完成过
     * @param $map
     * @return mixed
     */
    public function checkdonetask($map)
    {
        return $this->where($map)->find();
    }


    /**
     * 添加积分日志
     * @param int $task_id 任务id
     * @param $uuid         用户uuid
     * @return int|string
     */
    public function addTaskLog($task_id, $uuid, $to_id = 0, $to_type = 0)
    {
        $adddata = [];
        $adddata['task_id'] = $task_id;
        $adddata['uuid'] = $uuid;
        $adddata['to_id'] = $to_id ? $to_id : 0;
        $adddata['to_type'] = $to_type ? $to_type : 0;
        $adddata['created_at'] = time();
        return $this->add($adddata);

    }

}