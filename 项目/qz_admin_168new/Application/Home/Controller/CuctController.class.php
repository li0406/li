<?php
/**
 * 联通云总机调用
 * ChinaunicomCloudtel
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/20 0020
 * Time: 8:56
 */
namespace Home\Controller;

import('Library.Org.ChinaunicomCloudTel.ChinaunicomCloudTel',"",".php");

use Home\Common\Controller\HomeBaseController;
use Cuct\ChinaunicomCloudTel;

class CuctController extends HomeBaseController
{
    //构造
    public function _initialize(){
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

    public function  index()
    {
        echo 'cuct index';
        dump($this->Cuct);
    }




    public function callBack()
    {
        $fromTel = '17625293816'; //主叫
        //$toTel   = '18550210727';
        $toTel   = '15922943816'; //被叫
        $result = $this->Cuct->callBack($fromTel,$toTel);
        dump($result);
    }

    /**
     *  获取空闲总机号码
     * @param
     *
     *   用于从云总机开放平台获取本子账户专用云总机企业中有哪些空闲的直线号码，这些号码可用于绑定用户手机号码，使之成为直线用户
     */
    public function freeNumbers()
    {
        $result = $this->Cuct->freeNumbers();
        dump($result['resp']['freeNumbers']);
    }

    /**
     *  创建企业用户
     * @param phone 用户绑定电话号码:
    要求号码长度至少为 10 位，
    可以是手机号（以 1 开头），
    可以是固话号码（加区号，如 02566687765），
    也可以是400 和 800 开始的号码  必选
     * @param displayName 用户显示名称，不输入时用用户绑定号码代替  可选
     * @param directNumber  用户显示名称，不输入时用用户绑定号码代替   可选
     * @param callTime  用户呼叫时间限制（分钟）     可选
     */
    public function createUser()
    {
        $phone = '18915506238';
        //$displayName  = $phone;
        //$directNumber = '051286865125';
        //$callTime     = '99999';
        $result = $this->Cuct->createUser($phone, $displayName, $directNumber, $callTime);
        dump($result['msg']);
        dump($result['resp']);
    }

    /**
     *  删除企业用户
     * @param phone 用户绑定电话号码  必选
     * @param
     * @param
     * @param
     */
    public function dropUser($phone)
    {
        $phone = '18915506238';
        //$displayName  = $phone;
        //$directNumber = '051286865125';
        //$callTime     = '99999';
        $result = $this->Cuct->dropUser($phone);
        dump($result['msg']);
        dump($result['resp']);
    }

    /**
     *  更新企业用户信息
     * @param phone 用户绑定电话号码  必选
     * @param displayName 用户名称  可选
     * @param directNumber 用户直线号码：
    该参数为字符串‘0’时表示释放用户绑定的直线号码；
    不带该参数（或该参数为空字符串），表示保持不变  可选
     * @param callTime 用户呼叫时间限制（分钟） 可选
     */
    public function updateUser($phone, $displayName='', $directNumber='', $callTime='')
    {
        $phone = '18915506238';
        //$displayName  = $phone;
        //$directNumber = '051286865125';
        //$callTime     = '99999';
        $result = $this->Cuct->updateUser($phone, $displayName, $directNumber='', $callTime='');
        dump($result['msg']);
        dump($result['resp']);
    }


    /**
     * 创建子账户
     * @param nickName 子账号昵称
     * @param mobile 子账号用户手机号码
     * @param email 子账号用户邮件地址
     */
    public function createSubAccount($nickName='', $mobile='', $email='')
    {
        $result = $this->Cuct->createSubAccount($nickName, $mobile, $email);
        dump($result);
    }

    /**
     * 查询子账户列表
     */
    public function subAccountList()
    {
        $result = $this->Cuct->subAccountList();
        dump($result['resp']['subAccountList']);
    }


}