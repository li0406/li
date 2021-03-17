<?php

namespace Home\Model\Service;

class MsgServiceModel {

    const TRY_MAX_TIME = 5; // 最大重新尝试次数

    private $url;
    private $operator;

    public function __construct(){
        $config = C("SERVICE.QZMSG");
        $this->url = sprintf("%s://%s:%s", $config["PROTOCOL"], $config["DOMAIN"], $config["PORT"]);

        $admin = session("uc_userinfo");
        $this->operator = !empty($admin["id"]) ? $admin["id"] : 4;
    }

    /**
     * 发送消息
     * @param  [type] $template_id [消息模板ID]
     * @param  [type] $users       [发送人]
     * @param  string $link_param  [链接参数]
     * @param  string $action_id   [附加ID]
     * @return [type]              [description]
     */
    public function sendSystemMsg($template_id, $users, $link_param = "?", $action_id = "", $send_position = "168new", $params = []) {
        if (is_array($users)) {
            $users = implode(",", $users);
        }

        if (!empty($params)) {
            $params = array_map(function($item){
                return strval($item);
            }, $params);
        }

        $data = [
            "users" => $users,
            "action_id" => $action_id,
            "link_param" => $link_param,
            "template_id" => $template_id,
            "send_position" => $send_position,
            "from_app" => C("SMS_APP_SLOT"),
            "replace_params" => $params,
            "operator" => $this->operator
        ];

        $headers = [
            'Content-Type: application/json;charset=utf-8;',
        ];

        $data = json_encode($data, 320);
        $headers[] = 'Content-Length: ' . strlen($data);
        
        $idx = 1;
        do {
            $idx++;
            $result = curl($this->url . "/v1/send/system_msg", $data, $headers);
        } while (!$result && $idx <= static::TRY_MAX_TIME);

        return $result;
    }

	/**
     * 发送消息
     * @param  [type] $template_id [消息模板ID]
     * @param  [type] $users       [发送人]
     * @param  string $link_param  [链接参数]
     * @param  string $action_id   [附加ID]
     * @return [type]              [description]
     */
    public function sendCompanyMsg($template_id, $users, $link_param = "?", $action_id = "", $send_position = "168new", $params = [], $employees = "") {
        
        if (is_array($users)) {
            $users = implode(",", $users);
        }

        if (!empty($employees) && is_array($employees)) {
            $employees = implode(",", $employees);
        }

        if (!empty($params)) {
            $params = array_map(function($item){
                return strval($item);
            }, $params);
        }

        $data = [
            "users" => $users,
            "employees" => $employees,
            "action_id" => $action_id,
            "link_param" => $link_param,
            "template_id" => $template_id,
            "send_position" => $send_position,
            "from_app" => C("SMS_APP_SLOT"),
            "replace_params" => $params,
            "operator" => $this->operator
        ];

        $headers = [
            'Content-Type: application/json;charset=utf-8;',
        ];

        $data = json_encode($data, 320);
        $headers[] = 'Content-Length: ' . strlen($data);
        
        $idx = 1;
        do {
            $idx++;
            $result = curl($this->url . "/v1/send/company_msg", $data, $headers);
        } while (!$result && $idx <= static::TRY_MAX_TIME);

        return $result;
    }
   
}