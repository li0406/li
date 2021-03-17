<?php

namespace Home\Model\Logic;

use Home\Model\Db\UserCompanyExpendModel;

class CompanyExpendLogicModel {

    // 更新落档的会员公司VIP状态
    public static function editCompanyVipStatus($company_id, $date = "", $vip_status = 2){
        $data = array(
            "vip_status" => $vip_status
        );

        if (empty($date)) {
            $date = date("Y-m-d");
        }

        $datetime = strtotime($date);
        $companyExpendModel = new UserCompanyExpendModel();
        return $companyExpendModel->updateInfo($company_id, $datetime, $data);
    }

    // 删除公司落档信息
    public static function deleteCompanyExpendInfo($company_id, $date = ""){
        if (empty($date)) {
            $date = date("Y-m-d");
        }
        
        $datetime = strtotime($date);
        $companyExpendModel = new UserCompanyExpendModel();
        return $companyExpendModel->deleteInfo($company_id, $datetime);
    }
}