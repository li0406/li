<?php
// +----------------------------------------------------------------------
// | CompanyLogic 会员相关数据逻辑层
// +----------------------------------------------------------------------

namespace app\index\model\logic;

use app\index\model\adb\Orders;
use app\index\model\adb\UserVip;
use app\index\model\adb\UserCompany;
use app\index\model\adb\JiajuUserVip;
use app\index\model\adb\UserCompanyExpend;
use app\index\model\adb\SaleReportCityQuote;
use app\index\model\adb\UserCompanyVipStatus;
use app\index\model\adb\UserCompanyAccountLog;
use app\index\enum\CompanyCodeEnum;
use Util\Page;

class CompanyLogic {

    protected $companyModel;

    public function __construct(){
        $this->companyModel = new UserCompany();
    }

    // 检索装修公司
    public function getCompanySearchList($input){
        $list = $this->companyModel->getCompanySearchList($input, CompanyCodeEnum::SEARCH_LIMIT);

        if (count($list) > 0) {
            foreach ($list as $key => $item) {
                $list[$key]["user_on_name"] = CompanyCodeEnum::getItem("user_on", $item["user_on"]);
                $list[$key]["cooperate_mode_name"] = CompanyCodeEnum::getItem("cooperate_mode", $item["cooperate_mode"]);
            }
        }

        return $list;
    }

    // 获取会员详情数据
    public function getCompanyDetailedList($input, $page = 1, $limit = 20){
        $count = 0;
        $offset = 0;
        $pageSize = 0;
        $pageshow = null;
        if (empty($input["export"])) {
            $count = $this->companyModel->getCompanyDetailedCount($input["datetime"], $input);
            $pageobj = new Page($page, $limit, $count);
            $pageSize = $pageobj->pageSize;
            $offset = $pageobj->firstrow;
            $pageshow = $pageobj->show();
        }

        $list = [];
        if (!empty($input["export"]) || $count > 0) {
            $list = $this->companyModel->getCompanyDetailedList($input["datetime"], $input, $offset, $pageSize);

            $companyIds = [];
            if (count($list) <= 50) {
                $companyIds = array_column($list, "company_id");
            }

            // 查询会员公司量房统计（实际点击量房时间）
            $ordersModel = new Orders();
            $lfList = $ordersModel->getCompanyLfCount($input["date_begin"], $input["date_end"], $companyIds);
            $lfList = array_column($lfList, null, "company_id");

            // 查询会员公司签单统计（签单申请时间）
            $qiandanList = $ordersModel->getCompanyQiandanCount($input["date_begin"], $input["date_end"], $companyIds);
            $qiandanList = array_column($qiandanList, null, "company_id");

            // 会员公司补轮统计
            $roundApplyLogic = new RoundOrderApplyLogic();
            $roundApplyList = $roundApplyLogic->getCompanyApplyStatistic($input["date_begin"], $input["date_end"], $companyIds);

            // 查询1.0会员累计日消耗
            $companyExpendModel = new UserCompanyExpend();
            $expendList = $companyExpendModel->getCompanyExpendAmount($input["date_begin"], $input["date_end"], $companyIds);
            $expendList = array_column($expendList, null, "company_id");

            // 查询2.0消耗金额
            $orderAmountList = $ordersModel->getCompanyOrderAmount($input["date_begin"], $input["date_end"], $companyIds);
            $orderAmountList = array_column($orderAmountList, null, "company_id");

            // 查询报备金额
            $reportCompanyLogic = new ReportCompanyLogic();
            $reportList = $reportCompanyLogic->getCompanyReportAmountTotal($input["date_begin"], $input["date_end"], $companyIds);

            foreach ($list as $key => &$item) {
                $city_id = $item["city_id"];
                $company_id = $item["company_id"];

                $item["cooperate_mode_name"] = CompanyCodeEnum::getItem("cooperate_mode", $item["cooperate_mode"]);
                $item["account_amount"] = floatval($item["account_amount"]); // 轮单费余额
                $item["deposit_amount"] = floatval($item["deposit_amount"]); // 保证金余额

                $item["all_num"] = intval($item["all_num"]); // 总单量
                $item["fen_num"] = intval($item["fen_num"]); // 实际分单量
                $item["zen_num"] = intval($item["zen_num"]); // 实际赠单量
                $item["fen_lf_num"] = intval($item["fen_lf_num"]); // 分单量房量
                $item["fen_jugai_num"] = intval($item["fen_jugai_num"]); // 局改量
                $item["fen_lf_lv"] = sys_devision($item["fen_lf_num"], $item["fen_num"], 4) * 100; // 分单量房率
                $item["fen_jugai_lv"] = sys_devision($item["fen_jugai_num"], $item["fen_num"], 4) * 100; // 局改占比

                $item["fen_qiandan_num"] = intval($item["fen_qiandan_num"]); // 分单签单量
                $item["zen_qiandan_num"] = intval($item["zen_qiandan_num"]); // 赠单签单量
                $item["fen_qiandan_amount"] = round($item["fen_qiandan_amount"], 2); // 分单签单金额（单位：万元）
                $item["fen_qiandan_lv"] = sys_devision($item["fen_qiandan_num"], $item["fen_num"], 4) * 100; // 分单签单率

                $item["lf_num"] = $lfList[$company_id]["lf_num"] ?? 0; // 量房数
                $item["lf_lv"] = sys_devision($item["lf_num"], $item["all_num"], 4) * 100; // 量房率

                $item["qiandan_num"] = $qiandanList[$company_id]["qiandan_num"] ?? 0; // 签单数
                $item["qiandan_lv"] = sys_devision($item["qiandan_num"], $item["all_num"], 4) * 100; // 签单率
                $item["qiandan_amount"] = round($qiandanList[$company_id]["qiandan_amount"] ?? 0, 2); // 签单金额（单位：万元）

                $item["round_apply_num"] = $roundApplyList[$company_id]["apply_num"] ?? 0; // 申请补轮量
                $item["round_apply_pass_num"] = $roundApplyList[$company_id]["apply_pass_num"] ?? 0; // 补轮量
                $item["round_apply_unpass_num"] = $roundApplyList[$company_id]["apply_unpass_num"] ?? 0; // 补轮驳回量
                $item["round_apply_pass_amount"] = $roundApplyList[$company_id]["apply_pass_amount"] ?? 0; // 补轮金额
                $item["round_apply_lv"] = sys_devision($item["round_apply_num"], $item["fen_num"], 4) * 100; // 申请补轮率
                $item["round_apply_pass_lv"] = sys_devision($item["round_apply_pass_num"], $item["fen_num"], 4) * 100; // 申请补轮率
                $item["round_apply_unpass_lv"] = sys_devision($item["round_apply_unpass_num"], $item["fen_num"], 4) * 100; // 申请补轮率

                $item["round_foul_num"] = $roundApplyList[$company_id]["round_foul_num"] ?? 0; // 违规补轮次数
                $item["round_foul_amount"] = $roundApplyList[$company_id]["round_foul_amount"] ?? 0; // 违规扣款金额

                $item["platform_amount"] = floatval($reportList[$company_id]["platform_amount"] ?? 0); // 平台使用费

                if ($item["cooperate_mode"] == 2) {
                    // 2.0会员的总消耗金额，会员轮单扣费金额
                    $item["expend_amount"] = floatval($orderAmountList[$company_id]["order_amount"] ?? 0);

                    // 2.0装企投入产出比=2.0总签单金额/2.0会员总消耗金额
                    $item["output_lv"] = sys_devision($item["qiandan_amount"] * 10000, $item["expend_amount"], 4) * 100;
                } else {
                    // 1.0会员的总消耗=查询时间段内，累加每个会员每天的日消耗金额
                    $item["expend_amount"] = floatval($expendList[$company_id]["expend_amount"] ?? 0);

                    // 1.0装企投入产出比=1.0总签单金额/（1.0会员数*城市报价）
                    $item["output_lv"] = sys_devision($item["qiandan_amount"] * 10000, $item["expend_amount"], 4) * 100;
                }
            }
        }

        return [
            "list" => $list,
            "page" => $pageshow
        ];
    }

    // 获取城市会员数量
    public function getCityUserCount($date, $city_ids = []){
        // 如果是当天查询实时1.0会员数
        if ($date == date("Y-m-d")) {
            $cityZxCountList = $this->companyModel->getCityUserCount($city_ids);
        } else { // 不是当天从1.0日消耗查询会员数
            $companyExpendModel = new UserCompanyExpend();
            $cityZxCountList = $companyExpendModel->getCityUserCount($date, $city_ids);
        }

        $cityZxCountList = array_column($cityZxCountList, null, "city_id");
        $cityIds = array_keys($cityZxCountList);

        // 查询2.0会员数量
        $companyVipStatusModel = new UserCompanyVipStatus();
        $cityNewUserCountList = $companyVipStatusModel->getCityNewUserCount($date, $city_ids);
        $cityNewUserCountList = array_column($cityNewUserCountList, null, "city_id");
        $cityIds = array_merge($cityIds, array_keys($cityNewUserCountList));

        // 查询全屋定制会员数量
        $jiajuUserVipModel = new JiajuUserVip();
        $quanwuList = $jiajuUserVipModel->getCityQuanwuUserCount($date, $city_ids);
        $quanwuList = array_column($quanwuList, null, "city_id");
        $cityIds = array_merge($cityIds, array_keys($quanwuList));

        $list = [];
        $cityIds = array_unique($cityIds);
        foreach ($cityIds as $key => $city_id) {
            if (!array_key_exists($city_id, $cityIds)) {
                $list[$city_id]["city_id"] = $city_id;
            }

            $list[$city_id]["vip_count"] = $cityZxCountList[$city_id]["vip_count"] ?? 0; // 1.0会员个数
            $list[$city_id]["vip_num"] = floatval($cityZxCountList[$city_id]["vip_num"] ?? 0); // 1.0会员倍数
            $list[$city_id]["signback_valid_count"] = $cityNewUserCountList[$city_id]["vip_valid_count"] ?? 0; // 2.0有效会员数
            $list[$city_id]["signback_invalid_count"] = $cityNewUserCountList[$city_id]["vip_invalid_count"] ?? 0; // 2.0无效会员数

            $list[$city_id]["vip_total_count"] = $list[$city_id]["vip_count"] + $list[$city_id]["signback_valid_count"]; // 总会员数量
            $list[$city_id]["vip_total_num"] = $list[$city_id]["vip_num"] + $list[$city_id]["signback_valid_count"]; // 总会员倍数

            $list[$city_id]["vip_quanwu_count"] = $quanwuList[$city_id]["vip_count"] ?? 0; // 全屋会员数量
            $list[$city_id]["vip_quanwu_num"] = floatval($quanwuList[$city_id]["vip_num"] ?? 0); // 全屋会员倍数
        }

        return $list;
    }

    // 获取城市1.0续费会员数量
    public function getCityUserRenewCount($date_begin, $date_end, $city_ids = []){
        $datetime = strtotime($date_begin);
        $month_begin = date("Y-m-01", $datetime);
        $month_end = date("Y-m-t", $datetime);

        $userVipModel = new UserVip();

        // 查询当月续费公司数量
        $renewList = $userVipModel->getCityVipRenewCount($date_begin, $date_end, $city_ids);
        $renewList = array_column($renewList, null, "city_id");
        $cityIds = array_keys($renewList);

        // 查询当月到期公司数量
        $expireList = $userVipModel->getCityVipExpireCount($month_begin, $month_end, $city_ids);
        $expireList = array_column($expireList, null, "city_id");
        $cityIds = array_merge($cityIds, array_keys($expireList));

        $list = [];
        $cityIds = array_unique($cityIds);
        foreach ($cityIds as $key => $city_id) {
            if (!array_key_exists($city_id, $cityIds)) {
                $list[$city_id]["city_id"] = $city_id;
            }

            $user_renew_count = intval($renewList[$city_id]["renew_count"] ?? 0); // 续费数量
            $user_expire_count = intval($expireList[$city_id]["expire_count"] ?? 0); // 过期数量
            $user_expire_count = $user_expire_count ? $user_expire_count : 1; // 过期数量为0时按1计算

            $list[$city_id]["user_renew_count"] = $user_renew_count;
            $list[$city_id]["user_expire_count"] = $user_expire_count;

            // 1.0会员续费率=当月续费的1.0会员总数/当月到期的1.0会员数*100%
            $list[$city_id]["user_renew_lv"] = sys_devision($user_renew_count, $user_expire_count, 4) * 100;
        }

        return $list;
    }

    // 获取城市2.0续费会员数量
    public function getCitySignBackRenewCount($date_begin, $date_end, $city_ids = []){

        // 查询本月续费的2.0会员数
        $accountLogModel = new UserCompanyAccountLog();
        $rechargeList = $accountLogModel->getCityAccountRechargeUserCount($date_begin, $date_end, $city_ids);
        $rechargeList = array_column($rechargeList, null, "city_id");
        $cityIds = array_keys($rechargeList);

        // 查询余额不足的2.0会员次数
        $companyVipStatusModel = new UserCompanyVipStatus();
        $notEnoughList = $companyVipStatusModel->getCityAmountNotEnoughUserCount($date_begin, $date_end, $city_ids);
        $notEnoughList = array_column($notEnoughList, null, "city_id");
        $cityIds = array_merge($cityIds, array_keys($notEnoughList));

        $list = [];
        $cityIds = array_unique($cityIds);
        foreach ($cityIds as $key => $city_id) {
            if (!array_key_exists($city_id, $cityIds)) {
                $list[$city_id]["city_id"] = $city_id;
            }

            $recharge_count = intval($rechargeList[$city_id]["recharge_count"] ?? 0);
            $not_enough_count = intval($notEnoughList[$city_id]["not_enough_count"] ?? 0);

            // 2.0会员续费率
            $list[$city_id]["signback_renew_lv"] = 100;
            $list[$city_id]["recharge_count"] = $recharge_count;
            $list[$city_id]["not_enough_count"] = $not_enough_count;
            if ($recharge_count <= $not_enough_count) {
                $list[$city_id]["signback_renew_lv"] = sys_devision($recharge_count, $not_enough_count, 4) * 100;
            }
        }

        return $list;
    }

    // 获取城市2.0续费会员数量
    public function getCitySignBackRenewCount_back($date_begin, $date_end, $city_ids = []){
        $datetime = strtotime($date_begin);
        $last_month_end = date("Y-m-t", strtotime("-1 months", $datetime));

        // 查询本月续费的2.0会员数
        $accountLogModel = new UserCompanyAccountLog();
        $rechargeList = $accountLogModel->getCityAccountRechargeUserCount($date_begin, $date_end, $city_ids);
        $rechargeList = array_column($rechargeList, null, "city_id");
        $cityIds = array_keys($notEnoughList);

        $cityIds = array_merge($cityIds, array_keys($rechargeList));



        // 查询上个月余额不足的2.0会员数
        $companyVipStatusModel = new UserCompanyVipStatus();
        $notEnoughList = $companyVipStatusModel->getCityAmountNotEnoughUserCount($last_month_end, $city_ids);
        $notEnoughList = array_column($notEnoughList, null, "city_id");
        $cityIds = array_keys($notEnoughList);

        

        $list = [];
        $cityIds = array_unique($cityIds);
        foreach ($cityIds as $key => $city_id) {
            if (!array_key_exists($city_id, $cityIds)) {
                $list[$city_id]["city_id"] = $city_id;
            }

            $recharge_count = $rechargeList[$city_id]["recharge_count"] ?? 0;
            $recharge_company_ids = $rechargeList[$city_id]["company_ids"] ?? "";
            $rechargeCompanyIds = explode(",", $recharge_company_ids);

            $not_enough_count = $notEnoughList[$city_id]["not_enough_count"] ?? 0;
            $not_enough_company_ids = $notEnoughList[$city_id]["company_ids"] ?? "";
            $notEnoughCompanyIds = explode(",", $not_enough_company_ids);

            $companyIds = array_merge($rechargeCompanyIds, $notEnoughCompanyIds);
            $amount_all_count = count(array_filter(array_unique($companyIds)));

            $list[$city_id]["recharge_count"] = $recharge_count;
            $list[$city_id]["no_enough_count"] = $not_enough_count;
            $list[$city_id]["amount_all_count"] = $amount_all_count;

            // 2.0会员续费率=当月续费的2.0会员数/(上个余额不足的2.0会员数+当月续费的2.0会员数)
            $list[$city_id]["signback_renew_lv"] = sys_devision($recharge_count, $amount_all_count, 4) * 100;
        }

        return $list;
    }
}