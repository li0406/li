<?php

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

/**
*
*/
class VipstatController extends HomeBaseController
{
    /**
     * 会员历史记录
     * @return [type] [description]
     */
    public function vipHistory()
    {
        if (I("get.type") == 2) {
            //无会员历史记录
            $tmp = "viphistory_none";
            //获取无会员城市列表
            $list = $this->getVipHistory(2,I("get.year"),session("uc_userinfo.uid"),session("uc_userinfo.department_id"));
            $cs = I("get.cs") == null ?$list[0]["cs"]:I("get.cs");
            //获取城市无会员城市时间信息
            $fullYear = $this->getCityLostVipHistory($cs,I("get.year"));
        }
        $this->assign("cs",$cs);
        $this->assign("fullYear",$fullYear);
        $this->assign("list",$list);
        $this->display($tmp);
    }

    private function getVipHistory($type,$year,$uid,$dep)
    {
        $yearStart = date("Y-m-d",strtotime("-1 year",mktime(0,0,0,1,1,date("Y"))));
        $yearEnd = date("Y-m-d",mktime(23,59,59,12,31,date("Y")));

        if (!empty($dep) && $uid != 46) {
            $result = D("Quyu")->getCityInfoByDept($dep);
            foreach ($result as $key => $value) {
                $ids[] = $value["cid"];
            }
            $ids = implode(",", $ids);
        }

        //无会员城市
        if ($type == 2) {
            //获取无会员城市列表
            $list = D("User")->getLostVipList($ids);

            //获取城市最近的有会员时间
            $result = D("User")->getVipLastTime($yearStart,$yearEnd,$ids);

            foreach ($result as $key => $value) {
                $citys[$value["city_id"]] = $value["time"];
            }

            foreach ($list as $key => $value) {
                $end = strtotime($citys[$value["cs"]]);
                if (empty($end)) {
                    $end = mktime(0,0,0,1,1,date("Y",strtotime($yearStart)));
                }

                $offset = floor((time() - $end)/86400);

                switch ($value['little']) {
                    case '0':
                        $type = "A";
                        break;
                    case '1':
                        $type = "B";
                    break;
                    case '2':
                        $type = "C";
                        break;
                }

                switch ($value['manager']) {
                    case '0':
                        $list[$key]['type'] = "商务";
                        break;
                    case '1':
                        $list[$key]['type'] = "外销";
                        break;
                    case '2':
                        $list[$key]['type'] = "商务外销";
                        break;
                }

                $list[$key]['type'] .= $type;
                $list[$key]['offset'] = $offset;
            }
        }

        return $list;
    }

    private function getCityLostVipHistory($cs,$year)
    {
        if (empty($year)) {
            $year = date("Y");
        }

        $yearStart = date("Y-m-d",mktime(0,0,0,1,1,$year));
        $yearEnd = date("Y-m-d",mktime(23,59,59,12,31,$year));

        //获取全年的月份和每月的时间
        for ($i = 1; $i <= 12 ; $i ++) {
            $month = $i < 10?"0".$i:$i;
            $monthFirstDay = $year.$month."01";
            $days = date('t', strtotime($monthFirstDay));
            for ($j=1; $j <= $days ; $j++) {
                $day = $j < 10?"0".$j:$j;
                $fullYear[$month][] = array(
                    "day" => $year."-".$month."-".$day,
                    "show" => 0
                );
            }
        }

        $end = $yearEnd;
        if (date("Y-m-d") < $yearEnd) {
            $end = date("Y-m-d");
        }

        //获取城市VIP时间
        $result = D("User")->getCityVipHistory($cs,$yearStart,$yearEnd);

        foreach ($result as $key => $value) {
            $list[$value["time"]] = $value["time"];
        }

        foreach ($fullYear as $key => $value) {
            foreach ($value as $k => $val) {
                if (!array_key_exists($val["day"], $list) && $val["day"] <= $end) {
                    $fullYear[$key][$k]["show"] =  1;
                }
            }
        }

        return $fullYear;
    }
}
