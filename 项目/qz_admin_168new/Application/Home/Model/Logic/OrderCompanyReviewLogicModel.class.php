<?php

namespace Home\Model\Logic;

use Home\Model\Db\OrderCompanyReviewModel;
use Home\Model\Db\OrderReviewNewHistoryModel;
use Home\Model\Db\LogUserOrderChangeModel;
use Common\Enums\OrderStatus as OrderStatusEnum;

/**
 * 装修订单反馈表
 */
class OrderCompanyReviewLogicModel
{
    /**
     * 根据订单信息获取反馈信息
     * @param  [type] $order_id [description]
     * @return [type]           [description]
     */
    public function getReviewInfoByOrderId($order_id,$field = '*')
    {
        return D("Home/Db/OrderCompanyReview")->getReviewInfoByOrderId($order_id,$field);
    }

    /**
     * 删除订单反馈信息
     * @param  [type] $order_id [description]
     * @return [type]           [description]
     */
    public function delReviewInfoByOrderId($order_id)
    {
       return D("Home/Db/OrderCompanyReview")->delReviewInfoByOrderId($order_id);
    }

    public function delReviewInfoByOrderIdAndCompanyId($company,$order_id)
    {
        return D("Home/Db/OrderCompanyReview")->delReviewInfoByOrderIdAndCompanyId($company,$order_id);
    }

    /**
     * 添加订单反馈信息
     * @param [type] $data [description]
     */
    public function addReviewInfo($data)
    {
        return D("Home/Db/OrderCompanyReview")->addAllInfo($data);
    }

    /**
     * 添加订单反馈信息
     * @param [type] $data [description]
     */
    public function editReviewInfo($order_id,$company_id,$data)
    {
        return D("Home/Db/OrderCompanyReview")->editReviewInfo($order_id,$company_id,$data);
    }

    public function getOrderLiangfangStatus($order_id)
    {
        $model = new OrderCompanyReviewModel();
        $result = $model->getReviewInfoByOrderId($order_id);
        $status = 0;
        $unknow = 0;
        foreach ($result as $item) {
            if (in_array($item["status"],array(1,2,4))) {
                $status = 2;
                break;
            }
            //未量房的数量
            if ($item["status"] == 3) {
                $unknow ++;
            }
        }

        //没有标记量房的状态下，未量房的数量大于
        if ($status != 2 && $unknow >= (count($result) - 1)) {
            $status = 1;
        }

        return $status;
    }

    public function getPolicyReviwInfo($order_id)
    {
        $model = new OrderReviewNewHistoryModel();
        $info = $model->getLastReviewInfo($order_id);
        return $info;
    }

    // 查询订单公司量房操作记录
    public function getOrderCompanyReviewLog($order_id){
        $changeModel = new LogUserOrderChangeModel();
        $list = $changeModel->getOrderChange($order_id);

        $allStatus = OrderStatusEnum::getAllStatus2();
        foreach ($list as $key => $item) {
            $list[$key]["status_name"] = $allStatus[$item["status"]];
            $list[$key]["created_date"] = date("Y-m-d", $item["track_time"]);
        }

        return $list;
    }
}