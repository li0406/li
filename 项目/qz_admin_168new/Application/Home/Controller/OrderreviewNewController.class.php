<?php
/**
 * Created by PhpStorm.
 * User: jsb
 * Date: 2019/5/16
 * Time: 11:35
 */

namespace Home\Controller;

use Think\Exception;
use Common\Enums\ApiConfig;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\CompanyVisitLogicModel;
use Home\Model\Logic\RoundOrderApplyLogicModel;
use Home\Model\Logic\OrderCompanyReviewLogicModel;
use Home\Model\Logic\OrderReviewCompanyLogicModel;
use Home\Model\Logic\OrderReviewLogicModel;
use Home\Model\Logic\OrderReviewNewApplyLogicModel;
use Home\Model\Logic\OrderReviewNewLogicModel;
use Home\Model\Logic\OrderReviewNewRemarkLogicModel;
use Home\Model\Logic\LogTelcenterOrdercallLogicModel;
use Home\Model\Logic\LogSmsSendLogicModel;
use Home\Model\Logic\QuyuLogicModel;
use Home\Model\Logic\OrdersLogicModel;
use Home\Model\Logic\OrderSourceInLogicModel;
use Home\Model\Logic\AdminuserLogicModel;
use Home\Model\Logic\AdminAuthLogicModel;

use Home\Model\Service\SmsServiceModel;
use Home\Model\Service\MsgServiceModel;

use Common\Enums\DepartmentEnum;
use Common\Enums\RbacRoleEnum;

/**
 * FIXME
 * @description:
 */
class OrderreviewNewController extends HomeBaseController
{
    protected $orderReviewNewLogic;
    protected $orderReviewLogic;
    protected $orderReviewNewRemarkLogic;

    protected $orderReviewApplyLogic;

    protected $orderReviewController;

    protected $uid;
    
    public function __construct()
    {
        parent::__construct();
        $this->uid = $this->User['id'];
        $this->orderReviewNewLogic = new OrderReviewNewLogicModel($this->uid);
        $this->orderReviewLogic = new OrderReviewLogicModel();
        $this->orderReviewNewRemarkLogic = new OrderReviewNewRemarkLogicModel();
        $this->orderReviewApplyLogic = new OrderReviewNewApplyLogicModel();
        $this->orderReviewController = new OrderreviewController();
    }

    //列表中回访时间=下次回访时间
    //历史回访纪录中的回访时间=操作时间
    //排序中的回访时间=下次回访时间
    public function index() {
        $params = I("get.");
        if (empty($params["review_type"])) {
            $params["review_type"] = 1;
        }

        // 城市查询参数
        $params["kf_cs"] = $params["cs"];

        // 获取城市
        $quyuLogic = new QuyuLogicModel();
        $cities = $quyuLogic->getSimpleCitys();
        if ($this->User["uid"] != RbacRoleEnum::ROLE_ADMIN) {
            // 获取权限城市
            $adminAuthLogic = new AdminAuthLogicModel();
            $citys = $adminAuthLogic->getAuthCitys();

            foreach ($cities as $key => $city) {
                if (!in_array($city["cid"], $citys)) {
                    unset($cities[$key]);
                }
            }

            if (empty($params["cs"])) {
                $params["kf_cs"] = $citys;
            } else if (!in_array($params["cs"], $citys)) {
                $params["kf_cs"] = "none";
            }
        }

        // 获取所有装中客服
        $adminuserLogic = new AdminuserLogicModel();
        $kfList = $adminuserLogic->getAdminuserByDeptId(DepartmentEnum::DEPARTMENT_YCZZ_ZZKF);
        $kfIds = array_column($kfList, "id");

        // 显号审核权限
        $show_apply_tel = false;
        if ($this->User["uid"] != RbacRoleEnum::ROLE_ZZKF_KF) {
            $show_apply_tel = check_user_menu_auth("/orderreviewnewapplytel/pass_apply_tel");
        }
        
        $review_types = $this->orderReviewNewLogic->reviewType;
        unset($review_types[OrderReviewNewLogicModel::REVIEW_TYPE_DDD_NUM]); //去除待定单

        /**新增默回访时间*/
        $params['start'] = !isset($params['start']) ? date('Y-m-d', strtotime("-30 days")) : $params['start'];
        $params['end'] = !isset($params['end']) ? date('Y-m-d') : $params['end'];
        // 获取所有
        $params["user_id"] = $this->User["id"];
        $data = $this->orderReviewNewLogic->getAll($params, $kfIds);

        $ret = [
            'params' => $params,
            'review_types' => $review_types,
            'review_remarks' => $this->orderReviewNewRemarkLogic->getAll(),
            'ret' => $data,
            'cities' => $cities,
            'opuser' => session('uc_userinfo'),
            'show_apply_tel' => $show_apply_tel,
            // 'kflist' => D("Adminuser")->getKfList(true),
            'kflist' => $kfList,
        ];

        $orderType = $this->orderReviewNewLogic->orderType;

        $this->assign($ret);
        $this->assign('orderType', $orderType);
        $this->display();
    }

    public function getOrderRemark()
    {
        try {
            $type = I('get.review_type') ?: '';
            $ret = $this->orderReviewNewRemarkLogic->getAll($type);
            $code = 0;
            $msg = 'ok';
            $data = $ret;
        } catch (Exception $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
            $data = [];
        }

        $this->ajaxReturn(['error_code' => $code, 'error_msg' => $msg, 'data' => $data]);
    }

    /**
     * 订单详细页面
     * @return void
     */
    public function operate()
    {
        //第一次通话结束--第三次通话开始的时间差
        //控制器
        $orderId = I("get.id");
        $isShow = I('get.is_show');

        $ordersController = new OrdersController();
        if (!empty($orderId)) {
            $this->assign('is_show', $isShow);
            $results = $this->orderReviewController->getVoipRecordList($orderId);
            foreach ($results as $result) {
                if ($result['action'] == "开始") {
                    $kaishi[] = $result;
                } elseif ($result['action'] == "挂断") {
                    $jieshu[] = $result;
                }
            }
            $countkaishi = count($kaishi);

            //新逻辑:拨号满两次且（当前时间-第一次结束通话）≥30分钟，才能正常申请显号，否则通过紧急入口
            if ($countkaishi >= 2) {
                $endtime = $jieshu[0]['time_add'];
                $time = time() - strtotime($endtime);
                $timemin = $time / 60;
                if ($timemin >= 30) {
                    $jinji = 0;
                } else {
                    $jinji = 1;
                }
            } else {
                $jinji = 1;
            }
            //查询订单信息
            $info = $this->orderReviewController->getOrderInfo($orderId);
            //过滤不规则字符串
            $reg = '/[\`~!@#$%^&*\(\)+<>?"{},\/;\'\"\s]/';
            $info["xiaoqu"] = preg_replace($reg, "", $info["xiaoqu"]);

            //查询是否是活动订单
            if (!empty($info['source'])) {
                $info["activity"] = D("Home/Logic/ActivityLogic")->getActivityInfo($info["source"]);
            }
            $this->assign("info", $info);
            $this->assign("source_in", $ordersController->source_in);
            //后台转发人数组
            $ids = D("Options")->getOptionNameCC("kf_admin_order_users");
            $names = D("User")->getAdminNamesById($ids['option_value']);
            foreach ($names as $k => $v) {
                $zhuanfaren[] = $v['name'];
            }
            $this->assign("zhuanfaren", $zhuanfaren);
            //获取订单城市和区县
            $city = D("Quyu")->getCityInfoById($info["cs"]);
            $this->assign("city", $city);
            //户型
            $huxing = D("Huxing")->gethx();
            //预算
            $yusuan = D("Jiage")->getJiage();
            //装修方式
            $fangshi = D("Fangshi")->getfs();
            //风格
            $fengge = D("Fengge")->getfg();
            //获取最后审核人信息
            $csos_new = D("OrderCsosNew")->getCsosInfo($orderId);

            // 未接通短信
            $logSmsSendLogic = new LogSmsSendLogicModel();
            $smsCount = $logSmsSendLogic->getOrderTypeDayCount($info["id"], LogSmsSendLogicModel::LOG_TYPE_HFWJT, date("Y-m-d"));

            //获取订单分配信息
            $company = D("OrderInfo")->getOrderComapny($info["id"]);

            //有分配订单的情况下，查询微信是否发送
            if (count($company) > 0) {
                //获取订单微信发送记录
                $wx = D("LogWxOrdersend")->getWeixinLog($info["id"]);
                if (count($wx) > 0) {
                    $this->assign("wxMark", 1);
                }
            }

            //获取城市信息模板
            $cityTmp = $this->orderReviewController->getCityInfoTmp($info["cs"]);

            //获取城市信息
            $quyu = D('Quyu')->getQuyuList();
            $this->assign('quyu', $quyu);

            //获取该订单所在城市的的会员公司
            $companyList = $this->orderReviewController->getCompanyList($info["cs"], $info["lng"], $info["lat"]);
            $this->assign('companyList', $companyList);
            $nowCompanys = $this->fetch("nowCompanys");
            $otherCompanys = $this->fetch("otherCompanys");

            // 查询小区历史签单公司
            $history = $this->orderReviewController->orderHistory($info["xiaoqu"], $info['cs']);
            if (count($history) > 0) {
                $this->assign("history", $history);
            }

            // 获取最近30天过期的会员信息
            $lostCompany = $this->orderReviewController->getLastExpireCompanyList($info["cs"], date("Y-m-d"));

            // 获取订单IP是否重复
            $ipCount = D("Orders")->getIpRepaetCountByIds(array($info["id"]));

            if ($ipCount[0]["repeat_count"] > 1) {
                $this->assign("ipMark", $ipCount[0]["repeat_count"] - 1);
            }

            // 获取所有装中客服
            $adminuserLogic = new AdminuserLogicModel();
            $kfList = $adminuserLogic->getAdminuserByDeptId(DepartmentEnum::DEPARTMENT_YCZZ_ZZKF);
            $kfIds = array_column($kfList, "id");

            // 获取回访订单数据
            $uid = $this->User['id'];
            $reviewOrder = $this->orderReviewNewLogic->getDetail($uid, $orderId, $kfIds);
            $offset = 1;

            $reviewTypes = array_slice($this->orderReviewNewLogic->reviewType, $offset, null, true);
            unset($reviewTypes[OrderReviewNewLogicModel::REVIEW_TYPE_DDD_NUM]); //去除待定单

            $YCL_NUM = OrderReviewNewLogicModel::REVIEW_TYPE_YCL_NUM;
            $XD_NUM = OrderReviewNewLogicModel::REVIEW_TYPE_XD_NUM;
            if ($reviewOrder['review_type'] == $YCL_NUM) {
                $reviewTypes = [
                    $XD_NUM => $reviewTypes[$XD_NUM]
                ];
            } else { //预处理订单状态外的都 去掉 新单选项
                unset($reviewTypes[$XD_NUM]);
            }
            $this->assign('YCL_NUM', $YCL_NUM);
            $this->assign('XD_NUM', $XD_NUM);

            $this->assign('review_types', $reviewTypes);
            $this->assign('review_type_name', $reviewOrder['review_type']);
            $this->assign('review_remarks', $this->orderReviewNewRemarkLogic->getAll());
            $this->assign('next_visit_step', $this->orderReviewNewLogic->nextVisitStep);
            $this->assign("review_order", $reviewOrder);
            // 右侧显示
            $orderno = $orderId;
			// 获取分配公司信息
            $reviewLogic = new OrderReviewLogicModel();
            $company_fp = $reviewLogic->getOrderCompany($orderno);

            // 查询装修公司申请补轮情况
            $roundOrderApplyLogic = new RoundOrderApplyLogicModel();
            $comApplyList = $roundOrderApplyLogic->getOrderRoundApplyList($orderno);

			// 要求回访公司会员
            $reviewCompany = $reviewLogic->getReviewCompany($orderno);
			// 反馈详情
            $feedback = $reviewLogic->getReviewInfoByOrderId($orderno, 2);
			// 未量房原因
            $noLfReason = $reviewLogic->getNoLfReason($orderno);
            // 根据订单号获取当前城市的会员数量
            $vipCount = $reviewLogic->getVipUserCount($orderno);

            // 获取回访需求
            $companyVisitLogic = new CompanyVisitLogicModel();
            $companyReviewInfo = $companyVisitLogic->getOrderPassiveVisitInfo($orderno);//装修公司回访需求

            // 获取装修公司跟进情况
            $trackList = $companyVisitLogic->getComapnyTrackInfoList($orderno);

            // 获取已量房公司信息
            $lfCompany = $reviewLogic->getOrderLiangfangInfo($orderno);

            // 获取签单公司
            $qiandanCompany = $this->orderReviewNewLogic->getOrderQiandanCompany($orderno);
            
            // 获取订单标识
            $inLogic = new OrderSourceInLogicModel();
            $orderType = $inLogic->getSourceInSelect();
            $this->assign('orderType',$orderType);

            // 获取订单公司相关服务
            $companyReviewLogic = new OrderCompanyReviewLogicModel();
            $result = $companyReviewLogic->getReviewInfoByOrderId($orderno,"a.*,u.jc");
            $reviewList = [];
            if (count($result) > 0) {
                foreach ($result as $item) {
                    $reviewList["header"][] =  $item["jc"];
                    $item["fixup_special"] = empty($item["fixup_special"])?"暂无":$item["fixup_special"];
                    $item["company_discount"] = empty($item["company_discount"])?"暂无":$item["company_discount"];
                    $item["yz_worry"] = empty($item["yz_worry"])?"暂无":$item["yz_worry"];
                    $reviewList["body"][] = "<b>装修特色</b><span style='color: #c4c4c4;'>（主要说明主做楼盘、售后服务保障等）</span><br/>".$item["fixup_special"] ."<br/><b>装企优惠</b><span style='color: #c4c4c4;'>（主要说明可使用的活动以及价格说明）</span><br/>". $item["company_discount"] ."<br/><b>业主顾虑点</b><span style='color: #c4c4c4;'>（简述该业主装修的顾虑点以及其他特殊说明）</span><br/>" . $item["yz_worry"];
                    $reviewList["comid"][]= $item['comid'];
                }
            }

            // 装企操作记录
            $reviewLogList = $companyReviewLogic->getOrderCompanyReviewLog($orderno);

            $this->assign("reviewLogList", $reviewLogList);
            $this->assign("reviewList", $reviewList);
            $this->assign("lfCompany", $lfCompany);
            $this->assign("trackList", $trackList);
            $this->assign("companyReviewInfo", $companyReviewInfo);
            $this->assign("vipCount", $vipCount);
            $this->assign("company_fp", $company_fp);
            $this->assign("comApplyList", $comApplyList);
            $this->assign("reviewCompany", $reviewCompany);
            $this->assign("feedback", $feedback['feedback']);
            $this->assign("noLfReason", $noLfReason);

            $this->assign("jinji", $jinji);
            $this->assign("timemin", $timemin);
            $this->assign("lostCompany", $lostCompany);
            $this->assign("company", $company);
            $this->assign("smsCount", $smsCount);
            $this->assign("nowCompanys", $nowCompanys);
            $this->assign("otherCompanys", $otherCompanys);
            $this->assign("csos_new", $csos_new);
            $this->assign("status", $ordersController->status);
            $this->assign("keys", $ordersController->keys);
            $this->assign("lf_time", $ordersController->lf_time);
            $this->assign("start_time", $ordersController->start_time);
            $this->assign("shi", $ordersController->shi);
            $this->assign("lxs", $ordersController->lxs);
            $this->assign("fengge", $fengge);
            $this->assign("fangshi", $fangshi);
            $this->assign("yusuan", $yusuan);
            $this->assign("huxing", $huxing);
            $this->assign("cityTmp", $cityTmp);
            $this->assign('qiandanCompany', $qiandanCompany);

            $tmp = $this->fetch("operate");
            $this->ajaxReturn(array('data' => $tmp, 'info' => $info, 'status' => 1));
        }
    }

    public function setReviewCompanyInfo()
    {
        $data = I('post.');
        if (empty($data['company_id'])) {
            $this->ajaxReturn(['error_code' => ApiConfig::PARAMETER_MISSING, 'error_msg' => '缺少装修公司id']);
        }
        $companyLogic = new OrderReviewCompanyLogicModel();
        $status = $companyLogic->setReviewCompany($data, $this->User);
        if ($status) {
            $this->ajaxReturn(['error_code' => 0, 'error_msg' => '更新成功']);
        } else {
            $this->ajaxReturn(['error_code' => 400, 'error_msg' => '操作失败']);
        }
    }

    public function getReviewCompanyInfo()
    {
        $company_id = I('get.company_id');
        if (empty($company_id)) {
            $this->ajaxReturn(['error_code' => ApiConfig::PARAMETER_MISSING, 'error_msg' => '缺少装修公司id']);
        }
        $companyLogic = new OrderReviewCompanyLogicModel();
        $info = $companyLogic->getReviewCompanyInfo($company_id);
        $this->ajaxReturn(['error_code' => 0, 'error_msg' => '', 'info' => $info]);
    }

    public function update()
    {
        $params = I('post.');
        
        try {
            if (empty($params['order_id']) || empty($params['review_type'])) {
                throw new Exception('参数有误', 400);
            }

            // 非新单回访记录必填
            if ($params['review_type'] != 2 && empty($params['review_record'])) {
                throw new Exception('参数有误', 400);
            }

            //回访状态:1.预处理 2.新单 3.已量房 4.未量房 5.已签单 6.待定单 7.终结单
            if (!in_array($params['review_type'], [5, 7]) && empty($params['next_visit_time'])) {
                throw new Exception('请选择下次回访时间', 400);
            }
            //当发生新单和待定单外的订单状态变更时，订单备注必填
            if (isset($params['review_type']) && !in_array($params['review_type'], [2, 6]) && empty($params['review_record'])) {
                throw new Exception('参数有误', 400);
            }

            //新单和待定单没有remark_type
            if (in_array($params['review_type'], [6])) {
                $params['remark_type'] = 0;
            }

            $data = $this->orderReviewNewLogic->update($this->uid, $params, true);

            $code = 0;
            $msg = 'ok';

            // 发送消息给客服
            if(!empty($data)){
                $adminuserLogicModel = new AdminuserLogicModel();
                $saleDepartId = DepartmentEnum::DEPARTMENT_SALE_CENTER;
                $sales = $adminuserLogicModel->getAdminUserByDepartment($saleDepartId, 'id,uid,user', $data["cs"]);
                $saleIds = array_column($sales, 'id');

                $urlParam = '?order_id='.$params['order_id'];

                if($params['review_type'] == OrderReviewNewLogicModel::REVIEW_TYPE_YLF_NUM && $params['remark_type'] == 10){
                    $msgServiceModel = new MsgServiceModel();
                    $res = $msgServiceModel->sendSystemMsg("202009101021", $saleIds, $urlParam, $params['order_id'], '新回访', []);
                }

                if($params['review_type'] == OrderReviewNewLogicModel::REVIEW_TYPE_YQD_NUM && $params['remark_type'] == 17){
                    $msgServiceModel = new MsgServiceModel();
                    $res = $msgServiceModel->sendSystemMsg("202009101022", $saleIds, $urlParam, $params['order_id'], '新回访', []);
                }
            }
        } catch (Exception $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
        }
        $ret = [
            'error_code' => $code, 'error_msg' => $msg,
        ];
        $this->ajaxReturn($ret);
    }

    public function sendBlockCallSms()
    {
        $id = I('post.id');
        $ordersLogic = new OrdersLogicModel();
        $info = $ordersLogic->getInfoByOrderId($id, 'id,on,source_in');
        if (empty($info)) {
            $this->ajaxReturn(['error_code' => 400, 'error_msg' => '未查询到订单数据!']);
        }

        // 获取手机号码
        $telInfo = $ordersLogic->getOrderRealTel($id);

        // 发送短信频率：1天最多1次，次日0点清零重计
        $today = date("Y-m-d");
        $logSmsSendLogic = new LogSmsSendLogicModel();
        $count = $logSmsSendLogic->getOrderTypeDayCount($info['id'], LogSmsSendLogicModel::LOG_TYPE_HFWJT, $today);
        if ($count == 0) {
            $smsServiceModel = new SmsServiceModel();

            switch ($info['source_in']){
                case 26:
                    //装小七指定短信模板
                    $ret = $smsServiceModel->sendSms("202007311047", $telInfo['tel8']);
                    break;
                default:
                    $ret = $smsServiceModel->sendSms("202007071042", $telInfo['tel8']);
            }

            if (isset($ret["error_code"]) && $ret["error_code"] == 0) {
                import('Library.Org.Util.App');
                $app = new \App();

                $tel_encrypt = substr_replace($telInfo['tel8'], "*****", 3, 5); //做一个中间为星号的号码
                $tel_md5 = $app->order_tel_encrypt($telInfo['tel8']); //做一个加密后的号码

                // 短信发送成功增加日志
                $logSmsSendLogic->addLog([
                    "type" => LogSmsSendLogicModel::LOG_TYPE_HFWJT,
                    "op_id" => $this->User["id"],
                    "op_user" => $this->User["name"],
                    "orderid" => $info['id'],
                    "addtime" => date("Y-m-d H:i:s"),
                    "ip" => $app->get_client_ip(),
                    "tel_encrypt" => $tel_md5,
                    "tel" => $tel_encrypt,
                ]);
                $this->ajaxReturn(['error_code' => 0, 'error_msg' => '发送成功']);
            }

            $this->ajaxReturn(['error_code' => 400, 'error_msg' => '短信发送失败']);
        } else {
            $this->ajaxReturn(['error_code' => 400, 'error_msg' => '已发送过短信!']);
        }
    }

    // 新回访通话记录
    public function voiprecord() {
        $order_id = I('get.id');

        // 获取所有装中客服
        $adminuserLogic = new AdminuserLogicModel();
        $kfList = $adminuserLogic->getAdminuserByDeptId(DepartmentEnum::DEPARTMENT_YCZZ_ZZKF);
        $kfIds = array_column($kfList, "id");

        $logTelcenterLogic = new LogTelcenterOrdercallLogicModel();
        $list = $logTelcenterLogic->getReviewOrderCallList($order_id, $kfIds);

        $this->assign("list", $list);

        $tmp = $this->fetch("Order/tel_history");
        $this->ajaxReturn(array("status" => 1, "data" => $tmp));
    }
}