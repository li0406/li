<?php

namespace app\index\controller\v1;

use think\Request;
use think\Controller;
use app\index\model\logic\ReportPaymentLogic;
use app\common\enum\BaseStatusCodeEnum;

class ReportPayment extends Controller {

    // 成本效益图-业绩月趋势图
    public function getAchievementMonthtrend(Request $request, ReportPaymentLogic $reportPaymentLogic) {
        $team_id = $request->get("team_id");
        $month_begin = date("Y-m-01", strtotime("-11 months"));
        $month_end = date("Y-m-d");

        $list = $reportPaymentLogic->getAchievementMonthtrendList($month_begin, $month_end, $team_id);

        $data = [
            "series" => [
                [
                    "name" => "业绩（元）",
                    "type" => "line",
                    "data" => array_column($list, "achievement"),
                ],
                [
                    "name" => "业绩指标（元）",
                    "type" => "line",
                    "data" => array_column($list, "indicators"),
                ],
                [
                    "name" => "平均业绩（元）",
                    "type" => "line",
                    "data" => array_column($list, "achievement_avg"),
                ]
            ],
            "xAxis" => array_column($list, "month_show"),
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }

    // 成本效益图-人均产出
    public function getAchievementOutputAvg(ReportPaymentLogic $reportPaymentLogic) {
        $month_begin = date("Y-m-01", strtotime("-11 months"));
        $month_end = date("Y-m-d");

        $list = $reportPaymentLogic->getAchievementOutputAvgList($month_begin, $month_end);

        $series = [];
        foreach ($list as $item) {
            foreach ($item["team_list"] as $team) {
                $team_id = $team["team_id"];
                if (!array_key_exists($team_id, $series)) {
                    $series[$team_id] = [
                        "name" => $team["team_name"],
                        "type" => "bar",
                        "data" => []
                    ];
                }

                $series[$team_id]["data"][] = $team["achievement_avg"];
            }
        }

        $data = [
            "series" => array_values($series),
            "xAxis" => array_column($list, "month_show"),
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }

    // 成本效益图-平均业绩档位
    public function getAchievementGradeAvg(Request $request, ReportPaymentLogic $reportPaymentLogic){
        $team_id = $request->get("team_id", 0, "intval");

        $month_begin = date("Y-m-01");
        $month_end = date("Y-m-d");

        $list = $reportPaymentLogic->getAchievementGradeAvgList($month_begin, $month_end, $team_id);

        $data = [
            "series" => [
                [
                    "name" => "业绩人数",
                    "type" => "bar",
                    "data" => array_column($list, "number"),
                ],
            ],
            "xAxis" => array_column($list, "name"),
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }

}
