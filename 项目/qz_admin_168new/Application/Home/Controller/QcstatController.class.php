<?php

/**
* 质检模块
*/

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\QcInfoLogicModel;

class QcstatController extends HomeBaseController
{
    public function index()
    {
        $begin = I("get.begin");
        $end = I("get.end");
        if (!empty($begin) && !empty($end) ) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime($end)+86400-1;
        } else {
            $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
            $monthEnd = mktime(23,59,59,date("m"),date("d"),date("Y"));
            if (date("d") >= date("t")) {
                $monthEnd = mktime(23,59,59,date("m"),date("t"),date("Y"));
            }
        }
        //记录统计日期
        $offset = ceil(($monthEnd - $monthStart)/86400);
        for ($i = 0; $i < $offset; $i++) {
            $day = date("Ymd",strtotime("+$i day",$monthStart));
            $num = date("d",strtotime("+$i day",$monthStart));
            $date[$day] = ($num)."日";
        }
        //查询结果
        $result = D("QcInfo")->getQcItemStat($monthStart,$monthEnd);

        //汇总数据
        $sums = array();
        foreach ($result as $key => $value) {
            //按错误项汇总
            $sums['item'][$value['id']] += intval($value['count']);
            //按错误分类汇总
            $sums['item_parent'][$value['parentid']] += intval($value['count']);
            //总计
            $sums['total'] += intval($value['count']);
        }

        //获取列表数据
        foreach ($result as $key => $value) {
            if (!array_key_exists($value["parentid"], $list["child"])) {
                $list["child"][$value["parentid"]]["id"] = $value["parentid"];
                $list["child"][$value["parentid"]]["name"] = $value["pname"];
            }
            if (!array_key_exists($value["id"],$list[$value["parentid"]]["child"])) {
                $list["child"][$value["parentid"]]["child"][$value["id"]]["id"] = $value["id"];
                $list["child"][$value["parentid"]]["child"][$value["id"]]["name"] = $value["name"];
            }
            for ($i = 0; $i < $offset; $i++) {
                $day = date("Ymd",strtotime("+$i day",$monthStart));
                if ($day == $value["time"]) {
                    $list["child"][$value["parentid"]]["child"][$value["id"]]["date"][$day]["id"] = $value["id"];
                    $list["child"][$value["parentid"]]["child"][$value["id"]]["date"][$day]["name"] = $value["name"];
                    $list["child"][$value["parentid"]]["child"][$value["id"]]["date"][$day]["count"] += $value["count"];
                    $list["child"][$value["parentid"]]["date"][$day]["count"] += $value["count"];
                    $list["date"][$day]["count"] += $value["count"];
                } else {
                    $list["child"][$value["parentid"]]["child"][$value["id"]]["date"][$day]["id"] = $value["id"];
                    $list["child"][$value["parentid"]]["child"][$value["id"]]["date"][$day]["name"] = $value["name"];
                    $list["child"][$value["parentid"]]["child"][$value["id"]]["date"][$day]["count"] += 0;
                    $list["child"][$value["parentid"]]["date"][$day]["count"] += 0;
                    $list["date"][$day]["count"] += 0;
                }
            }
        }
        //获取图表数据
        foreach ($list["child"] as $key => $value) {
            foreach ($value["child"] as $val) {
                $chartData[$key]['id'] = $value['id'];
                $chartData[$key]['name'] = $value['name'];
                if (!in_array($val['name'],$chartData["caption"])) {
                    $chartData[$key]["caption"][$val["id"]] = $val['name'];
                }
                foreach ($val["date"] as $k=>$v) {
                    $chartData[$key]['item'][$v['id']]["name"] = $v["name"];
                    $chartData[$key]['item'][$v['id']]["date"][$k]["count"] += $v["count"];
                    $chartData[$key]['item'][$v['id']]['all']["count"] +=$v['count'];
                    $chartData[$key]['count'] +=$v['count'];
                }
            }
        }

        //模板赋值
        $this->assign("date",$date);
        $this->assign("list",$list);
        $this->assign("sums",$sums);
        $this->assign("chartData",$chartData);
        $this->display();
    }

    /**
     * 质检结论错误明细统计
     * @return [type] [description]
     */
    public function qcconclusionstat()
    {
        //获取客服列表
        $info['ke_fu'] = D('Adminuser')->getKfList(true);
        //获取对接客服列表
        $info['docking'] = D('Adminuser')->getDockingKfList();
        //获取对接团列表
        $info['duijie'] = D('Adminuser')->getAdminuserListByUid(104);
        //获取客服团/师
        $info['groups'] = $this->getKfGroupInfo();
        //获取质检人员列表
        $info['zhi_jian'] = D('Adminuser')->getAdminuserListByUid(array(23,99));
        //获取质检项
        $info['items'] = $this->getQcItem();

        //筛选项
        $order_id = I('get.order_id');
        $start_time = I('get.start_time'); strtotime();
        $start_time = empty($start_time) ? strtotime(date('Y-m-01')) : strtotime($start_time);
        $end_time = I('get.end_time');
        $end_time = empty($end_time) ? time() : strtotime('+1 day', strtotime($end_time));
        $op_uid = I('get.op_uid');
        $kf_manager = I('get.kf_manager');
        $kf_group = I('get.kf_group');
        $kf_id = I('get.kf_id');
        $docking_group = I('get.docking_group');
        $docking_id = I('get.docking_id');
        $remark = I('get.remark');
        $error = I('get.error');
        $type = I('get.type');
        $item = I('get.item');
        $qctype = I('get.qctype');

        //获取列表
        $count = D("QcInfo")->getQcConclusionErrorDetailCount($order_id, $start_time, $end_time, $op_uid, $kf_manager, $kf_group, $kf_id, 0, $docking_group, $docking_id, $remark, $error, $type, $item,$qctype);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $show   = $p->show();
            $result = D("QcInfo")->getQcConclusionErrorDetailList($order_id, $start_time, $end_time, $op_uid, $kf_manager, $kf_group, $kf_id, 0, $docking_group, $docking_id, $remark, $error, $type, $item,$qctype, $p->firstRow, $p->listRows);
            foreach ($result as $key => $value) {
                if (!array_key_exists($value["order_id"],$list)) {
                    $list[$value["order_id"]] = $value;
                }
                if (!empty($value["items"])) {
                    if ($value["group"] == 1) {
                        $list[$value["order_id"]]["kfmark"] = 1;
                    } elseif ($value["group"] == 2) {
                        $list[$value["order_id"]]["dockingmark"] = 1;
                    }
                    $list[$value["order_id"]]["child"][$value['group']] = $value["items"];
                }
                $arr = array("\r\n","\r","\n");
                $list[$value["order_id"]]["remark2"] = str_replace($arr,"<br/>",$value["remark2"]);
                $list[$value["order_id"]]["remark"] = str_replace($arr,"<br/>",$value["remark"]);
            }
        }

        //模板赋值
        $vars = array(
            'list' => $list,
            'show' => $show
        );
        //远程中心人员查看时,隐藏质检项目
        //获取部门下所有的角色ID
        $result = D("RbacRole")->getRoleListByDept(22);
        foreach ($result as $key => $value) {
            $ids[] = $value["role_id"];
        }
       if(!in_array(session("uc_userinfo.uid"),$ids)){
           $zjshow = 1;
       }else{
           $zjshow = 2;
       }
        $this->assign('zjshow', $zjshow);
        $this->assign('info', $info);
        $this->assign('vars', $vars);
        $this->display();
    }

    /**
     * 质检判定错误明细统计
     */
    public function qcqualityconclusionstat()
    {
        //获取质检人员列表
        $info['zhi_jian'] = D('Adminuser')->getAdminuserListByUid(array(23,99));
        $list = $this->getQcQualityConclusionStat(I("get.id"),I("get.begin"),I("get.end"),I("get.qc"));
        $this->assign('info', $info);
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 质检判定错误汇总
     * @param string $value [description]
     */
    public function qcqualityconclusionallstat()
    {
         //获取质检人员列表
        $info['zhi_jian'] = D('Adminuser')->getAdminuserListByUid(array(23,99));
        //筛选条件
        $begin = I("get.begin");
        $end = I("get.end");
        $qc = I("get.qc");
        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime("+1 day",strtotime($end))-1;
        } else {
            $time = time();
            $monthStart = mktime(0,0,0,date("m",$time),1,date("Y",$time));
            $monthEnd = mktime(23,59,59,date("m",$time),date("d",$time),date("Y",$time));
        }
        //查询列表
        $result = D("QcInfo")->getQcQualityConclusionStat(null,$monthStart,$monthEnd,$qc);
        $list = array();
        //以人为维度
        if (!empty($qc)){
            foreach ($result as $key => $value) {
                $list[$value["time"]]["time"] = $value["time"];
                $list[$value["time"]]["op_name"] = $value["op_name"];
                $list[$value['time']]["count"] ++;
                $list[$value['time']]["25"] += $value["25"];
                $list[$value['time']]["26"] += $value["26"];
                $list[$value['time']]["27"] += $value["27"];
                $list[$value['time']]["28"] += $value["28"];
                $list[$value['time']]["29"] += $value["29"];
                $list[$value['time']]["30"] += $value["30"];
                $list[$value['time']]["31"] += $value["31"];
                $list[$value['time']]["32"] += $value["32"];
            }
        } else {
            foreach ($result as $key => $value) {
                $list[$value['op_uid']]["op_name"] = $value["op_name"];
                $list[$value['op_uid']]["count"] ++;
                $list[$value['op_uid']]["25"] += $value["25"];
                $list[$value['op_uid']]["26"] += $value["26"];
                $list[$value['op_uid']]["27"] += $value["27"];
                $list[$value['op_uid']]["28"] += $value["28"];
                $list[$value['op_uid']]["29"] += $value["29"];
                $list[$value['op_uid']]["30"] += $value["30"];
                $list[$value['op_uid']]["31"] += $value["31"];
                $list[$value['op_uid']]["32"] += $value["32"];
            }
        }

        //模板赋值
        $this->assign('info', $info);
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 质检负激励统计
     * @return [type] [description]
     */
    public function qcDeductionStat()
    {
        //获取质检评分项目
        $items = $this->getQcItem();
        //  必问八项虚填 调整为3项目
        foreach ($items as $key => $value) {
               foreach ($value["child"] as $k => $val) {
                   if($val['money']&&$val['money2']&&$val['money3']){
                        $items[$key]["child"][$k]["child"][] = array(
                            "parentid" => "1",
                            "name" => $val['money']."元/次"
                        );
                        $items[$key]["child"][$k]["child"][] = array(
                            "parentid" => "1",
                            "name" => $val['money2']."元/次"
                        );
                        $items[$key]["child"][$k]["child"][] = array(
                            "parentid" => "1",
                            "name" => $val['money3']."元/次"
                        );
                    }
               }
        }
        //客服师/团
        $kfgroup = $this->getKfGroupInfo();
        $list = $this->getQcDeductionStat($items,I("get.begin"),I("get.end"),I("get.kf"), I("get.group"), I("get.manager"),I("get.docking"));
        //获取跨列数
        foreach($list["chk_item"] as $key=>$val){
            $list["chk_item"][$key]['colspan'] = 0;
            foreach($val['child'] as $v){
                if(isset($v['child'])){
                    $list["chk_item"][$key]['colspan'] += count($v['child']);
                }else{
                    $list["chk_item"][$key]['colspan'] ++;
                }
            }
        }
        //获取跨列数
        foreach($list["docking_item"] as $key=>$val){
            $list["docking_item"][$key]['colspan'] = 0;
            foreach($val['child'] as $v){
                if(isset($v['child'])){
                    $list["docking_item"][$key]['colspan'] += count($v['child']);
                }else{
                    $list["docking_item"][$key]['colspan'] ++;
                }
            }
        }
        //对接客服团
        $dockingGroup = $this->getKfGroupInfo(104);
        $dockingKf = D("Adminuser")->getDockingKfList();

        //获取客服列表
        $kfList = D("Adminuser")->getKfList();
        $this->assign("dockingKf", $dockingKf);
        $this->assign("kfList", $kfList);
        $this->assign("kfgroup", $kfgroup);
        $this->assign("dockingGroup", $dockingGroup);
        $this->assign("list",$list["list"]);
        $this->assign("dockingList",$list["docking_list"]);
        $this->assign("items",$list["chk_item"]);
        $this->assign("dockingItems",$list["docking_item"]);
        $this->assign("groupList",$list["groupList"]);
        $this->assign("managerList",$list["managerList"]);
        $this->display();
    }

    /**
     * 400/53客服负激励
     */
    public function kfDeductionStat()
    {
        $list = $this->getKfDeductionStat(I("get.begin"),I("get.end"));
        $this->assign("list",$list["list"]);
        $this->assign("all",$list["all"]);
        $this->display();
    }

    /**
     * 异常订单预警
     * 总统计
     */
    public function qcException()
    {
        //获取城市异常订单数量
        $cityOrderResultType = 2;
        $dateArray = $this->getDate('',$cityOrderResultType);
        $cityOrder = $this->getCityOrderException($dateArray[0], $dateArray[1]);
        $cityOrderCount = $cityOrder['number'];

        //渠道分单率异常数量
        $channelBranchResultType = 6;
        $dateArray = $this->getDate('',$channelBranchResultType);
        $channelBranch = $this->getCanalBranchException($dateArray[0], $dateArray[1],'');
        $channelBranchCount = $channelBranch['number'];

        //手机号归属地与发单地不符数量
        $phoneBlongResultType = 5;
        $dateArray = $this->getDate('',$phoneBlongResultType);
        $phoneBlong = $this->getMobileLocaltionException($dateArray[0], $dateArray[1],'');
        $phoneBlongCount = $phoneBlong['number'];

        //读取数据库配置的值
        $config = $this->getExcetpionConfig(3);
        $defaultTelConfig = $config[1]['ipTelLocation']['config_value'];
        if($phoneBlongCount < $defaultTelConfig){
            $phoneBlongCount = 0;
        }

        //渠道订单异常统计
        //$chanelOrderResultType = 1;
        //$dateArray = $this->getDate('',$chanelOrderResultType);
        //$chanelOrder = $this->getChannelOrderDetailedException($dateArray[0], $dateArray[1],'');
        //$chanelOrderCount = $chanelOrder['number'];

        $this->assign('data',compact('cityOrderCount','channelBranchCount','phoneBlongCount','chanelOrderCount'));
        $this->display();
    }

    /**
     * 城市订单异常
     */
    public function cityorderexception()
    {
        $time = I("get.date", '');
        //时间对应类型
        $orderResultType = 2;
        $dateArray = $this->getDate($time,$orderResultType);
        //获取城市异常订单数据

        $myResult = $this->getCityOrderException($dateArray[0], $dateArray[1]);
        $this->assign("list", $myResult);
        $this->display();
        exit();
    }

    /**
     * IP归属地异常
     * return view
     */
    public function ipascription()
    {
        die();
        $this->display();
    }

    /**
     * IP归属地和发单地不符合
     */
    public function ipplace()
    {
         die();
        $this->display();
    }

    /**
     *  手机号归属地与发单地不符
     */
    public function phonebelong()
    {
        $time = I("get.date", '');
        $mobile = I("get.mobile", '');
        $page = I("get.p", '1');

        //结果表中对应类型
        $orderResultType = 5;

        $dateArray = $this->getDate($time,$orderResultType);

        //实时结果直接调用方法
        $myResult = $this->getMobileLocaltionException($dateArray[0], $dateArray[1], $mobile);
        $this->assign("list", $myResult);
        $this->display();
        exit();
    }

    /**
     * 渠道分单率异常
     */
    public function qudaobranch()
    {
        $time = I("get.date", '');
        $channel = I("get.channel", '');

        //结果表中对应类型
        $orderResultType = 6;

        $dateArray = $this->getDate($time,$orderResultType);

        //渠道分单率异常数据
        $myResult = $this->getCanalBranchException($dateArray[0], $dateArray[1], $channel);
        $this->assign("list", $myResult);
        $this->display();
        exit();

    }

    /**
     * 渠道订单异常(以渠道为单位进行详细统计)
     */
    public function qudaoorder()
    {
        $time = I("get.date", '');
        $channel = I("get.channel", '');
        //结果表中对应类型
        $orderResultType = 1;
        $dateArray = $this->getDate($time,$orderResultType);

        //渠道分单率异常数据
        $myResult = $this->getChannelOrderDetailedException($dateArray[0], $dateArray[1], $channel);
        $this->assign("list", $myResult);
        $this->display();
        exit();

    }

    /**
     * 异常订单预警设置
     */
    public function exceptionConfig()
    {
        if (I("tab") == "" || I("tab") == 1) {
            //获取城市设置
            $citys = $this->getCityExceptionConfig();

            $this->assign("list",$citys["list"]);
            $this->assign("citys",$citys["citys"]);
            $this->assign("city_default",$citys["default"]);
        }

        if (I("tab") == 2) {
            if (I("get.src") !== "") {
                $result = D("OrderSource")->getBySrc(I("get.src"));
                $src[] = array(
                    "id" => $result["alias"],
                    "text" => $result["alias"]
                );
            }
            //获取渠道分单率设置
            $srcInfo = $this->getSrcExceptionConfig(I("get.src"));

            $this->assign("src",$src);
            $this->assign("srcInfo",$srcInfo);
        }

        if (I("tab") == 3) {
             //获取其他设置
            $other = $this->getOtherException();
            $this->assign("other",$other);
        }

        $this->display("exceptionConfig");
    }

    public function cityExceptionUp()
    {
        if ($_POST) {
            $list = I("post.");
            $default = str_replace("%","",$list["default"]);
            if (!is_numeric($default)) {
                $default = 100;
            }
            $default = $default;

            $data[] = array(
                "config_key" => 'default',
                "config_name" => "默认城市增长率",
                "config_type" => 1,
                "config_state" => 1,
                "config_value" => $default
            );

            unset($list["default"]);
            //获取城市信息
            $result = D("Quyu")->getQuyuList();
            foreach ($result as $key => $value) {
                $citys[$value["cid"]] = $value["cname"];
            }

            foreach ($list as $key => $value) {
                $value = str_replace("%","",$value);
                if (is_numeric($value)) {
                    $value = $value;
                    if ($value != $default) {
                       $data[] = array(
                            "config_key" => $key,
                            "config_name" => $citys[$key]."增长率",
                            "config_type" => 1,
                            "config_state" => 1,
                            "config_value" => $value
                       );
                    }
                }
            }

            //删除原城市异常增长率
            D("QcExceptionConfig")->delExceptionConfig(1);

            //添加异常
            $i = D("QcExceptionConfig")->addExceptionConfig($data);
            if ($i !== false) {
                $this->ajaxReturn(array("status" => 1,"info"=>"保存成功!"));
            }
            $this->ajaxReturn(array("status" => 0,"info" => "操作失败！"));
        }
    }

    public function srcExceptionUp()
    {
        if ($_POST) {
            $list = I("post.");
            foreach ($list as $key => $value) {
                if (strpos($key,"_charge") > 0) {
                   $explode = explode("_",$key);
                   $charge[$explode[0]] = $value;
                   unset($list[$key]);
                }
            }

            foreach ($list as $key => $value) {
                if (strpos($key,"_name") > 0) {
                   $explode = explode("_",$key);
                   $name[$explode[0]] = $value;
                   unset($list[$key]);
                }
            }

            $free_default = str_replace("%","",$list["free_default"]);
            $pay_default = str_replace("%","",$list["pay_default"]);
            unset($list["free_default"]);
            unset($list["pay_default"]);

            if (!is_numeric($free_default)) {
                $free_default = 100;
            }

            if (!is_numeric($pay_default)) {
                $pay_default = 100;
            }

            $free_default = $free_default;
            $pay_default = $pay_default;

            //获取设置项
            $config = $this->getExcetpionConfig(2);

            $freeConfig = $config[1];
            $payConfig = $config[2];

            if (count($config) > 0) {
                $nowList[] = array(
                    "id" =>  $freeConfig["default"]["id"],
                    "value" => $free_default,
                    "charge" => 1
                );

                $nowList[] = array(
                    "id" =>  $payConfig["default"]["id"],
                    "value" => $pay_default,
                    "charge" => 2
                );
            } else {
                $data[] = array(
                    "config_key" => 'default',
                    "config_name" => "付费默认分单率",
                    "config_type" => 2,
                    "config_state" => 2,
                    "config_value" => $pay_default
                );

                $data[] = array(
                    "config_key" => 'default',
                    "config_name" => "免费费默认分单率",
                    "config_type" => 2,
                    "config_state" => 1,
                    "config_value" => $free_default
                );
            }

            unset($freeConfig["default"]);
            unset($payConfig["default"]);

            //将原有的设置分离
            foreach ($list as $key => $value) {
                $value = str_replace("%","",$value);
                if (array_key_exists($key,$freeConfig)) {
                    if ($value != $free_default && !empty($value)) {
                        $nowList[] = array(
                            "id" =>  $freeConfig[$key]["id"],
                            "value" => $value,
                            "charge" => 1
                        );
                        unset($list[$key]);
                        unset($freeConfig[$key]);
                    } else {
                        $ids[] = $freeConfig[$key]["id"];
                    }
                } elseif (array_key_exists($key,$payConfig)) {
                    if ($value != $pay_default && !empty($value)) {
                        $nowList[] = array(
                            "id" =>  $payConfig[$key]["id"],
                            "value" => $value,
                            "charge" => 2
                        );
                        unset($list[$key]);
                        unset($payConfig[$key]);
                    }  else {
                        $ids[] = $payConfig[$key]["id"];
                    }
                }
            }

            foreach ($list as $key => $value) {
                $value = str_replace("%","",$value);
                if (is_numeric($value)) {
                    if ($charge[$key] == 1) {
                        if ($value == $free_default) {
                            unset($list[$key]);
                        }
                    } else {
                        if ($value == $pay_default) {
                            unset($list[$key]);
                        }
                    }
                }
            }

            foreach ($list as $key => $value) {
                $value = str_replace("%","",$value);
                if (is_numeric($value)) {
                    if ($charge[$key] == 1) {
                        //免费
                        $data[] = array(
                            "config_key" => $key,
                            "config_name" => $name[$key]."分单率",
                            "config_type" => 2,
                            "config_state" => 1,
                            "config_value" => $value
                        );
                    } else {
                        //付费
                        $data[] = array(
                            "config_key" => $key,
                            "config_name" => $name[$key]."分单率",
                            "config_type" => 2,
                            "config_state" => 2,
                            "config_value" => $value
                        );
                    }
                }
            }

            //删除多余的数据
            if (count($ids) > 0) {
               D("QcExceptionConfig")->delExceptionConfig(2,$ids);
            }

            //修改原有数据
            foreach ($nowList as $key => $value) {
                $sub_data = array(
                    "config_value" => $value["value"]
                );
                D("QcExceptionConfig")->updateExceptionConfig($value["id"],$sub_data);
            }

            //添加数据
            D("QcExceptionConfig")->addExceptionConfig($data);
            $this->ajaxReturn(array("status" => 1,"info"=>"保存成功!"));
        }
    }

    public function otherExceptionUp()
    {
        if ($_POST) {
            $list = array(
                "ipLocation" => I("post.ipLocation"),
                "ipOrderLocation" => I("post.ipOrderLocation"),
                "ipTelLocation" => I("post.ipTelLocation"),
                "ip" => I("post.ip"),
                "email" => I("post.email")
            );

            $list["ip"] = str_replace("，",",",$list["ip"]);

            $list["email"] = str_replace(array("\r\n", "\r", "\n"),",",$list["email"]);

            $default = array(
                "ipLocation" => "IP归属地出现次数",
                "ipOrderLocation" => "IP归属地与发单地不符",
                "ipTelLocation" => "手机号归属地与发单地不符",
                "ip" => "IP异常归属地设置",
                "email" => "邮箱提醒设置"
            );


            foreach ($list as $key => $value) {
                $data[] = array(
                    "config_key" => $key,
                    "config_name" => $default[$key],
                    "config_type" => 3,
                    "config_state" => 1,
                    "config_value" => $value
                );
            }

            //删除原有设置
            D("QcExceptionConfig")->delExceptionConfig(3);

            //添加数据
            $i = D("QcExceptionConfig")->addExceptionConfig($data);
            if ($i !== false) {
                $this->ajaxReturn(array("status" => 1,"info"=>"保存成功!"));
            }
            $this->ajaxReturn(array("status" => 0,"info" => "操作失败！"));
        }
    }

    /**
     * 获取负激励数据
     * @param  [type] $items   [质检项]
     * @param  [type] $begin   [开始时间]
     * @param  [type] $end     [结束时间]
     * @param  [type] $kf      [审核客服ID]
     * @param  [type] $group   [客服组]
     * @param  [type] $manager [客服师]
     * @param  [type] $dockingkf [对接客服ID]
     * @return mix
     */
    private function getQcDeductionStat($items,$begin,$end,$kf,$group,$manager,$dockingkf)
    {
        $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        $monthEnd = mktime(23,59,59,date("m"),date("d"),date("Y"));
        if (!empty($begin) && !empty($end) ) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime($end)+86400-1;
        }

        //获取订单质检有问题项目
        $result = D("QcInfo")->getQCInfoItemList($monthStart, $monthEnd,$kf,$group,$manager,$dockingkf);

        //将错误项分成对接错误项和审核错误项目
        foreach ($items as $key => $value) {
            foreach ($value["child"] as $val) {
                if ($val["group"] == 1) {
                    $chk_item[$key]["name"] = $value['name'];
                    $chk_item[$key]["child"][] = $val;
                } elseif ($val["group"] == 2) {
                    $chk_docking_item[$key]["name"] = $value['name'];
                    $chk_docking_item[$key]["child"][] = $val;
                }

                if ($val["welfare"] == 1) {
                   $errorItem[$val["id"]] = $val;
                }
            }
        }

        //客服名单
        foreach ($result as $key => $value) {
            if ($value["group"] == 1) {
                $list[$value["kf_id"]]["name"] = $value["kf_name"];
                $list[$value["kf_id"]]["group"] = $value["kf_group"];
                $list[$value["kf_id"]]["manager"] = $value["kf_manager"];
            }

            if ( $value["group"] == 2 ) {
                $dockingList[$value["docking_id"]]["name"] = $value["docking_name"];
                $dockingList[$value["docking_id"]]["group"] = $value["docking_group"];
            }
        }

        //将审核错误项和审核客服合并
        foreach ($list as $key => $value) {
            foreach ($chk_item as $k => $val) {
                foreach ($val["child"] as $v) {
                    if (count($v['child']) > 0) {
                        $list[$key]["item"][$v["id"]]["child"][0] = 0;
                        $list[$key]["item"][$v["id"]]["child"][1] = 0;
                        $list[$key]["item"][$v["id"]]["child"][2] = 0;
                        $list[$key]["item"][$v["id"]]["money"] = $v['money'];
                        $list[$key]["item"][$v["id"]]["money2"] = $v['money2'];
                        $list[$key]["item"][$v["id"]]["money3"] = $v['money3'];
                    } else {
                        $list[$key]["item"][$v["id"]]["count"] = 0;
                    }
                    $list[$key]["item"][$v["id"]]['welfare'] = $v['welfare'];
                }
            }
        }

        //合并对接客服和对接客服错误想
        foreach ($dockingList as $key => $value) {
            foreach ($chk_docking_item as $k => $val) {
                foreach ($val["child"] as $v) {
                    if (count($v['child']) > 0) {
                        $dockingList[$key]["item"][$v["id"]]["child"][0] = 0;
                        $dockingList[$key]["item"][$v["id"]]["child"][1] = 0;
                        $dockingList[$key]["item"][$v["id"]]["child"][2] = 0;
                        $dockingList[$key]["item"][$v["id"]]["money"] = $v['money'];
                        $dockingList[$key]["item"][$v["id"]]["money2"] = $v['money2'];
                        $dockingList[$key]["item"][$v["id"]]["money3"] = $v['money3'];
                    } else {
                        $dockingList[$key]["item"][$v["id"]]["count"] = 0;
                    }

                    $dockingList[$key]["item"][$v["id"]]['welfare'] = $v['welfare'];
                }
            }
        }

        //根据订单统计每个订单的扣钱金额
        foreach ($result as $key => $value) {
            //审核客服
            if ($value["group"] == 1) {
                $order[$value["order_id"]]["id"] = $value["kf_id"];
                $order[$value["order_id"]]["money"][] = $value["money"];

                if (isset( $list[$value["kf_id"]]["item"][$value["qc_item_id"]]["child"])) {
                    if ($value["money"] == $list[$value["kf_id"]]["item"][$value["qc_item_id"]]['money']) {
                        $list[$value["kf_id"]]["item"][$value["qc_item_id"]]["child"][0] ++;
                    } elseif ($value["money"] == $list[$value["kf_id"]]["item"][$value["qc_item_id"]]['money2']) {
                        $list[$value["kf_id"]]["item"][$value["qc_item_id"]]["child"][1] ++;
                    } elseif ($value["money"] == $list[$value["kf_id"]]["item"][$value["qc_item_id"]]['money3']) {
                        $list[$value["kf_id"]]["item"][$value["qc_item_id"]]["child"][2] ++;
                    }
                } else {
                    $list[$value["kf_id"]]["item"][$value["qc_item_id"]]["count"] ++;
                }
            } elseif ($value["group"] == 2) {
                //对接客服
                $dockingOrder[$value["order_id"]]["money"][] = $value["money"];
                $dockingOrder[$value["order_id"]]["id"] = $value["docking_id"];
                if (isset( $dockingList[$value["docking_id"]]["item"][$value["qc_item_id"]]["child"])) {
                    if ($value["money"] == $list[$value["kf_id"]]["item"][$value["qc_item_id"]]['money']) {
                        $list[$value["docking_id"]]["item"][$value["qc_item_id"]]["child"][0] ++;
                    } elseif ($value["money"] == $list[$value["kf_id"]]["item"][$value["qc_item_id"]]['money2']) {
                        $list[$value["docking_id"]]["item"][$value["qc_item_id"]]["child"][1] ++;
                    } elseif ($value["money"] == $list[$value["kf_id"]]["item"][$value["qc_item_id"]]['money3']) {
                        $list[$value["docking_id"]]["item"][$value["qc_item_id"]]["child"][2] ++;
                    }
                } else {
                    $dockingList[$value["docking_id"]]["item"][$value["qc_item_id"]]["count"] ++;
                }
            }
        }

        //汇总审核每个订单要扣罚的金额
        foreach ($order as $key => $value) {
            $list[$value["id"]]["money"] += max($value["money"]);
        }
        //上下备注不一致项，人均享受1次免罚次数
        foreach ($list as $key => $value) {
            //按团统计
            $groupList[$value["group"]]["manager"] = $value["manager"];
            $groupList[$value["group"]]["group"] = $value["group"];
            $groupList[$value["group"]]["money"] += $value["money"];

            //按师统计
            $managerList[$value["manager"]]["manager"] = $value["manager"];
            $managerList[$value["manager"]]["manager"] = $value["manager"];
            $managerList[$value["manager"]]["money"] += $value["money"];
            foreach ($value["item"] as $k => $val) {
                //有福利 1次免罚/每月
                if ($val['welfare'] == 1) {
                    if ($val["count"] > 0) {
                        $list[$key]["item"][$k]["count"] --;
                        $list[$key]["money"] -= $errorItem[$k]["money"];
                    }
                }

                if (isset($val['child'])) {
                    $groupList[$value["group"]]["item"][$k]["child"][0] += $val["child"][0];
                    $groupList[$value["group"]]["item"][$k]["child"][1] += $val["child"][1];
                    $groupList[$value["group"]]["item"][$k]["child"][2] += $val["child"][2];

                    $managerList[$value["manager"]]["item"][$k]["child"][0] += $val["child"][0];
                    $managerList[$value["manager"]]["item"][$k]["child"][1] += $val["child"][1];
                    $managerList[$value["manager"]]["item"][$k]["child"][2] += $val["child"][2];

                } else {
                    $groupList[$value["group"]]["item"][$k]["count"] +=  $list[$key]["item"][$k]["count"];
                    $managerList[$value["manager"]]["item"][$k]["count"] +=  $list[$key]["item"][$k]["count"];
                }
            }
        }

        foreach ($dockingOrder as $key => $value) {
            $dockingList[$value["id"]]["money"] += array_sum($value["money"]);
        }

        return array("list"=>$list,"docking_list"=>$dockingList,"chk_item"=>$chk_item,"docking_item"=>$chk_docking_item,"groupList"=>$groupList,"managerList" => $managerList);
    }

    /**
     * 工作量统计
     * @return [type] [description]
     */
    public function workStat()
    {
        //质检统计
        $list = $this->getWorkStat(I("get.begin"), I("get.end"));
        $this->assign("list",$list["list"]);
        $this->assign("all",$list["all"]);
        //抽检统计
        $list = $this->getSamplingWorkStat(I("get.begin"), I("get.end"));
        $this->assign("samplingList",$list["list"]);
        $this->assign("samplingAll",$list["all"]);
        //53、400客服统计
        $list = $this->getWorkStat400(I("get.begin"), I("get.end"));
        $this->assign("otherList",$list);
        $this->assign("index",I("get.index"));
        $this->display();
    }

    /**
     * 抽检工作量
     * @param  [type] $begin [开始时间]
     * @param  [type] $end   [结束时间]
     * @return [type]        [description]
     */
    private function getSamplingWorkStat($begin, $end)
    {
        $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        $monthEnd = mktime(23,59,59,date("m"),date("d"),date("Y"));

        if (!empty($begin) && !empty($end) ) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime($end)+86400-1;
        }

        $result = D("QcInfo")->getSamplingWorkStat($monthStart, $monthEnd);

        foreach ($result as $key => $value) {
            $result[$key]["all"] = $value["fen"] + $value["zen"] + $value["wx"];
            $all["count"] += $value["count"];
            $all["fen"] += $value["fen"];
            $all["zen"] += $value["zen"];
            $all["wx"] += $value["wx"];
            $all["all"] += $result[$key]["all"];
        }
        return array("list"=>$result,"all"=>$all);

    }

    /**
     * 质检工作量统计
     * @param  [type] $begin [开始时间]
     * @param  [type] $end   [结束时间]
     * @return array
     */
    private function getWorkStat($begin, $end)
    {
        $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        $monthEnd = mktime(23,59,59,date("m"),date("d"),date("Y"));

        if (!empty($begin) && !empty($end) ) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime($end)+86400-1;
        }

        $result = D("QcInfo")->getWorkStat($monthStart, $monthEnd);
        foreach ($result as $key => $value) {
            $result[$key]["all"] =  $value["fen"] + $value["zen"] + $value["wx"];
            $all["count"] += $value["count"];
            $all["fen"] += $value["fen"];
            $all["zen"] += $value["zen"];
            $all["wx"] += $value["wx"];
            $all["all"] += $result[$key]["all"];
        }
        return array("list"=>$result,"all"=>$all);
    }

    /**
     * 获取质检项目
     * @param  integer $type [质检项目类型]
     * @return array
     */
    private function getQcItem($type = 1)
    {
        $result = D("QcInfo")->getQcItem($type);
        foreach ($result as $key => $value) {
            if ($value["parentid"] == 0) {
                $item[$value["id"]]["id"] = $value["id"];
                $item[$value["id"]]["name"] = $value["name"];
            } else {
                $item[$value["parentid"]]["child"][] = $value;
            }
        }
        return $item;
    }

    /**
     * 质检判定错误明细统计
     * @param  [type] $id   [订单编号]
     * @param  [type] $date [质检日期]
     * @param  [type] $qc   [质检员]
     * @return [type]       [description]
     */
    private function getQcQualityConclusionStat($id,$begin,$end,$qc)
    {
        $monthEnd =  strtotime("+1 day",strtotime(date("Y-m-d")));
        $monthStart = strtotime("-7 day",$monthEnd);


        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime($end)+86400-1;
        }

        $result = D("QcInfo")->getQcQualityConclusionStat($id,$monthStart,$monthEnd,$qc);

        if (!empty($day)) {
            $time = date("Ymd");
            foreach ($result as $key => $value) {
                if ($value["time"] == $time) {
                    $list[] = $value;
                }
            }
        } else {
            $list = $result;
        }

        //记录统计日期
        $offset = ceil(($monthEnd - $monthStart)/86400);
        for ($i = 0; $i < $offset; $i++) {
            $day = date("Ymd",strtotime("+$i day",$monthStart));
            $num = date("d",strtotime("+$i day",$monthStart));
            $date[$day] = ($num)."日";
        }

        $chartData = array(
            "item" => array()
        );

        foreach ($result as $key => $value) {
            if (!array_key_exists($value['op_uid'],$chartData["caption"])) {
               $chartData["caption"][$value['op_uid']]["name"] = $value["op_name"];
            }
            $chartData["item"][$value['op_uid']]['op_uid'] = $value["op_uid"];
            $chartData["item"][$value['op_uid']]['name'] = $value["op_name"];
            $chartData["item"][$value['op_uid']]['date'][$value["time"]]["day"] = $value["time"]+0;
            $chartData["item"][$value['op_uid']]['date'][$value["time"]]["count"] += $value["25"];
            $chartData["item"][$value['op_uid']]['date'][$value["time"]]["count"] += $value["26"];
            $chartData["item"][$value['op_uid']]['date'][$value["time"]]["count"] += $value["27"];
            $chartData["item"][$value['op_uid']]['date'][$value["time"]]["count"] += $value["28"];
            $chartData["item"][$value['op_uid']]['date'][$value["time"]]["count"] += $value["29"];
            $chartData["item"][$value['op_uid']]['date'][$value["time"]]["count"] += $value["30"];
        }

        foreach ($chartData["item"] as $key => $value) {
            foreach ($value["date"] as $k => $val) {
                foreach ($date as $j => $v) {
                    if ($j != $k) {
                        $value["date"][$j]["day"] = $j;
                        $value["date"][$j]["count"] += 0;
                    }
                }
            }
            $chartData["item"][$key] = $value;
        }

        foreach ($chartData["item"] as $key => $value) {
            $edition = Array();
            foreach ($value["date"] as $k => $val) {
                $edition[] = $val["day"];
            }
            array_multisort($edition, SORT_ASC,$value["date"]);
            $chartData["item"][$key] = $value;
        }

        return array("list" => $list,"date" => $date,"chartData"=>$chartData);
    }

    /**
     * 质检判定错误汇总
     * @param  [type] $day [选择时间]
     * @param  [type] $qc  [选择质检]
     * @return [type]      [description]
     */
    private function getqcqualityconclusionallstat($begin,$end,$qc)
    {
        $time = time();
        $monthStart = mktime(0,0,0,date("m",$time),1,date("Y",$time));
        $monthEnd = mktime(23,59,59,date("m",$time),date("d",$time),date("Y",$time));

        if (!empty($day)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime("+1 day",strtotime($end))-1;
        }

        $result = D("QcInfo")->getQcQualityConclusionStat(null,$monthStart,$monthEnd,$qc);

        //以人为维度
        if (!empty($qc) && empty($day)){
            foreach ($result as $key => $value) {
                $list[$value["time"]]["time"] = $value["time"];
                $list[$value["time"]]["op_name"] = $value["op_name"];
                $list[$value['time']]["count"] ++;
                $list[$value['time']]["25"] += $value["25"];
                $list[$value['time']]["26"] += $value["26"];
                $list[$value['time']]["27"] += $value["27"];
                $list[$value['time']]["28"] += $value["28"];
                $list[$value['time']]["29"] += $value["29"];
                $list[$value['time']]["30"] += $value["30"];
                $list[$value['time']]["31"] += $value["31"];
                $list[$value['time']]["32"] += $value["32"];
            }

        } else {
            foreach ($result as $key => $value) {
                $list[$value['op_uid']]["op_name"] = $value["op_name"];
                $list[$value['op_uid']]["count"] ++;
                $list[$value['op_uid']]["25"] += $value["25"];
                $list[$value['op_uid']]["26"] += $value["26"];
                $list[$value['op_uid']]["27"] += $value["27"];
                $list[$value['op_uid']]["28"] += $value["28"];
                $list[$value['op_uid']]["29"] += $value["29"];
                $list[$value['op_uid']]["30"] += $value["30"];
                $list[$value['op_uid']]["31"] += $value["31"];
                $list[$value['op_uid']]["32"] += $value["32"];
            }
        }

        return $list;
    }

// ----------------------------------分割线------------------------------

    /*该处的审核团和对接团放在一个select的逻辑是2017-08-22日 14点 产品-庄哲(867303508) 定下的结果*/
    public function operateQuestion()
    {
        //时间判断
        $time_start = I('get.time_start');
        $time_end = I('get.time_end');
        if (!empty($time_start) && !empty($time_end)) {
            $time_start = strtotime(I('get.time_start'));
            $time_end = strtotime(I('get.time_end') . ' +1 day');
        } else {
            $time_start =strtotime(date('Y-m-d', strtotime('-30 days')));
            $time_end = strtotime(date('Y-m-d') . ' +1 day');
        }
        //获取对接团列表
        $info['dj_group'] = D('Adminuser')->getAdminuserListByUid(104);
        //默认审核团和对接团的数据都展示
        $info['tabs'] = 1;
        //客服师
        $kf_manager = intval(I('get.kf_manager'));
        //客服团
        $group = trim(I('get.group'));
        //列表显示类型：
        //当用户选择客服团时，根据用户所选的客服团类型（审核/对接），查询结果显示对应的字段。
        //当客服团同时包含审核团和对接团时，查询结果显示全部统计字段。
        //当用户同时选择了客服师和客服团时，以客服团为主要查询条件。
        if (!empty($group)) {
            if (false !== strpos($group, 'sh_')) {
                $kf_group = intval(explode('_', $group)[1]);
                if(empty($kf_manager)){
                    $kf_manager = intval(explode('_', $group)[2]);
                }
                $info['tabs'] = 2;
            } else if (false !== strpos($group, 'dj_')) {
                $docking_group = intval(explode('_', $group)[1]);
                $info['tabs'] = 3;
            }
        } else if (!empty($kf_manager)) {
            $info['tabs'] = 2;
        }
        //获取查询结果
        $result = D('QcInfo')->getQcInfoTelcenterQuestionStatListNew($time_start, $time_end, 0, $kf_group, $kf_manager, 0 , $docking_group);
        $days = $group = $manager = array();
        foreach ($result as $key => $value) {
            $value['kf_manager'] = explode(',', $value['kf_manager'])[0];
            //没有客服师和客服团的不统计 (2018年6月6号 产品需求 去掉客服团客服师的过滤)
//            if (empty($value['kf_id']) || empty($value['kf_group']) || empty($value['kf_manager'])) {
//                continue;
//            }
            $time = $value['date'];
            //按天统计
            $days[$time]['date']     = $time; //日期
            $days[$time]['sh_gt_cw'] += $value['sh_gt_cw']; //审核客服沟通错误
            $days[$time]['sh_jl_cw'] += $value['sh_jl_cw']; //审核客服记录错误
            $days[$time]['sh_cz_cw'] += $value['sh_cz_cw']; //审核客服操作错误
            $days[$time]['dj_cz_cw'] += $value['dj_cz_cw']; //对接客服操作错误
            $days[$time]['sh_zj_cw'] += $value['sh_zj_cw']; //审核客服错误汇总
            $days[$time]['dj_zj_cw'] += $value['dj_zj_cw']; //对接客服错误汇总
            $days[$time]['zj_cw']    += ($value['sh_zj_cw'] + $value['dj_zj_cw']); //所有客服错误汇总
            $days[$time]['zj_dd'] ++; //该天质检总订单
            //汇总数据处理
            $sums['date'][$time] = 1;
            $sums['sh_gt_cw']    += $value['sh_gt_cw'];
            $sums['sh_jl_cw']    += $value['sh_jl_cw'];
            $sums['sh_cz_cw']    += $value['sh_cz_cw'];
            $sums['dj_cz_cw']    += $value['dj_cz_cw'];
            $sums['sh_zj_cw']    += $value['sh_zj_cw'];
            $sums['dj_zj_cw']    += $value['dj_zj_cw'];
            $sums['zj_cw']       += ($value['sh_zj_cw'] + $value['dj_zj_cw']);
            $sums['zj_dd'] ++;
            //根据错误数量统计错误订单
            if (($value['sh_zj_cw'] > 0) || ($value['dj_zj_cw'] > 0)) {
                //总计错误订单
                $sums['zj_cw_dd'] ++;
                $days[$time]['zj_cw_dd'] ++; //该天质检总订单
                //如果审核错误大于0，则审核客服错误订单加1
                if ($value['sh_zj_cw'] > 0) {
                    $sums['sh_cw_dd'] ++;
                    $days[$time]['sh_cw_dd'] ++;
                }
                //如果对接错误大于0，则对接客服错误订单加1
                if ($value['dj_zj_cw'] > 0) {
                    $sums['dj_cw_dd'] ++;
                    $days[$time]['dj_cw_dd'] ++;
                }
            }
        }
        ksort($days);

        //获取团与师列表
        $manager = $sh_group = array();
        $temp = D("Adminuser")->getKfGroupInfo();
        foreach ($temp as $key => $value) {
            $sh_group[$value['kfgroup']] = $value;
            $manager[$value['manager_id']] = array(
                'id' => $value['manager_id'],
                'name' => $value['manager']
            );
        }
        $info['sh_group'] = $sh_group;
        $info['manager']  = $manager;
        $main['info']     = $info;
        //变量赋值
        $this->assign('sums', $sums);
        $this->assign('days', $days);
        $this->assign('main', $main);
        $this->display();
    }

    /**
     * 录音操作问题汇总按人统计
     */
    public function operateQuestionByPerson()
    {
        //时间判断
        $time_start = I('get.time_start');
        $time_end = I('get.time_end');
        if (!empty($time_start) && !empty($time_end)) {
            $time_start = strtotime(I('get.time_start'));
            $time_end = strtotime(I('get.time_end') . ' +1 day');
        } else {
            $time_start =strtotime(date('Y-m-d', strtotime('-30 days')));
            $time_end = strtotime(date('Y-m-d') . ' +1 day');
        }

        //客服师
        $kf_manager = intval(I('get.kf_manager'));
        //客服团
        $group = trim(I('get.group'));
        //客服人员
        $kf_id = trim(I('get.kf_id'));
        //列表显示类型：
        //当用户选择客服团时，根据用户所选的客服团类型（审核/对接），查询结果显示对应的字段。
        //当用户同时选择了客服师和客服团时，以客服团为主要查询条件。
        if (!empty($group)) {
            if (false !== strpos($group, 'sh_')) {
                $kf_group = intval(explode('_', $group)[1]);
                if(empty($kf_manager)){
                    $kf_manager =  intval(explode('_', $group)[2]);
                }
            }
        }
        //获取查询结果
        $result = D('QcInfo')->getQcInfoTelcenterQuestionStatListNew($time_start, $time_end, $kf_id, $kf_group, $kf_manager, 0 , '');
        foreach($result as $key=>$val){
            if(!empty($val["addtime"])){
                //添加账号大于90天
                if(time()-strtotime($val["addtime"])> 7776000 ){
                    $result[$key]["is_new"] = '2';
                    $old_result[$key] = $val;
                    $old_result[$key]["is_new"] = '2'; //不是新人了
                }else{
                    $result[$key]["is_new"] = '1';
                    $new_result[$key] = $val;
                    $new_result[$key]["is_new"] = '1'; //是新人
                }
            }
        }

        //客服人员
        $is_new = trim(I('get.is_new'));
        if(!empty($is_new)){
            if($is_new == 1){
                $result = $new_result;
            }elseif($is_new == 2){
                $result = $old_result;
            }
        }

        $list = [];
        foreach ($result as $key => $value) {
            $value['kf_manager'] = explode(',', $value['kf_manager'])[0];
            //没有客服师和客服团的不统计 (2018年6月6号 产品需求 去掉客服团客服师的过滤)
//            if (empty($value['kf_id']) || empty($value['kf_group']) || empty($value['kf_manager'])) {
//                continue;
//            }
            $person = $value['kf_id'];
            //按客服统计
            $list[$person]['mstate'] = $value['mstate']; //客服名称
            $list[$person]['muid'] = $value['muid'];
            $list[$person]['kf_name'] = $value['kf_name']; //客服名称
            $list[$person]['kf_id'] = $value['kf_id']; //客服id
            $list[$person]['is_new'] = $value['is_new']; //客服id
            $list[$person]['kf_group'] = $value['kf_group']; //客服组
            $list[$person]['kf_manager'] = $value['kf_manager']; //客服师
            $list[$person]['sh_gt_cw'] += $value['sh_gt_cw']; //审核客服沟通错误
            $list[$person]['sh_jl_cw'] += $value['sh_jl_cw']; //审核客服记录错误
            $list[$person]['sh_cz_cw'] += $value['sh_cz_cw']; //审核客服操作错误
            $list[$person]['sh_zj_cw'] += $value['sh_zj_cw']; //审核客服错误汇总
            $list[$person]['dj_zj_cw'] += $value['dj_zj_cw']; //对接客服错误汇总
            $list[$person]['zj_cw']    += ($value['sh_zj_cw'] ); //所有客服错误汇总
            $list[$person]['zj_dd'] ++; //该客服质检总订单

            //汇总数据处理
            $sums['sum'][$person] = 1;
            $sums['sh_gt_cw']    += $value['sh_gt_cw'];
            $sums['sh_jl_cw']    += $value['sh_jl_cw'];
            $sums['sh_cz_cw']    += $value['sh_cz_cw'];
            $sums['sh_zj_cw']    += $value['sh_zj_cw'];
            $sums['dj_zj_cw']    += $value['dj_zj_cw'];
            $sums['zj_cw']       += ($value['sh_zj_cw']);
            $sums['zj_dd'] ++;

            //根据错误数量统计错误订单
            if (($value['sh_zj_cw'] > 0) || ($value['dj_zj_cw'] > 0)) {
                //总计错误订单
                $sums['zj_cw_dd'] ++;
                $list[$person]['zj_cw_dd'] ++; //该人质检总订单
                //如果审核错误大于0，则审核客服错误订单加1
                if ($value['sh_zj_cw'] > 0) {
                    $sums['sh_cw_dd'] ++;
                    $list[$person]['sh_cw_dd'] ++;
                }
            }


        }

        //获取团与师列表
        $manager = $sh_group = array();
        $temp = D("Adminuser")->getKfGroupInfo();
        foreach ($temp as $key => $value) {
            $sh_group[$value['kfgroup']] = $value;
            $manager[$value['manager_id']] = array(
                'id' => $value['manager_id'],
                'name' => $value['manager'],
            );
        }

        foreach($sh_group as $val){
            $sh_manager[$val['manager_id']]['manager'] = $val['manager'];
            $sh_manager[$val['manager_id']]['groupList'][$val['kfgroup']]['name'] = $val['kfgroup'].'团|'.$val['name'];
        }
        $info['sh_group'] = $sh_group;
        $info['manager']  = $manager;
        $info['sh_manager']  = $sh_manager;
        $main['info']     = $info;

        //获取客服列表
        $info['ke_fu'] = D('Adminuser')->getKfList(true);
//        var_dump($list);
        $this->assign('list', $list);
        $this->assign('main', $main);
        $this->assign('info', $info);
        $this->assign('sums', $sums);
        $this->display();
    }

    /**
     * 录音详情统计
     */
    public function operatequestiondetail(){
        //时间判断
        $time_start = I('get.time_start');
        $time_end = I('get.time_end');
        if (!empty($time_start) && !empty($time_end)) {
            $time_start = strtotime(I('get.time_start'));
            $time_end = strtotime(I('get.time_end') . ' +1 day');
        } else {
            $time_start =strtotime(date('Y-m-d', strtotime('-30 days')));
            $time_end = strtotime(date('Y-m-d') . ' +1 day');
        }
        $vars['kf_id'] = 0;
        $vars['kf_group'] = 0;
        $vars['kf_manager'] = 0 ;
        $type = intval(I('get.type'));
        if($type == 1){
            $msg = '客服详情';
            $vars['kf_id'] = trim(I('get.kf_id'));
            //获取查询结果
            $info = $this->getkfDetail($time_start, $time_end,  $vars['kf_id']);
            $this->assign('stat', $info);
            //客服end
        }else if($type == 2){
            $msg = '客服团详情';
            $vars['kf_group'] = trim(I('get.kf_group'));
            //客服团start
            //获取查询结果
           $info = $this->getKfGroupDetail($time_start, $time_end, $vars['kf_group']);
            $this->assign('stat', $info);
            //客服团end
        }else if($type == 3){
            $msg = '客服师详情';
            $vars['kf_manager'] = trim(I('get.kf_manager'));
            //获取查询结果
            $info = $this->getKfManagerDetail($time_start, $time_end,$vars['kf_manager']);
            //变量赋值
            $this->assign('stat', $info);
        }
        $this->assign('vars',$vars);
        $this->assign('msg',$msg);
        $this->assign('type',$type);
        $this->display();
    }

    private function getkfDetail($time_start, $time_end, $kf_id){
        $result = D('QcInfo')->getQcInfoTelcenterQuestionStatListNew($time_start, $time_end, $kf_id);
        $list = [];
        foreach ($result as $key => $value) {
            $value['kf_manager'] = explode(',', $value['kf_manager'])[0];
            $person = date('Y-m', strtotime( $value['date']));
            //按客服统计
            $list[$person]['month'] = $person; //审核客服沟通错误
            $list[$person]['sh_gt_cw'] += $value['sh_gt_cw']; //审核客服沟通错误
            $list[$person]['sh_jl_cw'] += $value['sh_jl_cw']; //审核客服记录错误
            $list[$person]['sh_cz_cw'] += $value['sh_cz_cw']; //审核客服操作错误
            $list[$person]['sh_zj_cw'] += $value['sh_zj_cw']; //审核客服错误汇总
            $list[$person]['dj_zj_cw'] += $value['dj_zj_cw']; //对接客服错误汇总
            $list[$person]['zj_cw']    += ($value['sh_zj_cw']); //所有客服错误汇总
            $list[$person]['zj_dd'] ++; //该客服质检总订单
            //根据错误数量统计错误订单
            if (($value['sh_zj_cw'] > 0) || ($value['dj_zj_cw'] > 0)) {
                $list[$person]['zj_cw_dd'] ++; //该人质检总订单
                //如果审核错误大于0，则审核客服错误订单加1
                if ($value['sh_zj_cw'] > 0) {
                    $list[$person]['sh_cw_dd'] ++;
                }
            }
        }
        //预设的时间和月份
        $month = get_month_list($time_start, $time_end-1);
        rsort($month);
        foreach($month as $val){
            $info[$val] = $list[$val];
            $info[$val]['month'] = $val;
        }
        return $info;
    }

    private function getKfGroupDetail($time_start, $time_end,$kf_group){
        $result = D('QcInfo')->getQcInfoTelcenterQuestionStatListNew($time_start, $time_end, '',$kf_group);
        $stat = array();
        foreach ($result as $key => $value) {
            $value['kf_manager'] = explode(',', $value['kf_manager'])[0];
            //没有审核客服信息不统计
            if (empty($value['kf_id']) || empty($value['kf_group']) || empty($value['kf_manager'])) {
                continue;
            }
            //按团统计
            $group = date('Y-m', strtotime( $value['date']));
            $stat[$group]['month'] = $group;
            $stat[$group]['manager'] = $value['kf_manager'];
            $stat[$group]['sh_gt_cw'] += $value['sh_gt_cw']; //审核客服沟通错误
            $stat[$group]['sh_jl_cw'] += $value['sh_jl_cw']; //审核客服记录错误
            $stat[$group]['sh_cz_cw'] += $value['sh_cz_cw']; //审核客服操作错误
            $stat[$group]['sh_zj_cw'] += $value['sh_zj_cw']; //审核客服错误汇总
            $stat[$group]['zj_dd'] ++; //质检总订单
        }
        //预设的时间和月份
        $month = get_month_list($time_start, $time_end-1);
        rsort($month);
        foreach($month as $val){
            $info[$val] = $stat[$val];
            $info[$val]['month'] = $val;
        }
        return $info;
    }

    private function getKfManagerDetail($time_start, $time_end,$kf_manager){
        $result = D('QcInfo')->getQcInfoTelcenterQuestionStatListNew($time_start, $time_end,'','',$kf_manager);
        $stat = array();
        foreach ($result as $key => $value) {
            $value['kf_manager'] = explode(',', $value['kf_manager'])[0];
            //没有审核客服信息不统计
            if (empty($value['kf_id']) || empty($value['kf_group']) || empty($value['kf_manager'])) {
                continue;
            }

            $manager = date('Y-m', strtotime( $value['date']));
            //按师统计
            $stat[$manager]['sh_gt_cw'] += $value['sh_gt_cw']; //审核客服沟通错误
            $stat[$manager]['sh_jl_cw'] += $value['sh_jl_cw']; //审核客服记录错误
            $stat[$manager]['sh_cz_cw'] += $value['sh_cz_cw']; //审核客服操作错误
            $stat[$manager]['sh_zj_cw'] += $value['sh_zj_cw']; //审核客服错误汇总
            $stat[$manager]['zj_dd'] ++; //质检总订单
        }

        //获取审核团与审核师列表
        //预设的时间和月份
        $month = get_month_list($time_start, $time_end-1);
        rsort($month);
        foreach($month as $val){
            $info[$val] = $stat[$val];
            $info[$val]['month'] = $val;
        }
        return $info;
    }

    /**
     * 录音操作问题汇总统计
     */
    public function lyczwthztj()
    {
        //筛选条件获取
        $time_start = I('get.time_start');
        $time_end = I('get.time_end');
        if (!empty($time_start) && !empty($time_end)) {
            $time_start = strtotime(I('get.time_start'));
            $time_end = strtotime(I('get.time_end') . ' +1 day');
        } else {
            $time_start =strtotime(date('Y-m-d', strtotime('-30 days')));
            $time_end = strtotime(date('Y-m-d') . ' +1 day');
        }

        //获取查询结果
        $result = D('QcInfo')->getQcInfoTelcenterQuestionStatListNew($time_start, $time_end);
        $stat = array();
        foreach ($result as $key => $value) {
            $value['kf_manager'] = explode(',', $value['kf_manager'])[0];
            //没有审核客服信息不统计
            if (empty($value['kf_id']) || empty($value['kf_group']) || empty($value['kf_manager'])) {
                continue;
            }
            //按团统计
            $stat['group'][$value['kf_group']]['manager'] = $value['kf_manager'];
            $stat['group'][$value['kf_group']]['sh_gt_cw'] += $value['sh_gt_cw']; //审核客服沟通错误
            $stat['group'][$value['kf_group']]['sh_jl_cw'] += $value['sh_jl_cw']; //审核客服记录错误
            $stat['group'][$value['kf_group']]['sh_cz_cw'] += $value['sh_cz_cw']; //审核客服操作错误
            $stat['group'][$value['kf_group']]['sh_zj_cw'] += $value['sh_zj_cw']; //审核客服错误汇总
            $stat['group'][$value['kf_group']]['zj_dd'] ++; //质检总订单
            //按师统计
            $stat['manager'][$value['kf_manager']]['sh_gt_cw'] += $value['sh_gt_cw']; //审核客服沟通错误
            $stat['manager'][$value['kf_manager']]['sh_jl_cw'] += $value['sh_jl_cw']; //审核客服记录错误
            $stat['manager'][$value['kf_manager']]['sh_cz_cw'] += $value['sh_cz_cw']; //审核客服操作错误
            $stat['manager'][$value['kf_manager']]['sh_zj_cw'] += $value['sh_zj_cw']; //审核客服错误汇总
            $stat['manager'][$value['kf_manager']]['zj_dd'] ++; //质检总订单
            //汇总数据
            $sums['sh_gt_cw'] += $value['sh_gt_cw'];
            $sums['sh_jl_cw'] += $value['sh_jl_cw'];
            $sums['sh_cz_cw'] += $value['sh_cz_cw'];
            $sums['sh_zj_cw'] += $value['sh_zj_cw'];
            $sums['zj_dd'] ++;
        }
        ksort($stat['group']);
        ksort($stat['manager']);
        //获取审核团与审核师列表
        $sh_manager = $sh_group = array();
        $temp = D("Adminuser")->getKfGroupInfo();
        foreach ($temp as $key => $value) {
            $sh_group[$value['kfgroup']] = $value;
            $sh_manager[$value['manager_id']] = array(
                'id' => $value['manager_id'],
                'name' => $value['manager']
            );
        }
        foreach($sh_group as $val){
            $sh_manager[$val['manager_id']]['manager'] = $val['manager'];
            $sh_manager[$val['manager_id']]['groupList'][$val['kfgroup']]['name'] = $val['kfgroup'].'团|'.$val['name'];
        }

        $info['sh_group'] = $sh_group;
        $info['manager']  = $sh_manager;
        $info['sh_manager']  = $sh_manager;
        //变量赋值
        $this->assign('sums', $sums);
        $this->assign('stat', $stat);
        $this->assign('info', $info);
        $this->display();
    }

    /**
     * [teamDetailStat 团队问题点明细统计]
     * @return [type] [description]
     */
    public function teamDetailStat()
    {
        //获取问题点数组
        $result = M('qc_items')->field('id, name, parentid, group')->where(array('type' => 1))->order('px')->select();
        $item = build_tree(0, $result, 'parentid');
        //获取所有子类数据，用于生成图表
        $item_children = $item_sh = $item_dj = array();
        //对问题点归类，审核客服和对接客服分开
        foreach ($result as $key => $value) {
            if ($value['parentid'] == 0) {
                $item_dj[] = $value;
                $item_sh[] = $value;
            } else {
                $item_children[] = $value;
            }
            if ($value['group'] == 1) {
                $item_sh[] = $value;
            }
            if ($value['group'] == 2) {
                $item_dj[] = $value;
            }
        }
        //组合，用于前台展示
        $item = array(
            array(
                'name' => '审核客服',
                'children' => build_tree(0, $item_sh, 'parentid')
            ),
            array(
                'name' => '对接客服',
                'children' => build_tree(0, $item_dj, 'parentid')
            )
        );
        //计算表格rowspan数
        $rowspan = array();
        foreach ($item as $key => $value) {
            foreach ($value['children'] as $k => $v) {
                if (empty($v['children'])) {
                    unset($item[$key]['children'][$k]);
                } else {
                    $rowspan[$value['name']] = $rowspan[$value['name']] + count($v['children']);
                }
            }
        }

        //获取团队列表
        $team = D("Adminuser")->getKfTeam();
        //获取需要查看的客服id
        $user_ids = array();
        $kf_group = intval(I('get.kf_group'));
        //优先客服团-客服师
        if (!empty($kf_group)) {
            $user_ids = D('Adminuser')->getAdminuserIdsByKfgroup($kf_group);
        } else {
            $kf_manager = intval(I('get.kf_manager'));
            if (!empty($kf_manager)) {
                $user_ids = D('Adminuser')->getAdminuserIdsByKfmanager($kf_manager);
            }
        }

        //获取筛选时间
        $time_start = I('get.time_start');
        $time_end = I('get.time_end');
        if (!empty($time_start) && !empty($time_end)) {
            //如果不是同一个月
            $time_start = strtotime($time_start);
            $time_end = strtotime($time_end) + 86400;
        }
        if (empty($time_start) || empty($time_end)) {
            $time_start =strtotime(date('Y-m-01'));
            $time_end = strtotime(date('Y-m-d') . ' +1 day');
        }

        //获取抽检结果统计
        $result = D('QcInfo')->getQcInfoDetailStat($time_start, $time_end, $user_ids);

        //获取日期
        $date = array();
        $each_start = $time_start;
        $each_end = $time_end;
        while($each_start < $each_end){
            $date[] = trim(date('Y-m-d',$each_start),' ');
            $each_start += strtotime('+1 day',$each_start) - $each_start;
        }

        //获取信息
        $sums = $info  = array();
        $total = 0;
        foreach ($result as $key => $value) {
            $info[$value['item']][$value['date']] = $value['error_count'];
            $total += $value['error_count'];
            $sums['date'][$value['date']] += $value['error_count'];
            $sums['item'][$value['item']] += $value['error_count'];
        }

        /*S-生成图表*/
        $chart_series = $chart_legend = array();
        foreach ($item_children as $key => $value) {
            $series = array(
                'name' => $value['name'],
                'type' => 'line',
                'stack' => '总量'
            );
            $list = array();
            foreach ($date as $k => $v) {
                $list[] = empty($info[$value['id']][$v]) ? 0 : intval($info[$value['id']][$v]);
            }
            $series['data'] = $list;
            $chart_series[] = $series;
            $chart_legend[] = $value['name'];
            $chart_legend_selected[$value['name']] = ($key == 0) ? true : false;
        }
        $xAxis = array();
        //图表显示的时候只显示日期
        foreach ($date as $key => $value) {
            $xAxis[] = substr($value, 8) . '日';
        }

        //默认第一个选中
        $echarts = array(
            'legend'          => json_encode($chart_legend),
            'legend_selected' => json_encode($chart_legend_selected),
            'series'          => json_encode($chart_series),
            'xAxis'           => json_encode($xAxis)
        );
        /*E-生成图表*/

        //数据赋值
        $main['team']    = json_encode($team);
        $main['date']    = $date;
        $main['info']    = $info;
        $main['item']    = $item;
        $main['sums']    = $sums;
        $main['rowspan'] = $rowspan;
        $main['total']   = $total;
        $main['echarts'] = $echarts;
        $this->assign('main', $main);
        $this->display();
    }

    /**
     * [teamDetailStat 团队问题点汇总统计]
     * @return [type] [description]
     */
    public function teamCollectStat()
    {
        //获取筛选时间
        $time_start = I('get.time_start');
        $time_end = I('get.time_end');
        if (!empty($time_start) && !empty($time_end)) {
            //如果不是同一个月
            $time_start = strtotime($time_start);
            $time_end = strtotime($time_end) + 86400;
        }
        if (empty($time_start) || empty($time_end)) {
            $time_start =strtotime(date('Y-m-01'));
            $time_end = strtotime(date('Y-m-d') . ' +1 day');
        }
        //获取抽检结果统计
        $result = D('QcInfo')->getQcInfoCollectStat($time_start, $time_end);

        //获取日期
        $date = array();
        $each_start = $time_start;
        $each_end = $time_end;
        while($each_start < $each_end){
            $date[] = trim(date('Y-m-d',$each_start),' ');
            $each_start += strtotime('+1 day',$each_start) - $each_start;
        }

        //获取信息
        $info  = $sums = array();
        $total = 0;
        foreach ($result as $key => $value) {
            //错误总计
            $total += $value['error_count'];
            //按天汇总
            $sums['date'][$value['date']] += $value['error_count'];
            //按团汇总
            $sums['group'][$value['kf_group']] += $value['error_count'];
            //按师汇总
            $sums['manager'][$value['kf_manager']] += $value['error_count'];
            //获取团汇总
            $info['group'][$value['kf_group']][$value['date']] += $value['error_count'];
            //获取师汇总
            $info['manager'][$value['kf_manager']][$value['date']] += $value['error_count'];
        }

        //获取团与师列表
        $group = D("Adminuser")->getKfGroupInfo();
        $manager = array();
        foreach ($group as $key => $value) {
            $manager[$value['manager_id']] = array(
                'id' => $value['manager_id'],
                'name' => $value['manager']
            );
        }

        /*S-生成图表数据*/
        $chart_group_series = $chart_group_legend = $chart_manager_series = $chart_manager_legend = array();
        $xAxis = array();
        //图表显示的时候只显示日期
        foreach ($date as $key => $value) {
            $xAxis[] = substr($value, 8) . '日';
        }
        //生成客服团图表数据
        foreach ($group as $key => $value) {
            $group_series = array(
                'name' => '客服' . $value['kfgroup'] . '团',
                'type' => 'line',
                'stack' => $value['name'] . '师',
            );
            $list = array();
            foreach ($date as $k => $v) {
                $list[] = intval($info['group'][$value['kfgroup']][$v]);
            }
            $group_series['data'] = $list;
            $chart_group_series[] = $group_series;
            $chart_group_legend[] = '客服' . $value['kfgroup'] . '团';
        }
        //生成客服师图表数据
        foreach ($manager as $key => $value) {
            $manager_series = array(
                'name' => $value['name'] . '师',
                'type' => 'line',
                'stack' => $value['name'] . '师',
            );
            $list = array();
            foreach ($date as $k => $v) {
                $list[] = intval($info['manager'][$value['id']][$v]);
            }
            $manager_series['data'] = $list;
            $chart_manager_series[] = $manager_series;
            $chart_manager_legend[] = $value['name'] . '师';
        }
        $echarts = array(
            'group' => array(
                'legend' => json_encode($chart_group_legend),
                'series' => json_encode($chart_group_series),
                'xAxis'  => json_encode($xAxis)
            ),
            'manager' => array(
                'legend' => json_encode($chart_manager_legend),
                'series' => json_encode($chart_manager_series),
                'xAxis'  => json_encode($xAxis)
            )
        );
        /*E-生成图表数据*/

        //数据赋值
        $main['group']   = $group;
        $main['manager'] = $manager;
        $main['date']    = $date;
        $main['info']    = $info;
        $main['total']   = $total;
        $main['sums']    = $sums;
        $main['echarts'] = $echarts;
        $this->assign('main', $main);
        $this->display();
    }

    public function telcenterRecordStat()
    {
        //获取质检人员列表
        $uids = array();
        $main['zhi_jian'] = D('Adminuser')->getAdminuserListByUid(array(23,99));
        foreach ($main['zhi_jian'] as $key => $value) {
            $uids[] = $value['id'];
            $user[$value['id']] = $value;
        }

        //获取筛选时间
        $time_start = I('get.time_start');
        $time_end = I('get.time_end');
        if (!empty($time_start) && !empty($time_end)) {
            //如果不是同一个月
            $time_start = strtotime($time_start);
            $time_end = strtotime($time_end);
            if (date('Y-m', $time_start) != date('Y-m', $time_end)) {
                $time_start = $time_end = 0;
            }
            $time_end = $time_end + 86400;
        }
        if (empty($time_start) || empty($time_end)) {
            $time_start =strtotime(date('Y-m-01'));
            $time_end = strtotime(date('Y-m-d') . ' +1 day');
        }

        //获取查询人
        $zhi_jian_id = I('get.zhi_jian_id');
        if (empty($zhi_jian_id) || !in_array($zhi_jian_id, $uids)) {
            $zhi_jian_id = $uids;
        } else {
            $temp = $user[$zhi_jian_id];
            unset($user);
            $user[$zhi_jian_id] = $temp;
            $zhi_jian_id = array($zhi_jian_id);
        }

        $result = D('QcInfo')->getLogTelcenterRecordStat($time_start, $time_end, $zhi_jian_id);

        $sum = [];
        foreach ($result as $key => $value) {
            //分单
            if ($value['on'] == '4' && $value['type_fw'] == '1') {
                $user[$value['op_uid']]['info']['fen_dan']['order_sum'] += $value['order_sum'];
                $user[$value['op_uid']]['info']['fen_dan']['record_sum'] += $value['record_sum'];
                $user[$value['op_uid']]['info']['fen_dan']['record_click_number'] += $value['record_click_number'];
                $user[$value['op_uid']]['info']['fen_dan']['listen_record_time'] += $value['listen_record_time'];
                $user[$value['op_uid']]['sum']['order_sum'] += $value['order_sum'];
                $user[$value['op_uid']]['sum']['record_sum'] += $value['record_sum'];
                $user[$value['op_uid']]['sum']['record_click_number'] += $value['record_click_number'];
                $user[$value['op_uid']]['sum']['listen_record_time'] += $value['listen_record_time'];
                $sum['order_sum'] += $value['order_sum'];
                $sum['record_sum'] += $value['record_sum'];
                $sum['record_click_number'] += $value['record_click_number'];
                $sum['listen_record_time'] += $value['listen_record_time'];
            }
            //赠单
            if ($value['on'] == '4' && $value['type_fw'] == '2') {
                $user[$value['op_uid']]['info']['zeng_dan']['order_sum'] += $value['order_sum'];
                $user[$value['op_uid']]['info']['zeng_dan']['record_sum'] += $value['record_sum'];
                $user[$value['op_uid']]['info']['zeng_dan']['record_click_number'] += $value['record_click_number'];
                $user[$value['op_uid']]['info']['zeng_dan']['listen_record_time'] += $value['listen_record_time'];
                $user[$value['op_uid']]['sum']['order_sum'] += $value['order_sum'];
                $user[$value['op_uid']]['sum']['record_sum'] += $value['record_sum'];
                $user[$value['op_uid']]['sum']['record_click_number'] += $value['record_click_number'];
                $user[$value['op_uid']]['sum']['listen_record_time'] += $value['listen_record_time'];
                $sum['order_sum'] += $value['order_sum'];
                $sum['record_sum'] += $value['record_sum'];
                $sum['record_click_number'] += $value['record_click_number'];
                $sum['listen_record_time'] += $value['listen_record_time'];
            }
            //次新单
            if ($value['on'] == '0' && $value['on_sub'] == '9') {
                $user[$value['op_uid']]['info']['ci_xin_dan']['order_sum'] += $value['order_sum'];
                $user[$value['op_uid']]['info']['ci_xin_dan']['record_sum'] += $value['record_sum'];
                $user[$value['op_uid']]['info']['ci_xin_dan']['record_click_number'] += $value['record_click_number'];
                $user[$value['op_uid']]['info']['ci_xin_dan']['listen_record_time'] += $value['listen_record_time'];
                $user[$value['op_uid']]['sum']['order_sum'] += $value['order_sum'];
                $user[$value['op_uid']]['sum']['record_sum'] += $value['record_sum'];
                $user[$value['op_uid']]['sum']['record_click_number'] += $value['record_click_number'];
                $user[$value['op_uid']]['sum']['listen_record_time'] += $value['listen_record_time'];
                $sum['order_sum'] += $value['order_sum'];
                $sum['record_sum'] += $value['record_sum'];
                $sum['record_click_number'] += $value['record_click_number'];
                $sum['listen_record_time'] += $value['listen_record_time'];
            }
            //待定单
            if ($value['on'] == '2') {
                $user[$value['op_uid']]['info']['dai_ding_dan']['order_sum'] += $value['order_sum'];
                $user[$value['op_uid']]['info']['dai_ding_dan']['record_sum'] += $value['record_sum'];
                $user[$value['op_uid']]['info']['dai_ding_dan']['record_click_number'] += $value['record_click_number'];
                $user[$value['op_uid']]['info']['dai_ding_dan']['listen_record_time'] += $value['listen_record_time'];
                $user[$value['op_uid']]['sum']['order_sum'] += $value['order_sum'];
                $user[$value['op_uid']]['sum']['record_sum'] += $value['record_sum'];
                $user[$value['op_uid']]['sum']['record_click_number'] += $value['record_click_number'];
                $user[$value['op_uid']]['sum']['listen_record_time'] += $value['listen_record_time'];
                $sum['order_sum'] += $value['order_sum'];
                $sum['record_sum'] += $value['record_sum'];
                $sum['record_click_number'] += $value['record_click_number'];
                $sum['listen_record_time'] += $value['listen_record_time'];
            }
            //扫单
            if ($value['on'] == '0' && $value['on_sub'] == '8') {
                $user[$value['op_uid']]['info']['sao_dan']['order_sum'] += $value['order_sum'];
                $user[$value['op_uid']]['info']['sao_dan']['record_sum'] += $value['record_sum'];
                $user[$value['op_uid']]['info']['sao_dan']['record_click_number'] += $value['record_click_number'];
                $user[$value['op_uid']]['info']['sao_dan']['listen_record_time'] += $value['listen_record_time'];
                $user[$value['op_uid']]['sum']['order_sum'] += $value['order_sum'];
                $user[$value['op_uid']]['sum']['record_sum'] += $value['record_sum'];
                $user[$value['op_uid']]['sum']['record_click_number'] += $value['record_click_number'];
                $user[$value['op_uid']]['sum']['listen_record_time'] += $value['listen_record_time'];
                $sum['order_sum'] += $value['order_sum'];
                $sum['record_sum'] += $value['record_sum'];
                $sum['record_click_number'] += $value['record_click_number'];
                $sum['listen_record_time'] += $value['listen_record_time'];
            }
            //无效单
            if (in_array($value['on'], array('5', '6', '7', '8', '9', '98'))) {
                $user[$value['op_uid']]['info']['wu_xiao_dan']['order_sum'] += $value['order_sum'];
                $user[$value['op_uid']]['info']['wu_xiao_dan']['record_sum'] += $value['record_sum'];
                $user[$value['op_uid']]['info']['wu_xiao_dan']['record_click_number'] += $value['record_click_number'];
                $user[$value['op_uid']]['info']['wu_xiao_dan']['listen_record_time'] += $value['listen_record_time'];
                $user[$value['op_uid']]['sum']['order_sum'] += $value['order_sum'];
                $user[$value['op_uid']]['sum']['record_sum'] += $value['record_sum'];
                $user[$value['op_uid']]['sum']['record_click_number'] += $value['record_click_number'];
                $user[$value['op_uid']]['sum']['listen_record_time'] += $value['listen_record_time'];
                $sum['order_sum'] += $value['order_sum'];
                $sum['record_sum'] += $value['record_sum'];
                $sum['record_click_number'] += $value['record_click_number'];
                $sum['listen_record_time'] += $value['listen_record_time'];
            }
        }

        $main['user'] = $user;
        $main['sum'] = $sum;
        $this->assign('main', $main);
        $this->display();
    }

    /**
     * 质检管理-分增合规性统计
     */
    public function zjglfdhgxtj()
    {

        //获取质检人员列表
        $uids = array();
        $main['zhi_jian'] = D('Adminuser')->getAdminuserListByUid(array(23,99));
        foreach ($main['zhi_jian'] as $key => $value) {
            $uids[] = $value['id'];
            $user[$value['id']] = $value;
        }

        //筛选条件
        $conform_regulation = I('get.conform_regulation');
        if (!in_array($conform_regulation, array(1,2,3))) {
            $conform_regulation = '';
        }
        $time_start = I('get.time_start');
        if (!empty($time_start)) {
            $time_start = strtotime($time_start) == false ? '' : strtotime($time_start);
        }
        $time_end = I('get.time_end');
        if (!empty($time_end)) {
            $time_end = strtotime($time_end) == false ? '' : strtotime($time_end)+86400;
        }

        $op_uid = intval(I('get.op_uid'));

        //数据查询
        $count = D('QcInfo')->getQcInfoCountByConformRegulation($op_uid, $conform_regulation, $time_start, $time_end);
        $pageCount = 10;
        import('Library.Org.Util.Page');
        $page = new \Page($count,$pageCount);
        $main['info']['page'] =  $page->show();
        $main['info']['list'] = D('QcInfo')->getQcInfoListByConformRegulation($op_uid, $conform_regulation, $time_start, $time_end, $page->firstRow,$page->listRows);

        $main['user'] = $user;
        $this->assign('main', $main);
        $this->display();
    }

    /**
     *分赠合规性汇总统计
     */
    public function fzhgxtj()
    {
        //获取质检人员列表
        $uids = array();
        $main['zhi_jian'] = D('Adminuser')->getAdminuserListByUid(array(23,99));
        foreach ($main['zhi_jian'] as $key => $value) {
            $uids[] = $value['id'];
            $user[$value['id']] = $value;
        }
        //筛选条件
        $conform_regulation = I('get.conform_regulation');
        if (!in_array($conform_regulation, array(1,2,3))) {
            $conform_regulation = '';
        }
        $start_time = I('get.start_time');
        if (!empty($start_time)) {
            $start_time = strtotime($start_time) == false ? '' : strtotime($start_time);
        }
        $end_time = I('get.end_time');
        if (!empty($end_time)) {
            $end_time = strtotime($end_time) == false ? '' : strtotime($end_time)+86400;
        }
        //默认开始时间为每月1号
        if (empty($start_time)) {
            $start_time = strtotime(date('Y-m-01'));
        }
        $op_uid = intval(I('get.op_uid'));
        //获取列表
        $result = D('QcInfo')->getStatusByRegulationCollect($start_time, $end_time, $op_uid, $conform_regulation);
        $info = array();
        foreach ($result as $key => $value) {
            //只统计分单与赠单的
            if ('4' == $value['on']) {
                if ('1' == $value['type_fw'] || '2' == $value['type_fw']) {
                    $info[$value['op_name']]['op_name'] = $value['op_name'];
                    $info[$value['op_name']]['op_date'][date('Y-m-d', $value['time'])] = 1;
                    //汇总
                    $info[$value['op_name']]['all']++;
                    //分单
                    if ('1' == $value['type_fw']) {
                        $info[$value['op_name']]['fen']++;
                    }
                    //赠单
                    if ('2' == $value['type_fw']) {
                        $info[$value['op_name']]['zeng']++;
                    }
                }
            }
        }
        //质检员
        $vars['user'] = $user;
        //列表页
        $vars['info'] = $info;
        $this->assign('vars', $vars);
        $this->display();
    }

    /**
     * 订单质检时长统计
     * @return [type] [description]
     */
    public function qcTimeStat()
    {
        //获取质检人员列表
        $info['zhi_jian'] = D('Adminuser')->getAdminuserListByUid(array(23,99));
        $list = $this->getQcTimeStat(I("get.id"),I("get.begin"),I("get.end"));
        $this->assign("info",$info);
        $this->assign("list",$list);
        $this->display();
    }

    /**
     * 获取客服师团
     * @return array
     */
    private function getKfGroupInfo($uid = 31)
    {
        $edition = array();
        $result = D("Adminuser")->getKfGroupInfo($uid);
        foreach ($result as $key => $value) {
                $list["groups"][$value["id"]] = $value;
                $edition[] = $value["kfgroup"];
                if (!array_key_exists($value["manager_id"],$list["manager"])) {
                        $list["manager"][$value["manager_id"]] = array(
                                    "id" => $value["manager_id"],
                                    "name" => $value["manager"]
                        );
                }
        }
        return $list;
    }

    /**
     * 获取质检时间统计
     * @param  int $id    [质检人员ID]
     * @param  date $begin [质检开始时间]
     * @param  date $end   [质检结束时间]
     * @return array
     */
    private function getQcTimeStat($id,$begin,$end)
    {
        $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        $monthEnd = mktime(0,0,0,date("m"),date("t"),date("Y"));

        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime("+1 day", strtotime($end));
        }

        $list = D("QcInfo")->getQcTimeStat($id,$monthStart,$monthEnd);
        foreach ($list as $key => $value) {
            $list[$key]["fen_rate"] = round($value["fen_push_time"]/$value["fen_time"],2);
            $list[$key]["zen_rate"] = round($value["zen_push_time"]/$value["zen_time"],2);
            $list[$key]["wx_rate"] =round($value["wx_push_time"]/$value["wx_time"],2);
            $list[$key]["time_rate"] = round($value["push_time"]/$value["time_diff"],2);



            $list[$key]["fen_time"] = timediff($value["fen_time"]);
            $list[$key]["zen_time"] = timediff($value["zen_time"]);
            $list[$key]["wx_time"] = timediff($value["wx_time"]);
            $list[$key]["time_diff"] = timediff($value["time_diff"]);

            $list[$key]["avg_fen_time"] = timediff($value["fen_time"]/$value["fen_count"]);
            $list[$key]["avg_zen_time"] = timediff($value["zen_time"]/$value["zen_count"]);
            $list[$key]["avg_wx_time"] = timediff($value["wx_time"]/$value["wx_count"]);

            $list[$key]["avg_fen_push_time"] = timediff($value["fen_push_time"]/$value["fen_count"]);
            $list[$key]["avg_zen_push_time"] = timediff($value["zen_push_time"]/$value["zen_count"]);
            $list[$key]["avg_wx_push_time"] = timediff($value["wx_push_time"]/$value["wx_count"]);

            $list[$key]["fen_push_time"] = timediff($value["fen_push_time"]);
            $list[$key]["zen_push_time"] = timediff($value["zen_push_time"]);
            $list[$key]["wx_push_time"] = timediff($value["wx_push_time"]);
            $list[$key]["push_time"] = timediff($value["push_time"]);

            $list[$key]["avg_time"] = timediff($value["time_diff"]/$value["count"]);
            $list[$key]["avg_push_time"] = timediff($value["push_time"]/$value["count"]);
        }
        return $list;
    }

    /**
     * 53、400质检统计
     * @return [type] [description]
     */
    private function getWorkStat400($begin,$end)
    {
        $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        $monthEnd = mktime(0,0,0,date("m"),date("t"),date("Y"));

        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime("+1 day", strtotime($end));
        }

        $list = D("QcInfo")->getWorkStat400($monthStart, $monthEnd);

        foreach ($list as $key => $value) {
            $list[$key]["all"] = $value["400"] + $value["53"];
        }
        return $list;
    }

    private function getKfDeductionStat($begin,$end)
    {
        $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        $monthEnd = mktime(0,0,0,date("m"),date("t"),date("Y"));

        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime("+1 day", strtotime($end));
        }
        $result = D("QcInfo")->get400QCInfoItemList($monthStart,$monthEnd);

        $all = $list = [];
        foreach ($result as $key => $value) {
            if (!array_key_exists($value["kf_id"],$list)) {
                $list[$value["kf_id"]]["name"] = $value["name"];
            }

            if ($value["mark"] == '400' && !array_key_exists($value["id"],$list[$value["kf_id"]]['400']["ids"])) {
                $list[$value["kf_id"]]['400']["count"] ++;
                $list[$value["kf_id"]]['400']["ids"][$value["id"]] = $value["id"];
                $all["400"]["count"] ++;

            } else if ($value["mark"] == '53' && !array_key_exists($value["id"],$list[$value["kf_id"]]['53']["ids"])) {
                $list[$value["kf_id"]]['53']["count"] ++;
                $list[$value["kf_id"]]['53']["ids"][$value["id"]] = $value["id"];
                $all["53"]["count"] ++;
            }

            if ($value["mark"] == '400') {
                $list[$value["kf_id"]]['400']["child"][$value["type"]]["moneycount"] += $value["moneycount"];

                $list[$value["kf_id"]]['400']["moneycount"] += $value["moneycount"];
                $list[$value["kf_id"]]['400']["rate"] = round($list[$value["kf_id"]]['400']["moneycount"] / $list[$value["kf_id"]]['400']["count"],2)*100;

                $all["400"]["child"][$value["type"]]["moneycount"] += $value["moneycount"];
                $all["400"]["moneycount"] += $value["moneycount"];
                $all["400"]["rate"] = round($all["400"]["moneycount"] /  $all["400"]["count"],2)*100;

            } else if ($value["mark"] == '53') {
                $list[$value["kf_id"]]['53']["child"][$value["type"]]["moneycount"] += $value["moneycount"];

                $list[$value["kf_id"]]['53']["moneycount"] += $value["moneycount"];
                $list[$value["kf_id"]]['53']["rate"] = round($list[$value["kf_id"]]['53']["moneycount"] / $list[$value["kf_id"]]['53']["count"],2)*100;

                $all["53"]["child"][$value["type"]]["moneycount"] += $value["moneycount"];
                $all["53"]["moneycount"] += $value["moneycount"];
                $all["53"]["rate"] = round($all["400"]["moneycount"] /  $all["400"]["count"],2)*100;
            }
            $list[$value["kf_id"]]["all_money_count"] =  $list[$value["kf_id"]]['53']["moneycount"]+  $list[$value["kf_id"]]['400']["moneycount"];
            $all["all_money_count"] =   $all["400"]["moneycount"] +  $all["53"]["moneycount"];
        }

        return array("list"=>$list,"all"=>$all);
    }

    /**
     * 获取城市异常订单数据（分页）
     * @param $date[筛选日期]
     * @return array
     */
    private function getCityOrderException($start_time,$end_time)
    {
        //1.查询城市订单信息
        $result = D("Orders")->getCityOrderExceptionList($start_time,$end_time);
        $prevDay = date("Y-m-d",$start_time);
        $nowDay = date("Y-m-d",$end_time);
        $list = [];
        //2.循环数据，写入结果数组
        foreach ($result as $key => $value) {
            $list[$value["cs"]]["cname"] = $value["cname"];
            $list[$value["cs"]][$value["date"]] = $value["count"];
        }

        //3.获取城市异常项配置
        $config = $this->getExcetpionConfig(1);
        $defaultConfig = $config[1];
        //4.循环计算订单增长率，并判断是否超过预警值，不超过去掉结果
        foreach ($list as $key => $value) {
            //计算订单增长率 （当天发单-前一天发单）/前一天发单*100%
            $value[$nowDay] = isset($value[$nowDay])?$value[$nowDay]:0;
            $value[$prevDay] = isset($value[$prevDay])?$value[$prevDay]:0;

            if ($value[$prevDay] == 0){
                $rate = 0;
            }else{
                $rate = round(($value[$nowDay] - $value[$prevDay])/$value[$prevDay],4)*100;
            }
            $list[$key]["rate"] = $rate;
            if (array_key_exists($key,$defaultConfig)) {
                $base_rate = $defaultConfig[$key]["config_value"];
            } else {
                $base_rate = $defaultConfig['default']["config_value"];
            }

            if ($rate <= $base_rate) {
                unset($list[$key]);
            }
        }

        //5.统计最终结果，物理分页并返回
        $count = count($list);
        if ($count > 50) {
            //物理分页
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $show    = $p->show();
            $list = array_slice($list, $p->firstRow,$p->listRows);
        }
        return array("list" => $list,"page"=>$show ,'number'=>$count);
    }

    /**
     * 获取渠道分单率异常记录（分页）
     * @param $date[筛选日期]
     * @param $channel[查询的渠道(模糊查询)]
     * @return array
     */
    private function getCanalBranchException($start_time,$end_time,$channel)
    {
        //1.查询所有的渠道(或选择的渠道)
        $allChannelList = D("Orders")->getAllChannelList($channel);
        foreach ($allChannelList as $value) {
            $list[$value["src"]]['src'] = $value["src"];            //渠道标识
            $list[$value["src"]]['charge'] = $value["charge"];      //渠道付费标志
            $list[$value["src"]]['channel'] = $value["name"];       //渠道名称
            $list[$value["src"]]['alias'] = $value["alias"];        //渠道代号
            $list[$value["src"]]['now_order_count'] = 0;            //当前渠道发单量
            $list[$value["src"]]['now_real_order_count'] = 0;       //当前渠道分单量
        }
        //2.获取每个渠道当前时间发单量
        $nowresult = D("Orders")->getChannelOrderCountdList($start_time, $end_time, $channel);
        foreach ($nowresult as $key => $value) {
            $list[$value["src"]]["now_order_count"] = $value["count"];
        }
        //3.获取每个渠道当前时间分单量
        $nowResult2 = D("Orders")->getChannelRealOrderCountdList($start_time, $end_time, $channel);
        foreach ($nowResult2 as $key => $value) {
            $list[$value["src"]]["now_real_order_count"] = $value["count"];
        }
        //4.计算分单率
        foreach ($list as $key=>$value)
        {
            if ( $value['now_order_count'] == 0 &&  $value['now_real_order_count'] == 0 ){
                unset($list[$key]);
            }else{
                if ($list[$key]['now_order_count'] == 0){
                    $list[$key]['now_rate'] = 0;
                }else {
                    $list[$key]['now_rate'] = round($list[$key]['now_real_order_count']/$list[$key]['now_order_count'],4)*100;
                }
            }
        }

        //5 判断分单率是不是符合条件
        $config = $this->getExcetpionConfig(2);//数组1免费数组 数组2付费数组
        $defaultNoCharge = $config[1]['default'];
        $defaultCharge = $config[2]['default'];

        foreach ($list as $src => $value){
            if (($value['charge'] == 1 || $value['charge'] == 0) && array_key_exists($src,$config[1])) {    //免费判断
                $base_rate = $config[1][$src]["config_value"];
            } elseif($value['charge'] == 2 && array_key_exists($src,$config[2])){                           //付费判断
                $base_rate = $config[2][$src]["config_value"];
            } elseif ($value['charge'] == 2){                                                               //付费默认
                $base_rate = $defaultCharge["config_value"];
            } else{                                                                                         //免费默认
                $base_rate = $defaultNoCharge["config_value"];
            }
            $list[$src]['base_rate'] = $base_rate;
            if ( $value['now_rate'] <= $base_rate) {
                unset($list[$src]);
            }
        }

        $count = count($list);
        if ($count > 50) {
            //物理分页
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $show    = $p->show();
            $list = array_slice($list, $p->firstRow,$p->listRows);
        }
        return array("list" => $list,"page"=>$show ,'number'=>$count);
    }

    /**
     * 手机号归属地与发单地不符数据
     * @param $date[筛选日期]
     * @param $mobile[查询的手机]
     * @return array
     */
    private function getMobileLocaltionException($start_time,$end_time,$mobile)
    {
        //1.查询所有城市订单信息
        $result = D("Orders")->getMobileLocaltionExceptionList($start_time,$end_time,$mobile);
        //dump($result);
        //2.循环获取手机号码的发单地
        import('Library.Org.PhoneLocation.PhoneLocation',"",".php");
        $pl = new \PhoneLocation();
        foreach ($result as $key => $value) {
            $mobileExcept [$value['tel8']]['order'][] = $value['id'];
            $mobileExcept [$value['tel8']]['true_tel'] = $value['tel'];
            $mobileExcept [$value['tel8']]['tel_true'] = $value['tel8'];

            if (!isset($mobileExcept [$value['tel8']]['cname'])){
                $mobileExcept [$value['tel8']]['cname'] = $value['cname'];
            }
            if (!isset($mobileExcept [$value['tel8']]['province'])){
                $info = $pl->find($value['tel8']);
                $mobileExcept [$value['tel8']]['city'] = $info['city'];
            }
        }
        //3.循环判断城市和发单地是否一致，一致则去掉结果
        foreach ($mobileExcept as $phone=>$value)
        {
            $mobileExcept[$phone]['count'] =  empty($mobileExcept[$phone]['order'])?0:count($mobileExcept[$phone]['order']);
            if ($mobileExcept[$phone]['city']==$mobileExcept[$phone]['cname']){
                unset($mobileExcept[$phone]);
            }
        }
        //4，.统计最终结果，物理分页并返回
        $count = count($mobileExcept);
        if ($count > 50) {
            //物理分页
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $show    = $p->show();
            $mobileExcept = array_slice($mobileExcept, $p->firstRow,$p->listRows);
        }
        return array("list" => $mobileExcept,"page"=>$show ,'number'=>$count);
    }

    /**
     * 渠道异常数据
     * @param [int]$start_time(开始时间戳)
     * @param [int]$end_time(结束时间戳)
     * @return array
     */
    private function getChannelOrderDetailedException($start_time,$end_time,$channel)
    {
        $sixMonthDayStart = strtotime("-6 Month", $end_time);
        //1.查询所有的渠道
        $allChannelList = D("Orders")->getAllChannelList($channel);
        foreach ($allChannelList as $value) {
            $list[$value["src"]]['channel'] = $value["name"];       //渠道名称
            $list[$value["src"]]['alias'] = $value["alias"];        //渠道代号
            $list[$value["src"]]['src'] = $value["src"];            //渠道标识
            $list[$value["src"]]['charge'] = $value["charge"];      //渠道付费标志
            $list[$value["src"]]['order_count'] = 0;                //历史发单量
            $list[$value["src"]]['now_order_count'] = 0;            //当前渠道发单量
            $list[$value["src"]]['real_order_count'] = 0;           //历史渠道分单量
            $list[$value["src"]]['now_real_order_count'] = 0;       //当前渠道分担率
        }
        //2.获取每个渠道前半年的历史发单量和当前时间发单量
        $result = D("Orders")->getChannelOrderCountdList($sixMonthDayStart, $end_time, $channel);
        $nowresult = D("Orders")->getChannelOrderCountdList($start_time, $end_time, $channel);
        foreach ($result as $key => $value) {
            $list[$value["src"]]["channel"] = $value["channel"];
            $list[$value["src"]]["order_count"] = $value["count"];
        }
        foreach ($nowresult as $key => $value) {
            $list[$value["src"]]["now_order_count"] = $value["count"];
        }
        //3.获取每个渠道前半年实际分单量和当前时间分单量
        $result2 = D("Orders")->getChannelRealOrderCountdList($sixMonthDayStart, $end_time, $channel);
        $nowResult2 = D("Orders")->getChannelRealOrderCountdList($start_time, $end_time, $channel);
        foreach ($result2 as $key => $value) {
            $list[$value["src"]]["real_order_count"] = $value["count"];
        }
        foreach ($nowResult2 as $key => $value) {
            $list[$value["src"]]["now_real_order_count"] = $value["count"];
        }
        //4.获取手机号和归属地不符合的数据
        $detailList = D("Orders")->getChannelOrderDetailedList($start_time, $end_time, $channel);
        import('Library.Org.PhoneLocation.PhoneLocation', "", ".php");
        $pl = new \PhoneLocation();
        foreach ($detailList as $key => $value) {
            $info = $pl->find($value['tel8']);
            if ($info['city'] != $value['cname']) {                                 //此处记录具体每个异常手机具体信息
                $list[$value['src']]['phone'][] = ['order'=>$value['id'],'tel8' => $value['tel'],'tel_true' => $value['tel8'], 'city' => $info['city'], 'cname' => $value['cname']];
            }
        }

        //5.计算分单率，历史分单率，分单增长，手机号异常占比
        foreach ($list as $key=>$value)
        {
            $phoneArrayCount = count($list[$key]['phone']);
            if ($value['order_count'] ==0 && $value['now_order_count'] == 0 && $value['real_order_count'] == 0 &&  $value['now_real_order_count'] == 0 && empty($value['phone'])){
                unset($list[$key]);
            }else{
                if ( $value['now_order_count'] == 0 &&  $value['now_real_order_count'] == 0 ){
                    unset($list[$key]);
                }else{
                    $list[$key]['phone_count'] = $phoneArrayCount;

                    if ($list[$key]['order_count'] == 0){
                        $list[$key]['history_rate'] = 0;
                    }else {
                        $list[$key]['history_rate'] = round($list[$key]['real_order_count']/$list[$key]['order_count'],4)*100;
                    }

                    if ($list[$key]['now_order_count'] == 0){
                        $list[$key]['now_rate'] = 0;
                        $list[$key]['phone_count_rate'] = 0;
                    }else {
                        $list[$key]['now_rate'] = round($list[$key]['now_real_order_count']/$list[$key]['now_order_count'],4)*100;
                        $list[$key]['phone_count_rate'] = round($phoneArrayCount/$list[$key]['now_order_count'],4)*100;
                    }

                    if ($list[$key]['history_rate'] == 0){
                        $list[$key]['diff_rate'] = 0;
                    }else {
                        $list[$key]['diff_rate'] = round($list[$key]['now_rate'] - $list[$key]['history_rate'],4);
                    }
                }
            }
        }

        //6.判断分单率和历史分单率是不是符合条件，异常手机号码是不是为空
        $config = $this->getExcetpionConfig(2); //数组1免费数组 数组2付费数组
        $defaultNoCharge = $config[1]['default'];
        $defaultCharge = $config[2]['default'];
        foreach ($list as $src => $value){
            if (($value['charge'] == 1 || $value['charge'] == 0) && array_key_exists($src,$config[1])) {    //免费判断
                $base_rate = $config[1][$src]["config_value"];
            } elseif($value['charge'] == 2 && array_key_exists($src,$config[2])){                           //付费判断
                $base_rate = $config[2][$src]["config_value"];
            } elseif ($value['charge'] == 2){                                                               //付费默认
                $base_rate = $defaultCharge["config_value"];
            } else{                                                                                         //免费默认
                $base_rate = $defaultNoCharge["config_value"];
            }
            if ($value['history_rate'] <= $base_rate && $value['now_rate'] <= $base_rate && $value['phone_count'] == 0) {
                unset($list[$src]);
            }
        }

        $count = count($list);
        if ($count > 50) {
            //物理分页
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $show    = $p->show();
            $list = array_slice($list, $p->firstRow,$p->listRows);
        }
        return array("list" => $list,"page"=>$show ,'number'=>$count);

    }
    /**
     * 获取异常设置项
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    private function getExcetpionConfig($type)
    {
        //查询城市异常率
        $result = D("QcExceptionConfig")->getExcetpionConfig($type);

        foreach ($result as $key => $value) {
            $config[$value["config_state"]][$value["config_key"]] = $value;
        }
        return $config;
    }

    private function getCityExceptionConfig()
    {
        //获取设置项
        $config = $this->getExcetpionConfig(1);
        $config = $config[1];
        //获取城市信息
        $citys = D("Quyu")->getQuyuList();
        foreach ($citys as $key => $value) {
            if (count($config) > 0) {
                if (array_key_exists($value["cid"],$config)) {
                    $citys[$key]["rate"] = $config[$value["cid"]]["config_value"];
                    $citys[$key]["mark"] = 0;
                } else {
                    $citys[$key]["rate"] = $config["default"]["config_value"];
                    $citys[$key]["mark"] = 1;
                }
            } else {
                $citys[$key]["rate"] = 100;
                $citys[$key]["mark"] = 1;
            }
        }
        $list = array_chunk($citys,15);
        return array("list"=>$list,"citys"=>$citys,"default"=>$config["default"]["config_value"]);
    }

    private function getSrcExceptionConfig($src)
    {
        //获取设置项
        $config = $this->getExcetpionConfig(2);
        $freeConfig = $config[1];
        $payConfig = $config[2];

        //获取渠道信息
        $count = D("OrderSource")->getAllSourceDeptListCount($src);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $show    = $p->show();
            $srcList = D("OrderSource")->getAllSourceDeptList($src,$p->firstRow,$p->listRows);
            foreach ($srcList as $key => $value) {
                if ($value["charge"] == 1) {
                    if (count($freeConfig) > 0) {
                        //免费
                        if (array_key_exists($value["src"],$freeConfig)) {
                            $srcList[$key]["rate"] = $freeConfig[$value["src"]]["config_value"];
                            $srcList[$key]["mark"] = "freesrc";
                        } else {
                            $srcList[$key]["rate"] = $freeConfig["default"]["config_value"];
                            $srcList[$key]["mark"] = "freedefault";
                        }
                    } else {
                        $srcList[$key]["rate"] = 100;
                        $srcList[$key]["mark"] = "freedefault";
                    }
                } elseif($value["charge"] == 2) {
                    //付费
                    if (count($freeConfig) > 0) {
                        if (array_key_exists($value["src"],$payConfig)) {
                            $srcList[$key]["rate"] = $payConfig[$value["src"]]["config_value"];
                            $srcList[$key]["mark"] = "paysrc";
                        } else {
                            $srcList[$key]["rate"] = $payConfig["default"]["config_value"];
                            $srcList[$key]["mark"] = "paydefault";
                        }
                    } else {
                        $srcList[$key]["rate"] = 100;
                        $srcList[$key]["mark"] = "paydefault";
                    }
                }
            }
            return array("list" => $srcList,"page" => $show ,"freedefault" =>$freeConfig["default"]["config_value"],"paydefault" =>  $payConfig["default"]["config_value"]);
        }
    }


    private function getOtherException()
    {
        //获取设置项
        $config = $this->getExcetpionConfig(3);
        $config = $config[1];
        foreach ($config as $key => $value) {
            $list[$value["config_key"]] = $value["config_value"];
        }

        $list["email"] = str_replace(",","\n",$list["email"]);
        return $list;
    }

    /**
     * 传入筛选单天时间获取筛选时间条件
     * @param [int]$time 具体某天时间
     * @param [int]$type 结果集类型（1：渠道订单异常统计 2：城市订单异常 3：IP归属地异常 4：IP归属地与发单地不符 5：手机归属地和发单地不符 6：渠道分单率异常）     * @param [int]$type
     * retunr bool
     */
    private function getDate($date='',$type = 1)
    {
        if (!empty($date)) {
            $time = strtotime($date);
        } else {
            $time = time() - 86400;
        }
        switch (intval($type))
        {
            case 2:
            case 3:
                $start_time = strtotime("-1 day", mktime(0,0,0,date("m",$time),date("d",$time),date("Y",$time)));
                $end_time = strtotime("-0 day",mktime(23,59,59,date("m",$time),date("d",$time),date("Y",$time)));
                break;
            case 5:
            case 4:
                $start_time = strtotime("-0 day", mktime(0,0,0,date("m",$time),date("d",$time),date("Y",$time)));
                $end_time = strtotime("-0 day",mktime(23,59,59,date("m",$time),date("d",$time),date("Y",$time)));
                break;
            case 6:
            default:
                $start_time = strtotime("-0 day", mktime(0,0,0,date("m",$time),date("d",$time),date("Y",$time)));
                $end_time = strtotime("-0 day",mktime(23,59,59,date("m",$time),date("d",$time),date("Y",$time)));
                break;

        }
        return [$start_time,$end_time];
    }

    // 质检服务质量统计
    public function recordgrade(){
        //获取质检人员列表
        $qcList = D('Adminuser')->getAdminuserListByUid(array(23,99));
        $this->assign('qclist',$qcList);
        //客服人员
        //获取客服组长列表
        $users = $this->getKfGroupInfo();
        //客服列表
        $kfList = D("Adminuser")->getKfList();
        $this->assign("kfList",$kfList);
        $this->assign("manager",$users["manager"]);
        $this->assign("groups",$users["groups"]);
        $get = I("get.");
        $qcinfologic = new QcInfoLogicModel();
        $info = $qcinfologic->recordgrade($get);
//        var_dump($info);exit;
        //远程中心人员查看时,隐藏质检项目
        //获取部门下所有的角色ID
        $result = D("RbacRole")->getRoleListByDept(22);
        foreach ($result as $key => $value) {
            $ids[] = $value["role_id"];
        }
        if(!in_array(session("uc_userinfo.uid"),$ids)){
            $zjshow = 1;
        }else{
            $zjshow = 2;
        }
        $this->assign('zjshow', $zjshow);

        $this->assign('list',$info['list']);
        $this->assign('page',$info['page']);
        $this->display();
    }

    // 录音操作服务质量统计
    public function recordgradetab(){
        $get  = I('get.');
        $type = isset($get['type'])?$get['type']:1;
        $qcinfologic = new QcInfoLogicModel();
        $result = $qcinfologic->recordgradetab($get);

        switch($type){
            case 2:
                //客服
                $info = $qcinfologic->recordgradebyperson($result);
                break;
            case 3:
                //客服团
                $info = $qcinfologic->recordgradebygroup($result);
                break;
            case 4:
                //客服师
                $info = $qcinfologic->recordgradebymanager($result);
                break;
            default:
                //日期
                $info = $qcinfologic->recordgradebytime($get,$result);
                break;
        }

        if (in_array($type,array(2,3,4))) {
            //获取部门下所有的角色ID
            $result = D("RbacRole")->getRoleListByDept(22);
            foreach ($result as $key => $value) {
                $ids[] = $value["role_id"];
            }

            //获取部门下所有人员
            if (count($ids) > 0) {
                //获取客服人员
                $kfList = D("Adminuser")->getUserListByUid($ids);
            }
            //获取每个评分项的平均值
            $avgScore = $qcinfologic->getRecordAvgScore($get["start_time"],$get["end_time"]);
        }

        //获取客服团/师
        $manager = $this->getKfGroupInfo();
        $this->assign('avgScore',$avgScore);
        $this->assign('manager',$manager);
        $this->assign('kfList',$kfList);
        $this->assign('list',$info);
        $this->assign('type',$type);
        $this->display();
    }


    // 城市平均会员统计 导出excel
    public function exportcityavgvip () {
        $get = I("get.");
        ini_set('memory_limit','512M');
        ini_set('max_execution_time',  60 * 3);
        $excelData = [];
        $excelData['header'] =   [
            '质检时间' => 'string',
            '订单编号' => 'string',
            '质检类型' => 'string',
            '质检员' => 'string',
            '订单类型' => 'string',
            '录音客服' => 'string',
            '审核客服' => 'string',
            '审核团' => 'string',
            '审核师' => 'string',
            '服务礼貌' => 'string',
            '语音语速抢话口头禅' => 'string',
            '倾听能力' => 'string',
            '表达能力' => 'string',
            '引导能力' => 'string',
            '疑难解决' => 'string',
            '业务熟练' => 'string',
            '营销意识' => 'string',
            '总计' => 'string',
        ];
        $excelData['sheet'] = 'sheet1';
        $excelData['row'] = [];
        $qcinfologic = new QcInfoLogicModel();
        $result = $qcinfologic->recortgradeexport($get);
        $rowAll = [];
        $i = 0;
        foreach ($result as $key => $value) {
            $rowAll[$i][0] = $value['time'];
            $rowAll[$i][1] = $value['order_id'];
            $rowAll[$i][2] = $value['type'];
            $rowAll[$i][3] = $value['op_name'];
            $rowAll[$i][4] = $value['type_fw'];
            $rowAll[$i][5] = $value['record_name'];

            $rowAll[$i][6] = $value['kf_name'];
            $rowAll[$i][7] = $value['kf_group_name'];
            $rowAll[$i][8] = $value['kf_manager_name'];
            $rowAll[$i][9] = empty($value['1'])?0:$value['1'];
            $rowAll[$i][10] = empty($value['6'])?0:$value['6'];
            $rowAll[$i][11] = empty($value['11'])?0:$value['11'];

            $rowAll[$i][12] = empty($value['16'])?0:$value['16'];
            $rowAll[$i][13] = empty($value['21'])?0:$value['21'];
            $rowAll[$i][14] = empty($value['26'])?0:$value['26'];
            $rowAll[$i][15] = empty($value['31'])?0:$value['31'];
            $rowAll[$i][16] = empty($value['36'])?0:$value['36'];
            $rowAll[$i][17] = empty($value['sum_score'])?0:$value['sum_score'];
            $i++;
        }

        $excelData['row'] = $rowAll;
        $excelData['filename'] = '质检服务质量统计.xlsx';
        export_excel_download($excelData);
    }


}