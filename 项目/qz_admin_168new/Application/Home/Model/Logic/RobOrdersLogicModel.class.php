<?php
/**
 * 抢单池操作
 */

namespace Home\Model\Logic;

use Home\Model\AdminuserModel;
use Home\Model\Db\OrderInfoModel;
use Home\Model\Db\OrderRobPoolModel;
use Home\Model\Db\UserUnrobRelationModel;
use Home\Model\Db\UmengRecordModel;
use Home\Model\LogSmsSendModel;
use Home\Model\OrdersModel;
use Home\Model\UserModel;

use Home\Model\Service\MsgServiceModel;
use Common\Enums\MsgTemplateCodeEnum;

use Library\Org\Umeng\Umeng;
use Think\Exception;

class RobOrdersLogicModel
{
    /**
     * 添加抢单池
     * @param $info
     * @param array $now_company
     * @param array $other_company
     * @param int $status 6.抢分池 8.抢赠池
     * @return array
     */
    public function addRobPool($info, $now_company = [],$status = 6)
    {
        if (empty($info['id'])) {
            return ['errmsg' => '该订单不存在！', 'code' => 404];
        }
        //获取当前订单是否在抢单池中
        $robDb = new OrderRobPoolModel();
        $robInfo = $robDb->getRobInfo($info['id']);
        if (!$robInfo) {
            $companys = D("OrderInfo")->getOrderComapny($info['id']);

            //如果当前订单还有未分配数
            if (count($companys) != $info['allot_num']) {
                //待分配赠单 , 修改订单为赠单
                if ($info['type_fw'] == 6 || $info['type_fw'] == 4) {
                    $info['type_fw'] = 2;
                }
                if ($info['type_fw'] == 5 || $info['type_fw'] == 3) {
                    $info['type_fw'] = 1;
                }
                $save = [
                    'order_id' => $info['id'],
                    'is_rob' => 1,
                    'add_time' => time(),
                    'allot_num' => $info['allot_num'],
                    'rob_num' => count($companys),
                    'status' => $info['type_fw'],
                    'cs' => $info['cs'],
                    'order_status' => $status == 8 ? 2 : 1,
                ];
                $status = $robDb->addOrderRob($save);
                if ($status) {
                    //将已分配数添加到订单信息中(此时订单信息没有更新 , 手动更新)
                    $info['rob_num'] = $save['rob_num'];
                    D("Home/Orders")->editOrder($info['id'], ['type_fw' => $info['type_fw']]);
                    //先分配当前城市需要全部抢单的公司(k1.7.1)
                    if ($now_company > 0) {
                        $this->giveOrderCompany($info, $now_company);
                    }

                    $passCompany = [];
                    if (!empty($companys)) {
                        $passCompany = array_column($companys, "id");
                        
                        // 对立公司
                        $other_ids = array_column($companys, "other_id");
                        $other_ids = explode(",", implode(",", $other_ids));
                        $passCompany = array_merge($passCompany, array_filter(array_unique($other_ids)));
                    }
                    
                    // 查询通知的会员公司
                    $companyLogic = new CompanyLogicModel();
                    $comids = $companyLogic->getCompanyListByQx($info["qx"]);

                    if (!empty($comids)) {
                        $comids = array_diff($comids, $passCompany);

                        //发送微信通知
                        $wx = new SendSmsLogicModel();
                        $wx->send_rob_order_to_compnay($info, 1, $comids);
                        
                        // 发送友盟消息推送
                        $this->sendUmeng($info["id"], 2, "亲，抢单不等人，拼的就是手速！", $comids);
                    }

                    return ['errmsg' => '推送抢单池成功！', 'code' => 200];
                } else {
                    return ['errmsg' => '推送抢单池失败！', 'code' => 404];
                }
            }
        } else {
            return ['errmsg' => '抢单池存在该订单！', 'code' => 404];
        }
    }

    /**
     * 发送友盟消息
     * @param  [type] $order_id [description]
     * @param  [type] $type     [description]
     * @param  [type] $alert    [description]
     * @param  [type] $comid    [description]
     * @return [type]           [description]
     */
    public function sendUmeng($order_id, $type, $alert, $comid){
        $model = new UmengRecordModel();
        $orderinfomodel = new \Home\Model\Db\OrderInfoModel();
        $appkey_ios = C('umeng_appkey_ios');
        $secret_ios = C('ument_appMasterSecret_ios');

        $appkey_android = C('umeng_appkey_android');
        $secret_android = C('ument_appMasterSecret_android');
        $activity_android = C("umeng_activity_android");

        import('Library.Org.Umeng.Umeng', "", ".php");

        $getlist = $model->getDeviceTokenByComidlist($comid);   //获取devicetokenlist

        if($getlist){
            $device_tokens = array_column($getlist, "device_token");
            $device_tokens = array_filter(array_unique($device_tokens));
            $device_tokens = implode(",", $device_tokens);

            // ios推送
            $send = new Umeng($appkey_ios, $secret_ios);
            $send->sendIOSListcast($device_tokens, $order_id, $type, $alert);

            //android  推送
            $send2 = new Umeng($appkey_android, $secret_android);
            $send2->sendAndroidListcast($device_tokens, $order_id, $type, $alert, $alert, $alert, 'go_app', 0, true, $activity_android);
        }
    }

    /**
     * 验证接口请求
     * @param $info
     * @return array
     */
    public function checkUserByOrder($info)
    {
        if (empty($info['user_id'])) {
            return ['code' => 404, 'errmsg' => '未查询到该用户', 'dd' => 1];
        }
        if (empty($info['order_id'])) {
            return ['code' => 404, 'errmsg' => '未查询到该订单', 'dd' => 2];
        }
        //查询当前用户是否存在
        $user = D('User')->getUserCompanyInfoById($info['user_id']);
        if (count($user) == 0 || $user['user'] != $info['user']) {
            return ['code' => 404, 'errmsg' => '未查询到该用户', 'dd' => 3];
        }
        if ($user['on'] != 2 || $user['fake'] != 0) {
            return ['code' => 404, 'errmsg' => '非会员不可抢', 'dd' => 7];
        }

        // 验证抢单用户是否是签返会员
        if ($user["classid"] == UserModel::CLASSID_SIGNBACK) {
            return ['code' => 404, 'errmsg' => '签返会员不可抢', 'dd' => 7];
        }

        //验证抢单池中订单的状态
        $robDb = new OrderRobPoolModel();
        $robInfo = $robDb->getRobInfo($info['order_id']);
        //可能存在用户页面没刷新 , 但是订单被撤回
        if(empty($robInfo)){
            return ['code' => 404, 'errmsg' => '温馨提示：该订单已被系统回收，请忽略~', 'dd' => 4.1];
        }

        //查询分配表是否已经有该数据
        $infoDb = new OrderInfoModel();
        $getCompany = $infoDb->getOrderCompanyInfoByWhere(['order' => ['eq', $info['order_id']], 'com' => ['eq', $info['user_id']]]);
        if (count($getCompany) > 0) {
            return ['code' => 404, 'errmsg' => '温馨提示：您已抢过该订单了，可在订单列表查看哦~'];
        }

        if ($robInfo['rob_num'] == $robInfo['allot_num']) {
            return ['code' => 404, 'errmsg' => "抱歉，该订单已抢完！\n 下次手速要更快哦~", 'dd' => 5];
        }
        if (count($robInfo) == 0 || $robInfo['is_rob'] == 2) {
            return ['code' => 404, 'errmsg' => '温馨提示：该订单已超时被系统回收，请忽略~', 'dd' => 4];
        }
        if (($robInfo['add_time'] + 60 * 60 * 3) < time()) {
            return ['code' => 404, 'errmsg' => '温馨提示：该订单已超时被系统回收，请忽略~', 'dd' => 6];
        }
        return ['code' => 200, 'errmsg' => '通过审核', 'dd' => 6];
    }

    /**
     * 添加抢单数据
     * @param $data
     * @return array
     */
    public function addRobOrder($data)
    {
        $db = new OrderRobPoolModel();
        $infoDb = new OrderInfoModel();
        $company_id = $data["user_id"];//装修公司id
        //添加之前验证是否还有可抢数,处理并发问题
        $db->startTrans();
        try {
            //查询加锁
            $robInfo = $db->getLockRobInfo($data["order_id"]);
            //编辑条数据用于锁住表(乐观锁必须要有更新操作才能真正锁住表)
            D("Orders")->editOrder($data["order_id"], ['lasttime'=>time()]);
            //查询已分配信息
            $info = $infoDb->getLockOrderInfo(['i.order' => ['eq', $data["order_id"]]], 'i.com,c.other_id');

            //查询分配表是否已经有该数据
            if (in_array($company_id,array_column($info,'com'))) {
                throw new Exception("温馨提示：您已抢过该订单了，可在订单列表查看哦~", 404);
            }

            //超过可分配数则直接回退
            if (count($info) >= $robInfo['allot_num']) {
                throw new Exception("抱歉，该订单已抢完！\n 下次手速要更快哦~", 404);
            }

            //过滤对立公司
            foreach ($info as $k => $v) {
                $other_id = explode(",", $v["other_id"]);
                if (in_array($company_id, $other_id)) {
                    throw new Exception("抱歉，该订单已抢完！\n 下次手速要更快哦~", 404);
                }
            }

            //添加抢单成功后新增分配记录
            $subData[] = [
                "com" => $company_id,
                "order" => $data['order_id'],
                "type_fw" => $robInfo['order_status'], //抢分/抢赠 出来的单子跟着池子走
                "addtime" => time(),
                "allot_source" => 3,
                "opid" => '',
                "opname" => ''
            ];
            $status = D("OrderInfo")->addAllInfo($subData);
            if(empty($status)){
                throw new Exception('操作数据库失败!', 404);
            }

            //修改订单池信息
            $this->editRobOrder($data);
            //更新主订单信息/发送通知
            $this->saveOrderInfo($data['order_id']);

            // QZMSG 装修公司新订单提醒
            $msgService = new MsgServiceModel();
            $linkparam = "?order_id=" . $data["order_id"];
            $msgService->sendCompanyMsg(MsgTemplateCodeEnum::ORDER_NEW, $company_id, $linkparam, $data["order_id"], "用户抢单");

            // 1336 销售中心驾驶舱-基础数据处理 加入订单处理队列
            OrderQueueLogicModel::addQueueInfo($data["order_id"], $company_id);

            $db->commit();
            return ['code' => 200, 'errmsg' => "添加成功! "];
        }catch (Exception $e){
            $db->rollback();
            return ['code' => $e->getCode(), 'errmsg' => $e->getMessage()];
        }
    }

    /**
     * 获取抢单信息
     * @param $get
     * @return array
     */
    public function getRobOrderInfo($get)
    {
        $adminDb = new AdminuserModel();
        $adminInfo = $adminDb->getAdminUserInfoByIds(session('uc_userinfo.id'));
        $id = $get['condition'];
        if ($get['cs']) {
            if (strpos($get['cs'], $adminInfo['cs']) !== false) {
                $cs = $get['cs'];
            } else {
                $cs = $adminInfo['cs'];
            }
        } else {
            $cs = $adminInfo['cs'];
        }
        $begin = '';
        $end = '';
        if ($get['start'] && $get['end']) {
            $begin = $get['start'] . ' 00:00:00';
            $end = $get['end'] . ' 23:59:59';
        }
        $status = $get['status'];
        $model = new OrdersModel();
        $count = $model->getOrderRobListCount([], $id, $cs, $status, $begin, $end, [], []);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, 10);
            $p->setConfig('prev', "上一页");
            $p->setConfig('next', '下一页');
            $show = $p->show();
            $list = $model->getOrderRobList([], $id, $cs, $status, $begin, $end, $p->firstRow, $p->listRows, [], []);
            return ['list' => $list, 'page' => $show];
        }
    }

    public function getOrderPoolDetail($id)
    {
        $robDb = new OrderRobPoolModel();
        $info = $robDb->getOrderRobDetailById($id);
        if (count($info) > 0) {
            $returnData = [];
            foreach ($info as $k => $v) {
                $returnData[$v['allot_source']][] = $v;
            }
            return $returnData;
        } else {
            return [];
        }
    }


    /**
     * 验证抢单转赠单接口请求
     * @param $info
     * @return array
     */
    public function checkGiveOrder($id)
    {
        if (empty($id)) {
            return ['code' => 404, 'errmsg' => '未查询到该订单', 'dd' => 2];
        }
        //验证抢单池中订单的状态
        $robDb = new OrderRobPoolModel();
        $robInfo = $robDb->getRobInfo($id);
        if (count($robInfo) == 0 || $robInfo['is_rob'] == 2) {
            return ['code' => 404, 'errmsg' => '该订单不可抢', 'dd' => 4];
        }
        if ($robInfo['rob_num'] == $robInfo['allot_num']) {
            return ['code' => 404, 'errmsg' => '该订单已抢完', 'dd' => 5];
        }
        return ['code' => 200, 'errmsg' => '通过审核', 'dd' => 6];
    }

    /**
     * 添加抢单转赠单数据
     * @param $data
     * @return array
     */
    public function addRobOrderGiveCompany($data)
    {
        //先添加订单公司对接数据
        $subData[] = [
            "com" => $data["user_id"],
            "order" => $data['order_id'],
            "type_fw" => 1,
            "addtime" => time(),
            "allot_source" => 2,
            "opid" => '',
            "opname" => ''
        ];
        $status = D("OrderInfo")->addAllInfo($subData);
        if ($status) {
            //修改订单池信息
            $this->editRobOrder($data);
            $info = $this->saveOrderInfo($data['order_id']);
            return ['code' => 200, 'errmsg' => $info['errmsg'], 'dd' => 1, 'oo' => $info['oo']];
        } else {
            return ['code' => 404, 'errmsg' => '该订单不可抢', 'dd' => 2];
        }
    }

    public function getRobOrderList($get,$pageCount = 20)
    {
        $map = [
            'o.time_real' => ['between', [strtotime(date("Y-m") . '-1'), time()]]
        ];

        if (!empty($get['start']) && !empty($get['end'])) {
            $map['o.time_real'] = ['between', [strtotime($get['start'] . ' 00:00:01'), strtotime($get['end'] . ' 23:59:59')]];
        }

        if (!empty($get['cs'])) {
            $map['p.cs'] = ['eq', $get['cs']];
        }
        if (!empty($get['order_status'])) {
            switch ($get['order_status']) {
                case '1':
                case '2':
                    $map['o.type_fw'] = ['eq', $get['order_status']];
                    $map['p.is_rob'] = ['eq', 2];
                    break;
                //赠转分
                case '3':
                    $map['p.status'] = ['eq', 2];
                    $map['o.type_fw'] = ['eq', 1];
                    $map['p.is_rob'] = ['eq', 2];
                    break;
                //分可抢
                case '4':
                    $map['o.type_fw'] = ['eq', 1];
                    $map['p.is_rob'] = ['eq', 1];
                    break;
                //赠可抢
                case '5':
                    $map['o.type_fw'] = ['eq', 2];
                    $map['p.is_rob'] = ['eq', 1];
                    break;
            }
        }
        if (!empty($get['lf_status'])) {
            $map['lf_status'] = $get['lf_status'];
        }

        if (!empty($get['groups'])) {
            $map['u.kfgroup'] = ['eq', $get['groups']];
        }
        if (!empty($get['user'])) {
            $map['po.op_uid'] = ['eq', $get['user']];
        }
        $robDb = new OrderRobPoolModel();
        $count = $robDb->getRobOrderCount($map);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, 20);
            $show = $p->show();
            if ($pageCount === 0) {
                $list = $robDb->getRobOrderList($map);
            } else {
                $list = $robDb->getRobOrderList($map, $p->firstRow, $p->listRows);
            }
            return ['list' => $list, 'page' => $show];
        }
    }

    /**
     * 修改订单信息
     * @param $id
     * @return array
     */
    private function saveOrderInfo($id)
    {
        $saveData = [];
        $orderInfo = D('Home/Orders')->findOrderInfo($id);
        $robDb = new OrderRobPoolModel();
        $robInfo = $robDb->getRobInfo($id);
        if(count($robInfo) > 0){
            if($robInfo['order_status'] == 1){
                $saveData['on'] = 4;
                $saveData['type_fw'] = 1;
            }
        }
        $saveData['lasttime'] = time();
        $saveData["order2com_allread"] = 0; // 主订单已读状态
        $i = D("Orders")->editOrder($orderInfo["id"], $saveData);
        if ($i !== false) {
            //获取 通知业主分配的装修公司 短信记录
            $smsDb = new LogSmsSendModel();
            $smsCount = $smsDb->getOrderSendLogCount($orderInfo["id"], 1);
            //如果订单第一次抢,就发短信
            if ($smsCount == 0) {
                //自动发送短信
                $smsLogic = new SendSmsLogicModel();
                $smsLogic->RobSendOrderSms($orderInfo["id"], $orderInfo);
            }

            //添加操作日志
            $source = array(
                "username" => '',
                "admin_id" => '',
                "orderid" => $orderInfo["id"],
                "type" => $orderInfo['type_fw'],
                "postdata" => json_encode($saveData),
                "addtime" => time()
            );
            D("LogEditorders")->addLog($source);
            return ['code' => 200, 'errmsg' => '', 'dd' => 12];
        }
    }

    /**
     * 修改抢单池信息
     * @param $data
     * @return bool
     */
    private function editRobOrder($data)
    {
        $robDb = new OrderRobPoolModel();
        //获取订单信息
        $order = D('Home/Orders');
        $info = $order->findOrderInfo($data['order_id']);
        if ($info['allot_num'] == ((int)$info['rob_num'] + 1)) {
            $info['is_rob'] = 2;
        }
        $save = [
            'is_rob' => $info['is_rob'],
            'rob_num' => (int)$info['rob_num'] + 1,
        ];
        $robDb->editOrderRob(['order_id' => ['eq', $data['order_id']]], $save);

        //添加量房统计/erp订单
        $lfLogic = new OrderLiangFangLogicModel();
        $lfLogic->addOrderLiangFang($data['order_id'], [['compnay_id' => $data['user_id']]]);
        $erpLogic = new OrderErpLogicModel();
        $erpLogic->addOrderErp($info, [['compnay_id' => $data['user_id']]]);
    }

    /**
     * 抢单订单直接分配
     * @param $orderInfo
     * @param array $company
     * @return array
     */
    private function giveOrderCompany($orderInfo, $company = [])
    {
        //格式化装修公司数据
        $company = array_column($company, null, 'id');
        $companyErp = [];
        if (empty($orderInfo['id'])) {
            return [];
        }
        $infoDb = new OrderInfoModel();
        //1.查询出已经分配的 , 算还有几家可以分配
        $getCompany = $infoDb->getOrderCompanyInfo($orderInfo['id']);
        //2.根据订单城市取出可以分配的公司,并筛选出可能已经分配过的公司
        if (count($getCompany) > 0) {
            foreach ($getCompany as $k => $v) {
                //2.1删除已经分配的公司
                unset($company[$v['com']]);
                //2.2将已经分配过的数据拿出来(用作添加erp)
                $companyErp[] = [
                    'compnay_id' => $v["com"],
                    'type_fw' => $v['type_fw'],
                ];
            }
            //可分配数
            $allot_num = (int)$orderInfo['allot_num'] - count($getCompany);
        } else {
            $allot_num = (int)$orderInfo['allot_num'];
        }
        //3.先筛选出符合本城市的装修公司
        //3.1.0过滤不符合条件的公司(看当前公司是否设置了 当前订单 的接单区域,废除之前的订单的城市找公司)
        foreach ($company as $k=>$v){
            //区域条件
            if(strpos($v['deliver_area'],$orderInfo['qx']) === false){
                unset($company[$k]);
            }
            //面积条件
            if(!empty($v['mianji']) && $v['mianji'] > $orderInfo['mianji']){
                unset($company[$k]);
            }
            //装修类型条件
            if($v['lxs'] != $orderInfo['lxs'] && $v['lxs'] != 3){
                unset($company[$k]);
            }
        }
        if ($allot_num > 0 && count($company) > 0) {
            //3.1取出分配较少的公司 , 用作分配(过滤公司接收 订单 类型/是否要赠单)
            $companys = $infoDb->getCompanyAllRob(array_column($company, 'id'), $orderInfo['lx'], $orderInfo['cs']);
            //3.1.1 过滤对立公司
            foreach ($companys as $k=>$v){
                $companys[$v['com']] = $v;
                unset($companys[$k]);
            }
            //保留一份用来做数据源
            $company = $companys;
            $companys = array_column($companys, null, 'com');
            $other_com = [];
            foreach ($companys as $k => $v) {
                //3.2.1 根据当前公司的对立公司 , 删除后面可能存在的公司
                if (!in_array($v['com'], $other_com)) {
                    if ($v['other_id']) {
                        foreach (explode(',', $v['other_id']) as $kk => $vv) {
                            //从数据源中删除对立公司
                            unset($company[$vv]);
                            //将对立公司,塞入统一数组 , 继续循环后的公司如果不在该对立数组里,则继续执行并添加进对立数组
                            $other_com[] = $vv;
                        }
                    }
                }
            }
            //3.1.2 根据订单剩余分配数 , 取出需要的几条数据
            $company = array_slice($company, 0, $allot_num, true);
            //3.1.3 如果分配数不全 ,则不修改订单信息
            $allot_num = $allot_num - count($company);
        }
        //4.分配装修公司
        $subData = [];
        foreach ($company as $k => $v) {
            $company[$k]['compnay_id'] = $v['com'];//兼容后面添加数据
            $subData[] = [
                "com" => $v["com"],
                "order" => $orderInfo['id'],
                "type_fw" => 2,
                "addtime" => time(),
                "allot_source" => 2,
                "opid" => session("uc_userinfo.id"),
                "opname" => session("uc_userinfo.name")
            ];
            //用来添加erp数据
            $companyErp[] = [
                'compnay_id' => $v["com"],
                'type_fw' => 2,
            ];
        }

        $status = D("OrderInfo")->addAllInfo($subData);
        //5.更改订单信息
        if ($status) {
            //如果还未分配完 , 就不修改可抢状态
            if ($allot_num != 0) {
                //编辑分配后订单信息
                $this->noticeUser($orderInfo, $subData, 1);
            } else {
                $this->noticeUser($orderInfo, $subData);
            }
            //6.添加量房统计/erp订单
            $lfLogic = new OrderLiangFangLogicModel();
            $lfLogic->addOrderLiangFang($orderInfo['id'], $company);
            $erpLogic = new OrderErpLogicModel();
            $erpLogic->addOrderErp($orderInfo, $companyErp);
        } else {
            return ['errmsg' => '分配公司失败! ', 'code' => 404];
        }
    }

    /**
     * 
     * @param $orderInfo
     * @param $company
     * @param int $rob_status 可抢状态
     */
    private function noticeUser($orderInfo, $company, $rob_status = 2)
    {
        //修改抢单池信息
        $save = [
            'rob_num' => $orderInfo['rob_num'] + count($company),
            'is_rob' => $rob_status,
        ];
        $robDb = new OrderRobPoolModel();
        $robDb->editOrderRob(['order_id' => ['eq', $orderInfo['id']]], $save);
        //获取 通知业主分配的装修公司 短信记录
        $smsCount = D("LogSmsSend")->getOrderSendLogCount($orderInfo["id"], 1);
        if ($smsCount == 0) {
            //自动发送短信
            $smsLogic = new SendSmsLogicModel();
            $smsLogic->RobSendOrderSms($orderInfo["id"], $orderInfo);
        }
        //发送微信
        $wechat_compnay = array_column($company, 'com');
        $wx = new SendSmsLogicModel();
        $wx->send_order_to_compnay($wechat_compnay, $orderInfo["id"], 1);
    }

    /**
     * 获取装修公司详细信息
     * @param  [type] $companys [description]
     * @param  [type] $on       [description]
     * @return [type]           [description]
     */
    public function getOrderCompanyList($cs,$on,$companys = [],$getGiftOrder = '',$lx = '',$little = ''){
        $companys = D("User")->getCompanyDetailsList($cs,$on,$companys,$getGiftOrder,$lx,$little);
        foreach ($companys as $key => $value) {
            //计算到期时间
            $offset = (strtotime($value["end"]) - strtotime(date("Y-m-d")))/86400+1;

            if ($offset <= 15 && empty($value["start_time"])) {
                $companys[$key]["end_time"] = $offset;
            }

            //合同开始时间如果大于本月1号显示新
            if ($value["start"] >= date("Y-m-d",mktime(0,0,0,date("m"),1,date("Y")))) {
                $companys[$key]["newMark"] = 1;
            }
            $ids[] = $value["id"];
        }

        //获取装修公司暂停信息
        if (count($ids) > 0) {
            $result = D("UserVip")->getParseContractList($ids);
            foreach ($result as $key => $value) {
                //计算到期时间
                $offset = (strtotime(date("Y-m-d")) - strtotime($value["end_time"]))/86400+1;
                if ($offset <= 15 && empty($value["start_time"])) {
                    $parseList[$value["company_id"]]["parseMark"] = 1;
                }
            }

            foreach ($companys as $key => $value) {
                if (array_key_exists($value["id"],$parseList)) {
                    $companys[$key]["parseMark"] = $parseList[$value["id"]];
                }
            }
        }
        return $companys;
    }

    public function getUnRobCompanyById($order_id){
        $unrob = new UserUnrobRelationModel();
        return $unrob->getUnRobCompanyById($order_id,'company_id');
    }
}