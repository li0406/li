<?php

namespace Common\Model\Logic;
use Common\Model\Db\UserCompanyEmployeeModel;

/**
 *
 */
class UserCompanyLogic
{
    public function getEmployeeInfo($tel)
    {
        $model = new UserCompanyEmployeeModel();
        return $model->getEmployeeInfo($tel);

    }
}