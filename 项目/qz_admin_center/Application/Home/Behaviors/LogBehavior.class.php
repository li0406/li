<?php
namespace Home\Behaviors;
class LogBehavior extends \Think\Behavior{
    // 行为扩展的执行入口必须是run
    public function run(&$params) {
        //导入扩展文件
        import('Library.Org.Util.App');
        $app = new \App();
        $logData = array(
                "logtype"=>$params["type"],
                "time"=>date("Y-m-d H:i:s"),
                "username"=>session("uc_userinfo.name"),
                "userid"=>session("uc_userinfo.id"),
                "action"=>CONTROLLER_NAME."/".ACTION_NAME,
                "ip"=>$app->get_client_ip(),
                "info"=>$params["info"],
                "user_agent"=>$_SERVER["HTTP_USER_AGENT"],
                "remark"=>$params["errMsg"]
                         );
        D("Logadmin")->addLog($logData);
    }
}