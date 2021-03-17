<?php

namespace Home\Model\Logic;

use Think\Log;
use Home\Model\Service\MsgServiceModel;

class SalesReportPaymentLogicModel {

    const COOPERATION_TYPE_ZX = 1;
    const COOPERATION_TYPE_DJ = 2;
    const COOPERATION_TYPE_LD = 3;
    const COOPERATION_TYPE_SWJ = 4;
    const COOPERATION_TYPE_SBACK = 6;
    const COOPERATION_TYPE_QWU = 7;
    const COOPERATION_TYPE_UBACK = 8;
    const COOPERATION_TYPE_WL = 9;
    const COOPERATION_TYPE_SBACK_ACCOUNT = 10;
    const COOPERATION_TYPE_USER_REFUND = 11;
    const COOPERATION_TYPE_PLATFORM_USE = 12;
    const COOPERATION_TYPE_PLATFORM_USE_TURN = 13;
    const COOPERATION_TYPE_REGULAR_MODEL = 14;
    const COOPERATION_TYPE_SBACK_MODEL = 15;

    private $header;

    private $cooperationType = [
        self::COOPERATION_TYPE_ZX => '装修会员',
        self::COOPERATION_TYPE_DJ => '独家会员',
        self::COOPERATION_TYPE_LD => '垄断会员',
        self::COOPERATION_TYPE_SBACK => '签单返点会员',
        self::COOPERATION_TYPE_QWU => '全屋定制会员',
        self::COOPERATION_TYPE_UBACK => '会员返点',
        self::COOPERATION_TYPE_SWJ => '三维家会员',
        self::COOPERATION_TYPE_WL => '物料',
        self::COOPERATION_TYPE_SBACK_ACCOUNT => "签返会员（保证金转轮单费）",
        self::COOPERATION_TYPE_USER_REFUND => '会员退款',
        self::COOPERATION_TYPE_PLATFORM_USE => "平台使用费",
        self::COOPERATION_TYPE_PLATFORM_USE_TURN => "平台使用费（保证金转）",
        self::COOPERATION_TYPE_REGULAR_MODEL => '常规标杆会员',
        self::COOPERATION_TYPE_SBACK_MODEL => '新签返标杆会员',
    ];

    public function __construct($header){
        $this->header = $header;
    }

    public function getCooperationType() {
        return $this->cooperationType;
    }

    // 显示返点比例的类型
    public function getCooperationBackRatioType() {
        return [
            self::COOPERATION_TYPE_SBACK,
            self::COOPERATION_TYPE_UBACK,
            self::COOPERATION_TYPE_SBACK_ACCOUNT
        ];
    }

    // 显示viptype的类型
    public function getCooperationViptypeType() {
        return [
            self::COOPERATION_TYPE_ZX,
            self::COOPERATION_TYPE_DJ,
            self::COOPERATION_TYPE_LD,
            self::COOPERATION_TYPE_QWU,
        ];
    }

    //显示标杆会员(保产值)类型
    public function getCooperationBaoChanZhiType()
    {
        return [
            self::COOPERATION_TYPE_REGULAR_MODEL,
            self::COOPERATION_TYPE_SBACK_MODEL,
        ];
    }

    // 显示会员ID的类型
    public function getCooperationCompanyIdType() {
        return [
            self::COOPERATION_TYPE_PLATFORM_USE_TURN,
        ];
    }

    // 显示会员ID的类型
    public function getCooperationNoOtherMoneyType() {
        return [
            self::COOPERATION_TYPE_SBACK_ACCOUNT,
            self::COOPERATION_TYPE_PLATFORM_USE_TURN,
        ];
    }

    // 显示平台使用费的类型
    public function getCooperationPlatformDateType() {
        return [
            self::COOPERATION_TYPE_PLATFORM_USE,
            self::COOPERATION_TYPE_PLATFORM_USE_TURN,
        ];
    }


    // 不显示关联大报备的类型
    public function getCooperationNoRelatedType() {
        return [
            self::COOPERATION_TYPE_UBACK,
            self::COOPERATION_TYPE_WL,
            self::COOPERATION_TYPE_PLATFORM_USE_TURN,
			self::COOPERATION_TYPE_USER_REFUND
        ];
    }

    // 获取客服审核的报备类型
    public function getCooperationKfExamType() {
        return [
            self::COOPERATION_TYPE_UBACK => "会员返点",
            self::COOPERATION_TYPE_PLATFORM_USE_TURN => "平台使用费（保证金转）"
        ];
    }


    // 请求审核列表
    public function getReportPaymentExamList($input, $page, $limit){
        $input["page"] = $page;
        $input["limit"] = $limit;

        $url = "/v1/sale_report/payment/examlist";
        $response = $this->getRequest($url, $input);

        $data = [];
        if ($response["error_code"] == 0) {
            $data = $response["data"];
        }

        return $data;
    }

    // 请求客服审核列表
    public function getReportPaymentKfExamList($input, $page, $limit){
        $input["page"] = $page;
        $input["limit"] = $limit;

        $url = "/v1/sale_report/payment/examkflist";
        $response = $this->getRequest($url, $input);

        $data = [];
        if ($response["error_code"] == 0) {
            $data = $response["data"];
        }

        return $data;
    }
    
    // 小报备详情
    public function getReportPaymentDetail($id){
        $url = "/v1/sale_report/payment/detail";
        $input = [
            "id" => $id,
            "act_from" => 1
        ];

        $response = $this->getRequest($url, $input);

        $data = [];
        if ($response["error_code"] == 0) {
            $data = $response["data"];
        }

        return $data;
    }

    // 小报备审核
    public function checkReportPayment($id, $exam_status, $remark = "", $actfrom = 1, $isconfirm = 1){
        $input = [
            "id" => $id,
            "reason" => $remark,
            "actfrom" => $actfrom,
            "isconfirm" => $isconfirm,
        ];

        if ($exam_status == 1) {
            $url = "/v1/sale_report/payment/exampass";
        } else {
            $url = "/v1/sale_report/payment/examfail";
        }

        return $this->getRequest($url, $input, "POST");
    }

    // 小报备驳回
    public function rollbackReportPayment($id){
        $input = [
            "id" => $id
        ];

        $url = "/v1/sale_report/payment/rollback";
        return $this->getRequest($url, $input, "POST");
    }

    // 大报备关联小报备列表
    public function getReportRelatedPaymentList($report_id, $report_cooperation_type){
        $input = [
            "report_id" => $report_id,
            "report_cooperation_type" => $report_cooperation_type,
        ];

        $url = "/v1/sale_report/related/report_payment_list";
        $response = $this->getRequest($url, $input);

        $data = [];
        if ($response["error_code"] == 0) {
            $data = $response["data"];
        }

        return $data;
    }

    private function getRequest($url, $paramter = [], $type = "GET"){
        $uri = C("SALES_API") . $url;

        $postdata = [];
        if ($type == "GET" && !empty($paramter)) {
            $uri .= "?" . http_build_query($paramter);
        } else if ($type == "POST") {
            $paramter["_timestamp"] = time();
            $postdata = json_encode($paramter);
        }

        $response = curl($uri, $postdata, $this->header);
        if ($response == false || !isset($response["error_code"])) {
            // 失败记录日志
            $logdata = json_encode([
                "url" => $uri,
                "type" => $type,
                "data" => $paramter,
                "response" => $response
            ]);

            Log::write("CURL请求失败：" . $logdata, "ERR");

            $response = ["error_code" => -1, "error_msg" => "请求失败"];
        }

        return $response;
    }
}