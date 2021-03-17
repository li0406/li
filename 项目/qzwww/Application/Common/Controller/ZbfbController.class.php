<?php

namespace Common\Controller;

use Common\Model\Service\SmsServiceModel;
use Think\Controller;

class ZbfbController extends Controller
{
    private $data = null;

    public function _initialize()
    {
        //检测请求的域名是否合法
        //合法的域名数组
        $referer = trim($_SERVER['HTTP_REFERER']);
        if (preg_match('/([A-za-z])+(.[A-za-z]+)?.'.APP_HTTP_DOMAIN.'/i', $referer)) {
            $ssid = I("post.ssid");
            $action = I("post.action");
            $cs = I("post.cs");
            $des = I("post.des");
            $source = I("post.source");
            $select_desid = I("post.select_desid");
            $select_comid = I("post.select_comid");
            $display_type = I("post.display_type");
            if ($action == "load") {
                $this->assign("cityId", $this->cityInfo["id"]);
                $this->assign("des", $des);
                $this->assign("source", $source);
                $this->assign("select_desid", $select_desid);
                $this->assign("select_comid", $select_comid);
                $this->assign("display_type", $display_type);
                $this->assign("cityid", $cs);
            }
        } else {
            echo json_encode(array('data' => "", "info" => "不合法的请求,请刷新页面！", "status" => 0));
            die();
        }
    }

    /**
     * 订单发布订单
     * @return [type] [description]
     */

    public function fb_order()
    {

        $source = $_POST;

        if (empty($source['cs'])) {
            $source['cs'] = $this->cityInfo['id'];
        }

        $this->saveOrder($source);
    }

    /**
     * 带验证码的订单发布
     * @return [type] [description]
     */
    public function fb_order_safe()
    {
        $source = $_POST;
        if (!check_verify($source["code"])) {
            //重新设置安全码
            //安全验证码
            $safe = getSafeCode();
            $arr["safecode"] = $safe["safecode"];
            $arr["safekey"] = $safe["safekey"];
            $arr["ssid"] = $safe["ssid"];
            $this->ajaxReturn(array("data" => $arr, "info" => "验证码输入错误,请重新输入", "status" => 0));
            die();
        }
        $this->saveOrder($source);

        $this->ajaxReturn(array("", "info" => "成功,请注意接听电话!", "status" => 1));
        die();

    }

    /**
     * 保存订单
     * @return [type] [description]
     */
    public function saveOrder($source)
    {
        $arr = array();
        //如果登录状态下,装修公司/设计师不允许发单
        if (isset($_SESSION["u_userInfo"])) {
            if ($_SESSION["u_userInfo"]["classid"] != 1) {
                $this->ajaxReturn(array("data" => $arr, "info" => "您的帐号无法发布订单！", "status" => 0));
                die();
            }
        }

        //加一个手机号判断！！！
        $tel = remove_xss($source["tel"]);
        if (!empty($tel)) {
            $reg = "/^(13|14|15|16|17|18|19)[0-9]{9}$/";
            if (!preg_match($reg, $tel)) {
                $this->ajaxReturn(array("data" => $arr, "info" => "请填写正确的手机号码", "status" => 0));
                die();
            }
        }

        //如果有hltime，说明是黄历模块发布
        //$hlTime时间说明，1个月内是指本月和下月，3个月是指本月和之后3个月，半年内是指本月+之后半年
        if (!empty($source['hltime'])) {
            if ($source['hltime'] == '3') {
                $hlTime = strtotime('+6 month');
                $hlDate = '半年内';
            } elseif ($source['hltime'] == '2') {
                $hlTime = strtotime('+3 month');
                $hlDate = '3个月内';
            } elseif ($source['hltime'] == '1') {
                $hlTime = strtotime('+1 month');
                $hlDate = '1个月内';
            } elseif ($source['hltime'] == '4') {
                $hlTime = strtotime('+15 day');
                $hlDate = '1个月内';
            }
            $source["start"] = $hlDate;
        }

        $data = array(
            'time' => time(),
        );

        // feature-654  李斌
        if(!empty($source["source_in"])){
            $data['source_in'] = remove_xss($source["source_in"]);
        }

        //如果是微信中访问 发单的 就记下来
        if (false !== strpos($_SERVER["HTTP_USER_AGENT"], "MicroMessenger")) {
            $data['source_in'] = 10; //标记微信10
        }

        import('Library.Org.Util.App');

        $app = new \App();
        $data['ip'] = $app->get_client_ip();

        if (!empty($source["cs"])) {
            $data['cs'] = remove_xss($source["cs"]);
        }

        if (!empty($source["qx"])) {
            $data['qx'] = remove_xss($source["qx"]);
        }

        if (!empty($source["qy"])) {
            $data['qx'] = remove_xss($source["qy"]);
        }

        if (!empty($source["name"])) {
            $data['name'] = remove_xss($source["name"]);
        }

        if (!empty($source["sex"])) {
            $data['sex'] = remove_xss($source["sex"]);
        }

        if (!empty($source["tel"])) {
            $data['tel'] = remove_xss($source["tel"]);
        }

        if (!empty($source["mianji"])) {
            $data['mianji'] = remove_xss($source["mianji"]);
        }

        if (!empty($source["text"])) {
            $data['text'] = remove_xss($source["text"]);
        }

        if (!empty($source["lx"])) {
            $data['lx'] = remove_xss($source["lx"]);
        }

        if (!empty($source["lxs"])) {
            $data['lxs'] = remove_xss($source["lxs"]);
        }

        if (!empty($source["huxing"])) {
            $data['huxing'] = remove_xss($source["huxing"]);
        }

        if (!empty($source["fengge"])) {
            $data['fengge'] = remove_xss($source["fengge"]);
        }

        if (!empty($source["xiaoqu"])) {
            $data['xiaoqu'] = remove_xss($source["xiaoqu"]);
        }

        if (!empty($source["order_id"])) {
            $data['order_id'] = remove_xss($source["order_id"]);
        }

        if (!empty($source["yusuan"])) {
            $data['yusuan'] = remove_xss($source["yusuan"]);
        }

        if (!empty($source["zxdc"])) {
            $data['zxdc'] = remove_xss($source["zxdc"]);
        }

        if (!empty($source["shi"])) {
            $data['shi'] = remove_xss($source["shi"]);
        }

        if (!empty($source["ting"])) {
            $data['ting'] = remove_xss($source["ting"]);
        }

        if (!empty($source["wei"])) {
            $data['wei'] = remove_xss($source["wei"]);
        }

        if (!empty($source["chu"])) {
            $data['chu'] = remove_xss($source["chu"]);
        }

        if (!empty($source["yangtai"])) {
            $data['yangtai'] = remove_xss($source["yangtai"]);
        }

        if (!empty($source["yusuan"])) {
            $data['yusuan'] = remove_xss($source["yusuan"]);
        }

        if (!empty($source["source"])) {
            $data['source'] = remove_xss($source["source"]);
        }

        if (!empty($source["des"])) {
            $data['des'] = remove_xss($source["des"]);
        }
        //量房时间
        if (!empty($source["lftime"])) {
            $data['lftime'] = remove_xss($source["lftime"]);
        }
        //开工时间
        if (!empty($source["start"])) {
            $data['start'] = remove_xss($source["start"]);
        }

        if (!empty($source["userid"])) {
            $data['userid'] = remove_xss($source["userid"]);
        }

        if (!empty($source["referer"])) {
            //  发布页的HTTP Referer
            $data['referer'] = remove_xss($source["referer"]);
        }

        if (!empty($source["select_desid"])) {
            // 选择的设计师
            $data['select_desid'] = remove_xss($source["select_desid"]);
        }

        if (!empty($source["select_comid"])) {
            // 选择的装修公司
            $data['select_comid'] = remove_xss($source["select_comid"]);
        }

        if (!empty($source["display_type"])) {
            // 显示类型 默认0  1为开放给公司人员查看
            $data['display_type'] = remove_xss($source["display_type"]);
        }

        if (!empty($source["fb_type"])) {
            //发单类型 报价、设计、黄历等
            $data['fb_type'] = remove_xss($source["fb_type"]);
        }
        //装修方式
        if (!empty($source["fangshi"])) {
            $data['fangshi'] = remove_xss($source["fangshi"]);
        }
        //是否拿到钥匙
        if (isset($source['keys']) && in_array($source['keys'],[1,0]) ) {
            $data['keys'] = intval($source["keys"]);
        }
        $text = '';
        //何种方式联系
        if (!empty($source['contact_style'])) {
            $text .= '业主希望'.$source['contact_style'].'联系;';
        }
        //检测微信是否为当前手机
        if (!empty($source['isthisphone'])) {
            if ($source['isthisphone'] == 2) {
                $text .= '微信号为：'.$source['true_wechat'].';';
            } elseif ($source['isthisphone'] == 1) {
                $text .= '微信号为：'.$source['tel'].';';
            }
        }
        //期望沟通时间
        if (!empty($source['contact_time'])) {
            $text .= '期望沟通时间段为'.$source['contact_time'].';';
        }
        //备注是否为空
        if (!empty($text)) {
            $data['text'] = $text;
        }

        $oid = $source["orderid"];
        $info = '';
        //黑名单号码发布流程
        $isTelBlack = D('Common/Ordersblack')->checkTelBlackTrue($data['tel']);

        //号码在黑名单中 或 来源中包含 fb_order
        if ($isTelBlack || strstr(getReferer(), 'fb_order')) {
            //那么单子就发到qz_orders_black 表中去
            //后台管理功能在 订单管理>黑名单订单管理
            if (!strstr(getReferer(), 'fb_order')) {
                $ordersave = D('Common/Ordersblack')->orderpublish($data, 'insert');
            }
            $info = '发布成功！H_H';
        } else {
            //号码不在黑名单中
            //正常订单流程
            //如果带有
            if (!empty($oid)) {
                $orderid = $data["orderid"] = $oid;
                $ordersave = D('Common/Orders')->orderpublish($data, "update"); //传入去修改完善订单
            } else {
//                //单ip每天发布不得超过3条
                $chkip = D('Common/Orders')->order_chk_ip($source["ip"]);
                if ($chkip['status'] == 0) {
                    $this->ajaxReturn(array('data' => $arr, "info" => $chkip['info'], "status" => 0));
                    exit();
                }

                //判断 网络蜘蛛
                if ($app->GetRobot()) {
                    $this->ajaxReturn(array('data' => $arr, "info" => 'robot not access！', "status" => 0));
                    exit();
                }

                //简单判断电话号码 允许数字和短杠的组合  7-18位
                $chktel = D('Common/Orders')->order_chk_tel($data['tel']);
                if ($chktel['status'] == 0) {
                    $this->ajaxReturn(array('data' => $arr, "info" => $chktel['info'], "status" => 0));
                    exit();
                }

                //如果24小时内，该ip，该城市发布过订单，且订单为未审核状态
                //返回这个发布号码的单号

                //新逻辑：24小时内有发单
                //1，未审核，再次发单如果电话未变，即为更新订单(不再考虑城市问题，传入的城市直接更新)
                //2，已经审核，再次发单提示（您今日已发过订单，请明日再来。）

                $chkHistory = D('Common/Orders')->checkFenHistory($data['tel']);//查询该手机号是否在24小时内发布过订单
                $historyCount = count($chkHistory);// 1表示发过单 0表示未成功发过单
                if ($historyCount != 0) {
                    if ($chkHistory[0]['on'] == 0 && $chkHistory[0]['on_sub'] == 10) {
                        //如果本手机24小时内发布过单子但单子未审核, 完善订单
                        $orderid = $data['orderid'] = $chkHistory[0]['id']; //历史订单id号
                        unset($data['source']);
                        $ordersave = D('Common/Orders')->orderpublish($data, "update"); //传入去修改完善订单
                    } else {
                        //24小时内已有审核订单
                        $this->ajaxReturn(array("data" => '', "info" => "亲，您今日获取报价次数已超限。", "status" => 0));
                    }
                } else {
                    //单子入库 新增插入
                    $orderid = D('Common/Orders')->orderpublish($data, "insert");  //传入插入新订单

                    if ($orderid !== false) {
                        $ordersave = true;
                        //新单子 发送通知业主短信
                        //发送订单申请成功 短信
                        $sms_tel = $data['tel'];

                        // 发布订单，发送业主短信开关控制
                        if (OP('SMS_FB_TO_YEZHU') == '1') {
                            //如果是11位号码
                            if (11 == strlen($sms_tel)) {
                                if ($historyCount == 0) {
                                    //发送短信
                                    $smsService = new SmsServiceModel();
                                    switch ($data['fb_type']) {
                                        case 'baojia':
                                             $smsService->sendSms("201908201003",$sms_tel);
                                             break;
                                        case 'sheji':
                                             $smsService->sendSms("201908201004",$sms_tel);
                                             break;
                                        case 'huangli':
                                             $smsService->sendSms("201908201005",$sms_tel);
                                            break;
                                        default:
                                             $smsService->sendSms("201908211006",$sms_tel);
                                            break;
                                    }
                                }
                            }
                        }

                        //新发单，做一个cookie来判断是不是在发单完成时是否发起请求
                        //新单入库，cookie('vi')获取访客唯一标识，url=$data['referer'] ,发单时间 time()
                        $yy_ref = parse_url($_SERVER['HTTP_REFERER']);
                        $yy_url = $yy_ref["host"].$yy_ref["path"];

                        $yy_url = preg_replace("/(http:\/\/)/", '', $yy_url);//去掉http://
                        $yy_url = preg_replace("/(https:\/\/)/", '', $yy_url);//去掉https://
                        //过滤掉多余的反斜杠
                        $reg = '/\/+/';
                        $yy_url = preg_replace($reg, "/", $yy_url);
                        //过滤最后一个反斜杠
                        $reg = '/\/$/';
                        $yy_url = preg_replace($reg, "", $yy_url);

                        $yy_data['ref'] = $yy_url;
                        //判断是否以.html结尾，否则+'/'
                        $reg = '/.html$/';
                        $res = preg_match($reg, $yy_url);
                        if (!$res) {
                            $yy_url = $yy_url . '/';
                        }
                        $url2 = explode('.', $yy_url);
                        if ($url2[0] == 'servicewechat') {
                            $yy_data['vi'] = 'servicewechat';
                        } else {
                            $yy_data['vi'] = cookie('vi');
                            if (empty($yy_data['vi'])) {
                                $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
                                for ($i = 0; $i < 16; $i++) {
                                    $yy_data['vi'] .= $chars[mt_rand(0, strlen($chars) - 1)];
                                }
                                cookie('vi', $yy_data['vi'], array('expire' => 60, "path" => "/", 'domain' => '.' . C("QZ_YUMING")));
                            }
                        }
                        $yy_data['src'] = cookie('QZSRC');
                        if ($yy_data['src'] == '' || $yy_data['src'] == 'none') {
                            $yy_data['src'] = '';
                        }
                        $yy_data['oid'] = $orderid;
                        $yy_data['time'] = time();
                        //查询url在yy_site_url的ID
                        $where['url'] = $yy_url;
                        $find = M('yy_site_url')->where($where)->find();
                        $yy_data['urlid'] = 0;
                        if (!empty($find)) {
                            $yy_data['urlid'] = $find['id'];
                        }
                        M('yy_order_info')->add($yy_data);
                    }
                }
            }
        }

        //如果操作成功 返回true
        if ($ordersave) {
            //设置订单的cookie,有效期15分钟
            $orderid = $orderid;
            $time = time();
            setcookie("w_qizuang_n", $orderid, $time + 300, '/', '.' . APP_HTTP_DOMAIN);
            switch ($source["step"]) {
                case '1':
                    //获取发单成功页面第一步
                    $t = T("Common@Zbfb/setp1");
                    $fetch = $this->fetch($t);
                    $arr["tmp"] = $fetch;
                    break;
                case '2':
                    //获取发单成功页面第二步
                    //获取预算列表
                    // $mianji = array("60㎡以下","60-80㎡","80-100㎡","100-120㎡","120㎡以上");
                    // $step["mianji"] = $mianji;
                    $step["order"] = $data;
                    //获取风格列表
                    $fengge = D("Fengge")->getfg();
                    $step["fengge"] = $fengge;
                    //获取类型列表
                    $hx = D("Huxing")->gethx(false);
                    $hx = array_slice($hx, 0, 11);
                    $step["hx"] = $hx;
                    //获取预算
                    $yusuan = D("Jiage")->getJiage();
                    $step["yusuan"] = $yusuan;
                    //获取方式列表
                    $fs = D("Fangshi")->getfs();
                    $step["fs"] = $fs;

                    $step["orderid"] = $orderid;
                    $step["tel"] = $data["tel"];
                    $step["cs"] = $data["cs"];
                    $step["qx"] = $data["qx"];

                    $this->assign("step", $step);

                    $tpl = remove_xss($source["tpl"]);
                    if (!empty($tpl)) {
                        //获取报价结果直接返回
                        if($tpl == 'zxbjnew') {
                            $calculatePrice = R('Home/Zxbj/calculatePrice', array($data['mianji'], $data['cs']));
                            $calculatePrice ? $this->ajaxReturn(array("data" => $calculatePrice, 'info' => '报价成功', "status" => 1)) : $this->ajaxReturn(array('info' => '报价失败', "status" => 0));
                        }
                        $t = T("Common@Zbfb/" . $tpl);
                    } else {
                        $t = T("Common@Zbfb/bigStep2");
                    }
                    // $step["ssid"] = $safe["ssid"];
                    $this->assign("step", $step);
                    $fetch = $this->fetch($t);
                    $arr["tmp"] = $fetch;
                    break;
                case '3':
                    $tpl = remove_xss($source["tpl"]);
                    if (!empty($tpl)) {
                        //获取方式列表
                        $fs = D("Fangshi")->getfs();
                        $step["fs"] = $fs;
                        $step["tel"] = $data["tel"];
                        $step["orderid"] = $orderid;
                        $step["cs"] = $data["cs"];
                        $step["qx"] = $data["qx"];
                        $this->assign("step", $step);
                        $t = T("Common@Zbfb/" . $tpl);
                    } else {
                        //产品需求变动，不会触发此处bug ,修改此bug前端需调整样式，后端代码如下暂时注释该代码，以后休bug会用到
                        //todo 如果cookie 有来源取cookie来源 没有默认 注意次功能不适用接口，移植代码需注意
//                        $ma_src = cookie('QZSRC');
//                        if(!empty($ma_src)){
//                            $source = D("OrderSource")->getOne($ma_src);
//                            $weixinResult = D("YySrcWeixin")->getOneBySourceid($source['id']);
//                        }
//
//                        if(empty($weixinResult)){
//                            $weixinResult = D("YySrcWeixin")->getDefault();
//                        }
//                        $this->assign("img", $weixinResult['img']);
                        $t = T("Common@Zbfb/step3");
                    }

                    // $step["ssid"] = $safe["ssid"];
                    $fetch = $this->fetch($t);
                    $arr["tmp"] = $fetch;
                    break;
                case 'freetel':
                    //免费电话咨询
                    return self::freetelcall($source, $arr, $orderid);
                    break;
                case '99':
                    //获取发单成功页面 - 黄历
                    // $step["ssid"]   = $safe["ssid"];
                    $step['hlDate'] = $hlDate;
                    $step['hlTime'] = date('Y-m-d', $hlTime);
                    $step['hlType'] = $source['hltime'];

                    if ($source['module'] == 'moblie') {
                        $t = T("Common@Zbfb/huangliMobile");
                        $step['domain'] = C('MOBILE_DONAMES');
                    } else {
                        $t = T("Common@Zbfb/huangli");
                        $step['domain'] = C('QZ_YUMINGWWW');
                    }


                    //选近期吉日
                    $startTimeStr = $step['hlTime'];

                    $startTime = $hlTime;
                    $endTime = strtotime("$startTimeStr +1 month");

                    $startYear = date('Y', $startTime);
                    $endYear = date('Y', $endTime);
                    $map["y"] = array("between", "$startYear,$endYear");

                    $startMonty = date('m', $startTime);
                    $endMonty = date('m', $endTime);
                    $map['m'] = array('IN', "$startMonty,$endMonty");

                    $map['yi'] = array('like', '%修造%');
                    //如果是 改版的装修黄历 则直接返回数据
                    if ($source['huangli_new']) {
                        $jiriList = M('huangli')->field('*')->where($map)->limit(3)->select();
                        //98分
                        $score_time = strtotime("+3 month");
                        foreach ($jiriList as $k => $v) {
                            $score = 98;
                            if (strtotime($v['y'] . '-' . $v['m'] . '-' . $v['d']) > $score_time) {
                                $score = mt_rand(90, 95);
                                //判断当随机获取评分时,把第一条评分默认为95分
                                if ($k == 0) {
                                    $score = 95;
                                }
                            }
                            $jiriList[$k]['score'] = $score;
                            $jiriList[$k]['desc'] = mb_substr($v['yi'], 0, 14, 'utf-8') . ' ...';
                        }
                        $this->ajaxReturn(array("info" => $jiriList, "status" => 1));
                    } else {
                        $jiriList = M('huangli')->field('*')->where($map)->select();
                    }
                    $start = date('Ynj', $startTime);

                    //修改装修记日取出逻辑
                    foreach ($jiriList as $key => $v) {
                        if (count($newList) > 1) {
                            break;
                        }
                        $thisTime = $v['y'] . $v['m'] . $v['d'];
                        if ($thisTime >= $start) {
                            $yi = explode('、', $v['yi']);
                            $tempYi = '';
                            for ($i = 0; $i <= 4; $i++) {
                                $tempYi[] = $yi[$i];
                            }
                            $v['yi'] = implode($tempYi, '、');
                            $newList[] = $v;
                        }
                    }

                    $this->assign('jiriList', $newList);

                    $this->assign("step", $step);
                    $fetch = $this->fetch($t);
                    $arr["tmp"] = $fetch;
                    break;
                case 'activity-11yue':
                    $arr = ['order_id' => $orderid];
                    break;
            }

            $this->ajaxReturn(array("data" => $arr, "info" => $newList, "status" => 1));
        } else {
            $this->ajaxReturn(array("data" => $arr, "info" => "发布失败！", "status" => 0));
        }
    }

    //免费电话咨询
    public function freetel()
    {
        //先验证验证码
        $source = $_POST;
        $tel = I('post.tel');
        $source['step'] = 'freetel'; //定义saveOrder后的操作
        if (!check_verify($source["code"])) {
            //重新设置安全码
            //安全验证码
            $safe = getSafeCode();
            $arr["safecode"] = $safe["safecode"];
            $arr["safekey"] = $safe["safekey"];
            $arr["ssid"] = $safe["ssid"];
            $this->ajaxReturn(array("data" => $arr, "info" => "验证码输入错误,请重新输入", "status" => 0));
            die();
        }
        //再保存订单 ,因为 设置了 step 为freetel 会走到这个逻辑进行ajax返回的 直接调用 saveOrder即可
        self::saveOrder($source);

    }


    /**
     * 设置获取报价弹层显示
     * @return [type] [description]
     */
    public function setwindowswitch()
    {
        //设置cookie,时间24小时
        setcookie("w_index", 1, time() + (3600 * 24), '/', '.' . APP_HTTP_DOMAIN);
        $this->ajaxReturn(array("aaa"));
    }

    /**
     * 刷新验证码
     * @return [type] [description]
     */
    public function refresh()
    {
        $safe = getSafeCode();
        $this->ajaxReturn(array("data" => $safe, "info" => "", "status" => 1));
    }


    public function dispatcher()
    {
        $type = $_POST["type"];
        $cid = $_POST["cid"];
        //如果公司编号cid不为空,则添加公司的咨询量
        if (!empty($cid)) {
            D("Common/User")->editUvAndPv($cid, "uv");
        }

        if (!empty($type)) {
            switch ($type) {
                case 'step1':
                    $tmp = $this->step1();
                    break;
                case 'sj':
                    $tmp = $this->zbsj();
                    break;
                case 'sjnew':
                    $tmp = $this->zbsjnew();
                    break;
                case 'bj':
                    $tmp = $this->zxbj();
                    break;
                case 'zx':
                    $tmp = $this->zbzx();
                    break;
                case 'ys':
                    $tmp = $this->zbys();
                    break;
                case 'lf':
                    $tmp = $this->zblf();
                    break;
                case 'fb':
                    $tmp = $this->zbfb();
                    break;
                case 'zixunfb':
                    $tmp = $this->zixunfb();
                    break;
                case 'eventbj':
                    $tmp = $this->eventbj();
                    break;
                case 'sms':
                    if (!empty($this->data)) {
                        $data = $this->data;
                    } elseif ($_POST) {
                        $data = $_POST;
                    }
                    $tmp = $this->sms($data["savedata"]);
                    break;
                case 'zxfb':
                    if (!empty($this->data)) {
                        $data = $this->data;
                    } elseif ($_POST) {
                        $data = $_POST;
                    }
                    $tmp = $this->zxfb($data["savedata"]);
                    break;
                case 'hl':
                    //装修黄历弹窗
                    $tmp = $this->zxhl();
                    break;
            }
            if (!empty($tmp)) {
                $this->ajaxReturn(array("data" => $tmp, "info" => "abc", "status" => 1));
            }
        }
        $this->ajaxReturn(array("data" => "", "info" => "非法提交!", "status" => 0));
    }

    public function guide()
    {
        $vars["yusuan"] = D("Jiage")->getJiage();
        $this->assign('vars', $vars);
        $t = T("Common@Zbfb/guide");
        $tmp = $this->fetch($t);
        $this->ajaxReturn(array("data" => $tmp, "status" => 1));
    }

    /**
     * 订单结果反馈信息
     * @return [type] [description]
     */
    public function feedback()
    {
        if ($_POST) {
            $orderid = I("post.orderid");
            $data = array(
                "feedback" => I("post.feedback"),
                "orderid" => $orderid,
                "time" => time()
            );
            D("Orderfeedback")->setOrderFeed($data);
            $this->ajaxReturn(array("data" => "", "info" => "", "status" => 1));

        }
    }

    /**
     * 获取订单报价
     * @return [type] [description]
     */
    public function getZbPrice()
    {
        if (!empty($_COOKIE["w_qizuang_n"])) {
            $orderid = $_COOKIE["w_qizuang_n"];
            //查询订单信息
            $order = D("Orders")->getOrderInfoById($orderid);
            unset($order['tel8']);
            if (count($order) > 0) {
                //计算订单报价
                $mianji = $order["mianji"];
                $zxdc = $order["zxdc"];
                //根据面积计算房屋类型
                $house = array(
                    "shi" => 1,
                    "ting" => 1,
                    "wei" => 1,
                    "yangtai" => 1,
                    "chu" => 1,
                    "huxing" => "38"
                );
                if ($mianji >= 60 && $mianji < 90) {
                    $house["shi"] = 2;
                    $house["huxing"] = "39";
                } elseif ($mianji >= 90 && $mianji < 100) {
                    $house["shi"] = 3;
                    $house["huxing"] = "46";
                } elseif ($mianji >= 100 && $mianji < 120) {
                    $house["shi"] = 3;
                    $house["ting"] = 2;
                    $house["huxing"] = "47";
                } elseif ($mianji >= 120 && $mianji < 130) {
                    $house["shi"] = 3;
                    $house["ting"] = 2;
                    $house["wei"] = 2;
                    $house["huxing"] = "43";
                } elseif ($mianji >= 130 && $mianji < 150) {
                    $house["shi"] = 4;
                    $house["ting"] = 2;
                    $house["wei"] = 2;
                    $house["yangtai"] = 2;
                    $house["huxing"] = "48";
                } elseif ($mianji >= 150 && $mianji < 200) {
                    $house["shi"] = 5;
                    $house["ting"] = 2;
                    $house["wei"] = 2;
                    $house["yangtai"] = 3;
                    $house["huxing"] = "49";
                } else {
                    $house["shi"] = 6;
                    $house["ting"] = 2;
                    $house["wei"] = 3;
                    $house["yangtai"] = 3;
                    $house["huxing"] = "50";
                }
                //更新订单的室厅卫厨阳台
                $result = D("Orders")->editOrder($orderid, $house);
                if ($result !== false) {
                    unset($house["huxing"]);
                    //获取价格
                    $result = $this->getZxInfo($house);
                    $data = R("Home/Zxbj/getPricesTmp", array($mianji, $zxdc, $result, $orderid, $order["cs"]));
                    $this->ajaxReturn(array("data" => $data, "info" => "", "status" => 1));
                }
            }
        }
        $this->ajaxReturn(array("data" => "", "info" => "获取订单报价失败,请刷新后再试！", "status" => 0));
    }

    //获取订单报价
    public function getBJResult()
    {
        if (isset($_COOKIE["w_qizuang_n"])) {

            $mini = R("Home/Zxbj/getDetailsArray");
            $data = '<dl>
                        <dt>您的装修预算约为<em>' . $mini['total'] . '</em>万元<a href="/details">预算明细</a></dt>
                        <dd>客厅预算：' . $mini['kt'] . '万元</dd>
                        <dd>卧室预算：' . $mini['zw'] . '万元</dd>
                        <dd>厨房预算：' . $mini['cf'] . '万元</dd>
                        <dd>卫生间预算：' . $mini['wsj'] . '万元</dd>
                        <dd>水电预算：' . $mini['sd'] . '万元</dd>
                        <dd>其他预算：' . $mini['other'] . '万元</dd>
                    </dl>';
            // $data = '<dt>您的装修预算约为<em>'.$mini['total'].'</em>万元<a href="/details" target="_blank">预算明细</a></dt><dd>客厅预算：'.$mini['kt'].'万元</dd><dd>卧室预算：'.$mini['zw']+$mini['cw'].'万元</dd><dd>厨房预算：'.$mini['cf'].'万元</dd><dd>卫生间预算：'.$mini['wsj'].'万元</dd><dd>水电预算：'.$mini['sd'].'万元</dd><dd>其他预算：'.$mini['other'].'万元</dd>';
            $this->ajaxReturn(array("data" => $data, "info" => "", "status" => 1));
        }
        $this->ajaxReturn(array("data" => "", "info" => "获取订单报价失败,请刷新后再试！", "status" => 0));
    }

    /**
     * 获取装修报价
     * @param  [type] $house [房屋信息]
     * @param  [type] $zxdc  [装修档次]
     * @return [type]        [description]
     */
    private function getZxInfo($house)
    {
        //计算出房屋具体的装修位置
        $data = array();
        //计算出所有的房间
        for ($i = 1; $i <= $house["shi"]; $i++) {
            if ($i == 1) {
                array_push($data, "zw");
            } elseif ($i == 2) {
                array_push($data, "cw");
            } elseif ($i == 3) {
                array_push($data, "sf");
            } elseif ($i == 4) {
                array_push($data, "kw");
            } elseif ($i == 5) {
                array_push($data, "etws");
            } elseif ($i == 6) {
                array_push($data, "zwf");
            }
        }
        //计算出所有的厅
        for ($i = 1; $i <= $house["ting"]; $i++) {
            if ($i == 1) {
                array_push($data, "kt");
            } elseif ($i == 2) {
                array_push($data, "ct");
            }
        }

        //计算卫生间
        for ($i = 1; $i <= $house["wei"]; $i++) {
            if ($i == 1) {
                array_push($data, "wsj");
            } elseif ($i == 2) {
                array_push($data, "zwwsj");
            } else {
                array_push($data, "kwwsj");
            }
        }

        //计算厨房
        if ($house["chu"] > 0) {
            array_push($data, "cf");
        }
        //计算阳台
        for ($i = 1; $i <= $house["yangtai"]; $i++) {
            switch ($i) {
                case '2':
                    array_push($data, "cyt");
                    break;
                default:
                    array_push($data, "yt");
                    break;
            }
        }
        return $data;
    }

    //建材计算
    private function getMaterialsPrice($width, $length, $fangshi, $price)
    {
        // 计算方式  1.长*宽  2. （长+宽）*2  3.(长+宽)*2*2.8  4房屋面积*1
        // 5.1厨房+1卫生间 6. 等于5 7. 等于 4  8. 等于3  9 等于 6  默认 0  表示 1
        //获取计算方式
        $result = array();
        $total = 0;
        $count = 0;
        switch ($fangshi) {
            case 0:
                $count = 1;
                $total = sprintf('%.2f', 1 * $price);
                break;
            case 1:
                $count = sprintf('%.2f', ($width * $length));
                $total = sprintf('%.2f', ($width * $length) * $price);
                break;
            case 2:
                $count = sprintf('%.2f', ($width + $length) * 2);
                $total = sprintf('%.2f', ($width + $length) * 2 * $price);
                break;
            case 3:
                $count = sprintf('%.2f', ($width + $length) * 2 * 2.8);
                $total = sprintf('%.2f', ($width + $length) * 2 * 2.8 * $price);
                break;
            case 4:
                $count = $mianji;
                $total = sprintf('%.2f', $mianji * $price);
                break;
            case 5:
                $count = "按实际计算";
                $total = 0;
                break;
            case 6:
                $count = 5;
                $total = sprintf('%.2f', 5 * $price);
                break;
            case 7:
                $count = 4;
                $total = sprintf('%.2f', 4 * $price);
                break;
            case 8:
                $count = 3;
                $total = sprintf('%.2f', 3 * $price);
                break;
            case 9:
                $count = 6;
                $total = sprintf('%.2f', 6 * $price);
                break;
        }
        $result["count"] = $count;
        $result["total"] = $total;
        return $result;
    }

    //获取详细的价格明细
    private function getDetailsPrice($nowprice, $id, $width, $length, $fangshi = 0, $mianji = 0)
    {
        // 计算方式  1.长*宽  2. （长+宽）*2  3.(长+宽)*2*2.8  4房屋面积*1
        // 5.1厨房+1卫生间 6. 等于5 7. 等于 4  8. 等于3  9 等于 6
        // 10. 等于 1+1  默认 0  表示 1
        $result = array();
        foreach ($nowprice as $price) {
            if ($price["xm"] == $id) {
                $result["price"] = $price["price"];
                //获取长和宽
                $width = empty($width) == true ? $price["width"] : $width;
                $length = empty($length) == true ? $price["length"] : $length;
                //获取计算方式
                $total = 0;
                $count = 0;
                switch ($fangshi) {
                    case 0:
                        $count = 1;
                        $total = sprintf('%.2f', 1 * $price["price"]);
                        break;
                    case 1:
                        $count = sprintf('%.2f', ($width * $length));
                        $total = sprintf('%.2f', ($width * $length) * $price["price"]);
                        break;
                    case 2:
                        $count = sprintf('%.2f', ($width + $length) * 2);
                        $total = sprintf('%.2f', ($width + $length) * 2 * $price["price"]);
                        break;
                    case 3:
                        $count = sprintf('%.2f', ($width + $length) * 2 * 2.8);
                        $total = sprintf('%.2f', ($width + $length) * 2 * 2.8 * $price["price"]);
                        break;
                    case 4:
                        $count = $mianji;
                        $total = sprintf('%.2f', $mianji * $price["price"]);
                        break;
                    case 5:
                        $count = "按实际计算";
                        $total = 0;
                        break;
                    case 6:
                        $count = 5;
                        $total = sprintf('%.2f', 5 * $price["price"]);
                        break;
                    case 7:
                        $count = 4;
                        $total = sprintf('%.2f', 4 * $price["price"]);
                        break;
                    case 8:
                        $count = 3;
                        $total = sprintf('%.2f', 3 * $price["price"]);
                        break;
                    case 9:
                        $count = 6;
                        $total = sprintf('%.2f', 6 * $price["price"]);
                        break;
                    case 10:
                        $count = "1+1";
                        $total = sprintf('%.2f', 2 * $price["price"]);
                        break;
                }
                $result["width"] = $width;
                $result["length"] = $length;
                $result["count"] = $count;
                $result["total"] = $total;
            }
        }
        return $result;
    }

    private function sms($data)
    {
        $isSave = 0;
        if (!empty($data)) {
            $isSave = 1;
            $this->assign("source", json_encode($data));
        }
        $this->assign("isSave", 1);
        $t = T("sms");
        $tmp = $this->fetch($t);
        return $tmp;
    }

    /**
     * 获取发布的订单的弹窗
     * @return [type] [description]
     */
    private function zxfb($data)
    {
        if (!empty($data)) {
            $this->assign("source", json_encode($data));
        }
        $t = T("Common@Zbfb/zxfb");
        $tmp = $this->fetch($t);
        return $tmp;
    }

    //获取老客户活动弹窗
    private function eventbj($data)
    {
        if (!empty($data)) {
            $this->assign("source", json_encode($data));
        }
        $t = T("Common@Zbfb/eventbj");
        $tmp = $this->fetch($t);
        return $tmp;
    }

    /**
     * 招标发布
     * @return [type] [description]
     */
    private function zbfb()
    {
        $t = T("Common@Zbfb/zbfb");
        $tmp = $this->fetch($t);
        return $tmp;
    }

    /**
     * 优惠资讯发布
     * @return [type] [description]
     */
    private function zixunfb()
    {
        $t = T("Common@Zbfb/zixunfb");
        $tmp = $this->fetch($t);
        return $tmp;
    }

    private function step1()
    {
        $this->assign("source", I('post.source'));
        $this->assign("select_desid", I('post.select_desid'));
        $this->assign("select_comid", I('post.select_comid'));
        $this->assign("des", I('post.select_desid'));
        $t = T("Common@Zbfb/newstep");
        $tmp = $this->fetch($t);
        return $tmp;
    }

    /**
     * 招标设计
     * @return [type] [description]
     */
    /*    private function zbsj(){
            $zb_box_object=array(
                                 "show_box_div"         =>"zb_box_sj",
                                 "show_box_banner_div"  =>"zb_box_hd_sj",
                                 "zb_box_info"          =>'最合理的采光色调及空间布局、最理想的风水规划,专业人办专业事儿！',//招标弹窗头部提示
                                 "zb_box_tip"           =>'4套设计全面PK,让你的装修绝不后悔!',//招标弹窗温馨提示
                                 "zb_box_btn"           =>'获取4套设计方案',//招标弹窗button文字
                                 );
            $this->assign("zb_box_object",$zb_box_object);//赋值招标弹窗对象
            $t = T("Common@Zbfb/zbtmp");
            $tmp = $this->fetch($t);
            return $tmp;
        }*/

    /**
     * 招标设计
     * @return [type] [description]
     */
    private function zbsj()
    {
        $rand_num = $this -> randNum();
        $this -> assign('rand_num', $rand_num);
        $t = T("Common@Zbfb/freesheji");
        $tmp = $this->fetch($t);
        return $tmp;
    }
    //获取每小时减五个值的值
    /**
     * @return float|int
     */
    private  function randNum()
    {
        $hour = date('H', time());
        $count = 166;
        $remaining = $count - $hour * 5;
        return $remaining;
    }
    /**
     * 装修黄历-弹窗
     * @return [type] [description]
     */
    private function zxhl()
    {
        $t = T("Common@Zbfb/zxhl");
        $tmp = $this->fetch($t);
        return $tmp;
    }

    private function zbsjnew()
    {
        $t = T("Common@Zbfb/sjnewbox");
        $tmp = $this->fetch($t);
        return $tmp;
    }

    /**
     * 招标量房
     * @return [type] [description]
     */
    /*    private function zblf(){
            $zb_box_object=array(
                                 "show_box_div"         =>"zb_box_lf",
                                 "show_box_banner_div"  =>"zb_box_hd_lf",
                                 "zb_box_info"          =>'拒绝开发商用各项理由"大事化小",不放过自己的每一项权利!',//招标弹窗头部提示
                                 "zb_box_tip"           =>'漏水、裂缝、气泡等质量问题一网打尽!',//招标弹窗温馨提示
                                 "zb_box_btn"           =>'一键免费申请',//招标弹窗button文字
                                 );
            $this->assign("zb_box_object",$zb_box_object);//赋值招标弹窗对象
            $t = T("Common@Zbfb/zbtmp");
            $tmp = $this->fetch($t);
            return $tmp;
        }*/

    private function zblf()
    {
        $t = T("Common@Zbfb/lfbox");
        $tmp = $this->fetch($t);
        return $tmp;
    }

    /**
     * 招标预算
     * @return [type] [description]
     */
    private function zbys()
    {
        $zb_box_object = array(
            "show_box_div" => "zb_box_ys",
            "show_box_banner_div" => "zb_box_hd_ys",
            "zb_box_info" => '主材辅料费用,运输及人工成本等一目了然,您千万不要当"冤大头"!',//招标弹窗头部提示
            "zb_box_tip" => '全照国家标准,0漏项,0增项,远离被"蒙"!',//招标弹窗温馨提示
            "zb_box_btn" => '获取详细预算清单',//招标弹窗button文字
        );
        $this->assign("zb_box_object", $zb_box_object);//赋值招标弹窗对象
        $t = T("Common@Zbfb/zbtmp");
        $tmp = $this->fetch($t);
        return $tmp;
    }

    /**
     * 招标咨询
     * @return [type] [description]
     */
    private function zbzx()
    {
        $zb_box_object = array(
            "show_box_div" => "zb_box_zx",
            "show_box_banner_div" => "zb_box_hd_bj",
            "zb_box_info" => '详细预算清单,4份不同报价挤干水份,花1分钟可省万元！',//招标弹窗头部提示
            "zb_box_tip" => '环保材料,低报价,土豪也能省！',//招标弹窗温馨提示
            "zb_box_btn" => '马上获取预算报价',//招标弹窗button文字
        );
        $this->assign("zb_box_object", $zb_box_object);//赋值招标弹窗对象
        $t = T("Common@Zbfb/zbtmp");
        $tmp = $this->fetch($t);
        return $tmp;
    }

    /**
     * 装修报价
     * @return [type] [description]
     */
    private function zxbj()
    {
        //获取户型列表
        $hx = D("Common/Huxing")->gethx();
        //获取装修风格列表
        $fg = D("Common/Fengge")->getfg();
        $this->assign("hx", $hx);
        $this->assign("fengge", $fg);
        $t = T("Common@Zbfb/zxbjnew");
        $tmp = $this->fetch($t);
        return $tmp;
    }

    /**
     * 免费电话咨询
     * @param    $source  提交的表单
     * @param    $arr     验证相关
     * @param    $orderid 订单id
     * @return   ajax json
     */
    private function freetelcall($source, $arr, $orderid)
    {
        //打电话
        $tel = $source['tel'];
        if (empty($tel)) {
            $this->ajaxReturn('', '号码不能为空!', 0);
        }

        //取主叫号码
        $fromtel = OP('freetelzhujiao');

        //双向回拨
        //callBack("主叫电话号码","被叫电话号码","被叫侧显示的客服号码","主叫侧显示的号码","自定义回拨提示音");
        $from = $fromtel; //主叫电话号码
        $to = $tel; //被叫电话号码
        $customerSerNum = $fromtel;  //被叫侧显示的客服号码
        $fromSerNum = '';  //主叫侧显示的号码
        $promptTone;
        import('Library.Org.yuntongxun.Telytx');
        $Telytx = new \Telytx();
        $callBack = $Telytx->callBack($from, $to, $customerSerNum, $fromSerNum, $promptTone);
        if (1 == $callBack['status']) {
            $data['calltype'] = 'callBack';
            $data['orderid'] = $orderid;
            $data['callSid'] = $callBack['callSid'];
            $data['fromtel'] = $from;
            $data['totel'] = substr($to, 0, 3) . '*****' . substr($to, -3);
            $data['fromSerNum'] = $fromSerNum;
            $data['customerSerNum'] = $customerSerNum;
            $data['time_add'] = $callBack['dateCreated'];
            $data['admin_id'];
            $data['admin_user'];
            M('log_telcenter_ordercall')->add($data);
            $remsg = '成功,稍后请您接听电话!';
        } else {
            $remsg = $callBack['info'] . $callBack['msg'];
        }

        $this->ajaxReturn(array("data" => $arr, "info" => $remsg, "status" => 1));
    }
}
