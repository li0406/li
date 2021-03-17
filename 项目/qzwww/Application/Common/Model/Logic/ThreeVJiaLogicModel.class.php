<?php

namespace Common\Model\Logic;

use Common\Model\Db\ThreeVJiaModel;

class ThreeVJiaLogicModel
{
    public function register($data)
    {
        $model = new ThreeVJiaModel();
        return $model->addInfo($data);
    }
}