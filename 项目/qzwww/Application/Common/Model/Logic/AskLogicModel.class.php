<?php
namespace Common\Model\Logic;

use Common\Model\AskModel;

class AskLogicModel
{
    public function getHotAskList($type = 1){
        $askDb = new AskModel();
        return $askDb->getHomeAskList($type);
    }
}