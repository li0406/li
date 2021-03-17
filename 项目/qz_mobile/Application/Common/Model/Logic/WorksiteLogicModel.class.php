<?php

namespace Common\Model\Logic;

class WorksiteLogicModel {

    private $token;

    public function __construct()
    {
//        $token = ession("ZXS_API_TOKEN");
//        if (empty($token)) {
//            $token = JwtTokenLogicModel::approvalToken();
//            echo $token;exit;
//            session("ZXS_API_TOKEN", $token);
//        }
//
//        $this->token = $token;
    }

    /**
     * 获取工地直播分页列表
     * @param  [type]  $keyword [description]
     * @param  integer $page    [description]
     * @param  integer $limit   [description]
     * @return [type]           [description]
     */
    public function getLiveList($input, $page = 1, $limit = 10){
        $url = "/business/worksite/m/live_list";
        $param = array(
            "page" => $page,
            "limit" => $limit,
            "company_id" => $input['company_id'],
            "media" => $input['media'],
            "fans" => $input['fans'],
        );

        $response = $this->getRequest($url, $param, "GET");
        $list = $page = [];
        if ($response["error_code"] == 0) {
            $list = $response["data"]["info"];
            $page = $response["data"]["page"];
        }

        return ["list" => $list, "page" => $page];
    }

    /**
     * 获取工地直播详情
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getLiveDetail($id, $page = 1, $limit = 10){
        $url = "/business/worksite/m/live_detail";
        $param = [
            "live_id" => $id,
            "page" => $page,
            "limit" => $limit
        ];

        $response = $this->getRequest($url, $param, "GET");
        if ($response["error_code"] == 0) {
            return $response["data"];
        }

        return false;
    }

    public function getLiveFans($id){
        $url = "/business/worksite/m/live_fans";
        $param = [
            "live_id" => $id
        ];

        $response = $this->getRequest($url, $param, "GET");
        if ($response["error_code"] == 0) {
            return $response["data"];
        }

        return false;
    }

    public function getLiveQrcode($id){
        $url = "/business/worksite/m/live_wxqrcode";
        $param = [
            "live_id" => $id
        ];

        $response = $this->getRequest($url, $param, "POST");
        if ($response["error_code"] == 0) {
            return $response["data"];
        }

        return false;
    }

    // http请求
    private function getRequest($url, $param = [], $type = "GET"){
        $uri = C("ZXS_API") . $url;

        $postdata = [];
        if ($type == "GET" && !empty($param)) {
            $uri .= "?" . http_build_query($param);
        } else if ($type == "POST") {
            $param["_timestamp"] = time();
            $postdata = json_encode($param);
        }

        $header = [
            "Content-Type: application/json;charset=utf-8;",
//            "token:" . $this->token
        ];

        $response = curl($uri, $postdata, $header);
        if ($response == false) {
            $response = ["error_code" => -1, "error_msg" => "请求失败"];
        }

        return $response;
    }

}