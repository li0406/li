<?php
namespace Home\Model\Logic;


class LogAdminLogicModel
{
    public function addLog($remark,$logtype,$infos,$id){

        $info["info"] = json_encode($infos);
        $admin = getAdminUser();
        import('Library.Org.Util.App');
        $app = new \App();
        $ip =  $app->get_client_ip();
        $extra = array(
            'logtype'=> $logtype,
            'time' => date("Y-m-d H:i:s"),
            'username' => $admin['name'],
            'userid' => $admin['id'],
            'action' => CONTROLLER_NAME.'/'.ACTION_NAME,
            'ip' => $ip,
            'user_agent' => $_SERVER["HTTP_USER_AGENT"],
            'action_id'=>$id,
            'remark'=>$remark
        );
        $data = array_merge($info,$extra);

        return D("Home/Db/LogAdmin")->addLogAdmin($data);
    }
}