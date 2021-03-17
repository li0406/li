<?php


namespace Home\Model\Logic;


use Home\Model\Db\ScoreLevelModel;

class ScoreLevelLogicModel
{
    public function getLevelInfo()
    {
        $scorelevelmodel = new ScoreLevelModel();
        $list = $scorelevelmodel->getLevelInfo();
        return $list ? $list : [];
    }

}