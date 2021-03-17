<?php
// +----------------------------------------------------------------------
// | OrderLiangFangLogicModel  添加量房数据
// +----------------------------------------------------------------------

namespace Home\Model\Logic;


class OrderLiangFangLogicModel
{
    public function addOrderLiangFang($order_id, $companys)
    {
        if (count($companys) > 0) {
            $reviewList = [];
            //添加量房记录
            foreach ($companys as $key => $value) {
                $reviewList[] = array(
                    "orderid" => $order_id,
                    "comid" => $value["compnay_id"],
                    "status" => 0,
                    "time" => 0,
                    "reason" => "",
                    "remark" => "",
                    "lianxi" => 2,
                    "liangfang" => 2,
                    "lf_time" => 0,
                );
            }

            //1.查询分配公司历史订单反馈记录
            $field = 'a.orderid,a.comid,a.status,a.time,a.reason,a.remark,a.lianxi,a.liangfang,a.lf_time';
            $result = D("Home/Logic/OrderCompanyReviewLogic")->getReviewInfoByOrderId($order_id,$field);
            if (count($result) > 0) {
                //如果已有订单反馈记录
                foreach ($reviewList as $key => $value) {
                    foreach ($result as $k => $val) {
                        if ($val["comid"] == $value["comid"]) {
                            $reviewList[$key] = $val;
                        }else{
                            if(!in_array($val['comid'],array_column($reviewList,'comid'))){
                                $reviewList[] = $val;
                            }
                        }
                    }
                }
            }

            //2.删除之前的订单反馈记录
            D("Home/Logic/OrderCompanyReviewLogic")->delReviewInfoByOrderId($order_id);
            //3.添加反馈记录
            return D("Home/Logic/OrderCompanyReviewLogic")->addReviewInfo($reviewList);
        }
    }
}