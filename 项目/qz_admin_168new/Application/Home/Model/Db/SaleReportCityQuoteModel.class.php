<?php

namespace Home\Model\Db;

use Think\Model;

class SaleReportCityQuoteModel extends Model {

    public function getCityLastQuote($city_name) {
        $map = array(
            "is_delete" => array("EQ", 1),
            "city_name" => array("EQ", $city_name)
        );

        return $this->where($map)->order("id desc")->find();
    }

    public function getCityUserOrderLimit($city_name) {
        $map = array(
            "is_delete" => array("EQ", 1),
            "city_name" => array("EQ", $city_name)
        );

        return $this->where($map)
            ->field("id,city_name,user_order_limit")
            ->order("id desc")
            ->find();
    }
}