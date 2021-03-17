<?php
/**
 * 电话拨打
 */
/**
 * 联通云总机
 * @Author: Administrator
 * @Date:   2017-02-24 10:44:08
 * @Last Modified by:   qz_chk
 * @Last Modified time: 2018-09-13 15:47:33
 */



namespace Home\Model;

import('Library.Org.ChinaunicomCloudTel.ChinaunicomCloudTel',"",".php");

use Think\Model;
use Cuct\ChinaunicomCloudTel;


class TelcuctModel extends Model {

    protected $autoCheckFields = false; //设置autoCheckFields属性为false后，就会关闭字段信息的自动检测，因为ThinkPHP采用的是惰性数据库连接，只要你不进行数据库查询操作，是不会连接数据库的。

    //构造函数
    public function _initialize() {

        //主帐号
        $accountSid = OP('CUCT_ACCOUNTSID');
        //主帐号Token
        $accountToken = OP('CUCT_ACCOUNTTOKEN');
        //应用Id
        $appId = OP('CUCT_APPID_QZ');
        //请求地址
        $serverIP = OP('CUCT_SERVERIP');
        //请求端口
        $serverPort = OP('CUCT_SERVERPORT');
        //REST版本号
        $softVersion = OP('CUCT_SOFTVERSION');
        //子帐号
        $subAccountSid = OP('CUCT_SUBACCOUNTSID');
        //子帐号Token
        $subAccountToken =  OP('CUCT_SUBACCOUNTTOKEN');
        //VoIP帐号
        $voIPAccount = $userInfo['voipaccount'];
        //VoIP密码
        $voIPPassword = $userInfo['voippwd'];

        //如果是开发环境 把请求的服务器地址换成测试地址
        $APP_ENV = C('APP_ENV');
        if ($APP_ENV =='dev') {
            //主帐号
            $accountSid = OP('CUCT_ACCOUNTSID_DEV');
            //主帐号Token
            $accountToken = OP('CUCT_ACCOUNTTOKEN_DEV');
            //应用Id
            $appId = OP('CUCT_APPID_QZ_DEV');
            //请求地址
            $serverIP = OP('CUCT_SERVERIP_DEV');
            //请求端口
            $serverPort = OP('CUCT_SERVERPORT_DEV');
            //REST版本号
            $softVersion = OP('CUCT_SOFTVERSION_DEV');
            //子帐号
            $subAccountSid = OP('CUCT_SUBACCOUNTSID_DEV');
            //子帐号Token
            $subAccountToken =  OP('CUCT_SUBACCOUNTTOKEN_DEV');
            //VoIP帐号
            $voIPAccount = $userInfo['voipaccount'];
            //VoIP密码
            $voIPPassword = $userInfo['voippwd'];
        }

        $this->Cuct = new ChinaunicomCloudTel($accountSid, $accountToken, $appId, $serverIP, $serverPort, $softVersion, $subAccountSid, $subAccountToken, $voIPAccount, $voIPPassword);
    }

    /**
     * 双向回拨
     * 注意在联通云总机, 坐席的主叫号码必须是绑定的手机号
     * @param   $fromTel   主叫
     * @param   $toTel 被叫
     * @return
     */
    public function callBack($fromTel, $toTel)
    {
        $result = $this->Cuct->callBack($fromTel,$toTel);
        //dump($result);
        //rray(4) { 'info' => string(7) "成功!" 'status' => string(1) "1" 'msg' => NULL 'resp' => array(2) { 'respCode' => int(0) 'callBack' => array(2) { 'callId' => string(32) "api001000210901487842166666Lhs0Z" 'createTime' => string(14) "20170223172927" } } }
        return $result;
    }

    /**
     * 通话录音文件 Url
     * @param callId    呼叫 Id       必选
     */
    public function callRecordUrl($callId)
    {
        $result = $this->Cuct->callRecordUrl($callId);
        //         {
        // "resp": {
        // "respCode": 0,
        // "callRecordUrl": {
        // "duration" : 10,
        // "url":
        // "http:\/\/123.138.182.75:1055\/User\/Api\/getFile?access_token=4aab12c43
        // f4e1cfe72746007349f89121447929797KWvkrHZe&res_token=e9f65679712fe7cea942
        // 5969551de1fd1447923530GrrKfppd"
        // }
        // }
        // }
        return $result;
    }

    /**
     * API接口错误代码信息
     * @param $id 错误代码id 可选
    */
    public function errorInfo($code='0') {
       return  $this->Cuct->errorInfo($code); //反馈信息
    }

}