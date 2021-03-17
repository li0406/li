<?php
// +----------------------------------------------------------------------
// | CompanyRoundOrderLogic 新签返轮单记录逻辑层
// +----------------------------------------------------------------------
namespace app\common\model\logic;

use app\common\model\db\SaleReportPayment;
use app\common\model\db\UserCompanyRoundOrder;
use Util\Page;

class CompanyRoundOrderLogic {

    protected $companyRoundOrderModel;

    public function __construct(){
        $this->companyRoundOrderModel = new UserCompanyRoundOrder();
    }

    // 签返金额计算
    public function signbackRoundOrder($order_id, $user_id, $qiandan_jine){
        $info = $this->companyRoundOrderModel->findRoundOrderInfo($order_id, $user_id);

        $result = true;
        if (!empty($info)) {
            $back_ratio = $info["back_ratio"] / 100;
            $back_total_money = ($qiandan_jine * 10000) * $back_ratio;

            $data = [
                "back_total_money" => $back_total_money,
                "updated_at" => time()
            ];

            $result = $this->companyRoundOrderModel->saveData($info["id"], $data);
        }

        return $result;
    }

    // 签单列表
    public function getRoundOrderSignList($input, $page, $limit){
        $count = $this->companyRoundOrderModel->getRoundOrderSignPageCount($input);
        $pageobj = new Page($page, $limit, $count);

        if ($count > 0) {
            $list = $this->companyRoundOrderModel->getRoundOrderSignPageList($input, $pageobj->firstrow, $pageobj->pageSize);

            foreach ($list as $key => &$item) {
                $item["date_real"] = date("Y-m-d H:i:s", $item["time_real"]);
                $item["qiandan_adddate"] = date("Y-m-d H:i:s", $item["qiandan_addtime"]);
                $item["qiandan_chkdate"] = date("Y-m-d H:i:s", $item["qiandan_chktime"]);
                $item["back_ratio_show"] = $item["back_ratio"] . "%";

                $item["order_sign_money"] = floatval($item["qiandan_jine"] * 10000);
                $item["order_back_money"] = floatval($item["order_sign_money"] * $item["back_ratio"]);
            }
        }

        return [
            "count" => $count,
            "list" => $list ?? [],
            "page" => $pageobj->show()
        ];
    }

    // 签单详情
    public function getRoundOrderSignInfo($order_id){
        $orderinfo = $this->companyRoundOrderModel->getRoundOrderSignInfo($order_id, false);

        if (!empty($orderinfo)) {
            $orderinfo["date_real"] = date("Y-m-d H:i:s", $orderinfo["time_real"]);
            $orderinfo["qiandan_adddate"] = date("Y-m-d H:i:s", $orderinfo["qiandan_addtime"]);
            $orderinfo["qiandan_chkdate"] = date("Y-m-d H:i:s", $orderinfo["qiandan_chktime"]);
            $orderinfo["back_ratio"] = intval($orderinfo["back_ratio"]);
            $orderinfo["back_ratio_show"] = $orderinfo["back_ratio"] . "%";

            $orderinfo["order_sign_money"] = floatval($orderinfo["qiandan_jine"] * 10000);
            $orderinfo["order_back_money"] = floatval($orderinfo["order_sign_money"] * $orderinfo["back_ratio"] / 100);
        }

        return $orderinfo;
    }

    // 会员公司签单记录统计
    public function getCompanyRoundOrderSignTotal($company_id){
        $total = $this->companyRoundOrderModel->getCompanyOrderSignTotal($company_id);
        if (!empty($total)) {
            $total["qiandan_amount"] = round(floatval($total["qiandan_jine"]) * 10000);
            unset($total["qiandan_jine"]);
        }

        return $total;
    }

    // 会员公司签单记录
    public function getCompanyRoundOrderSignList($company_id, $page = 1, $limit = 10){
        $count = $this->companyRoundOrderModel->getCompanyOrderSignCount($company_id);
        $pageobj = new Page($page, $limit, $count);
        $pageshow = $pageobj->show();

        $list = [];
        if ($count > 0) {
            $list = $this->companyRoundOrderModel->getCompanyOrderSignList($company_id, $pageobj->firstrow, $pageobj->pageSize);
            $orderIds = array_column($list->toArray(), "order_id");

            $reportPaymentModel = new SaleReportPayment();
            $paymentTotal = $reportPaymentModel->getOrderBackTotal($orderIds);
            $paymentTotal = array_column($paymentTotal->toArray(), null, "order_id");

            foreach ($list as $key => &$item) {
                $item["type_fw_name"] = $item["company_type_fw"] == 1 ? "分单" : "赠单";
                $item["qiandan_mianji"] = $item["qiandan_mianji"] ? : $item["mianji"];
                $item["qiandan_adddate"] = date("Y-m-d", $item["qiandan_addtime"]);
                $item["back_total_money"] = floatval($item["back_total_money"]);
                $item["qiandan_jine"] = floatval($item["qiandan_jine"] * 10000);

                // 应缴金额如果没有计算则重新计算
                if (empty($item["back_total_money"]) && !empty($item["back_ratio"])) {
                    $item["back_total_money"] = $item["qiandan_jine"] * $item["back_ratio"] / 100;
                }

                $item["back_status"] = 2;
                $item["back_status_name"] = "未缴纳";
                $item["back_pay_money"] = 0;
                if (array_key_exists($item["order_id"], $paymentTotal)) {
                    $item["back_status"] = 1;
                    $item["back_status_name"] = "已缴纳";
                    $item["back_pay_money"] = floatval($paymentTotal[$item->order_id]["payment_money"]);
                }
            }
        }

        return [
            "list" => $list,
            "page" => $pageshow
        ];
    }

}