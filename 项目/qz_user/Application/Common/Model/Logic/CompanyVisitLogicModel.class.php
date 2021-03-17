<?php

namespace Common\Model\Logic;

use Common\Model\Db\CompanyVisitModel;
use Common\Model\Db\OrderReviewNewPushModel;

class CompanyVisitLogicModel {

    private $visitStep = [
        "1" => "促量房",
        "2" => "促到店",
        "3" => "促签单",
        "4" => "推荐服务"
    ];

    private $reviewNewStep = [
        1 => '促量房',
        2 => '促到店',
        3 => '促签单',
        4 => '推荐服务',
    ];

    public function getVisitStepName($step){
        $step_name = "";
        if (array_key_exists($step, $this->visitStep)) {
            $step_name = $this->visitStep[$step];
        }

        return $step_name;
    }

    /**
     * 根据订单和装修公司联合ID查询记录
     * @param $company_id
     * @param $order_id
     * @return mixed
     */
    public function getAllByUnique($company_id, $order_id){
        $companyVisitModel = new CompanyVisitModel();
        $list = $companyVisitModel->getAllByUnique($company_id, $order_id);

        foreach ($list as $key => $item) {
            $list[$key]["visit_date"] = date("Y-m-d H:i:s", $item["visit_time"]);
            $list[$key]["visit_step_name"] = $this->getVisitStepName($item["visit_step"]);
        }

        //获取新回访推送
        $pushDb = new OrderReviewNewPushModel();
        $pushList = $pushDb->getOrderPushList($company_id, $order_id);
        foreach ($pushList as $k=>$v){
            $pushList[$k]['visit_date']= date('Y-m-d H:i:s',$v['created_at']);
            $pushList[$k]['visit_step_name']= $this->reviewNewStep[$v['visit_step']];
        }
        $list = array_merge($list,$pushList);
        return $list;
    }

    /**
     * 获取订单未读回访数量
     * @param $company_id
     * @param $orderIds
     */
    public function getOrderNoRead($company_id, $orderIds){
        if (is_string($orderIds)) {
            $orderIds = explode(",", $orderIds);
        }

        $companyVisitModel = new CompanyVisitModel();
        $noreadList = $companyVisitModel->getNoRead($company_id, $orderIds);
        if (!empty($noreadList)) {
            $noreadList = array_column($noreadList, "noread_count", "order_id");
        }

        return $noreadList;
    }

    /**
     * 修改装修公司回访单已读状态
     * @param [type] $company_id [description]
     * @param [type] $visitIds   [description]
     */
    public function setCompanyVisitRead($company_id, $visitIds){
        $result = false;
        if (count($visitIds) > 0) {
            $companyVisitModel = new CompanyVisitModel();
            $result = $companyVisitModel->setCompanyVisitRead($company_id, $visitIds);
        }
        
        return $result;
    }

    /**
     * 获取装修公司订单回访记录
     * 根据订单分组
     */
    public function getCompanyOrderVisit($company_id, $order_id){
        $companyVisitModel = new CompanyVisitModel();
        $list = $companyVisitModel->getAllByUnique($company_id, $order_id);

        $orderVisitList = [];
        foreach ($list as $key => $item) {
            $item["visit_date"] = date("Y-m-d H:i:s", $item["visit_time"]);
            $item["visit_step_name"] = $this->getVisitStepName($item["visit_step"]);
            $orderVisitList[$item["order_id"]][] = $item;
        }

        return $orderVisitList;
    }

}