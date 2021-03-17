<?php

namespace Home\Model\Logic;

class ActivityLogicModel
{
    public function getActivityInfo($source)
    {
        return D("Home/Activity")->getActivityInfoBySource($source);
    }
}
