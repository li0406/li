<?php

/**
 * 异常捕获类
 * @Author: liuyupeng
 * @Date:   2019-03-05 17:52:16
 * @Last Modified by:   liuyupeng
 * @Last Modified time: 2019-05-21 14:59:31
 */

namespace Util;

use think\facade\Log;
use think\facade\Config;
use think\exception\Handle;

class ExceptionHandler extends Handle {

    public function render(\Exception $e){
        $app_debug = Config::get("app.app_debug");

        if($app_debug == true){
            return parent::render($e);
        } else {
            Log::Error($e->getMessage());
            $response = sys_response(500, $e->getMessage());
            // $response = sys_response(500, '服务器异常');
            return json($response, $e->getCode());
        }
    }
}