<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class ZxbjController extends HomeBaseController{
    public function _initialize(){
        parent::_initialize();

        //判断是否是搜索引擎蜘蛛
        $robotIsTrue = B("Common\Behavior\RobotCheck");
        //$robotIsTrue = true; //debug 警告线上应该为注释状态
        if (true === $robotIsTrue) {
            $this->assign('robot',1);
        }
        //8秒报价默认为选中
        $this->assign('choose_more', 'zxbj');
        if(empty($this->cityInfo["bm"])){
            $t = T("Home@Index:header");
        }else{
            if(!$robotIsTrue){
                $t = T("Sub@Index:header");
            }
            //显示头部导航栏效果
            $this->assign("nav_show",true);
        }
        //导航栏标识
        $this->assign("tabIndex",6);
        $headerTmp = $this->fetch($t);
        $this->assign("headerTmp",$headerTmp);
    }

    public function index(){
        //检查客户端设备类型 移动版本跳转到
        B("Home\Behavior\MobileBrowserCheck");
        $info = S('Zxbj:Index:Telnum');
        if (empty($info)) {
            import('Library.Org.Util.App');
            $app = new \App();
            $head = array('135', '136', '137', '138', '139', '150', '151', '152', '159', '130', '131', '132', '155', '133', '153', '189');
            for ($i = 0; $i < 10 ; $i++) {
                $xing = $app->getRandXing();
                $sex_array = array("先生","女士");
                $sex = $sex_array[rand(0,1)];
                $sub["name"] = $xing.$sex;
                $sub["time"] = rand(1,6);
                $sub["tel"] = $head[rand(0,count($head)-1)] . '****' . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9);
                $info[] = $sub;
            }
            S('Zxbj:Index:Telnum', $info, 300);
        }

        if (isset($_COOKIE["w_qizuang_n"])) {
            $orderid = $_COOKIE["w_qizuang_n"];
            $order = D("Orders")->getOrderInfoById($orderid);
            $result = $this->calculatePrice($order["mianji"],$order["cs"]);
            $result["cs"] = $order["cs"];
            $result["qx"] = $order["qx"];
            $result["mianji"] = $order["mianji"];
            $result["xiaoqu"] = $order["xiaoqu"];
            $result["tel8"] = $order["tel8"];

            $this->assign("price",$result);
        }

        $source = '30';
        if(I('get.source')){
            $source = remove_xss(trim(I('get.source')));
        }
        $this->assign("source",$source);
        $this->assign("info",$info);
        $this->display("index_v1");
    }

    /**
     * 获取报价弹窗
     * @return [type] [description]
     */
    public function zxtc(){
       $temp = $this->fetch("step");
       $this->ajaxReturn(array("data"=>$temp,"info"=>"","status"=>1));
    }

    /**
     * 装修报价信息完善接口
     */
    public function getDetailsByAjax()
    {
        $order_id = $_COOKIE['w_qizuang_n'];
        $step = I('post.step');
        if (!empty($order_id) && empty($step)) {
            $orderid = $_COOKIE['w_qizuang_n'];
            $order = D('Orders')->getOrderInfoById($orderid);
            if (count($order) > 0) {
                $result = $this->calculatePrice($order['mianji'], $order['cs']);
                $this->ajaxReturn(['data' => $result, 'info' => '获取成功！', 'status' => 1]);
            }
        } elseif (!empty($order_id) && $step == 2) {
            $data['orderid'] = $_COOKIE['w_qizuang_n'];      //订单编号
            $data['name'] = remove_xss(I('post.name'));      //名称
            $data['sex'] = remove_xss(I('post.sex'));        //性别
            $data['lftime'] = remove_xss(I('post.lftime'));  //量房时间
            $data['start'] = remove_xss(I('post.start'));    //开工时间

            $ordersave = D('Common/Orders')->orderpublish($data, 'update'); //传入去修改完善订单
            if ($ordersave !== false) {
                $this->ajaxReturn(['data' => '', 'info' => '提交成功！', 'status' => 1]);
            } else {
                $this->ajaxReturn(['data' => '', 'info' => '获取失败，请刷新重试~', 'status' => 0]);
            }
        }
        $this->ajaxReturn(['data' => '', 'info' => '获取失败，请刷新重试~', 'status' => 0]);
    }

    public function get_city_baojia()
    {
        $cs = I('post.cs');
        if (!empty($cs)) {
            $mianji = [
                100, 125, 90, 110, 150, 200
            ];
            $result = S('Case:Home:Zxbj:Mortcity:baojia:' . $cs);
            if (empty($result)) {
                $result = $this->calculateMorePrice($mianji, $cs);
                S('Case:Home:Zxbj:Mortcity:baojia:' . $cs, $result, 21600);
            }
            $this->ajaxReturn(array("data" => $result, "info" => "获取成功！", "error_code" => 0));
        }
        $this->ajaxReturn(array("data" => '', "info" => "获取失败，请刷新重试~", "error_code" => 400));
    }

    /**
     * 获取详细报价
     * @return [type] [description]
     */
    public function getDetails(){
        if(isset($_COOKIE["w_qizuang_n"])){
            $orderid = $_COOKIE["w_qizuang_n"];
            $order = D("Orders")->getOrderInfoById($orderid);
            if(count($order) > 0){
                switch ($order["huxing"]) {
                    case '38':
                        $order["shi"] = "1";
                        $order["ting"] = "1";
                        $order["wei"] = "1";
                        break;
                    case '39':
                        $order["shi"] = "2";
                        $order["ting"] = "1";
                        $order["wei"] = "1";
                        break;
                    case '40':
                        $order["shi"] = "2";
                        $order["ting"] = "2";
                        $order["wei"] = "1";
                        break;
                    case '41':
                        $order["shi"] = "2";
                        $order["ting"] = "2";
                        $order["wei"] = "2";
                        break;
                    case '42':
                        $order["shi"] = "3";
                        $order["ting"] = "2";
                        $order["wei"] = "1";
                        break;
                    case '43':
                        $order["shi"] = "3";
                        $order["ting"] = "2";
                        $order["wei"] = "2";
                        break;
                    case '46':
                        $order["shi"] = "3";
                        $order["ting"] = "1";
                        $order["wei"] = "1";
                        break;
                    case '47':
                        $order["shi"] = "3";
                        $order["ting"] = "2";
                        $order["wei"] = "1";
                        break;
                    case '48':
                        $order["shi"] = "4";
                        $order["ting"] = "2";
                        $order["wei"] = "2";
                        break;
                    case '49':
                        $order["shi"] = "5";
                        $order["ting"] = "2";
                        $order["wei"] = "2";
                        break;
                    case '50':
                        $order["shi"] = "6";
                        $order["ting"] = "2";
                        $order["wei"] = "3";
                        break;
                }
                //如果户型为空,根据面积添加默认的户型
                if (empty($order["huxing"])) {
                    if ($order["mianji"] < 61) {
                        $order["shi"] = "1";
                        $order["ting"] = "1";
                        $order["wei"] = "1";
                    } else if ($order["mianji"] >= 61 && $order["mianji"] < 81) {
                        $order["shi"] = "2";
                        $order["ting"] = "1";
                        $order["wei"] = "1";
                    } else if ($order["mianji"] >= 81 && $order["mianji"] < 101) {
                        $order["shi"] = "2";
                        $order["ting"] = "2";
                        $order["wei"] = "1";
                    } else if ($order["mianji"] >= 101 && $order["mianji"] < 121) {
                        $order["shi"] = "2";
                        $order["ting"] = "2";
                        $order["wei"] = "2";
                    } else if ($order["mianji"] >= 121 && $order["mianji"] < 141) {
                        $order["shi"] = "3";
                        $order["ting"] = "1";
                        $order["wei"] = "1";
                    } else if ($order["mianji"] >= 141 && $order["mianji"] < 161) {
                        $order["shi"] = "3";
                        $order["ting"] = "2";
                        $order["wei"] = "1";
                    } else if ($order["mianji"] >= 161 && $order["mianji"] < 181) {
                        $order["shi"] = "3";
                        $order["ting"] = "2";
                        $order["wei"] = "2";
                    } else if ($order["mianji"] >= 181 && $order["mianji"] < 201) {
                        $order["shi"] = "4";
                        $order["ting"] = "2";
                        $order["wei"] = "2";
                    } else if ($order["mianji"] >= 201 && $order["mianji"] < 221) {
                        $order["shi"] = "5";
                        $order["ting"] = "2";
                        $order["wei"] = "2";
                    } else if ($order["mianji"] >= 221 ) {
                        $order["shi"] = "6";
                        $order["ting"] = "2";
                        $order["wei"] = "3";
                    } else {
                        $order["shi"] = "2";
                        $order["ting"] = "1";
                        $order["wei"] = "1";
                    }
                }
                //没有装修档次默认精装
                if (empty($order["zxdc"])) {
                   $order["zxdc"] = 2;
                }

                $location = $this->getZxInfo($order);
                $result = $this->getPricesTmp($order["mianji"],$order["zxdc"],$location["data"],$id,$order["cs"]);
                $title = $order["cname"]."_".$order["fengge"]."_".$order["hxname"]."装修报价明细-齐装网";
                foreach ($result['nowdetails'] as $key => $value) {
                    switch ($value['jc']) {
                        case 'kt':
                            $result['simple']['kt'] = $value['total'];
                            break;
                        case 'zw':
                            $result['simple']['zw'] = $value['total'];
                            break;
                        case 'wsj':
                            $result['simple']['wsj'] = $value['total'];
                            break;
                        default:
                            $result['simple']['other'] = $result['simple']['other'] + $value['total'];
                            break;
                    }
                }
                $this->assign("title",$title);
                $this->assign("order",$order);
                $this->assign("info",$result);
                $this->assign("tags",$location["tags"]);
                $this->display("details_v1");
                die();
            }
        }
        header("LOCATION:".$this->SCHEME_HOST['scheme_full'] ."www.qizuang.com/zxbj/");
    }

    /**
     * 获取详细报价的Array数组
     * @return [type] [description]
     */
    public function getDetailsArray(){
        if(isset($_COOKIE["w_qizuang_n"])){
            $orderid = $_COOKIE["w_qizuang_n"];
            $order = D("Orders")->getOrderInfoById($orderid);
            if(count($order) > 0){
                $result = $this->calculatePrice($order["mianji"],$order["cs"]);
                return $result;
                die();
            }
        }
    }

    public function getBJData(){
        $data = $this->getDetailsArray();
        if(!empty($data)){
            //美图终端页，返回报价总价，以后如有需要可以返回整个报价数组
            $this->ajaxReturn(array("data"=>['allTotal' => $data['allTotal']],"info"=>"","status"=>1));
        }
        $this->ajaxReturn(array("data"=>'',"info"=>"获取订单报价失败,请刷新后再试！","status"=>0));
    }

    /**
     * 获取房屋的装修信息
     * @return [type] [description]
     */
    private function getZxInfo($order){
        //计算出房屋具体的装修位置
        $data = array();
        $sort_tag = array();
        //计算出所有的房间
        for ($i = 1; $i <= $order["shi"]; $i++) {
            if($i == 1){
                array_push($data, "zw");
                $sort_tag[] = array(
                        "long" => "主卧",
                        "short" => "主",
                        "jc" => "zw",
                        "order" => "3"
                                    );
            }elseif($i == 2){
                array_push($data, "cw");
                $sort_tag[] = array(
                        "long" => "次卧",
                        "short" => "次",
                        "jc" => "cw",
                        "order" => "4"
                                    );
            }elseif($i == 3){
                array_push($data, "sf");
                $sort_tag[] = array(
                        "long" => "书房",
                        "short" => "书",
                        "jc" => "sf",
                        "order" => "6"
                                    );
            }elseif($i == 4){
                array_push($data, "kw");
                $sort_tag[] = array(
                        "long" => "客卧",
                        "short" => "客",
                        "jc" => "kw",
                        "order" => "5"
                );
            }elseif($i == 5){
                array_push($data, "etws");
                $sort_tag[] = array(
                        "long" => "儿童房",
                        "short" => "儿",
                        "jc" => "etws",
                        "order" => "7"
                                    );
            }elseif($i == 6){
                array_push($data, "zwf");
                $sort_tag[] = array(
                        "long" => "杂物房",
                        "short" => "杂",
                        "jc" => "zwf",
                        "order" => "8"
                                    );
            }
        }
        //计算出所有的厅
        for ($i = 1; $i <= $order["ting"] ; $i++) {
            if($i == 1){
                array_push($data, "kt");
                $sort_tag[] = array(
                        "long" => "客厅",
                        "short" => "厅",
                        "jc" => "kt",
                        "order" => "1"
                                    );

            }elseif($i == 2){
                array_push($data, "ct");
                $sort_tag[] = array(
                        "long" => "餐厅",
                        "short" => "餐",
                        "jc" => "ct",
                        "order" => "2"
                                    );

            }
        }

        //计算卫生间
        for ($i = 1; $i <= $order["wei"] ; $i++) {
            if($i == 1){
                array_push($data, "wsj");
                $sort_tag[] = array(
                        "long" => "卫生间",
                        "short" => "卫",
                        "jc" => "wsj",
                        "order" => "9"
                                    );

            }elseif($i == 2){
                array_push($data, "zwwsj");
                $sort_tag[] = array(
                        "long" => "主卧卫生间",
                        "short" => "主卫",
                        "jc" => "zwwsj",
                        "order" => "10"
                                    );

            }else{
                array_push($data, "kwwsj");
                $sort_tag[] = array(
                        "long" => "客卧卫生间",
                        "short" => "客卫",
                        "jc" => "kwwsj",
                        "order" => "11"
                                    );

            }
        }
        //计算厨房
        if($order["chu"]>0){
            array_push($data, "cf");
            $sort_tag[] = array(
                        "long" => "厨房",
                        "short" => "厨",
                        "jc" => "cf",
                        "order" => "12"
                                    );

        }
        //计算阳台
        for ($i = 1; $i <= $order["yangtai"] ; $i++) {
            switch ($i) {
                case '2':
                    array_push($data, "cyt");
                    $sort_tag[] = array(
                        "long" => "次阳台",
                        "short" => "次阳",
                        "jc" => "cyt",
                        "order" => "14"
                                    );

                    break;
                default:
                     array_push($data, "yt");
                     $sort_tag[] = array(
                        "long" => "阳台",
                        "short" => "台",
                        "jc" => "yt",
                        "order" => "13"
                                    );

                    break;
            }

        }

        $sort_tag[] = array(
                        "long" => "水电及安装",
                        "short" => "水电",
                        "jc" => "sd",
                        "order" => "98"
                                    );

        $sort_tag[] = array(
                        "long" => "综合其他",
                        "short" => "其他",
                        "jc" => "qt",
                        "order" => "99"
                                    );


        $edition = array();
        foreach ($sort_tag as $key => $value) {
            // 准备要排序的数组
            $edition[] = $value["order"];
        }
        array_multisort($edition, SORT_ASC,SORT_STRING,$sort_tag);
        return array("data"=>$data,"tags" => $sort_tag);
    }



    public function getPricesTmp($mianji,$zxdc,$location,$orderid,$cs){
        //键值反转
        $location = array_flip($location);
        //根据位置查询位置信息
        //获取装修的全部位置
        $locations = D("Construction")->getLocation();
        foreach ($locations as $key => $value) {
            if(array_key_exists($value["jc"], $location)){
                $data[] = $value;
            }
        }

        //将位置结果排序
        $edition = array();
        foreach ($data as $key => $value) {
            // 准备要排序的数组
            $edition[] = $value["orders"];
        }
        array_multisort($edition, SORT_ASC,$data);

        //获取当前城市的价格组信息
        $groupInfo = D("Construction")->getConstructionPriceGroupByCs($cs);
        if(count($groupInfo) == 0){
            return array("errcode"=>"","errInfo"=>"获取城市信息价格异常,请稍后再试！");
        }
        //获取价格组的详细信息
        $price = D("Construction")->getConstructionPriceByGroup($groupInfo["group"]);

        //全部的施工详细位置
        $result =  D("Construction")->getDetails();
        //根据装修档次获取装修的详细位置,暂时死代码代替
        switch($zxdc){
            case"1":
                $item = array(
                    1,9,11,15,27,34,40,43,53,56
                              );
                break;
            case"2":
                $item = array(
                    5,14,18,31,37,41,47,49,55
                              );
                break;
            case"3":
                $item = array(
                    6,13,21,31,37,41,45,51,55
                              );
                break;
        }
        //获取制定的装修项目
        foreach ($result as $key => $value) {
            if(in_array($value["id"],$item)  || in_array($value["parentid"],$item)){
                $details[] = $value;
            }
        }

        //获取当前详细的装修位置的具体信息
        $nowdetails = array();
        foreach ($data as $key => $value) {
            $nowdetails[$value["id"]]["total"] = 0;
            $nowdetails[$value["id"]] = $value;
            foreach ($details as $k => $val) {
                $exp  = array_flip(array_filter(explode(',',$val["location"])));
                if(array_key_exists($value["id"], $exp)){
                    $nowdetails[$value["id"]]["child"][] = $val;
                }
            }
        }

        //水电安装及其他项目清单信息
        foreach ($result as $key => $value) {
            if(empty($value["location"]) && $value["range"] == 0 && $value["parentid"] == 0){
                $nowdetails[$value["id"]]= $value;
                $nowdetails[$value["id"]]["total"] = 0;
                if($value["id"] == 57){
                    $nowdetails[$value["id"]]["jc"] = "sd";
                }else if($value["id"] == 65){
                     $nowdetails[$value["id"]]["jc"] = "qt";
                }
            }else if(empty($value["location"]) && $value["range"] == 0 && $value["parentid"] != 0){
                $nowdetails[$value["parentid"]]["child"][] = $value;
            }
        }

        //计算项目的详细价格
        $allDetailsTotal = 0;
        foreach ($nowdetails as $key => $value) {
           foreach ($value["child"] as $k => $val) {
                if($val["parentid"] != 0){
                    if(empty($val["location"]) && $val["range"] == 0 && $val["parentid"] != 0){
                        $noewprice = $price["other"][$val["id"]];
                    }else{
                        $noewprice = $price[$value["id"]][$val["id"]];
                    }

                    //计算价格
                    $result = $this->getDetailsPrice($noewprice["price"],$noewprice["width"],$noewprice["length"],$val["fangshi"],$mianji);

                    $nowdetails[$key]["child"][$k]["total"] = $result["total"];
                    $nowdetails[$key]["child"][$k]["count"] = $result["count"];
                    $nowdetails[$key]["child"][$k]["price"] = $noewprice["price"];
                    $nowdetails[$key]["total"] += $result["total"];
                    $allDetailsTotal += $result["total"];
                }
           }
        }

        //合并项目价格
        foreach ($nowdetails as $key => $value) {
            foreach ($value["child"] as $k => $val) {
                if($val["parentid"] == 0){
                    $nowdetails[$key]["item"][$val["range"]]["child"][$val["id"]] = $val;
                }else{
                    $nowdetails[$key]["item"][$val["range"]]["child"][$val["parentid"]]["child"][] = $val;
                }
                unset($nowdetails[$key]["child"][$k]);
            }
        }

        //计算全包价格
        $allMaterialsTotal = 0;
        //获取所有建材表
        $materialsList = D("Construction")->getMaterials();
        $item = array_flip($item);
        //合并建材表
        foreach ($data as $key => $value) {
            foreach ($materialsList as $k => $val) {
                $locations = array_filter(explode(',',$val["location"]));
                if(in_array($value["id"],$locations)){
                    if($val["group"] != 0){
                        $detailsid = array_filter(explode(',',$val["detailsid"]));
                        if(count($detailsid) > 0){
                            foreach ($detailsid as $v) {
                                if(isset($item[$v])){
                                    $nowmaterials[] = $val;
                                }
                            }
                        }else{
                            $nowmaterials[] = $val;
                        }
                    }
                }
            }
        }
        //计算价格
        foreach ($nowmaterials as $key => $val) {
            $result = $this->getMaterialsPrice($val["width"],$val["length"],$val["fangshi"],$val["price"]);
            $allMaterialsTotal += $result["total"];
        }
        $allMaterialsTotal += $allDetailsTotal;
        return array("halfTotal"=>$allDetailsTotal,"nowdetails"=>$nowdetails,"allTotal"=>$allMaterialsTotal);
    }

    //获取详细的价格明细
    private function getDetailsPrice($price,$width,$length,$fangshi=0,$mianji=0){
        // 计算方式  1.长*宽  2. （长+宽）*2  3.(长+宽)*2*2.8  4房屋面积*1
        // 5.1厨房+1卫生间 6. 等于5 7. 等于 4  8. 等于3  9 等于 6
        // 10. 等于 1+1  默认 0  表示 1
        $result = array();

        switch ($fangshi) {
            case 0:
                $count = 1;
                $total = sprintf('%.2f',1*$price);
                break;
            case 1:
                $count = sprintf('%.2f', ($width*$length));
                $total = sprintf('%.2f', ($width*$length)*$price);
                break;
            case 2:
                $count = sprintf('%.2f', ($width+$length)*2);
                $total = sprintf('%.2f', ($width+$length)*2*$price);
                break;
            case 3:
                $count = sprintf('%.2f', ($width+$length)*2*2.8);
                $total = sprintf('%.2f',($width+$length)*2*2.8*$price);
                break;
            case 4:
                $count = $mianji;
                $total = sprintf('%.2f',$mianji*$price);
                break;
            case 5:
                $count = "按实际计算";
                $total = 0;
                break;
            case 6:
                $count = 5;
                $total = sprintf('%.2f',5*$price);
                break;
            case 7:
                $count = 4;
                $total = sprintf('%.2f',4*$price);
                break;
            case 8:
                $count = 3;
                $total = sprintf('%.2f',3*$price);
                break;
            case 9:
                $count = 6;
                $total = sprintf('%.2f',6*$price);
                break;
            case 10:
                $count = "1+1";
                $total = sprintf('%.2f',2*$price);
                break;
        }

        $result["count"] = $count;
        $result["total"] = $total;
        return $result;
    }

    private function getMaterialsPrice($width,$length,$fangshi,$price){
        // 计算方式  1.长*宽  2. （长+宽）*2  3.(长+宽)*2*2.8  4房屋面积*1
        // 5.1厨房+1卫生间 6. 等于5 7. 等于 4  8. 等于3  9 等于 6  默认 0  表示 1
        //获取计算方式
        $result = array();
        $total = 0;
        $count = 0;
        switch ($fangshi) {
            case 0:
                $count = 1;
                $total = sprintf('%.2f', 1*$price);
                break;
            case 1:
                $count = sprintf('%.2f', ($width*$length));
                $total = sprintf('%.2f', ($width*$length)*$price);
                break;
            case 2:
                $count = sprintf('%.2f', ($width+$length)*2);
                $total = sprintf('%.2f', ($width+$length)*2*$price);
                break;
            case 3:
                $count = sprintf('%.2f', ($width+$length)*2*2.8);
                $total =  sprintf('%.2f', ($width+$length)*2*2.8*$price);
                break;
            case 4:
                $count = $mianji;
                $total =  sprintf('%.2f', $mianji*$price);
                break;
            case 5:
                $count = "按实际计算";
                $total = 0;
                break;
            case 6:
                $count = 5;
                $total = sprintf('%.2f', 5*$price);
                break;
            case 7:
                $count = 4;
                $total = sprintf('%.2f', 4*$price);
                break;
            case 8:
                $count = 3;
                $total = sprintf('%.2f', 3*$price);
                break;
            case 9:
                $count = 6;
                $total = sprintf('%.2f', 6*$price);
                break;
        }
        $result["count"] = $count;
        $result["total"] = $total;
        return $result;
    }

    /**
     * 计算价格
     * @param  [type] $mianji [面积]
     * @param  [type] $cs [城市]
     * @return [type]         [description]
     */
    public function calculatePrice($mianji,$cs)
    {
        //占比：客厅25% 卧室 18% 厨房 8% 卫生间16% 水电25% 其他 8%
        //计算公式 （城市最低半包单价*120%）*房子的面积
        //获取改订单城市的最低半包价格
        $result = D("Orders")->getCityPrice($cs);
        $price = round(($result["half_price_min"]+$result["half_price_max"])/2);
        if (empty($price)) {
            $price = 300;
        }

        $total = $price*1.2*$mianji;
        $result['kt'] = $total*0.25 ;
        $result['zw'] = $total*0.18;
        $result['wsj'] = $total*0.16;
        $result['cf'] = $total*0.08;
        $result['sd'] = $total*0.25;
        $result['other'] = $total*0.08;
        $result['total'] = $total;
        return $result;
    }


    /**
     * 计算多面积价格
     * @param  [type] $mianji [面积]
     * @param  [type] $cs [城市]
     * @return [type]         [description]
     */
    private function calculateMorePrice($mianji = [],$cs)
    {
        //占比：客厅25% 卧室 18% 厨房 8% 卫生间16% 水电25% 其他 8%
        //计算公式 （城市最低半包单价*120%）*房子的面积

        //获取改订单城市的最低半包价格
        $result = D("Orders")->getCityPrice($cs);
        $price = round( ($result["half_price_min"]+$result["half_price_max"])/2);
        if (empty($price)) {
            $price = 300;
        }

        $result = [];
        foreach ($mianji as $k=>$v){
            $total = $price*1.2*$v;
            $result[$k]['kt'] = $total*0.25 ;
            $result[$k]['zw'] = $total*0.18;
            $result[$k]['wsj'] = $total*0.16;
            $result[$k]['cf'] = $total*0.08;
            $result[$k]['sd'] = $total*0.25;
            $result[$k]['other'] = $total*0.08;
            $result[$k]['total'] = $total;
        }
        return $result;
    }
}
