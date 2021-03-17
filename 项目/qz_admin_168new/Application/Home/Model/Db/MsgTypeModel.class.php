<?php

namespace Home\Model\Db;

use Think\Model;

class MsgTypeModel extends Model {

    public function getAppMsgType($app_slot, $admin_id){
        $map = array(
            "t.enabled" => array("EQ", 1),
            "a.app_slot" => array("EQ", $app_slot)
        );

        return $this->alias("t")->where($map)
            ->join("inner join qz_msg_type_app as a on a.type_id = t.id")
            ->join("left join qz_msg_system as m on m.type_id = t.id")
            ->join("left join qz_msg_system_related as r on r.msg_id = m.id and r.user_id = $admin_id")
            ->field("t.id,t.`name`,t.slot,count(r.id) as msg_count")
            ->group("t.id")
            ->select();
    }
}