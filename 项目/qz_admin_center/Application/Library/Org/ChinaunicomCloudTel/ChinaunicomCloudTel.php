<?php
/*
 * 联通云总机 业务调用使用本类
 * first created by wwek @ 2017年2月16日 10:25:03
 */


namespace Cuct;

use Cuct\CuctRestSDK;

require_once(dirname(__FILE__).'/CuctRestSDK.php');

class ChinaunicomCloudTel
{

    //主帐号
    public    $accountSid;
    //主帐号Token
    public    $accountToken;
    //应用Id
    public    $appId;
    //请求地址
    public    $serverIP;
    //请求端口
    public    $serverPort;
    //REST版本号
    public    $softVersion;

    //子帐号
    public    $subAccountSid;
    //子帐号Token
    public    $subAccountToken;
    //VoIP帐号
    public    $voIPAccount;
    //VoIP密码
    public    $voIPPassword;

    public function __construct($accountSid, $accountToken, $appId, $serverIP, $serverPort, $softVersion, $subAccountSid, $subAccountToken, $voIPAccount, $voIPPassword) {

        //主帐号
        $this->accountSid = $accountSid;
        //主帐号Token
        $this->accountToken = $accountToken;
        //应用Id
        $this->appId = $appId;
        //请求地址
        $this->serverIP = $serverIP;
        //请求端口
        $this->serverPort = $serverPort;
        //REST版本号
        $this->softVersion = $softVersion;
        //子帐号
        $this->subAccountSid = $subAccountSid;
        //子帐号Token
        $this->subAccountToken = $subAccountToken;
        //VoIP帐号
        $this->voIPAccount = $voIPAccount;
        //VoIP密码
        $this->voIPPassword = $voIPPassword;
        // 初始化REST SDK
        //global $appId,$subAccountSid,$subAccountToken,$voIPAccount,$voIPPassword,$serverIP,$serverPort,$softVersion;
        $this->rest = new CuctRestSDK($this->serverIP,$this->serverPort,$this->softVersion);
        //打开调试,在网站根目录打印日志  **特别注意,生产环境本项参数必须是 false 或者注释掉setDebug不执行**
        $this->rest->setDebug(false);
        $this->rest->setAccount($this->accountSid,$this->accountToken);
        $this->rest->setSubAccount($this->subAccountSid,$this->subAccountToken,$this->voIPAccount,$this->voIPPassword);
        $this->rest->setAppId($this->appId);
    }


    /**
     * 创建子账户
     * @param nickName 子账号昵称
     * @param mobile 子账号用户手机号码
     * @param email 子账号用户邮件地址
     */
    function createSubAccount($nickName='', $mobile='', $email='')
    {
        $result = $this->rest->createSubAccount($nickName, $mobile, $email);

        if($result == NULL ) {
            $rearr['info']   = "失败,返回为空!"; //信息
            $rearr['status'] = '0'; //成功状态
            return $rearr;
        }

        if($result['resp']['respCode']!= '00000') {
            $rearr['info']       = "失败!"; //信息
            $rearr['status']     = '0'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        } else {
            $rearr['info']       = "成功!"; //信息
            $rearr['status']     = '1'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        }
    }

    /**
     * 查询子账户列表
     */
    function subAccountList()
    {
        $result = $this->rest->subAccountList();

        if($result == NULL ) {
            $rearr['info']   = "失败,返回为空!"; //信息
            $rearr['status'] = '0'; //成功状态
            return $rearr;
        }

        if($result['resp']['respCode']!= '00000') {
            $rearr['info']       = "失败!"; //信息
            $rearr['status']     = '0'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        } else {
            $rearr['info']       = "成功!"; //信息
            $rearr['status']     = '1'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        }
    }

    /**
     * 查询子账户
     * @param subAccountSid 	子账户 Sid
     */
    function subAccount($subAccountSid)
    {
        $result = $this->rest->subAccount($subAccountSid);

        if($result == NULL ) {
            $rearr['info']   = "失败,返回为空!"; //信息
            $rearr['status'] = '0'; //成功状态
            return $rearr;
        }

        if($result['resp']['respCode']!= '00000') {
            $rearr['info']       = "失败!"; //信息
            $rearr['status']     = '0'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        } else {
            $rearr['info']       = "成功!"; //信息
            $rearr['status']     = '1'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        }
    }

    /**
     * 删除子账户
     * @param subAccountSid 	子账户 Sid
     */
    function dropSubAccount($subAccountSid)
    {
        $result = $this->rest->dropSubAccount($subAccountSid);

        if($result == NULL ) {
            $rearr['info']   = "失败,返回为空!"; //信息
            $rearr['status'] = '0'; //成功状态
            return $rearr;
        }

        if($result['resp']['respCode']!= '00000') {
            $rearr['info']       = "失败!"; //信息
            $rearr['status']     = '0'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        } else {
            $rearr['info']       = "成功!"; //信息
            $rearr['status']     = '1'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        }
    }

    /**
     * 更新子账户
     * @param subAccountSid 	子账户 Sid 	必选
     * @param mobile 	子账户用户手机号码 	可选
     * @param email 	子账户用户邮箱地址 	可选
     * @param nickName  子账户昵称 	可选

     */
    function updateSubAccount($subAccountSid, $mobile='', $email='', $nickName='')
    {
        $result = $this->rest->updateSubAccount($subAccountSid, $mobile, $email, $nickName);

        if($result == NULL ) {
            $rearr['info']   = "失败,返回为空!"; //信息
            $rearr['status'] = '0'; //成功状态
            return $rearr;
        }

        if($result['resp']['respCode']!= '00000') {
            $rearr['info']       = "失败!"; //信息
            $rearr['status']     = '0'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        } else {
            $rearr['info']       = "成功!"; //信息
            $rearr['status']     = '1'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        }
    }

    /**
     * 双向回拨功能
     * @param from 主叫电话号码  必选
     * @param to 被叫电话号码, 如果做多方通话,to字段可以是多个号码，每个号码之间用逗号隔开  必选
     * @param displayMode  被叫来电号码显示方式： 0-显示总机固话号码（默认方式）； 1-显示主叫号码； 可选
     * @param getFeedBack 通话过程中用户的按键反馈：
    0-	不开启按键反馈功能（默认方式）
    1-	获取被叫按键反馈
    2-	获取主叫按键反馈
    3-	获取主叫、被叫按键反馈  	可选
     * @param feedBackMode 	通话过程中用户按键方式：
    0	– 一键模式；
    1	– 普通模式（按“#”键结束） 	可选
     * @param keyRange 	一键反馈模式中，用户按键的有效范围，用
    逗号隔开字符。如：0，1，2，3，4，5，*，
    #标识为有效按键  	可选
     * @param userData 	用户透传数据，回调时返回给用户，可用于认证  可选
     */
    function callBack($from, $to, $displayMode='0', $getFeedBack='0', $feedBackMode='', $keyRange='', $userData='')
    {
        // 调用回拨接口
        $result = $this->rest->callBack($from, $to, $displayMode, $getFeedBack, $feedBackMode, $keyRange, $userData);

        if($result == NULL ) {
            $rearr['info']   = "失败,返回为空!"; //信息
            $rearr['status'] = '0'; //成功状态
            return $rearr;
        }

        if($result['resp']['respCode']!= '00000') {
            $rearr['info']       = "失败!"; //信息
            $rearr['status']     = '0'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        } else {
            $rearr['info']       = "成功!"; //信息
            $rearr['status']     = '1'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        }
    }

    /**
     * 电话直拨功能
     * @param from 主叫电话号码  必选
     * @param to 被叫电话号码  必选
     * @param userData 	用户透传数据，回调时返回给用户，可用于认证 	可选
     */
    function directCall($from, $to, $userData='')
    {
        $result = $this->rest->directCall($from, $to, $userData);

        if($result == NULL ) {
            $rearr['info']   = "失败,返回为空!"; //信息
            $rearr['status'] = '0'; //成功状态
            return $rearr;
        }

        if($result['resp']['respCode']!= '00000') {
            $rearr['info']       = "失败!"; //信息
            $rearr['status']     = '0'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        } else {
            $rearr['info']       = "成功!"; //信息
            $rearr['status']     = '1'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        }
    }

    /**
     * 语音验证码功能
     * @param verifyCode 验证号码，现规定为 4 位数字 	必选
     * @param to 验证码拨叫号码 	必选
     * @param userData 	用户透传数据，回调时返回给用户，可用于认证 可选
     */
    function voiceCode($verifyCode, $to, $userData='')
    {
        $result = $this->rest->voiceCode($verifyCode, $to, $userData);

        if($result == NULL ) {
            $rearr['info']   = "失败,返回为空!"; //信息
            $rearr['status'] = '0'; //成功状态
            return $rearr;
        }

        if($result['resp']['respCode']!= '00000') {
            $rearr['info']       = "失败!"; //信息
            $rearr['status']     = '0'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        } else {
            $rearr['info']       = "成功!"; //信息
            $rearr['status']     = '1'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        }
    }

    /**
     * 语音通知功能
     * @param voiceId 语音文件id 必选
     * @param to 被叫号码 必选
     * @param userData 	用户透传数据，回调时返回给用户，可用于认证 可选
     */
    function voiceNotify($voiceId, $to, $userData='')
    {
        $result = $this->rest->voiceNotify($verifyCode, $to, $userData);

        if($result == NULL ) {
            $rearr['info']   = "失败,返回为空!"; //信息
            $rearr['status'] = '0'; //成功状态
            return $rearr;
        }

        if($result['resp']['respCode']!= '00000') {
            $rearr['info']       = "失败!"; //信息
            $rearr['status']     = '0'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        } else {
            $rearr['info']       = "成功!"; //信息
            $rearr['status']     = '1'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        }
    }

    /**
     * 通话取消功能
     * @param callId 	呼叫 Id 		必选
     */
    function callCancel($callId)
    {
        $result = $this->rest->callCancel($callId);

        if($result == NULL ) {
            $rearr['info']   = "失败,返回为空!"; //信息
            $rearr['status'] = '0'; //成功状态
            return $rearr;
        }

        if($result['resp']['respCode']!= '00000') {
            $rearr['info']       = "失败!"; //信息
            $rearr['status']     = '0'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        } else {
            $rearr['info']       = "成功!"; //信息
            $rearr['status']     = '1'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        }
    }

    /**
     * 获取指定话单详情
     * @param callId 	呼叫 Id 		必选
     */
    function callDetail($callId)
    {
        $result = $this->rest->callDetail($callId);

        if($result == NULL ) {
            $rearr['info']   = "失败,返回为空!"; //信息
            $rearr['status'] = '0'; //成功状态
            return $rearr;
        }

        if($result['resp']['respCode']!= '00000') {
            $rearr['info']       = "失败!"; //信息
            $rearr['status']     = '0'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        } else {
            $rearr['info']       = "成功!"; //信息
            $rearr['status']     = '1'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        }
    }

    /**
     * 通话录音文件 Url
     * @param callId 	呼叫 Id 		必选
     */
    function callRecordUrl($callId)
    {
        $result = $this->rest->callRecordUrl($callId);

        if($result == NULL ) {
            $rearr['info']   = "失败,返回为空!"; //信息
            $rearr['status'] = '0'; //成功状态
            return $rearr;
        }

        if($result['resp']['respCode']!= '00000') {
            $rearr['info']       = "失败!"; //信息
            $rearr['status']     = '0'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        } else {
            $rearr['info']       = "成功!"; //信息
            $rearr['status']     = '1'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        }
    }

    /**
     *  获取空闲总机号码
     * @param
     *
     *   用于从云总机开放平台获取本子账户专用云总机企业中有哪些空闲的直线号码，这些号码可用于绑定用户手机号码，使之成为直线用户
     */
    function freeNumbers()
    {
        $result = $this->rest->freeNumbers();

        if($result == NULL ) {
            $rearr['info']   = "失败,返回为空!"; //信息
            $rearr['status'] = '0'; //成功状态
            return $rearr;
        }

        if($result['resp']['respCode']!= '00000') {
            $rearr['info']       = "失败!"; //信息
            $rearr['status']     = '0'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        } else {
            $rearr['info']       = "成功!"; //信息
            $rearr['status']     = '1'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        }
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
    function createUser($phone, $displayName='', $directNumber='', $callTime='')
    {
        $result = $this->rest->createUser($phone, $displayName, $directNumber, $callTime);

        if($result == NULL ) {
            $rearr['info']   = "失败,返回为空!"; //信息
            $rearr['status'] = '0'; //成功状态
            return $rearr;
        }

        if($result['resp']['respCode']!= '00000') {
            $rearr['info']       = "失败!"; //信息
            $rearr['status']     = '0'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        } else {
            $rearr['info']       = "成功!"; //信息
            $rearr['status']     = '1'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        }
    }

    /**
     *  删除企业用户
     * @param phone 用户绑定电话号码  必选
     * @param
     * @param
     * @param
     */
    function dropUser($phone)
    {
        $result = $this->rest->dropUser($phone);

        if($result == NULL ) {
            $rearr['info']   = "失败,返回为空!"; //信息
            $rearr['status'] = '0'; //成功状态
            return $rearr;
        }

        if($result['resp']['respCode']!= '00000') {
            $rearr['info']       = "失败!"; //信息
            $rearr['status']     = '0'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        } else {
            $rearr['info']       = "成功!"; //信息
            $rearr['status']     = '1'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        }
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
    function updateUser($phone, $displayName='', $directNumber='', $callTime='')
    {
        $result = $this->rest->updateUser($phone, $displayName, $directNumber, $callTime);

        if($result == NULL ) {
            $rearr['info']   = "失败,返回为空!"; //信息
            $rearr['status'] = '0'; //成功状态
            return $rearr;
        }

        if($result['resp']['respCode']!= '00000') {
            $rearr['info']       = "失败!"; //信息
            $rearr['status']     = '0'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        } else {
            $rearr['info']       = "成功!"; //信息
            $rearr['status']     = '1'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        }
    }

    /**
     *  更新企业用户绑定号码
     * @param number 用户分机号（输入条件）  必选
     * @param phone 用户新绑定号码  必选
     * @param
     * @param
     */
    function updateUserPhone($number, $phone)
    {
        $result = $this->rest->updateUserPhone($number, $phone);

        if($result == NULL ) {
            $rearr['info']   = "失败,返回为空!"; //信息
            $rearr['status'] = '0'; //成功状态
            return $rearr;
        }

        if($result['resp']['respCode']!= '00000') {
            $rearr['info']       = "失败!"; //信息
            $rearr['status']     = '0'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        } else {
            $rearr['info']       = "成功!"; //信息
            $rearr['status']     = '1'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        }
    }

    /**
     *  获取用户信息
     * @param
     * @param phone 用户绑定电话号码  必选
     * @param
     * @param
     */
    function userInfo($phone)
    {
        $result = $this->rest->userInfo($phone);

        if($result == NULL ) {
            $rearr['info']   = "失败,返回为空!"; //信息
            $rearr['status'] = '0'; //成功状态
            return $rearr;
        }

        if($result['resp']['respCode']!= '00000') {
            $rearr['info']       = "失败!"; //信息
            $rearr['status']     = '0'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        } else {
            $rearr['info']       = "成功!"; //信息
            $rearr['status']     = '1'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        }
    }




    /**
     * 主帐号信息查询
     */
    function AccountInfo()
    {
        $result = $this->rest->AccountInfo();

        if($result == NULL ) {
            $rearr['info']   = "失败,返回为空!"; //信息
            $rearr['status'] = '0'; //成功状态
            return $rearr;
        }

        if($result['resp']['respCode']!= '00000') {
            $rearr['info']       = "失败!"; //信息
            $rearr['status']     = '0'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        } else {
            $rearr['info']       = "成功!"; //信息
            $rearr['status']     = '1'; //成功状态
            $rearr['msg']        = $this->rest->errorInfo($result['resp']['respCode']); //反馈信息
            $rearr['resp']       = $result['resp'];
            return $rearr;
        }
    }
}