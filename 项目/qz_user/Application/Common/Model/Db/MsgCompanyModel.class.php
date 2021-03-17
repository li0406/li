<?php

namespace Common\Model\Db;

use Think\Model;

class MsgCompanyModel extends Model {

    public function getInfoByGroup($app_slot, $user_id, $send_group){
        $map = array(
            "m.send_group" => array("EQ", $send_group),
            "r.user_id" => array("EQ", $user_id),
        );

        return $this->alias("m")->where($map)
            ->join("inner join qz_msg_company_related as r on r.msg_id = m.id")
            ->join("left join qz_msg_template_link as l on l.template_id = m.template_id and l.app_slot = '$app_slot'")
            ->field("m.id,m.`title`,m.`notice`,m.send_group,m.link_param,l.`link`")
            ->find();
    }

}