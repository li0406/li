<?php

namespace app\common\model\logic;

use app\common\model\db\User;
use app\common\model\db\UserSystemNotice;

class UserSystemNoticeLogic {

    private $noticeTemplate = [
        "company_visit" => array(
            "title" => "订单回访通知",
            // "content" => "订单号%s已回访，请及时查看<a href='%s' target='_blank'>></a>",
            "content" => "订单号%s已回访，请及时查看"
        )
    ];


    // 生成通知
    public function addUserNotice($userIds, $temp, $params = []){
        if (array_key_exists($temp, $this->noticeTemplate) && !empty($userIds)) {
            $userModel = new User;
            $userList = $userModel->getList("id,classid,cs", ["id" => $userIds]);

            $template = $this->noticeTemplate[$temp];
            $template["content"] = vsprintf($template["content"], $params);

            $noticeModel = new UserSystemNotice();
            foreach ($userList as $user) {
                $notice_id = $noticeModel->addRecord([
                    "type" => 2,
                    "cs" => $user["cs"],
                    "classid" => $user["classid"],
                    "title" => $template["title"],
                    "text" => $template["content"],
                    "userid" => 0,
                    "username" => "系统",
                    "time" => time()
                ]);

                $noticeModel->addRelated([
                    "noticle_id" => $notice_id,
                    "uid" => $user["id"]
                ]);
            }
        }
    }

}
