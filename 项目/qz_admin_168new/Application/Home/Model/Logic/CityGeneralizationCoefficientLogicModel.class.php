<?php

namespace Home\Model\Logic;

use Home\Model\UserVipModel;

class CityGeneralizationCoefficientLogicModel
{
    public function getCityGeneralization($city,$date,$coefficient)
    {
        $result = D("Home/Db/CityGeneralizationCoefficient")->getCityGeneralization($date);
        foreach ($result as $key => $value) {
            if (array_key_exists($value["city_id"],$city)) {
                $city[$value["city_id"]]["coefficient"] = $value["coefficient"];
                $city[$value["city_id"]]["before_coefficient"] = $value["before_coefficient"];
                $city[$value["city_id"]]["now_coefficient"] = $value["now_coefficient"];
            }
        }
        if ($coefficient != "") {
            foreach ($city as $key => $value) {
                if ($value["coefficient"] != $coefficient) {
                    unset($city[$value["city_id"]]);
                }
            }
        }
        return $city;
    }

    public function getCityOrderList($city,$date)
    {
        if (empty($date)) {
            $date = date("Y-m-d");
        }
        $time = strtotime($date);
        $allDay = date("t",$time);
        $day = date("d",$time);
        $monthStart = date("Y-m-d", mktime(0,0,0,date("m",$time),1,date("Y",$time)));
        $monthEnd = date("Y-m-d", mktime(0,0,0,date("m",$time),date("t",$time),date("Y",$time)));
        //获取当月每个城市有会员的第一天的数量
        $result = D("Home/Db/LogUserRealCompany")->getCityFirstVipList($monthStart,$monthEnd);

        foreach ($result as $key => $value) {
            //如果数据开始时间不是1号开始的，默认是为0
            if (date("d", strtotime($value["time"])) >= 2) {
                $value["vip_num"] = 0;
                $value["vip_count"] = 0;
            }

            $list[$value["city_id"]] = $value;
        }
        if (I("get.debug") == "list3") {
            print_r($list);
        }

        //计算当月城市的会员数量
        //获取当月新增会员,获取当月过期会员,并折算天数
        $userVipModel = new UserVipModel();
        $result = $userVipModel->getCityContractList($monthStart,$monthEnd);

        $newList = [];
        foreach ($result as $key => $value) {
            if (!($value["start_time"] <= $monthStart && $value["end_time"] >= $monthEnd)) {
                if ($value["start_time"] >= $monthStart && $value["start_time"] <= $monthEnd) {
                    //计算折算天数
                    $offset = round(((strtotime($monthEnd) - strtotime($value["start_time"]))/86400+1)/$allDay,2);
                    //15号之后新增的会员
                    if (date("d", strtotime($value["start_time"])) > 15) {
                        $newList[$value["city_id"]]["vip_num"] += $offset*$value["viptype"];
                    } else {
                        //计算非整月新增会员
                        $list[$value["city_id"]]["vip_num"] += $offset*$value["viptype"];
                        $list[$value["city_id"]]["vip_count"] ++;
                    }
                } elseif($value["end_time"] <= $monthEnd) {
                    $list[$value["city_id"]]["vip_num"] -= $value["viptype"];
                    //计算非整月结束会员
                    //计算折算天数
                    $offset = round( ((strtotime($value["end_time"]) - strtotime($monthStart))/86400+1)/$allDay,2);
                    $list[$value["city_id"]]["vip_num"] += $offset*$value["viptype"];
                    if ($value["end_time"] <= $date) {
                        $list[$value["city_id"]]["end_vip"] ++;
                    } else {
                        $list[$value["city_id"]]["vip_count"] --;
                    }
                }
            }
        }

        //当月开始暂停的 按照过期时间来计算
        $result = $userVipModel->getCityPauseStartContractList($monthStart,$monthEnd);
        foreach ($result as $value) {
            $list[$value["city_id"]]["vip_num"] -= $value["viptype"];
            //计算非整月结束会员
            //计算折算天数
            $offset = round( ((strtotime($value["start_time"]) - strtotime($monthStart))/86400+1)/$allDay,2);
            $list[$value["city_id"]]["vip_num"] += $offset*$value["viptype"];
            $list[$value["city_id"]]["vip_count"] --;
        }

        //当月结束暂停的 上新会员的逻辑
        $result = $userVipModel->getCityPauseEndContractList($monthStart,$monthEnd);

        foreach ($result as $value) {
             //计算折算天数
             $offset = round(((strtotime($monthEnd) - strtotime($value["end_time"]))/86400+1)/$allDay,2);
             //计算非整月新增会员
             $list[$value["city_id"]]["vip_num"] += $offset*$value["viptype"];
             $list[$value["city_id"]]["vip_count"] ++;
        }

        if (I("get.debug") == "list") {
            print_r($list);
        }

        //获取城市前30天订单平均分几家

        $lastMonthEnd = strtotime(date("Y-m-d"));
        $lastMonthStart = strtotime("-30 day",$lastMonthEnd);
        $result = D("Home/OrderInfo")->getOrderCompanyList($lastMonthStart,$lastMonthEnd);

        foreach ($result as $key => $value) {
            $city_order_vip[$value["city_id"]] = round($value["order_all_count"]/$value["order_count"],2);
        }

        if (I("get.debug") == "last") {
            print_r($city_order_vip);
        }

        //获取当前城市会员数量
        foreach ($list as $key => $value) {
            if (array_key_exists($key,$city_order_vip)) {
                $list[$key]["city_order_avg"] = $city_order_vip[$value["city_id"]];
            } else {
                //如果没有数据则默认4家
                $list[$key]["city_order_avg"] = 1;
            }
        }

        foreach ($list as $key => $value) {
            switch ($value["city_level"]) {
                case 0:
                    # A类城市
                    $min = 13;
                    break;
                case 1:
                    # B类城市
                    $min = 9.5;
                    break;
                case 2:
                    # C类城市
                    $min = 6;
                    break;
                case 4:
                    # S1城市
                    $min = 22;
                    break;
                case 5:
                    # S2城市
                    $min = 32;
                    break;
            }
            //计算当月所需分单
            $list[$key]["demand_order"] = setInfNanToN($value["vip_num"]*$min/$value["city_order_avg"]);
            //新增分单量
            $list[$key]["new_order"] = setInfNanToN($newList[$key]["vip_num"]*$min/$value["city_order_avg"]);
            //理论平均订单 （每天的会员数（考虑倍数）/每天会员个数）*该城市理论最低值/当月自然天数
            $list[$key]["avg_theory_order"] = setInfNanToN(round(($value["vip_num"]/$value["vip_count"])*$min*$day/$allDay,2));
            //新增会员量
            $list[$key]["new_vip"] = round($newList[$key]["vip_num"],2);
            //平均分几家
            $list[$key]["city_order_avg"] = $value["city_order_avg"];
        }

        if (I("get.debug") == "list1") {
            print_r($list);
        }

        foreach ($city as $key => $value) {
            if (array_key_exists($key,$list)) {
                //所需发单
               $city[$key]["demand_order"] = round($list[$key]["demand_order"],2);
               $city[$key]["demand_order"] = $city[$key]["demand_order"]==0?0:$city[$key]["demand_order"];
               //当月新单
               $city[$key]["new_order"] = round($list[$key]["new_order"],2);
               //理论平均分单
               $city[$key]["avg_theory_order"] = setInfNanToN($list[$key]["avg_theory_order"]);
               //会员动态
               $city[$key]["end_vip"] = $list[$value["city_id"]]["end_vip"];
               //新增会员量
               $city[$key]["new_vip"] = $list[$value["city_id"]]["new_vip"];
               $city[$key]["city_order_avg"] = $list[$value["city_id"]]["city_order_avg"];
            }
        }

        return $city;
    }

    /**
     * 获取城市所需分单。有效分单、缺单量、缺单率、当前缺单、当前所需分单
     * @param  [type] $city    [description]
     * @param  [type] $date    [description]
     * @param  [type] $city_id [description]
     * @return [type]          [description]
     */
    public function getCityOrderNeed($city,$date)
    {
        $time = strtotime($date);
        $day = date("d",$time);
        $allDay = date("t",$time);
        $monthStart = mktime(0,0,0,date("m",$time),1,date("Y",$time));
        $monthEnd = mktime(23,59,59,date("m",$time),date("d",$time),date("Y",$time));

        foreach ($city as $key => $value) {
            $list[$value["city_id"]]["free_order"] = 0;
            $list[$value["city_id"]]["pay_order"] = 0;
        }

        //获取每天每个城市的免费、付费分单量
        $result = D("Home/OrderCsosNew")->getCityOrderList($monthStart,$monthEnd);

        foreach ($result as $key => $value) {
            $cityOrder[$value["city_id"]][$value["date"]]["free_order"] = $value["free_count"];
            $cityOrder[$value["city_id"]][$value["date"]]["pay_order"] = $value["pay_count"];
        }

        if (I("get.debug") == "pay") {
            dump($cityOrder);
        }

        //获取每个城市每天是否计算分单量
        $monthStart = date("Y-m-d",$monthStart);
        $monthEnd = date("Y-m-d",$monthEnd);
        $result = D("Home/Db/CityGeneralizationCoefficient")->getCityGeneralizationList($monthStart,$monthEnd);

        foreach ($result as $key => $value) {
            //免费分单量
            if ($value["warnning"] == 1) {
                $list[$value["city_id"]]["free_order"] +=  $cityOrder[$value["city_id"]][$value["date"]]["free_order"];
                $list[$value["city_id"]]["yx_order"] += $cityOrder[$value["city_id"]][$value["date"]]["free_order"];
            }

            //付费分单
            //mark表示前3天的重点系数有一天不是0
            if ($value["coefficient"] != 0 || ( $value["mark"] == 1 && $value["coefficient"] == 0) ) {
                $list[$value["city_id"]]["pay_order"] +=  $cityOrder[$value["city_id"]][$value["date"]]["pay_order"];
                 //有效分单
                $list[$value["city_id"]]["yx_order"] += $cityOrder[$value["city_id"]][$value["date"]]["pay_order"];
            }
        }
        if (I("get.debug") == "csos") {
            dump($list);
        }

        //合并数据
        foreach ($city as $key => $value) {
            //缺单量
            if ($value["demand_order"] > 0) {
                $city[$key]["miss_order"] = round($value["demand_order"] -  $city[$key]["real_fen_count"],2);
                //缺单大于0显示
                if ($city[$key]["miss_order"] > 0) {
                    //缺单率
                    $city[$key]["miss_order_rate"] = round(($city[$key]["miss_order"]/$city[$key]["demand_order"])*100,2);
                } else {
                    $city[$key]["miss_order"] = 0;
                }
            }

            //有效分单
            $city[$key]["yx_order"] = $list[$key]["yx_order"];

            //当前所需分单 （当月所需分单*（当前日期/当月自然天数))
            $city[$key]["now_demand_order"] = round($city[$key]["demand_order"] * ($day/$allDay),2);

            //当月平均缺单 平均分单（理论） - 平均分单（实际）
            $city[$key]["now_month_miss_order"] = round(setInfNanToN($city[$key]["avg_theory_order"] - $city[$key]["order_count"]),2);

        }
        return $city;
    }

    /**
     * 获取城市所需分单。有效分单、缺单量、缺单率
     * @param  [type] $city    [description]
     * @param  [type] $date    [description]
     * @param  [type] $city_id [description]
     * @return [type]          [description]
     */
    public function getCityOrderYxOrder($city,$date,$city_id,$begin,$end,$group,$src)
    {
        $time = strtotime($date);
        $allDay = date("t",$time);
        $monthStart =  mktime(12,0,0,date("m",$time),1,date("Y",$time));
        $monthEnd = mktime(23,59,59,date("m",$time),date("d",$time),date("Y",$time));

        if (!empty($begin) && !empty($end)) {
            $begin = strtotime($begin);
            $monthStart =  date("Y-m-d",mktime(12,00,0,date("m",$begin),date("d",$begin),date("Y",$begin)));
            $end = strtotime($end);
            $monthEnd = date("Y-m-d",mktime(23,59,59,date("m",$end),date("d",$end),date("Y",$end)));
        }

        //获取每天每个城市每个渠道的分单量
        $result = D("Home/OrderCsosNew")->getCityOrderListWithSource($monthStart,$monthEnd,$city_id,$group,$src);

        foreach ($result as $key => $value) {
            if (empty($value["src"])) {
               $value["source_name"] = "自然流量";
               $value["src"] = "normal";
            }
            $cityOrder[$value["date"]][$value["src"]]["free_order"] = $value["free_count"];
            $cityOrder[$value["date"]][$value["src"]]["pay_order"] = $value["pay_count"];
        }

        //获取每个城市每天是否计算分单量
        $monthStart = date("Y-m-d", mktime(12,0,0,date("m",$time),1,date("Y",$time)));
        $monthEnd = date("Y-m-d",mktime(23,59,59,date("m",$time),date("d",$time),date("Y",$time)));

        $result = D("Home/Db/CityGeneralizationCoefficient")->getCityGeneralizationList($monthStart,$monthEnd,$city_id);

        foreach ($result as $key => $value) {
            $list[$value["date"]] = $value;
        }

        foreach ($cityOrder as $key => $value) {
            if (array_key_exists($key,$list)) {
                if ($list[$key]["warnning"] == 1) {
                    //免费分单量
                    foreach ($value as $k => $val) {
                        if (array_key_exists($k,$city["list"])) {
                            $city["list"][$k]["yx_fen_order"] += $val["free_order"];
                            $city["all"]["yx_fen_order"] += $val["free_order"];
                        }
                    }
                }

                if ($list[$key]["coefficient"] != 0 || ( $list[$key]["mark"] == 1 && $list[$key]["coefficient"] == 0)) {
                    //付费分单量
                    foreach ($value as $k => $val) {
                        if (array_key_exists($k,$city["list"])) {
                            $city["list"][$k]["yx_fen_order"] += $val["pay_order"];
                            $city["all"]["yx_fen_order"] += $val["pay_order"];
                        }
                    }
                }
            }
        }

        return $city;
    }

    public function getCityOrderDetailsListWithSrc($city_id,$date,$src,$begin,$end,$status)
    {
        $time = strtotime($date);
        $allDay = date("t",$time);
        $monthStart = strtotime("-1 day",  mktime(12,0,0,date("m",$time),1,date("Y",$time)));
        $monthEnd = mktime(11,59,59,date("m",$time),date("d",$time),date("Y",$time));

        if (!empty($begin) && !empty($end)) {
            $begin = strtotime($begin);
            $monthStart =  strtotime("-1 day", mktime(12,00,0,date("m",$begin),date("d",$begin),date("Y",$begin)));
            $end = strtotime($end);
            $monthEnd = mktime(11,59,59,date("m",$end),date("d",$end),date("Y",$end));
        }

        if ($src == "normal") {
            $src = "";
        }

        $count = D("Home/Orders")->getCityOrderDetailsListWithSrcCount($city_id,$monthStart,$monthEnd,$src,$status);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, 50);
            $show = $p->show();
            $result = D("Home/Orders")->getCityOrderDetailsListWithSrc($p->firstRow, $p->listRows,$city_id,$monthStart,$monthEnd,$src,$status);
        }
        return array("list" => $result,"page"=> $show );
    }

    public function getCityCoefficientStatistics($date)
    {
        $result = D("Home/Db/CityGeneralizationCoefficient")->getCityCoefficientStatistics($date);

        $list = array(
            "1" => [
                "count" => 0,
                "city_a" => "",
                "city_b" => "",
                "name" => "0-1"
            ],
            "2" => [
                "count" => 0,
                "city_a" => "",
                "city_b" => "",
                "name" => "0-3"
            ],
            "3" => [
                "count" => 0,
                "city_a" => "",
                "city_b" => "",
                "name" => "1-0"
            ],
            "4" => [
                "count" => 0,
                "city_a" => "",
                "city_b" => "",
                "name" => "1-3"
            ],
            "5" => [
                "count" => 0,
                "city_a" => "",
                "city_b" => "",
                "name" => "3-0"
            ],
            "6" => [
                "count" => 0,
                "city_a" => "",
                "city_b" => "",
                "name" => "3-1"
            ],
        );

        foreach ($result as $key => $value) {
            if ($value["coefficient"] == 1 && $value["one_coefficient"] == 0) {
                $list[1]["count"] += 1;
                if ($value["little"] == 0) {
                     $list[1]["city_a"] .= $value["cname"].",";
                } else {
                    $list[1]["city_b"] .= $value["cname"].",";
                }
            } elseif ($value["coefficient"] == 3 && $value["one_coefficient"] == 0) {
                $list[2]["count"] += 1;
                if ($value["little"] == 0) {
                    $list[2]["city_a"] .= $value["cname"].",";
                } else {
                    $list[2]["city_b"] .= $value["cname"].",";
                }
            } elseif ($value["coefficient"] == 0 && $value["one_coefficient"] == 1) {
                $list[3]["count"] += 1;
                if ($value["little"] == 0) {
                     $list[3]["city_a"] .= $value["cname"].",";
                } else {
                    $list[3]["city_b"] .= $value["cname"].",";
                }
            } elseif ($value["coefficient"] == 3 && $value["one_coefficient"] == 1) {
                $list[4]["count"] += 1;
                if ($value["little"] == 0) {
                    $list[4]["city_a"] .= $value["cname"].",";
                } else {
                    $list[4]["city_b"] .= $value["cname"].",";
                }
            } elseif ($value["coefficient"] == 0 && $value["one_coefficient"] == 3) {
                $list[5]["count"] += 1;
                if ($value["little"] == 0) {
                    $list[5]["city_a"] .= $value["cname"].",";
                } else {
                    $list[5]["city_b"]  .= $value["cname"].",";
                }
            } elseif ($value["coefficient"] == 1 && $value["one_coefficient"] == 3) {
                $list[6]["count"] += 1;
                if ($value["little"] == 0) {
                    $list[6]["city_a"] .= $value["cname"].",";
                } else {
                    $list[6]["city_b"] .= $value["cname"].",";
                }
            }
        }
        return $list;
    }

    public function getCityCoefficientDetailsStatistics($date,$city_id,$level)
    {
        $time = strtotime($date);

        $monthStart = date("Y-m-d",  mktime(0,0,0,date("m",$time),1,date("Y",$time)));
        $monthEnd =  date("Y-m-d",mktime(0,0,0,date("m",$time),date("t",$time),date("Y",$time)));


        $result = D("Home/Db/CityGeneralizationCoefficient")->getCityCoefficientDetailsStatistics($monthStart,$monthEnd,$city_id,$level);

        $length = date("t",$time);

        foreach ($result as $key => $value) {
            if (!array_key_exists($value["city_id"],$list)) {
                $list[$value["city_id"]]["city_name"] = $value["city_name"];
                $list[$value["city_id"]]["little"] = $value["little"];
                for ($i = 0; $i < $length ; $i++) {
                    $day = date("Y-m-d",strtotime("+$i day",strtotime($monthStart)));
                    $list[$value["city_id"]]["date"][$day] = 0;
                    if ($day > date("Y-m-d")) {
                        $list[$value["city_id"]]["date"][$day] = '-';
                    }
                    if (!isset($days[$day])) {
                        $days[$day] = date("d",strtotime("+$i day",strtotime($monthStart)))."号";
                    }
                }
            }
            $list[$value["city_id"]]["date"][$value["date"]] = $value["coefficient"];
        }
        return array("list"=> $list,"days" => $days);
    }

    public function getCoefficientDetailsCityList($date)
    {
        $time = strtotime($date);
        $monthStart = date("Y-m-d",  mktime(0,0,0,date("m",$time),1,date("Y",$time)));
        $monthEnd =  date("Y-m-d",mktime(0,0,0,date("m",$time),date("d",$time),date("Y",$time)));
        return D("Home/Db/CityGeneralizationCoefficient")->getCoefficientDetailsCityList($monthStart,$monthEnd);
    }

    public function getCityNewVip($date,$city_id,$level,$whole)
    {
        $time = strtotime($date);
        $allDay = date("t",$time);
        $monthStart = date("Y-m-d",  mktime(0,0,0,date("m",$time),0,date("Y",$time)));
        $monthEnd =  date("Y-m-d",mktime(0,0,0,date("m",$time),date("d",$time),date("Y",$time)));

        $result = D("Home/UserVip")->getCityNewVip($monthStart,$monthEnd,$city_id,$level,$whole);

        foreach ($result as $key => $value) {
           $value["date"] = date("Y-m",$time);
           $city[$value["city_id"]] = $value;
        }
        return $city;
    }

    public function getCityNewVipDetails($city_id,$date)
    {
        $time = strtotime($date);
        $allDay = date("t",$time);
        $monthStart = date("Y-m-d",  mktime(0,0,0,date("m",$time),1,date("Y",$time)));
        $monthEnd =  date("Y-m-d",mktime(0,0,0,date("m",$time),date("t",$time),date("Y",$time)));

        $result = D("Home/UserVip")->getCityNewVipDetails($city_id,$monthStart,$monthEnd);

        foreach ($result  as $key => $value) {
            $day = date("d",strtotime($value["start_time"]));
            if ($day > 15) {
                $list[] = $value;
            }
        }
        return $list;
    }

    public function getCityNewVipList($date)
    {
        $time = strtotime($date);
        $monthStart = date("Y-m-d",  mktime(0,0,0,date("m",$time),1,date("Y",$time)));
        $monthEnd =  date("Y-m-d",mktime(0,0,0,date("m",$time),date("d",$time),date("Y",$time)));

        $result = D("Home/UserVip")->getCityNewVipList($monthStart,$monthEnd);
        foreach ($result as $key => $value) {
            $value["cname"] = strtoupper(mb_substr($value["px_abc"],0,1)) ." ". $value["cname"];
            $list[] = $value;
        }
        return $list;
    }

    public function setSummaryData($city)
    {
        $list = [];
        foreach ($city as $key => $value) {
            $list["now_month_miss_order"] += $city[$key]["now_miss_order"];
            $list["demand_order"] += $value["demand_order"];
            $list["miss_order"] += $value["miss_order"];
            $list["new_order"] += $value["new_order"];
            $list["all_count"] += $value["all_count"];
            $list["real_fen_count"] += $value["real_fen_count"];
            $list["yx_order"] += $value["yx_order"];
            $list["now_demand_order"] += $value["now_demand_order"];
            if ($value["now_miss_order"] > 0) {
                $list["now_miss_order"] += $value["now_miss_order"];
            }
        }

        return $list;
    }

    public function getHistoryCity($city,$historyCity)
    {
        foreach ($city as $key => $value) {
            if (array_key_exists($key,$historyCity)) {
                $hist_miss_order = round(setInfNanToN($historyCity[$key]["avg_theory_order"] - $historyCity[$key]["order_count"]),2);
                if ($hist_miss_order < 0) {
                    $hist_miss_order = 0;
                }
            }
            //当前平均缺单=平均分单（理）+上个月最后一天的当月平均缺单-平均分单（实）
            $city[$key]["now_miss_order"] = round($value["avg_theory_order"] + $hist_miss_order - $value["order_count"],2);
        }

        return $city;
    }

    public function getCityNewQianList($city,$date)
    {
        $model = new \Home\Model\Db\LogUserRealCompanyModel();
        $result = $model->getCityVipList($date);

        if (count($result) > 0 ) {
            foreach ($result as $item) {
                if (array_key_exists($item["city_id"],$city)) {
                    $city[$item["city_id"]]["vip_qian_count"] = $item["vip_qian_count"];
                }
            }
        }
        return $city;
    }
}