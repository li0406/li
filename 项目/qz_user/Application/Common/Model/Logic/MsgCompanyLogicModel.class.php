<?php

namespace Common\Model\Logic;

use Common\Model\Db\MsgCompanyModel;

class MsgCompanyLogicModel {

    public function getMsgInfo($app_slot, $user_id, $send_group){
        $msgCompanyModel = new MsgCompanyModel();
        $info = $msgCompanyModel->getInfoByGroup($app_slot, $user_id, $send_group);

        if (!empty($info)) {
            $info["link_full"] = "";
            if (!empty($info["link"])) {
                $info["link_full"] = $info["link"] . $info["link_param"];
            }
        }

        return $info;
    }

}