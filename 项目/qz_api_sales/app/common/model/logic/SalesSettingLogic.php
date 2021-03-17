<?php

/**
 * 
 * @Author: liuyupeng
 * @Email: lypeng9205@163.com
 * @Date:   2019-06-04 13:40:30
 */

namespace app\common\model\logic;

use think\Db;
use think\db\Query;
use think\db\Where;
use think\facade\Request;

use app\common\model\db\RoleDepartment;
use app\common\model\db\SalesCityManage;

class SalesSettingLogic {

    private $userPosition = [
        "shangwu" => [
            '商务经理'      => 'corps',//军长
            '商务助理'      => 'assistant',//助理
            '商务拓展经理'    => 'dev_division',//拓展师长
            '商务拓展督导'    => 'dev_regiment',//拓展团长
            '商务拓展城市经理'    => 'dev_manage',//城市经理
            '商务品牌经理'      => 'brand_division',//品牌师长
            '商务品牌督导'      => 'brand_regiment',//品牌团长
            '商务品牌师'       => 'brand_manage'//品牌师
        ],
        "waixiao" => [
            '外销经理'      => 'corps',//军长
            '外销助理'      => 'assistant',//助理
            '拓展经理'      => 'dev_division',//拓展师长
            '外销区域经理'    => 'dev_regiment',//拓展团长
            '销售'            => 'dev_manage',//城市经理
            '督导'            => 'dev_manage',//城市经理
            '品牌经理'      => 'brand_division',//品牌师长
            '品牌督导'      => 'brand_regiment',//品牌团长
            '品牌师'       => 'brand_manage'//品牌师
        ]
    ];

    // 获取职位字段名称
    public function getUserPosition($dept, $role){
        $position = [];
        if($dept == 1){
            $position = $this->userPosition["shangwu"];
        } else if ($dept == 2) {
            $position = $this->userPosition["waixiao"];
        }

        $result = false;
        if (array_key_exists($role, $position)) {
            $result = $position[$role];
        }

        return $result;
    }

    // 获取销售管辖城市
    public function getUserSaleCitys(){
        $user = Request::instance()->user;

        // 超级权限类别
        $manager = array(1, 37, 46, 51);
        if (in_array($user["role_id"], $manager)) {
            $salesCityManageModel = new SalesCityManage();
            $citys = $salesCityManageModel->getAllCitys();//超级管理员及销售司令能查看所有城市
        } else {
            $roleDepartmentModel = new RoleDepartment();
            $department = $roleDepartmentModel->getDepartmentByRoleId($user["role_id"]);
            $department_id = empty($department) ? 0 : $department["department_id"];

            $where = [];
            if ($department_id == 5) {
                $where["dept"] = 2; //外销
            } else if ($where["dept"] == 6) {
                $where["dept"] = 1; // 商务
            }

            $citys = [];
            if (count($where) > 0) {
                //根据商务、外销职位数组
                $position = $this->getUserPosition($where["dept"], $user["role_name"]);
                if ($position) {
                    if ($position != "assistant") {
                        $where[$position] = $user["user_id"];
                    }

                    $salesCityManageModel = new SalesCityManage();
                    $citys = $salesCityManageModel->getManageCitys(new Where($where));//普通职位只能查看自己管辖的城市
                }
            }
        }

        return $citys;
    }

}