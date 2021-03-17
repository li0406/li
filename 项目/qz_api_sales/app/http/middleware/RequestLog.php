<?php

namespace app\http\middleware;

use app\common\model\logic\LogAdminLogic;

class RequestLog {
    
    public function handle($request, \Closure $next) {
        // 添加用户请求数据
        $logLogic = new LogAdminLogic();
        $logLogic->saveLog($request);
        return $next($request);
    }
}