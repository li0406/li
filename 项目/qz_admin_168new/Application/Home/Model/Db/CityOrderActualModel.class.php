<?php

namespace Home\Model\Db;

use Think\Model;

class CityOrderActualModel extends Model {

    public function getInfo($city_id, $datetime){
        $map = array(
            "city_id" => array("EQ", $city_id),
            "datetime" => array("EQ", $datetime)
        );

        return $this->where($map)->find();
    }

    public function getCityMonthList($datetime){
        $map = array(
            "datetime" => array("EQ", $datetime)
        );

        return $this->where($map)
            ->field(["city_id", "datetime", "actual_orders"])
            ->select();
    }

    // 设置
    public function updateInfo($id, $data){
        $map = array(
            "id" => array("EQ", $id),
        );

        return $this->where($map)->save($data);
    }

    // 添加日志
    public function addLog($data){
        return M("city_order_actual_log")->add($data);
    }

    // 日志记录
    public function getLogList($city_id, $datetime){
        $map = array(
            "a.city_id" => array("EQ", $city_id),
            "a.datetime" => array("EQ", $datetime)
        );

        return M("city_order_actual_log")->alias("a")->where($map)
            ->join("left join qz_adminuser as u on u.id = a.operator")
            ->field(["a.*", "u.`user` as operator_name"])
            ->order("a.created_at desc,a.id desc")
            ->select();
    }

}