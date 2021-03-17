<?php

namespace app\common\model\db;

use think\Model;
use think\db\Query;

class UserCompanyEmployee extends Model {

    // 获取公司下的管理员
    public function getCompanyAdminList($company_id){
        $map = new Query();
        $map->where("m.role_id", 1);
        if (is_array($company_id)) {
            $map->where("a.company_id", "in", $company_id);
        } else {
            $map->where("a.company_id", $company_id);
        }

        return $this->alias("a")->where($map)
            ->join("user_company_employee_role_map m", "m.employee_id = a.id and m.company_id = a.company_id")
            ->join("user_company_role b", "b.id = m.role_id")
            ->field(["a.company_id", "m.employee_id", "m.role_id"])
            ->select();
    }

    // 公司订单分配员工
    public function getCompanyOrderEmployeeList($company_id, $order_id){
        $map = new Query();
        $map->where("m.order_id", $order_id);
        if (is_array($company_id)) {
            $map->where("a.company_id", "in", $company_id);
        } else {
            $map->where("a.company_id", $company_id);
        }

        return $this->alias("a")->where($map)
            ->join("user_company_employee_order_map m", "m.employee_id = a.id and m.company_id = a.company_id")
            ->field(["a.company_id", "m.employee_id", "m.order_id"])
            ->select();
    }
}