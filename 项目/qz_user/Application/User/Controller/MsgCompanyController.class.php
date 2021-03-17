<?php

namespace User\Controller;

use User\Common\Controller\CompanyBaseController;
use Common\Model\Logic\MsgCompanyLogicModel;

class MsgCompanyController extends CompanyBaseController {

    public function getMsgInfo(){
        $app_slot = C("SMS_APP_SLOT");
        $userinfo = session("u_userInfo");
        $send_group = I("param.send_group");

        $msgCompanyLogic = new MsgCompanyLogicModel();
        $info = $msgCompanyLogic->getMsgInfo($app_slot, $userinfo["id"], $send_group);

        if (!empty($info)) {
            $this->ajaxReturn([
                "error_code" => 0,
                "error_msg" => "fetch:ok",
                "data" => ["info" => $info]
            ]);
        }

        $this->ajaxReturn(["error_code" => 5000002, "error_msg" => "消息不存在"]);
    }

}