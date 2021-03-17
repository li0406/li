<?php

//推广渠道来源统计

namespace Home\Controller;
use Home\Model\Logic\JiajuOrdersLogicModel;
use Home\Model\Logic\OrderSourceLogicModel;
use Home\Model\Logic\OrdersLogicModel;
use Home\Model\Logic\HzsLogicModel;
use Home\Common\Controller\HomeBaseController;

class OrdersrcstatsController extends HomeBaseController{
    private $dept = array(
        "总裁办" => "zcb",
        "推广二部" => "tg2",
        "推广一部" => "tg1",
        "渠道部" => "qd",
        "公装部" => "gz",
        "新媒体部" => "media",
        "线下广告" => "online",
        "品牌部" => "brand"
    );

    public function _initialize(){
        parent::_initialize();
    }


    //首页
    public function index(){
        ini_set('memory_limit','512M');
        $pageIndex = 1;
        $pageCount = 10;
        if(!empty($_GET["p"])){
            $pageIndex = $_GET["p"];
        }

        //开始时间和结束时间
        $start_time = I('get.start_time');
        if(!empty($start_time)){
            $date = strtotime($start_time);
            $start_time = mktime(0,0,0,date("m",$date),date("d",$date),date("Y",$date));
        }else{
            $start_time = mktime(0,0,0,date("m"),date("d"),date("Y"));
        }
        $end_time = I('get.end_time');
        if(!empty($end_time)){
            $date = strtotime($end_time);
            $end_time   = mktime(23,59,59,date("m",$date),date("d",$date),date("Y",$date));
        }else{
            $end_time   = mktime(23,59,59,date("m"),date("d"),date("Y"));
        }

        $info['start_time'] = date('Y-m-d',$start_time);
        $info['end_time'] = date('Y-m-d',$end_time);

        $info['start_times'] = $start_time;
        $info['end_times'] = $end_time;

        $info['set_start_time'] = date('Y-m-d',strtotime("-6 day"));
        $info['set_end_time'] = date('Y-m-d',time());

        $timeDiff = $end_time - $start_time;

        if($timeDiff > 1 && $timeDiff <= 86400){
            $timeNum = '1';
        }else{
            $timeNum = floor($timeDiff/86400);
        }

        if($timeNum > 31){
            $this->error('时间不能大于一个月');
        }

        session('orderSrcTime',$info,3600);

        /*
        审核有效 on = 4
        单子类型: type_fw  0默认状态 1分 2问 3分没人跟 4问没人跟 问的不算单量
        分：1 或 3 增：2 或 4
        */

        //非管理员只能查看自己部门的数据
        if (session("uc_userinfo.uid") != 1) {
            $result = D("DepartmentIdentify")->findMyDept(session("uc_userinfo.id"));
            foreach ($result as $key => $value) {
                if (!array_key_exists($value["dept_belong"], $department)) {
                    $department[$value["dept_belong"]]["name"] = $value["dept_belong"];
                }
                $tag = $this->dept[$value["dept_belong"]];
                if (!array_key_exists('all', $department[$value["dept_belong"]]["child"])) {
                    $department[$value["dept_belong"]]["child"][$tag] = array(
                        "id" => $tag,
                        "name" => $value["dept_belong"].'全部'
                    );
                }

                if (!array_key_exists($value["dept"], $department[$value["dept_belong"]]["child"])) {
                    if ($value["dept"] != 15) {
                        $department[$value["dept_belong"]]["child"][$value["dept"]] = array(
                            "id" => $value["dept"],
                            "name" => $value["deptname"]
                        );
                    }
                    $dept[] = $value["dept"];
                }
            }

            if (count($dept) == 0) {
                $this->_error();
            }
        } else {
            $result = D("DepartmentIdentify")->findAllDept();
            foreach ($result as $key => $value) {
                if (!array_key_exists($value["dept_belong"], $department)) {
                    $department[$value["dept_belong"]]["name"] = $value["dept_belong"];
                }
                $tag = $this->dept[$value["dept_belong"]];
                if (!array_key_exists($tag, $department[$value["dept_belong"]]["child"])) {
                    $department[$value["dept_belong"]]["child"][$tag] = array(
                        "id" => $tag,
                        "name" => $value["dept_belong"].'全部'
                    );
                }

                if (!array_key_exists($value["id"], $department[$value["dept_belong"]]["child"])) {
                    if ($value["id"] != 15) {
                        $department[$value["dept_belong"]]["child"][$value["id"]] = array(
                            "id" => $value["id"],
                            "name" => $value["name"]
                        );
                    }
                }
            }
        }

        //如果只有一个总裁办全部，删除这个全部项
        if (count( $department["总裁办"]["child"]) == 1) {
            unset($department["总裁办"]);
        }

        if (I("get.dept") !== "") {
            $dept = array();
            if (!is_numeric(I("get.dept"))) {
                foreach ($this->dept as $key => $value) {
                    if (I("get.dept") == $value) {
                        $belong = $key;
                        break;
                    }
                }
                foreach ($department[$belong]["child"] as $key => $value) {
                   $dept[] = $value["id"];
                }
                unset($dept[0]);
                //手动添加客服发单
                $dept[] = 15;
            } else {
                $dept[] = I("get.dept");
            }
        }
       
        //管理员、运营总监、流量主管、产品经理 添加自然流量分单
        if (in_array(session("uc_userinfo.uid"),array(1,51,68,70,76,38,152,75,157))) {
            if (I("get.dept") == "") {
                $natural = true;
            }elseif ( (I("get.dept") !== "" && I("get.dept") == "tg2") || I("get.dept") == 14 ) {
                $natural = true;
            }
        }

        $this->assign("department",$department);

        if (I("get.charge") !== "") {
            $charge = I("get.charge");
        }

        if (I("get.partner") !== "") {
            $partner = I("get.partner");
            $hzsLogic = new HzsLogicModel();
            $hzsSourceList = $hzsLogic->getSourceListByCompanyId($partner);
            $sourceIds = array_column($hzsSourceList, "sourceid");
            $sourceIds = empty($sourceIds) ? ["-1"] : $sourceIds;
        }

        //获取实际的真实订单数量，备注：实际有效订单量与实际分单量按照勾选时间段内实际产生的数量统计
        /*S-前台发单逻辑*/
        $temp = D("OrderSourceStats")->getOrderStatFromOrderCsosNew($start_time,$end_time,$dept,$charge,$natural,$sourceIds);

        $tempReset = [];
        foreach ($temp as $key => $value) {
            $k = md5($value['days']) . md5($value['source']) . md5($value['on']) . md5($value['type_fw']);
            $tempReset[$k] = $value;
        }

        $result = D("OrderSourceStats")->getOrderSrcList($start_time,$end_time,$dept,$charge,$natural,$sourceIds);

        //定义一个src数组，用来统计UV和IP，不在这个src数组里面的UV和IP不进行统计
        $srcArray = array();
        //将当天产生相同状态（source,on,type_fw）的订单数据合并
        foreach ($result as $key => $value) {
            $k = md5($value['days']) . md5($value['source']) . md5($value['on']) . md5($value['type_fw']);
            if (empty($tempReset[$k])) {
                $result[$key]["real_num"] = 0;
            } else {
                $result[$key]["real_num"] = $tempReset[$k]["real_num"];
            }
            unset($tempReset[$k]);
            $srcArray[] = $value['src'];
        }

        //只追加实际有效单
        foreach ($tempReset as $key => $value) {
            if (('4' == $value['on']) && (in_array($value['type_fw'], array('1', '2')))) {
                $value["num"] = 0;
                $value["current_num"] = 0;
                $result[] = $value;
            }
        }

        $isDebug = I('get.debug');

        if($isDebug == 'result'){
            dump($result);
        }

        /*E-前台发单逻辑*/

        /*S-后台发单逻辑*/

        /*E-后台发单逻辑*/

        //订单量 分单量 有效分单
        $info['allCounts'] = $info['fendan'] = $info['real_fendan'] = $info['youxiao'] = $info['real_youxiao'] = $info['current_fendan'] = $info['current_youxiao'] = 0;
        $group = array();
        //逐条处理数据
        foreach ($result as $k => $v) {
            //$v['src'] 有时也为空
            if(empty($v['source'])){
                $v['id'] = '9999';
                $v['source'] = '888888';
                $v['src'] = 'othersss';
                $v['sourcename'] = '自然流量';
                $v['groupid'] = '9999';
                $v['group_name'] = '自然流量';
                $v['dept'] = '14';
                $v['charge'] = '1';
            }

            //统计各部门发单量
            if(!empty($v['dept'])){
                if($v['on'] == '4' && ($v['type_fw'] == '1')){
                    if(empty($realOrdersByDept[$v['dept']])){
                        $realOrdersByDept[$v['dept']] = 0;
                    }
                    $realOrdersByDept[$v['dept']] = $realOrdersByDept[$v['dept']] + $v['real_num'];
                }
            }

            if(($v['on'] == '4') && ($v['type_fw'] == '1')){
                //总分单量：查询时间段内的发单，从这些发单中产生的分单
                $info['fendan'] = $info['fendan'] + $v['num'];
                //实际总分单量：查询时间段内产生的分单，只要是该时间段内产生的分单都算
                $info['real_fendan'] = $info['real_fendan'] + $v['real_num'];
                //实时分单量：从查询时间段内的发单量中，在查询时间段内打出的分单量
                $info['current_fendan'] = $info['current_fendan'] + $v['current_num'];
            }

            if($v['on'] == '4' && in_array($v['type_fw'], array(1,2))){
                //有效单
                $info['youxiao'] = $info['youxiao'] + $v['num'];
                //实际有效单
                $info['real_youxiao'] = $info['real_youxiao'] + $v['real_num'];
                //实时有效单：查询时间段内的发单，并且在查询时间段内打出的有效单
                $info['current_youxiao'] = $info['current_youxiao'] + $v['current_num'];
            }

            //发单量
            $info['allCounts'] = $info['allCounts'] + $v['num'];

            //成生新的分组数据
            if(!array_key_exists($v['groupid'],$group)){
                $group[$v['groupid']] = array(
                        'id' => $v['groupid'],
                        'group_name' => $v['group_name'],
                        "parentid" => $v["parentid"],
                        "parentname" => $v["parent_name"]
                );
            }

            $group[$v['groupid']]['sub'][] = $v;

            //合并每天数据
            if($timeNum >= '1'){
                if (!empty($v['parentid'])) {
                   $parentid = $v['parentid'];
                   $parentname = $v['parent_name'];
                } else {
                   $parentid = $v['groupid'];
                   $parentname = $v["group_name"];
                }

                if(!array_key_exists($parentid,$day[$v['days']])){
                    $day[$v['days']][$parentid ] = array('parentid' => $parentid ,'name' => $parentname,);
                }
                //发单量
                $day[$v['days']][$parentid]['count'] += $v['num'];
            }
        }

        /*
        将现有的“客服发单”按一定的比例拆分给总裁办、流量部、推广部

        占比：
        部门实际分单量 / 总实际分单量 * 100%

        假设：3月客服后台发单数290，总发单量占比为 0.71 (71%)
              总裁办实际业绩= 290 * 0.71 * 0.8 = 164.7

        $deptArray = array(
            '5' => '1',//总裁办
            '1' => '2','6' => '2','7' => '2','4' => '2',//推广部
            '2' => '3',//流量部
        );

        */

        //总实际分单量
        $allRealOrderNum = D("OrderSourceStats")->getAllRealOrderCount($start_time,$end_time);

        //查询部门信息
        $rt = D("DepartmentIdentify")->getDeptList();

        foreach ($rt as $key => $value) {
            $departments[$this->dept[$value["dept_belong"]]][$value["id"]] = $value["id"];
        }

        //查询部门的总分单信息
        $zcbRealOrderNum = D("OrderSourceStats")->getAllRealOrderCount($start_time,$end_time,$departments["zcb"],false);
        $tg1RealOrderNum = D("OrderSourceStats")->getAllRealOrderCount($start_time,$end_time,$departments["tg1"],false);
        $tg2RealOrderNum = D("OrderSourceStats")->getAllRealOrderCount($start_time,$end_time,$departments["tg2"],true);


        //定义各部门总发单占比 -------------------------------

        //总裁办
        $deptOrderPer['1'] = setInfNanToN(round($zcbRealOrderNum / $allRealOrderNum,2));
        //推广部
        $deptOrderPer['2'] = setInfNanToN(round($tg1RealOrderNum / $allRealOrderNum,2));
        //流量部
        $deptOrderPer['3'] = setInfNanToN(round($tg2RealOrderNum/ $allRealOrderNum,2));

        if($isDebug == 'kefu'){
            dump($allRealOrderNum);
            dump($realOrdersByDept);
            dump($deptOrderPer);
        }

        if($isDebug == 'group1'){
            dump($group);
        }

        $condition['o.time_real']  = array('between',array($start_time,$end_time));
        $condition['o.source'] = array('neq',999);

        //获取时间段内所有 Source 列表
        $_allSrcList = D("OrderSourceStats")->getAllSrcByTime($condition,$dept);

        foreach ($_allSrcList as $key => $value) {
            $allSrcList[$value['src']] = $value;
        }
        S("Cache:OrderSrc:allSrcList:".session("uc_userinfo.id"),$allSrcList,9000);

        //取当前时段总数据
        $_allCountByDay = D("OrderSourceStats")->getOrderSrcByDay($condition);
        foreach ($_allCountByDay as $key => $value) {
            if(empty($value['source'])){
                $value['source'] = '888888';
            }
            //自然流量有日期重复数据
            if(empty($allCountByDay[$value['source']][$value['days']])){
                $allCountByDay[$value['source']][$value['days']] = $value['num'];
            }else{
                $allCountByDay[$value['source']][$value['days']] = $allCountByDay[$value['source']][$value['days']] + $value['num'];
            }
        }
        S("Cache:OrderSrc:allCountByDay:".session("uc_userinfo.id"),$allCountByDay,9000);

        //处理分组数据
        foreach ($group as $k => $v) {
            $exception_count = $un_tel_count = $tel_count = $subFendan = $subRealFendan = $subCurrentFendan = $subYouxiao = $subRealYouxiao = $subCurrentYouxiao = $subCount = $subUV = $subIP  = 0;

            if (!empty($v["parentid"])) {
                $list[$v["parentid"]]["name"] = $v["parentname"];
                $parentid = $list[$v["parentid"]]["id"] = $v["parentid"];
            } else {
                $list[$v["id"]]["name"] = $v["group_name"];
                $parentid  = $list[$v["id"]]["id"] = $v["id"];
            }

            if (!array_key_exists($v["id"], $list[$parentid]['sub'])) {
                $list[$parentid]['sub'][$v["id"]] = array();
            }

            //处理子分组
            foreach ($v['sub'] as $key => $value) {
                if (!array_key_exists($value['id'], $list[$parentid]['sub'])) {
                    $list[$parentid]['sub'][$v["id"]]["group_name"] = $value["group_name"];
                    $list[$parentid]['sub'][$v["id"]]["id"] = $v["id"];
                }

                if(empty($list[$parentid]['sub'][$v["id"]]['subGroup'][$value['source']])){
                    $list[$parentid]['sub'][$v["id"]]['subGroup'][$value['source']] = array(
                        'src' => $value['src'],
                        'source' => $value['source'],
                        'source_name' => $value['sourcename'],
                    );
                    //统计UV和IP数据，只统计初步查询出来的src数组里的
                    if(!empty($allSrcList[$value['src']]) && in_array($value['src'], $srcArray)){
                        $list[$parentid]['sub'][$v["id"]]['subGroup'][$value['source']]['UV'] = $allSrcList[$value['src']]['num'];
                        $subUV =  $subUV + $allSrcList[$value['src']]['num'];
                        $subIP =  $subIP + $allSrcList[$value['src']]['ip_num'];
                    }else{
                        $list[$parentid]['sub'][$v["id"]]['subGroup'][$value['source']]['UV'] = '0';
                    }
                }
                $list[$parentid]['sub'][$v["id"]]['subGroup'][$value['source']]['count'] +=  $value['num'];

                //分单量
                if(($value['on'] == '4') && ($value['type_fw'] == '1')){
                    //分单量
                    $subFendan = $subFendan + $value['num'];
                    $list[$parentid]['sub'][$v["id"]]['subGroup'][$value['source']]['fendan'] +=  $value['num'];
                    //实际分单量
                    $subRealFendan = $subRealFendan + $value['real_num'];
                    $list[$parentid]['sub'][$v["id"]]['subGroup'][$value['source']]['real_fendan'] += $value['real_num'];
                    //实时分单量：查询时间段内的发单，并且在查询时间段内打出的分单c
                    $subCurrentFendan = $subCurrentFendan + $value['current_num'];
                    $list[$parentid]['sub'][$v["id"]]['subGroup'][$value['source']]['current_fendan'] += $value['current_num'];
                }

                //有效单
                if($value['on'] == '4' && in_array($value['type_fw'], array(1,2))){
                    //有效单
                    $subYouxiao = $subYouxiao + $value['num'];
                    $list[$parentid]['sub'][$v["id"]]['subGroup'][$value['source']]['youxiao'] = $list[$parentid]['sub'][$v["id"]]['subGroup'][$value['source']]['youxiao'] + $value['num'];
                    //实际有效单,只算分单和赠单
                    $subRealYouxiao = $subRealYouxiao + $value['real_num'];
                    $list[$parentid]['sub'][$v["id"]]['subGroup'][$value['source']]['real_youxiao'] = $list[$parentid]['sub'][$v["id"]]['subGroup'][$value['source']]['real_youxiao'] + $value['real_num'];
                    //实时有效单
                    $subCurrentYouxiao = $subCurrentYouxiao + $value['current_num'];
                    $list[$parentid]['sub'][$v["id"]]['subGroup'][$value['source']]['current_youxiao'] = $list[$parentid]['sub'][$v["id"]]['subGroup'][$value['source']]['current_youxiao'] + $value['current_num'];
                }

                //发单量
                $subCount = $subCount + $value['num'];
                 //已拨打量和未拨打量
                $tel_count += $value["tel_count"];
                $un_tel_count += $value["un_tel_count"];
                $list[$parentid]['sub'][$v["id"]]['subGroup'][$value['source']]["tel_count"] += $value["tel_count"];
                $list[$parentid]['sub'][$v["id"]]['subGroup'][$value['source']]["un_tel_count"] += $value["un_tel_count"];

                //异常单量
                $exception_count +=  $value["exception_count"];
                $list[$parentid]['sub'][$v["id"]]['subGroup'][$value['source']]["exception_count"] += $value["exception_count"];
            }

            $info['allUV'] = $info['allUV'] + $subUV;
            $info['allIP'] = $info['allIP'] + $subIP;

            $list[$parentid]['sub'][$v["id"]]['exception_count'] +=  $exception_count;
            $list[$parentid]['sub'][$v["id"]]['tel_count'] += $tel_count;
            $list[$parentid]['sub'][$v["id"]]['un_tel_count'] += $un_tel_count;
            $list[$parentid]['sub'][$v["id"]]['count'] = $subCount;
            $list[$parentid]['sub'][$v["id"]]['sub_UV'] = $subUV;
            $list[$parentid]['sub'][$v["id"]]['sub_IP'] = $subIP;
            $list[$parentid]['sub'][$v["id"]]['sub_youxiao'] = $subYouxiao;
            $list[$parentid]['sub'][$v["id"]]['sub_real_youxiao'] = $subRealYouxiao;
            $list[$parentid]['sub'][$v["id"]]['sub_current_youxiao'] = $subCurrentYouxiao;
            $list[$parentid]['sub'][$v["id"]]['sub_fendan'] = $subFendan;
            $list[$parentid]['sub'][$v["id"]]['sub_real_fendan'] = $subRealFendan;
            $list[$parentid]['sub'][$v["id"]]['sub_current_fendan'] = $subCurrentFendan;
            $list[$parentid]['sub'][$v["id"]]['sub_fendan_lv'] = setInfNanToN(round(($subFendan / $subCount) * 100, 2));
            $list[$parentid]['sub'][$v["id"]]['sub_real_fendan_lv'] = setInfNanToN(round(($subRealFendan / $subCount) * 100, 2));
            $list[$parentid]['sub'][$v["id"]]['sub_current_fendan_lv'] = setInfNanToN(round(($subCurrentFendan / $subCount) * 100, 2));
            $list[$parentid]['sub'][$v["id"]]['sub_youxiao_lv'] = setInfNanToN(round(($subYouxiao / $subCount) * 100, 2));
            $list[$parentid]['sub'][$v["id"]]['sub_real_youxiao_lv'] = setInfNanToN(round(($subRealYouxiao / $subCount) * 100, 2));
            $list[$parentid]['sub'][$v["id"]]['sub_current_youxiao_lv'] = setInfNanToN(round(($subCurrentYouxiao / $subCount) * 100, 2));
            $list[$parentid]['sub'][$v["id"]]['exception_lv'] = setInfNanToN(round(($exception_count / $subCount)*100,1));


            $list[$parentid]['exception_count'] += $exception_count;
            $list[$parentid]['tel_count'] += $tel_count;
            $list[$parentid]['un_tel_count'] += $un_tel_count;
            $list[$parentid]['count'] += $subCount;
            $list[$parentid]['sub_UV'] += $subUV;
            $list[$parentid]['sub_IP'] += $subIP;
            $list[$parentid]['sub_youxiao'] += $subYouxiao;
            $list[$parentid]['sub_real_youxiao'] += $subRealYouxiao;
            $list[$parentid]['sub_current_youxiao'] += $subCurrentYouxiao;
            $list[$parentid]['sub_fendan'] += $subFendan;
            $list[$parentid]['sub_real_fendan'] += $subRealFendan;
            $list[$parentid]['sub_current_fendan'] += $subCurrentFendan;
            $list[$parentid]['sub_fendan_lv'] = setInfNanToN(round(($list[$parentid]['sub_fendan']  / $list[$parentid]['count']) * 100, 2));
            $list[$parentid]['sub_real_fendan_lv'] = setInfNanToN(round(($list[$parentid]['sub_real_fendan'] / $list[$parentid]['count']) * 100, 2));
            $list[$parentid]['sub_current_fendan_lv'] = setInfNanToN(round(($list[$parentid]['sub_current_fendan'] / $list[$parentid]['count']) * 100, 2));
            $list[$parentid]['sub_youxiao_lv'] = setInfNanToN(round(($list[$parentid]['sub_youxiao'] / $list[$parentid]['count']) * 100, 2));
            $list[$parentid]['sub_real_youxiao_lv'] = setInfNanToN(round(($list[$parentid]['sub_real_youxiao'] / $list[$parentid]['count']) * 100, 2));
            $list[$parentid]['sub_current_youxiao_lv'] = setInfNanToN(round(($list[$parentid]['sub_current_youxiao'] / $list[$parentid]['count']) * 100, 2));
            $list[$parentid]['exception_lv'] = setInfNanToN(round(($list[$parentid]['exception_count'] / $list[$parentid]['count'])*100,1));

            foreach ($day as $keys => $values) {
                if(!empty($values[$v['id']])){
                    $list[$parentid]['days'][$keys] += $values[$v['id']]['count'];
                }else{
                    $list[$parentid]['days'][$keys] = 0;
                }
            }
            $list[$parentid]['dayData'] = implode($list[$parentid]['days'],',');

            $countVal['exception_count'] += $exception_count;
            $countVal['un_tel_count'] += $un_tel_count;
            $countVal['uv'] = $countVal['uv'] + $subUV;
            $countVal['IP'] = $countVal['IP'] + $subIP;
            $countVal['ddl'] = $countVal['ddl'] + $subCount;
            $countVal['youxiao'] = $countVal['youxiao'] + $subYouxiao;
            $countVal['real_youxiao'] = $countVal['real_youxiao'] + $subRealYouxiao;
            $countVal['current_youxiao'] = $countVal['current_youxiao'] + $subCurrentYouxiao;
            $countVal['fendan'] = $countVal['fendan'] + $subFendan;
            $countVal['real_fendan'] = $countVal['real_fendan'] + $subRealFendan;
            $countVal['current_fendan'] = $countVal['current_fendan'] + $subCurrentFendan;
            $countVal['tel_count'] += $tel_count;
        }

        //处理客服发单数据 -----------------------------------------------------------

        //管理员、运营总监、流量主管、产品经理 添加客服发单数量 51,68

        if (in_array(session("uc_userinfo.uid"),array(1,51,68,76,38,152))) {
            if (I("get.dept") !== "" && I("get.dept") == "tg1") {
                $deptId = 2;
            } elseif(I("get.dept") !== "" && I("get.dept") == "tg2"){
                $deptId = 3;
            } elseif(I("get.dept") !== "" && I("get.dept") == "zcb"){
                $deptId = 1;
            }
        } elseif (in_array(session("uc_userinfo.uid"),array(75))) {
            $deptId = 2;
        } elseif (in_array(session("uc_userinfo.uid"),array(70))) {
            $deptId = 3;
        }

        if((!empty($deptId) && I("get.dept") == "") || (I("get.dept") !== "" && count($dept) > 1) ){
            $explKey = array('src','source','source_name','UV');

            $kfOldData = $list['48'];
            foreach ($list['48']['sub']['48']['subGroup']['164'] as $k => $v) {
                if(in_array($k,$explKey)){
                    continue;
                }
                $list['48']['sub']['48']['subGroup']['164'][$k] = setInfNanToN(round($v * $deptOrderPer[$deptId] * 0.8,2));

            }

            $list['48']['exception_count'] = 0;
            $list['48']['count'] = $list['48']['sub']['48']['count'] = setInfNanToN(round($list['48']['sub']['48']['count'] * $deptOrderPer[$deptId] * 0.8,2));
            $list['48']['sub_youxiao'] = $list['48']['sub']['48']['sub_youxiao'] = setInfNanToN(round($list['48']['sub']['48']['sub_youxiao'] * $deptOrderPer[$deptId] * 0.8,2));
            $list['48']['sub_real_youxiao'] = $list['48']['sub']['48']['sub_real_youxiao'] = setInfNanToN(round($list['48']['sub']['48']['sub_real_youxiao'] * $deptOrderPer[$deptId] * 0.8,2));
            $list['48']['sub_current_youxiao'] = $list['48']['sub']['48']['sub_current_youxiao'] = setInfNanToN(round($list['48']['sub']['48']['sub_current_youxiao'] * $deptOrderPer[$deptId] * 0.8,2));

            $list['48']['sub_fendan'] = $list['48']['sub']['48']['sub_fendan'] = setInfNanToN(round($list['48']['sub']['48']['sub_fendan'] * $deptOrderPer[$deptId] * 0.8,2));

            $list['48']['sub_real_fendan'] = $list['48']['sub']['48']['sub_real_fendan'] = setInfNanToN(round($list['48']['sub']['48']['sub_real_fendan'] * $deptOrderPer[$deptId] * 0.8,2));


            $list['48']['sub_current_fendan'] = $list['48']['sub']['48']['sub_current_fendan'] = setInfNanToN(round($list['48']['sub']['48']['sub_current_fendan'] * $deptOrderPer[$deptId] * 0.8,2));

            $list['48']['sub_fendan_lv'] = $list['48']['sub']['48']['sub_fendan_lv'] = setInfNanToN(round(($list['48']['sub']['48']['sub_fendan'] / $list['48']['sub']['48']['count']) * 100, 2));
            $list['48']['sub_real_fendan_lv'] = $list['48']['sub']['48']['sub_real_fendan_lv'] = setInfNanToN(round(($list['48']['sub']['48']['sub_real_fendan'] / $list['48']['sub']['48']['count']) * 100, 2));
            $list['48']['sub_current_fendan_lv'] = $list['48']['sub']['48']['sub_current_fendan_lv'] = setInfNanToN(round(($list['48']['sub']['48']['sub_current_fendan'] / $list['48']['sub']['48']['count']) * 100, 2));
            $list['48']['sub_youxiao_lv'] =  $list['48']['sub']['48']['sub_youxiao_lv'] = setInfNanToN(round(($list['48']['sub']['48']['sub_youxiao'] / $list['48']['sub']['48']['count']) * 100, 2));
            $list['48']['sub_real_youxiao_lv'] = $list['48']['sub']['48']['sub_real_youxiao_lv'] = setInfNanToN(round(($list['48']['sub']['48']['sub_real_youxiao'] / $list['48']['sub']['48']['count']) * 100, 2));
            $list['48']['sub_current_youxiao_lv'] = $list['48']['sub']['48']['sub_current_youxiao_lv'] = setInfNanToN(round(($list['48']['sub']['48']['sub_current_youxiao'] / $list['48']['sub']['48']['count']) * 100, 2));

            foreach ($list['48']['sub']['48']['days'] as $k => $v) {
                $list['48']['sub']['48']['days'][$k] = setInfNanToN(round($v * $deptOrderPer[$deptId] * 0.8));
            }

            $list['48']['dayData'] = implode($list['48']['days'],',');

            $countVal['ddl'] = $info['allCounts'] = $info['allCounts'] - ($kfOldData['count'] - $list['48']['count']);
            $countVal['youxiao'] = $info['youxiao'] = $info['youxiao'] - ($kfOldData['sub_youxiao'] - $list['48']['sub_youxiao']);
            $countVal['real_youxiao'] = $info['real_youxiao'] = $info['real_youxiao'] - ($kfOldData['sub_real_youxiao'] - $list['48']['sub_real_youxiao']);
            $countVal['current_youxiao'] = $info['current_youxiao'] = $info['current_youxiao'] - ($kfOldData['sub_current_youxiao'] - $list['48']['sub_current_youxiao']);
            $countVal['fendan'] = $info['fendan'] = $info['fendan'] - ($kfOldData['sub_fendan'] - $list['48']['sub_fendan']);
            $countVal['real_fendan'] = $info['real_fendan'] = $info['real_fendan'] - ($kfOldData['sub_real_fendan'] - $list['48']['sub_real_fendan']);

            $countVal['current_fendan'] = $info['current_fendan'] = $info['current_fendan'] - ($kfOldData['sub_current_fendan'] - $list['48']['sub_current_fendan']);

            unset($list['48']['sub']['48']['sub']);
            unset($kfOldData['sub']);
        }

        if ($list['48']["name"] == "") {
            unset($group['48']);
        }

        $list = multi_array_sort($list,'name',SORT_DESC);
        //添加按比例分单量
        $group = $this->addProportionFendan($group);
        $countVal['real_fendan_prop_count'] = $group['real_fendan_prop_count'];
        unset($group['real_fendan_prop_count']);

        if($isDebug == 'group2'){
            dump($group);
        }

        S("Cache:OrderSrc:Group:".session("uc_userinfo.id"),json_encode($list),9000);

        //有效订单率
        $countVal['youxiao_lv'] = setInfNanToN(round(($countVal['youxiao'] / $countVal['ddl']) * 100, 2));
        $info['youxiaolv'] =  setInfNanToN(round(($info['youxiao'] / $info['allCounts'])*100, 2));

        //实际有效订单率
        $countVal['real_youxiao_lv'] = setInfNanToN(round(($countVal['real_youxiao'] / $countVal['ddl']) * 100, 2));
        $info['real_youxiaolv'] =  setInfNanToN(round(($info['real_youxiao'] / $info['allCounts'])*100, 2));

        //实时有效订单率
        $countVal['current_youxiao_lv'] = setInfNanToN(round(($countVal['current_youxiao'] / $countVal['ddl']) * 100, 2));
        $info['current_youxiaolv'] =  setInfNanToN(round(($info['current_youxiao'] / $info['allCounts'])*100, 2));

        //分单率
        $countVal['fendan_lv'] = setInfNanToN(round(($countVal['fendan'] / $countVal['ddl']) * 100, 2));
        $info['fendanlv'] =  setInfNanToN(round(($info['fendan'] / $info['allCounts'])*100, 2));

        //实际分单率
        $countVal['real_fendan_lv'] = setInfNanToN(round(($countVal['real_fendan'] / $countVal['ddl']) * 100, 2));
        $info['real_fendanlv'] =  setInfNanToN(round(($info['real_fendan'] / $info['allCounts'])*100, 2));

        //实时分单率
        $countVal['current_fendan_lv'] = setInfNanToN(round(($countVal['current_fendan'] / $countVal['ddl']) * 100, 2));
        $info['current_fendanlv'] =  setInfNanToN(round(($info['current_fendan'] / $info['allCounts'])*100, 2));

        //异常单率
        $countVal['exception_lv'] = setInfNanToN(round(($countVal['exception_count'] / $countVal['ddl']) * 100, 1));

        $info['deptId'] = $deptId;
        $info['chargeId'] = $chargeId;

        // 获取合作商
        $partnerList = D("Partner")->getCompanyAll();
        $this->assign('partnerList',$partnerList);

        $this->assign('countval',$countVal);
        $this->assign('group',$list);
        $this->assign('info',$info);
        $this->assign('days',$day);
        $this->display('Orderstat/orderSrcStats');
    }


    public function ordersrcbycity(){
        $sourceid = I('get.src');
        $start = I('get.start');
        $end = I('get.end');

        //开始时间和结束时间
        $start = I('get.start');
        if(!empty($start)){
            $date = strtotime($start);
            $start = mktime(0,0,0,date("m",$date),date("d",$date),date("Y",$date));
        }else{
            $start = mktime(0,0,0,date("m"),date("d"),date("Y"));
        }

        $end = I('get.end');
        if(!empty($end)){
            $date = strtotime($end);
            $end   = mktime(23,59,59,date("m",$date),date("d",$date),date("Y",$date));
        }else{
            $end   = mktime(23,59,59,date("m"),date("d"),date("Y"));
        }

        $info['start'] = date('Y-m-d',$start);
        $info['end'] = date('Y-m-d',$end);
        $info['src'] = $sourceid;


        //获取发单数
        $_orderSrc = D('OrderSourceStats')->getOrderSrcByCity($start,$end,$sourceid);
        foreach ($_orderSrc as $key => $value) {
            $orderSrc[$value['cs']] = $value;
        }

        //获取实际发单数据
        $_realOrderSrc = D('OrderSourceStats')->getRealOrderSrcByCity($start,$end,$sourceid);
        foreach ($_realOrderSrc as $key => $value) {
            $realOrderSrc[$value['cs']] = $value;
        }

        //获取此来源订单
        $source = D('OrderSourceStats')->getOrderSrcById($sourceid);
        $info['srcName'] = $source['name'];

        //获取所有城市
        $quyuList = D('OrderSourceStats')->getAllQuyu();

        //组合数据
        foreach ($quyuList as $k => $v) {

            if(empty($orderSrc[$v['cid']]) && empty($realOrderSrc[$v['cid']])){
                continue;
            }


            //如果此城市有发单
            if(!empty($orderSrc[$v['cid']])){

                $list[$k]['num'] = $orderSrc[$v['cid']]['num'];
                $list[$k]['youxiao'] = $orderSrc[$v['cid']]['youxiao'];
                $list[$k]['fendan'] = $orderSrc[$v['cid']]['fendan'];
                $list[$k]['fendan_lv'] = setInfNanToN(round(($list[$k]['fendan'] / $list[$k]['num']) * 100, 2));

                //统计总数
                $count['youxiao'] = $count['youxiao'] + $orderSrc[$v['cid']]['youxiao'];
                $count['fendan'] = $count['fendan'] + $orderSrc[$v['cid']]['fendan'];
                $count['ddl'] = $count['ddl'] + $orderSrc[$v['cid']]['num'];
            }

            //如果此城市有实际发单
            if(!empty($realOrderSrc[$v['cid']])){

                $real_youxiao = $realOrderSrc[$v['cid']]['real_youxiao'];
                if(!empty($real_youxiao)){
                    $list[$k]['real_youxiao'] = $realOrderSrc[$v['cid']]['real_youxiao'];
                }

                $real_fendan = $realOrderSrc[$v['cid']]['real_fendan'];
                if(!empty($real_fendan)){
                    $list[$k]['real_fendan'] = $realOrderSrc[$v['cid']]['real_fendan'];
                }

                if(!empty($real_fendan)){
                    $list[$k]['real_fendan_lv'] = setInfNanToN(round(($real_fendan / $list[$k]['num']) * 100, 2));
                }

                //统计总数
                $count['real_youxiao'] = $count['real_youxiao'] + $real_youxiao;
                $count['real_fendan'] = $count['real_fendan'] + $real_fendan;
            }

            if(!empty($list[$k])){
                $list[$k]['cname'] = $v['cname'];

                $list[$k]['num'] = empty($list[$k]['num']) ? '0' : $list[$k]['num'];
                $list[$k]['youxiao'] = empty($list[$k]['youxiao']) ? '0' : $list[$k]['youxiao'];
                $list[$k]['real_youxiao'] = empty($list[$k]['real_youxiao']) ? '0' : $list[$k]['real_youxiao'];
                $list[$k]['fendan'] = empty($list[$k]['fendan']) ? '0' : $list[$k]['fendan'];
                $list[$k]['fendan_lv'] = empty($list[$k]['fendan_lv']) ? '0' : $list[$k]['fendan_lv'];
                $list[$k]['real_fendan'] = empty($list[$k]['real_fendan']) ? '0' : $list[$k]['real_fendan'];
                $list[$k]['real_fendan_lv'] = empty($list[$k]['real_fendan_lv']) ? '0' : $list[$k]['real_fendan_lv'];
            }
        }

        //分单率
        $count['fendan_lv'] = setInfNanToN(round(($count['fendan'] / $count['ddl']) * 100, 2));
        //实际分单率
        $count['real_fendan_lv'] = setInfNanToN(round(($count['real_fendan'] / $count['ddl']) * 100, 2));
        //统计总数
        $count['allCity'] = count($list);

        if(I('get.dl') == '1'){
            $this->downCityExcel($list,$count);
        }

        $this->assign('count',$count);
        $this->assign('list',$list);
        $this->assign('info',$info);
        $this->display('Orderstat/ordersrcbycity');
    }

    public function srcCityVip($value='')
    {
        $date = date("Y-m-d");
        //获取所有城市
        $citys = D("Quyu")->getAllQuyuOnly();
        //获取到期天数
        $date_maturity =  D("Options")->getOptionNoCache('DATE_MATURITY');
        if (I("get.date") !== "") {
             $date = I("get.date");
        }

        //获取符合的城市会员数量
        $result = $this->getCityVip($date_maturity["option_value"],I("get.date"),I("get.city"),I("get.sort"));
        $this->assign("date_maturity",$date_maturity["option_value"]);
        $this->assign("date",  $date);
        $this->assign("list", $result);
        $this->assign("citys", $citys);
        $this->display();
    }

    public function saveconfig()
    {
        if ($_POST) {
            $day = I("post.day");
            $reg = '/\d+/';
            if (!preg_match($reg, $day)) {
                $this->ajaxReturn(array('data'=>"",'info'=>'只能设置数字','status'=>0));
            }

            $options = D("Options")->getOptionNoCache("DATE_MATURITY");
            if (count($options) > 0) {
               $i = D("Options")->setOption($options["option_name"],$day);
            } else {
                $data = array(
                    "option_name" => "DATE_MATURITY",
                    "option_value" => $day,
                    "option_group" => "DATEMATURITY",
                    "option_remark" => "渠道推广城市筛选到期天数"
                );
                $i = D("Options")->addOption($data);
            }
            if ($i !== false) {
                $this->ajaxReturn(array('status'=>1));
            }
            $this->ajaxReturn(array('data'=>"",'info'=>'操作失败','status'=>0));
        }
    }

    //执行搜索过滤
    private function filterSearch($var,$deptId,$chargeId){
        /*
        总裁办可查看全部数据，权限上具体为可查看推广部和流量部，渠道上可查看免费和付费；

        推广部和流量部，各自查看自己的部门数据，总裁办的权限可以查看全部；
        付费中的总裁办和推广部的数据总裁办的权限可选择性查看，推广部仅看到本部门的付费


        1   推广部
        2   流量部
        3   产品部
        4   渠道部
        5   总裁办
        6   推广部自媒体组
        7   推广部视频组
        */
        $deptArray = array(
            '1' => array('5'),
            '2' => array('1','6','7'),
            '3' => array('2'),
        );

        if($var['source'] == '888888'){
            $var['dept'] = '2';
            $var['charge'] = '1';
        }

        //如果付费条件不为空
        if(!empty($chargeId)){
            if($var['charge'] != $chargeId){
                return false;
            }
        }

        //客服发单直接返回
        if($var['source'] == '164'){
            return true;
        }

        if(!empty($deptId) && !empty($var['dept']) && !empty($deptArray[$deptId])){
            if(!in_array($var['dept'],$deptArray[$deptId])){
                return false;
            }
        }

        return ture;
    }

    //获取统计细节
    public function getStatsDetails(){
        ini_set('memory_limit','512M');
        header("Content-type:text/html;charset=utf-8");
        $gid = I('get.gid');
        $type = I('get.subtype');

        $group = S("Cache:OrderSrc:Group:".session("uc_userinfo.id"));

        if(empty($group)){
            $this->ajaxReturn(array('data'=>'','info'=>'本次数据已过期，请重新查看！','status'=>0));
        }
        if(!is_array($group)) {
            $group = json_decode($group,true);
        }
        if(!empty($gid)){
            $gid = str_replace('#group-','',$gid);
        }

        //取图表总量
        if($type == 'groupAllData'){
            foreach ($group as $key => $value) {
                if ($gid == $value["id"]) {
                    $data = $value;
                }
            }
            $allCountByDay = S("Cache:OrderSrc:allCountByDay:".session("uc_userinfo.id"));

            foreach ($data["sub"] as $key => $value) {
                $dayList = array_keys($data['days']);
                foreach ($value['subGroup'] as $k => $v) {
                    $dayCount = '';
                    $data['item'][] = $v['source_name'];
                    $data['pieData'][] = array(
                          'value'=>$v['count'],
                          'name'=>$v['source_name'],
                    );
                    foreach ($dayList as $ks => $vs) {
                        if(!empty($allCountByDay[$v['source']][$vs])){
                            $dayCount[] = $allCountByDay[$v['source']][$vs];
                        }else{
                            $dayCount[] = '0';
                        }
                    }
                    $data['lineData'][]= array(
                        'name'=>$v['source_name'],
                        'type'=> 'line','smooth'=> 'true',
                        'data'=> $dayCount
                    );
                    $data['dayList'] = $dayList;
                }
            }
            $this->ajaxReturn(array('data'=>$data,'info'=>'成功','status'=>1));
        }elseif($type == 'subitem'){
            //表格详细数据列表
            // $allSrcList = S("Cache:OrderSrc:allSrcList:".session("uc_userinfo.id"));
            $orderSrcTime = session('orderSrcTime');

            foreach ($group as $key => $value) {
                if ($gid == $value["id"]) {
                    $data = $value;
                }
            }


            if (count($data) == 0) {
                $this->ajaxReturn(array('data'=>'','info'=>'没有找到数据,请尝试刷新页面！','status'=>0));
            }
            $html = "";

            foreach ($data["sub"] as $key => $value) {
                $html .= '<tr data-id="subItem-'.$gid.'" class="warning subList" ><td class="first-td">&nbsp;&nbsp;<i class="fa fa-plus-square-o sub" data-id="'.$gid.'" data-sub-id="'.$value["id"].' " data-type="itemList" data-on="0" data-level="2"></i>&nbsp;&nbsp;'.$value['group_name'].$icon.'&nbsp;</td>
                            <td>'.number_format($value['sub_UV']).'</td>
                            <td>'.number_format($value['sub_IP']).'</td>
                            <td>'.number_format($value['count']).'</a></td>
                            <td>'.number_format($value['tel_count']).'</td>
                            <td>'.number_format($value['un_tel_count']).'</td>
                            <td>'.number_format($value['sub_current_youxiao']).'</td>
                            <td>'.number_format($value['sub_real_youxiao']).'</td>
                            <td>'.number_format($value['sub_fendan']).'</td>
                            <td>'.$value['sub_fendan_lv'].' %</td>
                            <td>'.number_format($value['sub_real_fendan']).'</td>
                            <td>-</td>
                            <td>'.$value['sub_real_fendan_lv'].' %</td>
                            <td>'.$value['exception_lv'].' %</td>
                        </tr>';
            }

            if (!empty($html)) {
                $this->ajaxReturn(array('data'=>$html,'info'=>'成功','status'=>1));
            }

            $this->ajaxReturn(array('data'=>'','info'=>'没有找到数据,请尝试刷新页面！','status'=>0));
            die;
        } else if($type == 'itemList'){
            $subId = trim(I('get.subId'));
            //表格详细数据列表
            $allSrcList = S("Cache:OrderSrc:allSrcList:".session("uc_userinfo.id"));

            $orderSrcTime = session('orderSrcTime');

            foreach ($group as $key => $value) {
                if ($gid == $value["id"]) {
                    $data = $value;
                    break;
                }
            }

            if (count($data) == 0) {
                $this->ajaxReturn(array('data'=>'','info'=>'没有找到数据,请尝试刷新页面！','status'=>0));
            }
            $html = "";


            $orderCol = I('get.orderCol');
            $order = I('get.order');
            $result = array();
            foreach ($data["sub"][$subId]['subGroup'] as $k => $v) {
                $v['youxiao'] = empty($v['youxiao']) ? '0' : $v['youxiao'];
                $v['fendan'] = empty($v['fendan']) ? '0' : $v['fendan'];
                $v['UV'] = empty($allSrcList[$v['src']]) ? '0' : $allSrcList[$v['src']]['num'];
                $v['IP'] = empty($allSrcList[$v['src']]) ? '0' : $allSrcList[$v['src']]['ip_num'];
                $v['real_fendan'] = empty($v['real_fendan']) ? '0' : $v['real_fendan'];
                $v['current_fendan'] = empty($v['fendan']) ? '0' : $v['fendan'];
                $v['fendan_lv'] = setInfNanToN(round(($v['fendan'] / $v['count']) * 100, 2));
                $v['real_fendan_lv'] = setInfNanToN(round(($v['real_fendan'] / $v['count']) * 100, 2));
                $v['exception_lv'] = setInfNanToN(round(($v['exception_count'] / $v['count']) * 100,1));
                $result[] = $v;
            }

            $fieldCol = array(
                '0' => "source_name",
                '1' => "UV",
                '2' => "IP",
                '3' => "count",
                '4' => "tel_count",
                '5' => "un_tel_count",
                '6' => "current_youxiao",
                '7' => "real_youxiao",
                '8' => "current_fendan",
                '9' => "current_fendan_lv",
                '10' => "real_fendan",
                '11' => "real_fendan_prop_count",
                '12' => "real_fendan_lv",
                "13" => "exception_lv"
            );

            $order = $order == 'desc' ? SORT_DESC : SORT_ASC;
            $sortList = multi_array_sort($result,$fieldCol[$orderCol],$order);

            foreach ($sortList as $key => $v) {
                //增加描述信息
                $desc = $this->getOrderSrcDesc($v['source']);
                if(!empty($desc['descs'])){
                    $icon = '&nbsp;<a href="javascript:void(0)"><i title="'.$desc['descs'].'" class="fa fa-question-circle"></i></a>';
                }

                $url = '/ordersrcstats/ordersrcbycity/?src='.$v['source'].'&start='.date('Y-m-d',$orderSrcTime['start_times']).'&end='. date('Y-m-d',$orderSrcTime['end_times']);

                $detailsUrl = '/ordersrcstats/fadandetail?src='.$v["src"].'&start='.date('Y-m-d',$orderSrcTime['start_times']).'&end='. date('Y-m-d',$orderSrcTime['end_times']);
                $html .= '<tr data-id="subItem-'.$gid.'-'.$value["id"].'" class="warning subList" ><td class="first-td">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|—&nbsp;&nbsp;'.$v['source_name'].$icon.'&nbsp;<a href="'.$url.'" target="blank">详情</a></td>
                        <td>'.number_format($v['UV']).'</td>
                        <td>'.number_format($v['IP']).'</td>
                        <td><a href="'.$detailsUrl.'" target="blank" title="查看发单详细">'.number_format($v['count']).'</a></td>
                        <td>'.number_format($v['tel_count']).'</td>
                        <td>'.number_format($v['un_tel_count']).'</td>
                        <td>'.number_format($v['youxiao']).'</td>
                        <td>'.number_format($v['real_youxiao']).'</td>
                        <td>'.number_format($v['current_fendan']).'</td>
                        <td>'.$v['fendan_lv'].' %</td>
                        <td>'.number_format($v['real_fendan']).'</td>
                        <td>-</td>
                        <td>'.$v['real_fendan_lv'].' %</td>
                        <td>'.$v['exception_lv'].' %</td>
                    </tr>';
            }


            if (!empty($html)) {
                $this->ajaxReturn(array('data'=>$html,'info'=>'成功','status'=>1));
            }

            $this->ajaxReturn(array('data'=>'','info'=>'没有找到数据,请尝试刷新页面！','status'=>0));
            die;
        }
    }

    //取图表子细节
    public function getSubDetails(){
        die();
        $gid = I('get.gid');
        $type = I('get.subtype');
        $start_time = I('get.start_time');
        $end_time = I('get.end_time');
        $condition['time_real']  = array('between',array($start_time,$end_time));

        $group = S("Cache:OrderSrc:Group:".session("uc_userinfo.id"));
        $group = json_decode($group,true);

        if(empty($group)){
            $this->ajaxReturn(array('data'=>'','info'=>'本次数据已过期，请重新查看！','status'=>0));
        }
        if(!empty($gid)){
            $gid = str_replace('#group-','',$gid);
        }

        //有效订单量
        if($type == 'youxiao'){
            if($gid == '0'){
                //总的有效订单量
                $condition['on']  = array('EQ',4);
                $data = $this->getAllYouXiaoCount($group,$condition);
            }else{
                $data = $this->getSubYouXiaoCount($group,$gid,$condition);
            }

            $this->ajaxReturn(array('data'=>$data,'info'=>'成功','status'=>1));
            die;
        }

        //有效订单率
        if($type == 'youxiaolv'){
            if($gid == '0'){
                //总的有效订单量
                $data = $this->getAllYouXiaoLv($group,$condition);
            }else{
                $data = $this->getSubYouXiaoLv($group,$gid,$condition);
            }
            //dump($data);
            $this->ajaxReturn(array('data'=>$data,'info'=>'成功','status'=>1));
            die;
        }

        //分单量
        if($type == 'fendan'){
            if($gid == '0'){
                //总的分单量
                $data = $this->getAllFendan($group,$condition);
            }else{
                $data = $this->getSubFendan($group,$gid,$condition);
            }
            $this->ajaxReturn(array('data'=>$data,'info'=>'成功','status'=>1));
            die;
        }

        //分单率
        if($type == 'fendanlv'){
            if($gid == '0'){
                //总的分单率
                $data = $this->getAllFendanLv($group,$condition);
            }else{
                $data = $this->getSubFendanLv($group,$gid,$condition);
            }
            $this->ajaxReturn(array('data'=>$data,'info'=>'成功','status'=>1));
            die;
        }

        //UV
        if($type == 'uv'){
            if($gid == '0'){
                //总的分单率
                $data = $this->getAllUV($group,$condition);
            }else{
                $data = $this->getSubUV($group,$gid,$condition);
            }
            $this->ajaxReturn(array('data'=>$data,'info'=>'成功','status'=>1));
            die;
        }
    }

    public function fadandetail()
    {
        $src = I("get.src");
        if (!empty($src)) {
            $begin = I("get.start");
            $end = I("get.end");

            //获取时间段内该渠道的发单信息
            $result = $this->getSrcOrderDetailsList($src,$begin,$end,I("get.state"));
            $this->assign("list",$result["list"]);
            $this->assign("page",$result["page"]);
            $this->display('Orderstat/fadandetail');
        } else {
            $this->_error();
        }
    }

    /**
     * 获取电话列表
     * @return [type] [description]
     */
    public function gettellist()
    {
        if ($_POST) {
            $id = I("post.source");
            $begin = strtotime("-3 month", strtotime(I("post.end")));
            $end = strtotime(I("post.end"))+86400;

            $result = D("Orders")->getOrderListWithTelById($id,$begin,$end);
            foreach ($result as $key => $value) {
                if(empty($value['name'])){
                    $result[$key]['name'] = '';
                }
                if ($value["on"] == 4 && $value["type_fw"] == 1) {
                    $result[$key]["type"] = "分单";
                } elseif ($value["on"] == 4 && $value["type_fw"] == 2) {
                    $result[$key]["type"] = "赠单";
                } elseif ($value["on"] == 4 && $value["type_fw"] == 3) {
                    $result[$key]["type"] = "分没人跟";
                } elseif ($value["on"] == 4 && $value["type_fw"] == 4) {
                    $result[$key]["type"] = "赠没人跟";
                } elseif ($value["on"] == 5) {
                    $result[$key]["type"] = "无效";
                } else {
                    $result[$key]["type"] = "其他";
                }
            }
            $this->ajaxReturn(array('data'=>$result,'status'=>1));
        }
        $this->ajaxReturn(array('data'=>'','info'=>'没有找到数据,请尝试刷新页面！','status'=>0));
    }

    /**
     * 获取IP列表
     * @return [type] [description]
     */
    public function getiplist()
    {
        if ($_POST) {
            $ip = I("post.source");
            $begin = strtotime("-3 month", strtotime(I("post.end")));
            $end = strtotime(I("post.end"))+86400;

            $result = D("Orders")->getOrderListByIp($ip,$begin,$end);
            foreach ($result as $key => $value) {
                if(empty($value['name'])){
                    $result[$key]['name'] = '';
                }
                if ($value["on"] == 4 && $value["type_fw"] == 1) {
                    $result[$key]["type"] = "分单";
                } elseif ($value["on"] == 4 && $value["type_fw"] == 2) {
                    $result[$key]["type"] = "赠单";
                } else {
                    $result[$key]["type"] = "其他";
                }
            }
            $this->ajaxReturn(array('data'=>$result,'status'=>1));
        }
        $this->ajaxReturn(array('data'=>'','info'=>'没有找到数据,请尝试刷新页面！','status'=>0));
    }

    /**
     * 家具转化装修
     */
    public function converttonormal()
    {
        //获取部门列表
        $model = new OrderSourceLogicModel();
        $depts =  $model->getSourceDepartmentList();

        if (count($depts) == 0) {
            $this->_error();
        }
        $begin = I("get.begin");
        $end = I("get.end");
        $department = I("get.department");

        $flip= array_flip($this->dept);
        if (array_key_exists($department,$flip)) {
            $belong = $flip[$department];
            //获取所有部门ID
            $department = $model->getDepartmentListWithBelong($belong);
        } elseif(!empty($department)) {
            $department = [$department];
        }
        $charge = I("get.charge");

        //获取家具转装修订单列表 发单量、拨打量、未拨打量、有效单量、分单率、分单量、际分单量、实际分单率
        $jiajuOrder = $model->getConvertToNormalList($begin,$end,$department,$charge);

        $this->assign("list",$jiajuOrder);
        $this->assign("depts",$depts);
        $this->display();
    }

    /**
     *  装修转化家具
     */
    public function converttojiaju()
    {
        //获取部门列表
        $model = new OrderSourceLogicModel();
        $depts =  $model->getSourceDepartmentList();

        if (count($depts) == 0) {
            $this->_error();
        }
        $begin = I("get.begin");
        $end = I("get.end");
        $department = I("get.department");

        $flip= array_flip($this->dept);
        if (array_key_exists($department,$flip)) {
            $belong = $flip[$department];
            //获取所有部门ID
            $department = $model->getDepartmentListWithBelong($belong);
        } elseif(!empty($department)) {
            $department = [$department];
        }
        $charge = I("get.charge");

        //获取装修转家具订单列表 发单量、拨打量、未拨打量、有效单量、分单率、分单量
        $list = $model->getConvertToJiajuList($begin,$end,$department,$charge);

        $this->assign("list",$list);
        $this->assign("depts",$depts);
        $this->display();
    }

    public function convertorderdetails()
    {
        $begin = I("get.begin");
        $end = I("get.end");
        $src = I("get.src");
        $tab = I("get.tab");
        $status = I("get.status");
        $model = new OrderSourceLogicModel();
        switch ($tab){
            case 1:
                //家具转装修
                $list = $model->getCconvertOrderDetailsWithNormal($src,$begin,$end,$status);
                break;
            case 2:
                //装修转家具
                $list = $model->getCconvertOrderDetailsWithJiaju($src,$begin,$end,$status);
                break;
            case 3:
                //家具渠道发至装修
                $list = $model->getTransferOrderDetailsWithNormal($src,$begin,$end,$status);
                $tmp = "transfertonormaldetail";
                break;
        }

        $this->assign("list",$list);
        $this->display($tmp);
    }
    /**
     *  装修渠道发单至装修
     */
    public function transfertonormal ()
    {
        //获取部门列表
        $model = new OrderSourceLogicModel();
        $depts =  $model->getSourceDepartmentList();

        if (count($depts) == 0) {
            $this->_error();
        }
        $begin = I("get.begin");
        $end = I("get.end");
        $department = I("get.department");

        $flip= array_flip($this->dept);
        if (array_key_exists($department,$flip)) {
            $belong = $flip[$department];
            //获取所有部门ID
            $department = $model->getDepartmentListWithBelong($belong);
        } elseif(!empty($department)) {
            $department = [$department];
        }
        $charge = I("get.charge");
        //获取家具渠道发送至装修订单列表 发单量、拨打量、未拨打量、有效单量、分单率、分单量
        $list = $model->getTtransfertoNormalList($begin,$end,$department,$charge);

        $this->assign("list",$list);
        $this->assign("depts",$depts);
        $this->display();
    }

    /**
     * 转化单详细数据
     */
    public function getSourceDetailsWithConvert()
    {
        if ($_POST) {
            //获取部门列表
            $model = new OrderSourceLogicModel();
            $depts =  $model->getSourceDepartmentList();
            if (count($depts) == 0) {
                $this->ajaxReaturn(array("error_code"=>"1","error_msg" =>"您无权查看渠道数据"));
            }

            $department = I("get.department");

            $flip= array_flip($this->dept);
            if (array_key_exists($department,$flip)) {
                $belong = $flip[$department];
                //获取所有部门ID
                $department = $model->getDepartmentListWithBelong($belong);
            } elseif(!empty($department)) {
                $department = [$department];
            }

            $begin = I("post.begin");
            $end = I("post.end");
            $level = I("post.level");
            $group_id = I("post.gid");
            $category = I("post.category");
            $charge = I("post.charge");
            $tab = I("post.tab");

             //渠道组下所有的渠道标识
             $srcList = $model->getMyChildSrcList($category,$level,$group_id);

             if ($tab != 3 && $category == 2) {
                 //获取家具转装修订单列表 发单量、拨打量、未拨打量、有效单量、分单率、分单量、实际分单量、实际分单率’
                 $list = $model->getSourceDetailsWithConvert($begin,$end,$department,$charge,$srcList);
             } elseif ($tab != 3 && $category == 1){
                 //获取装修转家具订单列表 发单量、拨打量、未拨打量、有效单量、分单率、分单量、实际分单量、实际分单率’
                 $list = $model->getNormalSourceDetailsWithConvert($begin,$end,$department,$charge,$srcList);
             } elseif($tab == 3 && $category == 2){
                 //获取家具渠道发单至装修订单列表 发单量、拨打量、未拨打量、有效单量、分单率、分单量、实际分单量、实际分单率’
                 $list = $model->getTransfertoNormalDetailsWithConvert($begin,$end,$department,$charge,$srcList);
             }
            return $this->ajaxReturn(array("error_code"=>"0","error_msg" =>"","data" => $list));
        }
    }

    /**
     *
     */
    public function convertRepeatList()
    {
        if ($_POST) {
            $id = I("post.id");
            $tab = I("post.tab");
            $type = I("post.type");
            $model = new OrdersLogicModel();
            $jiaJuModel = new JiajuOrdersLogicModel();
            if ($type == "ip") {
                $field = "IP";
                switch ($tab){
                    case 1:
                        //装修单
                        $list = $model->getOrderIpDetails($id);
                        break;
                    case 2:
                    //家具单
                    $list = $jiaJuModel->getOrderIpDetails($id);
                    break;
                    case 3:
                        //装修单
                        $list = $model->getOrderIpDetails($id);
                        break;
                }
            } elseif ($type == "tel") {
                $field = "电话";
                switch ($tab){
                    case 1:
                        //装修单
                        $list = $model->getOrderTelDetails($id);
                        break;
                    case 2:
                        //家具单
                        $list = $jiaJuModel->getOrderTelDetails($id);
                        break;
                    case 3:
                        //装修单
                        $list = $model->getOrderTelDetails($id);
                        break;
                }
            }
            $this->assign("field",$field);
            $this->assign("list",$list);
            $tmp = $this->fetch("popup-repeat");
            $this->ajaxReturn(array("error_code"=>0,"data" => $tmp));
        }
    }

    //取UV 总的
    private function getAllUV($group,$condition){

        //取当前时段总UV数据 有来源分组数据
        $_allUV = D("OrderSourceStats")->getAllUVByDay($condition);

        foreach ($_allUV as $key => $value) {
            $allUV[$value['groupid']][$value['days']] = $allUV[$value['groupid']][$value['days']] + $value['num'];
        }

        foreach ($group as $k=> $val) {
            foreach ($val["sub"] as $key => $value) {
                $dayList = array_keys($val['days']);
                $dayCount = '';
                $value['group_name'] = empty($value['group_name']) ? '未知' : $value['group_name'];
                $data['dayList'] = $dayList;
                $data['item'][] = $value['group_name'];
                $data['pieData'][] = array(
                      'value'=>$value['sub_UV'],
                      'name'=>$value['group_name'],
                );

                foreach ($dayList as $ks => $vs) {
                    if(!empty($allUV[$value['id']][$vs])){
                        $dayCount[] = $allUV[$value['id']][$vs];
                    }else{
                        $dayCount[] = '0';
                    }
                }

                $data['lineData'][]= array(
                    'name'=> $value['group_name'],
                    'type'=> 'line','smooth'=> 'true',
                    'data'=> $dayCount
                );
                $data['dayList'] = $dayList;
            }
        }

        return $data;
    }

    //取UV 子分类
    private function getSubUV($group,$gid,$condition){
        //取当前时段总UV数据 有来源分组数据
        $_allUV = D("OrderSourceStats")->getAllUVByDay($condition);
        //dump($_allUV);
        foreach ($_allUV as $key => $value) {
            $allUV[$value['src']][$value['days']] = $allUV[$value['src']][$value['days']] + $value['num'];
        }
        foreach ($group as $key => $value) {
            if($value['id'] == $gid){
                //dump($value);
                $dayList = array_keys($value['days']);

                foreach ($value['subGroup'] as $k => $v) {
                    $dayCount = '';
                    $v['UV'] = empty($v['UV']) ? 0 : $v['UV'];
                    $data['item'][] = $v['source_name'];
                    $data['pieData'][] = array(
                          'value' => $v['UV'],
                          'name' => $v['source_name'],
                    );

                    foreach ($dayList as $ks => $vs) {
                        if(!empty($allUV[$v['src']][$vs])){
                            $dayCount[] = $allUV[$v['src']][$vs];
                        }else{
                            $dayCount[] = '0';
                        }
                    }
                    $data['lineData'][]= array(
                        'name'=>$v['source_name'],
                        'type'=> 'line','smooth'=> 'true',
                        'data'=> $dayCount
                    );
                    $data['dayList'] = $dayList;
                }
                return $data;
            }
        }
    }

    //有效订单量 总的
    private function getAllYouXiaoCount($group,$condition){
        $_allYouxiao = D("OrderSourceStats")->getOrderSrcAllByDay($condition);
        foreach ($_allYouxiao as $key => $value) {
            $allYouxiao[$value['groupid']][$value['days']] = $allYouxiao[$value['groupid']][$value['days']] + $value['num'];
        }
        foreach ($group as $k => $val) {
            foreach ($val["sub"] as $key => $value) {
                $dayList = array_keys($val['days']);
                $dayCount = '';
                $value['group_name'] = empty($value['group_name']) ? '未知' : $value['group_name'];
                $data['dayList'] = $dayList;
                $data['item'][] = $value['group_name'];
                $data['pieData'][] = array(
                      'value'=>$value['sub_youxiao'],
                      'name'=>$value['group_name'],
                );

                foreach ($dayList as $ks => $vs) {
                    if(!empty($allYouxiao[$value['id']][$vs])){
                        $dayCount[] = $allYouxiao[$value['id']][$vs];
                    }else{
                        $dayCount[] = '0';
                    }
                }
                $data['lineData'][]= array(
                    'name'=>$value['group_name'],
                    'type'=> 'line','smooth'=> 'true',
                    'data'=> $dayCount
                );
                $data['dayList'] = $dayList;
            }
        }

        return $data;
    }

    //有效订单量 子分类
    private function getSubYouXiaoCount($group,$gid,$condition){
        $condition['on']  = array('EQ',4);
        $_allYouxiao = D("OrderSourceStats")->getOrderSrcByDay($condition);
        foreach ($_allYouxiao as $key => $value) {
            if(empty($value['source'])){
                $value['source'] = '888888';
            }
            //自然流量有日期重复数据
            if(empty($allYouxiao[$value['source']][$value['days']])){
                $allYouxiao[$value['source']][$value['days']] = $value['num'];
            }else{
                $allYouxiao[$value['source']][$value['days']] = $allYouxiao[$value['source']][$value['days']] + $value['num'];
            }
        }

        foreach ($group as $key => $value) {
            if($value['id'] == $gid){
                //dump($value);
                $dayList = array_keys($value['days']);

                foreach ($value['subGroup'] as $k => $v) {
                    $dayCount = '';
                    $v['youxiao'] = empty($v['youxiao']) ? 0 : $v['youxiao'];
                    $data['item'][] = $v['source_name'];
                    $data['pieData'][] = array(
                          'value'=>$v['youxiao'],
                          'name'=>$v['source_name'],
                    );

                    foreach ($dayList as $ks => $vs) {
                        if(!empty($allYouxiao[$v['source']][$vs])){
                            $dayCount[] = $allYouxiao[$v['source']][$vs];
                        }else{
                            $dayCount[] = '0';
                        }
                    }
                    $data['lineData'][]= array(
                        'name'=>$v['source_name'],
                        'type'=> 'line','smooth'=> 'true',
                        'data'=> $dayCount
                    );
                    $data['dayList'] = $dayList;
                }
                return $data;
            }
        }
    }

    //有效订单率 总的
    private function getAllYouXiaoLv($group,$condition){
        //先取总量
        $_allCount = D("OrderSourceStats")->getOrderSrcAllByDay($condition);
        foreach ($_allCount as $key => $value) {
            $allCount[$value['groupid']][$value['days']] = $allCount[$value['groupid']][$value['days']] + $value['num'];
        }
        //dump($allCount);

        $condition['on']  = array('EQ',4);
        $_allYouxiao = D("OrderSourceStats")->getOrderSrcAllByDay($condition);

        foreach ($_allYouxiao as $key => $value) {
            $allYouxiao[$value['groupid']][$value['days']] = $allYouxiao[$value['groupid']][$value['days']] + $value['num'];
        }

        foreach ($group as $k => $val) {
            foreach ($val["sub"] as $key => $value) {
                unset($value['sub']);
                unset($value['subGroup']);

                $dayList = array_keys($val['days']);
                $dayCount = '';
                $value['group_name'] = empty($value['group_name']) ? '未知' : $value['group_name'];
                $data['dayList'] = $dayList;
                $data['item'][] = $value['group_name'];
                $data['pieData'][] = array(
                      'value' => setInfNanToN(round(($value['sub_youxiao'] / $value['count']) * 100, 2)),
                      'name' => $value['group_name'],
                );

                foreach ($dayList as $ks => $vs) {
                    if(!empty($allYouxiao[$value['id']][$vs])){
                        $dayCount[] = setInfNanToN(round(($allYouxiao[$value['id']][$vs] / $allCount[$value['id']][$vs]) * 100, 2));
                    }else{
                        $dayCount[] = '000';
                    }
                }
                $data['lineData'][]= array(
                    'name'=>$value['group_name'],
                    'type'=> 'line','smooth'=> 'true',
                    'data'=> $dayCount
                );
                $data['dayList'] = $dayList;
            }
        }

        //dump($data);die;
        return $data;
    }

    //有效订单率 子分类
    private function getSubYouXiaoLv($group,$gid,$condition){
        //先取总量
        $_allCount = D("OrderSourceStats")->getOrderSrcByDay($condition);
        foreach ($_allCount as $key => $value) {
            if(empty($value['source'])){
                $value['source'] = '888888';
            }
            //自然流量有日期重复数据
            if(empty($allCount[$value['source']][$value['days']])){
                $allCount[$value['source']][$value['days']] = $value['num'];
            }else{
                $allCount[$value['source']][$value['days']] = $allCount[$value['source']][$value['days']] + $value['num'];
            }
        }

        $condition['on']  = array('EQ',4);
        $_allYouxiao = D("OrderSourceStats")->getOrderSrcByDay($condition);
        foreach ($_allYouxiao as $key => $value) {
            if(empty($value['source'])){
                $value['source'] = '888888';
            }
            //自然流量有日期重复数据
            if(empty($allYouxiao[$value['source']][$value['days']])){
                $allYouxiao[$value['source']][$value['days']] = $value['num'];
            }else{
                $allYouxiao[$value['source']][$value['days']] = $allYouxiao[$value['source']][$value['days']] + $value['num'];
            }
            $allYouxiao[$value['source']][$value['days']] = $value['num'];
        }

        foreach ($group as $key => $value) {
            if($value['id'] == $gid){
                //dump($value);
                $dayList = array_keys($value['days']);

                foreach ($value['subGroup'] as $k => $v) {
                    $dayCount = '';
                    $v['youxiao'] = empty($v['youxiao']) ? 0 : $v['youxiao'];
                    $data['item'][] = $v['source_name'];
                    $data['pieData'][] = array(
                          'value' => setInfNanToN(round(($v['youxiao'] / $v['count']) * 100, 2)),
                          'name' => $v['source_name'],
                    );

                    foreach ($dayList as $ks => $vs) {
                        if(!empty($allYouxiao[$v['source']][$vs])){
                            $dayCount[] = setInfNanToN(round(($allYouxiao[$v['source']][$vs] / $allCount[$v['source']][$vs]) * 100, 2));
                        }else{
                            $dayCount[] = '0';
                        }
                    }

                    $data['lineData'][]= array(
                        'name'=>$v['source_name'],
                        'type'=> 'line','smooth'=> 'true',
                        'data'=> $dayCount
                    );
                    $data['dayList'] = $dayList;
                }
                return $data;
            }
        }
    }

    //分单量 总的
    private function getAllFendan($group,$condition){
        $condition['type_fw']  = array('EQ',1);
        //dump($condition);
        $_allYouxiao = D("OrderSourceStats")->getOrderSrcAllByDay($condition);
        //dump($_allYouxiao);
        foreach ($_allYouxiao as $key => $value) {
            $allYouxiao[$value['groupid']][$value['days']] = $allYouxiao[$value['groupid']][$value['days']] + $value['num'];
        }
        foreach ($group as $k => $val) {
            foreach ($val["sub"] as $key => $value) {
                $dayList = array_keys($val['days']);
                $dayCount = '';
                $value['group_name'] = empty($value['group_name']) ? '未知' : $value['group_name'];
                $data['dayList'] = $dayList;
                $data['item'][] = $value['group_name'];
                $data['pieData'][] = array(
                      'value'=>$value['sub_fendan'],
                      'name'=>$value['group_name'],
                );
                unset($value['sub']);

                foreach ($dayList as $ks => $vs) {
                    if(!empty($allYouxiao[$value['id']][$vs])){
                        $dayCount[] = $allYouxiao[$value['id']][$vs];
                    }else{
                        $dayCount[] = '0';
                    }
                }
                $data['lineData'][]= array(
                    'name'=>$value['group_name'],
                    'type'=> 'line','smooth'=> 'true',
                    'data'=> $dayCount
                );
                $data['dayList'] = $dayList;
            }
        }

        return $data;
    }

    //分单量 子分类
    private function getSubFendan($group,$gid,$condition){
        $condition['type_fw']  = array('EQ',1);
        $_allYouxiao = D("OrderSourceStats")->getOrderSrcByDay($condition);
        foreach ($_allYouxiao as $key => $value) {
            if(empty($value['source'])){
                $value['source'] = '888888';
            }
            //自然流量有日期重复数据
            if(empty($allYouxiao[$value['source']][$value['days']])){
                $allYouxiao[$value['source']][$value['days']] = $value['num'];
            }else{
                $allYouxiao[$value['source']][$value['days']] = $allYouxiao[$value['source']][$value['days']] + $value['num'];
            }
            $allYouxiao[$value['source']][$value['days']] = $value['num'];
        }
        //dump($allYouxiao);

        foreach ($group as $key => $value) {
            if($value['id'] == $gid){
                //dump($value);
                $dayList = array_keys($value['days']);

                foreach ($value['subGroup'] as $k => $v) {
                    $dayCount = '';
                    $v['fendan'] = empty($v['fendan']) ? 0 : $v['fendan'];
                    $data['item'][] = $v['source_name'];
                    $data['pieData'][] = array(
                          'value'=>$v['fendan'],
                          'name'=>$v['source_name'],
                    );

                    foreach ($dayList as $ks => $vs) {
                        if(!empty($allYouxiao[$v['source']][$vs])){
                            $dayCount[] = $allYouxiao[$v['source']][$vs];
                        }else{
                            $dayCount[] = '0';
                        }
                    }
                    $data['lineData'][]= array(
                        'name'=>$v['source_name'],
                        'type'=> 'line','smooth'=> 'true',
                        'data'=> $dayCount
                    );
                    $data['dayList'] = $dayList;
                }
                return $data;
            }
        }
    }

    //分单率 总的
    private function getAllFendanLv($group,$condition){
        //先取总量
        $_allCount = D("OrderSourceStats")->getOrderSrcAllByDay($condition);
        foreach ($_allCount as $key => $value) {
            $allCount[$value['groupid']][$value['days']] = $allCount[$value['groupid']][$value['days']] + $value['num'];
        }

        $condition['type_fw']  = array('EQ',1);
        $_allFendan = D("OrderSourceStats")->getOrderSrcAllByDay($condition);
        foreach ($_allFendan as $key => $value) {
            $allFendan[$value['groupid']][$value['days']] = $allFendan[$value['groupid']][$value['days']] + $value['num'];
        }

        foreach ($group as $k => $val) {
            foreach ($val["sub"] as $key => $value) {
                unset($value['sub']);
                unset($value['subGroup']);
                //dump($value);

                $dayList = array_keys($val['days']);
                $dayCount = '';
                $value['group_name'] = empty($value['group_name']) ? '未知' : $value['group_name'];
                $data['dayList'] = $dayList;
                $data['item'][] = $value['group_name'];
                $data['pieData'][] = array(
                      'value' => setInfNanToN(round(($value['sub_fendan'] / $value['count']) * 100, 2)),
                      'name' => $value['group_name'],
                );

                foreach ($dayList as $ks => $vs) {
                    if(!empty($allFendan[$value['id']][$vs])){
                        $dayCount[] = setInfNanToN(round(($allFendan[$value['id']][$vs] / $allCount[$value['id']][$vs]) * 100, 2));
                    }else{
                        $dayCount[] = '0';
                    }
                }
                $data['lineData'][]= array(
                    'name'=>$value['group_name'],
                    'type'=> 'line','smooth'=> 'true',
                    'data'=> $dayCount
                );
                $data['dayList'] = $dayList;
            }
        }

        return $data;
    }

    //获取渠道来源描述信息
    private function getOrderSrcDesc($id){
        $srcDesc = S("Cache:OrderSrc:SrcDesc");
        if(empty($srcDesc)){
            $_srcDesc = D("OrderSourceStats")->getOrderSrcDesc();
            foreach ($_srcDesc as $key => $v) {
                $srcDesc[$v['id']] = $v;
            }
            S("Cache:OrderSrc:SrcDesc",$srcDesc,9000);
        }

        if(!empty($id)){
            return $srcDesc[$id];
        }else{
            return $srcDesc;
        }
    }

    //分单率 子分类
    private function getSubFendanLv($group,$gid,$condition){
        //先取总量
        $_allCount = D("OrderSourceStats")->getOrderSrcByDay($condition);
        foreach ($_allCount as $key => $value) {
            if(empty($value['source'])){
                $value['source'] = '888888';
            }
            //自然流量有日期重复数据
            if(empty($allCount[$value['source']][$value['days']])){
                $allCount[$value['source']][$value['days']] = $value['num'];
            }else{
                $allCount[$value['source']][$value['days']] = $allCount[$value['source']][$value['days']] + $value['num'];
            }
            $allCount[$value['source']][$value['days']] = $value['num'];
        }

        $condition['type_fw']  = array('EQ',1);
        $_allFendan = D("OrderSourceStats")->getOrderSrcByDay($condition);
        foreach ($_allFendan as $key => $value) {
            if(empty($value['source'])){
                $value['source'] = '888888';
            }
            //自然流量有日期重复数据
            if(empty($allFendan[$value['source']][$value['days']])){
                $allFendan[$value['source']][$value['days']] = $value['num'];
            }else{
                $allFendan[$value['source']][$value['days']] = $allFendan[$value['source']][$value['days']] + $value['num'];
            }
            $allFendan[$value['source']][$value['days']] = $value['num'];
        }

        foreach ($group as $key => $value) {
            if($value['id'] == $gid){
                //dump($value);
                $dayList = array_keys($value['days']);

                foreach ($value['subGroup'] as $k => $v) {
                    //dump($v);
                    $dayCount = '';
                    $v['fendan'] = empty($v['fendan']) ? 0 : $v['fendan'];
                    $data['item'][] = $v['source_name'];
                    $data['pieData'][] = array(
                          'value' => setInfNanToN(round(($v['fendan'] / $v['count']) * 100, 2)),
                          'name' => $v['source_name'],
                    );

                    foreach ($dayList as $ks => $vs) {
                        if(!empty($allFendan[$v['source']][$vs])){
                            $dayCount[] = setInfNanToN(round(($allFendan[$v['source']][$vs] / $allCount[$v['source']][$vs]) * 100, 2));
                        }else{
                            $dayCount[] = '0';
                        }
                    }

                    $data['lineData'][]= array(
                        'name'=>$v['source_name'],
                        'type'=> 'line','smooth'=> 'true',
                        'data'=> $dayCount
                    );
                    $data['dayList'] = $dayList;
                }
                return $data;
            }
        }
    }

    private function addProportionFendan($data){
        foreach ($data as $k => $d) {
            $data[$k]['sub_real_fendan_prop'] = sprintf("%.2f", ($d['sub_real_fendan'] / (date("d") - 0.5)) * date("t"));
            $data['real_fendan_prop_count'] += $data[$k]['sub_real_fendan_prop'];
        }
        return $data;
    }

    //下载Excel
    public function downCityExcel($list,$count){

        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array ( 'cacheTime' => 300 );
        \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );
        $phpExcel = new \PHPExcel();

        //设置表头
        $title = array(
            '序号',
            '城市',
            '发单量',
            '有效订单量',
            '实际有效订单量',
            '分单量',
            '分单率',
            '实际分单量',
            '实际分单率',
        );
        $i = 0;
        foreach ($title as $key => $value) {
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . 1;
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value);
        }

        $phpExcel->getActiveSheet()->getStyle('A1:I1')->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                )
            )
        );

        $counts = array(
            '#',
            $count['allCity'],
            number_format($count['ddl']),
            number_format($count['youxiao']),
            number_format($count['real_youxiao']),
            number_format($count['fendan']),
            $count['fendan_lv'].' %',
            number_format($count['real_fendan']),
            $count['real_fendan_lv'].' %',
        );
        $i = 0;
        foreach ($counts as $key => $value) {
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . 2;
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value);
        }

        //设置表内容
        $j = 2;
        foreach ($list as $k => $v) {
            //初始化$i
            $i = 0;

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$j - 1);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['cname']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['num']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['youxiao']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['real_youxiao']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['fendan']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['fendan_lv'].' %');

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['real_fendan']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$v['real_fendan_lv'].' %');

            $j++;
        }

        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="订单渠道来源-城市数据详情.xls"');
        header("Content-Transfer-Encoding:binary");
        $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save('php://output');
        exit();
    }

    /**
     * 获取渠道订单信息
     * @param  [type] $src   [渠道标识]
     * @param  [type] $begin [开始时间]
     * @param  [type] $end   [结束时间]
     * @return [type]        [description]
     */
    private function getSrcOrderDetailsList($src,$begin,$end,$state)
    {
        if (empty($begin) && empty($end)) {
            $begin = mktime(0,0,0,date("m"),1,date("Y"));
            $end = strtotime("+1 day",strtotime(date("Y-m-d")));
        } else {
            $begin = strtotime($begin);
            $end = strtotime("+1 day", strtotime($end));
        }

        if ($src == "othersss") {
            $src = " ";
        }

        $count = D("OrderSourceStats")->getSrcOrderDetailsListCount($src,$begin,$end,$state);

        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $list = D("OrderSourceStats")->getSrcOrderDetailsList($src,$begin,$end,$state,$p->firstRow,$p->listRows);

            foreach ($list as $key => $value) {
                if ($value["on"] == 4 && $value["type_fw"] == 1) {
                    $list[$key]["type"] = "分单";
                } elseif ($value["on"] == 4 && $value["type_fw"] == 2) {
                    $list[$key]["type"] = "赠单";
                } elseif ($value["on"] == 4 && $value["type_fw"] == 3) {
                    $list[$key]["type"] = "分没人跟";
                } elseif ($value["on"] == 4 && $value["type_fw"] == 4) {
                    $list[$key]["type"] = "赠没人跟";
                } elseif ($value["on"] == 5) {
                    $list[$key]["type"] = "无效";
                } else {
                    $list[$key]["type"] = "其他";
                }
                $ids[] = $value["tel8"];
                $orders[] = $value["id"];
            }

            $ids = array_filter($ids);

            //获取电话信息
            $result = D("Orders")->getTelList($ids);

            foreach ($result as $key => $value) {
                $tels[trim($value["tel8"])] = $value["count"];
            }

            //获取重复IP信息
            $result = D("Orders")->getIpRepaetCountByIds($orders);

            foreach ($result as $key => $value) {
                $ips[$value["id"]] = $value["repeat_count"];
            }

            foreach ($list as $key => $value) {
                $list[$key]["ipcount"] = $ips[$value["id"]]["repeat_count"];
                $list[$key]["telcount"] = $tels[$value["tel8"]]["count"];
            }

            $pShow = $p->show();
        }

        return array("list"=>$list,"page"=>$pShow);
    }

    /**
     * 获取到期城市列表
     * @param  [type] $date_maturity [description]
     * @return [type]                [description]
     */
    private function getCityVip($date_maturity,$date,$city,$sort)
    {
        $day = date("Y-m-d");
        if (!empty($date)) {
            $day = $date;
        }

        $count = D("User")->getExpiringMemberListCount($day,$date_maturity,$city);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $show = $p->show();
            $list = D("User")->getExpiringMemberList($day,$date_maturity,$city,$sort,$p->firstRow,$p->listRows);
        }

        return array("list"=>$list,"page"=>$show);
    }
}
