<?php
/**
 * 直接分配装修公司
 */
namespace Home\Model\Logic;

use Home\Controller\OrdersController;
use Home\Model\Db\OrderInfoModel;
use Home\Model\Db\OrderRobPoolModel;

use Home\Model\Service\MsgServiceModel;
use Common\Enums\MsgTemplateCodeEnum;

use Home\Model\Logic\NewReviewLogicModel;

class OrdersGiveCompanyLogicModel
{
    /**
     * 勾选直接赠送
     * @param $orderInfo
     * @param array $company
     * @return array
     */
    public function giveCompany($orderInfo,$companyList = [])
    {
        $companyErp = [];
        if(empty($orderInfo['id'])){
            return [];
        }
        $infoDb = new OrderInfoModel();
        //1.查询出已经分配的 , 算还有几家可以分配
        $getCompany = $infoDb->getOrderCompanyInfo($orderInfo['id']);
        //2.根据订单城市取出可以分配的公司,并筛选出可能已经分配过的公司
        $company = array_column($companyList,'id');//赋值
        if(count($getCompany) > 0){
            $company = array_diff($company,array_column($getCompany,'com'));
            //可分配数
            $allot_num = (int)$orderInfo['allot_num'] - count($getCompany);
            //将已经分配过的数据拿出来(用作添加erp)
            foreach ($getCompany as $k => $v) {
                $companyErp[] = [
                    'compnay_id' => $v["com"],
                    'type_fw' => $v['type_fw'],
                ];
            }
        } else {
            $allot_num = (int)$orderInfo['allot_num'];
        }
        //2.1过滤不符合条件的公司(看当前公司是否设置了 当前订单 的接单区域,废除之前的订单的城市找公司)
        foreach ($companyList as $k=>$v){
            //区域条件
            if(strpos($v['deliver_area'],$orderInfo['qx']) === false){
                foreach ($company as $kk=>$vv){
                    if($vv == $v['id']){
                        unset($company[$kk]);
                    }
                }
            }
            //面积条件
            if(!empty($v['mianji']) && $v['mianji'] > $orderInfo['mianji']){
                foreach ($company as $kk=>$vv){
                    if($vv == $v['id']){
                        unset($company[$kk]);
                    }
                }
            }
            //装修类型条件
            if($v['lxs'] != $orderInfo['lxs'] && $v['lxs'] != 3){
                foreach ($company as $kk=>$vv){
                    if($vv == $v['id']){
                        unset($company[$kk]);
                    }
                }
            }
        }
        //如果能分配
        if ($allot_num > 0 && count($company) > 0) {
            //3.取出分配较少的公司 , 用作分配(过滤公司接收 订单 类型/是否要赠单)
            $companys = $infoDb->getCompanyGetLess($company, $orderInfo['lx'], $orderInfo['cs'],1);
            //3.2 过滤对立公司
            foreach ($companys as $k => $v) {
                $companys[$v['com']] = $v;
                unset($companys[$k]);
            }
            //用来做数据源
            $company = $companys;
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
            //3.2.2 根据订单剩余分配数 , 取出需要的几条数据
            $company = array_slice($company, 0, $allot_num, true);
            //4.分配装修公司
            $subData = [];
            $companyIds = [];
            foreach ($company as $k => $v) {
                $companyIds[] = $v['com'];
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
                //用来添加ero数据
                $companyErp[] = [
                    'compnay_id' => $v["com"],
                    'type_fw' => 2,
                ];
            }

            $status = D("OrderInfo")->addAllInfo($subData);
            //5.更改订单信息
            if($status){
                //获取分配后订单信息
                $type_fw = ($orderInfo['type_fw'] == 1) ? 1 : 2;
                $this->saveOrderInfo($orderInfo,$type_fw,$company);
                //6.添加量房统计/erp订单
                $lfLogic = new OrderLiangFangLogicModel();
                $lfLogic ->addOrderLiangFang($orderInfo['id'],$company);
                $erpLogic = new OrderErpLogicModel();
                $erpLogic->addOrderErp($orderInfo,$companyErp);

                // QZMSG 装修公司新订单提醒
                $msgService = new MsgServiceModel();
                $linkparam = "?order_id=" . $orderInfo['id'];
                $msgService->sendCompanyMsg(MsgTemplateCodeEnum::ORDER_NEW, $companyIds, $linkparam, $orderInfo['id'], "客服对接直接赠送");

                // 1336 销售中心驾驶舱-基础数据处理 加入订单处理队列
                OrderQueueLogicModel::addQueueInfo($orderInfo["id"], $companyIds);
            }else{
                return ['errmsg'=> '分配公司失败! ','code'=> 404];
            }
        }
    }

    /**
     * 抢单池订单转直接赠送
     * @param $orderInfo
     * @param array $company
     * @return array
     */
    public function giveCompanyApi($orderInfo,$companyList = [])
    {
        $companyErp = [];
        if (empty($orderInfo['id'])) {
            return ['code' => 404, 'errmsg' => '未查找到订单数据'];
        }
        $infoDb = new OrderInfoModel();

        //1.查询出已经分配的 , 算还有几家可以分配
        $getCompany = $infoDb->getOrderCompanyInfo($orderInfo['id'],'i.*,c.other_id');

        //2.根据订单城市取出可以分配的公司,并筛选出可能已经分配过的公司
        $company = array_column($companyList, 'id');//赋值.

        //2.0.1根据当前公司的对立公司 , 删除数据源的公司
        $other_ids = '';//已分配的对立公司
        foreach ($getCompany as $k => $v) {
            if ($v['other_id']) {
                $other_ids .= $v['other_id'] . ',';
            }
        }
        if (!empty($other_ids)) {
            $company = array_diff($company, explode(',', $other_ids));
        }

        //2.0.2 过滤不抢当前订单的公司 逻辑
        $rob = new RobOrdersLogicModel();
        $coms = $rob->getUnRobCompanyById($orderInfo['id']);
        if(count($coms) > 0){
            $company = array_diff($company,array_column($coms,'company_id'));
        }

        //2.0.3 过滤已分配的公司
        if(count($getCompany) > 0){
            $company = array_diff($company,array_column($getCompany,'com'));
            //可分配数
            $allot_num = (int)$orderInfo['allot_num'] - count($getCompany);
            //将已经分配过的数据拿出来(用作添加erp)
            foreach ($getCompany as $k => $v) {
                $companyErp[] = [
                    'compnay_id' => $v["com"],
                    'type_fw' => $v['type_fw'],
                ];
            }
        } else {
            //可分配数
            $allot_num = (int)$orderInfo['allot_num'];
        }

        //2.1过滤不符合条件的公司(看当前公司是否设置了 当前订单 的接单区域,废除之前的订单的城市找公司)
        foreach ($companyList as $k=>$v){
            //区域条件
            if(strpos($v['deliver_area'],$orderInfo['qx']) === false){
                foreach ($company as $kk=>$vv){
                    if($vv == $v['id']){
                        unset($company[$kk]);
                    }
                }
            }
            //面积条件
            if(!empty($v['mianji']) && $v['mianji'] > $orderInfo['mianji']){
                foreach ($company as $kk=>$vv){
                    if($vv == $v['id']){
                        unset($company[$kk]);
                    }
                }
            }
            //装修类型条件
            if($v['lxs'] != $orderInfo['lxs'] && $v['lxs'] != 3){
                foreach ($company as $kk=>$vv){
                    if($vv == $v['id']){
                        unset($company[$kk]);
                    }
                }
            }
        }

        //如果能分配
        if ($allot_num > 0 && count($company) > 0) {
            //3.取出分配较少的公司 , 用作分配
            $companys = $infoDb->getCompanyGetLess($company, $orderInfo['lx'], $orderInfo['cs'], 2);
            //如果没有数据
            if (count($companys) == 0) {
                $this->changeRobOrder($orderInfo['id']);
                return ['code' => 404, 'errmsg' => '未查找到可分配公司'];
            }
            //3.2 过滤对立公司
            foreach ($companys as $k => $v) {
                $companys[$v['com']] = $v;
                unset($companys[$k]);
            }
            //用来做数据源
            $company = $companys;
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
            //3.2.2 根据订单剩余分配数 , 取出需要的几条数据
            $company = array_slice($company, 0, $allot_num, true);
            //4.分配装修公司
            $subData = [];
            $companyIds = [];
            foreach ($company as $k => $v) {
                $companyIds[] = $v['com'];
                $company[$k]['compnay_id'] = $v['com'];//兼容后面添加数据
                $subData[] = [
                    "com" => $v["com"],
                    "order" => $orderInfo['id'],
                    "type_fw" => 2,
                    "addtime" => time(),
                    "allot_source" => 2,
                    "opid" => '',
                    "opname" => ''
                ];
                //用来添加erp数据
                $companyErp[] = [
                    'compnay_id' => $v["com"],
                    'type_fw' => 2,
                ];
            }

            $msg = '开始';
            $status = D("OrderInfo")->addAllInfo($subData);
            //5.更改订单信息
            if ($status) {
                //编辑分配后订单信息
                $wx_msg = $this->noticeUser($orderInfo, $subData);
                $msg .= '/'.$wx_msg;
                //更新订单信息(如果没人抢过抢分订单,则自动赠送后将订单转为赠单)
                $this->changeOrderByGiveCompany($orderInfo['id']);
                $msg .= '/更新订单状态';
                //6.添加量房统计/erp订单11
                $lfLogic = new OrderLiangFangLogicModel();
                $lfLogic->addOrderLiangFang($orderInfo['id'], $company);
                $msg .= '/添加量房信息状态';
                // $erpLogic = new OrderErpLogicModel();
                // $erpLogic->addOrderErp($orderInfo, $companyErp);

                // QZMSG 装修公司新订单提醒
                $msgService = new MsgServiceModel();
                $linkparam = "?order_id=" . $orderInfo['id'];
                $msgService->sendCompanyMsg(MsgTemplateCodeEnum::ORDER_NEW, $companyIds, $linkparam, $orderInfo['id'], "抢单池订单转直接赠送");
                $msg .= '/推送装修公司站内信';

                // 推送到回访池
                $this->addToReviewNew($orderInfo);

                // 1336 销售中心驾驶舱-基础数据处理 加入订单处理队列
                OrderQueueLogicModel::addQueueInfo($orderInfo["id"], $companyIds);

                return ['errmsg' => $msg . '/分配公司处理成功! ', 'code' => 200];
            } else {
                return ['errmsg' => '分配公司失败! ', 'code' => 404];
            }
        } else {
            $this->changeRobOrder($orderInfo['id']);
            return ['code' => 404, 'errmsg' => '订单已分配完'];
        }
    }

    /**
     * 直接赠送修改订单信息
     * @param $orderInfo
     * @param $type_fw
     * @param array $company
     */
    private function saveOrderInfo($orderInfo,$type_fw,$company = []){
        $saveData = array(
            "on" => 4,
            "type_fw" => $type_fw,
            "lasttime" => time()
        );
        $i = D("Orders")->editOrder($orderInfo["id"],$saveData);
        if ($i !== false) {
            //获取 通知业主分配的装修公司 短信记录
            $smsCount = D("LogSmsSend")->getOrderSendLogCount($orderInfo["id"],1);
            if ($smsCount  == 0) {
                $orderCon = new OrdersController();
                //自动发送短信
                $orderCon->sendOrderSms($orderInfo["id"]);
            }
            $wechat_compnay = array_column($company,'com');
            //发送装修公司
            if (count($wechat_compnay) > 0) {
                $weixin = A("Home/Orderweixin");
                $weixin->send_order_to_compnay($wechat_compnay,$orderInfo["id"], 3);
            }

            //添加操作日志
            $source = array(
                "username" => session("uc_userinfo.name"),
                "admin_id" => session("uc_userinfo.id"),
                "orderid"  => $orderInfo["id"],
                "type"     => $type_fw,
                "postdata" => json_encode($saveData),
                "addtime"  => time()
            );
            D("LogEditorders")->addLog($source);

            if (empty($orderInfo["fen_customer"])) {
                //修改订单分单人信息
                $data = array(
                    "fen_customer" => session("uc_userinfo.id")
                );
                D("Orders")->editOrder($orderInfo["id"],$data);
            }
        }
    }

    /**
     * 修改抢单池信息
     * @param $orderInfo
     * @param $company
     * @param $allot_num 可分配数
     */
    private function noticeUser($orderInfo, $company)
    {
        //修改抢单池信息
        $save = [
            'rob_num' => $orderInfo['rob_num'] + count($company),
            'is_rob' => 2,
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
        return $wx->send_order_to_compnay($wechat_compnay,$orderInfo["id"], 1);
    }

    /**
     * 根据直接赠送装修公司逻辑 , 来修改订单信息
     * @param $order_id
     */
    private function changeOrderByGiveCompany($order_id)
    {
        //更新订单信息,如果没人抢过抢分订单,则自动赠送后将订单转为赠单
        $infoDb = new OrderInfoModel();
        $getCompany = $infoDb->getOrderCompanyInfo($order_id);
        $allot_source = array_count_values(array_column($getCompany, 'allot_source'));

        $saveData = [];
        $saveData["order2com_allread"] = 0;
        //如果自动分配数 和 分配的公司数相同 , 则将抢分转为 赠单
        if ($allot_source[2] == count($getCompany)) {
            //获取抢单池订单的信息
            $robDb = new OrderRobPoolModel();
            $robInfo = $robDb->getRobInfo($order_id);
            if ($robInfo['order_status'] == 1) {
                $saveData["type_fw"] = 2;
                $saveData["lasttime"] = time();
            }
        }

        D("Orders")->editOrder($order_id, $saveData);
    }

    public function changeRobOrder($id){
        //修改抢单池信息
        $save = [
            'is_rob' => 2
        ];
        $robDb = new OrderRobPoolModel();
        $robDb->editOrderRob(['order_id' => ['eq', $id]], $save);
    }

    public function addToReviewNew($orderInfo){
        $reviewLogic = new NewReviewLogicModel();
        $reviewCs = $reviewLogic->getReviewCityByCity($orderInfo['cs']);
        if (!empty($reviewCs)) {
            //新增新回访
            if(!empty($orderInfo['review_time'])){
                $reviewCs["mianji_min"] = floatval($reviewCs["mianji_min"]);
                $reviewCs["mianji_max"] = floatval($reviewCs["mianji_max"]);

                $mianji_condition = 1;
                if (is_numeric($orderInfo["mianji"])) {
                    // 最小面积限制
                    if ($orderInfo["mianji"] < $reviewCs["mianji_min"]) {
                        $mianji_condition = 2;
                    }

                    // 最大面积限制
                    if ($reviewCs["mianji_max"] > 0 && $orderInfo["mianji"] > $reviewCs["mianji_max"]) {
                        $mianji_condition = 2;
                    }
                }
                
                if ($mianji_condition == 1) {
                    $reviewLogic->addReviewInfo($orderInfo["id"], $orderInfo['review_time']);
                }
            }
        } else {
            //删除新回访数据
            $reviewLogic->delReviewInfo($orderInfo["id"]);
        }
    }
}
