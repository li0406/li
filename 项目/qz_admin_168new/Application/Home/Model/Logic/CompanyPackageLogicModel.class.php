<?php

namespace Home\Model\Logic;

use Exception;
use Home\Model\UserModel;
use Home\Model\Db\CompanyPackageModel;
use Home\Model\Db\CompanyPackageOrderModel;

class CompanyPackageLogicModel {

    public function getCompanyPackageList($company_id){
        $companyPackageModel = new CompanyPackageModel();
        $list = $companyPackageModel->getPackageList($company_id);

        foreach ($list as $key => $item) {
            $list[$key]["fen_left_number"] = $item["fen_number"] - $item["fen_send_number"];
            $list[$key]["zen_left_number"] = $item["zen_number"] - $item["zen_send_number"];
        }

        return $list;
    }

    public function getCompanyPackageHistoryList($company_id){
        $companyPackageModel = new CompanyPackageModel();
        $list = $companyPackageModel->getPackageHistoryList($company_id);

        foreach ($list as $key => $item) {
            $list[$key]["fen_left_number"] = $item["fen_number"] - $item["fen_send_number"];
            $list[$key]["zen_left_number"] = $item["zen_number"] - $item["zen_send_number"];
        }

        return $list;
    }

    public function getCompanyPackagePageList($company_id, $limit = 10){
        $companyPackageModel = new CompanyPackageModel();
        $count = $companyPackageModel->getPackagePageCount($company_id);

        if ($count > 0) {
            import('Library.Org.Util.Page');
            $pageClass = new \Page($count, $limit);
            $page = $pageClass->show();

            $list = $companyPackageModel->getPackagePageList($company_id, $pageClass->firstRow, $pageClass->listRows);

            $packageIds = array_column($list, "id");
            $companyPackageOrderModel = new CompanyPackageOrderModel();
            $totalList = $companyPackageOrderModel->getPackageOrderTotal($packageIds);

            $totalList = array_column($totalList, null, "package_id");
            foreach ($list as $key => $item) {
                $list[$key]["total"] = [];
                if (array_key_exists($item["id"], $totalList)) {
                    $list[$key]["total"] = $totalList[$item["id"]];
                }

            }

            return ["list" => $list, "page" => $page];
        }

        return ["list" => [], "page" => ""];
    }

    // 添加订单包
    public function addCompanyPackage($company_id, $input){
        try {
            // 新增包
            $data = array(
                "company_id" => $company_id,
                "deposit_money" => $input["deposit_money"],
                "back_ratio" => $input["back_ratio"],
                "created_at" => time(),
                "updated_at" => time()
            );

            $companyPackageModel = new CompanyPackageModel();
            $package_id = $companyPackageModel->add($data);
            if (empty($package_id)) {
                throw new Exception("保存失败", 404);
            }

            // 新增分单包
            $fenData = array(
                "package_id" => $package_id,
                "total_number" => $input["fen_number"],
                "use_status" => $input["fen_number"] ? 1: 3,
                "package_type" => 1,
                "created_at" => time(),
                "updated_at" => time()
            );
            $fenRet = $companyPackageModel->addInfo($fenData);

            // 新增赠单包
            $zenData = array(
                "package_id" => $package_id,
                "total_number" => $input["zen_number"],
                "use_status" => $input["zen_number"] ? 1: 3,
                "package_type" => 2,
                "created_at" => time(),
                "updated_at" => time()
            );
            $zenRet = $companyPackageModel->addInfo($zenData);

            if (empty($fenRet) || empty($zenRet)) {
                throw new Exception("保存失败", 404);
            }

            // 添加日志
            $input["company_id"] = $company_id;
            $logData = array(
                "package_id" => $package_id,
                "log_type" => 1,
                "log_type_name" => "新增包",
                "params" => json_encode($input),
                "operator" => session("uc_userinfo.id"),
                "operator_name" => session("uc_userinfo.name"),
                "created_at" => time(),
                "updated_at" => time()
            );

            $companyPackageModel->addLog($logData);

            $code = 200;
            $errmsg = "";
        } catch (Exception $e) {
            $code = $e->getCode();
            $errmsg = $e->getMessage();
        }

        return ["code" => $code, "errmsg" => $errmsg];
    }

    // 补包
    public function setPackageBudan($package_id, $fen_number = 0, $zen_number = 0){
        try {
            $companyPackageModel = new CompanyPackageModel();
            $packageInfo = $companyPackageModel->getPackageInfo($package_id);
            if (empty($packageInfo)) {
                throw new Exception("包不存在", 404);
            } else if ($packageInfo["refund_status"] != 1) {
                throw new Exception("该订单包已退费不能补包", 404);
            } else if ($packageInfo["back_status"] != 1) {
                throw new Exception("该订单包已返还保证金不能补包", 404);
            } else if ($packageInfo["use_status"] == 3) {
                throw new Exception("该订单包已使用完毕不能补包", 404);
            }

            // 补充分单包
            if ($fen_number > 0) {
                $fenData = array(
                    "total_number" => $packageInfo["fen_number"] + $fen_number,
                    "updated_at" => time()
                );

                if ($packageInfo["fen_use_status"] == 3) {
                    $packageInfo["fen_use_status"] = 1;
                    $fenData["use_status"] = 1;
                }

                $ret = $companyPackageModel->saveInfoData($package_id, 1, $fenData);
                if ($ret == false) {
                    throw new Exception("保存失败", 404);
                }
            }

            // 补充赠单包
            if ($zen_number > 0) {
                $zenData = array(
                    "total_number" => $packageInfo["zen_number"] + $zen_number,
                    "updated_at" => time()
                );

                if ($packageInfo["zen_use_status"] == 3) {
                    $packageInfo["zen_use_status"] = 1;
                    $zenData["use_status"] = 1;
                }

                $ret = $companyPackageModel->saveInfoData($package_id, 2, $zenData);
                if ($ret == false) {
                    throw new Exception("保存失败", 404);
                }
            }

            // 修改总包状态
            if ($packageInfo["fen_use_status"] == 1 && $packageInfo["zen_use_status"] == 1) {
                if ($packageInfo["use_status"] != 1) {
                    $data = array(
                        "use_status" => 1,
                        "updated_at" => time()
                    );

                    $companyPackageModel->where(["id" => $package_id])->save($data);
                }
            }

            // 添加日志
            $logData = array(
                "package_id" => $package_id,
                "log_type" => 2,
                "log_type_name" => "补包",
                "params" => json_encode([
                    "package_id" => $package_id,
                    "fen_number" => $fen_number,
                    "zen_number" => $zen_number,
                ]),
                "operator" => session("uc_userinfo.id"),
                "operator_name" => session("uc_userinfo.name"),
                "created_at" => time(),
                "updated_at" => time()
            );

            $companyPackageModel->addLog($logData);

            $code = 200;
            $errmsg = "";
        } catch (Exception $e) {
            $code = $e->getCode();
            $errmsg = $e->getMessage();
        }

        return ["code" => $code, "errmsg" => $errmsg];
    }

    // 退费
    public function setPackageRefund($package_id, $refund_date){
        try {
            $companyPackageModel = new CompanyPackageModel();
            $packageInfo = $companyPackageModel->getPackageInfo($package_id);
            if (empty($packageInfo)) {
                throw new Exception("包不存在", 404);
            } else if ($packageInfo["refund_status"] != 1) {
                throw new Exception("该订单包已退费不能重复退费", 404);
            } else if ($packageInfo["back_status"] != 1) {
                throw new Exception("该订单包已返还保证金不能退费", 404);
            } else if ($packageInfo["use_status"] == 3) {
                throw new Exception("该订单包已使用完毕不能退费", 404);
            }

            // 退费
            $data = array(
                "use_status" => 3,
                "refund_status" => 3,
                "refund_time" => strtotime($refund_date),
                "updated_at" => time()
            );

            $ret = $companyPackageModel->where(["id" => $package_id])->save($data);
            if ($ret == false) {
                throw new Exception("操作失败", 404);
            }

            // 添加日志
            $logData = array(
                "package_id" => $package_id,
                "log_type" => 3,
                "log_type_name" => "退费",
                "params" => json_encode([
                    "package_id" => $package_id,
                    "refund_date" => $refund_date,
                    "refund_date_confirm" => $refund_date,
                ]),
                "operator" => session("uc_userinfo.id"),
                "operator_name" => session("uc_userinfo.name"),
                "created_at" => time(),
                "updated_at" => time()
            );

            $companyPackageModel->addLog($logData);

            $code = 200;
            $errmsg = "";
        } catch (Exception $e) {
            $code = $e->getCode();
            $errmsg = $e->getMessage();
        }

        return ["code" => $code, "errmsg" => $errmsg];
    }

    // 转让
    public function setPackageTrans($package_id, $trans_company_id){
        try {
            // 查询订单包信息
            $companyPackageModel = new CompanyPackageModel();
            $packageInfo = $companyPackageModel->getPackageInfo($package_id);
            if (empty($packageInfo)) {
                throw new Exception("包不存在", 404);
            } else if ($packageInfo["refund_status"] != 1) {
                throw new Exception("该订单包已退费不能转让", 404);
            } else if ($packageInfo["back_status"] != 1) {
                throw new Exception("该订单包已返还保证金不能转让", 404);
            } else if ($packageInfo["use_status"] == 3) {
                throw new Exception("该订单包已使用完毕不能转让", 404);
            } else if ($packageInfo["company_id"] == $trans_company_id) {
                throw new Exception("不能转让给当前公司", 404);
            }

            // 查询转让公司信息
            $userModel = new UserModel();
            $transComInfo = $userModel->findCompanyInfo($trans_company_id);
            if (empty($transComInfo)) {
                throw new Exception("转让公司不存在", 404);
            } else if ($transComInfo["classid"] != 6) {
                throw new Exception("转让公司不属于签单返点会员，不可转让", 404);
            }

            $fen_number = $packageInfo["fen_number"] - $packageInfo["fen_send_number"];
            $zen_number = $packageInfo["zen_number"] - $packageInfo["zen_send_number"];

            if ($fen_number == 0 && $zen_number == 0) {
                throw new Exception("该订单包已使用完毕不能转让", 404);
            }
            
            // 修改原包状态
            $data = array(
                "use_status" => 3,
                "back_status" => 3,
                "back_money" => $packageInfo["deposit_money"],
                "updated_at" => time()
            );

            $companyPackageModel->saveData($packageInfo["id"], $data);

            // 修改原包剩余数量
            $fenData = array(
                "total_number" => $packageInfo["fen_send_number"],
                "updated_at" => time(),
                "use_status" => 3,
            );
            $companyPackageModel->saveInfoData($packageInfo["id"], 1, $fenData);

            $zenData = array(
                "total_number" => $packageInfo["zen_send_number"],
                "updated_at" => time(),
                "use_status" => 3,
            );
            $companyPackageModel->saveInfoData($packageInfo["id"], 2, $zenData);

            // 添加日志
            $logData = array(
                "package_id" => $package_id,
                "log_type" => 4,
                "log_type_name" => "转让",
                "params" => json_encode([
                    "package_id" => $package_id,
                    "trans_company_id" => $trans_company_id,
                    "old_company_id" => $packageInfo["company_id"],
                ]),
                "operator" => session("uc_userinfo.id"),
                "operator_name" => session("uc_userinfo.name"),
                "created_at" => time(),
                "updated_at" => time()
            );

            $companyPackageModel->addLog($logData);



            // 新增包
            $newdata = array(
                "company_id" => $trans_company_id,
                "deposit_money" => $packageInfo["deposit_money"],
                "back_ratio" => $packageInfo["back_ratio"],
                "created_at" => time(),
                "updated_at" => time()
            );

            $companyPackageModel = new CompanyPackageModel();
            $new_package_id = $companyPackageModel->add($newdata);

            // 新增分单包
            $fenData = array(
                "package_id" => $new_package_id,
                "total_number" => $fen_number,
                "use_status" => $fen_number > 0 ? 1: 3,
                "package_type" => 1,
                "created_at" => time(),
                "updated_at" => time()
            );
            $fenRet = $companyPackageModel->addInfo($fenData);

            // 新增赠单包
            $zenData = array(
                "package_id" => $new_package_id,
                "total_number" => $zen_number,
                "use_status" => $zen_number > 0 ? 1: 3,
                "package_type" => 2,
                "created_at" => time(),
                "updated_at" => time()
            );
            $zenRet = $companyPackageModel->addInfo($zenData);

            // 添加日志
            $logData = array(
                "package_id" => $new_package_id,
                "log_type" => 1,
                "log_type_name" => "新增包",
                "params" => "",
                "operator" => session("uc_userinfo.id"),
                "operator_name" => session("uc_userinfo.name"),
                "created_at" => time(),
                "updated_at" => time()
            );

            $companyPackageModel->addLog($logData);

            $code = 200;
            $errmsg = "";
        } catch (Exception $e) {
            $code = $e->getCode();
            $errmsg = $e->getMessage();
        }

        return ["code" => $code, "errmsg" => $errmsg];
    }

    // 会员公司订单包签单记录
    public function getCompanySignList($back_status, $company_id, $limit = 10){
        $companyPackageModel = new CompanyPackageModel();

        $total = $companyPackageModel->getCompanyNowPackageSignCount($back_status, $company_id);
        if ($total["count"] > 0) {
            import('Library.Org.Util.Page');
            $pageClass = new \Page($total["count"], $limit);
            $page = $pageClass->show();

            $list = $companyPackageModel->getCompanyNowPackageSignList($back_status, $company_id, $pageClass->firstRow, $limit);

            if (count($list) > 0) {
                foreach ($list as $key => $item) {
                    $list[$key]["qiandan_mianji"] = $item["qiandan_mianji"] ? : $item["mianji"];
                }
            }
        }

        return ["list" => $list, "page" => $page, "total" => $total];
    }


    // 收款
    public function setSignPayMoney($id, $pay_money){
        try {
            $companyPackageOrderModel = new CompanyPackageOrderModel();
            $info = $companyPackageOrderModel->getById($id);
            if (empty($info)) {
                throw new Exception("签单记录不存在", 404);
            }

            $data = array(
                "back_pay_money" => $pay_money,
                "updated_at" => time()
            );

            $ret = $companyPackageOrderModel->saveData($id, $data);
            if ($ret == false) {
                throw new Exception("操作失败", 404);
            }

            $logData = array(
                "package_order_id" => $id,
                "log_type" => 1,
                "log_type_name" => "收款",
                "pay_money" => $pay_money,
                "operator" => session("uc_userinfo.id"),
                "operator_name" => session("uc_userinfo.name"),
                "created_at" => time(),
                "updated_at" => time()
            );

            $companyPackageOrderModel->addLog($logData);

            $code = 200;
            $errmsg = "";
        } catch (Exception $e) {
            $code = $e->getCode();
            $errmsg = $e->getMessage();
        }

        return ["code" => $code, "errmsg" => $errmsg];
    }

    // 审核
    public function setSignOrderExam($id, $exam_status){
        try {
            $companyPackageOrderModel = new CompanyPackageOrderModel();
            $info = $companyPackageOrderModel->getById($id);
            if (empty($info)) {
                throw new Exception("签单记录不存在", 404);
            } else if ($exam_status == 1 && $info["back_pay_money"] == 0) {
                throw new Exception("未填写实缴金额", 404);
            }

            $back_status = $exam_status == 1 ? 2 : 1;
            if ($info["back_status"] != $back_status) {
                $data = array(
                    "back_status" => $back_status,
                    "updated_at" => time()
                );
                $ret = $companyPackageOrderModel->saveData($id, $data);
                if ($ret == false) {
                    throw new Exception("操作失败", 404);
                }
            }

            $logData = array(
                "package_order_id" => $id,
                "log_type" => 2,
                "log_type_name" => "审核",
                "exam_status" => $exam_status,
                "operator" => session("uc_userinfo.id"),
                "operator_name" => session("uc_userinfo.name"),
                "created_at" => time(),
                "updated_at" => time()
            );

            $companyPackageOrderModel->addLog($logData);

            $code = 200;
            $errmsg = "";
        } catch (Exception $e) {
            $code = $e->getCode();
            $errmsg = $e->getMessage();
        }

        return ["code" => $code, "errmsg" => $errmsg];
    }

    public function getSignOrderLog($id){
        $companyPackageOrderModel = new CompanyPackageOrderModel();
        return $companyPackageOrderModel->getOrderLogList($id);
    }

    // 保证金审核
    public function setPackageExam($id, $back_status, $back_money = 0){
        try {
            $companyPackageModel = new CompanyPackageModel();
            $info = $companyPackageModel->getById($id);
            if (empty($info)) {
                throw new Exception("订单包不存在", 404);
            }

            if ($back_status == 2 && $back_money > $info["deposit_money"]) {
                throw new Exception("返还金额不可超出保证金", 404);
            }

            $data = array(
                "back_status" => $back_status,
                "updated_at" => time()
            );

            if ($back_status == 2) {
                $data["back_money"] = $back_money;
            } else if ($back_status == 3) {
                $data["back_money"] = $info["deposit_money"];
            }

            $ret = $companyPackageModel->saveData($id, $data);
            if ($ret == false) {
                throw new Exception("操作失败", 404);
            }

            $logData = array(
                "package_id" => $id,
                "log_type" => 5,
                "log_type_name" => "审核",
                "params" => json_encode($data),
                "operator" => session("uc_userinfo.id"),
                "operator_name" => session("uc_userinfo.name"),
                "created_at" => time(),
                "updated_at" => time()
            );

            $companyPackageModel->addLog($logData);

            $code = 200;
            $errmsg = "";
        } catch (Exception $e) {
            $code = $e->getCode();
            $errmsg = $e->getMessage();
        }

        return ["code" => $code, "errmsg" => $errmsg];
    }

    public function getPackageLog($id){
        $companyPackageModel = new CompanyPackageModel();
        return $companyPackageModel->getPackageLogList($id);
    }
}