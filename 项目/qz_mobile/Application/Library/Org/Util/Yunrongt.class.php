<?php
/**
 * 云融正通 yunrongt 短信提供商
 */
class Yunrongt{

    //云融正通错误信息
    private $errorMsg = array(
        0 => '失败，提交抛出异常',
        -1 => '用户名或者密码不正确',
        -2 => '必填选项为空或扣费条数小于0',
        -3 => '短信内容0个字节',
        -4 => '0个有效号码',
        -5 => '余额不够',
        -10 => '用户被禁用',
        -11 => '短信内容超过500字',
        -12 => '无扩展权限（ext字段需填空）',
        -13 => 'IP校验错误',
        -24 => '手机号码超过限定个数',
        -25 => '没有提交权限',
        -990 => '未知错误',
    );

  /**
   * 发送信息
   * @param  $data 数组   数组中具体传值见本文件底部文档
   * @return 接口返回 array
   */
  public function sendMessage($data) {
      $reArr = array();
      $reArr['errcode'] = 0;
      $reArr['errmsg'] = '';
      $reArr['data'] = '';
      /*$xml =
  <<<EOF
  <?xml version="1.0" encoding="UTF-8"?>
  <message><body><field name="resultCode">0</field><field name="errorCode"></field></body></message>
  EOF;
      $result           =   xmltoarray($xml);
      */
      //unset($data['mobilePhone']); //调试用
//    $result           =   xmltoarray($this->curl_get_new($data));
      $result = $this->curl_get_new($data);
//    $reArr['errcode'] = $result['body']['field'][0]; //errcode为0为发送成功
      $reArr['errcode'] = $result; //直接返回状态码
      $reArr['errmsg'] = $this->errorMsg[$result];
      $reArr['data'] = json_encode($result);

      return $reArr;
  }

  /**
   * 营销接口发送
   * @param   $data 数组   数组中具体传值见本文件底部文档
   * @return 接口返回 array
   */
    public function sendYxMessage($data)
    {
        $reArr = array();
        $reArr['errcode'] = 0;
        $reArr['errmsg'] = '';
        $reArr['data'] = '';
//    $result           =   xmltoarray($this->curl_get_new($data));
        $result = $this->curl_get_yx_new($data);
//    $reArr['errcode'] = $result['body']['field'][0]; //errcode为0为发送成功
        $reArr['errcode'] = $result; //直接返回状态码
        $reArr['errmsg'] = $this->errorMsg[$result];
        $reArr['data'] = json_encode($result);

        return $reArr;
  }


  /**
 * 发送get请求
 * @param $data 数组   数组中具体传值见本文件底部文档
 * @return 接口返回 xml string
 */
    public function curl_get($data) {
        $data['userName'] =  $data['userName'] ? : OP('yunrongt_userName','yes');
        $data['passWord'] =  $data['passWord'] ? : OP('yunrongt_passWord','yes');
        // $data['body']     = rawurlencode($data['body']);

        $getparam  = '?'.http_build_query($data);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "http://101.201.238.246/MessageTransferWebAppJs/servlet/messageTransferServiceServletByXml". $getparam);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 3);

        $return_str = curl_exec($curl);
        curl_close($curl);

        return $return_str;
    }

    /**
     * 新发送get请求
     * @param $data 数组   数组中具体传值见本文件底部文档
     * @return 接口返回 xml string
     */
    public function curl_get_new($data)
    {
        $data['username'] = $data['userName'] ?: OP('yunrongt_userName_new', 'yes');
        $data['password'] = md5($data['username'] . md5($data['passWord'] ?: OP('yunrongt_passWord_new', 'yes')));
        // $data['body']     = rawurlencode($data['body']);

        $getparam = '?' . http_build_query($data);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "http://47.98.61.138:9001/smsSend.do" . $getparam);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 3);

        $return_str = curl_exec($curl);
        curl_close($curl);

        return $return_str;
    }

  /**
   * 营销短信发送
   * @param  [type] $data [description]
   * @return [type]       [description]
   */
  public function curl_get_yx($data) {
      $data['userName'] =  $data['userName'] ? : OP('yunrongtyx_userName','yes');
      $data['passWord'] =  $data['passWord'] ? : OP('yunrongtyx_passWord','yes');
      // $data['body']     = rawurlencode($data['body']);

      $getparam  = '?'.http_build_query($data);

      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, "http://101.201.239.1/MessageTransferWebAppJs/servlet/messageTransferServiceServletByXml". $getparam);
      curl_setopt($curl, CURLOPT_HEADER, false);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_TIMEOUT, 3);

      $return_str = curl_exec($curl);
      curl_close($curl);

      return $return_str;
  }

    /**
     * 新营销短信发送
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function curl_get_yx_new($data)
    {
        $data['username'] = $data['userName'] ?: OP('yunrongtyx_userName_new', 'yes');
        $data['password'] = md5($data['username'] . md5($data['passWord'] ?: OP('yunrongtyx_passWord_new', 'yes')));
        // $data['body']     = rawurlencode($data['body']);

        $getparam = '?' . http_build_query($data);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "http://47.96.147.133:9001/smsSend.do" . $getparam);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 3);

        $return_str = curl_exec($curl);
        curl_close($curl);

        return $return_str;
    }


/**
 *
 *

短信平台接口文档
（HTTP 版）


V1.8.7


用 户 手 册



1.  版本说明
2.  协议描述
接口采用http协议，数据提交采用post方式，编码为UTF-8。
以下输入中的参数为http输入参数名，输出为http应答体内容。
3.  访问地址
http://101.201.238.246/MessageTransferWebAppJs/servlet/messageTransferServiceServletByXml
4.  接口定义
app：调用短信平台的应用系统。
短信平台：用于发送短信的系统，为应用app系统提供接口。

注：所有参数名首字母都为小写。

4.1批量发送相同内容不同手机号码短信(app->短信平台)
描述：群发相同内容短信，就是同时为多个号码发送相同内容的短信。

参数  数据类型  中文说明  描述  必填
输入：
cmd string    固定值:sendBatchMessage  y
userName  string  用户名 短信平台提供。 y
passWord  string  密码  短信平台提供。 y
messageId string  应用系统消息ID  如需要状态报告则不能为空，最长50位；或异常重发时平台进行防重发检查时用  N
resendFlag  string  重发标志  如果网络超时、网络异常时进行重发时送该参数，其值为“y”；平台会根据messageId进行重复检查，如果之前处理完成，则把之前处理结果返回。  N
clientMessageBatchId  string  应用系统发送批次号 用于整批停止发送时使用 n
contentType string  消息类型  短信为：sms/mt(默认值)
彩信为：mm/mt n
mobilePhones  string  手机号码(支持多个)  多个号码之间用英文逗号分割,最多支持200个。
如:13811440725,13811440726 y
body  string  短信内容  （短信内容），短信文本长度根据通道进行调整，目前包含签名500字, 超过500字符系统会自动分割成多条发送。
UTF8编码。 y
serviceCode string  特服号 短信平台为应用系统分配 n
serviceCodeExt  string  特服号扩展码  短信平台为应用系统分配 n
messagePriority int 发送优先级 短信平台为应用系统分配,取值为0--5，默认为0,数值大优先级高。 n
scheduleDateStr string  定时发送时间  格式 yyyyMMddHHmmss N
appNumber String  应用系统编码    N
srcNumber String  渠道编码    N
appBusinessNumber String  业务编码    N
costCenterNumber  String  成本中心编码    N
sendUserName  String  发送者用户   N
sendUserFullName  String  发送者全称   N
输出：
返回xml报文：
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<message>
<body>
<field-list name="RECORD" size=”2”>
<field-list name="1">
<field name=" messageId">1</field>
<field name=" mobilePhone">1391111111</field>
<field name=" resultCode">0</field>
</field-list>
<field-list name="2">
<field name=" messageId">1</field>
<field name=" mobilePhone">1391111112</field>
<field name=" resultCode">0</field>
</field-list>
</field-list>
<field name=" resultCode">0</field>
<field name=" errorCode">0</field>

</body>
</message>
Xml标签 类型  说明  描述
messageId String  提交消息id
mobilePhone String  手机号码
resultCode  String  处理结果  0：发送成功
非0：错误
errorCode String  错误码 错误码说明：
-9      用户名为空（可能提交的请求可是有误，系统这次GET和POST两种请求）
-1   用户名或口令错误
-2   IP验证错误
-3   定时日期错误
-10  余额不足
-101 userId为空
-102 目标号码为空
-103 内容为空
-104  群发手机号码大于200个或短信群发号码个数不能大于100条
200  目标号码错误
201  目标号码在黑名单中
202  内容包含敏感单词
203  特服号未分配
204  优先级错误(可以不传只进行发送)或分配通道错误
999  其他异常
-999   其它异常，短信内容可能为空

Get方式例子：
http://101.201.238.246/MessageTransferWebAppJs/servlet/messageTransferServiceServletByXml?cmd= sendBatchMessage&userName=***&passWord=***&mobilePhones=13100000000,13100000001&contentType=sms/mt&serviceCodeExt=&serviceCode=&body=%e6%82%a8%e5%a5%bd%ef%bc%81&messageId=******

4.2批量发送个性短信(app->短信平台)
描述：批量发送不同内容、不同接收人的短信。


参数  数据类型  中文说明  描述  必填
输入：
cmd string    固定为：sendBatchMessageX y
userName  string  用户名 短信平台为应用系统分配。  y
passWord  string  密码  短信平台为应用系统分配。  y
contentType string  消息类型  短信为：sms/mt(默认值)
彩信为：mm/mt n
messageQty  Int 消息个数  已下数据根据该值重复获取（1…messageQty）建议不要大于100 Y
clientMessageBatchId  string  应用系统发送批次号 用于整批停止发送时使用 n
messageId string  应用系统消息ID  如需要状态报告则不能为空，最长50位；或异常重发时平台进行防重发检查时用  n
resendFlag  string  重发标志  如果网络超时、网络异常时进行重发时送该参数，其值为“y”；平台会根据messageId进行重复检查，如果之前处理完成，则把之前处理结果返回。  n
循环获以下红色参数数据，每个参数名加上当前序号，比如body1….. bodyN，其他属性类似。
messageId string  应用系统消息ID  如需要状态报告则不能为空，最长50位  N
body  string  短信内容  （短信内容），短信文本长度根据通道进行调整，目前包含签名500字, 超过500字符系统会自动分割成多条发送。
UTF8编码。 y
mobilePhone String  手机号号码   Y
serviceCode string  特服号 短信平台为应用系统分配 Y
serviceCodeExt  string  特服号扩展码  短信平台为应用系统分配 n
messagePriority int 优先级 0---5 n
scheduleDateStr string  定时发送时间  格式 yyyyMMddHHmmss N
appNumber String  应用系统编码  非必填 N
srcNumber String  渠道编码    N
appBusinessNumber String  业务编码    N
costCenterNumber  String  成本中心编码    N
sendUserName  String  发送者用户   N
sendUserFullName  String  发送者全称   N
输出：
返回xml报文：
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<message>
<body>
<field-list name="RECORD" size=”2”>
<field-list name="1">
<field name=" messageId">1</field>
<field name=" mobilePhone">1391111111</field>
<field name=" resultCode">0</field>
</field-list>
<field-list name="2">
<field name=" messageId">2</field>
<field name=" mobilePhone">1391111112</field>
<field name=" resultCode">0</field>
</field-list>
</field-list>
<field name=" resultCode">0</field>
<field name=" errorCode">0</field>
</body>
</message>
Xml标签 类型  说明  描述
messageId String  提交消息id
mobilePhone String  手机号码
resultCode  String  处理结果  0：发送成功
非0：错误
errorCode String  错误码 错误码说明：
-9      用户名为空（可能提交的请求可是有误，系统这次GET和POST两种请求）
-1   用户名或口令错误
-2   IP验证错误
-3   定时日期错误
-10  余额不足
-101 userId为空
-102 目标号码为空
-103 内容为空
-104  群发手机号码大于200个或短信群发号码个数不能大于100条
200  目标号码错误
201  目标号码在黑名单中
202  内容包含敏感单词
203  特服号未分配
204  优先级错误(可以不传只进行发送)或分配通道错误
999  其他异常
-999   其它异常，短信内容可能为空

Get方式例子：
http://101.201.238.246/MessageTransferWebAppJs/servlet/messageTransferServiceServletByXml?cmd= sendBatchMessageX &userName=***&passWord=***&messageQty=2& contentType=sms/mt&serviceCodeExt=&serviceCode=&
mobilePhone1=13100000000&&body1=%e6%82%a8%e5%a5%bd%ef%bc%81&messageId1=****
mobilePhone2=13100000000&&body2=%e6%82%a8%e5%a5%bd%ef%bc%81&messageId2=****


4.3单条发送短信(app->短信平台)
描述：app调用该接口发送消息到用户手机。

参数  数据类型  中文说明  描述    必填
输入：
cmd string  函数名 固定为：sendMessage   y
userName  string  用户名 短信平台提供。   y
passWord  string  密码  短信平台提供。   y
messageId string  应用系统消息ID  如需要状态报告则不能为空，最长50位；或异常重发时平台进行防重发检查时用    N
resendFlag  string  重发标志  如果网络超时、网络异常时进行重发时送该参数，其值为“y”；平台会根据messageId进行重复检查，如果之前处理完成，则把之前处理结果返回。    N
clientMessageBatchId  string  应用系统发送批次号 用于整批停止发送时使用   n
contentType string  消息类型  短信为：sms/mt
彩信为：mm/mt   N
mobilePhone string  手机号码      y
body  string  短信内容  短信内容；
短信内容长度+短信签名长度不要超过500;超过500字符系统会自动分割成多条发送。   y
serviceCode string  特服号 手机上显示的长号码，短信平台为应用系统分配。    n
serviceCodeExt  string  特服号扩展码  应用自己扩展，为支持上行短信设置为空    n
scheduleDateStr String  定时发送时间  yyyyMMddhhmmss(为空则立即发送)   n
输出：
返回xml报文：
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<message>
<body>
<field name="resultCode">0</field >
<field name="errorCode"></field >
</body>
</ message>
Xml标签 类型  说明  描述
resultCode  int 返回状态值 0：发送成功
非0：错误
errorCode String  错误码 错误码说明：
-9      用户名为空（可能提交的请求可是有误，系统这次GET和POST两种请求）
-1   用户名或口令错误
-2   IP验证错误
-3   定时日期错误
-10  余额不足
-101 userId为空
-102 目标号码为空
-103 内容为空
-104  群发手机号码大于200个或短信群发号码个数不能大于100条
200  目标号码错误
201  目标号码在黑名单中
202  内容包含敏感单词
203  特服号未分配
204  优先级错误(可以不传只进行发送)或分配通道错误
999  其他异常
-999   其它异常，短信内容可能为空

Get方式例子：
http://101.201.238.246/MessageTransferWebAppJs/servlet/messageTransferServiceServletByXml?cmd=sendMessage&userName=***&passWord=***&mobilePhone=13100000000&contentType=sms/mt&serviceCodeExt=&serviceCode=&body=%e6%82%a8%e5%a5%bd%ef%bc%81&messageId=******

4.4发送语音验证码(app->短信平台)
描述：app调用该接口发送消息到用户手机。

参数  数据类型  中文说明  描述    必填
输入：
cmd string  函数名 固定为：sendAudioMessage    y
userName  string  用户名 短信平台提供。   y
passWord  string  密码  短信平台提供。   y
body  string  语音验证码 4或6位数字。   y
messageId string  应用系统消息ID  如需要状态报告则不能为空，最长50位；或异常重发时平台进行防重发检查时用    N
resendFlag  string  重发标志  如果网络超时、网络异常时进行重发时送该参数，其值为“y”；平台会根据messageId进行重复检查，如果之前处理完成，则把之前处理结果返回。    N
输出：
返回xml报文：
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<message>
<body>
<field name="resultCode">0</field >
<field name="errorCode"></field >
</body>
</ message>
Xml标签 类型  说明  描述
resultCode  int 返回状态值 0：发送成功
非0：错误
errorCode String  错误码 错误码说明：
-9      用户名为空（可能提交的请求可是有误，系统这次GET和POST两种请求）
-1   用户名或口令错误
-2   IP验证错误
-3   定时日期错误
-10  余额不足
-101 userId为空
-102 目标号码为空
-103 内容为空
200  目标号码错误
201  目标号码在黑名单中
202  内容包含敏感单词
204  优先级错误(可以不传只进行发送)或分配通道错误
999  其他异常
-999   其它异常，短信内容可能为空

Get方式例子：
http://101.201.238.246/MessageTransferWebAppJs/servlet/messageTransferServiceServletByXml?cmd= sendAudioMessage&userName=***&passWord=***&mobilePhone=13100000000& &body=%e6%82%a8%e5%a5%bd%ef%bc%81&messageId=******

4.5停止发送短信 (app ->短信平台)
描述： app调用该接口停止短信发送（平台排队发送及后续提交的短信）。

参数  数据类型  中文说明  描述  默认值 必填
输入：
cmd string  函数名   stopSendMessageByMessageClientBatchId y
userName  string  用户名 短信平台提供。   y
passWord  string  密码  短信平台提供。   y
clientMessageBatchId  string  应用系统发送批次号 发送短信是时提交的参数   y
输出：
返回xml报文：
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<message>
<body>
<field name="resultCode">0</field >
<field name="errorCode"></field >
</body>
</ message>
Xml标签 类型  说明  描述
resultCode  int 返回状态值 0：发送成功
非0：错误
errorCode String  错误码 错误码说明：
-1 用户名或口令错误
-2 IP验证错误

Get方式例子：
http://101.201.238.246/MessageTransferWebAppJs/servlet/messageTransferServiceServletByXml?cmd= stopSendMessageByMessageClientBatchId&userName=***&passWord=***& clientMessageBatchId=*****

4.6查询余额 (app ->短信平台)
描述： app调用该接口查询余额。
参数  数据类型  中文说明  描述  默认值 必填
输入：
cmd string  函数名   getBalance  y
userName  string  用户名 短信平台提供。   y
passWord  string  密码  短信平台提供。   y
输出：
返回xml报文：
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<message>
<body>
<field name="resultCode">0</field >
<field name="errorCode"></field >
<field name="balance"></field >
<field name="unitPrice"></field>
<field name="messageQtyBalance"></field>
</body>
</ message>
Xml标签 类型  说明  描述
resultCode  int 返回状态值 0：发送成功
非0：错误
errorCode String  错误码 错误码说明：
-1   用户名或口令错误
-2   IP验证错误
balance double  余额  元为单位
unitPrice double  发送单价  元为单位
messageQtyBalance int 可发送短信条数

4.7批量接收上行短信 (短信平台-> app，短信平台推送到应用系统)
描述：app提供消息接收接口（通过互联网能访问的地址），通信平台把上行短信或发送状态报告推送到app接收地址。

参数  数据类型  中文说明  描述  固定值 必填
输入：
userName  string  用户名 应用系统提供。   y
passWord  string  密码  应用系统提供。   y
messageQty  string  状态报告条数
循环获以下红色参数数据，每个参数名加上当前序号，比如第一条上行短信mobilePhone1、 serviceCode1、body1、dateTimeStr1，第二、第三条等类似。
mobilePhone string  手机号码
serviceCode string  特服号码
body  string  短信内容
dateTimeStr string  接收时间  yyyyMMddHHmmss
toUserName  string  平台为应用开通的用户名
输出（应用系统返回）：采用“#”分割处理结果和失败原因。
0#成功
-1#失败原因


4.8批量接收状态报告(短信平台-> app，短信平台推送到应用系统)
描述：app提供消息接收接口（通过互联网能访问的地址），通信平台把上行短信或发送状态报告推送到app接收地址。

参数  数据类型  中文说明  描述  固定值 必填
输入：
userName  string  用户名 应用系统提供。   y
passWord  string  密码  应用系统提供。   y
messageQty  string  状态报告条数
循环获以下红色参数数据，每个参数名加上当前序号，比如第一条状态报告submitMessageId1、 mobilePhone 1、dateTimeStr1、deliveryStatus1、deliveryStatusCode1(1为序号)，第二、第三条等类似。。
submitMessageId string  发送的消息id     Y
clientMessageBatchId  string  客户端批次id
mobilePhone string  手机号码
dateTimeStr string  接收时间  yyyyMMddHHmmss
deliveryStatus  string  状态报告标识  y 或true表示到达，n 或false表示到达失败；
deliveryStatusCode  string  状态报告  运营商返回的状态报告码
输出（应用系统返回）：采用“#”分割处理结果和失败原因。
0#成功
-1#失败原因


4.9查询发送批次发送状态(app ->短信平台)
描述： app调用该接口查询余额。
参数  数据类型  中文说明  描述  默认值 必填
输入：
cmd string  函数名   getSentInfoByMessageClientBatchId y
userName  string  用户名 短信平台提供。   y
passWord  string  密码  短信平台提供。   y
clientMessageBatchId  string  应用系统发送批次号 发送短信是时提交的参数   y
输出：
返回xml报文：
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<message>
<body>
<field name="resultCode">0</field >
<field name="errorCode"></field >
<field name="sendingQty"></field >
<field name="sentSuccessQty"></field>
<field name="sentFailQty"></field>
<field name="deliverSuccessQty"></field>
<field name="deliverFailQty"></field>
</body>
</ message>
Xml标签 类型  说明  描述
resultCode  int 返回状态值 0：发送成功
非0：错误
errorCode String  错误码 错误码说明：
-1   用户名或口令错误
-2   IP验证错误
-3      发送批次为空
sendingQty  int   发送中数量（短信平台排队发送）
sentSuccessQty  int   发送成功（提交运营商成功）数量
sentFailQty int   发送失败（提交运营商失败）数量
deliverSuccessQty int   状态报告成功（成功到达手机）数量
deliverFailQty  int   状态报告失败（到达手机失败）数量
 *
 *
 *
 */

}

