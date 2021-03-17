<?php

// +----------------------------------------------------------------------
// | clickhouse 数据库配置文件
// +----------------------------------------------------------------------

return [
    'host' => env('CLICK_HOUSE_HOST', '127.0.0.1'),
    'port' => env('CLICK_HOUSE_PORT', 8123),
    'username' => env('CLICK_HOUSE_USER'),
    'password' => env('CLICK_HOUSE_PASSWORD'),
    'database' => env('CLICK_HOUSE_DATABASE', 'sq_qizuang'),
];