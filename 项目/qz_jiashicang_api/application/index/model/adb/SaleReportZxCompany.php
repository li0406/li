<?php

// +----------------------------------------------------------------------
// | SaleReportZxCompany 大报备数据模型
// +----------------------------------------------------------------------

namespace app\index\model\adb;

use think\Db;
use think\db\Query;

use app\common\model\adb\BaseModel;

class SaleReportZxCompany extends BaseModel {

    // 城市大报备轮单费、会员费
    public function getCityReportAmountTotal($date_begin, $date_end, $city_ids = []){
        $map = new Query();
        $map->where("a.status", 8);
        $map->where("a.cooperation_type", "in", [1, 2, 3, 6]);
        $map->where("a.current_payment_time", ">=", strtotime($date_begin));
        $map->where("a.current_payment_time", "<=", strtotime($date_end) + 86399);

        if (!empty($city_ids)){
            $map->where("a.cs", "in", $city_ids);
        }

        $list = $this->link()->name("sale_report_zx_company")->alias("a")->where($map)
            ->field([
                "a.cs as city_id",
                "sum(a.current_contract_round_amount) as total_round_amount",
                "sum(if(a.current_contract_round_total_amount = 0, 
                    a.current_contract_round_amount, 
                    a.current_contract_round_total_amount)
                ) as round_amount"
            ])
            ->group("a.cs")
            ->select();

        return $list;
    }

    // 会员大报备平台使用费
    public function getCompanyReportAmountTotal($date_begin, $date_end, $company_ids = []){
        $map = new Query();
        $map->where("a.status", 8);
        $map->where("a.cooperation_type", "in", [1, 2, 3, 6]);
        $map->where("a.current_payment_time", ">=", strtotime($date_begin));
        $map->where("a.current_payment_time", "<=", strtotime($date_end) + 86399);

        if (!empty($company_ids)){
            $map->where("a.company_id", "in", $company_ids);
        }

        return $this->link()->name("sale_report_zx_company")->alias("a")->where($map)
            ->field([
                "a.company_id",
                "sum(a.current_platform_money) as platform_amount",
            ])
            ->group("a.company_id")
            ->select();
    }
}