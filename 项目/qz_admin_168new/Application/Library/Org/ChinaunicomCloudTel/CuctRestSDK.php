<?php
/*
 * 联通云总机SDK
 *
 * fork from 容联云通讯SDK
 * first created by wwek @ 2017年2月16日 10:25:03
 */

namespace Cuct;

class CuctRestSDK
{
	private $AccountSid;
	private $AccountToken;
	private $AppId;
	private $SubAccountSid;
	private $SubAccountToken;
	private $VoIPAccount;
	private $VoIPPassword;
	private $ServerIP;
	private $ServerPort;
	private $SoftVersion;
	private $Batch;  //时间戳
	private $BodyType = "json";//包体格式，可填值：json 、xml
	private $enabeLog = false; //日志开关。可填值：true、
	private $Filename = "./logcucttel.txt"; //日志文件
	private $Handle;

  	public  $Sig;

	function __construct($ServerIP,$ServerPort,$SoftVersion)
	{
		$this->Batch       = date("YmdHms");
		$this->ServerIP    = $ServerIP;
		$this->ServerPort  = $ServerPort;
		$this->SoftVersion = $SoftVersion;

	}

	/**
	 * 设置调试模式 打开日志
     *
     * @param log  false or true
     */
	function setDebug($log=false)
    {
        if ($log){
            $this->Handle   = fopen($this->Filename, 'a');  //关闭创建日志句柄
            $this->enabeLog = true; //日志开关。可填值：true、
        }
    }

   /**
    * 设置主帐号
    *
    * @param AccountSid 主帐号
    * @param AccountToken 主帐号Token
    */
    function setAccount($AccountSid,$AccountToken)
    {
      $this->AccountSid = $AccountSid;
      $this->AccountToken = $AccountToken;
    }

   /**
    * 设置子帐号
    *
    * @param SubAccountSid 子帐号
    * @param SubAccountToken 子帐号Token
    * @param VoIPAccount VoIP帐号
    * @param VoIPPassword VoIP密码
    */
    function setSubAccount($SubAccountSid,$SubAccountToken,$VoIPAccount,$VoIPPassword)
    {
      $this->SubAccountSid = $SubAccountSid;
      $this->SubAccountToken = $SubAccountToken;
      $this->VoIPAccount = $VoIPAccount;
      $this->VoIPPassword = $VoIPPassword;

    }

   /**
    * 设置应用ID
    *
    * @param AppId 应用ID
    */
    function setAppId($AppId)
    {
       $this->AppId = $AppId;
    }

   /**
    * 打印日志
    *
    * @param log 日志内容
    */
    function showlog($log){
      if($this->enabeLog){
         fwrite($this->Handle,$log."\n");
      }
    }

    /**
     * 发起HTTPS请求
     */
     function curl_post($url,$data,$header,$post=1)
     {
       //初始化curl
       $ch = curl_init();
       //参数设置
       $res= curl_setopt ($ch, CURLOPT_URL,$url);
       curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
       curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
       curl_setopt ($ch, CURLOPT_HEADER, 0);
       curl_setopt($ch, CURLOPT_POST, $post);
       curl_setopt($ch, CURLOPT_TIMEOUT, 10); //设置超时6秒
       curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
       if($post)
          curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
       curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
       $result = curl_exec ($ch);
       //连接失败
       if($result == FALSE){
          if($this->BodyType=='json'){
             $result = "{\"statusCode\":\"172001\",\"statusMsg\":\"网络错误\"}";
          } else {
             $result = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?><Response><statusCode>172001</statusCode><statusMsg>网络错误</statusMsg></Response>";
          }
       }

       curl_close($ch);
       return $result;
     }

    /**
    * 创建子账户
    * @param nickName 子账号昵称
    * @param mobile 子账号用户手机号码
    * @param email 子账号用户邮件地址
    */
	  function createSubAccount($nickName='', $mobile='', $email='')
	  {
        //主帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->accAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
          $body= "{
                    'createSubAccount'   : {
                      'appId'      : '$this->AppId',
                      'nickName'   : '$nickName',
                      'mobile'     : '$mobile',
                      'email'      : '$email'
                   }
                  }";
        }else{
           $body="<createSubAccount>
                    <appId>$this->AppId</appId>
                    <nickName>$nickName</nickName>
                    <mobile>$mobile</mobile>
                    <email>$email</email>
                  </createSubAccount>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->AccountSid . $this->AccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/Accounts/$this->AccountSid/Applications/createSubAccount?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：主帐号Id + 英文冒号 + 时间戳
        $authen = base64_encode($this->AccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");
        // 发请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
           $datas=json_decode($result, true);
        }else{ //xml格式
           $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
      //  if($datas == FALSE){
      //            $datas = new \stdClass();
      //            $datas->statusCode = '172003';
      //            $datas->statusMsg = '返回包体错误';
      //        }
        return $datas;
	  }

    /**
    * 查询子账户列表
    */
    function subAccountList()
    {

        //主帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->accAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
          $body= "{
                    'subAccountList' : {
                      'appId' : '$this->AppId'
                    }
                  }";
        }else{
        	 $body="
            <subAccountList>
              <appId>$this->AppId</appId>
            </subAccountList>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->AccountSid . $this->AccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/Accounts/$this->AccountSid/Applications/subAccountList?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：主帐户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->AccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
           $datas=json_decode($result, true);
        }else{ //xml格式
           $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
      //  if($datas == FALSE){
      //            $datas = new \stdClass();
      //            $datas->statusCode = '172003';
      //            $datas->statusMsg = '返回包体错误';
      //        }
        return $datas;
    }

   /**
    * 查询子账户
    * @param subAccountSid 	子账户 Sid
    */
    function subAccount($subAccountSid)
    {
        //主帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->accAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体

        if($this->BodyType=="json"){
           $body= "{'subAccount' : { 'appId':'$this->AppId','subAccountSid':'$subAccountSid'}}";
        }else{
        	 $body="
            <subAccount>
              <appId>$this->AppId</appId>
              <subAccountSid>$subAccountSid</subAccountSid>
            </subAccount>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->AccountSid . $this->AccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/Accounts/$this->AccountSid/Applications/subAccount?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：主帐户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->AccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
           $datas=json_decode($result, true);
        }else{ //xml格式
           $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
      //  if($datas == FALSE){
      //            $datas = new \stdClass();
      //            $datas->statusCode = '172003';
      //            $datas->statusMsg = '返回包体错误';
      //        }
        return $datas;
    }

    /**
     * 删除子账户
     * @param subAccountSid 	子账户 Sid
     */
    function dropSubAccount($subAccountSid)
    {
        //主帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->accAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体

        if($this->BodyType=="json"){
            $body= "{'dropSubAccount' : { 'appId':'$this->AppId','subAccountSid':'$subAccountSid'}}";
        }else{
            $body="
            <dropSubAccount>
              <appId>$this->AppId</appId>
              <subAccountSid>$subAccountSid</subAccountSid>
            </dropSubAccount>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->AccountSid . $this->AccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/Accounts/$this->AccountSid/Applications/dropSubAccount?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：主帐户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->AccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
            $datas=json_decode($result, true);
        }else{ //xml格式
            $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
        //  if($datas == FALSE){
        //            $datas = new \stdClass();
        //            $datas->statusCode = '172003';
        //            $datas->statusMsg = '返回包体错误';
        //        }
        return $datas;
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
        //主帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->accAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体

        if($this->BodyType=="json"){
            $body= "{
                    'subAccountList' : {
                      'appId' : '$this->AppId',
                      'subAccountSid' : '$subAccountSid',
                      'mobile' : '$mobile',
                      'email' : '$email',
                      'nickName' : '$nickName'
                    }
                  }";
        }else{
            $body="
            <updateSubAccount>
              <appId>$this->AppId</appId>
              <subAccountSid>$subAccountSid</subAccountSid>
              <mobile>$mobile</mobile>
              <email>$email</email>
              <nickName>$nickName</nickName>
            </updateSubAccount>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->AccountSid . $this->AccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/Accounts/$this->AccountSid/Applications/updateSubAccount?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：主帐户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->AccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
            $datas=json_decode($result, true);
        }else{ //xml格式
            $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
        //  if($datas == FALSE){
        //            $datas = new \stdClass();
        //            $datas->statusCode = '172003';
        //            $datas->statusMsg = '返回包体错误';
        //        }
        return $datas;
    }

   /**
    * 发送短信
    * @param to 短信接收彿手机号码集合,用英文逗号分开
    * @param body 短信正文
    */
    function sendSMS($to,$smsBody)
    {
        //主帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->accAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
           $body= "{'to':'$to','body':'$smsBody','appId':'$this->AppId'}";
        }else{
           $body="<SMSMessage>
                    <to>$to</to>
                    <body>$smsBody</body>
                    <appId>$this->AppId</appId>
                  </SMSMessage>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->AccountSid . $this->AccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/Accounts/$this->AccountSid/SMS/Messages?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：主帐号Id + 英文冒号 + 时间戳
        $authen = base64_encode($this->AccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");
        // 发请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
           $datas=json_decode($result, true);
        }else{ //xml格式
           $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
      //  if($datas == FALSE){
      //            $datas = new \stdClass();
      //            $datas->statusCode = '172003';
      //            $datas->statusMsg = '返回包体错误';
      //        }
        return $datas;
    }

   /**
    * 发送模板短信
    * @param to 短信接收彿手机号码集合,用英文逗号分开
    * @param datas 内容数据
    * @param $tempId 模板Id
    */
    function sendTemplateSMS($to,$datas,$tempId)
    {
        //主帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->accAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
           $data="";
           for($i=0;$i<count($datas);$i++){
              $data = $data. "'".$datas[$i]."',";
           }
           $body= "{'to':'$to','templateId':'$tempId','appId':'$this->AppId','datas':[".$data."]}";
        }else{
           $data="";
           for($i=0;$i<count($datas);$i++){
              $data = $data. "<data>".$datas[$i]."</data>";
           }
           $body="<TemplateSMS>
                    <to>$to</to>
                    <appId>$this->AppId</appId>
                    <templateId>$tempId</templateId>
                    <datas>".$data."</datas>
                  </TemplateSMS>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->AccountSid . $this->AccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/Accounts/$this->AccountSid/SMS/TemplateSMS?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：主帐户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->AccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
           $datas=json_decode($result, true);
        }else{ //xml格式
           $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
      //  if($datas == FALSE){
      //            $datas = new \stdClass();
      //            $datas->statusCode = '172003';
      //            $datas->statusMsg = '返回包体错误';
      //        }
        //重新装填数据
        if($datas->statusCode==0){
         if($this->BodyType=="json"){
            $datas->TemplateSMS =$datas->templateSMS;
            unset($datas->templateSMS);
          }
        }

        return $datas;
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
        //子帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->subAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
            $body= "{
                    'callBack' : {
                      'appId' : '$this->AppId',
                      'from' : '$from',
                      'to' : '$to',
                      'displayMode' : '$displayMode',
                      'getFeedBack' : '$getFeedBack',
                      'feedBackMode' : '$feedBackMode',
                      'keyRange' : '$keyRange',
                      'userData' : '$userData'
                    }
                  }";
        }else{
           $body= "<callBack>
                     <from>$from</from>
                     <to>$to</to>
                     <displayMode>$displayMode</displayMode>
                     <getFeedBack>$getFeedBack</getFeedBack>
                     <feedBackMode>$feedBackMode</feedBackMode>
                     <keyRange>$keyRange</keyRange>
                     <userData>$userData</userData>
                   </callBack>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->SubAccountSid . $this->SubAccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/SubAccounts/$this->SubAccountSid/Calls/callBack?sig=$sig";
          //$url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/Accounts/$this->AccountSid/Calls/callBack?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：子帐号Id + 英文冒号 + 时间戳
        $authen=base64_encode($this->SubAccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");

        // 发请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
           $datas=json_decode($result, true);
        }else{ //xml格式
           $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
      //  if($datas == FALSE){
      //            $datas = new \stdClass();
      //            $datas->statusCode = '172003';
      //            $datas->statusMsg = '返回包体错误';
      //        }
        return $datas;
	}

    /**
     * 电话直拨功能
     * @param from 主叫电话号码  必选
     * @param to 被叫电话号码  必选
     * @param userData 	用户透传数据，回调时返回给用户，可用于认证 	可选
     */
    function directCall($from, $to, $userData='')
    {
        //子帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->subAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
            $body= "{
                    'directCall' : {
                      'appId' : '$this->AppId',
                      'from' : '$from',
                      'to' : '$to',
                      'userData' : '$userData'
                    }
                  }";
        }else{
            $body= "<directCall>
                     <from>$from</from>
                     <to>$to</to>
                     <userData>$userData</userData>
                   </directCall>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->SubAccountSid . $this->SubAccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/SubAccounts/$this->SubAccountSid/Calls/directCall?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：子帐号Id + 英文冒号 + 时间戳
        $authen=base64_encode($this->SubAccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");

        // 发请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
            $datas=json_decode($result, true);
        }else{ //xml格式
            $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
        //  if($datas == FALSE){
        //            $datas = new \stdClass();
        //            $datas->statusCode = '172003';
        //            $datas->statusMsg = '返回包体错误';
        //        }
        return $datas;
    }

    /**
     * 语音验证码功能
     * @param verifyCode 验证号码，现规定为 4 位数字 	必选
     * @param to 验证码拨叫号码 	必选
     * @param userData 	用户透传数据，回调时返回给用户，可用于认证 可选
     */
    function voiceCode($verifyCode, $to, $userData='')
    {
        //主帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->accAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
            $body= "{
                    'voiceCode' : {
                      'appId' : '$this->AppId',
                      'to' : '$to',
                      'userData' : '$userData'
                    }
                  }";
        }else{
            $body="<voiceCode>
                    <appId>$this->AppId</appId>
                    <verifyCode>$verifyCode</verifyCode>
                    <to>$to</to>
                    <userData>$userData</userData>
                  </voiceCode>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->AccountSid . $this->AccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/Accounts/$this->AccountSid/Calls/voiceCode?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：主帐户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->AccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
            $datas=json_decode($result, true);
        }else{ //xml格式
            $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
        //  if($datas == FALSE){
        //            $datas = new \stdClass();
        //            $datas->statusCode = '172003';
        //            $datas->statusMsg = '返回包体错误';
        //        }
        return $datas;
    }

    /**
    * 语音通知功能
    * @param voiceId 语音文件id 必选
    * @param to 被叫号码 必选
    * @param userData 	用户透传数据，回调时返回给用户，可用于认证 可选
    */
    function voiceNotify($voiceId, $to, $userData='')
    {
        //主帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->accAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
            $body= "{
                    'voiceNotify' : {
                      'appId' : '$this->AppId',
                      'voiceId' : '$voiceId',
                      'to' : '$to',
                      'userData' : '$userData'
                    }
                  }";
        }else{
           $body="<voiceNotify>
                    <appId>$this->AppId</appId>
                    <voiceId>$voiceId</voiceId>
                    <to>$to</to>
                    <userData>$userData</userData>
                  </voiceNotify>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->AccountSid . $this->AccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/Accounts/$this->AccountSid/Calls/voiceNotify?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：主帐户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->AccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
           $datas=json_decode($result, true);
        }else{ //xml格式
           $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
      //  if($datas == FALSE){
      //            $datas = new \stdClass();
      //            $datas->statusCode = '172003';
      //            $datas->statusMsg = '返回包体错误';
      //        }
        return $datas;
    }

    /**
     * 通话取消功能
     * @param callId 	呼叫 Id 		必选
     */
    function callCancel($callId)
    {
        //主帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->accAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
            $body= "{
                    'callCancel' : {
                      'appId' : '$this->AppId',
                      'callId' : '$callId'
                    }
                  }";
        }else{
            $body="<callCancel>
                    <appId>$this->AppId</appId>
                    <callId>$callId</callId>
                  </callCancel>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->AccountSid . $this->AccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/Accounts/$this->AccountSid/Calls/callCancel?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：主帐户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->AccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
            $datas=json_decode($result, true);
        }else{ //xml格式
            $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
        //  if($datas == FALSE){
        //            $datas = new \stdClass();
        //            $datas->statusCode = '172003';
        //            $datas->statusMsg = '返回包体错误';
        //        }
        return $datas;
    }

   /**
    * IVR外呼
    * @param number   待呼叫号码，为Dial节点的属性
    * @param userdata 用户数据，在<startservice>通知中返回，只允许填写数字字符，为Dial节点的属性
    * @param record   是否录音，可填项为true和false，默认值为false不录音，为Dial节点的属性
    */
    function ivrDial($number,$userdata,$record)
    {
       //主帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->accAuth();
        if($auth!=""){
            return $auth;
        }
       // 拼接请求包体
        $body=" <Request>
                  <Appid>$this->AppId</Appid>
                  <Dial number='$number'  userdata='$userdata' record='$record'></Dial>
                </Request>";
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->AccountSid . $this->AccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/Accounts/$this->AccountSid/ivr/dial?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：主帐户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->AccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/xml","Content-Type:application/xml;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        $datas = simplexml_load_string(trim($result," \t\n\r"));
      //  if($datas == FALSE){
      //            $datas = new \stdClass();
      //            $datas->statusCode = '172003';
      //            $datas->statusMsg = '返回包体错误';
      //        }
        return $datas;
    }

   /**
    * 下载应用话单
    * 应用话单每次最多只给出 100 条记录。因此，如果指定时间范围内的话单超过 100 条，就需要调整 startTime，并多次调用本接口，才能获得所有话单。
    * @param subAccountSid 	子账户 ID，输入该参数时，只下载该子账户专用云总机企业内发生的通话话单。 	可选
    * @param startTime 	话单开始时间，格式：yyyymmddHHMMSS 	可选
    * @param endTime 话单结束时间，格式：yyyymmddHHMMSS 	可选
    * @param lastMaxId 	上次话单最大 ID 	 	可选
    */
    function billList($subAccountSid='', $startTime='', $endTime='', $lastMaxId='')
    {
        //主帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->accAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
            $body= "{
                    'billList' : {
                      'appId' : '$this->AppId',
                      'subAccountSid' : '$subAccountSid',
                      'startTime' : '$startTime',
                      'endTime' : '$endTime',
                      'lastMaxId' : '$lastMaxId'
                    }
                  }";
        }else{
           $body="<billList>
                    <appId>$this->AppId</appId>
                    <subAccountSid>$subAccountSid</subAccountSid>
                    <startTime>$startTime</startTime>
                    <endTime>$endTime</endTime>
                    <lastMaxId>$lastMaxId</lastMaxId>
                  </billList>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->AccountSid . $this->AccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/Accounts/$this->AccountSid/Applications/billList?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：主帐户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->AccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
           $datas=json_decode($result, true);
        }else{ //xml格式
           $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
      //  if($datas == FALSE){
      //            $datas = new \stdClass();
      //            $datas->statusCode = '172003';
      //            $datas->statusMsg = '返回包体错误';
      //        }
        return $datas;
   }

    /**
     * 获取指定话单详情
     * @param callId 	呼叫 Id 		必选
     */
    function callDetail($callId)
    {
        //主帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->accAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
            $body= "{
                    'callDetail' : {
                      'appId' : '$this->AppId',
                      'callId' : '$callId'
                    }
                  }";
        }else{
            $body="<callDetail>
                    <appId>$this->AppId</appId>
                    <callId>$callId</callId>
                  </callDetail>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->AccountSid . $this->AccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/Accounts/$this->AccountSid/Applications/callDetail?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：主帐户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->AccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
            $datas=json_decode($result, true);
        }else{ //xml格式
            $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
        //  if($datas == FALSE){
        //            $datas = new \stdClass();
        //            $datas->statusCode = '172003';
        //            $datas->statusMsg = '返回包体错误';
        //        }
        return $datas;
    }

    /**
     * 通话录音文件 Url
     * @param callId 	呼叫 Id 		必选
     */
    function callRecordUrl($callId)
    {
        //主帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->accAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
            $body= "{
                    'callRecordUrl' : {
                      'appId' : '$this->AppId',
                      'callId' : '$callId'
                    }
                  }";
        }else{
            $body="<callRecordUrl>
                    <appId>$this->AppId</appId>
                    <callId>$callId</callId>
                  </callRecordUrl>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->AccountSid . $this->AccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/Accounts/$this->AccountSid/Applications/callRecordUrl?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：主帐户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->AccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
            $datas=json_decode($result, true);
        }else{ //xml格式
            $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
        //  if($datas == FALSE){
        //            $datas = new \stdClass();
        //            $datas->statusCode = '172003';
        //            $datas->statusMsg = '返回包体错误';
        //        }
        return $datas;
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
        //主帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->accAuth();
        if($auth!=""){
            return $auth;
        }
        //验证码
        if (empty($verifyCode)) {
            $verifyCode = rand(1000,9999); //4位纯数字
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
           $body= "{'appId':'$this->AppId','verifyCode':'$verifyCode','playTimes':'$playTimes','to':'$to','displayNum':'$displayNum','feeTypeAuth':'$feeTypeAuth'}";
        }else{
           $body="<ValidateDisplayNumbers>
                  <appId>$this->AppId</appId>
                  <verifyCode>$verifyCode</verifyCode>
                  <playTimes>$playTimes</playTimes>
                  <to>$to</to>
                  <displayNum>$displayNum</displayNum>
                  <feeTypeAuth>$feeTypeAuth</feeTypeAuth>
                  </ValidateDisplayNumbers>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->AccountSid . $this->AccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/Accounts/$this->AccountSid/partner/ValidateDisplayNumbers?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：主帐户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->AccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
           $datas=json_decode($result, true);
        }else{ //xml格式
           $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
      //  if($datas == FALSE){
      //      $datas = new \stdClass();
      //      $datas->statusCode = '172003';
      //      $datas->statusMsg = '返回包体错误';
      //}

        return $datas;
   }

   /****************4 云总机企业和用户管理功能接口定义************************/

   /**
    *  添加云总机企业
    * @param switchNumber 云总机企业总机号码（必须带区号，首字符为0）  必选
    * @param number 云总机企业管理员分级号码 ，不填时自动使用默认分机号  可选
    * @param password  云总机企业管理员认证密码   可选
    * @param chargeMode  计费模式：
                            0-  应用计费（默认方式）
                            1-  云总机开放平台计费    可选
    * @param userData  用户私有数据，字母和数字组合，最大长度64     可选
    * @param callreqUrl  用户呼叫请求和鉴权服务器Url   可选
    */
    function addEnterprise($switchNumber, $number='', $password='', $chargeMode='0', $userData='', $callreqUrl='')
    {
        //子帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->subAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
            $body= "{
                    'addEnterprise'   : {
                        'appId'         : '$this->AppId',
                        'switchNumber'  : '$switchNumber',
                        'number'        : '$number',
                        'password'      : '$password',
                        'chargeMode'    : '$chargeMode',
                        'userData'      : '$userData',
                        'callreqUrl'    : '$callreqUrl'
                    }
                }";
        }else{
            $body= "<addEnterprise>
                    <appId>$this->AppId</appId>
                    <switchNumber>$switchNumber</switchNumber>
                    <number>$number</number>
                    <password>$password</password>
                    <chargeMode>$chargeMode</chargeMode>
                    <userData>$userData</userData>
                    <callreqUrl>$callreqUrl</addEnterprise>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->SubAccountSid . $this->SubAccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/SubAccounts/$this->SubAccountSid/Enterprises/addEnterprise?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：子帐号Id + 英文冒号 + 时间戳
        $authen=base64_encode($this->SubAccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");

        // 发请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
           $datas=json_decode($result, true);
        }else{ //xml格式
           $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
        //  if($datas == FALSE){
        //            $datas = new \stdClass();
        //            $datas->statusCode = '172003';
        //            $datas->statusMsg = '返回包体错误';
        //        }
        return $datas;
    }

    /**
    *  删除云总机企业
    * @param
    *
    */
    function dropEnterprise()
    {
        //子帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->subAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
            $body= "{
                    'dropEnterprise'   : {
                        'appId'         : '$this->AppId'
                    }
                }";
        }else{
            $body= "<dropEnterprise>
                    <appId>$this->AppId</appId>
                    </dropEnterprise>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->SubAccountSid . $this->SubAccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/SubAccounts/$this->SubAccountSid/Enterprises/dropEnterprise?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：子帐号Id + 英文冒号 + 时间戳
        $authen=base64_encode($this->SubAccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");

        // 发请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
           $datas=json_decode($result, true);
        }else{ //xml格式
           $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
        //  if($datas == FALSE){
        //            $datas = new \stdClass();
        //            $datas->statusCode = '172003';
        //            $datas->statusMsg = '返回包体错误';
        //        }
        return $datas;
    }

    /**
    *  获取空闲总机号码
    * @param
    *
    *   用于从云总机开放平台获取本子账户专用云总机企业中有哪些空闲的直线号码，这些号码可用于绑定用户手机号码，使之成为直线用户
    */
    function freeNumbers()
    {
        //子帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->subAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
            $body= "{
                    'freeNumbers'   : {
                        'appId'         : '$this->AppId'
                    }
                }";
        }else{
            $body= "<freeNumbers>
                    <appId>$this->AppId</appId>
                    </freeNumbers>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->SubAccountSid . $this->SubAccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/SubAccounts/$this->SubAccountSid/Enterprises/freeNumbers?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：子帐号Id + 英文冒号 + 时间戳
        $authen=base64_encode($this->SubAccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");

        // 发请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
           $datas=json_decode($result, true);
        }else{ //xml格式
           $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
        //  if($datas == FALSE){
        //            $datas = new \stdClass();
        //            $datas->statusCode = '172003';
        //            $datas->statusMsg = '返回包体错误';
        //        }
        return $datas;
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
        //子帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->subAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
            $body= "{
                    'createUser'   : {
                        'appId'         : '$this->AppId',
                        'mobile'         : '$phone',
                        'displayName'   : '$displayName',
                        'directNumber'  : '$directNumber',
                        'callTime'      : '$callTime'
                    }
                }";
        }else{
            $body= "<createUser>
                    <appId>$this->AppId</appId>
                    <mobile>$phone</mobile>
                    <displayName>$displayName</displayName>
                    <directNumber>$directNumber</directNumber>
                    <callTime>$callTime</callTime>
                    </createUser>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->SubAccountSid . $this->SubAccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/SubAccounts/$this->SubAccountSid/Enterprises/createUser?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：子帐号Id + 英文冒号 + 时间戳
        $authen=base64_encode($this->SubAccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");

        // 发请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
           $datas=json_decode($result, true);
        }else{ //xml格式
           $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
        //  if($datas == FALSE){
        //            $datas = new \stdClass();
        //            $datas->statusCode = '172003';
        //            $datas->statusMsg = '返回包体错误';
        //        }
        return $datas;
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
        //子帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->subAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
            $body= "{
                    'dropUser'   : {
                        'appId'     : '$this->AppId',
                        'mobile'     : '$phone'
                    }
                }";
        }else{
            $body= "<dropUser>
                    <appId>$this->AppId</appId>
                    <mobile>$phone</mobile>
                    </dropUser>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->SubAccountSid . $this->SubAccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/SubAccounts/$this->SubAccountSid/Enterprises/dropUser?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：子帐号Id + 英文冒号 + 时间戳
        $authen=base64_encode($this->SubAccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");

        // 发请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
           $datas=json_decode($result, true);
        }else{ //xml格式
           $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
        //  if($datas == FALSE){
        //            $datas = new \stdClass();
        //            $datas->statusCode = '172003';
        //            $datas->statusMsg = '返回包体错误';
        //        }
        return $datas;
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
        //子帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->subAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
            $body= "{
                    'updateUser'   : {
                        'appId'         : '$this->AppId',
                        'mobile'         : '$phone',
                        'displayName'   : '$displayName',
                        'directNumber'  : '$directNumber',
                        'callTime'      : '$callTime'
                    }
                }";
        }else{
            $body= "<updateUser>
                    <appId>$this->AppId</appId>
                    <mobile>$phone</mobile>
                    <displayName>$displayName</displayName>
                    <directNumber>$directNumber</directNumber>
                    <callTime>$callTime</callTime>
                    </updateUser>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->SubAccountSid . $this->SubAccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/SubAccounts/$this->SubAccountSid/Enterprises/updateUser?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：子帐号Id + 英文冒号 + 时间戳
        $authen=base64_encode($this->SubAccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");

        // 发请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
           $datas=json_decode($result, true);
        }else{ //xml格式
           $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
        //  if($datas == FALSE){
        //            $datas = new \stdClass();
        //            $datas->statusCode = '172003';
        //            $datas->statusMsg = '返回包体错误';
        //        }
        return $datas;
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
        //子帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->subAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
            $body= "{
                    'updateUserPhone'   : {
                        'appId'         : '$this->AppId',
                        'number'        : '$number',
                        'mobile'        : '$phone'
                    }
                }";
        }else{
            $body= "<updateUserPhone>
                    <appId>$this->AppId</appId>
                    <number>$number</number>
                    <mobile>$phone</mobile>
                    </updateUserPhone>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->SubAccountSid . $this->SubAccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/SubAccounts/$this->SubAccountSid/Enterprises/updateUserPhone?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：子帐号Id + 英文冒号 + 时间戳
        $authen=base64_encode($this->SubAccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");

        // 发请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
           $datas=json_decode($result, true);
        }else{ //xml格式
           $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
        //  if($datas == FALSE){
        //            $datas = new \stdClass();
        //            $datas->statusCode = '172003';
        //            $datas->statusMsg = '返回包体错误';
        //        }
        return $datas;
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
        //子帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->subAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
            $body= "{
                    'userInfo'   : {
                        'appId'     : '$this->AppId',
                        'phone'     : '$phone'
                    }
                }";
        }else{
            $body= "<userInfo>
                    <appId>$this->AppId</appId>
                    <phone>$phone</phone>
                    </userInfo>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->SubAccountSid . $this->SubAccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/SubAccounts/$this->SubAccountSid/Enterprises/userInfo?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：子帐号Id + 英文冒号 + 时间戳
        $authen=base64_encode($this->SubAccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");

        // 发请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
           $datas=json_decode($result, true);
        }else{ //xml格式
           $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
        //  if($datas == FALSE){
        //            $datas = new \stdClass();
        //            $datas->statusCode = '172003';
        //            $datas->statusMsg = '返回包体错误';
        //        }
        return $datas;
    }

    /**
    *  设置保护号码对
    * @param
    * @param numberA 用户绑定电话号码A  必选
    * @param numberB 用户绑定电话号码B  必选
    * @param
    */
    function createNumberPair($numberA, $numberB)
    {
        //子帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->subAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
            $body= "{
                    'createNumberPair'   : {
                        'appId'      : '$this->AppId',
                        'numberA'    : '$numberA',
                        'numberB'    : '$numberB'
                    }
                }";
        }else{
            $body= "<createNumberPair>
                    <appId>$this->AppId</appId>
                    <numberA>$numberA</numberA>
                    <numberB>$numberB</numberB>
                    </createNumberPair>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->SubAccountSid . $this->SubAccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/SubAccounts/$this->SubAccountSid/Enterprises/createNumberPair?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：子帐号Id + 英文冒号 + 时间戳
        $authen=base64_encode($this->SubAccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");

        // 发请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
           $datas=json_decode($result, true);
        }else{ //xml格式
           $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
        //  if($datas == FALSE){
        //            $datas = new \stdClass();
        //            $datas->statusCode = '172003';
        //            $datas->statusMsg = '返回包体错误';
        //        }
        return $datas;
    }

    /**
    *  删除保护号码对
    * @param
    * @param numberA 用户绑定电话号码A  必选
    * @param numberB 用户绑定电话号码B  必选
    * @param
    */
    function dropNumberPair ($numberA, $numberB)
    {
        //子帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->subAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
            $body= "{
                    'dropNumberPair'   : {
                        'appId'      : '$this->AppId',
                        'numberA'    : '$numberA',
                        'numberB'    : '$numberB'
                    }
                }";
        }else{
            $body= "<dropNumberPair>
                    <appId>$this->AppId</appId>
                    <numberA>$numberA</numberA>
                    <numberB>$numberB</numberB>
                    </dropNumberPair>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->SubAccountSid . $this->SubAccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/SubAccounts/$this->SubAccountSid/Enterprises/dropNumberPair?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：子帐号Id + 英文冒号 + 时间戳
        $authen=base64_encode($this->SubAccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");

        // 发请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
           $datas=json_decode($result, true);
        }else{ //xml格式
           $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
        //  if($datas == FALSE){
        //            $datas = new \stdClass();
        //            $datas->statusCode = '172003';
        //            $datas->statusMsg = '返回包体错误';
        //        }
        return $datas;
    }
    /*****************************************/

    /*******************6 语音文件管理功能接口定义**********************/
    /**
     * 上传语音文件
     * @param data      语音文件内容 必选
     * @param maxAge    最大生效时间（单位为秒，默认为 1800s），如果为 0，则永久生效。
                        注：系统默认只允许每个应用同时有 16 个语音文件生效   可选

     */
    function uploadVoice($data, $maxAge=1800)
    {
        //主帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->accAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
            $body= "$data";
        }else{
            $body= "$data";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->AccountSid . $this->AccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/Accounts/$this->AccountSid/Voice/uploadVoice ?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：主帐户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->AccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
            $datas=json_decode($result, true);
        }else{ //xml格式
            $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
        //  if($datas == FALSE){
        //            $datas = new \stdClass();
        //            $datas->statusCode = '172003';
        //            $datas->statusMsg = '返回包体错误';
        //        }
        return $datas;
    }


    /**
     * 删除语音文件
     * @param voiceId    语音文件 Id        必选
     */
    function deleteVoice($voiceId)
    {
        //主帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->accAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
            $body= "{
                    'deleteVoice'   : {
                        'appId'      : '$this->AppId',
                        'voiceId'    : '$voiceId'
                    }
                }";
        }else{
            $body= "<deleteVoice>
                    <appId>$this->AppId</appId>
                    <voiceId>$voiceId</voiceId>
                    </deleteVoice>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->AccountSid . $this->AccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/Accounts/$this->AccountSid/Voice/deleteVoice?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：主帐户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->AccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
            $datas=json_decode($result, true);
        }else{ //xml格式
            $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
        //  if($datas == FALSE){
        //            $datas = new \stdClass();
        //            $datas->statusCode = '172003';
        //            $datas->statusMsg = '返回包体错误';
        //        }
        return $datas;
    }

    /**
     * 上传语音文本
     * @param text    语音文本或模板，长度默认为 500（字节数）。最大长度可以在后台配置（应用相关）。
                        模板规则：
                        1）  模板内容不能含有敏感词，凡涉嫌诈骗、恐吓、骚扰用户的语音通知将无法通过审核；
                        2）  变量格式：变量内容（参数）请使用{数字}，按序填写，如{1}，变量内容必须为半角字符，外侧括号为英文花括号，其中的数字必须从 1 开始顺序排列，如：尊敬的用户您好，您的{1}已送至 1 号 101 楼快   递中心，快递单号为{2}，提货码{3}，请于早 9 点至晚 6 点到店取货。必选
     * @param maxAge    最大生效时间（单位为秒，默认为 1800s），如果为 0，则永久生效。
                        注：系统默认只允许每个应用同时有 16 个语音文本（包括模板）生效   可选

     */
    function uploadText($text, $maxAge=1800)
    {
        //主帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->accAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
            $body= "{
                    'uploadText'   : {
                        'appId'         : '$this->AppId',
                        'text'          : '$text',
                        'maxAge'        : '$maxAge'
                    }
                }";
        }else{
            $body= "<uploadText>
                    <appId>$this->AppId</appId>
                    <text>$text</text>
                    <maxAge>$maxAge</maxAge>
                    </uploadText>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->AccountSid . $this->AccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/Accounts/$this->AccountSid/Voice/uploadText?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：主帐户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->AccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
            $datas=json_decode($result, true);
        }else{ //xml格式
            $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
        //  if($datas == FALSE){
        //            $datas = new \stdClass();
        //            $datas->statusCode = '172003';
        //            $datas->statusMsg = '返回包体错误';
        //        }
        return $datas;
    }

    /**
     * 删除语音文本
     * @param textId     语音文本 Id        必选
     */
    function deleteText($textId)
    {
        //主帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->accAuth();
        if($auth!=""){
            return $auth;
        }
        // 拼接请求包体
        if($this->BodyType=="json"){
            $body= "{
                    'deleteText'   : {
                        'appId'      : '$this->AppId',
                        'voiceId'    : '$textId'
                    }
                }";
        }else{
            $body= "<deleteText>
                    <appId>$this->AppId</appId>
                    <voiceId>$voiceId</voiceId>
                    </deleteText>";
        }
        $this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->AccountSid . $this->AccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/Accounts/$this->AccountSid/Voice/deleteText?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：主帐户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->AccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = $this->curl_post($url,$body,$header);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
            $datas=json_decode($result, true);
        }else{ //xml格式
            $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
        //  if($datas == FALSE){
        //            $datas = new \stdClass();
        //            $datas->statusCode = '172003';
        //            $datas->statusMsg = '返回包体错误';
        //        }
        return $datas;
    }

    /*****************************************/

  /**
    * 主帐号信息查询
    */
   function AccountInfo()
   {
        //主帐号鉴权信息验证，对必选参数进行判空。
        $auth=$this->accAuth();
        if($auth!=""){
            return $auth;
        }
        // 大写的sig参数
        $sig =  strtoupper(md5($this->AccountSid . $this->AccountToken . $this->Batch));
        $this->Sig = $sig;
        // 生成请求URL
        $url="http://$this->ServerIP:$this->ServerPort/$this->SoftVersion/Accounts/$this->AccountSid/AccountInfo?sig=$sig";
        $this->showlog("request url = ".$url);
        // 生成授权：主帐户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->AccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/$this->BodyType","Content-Type:application/$this->BodyType;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = $this->curl_post($url,"",$header,0);
        $this->showlog("response body = ".$result);
        if($this->BodyType=="json"){//JSON格式
           $datas=json_decode($result, true);
        }else{ //xml格式
           $datas = simplexml_load_string(trim($result," \t\n\r"));
        }
      //  if($datas == FALSE){
      //            $datas = new \stdClass();
      //            $datas->statusCode = '172003';
      //            $datas->statusMsg = '返回包体错误';
      //        }
        return $datas;
   }

  /**
    * 子帐号鉴权
    */
   function subAuth()
   {
       if($this->ServerIP==""){
            $data = new \stdClass();
            $data->statusCode = '172004';
            $data->statusMsg = 'IP为空';
          return $data;
        }
        if($this->ServerPort<=0){
            $data = new \stdClass();
            $data->statusCode = '172005';
            $data->statusMsg = '端口错误（小于等于0）';
          return $data;
        }
        if($this->SoftVersion==""){
            $data = new \stdClass();
            $data->statusCode = '172013';
            $data->statusMsg = '版本号为空';
          return $data;
        }
        if($this->SubAccountSid==""){
            $data = new \stdClass();
            $data->statusCode = '172008';
            $data->statusMsg = '子帐号为空';
          return $data;
        }
        if($this->SubAccountToken==""){
            $data = new \stdClass();
            $data->statusCode = '172009';
            $data->statusMsg = '子帐号令牌为空';
          return $data;
        }
        if($this->AppId==""){
            $data = new \stdClass();
            $data->statusCode = '172012';
            $data->statusMsg = '应用ID为空';
          return $data;
        }
   }

  /**
    * 主帐号鉴权
    */
   function accAuth()
   {
       if($this->ServerIP==""){
            $data = new \stdClass();
            $data->statusCode = '172004';
            $data->statusMsg = 'IP为空';
          return $data;
        }
        if($this->ServerPort<=0){
            $data = new \stdClass();
            $data->statusCode = '172005';
            $data->statusMsg = '端口错误（小于等于0）';
          return $data;
        }
        if($this->SoftVersion==""){
            $data = new \stdClass();
            $data->statusCode = '172013';
            $data->statusMsg = '版本号为空';
          return $data;
        }
        if($this->AccountSid==""){
            $data = new \stdClass();
            $data->statusCode = '172006';
            $data->statusMsg = '主帐号为空';
          return $data;
        }
        if($this->AccountToken==""){
            $data = new \stdClass();
            $data->statusCode = '172007';
            $data->statusMsg = '主帐号令牌为空';
          return $data;
        }
        if($this->AppId==""){
            $data = new \stdClass();
            $data->statusCode = '172012';
            $data->statusMsg = '应用ID为空';
          return $data;
        }
   }

   /**
    * API接口错误代码信息
    * @param $id 错误代码id 可选
   */
   function errorInfo($id='0')
   {
       $errorArray = array(
           //respCode 错误描述云总机开发平台系统级错误
           '100000'=>'目前尚不能提供的待开发功能',
           '100001'=>'内部数据库访问失败',
           '100002'=>'上传语音文件时创建目录失败',
           '100003'=>'上传语音文件时存储文件失败',
           '100004'=>'系统内存分配失败',

           //云总机服务平台错误
           '100500'=>'创建云总机企业分机用户失败',
           '100501'=>'更新云总机企业分机用户信息失败',
           '100502'=>'调用云总机 EP_PROFILE 接口失败',
           '100503'=>'获取云总机企业（用户）信息失败',
           '100504'=>'删除云总机企业分机失败',
           '100505'=>'与云总机的 HTTP 连接失败，或返回错误值',
           '100506'=>'向云总机获取通话录音失败',
           '100507'=>'向云总机申请直拨通话时，未返回总机号',
           '100508'=>'向云总机获取用户信息失败',

           //用户接口 HTTP 访问请求错误
           '101000'=>'HTTP 请求包头无 Authorization 参数',
           '101001'=>'HTTP 请求包头无 Content-Length 参数',
           '101002'=>'Authorization 参数 Base64 解码失败',
           '101003'=>'Authorization 参数解码后的格式错误，正确格式： <AccountSid:Timestamp>，注意以“:”隔开',
           '101004'=>'Authorization 参数不包含认证账户 ID',
           '101005'=>'Authorization 参数不包含时间戳',
           '101006'=>'Authorization 参数的账户 ID 不正确（应与 URL 中的账户 ID 一致）',
           '101007'=>'HTTP 请求使用的账号不存在',
           '101008'=>'HTTP 请求使用的账号已关闭',
           '101009'=>'HTTP 请求使用的账号已被锁定',
           '101010'=>'HTTP 请求使用的账户尚未校验',
           '101011'=>'HTTP 请求使用的子账户不存在',
           '101012'=>'HTTP 请求的 sig 参数校验失败',
           '101013'=>'HTTP 请求包体没有任何内容',
           '101014'=>'HTTP 请求包体 XML 格式错误',
           '101015'=>'HTTP 请求包体 XML 包中的功能名称错误',
           '101016'=>'HTTP 请求包体 XML 包无任何有效字段',
           '101017'=>'HTTP 请求包体 Json 格式错误',
           '101018'=>'HTTP 请求包体 Json 包中的功能名称错误',
           '101019'=>'HTTP 请求包体 Json 包无任何有效字段',
           '101020'=>'HTTP 请求包体中缺少 AppId',
           '101021'=>'HTTP 请求包体中缺少子账号 ID',
           '101022'=>'HTTP 请求包体中的开始时间不正确',
           '101023'=>'HTTP 请求包体中的结束时间不正确',
           '101024'=>'HTTP 请求包体中缺少总机号码',
           '101025'=>'HTTP 请求包体中的总机号码格式不正确',
           '101026'=>'HTTP 请求包体中缺少管理员用户分机号码',
           '101027'=>'HTTP 请求包体中缺少管理员用户分机密码',
           '101028'=>'HTTP 请求包体中的总机号码已被预置，无法使用',
           '101029'=>'HTTP 请求包体中缺少用户绑定手机号码',
           '101030'=>'HTTP 请求包体中手机号码格式错误',
           '101031'=>'HTTP 请求包体中缺少直线号码',
           '101032'=>'HTTP 请求包体中缺少被叫号码',
           '101033'=>'HTTP 请求包体中被叫号码格式错误',
           '101034'=>'HTTP 请求包体中被叫号码非法',
           '101035'=>'HTTP 请求包体中主叫号码格式错误',
           '101036'=>'HTTP 请求包体中主叫号码非法',
           '101037'=>'HTTP 请求包体中无主叫号码',
           '101038'=>'HTTP 请求包体中无验证码',
           '101039'=>'HTTP 请求包体中验证码格式错误',
           '101040'=>'HTTP 请求包体中缺少呼叫 ID（callId）',
           '101041'=>'HTTP 请求包体的子账户 ID 非法',
           '101042'=>'HTTP 请求包体中缺少语音 ID（voiceId）',
           '101043'=>'HTTP 请求包体中的语音 ID 不正确',
           '101044'=>'HTTP 请求包头的 Content-Length 值过大（应不大于1024X1024）',
           '101045'=>'HTTP 请求包体中缺少 numberA 101046 HTTP 请求包体中缺少 numberB',
           '101047'=>'numberA 或 numberB 格式错误',
           '101048'=>'呼叫来显模式数值错误',
           '101049'=>'请求更新的子账户不属于本应用',
           '101050'=>'按键反馈字段（getFeedBack）不正确',
           '101051'=>'按键反馈模式字段（feedBackMode）不正确',
           '101052'=>'按键反馈键值范围不正确（keyRange）',
           '101053'=>'用户分机号未输入',
           '101054'=>'呼叫时间限制值格式错误',

           //账户和应用资源错误
           '102000'=>'账户所属省份错误',
           '102001'=>'账户关联企业错误',
           '102002'=>'应用 ID（appId）不存在应用 ID（appId）不存在',
           '102003'=>'应用 ID与主账户不匹配',
           '102004'=>'应用状态为关闭',
           '102005'=>'子账户与应用 ID不匹配',
           '102006'=>'请求包体中的子账户 ID不存在',
           '102007'=>'子账户与应用 ID不匹配',
           '102008'=>'子账户尚未关联或添加专用云总机企业',
           '102009'=>'总机号码找不到对应省份',
           '102010'=>'用户应用服务器连接失败',
           '102011'=>'用户欠费',
           '102012'=>'用户当天调用接口次数已经超过设定值',

           //通话错误
           '102100'=>'通话被用户应用服务器拒绝',
           '102101 通话被叫数超限',
           '102102'=>'无法根据 callId获得通话记录',
           '102103'=>'主叫号码无呼叫权限',
           '102104'=>'callId对应的呼叫记录与所属账户不匹配',
           '102105'=>'呼叫状态为“失败”',
           '102106'=>'呼叫状态尚不是挂断状态',
           '102107'=>'呼叫时长太短',
           '102108'=>'呼叫记录因通话失败或异常而无通话录音',
           '102109'=>'呼叫尚在录音中，无法获取录音',
           '102110'=>'本次呼叫没有录音',
           '102111'=>'用户无主账户呼叫权限',
           '102112'=>'没有呼叫该被叫的权限（即被叫不是分机绑定号码）',
           '102113'=>'账户没有取消通话的权限',

           // 云总机企业信息错误
           '102300'=>'请求必须使用子账户认证',
           '102301'=>'之账户所属云总机企业不存在',
           '102302'=>'子账户尚未绑定或添加云总机企业',
           '102303'=>'参数中的分机号码或密码错误，认证失败',
           '102304'=>'子账户已经绑定了云总机企业',
           '102305'=>'云总机企业已被添加到另一个子账户',
           '102306'=>'云总机企业是系统预置企业，无法删除',
           '102307'=>'用户手机号已经在云总机中注册',
           '102308'=>'直线号码已被其他用户使用',
           '102309'=>'直线号码非法，无法绑定',
           '102310'=>'直线号码不属于用户所属企业',
           '102311'=>'用户手机号码尚未在企业中注册',
           '102312'=>'用户呼叫限制时间格式错误',
           '102313'=>'没有中间号码用于号码保护',
           '102314'=>'未找到输入的号码配对',
           '102315'=>'号码已经配对',
           '102316'=>'输入的云总机企业分机号不存在',

           //语音通知错误
           '102400'=>'语音文件与应用 ID 不匹配',
           '102401'=>'语音文件已被另一个企业使用',
           '102402'=>'没有语音文本字段或语音文本长度为 0',
           '102403'=>'未找到语音通知要使用的语音文本',
           '102404'=>'语音文本长度超限',
           '102405'=>'语音文本总数超限',
           '102406'=>'语音文件个数超限',
           '102407'=>'语音文件尚未审核',
           '102408'=>'语音文件未审核通过',
           '102409'=>'语音文本或模板尚未审核',
           '102410'=>'语音文本或模板未审核通过',
           '102411'=>'输入的是否模板参数值非法',
           '102412'=>'有效语音文本数量已经超限',
           '102413'=>'语音文本模板参数数量与模板不符',
           '102414'=>'使用语音文本模板，但未输入参数',
           '102415'=>'语音文本或模板长度超过规定值',
           '102416'=>'语音文件格式不符合规定',
           '0'=>'成功'
       );
       if (!empty($id)) {
           return $errorArray[$id];
       }
       if ($id == '0') {
           return $errorArray[$id];
       }
       return '未匹配的状态码!';
   }

}