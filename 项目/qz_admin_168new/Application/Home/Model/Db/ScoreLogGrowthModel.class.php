<?php


namespace Home\Model\Db;


use Think\Model;

class ScoreLogGrowthModel extends Model
{
    protected $tableName = "score_log_growth";
    public function addLogGrowth($adddata)
    {
        return $this->add($adddata);
    }

}