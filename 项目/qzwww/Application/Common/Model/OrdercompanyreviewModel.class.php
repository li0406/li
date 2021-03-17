<?php
/**
 * 装修公司回访记录表
 */
namespace Common\Model;
use Think\Model;
class OrdercompanyreviewModel extends Model{
    protected $tableName ="order_company_review";

    /**
     * 添加回访记录
     */
    public function addReview($data){
        return M("order_company_review")->add($data);
    }

    /**
     * 获取订单的回访记录
     * @param  [type] $orderid [description]
     * @param  [type] $comid   [description]
     * @return [type]          [description]
     */
    public function gerOrderReviewList($orderid,$comid){
        $map = array(
                "orderid"=>array("EQ",$orderid),
                "comid"=>array("EQ",$comid)
                     );
        return M("order_company_review")->where($map)->order("id")->select();
    }

    /**
     * 获取回访记录的数量
     * @param  [type] $id    [description]
     * @param  [type] $comid [description]
     * @return [type]        [description]
     */
    public function getReviewCountById($id,$comid){
        $map = array(
                "id"=>array("EQ",$id),
                "comid"=>array("EQ",$comid)
                     );
        return M("order_company_review")->where($map)->count();
    }

    /**
     * 删除回访记录
     * @return [type] [description]
     */
    public function deleteReview($id,$comid){
        $map = array(
                "id"=>array("EQ",$id),
                "comid"=>array("EQ",$comid)
                     );
        return M("order_company_review")->where($map)->delete();
    }
}