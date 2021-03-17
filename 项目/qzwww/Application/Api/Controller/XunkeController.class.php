<?php

//百度寻客API

namespace Api\Controller;

use Think\Controller;

class XunkeController extends Controller
{

    //初始化
    public function _initialize()
    {

    }


    //我方百度寻客接收接口

    //接口回调配置地址 http://xunke.baidu.com/tool.html
    //可以配置百度往我放什么地址进行post
    //PC的用 http://oauthtmp.qizuang.com/xunke?type=pc
    //Mobile的用 http://oauthtmp.qizuang.com/xunke?type=mobile

    //以下为百度post过来的数据结构
    /***
    //<?xml version="1.0" encoding="UTF-8"?>
    //<TXTbaidu>
    //    <TXTbaiduRequest>
    //        <userName>${protocol.userName}</userName>
    //        <password>${protocol.password}</password>
    //    </TXTbaiduRequest>
    //    <body>
    //        <Mediasource>baidu-xunke</Mediasource>
    //        <Device>${leads.device}</Device>
    //        <baiduUserid>${leads.userId}</baiduUserid>
    //        <OptimizationURL><![CDATA[${leads.targetUrl}]]></OptimizationURL>
    //        <Query>${leads.query}</Query>
    //        <queryTime>${leads.queryTime?datetime}</queryTime>
    //        <cityName>${leadsForm.cityName}</cityName>
    //        <cityCode>${leadsForm.cityCode}</cityCode>
    //        <country>${leadsForm.country}</country>
    //        <level>${leadsForm.level}</level>
    //        <area>${leadsForm.area}</area>
    //        <mobile>${leadsForm.mobile}</mobile>
    //   </body>
    //</TXTbaidu>
    */
    public function xunkedata()
    {

        //获取POST数据
        $result = file_get_contents("php://input");
        //获取GET数据
        $getData = I('get.');
        //var_dump($getData);

        if (!$result) {
            //如果没有提交数据
            die('no');
        }

        //解析XML
        $xmlData = simplexml_load_string(trim($result," \t\n\r"),'SimpleXMLElement', LIBXML_NOCDATA); //字符串转换为xml对象
        $jsonData  = json_encode($xmlData); //xml对象转换为json对象
        $arrayData = json_decode($jsonData, true); //json对象转换为数组
        //var_dump($arrayData);

        /*array(2) {
          'TXTbaiduRequest' =>
          array(2) {
            'userName' =>
            string(2) "qz"
            'password' =>
            string(2) "qz"
          }
          'body' =>
          array(11) {
            'Mediasource' =>
            string(11) "baidu-xunke-pc"
            'Device' =>
            string(2) "PC"
            'baiduUserid' =>
            string(7) "5088110"
            'OptimizationURL' =>
            string(40) "http://www.qizuang.com/zhaobiao/?t=baidu"
            'Query' =>
            string(12) "苏州装修"
            'queryTime' =>
            string(19) "2016-04-19 15:46:40"
            'cityName' =>
            string(9) "上海市"
            'cityCode' =>
            string(6) "310000"
            'country' =>
            array(0) {
            }
            'name' =>
            string(12) "测试名称"
            'mobile' =>
            string(11) "18888888888"
          }
        }*/

        //验证帐号
        $userName = $arrayData['TXTbaiduRequest']['userName'];
        $password = $arrayData['TXTbaiduRequest']['password'];
        $authResult =  self::xunkeAuth($userName, $password); //认证
        if (!$authResult['status']) {
            //如果认证失败,返回信息
            die($authResult['info']);
        }

        //存数据
        $saveData = array();
        $saveData = $arrayData['body'];
        $saveData['qz_source'] = $getData['type'];
        if (is_array($saveData['country'])) {
            $saveData['country']   = implode(',', $saveData['country']);
        }
        $saveData['addtime']   = time();

        //存订单表
        $orderResult = self::xunkeSaveOrder($saveData);
        //var_dump($orderResult);

        //存寻客数据到寻客表
        if (!empty($orderResult['data'])) {
             $saveData['orderid']  = $orderResult['data'];
        }

        $xunkeResult = self::xunkeSaveData($saveData);
        //var_dump($xunkeResult);

        if ( (0 != $orderResult['status']) ) {
            die('ok');
        }

        //接口返回结果
        die('数据保存失败!');
    }


    /**
     * 寻客保存数据 到 qz_xunke表
     * @param   array $data 传入数据
     * @return  保存结果
     */
    private function xunkeSaveData($data)
    {
        if (empty($data)) {
            return false;
        }
        $xunkeData = $data;
        import('Library.Org.Util.App');
        $app         = new \App();
        $xunkeData['mobile_encrypt'] = $app->order_tel_encrypt($xunkeData['mobile']); //MD5加盐(电话号码)
        unset($xunkeData['mobile']); //干掉电话号码
        return D('Xunke')->xunkeSave($xunkeData);
    }


    /**
     * 寻客保存订单 到 qz_orders表
     * @param   array $datas 传入数据
     * @return
     *         //定义返回数据
     *   $redata = array(
     *                   'status' => 0, //0,1,2  0为失败,1为正常发单成功,2为黑名单发布成功
     *                   'data'   => '', //数据  默认是订单号
     *                  'info'   => '', //提示信息 辅助提示信息
     *                   );
     */
    private function xunkeSaveOrder($data)
    {
        //定义返回数据
        $redata = array(
                        'status' => 0, //0,1,2
                        'data'   => '', //数据
                        'info'   => '', //提示信息
                        );

        if (empty($data)) {
            $redata['status'] = 0;
            $redata['data']   = '';
            $redata['info']   = '传入的数据不能为空!';
            return $redata;
        }


        //绑定数据
        $xunkeOrderData          = array();
        $xunkeOrderData['tel']   = $data['mobile']; //电话
        if (!empty($data['name'])) {
            $xunkeOrderData['name']  = $data['name']; //名称
        }

        //档次
        if (!empty($data['level'])) {
            switch ($data['level']) {
                case '简装':
                    $xunkeOrderData['zxdc'] = '1';
                    break;
                case '精装':
                    $xunkeOrderData['zxdc'] = '2';
                    break;
                case '豪装':
                    $xunkeOrderData['zxdc'] = '3';
                    break;
                case '奢华':
                    $xunkeOrderData['zxdc'] = '4';
                    break;
                default:
                    $xunkeOrderData['zxdc'] = intval($data['level']);
                    break;
            }
        }
        //面积
        if (!empty($data['area'])) {
            $xunkeOrderData['mianji']  = (int) $data['area']; //面积
        }
        //把档次和面积拼接也存入需求
        $xunkeOrderData['text'] = '装修档次:' . $data['level'] . " 面积:" . $data['area'];
        $xunkeOrderData['text'] = remove_xss($xunkeOrderData['text']);

        //标识齐装网的orders表的source
        /*//百度寻客
                 "501"   => "百度寻客-PC",
                 "510"   => "百度寻客-Mobile",
        */
        switch ($data['qz_source']) {
            case 'pc':
                //$xunkeOrderData['source']        = '501';
                $xunkeOrderData['source_src']    = 'xunke_pc';
                $xunkeOrderData['source_src_id'] = '94';
                break;
            case 'mobile':
                //$xunkeOrderData['source']        = '510';
                $xunkeOrderData['source_src']    = 'xunke_mobile';
                $xunkeOrderData['source_src_id'] = '95';
                break;
            default:
                $xunkeOrderData['source'] = '0';
                break;
        }

        //标识城市
        if (!empty($data['cityName'])) {
            $cityInfo = D('Common/Quyu')->getCityIdByCname($data['cityName']); //通过城市名称查出齐装网城市id编号
            $xunkeOrderData['cs'] = $cityInfo['cid']; //城市
            $xunkeOrderData['sf'] = $cityInfo['uid']; //省份
        }


        //先检查黑名单
        $isTelBlack = D('Common/Ordersblack')->checkTelBlackTrue($xunkeOrderData['tel']);
        if ($isTelBlack) { //号码在黑名单中
            //那么单子就发到qz_orders_black 表中去
            //后台管理功能在 订单管理>黑名单订单管理
            $ordersave = D('Common/Ordersblack')->orderpublish($xunkeOrderData, 'insert');
            $redata['status'] = 2;
            $redata['data']   = $ordersave;
            $redata['info']   = '黑名单发布成功!';
        }else {
            //号码不在黑名单中
            //正常订单流程

            //如果24小时内，该ip，该城市发布过订单，且订单为未审核状态
            //返回这个发布号码的单号
            $chkhistory = D('Common/Orders')->order_chk_history($xunkeOrderData['tel'], 0);
            if($chkhistory != null) {
                //如果本手机24小时内发布过单子, 完善订单
                $redata['status'] = 1;
                $redata['data']   = $chkhistory;
                $redata['info']   = '今天已经发布过啦!';
            } else {
                $orderid = D('Common/Orders')->orderpublish($xunkeOrderData,"insert"); //传入插入新订单
                $redata['status'] = 1;
                $redata['data']   = $orderid;
                $redata['info']   = '订单发布成功!';
            }
        }
        return $redata;
    }

    /**
     * 寻客帐号密码认证
     * @param   str $username 帐号
     * @param   str $password 密码
     * @return  array  $redata = array(
     *                                 'status' => false, //是否成功
     *                                  'info'   => '', //信息
     *                            );
     */
    private function xunkeAuth($username, $password)
    {
        //定义返回数据
        $redata = array(
                        'status' => false, //是否成功
                        'info'   => '', //信息
                        );

        if (empty($username) || empty($password)) {
            $redata['status'] = false;
            $redata['info']   = '缺少帐号或者密码!';
        } else {
            if ($username == C('XUNKE_USERNAME') & $password == C('XUNKE_PASSWORD') ) {
                $redata['status'] = true;
                $redata['info']   = '认证通过!';
            } else {
                $redata['status'] = false;
                $redata['info']   = '认证失败!';
            }
        }
        return $redata;
    }


}
