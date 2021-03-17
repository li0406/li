<?php
namespace Common\Model\Logic;

use Common\Model\Db\UserCompanyEmployeeModel;
use Common\Model\UserModel;
use Common\Model\Db\CompanyPackageModel;
use Common\Model\Db\UserCompanyAccountModel;
use Common\Model\Db\UserCompanySignbackModel;

/**
 *
 */
class UserLogicModel
{
    public function getPackageInfo($company_id)
    {
        $model = new CompanyPackageModel();
        $result = $model->getPackageInfo($company_id);
        $list = [];
        foreach ($result as $item) {
            $item["deposit_money"] = (int)$item["deposit_money"];
            $list["list"][] = $item;
            $list["info"]["fen_send"] += $item["fen_send"];
            $list["info"]["zen_send"] += $item["zen_send"];
        }

        if (empty( $list["info"]["fen_send"])) {
            $list["info"]["fen_send"] = "请耐心等待";
        }

        if (empty( $list["info"]["zen_send"])) {
            $list["info"]["zen_send"] = "请耐心等待";
        }

        return $list;
    }

    public function getPackageQianDanInfo($company_id)
    {
        $model = new CompanyPackageModel();
        $info = $model->getPackageQianDanInfo($company_id);

        if (empty($info["all_count"])) {
            $info["all_count"] = "加油哦";
        }

        if (empty($info["all_money"])) {
            $info["all_money"] = "马上破零了";
        } else {
            $info["all_money"] = round($info["all_money"]*10000,0);
        }

        return $info;
    }

    public function loadpackageorderinfo($company_id)
    {
        $model = new CompanyPackageModel();
        $info = $model->loadpackageorderinfo($company_id);
        if ($info["count"] > 0) {
            $info["all_money"] = round($info["all_money"],0);
            $info["un_pay_money"] = round($info["all_money"] - $info["pay_money"],0);
        }
        return $info;
    }

    public function checkAccount($name)
    {
        $model = new UserModel();
        return $model->checkAccount($name);
    }

    // 添加用户账户记录
    public function addCompanyAccountInfo($user_id){
        $data = [
            "user_id" => $user_id
        ];

        $userCompanyAccountModel = new UserCompanyAccountModel();
        return $userCompanyAccountModel->addAccountInfo($data);
    }

    // 添加签返记录
    public function addCompanySignbackInfo($user_id){
        $data = [
            "user_id" => $user_id
        ];

        $userCompanySignbackModel = new UserCompanySignbackModel();
        return $userCompanySignbackModel->addSignbackInfo($data);
    }

    public function getEmployeeCount($company_id)
    {
        $model = new UserCompanyEmployeeModel();
        return $model->getEmployeeCount($company_id);
    }
}