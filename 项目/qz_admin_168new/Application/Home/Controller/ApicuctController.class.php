<?php
/**
 * Cuct 联通云总机API回调
 *
 * first created by wwek @ 2017年2月20日 14:55:30
 */

namespace Home\Controller;
use Think\Controller;

class ApicuctController extends Controller
{
    public function index()
    {
        die();
    }

    //呼叫请求、鉴权或被叫查询回调
    public function callreq()
    {
        if (!IS_POST) {
            echo '非法请求!';
            die();
        }
        //获取POST数据
        $result = file_get_contents("php://input");
        //转换为数组数据
        $dataarr             =  $this->xmltoarr($result);
        $dataarr['called']   =  $this->teladdxx($dataarr['called']); //把电话号码隐藏处理,打上星号
        $dataarr['action']   =  'CallAuth'; //动作
        //   callType：呼叫类型，0-双向回拨；1-电话直拨；2-语音验证码；
        //   3-语音通知；4-多方通话；5-电话呼入；99-查询被叫（号码保护模式下使用）；
        $dataarr['type']     =  $dataarr['callType']; // callType：呼叫类型 映射到数据库的 type字段
        $dataarr['callSid']  =  $dataarr['callId']; //呼叫id 映射到数据库的 callSid字段
        $dataarr['time_add'] =  date("Y-m-d H:i:s"); //增加时间

        //dump($dataarr);die(); //调试用,生成环境必须注释掉

        //数组数据入库
        M('log_telcenter')->add($dataarr);


        $strXML="<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>
                <response>
                <retcode>00000</retcode>
                <called></called>
                <reason>qz接口返回</reason>
                </response>
                ";
        echo $strXML;
    }

    //呼叫建立通知回调
    /**请求数据包括以下信息：
        1)    appId：应用 ID；
        2)    callId：呼叫 ID；
        3)    accountSid：开发者主账号 ID；
        4)    subAccountSid：子账号 ID（如果有的话）；
        5)    caller：主叫号码；
        6)    called：被叫号码；
        7)    startTime：通话开始时间；
        8)    userData：用户数据；
     */
    public function  callestablish()
    {
        if (!IS_POST) {
            echo '非法请求!';
            die();
        }
        //获取POST数据
        $result = file_get_contents("php://input");
        //转换为数组数据
        $dataarr             =  $this->xmltoarr($result);
        if ($this->isCuctSub($dataarr['called'])) {
            //如果被叫号码是联通云总机的分机号码
            //处理action标记
            $dataarr['action']   =  'CallEstablish_Sub'; //动作
        } else {
            $dataarr['action']   =  'CallEstablish'; //动作
            $dataarr['called']   =  $this->teladdxx($dataarr['called']); //把电话号码隐藏处理,打上星号
        }
        $dataarr['callSid']  =  $dataarr['callId']; //呼叫id 映射到数据库的 callSid字段
        $dataarr['starttime']=  $dataarr['startTime'] ? : ''; //开始时间 映射到数据库的 starttime 字段
        $dataarr['time_add'] =  date("Y-m-d H:i:s"); //增加时间

        //dump($dataarr);die(); //调试用,生成环境必须注释掉

        //数组数据入库
        M('log_telcenter')->add($dataarr);

        //页面返回数据
        $strXML="<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>
                <response>
                <retcode>00000</retcode>
                <reason>qz接口返回</reason>
                </response>
                ";
        echo $strXML;
    }

    //呼叫失败或挂机计费通知
    /**请求数据包括以下信息
        1)    appId：应用 ID；
        2)    callId：呼叫 ID；
        3)    accountSid：开发者主账号 ID；
        4)    subAccountSid：子账号 ID（如果有的话）；
        5)    caller：主叫号码；
        6)    called：被叫号码；
        7)    state：状态：0-呼叫挂机（默认值）；1-通话失败。
        8)    startTime：通话开始时间；
        9)    stopTime：通话结束时间；
        10)  duration：通话时长（秒）；
        11)  userdata：用户数据；
     */
    public function callhangup()
    {
        if (!IS_POST) {
            echo '非法请求!';
            die();
        }
        //获取POST数据
        $result = file_get_contents("php://input");
        //转换为数组数据
        $dataarr             =  $this->xmltoarr($result);
        if ($this->isCuctSub($dataarr['called'])) {
            //如果被叫号码是联通云总机的分机号码

            //处理action标记
            $dataarr['action']   =  'Hangup_Sub'; //动作
        } else {
            $dataarr['action']   =  'Hangup'; //动作
            $dataarr['called']   =  $this->teladdxx($dataarr['called']); //把电话号码隐藏处理,打上星号
        }
        $dataarr['callSid']  =  $dataarr['callId']; //呼叫id 映射到数据库的 callSid字段
        $dataarr['byetype']  =  $dataarr['state'];  // state：状态：0-呼叫挂机（默认值）；1-通话失败。  映射到数据库的 byetype字段
        $dataarr['starttime']=  $dataarr['startTime'] ? : ''; //开始时间 映射到数据库的 starttime 字段
        $dataarr['endtime']  =  $dataarr['stopTime'] ? : ''; //开始时间 映射到数据库的 endtime 字段
        $dataarr['time_add'] =  date("Y-m-d H:i:s"); //增加时间

        //dump($dataarr);die(); //调试用,生成环境必须注释掉

        //数组数据入库
        M('log_telcenter')->add($dataarr);

        //页面返回数据
        $strXML="<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>
                <response>
                <retcode>00000</retcode>
                <reason>qz接口返回</reason>
                </response>
                ";
        echo $strXML;

        //老版后台的电话拨打后的回调处理, 这次不使用 2017年2月23日 16:01:30
        /*$callSid = $dataarr['callSid']; //callsid
        //测试用 $callSid = '1408160909216548000100160005eeef';
        $dtelcenter = D('Telcenter');
        $ordercall = $dtelcenter->getOrdercallByCallsid($callSid); //取出订单呼叫记录

        //从发布订单到第一次打电话的时间差入库,以后的拨打不再入库 用于订单拨打及时率
        $callfast = $dtelcenter->getOrdercallfast($ordercall['orderid']);
        if ($callfast) { //如果查到数据
            if (empty($callfast['callfast_time'])) { //如果本字段为空就保存数据
                $data_callfast = array();
                $data_callfast['callfast_time']     =  strtotime($ordercall['time_add']) - $callfast['time'];
                $data_callfast['callfast_admin_id'] = $ordercall['admin_id'];
                $dtelcenter->saveOrdercallfast($ordercall['orderid'],$data_callfast);
            }

        }

        //记录未审核订单 待定订单 记录最长的一个通话时间
        $timecha     = strtotime($dataarr['endtime']) - strtotime($dataarr['starttime']); //本次拨打时长
        $orderinfo   = D('Orders')->getOrderOne($ordercall['orderid']); //订单信息
        if (('0' == $orderinfo['on']) || ('2' == $orderinfo['on'])) { //如果是未审核或者待定
            $calllong = $dtelcenter->getOrdercalllong($ordercall['orderid']); //数据库库中历史记录的时长
            if ($calllong) { //如果查到数据
                if ($timecha > $calllong['calllong_time']) { //当前的比历史的时长长
                    $data_calllong = array();
                    $data_calllong['calllong_time']     =  $timecha;
                    $data_calllong['calllong_admin_id'] =  $ordercall['admin_id'];
                    $dtelcenter->saveOrdercalllong($ordercall['orderid'],$data_calllong);
                }

            }
        }*/

    }

    //语音验证码状态通知接口
    public function voicecode()
    {
        if (!IS_POST) {
            echo '非法请求!';
            die();
        }
    }

    //语音通知状态通知回调
    public function voicenotify()
    {
        if (!IS_POST) {
            echo '非法请求!';
            die();
        }
        //获取POST数据
        $result = file_get_contents("php://input");
        //转换为数组数据
        $dataarr             =  $this->xmltoarr($result);
        $dataarr['called']   =  $this->teladdxx($dataarr['called']); //把电话号码隐藏处理,打上星号
        $dataarr['action']   =  'voicenotify'; //动作
        $dataarr['callSid']  =  $dataarr['callId']; //呼叫id 映射到数据库的 callSid字段
        $dataarr['time_add'] =  date("Y-m-d H:i:s"); //增加时间

        //打日志调试
        //load('extend');
        //Log::write(json_encode($dataarr), LOG::WARN, LOG::FILE, LOG_PATH . "yuntongxunapiback" . date("Y-m-d") . ".log");

        //数组数据入库
        M('log_telcenter_sellingcall_apiback')->add($dataarr);

        //返回页面数据
        $strXML='<?xml version="1.0" encoding="UTF-8"?>
                 <response>
                    <retcode>00000</retcode>
                    <reason>qz语音通知状态回调OK</reason>
                 </response>';
        echo $strXML;
    }

    //呼叫过程按键反馈
    public function  keyfeedback()
    {
        if (!IS_POST) {
            echo '非法请求!';
            die();
        }
        //获取POST数据
        $result = file_get_contents("php://input");
        //转换为数组数据
        //$dataarr             =  $this->xmltoarr($result);
        //$dataarr['time_add'] =  date("Y-m-d H:i:s"); //增加时间

        //数组数据入库
        //M('log_telcenter')->add($dataarr);


        $strXML="<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>
                <response>
                <retcode>00000</retcode>
                <called></called>
                <reason>qz接口返回</reason>
                </response>
                ";
        echo $strXML;
    }


    //空操作
    public function _empty() {
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        //$this->display('Public:404');
    }

    //xml 解析成 数组返回
    private function xmltoarr($result) {
        //解析XML
        $xml = simplexml_load_string(trim($result," \t\n\r"));
        return (array)$xml;
    }

    /**
     * 给电话处理加*** 给电话号码中间替换成星号
     * @param  $tel 电话号码
     * @return 加星后的电话号码
     */
    private function teladdxx($tel) {
        $tel = substr($tel, 0,3) . "*****" . substr($tel, -3); //被呼叫号码加星号
        return  $tel;
    }

    /**
     * 判断号码是否是联通云总机的 分机号码
     * @param  [type]  $tel [description]
     * @return boolean      [description]
     */
    private function isCuctSub($tel) {
        //联通云总机的分机号码为固定4位数,所以可以利用这个特性判断
        if (strlen($tel) == 4) {
            return true;
        }
        return false;
    }
}