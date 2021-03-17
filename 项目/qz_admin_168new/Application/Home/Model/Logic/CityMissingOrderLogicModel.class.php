<?php

namespace Home\Model\Logic;

class CityMissingOrderLogicModel
{
    public function getCityList($month,$city_id,$city_level,$whole_month,$is_mark)
    {
        //获取上个月最后一天
        $time = strtotime("-1 month",time());
        if (!empty($month)) {
            $time = strtotime($month."-01");
        }
        $lastDay = date("Y-m-d", mktime(0,0,0,date("m",$time),date("t",$time),date("Y",$time)));

        if ($is_mark == 1) {
            $is_end = 1;
        } elseif ($is_mark == 2) {
            $is_new = 1;
        }

        $result = D("Home/Db/CityMissingOrder")->getCityList($lastDay,$city_id,$city_level,$whole_month,$is_end,$is_new);

        foreach ($result as $key => $value) {
            if ($value["warnning"] == 1 && $value["isnew"] == 0 && $value["isend"] == 0) {
                $list[] = array(
                    "date" => date("Y-m",$time),
                    "city_id" => $value["city_id"],
                    "city_name" => $value["city_name"],
                    "city_level" => $value["city_level"],
                    "whole_month" => $value["whole_month"],
                    "order_count" => $value["order_count"],
                    "vip_count" => $value["vip_count"],
                    "missing_order" => $value["missing_order"]
                );
                $all_count +=  $value["missing_order"];
            } elseif ($value["isnew"] == 1 || $value["isend"] == 1) {
                $newList[] = array(
                    "city_id" => $value["city_id"],
                    "city_name" => $value["city_name"],
                    "city_level" => $value["city_level"],
                    "whole_month" => $value["whole_month"],
                    "order_count" => $value["order_count"],
                    "vip_count" => $value["vip_count"],
                    "date" => $value["start_time"],
                    "isnew" => $value["isnew"],
                    "isend" => $value["isend"]
                );
            }
        }

        return array("list" => $list,"newList" => $newList,"all_count" => $all_count);
    }


    public function getCityBasicList($date,$city_id,$level,$whole = "")
    {
        if (empty($date)) {
            $date = date("Y-m-d");
        }

        $date = strtotime($date);
        $monthStart = date("Y-m-d",mktime(0,0,0,date("m",$date),1,date("Y",$date)));
        $monthEnd = date("Y-m-d",mktime(23,59,59,date("m",$date),date("d",$date),date("Y",$date)));

        $result = D("Home/Db/CityMissingOrder")->getCityBasicList($monthStart,$monthEnd,$city_id,$level,$whole);

        foreach ($result as $key => $value) {
            $city[$value["city_id"]] = $value;
        }

        return $city;
    }

    public function getMissCityList($date)
    {
        $date = strtotime($date);
        $monthStart = date("Y-m-d",mktime(0,0,0,date("m",$date),1,date("Y",$date)));
        $monthEnd = date("Y-m-d",mktime(23,59,59,date("m",$date),date("d",$date),date("Y",$date)));
        $result = D("Home/Db/CityMissingOrder")->getMissCityList($monthStart,$monthEnd);

        foreach ($result as $key => $value) {
            $value["cname"] = strtoupper(mb_substr($value["px_abc"],0,1)) ." ". $value["cname"];
            $list[] = $value;
        }
        return $list;
    }
}
