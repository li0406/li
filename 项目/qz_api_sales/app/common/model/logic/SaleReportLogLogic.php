<?php
// +----------------------------------------------------------------------
// | SaleReportLogLogic 日志记录
// +----------------------------------------------------------------------
namespace app\common\model\logic;

use app\common\model\db\SaleReportLog;

class SaleReportLogLogic
{
    public function saveLog($request){
        $send = $request->post();
        if (!empty($request->report_id) || !empty($send['id'])) {
            $remark = "";
            if (!empty($send["remark"])) {
                $remark = $send["remark"];
            } else if (!empty($send["admin_remarke"])) {
                $remark = $send["admin_remarke"];
            } else if (!empty($send["service_remarke"])) {
                $remark = $send["service_remarke"];
            }

            $status = 0;
            if (!empty($send["status"])) {
                $status = intval($send["status"]);
            } else if ($request->route("status")) {
                $status = $request->route("status");
            }

            $save = [
                'report_id' => !empty($request->report_id) ? $request->report_id : $send['id'],
                'cooperation_type' => !empty($send['cooperation_type']) ? $send['cooperation_type'] : 0,
                'status' => $status,
                'op_name' => $request->user['user_name'],
                'op_id' => $request->user['user_id'],
                'remark' => $remark,
                'created_at' => time(),
                'save_info' => json_encode($send, 320)
            ];
            $logDb = new SaleReportLog();
            $logDb->saveLog($save);
        }
    }
}