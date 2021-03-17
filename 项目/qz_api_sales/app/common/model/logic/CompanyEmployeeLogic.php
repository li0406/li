<?php

namespace app\common\model\logic;

use app\common\model\db\User;
use app\common\model\db\UserCompanyEmployee;

class CompanyEmployeeLogic {

    protected $companyEmployee;

    public function __construct(){
        $this->companyEmployee = new UserCompanyEmployee();
    }

    // 公司有订单权限的员工
    public function getOrderCompanyEmployees($company_id, $order_id){
        $adminList = $this->companyEmployee->getCompanyAdminList($company_id);
        $employeeList = $this->companyEmployee->getCompanyOrderEmployeeList($company_id, $order_id);

        $companyEmployees = [];
        foreach ($adminList as $key => $item) {
            $company_id = $item["company_id"];
            $employee_id = $item["employee_id"];
            $companyEmployees[$company_id][$employee_id] = $item->toArray();
        }

        foreach ($employeeList as $key => $item) {
            $company_id = $item["company_id"];
            $employee_id = $item["employee_id"];
            $companyEmployees[$company_id][$employee_id] = $item->toArray();
        }

        return $companyEmployees;
    }
}