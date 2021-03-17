<?php

namespace app\common\model\logic;

use think\Db;
use think\Exception;
use think\facade\Request;

use app\common\model\db\SaleReportUnpassLog;
use app\index\enum\ReportUnpassCodeEnum;
use Util\Page;

class ReportUnpassLogLogic {

    protected $reportUnpassLogModel;

    public function __construct(){
        $this->reportUnpassLogModel = new SaleReportUnpassLog();
    }

    // 报备审核不通过记录列表
    public function getUnpassLogPageList($input, $page = 1, $limit = 20){
        $teamLogic = new TeamLogic();
        if (!empty($input["top_team_id"])) {
            $top_user_list = $teamLogic->getTeamUserList($input["top_team_id"], 1);
            $input["user_ids"] = array_column($top_user_list, "current_id");
            $input["user_ids"] = $input["user_ids"] ? : [-1];
        }

        $count = 0;
        $offset = 0;
        $pageSize = 0;
        $pageshow = null;
        if (empty($input["export"])) {
            $count = $this->reportUnpassLogModel->getUnpassLogPageCount($input);
            $pageobj = new Page($page, $limit, $count);
            $offset = $pageobj->firstrow;
            $pageSize = $pageobj->pageSize;
            $pageshow = $pageobj->show();
        }

        $list = [];
        if (!empty($input["export"]) || $count > 0) {
            $list = $this->reportUnpassLogModel->getUnpassLogPageList($input, $offset, $pageSize);

            $userIds = array_column($list->toArray(), "report_saler");
            $userTopTeamIds = $teamLogic->getUserTopTeamId($userIds);

            foreach ($list as $key => $item) {
                $list[$key]["report_type_name"] = ReportUnpassCodeEnum::getItem("report_type", $item["report_type"]);
                $list[$key]["report_date"] = date("Y-m-d H:i", $item["report_time"]);
                $list[$key]["exam_date"] = date("Y-m-d H:i", $item["exam_time"]);
                $list[$key]["index"] = $offset + $key + 1;

                $list[$key]["top_team_id"] = 0;
                $list[$key]["top_team_name"] = "";
                $saler_id = $item['report_saler'];
                if (array_key_exists($saler_id, $userTopTeamIds)) {
                    $list[$key]["top_team_id"] = $userTopTeamIds[$saler_id]["team_id"];
                    $list[$key]["top_team_name"] = $userTopTeamIds[$saler_id]["team_name"];
                }
            }
        }

        return [
            "list" => $list,
            "page" => $pageshow
        ];
    }

    // 添加大报备审核不通过日志
    public function addDefaultLog($info, $data){
        $log = [
            "report_type" => ReportUnpassCodeEnum::REPORT_TYPE_DEFAULT,
            "report_id" => $info["id"],
            "cooperation_type" => $info["cooperation_type"],
            "city_id" => $info["cs"] ?? "",
            "city_name" => $info["city_name"] ?? "",
            "company_id" => $info["company_id"] ?? 0,
            "company_name" => $info["company_name"] ?? "",
            "report_time" => $info["create_time"],
            "report_saler" => $info["saler_id"],
            "exam_operator" => Request::instance()->user["user_id"],
            "exam_remark" => $data["exam_remark"],
            "exam_status" => $data["status"],
            "exam_time" => time(),
            "created_at" => time()
        ];

        return $this->reportUnpassLogModel->addLog($log);
    }

    // 添加小报备审核不通过日志
    public function addPaymentLog($info, $data){
        $log = [
            "report_type" => ReportUnpassCodeEnum::REPORT_TYPE_PAYMENT,
            "report_id" => $info["id"],
            "cooperation_type" => $info["cooperation_type"],
            "city_id" => $info["city_id"],
            "city_name" => $info["city_name"],
            "company_id" => $info["company_id"],
            "company_name" => $info["company_name"],
            "report_time" => $info["created_at"],
            "report_saler" => $info["creator"],
            "exam_operator" => Request::instance()->user["user_id"],
            "exam_time" => $data["exam_time"],
            "exam_status" => $data["exam_status"],
            "exam_remark" => $data["exam_reason"],
            "created_at" => time()
        ];

        return $this->reportUnpassLogModel->addLog($log);
    }
}