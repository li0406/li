<?php

// +----------------------------------------------------------------------
// | 异常处理类
// +----------------------------------------------------------------------
// | Author: 米兰的小铁匠
// +----------------------------------------------------------------------

namespace Util;

use think\facade\Log;
use think\facade\Config;
use think\exception\Handle;

class ExceptionHandler extends Handle {

    public function render(\Exception $e){
        if(Config::get("app.app_debug") == true){
            return parent::render($e);
        } else {
            Log::Error($e->getMessage());
            if (Config::get("app.APP_ENV") == "prod") {
                $response = sys_response(500, "服务器异常");
            } else {
                $response = sys_response(500, $e->getMessage());
            }

            return json($response, $e->getCode());
        }
    }
}