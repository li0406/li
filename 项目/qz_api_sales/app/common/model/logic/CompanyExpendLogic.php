<?php

namespace app\common\model\logic;

use app\common\model\db\User;
use app\common\model\db\UserCompanyExpend;

class CompanyExpendLogic {

    // 加入会员公司日消耗更新队列
    public static function addExpendQueue($company_id, $report_id, $cooperation_type){
        $data = [
            "report_id" => $report_id,
            "cooperation_type" => $cooperation_type,
            "company_id" => $company_id,
            "created_at" => time(),
            "updated_at" => time()
        ];

        $companyExpendModel = new UserCompanyExpend();
        return $companyExpendModel->addQueueInfo($data);
    }
}