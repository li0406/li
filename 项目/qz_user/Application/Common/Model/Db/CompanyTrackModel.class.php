<?php

namespace Common\Model\Db;

use Think\Model;

class CompanyTrackModel extends Model {

    public function getById($id){
        return $this->where(["id" => $id])->find();
    }

    /**
     * 根据订单和装修公司联合ID查询记录
     * @param $company_id
     * @param $order_id
     * @return mixed
     */
    public function getAllByUnique($company_id, $order_id){
        $map = array(
            "company_id" => array("EQ", $company_id)
        );

        if (is_array($order_id)) {
            $map["order_id"] = array("IN", $order_id);
        } else {
            $map["order_id"] = array("EQ", $order_id);
        }

        return $this->where($map)->order("track_time desc,id desc")->select();
    }

    /**
     * 获取最新一条跟单小计信息
     * @param  [type] $company_id [description]
     * @param  [type] $order_id   [description]
     * @return [type]             [description]
     */
    public function getLastTrack($company_id, $order_id){
        $map = array(
            "company_id" => array("EQ", $company_id),
            "order_id" => array("EQ", $order_id),
        );

        return $this->where($map)->order("track_time desc,id desc")->find();
    }

    /**
     * 删除跟单小计信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function deleteInfo($id){
        $map = array(
            "id" => array("EQ", $id),
        );

        return $this->where($map)->delete();
    }

}