<?php

namespace Home\Model\Logic;

use Exception;
use Home\Model\Db\UserModel;
use Home\Model\Db\CompanyModel;
use Home\Model\Db\SaleReportPaymentModel;
use Home\Model\Db\UserCompanyRoundOrderModel;

class CompanyRoundOrderLogicModel {

    private $companyRoundOrderModel;

    public function __construct(){
        $this->companyRoundOrderModel = new UserCompanyRoundOrderModel();
    }

    // 获取装修公司签单列表
    public function getCompanySignOrderList($user_id, $limit = 10){
        $total = $this->companyRoundOrderModel->getCompanySignCount($user_id);
        $total["qiandan_jine"] = round(floatval($total["qiandan_jine"]) * 10000);

        // 累计实缴金额
        $paymentTotal = $this->companyRoundOrderModel->getPaymentMoneyTotal($user_id);
        $total["payment_total_money"] = floatval($paymentTotal["total_money"]);

        if ($total["count"] > 0) {
            import('Library.Org.Util.Page');
            $pageClass = new \Page($total["count"], $limit);
            $pageshow = $pageClass->show();

            $list = $this->companyRoundOrderModel->getCompanySignList($user_id, $pageClass->firstRow, $pageClass->listRows);

            // 查询小报备次数和金额
            $orderIds = array_column($list, "order_id");
            $reportPaymentModel = new SaleReportPaymentModel();
            $paymentList = $reportPaymentModel->getOrderTotal($orderIds);
            $orderPaymentList = array_column($paymentList, null, "order_id");

            if (count($list) > 0) {
                foreach ($list as $key => $item) {
                    $order_id = $item["order_id"];
                    $list[$key]["qiandan_jine"] = floatval($item["qiandan_jine"]);
                    $list[$key]["back_pay_money"] = floatval($item["back_pay_money"]);
                    $list[$key]["back_total_money"] = floatval($item["back_total_money"]);
                    $list[$key]["qiandan_jine_yuan"] = floatval($item["qiandan_jine"]) * 10000;
                    $list[$key]["qiandan_mianji"] = $item["qiandan_mianji"] ? : $item["mianji"];

                    // 修复错误数据
                    if (empty($list[$key]["back_total_money"]) && !empty($item["back_ratio"])) {
                        $list[$key]["back_total_money"] = $list[$key]["qiandan_jine_yuan"] * $item["back_ratio"] / 100;
                    }

                    $list[$key]["payment_num"] = 0;
                    $list[$key]["payment_money"] = 0;
                    if (array_key_exists($order_id, $orderPaymentList)) {
                        $list[$key]["payment_num"] = $orderPaymentList[$order_id]["payment_num"];
                        $list[$key]["payment_money"] = floatval($orderPaymentList[$order_id]["payment_amount"]);
                        $list[$key]["payment_money"] += floatval($orderPaymentList[$order_id]["deposit_amount"]);
                        $list[$key]["payment_money"] += floatval($orderPaymentList[$order_id]["round_order_amount"]);
                    }
                }
            }
        }

        return ["list" => $list, "pageshow" => $pageshow, "total" => $total];
    }
    

    // 签单核销收款
    public function setSignPayMoney($id, $pay_money){
        try {
            $info = $this->companyRoundOrderModel->getById($id);
            if (empty($info)) {
                throw new Exception("签单记录不存在", 4000001);
            }

            $data = array(
                "back_pay_money" => $pay_money,
                "updated_at" => time()
            );

            $ret = $this->companyRoundOrderModel->saveData($id, $data);
            if ($ret == false) {
                throw new Exception("操作失败", 5000001);
            }

            $logData = array(
                "round_order_id" => $id,
                "log_type" => 1,
                "log_type_name" => "收款",
                "pay_money" => $pay_money,
                "operator" => session("uc_userinfo.id"),
                "operator_name" => session("uc_userinfo.name"),
                "created_at" => time(),
                "updated_at" => time()
            );

            $this->companyRoundOrderModel->addLog($logData);

            $errcode = 0;
            $errmsg = "";
        } catch (Exception $e) {
            $errcode = $e->getCode();
            $errmsg = $e->getMessage();
        }

        return ["errcode" => $errcode, "errmsg" => $errmsg];
    }

    // 签单核销收款审核
    public function setSignOrderExam($id, $exam_status){
        try {
            $info = $this->companyRoundOrderModel->getById($id);
            if (empty($info)) {
                throw new Exception("签单记录不存在", 4000001);
            } else if ($exam_status == 1 && $info["back_pay_money"] == 0) {
                throw new Exception("未填写实缴金额", 4000001);
            }

            $back_status = $exam_status == 1 ? 2 : 1;
            if ($info["back_status"] != $back_status) {
                $data = array(
                    "back_status" => $back_status,
                    "updated_at" => time()
                );
                $ret = $this->companyRoundOrderModel->saveData($id, $data);
                if ($ret == false) {
                    throw new Exception("操作失败", 5000001);
                }
            }

            $logData = array(
                "round_order_id" => $id,
                "log_type" => 2,
                "log_type_name" => "审核",
                "exam_status" => $exam_status,
                "operator" => session("uc_userinfo.id"),
                "operator_name" => session("uc_userinfo.name"),
                "created_at" => time(),
                "updated_at" => time()
            );

            $this->companyRoundOrderModel->addLog($logData);

            $errcode = 0;
            $errmsg = "";
        } catch (Exception $e) {
            $errcode = $e->getCode();
            $errmsg = $e->getMessage();
        }

        return ["errcode" => $errcode, "errmsg" => $errmsg];
    }

    // 签单核销收款日志
    public function getSignOrderLog($id){
        $list = $this->companyRoundOrderModel->getOrderLogList($id);
        return $list;
    }

}