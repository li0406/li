<?php


namespace Home\Model\Logic;



use Home\Model\Db\ScoreReceiveRecordModel;

class ScoreReceiveRecordLogicModel
{

    public function addScoreReceiveRecord($uuid, $type, $score, $task_id)
    {
        $scorereceiverecordmodel = new ScoreReceiveRecordModel();
        $nowtime = time();
        $adddata = array(
            "type" => $type,
            "uuid" => $uuid,
            "score" => $score,
            "created_at" => $nowtime,
            "updated_at" => $nowtime
        );
        if ($type == 3) {       //3表示完成积分任务  需要保存任务id
            $adddata['task_id'] = $task_id;
        }

        $result = $scorereceiverecordmodel->createLog($adddata);
        if ($result !== false) {
            return true;
        } else {
            return false;
        }
    }

}