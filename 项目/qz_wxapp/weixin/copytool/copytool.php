<?php
/**
* 小程序复制工具
 */

//设置时区
ini_set('date.timezone','PRC');
ini_set('date.timezone','Asia/Shanghai');

// Autoload 自动载入
require __DIR__.'/vendor/autoload.php';


use App\Command\Copy;
use Symfony\Component\Console\Application;


//常量定义
define('DS', DIRECTORY_SEPARATOR);
defined('ROOT_PATH') or define('ROOT_PATH', dirname(__FILE__) . DS);

$application = new Application();
$application->add(new Copy());
$application->run();