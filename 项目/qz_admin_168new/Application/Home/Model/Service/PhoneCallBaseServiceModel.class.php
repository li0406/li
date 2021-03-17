<?php

namespace Home\Model\Service;
/**
 *
 */
abstract class PhoneCallBaseServiceModel
{

    /**
     * 获取用户电话信息
     * @param $user_id
     * @return mixed
     */
   abstract function getPhoneInfo($user_id);

    /**
     * 打电话
     * @param $call_no 主叫号码
     * @param $caller_no 被叫号码
     * @return mixed
     */
   abstract public function call($call_no,$caller_no);

    /**
     * 订单回调日志
     * @param string $value
     * @return mixed
     */
   abstract public function callBackToOrderLog($params);

    /**
     * 销售回调日志
     * @param string $value
     * @return mixed
     */
   abstract public function callBackToSellerLog($params);

    /**
     * 客服回访回调日志
     * @param string $value
     * @return mixed
     */
    abstract public function callBackToOrderReviewLog($params);

    /**
     * 非客服订单回调日志
     * @param string $value
     * @return mixed
     */
    abstract public function callBackToOtherLog($params);
    /**
     * 自助电话回调日志
     * @param string $value
     * @return mixed
     */
    abstract public function callBackToDiyLog($params);
}