<?php
// +----------------------------------------------------------------------
// | UserSystemNotice 用户后台通知/站内信表
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Db;
use think\Model;

class UserSystemNotice extends Model {

    public function addRecord($data){
        return $this->insertGetId($data);
    }

    public function addRelated($data){
        return Db::name("user_notice_related")->insert($data);
    }

}
