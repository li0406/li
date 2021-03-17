<?php

namespace Home\Model\Db;

use Think\Model;

class UserCompanyEmployeeModel extends Model {

    public function getCompanyMenuLinkEmployeeList($company_id, $link){

        $map = array(
            "a.company_id" => array("EQ", $company_id),
            "d.link" => array("EQ", $link)
        );

        return $this->alias("a")->where($map)
            ->join("inner join qz_user_company_employee_role_map as b on b.employee_id = a.id and b.company_id = a.company_id")
            ->join("inner join qz_user_company_role_menu_map as c on c.role_id = b.role_id")
            ->join("inner join qz_user_company_menu as d on d.menu_node_id = c.menu_id")
            ->field(["a.id as employee_id", "a.company_id"])
            ->group("a.id")
            ->select();
    }

}