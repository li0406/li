<?php
/**
 * 云通讯 电话平台
 */
class Telytx
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

    public function __construct($accountSid, $accountToken, $appId, $serverIP, $serverPort, $softVersion, $subAccountSid, $subAccountToken, $voIPAccount, $voIPPassword)
    {
        //载入扩展 配置
        import('Library.Org.yuntongxun.CCPRestSDK',"",".php");
        //主帐号
        $this->accountSid = $accountSid ?: OP('YTX_ACCOUNTSID');
        //主帐号Token
        $this->accountToken = $accountToken ?: OP('YTX_ACCOUNTTOKEN');
        //应用Id
        $this->appId = $appId ?: OP('YTX_APPID');
        //请求地址
        $this->serverIP = $serverIP ?: OP('YTX_SERVERIP');
        //请求端口
        $this->serverPort = $serverPort ?: OP('YTX_SERVERPORT');
        //REST版本号
        $this->softVersion = $softVersion ?: OP('YTX_SOFTVERSION');
        //子帐号
        $this->subAccountSid = $subAccountSid ?: OP('YTX_SUBACCOUNTSID');
        //子帐号Token
        $this->subAccountToken = $subAccountToken ?: OP('YTX_SUBACCOUNTTOKEN');
        //VoIP帐号
        $this->voIPAccount = $voIPAccount ?: OP('YTX_VOIPACCOUNT01');
        //VoIP密码
        $this->voIPPassword = $voIPPassword ?: OP('YTX_VOIPPASSWORD01');
        // 初始化REST SDK
        //global $appId,$subAccountSid,$subAccountToken,$voIPAccount,$voIPPassword,$serverIP,$serverPort,$softVersion;
        $this->rest = new \REST($this->serverIP,$this->serverPort,$this->softVersion);
        $this->rest->setAccount($this->accountSid,$this->accountToken);
        $this->rest->setSubAccount($this->subAccountSid,$this->subAccountToken,$this->voIPAccount,$this->voIPPassword);
        $this->rest->setAppId($this->appId);
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
    public function callBack($from,$to,$customerSerNum,$fromSerNum,$promptTone) {
            // 调用回拨接口
            $result = $this->rest->callBack($from,$to,$customerSerNum,$fromSerNum,$promptTone);

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
    * 获取子帐号
    * @param startNo 开始的序号，默认从0开始
    * @param offset 一次查询的最大条数，最小是1条，最大是100条
    */
    public function getSubAccounts($appid,$startNo,$offset) {
        // 调用云通讯平台的获取子帐号接口
        $this->rest->setAppId($appid);
        $result = $this->rest->getSubAccounts($startNo,$offset);
        if($result == NULL ) {
            echo "result error!";
            return;
        }
        if($result->statusCode!=0) {
            echo "error code :" . $result->statusCode . "<br/>";
            echo "error msg :" . $result->statusMsg . "<br>";
        }else {
            echo "获取子帐号列表成功<br/><hr>";
            // 获取返回信息
            $subaccount = $result->SubAccount;

/*            for($i =0;$i<count($subaccount);$i++){
                echo '编号：'.$i.'<br>';
               echo "subAccountid:".$subaccount[$i]->subAccountSid."<br/>";
               echo "subToken:".$subaccount[$i]->subToken."<br/>";
               echo "dateCreated:".$subaccount[$i]->dateCreated."<br/>";
               echo "voipAccount:".$subaccount[$i]->voipAccount."<br/>";
               echo "voipPwd:".$subaccount[$i]->voipPwd."<br/>";
               echo "friendlyName:".$subaccount[$i]->friendlyName."<br/>";
               echo "<br/>";
            }*/

            foreach ($subaccount as $key => $value) {
                $value = (array) $value;
                $data                      = array();
                $data['solutions']         = 'yuntongxun'; //voip提供商
                $data['voipAccount']       = $value['voipAccount'];
                $data['voipPwd']           = $value['voipPwd'];
                $data['appid']             = $appid;
                $data['ytx_subAccountSid'] = $value['subAccountSid'];
                $data['ytx_subToken']      = $value['subToken'];
                $data['ytx_friendlyName']  = $value['friendlyName'];

                $isInDB = M('admin_voip_tels')->where($data)->find();
                if(empty($isInDB)){
                    $data['time_add'] = time();
                    M('admin_voip_tels')->add($data);
                    echo $key.' 插入子帐号：'.$data['ytx_subAccountSid'].'<br>';
                }else{
                    echo $key.' 子帐号已存在：'.$data['ytx_subAccountSid'].'<br>';
                }

            }
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

    /**
    * 营销外呼
    * 营销外呼 api说明 http://docs.yuntongxun.com/index.php/%E8%90%A5%E9%94%80%E5%A4%96%E5%91%BC
    * 营销外呼状态通知接口说明http://docs.yuntongxun.com/index.php/%E8%90%A5%E9%94%80%E5%A4%96%E5%91%BC%E7%8A%B6%E6%80%81%E9%80%9A%E7%9F%A5%E6%8E%A5%E5%8F%A3%E8%AF%B4%E6%98%8E
    * @param to 被叫号码
    * @param mediaName 语音文件名称，格式 wav。与mediaTxt不能同时为空。当不为空时mediaTxt属性失效。
    * @param mediaTxt 文本内容
    * @param displayNum 显示的主叫号码
    * @param playTimes 循环播放次数，1－3次，默认播放1次。
    * @param respUrl 营销外呼状态通知回调地址，云通讯平台将向该Url地址发送呼叫结果通知。
    */
    function landingCall($to,$mediaName,$mediaTxt,$displayNum,$playTimes,$respUrl) {
            //dump($this->rest);
            // 调用回拨接口
            $result = $this->rest->landingCall($to,$mediaName,$mediaTxt,$displayNum,$playTimes,$respUrl);
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
                $LandingCall = $result->LandingCall;
                $rearr['info']        = "成功!"; //信息
                $rearr['status']      = '1'; //成功状态
                $rearr['code']        = $result->statusCode; //状态码
                $rearr['msg']         = $result->statusMsg; //反馈信息
                $rearr['callSid']     = $LandingCall->callSid; //拨号id 回拨有效
                $rearr['dateCreated'] = $LandingCall->dateCreated; //拨号创建时间
                return $rearr;
              }
    }


   /**
    * 自动审核透传号码
    * @param 属性  类型  约束  说明  举例
    * @param verifyCode  String  必选  验证码内容，为数字和英文字母，不区分大小写，长度4-8位  123bs
    * @param to  String  必选  接收号码  008613811975505
    * @param playTimes String  可选  播放次数，1－3次   1
    * @param respUrl String  可选  用户接听呼叫后，发起请求通知应用侧。  url
    * @param displayNum  String  可选  显示号码，显示权由服务器控制  01057234444
    * @param feeTypeAuth String  可选  内容格式为“id#”；
    ; 目前可用的值有如下：
    ;0 直拨
    ;1 回拨
    ;5 ivr外呼(pstn)
    ;6 ivr外呼(voip)
    ;13 语音验证码
    ;14 营销外呼 #1#
    */
    function validateDisplayNumbers($verifyCode,$to,$playTimes='3',$respUrl='',$displayNum='',$feeTypeAuth='#1#')
    {
            // 调用回拨接口
            $result = $this->rest->validateDisplayNumbers($verifyCode,$to,$playTimes,$respUrl,$displayNum,$feeTypeAuth);
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
                $validateDisplayNumbers = $result->validateDisplayNumbers;
                $rearr['info']        = "成功!"; //信息
                $rearr['status']      = '1'; //成功状态
                $rearr['code']        = $result->statusCode; //状态码
                $rearr['msg']         = $result->statusMsg; //反馈信息
                $rearr['callSid']     = $validateDisplayNumbers->callSid; //拨号id 回拨有效
                $rearr['dateCreated'] = $validateDisplayNumbers->dateCreated; //拨号创建时间
                return $rearr;
              }
    }

}
?>