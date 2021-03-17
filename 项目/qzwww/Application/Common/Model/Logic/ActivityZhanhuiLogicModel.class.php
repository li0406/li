<?php

namespace Common\Model\Logic;

class ActivityZhanhuiLogicModel
{
    public function findByTel($tel)
    {
        return D("Common/Db/ActivityZhanhui")->findByTel($tel);
    }

    public function addInfo($data)
    {
        return D("Common/Db/ActivityZhanhui")->addInfo($data);
    }
}
