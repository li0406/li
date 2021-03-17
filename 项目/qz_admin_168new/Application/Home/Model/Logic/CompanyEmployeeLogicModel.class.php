<?php

namespace Home\Model\Logic;

use Home\Model\Db\UserCompanyEmployeeModel;

class CompanyEmployeeLogicModel {

    protected $employeeModel;

    public function __construct(){
        $this->employeeModel = new UserCompanyEmployeeModel();
    }

    // 获取装修公司具有某个菜单权限的员工
    public function getCompanyMenuLinkEmployees($company_id, $link){
        return $this->employeeModel->getCompanyMenuLinkEmployeeList($company_id, $link);
    }

}