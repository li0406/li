<?php
/**
 *  装修订单反馈表
 */
namespace Common\Model;
use Think\Model;
class OrderfeedbackModel extends Model{
    protected $tableName = 'order_feedback';

    /**
     * 保存回馈信息
     * @param [type] $data [description]
     */
    public function setOrderFeed($data){
        return M("order_feedback")->add($data);
    }

    /**
     * 获取参与反馈的人数信息
     * @return [type] [description]
     */
    public function getFeedbackNum(){
        return M("order_feedback")->field("count(id) as `all`,count(if(feedback =1,1,null)) as yes,count(if(feedback <> 1,1,null)) as no")
                                  ->find();
    }
}