<?php

/**
*  数据分析
*/
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

class OrderanalysisController extends HomeBaseController
{
    /**
     * 个人新单明细
     */
    public function newordersinger()
    {
        if (I("get.start") !== "" && I("get.end") !== "") {
            $start = I("get.start");
            $end = I("get.end");
            $this->assign("begin",$start);
            $this->assign("end",$end);
            if ($start < "2016-06-28") {
                $this->_error("数据采集问题,2016-06-28前无法搜索");
            }
        }



        if (I("get.cs") !== "") {
            $cs = I("get.cs");
            $this->assign("cs",$cs);
        }

        if (I("get.group") !== "") {
            $group = I("get.group");
            $this->assign("group",$group);
        }

        if (I("get.zz") !== "") {
            $zz = I("get.zz");
             $this->assign("zz",$zz);
        }

        if (I("get.name") !== "") {
            $name = I("get.name");
            $this->assign("name",$name);
        }

        $list = $this->getNewOrderSingerList($start,$end,$cs,$group,$zz,$name);
        //获取管辖城市
        if(count($this->city) > 0){
            $citys = getUserCitys(false);
            $this->assign("citys",$citys);
        }

        //获取客服列表
        $kfList = D("Adminuser")->getKfList();
        $this->assign("kfList",$kfList);

        $this->assign("list",$list);
        $this->display();
    }

    /**
     * 日有效率统计
     * @return [type] [description]
     */
    public function orderseffectiverate()
    {

        //获取管辖城市
        if(count($this->city) > 0){
            $citys = getUserCitys(false);
            $this->assign("citys",$citys);
        }

        if (I("get.date") !== "") {
            $date = I("get.date");
            $this->assign("date",$date);
            if ($date < "2016-07-01") {
                $this->_error("数据采集问题,2016-07月前无法搜索");
            }
        }

        if (I("get.cs") !== "") {
            $cs = I("get.cs");
            $this->assign("cs",$cs);
        }
        $list = $this->orderseffectiverateList($date,$cs);
        $this->assign("list",$list);
        $this->display();
    }


    /**
     * 月有效率统计
     * @return [type] [description]
     */
    public function orderseffectiveratemonth()
    {
        if (I("get.date") !== "") {
            $date = I("get.date");
            $this->assign("date",$date);
            if ($date < "2016-07-01") {
                $this->_error("数据采集问题,2016-07月前无法搜索");
            }
        }

        if (I("get.cs") !== "") {
            $cs = I("get.cs");
            $this->assign("cs",$cs);
        }

        //获取管辖城市
        if(count($this->city) > 0){
            $citys = getUserCitys(false);
            $this->assign("citys",$citys);
        }

        $list = $this->orderseffectiveratemonthList($date,$cs);
        $this->assign("list",$list);
        $this->display();
    }


    /**
     * 打电话及时性
     * @return [type] [description]
     */
    public function timeliness()
    {
        //获取管辖城市
        if(count($this->city) > 0){
            $citys = getUserCitys(false);
            $this->assign("citys",$citys);
        }

        if (I("get.date") !== "") {
            $date = I("get.date");
            $this->assign("date",$date);
            if ($date < "2016-07-01") {
                $this->_error("数据采集问题,2016-07月前无法搜索");
            }
        }

        if (I("get.cs") !== "") {
            $cs = I("get.cs");
            $this->assign("cs",$cs);
        }

        $list = $this->timelinessList($cs,$date);
        $this->assign("list",$list);
        $this->display();
    }

    /**
     * 电话呼出率统计
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function calloutgoing()
    {
        //获取管辖城市
        if(count($this->city) > 0){
            $citys = getUserCitys(false);
            $this->assign("citys",$citys);
        }

        if (I("get.date") !== "") {
            $date = I("get.date");
            $this->assign("date",$date);
        }

        if (I("get.cs") !== "") {
            $cs = I("get.cs");
            $this->assign("cs",$cs);
        }

        $list = $this->calloutgoingList($cs,$date);
        $this->assign("list",$list);
        $this->display();
    }

    /**
     * 个人有效率统计
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function singleseffectiverate()
    {
        if (I("get.date") !== "") {
            $date = I("get.date");
            $this->assign("date",$date);
        }

        if (I("get.class") !== "") {
            $class = I("get.class");
            $this->assign("class",$class);
        }

        if (I("get.group") !== "") {
            $group = I("get.group");
            $this->assign("group",$group);
        }

        if (I("get.kf") !== "") {
            $kf = I("get.kf");
            $this->assign("kf",$kf);
        }

        //获取客服列表
        $kfList = D("Adminuser")->getKfList();
        $this->assign("kfList",$kfList);

        foreach ($kfList as $key => $value) {
            if (!array_key_exists($value["kfgroup"], $groups)) {
                $groups[$value["kfgroup"]] = $value["kfgroup"];
            }
        }
        sort($groups);
        $this->assign("kfgroup",$groups);

        $list = $this->singleseffectiverateList($date,$class,$group,$kf);
        $this->assign("list",$list);
        $this->display();
    }

    private function singleseffectiverateList($date,$class,$group,$kf)
    {
        if (empty($date)|| $date >= date("Y-m-d")) {
            $date = date("Y-m-d");
        }
        $result = D("Orders")->singleseffectiverateList($date,$class,$group,$kf);

        foreach ($result as $key => $value) {
            if ($value["nowdate"] == $date) {
                $value["fenrate"] = round($value["fencount"]/$value["count"],2)*100;
                $value["zenrate"] = round($value["zencount"]/$value["count"],2)*100;
                $value["allrate"] = round(($value["zencount"]+$value["fencount"])/$value["count"],2)*100;
                $list[$value["uid"]]["now"] = $value;
            }

            $list[$value["uid"]]["allcount"] += $value["count"];
            $list[$value["uid"]]["allfen"] += $value["fencount"];
            $list[$value["uid"]]["allzen"] += $value["zencount"];
            $list[$value["uid"]]["allfenrate"] = round($list[$value["uid"]]["allfen"]/$list[$value["uid"]]["allcount"],2)*100;
            $list[$value["uid"]]["allzenrate"] = round($list[$value["uid"]]["allzen"]/$list[$value["uid"]]["allcount"],2)*100;
            $list[$value["uid"]]["allrate"] = round(($list[$value["uid"]]["allzen"]+$list[$value["uid"]]["allfen"])/$list[$value["uid"]]["allcount"],2)*100;

        }

        return $list;
    }

    /**
     * 电话呼出统计
     * @param  [type] $cs   [城市]
     * @param  [type] $date [日期]
     * @return [type]       [description]
     */
    private function calloutgoingList($cs,$date)
    {
        if (empty($date)|| $date >= date("Y-m-d")) {
            $date = date("Y-m-d");
        }

        $begin =  $date;
        $end = date("Y-m-d", strtotime("+1 day",strtotime($date)));
        $list = D("Orders")->calloutgoingList($begin,$end,$cs);

        foreach ($list as $key => $value) {
            $list[$key]["nowuncallrate"] = round($value["nowuncallcount"]/$value["nowcall"],2)*100;
            $list[$key]["nowcallrate"] = round($value["nowcallcount"]/$value["nowcall"],2)*100;
            $list[$key]["uncallrate"] = round($value["uncallcount"]/$value["allcount"],2)*100;
            $list[$key]["callrate"] = round($value["callcount"]/$value["allcount"],2)*100;
        }
        return $list;
    }

    /**
     * 当日拨打率
     * @param  [type] $cs   [description]
     * @param  [type] $date [description]
     * @return [type]       [description]
     */
    private function timelinessList($cs,$date)
    {
        if (empty($date)|| $date >= date("Y-m-d")) {
            $date = date("Y-m-d");
        }

        $begin = date("Y-m-d", strtotime("-1 day",strtotime($date)));
        $end = $date;

        $result = D("Orders")->callrete($begin,$end,$cs);

        foreach ($result as $key => $value) {
            $list[$value["cs"]]["cname"] = $value["cname"];
            $list[$value["cs"]]["month"] = round($value["telmonthnew"]/$value["monthnew"],2)*100;
            $list[$value["cs"]]["now"] = round($value["telnewmark"]/$value["newmark"],2)*100;
            $list[$value["cs"]]["subnew"] = round($value["telsubnewmark"]/$value["subnewmark"],2)*100;
            $list[$value["cs"]]["montsubnew"] = round($value["telmonthsubnewmark"]/$value["allsubnew"],2)*100;
            $list[$value["cs"]]["sd"] = round($value["telsdmark"]/$value["sdmark"],2)*100;
            $list[$value["cs"]]["dd"] = round($value["telddmark"]/$value["ddmark"],2)*100;
        }

        return $list;
    }

    /**
     * 月有效率统计列表
     * @param  [type] $date [日期]
     * @param  [type] $cs   [城市]
     * @return [type]       [description]
     */
    private function orderseffectiveratemonthList($date,$cs)
    {
        if (empty($date)|| $date >= date("Y-m-d")) {
            $date = date("Y-m-d");
        }
        $prevMonth = strtotime("-1 month",strtotime($date));
        $start = date("Y-m-d",mktime(0,0,0,date("m",$prevMonth),date("t",$prevMonth),date("Y",$prevMonth)));
        $end =  $date;
        $result = D("Orders")->orderseffectiveratemonthList($start,$end,$cs);

        foreach ($result as $key => $value) {
            $list["item"][] = $value;
            if (!isset($item)) {
                foreach ($value as $k => $val) {
                   $item[$k] = "";
                }
            }

            foreach ($item as $k => $val) {
                $item[$k] += $value[$k];
            }

            //如果是真会员
            if ($value["vip"] == 1) {
                $item["vipcount"] += $value["count"];
                $item["vipfencount"] += $value["fencount"];
                $item["vipzencount"] += $value["zencount"];
                $item["vipsubnewfencount"] += $value["subnewfencount"];
                $item["vipfen_sub_count"] += $value["fen_sub_count"];
                $item["vipzen_sub_count"] += $value["zen_sub_count"];
                $item["vipwxfencount"] += $value["wxfencount"];
            }
        }

        $item["date"] = $date;
        $item["fen_rate"] = round($item["vipfencount"]/$item["vipcount"],2)*100;
        $item["zen_rate"] = round($item["vipzencount"]/$item["vipcount"],2)*100;
        $item["fen_not_wx_rate"] = round($item["vipfencount"]/($item["vipcount"] - $item["vipwxfencount"]),2)*100;
        $item["subnew_rate"] = round($item["vipsubnewfencount"]/$item["vipcount"],2)*100;
        $item["fen_sub_rate"] = round($item["vipfen_sub_count"]/$item["vipcount"],2)*100;
        $item["fen_zen_rate"] = round(($item["vipfen_sub_count"]+$item["vipzen_sub_count"])/($item["vipfencount"]+$item["vipzencount"]),2)*100;
        $list["all"] = $item;
        return $list;
    }

    /**
     * 获取日有效率列表
     * @param  [type] $date [日期]
     * @return [type]       [description]
     */
    private function orderseffectiverateList($date,$cs)
    {
        if (empty($date) || $date >= date("Y-m-d")) {
            $date = date("Y-m-d");
        }
        $start = date("Y-m-d", strtotime("-1 day",strtotime($date)));
        $end =  $date;
        $result = D("Orders")->orderseffectiverateList($start,$end,$cs);
        foreach ($result as $key => $value) {
            $list["item"][] = $value;

            if (!isset($item)) {
                foreach ($value as $k => $val) {
                   $item[$k] = "";
                }
            }

            foreach ($item as $k => $val) {
                $item[$k] += $value[$k];
            }

            //如果是真会员
            if ($value["vip"] == 1) {
                $item["vipcount"] += $value["count"];
                $item["vipfencount"] += $value["fencount"];
                $item["vipzencount"] += $value["zencount"];
                $item["vipsubnew_fen_count"] += $value["subnew_fen_count"];
                $item["vipdd_fen_count"] += $value["dd_fen_count"];
                $item["vipsd_fen_count"] += $value["sd_fen_count"];
                $item["vipfen_sub_count"] += $value["fen_sub_count"];
                $item["vipfennowcount"] += $value["fennowcount"];
            }
        }
        $item["date"] = $date;
        $item["fen_rate"] = round($item["vipfencount"]/$item["vipcount"],2)*100;
        $item["fennow_rate"] = round($item["vipfennowcount"]/$item["vipcount"],2)*100;
        $item["zen_rate"] = round($item["vipzencount"]/$item["vipcount"],2)*100;
        $item["subnew_rate"] = round($item["vipsubnew_fen_count"]/$item["vipcount"],2)*100;
        $item["ddfen_rate"] = round($item["vipdd_fen_count"]/$item["vipcount"],2)*100;
        $item["sdfen_rate"] = round($item["vipsd_fen_count"]/$item["vipcount"],2)*100;
        $item["fen_sub_rate"] = round($item["vipfen_sub_count"]/$item["vipcount"],2)*100;
        $list["all"] = $item;
        return $list;
    }

    /**
     * 获取个人明细列表
     * @param  [type] $start [开始时间]
     * @param  [type] $end   [结束时间]
     * @param  [type] $cs    [城市]
     * @param  [type] $group [客服组]
     * @param  [type] $zz    [客服组长]
     * @param  [type] $name  [客服]
     * @return [type]        [description]
     */
    private function getNewOrderSingerList($start,$end,$cs,$group,$zz,$name)
    {
        $begin = strtotime($start);
        $end  = strtotime($end);
        //默认当天
        if (empty($start)) {
            $begin = strtotime(date("Y-m-d"));
            $end = strtotime("+1 day",strtotime(date("Y-m-d")));
        }

        $result = D("Orders")->getNewOrderSingerList($begin,$end,$cs,$group,$zz,$name);
        if (count($result) > 0) {
            foreach ($result as $key => $value) {
                $list[$value["cs"]]["count"] += $value["ordercount"];
                if ($value["nowdate"] >= date("Y-m-d",$begin) && $value["nowdate"] < date("Y-m-d",$end)) {
                    $value["allcount"] = $list[$value["cs"]]["count"];
                    $list[$value["cs"]]["cname"] = $value["cname"];
                    $list[$value["cs"]]["item"][$value["nowdate"]] = $value;
                }
            }

            foreach ($list as $key => $value) {
                if (count($value["item"]) == 0) {
                    unset($list[$key]);
                }
            }

            return $list;
        }
    }
}