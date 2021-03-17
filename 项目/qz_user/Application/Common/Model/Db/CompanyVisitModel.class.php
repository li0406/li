<?php

namespace Common\Model\Db;

use Think\Model;

class CompanyVisitModel extends Model {

    /**
     * 获取装修公司订单未读回访记录
     * @param $company_id
     * @param array $orderIds
     */
    public function getNoRead($company_id, array $orderIds){
        $map = array(
            "v.order_id" => array("IN", $orderIds),
            "r.company_id" => array("EQ", $company_id),
            "r.related_push" => array("EQ", 1),
            "r.related_push_isread" => array("EQ", 0),
        );

        return $this->alias("v")->where($map)
            ->join("inner join qz_company_visit_related as r on r.visit_id = v.id")
            ->field("v.order_id,count(v.id) as noread_count")
            ->group("v.order_id")
            ->select();
    }

    /**
     * 根据订单和装修公司联合ID查询记录
     * @param $company_id
     * @param $order_id
     * @return mixed
     */
    public function getAllByUnique($company_id, $order_id){
        $map = array(
            "r.company_id" => array("EQ", $company_id),
            "r.related_push" => array("EQ", 1)
        );

        if (is_array($order_id)) {
            $map["v.order_id"] = array("IN", $order_id);
        } else {
            $map["v.order_id"] = array("EQ", $order_id);
        }

        return $this->alias("v")->where($map)
            ->join("inner join qz_company_visit_related as r on r.visit_id = v.id")
            ->field("v.*")->order("v.visit_time desc")
            ->group("v.id")->select();
    }

    /**
     * 修改装修公司回访单已读状态
     * @param [type] $company_id [description]
     * @param [type] $visitIds   [description]
     */
    public function setCompanyVisitRead($company_id, $visitIds){
        $map = array(
            "visit_id" => array("IN", $visitIds),
            "company_id" => array("EQ", $company_id),
            "related_push_isread" => array("EQ", 0),
            "related_push" => array("EQ", 1),
        );

        $data = array(
            "related_push_isread" => 1,
            "updated_at" => time()
        );
        
        return M("company_visit_related")->where($map)->save($data);
    }

}