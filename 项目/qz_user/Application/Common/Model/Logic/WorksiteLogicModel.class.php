<?php

namespace Common\Model\Logic;

class WorksiteLogicModel {

    private $token;

    public function __construct(){
        $userinfo = session("u_userInfo");
        if (empty($userinfo["ZXS_API_TOKEN"])) {
            $userinfo["ZXS_API_TOKEN"] = JwtTokenLogicModel::approvalToken();
            session("u_userInfo", $userinfo);
        }
        
        $this->token = $userinfo["ZXS_API_TOKEN"];
    }

    /**
     * 获取工地直播分页列表
     * @param  [type]  $keyword [description]
     * @param  integer $page    [description]
     * @param  integer $limit   [description]
     * @return [type]           [description]
     */
    public function getLiveList($keyword, $page = 1, $limit = 10){
        $url = "/business/worksite/pc/live_list";
        $param = array(
            "page" => $page,
            "limit" => $limit,
            "keyword" => trim($keyword),
        );

        $response = $this->getRequest($url, $param, "GET");
        if ($response["error_code"] == 0) {
            $list = $response["data"]["list"];
            $pageshow = $this->getPageshow($response["data"]["page"]);
        }

        return ["list" => $list, "page" => $pageshow];
    }

    /**
     * 获取工地直播详情
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getLiveDetail($id){
        $url = "/business/worksite/pc/live_detail";
        $param = [
            "id" => $id
        ];

        $response = $this->getRequest($url, $param, "GET");
        if ($response["error_code"] == 0) {
            return $response["data"];
        }

        return false;
    }

    /**
     * 生成工地直播
     * @param  [type] $order_type [description]
     * @param  [type] $order_id   [description]
     * @return [type]             [description]
     */
    public function createdLive($order_type, $order_id){
        $url = "/business/worksite/pc/live_created";
        $param = [
            "order_type" => $order_type,
            "order_id" => $order_id
        ];

        return $this->getRequest($url, $param, "POST");
    }


    /**
     * 删除工地直播
     * @param  [type] $order_type [description]
     * @param  [type] $order_id   [description]
     * @return [type]             [description]
     */
    public function deleteLive($order_type, $order_id){
        $url = "/business/worksite/pc/live_delete";
        $param = [
            "order_type" => $order_type,
            "order_id" => $order_id
        ];

        return $this->getRequest($url, $param, "POST");
    }


    /**
     * 获取施工信息列表
     * @param  [type]  $live_id [description]
     * @param  integer $step    [description]
     * @param  integer $page    [description]
     * @param  integer $limit   [description]
     * @return [type]           [description]
     */
    public function getInfoList($live_id, $step = 0, $page = 1, $limit = 5){
        $url = "/business/worksite/pc/info_list";
        $param = [
            "live_id" => $live_id,
            "step" => $step,
            "page" => $page,
            "limit" => $limit,
        ];

        return $this->getRequest($url, $param, "GET");
    }

    /**
     * 获取施工信息详情
     * @return [type] [description]
     */
    public function getInfoDetail($id){
        $url = "/business/worksite/pc/info_detail";
        $param = [
            "id" => $id
        ];

        return $this->getRequest($url, $param, "GET");
    }

    /**
     * 编辑施工信息
     * @param  [type] $input [description]
     * @return [type]        [description]
     */
    public function editInfo($input){
        $url = "/business/worksite/pc/info_edit";
        return $this->getRequest($url, $input, "POST");
    }

    /**
     * 删除施工信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function deleteInfo($id){
        $url = "/business/worksite/pc/info_delete";
        $param = [
            "id" => $id
        ];

        return $this->getRequest($url, $param, "POST");
    }

    /**
     * 检测订单是否在直播
     * @param  [type] $order_type [description]
     * @param  [type] $order_id   [description]
     * @return [type]             [description]
     */
    public function getLiveIdByOrder($order_type, $order_id){
        $url = "/business/worksite/pc/order_live_id";
        $param = [
            "order_type" => $order_type,
            "order_id" => $order_id,
        ];

        $response = $this->getRequest($url, $param, "GET");
        if ($response["error_code"] == 0) {
            return $response["data"]["live_id"];
        }

        return false;
    }

    /**
     * 获取小程序二维码
     * 加入缓存（时间一天）
     * @param  [type] $live_code [description]
     * @return [type]            [description]
     */
    public function liveQrcode($live_code){
        $cache_key = "CACHE:USER:WORKSITE:QRCODE:" . $live_code;
        $imgbase64 = S($cache_key);
        if (empty($imgbase64)) {
            $url = "/business/worksite/pc/live_qrcode";
            $param = [
                "live_code" => $live_code
            ];

            $response = $this->getRequest($url, $param, "GET");
            if ($response["error_code"] == 0) {
                $imgbase64 = $response["data"];
                S($cache_key, $imgbase64, 86400);
            }
        }

        return $imgbase64;
    }


    // 获取分页展示
    public function getPageshow($pdata){
        $pageshow = "";
        if ($pdata["total_number"] > 0) {
            //自定义配置项
            $config  = array("prev","next");
            import('Library.Org.Page.Page');
            $page = new \Page($pdata["page_current"], $pdata["page_size"], $pdata["total_number"], $config);
            $pageshow = $page->show();
        }

        return $pageshow;
    }

    public function setInfoComment($input){
        $url = "/business/worksite/pc/comment";
        $param = [
            "comment_id" => $input['comment_id'],
            "info_id" => $input['info_id'],
            "content" => trim($input['content']),
        ];

        $response = $this->getRequest($url, $param, "POST");
        if ($response["error_code"] == 0) {
            return true;
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
            "token:" . $this->token
        ];

        $response = curl($uri, $postdata, $header);
        if ($response == false) {
            $response = ["error_code" => -1, "error_msg" => "请求失败"];
        }

        return $response;
    }

}