<?php

namespace Home\Model\Logic;

use Home\Model\Db\YgjNoticeModel;
use Home\Model\Db\UmengRecordModel;

use Library\Org\Umeng\Umeng;

class YgjNoticeLogicModel {

    const ACTION_TYPE_BLD = 2;

    private $ygjNoticeModel;

    public function __construct(){
        $this->ygjNoticeModel = new YgjNoticeModel();
    }

    // 补轮审核通知
    public function setRoundApplyNotice($action_id, $user_id){
        $data = [
            "user_id" => $user_id,
            "action_id" => $action_id,
            "action_type" => static::ACTION_TYPE_BLD,
            "created_at" => time(),
            "updated_at" => time(),
        ];

        $ret = $this->ygjNoticeModel->addRecord($data);

        // 友盟推送
        $this->sendUmengRoundApplyMsg($action_id, $user_id);

        return $ret;
    }

    // 补轮审核友盟通知
    public function sendUmengRoundApplyMsg($action_id, $user_id){
        $umengRecordModel = new UmengRecordModel();
        $deviceTokenList = $umengRecordModel->getCompanyDeviceTokenById($user_id);
        if (!empty($deviceTokenList)) {
            $device_tokens = array_column($deviceTokenList, "device_token");
            $device_tokens = array_filter(array_unique($device_tokens));
            $device_tokens = implode(",", $device_tokens);

            $appkey_ios = C('umeng_appkey_ios');
            $secret_ios = C('ument_appMasterSecret_ios');

            $appkey_android = C('umeng_appkey_android');
            $secret_android = C('ument_appMasterSecret_android');
            $activity_android = C("umeng_activity_android");

            import('Library.Org.Umeng.Umeng', "", ".php");

            $type = 3;
            $alert = "您的申请补轮订单出审核结果啦！";
            $alert2 = "点击查看详情~";

            // ios推送
            $send = new Umeng($appkey_ios, $secret_ios);
            $send->sendIOSListcast($device_tokens, $action_id, $type, $alert);

            //android推送
            $send2 = new Umeng($appkey_android, $secret_android);
            $send2->sendAndroidListcast($device_tokens, $action_id, $type, $alert, $alert, $alert2, 'go_app', 0, true, $activity_android);
        }
    }
}
