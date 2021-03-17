<?php
/**
 * 共用短信发送
 */
class Telytx{
    public function __construct() {
        //云通讯 电话平台
        //载入扩展 配置
        //主帐号
        $accountSid      = OP('YTX_ACCOUNTSID');
        //主帐号Token
        $accountToken    = OP('YTX_ACCOUNTTOKEN');
        //应用Id
        $appId           = OP('YTX_APPID');
        //子帐号
        $subAccountSid   = OP('YTX_SUBACCOUNTSID');
        //子帐号Token
        $subAccountToken = OP('YTX_SUBACCOUNTTOKEN');
        //VoIP帐号
        $voIPAccount     = OP('YTX_VOIPACCOUNT01');
        //VoIP密码
        $voIPPassword    = OP('YTX_VOIPPASSWORD01');
        //请求地址
        $serverIP        = OP('YTX_SERVERIP');
        //请求端口
        $serverPort      = OP('YTX_SERVERPORT');
        //REST版本号
        $softVersion     = OP('YTX_SOFTVERSION');

        // 初始化REST SDK
        //global $appId,$subAccountSid,$subAccountToken,$voIPAccount,$voIPPassword,$serverIP,$serverPort,$softVersion;
        import('Library.Org.yuntongxun.CCPRestSDK','','.php');
        $this->rest = new \REST($serverIP,$serverPort,$softVersion);
        $this->rest->setAccount($accountSid,$accountToken);
        $this->rest->setSubAccount($subAccountSid,$subAccountToken,$voIPAccount,$voIPPassword);
        $this->rest->setAppId($appId);
    }

    /**
     * 双向回呼
     * callBack("主叫电话号码","被叫电话号码","被叫侧显示的客服号码","主叫侧显示的号码","自定义回拨提示音");
     *
     * @param from 主叫电话号码
     * @param to 被叫电话号码
     * @param customerSerNum 被叫侧显示的客服号码
     * @param fromSerNum 主叫侧显示的号码
     * @param promptTone 自定义回拨提示音
     */
    function callBack($from,$to,$customerSerNum,$fromSerNum,$promptTone) {
        //dump($this->rest);
        // 调用回拨接口
        $result = $this->rest->callBack($from,$to,$customerSerNum,$fromSerNum,$promptTone);
        //dump($result);
        if($result == NULL ) {
            $rearr['info']   = "失败,返回为空!"; //信息
            $rearr['status'] = '0'; //成功状态
            return $rearr;
        }
        if($result->statusCode!=0) {
          $rearr['info']   = "失败!"; //信息
          $rearr['status'] = '0'; //成功状态
          $rearr['code']   = $result->statusCode; //状态码
          $rearr['msg']    = $result->statusMsg; //反馈信息
          return $rearr;
        } else {
          // 获取返回信息
          $callback = $result->CallBack;
          $rearr['info']        = "成功!"; //信息
          $rearr['status']      = '1'; //成功状态
          $rearr['code']        = $result->statusCode; //状态码
          $rearr['msg']         = $result->statusMsg; //反馈信息
          $rearr['callSid']     = $callback->callSid; //拨号id 回拨有效
          $rearr['dateCreated'] = $callback->dateCreated; //拨号创建时间
          return $rearr;
        }
    }

    /**
      * 语音验证码
      * voiceVerify("验证码内容","循环播放次数","接收号码","显示的主叫号码","营销外呼状态通知回调地址");
      *
      * @param verifyCode 验证码内容，为数字和英文字母，不区分大小写，长度4-8位
      * @param playTimes 播放次数，1－3次
      * @param to 接收号码
      * @param displayNum 显示的主叫号码
      * @param respUrl 语音验证码状态通知回调地址，云通讯平台将向该Url地址发送呼叫结果通知
      */
    function voiceVerify($verifyCode,$playTimes,$to,$displayNum,$respUrl)
    {
        //调用语音验证码接口
        echo "Try to make a voiceverify,called is $to <br/>";
        $result = $this->rest->voiceVerify($verifyCode,$playTimes,$to,$displayNum,$respUrl);
         if($result == NULL ) {
            echo "result error!";
            return;
        }

        if($result->statusCode!=0) {
            echo "error code :" . $result->statusCode . "<br>";
            echo "error msg :" . $result->statusMsg . "<br>";
            //TODO 添加错误处理逻辑
        } else{
            echo "voiceverify success!<br>";
            // 获取返回信息
            $voiceVerify = $result->VoiceVerify;
            echo "callSid:".$voiceVerify->callSid."<br/>";
            echo "dateCreated:".$voiceVerify->dateCreated."<br/>";
           //TODO 添加成功处理逻辑
        }
    }

    /**
     * 创建子帐号
     * createSubAccount("子帐号名称")
     * @param friendlyName 子帐号名称
     */
    function createSubAccount($friendlyName) {
        // 调用云通讯平台的创建子帐号,绑定您的子帐号名称
        echo "Try to create a subaccount, binding to user $friendlyName <br/>";
        $result = $this->rest->CreateSubAccount($friendlyName);
        if($result == NULL ) {
            echo "result error!";
            return;
        }
        if($result->statusCode!=0) {
            echo "error code :" . $result->statusCode . "<br/>";
            echo "error msg :" . $result->statusMsg . "<br>";
            //TODO 添加错误处理逻辑
        }else {
            echo "create SubbAccount success<br/>";
            // 获取返回信息
            $subaccount = $result->SubAccount;
            echo "subAccountid:".$subaccount->subAccountSid."<br/>";
            echo "subToken:".$subaccount->subToken."<br/>";
            echo "dateCreated:".$subaccount->dateCreated."<br/>";
            echo "voipAccount:".$subaccount->voipAccount."<br/>";
            echo "voipPwd:".$subaccount->voipPwd."<br/>";
            //TODO 把云平台子帐号信息存储在您的服务器上.
            //TODO 添加成功处理逻辑
        }
    }

    /**
      * IVR外呼
      * //ivrDial("待呼叫号码","用户数据","是否录音")
      * @param number   待呼叫号码，为Dial节点的属性
      * @param userdata 用户数据，在<startservice>通知中返回，只允许填写数字字符，为Dial节点的属性
      * @param record   是否录音，可填项为true和false，默认值为false不录音，为Dial节点的属性
      */
    function ivrDial($number,$userdata,$record)
    {
        // 调用IVR外呼接口
        $result = $this->rest->ivrDial($number,$userdata,$record);
        if($result == NULL ) {
            echo "result error!";
            return;
        }
        if($result->statusCode!=0) {
            echo "error code :" . $result->statusCode . "<br>";
            echo "error msg :" . $result->statusMsg . "<br>";
            //TODO 添加错误处理逻辑
        }else{
            echo "ivrDial success!<br/>";
            //获取返回信息
            echo "callSid:".$result->callSid."<br/>";
            //TODO 添加成功处理逻辑
        }
    }


    /**
      * sendTemplateSMS("手机号码","内容数据","模板Id")
      * 发送模板短信
      * @param to 手机号码集合,用英文逗号分开
      * @param datas 内容数据 格式为数组 例如：array('Marry','Alon')，如不需替换请填 null
      * @param $tempId 模板Id
      * @return  arr [description]
      */

    function sendTemplateSMS($to,$datas,$tempId)
    {
        // 发送模板短信
        $result = $this->rest->sendTemplateSMS($to,$datas,$tempId);
        if($result == NULL ) {
             $rearr['info']   = "错误,实例化失败!"; //信息
             $rearr['status'] = '0'; //成功状态
             $rearr['code']   = ''; //状态码
             $rearr['msg']    = ''; //反馈信息
             return $rearr;
        }
        if($result->statusCode!=0) {
             //失败
             $rearr['info']   = '失败!'; //信息
             $rearr['status'] = '0'; //成功状态
             $rearr['code']   = $result->statusCode; //状态码
             $rearr['msg']    = $result->statusMsg; //反馈信息
              //dump($rearr);
             return $rearr;
        }else{
            // 获取返回信息
            $smsmessage = $result->TemplateSMS;

             //TODO 添加成功处理逻辑
             $rearr['info']          = '成功!'; //信息
             $rearr['status']        = '1'; //成功状态
             $rearr['code']          = $result->statusCode; //状态码
             $rearr['msg']           = $result->statusMsg; //反馈信息
             $rearr['dateCreated']   = $smsmessage->dateCreated; //反馈信息
             $rearr['smsMessageSid'] = $smsmessage->smsMessageSid; //反馈信息
             //dump($rearr);
             return $rearr;
         }
    }
}