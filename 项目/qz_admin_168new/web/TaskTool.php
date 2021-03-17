<?php
/**
 *命令行下计划任务执行
 */

//如果不是命令行执行,就退出
PHP_SAPI != "cli" && exit;

//引入框架
require("./Core2016/ThinkPHP.php");

if (md5('TaskTool') != $_SERVER['argv'][1]) {
    die('效验失败!');
}


App::init();
Session::set('chkadmin', 1); //给后台登录权限
Session::set('uid',1); //给超级管理员

//自动处理 百度链接提交
// 需要每天 02:00 执行
//cron 计划任务为
//每天02:00 执行
//00 02 * * * cd /data/wwwroot/qizuang.com;/usr/bin/php /data/wwwroot/qizuang.com/TaskTool.php 4b32d0ab2fdbe7f56ec8aa6caac24168 baidupostdo >> /tmp/baidupostdo.log
if ('baidupostdo' == $_SERVER['argv'][2]) {

    import("@.Action.AdmintaskdoAction");
    $Admintaskdo = new AdmintaskdoAction();

    $Admintaskdo->baiduUrlPost();
}

//生成平均订单排行
if ('orderassignavg' == $_SERVER['argv'][2]) {
    import("@.Action.AdmintaskdoAction");
    $Taskdo = new TaskdoAction();
    $Taskdo->orderAssignAvg();
}


//更新每个会员是否是 全月会员数据

//更新 全瞰-分单量进度 buildFdljd


?>