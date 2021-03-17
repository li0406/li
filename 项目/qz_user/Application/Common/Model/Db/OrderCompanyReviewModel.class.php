<?php
/**
 * 装修公司回访记录表
 */

namespace Common\Model\Db;

use Think\Model;

class OrderCompanyReviewModel extends Model
{
    protected $tableName = "order_company_review";

    /**
     * 添加跟踪信息
     */
    public function addReview($data)
    {
        return M("order_company_review")->add($data);
    }

    /**
     * 编辑跟踪信息
     * @param  [type] $id   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function editReview($order_id, $company_id,$data)
    {
        $map = array(
            "orderid" => array("EQ", $order_id),
            "comid" => array("EQ", $company_id)
        );
        return M("order_company_review")->where($map)->save($data);
    }

    public function replaceReview($data){
        return M("order_company_review")->add($data,[],true);
    }
    /**
     * 根据订单编号获取
     * @param  [type] $order_id [description]
     * @return [type]           [description]
     */
    public function getReviewInfo($order_id, $company_id)
    {
        $map = array(
            "orderid" => array("EQ", $order_id),
            "comid" => array("EQ", $company_id)
        );
        return M("order_company_review")->where($map)->find();
    }


}