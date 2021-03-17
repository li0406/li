<?php
// +----------------------------------------------------------------------
// | LogAdminLogic 日志记录
// +----------------------------------------------------------------------
namespace app\common\model\logic;

use think\facade\Request;
use app\common\model\db\LogAdmin;

class LogAdminLogic
{
    public function saveLog($request){
        $paramter = $request->param();
        unset($paramter["user_pwd"]);

        $save = [
            "ip" => get_client_ip(),
            "action" => $request->url(),
            "userid" => $request->user["user_id"] ?? 0,
            "username" => $request->user["user_name"] ?? "",
            "user_agent" => $request->header("user-agent", ""),
            "info" => json_encode($paramter, 320),
            "remark" => "销售系统接口请求",
            "time" => date("Y-m-d H:i:s"),
            "logtype" => "sales_api",
            "action_id" => "",
            "data" => json_encode([
                "method" => $request->method(),
                "referer" => $request->header("referer", ""),
                "host" => $request->header("HTTPS") == "on" ? "https" : "http",
            ], 320),
        ];

        $logDb = new LogAdmin();
        $logDb->addActionLog($save);
    }

    public static function addLog($logtype, $action_id = 0, $remark = "", $info = ""){
        $request = Request::instance();

        if (is_array($info)) {
            $info = json_encode($info);
        }

        $record = [
            "logtype" => $logtype,
            "info" => $info,
            "remark" => $remark,
            "action_id" => $action_id,
            "ip"=> get_client_ip(),
            "action" => $request->url(),
            "userid" => $request->user["user_id"] ?? 0,
            "username" => $request->user["user_name"] ?? "",
            "user_agent" => $request->header("user-agent", ""),
            "time" => date("Y-m-d H:i:s"),
            "data" => ""
        ];

        $logAdmin = new LogAdmin();
        return $logAdmin->addActionLog($record);
    }

    // 小程序登录日志
    public static function addLoginLog($userinfo){
        $logAdminModel = new LogAdmin();
        $logAdminModel->addLoginLog([
            "uid" => $userinfo["user_id"],
            "username" => $userinfo["user_name"],
            "cs" => $userinfo["cs"],
            "ip" => get_client_ip(),
            "user_agent" => Request::instance()->header("user-agent"),
            "time" => time(),
            "status" => 1,
        ]);
    }
}