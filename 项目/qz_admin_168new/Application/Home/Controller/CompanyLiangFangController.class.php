<?php

namespace Home\Controller;

use Common\Enums\LiangFangInfo;
use Common\Enums\OrdersInfo;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\CompanyLiangfangLogicModel;
use Home\Model\Logic\OrderSourceLogicModel;

class CompanyLiangFangController extends HomeBaseController
{
    public function index()
    {
        $admin = getAdminUser();
        $allCount = D('Home/Logic/CompanyLiangfangLogic')->getNotLfListCount(I('get.'));//数据总数
        $list = D('Home/Logic/CompanyLiangfangLogic')->getNotLfList(I('get.'),$allCount['count']);
        //获取订单归属人
        $operaters = D('Adminuser')->getLiangfangKfManagerList($admin['id'], $admin['uid']);
        $this->assign("backstatus", LiangFangInfo::getBackStatus());
        $this->assign("backremarkbust", json_encode(LiangFangInfo::getBackRemarkBust()));
        $this->assign("backremarkfalse", json_encode(LiangFangInfo::getBackRemarkFalse()));
        //判断是否有查看呼叫记录的权限
        if (check_menu_auth('/voip/voiprecord/') == true) {
            $this->assign('checkcall',1);
        }
        //获取城市信息
        $this->assign("city", D('Quyu')->getQuyuList());
        $this->assign('list',$list);
        $this->assign('allCount',$allCount);
        $this->assign('operaters',$operaters['list']);
        $this->display();
    }

    public function operate()
    {
        if (IS_POST) {
            //查询订单信息
            $info = D('Home/Logic/OrdersLogic')->getOrderInfo(I("post.id"));
            //生成手机访问的短网址
            $orderdwz = url_getdwz($info['id']);
            $info['dwz'] = $orderdwz;
            $this->assign("info", $info);
            $this->assign("source_in",OrdersInfo::getSourceIn());
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
            //获取 未接通电话短信通知 短信记录
            $logCount = D("LogSmsSend")->getOrderSendLogCount($info["id"], 2);
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
            //如果是已分配公司,默认选中
            foreach ($company as $key => $value) {
                $compnay_id[$value["id"]] = $value["id"];
            }
            //查询上次分配装修公司
            $fenpei_company = D("OrderInfo")->getLastTypeFw($info["id"], $info["cs"]);

            //本地装修公司
            foreach ($fenpei_company as $k => $val) {
                if ($val["cs"] == $info["cs"]) {
                    $fenpei_now_company[] = $val;
                    unset($fenpei_company[$k]);
                }
            }
            //查询小区历史签单公司
            $history = D("Home/Logic/CompanyLiangfangLogic")->orderHistory($info["xiaoqu"], $info['cs']);
            if (count($history) > 0) {
                $this->assign("history", $history);
            }

            //获取订单IP是否重复
            $ipCount = D("Orders")->getIpRepaetCountByIds(array($info["id"]));

            if ($ipCount[0]["repeat_count"] > 0) {
                $this->assign("ipMark", $ipCount[0]["repeat_count"]);
            }

            /**回访操作S**/
            //回访数据
            $backInfo = D('Home/Logic/CompanyLiangfangLogic')->getNotLfList(['condition' => I('post.id'), 'is_ajax' => 1]);
            //未量房原因
            $reason = D('Home/Logic/CompanyLiangfangLogic')->getNotLfReason(I('post.id'));
            //回访装修公司信息
            $companyInfo = D('Home/Logic/CompanyLiangfangLogic')->getNotLfCompanyList(I('post.id'));
            //获取当前订单推送数据
            $pushCompanyInfo = D('Home/Logic/CompanyLiangfangLogic')->getPushCompanyInfo(I('post.id'));
            $this->assign('back_info',$backInfo['list']);
            $this->assign('reason',$reason);
            $this->assign('back_company',$companyInfo);
            if (empty($pushCompanyInfo)) {
                $this->assign("push_status", 0);
            } else {
                $this->assign("push_status", 1);
            }
            $this->assign('push_company',$pushCompanyInfo);
            /**回访操作E**/
            $this->assign("company", $company);
            $this->assign("logCount", $logCount);
            $this->assign("keys", OrdersInfo::getKeys());
            $this->assign("lf_time", OrdersInfo::getLfTime());
            $this->assign("start_time", OrdersInfo::getStartTime());
            $this->assign("shi", OrdersInfo::getShi());
            $this->assign("lxs", OrdersInfo::getLxs());
            $this->assign("fengge", $fengge);
            $this->assign("fangshi", $fangshi);
            $this->assign("yusuan", $yusuan);
            $this->assign("huxing", $huxing);
            $this->assign("backstatus", LiangFangInfo::getBackStatus());
            $this->assign("backremarkbust", json_encode(LiangFangInfo::getBackRemarkBust()));
            $this->assign("backremarkfalse", json_encode(LiangFangInfo::getBackRemarkFalse()));
            $tmp = $this->fetch("operate");
            $this->ajaxReturn(array("code" => 200, "data" => $tmp, 'info' => $info));
        }
    }

    /**
     * 保存
     */
    public function saveBackInfo(){
        $status = D('Home/Logic/CompanyLiangfangLogic')->saveBackInfo(I('post.'));
        if($status){
            //更改订单的待定时间
            D('Home/Logic/OrdersLogic')->updateOrderInfo(I('post.'));
            //添加操作日志
            D('Home/Logic/LogAdminLogic')->addLog('更新后:'.$status,'updateliangfang',I('post.'),'');
            D('Home/Logic/OrdersLogic')->updateOrderInfo(I('post.'));
            $this->ajaxReturn(['status'=>1,'info'=>'保存成功']);
        }else{
            $this->ajaxReturn(['status'=>0,'info'=>'保存失败! 刷新后再试']);
        }
    }

    /**
     * 推送
     */
    public function pushCompanyInfo(){
        $status = D('Home/Logic/CompanyLiangfangLogic')->pushCompanyInfo(I('post.'));
        if($status){
            //添加操作日志
            D('Home/Logic/LogAdminLogic')->addLog('推送回访操作:'.$status,'updateliangfangcorrelation',I('post.'),'');
            $this->ajaxReturn(['status'=>1,'info'=>'推送成功']);
        }else{
            $this->ajaxReturn(['status'=>0,'info'=>'推送失败! 刷新后再试']);
        }
    }

    /**
     * 量房统计
     */
    public function statistics(){
        $orderSourceLogic = new OrderSourceLogicModel();
        //渠道来源组
        $group = $orderSourceLogic->getSourceGroupList();
        //渠道来源
        $src = $orderSourceLogic->getSrcByGroup(I('get.'));
        //部门
        $department = $orderSourceLogic->getDepartmentList();
        $liangfangLogic = new CompanyLiangfangLogicModel();
        $list = $liangfangLogic->getOrdersLiangfang(I('get.'));
        $this->assign('department', $department);
        $this->assign('group', $group);
        $this->assign('src', $src);
        $this->assign('info', $list['list']);
        $this->assign('page', $list['page']);
        $this->display();
    }

    //根据渠道组取渠道
    public function ajaxSrcList()
    {
        $orderSourceLogic = new OrderSourceLogicModel();
        $list =$orderSourceLogic->getSrcByGroup(I('get.'));
        $this->ajaxReturn(['status' => 1, 'info' => $list]);
    }
}
