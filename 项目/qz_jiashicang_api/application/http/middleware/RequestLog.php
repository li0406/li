<?php

namespace app\http\middleware;

use app\common\model\logic\LogAdminLogic;

class RequestLog {
    
    public function handle($request, \Closure $next) {
        $logtype = "qz_jiashicang_api";
        $logremark = "数据驾驶舱接口请求";
        $paramter = $request->param();
        $action_id = "";
        $data = [
            "method" => $request->method(),
            "referer" => $request->header("referer", ""),
            "host" => $request->header("HTTPS") == "on" ? "https" : "http",
        ];

        // 添加用户请求数据
        LogAdminLogic::addLog($logtype, $logremark, $paramter, $action_id, $data);
        return $next($request);
    }
}