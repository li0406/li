<?php
/*
 *  Holly SDK
 *  合力亿捷 SDK
 *
 */

namespace Library\Org\Holly;

class HollySdk
{
    public $Appid;
    public $Account;
    public $Secret;
    public $ServerUri = 'http://a1.7x24cc.com'; // API 服务器uri
    public $AccessToken; // token

    // 调试控制
    public $enabeLog = false; //日志开关。可填值：true、
    public $Filename = "./loghollytel.txt"; //日志文件
    public $Handle; // 调试日志文件句柄

    //public  $Sig;

    /**
     *
     * 构造函数
     * Holly constructor.
     *
     * @param array $option
     */
    function __construct($option = [])
    {
        // $this->Batch       = date("YmdHms");
        self::setOption($option);
    }

    /**
     * 设置接口的基本配置信息
     *
     * @param Appid 应用ID
     */
    function setOption($option = [])
    {
        // api接口地址
        if (!empty($option['ServerUri'])) {
            $this->ServerUri = $option['ServerUri'];
        }

        // appid
        if (!empty($option['appid'])) {
            $this->Appid = $option['appid'];
        }
        // account
        if (!empty($option['account'])) {
            $this->Account = $option['account'];
        }
        // secret
        if (!empty($option['secret'])) {
            $this->Secret = $option['secret'];
        }

        // accessToken
        if (!empty($option['accessToken'])) {
            $this->AccessToken = $option['accessToken'];
        }


        // debug
        if ($option['debug']) {
            self::setDebug($option['debug']);
        }

    }

    /**
     * 设置调试模式 打开日志
     *
     * @param log  false or true
     */
    function setDebug($log = false)
    {
        if ($log) {
            $this->Handle   = fopen($this->Filename, 'a');  //关闭创建日志句柄
            $this->enabeLog = true; //日志开关。可填值：true、
        }
    }


    /**
     * 打印日志
     *
     * @param log 日志内容
     */
    function showlog($log)
    {
        if ($this->enabeLog) {
            fwrite($this->Handle, $log . "\n");
        }
    }


    /**
     * http post
     */
    function curl_post($url, $data = [], $header, $post = 1)
    {
        //初始化curl
        $ch = curl_init();
        //参数设置
        $res = curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, $post);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); //设置超时
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        if ($post)
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        //连接失败
        if ($result == FALSE) {
            return false;
        }

        curl_close($ch);
        return $result;
    }

    /**
     * http get
     *
     * @param str $url 发送接口地址
     *
     * @return  返回json数据
     */
    public function curl_get($url)
    {

        $ch = curl_init(); // 启动一个CURL会话
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); //设置超时
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
        $output = curl_exec($ch);     //返回api的json对象
        //关闭URL请求
        curl_close($ch);
        return $output;    //返回json对象
    }


    /**
     * 用户鉴权- 获取accessToken
     * 正常情况下，合力会返回下述JSON数据包：
     * {"success":true;"accessToken":"ACCESSTOKEN",﻿"invalidTime" : "2018-11-05 14:25:13","message":"更新accessToken成功"}
     */
    function getAccessToken()
    {
        // 生成请求URL
        // http://a1.7x24cc.com/accessToken?account=N000000004153&appid=vgismc892btsxlxr&secret=cf70ea10-8365-11e9-a1fd-ebb8d0d2346a
        $url = "$this->ServerUri/accessToken?account=$this->Account&appid=$this->Appid&secret=$this->Secret";
        $this->showlog("request url = " . $url);

        //  发请求
        $result = $this->curl_get($url);
        $this->showlog("response body = " . $result);

        $datas = json_decode($result, true);

        $result = self::hadleResult($datas);

        return $result;
    }


    /**
     *
     * 点击外呼
     * http://a6.7x24cc.com/#/interfaceNew/config_dialoutApi
     *
     * @param string $integratedid   合力7x24平台的登录名 、工号 、手机号码或邮箱
     *                               注 : 如果没有配置登录集成中单点登录 , 则该标识默认为登录名 ( 如 : 8001@xxxxx中的8001是登录名 )
     * @param string $phonenum       外呼号码
     *                               被呼叫用户的手机号码或固话号码
     * @param string $Variable       外显号码 ( 可传您自己的中继号码 ( 传递IVR流程中使用的参数 ) )
     *                               格式为 : Variable=directCallerIDNum%3d5810xxxx , 其中为directCallerIDNum%3d固定参数 ,
     *                               5810xxxx为外显号码
     * @param string $ExternalData   业务参数 ( 您可以在我们提供的外呼集成接口后面拼接上您自己的业务参数 , 我们通过通话记录回传给您 )
     *                               如果有多个参数 , 则用逗号连接 , 如下 : ExternalData=main:00001 , main1 : 00002
     *
     * @return  mixed
     */
    function dialout($integratedid, $phonenum, $Variable = '', $ExternalData = '')
    {
        // 生成请求URL
        // http://a1.7x24cc.com/commonInte?flag=106&account=N000000004153&phonenum=外呼号码&integratedid=贵公司系统与合力7x24平台用户对应的唯一标识符&&accessToken=xxxxxxxxxxxxx
        $url = "$this->ServerUri/commonInte?flag=106&account=$this->Account&phonenum=$phonenum&integratedid=$integratedid&&accessToken=$this->AccessToken";
        if (!empty($Variable)) {
            $url .= "&&Variable=$Variable";
        }
        if (!empty($ExternalData)) {
            $url .= "&&ExternalData=$ExternalData";
        } else {
            $url .= "&&ExternalData=CallSid:".self::uuid();
        }
        $this->showlog("request url = " . $url);

        //  发请求
        $result = $this->curl_get($url);
        $this->showlog("response body = " . $result);

        $datas = json_decode($result, true);

        $result = self::hadleResult($datas);

        return $result;
    }

    /**
     * 统一处理返回信息
     *
     * @param $result
     */
    function hadleResult($result)
    {
        if ($result == NULL) {
            $rearr['msg']      = "失败,接口返回为空!"; //信息
            $rearr['code']     = '-2'; //成功状态
            $rearr['api_msg']  = ''; //反馈信息
            $rearr['api_resp'] = $result;
            return $rearr;
        }

        if ($result != '200') {
            $rearr['msg']      = "失败!"; //信息
            $rearr['code']     = '-1'; //成功状态
            $rearr['api_msg']  = self::errorInfo($result); //反馈信息
            $rearr['api_resp'] = $result;
            return $rearr;
        } else {
            $rearr['msg']      = "成功!"; //信息
            $rearr['code']     = '0'; //成功状态
            $rearr['api_msg']  = self::errorInfo($result); //反馈信息
            $rearr['api_resp'] = $result;
            return $rearr;
        }
    }

    /**
     * API接口错误代码信息
     *
     * @param $id 错误代码id 可选
     */
    function errorInfo($id = '0')
    {
        $errorArray = [

            //点击外呼
            '301' => '请求接口时缺少参数',
            '303' => 'phonenum参数的格式不正确',
            '309' => 'accessToken验证失败,请重新获取',
            '401' => '账户不存在',
            '402' => '参数错误 ( 请联系合力7x24 , 技术支持热线 : 4006-689-826 )',
            '403' => '参数错误 ( 请联系合力7x24 , 技术支持热线 : 4006-689-826 )',
            '404' => 'integratedid参数不存在',
            '405' => 'key不存在',
            '407' => '坐席未登录',
            '408' => '坐席忙碌',
            '409' => 'ExternalData参数错误',
            '410' => '达到外呼防骚扰上限或该号码为限制外呼号码',
            '603' => 'flag参数错误',

            '200' => '成功',
        ];
        if (!empty($id)) {
            return $errorArray[$id];
        }
        if ($id == '0') {
            return $errorArray[$id];
        }
        return '未匹配的状态码!';
    }

    /**
     * 生成UUID 单机使用
     * @access public
     * @return string
     */
    static public function uuid() {
        $charid = md5(uniqid(mt_rand(), true));
        $hyphen = chr(45);// "-"
        $uuid = ''. //chr(123)// "{"
            substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12)
            . '';
            //.chr(125);// "}"
        return $uuid;
    }

}