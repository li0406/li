<?php

// +----------------------------------------------------------------------
// | 数据处理中心
// +----------------------------------------------------------------------

namespace Home\Controller;

use Exception;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\FixdataLogic;
use Home\Model\Db\UmengRecordModel;
use Home\Model\Db\OrderInfoModel;

class FixdataController extends HomeBaseController {

    // 修复城市缺单统计数据
    public function fixcityorderlack(){
        exit("access denied.");
        
        $fixdataLogic = new FixdataLogic();
        $success = $fixdataLogic->fixCityOrderLack();

        echo sprintf("执行时间：%s；\n共修复 %d 条数据", date("Y-m-d H:i:s"), $success);
        exit();
    }


    // 测试友盟消息推送
    public function umeng_send_test(){
        exit("access denied.");

        $comid = [551105];
        if (C("APP_ENV") == "dev") {
            $comid = [15157];
        }

        try {
            $umengRecordModel = new UmengRecordModel();
            $tokenList = $umengRecordModel->getDeviceTokenByComidlist($comid);
            if (empty($tokenList)) {
                throw new Exception("未找到device token", 1);
            }

            $appkey_ios = C("umeng_appkey_ios");
            $secret_ios = C("ument_appMasterSecret_ios");

            $appkey_android = C("umeng_appkey_android");
            $secret_android = C("ument_appMasterSecret_android");

            import("Library.Org.Umeng.Umeng", "", ".php");
            $send = new \Library\Org\Umeng\Umeng($appkey_ios, $secret_ios);
            $send2 = new \Library\Org\Umeng\Umeng($appkey_android, $secret_android);

            $type = 2;
            $orderid = "1";
            $alert = "您有新的装修订单，立即查看~";
            $notreadcount = 1;

            $device_token_list = array_column($tokenList, "device_token");

            $result["ios"] = $send->sendIOSListcast($device_token_list, $orderid, $type, $alert, $notreadcount);
            $result["android"] = $send2->sendAndroidListcast($device_token_list, $orderid, $type, $alert, $alert, $alert, "go_app", $notreadcount);


            // $result = [];
            // foreach ($tokenList as $key => $val) {
            //     //ios推送
            //     $result[$key]["ios"] = $send->sendIOSListcast($val["device_token"], $orderid, $type, $alert, $notreadcount);

            //     //android  推送
            //     $result[$key]["android"] = $send2->sendAndroidListcast($val["device_token"], $orderid, $type, $alert, $alert, $alert, "go_app", $notreadcount);
            // }

            $this->ajaxReturn($result);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }


    // 测试友盟消息推送
    public function umeng_send_test2(){
        exit("access denied.");

        $ios_token = "95d4ae9c57b42cba722fa8466dddc96b549dcc58316d70a20d730a6502c89979";
        // $android_token = "AjP8zUtcBv7PpOTRwAuFXIDqgAKjYFZcwCTX1BvADGwV";
        // $android_token = "ArFdK9qCqT3B-rG7zx4sAkSTymQFGPcgwQeN05lkoiQW";

        try {
            $appkey_ios = C("umeng_appkey_ios");
            $secret_ios = C("ument_appMasterSecret_ios");

            $appkey_android = C("umeng_appkey_android");
            $secret_android = C("ument_appMasterSecret_android");
            $activity_android = C("umeng_activity_android");

            import("Library.Org.Umeng.Umeng", "", ".php");
            $send = new \Library\Org\Umeng\Umeng($appkey_ios, $secret_ios);
            $send2 = new \Library\Org\Umeng\Umeng($appkey_android, $secret_android);

            $type = 2;
            $orderid = "1"; 
            $alert = "这是一个测试消息，" . date("Y-m-d H:i:s");
            $notreadcount = 1;

            $result["ios"] = $send->sendIOSListcast($ios_token, $orderid, $type, $alert, $notreadcount);
            // $result["android"] = $send2->sendAndroidListcast($android_token, $orderid, $type, $alert, $alert, $alert, "go_app", $notreadcount, true, $activity_android);

            $this->ajaxReturn($result);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    // 给客服分配订单
    public function setordertokf(){
        $sign = I("get.sign");
        $kefu_id = I("get.kefu_id");
        $kefu_name = I("get.kefu_name");
        $order_id = I("get.order_id");

        if ($sign != date("Ymd")) {
            die("认证失败");
        } else if (empty($kefu_id) || empty($order_id) || empty($kefu_name)) {
            die("参数错误");
        }

        $fixdataLogic = new FixdataLogic();
        $ret = $fixdataLogic->setOrderPoolToKefu($order_id, $kefu_id, $kefu_name);

        exit($ret);
    }


    // 重置用户密码
    public function resetuserpwd(){
        exit("access denied.");
        
        $phoneList = [
            "14526100018",
            "14526100019",
            "14526100020",
            "14526100021",
            "14526100022",
            "17777777869",
            "17777777763",
            "17777773333",
            "17777722233",
            "17777772221",
            "12223333555",
            "12222222333",
            "12222255588",
            "12222999888",
            "12222277744",
            "12229988775",
            "12222654789",
            "12656489794",
            "12222875625",
            "12564898795",
            "12222353654",
            "12223596979",
            "12222222365",
            "12222289745",
            "12222222223",
            "12222568974",
            "12222223546",
            "12222336596",
            "12222223654",
            "12222222288",
            "12222222236",
            "12222254897",
            "12222222265",
            "12222233332",
            "12222332322",
            "12222222321",
            "12222254544",
            "12222228888",
            "12222323146",
            "12222222222"
            ];

        $number = 0;
        foreach ($phoneList as $key => $phone) {
            $map = array(
                "phone" => array("EQ", $phone)
            );

            $data = [
                "password" => password_hash("qz123456", PASSWORD_DEFAULT)
            ];

            $ret = M("ucenter_user")->where($map)->save($data);
            if ($ret !== false) {
                $number++;
            }
        }

        dump($number);exit;
    }
}