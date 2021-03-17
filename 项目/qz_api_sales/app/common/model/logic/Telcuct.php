<?php
/**
 * 电话拨打
 */
/**
 * 联通云总机
 */
namespace app\common\model\logic;

use ChinaunicomCloudTel\ChinaunicomCloudTel;

class Telcuct {

    protected $autoCheckFields = false; //设置autoCheckFields属性为false后，就会关闭字段信息的自动检测，因为ThinkPHP采用的是惰性数据库连接，只要你不进行数据库查询操作，是不会连接数据库的。
    private $user;
    private $Cuct;
    //构造函数
    public function __construct($userInfo = []) {
        $this->user = $userInfo;
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
        $voIPAccount = isset($userInfo['voipaccount']) ? $userInfo['voipaccount'] : '';
        //VoIP密码
        $voIPPassword = isset($userInfo['voippwd']) ? $userInfo['voippwd'] : '';

        //如果是开发环境 把请求的服务器地址换成测试地址
        $app_status = config('app.app_status');
        if (!empty($app_status) && $app_status == 'dev') {
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
//        rray(4) { 'info' => string(7) "成功!" 'status' => string(1) "1" 'msg' => NULL 'resp' => array(2) { 'respCode' => int(0) 'callBack' => array(2) { 'callId' => string(32) "api001000210901487842166666Lhs0Z" 'createTime' => string(14) "20170223172927" } } }
//        return [
//            'info'=>'成功!',
//            'status'=>'1',
//            'msg'=>NULL,
//            'resp'=>[
//                'respCode'=>0,
//                'callBack'=>[
//                    'callId'=>'api001000210901487842166666Lhs0Z',
//                    'createTime'=>'20170223172927',
//                ],
//            ],
//        ];
        return $result;
    }

    /**
     * 通话录音文件 Url
     * @param callId    呼叫 Id       必选
     */
    public function callRecordUrl($callId)
    {
        $result = $this->Cuct->callRecordUrl($callId);
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