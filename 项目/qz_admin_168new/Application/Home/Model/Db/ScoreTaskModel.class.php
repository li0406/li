<?php


namespace Home\Model\Db;


use Think\Model;

class ScoreTaskModel extends Model
{
    protected $tableName = "score_task";
    public function getTaskListByMap($map)
    {
        return $this->where($map)->field("id as task_id,type as task_type,title task_name,button_text,sort,score,times as need_did")->order("sort,id")->select();
    }

}