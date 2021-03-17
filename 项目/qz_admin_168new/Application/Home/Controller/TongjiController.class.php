<?php

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Library\Org\Page\CPage;

/**
 * 统计功能
 */
class TongjiController extends HomeBaseController
{

    public function index()
    {
        $this->_error();
    }

    /**
     * [cityTypeStatistics 城市类型分单率统计]
     * @return [type] [description]
     */
    public function cityTypeOrderStatistics()
    {
        /*S-按月统计*/
        $monthStart = mktime(0, 0, 0, date("m"), 1, date("Y"));
        $monthEnd = mktime(23, 59, 59, date("m"), date("t"), date("Y"));

        if (I("get.datetime") !== "") {
            $datetime = I("get.datetime");
            $monthStart = strtotime($datetime);
            $monthEnd = mktime(23, 59, 59, date("m", $monthStart), date("t", $monthStart), date("Y", $monthStart));
        }

        //获取全部城市
        $result = D("Quyu")->getAllQuyuOnly();
        foreach ($result as $key => $value) {
            $quyu[$value["cs"]] = $value;
        }

        //获取客服的发单量
        $poolList = D("OrderPool")->getCityOrdereffective($id, $cs, $kfgroup, $manager, $monthStart, $monthEnd);

        //获取客服分单量
        $fenList = D("OrderPool")->getKFOrderOperate($id, $cs, $kfgroup, $manager, $monthStart, $monthEnd);

        foreach ($quyu as $key => $value) {
            foreach ($kfList as $val) {
                if ($value["cid"] == $val["cs"]) {
                    $city["child"][$val["cs"]]["cname"] = $value["cname"];
                }
            }
        }

        //获取全部城市
        $result = D("Quyu")->getAllQuyuOnly();

        foreach ($result as $key => $value) {
            $quyu[$value["cid"]] = $value;
        }

        //归类统计城市的发单量
        foreach ($poolList as $key => $value) {
            $list["child"][$value["cs"]]["all"] += $value["all"];
            $list["child"][$value["cs"]]["cname"] = $value["cname"];
            $list["child"][$value["cs"]]["little"] = $value["little"];
            $list["child"][$value["cs"]]["manager"] = $value["manager"];
        }

        //归类统计城市的分单量
        foreach ($fenList as $key => $value) {
            if (!array_key_exists($value["cs"], $list["child"])) {
                $list["child"][$value["cs"]]["cname"] = $quyu[$value["cs"]]["cname"];
                $list["child"][$value["cs"]]["little"] = $quyu[$value["cs"]]["little"];
                $list["child"][$value["cs"]]["manager"] = $quyu[$value["cs"]]["manager"];
            }
            $list["child"][$value["cs"]]["fen"] += $value["fen"];
            $list["child"][$value["cs"]]["zen"] += $value["zen"];
            $list["child"][$value["cs"]]["fen_other"] += $value["fen_other"];
            $list["child"][$value["cs"]]["zen_other"] += $value["zen_other"];
        }

        //城市归类
        $city = array(
            "classA" => array(
                "1" => array(),//外销
                "2" => array() //商务
            ),
            "classB" => array(
                "1" => array(),
                "2" => array()
            ),
            "classC" => array(
                "1" => array(),
                "2" => array()
            ),
            "allA" => array(),
            "allB" => array(),
            "allC" => array(),
            "all" => array()
        );
        foreach ($list["child"] as $key => $value) {
            if ($value["manager"] == 1) {
                //外销城市
                switch ($value["little"]) {
                    case 0:
                        # A类
                        $city["classA"][1]["all"] += $value["all"];
                        $city["classA"][1]["fen"] += $value["fen"];
                        $city["classA"][1]["zen"] += $value["zen"];
                        $city["classA"][1]["all"] += $value["all"];
                        $city["classA"][1]["fen_rate"] = round($city["classA"][1]["fen"] / $city["classA"][1]["all"], 4) * 100;
                        $city["classA"][1]["zen_rate"] = round($city["classA"][1]["zen"] / $city["classA"][1]["all"], 4) * 100;
                        $city["classA"][1]["fen_zen"] = $city["classA"][1]["fen"] + $city["classA"][1]["zen"];
                        $city["classA"][1]["fen_zen_rate"] = round($city["classA"][1]["fen_zen"] / $city["classA"][1]["all"], 4) * 100;
                        //分类汇总
                        $city["allA"]["all"] += $value["all"];
                        $city["allA"]["fen"] += $value["fen"];
                        $city["allA"]["zen"] += $value["zen"];
                        $city["allA"]["all"] += $value["all"];
                        $city["allA"]["fen_rate"] = round($city["allA"]["fen"] / $city["allA"]["all"], 4) * 100;
                        $city["allA"]["zen_rate"] = round($city["allA"]["zen"] / $city["allA"]["all"], 4) * 100;
                        $city["allA"]["fen_zen"] = $city["allA"]["fen"] + $city["allA"]["zen"];
                        $city["allA"]["fen_zen_rate"] = round($city["allA"]["fen_zen"] / $city["allA"]["all"], 4) * 100;

                        break;
                    case 1:
                        # B类
                        $city["classB"][1]["all"] += $value["all"];
                        $city["classB"][1]["fen"] += $value["fen"];
                        $city["classB"][1]["zen"] += $value["zen"];
                        $city["classB"][1]["all"] += $value["all"];
                        $city["classB"][1]["fen_rate"] = round($city["classB"][1]["fen"] / $city["classB"][1]["all"], 4) * 100;
                        $city["classB"][1]["zen_rate"] = round($city["classB"][1]["zen"] / $city["classB"][1]["all"], 4) * 100;
                        $city["classB"][1]["fen_zen"] = $city["classB"][1]["fen"] + $city["classB"][1]["zen"];
                        $city["classB"][1]["fen_zen_rate"] = round($city["classB"][1]["fen_zen"] / $city["classB"][1]["all"], 4) * 100;

                        //分类汇总
                        $city["allB"]["all"] += $value["all"];
                        $city["allB"]["fen"] += $value["fen"];
                        $city["allB"]["zen"] += $value["zen"];
                        $city["allB"]["all"] += $value["all"];
                        $city["allB"]["fen_rate"] = round($city["allB"]["fen"] / $city["allB"]["all"], 4) * 100;
                        $city["allB"]["zen_rate"] = round($city["allB"]["zen"] / $city["allB"]["all"], 4) * 100;
                        $city["allB"]["fen_zen"] = $city["allB"]["fen"] + $city["allB"]["zen"];
                        $city["allB"]["fen_zen_rate"] = round($city["allB"]["fen_zen"] / $city["allB"]["all"], 4) * 100;
                        break;
                    case 2:
                        # C类
                        $city["classC"][1]["all"] += $value["all"];
                        $city["classC"][1]["fen"] += $value["fen"];
                        $city["classC"][1]["zen"] += $value["zen"];
                        $city["classC"][1]["all"] += $value["all"];
                        $city["classC"][1]["fen_rate"] = round($city["classC"][1]["fen"] / $city["classC"][1]["all"], 4) * 100;
                        $city["classC"][1]["zen_rate"] = round($city["classC"][1]["zen"] / $city["classC"][1]["all"], 4) * 100;
                        $city["classC"][1]["fen_zen"] = $city["classC"][1]["fen"] + $city["classC"][1]["zen"];
                        $city["classC"][1]["fen_zen_rate"] = round($city["classC"][1]["fen_zen"] / $city["classC"][1]["all"], 4) * 100;

                        //分类汇总
                        $city["allC"]["all"] += $value["all"];
                        $city["allC"]["fen"] += $value["fen"];
                        $city["allC"]["zen"] += $value["zen"];
                        $city["allC"]["all"] += $value["all"];
                        $city["allC"]["fen_rate"] = round($city["allC"]["fen"] / $city["allC"]["all"], 4) * 100;
                        $city["allC"]["zen_rate"] = round($city["allC"]["zen"] / $city["allC"]["all"], 4) * 100;
                        $city["allC"]["fen_zen"] = $city["allC"]["fen"] + $city["allC"]["zen"];
                        $city["allC"]["fen_zen_rate"] = round($city["allC"]["fen_zen"] / $city["allC"]["all"], 4) * 100;
                        break;
                }
            } else {
                //商务城市
                switch ($value["little"]) {
                    case 0:
                        # A类
                        $city["classA"][2]["all"] += $value["all"];
                        $city["classA"][2]["fen"] += $value["fen"];
                        $city["classA"][2]["zen"] += $value["zen"];
                        $city["classA"][2]["all"] += $value["all"];
                        $city["classA"][2]["fen_rate"] = round($city["classA"][2]["fen"] / $city["classA"][2]["all"], 4) * 100;
                        $city["classA"][2]["zen_rate"] = round($city["classA"][2]["zen"] / $city["classA"][2]["all"], 4) * 100;
                        $city["classA"][2]["fen_zen"] = $city["classA"][2]["fen"] + $city["classA"][2]["zen"];
                        $city["classA"][2]["fen_zen_rate"] = round($city["classA"][2]["fen_zen"] / $city["classA"][2]["all"], 4) * 100;

                        //分类汇总
                        $city["allA"]["all"] += $value["all"];
                        $city["allA"]["fen"] += $value["fen"];
                        $city["allA"]["zen"] += $value["zen"];
                        $city["allA"]["all"] += $value["all"];
                        $city["allA"]["fen_rate"] = round($city["allA"]["fen"] / $city["allA"]["all"], 4) * 100;
                        $city["allA"]["zen_rate"] = round($city["allA"]["zen"] / $city["allA"]["all"], 4) * 100;
                        $city["allA"]["fen_zen"] = $city["allA"]["fen"] + $city["allA"]["zen"];
                        $city["allA"]["fen_zen_rate"] = round($city["allA"]["fen_zen"] / $city["allA"]["all"], 4) * 100;
                        break;
                    case 1:
                        # B类
                        $city["classB"][2]["all"] += $value["all"];
                        $city["classB"][2]["fen"] += $value["fen"];
                        $city["classB"][2]["zen"] += $value["zen"];
                        $city["classB"][2]["all"] += $value["all"];
                        $city["classB"][2]["fen_rate"] = round($city["classB"][2]["fen"] / $city["classB"][2]["all"], 4) * 100;
                        $city["classB"][2]["zen_rate"] = round($city["classB"][2]["zen"] / $city["classB"][2]["all"], 4) * 100;
                        $city["classB"][2]["fen_zen"] = $city["classB"][2]["fen"] + $city["classB"][2]["zen"];
                        $city["classB"][2]["fen_zen_rate"] = round($city["classB"][2]["fen_zen"] / $city["classB"][2]["all"], 4) * 100;

                        //分类汇总
                        $city["allB"]["all"] += $value["all"];
                        $city["allB"]["fen"] += $value["fen"];
                        $city["allB"]["zen"] += $value["zen"];
                        $city["allB"]["all"] += $value["all"];
                        $city["allB"]["fen_rate"] = round($city["allB"]["fen"] / $city["allB"]["all"], 4) * 100;
                        $city["allB"]["zen_rate"] = round($city["allB"]["zen"] / $city["allB"]["all"], 4) * 100;
                        $city["allB"]["fen_zen"] = $city["allB"]["fen"] + $city["allB"]["zen"];
                        $city["allB"]["fen_zen_rate"] = round($city["allB"]["fen_zen"] / $city["allB"]["all"], 4) * 100;

                        break;
                    case 2:
                        # C类
                        $city["classC"][2]["all"] += $value["all"];
                        $city["classC"][2]["fen"] += $value["fen"];
                        $city["classC"][2]["zen"] += $value["zen"];
                        $city["classC"][2]["all"] += $value["all"];
                        $city["classC"][2]["fen_rate"] = round($city["classC"][2]["fen"] / $city["classC"][2]["all"], 4) * 100;
                        $city["classC"][2]["zen_rate"] = round($city["classC"][2]["zen"] / $city["classC"][2]["all"], 4) * 100;
                        $city["classC"][2]["fen_zen"] = $city["classC"][2]["fen"] + $city["classC"][2]["zen"];
                        $city["classC"][2]["fen_zen_rate"] = round($city["classC"][2]["fen_zen"] / $city["classC"][2]["all"], 4) * 100;

                        //分类汇总
                        $city["allC"]["all"] += $value["all"];
                        $city["allC"]["fen"] += $value["fen"];
                        $city["allC"]["zen"] += $value["zen"];
                        $city["allC"]["all"] += $value["all"];
                        $city["allC"]["fen_rate"] = round($city["allC"]["fen"] / $city["allC"]["all"], 4) * 100;
                        $city["allC"]["zen_rate"] = round($city["allC"]["zen"] / $city["allC"]["all"], 4) * 100;
                        $city["allC"]["fen_zen"] = $city["allC"]["fen"] + $city["allC"]["zen"];
                        $city["allC"]["fen_zen_rate"] = round($city["allC"]["fen_zen"] / $city["allC"]["all"], 4) * 100;
                        break;
                }
            }

            //全部汇总
            $city["all"]["all"] += $value["all"];
            $city["all"]["fen"] += $value["fen"];
            $city["all"]["zen"] += $value["zen"];
            $city["all"]["fen_rate"] = round($city["all"]["fen"] / $city["all"]["all"], 4) * 100;
            $city["all"]["zen_rate"] = round($city["all"]["zen"] / $city["all"]["all"], 4) * 100;
            $city["all"]["fen_zen"] = $city["all"]["fen"] + $city["all"]["zen"];
            $city["all"]["fen_zen_rate"] = round($city["all"]["fen_zen"] / $city["all"]["all"], 4) * 100;
        }

        /*S-跨月统计*/
        if (I("get.start") !== "" && I("get.end") !== "") {
            $start = strtotime(I("get.start"));
            $end = strtotime(I("get.end"));
            $monthStart = mktime(0, 0, 0, date("m", $start), 1, date("Y", $start));
            $monthEnd = mktime(23, 59, 59, date("m", $end), date("t", $end), date("Y", $end));
        }
        $list = array();
        //城市分单量
        $cityFenList = D("OrderPool")->getMonthOrderOperate($monthStart, $monthEnd);

        //城市发单量
        $cityPoolList = D("OrderPool")->getMonthCityOrdereffective($monthStart, $monthEnd);

        //归类统计城市的发单量
        foreach ($cityPoolList as $key => $value) {
            $list[$value["month"]][$value["cs"]]["all"] += $value["all"];
            $list[$value["month"]][$value["cs"]]["cname"] = $value["cname"];
            $list[$value["month"]][$value["cs"]]["little"] = $value["little"];
            $list[$value["month"]][$value["cs"]]["manager"] = $value["manager"];
            $list[$value["month"]][$value["cs"]]["month"] = $value["month"];
        }

        //归类统计城市的分单量
        foreach ($cityFenList as $key => $value) {
            if (!array_key_exists($value["cs"], $list[$value["month"]])) {
                $list[$value["month"]][$value["cs"]]["cname"] = $quyu[$value["cs"]]["cname"];
                $list[$value["month"]][$value["cs"]]["little"] = $quyu[$value["cs"]]["little"];
                $list[$value["month"]][$value["cs"]]["manager"] = $quyu[$value["cs"]]["manager"];
                $list[$value["month"]][$value["cs"]]["month"] = $value["month"];
            }
            $list[$value["month"]][$value["cs"]]["fen"] += $value["fen"];
            $list[$value["month"]][$value["cs"]]["zen"] += $value["zen"];
            $list[$value["month"]][$value["cs"]]["fen_other"] += $value["fen_other"];
            $list[$value["month"]][$value["cs"]]["zen_other"] += $value["zen_other"];
        }

        //按照商务外销城市分割
        foreach ($list as $key => $value) {
            foreach ($value as $val) {
                if ($val["manager"] == 1) {
                    //外销
                    $month["child"][$key][1]["all"] += $val["all"];
                    $month["child"][$key][1]["fen"] += $val["fen"];
                    $month["child"][$key][1]["zen"] += $val["zen"];
                    $month["child"][$key][1]["fen_rate"] = round($month["child"][$key][1]["fen"] / $month["child"][$key][1]["all"], 4) * 100;

                    $month["allA"]["all"] += $val["all"];
                    $month["allA"]["fen"] += $val["fen"];
                    $month["allA"]["zen"] += $val["zen"];
                    $month["allA"]["fen_rate"] = round($month["allA"]["fen"] / $month["allA"]["all"], 4) * 100;
                } else {
                    //商务
                    $month["child"][$key][2]["all"] += $val["all"];
                    $month["child"][$key][2]["fen"] += $val["fen"];
                    $month["child"][$key][2]["zen"] += $val["zen"];
                    $month["child"][$key][2]["fen_other"] += $val["fen_other"];
                    $month["child"][$key][2]["zen_other"] += $val["zen_other"];
                    $month["child"][$key][2]["fen_rate"] = round($month["child"][$key][2]["fen"] / $month["child"][$key][2]["all"], 4) * 100;

                    $month["allB"]["all"] += $val["all"];
                    $month["allB"]["fen"] += $val["fen"];
                    $month["allB"]["zen"] += $val["zen"];
                    $month["allB"]["fen_rate"] = round($month["allB"]["fen"] / $month["allB"]["all"], 4) * 100;
                }

                $month["all"]["all"] += $val["all"];
                $month["all"]["fen"] += $val["fen"];
                $month["all"]["zen"] += $val["zen"];
                $month["all"]["fen_rate"] = round($month["all"]["fen"] / $month["all"]["all"], 4) * 100;
            }
        }

        $this->assign('month', $month);
        $this->assign('city', $city);
        $this->display();
    }

    /**
     * 发单填写统计
     * @return [type] [description]
     */
    public function form()
    {
        $start = I("get.start");
        $end = I("get.end");

        $result = $this->getFormList($start, $end);
        $this->assign("form", $result);
        $this->display();
    }

    /**
     *  新单呼出率
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function order_tel()
    {
        if (count($this->city) > 0) {
            $citys = getUserCitys(false);
            $this->assign("citys", $citys);
        }
        if (I("get.cs") !== "") {
            $cs = I("get.cs");
            $this->assign("cs", $cs);
        }
        if (I("get.date") !== "") {
            $date = I("get.date");
            $list = $this->getOrderTel($date, $cs);
            $this->assign("date", $date);
            $this->assign("list", $list);
        }
        $this->display();
    }

    /**
     * 客服通话统计
     * @return [type] [description]
     */
    public function telcall()
    {
        switch ($this->User["uid"]) {
            case 2:
                //客服
                $id[] = $this->User["id"];
                $kfList[] = array(
                    "name" => $this->User["name"],
                    "id" => $this->User["id"]
                );
                break;
            case 31:
                //组长
                //获取本组的客服人员
                $result = D("Adminuser")->findMyGroupUser($this->User["id"], array(2));
                foreach ($result as $key => $value) {
                    $id[] = $value["id"];
                    $kfList[] = array(
                        "name" => $value["name"],
                        "id" => $value["id"]
                    );
                }
                break;
            case 30:
            case 63:
                //客服主管、客服经理
                $result = D("Adminuser")->findMyManageUser($this->User["id"]);
                foreach ($result as $key => $value) {
                    $id[] = $value["id"];
                    $kfList[] = array(
                        "name" => $value["name"],
                        "id" => $value["id"]
                    );
                }
                break;
            default :
                $result = D("Adminuser")->getKfList();
                foreach ($result as $key => $value) {
                    $id[] = $value["id"];
                    $kfList[] = array(
                        "name" => $value["name"],
                        "id" => $value["id"]
                    );
                }
                break;
        }

        if (count($id) == 0) {
            $this->_error("您的客服管辖尚未设置！");
        }

        $id = implode(",", $id);
        $this->assign("kfList", $kfList);

        if (I("get.name") !== "") {
            $name = I("get.name");
            $this->assign("kf", $name);
            if (!empty($id)) {
                $ids = array_flip(array_filter(explode(",", $id)));
                if (!array_key_exists($name, $ids)) {
                    $this->_error("您无权查看该客服！");
                }
            }
            $id = $name;
        }

        if (I("get.begin") !== "") {
            $begin = I("get.begin");
            $this->assign("begin", $begin);
        }

        if (I("get.end") !== "") {
            $end = I("get.end");
            $this->assign("end", $end);
        }

        if (I("get.group") !== "") {
            $group = I("get.group");
            $this->assign("group", $group);
        }

        $list = $this->getKfTelCall($id, $begin, $end, $group);

        $this->assign("list", $list);
        $this->display();
    }

    /**
     * 客服坐席数统计
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function customerseatsstat()
    {
        $begin = date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y")));
        $end = date("Y-m-d", strtotime("+1 day", strtotime(date("Y-m-d"))));

        if (I("get.begin") !== "" && I("get.end") !== "") {
            $begin = I("get.begin");
            $end = I("get.end");
            $end = date("Y-m-d", strtotime("+1 day", strtotime($end)));
        }

        $result = D("OrderPool")->customerseatsstat($begin, $end);

        $list["child"] = $result;
        $count = count($list["child"]);

        foreach ($list["child"] as $key => $value) {

            $list["date"]["1"]["all"] += $value["group1"];
            $list["date"]["1"]["avg"] = round($list["date"]["1"]["all"] / $count, 2);

            $list["date"]["2"]["all"] += $value["group2"];
            $list["date"]["2"]["avg"] = round($list["date"]["2"]["all"] / $count, 2);

            $list["date"]["3"]["all"] += $value["group3"];
            $list["date"]["3"]["avg"] = round($list["date"]["3"]["all"] / $count, 2);

            $list["date"]["4"]["all"] += $value["group4"];
            $list["date"]["4"]["avg"] = round($list["date"]["4"]["all"] / $count, 2);

            $list["date"]["5"]["all"] += $value["group5"];
            $list["date"]["5"]["avg"] = round($list["date"]["5"]["all"] / $count, 2);

            $list["date"]["6"]["all"] += $value["group6"];
            $list["date"]["6"]["avg"] = round($list["date"]["6"]["all"] / $count, 2);

            $list["date"]["7"]["all"] += $value["group7"];
            $list["date"]["7"]["avg"] = round($list["date"]["7"]["all"] / $count, 2);

            $list["date"]["8"]["all"] += $value["group8"];
            $list["date"]["8"]["avg"] = round($list["date"]["8"]["all"] / $count, 2);

            $list["date"]["9"]["all"] += $value["group9"];
            $list["date"]["9"]["avg"] = round($list["date"]["9"]["all"] / $count, 2);

            $list["date"]["11"]["all"] += $value["group11"];
            $list["date"]["11"]["avg"] = round($list["date"]["11"]["all"] / $count, 2);

            $list["date"]["12"]["all"] += $value["group12"];
            $list["date"]["12"]["avg"] = round($list["date"]["12"]["all"] / $count, 2);

            $list["date"]["13"]["all"] += $value["group13"];
            $list["date"]["13"]["avg"] = round($list["date"]["13"]["all"] / $count, 2);

            $list["date"]["14"]["all"] += $value["group14"];
            $list["date"]["14"]["avg"] = round($list["date"]["14"]["all"] / $count, 2);

            $list["date"]["15"]["all"] += $value["group15"];
            $list["date"]["15"]["avg"] = round($list["date"]["15"]["all"] / $count, 2);

            $list["all"]["all"] = $list["date"]["1"]["all"] + $list["date"]["2"]["all"]
                + $list["date"]["3"]["all"] + $list["date"]["4"]["all"] + $list["date"]["5"]["all"]
                + $list["date"]["6"]["all"] + $list["date"]["7"]["all"] + $list["date"]["8"]["all"]
                + $list["date"]["9"]["all"] + $list["date"]["11"]["all"] + $list["date"]["12"]["all"]
                + $list["date"]["13"]["all"] + $list["date"]["14"]["all"] + $list["date"]["15"]["all"];
            $list["all"]["avg"] = round($list["all"]["all"] / $count, 2);
        }

        $this->assign("list", $list);
        $this->display();
    }


    /**
     * 量房统计页面
     * author: mcj
     */
    public function liangFang()
    {
        $liang_fang_count_logic = D("Home/Logic/LiangFangCountLogic");
        $search_data = I('get.', '', 'trim,htmlspecialchars');
//        dump($search_data);die;


        $validate = $liang_fang_count_logic->valiLiangFang($search_data);
        if ($validate['result'] == false) {
            $this->error($validate['mes']);
        }
        //设置默认筛选时间
        if (empty($search_data['time_start'])) {
            $search_data['time_start'] = date("Y-m-d", strtotime("-30 day"));
        }
        if (empty($search_data['time_end'])) {
            $search_data['time_end'] = date("Y-m-d");
        }
        $this->assign('search_data', $search_data);

        $admin = getAdminUser();
        $operaters = D('Adminuser')->getKfManagerListByIdAndUid($admin['id'], $admin['uid']);

        //分页
        $page_size = empty($search_data['p_size']) ? 20 : $search_data['p_size'];
        $p = empty($search_data['p']) ? 1 : $search_data['p'];
        $count = $liang_fang_count_logic->countLiangFang($search_data);
        $page = new CPage($count, $page_size);
        $this->assign('page', $page->show());
        $list = $liang_fang_count_logic->getLiangfang($search_data, $p, $page_size);
        $this->assign('list', $list);
        $this->assign('operaters', $operaters['list']);
        $this->display();
    }

    /**
     * 量房统计导出，时间问题，excle部分未拆解封装
     * author: mcj
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function liangFangExp()
    {
        $liang_fang_count_logic = D("Home/Logic/LiangFangCountLogic");
        $search_data = I('get.', '', 'trim,htmlspecialchars');
        $validate = $liang_fang_count_logic->valiLiangFang($search_data);
        if ($validate['result'] == false) {
            $this->error($validate['mes']);
        }
        //设置默认筛选时间
        if (empty($search_data['time_start'])) {
            $search_data['time_start'] = date("Y-m-d", strtotime("-31 day"));
        }
        if (empty($search_data['time_end'])) {
            $search_data['time_end'] = date("Y-m-d");
        }
        $result = $liang_fang_count_logic->getLiangfangAll($search_data);
//        dump($result);die;
        $list = [];
        foreach ($result as $key => $value) {
            $list[$key + 1]['id'] = $key + 1;
            $list[$key + 1]['time'] = date('Y年m月d日', $value['give_time']);
            $list[$key + 1]['order_no'] = (string)$value['order_id'];
            if ($value['type_fw'] == '1') {
                $list[$key + 1]['order_status'] = '分单';
            } else if ($value['type_fw'] == '2') {
                $list[$key + 1]['order_status'] = '赠单';
            } else {
                $list[$key + 1]['order_status'] = '无状态';
            }
            if ($value['lf_status'] == 2) {
                $list[$key + 1]['measure'] = '未量房';
            } else if ($value['lf_status'] == 3) {
                $list[$key + 1]['measure'] = '已量房';
            } else {
                $list[$key + 1]['measure'] = '无状态';
            }

            if($value['yzstatus'] == 1){
                $list[$key + 1]['yzstatus'] = '已量房';
            }elseif($value['yzstatus'] == 2){
                $list[$key + 1]['yzstatus'] = '未量房';
            }else{
                $list[$key + 1]['yzstatus'] = '未知';
            }

            if ($value['lf_order_id'] == null) {
                $list[$key + 1]['revisit'] = '否';
            } else {
                $list[$key + 1]['revisit'] = '是';
            }
            $list[$key + 1]['belong'] = $value['op_name'];
        }

        import('Library.Org.PHP_XLSXWriter.xlsxwriter');
        try {
            $writer = new \XLSXWriter();
            $writer->writeSheetHeader('Sheet1', array('c1' => 'string', 'c2' => 'string', 'c3' => 'string', 'c4' => 'string', 'c5' => 'string', 'c6' => 'string', 'c7' => 'string'));
            //写入日期
            $writer->writeSheetRow('Sheet1', array('日期：' . $search_data['time_start'] . '-' . $search_data['time_end']));
            //标题
            $herder = array(
                '序号',
                '时间',
                '订单号',
                '订单状态',
                '会员量房状态',
                '业主量房状态',
                '是否二次回访',
                '归属人',
            );
            $writer->writeSheetRow('Sheet1', $herder);
            //数据
            $jsq = 1;
            foreach ($list as $key => $value) {
                $v = array(
                    $jsq,
                    $value['time'],
                    $value['order_no'],
                    $value['order_status'],
                    $value['measure'],
                    $value['yzstatus'],
                    $value['revisit'],
                    $value['belong'],
                );
                $writer->writeSheetRow('Sheet1', $v);
                $jsq++;
            }
            ob_end_clean();
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");;
            header('Content-Disposition:attachment;filename="量房统计.xlsx"');
            header("Content-Transfer-Encoding:binary");
            $writer->writeToStdOut("php://output");
        } catch (Exception $e) {
            echo 'something error';
        }

    }



    /*
     * ------------------------------------------------------------------------------------------------------
     */
    /**
     * 获取新单呼出率统计数据
     * @param  string $value [description]
     * @return [type]        [description]
     */
    private function getOrderTel($date, $cs)
    {
        $time = strtotime($date);
        $monthStart = mktime(0, 0, 0, date("m", $time), 1, date("Y", $time));
        $monthEnd = strtotime("+1 month", $monthStart);

        $result = D("Orders")->getOrderTelTongji($monthStart, $monthEnd, $cs);
        foreach ($result as $key => $value) {
            if ($value["nowdate"] == $date) {
                $list[$value["cname"]]["item"] = $value;
                $list[$value["cname"]]["day_rate"] = round($value["3-8"] / $value["count"], 2) * 100;
                $nowList["date"] = $value["nowdate"];
                $nowList["3-8"] += $value["3-8"];
                $nowList["8-12"] += $value["8-12"];
                $nowList["12-20"] += $value["12-20"];
                $nowList["20-30"] += $value["20-30"];
                $nowList["30以上"] += $value["30以上"];
                $nowList["count"] += $value["count"];
                $nowList["rate"] = round($nowList["3-8"] / $nowList["count"], 2) * 100;
            }

            if (array_key_exists($value["cname"], $list)) {
                $list[$value["cname"]]["3-8"] += $value["3-8"];
                $list[$value["cname"]]["count"] += $value["count"];
            }
        }

        foreach ($list as $key => $value) {
            $list[$key]["rate"] = round($value["3-8"] / $value["count"], 2) * 100;
        }

        return array("list" => $list, "nowList" => $nowList);
    }

    /**
     * 获取统计列表
     * @param  string $value [description]
     * @return [type]        [description]
     */
    private function getFormList($start, $end)
    {
        if (!empty($start) && strtotime($start) < strtotime("2016-06-08")) {
            $this->_error("很抱歉，数据查询时间必须大于2016年6月8日");
        }

        $end = empty($end) ? strtotime(date("Y-m-d")) : strtotime($end);
        $end = $end + 86400 - 1;
        $start = empty($start) ? strtotime("-7 day", $end) : strtotime($start);
        if ($start < strtotime("2016-06-08")) {
            $start = strtotime("2016-06-08");
        }
        $result = D("OrderSource")->getOrderFormStatistics($start, $end);
        return $result;
    }

    /**
     * 获取客服通话统计
     * @param  [type] $id    [description]
     * @param  [type] $begin [description]
     * @param  [type] $end   [description]
     * @return [type]        [description]
     */
    private function getKfTelCall($id, $begin, $end, $group)
    {
        $monthBegin = date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y")));
        $monthEnd = date("Y-m-d");

        if (!empty($begin) && !empty($end)) {
            $offset = (strtotime($end) - strtotime($begin)) / 86400;
            if ($offset > 31) {
                $this->_error("时间跨度不能大于31天！");
            }
            $monthBegin = $begin;
            $monthEnd = $end;
        }

        $result = D("Orders")->getKfTelCall($id, $monthBegin, $monthEnd, $group);

        foreach ($result as $key => $value) {
            $sub = $list[$value["id"]];
            if (count($sub) == 0) {
                $sub = array();
                $sub["name"] = $value["name"];
                $sub["kfgroup"] = $value["kfgroup"];
            }

            if (!array_key_exists($value["cid"], $sub["date"][$value["date"]]["cid"])) {
                $sub["date"][$value["date"]]["cid"][$value["cid"]] = $value["cid"];
                $sub["date"][$value["date"]]["fdcount"] += $value["fdcount"];
            } else {
                if (!array_key_exists($value["date"], $sub["date"])) {
                    $sub["date"][$value["date"]]["fdcount"] += $value["fdcount"];
                }
            }

            switch ($value["mark"]) {
                case '1':
                    //3-8分钟内
                    $sub["date"][$value["date"]]["A"] += $value["telcount"];
                    break;
                case '2':
                    //8-12分钟内
                    $sub["date"][$value["date"]]["B"] += $value["telcount"];
                    break;
                case '3':
                    //12-30分钟内
                    $sub["date"][$value["date"]]["C"] += $value["telcount"];
                    break;
                case '4':
                    //30分钟以上
                    $sub["date"][$value["date"]]["D"] += $value["telcount"];
                    break;
                case '5':
                    //新单未拨打
                    $sub["date"][$value["date"]]["E"] += $value["telcount"];
                    break;
            }
            $list[$value["id"]] = $sub;
        }

        return $list;
    }

}