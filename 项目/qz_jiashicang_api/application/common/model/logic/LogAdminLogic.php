<?php
// +----------------------------------------------------------------------
// | LogAdminLogic 日志记录
// +----------------------------------------------------------------------
namespace app\common\model\logic;

use think\facade\Request;
use app\common\model\db\LogAdmin;

class LogAdminLogic {

    // 管理员请求日志
    public static function addLog($logtype, $remark, $info = [], $action_id = "", $data = []){
        $request = Request::instance();

        $data = [
            "remark" => $remark,
            "logtype" => $logtype,
            "action_id" => $action_id,
            "info" => json_encode($info, 320),
            "data" => json_encode($data, 320),
            "ip" => $request->ip(),
            "action" => $request->url(),
            "userid" => $request->user["user_id"] ?? 0,
            "username" => $request->user["user_name"] ?? "",
            "user_agent" => $request->header("user-agent", ""),
            "time" => date("Y-m-d H:i:s")
        ];

        $logAdminModel = new LogAdmin();
        return $logAdminModel->addActionLog($data);
    }
}