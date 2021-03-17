<?php
namespace Home\Model\Db;
use Think\Model;

class QcItemsScoreModel extends Model
{
    public function addQcScoreItem($data)
    {
        return M("qc_info_item_score")->addAll($data);
    }

    public function getQcItem($type = 1)
    {
        $map = array(
            "type" => array("EQ",$type),
            "status" => array("EQ",1)
        );

        return M("qc_items_score")->where($map)->order("parentid,px")->field("id,name,score,score2,score3,score4,score5,score6,parentid,group")->select();
    }

    public function getOrderScoreItem($order_id,$type)
    {
        $map = array(
            "type" => array("EQ",$type),
            "order_id" => array("EQ",$order_id)
        );

        return M("qc_info_item_score")->where($map)->field("qc_item_id")->select();
    }

    /**
     * 删除质检评分项目
     * @param  [type] $ids [质检项目ID]
     * @param  [type] $id [订单编号]
     * @return [type]     [description]
     */
    public function delQcItemByIds($order_id,$ids)
    {
        $map = array(
            "qc_item_id" => array("IN",$ids),
            "order_id" => array("EQ",$order_id)
        );
        return M("qc_info_item_score")->where($map)->delete();
    }


    public function getQcInfoById($order_id)
    {
        $map = array(
            "order_id" => array("EQ",$order_id)
        );
        return M("qc_info_item_score")->where($map)->field("qc_item_id,score")->select();
    }
}
