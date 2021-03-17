<?php
// +----------------------------------------------------------------------
// | CityLogic 城市相关数据逻辑层
// +----------------------------------------------------------------------

namespace app\index\model\logic;

use app\common\enum\BaseStatusCodeEnum;
use app\index\model\adb\Quyu;
use app\index\model\adb\Orders;
use app\index\model\adb\OrderAllotInfo;
use app\index\model\adb\CityOrderLack;
use app\index\model\adb\CityExceptUser;
use app\index\model\adb\CityOrderActual;
use app\index\model\adb\OrderBackRecord;
use app\index\model\adb\UserCompanyExpend;
use app\index\model\adb\SaleReportCityQuote;

use app\index\model\adb\UserCompanyAccountLog;

use app\index\enum\CityAreaCodeEnum;

class CityLogic {

    protected $quyuModel;

    public function __construct(){
        $this->quyuModel = new Quyu();
    }

    // 获取所有开站城市
    public function getAllOpenCity(){
        $cityList = $this->quyuModel->getAllOpenCity();

        foreach ($cityList as $key => $city) {
            $first_abc = substr($city["px_abc"], 0, 1);
            $cityList[$key]["value"] = strtoupper($first_abc) . " " . $city["city_name"];
            unset($cityList[$key]["px_abc"]);
        }

        return $cityList;
    }

    // 获取城市管辖区域
    public function getCityAreaList($city_ids = ""){
        if (!empty($city_ids) && is_string($city_ids)) {
            $city_ids = explode(",", $city_ids);
        }

        $areaList = $this->quyuModel->getCityAreaList($city_ids);

        foreach ($areaList as $key => $area) {
            $areaList[$key]["city_area"] = $area["city_name"] . "-" . $area["area_name"];
        }

        return $areaList;
    }

    // 公共数据分析-城市会员明细
    public function getCityUserDetailedList($input){
        $date_begin = $input["date_begin"];
        $date_end = $input["date_end"];
        $city_ids = $input["city_ids"];

        $month_begin = date("Y-m-01", strtotime($input["date_begin"]));

        $list = $this->quyuModel->getCityList($city_ids);
        $list = array_column($list, null, "city_id");

        if (count($list) > 0) {
            // 查询期望会员数
            $cityExceptUserModel = new CityExceptUser();
            $exceptList = $cityExceptUserModel->getCityExceptUserCount($month_begin, $city_ids);
            $exceptList = array_column($exceptList, null, "city_id");


            // 查询会员数量
            $companyLogic = new CompanyLogic();
            $companyCountList = $companyLogic->getCityUserCount($date_end, $city_ids);

            // 查询城市1.0续费会员统计
            $userRenewList = $companyLogic->getCityUserRenewCount($date_begin, $date_end, $city_ids);

            // 查询城市2.0续费会员统计
            $sbackRenewList = $companyLogic->getCitySignBackRenewCount($date_begin, $date_end, $city_ids);

            // 查询城市小报备收款金额
            $reportPaymentLogic = new ReportPaymentLogic();
            $paymentList = $reportPaymentLogic->getCityReportAmountStatistic($date_begin, $date_end, $city_ids);

            // 查询城市签单金额
            $ordersLogic = new OrdersLogic();
            $qiandanList = $ordersLogic->getCityUserQiandanAmount($date_begin, $date_end, $city_ids);

            // 获取1.0累计消耗金额
            $companyExpendModel = new UserCompanyExpend();
            $expendList = $companyExpendModel->getCityExpendAmount($date_begin, $date_end, $city_ids);
            $expendList = array_column($expendList, null, "city_id");

            // 查询2.0总消耗金额
            $ordersModel = new Orders();
            $orderAmountList = $ordersModel->getCityOrderAmount($date_begin, $date_end, $city_ids);
            $orderAmountList = array_column($orderAmountList, null, "city_id");

            $all = [
                "city_id" => "0",
                "city_name" => "汇总"
            ];

            foreach ($list as $key => $item) {
                $list[$key]["expect_user_num"] = floatval($exceptList[$key]["except_user"] ?? 0); // 期望会员数

                $list[$key]["vip_total_count"] = $companyCountList[$key]["vip_total_count"] ?? 0; // 总会员数（不算倍数）
                $list[$key]["vip_total_num"] = $companyCountList[$key]["vip_total_num"] ?? 0; // 总会员数（算倍数）
                $list[$key]["vip_count"] = $companyCountList[$key]["vip_count"] ?? 0; // 1.0会员数
                $list[$key]["vip_num"] = $companyCountList[$key]["vip_num"] ?? 0; // 1.0会员倍数
                $list[$key]["signback_valid_count"] = $companyCountList[$key]["signback_valid_count"] ?? 0; // 2.0有效会员数
                $list[$key]["signback_invalid_count"] = $companyCountList[$key]["signback_invalid_count"] ?? 0; // 2.0无效会员数
                $list[$key]["vip_quanwu_count"] = $companyCountList[$key]["vip_quanwu_count"] ?? 0; // 全屋会员数量
                $list[$key]["vip_quanwu_num"] = $companyCountList[$key]["vip_quanwu_num"] ?? 0; // 全屋会员倍数

                $list[$key]["vip_total_left"] = $list[$key]["expect_user_num"] - $list[$key]["vip_total_num"]; // 会员数量差距

                $list[$key]["zx_total_amount"] = $paymentList[$key]["zx_total_amount"] ?? 0; // 1.0总收款
                $list[$key]["signback_total_amount"] = $paymentList[$key]["signback_total_amount"] ?? 0; // 2.0总收款

                $list[$key]["vip_price"] = sys_devision($list[$key]["zx_total_amount"], $list[$key]["vip_num"], 2); // 1.0会员客单价
                $list[$key]["signback_price"] = sys_devision($list[$key]["signback_total_amount"], $list[$key]["signback_valid_count"], 2); // 2.0会员客单价

                $list[$key]["user_qiandan_amount"] = $qiandanList[$key]["user_qiandan_amount"] ?? 0; // 1.0签单金额
                $list[$key]["signback_qiandan_amount"] = $qiandanList[$key]["signback_qiandan_amount"] ?? 0; // 2.0签单金额

                $list[$key]["user_expend_amount"] = $expendList[$key]["expend_amount"] ?? 0; // 1.0会员总消耗
                $list[$key]["signback_round_amount"] = floatval($orderAmountList[$key]["order_amount"] ?? 0); // 2.0会员总消耗

                // 1.0装企投入产出比=1.0总签单金额/1.0总消耗金额
                $list[$key]["user_output_lv"] = sys_devision($list[$key]["user_qiandan_amount"], $list[$key]["user_expend_amount"], 4) * 100;

                // 2.0装企投入产出比=2.0总签单金额/2.0会员当月总消耗金额
                $list[$key]["signback_output_lv"] = sys_devision($list[$key]["signback_qiandan_amount"], $list[$key]["signback_round_amount"], 4) * 100;

                $list[$key]["user_renew_count"] = $userRenewList[$key]["user_renew_count"] ?? 0; // 1.0会员续费数量
                $list[$key]["user_expire_count"] = $userRenewList[$key]["user_expire_count"] ?? 0; // 1.0会员到期数量
                $list[$key]["user_renew_lv"] = $userRenewList[$key]["user_renew_lv"] ?? 0; // 1.0会员续费率

                $list[$key]["signback_recharge_count"] = $sbackRenewList[$key]["recharge_count"] ?? 0; // 2.0会员充值数量
                $list[$key]["signback_no_enough_count"] = $sbackRenewList[$key]["not_enough_count"] ?? 0; // 2.0会员余额不足数量
                $list[$key]["signback_renew_lv"] = $sbackRenewList[$key]["signback_renew_lv"] ?? 0; // 2.0会员续费率

                // 累加全国数据
                $all["expect_user_num"] = ($all["expect_user_num"] ?? 0) + $list[$key]["expect_user_num"];
                $all["vip_total_count"] = ($all["vip_total_count"] ?? 0) + $list[$key]["vip_total_count"];
                $all["vip_total_left"] = ($all["vip_total_left"] ?? 0) + $list[$key]["vip_total_left"];
                $all["vip_total_num"] = ($all["vip_total_num"] ?? 0) + $list[$key]["vip_total_num"];
                $all["vip_count"] = ($all["vip_count"] ?? 0) + $list[$key]["vip_count"];
                $all["vip_num"] = ($all["vip_num"] ?? 0) + $list[$key]["vip_num"];
                $all["signback_valid_count"] = ($all["signback_valid_count"] ?? 0) + $list[$key]["signback_valid_count"];
                $all["signback_invalid_count"] = ($all["signback_invalid_count"] ?? 0) + $list[$key]["signback_invalid_count"];
                $all["vip_quanwu_count"] = ($all["vip_quanwu_count"] ?? 0) + $list[$key]["vip_quanwu_count"];
                $all["vip_quanwu_num"] = ($all["vip_quanwu_num"] ?? 0) + $list[$key]["vip_quanwu_num"];
                $all["zx_total_amount"] = ($all["zx_total_amount"] ?? 0) + $list[$key]["zx_total_amount"];
                $all["signback_total_amount"] = ($all["signback_total_amount"] ?? 0) + $list[$key]["signback_total_amount"];
                $all["user_qiandan_amount"] = ($all["user_qiandan_amount"] ?? 0) + $list[$key]["user_qiandan_amount"];
                $all["signback_qiandan_amount"] = ($all["signback_qiandan_amount"] ?? 0) + $list[$key]["signback_qiandan_amount"];
                $all["signback_round_amount"] = ($all["signback_round_amount"] ?? 0) + $list[$key]["signback_round_amount"];
                $all["user_expend_amount"] = ($all["user_expend_amount"] ?? 0) + $list[$key]["user_expend_amount"];
                $all["user_renew_count"] = ($all["user_renew_count"] ?? 0) + $list[$key]["user_renew_count"];
                $all["user_expire_count"] = ($all["user_expire_count"] ?? 0) + $list[$key]["user_expire_count"];
                $all["signback_recharge_count"] = ($all["signback_recharge_count"] ?? 0) + $list[$key]["signback_recharge_count"];
                $all["signback_no_enough_count"] = ($all["signback_no_enough_count"] ?? 0) + $list[$key]["signback_no_enough_count"];
            }

            $list = array_values($list);
            if (!empty($input["sort_field"]) && !empty($input["sort_rule"])) {
                $list = sys_array_multisort($list, $input["sort_field"], $input["sort_rule"]);
            }

            $all["vip_price"] = sys_devision($all["zx_total_amount"], $all["vip_num"], 2); // 1.0会员客单价
            $all["signback_price"] = sys_devision($all["signback_total_amount"], $all["signback_valid_count"], 2); // 1.0会员客单价

            $all["user_output_lv"] = sys_devision($all["user_qiandan_amount"], $all["user_expend_amount"], 4) * 100;
            $all["signback_output_lv"] = sys_devision($all["signback_qiandan_amount"], $all["signback_round_amount"], 4) * 100;
            $all["user_renew_lv"] = sys_devision($all["user_renew_count"], $all["user_expire_count"], 4) * 100;

            $all["signback_renew_lv"] = 100;
            if ($all["signback_recharge_count"] <= $all["signback_no_enough_count"]) {
                $all["signback_renew_lv"] = sys_devision($all["signback_recharge_count"], $all["signback_no_enough_count"], 4) * 100;
            }

            array_unshift($list, $all);
        }

        return $list;
    }

    // 公共数据分析-城市分单数据明细
    public function getCityFendanDetailedList($input){
        $lack_date = $input["lack_date"];
        $date_begin = $input["date_begin"];
        $date_end = $input["date_end"];
        $city_ids = $input["city_ids"];

        $list = $this->quyuModel->getCityList($city_ids);
        $list = array_column($list, null, "city_id");

        if (count($list) > 0) {
            // 查询月
            $month_begin = date("Y-m-01", strtotime($date_end));
            $month_end = date("Y-m-t", strtotime($date_end));

            // 近三个月（不包含当月）
            $three_month_begin = date("Y-m-01", strtotime("-3 months", strtotime($date_begin)));
            $three_month_end = date("Y-m-t", strtotime("-1 months", strtotime($date_begin)));

            // 查询会员数量
            $companyLogic = new CompanyLogic();
            $companyCountList = $companyLogic->getCityUserCount($date_end, $city_ids);

            // 非跨月查询时需要查询的数据
            if ($input["in_month"] == true) {
                // 查询城市缺单统计数据
                $cityOrderLackModel = new CityOrderLack();
                $lackList = $cityOrderLackModel->getCityOrderLackDateInfo($lack_date, $city_ids);
                $lackList = array_column($lackList, null, "city_id");

                // 查询月实际所需分单数据
                $cityOrderActualModel = new CityOrderActual();
                $actualList = $cityOrderActualModel->getCityMonthList($month_begin, $city_ids);
                $actualList = array_column($actualList, null, "city_id");
            }

            // 查询城市报价
            $cityQuoteModel = new SaleReportCityQuote();
            $quoteList = $cityQuoteModel->getCityQuoteList($month_end, $city_ids);
            $quoteList = array_column($quoteList, null, "city_id");

            // 查询分单量
            $ordersModel = new Orders();
            $fenList = $ordersModel->getAreaFendanStatistic($date_begin, $date_end, $city_ids);
            $fenList = $this->setCityAreaGroup(CityAreaCodeEnum::TAB_CITY, $fenList);

            // 查询2.0分单量
            $sbackFendanList = $ordersModel->getCitySbackFendan($date_begin, $date_end, $city_ids);
            $sbackFendanList = array_column($sbackFendanList, null, "city_id");

            // 查询撤回单量
            $orderRecallList = $ordersModel->getOrderRecallStatistic($date_begin, $date_end, $city_ids);
            $orderRecallList = array_column($orderRecallList, null, "city_id");

            // 查询撤回次数
            $orderBackRecordModel = new OrderBackRecord();
            $backList = $orderBackRecordModel->getCityBackCount($date_begin, $date_end, $city_ids);
            $backList = array_column($backList, null, "city_id");

            // 查询订单分配信息
            $orderAllotInfoModel = new OrderAllotInfo();
            $orderAllotList = $orderAllotInfoModel->getCityOrderAllotList($date_begin, $date_end, $city_ids);
            $orderAllotList = array_column($orderAllotList, null, "city_id");

            // 获取1.0累计消耗金额
            $companyExpendModel = new UserCompanyExpend();
            $expendList = $companyExpendModel->getCityExpendAmount($date_begin, $date_end, $city_ids);
            $expendList = array_column($expendList, null, "city_id");

            // 查询2.0累计消耗金额
            $orderAmountList = $ordersModel->getCityOrderAmount($date_begin, $date_end, $city_ids);
            $orderAmountList = array_column($orderAmountList, null, "city_id");

            // 查询三个月总收款/总充值
            $reportCompanyLogic = new ReportCompanyLogic();
            $reportAmountList = $reportCompanyLogic->getCityReportAmountTotal($three_month_begin, $three_month_end, $city_ids);
            $reportAmountList = array_column($reportAmountList, null, "city_id");

            // 补轮相关数据查询
            $roundOrderApplyLogic = new RoundOrderApplyLogic();
            $roundApplyList = $roundOrderApplyLogic->getCityRoundApplyStatistic($date_begin, $date_end, $city_ids);

            $all = [
                "city_id" => "0",
                "city_name" => "汇总"
            ];

            foreach ($list as $key => $item) {
                $list[$key]["vip_total_count"] = $companyCountList[$key]["vip_total_count"] ?? 0; // 会员数
                $list[$key]["city_order_price"] = floatval($quoteList[$key]["round_order_money"] ?? 0); // 城市2.0单价
                $list[$key]["csos_fendan"] = $fenList[$key]["csos_fendan"] ?? 0; // 实际分单量
                $list[$key]["csos_all"] = $fenList[$key]["csos_all"] ?? 0; // 总单量
                $list[$key]["sback_fendan"] = $sbackFendanList[$key]["fendan"] ?? 0; // 2.0实际分单量

                if ($input["in_month"] == true) {
                    // 月所需分单量
                    $list[$key]["month_need_orders"] = floatval($actualList[$key]["actual_orders"] ?? 0);
                    if ($list[$key]["month_need_orders"] == 0) {
                        $list[$key]["month_need_orders"] = floatval($lackList[$key]["predict_whole_month_orders"] ?? 0);
                    }

                    // 实际缺单量：实际缺单量=月所需分单量-实际分单量；
                    $list[$key]["csos_quedan"] = round($list[$key]["month_need_orders"] - $list[$key]["csos_fendan"], 2);
                } else {
                    $list[$key]["month_need_orders"] = "-";
                    $list[$key]["csos_quedan"] = "-";
                }

                $list[$key]["month3_chongzhi"] = floatval($reportAmountList[$key]["round_amount"] ?? 0); // 3个月总充值
                $list[$key]["month3_shoukuan"] = floatval($reportAmountList[$key]["total_round_amount"] ?? 0); // 3个月总收款
                $list[$key]["month_expend_amount"] = floatval($expendList[$key]["expend_amount"] ?? 0); // 1.0累计月消耗
                $list[$key]["month_round_amount"] = floatval($orderAmountList[$key]["order_amount"] ?? 0); // 2.0累计月消耗
                $list[$key]["month_expend"] = $list[$key]["month_expend_amount"] + $list[$key]["month_round_amount"]; // 当月总扣款(总消耗金额)

                // 实际订单售价
                $list[$key]["order_price"] = 0;
                if ($list[$key]["csos_fendan"] > 0){
                    $month3_money = 1;
                    if ($list[$key]["month3_shoukuan"] > 0 && $list[$key]["month3_chongzhi"] > 0) {
                        $month3_money = $list[$key]["month3_shoukuan"] / $list[$key]["month3_chongzhi"];
                    }
 
                    // 实际订单售价=当月总扣款*（3个月总收款/3个月总充值）/(城市订单价格*4*当月实际分单量)*城市订单价格；
                    $list[$key]["order_price"] = $list[$key]["month_expend"] * $month3_money / (4 * $list[$key]["csos_fendan"]);
                    $list[$key]["order_price"] = round($list[$key]["order_price"], 2);
                }

                $list[$key]["recall_num"] = $backList[$key]["back_num"] ?? 0; // 撤回次数
                $list[$key]["recall_count"] = $orderRecallList[$key]["count"] ?? 0; // 撤回单量

                $list[$key]["recall_lv"] = sys_devision($list[$key]["recall_num"], $list[$key]["recall_count"] + $list[$key]["csos_all"], 4) * 100; // 撤回占比

                $list[$key]["allot_max_num"] = $orderAllotList[$key]["allot_max_num"] ?? 0; // 理论分配数
                $list[$key]["allot_fen_num"] = $orderAllotList[$key]["allot_fen_num"] ?? 0; // 实际分配数
                $list[$key]["allot_fen_user_num"] = $orderAllotList[$key]["allot_fen_user_num"] ?? 0; // 以分单形式分给1.0会员的总数量
                $list[$key]["allot_fen_newuser_num"] = $orderAllotList[$key]["allot_fen_newuser_num"] ?? 0; // 以分单形式分给2.0会员的总数量
                $list[$key]["allot_waste_num"] = $list[$key]["allot_max_num"] - $list[$key]["allot_fen_num"]; // 分配浪费数
                $list[$key]["allot_waste_lv"] = sys_devision($list[$key]["allot_waste_num"], $list[$key]["allot_max_num"], 4) * 100; //
                // 分单浪费率

                // 1.0分单客单价：1.0会员分单客单价=1.0会员总消耗金额/以分单形式分配给1.0会员的分单次数；
                $list[$key]["user_price"] = sys_devision($list[$key]["month_expend_amount"], $list[$key]["allot_fen_user_num"], 2);

                // 2.0会员分单客单价=2.0会员总消耗金额/以分单形式分配给2.0会员的分单次数；单位：元；保留2位小数；
                $list[$key]["newuser_price"] = sys_devision($list[$key]["month_round_amount"], $list[$key]["allot_fen_newuser_num"], 2);

                $list[$key]["apply_order_num"] = $roundApplyList[$key]["apply_order_num"] ?? 0; // 申请补轮量
                $list[$key]["apply_company_num"] = $roundApplyList[$key]["apply_company_num"] ?? 0; // 申请补轮次数
                $list[$key]["apply_pass_num"] = intval($roundApplyList[$key]["apply_pass_num"] ?? 0); // 补轮量
                $list[$key]["apply_company_pass_num"] = $roundApplyList[$key]["apply_company_pass_num"] ?? 0; // 补轮次数
                $list[$key]["round_pass_money"] = $roundApplyList[$key]["round_pass_money"] ?? 0; // 补轮金额

                $list[$key]["apply_unpass_num"] = $roundApplyList[$key]["apply_unpass_num"] ?? 0; // 补轮驳回单量
                $list[$key]["apply_company_unpass_num"] = $roundApplyList[$key]["apply_company_unpass_num"] ?? 0; // 补轮驳回次数
                $list[$key]["round_unpass_money"] = $roundApplyList[$key]["round_unpass_money"] ?? 0; // 补轮驳回金额

                $list[$key]["round_foul_num"] = $accountLogList[$key]["round_foul_num"] ?? 0; // 违规补轮次数
                // 违规补轮占比（分母为分配给2.0次数）
                $list[$key]["round_foul_lv"] = sys_devision($list[$key]["round_foul_num"], $list[$key]["allot_fen_newuser_num"], 4) * 100;

                // 申请补轮率：申请补轮率=申请补轮次数/实际分配数
                $list[$key]["apply_company_lv"] = sys_devision($list[$key]["apply_company_num"], $list[$key]["allot_fen_num"], 4) * 100;

                // 补轮率：补轮率=补轮量/实际分单量*100%；保留2位小数
                $list[$key]["apply_pass_lv"] = sys_devision($list[$key]["apply_pass_num"], $list[$key]["csos_fendan"], 4) * 100;

                // 2.0补轮率：补轮率=补轮量/实际分单量*100%；保留2位小数
                $list[$key]["apply_sback_pass_lv"] = sys_devision($list[$key]["apply_pass_num"], $list[$key]["sback_fendan"], 4) * 100;

                // 补轮驳回率：补轮驳回率=补轮驳回次数/实际分配数
                $list[$key]["apply_company_unpass_lv"] = sys_devision($list[$key]["apply_company_unpass_num"], $list[$key]["allot_fen_num"], 4) * 100;
                
                // 累加全国数据
                $all["vip_total_count"] = ($all["vip_total_count"] ?? 0) + $list[$key]["vip_total_count"];
                $all["city_order_price"] = ($all["city_order_price"] ?? 0) + $list[$key]["city_order_price"];
                $all["csos_fendan"] = ($all["csos_fendan"] ?? 0) + $list[$key]["csos_fendan"];
                $all["csos_all"] = ($all["csos_all"] ?? 0) + $list[$key]["csos_all"];
                $all["sback_fendan"] = ($all["sback_fendan"] ?? 0) + $list[$key]["sback_fendan"];

                if ($input["in_month"] == true) {
                    $all["month_need_orders"] = ($all["month_need_orders"] ?? 0) + $list[$key]["month_need_orders"];
                    $all["csos_quedan"] = ($all["csos_quedan"] ?? 0) + $list[$key]["csos_quedan"];
                } else {
                    $all["month_need_orders"] = "-";
                    $all["csos_quedan"] = "-";
                }

                // 三个月总收款和总充值之累加两个数据都有的
                if ($list[$key]["month3_shoukuan"] > 0 && $list[$key]["month3_chongzhi"] > 0) {
                    $all["month3_chongzhi"] = ($all["month3_chongzhi"] ?? 0) + $list[$key]["month3_chongzhi"];
                    $all["month3_shoukuan"] = ($all["month3_shoukuan"] ?? 0) + $list[$key]["month3_shoukuan"];
                } else {
                    $all["month3_chongzhi"] = ($all["month3_chongzhi"] ?? 0) + 0;
                    $all["month3_shoukuan"] = ($all["month3_shoukuan"] ?? 0) + 0;
                }

                $all["month_expend_amount"] = ($all["month_expend_amount"] ?? 0) + $list[$key]["month_expend_amount"];
                $all["month_round_amount"] = ($all["month_round_amount"] ?? 0) + $list[$key]["month_round_amount"];
                $all["month_expend"] = ($all["month_expend"] ?? 0) + $list[$key]["month_expend"];
                $all["recall_num"] = ($all["recall_num"] ?? 0) + $list[$key]["recall_num"];
                $all["recall_count"] = ($all["recall_count"] ?? 0) + $list[$key]["recall_count"];
                $all["allot_max_num"] = ($all["allot_max_num"] ?? 0) + $list[$key]["allot_max_num"];
                $all["allot_fen_num"] = ($all["allot_fen_num"] ?? 0) + $list[$key]["allot_fen_num"];
                $all["allot_waste_num"] = ($all["allot_waste_num"] ?? 0) + $list[$key]["allot_waste_num"];
                $all["allot_fen_user_num"] = ($all["allot_fen_user_num"] ?? 0) + $list[$key]["allot_fen_user_num"];
                $all["allot_fen_newuser_num"] = ($all["allot_fen_newuser_num"] ?? 0) + $list[$key]["allot_fen_newuser_num"];
                $all["apply_order_num"] = ($all["apply_order_num"] ?? 0) + $list[$key]["apply_order_num"];
                $all["apply_company_num"] = ($all["apply_company_num"] ?? 0) + $list[$key]["apply_company_num"];
                $all["apply_pass_num"] = ($all["apply_pass_num"] ?? 0) + $list[$key]["apply_pass_num"];
                $all["apply_company_pass_num"] = ($all["apply_company_pass_num"] ?? 0) + $list[$key]["apply_company_pass_num"];
                $all["round_pass_money"] = ($all["round_pass_money"] ?? 0) + $list[$key]["round_pass_money"];
                $all["apply_unpass_num"] = ($all["apply_unpass_num"] ?? 0) + $list[$key]["apply_unpass_num"];
                $all["apply_company_unpass_num"] = ($all["apply_company_unpass_num"] ?? 0) + $list[$key]["apply_company_unpass_num"];
                $all["round_unpass_money"] = ($all["round_unpass_money"] ?? 0) + $list[$key]["round_unpass_money"];
                $all["round_foul_num"] = ($all["round_foul_num"] ?? 0) + $list[$key]["round_foul_num"];
                $all["order_price"] = ($all["order_price"] ?? 0) + $list[$key]["order_price"];
            }

            $count = count($list);
            $list = array_values($list);
            if (!empty($input["sort_field"]) && !empty($input["sort_rule"])) {
                $list = sys_array_multisort($list, $input["sort_field"], $input["sort_rule"]);
            }

            // 加入全国数据
            // $all["order_price"] = sys_devision($all["order_price"], $count, 2); // 实际订单售价
            $all["city_order_price"] = sys_devision($all["city_order_price"], $count, 2); // 城市2.0单价
            $all["user_price"] = sys_devision($all["month_expend_amount"], $all["allot_fen_user_num"], 2);
            $all["newuser_price"] = sys_devision($all["month_round_amount"], $all["allot_fen_newuser_num"], 2);
            $all["recall_lv"] = sys_devision($all["recall_num"], $all["recall_count"] + $all["csos_all"], 4) * 100; // 撤回占比
            $all["allot_waste_lv"] = sys_devision($all["allot_waste_num"], $all["allot_max_num"], 4) * 100; // 分单浪费率

            $all["apply_company_lv"] = sys_devision_ratio($all["apply_company_num"], $all["allot_fen_num"], 4) * 100;
            $all["apply_pass_lv"] = sys_devision_ratio($all["apply_pass_num"], $all["csos_fendan"], 4) * 100;
            $all["apply_sback_pass_lv"] = sys_devision_ratio($all["apply_pass_num"], $all["sback_fendan"], 4) * 100;
            $all["apply_company_unpass_lv"] = sys_devision_ratio($all["apply_company_unpass_num"], $all["allot_fen_num"], 4) * 100;

            $all["round_foul_lv"] = sys_devision($all["round_foul_num"], $all["allot_fen_newuser_num"], 4) * 100;

            // 实际订单售价
            $all["order_price"] = 0;
            if ($all["csos_fendan"] > 0){
                $month3_all_money = 1;
                if ($all["month3_chongzhi"] > 0 && $all["month3_shoukuan"] > 0) {
                    $month3_all_money = $all["month3_shoukuan"] / $all["month3_chongzhi"];
                }

                // 实际订单售价=当月总扣款*（3个月总收款/3个月总充值）/(城市订单价格*4*当月实际分单量)*城市订单价格；
                $all["order_price"] = $all["month_expend"] * $month3_all_money / (4 * $all["csos_fendan"]);
                $all["order_price"] = round($all["order_price"], 2);
            }

            array_unshift($list, $all);
        }

        return $list;
    }

    // 公共数据分析-城市数据明细
    public function getCityOrderDetailedList($tab, $input){
        $date_begin = $input["date_begin"];
        $date_end = $input["date_end"];
        $city_ids = $input["city_ids"];
        $area_ids = $input["area_ids"];

        // 城市区域数据查询
        if ($tab == CityAreaCodeEnum::TAB_AREA) { // 按区域
            $list = $this->quyuModel->getCityAreaList($city_ids, $area_ids);
            $list = $this->setCityAreaGroup($tab, $list);
        } else { // 按城市
            $list = $this->quyuModel->getCityList($city_ids);
            $list = array_column($list, null, "city_id");
        }

        // 聚合数据
        if (count($list) > 0) {
            // 发单相关数据查询
            $ordersLogic = new OrdersLogic();
            $fadanList = $ordersLogic->getCityAreaFadanStatistic($date_begin, $date_end, $city_ids, $area_ids);
            $fadanList = $this->setCityAreaGroup($tab, $fadanList);

            // 分单相关数据查询
            $fendanList = $ordersLogic->getCityAreaFendanStatistic($date_begin, $date_end, $city_ids, $area_ids);
            $fendanList = $this->setCityAreaGroup($tab, $fendanList);

            $telcenterList = [];
            // 如果显示发单相关数据则查询呼叫相关统计
            if ($input["show_fadan"] == CityAreaCodeEnum::FIELD_SHOW) {
                $telcenterLogic = new TelcenterLogic();
                $telcenterList = $telcenterLogic->getCityAreaOrderTelcenterStatistic($date_begin, $date_end, $city_ids, $area_ids);
                $telcenterList = $this->setCityAreaGroup($tab, $telcenterList);
            }

            $mianjiList = [];
            // 如果显示面积数据则取查询面积统计
            if ($input["show_mianji"] == CityAreaCodeEnum::FIELD_SHOW) {
                $mianjiList = $ordersLogic->getCityAreaMianjiStatistic($date_begin, $date_end, $city_ids, $area_ids);
                $mianjiList = $this->setCityAreaGroup($tab, $mianjiList);
            }

            // 分单时间计算量房量
            $fenLfList = $ordersLogic->getCityAreaFenLfStatistic($date_begin, $date_end, $city_ids, $area_ids);
            $fenLfList = $this->setCityAreaGroup($tab, $fenLfList);

            // 用户点击量房时间计算量房量
            $realLfList = $ordersLogic->getCityAreaRealLfStatistic($date_begin, $date_end, $city_ids, $area_ids);
            $realLfList = $this->setCityAreaGroup($tab, $realLfList);

            $qiandanList = [];
            // 申请签单时间计算签单量
            if ($input["show_qiandan"] == CityAreaCodeEnum::FIELD_SHOW) {
                $qiandanList = $ordersLogic->getCityAreaQiandanStatistic($date_begin, $date_end, $city_ids, $area_ids);
                $qiandanList = $this->setCityAreaGroup($tab, $qiandanList);
            }

            $achievementList = [];
            // 业绩查询（只有城市有业绩）
            if ($tab == CityAreaCodeEnum::TAB_CITY && $input["show_achievement"] == CityAreaCodeEnum::FIELD_SHOW) {
                $reportPaymentLogic = new ReportPaymentLogic();
                $achievementList = $reportPaymentLogic->getCityAchievementStatistic($date_begin, $date_end, $city_ids);
                $achievementList = array_column($achievementList, null, "city_id");
            }

            $all = [
                "city_id" => "0",
                "city_name" => "汇总"
            ];

            $decimal = 4; // 小数精确位数
            foreach ($list as $key => $item) {
                // 按发单时间统计订单结果
                $list[$key]["fadan"] = $fadanList[$key]["fadan"] ?? 0; // 发单量
                $list[$key]["fendan"] = $fadanList[$key]["fendan"] ?? 0; // 分单量
                $list[$key]["zendan"] = $fadanList[$key]["zendan"] ?? 0; // 赠单量
                $list[$key]["paidan"] = $fadanList[$key]["paidan"] ?? 0; // 派单量
                $list[$key]["fendan_lv"] = sys_devision_ratio($list[$key]["fendan"], $list[$key]["fadan"], $decimal) * 100;

                // 按发单时间统计呼叫结果
                $list[$key]["yihu"] = $telcenterList[$key]["yihu"] ?? 0; // 已呼
                $list[$key]["weihu"] = $telcenterList[$key]["weihu"] ?? 0; // 未呼
                $list[$key]["hutong"] = $telcenterList[$key]["hutong"] ?? 0; // 呼通
                $list[$key]["hutong_lv"] = sys_devision_ratio($list[$key]["hutong"], $list[$key]["paidan"], $decimal) * 100; // 呼通率


                // 按分单时间统计结果
                $list[$key]["csos_all"] = $fendanList[$key]["csos_all"] ?? 0; // 总单量
                $list[$key]["csos_fendan"] = $fendanList[$key]["csos_fendan"] ?? 0; // 实际分单
                $list[$key]["csos_zendan"] = $fendanList[$key]["csos_zendan"] ?? 0; // 实际赠单
                $list[$key]["csos_fen_qiandan"] = $fendanList[$key]["csos_fen_qiandan"] ?? 0; // 实际分单签单量
                $list[$key]["csos_zen_qiandan"] = $fendanList[$key]["csos_zen_qiandan"] ?? 0; // 实际赠单签单量
                $list[$key]["csos_jugai"] = $fendanList[$key]["csos_jugai"] ?? 0; // 实际局改
                $list[$key]["csos_jiazhuang"] = $fendanList[$key]["csos_jiazhuang"] ?? 0; // 实际家装
                $list[$key]["csos_gongzhuang"] = $fendanList[$key]["csos_gongzhuang"] ?? 0; // 实际公装
                $list[$key]["csos_banbao"] = $fendanList[$key]["csos_banbao"] ?? 0; // 实际半包
                $list[$key]["csos_quanbao"] = $fendanList[$key]["csos_quanbao"] ?? 0; // 实际全包
                $list[$key]["csos_qingbao"] = $fendanList[$key]["csos_qingbao"] ?? 0; // 实际清包
                $list[$key]["csos_mianyi"] = $fendanList[$key]["csos_mianyi"] ?? 0; // 实际面议

                $list[$key]["csos_fen_lfnum"] = $fenLfList[$key]["csos_fen_lfnum"] ?? 0; // 分单量房量
                $list[$key]["csos_zen_lfnum"] = $fenLfList[$key]["csos_zen_lfnum"] ?? 0; // 赠单量房量

                $list[$key]["real_lfnum"] = $realLfList[$key]["real_lfnum"] ?? 0; // 量房量
                $list[$key]["real_unlfnum"] = $realLfList[$key]["real_unlfnum"] ?? 0; // 未量房量

                $list[$key]["qiandan"] = $qiandanList[$key]["qiandan"] ?? 0; // 签单量

                // 面积
                $list[$key]["mianji_40"] = $mianjiList[$key]["mianji_40"] ?? 0;
                $list[$key]["mianji_60"] = $mianjiList[$key]["mianji_60"] ?? 0;
                $list[$key]["mianji_80"] = $mianjiList[$key]["mianji_80"] ?? 0;
                $list[$key]["mianji_100"] = $mianjiList[$key]["mianji_100"] ?? 0;
                $list[$key]["mianji_120"] = $mianjiList[$key]["mianji_120"] ?? 0;
                $list[$key]["mianji_150"] = $mianjiList[$key]["mianji_150"] ?? 0;
                $list[$key]["mianji_200"] = $mianjiList[$key]["mianji_200"] ?? 0;
                $list[$key]["mianji_max"] = $mianjiList[$key]["mianji_max"] ?? 0;

                $list[$key]["csos_fendan_lv"] = sys_devision_ratio($list[$key]["csos_fendan"], $list[$key]["fadan"], $decimal)
                    * 100; // 实际分单率
                $list[$key]["csos_fen_qiandan_lv"] = sys_devision_ratio($list[$key]["csos_fen_qiandan"], $list[$key]["csos_fendan"], $decimal)
                    * 100; // 分单签单率
                $list[$key]["csos_zen_qiandan_lv"] = sys_devision_ratio($list[$key]["csos_zen_qiandan"], $list[$key]["csos_zendan"], $decimal)
                    * 100; // 赠单签单率
                $list[$key]["csos_jugai_lv"] = sys_devision_ratio($list[$key]["csos_jugai"], $list[$key]["csos_fendan"], $decimal)
                    * 100; // 局改占比
                $list[$key]["csos_fen_lflv"] = sys_devision_ratio($list[$key]["csos_fen_lfnum"], $list[$key]["csos_fendan"], $decimal)
                    * 100; // 分单量房率
                $list[$key]["csos_zen_lflv"] = sys_devision_ratio($list[$key]["csos_zen_lfnum"], $list[$key]["csos_zendan"], $decimal) * 100;
                // 赠单量房率
                $list[$key]["real_lflv"] = sys_devision_ratio($list[$key]["real_lfnum"], $list[$key]["csos_all"], $decimal) * 100; // 量房率
                $list[$key]["qiandan_lv"] = sys_devision_ratio($list[$key]["qiandan"], $list[$key]["csos_all"], $decimal) * 100; // 签单率
                $list[$key]["lf_qiandan_lv"] = sys_devision_ratio($list[$key]["qiandan"], $list[$key]["real_lfnum"], $decimal) * 100; // 量房签单率

                $list[$key]["achievement"] = round($achievementList[$key]["achievement"] ?? 0, 2); // 业绩

                // 累加列表数据
                $all["fadan"] = ($all["fadan"] ?? 0) + $list[$key]["fadan"];
                $all["fendan"] = ($all["fendan"] ?? 0) + $list[$key]["fendan"];
                $all["zendan"] = ($all["zendan"] ?? 0) + $list[$key]["zendan"];
                $all["paidan"] = ($all["paidan"] ?? 0) + $list[$key]["paidan"];
                $all["yihu"] = ($all["yihu"] ?? 0) + $list[$key]["yihu"];
                $all["weihu"] = ($all["weihu"] ?? 0) + $list[$key]["weihu"];
                $all["hutong"] = ($all["hutong"] ?? 0) + $list[$key]["hutong"];

                $all["csos_all"] = ($all["csos_all"] ?? 0) + $list[$key]["csos_all"];
                $all["csos_fendan"] = ($all["csos_fendan"] ?? 0) + $list[$key]["csos_fendan"];
                $all["csos_zendan"] = ($all["csos_zendan"] ?? 0) + $list[$key]["csos_zendan"];
                $all["csos_fen_qiandan"] = ($all["csos_fen_qiandan"] ?? 0) + $list[$key]["csos_fen_qiandan"];
                $all["csos_zen_qiandan"] = ($all["csos_zen_qiandan"] ?? 0) + $list[$key]["csos_zen_qiandan"];
                $all["csos_jugai"] = ($all["csos_jugai"] ?? 0) + $list[$key]["csos_jugai"];
                $all["csos_jiazhuang"] = ($all["csos_jiazhuang"] ?? 0) + $list[$key]["csos_jiazhuang"];
                $all["csos_gongzhuang"] = ($all["csos_gongzhuang"] ?? 0) + $list[$key]["csos_gongzhuang"];
                $all["csos_banbao"] = ($all["csos_banbao"] ?? 0) + $list[$key]["csos_banbao"];
                $all["csos_quanbao"] = ($all["csos_quanbao"] ?? 0) + $list[$key]["csos_quanbao"];
                $all["csos_qingbao"] = ($all["csos_qingbao"] ?? 0) + $list[$key]["csos_qingbao"];
                $all["csos_mianyi"] = ($all["csos_mianyi"] ?? 0) + $list[$key]["csos_mianyi"];

                $all["csos_fen_lfnum"] = ($all["csos_fen_lfnum"] ?? 0) + $list[$key]["csos_fen_lfnum"];
                $all["csos_zen_lfnum"] = ($all["csos_zen_lfnum"] ?? 0) + $list[$key]["csos_zen_lfnum"];
                $all["real_unlfnum"] = ($all["real_unlfnum"] ?? 0) + $list[$key]["real_unlfnum"];
                $all["real_lfnum"] = ($all["real_lfnum"] ?? 0) + $list[$key]["real_lfnum"];
                $all["qiandan"] = ($all["qiandan"] ?? 0) + $list[$key]["qiandan"];

                $all["mianji_40"] = ($all["mianji_40"] ?? 0) + $list[$key]["mianji_40"];
                $all["mianji_60"] = ($all["mianji_60"] ?? 0) + $list[$key]["mianji_60"];
                $all["mianji_80"] = ($all["mianji_80"] ?? 0) + $list[$key]["mianji_80"];
                $all["mianji_100"] = ($all["mianji_100"] ?? 0) + $list[$key]["mianji_100"];
                $all["mianji_120"] = ($all["mianji_120"] ?? 0) + $list[$key]["mianji_120"];
                $all["mianji_150"] = ($all["mianji_150"] ?? 0) + $list[$key]["mianji_150"];
                $all["mianji_200"] = ($all["mianji_200"] ?? 0) + $list[$key]["mianji_200"];
                $all["mianji_max"] = ($all["mianji_max"] ?? 0) + $list[$key]["mianji_max"];

                $all["achievement"] = ($all["achievement"] ?? 0) + $list[$key]["achievement"];
            }

            $list = array_values($list);
            if (!empty($input["sort_field"]) && !empty($input["sort_rule"])) {
                $list = sys_array_multisort($list, $input["sort_field"], $input["sort_rule"]);
            }

            // 加入全国数据
            if ($tab == CityAreaCodeEnum::TAB_CITY){
                $all["fendan_lv"] = sys_devision_ratio($all["fendan"], $all["fadan"], $decimal) * 100;
                $all["hutong_lv"] = sys_devision_ratio($all["hutong"], $all["paidan"], $decimal) * 100;
                $all["csos_fendan_lv"] = sys_devision_ratio($all["csos_fendan"], $all["fadan"], $decimal)* 100;
                $all["csos_fen_qiandan_lv"] = sys_devision_ratio($all["csos_fen_qiandan"], $all["csos_fendan"], $decimal) * 100;
                $all["csos_zen_qiandan_lv"] = sys_devision_ratio($all["csos_zen_qiandan"], $all["csos_zendan"], $decimal) * 100;
                $all["csos_jugai_lv"] = sys_devision_ratio($all["csos_jugai"], $all["csos_fendan"], $decimal) * 100;
                $all["csos_fen_lflv"] = sys_devision_ratio($all["csos_fen_lfnum"], $all["csos_fendan"], $decimal) * 100;
                $all["csos_zen_lflv"] = sys_devision_ratio($all["csos_zen_lfnum"], $all["csos_zendan"], $decimal) * 100;
                $all["real_lflv"] = sys_devision_ratio($all["real_lfnum"], $all["csos_all"], $decimal) * 100;
                $all["qiandan_lv"] = sys_devision_ratio($all["qiandan"], $all["csos_all"], $decimal) * 100;
                $all["lf_qiandan_lv"] = sys_devision_ratio($all["qiandan"], $all["real_lfnum"], $decimal) * 100;

                array_unshift($list, $all);
            }
        }

        return $list;
    }

    // 全国城市重点数据
    public function getCityImportantList($date_begin, $date_end, $city_ids = []){
        // 获取本月业绩前20的城市以及业绩金额
        $reportPaymentLogic = new ReportPaymentLogic();
        $list = $reportPaymentLogic->getCityAchievementTop($date_begin, $date_end, $city_ids);

        if (count($list) > 0) {
            $cityIds = array_column($list, "city_id");

            $ordersModel = new Orders();

            // 查询总单量（实际分单时间）
            $fendanList = $ordersModel->getAreaFendanStatistic($date_begin, $date_end, $cityIds);
            $fendanList = $this->setCityAreaGroup(CityAreaCodeEnum::TAB_CITY, $fendanList);

            // 查询量房量（装企点击量房时间）
            $lfList = $ordersModel->getAreaRealLfStatistic($date_begin, $date_end, $cityIds);
            $lfList = $this->setCityAreaGroup(CityAreaCodeEnum::TAB_CITY, $lfList);

            // 查询签单量（签单申请时间）
            $qdList = $ordersModel->getAreaQiandanStatistic($date_begin, $date_end, $cityIds);
            $qdList = $this->setCityAreaGroup(CityAreaCodeEnum::TAB_CITY, $qdList);

            foreach ($list as $key => &$item) {
                $city_id = $item["city_id"];
                $item["achievement"] = floatval($item["achievement"]);

                $item["csos_all"] = floatval($fendanList[$city_id]["csos_all"] ?? 0); // 总单量
                $item["csos_fen"] = floatval($fendanList[$city_id]["csos_fendan"] ?? 0); // 实际分单量

                // 量房量/量房率
                $item["lf_num"] = $lfList[$city_id]["real_lfnum"] ?? 0;
                $item["lf_lv"] = sys_devision_ratio($item["lf_num"], $item["csos_all"], 4) * 100;
                $item["lf_lv_show"] = $item["lf_lv"] . "%";

                    // 签单量/签单率
                $item["qiandan_num"] = $qdList[$city_id]["qiandan"] ?? 0;
                $item["qiandan_lv"] = sys_devision_ratio($item["qiandan_num"], $item["csos_all"], 4) * 100;
                $item["qiandan_lv_show"] = $item["qiandan_lv"] . "%";
            }
        }

        return $list;
    }

    // 当月订单不足城市（需要全国数据）
    public function getCityOrderLackList($date_begin, $date_end, $city_ids = []){
        $list = $this->quyuModel->getCityList($city_ids);

        if (count($list) > 0) {
            // 城市缺单统计查询日期
            $lack_date = $date_end;
            if (strtotime($date_end) >= strtotime(date("Y-m-d"))) {
                $lack_date = date("Y-m-d");
                if (date("H") < 12) {
                    $lack_date = date("Y-m-d", strtotime("-1 day"));
                }
            }

            // 查询城市缺单统计数据
            $cityOrderLackModel = new CityOrderLack();
            $lackList = $cityOrderLackModel->getCityOrderLackDateInfo($lack_date, $city_ids);
            $lackList = array_column($lackList, null, "city_id");

            // 查询月实际所需分单数据
            $cityOrderActualModel = new CityOrderActual();
            $actualList = $cityOrderActualModel->getCityMonthList($date_begin, $city_ids);
            $actualList = array_column($actualList, null, "city_id");

            $ordersModel = new Orders();
            $fenList = $ordersModel->getAreaFendanStatistic($date_begin, $date_end, $city_ids);
            $fenList = $this->setCityAreaGroup(CityAreaCodeEnum::TAB_CITY, $fenList);

            $all = [
                "city_id" => "000001",
                "city_name" => "全国",
                "predict_orders" => 0,
                "fen_orders" => 0,
                "lack_orders" => 0,
            ];

            foreach ($list as $key => $item) {
                $city_id = $item["city_id"];

                // 月所需分单量
                $list[$key]["predict_orders"] = floatval($actualList[$city_id]["actual_orders"] ?? 0);
                if ($list[$key]["predict_orders"] == 0) {
                    $list[$key]["predict_orders"] = floatval($lackList[$city_id]["predict_whole_month_orders"] ?? 0);
                }

                $list[$key]["fen_orders"] = floatval($fenList[$city_id]["csos_fendan"] ?? 0); // 实际分单量
                $list[$key]["lack_orders"] = round($list[$key]["predict_orders"] - $list[$key]["fen_orders"], 2); // 实际缺单量

                $all["predict_orders"] = round($all["predict_orders"] + $list[$key]["predict_orders"], 2);
                $all["fen_orders"] = round($all["fen_orders"] + $list[$key]["fen_orders"], 2);

                if ($list[$key]["lack_orders"] > 0) {
                    $all["lack_orders"] = round($all["lack_orders"] + $list[$key]["lack_orders"], 2);
                }

                unset($list[$key]["bm"], $list[$key]["px_abc"]);
            }

            $list = sys_array_multisort($list, "lack_orders", SORT_DESC);
            $list = array_slice($list, 0, 20);
            $list[] = $all;
        }

        return $list;
    }

    // 城市订单价格不足TOP20
    public function getCityOrderPriceInsuffList($date_begin, $date_end, $city_ids = []){
        $list = $this->quyuModel->getCityList($city_ids);

        if (count($list) > 0) {
            // 近三个月（不包含当月）
            $three_month_begin = date("Y-m-01", strtotime("-3 months", strtotime($date_begin)));
            $three_month_end = date("Y-m-t", strtotime("-1 months", strtotime($date_begin)));

            // 查询三个月总收款/总充值
            $reportCompanyLogic = new ReportCompanyLogic();
            $reportAmountList = $reportCompanyLogic->getCityReportAmountTotal($three_month_begin, $three_month_end, $city_ids);
            $reportAmountList = array_column($reportAmountList, null, "city_id");

            // 查询分单量
            $ordersModel = new Orders();
            $fenList = $ordersModel->getAreaFendanStatistic($date_begin, $date_end, $city_ids);
            $fenList = $this->setCityAreaGroup(CityAreaCodeEnum::TAB_CITY, $fenList);

            // 获取1.0累计消耗金额
            $companyExpendModel = new UserCompanyExpend();
            $expendList = $companyExpendModel->getCityExpendAmount($date_begin, $date_end, $city_ids);
            $expendList = array_column($expendList, null, "city_id");

            // 查询2.0累计消耗金额
            $orderAmountList = $ordersModel->getCityOrderAmount($date_begin, $date_end, $city_ids);
            $orderAmountList = array_column($orderAmountList, null, "city_id");

            // 查询城市报价
            $cityQuoteModel = new SaleReportCityQuote();
            $quoteList = $cityQuoteModel->getCityQuoteList($date_end, $city_ids);
            $quoteList = array_column($quoteList, null, "city_id");

            // 查询城市会员数量
            $companyLogic = new CompanyLogic();
            $companyCountList = $companyLogic->getCityUserCount($date_end, $city_ids);

            foreach ($list as $key => $item) {
                $city_id = $item["city_id"];

                // 城市会员数（1.0 + 2.0）
                $vip_total_count = $companyCountList[$city_id]["vip_total_count"] ?? 0;
                if ($vip_total_count == 0) {
                    unset($list[$key]);
                    continue;
                }

                $csos_fendan = $fenList[$city_id]["csos_fendan"] ?? 0; // 实际分单量
                $month3_chongzhi = floatval($reportAmountList[$city_id]["round_amount"] ?? 0); // 3个月总充值
                $month3_shoukuan = floatval($reportAmountList[$city_id]["total_round_amount"] ?? 0); // 3个月总收款
                $month_expend_amount = floatval($expendList[$city_id]["expend_amount"] ?? 0); // 1.0累计月消耗
                $month_round_amount = floatval($orderAmountList[$city_id]["order_amount"] ?? 0); // 2.0累计月消耗
                $month_expend = round($month_expend_amount + $month_round_amount, 2); // 当月总扣款

                // 实际订单售价
                $list[$key]["real_order_price"] = 0;
                if ($csos_fendan > 0){
                    $month3_money = 1;
                    if ($month3_chongzhi > 0 && $month3_shoukuan > 0) {
                        $month3_money = $month3_shoukuan / $month3_chongzhi;
                    }
                    
                    // 实际订单售价=当月总扣款*（3个月总收款/3个月总充值）/(城市订单价格*4*当月实际分单量)*城市订单价格；
                    $list[$key]["real_order_price"] = $month_expend * $month3_money / (4 * $csos_fendan);
                    $list[$key]["real_order_price"] = round($list[$key]["real_order_price"], 2);
                }

                $list[$key]["city_order_price"] = floatval($quoteList[$city_id]["round_order_money"] ?? 0);
                $list[$key]["order_price_diff"] = round($list[$key]["city_order_price"] - $list[$key]["real_order_price"], 2);

                unset($list[$key]["bm"], $list[$key]["px_abc"]);
            }

            $list = array_values($list);
            $list = sys_array_multisort($list, "order_price_diff", SORT_DESC);
            $list = array_slice($list, 0, 20);
        }

        return $list;
    }

    // 城市发单浪费率
    public function getCityOrderFawasteList($date_begin, $date_end){
        $ordersModel = new Orders();
        $list = $ordersModel->getCityLittleOrderFawasteStatistic($date_begin, $date_end);
        $list = array_column($list, null, "city_little");

        $glist = [];
        $littleList = CityAreaCodeEnum::$city_little;
        foreach ($littleList as $little => $little_name) {
            $data = [
                "city_little" => $little,
                "city_little_name" => $little_name,
                "fa_count" => intval($list[$little]["fa_count"] ?? 0),
                "waste_count" => intval($list[$little]["waste_count"] ?? 0)
            ];

            $data["waste_lv"] = sys_devision($data["waste_count"], $data["fa_count"], 4) * 100;
            $glist[] = $data;
        }

        return $glist;
    }

    // 城市分单浪费率
    public function getCityOrderFenwasteList($date_begin, $date_end){
        $ordersModel = new Orders();
        $list = $ordersModel->getCityLittleOrderFenwasteStatistic($date_begin, $date_end);
        $list = array_column($list, null, "city_little");

        $glist = [];
        $littleList = CityAreaCodeEnum::$city_little;
        foreach ($littleList as $little => $little_name) {
            $data = [
                "city_little" => $little,
                "city_little_name" => $little_name,
                "csos_fennum" => intval($list[$little]["csos_fennum"] ?? 0),
                "max_fennum" => intval($list[$little]["max_fennum"] ?? 0),
                "allot_fennum" => intval($list[$little]["allot_fennum"] ?? 0)
            ];

            $data["waste_fennum"] = $data["max_fennum"] - $data["allot_fennum"];
            $data["waste_lv"] = sys_devision($data["waste_fennum"], $data["max_fennum"], 4) * 100;
            $glist[] = $data;
        }

        return $glist;
    }

    // 按城市聚合
    private function setCityAreaGroup($tab, $list){
        $glist = [];
        if (count($list) > 0) {
            if ($tab == CityAreaCodeEnum::TAB_AREA) {
                foreach ($list as $key => $item) {
                    $gkey = sprintf("%s-%s", $item["city_id"], $item["area_id"]);
                    // $gkey = $item["gkey"];
                    if (!array_key_exists($gkey, $glist)){
                        $glist[$gkey] = $item;
                    }
                }
            } else {
                foreach ($list as $key => $item) {
                    $city_id = $item["city_id"];
                    if (!array_key_exists($city_id, $glist)){
                        $glist[$city_id] = $item;
                    } else {
                        foreach ($item as $k => $v) {
                            if (!in_array($k, ["city_id", "area_id", "gkey"])){
                                $glist[$city_id][$k] += $v;
                            }
                        }
                    }
                }
            }
        }

        return $glist;
    }

    /**
     * @des:城市企业潜力分析
     */
    public function getCityPotential()
    {
        $city = new Quyu();
        //已开通城市区域
        $cityOpen = $city->getCityOpenByUid();
        //城市签约会员
        $citySignVip = $city->getCitySignVip();
        $citySignVip = array_column($citySignVip,null,'qz_bigpart');
        $result = [];
        foreach ($cityOpen as $k => $v) {
            $result[] = [
                'name' => $citySignVip[$v['qz_bigpart']]['name'],
                'count' => (int)$v['amount'],
                'potential' => (int)$citySignVip[$v['qz_bigpart']]['vip_amount'],
                'sign_amount' => (int)$citySignVip[$v['qz_bigpart']]['vip_amount'],
                'sign_percent' => ($citySignVip[$v['qz_bigpart']]['vip_amount'] > 0) ? 100 : 0
            ];
        }

        $return = [
            'error_code' => 0,
            'error_msg' => BaseStatusCodeEnum::CODE_0,
            'data' => $result
        ];
        return $return;
    }

}