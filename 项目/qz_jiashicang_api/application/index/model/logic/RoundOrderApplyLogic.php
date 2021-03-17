<?php
// +----------------------------------------------------------------------
// | RoundOrderApplyLogic 补轮申请相关数据逻辑层
// +----------------------------------------------------------------------

namespace app\index\model\logic;

use app\index\model\adb\UserCompanyAccountLog;
use app\index\model\adb\UserCompanyRoundOrderApply;
use app\index\enum\RoundOrderCodeEnum;
use Util\Page;

class RoundOrderApplyLogic {

    protected $roundApplyModel;

    public function __construct() {
        $this->roundApplyModel = new UserCompanyRoundOrderApply();
    }

    // 补轮明细
    public function getRoundApplyDetailed($input, $page = 1, $limit = 20){
        $count = 0;
        $offset = 0;
        $pageSize = 0;
        $pageshow = null;
        if ($input["export"] == false) {
            $count = $this->roundApplyModel->getRoundApplyDetailedCount($input);

            $pageobj = new Page($page, $limit, $count);
            $offset = $pageobj->firstrow;
            $pageSize = $pageobj->pageSize;
            $pageshow = $pageobj->show();
        }

        $list = [];
        if ($input["export"] || $count > 0) {
            $list = $this->roundApplyModel->getRoundApplyDetailedList($input, $offset, $pageSize);

            foreach ($list as $key => &$item) {
                $item["round_money"] = floatval($item["round_money"]);
                $item["exam_status_name"] = RoundOrderCodeEnum::getItem("exam_status", $item["exam_status"]);
                $item["apply_reason_text"] = RoundOrderCodeEnum::getItem("apply_reason", $item["apply_reason"]);
                $item["apply_reason_remark"] = $item["apply_reason_text"];

                if (!empty($item["apply_remark"])) {
                    $item["apply_reason_remark"] = sprintf("%s（%s）", $item["apply_reason_remark"], $item["apply_remark"]);
                }

                $item["fen_date"] = date("Y-m-d H:i:s", $item["addtime"]);
                $item["apply_date"] = date("Y-m-d H:i:s", $item["created_at"]);
                $item["exam_date"] = $item["exam_time"] ? date("Y-m-d H:i:s", $item["exam_time"]) : "";

                unset($item["addtime"], $item["created_at"], $item["exam_time"]);
            }
        }

        return [
            "list" => $list,
            "page" => $pageshow
        ];
    }




    // 获取城市补轮申请统计
    public function getCityRoundApplyStatistic($date_begin, $date_end, $city_ids = []){
        // 申请补轮量：查询时间段内，该城市申请补轮的分单量（按照第一家申请时间进行统计，订单维度）
        $applyList = $this->roundApplyModel->getCityOrderApplyStatistic($date_begin, $date_end, $city_ids);
        $applyList = array_column($applyList, null, "city_id");
        $cityIds = array_keys($applyList);

        // 申请补轮次数：查询时间段内，该城市申请补轮的次数；（按照申请时间进行统计，分配维度）
        $companyApplyList = $this->roundApplyModel->getCityCompanyApplyStatistic($date_begin, $date_end, $city_ids);
        $companyApplyList = array_column($companyApplyList, null, "city_id");
        $cityIds = array_merge($cityIds, array_keys($companyApplyList));

        // 补轮量：第一次补轮通过时间在查询时间段内的审核通过的补轮单；（，按照客服操作时间进行统计，订单维度）
        $applyPassList = $this->roundApplyModel->getCityOrderApplyPassStatistic($date_begin, $date_end, $city_ids);
        $applyPassList = array_column($applyPassList, null, "city_id");
        $cityIds = array_merge($cityIds, array_keys($applyPassList));

        // 补轮次数：查询时间段内，审核通过的补轮次数；（按照审核通过时间进行统计，分配维度）
        $companyApplyPassList = $this->roundApplyModel->getCityCompanyApplyPassStatistic($date_begin, $date_end, $city_ids);
        $companyApplyPassList = array_column($companyApplyPassList, null, "city_id");
        $cityIds = array_merge($cityIds, array_keys($companyApplyPassList));

        // 补轮驳回单量：第一次审核不通过时间在查询时间段内的补轮不通过单；（按照客服操作时间进行统计，订单维度）
        $applyUnpassList = $this->roundApplyModel->getCityOrderApplyUnpassStatistic($date_begin, $date_end, $city_ids);
        $applyUnpassList = array_column($applyUnpassList, null, "city_id");
        $cityIds = array_merge($cityIds, array_keys($applyUnpassList));

        // 补轮驳回次数：查询时间段内，审核不通过的补轮次数；（按照审核通过时间进行统计，分配维度）
        // 补轮驳回金额：补轮不通过单的总金额；(以最新状态为准)
        $companyApplyUnpassList = $this->roundApplyModel->getCityCompanyApplyUnpassStatistic($date_begin, $date_end, $city_ids);
        $companyApplyUnpassList = array_column($companyApplyUnpassList, null, "city_id");
        $cityIds = array_merge($cityIds, array_keys($companyApplyUnpassList));

        $list = [];
        $cityIds = array_filter(array_unique($cityIds));
        foreach ($cityIds as $key => $city_id) {
            if (!array_key_exists($city_id, $list)) {
                $list[$city_id]["city_id"] = $city_id;
            }

            $list[$city_id]["apply_order_num"] = $applyList[$city_id]["apply_order_num"] ?? 0; // 订单申请补轮量
            $list[$city_id]["apply_company_num"] = $companyApplyList[$city_id]["apply_company_num"] ?? 0; // 装企申请补轮量
            $list[$city_id]["apply_pass_num"] = $applyPassList[$city_id]["apply_pass_num"] ?? 0; // 订单补轮通过量
            $list[$city_id]["apply_company_pass_num"] = $companyApplyPassList[$city_id]["apply_company_pass_num"] ?? 0; // 公司补轮通过量
            $list[$city_id]["round_pass_money"] = floatval($companyApplyPassList[$city_id]["round_pass_money"] ?? 0); // 订单补轮单总金额

            $list[$city_id]["apply_unpass_num"] = $applyUnpassList[$city_id]["apply_unpass_num"] ?? 0; // 订单补轮不通过量
            $list[$city_id]["apply_company_unpass_num"] = $companyApplyUnpassList[$city_id]["apply_company_unpass_num"] ?? 0; // 公司补轮不通过量
            $list[$city_id]["round_unpass_money"] = floatval($companyApplyUnpassList[$city_id]["round_unpass_money"] ?? 0); // 订单补轮不通过总金额
        }

        return $list;
    }

    // 查询会员公司补轮统计
    public function getCompanyApplyStatistic($date_begin, $date_end, $company_ids = []){
        // 申请补轮量：查询时间段内，该会员申请的补轮订单量（按照会员申请补轮的时间进行查询）
        $applyList = $this->roundApplyModel->getCompanyOrderApplyCount($date_begin, $date_end, $company_ids);
        $applyList = array_column($applyList, null, "company_id");
        $companyIds = array_keys($applyList);

        // 补轮量：查询时间段内，该会员补轮通过的订单；（按照客服审核时间查询）
        // 补轮驳回量：查询时间段内，该会员补轮不通过的订单；（按照客服审核时间查询）
        $applyExamList = $this->roundApplyModel->getCompanyApplyExamCount($date_begin, $date_end, $company_ids);
        $applyExamList = array_column($applyExamList, null, "company_id");
        $companyIds = array_merge($companyIds, array_keys($applyExamList));

        // 违规补轮次数：按照新会员充值->扣款类型为“违规补轮扣费”进行统计。（按照客服操作时间统计）
        $accountLogModel = new UserCompanyAccountLog();
        $accountList = $accountLogModel->getCompanyRoundAmount($date_begin, $date_end, $company_ids);
        $accountList = array_column($accountList, null, "company_id");
        $companyIds = array_merge($companyIds, array_keys($accountList));

        $list = [];
        $companyIds = array_unique($companyIds);
        foreach ($companyIds as $key => $company_id) {
            if (!array_key_exists($company_id, $list)) {
                $list[$company_id]["company_id"] = $company_id;
            }

            $list[$company_id]["apply_num"] = $applyList[$company_id]["apply_num"] ?? 0;
            $list[$company_id]["apply_pass_num"] = $applyExamList[$company_id]["apply_pass_num"] ?? 0;
            $list[$company_id]["apply_unpass_num"] = $applyExamList[$company_id]["apply_unpass_num"] ?? 0;
            $list[$company_id]["apply_pass_amount"] = floatval($applyExamList[$company_id]["apply_pass_amount"] ?? 0);

            $list[$company_id]["round_num"] = $accountList[$company_id]["round_num"] ?? 0;
            $list[$company_id]["round_amount"] = floatval($accountList[$company_id]["round_amount"] ?? 0);
            $list[$company_id]["round_foul_num"] = $accountList[$company_id]["round_foul_num"] ?? 0;
            $list[$company_id]["round_foul_amount"] = floatval($accountList[$company_id]["round_foul_amount"] ?? 0);
        }

        return $list;
    }

}