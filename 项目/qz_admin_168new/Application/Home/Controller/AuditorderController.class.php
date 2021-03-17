<?php
// +----------------------------------------------------------------------
// | AuditOrderController 质检订单
// +----------------------------------------------------------------------
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Db\OrderRobPoolModel;
use Home\Model\Logic\AuditCitysLogicModel;
use Home\Model\Logic\CompanyLogicModel;
use Home\Model\Logic\NewReviewLogicModel;
use Home\Model\Logic\OrderReviewLogicModel;
use Home\Model\Logic\QuyuLogicModel;
use Home\Model\Logic\AuditOrderLogicModel;
use Home\Model\Logic\OrdersLogicModel;
use Home\Model\Logic\RobOrdersLogicModel;
use Home\Model\Logic\OrderQueueLogicModel;
use Home\Model\Logic\UserLogicModel;

use Home\Model\OrderCsosNewModel;
use Home\Model\OrdersModel;
use Home\Model\Service\MsgServiceModel;
use Home\Model\Service\PnpServiceModel;
use Common\Enums\MsgTemplateCodeEnum;

class AuditorderController extends HomeBaseController
{
    /**
     * 质检订单列表
     */
    public function index()
    {
        //查新订单类型
        $begin = I('get.begin', '', 'trim');
        $end = I('get.end', '', 'trim');

        $page_type = I('get.type', 1, 'intval');
        if ($page_type != 1) {
            // 获取质检人员列表
            $adminModel = new \Home\Model\Db\AdminuserModel();
            $zjList = $adminModel->getAdminByUid([23, 66, 99]);
            foreach ($zjList as $key => $value) {
                $zjList[$key]['name'] = $value['name'];
                $zjList[$key]['id'] = $value['id'];
            }
            $this->assign('zjlist',$zjList);
        }

        // 如果是已经分配的列表,默认限制只看最近3个月的数据
        if (!empty($begin) && !empty($end)) {
            $end = $end.' 23:59:59';
        } else {
            $begin = date('Y-m-d H:i:s', strtotime('-3 month'));
            $end = date('Y-m-d H:i:s', time());
        }

        // 获取当前质检用户可以查看的城市
        $cs = AuditCitysLogicModel::getRealUserCitys(session('uc_userinfo.id'), session('uc_userinfo.uid'), $page_type);

        $quyuLogic = new QuyuLogicModel();
        $this->assign('citys', $quyuLogic->getQuyuByCid($cs));

        $select_cs = I('get.cs', '', 'trim');
        // 如果有选择城市,则查出所对应的的区县,并判断城市是否在可管辖城市内
        if (!empty($select_cs)) {
            $qxs = $quyuLogic->getArea($select_cs);
            $this->assign('area',$qxs);
            $cs = in_array($select_cs, $cs) ? $select_cs : [];
        }

        // 获取对接的列表
        $list = AuditOrderLogicModel::getZjDockingList(
            $page_type,
            I('get.zj', 0, 'trim'),
            empty($cs) ? '' : $cs,
            I('get.id', '', 'trim'),
            $begin,
            $end,
            I('get.type_fw', 0, 'intval'),
            I('get.qx', '', 'trim'),
            I('get.rob_type', '', 'trim')
        );
        $this->assign('list',$list['list']);
        $this->assign('page',$list['page']);
        $this->display();
    }

    /**
     * 质检订单详情
     */
    public function detail()
    {
        if (IS_POST) {
            // 查询订单信息
            $info = AuditOrderLogicModel::getOrderInfo(I('post.id', ''));

            if (empty($info)) {
                $this->ajaxReturn(['code' => 404, 'errmsg' => '该订单不存在！']);
            }

            // 过滤不规则字符串
            $reg = '/[\`~!@#$%^&*\(\)+<>?"{},.\/;\'\"\s]/';
            $info['xiaoqu'] = preg_replace($reg, '', $info['xiaoqu']);

            $this->assign('info', $info);

            // 后台转发人数组
            $ids = D('Options')->getOptionNameCC('kf_admin_order_users');
            $names = D('User')->getAdminNamesById($ids['option_value']);
            $this->assign('zhuanfaren', array_column($names, 'name'));

            //获取订单城市和区县
            $city = D('Quyu')->getCityInfoById($info['cs']);
            $this->assign('city', $city);

            //户型
            $huxing = D('Huxing')->gethx();
            //预算
            $yusuan = D('Jiage')->getJiage();
            //装修方式
            $fangshi = D('Fangshi')->getfs();
            //风格
            $fengge = D('Fengge')->getfg();
            //获取最后审核人信息
            $csos_new = D('OrderCsosNew')->getCsosInfo($info['id']);

            //获取 未接通电话短信通知 短信记录
            $logCount = D('LogSmsSend')->getOrderSendLogCount($info['id'], 2);

            //获取 通知业主分配的装修公司 短信记录
            $smsCount = D('LogSmsSend')->getOrderSendLogCount($info['id'], 1);

            //获取订单分配信息
            $company = D('OrderInfo')->getOrderComapny($info['id']);
            $lostFenCompany = [];
            //有分配订单的情况下，查询微信是否发送
            if (!empty($company)) {
                foreach ($company as $item) {
                    if ($item["on"] == "-1") {
                        $lostFenCompany[] = $item;
                    }
                }
                //获取订单微信发送记录
                $wx = D('LogWxOrdersend')->getWeixinLog($info['id']);
                if (!empty($wx)) {
                    $this->assign('wxMark', 1);
                }
            }

            // 获取所有的会员公司
            $bold_company_ids = [];

            //获取该订单所在城市的的会员公司
            $result = AuditOrderLogicModel::getCompanyList($info['cs'], $info['lng'], $info['lat'],$info);

            //如果是已分配公司,默认选中
            foreach ($company as $key => $value) {
                $bold_company_ids[] = $value["id"];
                $compnay_id[$value['id']] = $value['id'];
            }
            $choose_company = [];
            foreach ($company as $key => $value) {
                $choose_company[$value['id']]['company_id'] = $value['id'];
                $choose_company[$value['id']]['allot_source'] = $value['allot_source'];
            }

            foreach ($result[0] as $key => $value) {
                foreach ($value['child'] as $k => $val) {
                    $bold_company_ids[] = $val["id"];
                    if (array_key_exists($val['id'], $compnay_id)) {
                        $result[0][$key]['child'][$k]['ischeck'] = 1;
                        if ($choose_company[$val['id']]['allot_source'] == 3) {
                            $result[0][$key]['child'][$k]['no_change'] = 1;
                        } else {
                            $result[0][$key]['child'][$k]['no_change'] = 0;
                        }
                    }

                    //签返装修公司不可选中状态
                    if ( $val["classid"] == 6 && isset($val["package_status"]) && $val["package_status"] == 3) {
                        $result[0][$key]["child"][$k]["un_package_change"] = 1;
                    } elseif($val["classid"] == 6 && isset($val["package_status"]) && $val["package_status"] == 2 && in_array($info["type_fw"],array(1,3)) ){
                        //订单包可用并且是分、分没人跟状态
                        if ($val["use_fen"] == 3) {
                            $result[0][$key]["child"][$k]["un_package_change"] = 1;
                        }
                    } elseif($val["classid"] == 6 && isset($val["package_status"]) && $val["package_status"] == 2 && in_array($info["type_fw"],array(2,4)) ){
                        //订单包可用并且是分、分没人跟状态
                        if ($val["use_zen"] == 3) {
                            $result[0][$key]["child"][$k]["un_package_change"] = 1;
                        }
                    } elseif(!isset($val["package_status"]) && $val["classid"] == 6) {
                        $result[0][$key]["child"][$k]["un_package_change"] = 1;
                    }
                }
            }

            foreach ($result[1] as $key => $value) {
                foreach ($value['child'] as $k => $val) {
                    if (array_key_exists($val['id'], $compnay_id)) {
                        $result[1][$key]['child'][$k]['ischeck'] = 1;
                    }
                    if ($choose_company[$val['id']]['allot_source'] == 3) {
                        $result[1][$key]['child'][$k]['no_change'] = 1;
                    } else {
                        $result[1][$key]['child'][$k]['no_change'] = 0;
                    }

                    //签返装修公司不可选中状态
                    if ($val["classid"] == 6 && isset($val["package_status"]) && $val["package_status"] == 3) {
                        $result[1][$key]["child"][$k]["un_package_change"] = 1;
                    } elseif($val["classid"] == 6 && isset($val["package_status"]) && $val["package_status"] == 2 && in_array($info["type_fw"],array(1,3)) ){
                        //订单包可用并且是分、分没人跟状态
                        if ($val["use_fen"] == 3) {
                            $result[1][$key]["child"][$k]["un_package_change"] = 1;
                        }
                    } elseif($val["classid"] == 6 && isset($val["package_status"]) && $val["package_status"] == 2 && in_array($info["type_fw"],array(2,4)) ){
                        //订单包可用并且是分、分没人跟状态
                        if ($val["use_zen"] == 3) {
                            $result[1][$key]["child"][$k]["un_package_change"] = 1;
                        }
                    }  elseif(!isset($val["package_status"]) && $val["classid"] == 6) {
                        $result[1][$key]["child"][$k]["un_package_change"] = 1;
                    }
                }
            }


            //查询上次分配装修公司
            $fenpei_company = D('OrderInfo')->getLastTypeFw($info['id'], $info['cs']);
            //本地装修公司
            foreach ($fenpei_company as $k => $val) {
                $bold_company_ids[] = $val["id"];
                if ($val['cs'] == $info['cs']) {
                    $fenpei_now_company[] = $val;
                    unset($fenpei_company[$k]);
                }
            }

            //其他城市
            foreach ($result[1] as $key => $value) {
                foreach ($fenpei_company as $k => $val) {
                    if ($val['cs'] == $key) {
                        $result[1][$key]['fen_company'][] = $val;
                        unset($fenpei_company[$k]);
                    }
                }
            }

            // 装修公司月订单达标状态
            $companyLogic = new CompanyLogicModel();
            $result = $companyLogic->setCompanyMonthOrderReachStatus($result, $info["cs"], $info["cname"]);

            //获取最近30过期的会员信息
            $lostCompany = AuditOrderLogicModel::getLastExpireCompanyList($info['cs'], date('Y-m-d'));

            //获取群公布模版信息
            $notice = D('OrderNoticeTemplate')->getCityTemplate($info['cs']);

            //获取订单的城市信息
            $cityInfo = D('OrderCityInfo')->findCityInfo($info['cs']);
            if (!empty($cityInfo)) {
                $this->assign('isDocking',1);
                $this->assign('cityInfo',$cityInfo);

            }
            //获取城市信息模版
            $tmp = $this->fetch('/Auditorder/order/cityTmp');
            $this->assign('tmp', $tmp);

            //获取申请记录
            //$apply = D('Orders')->getOrderApplyEdit($info['id'], 1);
            //$this->assign('apply', $apply);

            // 需要加粗的会员公司ID
            $bold_company_ids = array_unique($bold_company_ids);
            $bold_company_ids = $this->getBoldCompanyIds($bold_company_ids);
            $this->assign("boldIds", $bold_company_ids);

            $this->assign("lostFenCompany",$lostFenCompany);
            $this->assign('notice', $notice);
            $this->assign('lostCompany', $lostCompany);
            $this->assign('company', $company);
            $this->assign('smsCount', $smsCount);
            $this->assign('fenpei_now_company', $fenpei_now_company);
            $this->assign('nowCompanys', $result[0]);
            $this->assign('otherCompanys', $result[1]);
            $this->assign('logCount', $logCount);
            $this->assign('csos_new', $csos_new);
            $docking_status = AuditOrderLogicModel::$docking_status;

            //940 删除撤回操作
            unset($docking_status[2]);

            if (I('post.type', 1, 'intval') != 1) {
                //已分配订单/抢单撤回 仅保留撤回操作
                // $docking_status_new[2] = $docking_status[2];
                $this->assign('status', []);
            } else {
                $this->assign('status', $docking_status);
            }

            $this->assign('gettype', I('post.type', 1, 'intval'));

            $this->assign('keys', AuditOrderLogicModel::$keys);
            $this->assign('lf_time', AuditOrderLogicModel::$lf_time);
            $this->assign('start_time', AuditOrderLogicModel::$start_time);
            $this->assign('shi', AuditOrderLogicModel::$shi);
            $this->assign('lxs', AuditOrderLogicModel::$lxs);
            $this->assign('fengge', $fengge);
            $this->assign('fangshi', $fangshi);
            $this->assign('yusuan', $yusuan);
            $this->assign('huxing', $huxing);
            $this->assign('applyname', session('uc_userinfo.name'));
            $tmp = $this->fetch('detail');
            $this->ajaxReturn(['code' => 200, 'data' => $tmp, 'info' => $info]);
        }
    }


    /**
     * 质检订单审核
     */
    public function check()
    {
        if (IS_POST) {
            $status = str_replace('remark_','',I('post.status',''));
            $sub_status = I('post.sub_status','');
            if (!$status) {
                $this->ajaxReturn(['code' => 404, 'errmsg'=> '请选择订单状态后, 再审核']);
            }
            if ($status != 6 && !$sub_status) {
                $this->ajaxReturn(['code' => 404, 'errmsg'=> '请选择备注']);
            }
            switch ($status) {
                case '2': //撤回
                    $data['on'] = 99;
                    $data['on_sub'] = 0;
                    $data['on_sub_wuxiao'] = 0;
                    $data['type_fw'] = 0;
                    $data['fen_customer'] = 0;
                    break;
                case '5': //分单
                    $data['on'] = 4;
                    $data['type_fw'] = 1;
                    $data['on_sub'] = 0;
                    $data['on_sub_wuxiao'] = 0;
                    break;
                case '9': //赠单
                    $data['on'] = 4;
                    $data['type_fw'] = 2;
                    $data['on_sub'] = 0;
                    $data['on_sub_wuxiao'] = 0;
                    break;
                case '6':
                    //推送至抢单池 (6.抢分 8.抢赠)
                    $data["on"] = 4;
                    break;
            }

            if ($sub_status !== '') {
                $data['remarks'] = I('post.sub_status');
            }

            //查询订单信息
            $info = AuditOrderLogicModel::getOrderInfo(I('post.id', ''));
            if (empty($info)) {
                $this->ajaxReturn(['code' => 404, 'errmsg'=> '该订单不存在！']);
            }
            //抢单撤回不需要验证
            if(I('post.gettype') != 3){
                if (!empty($info['is_rob'])) {
                    $this->ajaxReturn(['code' => 404, 'errmsg'=> '订单已入抢单池，不可操作']);
                }
            }

            // 审核的时候先验证数据是否填写
            $ordersLogic = new OrdersLogicModel();
            $check = $ordersLogic->checkDockingOrdersInfo(I('post.'),$info);
            if ($check['code'] != 200) {
                $this->ajaxReturn(['code' => 404, 'errmsg' => $check['errmsg'], 'err_field' => $check['err_field']]);
            }

            if (empty($info['fen_customer']) && $status != 2) {
                $data['fen_customer'] = session('uc_userinfo.id');
            }

            $time = time();
            $data['lasttime'] = $time;
            $orders = D('Home/Orders');
            $csosModel = D('OrderCsosNew');
            $result = $orders->editOrder($info['id'],$data);

            if ($result !== false) {
                // 删除装修公司反馈信息
                D('Home/Logic/OrderCompanyReviewLogic')->delReviewInfoByOrderId($info['id']);
                // 修改订单状态
                AuditOrderLogicModel::orderStatusChange($info['id'],$data['on'],$data['on_sub'],$data['on_sub_wuxiao']);

                // 撤回单的时候调整订单状态
                if ($data['on'] == 99) {
                    // 记录操作统计表
                    $csosData['order_on'] = $data['on'];
                    $csosData['order_on_sub'] = $data['on_sub'];
                    $csosData['order_on_sub_wuxiao'] = $data['on_sub_wuxiao'];
                    $csosModel->editCsos($info['id'],$csosData);

                    // 删除对接信息
                    D('OrderDocking')->delDocking($info['id']);
                    // 删除抢单池数据
                    $robDb = new OrderRobPoolModel();
                    $robDb->delRobInfo($info['id']);

                    //删除新回访数据
                    $reviewLogic = new NewReviewLogicModel();
                    $reviewLogic->delReviewInfo($info["id"]);

                    //解除虚号订单绑定
                    $service = new PnpServiceModel();
                    $service->unBindTel($info["id"]);
                }
                if ($status != 2) {
                    //查询是否有对接信息
                    $count = D('OrderDocking')->getDockingInfoCount($info['id']);
                    if ($count == 0) {
                        $dockData['order_id'] = $info['id'];
                        $dockData['op_uid'] = session('uc_userinfo.id');
                        $dockData['op_uname'] = session('uc_userinfo.name');
                        $dockData['time'] = $time;
                        D('OrderDocking')->addDocking($dockData);
                    }
                }

                // 添加审单日志
                $logData = [
                    'orderid' => $info['id'],
                    'old_on' => $info['on'],
                    'new_on' => $data['on'],
                    'old_on_sub' => $info['on_sub'],
                    'new_on_sub' => $data['on_sub'],
                    'old_on_sub_wuxiao' => $info['on_sub_wuxiao'],
                    'new_on_sub_wuxiao' => $data['on_sub_wuxiao'],
                    'old_type_fw' => $info['type_fw'],
                    'new_type_fw' => $data['type_fw'],
                    'new_type_zs_sub' => $data['type_zs_sub'],
                    'user_id' => session('uc_userinfo.id'),
                    'user_uid' => session('uc_userinfo.uid'),
                    'old_name' => '',
                    'name' => session('uc_userinfo.name'),
                    'time' => $time
                ];

                $csosModel->addLog($logData);
                if (($sub_status !== '') && ($info['remarks'] != $sub_status)) {
                    $save['order_id'] = $info['id'];
                    $save['remark_time'] = date('Y-m-d H:i:s', $time);
                    D('LogOrderRemarkTime')->addLogOrderRemarkTime($save);
                }
                
                //勾选了推送抢单池
                if($status == 6){
                    $robLogic = new RobOrdersLogicModel();
                    $company = $this->getCompanyDetailsList(array($info['cs']),2);
                    $status = $robLogic->addRobPool($info,$company,[],$status);
                    //添加订单绑定虚号
                    $service = new PnpServiceModel();
                    $service->BindOrderTelX($info["id"]);
                    if($status['code'] != 200){
                        $this->ajaxReturn(array('errmsg'=> $status['errmsg'],'code'=> $status['code']));
                    }
                }else{
                    // 删除已分配装修公司
                    D('OrderInfo')->delOrderInfo($info['id']);
                }

                $this->ajaxReturn(['code' => 200, 'errmsg'=> '订单审核成功！']);
            }

            $this->ajaxReturn(['code' => 404, 'errmsg'=> '订单审核失败！']);
        }
    }

    /**
     * 质检订单分配
     */
    public function allocation()
    {
        if (IS_POST) {
            $oldComids = $oldComids = $order_info = [];
            try {
                M()->startTrans();
                //装修公司验证
                $logic = new OrdersLogicModel();
                $reviewLogic = new OrderReviewLogicModel();
                $newReviewLogic = new NewReviewLogicModel();
                $companys = I("post.companys");
                $type = I("post.type");
                $type_fw = $logic->checkFenCompanyInfo($companys);

                //订单信息验证
                //订单信息
                $order_info = $logic->checkFenOrderInfo(I("post.id"),$type_fw);

                //获取常规公司和老签返公司和新签返公司
                $result = $logic->getComapnyClassified($companys);
                $normal = $result["normal"]; //常规
                $old_qian = $result["old_qian"]; //老签返
                $new_qian = $result["new_qian"]; //新签返
                $all_company = $result["all"]; //全部

                //获取订单分配信息
                $order_fen_info = $logic->getOrderFenComapny(I("post.id"));
                //已分配的公司ID
                $oldComids = array_column($order_fen_info, "id");
                //新分配的公司ID
                $nowComids = array_column($companys, "company_id");

                //常规会员业务逻辑处理（1.0 1.0标杆）
                $logic->handlingNormalBusiness($normal,$order_info,$order_fen_info);

                //新签返会员业务逻辑处理（2.0 2.0标杆）
                $logic->handlingNewQianBusiness($new_qian,$order_info,$order_fen_info);

                //老签返业务逻辑处理
                $logic->handlingOldQianBusiness($old_qian,$order_info,$order_fen_info);

                //订单量房处理
                $logic->setOrderCompanyReview($all_company,$order_info["id"]);

                //回访业务处理
                //订单转入回访池(未分配和待分配的分单)
                if(($type == 3 || $type == 1) && ($type_fw == 1)) {
                    $reviewLogic->insertToReview(I("post.id"),I("post.lftime"));
                }

                $reviewCs = $newReviewLogic->getReviewCityByCity($order_info['cs']);
                if (!empty($reviewCs)) {
                    //新增新回访
                    if(!empty($order_info['review_time'])){
                        $reviewCs["mianji_min"] = floatval($reviewCs["mianji_min"]);
                        $reviewCs["mianji_max"] = floatval($reviewCs["mianji_max"]);

                        $mianji_condition = 1;
                        if (is_numeric($order_info["mianji"])) {
                            // 最小面积限制
                            if ($order_info["mianji"] < $reviewCs["mianji_min"]) {
                                $mianji_condition = 2;
                            }

                            // 最大面积限制
                            if ($reviewCs["mianji_max"] > 0 && $order_info["mianji"] > $reviewCs["mianji_max"]) {
                                $mianji_condition = 2;
                            }
                        }

                        if ($mianji_condition == 1) {
                            $newReviewLogic->addReviewInfo($order_info["id"], $order_info['review_time']);
                        }
                    }
                } else {
                    //删除新回访数据
                    $newReviewLogic->delReviewInfo($order_info["id"]);
                }

                //订单逻辑处理
                //更新订单状态，分/赠单算分单
                $data = array(
                    "on" => 4,
                    "type_fw" => $type_fw,
                    "lasttime" => time(),
                    "fen_customer" => empty($order_info["fen_customer"])?session("uc_userinfo.id"):$order_info["fen_customer_id"]
                );

                //如果分配的时候是分单，添加结算标识为有效
                if ($order_info["on"] == 4 && $type_fw == 1) {
                    $data["is_settlement"] = 1;
                }

                (new OrdersModel())->editOrder($order_info["id"],$data);
                // 修改订单已读状态
                $logic->updateOrderComReadAll($order_info["id"], $order_info["order2com_allread"]);

                //对接业务处理
                //查询是否有对接信息
                $count = D("OrderDocking")->getDockingInfoCount($order_info["id"]);
                if ($count == 0) {
                    $data = array(
                        "order_id" => $order_info["id"],
                        "op_uid" => session("uc_userinfo.id"),
                        "op_uname" => session("uc_userinfo.name"),
                        "time" => time()
                    );
                    D("OrderDocking")->addDocking($data);
                }

                //添加操作日志
                $source = array(
                    "username" => session("uc_userinfo.name"),
                    "admin_id" => session("uc_userinfo.id"),
                    "orderid"  => $order_info["id"],
                    "type"     => $type_fw,
                    "postdata" => json_encode($data),
                    "addtime"  => time()
                );
                D("LogEditorders")->addLog($source);

                $csosModel = new OrderCsosNewModel();
                //添加审单日志
                $logData = array(
                    "orderid" => $order_info["id"],
                    "old_on" => $order_info["on"],
                    "new_on" => $order_info["on"],
                    "old_on_sub" => $order_info["on_sub"],
                    "new_on_sub" => $order_info["on_sub"],
                    "old_on_sub_wuxiao" => $order_info["on_sub_wuxiao"],
                    "new_on_sub_wuxiao" => $order_info["on_sub_wuxiao"],
                    "old_type_fw" => $order_info["type_fw"],
                    "new_type_fw" => $type_fw,
                    "user_id" => session("uc_userinfo.id"),
                    "user_uid" => session("uc_userinfo.uid"),
                    "name" => session("uc_userinfo.name"),
                    "time" => time(),
                );

                $csosModel->addLog($logData);


                M()->commit();
            }catch (\Exception $e) {
                M()->rollback();
                $this->ajaxReturn(["error_msg" => $e->getMessage() ,"error_code" => $e->getCode()]);
            }

            try {
                //其他辅助功能
                //消息推送处理
                //获取 通知业主分配的装修公司 短信记录
                $sms_status = 2;
                $smsCount = D("LogSmsSend")->getOrderSendLogCount($order_info["id"],1);
                if ($smsCount  == 0) {
                    //自动发送短信
                    $orderCon = new OrdersController();
                    $result = $orderCon->sendOrderSms($order_info["id"]);
                    if ($result) {
                        $sms_status = 1;
                    }
                }

                // QZMSG 装修公司新订单通知
                $newComids = array_diff($nowComids, $oldComids);
                if (count($newComids) > 0) {
                    $msgService = new MsgServiceModel();
                    $linkparam = "?order_id=" . $order_info["id"];
                    $msgService->sendCompanyMsg(MsgTemplateCodeEnum::ORDER_NEW, $newComids, $linkparam, $order_info["id"], "订单分配");
                }
                // 1336 销售中心驾驶舱-基础数据处理 加入订单处理队列
                OrderQueueLogicModel::addQueueInfo($order_info["id"], $newComids);

                //友盟推送处理
                //获取device_token
                $devidetokens = $logic->getDeviceToken($newComids);
                if(!empty($devidetokens)){
                    $logic->sendUmeng($devidetokens,I("post.id"),'1','您有新的装修订单，立即查看~',$newComids);
                }

                //添加订单绑定虚号
                $service = new PnpServiceModel();
                $service->BindOrderTelX($order_info["id"]);

                //获取订单分配信息
                $company = D("OrderInfo")->getOrderComapny($order_info["id"]);

                $this->ajaxReturn(array('error_code'=> 0,"data"=>$company,"info"=> $sms_status ,"error_msg"=>"订单分配成功！ "));

            } catch (\Exception $e) {
                $this->ajaxReturn(["error_msg" => '分配业务已完成，推送业务异常，请重新推送' ,"error_code" => $e->getCode()]);
            }
        }
    }

    /**
     * 需要加粗的会员公司ID
     * @param  [type] $company_ids [description]
     * @return [type]              [description]
     */
    private function getBoldCompanyIds($company_ids){
        $companyIds = [];
        if (!empty($company_ids)) {
            $list = D("Orders")->getOrderMonthReachCompany($company_ids);
            if (!empty($list)) {
                $companyIds = array_column($list, "id");
            }
        }
        
        return $companyIds;
    }

    /**
     * 获取装修公司详细信息
     * @param  [type] $companys [description]
     * @param  [type] $on       [description]
     * @return [type]           [description]
     */
    private function getCompanyDetailsList($cs,$on,$companys = [],$getGiftOrder = ''){
        $companys = D("User")->getCompanyDetailsList($cs,$on,$companys,$getGiftOrder);

        //给公司数据添加设置接单区域
        $comLogic = new CompanyLogicModel();
        $companys = $comLogic->setCompanyDeliverArea($companys);
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

            if ($value["cooperation_type"] != 1) {
                $companys[$key]["cooperation_name"] = $comLogic->cooperationType[$value["cooperation_type"]];
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
}