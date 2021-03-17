<?php

namespace Common\Model\Logic;

use Common\Model\Db\CompanyConsultModel;

class CompanyConsultLogicModel
{
    public function addInfo($data)
    {
        $model = new  CompanyConsultModel();
        return $model->addInfo($data);
    }
}