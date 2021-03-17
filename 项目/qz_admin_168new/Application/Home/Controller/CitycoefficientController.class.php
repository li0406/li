<?php

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

/**
*
*/
class CitycoefficientController extends HomeBaseController
{
    public function index()
    {
        //获取全年的月份
        for ($i =1; $i <= 12 ; $i++) {
            $months[date("Y").sprintf("%02d", $i)] = date("Y").sprintf("%02d", $i);
        }
        $this->assign("months",$months);
        //获取所有有会员的城市
        $citys = D("Quyu")->getOpenCityInfo();
        $this->assign("citys",$citys);
        if (I("get.city") !== "") {
            $city = array_filter(explode(",",I("get.city")));
            $this->assign("city",array_flip($city));
        }
        $list = $this->getCoefficientList(date("Y"),$city);
        $this->assign("list",$list);
        $this->display();
    }

    public function getCoefficientList($date,$cs)
    {
        $result = D("CityCoefficient")->getCoefficientList($date,$cs);

        foreach ($result as $key => $value) {
            $cityId = $value["city_id"];
            if (!array_key_exists($value["city_id"], $list)) {
                $list[$value["city_id"]]["city_name"] = $value["city_name"];
            }
            unset($value["city_name"]);
            unset($value["city_id"]);
            foreach ($value as $k => $val) {
                $exp = array_filter(explode("|", $val));
                $list[$cityId]["date"][$k]["day"] = $exp[0];
                $list[$cityId]["date"][$k]["night"] = $exp[1];
            }
        }
        return $list;
    }
}