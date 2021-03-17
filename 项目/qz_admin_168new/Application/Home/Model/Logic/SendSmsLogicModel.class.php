<?php

namespace Home\Model\Logic;

use Home\Model\Service\SmsServiceModel;

class SendSmsLogicModel
{
    /**
     * 抢单短信
     * @param $order_id
     * @param array $info
     * @return array
     */
    public function RobSendOrderSms($order_id, $info = [])
    {
        //查询订单信息
        if (empty($info)) {
            $info = D('Home/Orders')->findOrderInfo($order_id);
        }
        if ($info["on"] != 4 && !in_array($info["type_fw"], array(1, 2))) {
            return array("code" => 404, "errmsg" => "该订单不可抢");
        }
        $orderModel = D("Orders");
        //获取业主电话信息
        $tel = $orderModel->findOrderInfoAndTel($info["id"]);

        if (empty($tel["tel8"])) {
            return array('errmsg' => "业主联系电话未知，发送失败", 'code' => 404);
        }

        //获取分单装修公司
        $company = D("OrderInfo")->getOrderComapny($info["id"]);
        foreach ($company as $key => $value) {
            $ids[] = $value["id"];
        }

        //查询装修公司接单报备信息,装修公司网店填写的电话优先
        $jdbbList = D("User")->getJdbbList($ids);

        $str = "";
        foreach ($jdbbList as $key => $value) {
            if (empty($value["tel"]) && empty($value["cal"]) && empty($value["receive_order_tel1"])) {
                $this->ajaxReturn(array('errmsg' => "装修公司【 " . $value["jc"] . " (" . $value["comid"] . ") 】 未设置接单联系方式,请设置后重新发送！", 'code' => 404));
            }

            $jc = $value["jc"] ?: '装修公司'; //装修公司简称;
            $receive_order_tel1_remark = str_replace(array('总', '经理', '老板'), '', $value['receive_order_tel1_remark']);
            if (!empty($value["tel"])) {
                $receive_order_tel1 = $value["tel"];
            } elseif (!empty($value["cal"])) {
                $receive_order_tel1 = $value["cal"] . $value["cals"];
            } else {
                $receive_order_tel1 = $value["receive_order_tel1"];
            }
            $str .= sprintf(" %s;联系人:%s;电话:%s", $jc, $receive_order_tel1_remark, $receive_order_tel1);
        }

        $params[] = $str;
        $smsService = new SmsServiceModel();
        $result = $smsService->sendSms("201908211007", $tel["tel8"], $params);

        if ($result["error_code"] != 0) {
            return $result["error_msg"];
        }

        //添加后台日志记录
        $orderLogic = new OrdersLogicModel();
        $orderLogic->setOrderSmsLog($order_id, 1, $tel["tel8"]);

        return true;
    }


    /**
     * 拉黑/取消拉黑发送短信
     * @param $type 1表示拉黑  2表示取消拉黑
     * @param array $info
     * @return bool
     */
    public function userPullBlackSendSms($type, $phone, $remark = '')
    {
        $smsService = new SmsServiceModel();
        if ($type == 1) {
            $params = [$phone, $remark];
            $result = $smsService->sendSms("201911091031", $phone, $params);
        } else {
            $params = [$phone];
            $result = $smsService->sendSms("201911091032", $phone, $params);
        }
        if ($result["error_code"] != 0) {
            return false;
        } else {
            return true;
        }
    }


    /**
     * 订单推送微信给装修公司
     * @return [type] [description]
     */
    public function send_order_to_compnay($compnays,$orderid, $logtype = 0)
    {
        //添加微信验证数据
        $this->wxoptions = array(
            'token'          => OP('WX_QZ_FW_TOKEN'), //填写你设定的key
            'encodingaeskey' => OP('WX_QZ_FW_ENCODINGAESKEY'), //填写加密用的EncodingAESKey
            'appid'          => OP('WX_QZ_FW_APPID'), //填写高级调用功能的app id
            'appsecret'      => OP('WX_QZ_FW_APPSECRET') //填写高级调用功能的密钥
        );

        $APP_ENV = C('APP_ENV');
        if ($APP_ENV == 'dev') {
            //开发环境覆盖配置
            //https://mp.weixin.qq.com/debug/cgi-bin/sandboxinfo?action=showinfo&t=sandbox/index
            $this->wxoptions = array(
                'token'          => OP('WX_QZ_FW_TOKEN_DEV'), //填写你设定的key
                'encodingaeskey' => OP('WX_QZ_FW_ENCODINGAESKEY_DEV'), //填写加密用的EncodingAESKey
                'appid'          => OP('WX_QZ_FW_APPID_DEV'), //填写高级调用功能的app id
                'appsecret'      => OP('WX_QZ_FW_APPSECRET_DEV') //填写高级调用功能的密钥
            );
        }

        if (count($compnays) == 0) {
            return false;
        }

        $compnays = array_flip($compnays);
        //获取订单信息
        $order = D('Home/Orders');
        $info = $order->findOrderInfo($orderid);

        //获取分单信息
        //查询订单已分单情况
        $result = D("OrderInfo")->getOrderComapny($orderid);
        if (count($result) == 0) {
            return "未找到分单信息,请先保存后再推送微信";
        }

        foreach ($result as $key => $value) {
            if (array_key_exists($value["id"], $compnays)) {
                $list[$value["id"]] = array(
                    "company_id" => $value["id"],
                    "jc" => $value["jc"],
                    "addtime" => $value["addtime"],
                    "id" => $orderid,
                    "type_fw" => $value["type_fw"]
                );
                $ids[] = $value["id"];
            }
        }

        //获取分单公司微信绑定信息
        $result = D("User")->getCompanysWexinInfo($ids);


        $errMsg = "";
        foreach ($result as $key => $value) {
            $wechat[$value["comid"]][] = $value["wx_openid"];
        }

        foreach ($list as $key => $value) {
            if (count($wechat[$key]) != 0) {
                foreach ($wechat[$key] as $val) {
                    $user[] = array(
                        "id" => $value["company_id"],
                        "openid" => $val,
                        "jc" => $value['jc'],
                        "type_fw" => $value["type_fw"] == 1 ? "分单" : "赠单"
                    );
                }
            } else {
                $errMsg .= $value["jc"] . "(未绑定微信) ";
            }

            $fdtime = $value["addtime"];
        }

        //填充模版
        $str = '【 %s 】';
        //市 区县
        $str .= $info["cname"] . ' ';
        $str .= $info["qz_area"] . ' ';
        //小区
        $str .= $info['xiaoqu'] . ' ';

        /*
        //装修类型
        $str .= (1 == $info['lx']) ? ',家装' : ',公装';
        //面积
        $str .= $info['mianji'] . '㎡ ';
        //半包全包
        if (29 == $info['fangshi']) {
            $str .= '半包 ';
        }else if (30 == $info['fangshi']) {
            $str .= '全包 ';
        }

        //预算
        $getjg =  D("Jiage")->getJiage();
        if ($getjg) {
             foreach ($getjg as $key => $value) {
                if ($info['yusuan'] == $value['id']) {
                  $str .= $value['name'] . ',';
                }
             }
        }

        //量房时间
        $str .= '量房:'. $info['lftime'];
        */

        $template_id = OP('WX_QZ_FW_TEMPLATE_ID1'); //模版消息 模版的id
        if ($APP_ENV == 'dev') {
            $template_id = 's7AfgWFH38Mhp6PkC2UOSK9SvZWf02wld1kVwMP9zz8'; //模版消息 模版的id
        }
        // $url = 'https://old.qizuang.com/muser/orderinfo?id=' . $info['id'];
        $url = C("YGJ_DOWNLOAD_URL");
        $topcolor = '#F00000'; //顶部颜色
        $template['first'] = array(
            'value' => '您好 %s，您有新订单未处理!',
            'color' => '#F00000',
        ); //头部信息
        $template['tradeDateTime'] = array(
            'value' => date('Y-m-d H:i:s', $fdtime),
            'color' => '#173177',
        );  //提交时间 订单时间

        $template['orderType'] = array(
            'value' => "%s",
            'color' => '#173177',
        ); //订单类型 订单号
        $template['customerInfo'] = array(
            'value' => $info['name'] . ' ' . $info['sex'],
            'color' => '#173177',
        ); //客户信息
        $template['orderItemName'] = array(
            'value' => '订单介绍',
            'color' => '#173177',
        ); //订单详细

        $template['orderItemData'] = array(
            'value' => $str,
            'color' => '#173177',
        ); //订单详细
        $template['remark'] = array(
            'value' => '打开APP，查看订单详情',
            'color' => '#173177',
        ); //尾部

        //封装模版
        //发送微信推送
        import('Library.Org.wechat.wechat');
        $wechat = new \TPWechat($this->wxoptions);

        foreach ($user as $key => $value) {
            $data = array();
            $data["touser"] = $value["openid"];
            $data["template_id"] = $template_id;
            $data["url"] = $url;
            $data["topcolor"] = $topcolor;
            $data["data"]["tradeDateTime"] = $template["tradeDateTime"];
            $data["data"]["orderType"] = $template["orderType"];
            $data["data"]["orderType"]["value"] = sprintf($data["data"]["orderType"]["value"], $info["id"]);
            $data["data"]["customerInfo"] = $template['customerInfo'];
            $data["data"]["orderItemName"] = $template['orderItemName'];
            $data["data"]["orderItemData"] = $template['orderItemData'];
            $data["data"]["orderItemData"]["value"] = sprintf($data["data"]["orderItemData"]["value"], $value["type_fw"]);
            $data["data"]["remark"] = $template['remark'];
            $first = $template['first'];
            $first["value"] = sprintf($first["value"], $value["jc"]);
            $data['data']['first'] = $first;
            $result = $wechat->sendTemplateMessage($data);

            $callbackData[$value["id"]]["jc"] = $value["jc"];
            if ($result["errcode"] != 0 || $result == false) {
                // $errMsg .= $value["jc"]."(推送成功: ; 推送失败: ) ";
                $callbackData[$value["id"]]["error"]["count"]++;
            } else {
                $callbackData[$value["id"]]["success"]["count"]++;
            }

            $wx_openids[] = $value["openid"];
            $wx_back_result[] = $result;
            $userid[] = $value["id"];
        }

        //打日志到数据库
        $data = array(
            "orderid" => $orderid?:'',
            "userid" => implode(array_unique($userid), ',')?:'',
            "wx_back_result" => json_encode($wx_back_result)?:'',
            "admin_id" => session("uc_userinfo.id")?:'',
            "admin_user" => session("uc_userinfo.name")?:'',
            "wx_openids" => json_encode($wx_openids)?:'',
            "type" => $logtype,
            "time_add" => time()
        );
        D("LogWxOrdersend")->addLog($data);

        foreach ($callbackData as $key => $value) {
            if (count($value["error"]) > 0) {
                $errMsg .= $value["jc"] . "(推送成功: " . $value["success"]["count"] . "; 推送失败: " . $value["error"]["count"] . ") ";
            }
        }

        if (!empty($errMsg)) {
            $errMsg = "以下公司推送订单通知失败:" . $errMsg;
        }

        return $errMsg;
    }


    /**
     * 订单推送微信给装修公司
     * @return [type] [description]
     */
    public function send_rob_order_to_compnay($info, $logtype = 0, $comid = [])
    {
        //添加微信验证数据
        $this->wxoptions = array(
            'token'          => OP('WX_QZ_FW_TOKEN'), //填写你设定的key
            'encodingaeskey' => OP('WX_QZ_FW_ENCODINGAESKEY'), //填写加密用的EncodingAESKey
            'appid'          => OP('WX_QZ_FW_APPID'), //填写高级调用功能的app id
            'appsecret'      => OP('WX_QZ_FW_APPSECRET') //填写高级调用功能的密钥
        );

        $APP_ENV = C('APP_ENV');
        if ($APP_ENV == 'dev') {
            //开发环境覆盖配置
            //https://mp.weixin.qq.com/debug/cgi-bin/sandboxinfo?action=showinfo&t=sandbox/index
            $this->wxoptions = array(
                'token'          => OP('WX_QZ_FW_TOKEN_DEV'), //填写你设定的key
                'encodingaeskey' => OP('WX_QZ_FW_ENCODINGAESKEY_DEV'), //填写加密用的EncodingAESKey
                'appid'          => OP('WX_QZ_FW_APPID_DEV'), //填写高级调用功能的app id
                'appsecret'      => OP('WX_QZ_FW_APPSECRET_DEV') //填写高级调用功能的密钥
            );
        }

        //获取分单信息
//        //查询订单已分单情况
        // $result = D("OrderInfo")->getOrderComapny($info['id']);
        // $passCompany = [];
        // if (count($result) > 0) {
        //     $passCompany = array_column($result,'id');
        // }
        //获取订单区县, 用于查询设置了该区县的公司
        // $qx = $info['qx'];
        // $companyLogic = new CompanyLogicModel();
        // $comid = $companyLogic->getCompanyListByQx($qx);
        // $comid = array_diff($comid,$passCompany);
        //查询公司信息
        $orderCon = new RobOrdersLogicModel();
        $result = $orderCon->getOrderCompanyList([], 2, $comid, 1, $info['lx']);
        foreach ($result as $key => $value) {
            //需求变更(k.1.6.14)
//            //过滤装修面积条件
//            if(!empty($value['mianji']) && $value['mianji'] > $info['mianji']){
//                unset($result[$key]);
//                continue;
//            }
//            //过滤装修类型条件
//            if($value['lxs'] != $info['lxs'] && $value['lxs'] != 3){
//                unset($result[$key]);
//                continue;
//            }
            //过滤装修家装/公装条件
            if ($value['lx'] != $info['lx'] && $value['lx'] != 3) {
                unset($result[$key]);
                continue;
            }
            $list[$value["id"]] = array(
                "company_id" => $value["id"],
                "jc" => $value["jc"],
                "addtime" => $value["addtime"],
                "id" => $info['id'],
                "type_fw" => $value["type_fw"]
            );
            $ids[] = $value["id"];
        }

        if (count($ids) == 0) {
            return [];
        }
        //获取分单公司微信绑定信息
        $result = D("User")->getCompanysWexinInfo($ids);

        $errMsg = "";
        foreach ($result as $key => $value) {
            $wechat[$value["comid"]][] = $value["wx_openid"];
        }
        foreach ($list as $key => $value) {
            if (count($wechat[$key]) != 0) {
                foreach ($wechat[$key] as $val) {
                    $user[] = array(
                        "id" => $value["company_id"],
                        "openid" => $val,
                        "jc" => $value['jc']
                    );
                }
            } else {
                $errMsg .= $value["jc"] . "(未绑定微信) ";
            }
        }

        //填充模版
        $str = '';
        //市 区县
        $str .= $info["cname"] . ' ';
        $str .= $info["qz_area"] . ' ';
        //小区
        $str .= $info['xiaoqu'] . ' ';

        $template_id = OP('WX_QZ_FW_TEMPLATE_ID1'); //模版消息 模版的id
        if ($APP_ENV == 'dev') {
            $template_id = 'nCrrTNzToMn04Wlc9RHv_UFwKkoCpTPmNjTyhg0Gb8s'; //模版消息 模版的id
        }
        // $url = 'https://old.qizuang.com/robdetail?id=' . $info['id'];
        $url = C("YGJ_DOWNLOAD_URL");
        $topcolor = '#F00000'; //顶部颜色
        $template['first'] = array(
            'value' => '您好 %s，您有新订单可抢!',
            'color' => '#F00000',
        ); //头部信息
        $template['tradeDateTime'] = array(
            'value' => date("Y-m-d H:i:s"),
            'color' => '#173177',
        );  //提交时间 订单时间

        $template['orderType'] = array(
            'value' => "%s",
            'color' => '#173177',
        ); //订单类型 订单号
        $template['customerInfo'] = array(
            'value' => $info['name'] . ' ' . $info['sex'],
            'color' => '#173177',
        ); //客户信息
        $template['orderItemName'] = array(
            'value' => '订单介绍',
            'color' => '#173177',
        ); //订单详细

        $template['orderItemData'] = array(
            'value' => $str,
            'color' => '#173177',
        ); //订单详细
        $template['remark'] = array(
            'value' => '打开APP，查看订单详情',
            'color' => '#173177',
        ); //尾部

        //封装模版
        //发送微信推送
        import('Library.Org.wechat.wechat');
        $wechat = new \TPWechat($this->wxoptions);

        foreach ($user as $key => $value) {
            $data = array();
            $data["touser"] = $value["openid"];
            $data["template_id"] = $template_id;
            $data["url"] = $url;
            $data["topcolor"] = $topcolor;
            $data["data"]["tradeDateTime"] = $template["tradeDateTime"];
            $data["data"]["orderType"] = $template["orderType"];
            $data["data"]["orderType"]["value"] = sprintf($data["data"]["orderType"]["value"], $info["id"]);
            $data["data"]["customerInfo"] = $template['customerInfo'];
            $data["data"]["orderItemName"] = $template['orderItemName'];
            $data["data"]["orderItemData"] = $template['orderItemData'];
            $data["data"]["orderItemData"]["value"] = sprintf($data["data"]["orderItemData"]["value"], $value["type_fw"]);
            $data["data"]["remark"] = $template['remark'];
            $first = $template['first'];
            $first["value"] = sprintf($first["value"], $value["jc"]);
            $data['data']['first'] = $first;

            $postData[] = $data;
            $codes[] = $value;
        }
        $result = $wechat->sendBatchTemplateMessage($postData, $codes);
        //处理 批量发送返回的数据
        $callbackData = [];
        foreach ($result as $key => $value) {
            $callbackData[$value["id"]]["jc"] = $value["jc"];
            $result = json_decode($value['result'], true);
            if ($result["errcode"] != 0 || $result == false) {
                // $errMsg .= $value["jc"]."(推送成功: ; 推送失败: ) ";
                $callbackData[$value["id"]]["error"]["count"]++;
            } else {
                $callbackData[$value["id"]]["success"]["count"]++;
            }

            $wx_openids[] = $value["openid"];
            $wx_back_result[] = $result;
            $userid[] = $value["id"];
        }
        //打日志到数据库
        $data = array(
            "orderid" => $info['id'],
            "userid" => implode(array_unique($userid), ','),
            "wx_back_result" => json_encode($wx_back_result),
            "admin_id" => session("uc_userinfo.id"),
            "admin_user" => session("uc_userinfo.name"),
            "wx_openids" => json_encode($wx_openids),
            "type" => $logtype,
            "time_add" => time()
        );
        D("LogWxOrdersend")->addLog($data);
    }


    /**
     * 发送已分配订单通知业主-yunrongt云融正通
     *
     * @param array $datacompany
     * @param string $totel
     * @param int $orderid
     *
     * @return bool
     */
    public function send_yunrongt_sms($datacompany, $totel, $orderid)
    {
        if (empty($datacompany) || empty($totel)) {
            return false;
        }
        if (count($datacompany) > 0) {
            //1发送分配装修公司的提醒
            $smsdatav[]          = '尾号为'.substr($totel,-3);
            $smsdatav[]          = '小齐';
            $smsdata['tel']      = $totel;
            $smsdata['type']     = 'toyz_fpgs';
            $smsdata['variable'] = $smsdatav;
            $smsdata['extend']['type']    = 1; //1 通知业主分配的装修公司
            $smsdata['extend']['op_user'] = (session("uc_userinfo.user") ? :'');
            $smsdata['extend']['op_id']   = (session("uc_userinfo.id") ? :'');
            $smsdata['extend']['orderid'] = $orderid ? :'';
//            sendSmsQz($smsdata);//a.1.0.17 要求刪除
            unset($smsdatav);
            unset($smsdata);
        }
        switch (count($datacompany)) { //2再发送具体分配的装修公司信息(根据1-4家区间组建不同的短信)
            case '1':
                //1个公司
                //1条短信
                $smsdataArr          = array(); //多条短信

                $smsdatav[]          = $datacompany[0]['jc']; //公司简称
                $smsdatav[]          = $datacompany[0]['receive_order_tel1_remark']. "经理"; //联系人
                $smsdatav[]          = $datacompany[0]['receive_order_tel1']; //联系电话
                $smsdata['tel']      = $totel;
                $smsdata['type']     = 'toyz_fpgs1';
                $smsdata['variable'] = $smsdatav;
                $smsdataArr[]        = $smsdata;
                unset($smsdatav);
                unset($smsdata);

                foreach ($smsdataArr as $keysms => $smsdataone) {
                    $smsdataone['extend']['type']     = 1; //1 通知业主分配的装修公司
                    $smsdataone['extend']['op_user'] = (session("uc_userinfo.user") ? :'');
                    $smsdataone['extend']['op_id']   = (session("uc_userinfo.id") ? :'');
                    $smsdataone['extend']['orderid'] = $orderid ? :'';
                    sendSmsQz($smsdataone);
                }

                break;
            case '2':
                //2个公司
                //1条短信
                $smsdataArr          = array(); //多条短信

                $smsdatav[]          = $datacompany[0]['jc']; //公司简称
                $smsdatav[]          = $datacompany[0]['receive_order_tel1_remark']. "经理"; //联系人
                $smsdatav[]          = $datacompany[0]['receive_order_tel1']; //联系电话
                $smsdatav[]          = $datacompany[1]['jc']; //公司简称
                $smsdatav[]          = $datacompany[1]['receive_order_tel1_remark']. "经理"; //联系人
                $smsdatav[]          = $datacompany[1]['receive_order_tel1']; //联系电话
                $smsdata['tel']      = $totel;
                $smsdata['type']     = 'toyz_fpgs2';
                $smsdata['variable'] = $smsdatav;
                $smsdataArr[]        = $smsdata;
                unset($smsdatav);
                unset($smsdata);

                foreach ($smsdataArr as $keysms => $smsdataone) {
                    $smsdataone['extend']['type']     = 1; //1 通知业主分配的装修公司
                    $smsdataone['extend']['op_user'] = (session("uc_userinfo.user") ? :'');
                    $smsdataone['extend']['op_id']   = (session("uc_userinfo.id") ? :'');
                    $smsdataone['extend']['orderid'] = $orderid ? :'';
                    sendSmsQz($smsdataone);
                }

                break;
            case '3':
                //3个公司
                //2条短信
                $smsdataArr          = array(); //多条短信

                $smsdatav[]          = $datacompany[0]['jc']; //公司简称
                $smsdatav[]          = $datacompany[0]['receive_order_tel1_remark']. "经理"; //联系人
                $smsdatav[]          = $datacompany[0]['receive_order_tel1']; //联系电话
                $smsdatav[]          = $datacompany[1]['jc']; //公司简称
                $smsdatav[]          = $datacompany[1]['receive_order_tel1_remark']. "经理"; //联系人
                $smsdatav[]          = $datacompany[1]['receive_order_tel1']; //联系电话
                $smsdata['tel']      = $totel;
                $smsdata['type']     = 'toyz_fpgs2';
                $smsdata['variable'] = $smsdatav;
                $smsdataArr[]        = $smsdata;
                unset($smsdatav);
                unset($smsdata);

                $smsdatav[]          = $datacompany[2]['jc']; //公司简称
                $smsdatav[]          = $datacompany[2]['receive_order_tel1_remark']. "经理"; //联系人
                $smsdatav[]          = $datacompany[2]['receive_order_tel1']; //联系电话
                $smsdata['tel']      = $totel;
                $smsdata['type']     = 'toyz_fpgs1';
                $smsdata['variable'] = $smsdatav;
                $smsdataArr[]        = $smsdata;
                unset($smsdatav);
                unset($smsdata);

                foreach ($smsdataArr as $keysms => $smsdataone) {
                    $smsdataone['extend']['type']     = 1; //1 通知业主分配的装修公司
                    $smsdataone['extend']['op_user'] = (session("uc_userinfo.user") ? :'');
                    $smsdataone['extend']['op_id']   = (session("uc_userinfo.id") ? :'');
                    $smsdataone['extend']['orderid'] = $orderid ? :'';
                    sendSmsQz($smsdataone);
                }

                break;
            case '4':
                //4个公司
                //2条短信
                $smsdataArr          = array(); //多条短信

                $smsdatav[]          = $datacompany[0]['jc']; //公司简称
                $smsdatav[]          = $datacompany[0]['receive_order_tel1_remark']. "经理"; //联系人
                $smsdatav[]          = $datacompany[0]['receive_order_tel1']; //联系电话
                $smsdatav[]          = $datacompany[1]['jc']; //公司简称
                $smsdatav[]          = $datacompany[1]['receive_order_tel1_remark']. "经理"; //联系人
                $smsdatav[]          = $datacompany[1]['receive_order_tel1']; //联系电话
                $smsdata['tel']      = $totel;
                $smsdata['type']     = 'toyz_fpgs2';
                $smsdata['variable'] = $smsdatav;
                $smsdataArr[]        = $smsdata;
                unset($smsdatav);
                unset($smsdata);

                $smsdatav[]          = $datacompany[2]['jc']; //公司简称
                $smsdatav[]          = $datacompany[2]['receive_order_tel1_remark']. "经理"; //联系人
                $smsdatav[]          = $datacompany[2]['receive_order_tel1']; //联系电话
                $smsdatav[]          = $datacompany[3]['jc']; //公司简称
                $smsdatav[]          = $datacompany[3]['receive_order_tel1_remark']. "经理"; //联系人
                $smsdatav[]          = $datacompany[3]['receive_order_tel1']; //联系电话
                $smsdata['tel']      = $totel;
                $smsdata['type']     = 'toyz_fpgs2';
                $smsdata['variable'] = $smsdatav;
                $smsdataArr[]        = $smsdata;
                unset($smsdatav);
                unset($smsdata);

                foreach ($smsdataArr as $keysms => $smsdataone) {
                    $smsdataone['extend']['type']     = 1; //1 通知业主分配的装修公司
                    $smsdataone['extend']['op_user'] = (session("uc_userinfo.user") ? :'');
                    $smsdataone['extend']['op_id']   = (session("uc_userinfo.id") ? :'');
                    $smsdataone['extend']['orderid'] = $orderid ? :'';
                    sendSmsQz($smsdataone);
                }

                break;

            default:
                return "失败!没有公司报备的手机号码!";
                break;
        }

        return true;
    }
}
