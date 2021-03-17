<?php
/**
 * qq管家 360 等"安全工具" 会对电脑访问过的url地址进行 扫描工作.
 * 而我们chk_order1 发单子.是采用get的方式 发布.
 * 导致还没审核的单子,单子信息被get请求成了乱码.
 * 他们扫描的服务器的 user agent 有个特征.
 * 特征useragent为 Mozilla/4.0
 */
namespace Mobile\Behavior;
use Think\Behavior;
class BrowserScanCheckBehavior extends Behavior {
    // 行为扩展的执行入口必须是run
    public function run(&$params) {
        //导入扩展文件
        import('Library.Org.Util.App');
        $app = new \App();
        if (self::checkscan()) {
            $msg = "禁止机器扫描! ";
            echo $msg;
            $msg .= $_SERVER['HTTP_USER_AGENT'] . " " . $app->get_client_ip();
            // Log::write($msg, LOG::WARN, LOG::FILE, LOG_PATH . "checkscan" . date("Y-m-d") . ".log");
            die();
        }
    }

    private static function checkscan() {
        $scanagent = "/^Mozilla\/4\.0$/";
        preg_match($scanagent, $_SERVER['HTTP_USER_AGENT'], $matches);
        if (empty($matches)) {
            return false;
            //print_r($matches);
        }else{
            return true;
        }
    }
}