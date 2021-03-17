<?php
/**
 * 更改报备状态时添加记录
 */

namespace app\http\middleware;

use app\common\model\logic\SaleReportLogLogic;

class EditReportStatus {

    public function handle($request, \Closure $next) {
        $response = $next($request);
        if ($request->isPost()) {
            $data = $response->getData();
            if (isset($data["error_code"]) && $data["error_code"] == 0) {
                // 添加用户更改操作数据
                $logLogic = new SaleReportLogLogic();
                $logLogic->saveLog($request);
            }
        }

        return $response;
    }
}