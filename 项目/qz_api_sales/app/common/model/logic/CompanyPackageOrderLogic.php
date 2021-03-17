<?php
// +----------------------------------------------------------------------
// | OrdersLogic 订单逻辑层
// +----------------------------------------------------------------------
namespace app\common\model\logic;

use app\common\model\db\CompanyPackageOrder;

class CompanyPackageOrderLogic {

    public function signbackOrder($order_id, $company_id, $qiandan_jine){
        $companyPackageOrderModel = new CompanyPackageOrder();
        $info = $companyPackageOrderModel->findPackageOrder($order_id, $company_id);

        $result = true;
        if (!empty($info)) {
            $back_ratio = $info["back_ratio"] / 100;
            $back_total_money = ($qiandan_jine * 10000) * $back_ratio;

            $data = [
                "back_total_money" => $back_total_money,
                "updated_at" => time()
            ];

            $result = $companyPackageOrderModel->saveData($info["id"], $data);
        }

        return $result;
    }

}