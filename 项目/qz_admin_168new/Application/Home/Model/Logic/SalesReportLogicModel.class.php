<?php

namespace Home\Model\Logic;

use Think\Log;
use Home\Model\Service\MsgServiceModel;
use Common\Enums\SalesReportStatus;

class SalesReportLogicModel
{

    private $header;

    // 合作类型
    const REPORT_COOPERATION_ZX = 1; // 装修会员
    const REPORT_COOPERATION_DJ = 2; // 独家会员
    const REPORT_COOPERATION_LD = 3; // 垄断会员
    const REPORT_COOPERATION_SWJ = 4; // 三维家会员
    const REPORT_COOPERATION_ERP = 5; // ERP会员
    const REPORT_COOPERATION_SBACK = 6; // 签单返点会员
    const REPORT_COOPERATION_QWU = 7; // 全屋定制会员
    const REPORT_COOPERATION_DELAY = 8; // 会员延期
    const REPORT_COOPERATION_REGULAR_MODEL = 14;//常规标杆会员
    const REPORT_COOPERATION_SBACK_MODEL = 15;//签返标杆会员


    private $cooperationType = [
        self::REPORT_COOPERATION_ZX => '装修会员',
        self::REPORT_COOPERATION_DJ => '独家会员',
        self::REPORT_COOPERATION_LD => '垄断会员',
        self::REPORT_COOPERATION_SBACK => '签单返点会员',
        self::REPORT_COOPERATION_QWU => '全屋定制会员',
        self::REPORT_COOPERATION_SWJ => '三维家会员',
        self::REPORT_COOPERATION_ERP => 'ERP会员',
        self::REPORT_COOPERATION_DELAY => '会员延期',
        self::REPORT_COOPERATION_REGULAR_MODEL => '常规标杆会员',
        self::REPORT_COOPERATION_SBACK_MODEL => '新签返标杆会员',
    ];

    public function __construct($header){
        $this->header = $header;
    }

    public function getSalesReport($adminuser_type, $get, $url, $header)
    {
        switch ($adminuser_type) {
            //老板审核
            case 1:
                $url .= '?admin_user=1&submit_start_time=' . $get['submit_start_time'] . '&submit_end_time=' . $get['submit_end_time'];
                break;
            //客服审核
            case 2:
                $url .= '?admin_user=2&check_start_time=' . $get['check_start_time'] . '&check_end_time=' . $get['check_end_time'];
                break;
        }
        $url .= !empty($get['company_name']) ? '&company_name=' . trim($get['company_name']) : '';
        $url .= !empty($get['op_id']) ? '&op_id=' . trim($get['op_id']) : '';
        $url .= !empty($get['cs']) ? '&cs=' . trim($get['cs']) : '';
        $url .= !empty($get['cooperation_type']) ? '&cooperation_type=' . trim($get['cooperation_type']) : '';
        $url .= !empty($get['is_new']) ? '&is_new=' . trim($get['is_new']) : '';
        $url .= (!empty($get['is_voucher'])) && (!empty($get['is_voucher']) == 1) ? '&is_voucher=' . trim($get['is_voucher']) : '';
        $url .= !empty($get['p']) ? '&p=' . trim($get['p']) : '';

        $response = $this->getRequest($url, [], "GET");
        $list = [];
        $page = '';
        if ($response['error_code'] == 0) {
            $list = $response['data']['list'];
            import('Library.Org.Util.Page');
            $p = new \Page($response['data']['page']['total_number'], $response['data']['page']['page_size']);
            $page = $p->show();
        }
        return ['list' => $list, 'page' => $page];
    }

    public function checkReport($post, $url, $header)
    {
        $send = [
            'id' => $post['check_id'],
            'cooperation_type' => $post['check_cooperation_type'],
        ];
        if(!empty($post['admin_remarke'])){
            $send['admin_remarke'] =  $post['admin_remarke'];
        }
        if(!empty($post['service_remarke']))
        {
            $send['service_remarke'] = $post['service_remarke'];
        }
        $send['remark'] = $post['remark'];
        switch ($post['admin_user']) {
            case 1:
                if ($post['checkResult'] == 1) {
                    $send['status'] = SalesReportStatus::REPORT_STATUS_SHTG;
                } elseif ($post['checkResult'] == 2) {
                    $send['status'] = SalesReportStatus::REPORT_STATUS_WTG;
                }
                break;
            case 2:
                //客服审核操作
                if ($post['checkResult'] == 1) {
                    $send['status'] = SalesReportStatus::REPORT_STATUS_KFSHTG;
                } elseif ($post['checkResult'] == 2) {
                    if ($post['isRecheck'] == 1) {
                        $send['status'] = SalesReportStatus::REPORT_STATUS_KFWTG_CHECK;
                    } else {
                        $send['status'] = SalesReportStatus::REPORT_STATUS_KFWTG;
                    }
                } elseif ($post['checkResult'] == 3){
                    $send['status'] = SalesReportStatus::REPORT_STATUS_KFSTOP;
                }
                //客服完成操作
                if ($post['accomplish'] == 1) {
                    $send['status'] = SalesReportStatus::REPORT_STATUS_KFWC;
                }
                break;
        }

        return $this->getRequest($url, $send, "POST");
    }

    // 大报备驳回
    public function recallReport($url, $report_id, $cooperation_type, $remark) {
        $data = [
            "id" => $report_id,
            "cooperation_type" => $cooperation_type,
            "remark" => $remark,
        ];

        $info = $this->getRequest($url, $data, "POST");
        return $info;
    }


    public function reportDetail($get, $url, $header)
    {
        $admin_user = $get["admin_user"];

        $urlparam = [
            "id" => trim($get["id"]),
            "cooperation_type" => trim($get["cooperation_type"]),
        ];

        // 判断是否查询城市报价和会员报价
        if (in_array($get['cooperation_type'], [1, 2, 3, 6, 7, 8, 14, 15])) {
            $urlparam["city_quote"] = 1;
        } else if (in_array($get['cooperation_type'], [4, 5])) {
            if ($admin_user == 1) {
                $urlparam["member_quote"] = 1;
            }
        }

        $url = $url ."?". http_build_query($urlparam);
        $info = $this->getRequest($url, [], "GET");

        if (isset($info["error_code"]) && $info["error_code"] == 0) {
            // 根据报备状态和用户略表 判断是否显示审核
            if ($admin_user == 1 && $info["data"]["info"]["status"] == SalesReportStatus::REPORT_STATUS_TJ) {
                $info["data"]["info"]["check_btn"] = 1;
            }

            if ($admin_user == 2 && in_array($info["data"]["info"]["status"], [SalesReportStatus::REPORT_STATUS_SHTG, SalesReportStatus::REPORT_STATUS_KFSTOP])) {
                $info["data"]["info"]["check_btn"] = 1;
            }

            if ($admin_user == 2 && in_array($info["data"]["info"]["status"], [SalesReportStatus::REPORT_STATUS_KFWAIT])) {
                $info["data"]["info"]["save_btn"] = 1;
                $info["data"]["info"]["upload_img"] = 1;
            }
        }

        return $info;
    }

    public function saveVoucher($post, $url, $header)
    {
        $send = [
            'id' => $post['id'],
            'cooperation_type' => $post['cooperation_type'],
            'img' => $post['img'],
        ];
        $info = $this->getRequest($url, $send, "POST");
//        if ($info['error_code'] == 0) {
//            //获取当前报备的销售
//            $msgServiceModel = new MsgServiceModel();
//            $msgServiceModel->sendSystemMsg("201909211009", $post['saler_id'], "?", $post['id'], "会员报备");
//        }
        return $info;
    }

    public function getReportCityLogByCity($city_name, $url, $header)
    {
        $url = $url . '?city_name=' . $city_name;
        $info = $this->getRequest($url, [], "GET");
        return $info;
    }

    public function getCooperationType()
    {
        return $this->cooperationType;
    }

    // 获取会员公司大报备列表
    public function getCompanyReportList($company_id){
        $input = [
            "company_id" => $company_id
        ];

        $url = "/v1/sale_report/company/report_list";
        $response = $this->getRequest($url, $input);

        $data = [];
        if ($response["error_code"] == 0) {
            $data = $response["data"];
        }

        return $data;
    }

	//获取大报备修改记录
    public function getReportModifyHistoryList($report_id)
    {
        $input = [
            "report_id" => $report_id
        ];

        $url = "/v1/sale_report/modify_history";
        $response = $this->getRequest($url, $input);

        $data = [];
        if ($response["error_code"] == 0) {
            $data = $response["data"];
        }

        return $data;
    }    // 装修公司历史会员时间列表
    public function getCompanyVipHistoryList($company_id){
        $input = [
            "company_id" => $company_id
        ];

        $url = "/v1/company/vip_history_list";
        $response = $this->getRequest($url, $input);

        $data = [];
        if ($response["error_code"] == 0) {
            $data = $response["data"];
        }

        return $data;
    }

    // 
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
