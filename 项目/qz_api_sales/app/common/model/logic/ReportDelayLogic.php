<?php

namespace app\common\model\logic;

use think\Db;
use think\Exception;

use app\common\model\db\UserCompany;
use app\common\model\db\SaleReportZxCompany;
use app\common\model\db\SaleReportDelayCompany;
use app\index\enum\SalesReportCodeEnum;
use app\index\enum\CompanyCodeEnum;

class ReportDelayLogic {

    protected $reportDelayModel;

    public function __construct(){
        $this->reportDelayModel = new SaleReportDelayCompany();
    }

    // 查找延期装修公司信息
    public function findDelayCompanyInfo($company_id){
        $userCompanyModel = new UserCompany();
        $company_info = $userCompanyModel->getDelayCompanyInfo($company_id);

        $vip_contract_list = [];
        $report_contract_list = [];
        if (!empty($company_info)) {
            $today = date("Y-m-d");

            $company_info["back_ratio"] = $company_info["back_ratio"] ."%";
            $company_info["company_mode"] = CompanyCodeEnum::getCompanyMode($company_info["classid"], $company_info["cooperate_mode"]);
            unset($company_info["classid"], $company_info["cooperate_mode"]);

            // 查找会员公司合同
            $userVipLogic = new UserVipLogic();
            $vip_contract_list = $userVipLogic->getCompanyContractList($company_id, $today)->toArray();

            $company_info["current_vip_id"] = 0;
            foreach ($vip_contract_list as $key => $item) {
                $vip_contract_list[$key]["back_ratio"] = $company_info["back_ratio"];
            }

            // 查找当前公司大报备本次合同时间
            $reportZxModel = new SaleReportZxCompany();
            $reportList = $reportZxModel->getCompanyReportList($company_id);
            foreach ($reportList as $key => $item) {
                if ($item["cooperation_type"] != SalesReportCodeEnum::REPORT_COOPERATION_SBACK) {
                    $report_contract_list[] = [
                        "report_id" => $item["id"],
                        "viptype" => $item["viptype"],
                        "back_ratio" => $item["back_ratio"],
                        "cooperation_type" => $item["cooperation_type"],
                        "current_promised_orders_fen" => $item["current_promised_orders_fen"],
                        "current_contract_start" => date("Y-m-d", $item["current_contract_start"]),
                        "current_contract_end" => date("Y-m-d", $item["current_contract_end"]),
                        "create_time" => $item["create_time"]
                    ];
                }
            }

            // 查找当前公司会员延期大报备延期合同时间
            $delayList = $this->reportDelayModel->getCompanyReportDelayList($company_id);
            foreach ($delayList as $key => $item) {
                $report_contract_list[] = [
                    "report_id" => $item["id"],
                    "viptype" => $item["viptype"],
                    "back_ratio" => $item["back_ratio"],
                    "cooperation_type" => $item["cooperation_type"],
                    "current_promised_orders_fen" => $item["delay_promise_orders"],
                    "current_contract_start" => date("Y-m-d", $item["delay_contract_start"]),
                    "current_contract_end" => date("Y-m-d", $item["delay_contract_end"]),
                    "create_time" => $item["create_time"]
                ];
            }

            // 会员公司报备重新排序
            $createTimeList = array_column($report_contract_list, "create_time");
            array_multisort($createTimeList, SORT_DESC, $report_contract_list);
        }

        return [
            "company_info" => $company_info,
            "vip_contract_list" => $vip_contract_list,
            "report_contract_list" => $report_contract_list,
        ];
    }

    // 会员延期提交数据补充
    public function reportDelayPostData($post){
        $data = $this->findDelayCompanyInfo($post["company_id"]);
        if (!empty($data["company_info"])) {
            $data["vip_contract_list"] = array_column($data["vip_contract_list"], null, "id");
            $data["report_contract_list"] = array_column($data["report_contract_list"], null, "report_id");

            $report_id = $post["report_id"];
            $current_vip_id = $post["current_vip_id"];
            if (!isset($data["report_contract_list"][$report_id]) || !isset($data["vip_contract_list"][$current_vip_id])) {
                return false;
            }

            // 选中的合同
            $vip_contract = $data["vip_contract_list"][$current_vip_id];

            // 选中的报备
            $report_contract = $data["report_contract_list"][$report_id];

            // 取会员公司信息补充post
            $post["company_mode"] = $data["company_info"]["company_mode"]; // 合作模式
            $post["city_name"] = $data["company_info"]["city_name"]; // 城市名称
            $post["cs"] = $data["company_info"]["cs"]; // 城市ID

            // 取合同中的会员倍数和返点比例
            $post["viptype"] = $vip_contract["viptype"];
            $post["back_ratio"] = $vip_contract["back_ratio"];

            // 取报备的合作类型
            $post["report_cooperation_type"] = $report_contract["cooperation_type"];
            $post["report_back_ratio"] = $report_contract["back_ratio"];
            $post["report_viptype"] = $report_contract["viptype"];
                
            // 计算理论实际延期天数（应延期天数*70%）
            $post["delay_sys_days"] = ceil($post["delay_days"] * 0.7);

            return $post;
        }

        return false;
    }

    // 会员公司会员延期大报备列表
    public function getCompanyReportDelayList($company_id){
        $list = $this->reportDelayModel->getCompanyReportDelayList($company_id);

        foreach ($list as $key => &$item) {
            $item["is_new"] = 2;
            $item["report_type"] = "delay";
            $item["cooperation_type_name"] = SalesReportCodeEnum::getCooperationType($item["cooperation_type"]);
            $item["current_contract_start_date"] = $item["delay_contract_start"] ? date("Y/m/d", $item["delay_contract_start"]) : "";
            $item["current_contract_end_date"] = $item["delay_contract_end"] ? date("Y/m/d", $item["delay_contract_end"]) : "";
            $item["viptype_ratio_show"] = SalesReportCodeEnum::getViptypeBackRatioShow($item["cooperation_type"], $item["company_mode"]);
            
            // 延期报备没有总合同数据默认为空
            $item["total_contract_amount"] = 0;
            $item["contract_start_date"] = "";
            $item["contract_end_date"] = "";

            $item["submit_date"] = $item["submit_time"] ? date("Y/m/d", $item["submit_time"]) : "";
            $item["created_date"] = date("Y/m/d H:i", $item["create_time"]);
        }

        return $list;
    }

}