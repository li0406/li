<?php

namespace Home\Model\Logic;

use Home\Model\LogSmsSendModel;

class LogSmsSendLogicModel {

    private $logSmsSendModel;

    // 短信类型
    const LOG_TYPE_FPGS = 1; // 通知业主分配的装修公司
    const LOG_TYPE_WJTDH = 2; // 未接通电话
    const LOG_TYPE_TZZXGS = 3; // 通知装修公司短信
    const LOG_TYPE_JJFW = 4; // 拒绝服务
    const LOG_TYPE_QYWX = 5; // 企业微信
    const LOG_TYPE_HFWJT = 6; // 回访未接通
    const LOG_TYPE_BEFORECALL = 7; //打电话前通知

    public function __construct(){
        $this->logSmsSendModel = new logSmsSendModel();
    }

    // 查询发送记录
    public function getOrderTypeDayCount($order_id, $type, $date){
        return $this->logSmsSendModel->getOrderTypeDayCount($order_id, $type, $date);
    }

    // 查询发送记录
    public function getOrderTypeCount($order_id, $type){
        return $this->logSmsSendModel->getOrderSendLogCount($order_id, $type);
    }

    // 添加日志
    public function addLog($data){
        return $this->logSmsSendModel->addLog($data);
    }
}