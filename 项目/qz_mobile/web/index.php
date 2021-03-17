<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用入口文件

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
//ini_set('display_errors',1);            //错误信息
//ini_set('display_startup_errors',1);    //php启动错误信息
//error_reporting(-1);                    //打印出所有的 错误信息

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
// define('APP_DEBUG',True);

// 定义应用目录
define('APP_PATH','../Application/');


// 多域名访问支持得到当前访问域名的常量
$httpHost = $_SERVER['HTTP_HOST'];
// 强制过滤掉 HTTP_HOST 中的端口号
$httpHostArr = explode(':', $httpHost);
if (count($httpHostArr) > 1) {
    $httpHost = $httpHostArr[0];
}
$http_domain = implode('.',array_slice(explode('.',$httpHost),-2));
if(empty($http_domain)) $http_domain = $httpHost;
defined('APP_HTTP_DOMAIN')    or define('APP_HTTP_DOMAIN', $http_domain);
defined('APP_HTTP_HOST')    or define('APP_HTTP_HOST', $_SERVER['REQUEST_SCHEME']."://".$httpHost);

unset($http_domain);

// 引入ThinkPHP入口文件
require '../Core2015/ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单
