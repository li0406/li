<?php
namespace Home\Model\Logic;

use Think\Log;

class InvoiceLogicModel {

    private $header;

    // 发票状态
    const INVOICE_STATUS_PRE = 1; //待提交
    const INVOICE_STATUS_SUBMIT = 2; //待审核
    const INVOICE_STATUS_ONGOING = 3; //开票中
    const INVOICE_STATUS_NORMAL = 4; //已开票
    const INVOICE_STATUS_REJECT = 5; //已驳回
    const INVOICE_STATUS_DELETE = -1; //删除
    const INVOICE_STATUS_RECALL = -2; //撤回

    public function __construct($header){
        $this->header = $header;
    }

    // 获取报备列表
    public function getInvoiceList($input, $page, $limit){
        $input["page"] = $page;
        $input["limit"] = $limit;
        $input["status168"] = 1;

        $url = "/v1/invoice/list";
        $response = $this->getRequest($url, $input);

        $data = [];
        if ($response["error_code"] == 0) {
            $data = $response["data"];
        }

        return $data;
    }

    // 获取搜索options
    public function getOptions(){

        $url = "/v1/invoice/options";
        $response = $this->getRequest($url);


        $data = [];
        if ($response["error_code"] == 0) {
            $data = $response["data"];

            foreach ($data['status'] as $key => &$value) {
                if($value['id'] == self::INVOICE_STATUS_PRE){
                    unset($data['status'][$key]);
                }
            }
        }

        return $data;
    }

    // 获取报备列表
    public function getDeptList($input, $page, $limit){
        $url = "/v1/team/select";
        $response = $this->getRequest($url);


        $data = [];
        if ($response["error_code"] == 0) {
            $data = $response["data"];
        }

        return $data ? $data['list'] : [];
    }

    // 新后台发票审核详情页
    public function getInvoiceDetails($id)
    {
        $url = "/v1/invoice/view";
        $input = [
            "id" => $id,
        ];

        $response = $this->getRequest($url, $input);

        $data = [];
        if ($response["error_code"] == 0) {
            $data = $response["data"];
        }

        return $data;
    }

    // 小报备多次开票关联发票列表
    public function getPaymentInvoiceList($payment_id, $invoice_id){
        $input = [
            "id" => $payment_id,
            "invoice_id" => $invoice_id
        ];

        $url = "/v1/invoice/getPaymentInvoiceList";
        $response = $this->getRequest($url, $input);

        $data = [];
        if ($response["error_code"] == 0) {
            $data = $response["data"];
        }

        return $data;
    }

    // 发票审核
    public function checkInvoice($id, $remark, $status){
        $input = [
            "id" => $id,
            "remark" => $remark,
            "status" => $status,
        ];

        $url = "/v1/invoice/verify";

        return $this->getRequest($url, $input, "POST");
    }

    // 获取数据公共方法
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
