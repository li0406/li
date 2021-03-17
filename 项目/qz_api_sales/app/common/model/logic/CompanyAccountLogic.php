<?php

namespace app\common\model\logic;

use think\Db;
use think\Exception;
use think\facade\Request;
use Util\Page;

use app\common\model\db\UserCompanyAccount;
use app\common\model\db\UserCompanyAccountLog;
use app\common\model\db\UserCompanyRoundOrderAmount;
use app\common\enum\BaseStatusCodeEnum;
use app\index\enum\CompanyAccountCodeEnum;

class CompanyAccountLogic {

    protected $accountModel;
    protected $accountLogModel;

    public function __construct(){
        $this->accountModel = new UserCompanyAccount();
        $this->accountLogModel = new UserCompanyAccountLog();
    }

    // 获取用户账户信息
    public function getAccountInfo($user_id){
        $accountInfo = $this->accountModel->getAccountInfo($user_id);
        if (!empty($accountInfo)) {
            $accountInfo["account_amount"] = floatval($accountInfo["account_amount"]);
            $accountInfo["deposit_money"] = floatval($accountInfo["deposit_money"]);
            $accountInfo["gift_amount"] = floatval($accountInfo["gift_amount"]);
        }
        
        return $accountInfo;
    }

    // 会员资金列表
    public function getAccountPageList($input, $page = 1, $limit = 10){
        if (!empty($input["user_on"])) {
            $input["on_status"] = CompanyAccountCodeEnum::getItem("user_on_map", $input["user_on"]);
        }

        $count = $this->accountModel->getAccountPageCount($input);
        $pageobj = new Page($page, $limit, $count);
        $pageshow = $pageobj->show();

        $list = [];
        if ($count > 0) {
            $list = $this->accountModel->getAccountPageList($input, $pageobj->firstrow, $pageobj->pageSize);
            $companyIds = array_column($list->toArray(), "company_id");

            // 查询加款扣款总金额
            $statisticList = $this->accountLogModel->getAccountStatistic($companyIds);
            $statisticList = array_column($statisticList->toArray(), null, "user_id");

            foreach ($list as $key => &$item) {
                $item["deposit_money"] = floatval($item["deposit_money"]);
                $item["account_amount"] = floatval($item["account_amount"]);
                $item["back_ratio_text"] = $item["back_ratio"] ."%";

                $item["round_amount"] = 0;
                if (array_key_exists($item["company_id"], $statisticList)) {
                    $stat = $statisticList[$item->company_id];
                    $trade_recharge_amount = floatval($stat["trade_recharge_amount"]);
                    $trade_deduction_amount = floatval($stat["trade_deduction_amount"]);
                    $item["round_amount"] = floatval($trade_recharge_amount - $trade_deduction_amount);
                }
            }
        }

        return [
            "list" => $list,
            "page" => $pageshow
        ];
    }

    // 装修公司账户交易明细
    public function getCompanyAccountLogList($company_id, $input, $page = 1, $limit = 10){
        $trade_type_list = CompanyAccountCodeEnum::getList("trade_type");
        $trade_type_list = array_column($trade_type_list, null, "id");
        $input["trade_type"] = array_keys($trade_type_list);

        $count = $this->accountLogModel->getCompanyAccountLogCount($company_id, $input);
        $pageobj = new Page($page, $limit, $count);
        $pageshow = $pageobj->show();


        $list = [];
        if ($count > 0) {
            $list = $this->accountLogModel->getCompanyAccountLogList($company_id, $input, $pageobj->firstrow, $pageobj->pageSize);

            foreach ($list as $key => &$item) {
                $item["trade_date"] = date("Y-m-d", $item["created_at"]);
                $item["trade_type_name"] = $trade_type_list[$item->trade_type]["name"] ?? "";
                $item["order_type"] = CompanyAccountCodeEnum::getItem("round_amount_type", $item["round_amount_type"]);
            }
        }

        return [
            "list" => $list,
            "page" => $pageshow
        ];
    }

    // 装修公司账户交易明细统计
    public function getCompanyAccountLogTotal($company_id){
        $roundOrderAmountModel = new UserCompanyRoundOrderAmount();
        $roundAmount = $roundOrderAmountModel->getRoundOrderAmountInfo($company_id);

        $total = [];
        $total["pub_price"] = $roundAmount ? floatval($roundAmount["gz_price"]) : 0;
        $total["part_price"] = $roundAmount ? floatval($roundAmount["jg_price"]) : 0;
        $total["home_min_price"] = $roundAmount ? floatval($roundAmount["mjmin_price"]) : 0;
        $total["home_max_price"] = $roundAmount ? floatval($roundAmount["mjmax_price"]) : 0;
        $total["home_max_total_amount"] = 0;
        $total["home_min_total_amount"] = 0;
        $total["part_total_amount"] = 0;
        $total["pub_total_amount"] = 0;

        $amountTotal = $this->accountLogModel->getCompanyRoundAmountTotal($company_id);
        if (count($amountTotal) > 0) {
            foreach ($amountTotal as $key => $item) {
                $total_amount = floatval($item["deduction"]) - floatval($item["back"]);
                $total_amount = round($total_amount);

                switch ($item["round_amount_type"]) {
                    case CompanyAccountCodeEnum::ROUND_AMOUNT_TYPE_PUB:
                        $total["pub_total_amount"] = $total_amount; // 公装
                        break;
                    case CompanyAccountCodeEnum::ROUND_AMOUNT_TYPE_PART:
                        $total["part_total_amount"] = $total_amount; // 局改
                        break;
                    case CompanyAccountCodeEnum::ROUND_AMOUNT_TYPE_HOME_MIN:
                        $total["home_min_total_amount"] = $total_amount; // 家装小于等于
                        break;
                    case CompanyAccountCodeEnum::ROUND_AMOUNT_TYPE_HOME_MAX:
                        $total["home_max_total_amount"] = $total_amount; // 家装大于
                        break;
                }
            }
        }

        return $total;
    }

    public function addInfo($data)
    {
        $model = new UserCompanyAccount();
        return $model->addAccountInfo($data);
    }

    public function editInfo($company_id,$data)
    {
        $model = new UserCompanyAccount();
        return $model->editInfo($company_id,$data);
    }
}