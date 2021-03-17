<?php


namespace Home\Model\Db;


use Think\Model;

class ScoreReceiveRecordModel extends Model
{
    protected $tableName = "score_receive_record";

    /**
     * 添加积分增加记录
     * @param $adddata
     * @return mixed
     */
    public function createLog($adddata)
    {
        return $this->add($adddata);
    }

}