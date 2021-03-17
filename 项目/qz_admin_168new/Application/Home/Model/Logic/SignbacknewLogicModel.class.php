<?php
// +----------------------------------------------------------------------
// | SignbacknewLogicModel  签单返点会员逻辑层
// +----------------------------------------------------------------------

namespace Home\Model\Logic;

use Exception;
use Home\Model\Db\UserModel;
use Home\Model\Db\CompanyModel;
use Home\Model\Db\UserCompanyAccountModel;
use Home\Model\Db\UserCompanyAccountLogModel;

class SignbacknewLogicModel {

    private $back_ratio_list = [
        "5" => "5%",
        "3" => "3%",
        "2" => "2%",
        "1" => "1%",
        "0" => "0%"
    ];

    public function getBackRatioList(){
        return $this->back_ratio_list;
    }

    public function getInfo($user_id, $relation = false){
        $userModel = new UserModel();
        $userinfo = $userModel->getSignBackNewInfo($user_id);
        if (!empty($userinfo)) {
            $userinfo = $this->setFormatter([$userinfo], 0, $relation)[0];
        }

        return $userinfo;
    }

    // 签单返点会员列表
    public function getAccountList($input,$sort, $limit = 20){
        $userModel = new UserModel();
        $count = $userModel->getSignBackNewCount($input);

        import('Library.Org.Util.Page');
        $pageClass = new \Page($count, $limit);
        $page = $pageClass->show();

        if ($count > 0) {
            $list = $userModel->getSignBackNewList($input, $sort, $pageClass->firstRow, $pageClass->listRows);
            $list = $this->setFormatter($list, $pageClass->firstRow);
        }

        return ["list" => $list, "page" => $page];
    }

    public function getAccountExportList($input){
        $userModel = new UserModel();
        $list = $userModel->getSignBackNewList($input);
        $list = $this->setFormatter($list);

        return $list;
    }

    // 设置返点比例
    public function setAccountBackRatio($user_id, $back_ratio){
        $userModel = new UserModel();
        $info = $userModel->getUserSignbackInfo($user_id);
        if (empty($info)) {
            $data = array(
                "user_id" => $user_id,
                "back_ratio" => $back_ratio,
                "created_at" => time(),
                "updated_at" => time()
            );

            $ret = $userModel->addUserSignbackInfo($data);
        } else {
            $data = array(
                "back_ratio" => $back_ratio,
                "updated_at" => time()
            );

            $ret = $userModel->editUserSignbackInfo($user_id, $data);
        }

        if ($ret) {
            //添加操作日志
            $log = array(
                'remark' => '修改会员【' . $user_id . '】返点比例',
                'logtype' => 'edituserbackratio',
                'action_id' => $user_id,
                'info' => $data
            );
            D('LogAdmin')->addLog($log);
        }

        return $ret;
    }

    // 格式化处理、关联数据
    private function setFormatter($list, $firstrow = 0, $relation = true){
        if (count($list) > 0) {
            if ($relation == true) {
                $ids = array_column($list, "id");
                
                // 查询加款扣款总金额
                $accountLogModel = new UserCompanyAccountLogModel();
                $statisticList = $accountLogModel->getAccountStatistic($ids);
                $statisticList = array_column($statisticList, null, "user_id");
            }

            foreach ($list as $key => &$item) {
                $company_id = $item["id"];
                $item["index"] = $firstrow + $key + 1;
                $item["back_ratio"] = floatval($item["back_ratio"]);
                $item["deposit_money"] = floatval($item["deposit_money"]);
                $item["account_amount"] = floatval($item["account_amount"]);
                $item["contract_start_date"] = $item["contract_start"] ? date("Y-m-d", $item["contract_start"]) : "";
                $item["contract_end_date"] = $item["contract_end"] ? date("Y-m-d", $item["contract_end"]) : "";
                $item["last_recharge_date"] = $item["last_recharge_time"] ? date("Y-m-d", $item["last_recharge_time"]) : "";
                $item["cooperate_mode"] = $item["cooperate_mode"] == 2?"新签返":($item["cooperate_mode"] == 4?"新签返标杆会员":"-");

                if ($relation == true) {
                    // 派单总金额
                    $item["order_amount"] = 0;
                    if (array_key_exists($company_id, $statisticList)) {
                        $stat = $statisticList[$company_id];
                        $item["trade_recharge_amount"] = floatval($stat["trade_recharge_amount"]);
                        $item["trade_deduction_amount"] = floatval($stat["trade_deduction_amount"]);
                        $item["order_amount"] = round($stat["trade_recharge_amount"] - $stat["trade_deduction_amount"], 2);
                    }
                }
            }
        }

        return $list;
    }

    public function getAccountStat($params)
    {
        $userModel = new UserModel();
        $result = $userModel->getAccountStat($params);

        $all = [];
        foreach ($result as $item) {
            $all["all_trade_money"] += $item["trade_recharge_amount"] - $item["trade_deduction_amount"];
            $all["all_account_amount"] += $item["account_amount"];
            $all["all_round_order"] += $item["round_order_number"];
            $all["deposit_amount"] += $item["deposit_money"];
        }

        $all["all_trade_money"] = round($all["all_trade_money"], 2);
        $all["all_account_amount"] = round($all["all_account_amount"], 2);
        $all["deposit_amount"] = round($all["deposit_amount"], 2);

        return $all;
    }
}
