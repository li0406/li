<?php
namespace app\common\model\service;

use app\common\model\logic\HollyLogic;
use app\common\model\service\PhoneCallBaseService;
use app\common\model\db\Adminuser;
use Holly\Holly;

/**
 *
 */
class PhoneCallHollyService extends PhoneCallBaseService
{

    private $solution = "holly";

    /**
     * 获取用户电话信息
     * @param $user_id
     * @return mixed
     */
    function getPhoneInfo($user_id)
    {
        $model = new Adminuser();
        $userInfo = $model->findUserVoipInfo($user_id, $this->solution);
        if (count($userInfo) > 0) {
            return $userInfo;
        }
        return [];
    }

    /**
     * 打电话
     * @param $call_no 主叫号码
     * @param $caller_no 被叫号码
     * @return mixed
     */
    public function call($call_no, $caller_no)
    {
        $holly = new Holly();
        $holly->GenToken();
        $call_sid = $holly->uuid(); // call_sid
        $ExternalData = "CallSid:". $call_sid; // 扩展数据 callsid, : 分割kv  , 分割组
        $callResult = $holly->dialout($call_no,$caller_no,'', $ExternalData);

        if ($callResult["api_resp"] == "200") {
            return array("error_code" =>$callResult["api_resp"],"error_msg" => $callResult["api_msg"],"data" => $call_sid );
        }
        return array("error_code" =>$callResult["api_resp"],"error_msg" => $callResult["api_msg"] );
    }

    /**
     * 订单回调日志
     * @param string $value
     * @return mixed
     */
    public function callBackToOrderLog($params)
    {

    }

    /**
     * 销售回调日志
     * @param string $value
     * @return mixed
     */
    public function callBackToSellerLog($params)
    {
        $model = new HollyLogic();
        return $model->addReportTelLog($params);
    }

    /**
     * 客服回访回调日志
     * @param string $value
     * @return mixed
     */
    public function callBackToOrderReviewLog($params)
    {

    }

    /**
     * 非客服订单回调日志
     * @param string $value
     * @return mixed
     */
    public function callBackToOtherLog($params)
    {

    }
}