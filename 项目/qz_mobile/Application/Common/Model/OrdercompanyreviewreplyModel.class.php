<?php
/**
 * 装修公司回访记录回复表
 */
namespace Common\Model;
use Think\Model;
class OrdercompanyreviewreplyModel extends Model{
    protected $tableName ="order_company_review_reply";

    /**
     * 获取回复的记录列表
     * @param  [type] $orderid [订单编号]
     * @param  [type] $comid   [公司编号]
     * @return [type]          [description]
     */
    public function getReviewReply($orderid,$comid){
        $map = array(
                "orderid"=>array("EQ",$orderid),
                "comid"=>array("EQ",$comid)
                     );
        return M("order_company_review_reply")->where($map)->order("id")->select();
    }
}