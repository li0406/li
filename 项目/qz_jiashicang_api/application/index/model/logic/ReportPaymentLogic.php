<?php
// +----------------------------------------------------------------------
// | ReportPaymentLogic 小报备相关数据逻辑层
// +----------------------------------------------------------------------

namespace app\index\model\logic;

use app\index\model\adb\SaleIndicators;
use app\index\model\adb\SaleReportPayment;
use app\index\model\adb\SaleTeamAchievement;
use app\index\enum\ReportPaymentCodeEnum;

class ReportPaymentLogic {

    protected $paymentModel;

    public function __construct(){
        $this->paymentModel = new SaleReportPayment();
    }

    // 小报备城市业绩统计
    public function getCityAchievementStatistic($date_begin, $date_end, $city_ids = []){
        $list = $this->paymentModel->getCityAchievementStatistic($date_begin, $date_end, $city_ids);
        return $list;
    }

    // 城市小报备收款金额查询
    public function getCityReportAmountStatistic($date_begin, $date_end, $city_ids = []){
        $zxList = $this->paymentModel->getCityReportTotalAmount(1, $date_begin, $date_end, $city_ids);
        $zxList = array_column($zxList, null, "city_id");
        $cityIds = array_keys($zxList);

        $sbackList = $this->paymentModel->getCityReportTotalAmount(2, $date_begin, $date_end, $city_ids);
        $sbackList = array_column($sbackList, null, "city_id");
        $cityIds = array_merge($cityIds, array_keys($sbackList));

        $list = [];
        $cityIds = array_filter(array_unique($cityIds));
        foreach ($cityIds as $key => $city_id) {
            if (!array_key_exists($city_id, $list)) {
                $list[$city_id]["city_id"] = $city_id;
            }

            $list[$city_id]["zx_total_amount"] = floatval($zxList[$city_id]["total_amount"] ?? 0);
            $list[$city_id]["signback_total_amount"] = floatval($sbackList[$city_id]["total_amount"] ?? 0);
        }

        return $list;
    }

    // 获取重点城市业绩
    public function getCityAchievementTop($date_begin, $date_end, $city_ids = []){
        $list = $this->paymentModel->getCityAchievementTop($date_begin, $date_end, $city_ids);
        return $list;
    }

    // 成本效益图-业绩月趋势图
    public function getAchievementMonthtrendList($month_begin, $month_end, $team_id = 0){
        if (!empty($team_id)) {
            $team_ids = [$team_id];
        } else {
            $teamLogic = new TeamLogic();
            $topTeamList = $teamLogic->getTeamTopList();
            $topTeamList = array_column($topTeamList, null, "team_id");
            $team_ids = array_keys($topTeamList);
        }

        // 查询团队业绩人员数量
        $memberList = $this->getTeamAchievementMemberCount($month_begin, $month_end, $team_ids);

        // 查询团队业绩
        $teamAchievementModel = new SaleTeamAchievement();
        $achievementList = $teamAchievementModel->getTeamAchievementTotal($month_begin, $month_end, $team_ids);
        $achievementList = array_column($achievementList, null, "month");

        // 团队指标查询时间
        $indicators_begin = date("Ym", strtotime($month_begin));
        $indicators_end = date("Ym", strtotime($month_end));

        // 查询团队指标
        $indicatorsModel = new SaleIndicators();
        $indicatorsList = $indicatorsModel->getTeamIndicatorsTotal($indicators_begin, $indicators_end, $team_ids);
        $indicatorsList = array_column($indicatorsList, null, "month");

        $list = [];
        $begin = strtotime($month_begin);
        $end = strtotime($month_end);
        for ($datetime = $begin; $datetime <= $end; $datetime = strtotime("+1 month", $datetime)) {
            $month_show = date("y-m", $datetime);
            $month = date("Ym", $datetime);

            $allkey = sprintf("%s-%s", $month, 0);
            $member_count = $memberList[$allkey]["member_count"] ?? 0;

            $list[$month] = [
                "month_show" => $month_show,
                "achievement" => floatval($achievementList[$month]["achievement"] ?? 0),
                "indicators" => floatval($indicatorsList[$month]["indicators"] ?? 0),
                "member_count" => $member_count
            ];

            // 平均业绩
            $list[$month]["achievement_avg"] = sys_devision($list[$month]["achievement"], $member_count, 2);
        }

        return $list;
    }

    // 成本效益图-人均产出
    public function getAchievementOutputAvgList($month_begin, $month_end){
        $teamLogic = new TeamLogic();
        $topTeamList = $teamLogic->getTeamTopList();
        $topTeamList = array_column($topTeamList, null, "team_id");
        $team_ids = array_keys($topTeamList);

        // 查询团队业绩人员数量
        $memberList = $this->getTeamAchievementMemberCount($month_begin, $month_end, $team_ids);

        // 查询团队业绩
        $teamAchievementModel = new SaleTeamAchievement();
        $achievementList = $teamAchievementModel->getTeamMonthAchievementTotal($month_begin, $month_end, $team_ids);

        $achList = [];
        foreach ($achievementList as $key => $item) {
            $gkey = sprintf("%s-%s", $item["month"], $item["team_id"]);
            $achList[$gkey] = $item;
        }

        $list = [];
        $begin = strtotime($month_begin);
        $end = strtotime($month_end);
        for ($datetime = $begin; $datetime <= $end; $datetime = strtotime("+1 month", $datetime)) {
            $month_show = date("y-m", $datetime);
            $month = date("Ym", $datetime);

            $list[$month] = [
                "month_show" => $month_show,
                "team_list" => []
            ];

            $allkey = sprintf("%s-%s", $month, 0);
            $member_count_all = $memberList[$allkey]["member_count"] ?? 0;
            $achievement_all = 0;

            foreach ($team_ids as $team_id) {
                $gkey = sprintf("%s-%s", $month, $team_id);
                $member_count = $memberList[$gkey]["member_count"] ?? 0;
                $achievement = floatval($achList[$gkey]["achievement"] ?? 0);

                $achievement_all += $achievement;
                $achievement_avg = sys_devision($achievement, $member_count, 2);

                $list[$month]["team_list"][] = [
                    "team_id" => $team_id,
                    "team_name" => $topTeamList[$team_id]["team_name"],
                    "achievement" => $achievement,
                    "member_count" => $member_count,
                    "achievement_avg" => $achievement_avg
                ];
            }

            $achievement_avg_all = sys_devision($achievement_all, $member_count_all, 2);
            array_unshift($list[$month]["team_list"], [
                "team_id" => 0,
                "team_name" => "销售中心",
                "achievement" => $achievement_all,
                "member_count" => $member_count_all,
                "achievement_avg" => $achievement_avg_all
            ]);
        }

        return $list;
    }

    // 成本效益图-人均业绩档位
    public function getAchievementGradeAvgList($month_begin, $month_end, $team_id = 0){
        $teamLogic = new TeamLogic();
        if (empty($team_id)) {
            $topTeamList = $teamLogic->getTeamTopList();
            $team_ids = array_column($topTeamList, "team_id");
        } else {
            $team_ids = [$team_id];
        }

        // 获取团队成员ID
        $teamAllIds = $teamLogic->getTeamChildIds($team_ids);
        $teamAllIds = array_merge($teamAllIds, $team_ids);
        $memberIds = $teamLogic->getTeamMemberIds($teamAllIds, true, false);

        // 查询团队人员业绩
        $paymentList = $this->paymentModel->getTeamSalerAchievement($month_begin, $month_end, $team_ids);
        $paymentList = array_column($paymentList, null, "saler_id");

        // 档位列表
        $list = ReportPaymentCodeEnum::getAchievementGradeList();

        foreach ($memberIds as $member_id) {
            $achievement = floatval($paymentList[$member_id]["achievement"] ?? 0);
            foreach ($list as $key => $val) {
                $condition_min = $val["min"] == 0 || $achievement >= $val["min"];
                $condition_max = $val["max"] == 0 || $achievement < $val["max"];
                if ($condition_min && $condition_max) {
                    $list[$key]["number"] += 1;
                    break;
                }
            }
        }

        return $list;
    }

    // 获取团队业绩人员数量
    public function getTeamAchievementMemberCount($month_begin, $month_end, $team_ids){
        $glist = [];
        $all_member_ids = [];
        foreach ($team_ids as $team_id) {
            $list = $this->paymentModel->getTeamAchievementMembers($month_begin, $month_end, $team_id);

            foreach ($list as $key => $ret) {
                $gkey = sprintf("%s-%s", $ret["month"], $team_id);
                $allkey = sprintf("%s-%s", $ret["month"], 0);

                $glist[$gkey] = [
                    "team_id" => $team_id,
                    "month" => $ret["month"],
                    "member_count" => intval($ret["member_count"])
                ];

                if (!array_key_exists($allkey, $glist)) {
                    $glist[$allkey] = [
                        "team_id" => $team_id,
                        "month" => $ret["month"],
                        "member_count" => 0,
                    ];

                    $all_member_ids[$allkey] = [];
                }

                $member_ids = explode(",", $ret["member_ids"]);
                $all_member_ids[$allkey] = array_unique(array_merge($all_member_ids[$allkey], $member_ids));
                $glist[$allkey]["member_count"] = count($all_member_ids[$allkey]);
            }
        }

        return $glist;
    }
}