<?php

namespace app\common\model\db;

use think\Db;
use think\Model;
use think\db\Query;

class MsgType extends Model {

    protected $autoWriteTimestamp = false;

    public function getAppTypeList($app_slot, $user_id = 0){
        $map = new Query();
        $map->where("t.enabled", 1);
        $map->where("a.app_slot", $app_slot);

        return $this->alias("t")->where($map)
            ->join("msg_type_app a", "a.type_id = t.id", "inner")
            ->join("msg_system m", "m.type_id = t.id", "left")
            ->join("msg_system_related r", "r.msg_id = m.id and r.user_id = $user_id", "left")
            ->field("t.id,t.slot,t.`name`,count(r.id) as msg_count")
            ->group("t.id")
            ->select();
    }
}