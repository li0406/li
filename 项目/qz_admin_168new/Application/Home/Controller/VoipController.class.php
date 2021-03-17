<?php
namespace Home\Controller;
use Common\Enums\ClinkEnum;
use Home\Model\Logic\HollyLogicModel;
use Home\Model\Logic\OptionsLogicModel;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\OrdersLogicModel;
use Home\Model\Service\PhoneCallClinkServiceModel;
use Home\Model\Service\PhoneCallHollyServiceModel;

import('Library.Org.Holly.Holly',"",".php");
use Library\Org\Holly\Holly;

class VoipController extends HomeBaseController
{
    public function index()
    {
        $time = strtotime("-2 month");
        $info = D("voip")->getvoiplist();
        foreach ($info as $key => $value) {
            if ($value['use_on'] == 1) {
                $info[$key]['unused'] = '1';
            }else{
                $info[$key]['unused'] = '0';
            }
            //处理绑定时间不显示为1970-01-01
            if ($value['use_time'] == '0') {
                $info[$key]['use_time'] = '';
            }else{
                $info[$key]['use_time'] = date("Y-m-d H:i:s",$value['use_time']);
            }
            //如果没有使用人，则显示显示为空，不显示数字0
            if ($value['use_id'] == 0) {
                $info[$key]['use_id'] = '';
            }

            $info[$key]['time_add'] = date("Y-m-d H:i:s",$value['time_add']);
        }

        $this->assign("info",$info);
        $this->display();
    }

    /**
     * VOIP电话拨打
     * @return [type] [description]
     */
    public function tel_voipcallback()
    {
        if ($_POST) {
            $order_id = I("post.id");
            $user_id = session("uc_userinfo.id");
            $user_name = session("uc_userinfo.name");
            //获取电话商设置
            $optionModel = new OptionsLogicModel();
            $channel = $optionModel->getMyTelCenterChannelByid($user_id);

            switch ($channel){
                case "holly":
                    //合力电话
                    $result = self::callHolly($order_id,$user_id,$user_name,'order');

                    break;
                case "clink":
                    $result =  self::callClink($order_id,$user_id,$user_name,'order');
                    break;
                default:
                    //默认电话
                    $result = self::telephoneVoipCall(I("post."));
                    break;
            }
            $this->ajaxReturn(array("code"=>$result["code"],"errmsg"=>$result["errmsg"]));
        }
    }

    public function callClink($order_id,$user_id,$user_name,$logType,$call_obj = '')
    {
        //查询业主发布的电话
        $orderModel =  new OrdersLogicModel();

        //自助拨打不需要查询订单
        if($logType != 'diy'){
            $orderInfo = $orderModel->findOrderInfoAndTel($order_id);
            if (empty($orderInfo["tel8"])) {
                return array("code"=>4000001,"errmsg"=>'该订单无业主电话,无法拨打');
            }
            $tel = $orderInfo["tel8"];
        }else{
            //如果不验证订单号 , 则将订单号换成手机号使用
            $tel = $order_id;
        }

        $service = new PhoneCallClinkServiceModel();
        //获取用户电话信息
        $userInfo = $service->getPhoneInfo($user_id);

        if (empty($userInfo["voipaccount"])) {
            return array("code"=>4000001,"errmsg"=>'请先设置VOIP号码');
        }

        //拨打电话
        $result = $service->call($userInfo["voipaccount"],$tel);
        
        
        if (!empty($result["data"])) {
            switch ($logType) {
                case "order":
                    $params = [
                        "order_id" => $order_id,
                        "op_uid" => $user_id,
                        "op_uname" => $user_name,
                        "call_sid" => $result["data"],
                        "time" => time()
                    ];

                    $service->callBackToOrderLog($params);

                    //订单状态操作
                    if ($orderInfo["on"] == 0 && $orderInfo["on_sub"] == 10) {
                        //新单变成次新单
                        $order = A("Home/Orders");
                        $order->orderStatusChange($orderInfo["id"],0,9,0);
                    }
                    break;

                case 'review':
                    $params = [
                        "order_id" => $order_id,
                        "op_uid" => $user_id,
                        "op_uname" => $user_name,
                        "call_sid" => $result["data"],
                        "time" => time()
                    ];
                    $service->callBackToOrderReviewLog($params);
                    break;
                case 'diy':
                    //自助拨打
                    $params = [
                        "call_obj" => $call_obj,
                        "op_uid" => $user_id,
                        "op_uname" => $user_name,
                        "call_sid" => $result["data"],
                        "time" => time()
                    ];
                    $service->callBackToDiyLog($params);
                    break;
            }
        }
        return ["code" =>  $result["code"],"errmsg" => $result["errmsg"]];
    }

    /**
     * VOIP电话拨打
     * @return [type] [description]
     */
    public function tel_review_voipcallback()
    {
        if ($_POST) {
            $order_id = I("post.id");
            $user_id = session("uc_userinfo.id");
            $user_name = session("uc_userinfo.name");
            //获取电话商设置
            $optionModel = new OptionsLogicModel();
            $channel = $optionModel->getMyTelCenterChannelByid($user_id);
            switch ($channel){
                case "holly":
                    //合力电话
                    $result = self::callHolly($order_id,$user_id,$user_name,'review');
                    break;
                case "clink":
                    $result =  self::callClink($order_id,$user_id,$user_name,'review');
                    break;
                default:
                    //默认电话
                    $result = self::telephoneVoipCall(I("post."));
                    break;
            }
            $this->ajaxReturn(array("code"=>$result["code"],"errmsg"=>$result["errmsg"]));
        }
    }
    /**
     * VOIP电话拨打
     * @return [type] [description]
     */
    public function tel_diy_voipcallback()
    {
        if ($_POST) {
            $tel = I("post.calltel");
            $call_obj = I("post.calltowho");
            $user_id = session("uc_userinfo.id");
            $user_name = session("uc_userinfo.name");
            //获取电话商设置
            $optionModel = new OptionsLogicModel();
            $channel = $optionModel->getMyTelCenterChannelByid($user_id);
            switch ($channel){
                case "holly":
                    //合力电话
                    $result = self::callHolly($tel,$user_id,$user_name,'diy',$call_obj);
                    break;
                case "clink":
                    $result =  self::callClink($tel,$user_id,$user_name,'diy',$call_obj);
                    break;
                default:
                    //默认电话
                    $result = self::telephoneVoipCall(I("post."));
                    break;
            }
            $this->ajaxReturn(array("code"=>$result["code"],"errmsg"=>$result["errmsg"]));
        }
    }

    /**
     * 非客服拨打订单voip
     * @return [type] [description]
     */
    public function other_order_voipcallback()
    {
        if ($_POST) {
            $order_id = I("post.id");
            $user_id = session("uc_userinfo.id");
            $user_name = session("uc_userinfo.name");
            //获取电话商设置
            $optionModel = new OptionsLogicModel();
            $channel = $optionModel->getMyTelCenterChannelByid($user_id);
            switch ($channel){
                case "holly":
                    //合力电话
                    $result = self::callHolly($order_id,$user_id,$user_name,'other');
                    break;
                default:
                    //默认电话
                    $result = self::telephoneVoipCall(I("post."));
                    break;
            }
            $this->ajaxReturn(array("code"=>$result["code"],"errmsg"=>$result["errmsg"]));
        }
    }


    /**
     * 合力电话呼叫
     * @param $order_id 订单id[自助拨打手机号]
     * @param $user_id
     * @param $user_name
     * @param $logType 拨打类型
     * @param string $call_obj 自助拨打对象
     * @return array
     */
    public function callHolly($order_id,$user_id,$user_name,$logType,$call_obj = '')
    {
        //查询业主发布的电话
        $orderModel =  new OrdersLogicModel();

        //自助拨打不需要查询订单
        $orderInfo = $orderModel->findOrderInfoAndTel($order_id);
        if (empty($orderInfo["tel8"])) {
            return array("code"=>4000001,"errmsg"=>'该订单无业主电话,无法拨打');
        }
        $tel = $orderInfo["tel8"];


        $service = new PhoneCallHollyServiceModel();
        //获取用户电话信息
        $userInfo = $service->getPhoneInfo($user_id);

        if (empty($userInfo["voipaccount"])) {
            return array("code"=>4000001,"errmsg"=>'请先设置VOIP号码');
        }

        //拨打电话
        $result = $service->call($userInfo["voipaccount"],$tel);

        if ($result["error_code"] == 200) {
            //添加回调日志
            switch ($logType){
                case 'order':
                    $params = [
                        "order_id" => $order_id,
                        "op_uid" => $user_id,
                        "op_uname" => $user_name,
                        "call_sid" => $result["data"],
                        "time" => time()
                    ];
                    $service->callBackToOrderLog($params);

                    //订单状态操作
                    if ($orderInfo["on"] == 0 && $orderInfo["on_sub"] == 10) {
                        //新单变成次新单
                        $order = A("Home/Orders");
                        $order->orderStatusChange($orderInfo["id"],0,9,0);
                    }

                    break;
                case 'review':
                    $params = [
                        "order_id" => $order_id,
                        "op_uid" => $user_id,
                        "op_uname" => $user_name,
                        "call_sid" => $result["data"],
                        "time" => time()
                    ];
                    $service->callBackToOrderReviewLog($params);
                    break;
                case 'other':
                    $params = [
                        "order_id" => $order_id,
                        "op_uid" => $user_id,
                        "op_uname" => $user_name,
                        "call_sid" => $result["data"],
                        "time" => time()
                    ];
                    $service->callBackToOtherLog($params);
                    break;
                case 'diy':
                    //自助拨打
                    $params = [
                        "call_obj" => $call_obj,
                        "op_uid" => $user_id,
                        "op_uname" => $user_name,
                        "call_sid" => $result["data"],
                        "time" => time()
                    ];
                    $service->callBackToDiyLog($params);
                    break;
            }
            return array("code"=>200,"errmsg"=>'通话成功！');
        }
        return array("code"=>4000001,"errmsg"=>$result["error_msg"]);
    }

    /**
     * voip电话记录
     * @return [type] [description]
     */
    public function voiprecord()
    {
        //判断是否是专门查询回访电话 , 不传callback 则取订单所有通话记录
        if(I("get.callback") == 1){
            $result = $this->getVoipBackRecordList(I("get.id"));
        }else{
            $result = $this->getVoipRecordList(I("get.id"));
        }

        $this->assign( "list" , $result);
        if (IS_AJAX) {
            if (I("get.type") == "qc") {
                $tmp = $this->fetch("Order/qc_tel_history");
            } else {
                $tmp = $this->fetch("Order/tel_history");
            }

            $this->ajaxReturn(array("status"=>1,"data"=>$tmp));
        } else {
            $this->display();
        }
    }

    /**
     * voip电话记录
     * @return [type] [description]
     */
    public function otherVoipRecord()
    {
        //判断是否是专门查询回访电话 , 不传callback 则取订单所有童话记录
        $result = $this->getOtherVoipRecordList(I("get.id"));
        $this->assign( "list" , $result);
        $this->display("voiprecord");
    }

    /**
     * 非客服订单拨打VOIP电话
     * @param  [type] $data []
     * @return [type]       [description]
     */
    private function otherTelephoneVoipCall($data)
    {
        //取当前登录用户 电话提供商设置
        $Doption                        = D('Options');
        $TelCenter_Channel              = $Doption->getMyTelCenterChannelByid(session("uc_userinfo.id")); //获取当前帐号的电话提供商信息
        $TelCenter_ChannelInfo          = $Doption->getTelCenterChannelInfoByChannel($TelCenter_Channel); //通过提供商标识 获取完整的信息

        //查询VOIP电话号码
        $adminUser = D("Home/Adminuser");
        $userInfo = $adminUser->findUserVoipInfo(session("uc_userinfo.id"), $TelCenter_ChannelInfo['solutions']);

        if (empty($userInfo["voipaccount"])) {
           return array("code"=>"404","errmsg"=>"请先设置VOIP号码");
        }

        //查询业主发布的电话
        $orders = D("Home/Orders");
        $orderInfo = $orders->findOrderInfoAndTel($data["id"]);

        if (empty($orderInfo["tel8"])) {
           return array("code"=>"404","errmsg"=>"该订单无业主电话,无法拨打");
        }

        $time = time();
        //3分钟内的新单无法呼叫
        $time_offset = ($time - $orderInfo["time_real"]);
        if ($time_offset < 180) {
            return array("code"=>"404","errmsg"=>"该订单发布时间在3分钟以内不能拨打");
        }

        //呼叫频率限制，如果该订单
        $voip = D("Home/Logic/LogTelcenterOtherordercallLogic");

        //取当前电话提供商
        $Doption        = D('Options');
        $TelCenter_Channel      = $Doption->getMyTelCenterChannelByid(session("uc_userinfo.id"));

        //提供商选择逻辑
        if ($TelCenter_Channel == 'cuct') {
            //走 联通云总机
            $from           = $userInfo['tel_work1']; //VOIP主叫号码
            $toTel          = $orderInfo['tel8']; //被叫电话号码
            $cuctResult     = D('Telcuct')->callBack($from, $toTel);

            if($cuctResult['resp']['respCode'] == '0') {
                //如果订单是新单，转成次新单
                if ($orderInfo["on"] == 0 && $orderInfo["on_sub"] == 10) {
                    $order = A("Home/Order");
                    $order->orderStatusChange($orderInfo["id"],0,9,0);
                }

                //呼叫记录日志
                $logData['calltype']       = 'callBack';
                $logData['orderid']        = $data["id"];
                $logData['callSid']        = $cuctResult['resp']['callBack']['callId'];
                //因为联通云总机主叫号码是绑定的号码,并非voip号,所以这里给voip号码, 故意不给主叫(主叫是绑定的号码)
                $logData['fromtel']        = $userInfo['voipaccount']; //主叫
                $logData['totel']          = substr($toTel, 0,3) . '*****' . substr($toTel, -3); //被叫
                $logData['fromSerNum']     = ''; //主叫显号
                $logData['customerSerNum'] = $userInfo['tel_customer_ser_num']; //被叫显号
                $logData['time_add']       = date('Y-m-d H:i:s');
                $logData['admin_id']       = session("uc_userinfo.id");
                $logData['admin_user']     = session("uc_userinfo.user");
                $logData['appid']          = OP('CUCT_APPID_QZ');
                $logData['channel']        = $TelCenter_Channel; //电话提供商渠道

                $voip->addLog($logData);
                //返回ajax
                return array("code"=>"200","errmsg"=>"呼叫中,请注意接听");
            } else {
                // 记录日志
                $logArr['time']   = time();
                $logArr['log']    = json_encode($cuctResult);
                $logArr['level']  = '失败';
                $logArr['module'] = 'cuct_callback';
                D('LogMain')->addLog($logArr);
                //接口不成功 返回msg信息
                return array("code"=>"404","errmsg"=>$cuctResult['msg']);
            }
       }

        return array("code"=>"404","errmsg"=>"呼叫失败,请稍后再试");
    }

    /**
     * 客服拨打VOIP电话
     * @param  [type] $data []
     * @return [type]       [description]
     */
    private function telephoneVoipCall($data)
    {
        //判断是否是二次互访电话(走二次回访页面过来的)
        $callback = '';
        if ($data['callback'] == 1) {
            $callback = 1;
            unset($data['callback']);
        }
        //取当前登录用户 电话提供商设置
        $Doption                        = D('Options');
        $TelCenter_Channel              = $Doption->getMyTelCenterChannelByid(session("uc_userinfo.id")); //获取当前帐号的电话提供商信息
        $TelCenter_ChannelInfo          = $Doption->getTelCenterChannelInfoByChannel($TelCenter_Channel); //通过提供商标识 获取完整的信息

        //查询VOIP电话号码
        $adminUser = D("Home/Adminuser");
        $userInfo = $adminUser->findUserVoipInfo(session("uc_userinfo.id"), $TelCenter_ChannelInfo['solutions']);

        if (empty($userInfo["voipaccount"])) {
           return array("code"=>"404","errmsg"=>"请先设置VOIP号码");
        }

        //查询业主发布的电话
        $orders = D("Home/Orders");
        $orderInfo = $orders->findOrderInfoAndTel($data["id"]);

        if (empty($orderInfo["tel8"])) {
           return array("code"=>"404","errmsg"=>"该订单无业主电话,无法拨打");
        }

        $time = time();
        //3分钟内的新单无法呼叫
        $time_offset = ($time - $orderInfo["time_real"]);
        if ($time_offset < 180) {
            return array("code"=>"404","errmsg"=>"该订单发布时间在3分钟以内不能拨打");
        }

        //呼叫频率限制，如果该订单
        $voip = D("Home/LogTelcenterOrdercall");

        //取当前电话提供商
        $Doption        = D('Options');
        $TelCenter_Channel      = $Doption->getMyTelCenterChannelByid(session("uc_userinfo.id"));

        //提供商选择逻辑
        if ($TelCenter_Channel == 'cuct') {
            //走 联通云总机
            $from           = $userInfo['tel_work1']; //VOIP主叫号码
            $toTel          = $orderInfo['tel8']; //被叫电话号码
            $cuctResult     = D('Telcuct')->callBack($from, $toTel);

            if($cuctResult['resp']['respCode'] == '0') {
                //如果订单是新单，转成次新单
                if ($orderInfo["on"] == 0 && $orderInfo["on_sub"] == 10) {
                    $order = A("Home/Order");
                    $order->orderStatusChange($orderInfo["id"],0,9,0);
                }
                //呼叫记录日志
                $logData['calltype']       = 'callBack';
                $logData['orderid']        = $data["id"];
                $logData['callSid']        = $cuctResult['resp']['callBack']['callId'];
                //因为联通云总机主叫号码是绑定的号码,并非voip号,所以这里给voip号码, 故意不给主叫(主叫是绑定的号码)
                $logData['fromtel']        = $userInfo['voipaccount']; //主叫
                $logData['totel']          = substr($toTel, 0,3) . '*****' . substr($toTel, -3); //被叫
                $logData['fromSerNum']     = ''; //主叫显号
                $logData['customerSerNum'] = $userInfo['tel_customer_ser_num']; //被叫显号
                $logData['time_add']       = date('Y-m-d H:i:s');
                $logData['admin_id']       = session("uc_userinfo.id");
                $logData['admin_user']     = session("uc_userinfo.user");
                $logData['appid']          = OP('CUCT_APPID_QZ');
                $logData['channel']        = $TelCenter_Channel; //电话提供商渠道
                $logData['on']             = $orderInfo['on'];
                $logData['on_sub']         = $orderInfo['on_sub'];

                $id = $voip->addLog($logData);
                //如果是二次回访电话 , 则记录到qz_company_liangfang_telback表
                if($id && $callback){
                    D('Home/Logic/CompanyLiangfangLogic')->saveTelBackInfo($id,$data["id"]);
                }
                //返回ajax
                return array("code"=>"200","errmsg"=>"呼叫中,请注意接听");
            } else {
                // 记录日志
                $logArr['time']   = time();
                $logArr['log']    = json_encode($cuctResult);
                $logArr['level']  = '失败';
                $logArr['module'] = 'cuct_callback';
                D('LogMain')->addLog($logArr);
                //接口不成功 返回msg信息
                return array("code"=>"404","errmsg"=>$cuctResult['msg']);
            }
        } elseif ($TelCenter_Channel == 'ytx') {
            //走 容联云通讯

            //拨打电话
            $customerSerNum = $userInfo['tel_customer_ser_num'];//显号号码
            $fromTel = $userInfo["voipaccount"]; //VOIP主叫号码
            $toTel  = $orderInfo['tel8']; //被叫电话号码

            import('Library.Org.yuntongxun.Telytx');
            //主帐号
            $accountSid = OP('YTX_ACCOUNTSID');
            //主帐号Token
            $accountToken = OP('YTX_ACCOUNTTOKEN');
            //应用Id 客服专用
            $appId = OP('YTX_APPID');
            //请求地址
            $serverIP = OP('YTX_SERVERIP');
            //请求端口
            $serverPort = OP('YTX_SERVERPORT');
            //REST版本号
            $softVersion = OP('YTX_SOFTVERSION');
            //子帐号
            $subAccountSid = $userInfo['ytx_subaccountsid'];
            //子帐号Token
            $subAccountToken = $userInfo['ytx_subtoken'];
            //VoIP帐号
            $voIPAccount = $userInfo['voipaccount'];
            //VoIP密码
            $voIPPassword = $userInfo['voippwd'];

            $telYtx = new \Telytx($accountSid, $accountToken, $appId, $serverIP, $serverPort, $softVersion, $subAccountSid, $subAccountToken, $voIPAccount, $voIPPassword);

            $result = $telYtx->callBack($fromTel,$toTel,$customerSerNum);

            if ($result["status"] != 0) {
                //如果订单是新单，转成次新单
                if ($orderInfo["on"] == 0 && $orderInfo["on_sub"] == 10) {
                    $order = A("Home/Order");
                    $order->orderStatusChange($orderInfo["id"],0,9,0);
                }

                //添加呼叫记录日志
                //呼叫记录日志
                $logData = array();
                $logData['calltype'] = 'callBack';
                $logData['orderid'] = $data["id"];
                $logData['callSid'] = $result['callSid'];
                $logData['fromtel'] = $fromTel;
                $logData['totel'] = substr($toTel, 0,3) . '*****' . substr($toTel, -3);
                $logData['customerSerNum'] = $customerSerNum;
                $logData['time_add'] = $result['dateCreated'];
                $logData['admin_id'] = session("uc_userinfo.id");
                $logData['admin_user'] = session("uc_userinfo.user");
                $logData['appid']  = $appId;
                $logData['channel']= $TelCenter_Channel; //电话提供商渠道
                $logData['on']     = $orderInfo['on'];
                $logData['on_sub'] = $orderInfo['on_sub'];
                $voip->addLog($logData);
                return array("code"=>"200","errmsg"=>"呼叫中,请注意接听");
            }
        }

        return array("code"=>"404","errmsg"=>"呼叫失败,请稍后再试");
    }

    /**
     * 获取VOIP电话列表
     * @param  [type] $orderid 订单id
     * @return [type]       [description]
     */
    private function getVoipRecordList($orderid)
    {
        $db = D('LogTelcenterOrdercall');
        $result = $db->getOrderCallListByOrderId($orderid);
        $result = $db->callistHuman($result, 1); //数据再加工
        foreach ($result as $key => &$value) {
            $value['tonghuasj'] = $value['endtime'] - $value['starttime']; //通话时长
            //呼叫动作
            switch ($value['action']) {
                case 'CallAuth':
                    $value['action'] = "开始";
                    break;
                case 'CallEstablish':
                    $value['action'] = "接通";
                    break;
                case 'Hangup':
                    $value['action'] = "挂断";
                    break;
                case 'CallEstablish_Sub':
                    $value['action'] = "主叫接通";
                    break;
                case 'Hangup_Sub':
                    $value['action'] = "主叫挂断";
                    break;
                default:
                    # code...
                    break;
            }
            //拨打方式
            switch ($value['calltype']) {
                case 'callBack':
                    $value['calltype'] = "回拨拨打";
                    break;
                default:
                    break;
            }
            //挂断方式
            if ($value['TelCenter_Channel'] == 'cuct') {
                //对联通云总机的挂机代码处理
                //state：状态：0-呼叫挂机（默认值）；1-通话失败。
                switch ($value['byetype']) {
                    case '0':
                        $value['byetypewz'] = '呼叫挂机';
                        break;
                    case '1':
                        $value['byetypewz'] = '通话失败';
                        break;
                    default:
                        $value['byetypewz'] = '-';
                        break;
                }
            }else{
                // 默认是 云通讯的挂机代码
                $value['byetypewz'] = $db->getByeTypeInfo($value['byetype']);
            }
        }

        //查询合力的电话记录
        $model = new HollyLogicModel();
        $list = $model->getOrderTelLogById($orderid);

        $list = $db->callistHuman($list, 1); //数据再加工
        if (count($list) > 0) {
            if (count($result) > 0) {
                $result = array_merge($result,$list);
            } else {
                $result = $list;
            }

            //按时间重新排序
            foreach ($result as $key => $value)
            {
                $edition[] = $value['time_add'];
            }
            array_multisort($edition, SORT_DESC, $result);
        }

        //查询天润通话记录
        $model = new PhoneCallClinkServiceModel();
        $list = $model->getOrderTelLogById($orderid);

        if (count($list) > 0) {
            foreach ($list as $item) {
                $result[] = [
                    "time_add" => date("Y-m-d H:i:s",$item["created_at"]),
                    "byetype" => ClinkEnum::getStatus($item["cdr_status"]),
                    "call_time_length" => $item["cdr_bridge_time"] == 0?0:$item["cdr_end_time"] - $item["cdr_bridge_time"],
                    "callsid" => $item["call_sid"],
                    "caller" => $item["cust_callee_clid"],
                    "called" => $item["cdr_customer_number"],
                    "admin_user" => $item["op_uname"],
                    "action" => $item["cdr_call_type"] == 1?"呼入":"外呼",
                    "TelCenter_Channel" => "clink",
                    'call_time_length_human' =>$item["cdr_bridge_time"] == 0?"-": timediff($item["cdr_end_time"] - $item["cdr_bridge_time"])
                ];
            }

            //按时间重新排序
            foreach ($result as $key => $value)
            {
                $edition[] = $value['time_add'];
            }
            array_multisort($edition, SORT_DESC, $result);
        }


        return $result;
    }

     /**
     * 获取VOIP电话列表
     * @param  [type] $orderid 订单id
     * @return [type]       [description]
     */
    private function getOtherVoipRecordList($orderid)
    {
        $db = D('Home/Logic/LogTelcenterOtherordercallLogic');
        $result = $db->getOrderCallListByOrderId($orderid);

        $result = D("LogTelcenterOrdercall")->callistHuman($result, 1); //数据再加工
        foreach ($result as $key => &$value) {
            $value['tonghuasj'] = $value['endtime'] - $value['starttime']; //通话时长
            //呼叫动作
            switch ($value['action']) {
                case 'CallAuth':
                    $value['action'] = "开始";
                    break;
                case 'CallEstablish':
                    $value['action'] = "接通";
                    break;
                case 'Hangup':
                    $value['action'] = "挂断";
                    break;
                case 'CallEstablish_Sub':
                    $value['action'] = "主叫接通";
                    break;
                case 'Hangup_Sub':
                    $value['action'] = "主叫挂断";
                    break;
                default:
                    # code...
                    break;
            }
            //拨打方式
            switch ($value['calltype']) {
                case 'callBack':
                    $value['calltype'] = "回拨拨打";
                    break;
                default:
                    break;
            }
            //挂断方式
            if ($value['TelCenter_Channel'] == 'cuct') {
                //对联通云总机的挂机代码处理
                //state：状态：0-呼叫挂机（默认值）；1-通话失败。
                switch ($value['byetype']) {
                    case '0':
                        $value['byetypewz'] = '呼叫挂机';
                        break;
                    case '1':
                        $value['byetypewz'] = '通话失败';
                        break;
                    default:
                        $value['byetypewz'] = '-';
                        break;
                }
            }else{
                // 默认是 云通讯的挂机代码
                $value['byetypewz'] = $db->getByeTypeInfo($value['byetype']);
            }
        }

        //查询合力的电话记录
        $model = new HollyLogicModel();
        $list = $model->getOrderOtherTelLogById($orderid);
        $list = D("LogTelcenterOrdercall")->callistHuman($list, 1); //数据再加工
        if (count($list) > 0) {
            $result = array_merge($result,$list);
            //按时间重新排序
            foreach ($result as $key => $value)
            {
                $edition[] = $value['time_add'];
            }
            array_multisort($edition, SORT_DESC, $result);
        }
        return $result;
    }

    /**
     * 获取VOIP电话回访列表
     * @param  [type] $orderid 订单id
     * @return [type]       [description]
     */
    private function getVoipBackRecordList($orderid)
    {
        $db = D('LogTelcenterOrdercall');
        $result = $db->getBackOrderCallListByOrderId($orderid);
        $result = $db->callistHuman($result, 1); //数据再加工
        foreach ($result as $key => &$value) {
            $value['tonghuasj'] = $value['endtime'] - $value['starttime']; //通话时长
            //呼叫动作
            switch ($value['action']) {
                case 'CallAuth':
                    $value['action'] = "开始";
                    break;
                case 'CallEstablish':
                    $value['action'] = "接通";
                    break;
                case 'Hangup':
                    $value['action'] = "挂断";
                    break;
                case 'CallEstablish_Sub':
                    $value['action'] = "主叫接通";
                    break;
                case 'Hangup_Sub':
                    $value['action'] = "主叫挂断";
                    break;
                default:
                    # code...
                    break;
            }
            //拨打方式
            switch ($value['calltype']) {
                case 'callBack':
                    $value['calltype'] = "回拨拨打";
                    break;
                default:
                    break;
            }
            //挂断方式
            if ($value['TelCenter_Channel'] == 'cuct') {
                //对联通云总机的挂机代码处理
                //state：状态：0-呼叫挂机（默认值）；1-通话失败。
                switch ($value['byetype']) {
                    case '0':
                        $value['byetypewz'] = '呼叫挂机';
                        break;
                    case '1':
                        $value['byetypewz'] = '通话失败';
                        break;
                    default:
                        $value['byetypewz'] = '-';
                        break;
                }
            }else{
                // 默认是 云通讯的挂机代码
                $value['byetypewz'] = $db->getByeTypeInfo($value['byetype']);
            }
        }

        return $result;
    }




    /**
     * 客服拨打VOIP电话
     * @param  [type] $data []
     * @return [type]       [description]
     */
    private function telephoneReviewVoipCall($data)
    {
        //判断是否是二次互访电话(走二次回访页面过来的)
        $callback = '';
        if ($data['callback'] == 1) {
            $callback = 1;
            unset($data['callback']);
        }
        //取当前登录用户 电话提供商设置
        $Doption                        = D('Options');
        $TelCenter_Channel              = $Doption->getMyTelCenterChannelByid(session("uc_userinfo.id")); //获取当前帐号的电话提供商信息
        $TelCenter_ChannelInfo          = $Doption->getTelCenterChannelInfoByChannel($TelCenter_Channel); //通过提供商标识 获取完整的信息
        //查询VOIP电话号码
        $adminUser = D("Home/Adminuser");
        $userInfo = $adminUser->findUserVoipInfo(session("uc_userinfo.id"), $TelCenter_ChannelInfo['solutions']);
        if (empty($userInfo["voipaccount"])) {
            return array("code"=>"404","errmsg"=>"请先设置VOIP号码");
        }

        //查询业主发布的电话
        $orders = D("Home/Orders");
        $orderInfo = $orders->findOrderInfoAndTel($data["id"]);

        if (empty($orderInfo["tel8"])) {
            return array("code"=>"404","errmsg"=>"该订单无业主电话,无法拨打");
        }

        $time = time();
        //3分钟内的新单无法呼叫
        $time_offset = ($time - $orderInfo["time_real"]);
        if ($time_offset < 180) {
            return array("code"=>"404","errmsg"=>"该订单发布时间在3分钟以内不能拨打");
        }

        //呼叫频率限制，如果该订单
        $voip = D("Home/Db/LogTelcenterReviewOrdercall");

        //取当前电话提供商
        $Doption        = D('Options');
        $TelCenter_Channel      = $Doption->getMyTelCenterChannelByid(session("uc_userinfo.id"));

        //提供商选择逻辑
        if ($TelCenter_Channel == 'cuct') {
            //走 联通云总机
            $from           = $userInfo['tel_work1']; //VOIP主叫号码
            $toTel          = $orderInfo['tel8']; //被叫电话号码
            $cuctResult     = D('Telcuct')->callBack($from, $toTel);
            if($cuctResult['resp']['respCode'] == '0') {
                //呼叫记录日志
                $logData['calltype']       = 'callBack';
                $logData['orderid']        = $data["id"];
                $logData['callSid']        = $cuctResult['resp']['callBack']['callId'];
                //因为联通云总机主叫号码是绑定的号码,并非voip号,所以这里给voip号码, 故意不给主叫(主叫是绑定的号码)
                $logData['fromtel']        = $userInfo['voipaccount']; //主叫
                $logData['totel']          = substr($toTel, 0,3) . '*****' . substr($toTel, -3); //被叫
                $logData['fromSerNum']     = ''; //主叫显号
                $logData['customerSerNum'] = $userInfo['tel_customer_ser_num']; //被叫显号
                $logData['time_add']       = date('Y-m-d H:i:s');
                $logData['admin_id']       = session("uc_userinfo.id");
                $logData['admin_user']     = session("uc_userinfo.user");
                $logData['appid']          = OP('CUCT_APPID_QZ');
                $logData['channel']        = $TelCenter_Channel; //电话提供商渠道
                $logData['on']             = $orderInfo['on'];
                $logData['on_sub']         = $orderInfo['on_sub'];
                $voip->addLog($logData);
                //返回ajax
                return array("code"=>"200","errmsg"=>"呼叫中,请注意接听");
            } else {
                // 记录日志
                $logArr['time']   = time();
                $logArr['log']    = json_encode($cuctResult);
                $logArr['level']  = '失败';
                $logArr['module'] = 'cuct_callback';
                D('LogMain')->addLog($logArr);
                //接口不成功 返回msg信息
                return array("code"=>"404","errmsg"=>$cuctResult['msg']);
            }
        } elseif ($TelCenter_Channel == 'ytx') {
            //走 容联云通讯

            //拨打电话
            $customerSerNum = $userInfo['tel_customer_ser_num'];//显号号码
            $fromTel = $userInfo["voipaccount"]; //VOIP主叫号码
            $toTel  = $orderInfo['tel8']; //被叫电话号码

            import('Library.Org.yuntongxun.Telytx');
            //主帐号
            $accountSid = OP('YTX_ACCOUNTSID');
            //主帐号Token
            $accountToken = OP('YTX_ACCOUNTTOKEN');
            //应用Id 客服专用
            $appId = OP('YTX_APPID');
            //请求地址
            $serverIP = OP('YTX_SERVERIP');
            //请求端口
            $serverPort = OP('YTX_SERVERPORT');
            //REST版本号
            $softVersion = OP('YTX_SOFTVERSION');
            //子帐号
            $subAccountSid = $userInfo['ytx_subaccountsid'];
            //子帐号Token
            $subAccountToken = $userInfo['ytx_subtoken'];
            //VoIP帐号
            $voIPAccount = $userInfo['voipaccount'];
            //VoIP密码
            $voIPPassword = $userInfo['voippwd'];

            $telYtx = new \Telytx($accountSid, $accountToken, $appId, $serverIP, $serverPort, $softVersion, $subAccountSid, $subAccountToken, $voIPAccount, $voIPPassword);

            $result = $telYtx->callBack($fromTel,$toTel,$customerSerNum);

            if ($result["status"] != 0) {
                //如果订单是新单，转成次新单
                if ($orderInfo["on"] == 0 && $orderInfo["on_sub"] == 10) {
                    $order = A("Home/Order");
                    $order->orderStatusChange($orderInfo["id"],0,9,0);
                }

                //添加呼叫记录日志
                //呼叫记录日志
                $logData = array();
                $logData['calltype'] = 'callBack';
                $logData['orderid'] = $data["id"];
                $logData['callSid'] = $result['callSid'];
                $logData['fromtel'] = $fromTel;
                $logData['totel'] = substr($toTel, 0,3) . '*****' . substr($toTel, -3);
                $logData['customerSerNum'] = $customerSerNum;
                $logData['time_add'] = $result['dateCreated'];
                $logData['admin_id'] = session("uc_userinfo.id");
                $logData['admin_user'] = session("uc_userinfo.user");
                $logData['appid']  = $appId;
                $logData['channel']= $TelCenter_Channel; //电话提供商渠道
                $logData['on']     = $orderInfo['on'];
                $logData['on_sub'] = $orderInfo['on_sub'];
                $voip->addLog($logData);
                return array("code"=>"200","errmsg"=>"呼叫中,请注意接听");
            }
        }

        return array("code"=>"404","errmsg"=>"呼叫失败,请稍后再试");
    }

    /***  合力忆捷 holly专用  ***/
    /**
     * 通过get call_sid 获取录音文件
     */
    public function hollyRecordFile() {
        $call_sid = I('get.call_sid');
        $PhoneCallHollyServiceModel = new PhoneCallHollyServiceModel();
        //通过call_sid 获取录音文件
        $PhoneCallHollyServiceModel->recordFile($call_sid);
    }

    public function hollysoftbar()
    {
        $holly = [];

        $user_id = session("uc_userinfo.id");
        $user_name = session("uc_userinfo.name");
        $service = new PhoneCallHollyServiceModel();
        //获取用户电话信息
        $userInfo = $service->getPhoneInfo($user_id);

        if (empty($userInfo["voipaccount"])) {
            return array("code"=>4000001,"errmsg"=>'请先设置VOIP号码');
        }

        $holly["account"] = OP("holly_account");
        $holly["pwd"] = $userInfo["voippwd"];

        //服务器地址
        if (getAppEnv() == "prod") {
            $holly["proxy"] = "https://a1pbxsh01.7x24cc.com";
            $holly["user"]  = $userInfo["voipaccount"]."@qizuang";
        } else {
            //测试环境
            $holly["proxy"] = "http://211.151.35.102";
            $holly["user"]  = $userInfo["voipaccount"]."@kfcs";
        }

        //获取帐号信息
        $this->assign("holly",$holly);
        $this->display();
    }


    // 测试电话拨打
    public function testCall() {
        $option = [];
        $option['debug']  = true;
        $Holly= new  Holly($option);
        $Holly->GenToken();
        $call_sid = $Holly->uuid(); // call_sid
        $ExternalData = "CallSid:". $call_sid; // 扩展数据 callsid, : 分割kv  , 分割组
        $callResult = $Holly->dialout('8001','17625293816','', $ExternalData);

        dump($callResult);

    }

    public function clinkRecordFile()
    {
        $call_sid = I('get.call_sid');
        $service = new PhoneCallClinkServiceModel();
        $result = $service->getMediaId($call_sid);
        if (count($result) > 0) {
            //根据call_sid获取录音地址
            $service->getClinkRecord($result["cdr_main_unique_id"],$call_sid);
            die();
        }
    }

    public function checkClinkStatus()
    {
        $user_id = session("uc_userinfo.id");

        //获取电话商设置
        //获取用户电话信息
        $service = new PhoneCallClinkServiceModel();
        $userInfo = $service->getPhoneInfo($user_id);
        if (!empty($userInfo["voipaccount"])) {
           $result =  $service->checkClinkStatus($userInfo["voipaccount"]);
           $this->assign("info",$result["agentStatus"][0]);
        }
        $this->display();
    }
}
