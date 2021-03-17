<?php

namespace app\common\model\service;

use Log;
use think\Exception;
use think\facade\Request;

class MsgService {

    const TRY_MAX_TIME = 5; // 最大重新尝试次数

    protected $url;
    protected $operator;

    public function __construct(){
        $config = config("app.SERVICE.QZMSG");
        $this->url = sprintf("%s://%s:%s", $config["PROTOCOL"], $config["DOMAIN"], $config["PORT"]);
        $this->operator = Request::instance()->user["user_id"] ?? 4;
    }

    /**
     * 发送消息
     * @param  [type] $template_id [消息模板ID]
     * @param  [type] $users       [发送人]
     * @param  string $link_param  [链接参数]
     * @param  string $action_id   [附加ID]
     * @return [type]              [description]
     */
    public function sendSystemMsg($template_id, $users, $link_param = "?", $action_id = "", $send_position = "销售系统", $params = []) {
        $url = $this->url . "/v1/send/system_msg";

        try {
            if (is_array($users)) {
                $users = implode(",", $users);
            }

            if (!empty($params)) {
                $params = array_map(function($item){
                    return strval($item);
                }, $params);
            }

            $data = json_encode([
                "users" => $users,
                "action_id" => $action_id,
                "link_param" => $link_param,
                "template_id" => $template_id,
                "send_position" => $send_position,
                "from_app" => config("SMS_APP_SLOT"),
                "operator" => $this->operator,
                "replace_params" => $params,
            ], 320);

            $headers = [
                'Content-Type: application/json;charset=utf-8;',
                'Content-Length: ' . strlen($data)
            ];

            $idx = 1;
            do {
                $idx++;
                $result = curl($url, $data, $headers);
            } while (!$result && $idx <= static::TRY_MAX_TIME);

            // 如果失败记录到日志
            if (empty($result)) {
                Log::warning(sprintf("消息推送失败：\n  URL：%s\n  数据：%s", $url, $data));
            }
        } catch (Exception $e) {
            $result = sys_response(1000001, "curl请求失败");
            Log::warning(sprintf("消息推送失败：\n  URL：%s\n  数据：%s", $url, $data ?? ""));
        }

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
    public function sendCompanyMsg($template_id, $users, $link_param = "?", $action_id = "", $send_position = "销售系统", $params = [], $employees = "") {
        $url = $this->url . "/v1/send/company_msg";

        try {
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

            $data = json_encode([
                "users" => $users,
                "employees" => $employees,
                "action_id" => $action_id,
                "link_param" => $link_param,
                "template_id" => $template_id,
                "send_position" => $send_position,
                "from_app" => config("SMS_APP_SLOT"),
                "operator" => $this->operator,
                "replace_params" => $params,
            ], 320);

            $headers = [
                'Content-Type: application/json;charset=utf-8;',
                'Content-Length: ' . strlen($data)
            ];

            $idx = 1;
            do {
                $idx++;
                $result = curl($url, $data, $headers);
            } while (!$result && $idx <= static::TRY_MAX_TIME);

            // 如果失败记录到日志
            if (empty($result)) {
                Log::warning(sprintf("消息推送失败：\n  URL：%s\n  数据：%s", $url, $data));
            }

        } catch (Exception $e) {
            $result = sys_response(1000001, "curl请求失败");
            Log::warning(sprintf("消息推送失败：\n  URL：%s\n  数据：%s", $url, $data ?? ""));
        }

        return $result;
    }
}