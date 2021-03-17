<?php
// +----------------------------------------------------------------------
// | LogAdmin
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Db;
use think\Model;

class LogAdmin extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_log_admin';

    public function addTelFailedLog($user,$data){
        $info['info'] = json_encode($data);
        $extra = array(
            'time' => date("Y-m-d H:i:s"),
            'username' => $user['user_name'],
            'userid' => $user['user_id'],
            'action' => 'report/failed_tel',
            'ip' => '',
            'user_agent' => $_SERVER["HTTP_USER_AGENT"],
        );
        $data = array_merge($info,$extra);
        return $this->insertGetId($data);
    }

    public function addActionLog($save){
        return $this->insertGetId($save);
    }

    public function addLoginLog($data){
        return Db::name("admin_logging")->insert($data);
    }
}