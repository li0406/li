<?php

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

/**
*   订单发送微信
*/
class OrderweixinController extends HomeBaseController
{
    public function _initialize()
    {
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
           
    }

    /**
     * 订单推送装修公司
     * @return [type] [description]
     */
    public function send_order_to_compnay($compnays,$orderid, $logtype = 0)
    {
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
            if (array_key_exists($value["id"],$compnays)) {
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
                        "type_fw" => $value["type_fw"] == 1?"分单":"赠单"
                    );
                }
            } else {
                $errMsg .= $value["jc"]."(未绑定微信) ";
            }

            $fdtime = $value["addtime"];
        }

        //填充模版
        $str = '【 %s 】';
        //市 区县
        $str .= $info["cname"] .' ';
        $str .= $info["qz_area"] .' ';
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
        // $url = 'https://old.qizuang.com/muser/orderinfo?id='. $info['id'];
        $url = C("YGJ_DOWNLOAD_URL");
        $topcolor    = '#F00000'; //顶部颜色
        $template['first'] = array(
                           'value' => '您好 %s，您有新订单未处理!',
                           'color' => '#F00000',
                         ); //头部信息
        $template['tradeDateTime'] = array(
                           'value' => date('Y-m-d H:i:s',$fdtime),
                           'color' => '#173177',
                            );  //提交时间 订单时间

        $template['orderType'] = array(
                           'value' => "%s",
                           'color' => '#173177',
                            ); //订单类型 订单号
        $template['customerInfo'] = array(
                           'value' => $info['name'].' '.$info['sex'],
                           'color' => '#173177',
                            ); //客户信息
        $template['orderItemName'] =  array(
                           'value' => '订单介绍',
                           'color' => '#173177',
                            ); //订单详细

        $template['orderItemData'] =  array(
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
            $data["topcolor"] = $topcolor ;
            $data["data"]["tradeDateTime"] = $template["tradeDateTime"];
            $data["data"]["orderType"] = $template["orderType"];
            $data["data"]["orderType"]["value"] = sprintf($data["data"]["orderType"]["value"],$info["id"]);
            $data["data"]["customerInfo"] = $template['customerInfo'];
            $data["data"]["orderItemName"] = $template['orderItemName'];
            $data["data"]["orderItemData"] = $template['orderItemData'];
            $data["data"]["orderItemData"]["value"] =  sprintf($data["data"]["orderItemData"]["value"],$value["type_fw"]);
            $data["data"]["remark"] = $template['remark'];
            $first = $template['first'];
            $first["value"] = sprintf($first["value"],$value["jc"]);
            $data['data']['first'] = $first;
            $result = $wechat->sendTemplateMessage($data);

            $callbackData[$value["id"]]["jc"] = $value["jc"];
            if ($result["errcode"] != 0 || $result == false) {
                // $errMsg .= $value["jc"]."(推送成功: ; 推送失败: ) ";
                $callbackData[$value["id"]]["error"]["count"] ++ ;
            } else {
                $callbackData[$value["id"]]["success"]["count"] ++ ;
            }

            $wx_openids[] = $value["openid"];
            $wx_back_result[] = $result;
            $userid[] = $value["id"];
        }

        //打日志到数据库
        $data = array(
            "orderid" => $orderid,
            "userid" => implode(array_unique($userid), ','),
            "wx_back_result" => json_encode($wx_back_result),
            "admin_id" => session("uc_userinfo.id"),
            "admin_user" => session("uc_userinfo.name"),
            "wx_openids" => json_encode($wx_openids),
            "type" => $logtype,
            "time_add" => time()
        );
        D("LogWxOrdersend")->addLog($data);

        foreach ($callbackData as $key => $value) {
            if ( count($value["error"]) > 0) {
                $errMsg .= $value["jc"]."(推送成功: ".$value["success"]["count"]."; 推送失败: ".$value["error"]["count"].") ";
            }
        }

        if (!empty($errMsg)) {
            $errMsg = "以下公司推送订单通知失败:".$errMsg;
        }

        return $errMsg;
    }

    //本函数用于开发调试用
    public function wechatsend_dev()
    {
        $APP_ENV = C('APP_ENV');
        if ($APP_ENV !='dev') {
            echo "404";
            die();
        }

        //发送微信推送
        import('Library.Org.wechat.wechat');
        $wechat = new \TPWechat($this->wxoptions);
        $data = array();
        $data["touser"] = 'oEsbQsoGSrHttP6XhfMolphx7Kzk';
        $data["template_id"] = OP('WX_QZ_FW_TEMPLATE_ID1_DEV');
        $data["data"]["orderType"]["value"] = '666';
        $data["data"]["customerInfo"]["value"]  = '大家好我是渣渣辉，是兄弟就和我一起玩';
        $data["url"] = 'http://m.qizuang.com';
        $result = $wechat->sendTemplateMessage($data);
        dump($result);
    }
}