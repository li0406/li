<?php
namespace Home\Model\Logic;

class ActivityZhanhuiLogicModel
{
    public function getActivityList()
    {
        return D("Home/Db/ActivityZhanhui")->getActivityList();
    }
}
