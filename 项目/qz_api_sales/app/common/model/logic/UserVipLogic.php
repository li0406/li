<?php

namespace app\common\model\logic;

use app\common\model\db\UserVip;
use app\index\enum\CompanyCodeEnum;

class UserVipLogic {

    protected $userVipModel;

    public function __construct(){
        $this->userVipModel = new UserVip();
    }

    // 查找会员公司合同
    public function getCompanyContractList($company_id, $date){
        $list = $this->userVipModel->getCompanyContractList($company_id, $date);

        foreach ($list as $key => &$item) {
            $item["company_mode"] = CompanyCodeEnum::getCompanyMode($item["contract_type"], $item["cooperate_mode"]);
            $item["company_mode_name"] = CompanyCodeEnum::getItem("company_mode", $item["company_mode"]);
            $item["add_date"] = $item["time"] ? date("Y-m-d H:i", $item["time"]) : "";
            unset($item["contract_type"], $item["cooperate_mode"]);
        }

        return $list;
    }

    public function getCompanystanrdContractInfo($company_id = 0, $mode = 0)
    {
         return $this->userVipModel->getCompanystanrdContractInfo($company_id,$mode);
    }

}