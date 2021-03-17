<?php
namespace Sub\Controller;
use Sub\Common\Controller\SubBaseController;
class XiaoguotuController extends SubBaseController{

    public $selectType = array();
    public function _initialize(){
        parent::_initialize();
        $uri = $_SERVER['REQUEST_URI'];
        preg_match('/html$/',$uri,$m);

        if (count($m) == 0) {
            preg_match('/\/$/',$uri,$m);
            $parse = parse_url($uri);
            if (count($m) == 0 && empty($parse["query"])) {
                header( "HTTP/1.1 301 Moved Permanently");
                header("Location: ".$this->SCHEME_HOST['scheme_full'].$this->cityInfo["bm"].".". C("QZ_YUMING").$uri."/");
            }
        }
        //添加顶部搜索栏信息
        $this->assign('serch_uri','xgt');
        $this->assign('serch_type','装修案例');
        $this->assign('holdercontent','海量装修案例任你挑选');
        //导航栏标识
        $this->assign("tabIndex", 2);
        $this->assign("choose_menu", 'xgt');
    }

    // 面积筛选项
    private function getMianji($id = ""){
        $list = array(
            array(
                "id" => "1",
                "name" => "60㎡以下",
                "min_value" => "",
                "max_value" => "60",
            ),
            array(
                "id" => "2",
                "name" => "60~80㎡",
                "min_value" => "60",
                "max_value" => "80",
            ),
            array(
                "id" => "3",
                "name" => "80~100㎡",
                "min_value" => "80",
                "max_value" => "100",
            ),
            array(
                "id" => "4",
                "name" => "100~130㎡",
                "min_value" => "100",
                "max_value" => "130",
            ),
            array(
                "id" => "5",
                "name" => "130~160㎡",
                "min_value" => "130",
                "max_value" => "160",
            ),
            array(
                "id" => "6",
                "name" => "160~190㎡",
                "min_value" => "160",
                "max_value" => "190",
            ),
            array(
                "id" => "7",
                "name" => "190㎡以上",
                "min_value" => "190",
                "max_value" => "",
            )
        );

        if (!empty($id)) {
            foreach ($list as $key => $item) {
                if ($item["id"] == $id) {
                    return $item;
                }
            }

            return false;
        }

        return $list;
    }

    private function getMjMap($mj){
        $map = [];
        if (!empty($mj)) {
            $info = $this->getMianji($mj);
            $info["min_value"] = intval($info["min_value"]);
            $info["max_value"] = intval($info["max_value"]);
            if ($info["min_value"] && $info["max_value"]) {
                $map = array("BETWEEN", array($info["min_value"], $info["max_value"]));
            } else if ($info["min_value"]) {
                $map = array("EGT", $info["min_value"]);
            } else if ($info["max_value"]) {
                $map = array("ELT", $info["max_value"]);
            }
        }

        return $map;
    }

    public function index(){
        import('Library.Org.Page.Page');
        $page = new \Page();
        $params = $page->analyticalAddress();

        $urls = [];

        if (isset($params['params']['type']) && $params['params']['type']) {
            $urls['type'] = $params['params']['type'];
        }

        if (isset($params['params']['leixing']) && $params['params']['leixing']) {
            $urls['lx'] = $params['params']['leixing'];
        }

        if (isset($params['params']['huxing']) && $params['params']['huxing']) {
            $urls['hx'] = $params['params']['huxing'];
        }

        if (isset($params['params']['fengge']) && $params['params']['fengge']) {
            $urls['fg'] = $params['params']['fengge'];
        }

        if (isset($params['params']['jiage']) && $params['params']['jiage']) {
            $urls['jg'] = $params['params']['jiage'];
        }

        if (isset($params['params']['p']) && $params['params']['p']) {
            $urls['p'] = $params['params']['p'];
        }

        $uri = http_build_query($urls);
        header( "HTTP/1.1 301 Moved Permanently");
        header("Location: ".$this->SCHEME_HOST['scheme_full'].$this->cityInfo["bm"].".". C("QZ_YUMING")."/xgt?".$uri);
        exit();

        //判断是否是搜索引擎蜘蛛
        $robotIsTrue = B("Common\Behavior\RobotCheck");
        //$robotIsTrue = true; //debug 警告线上应该为注释状态
        if (true === $robotIsTrue) {
            $this->assign('robot',1);
        }
        $bm =  $this->bm;
        $xiaoguotuInfo = S('Cache:XiaoguotuInfo:'.$bm);
        $cityInfo = $this->cityInfo;
        if(!$xiaoguotuInfo){
            //获取装修案例图片数量
            $xiaoguotuInfo["caseimgsCount"] = releaseCount("caseimgs",strval($_SESSION["cityId"]));
            //获取户型
            $hx = D("Common/Huxing")->gethx();
            $top = array(
                    "id"=>"",
                    "type"=>"hx",
                    "name" =>"不限"
                         );
            array_unshift($hx,$top);
            $xiaoguotuInfo["hx"] = $hx;
            //获取装修风格列表
            $fg = D("Common/Fengge")->getfg();
            $top = array(
                    "id"=>"",
                    "type"=>"fengge",
                    "name" =>"不限"
                         );
            array_unshift($fg,$top);
            $xiaoguotuInfo["fenge"] = $fg;
            //获取造价
            $jiage = D("Common/Jiage")->getJiage();
            $top = array(
                    "id"=>"",
                    "type"=>"zaojia",
                    "name" =>"不限"
                         );
            unset($jiage[count($jiage)-1]);
            array_unshift($jiage,$top);
            $xiaoguotuInfo["jiage"] = $jiage;
            //获取类型
            $leixing = D("Common/Leixing")->getlx();
            $top = array(
                    "id"=>"",
                    "type"=>"leixing",
                    "name" =>"不限"
                         );
            array_unshift($leixing,$top);
            //删除无用的最后三个选项
            array_pop($leixing);
            array_pop($leixing);
            array_pop($leixing);

            $xiaoguotuInfo["leixing"] = $leixing;
            //获取选项卡
            $tabs = array(
                    array("id"=>1,"name"=>家装案例),
                    array("id"=>2,"name"=>公装案例),
                    array("id"=>3,"name"=>在建工地),
                          );
            $xiaoguotuInfo["tabs"] =  $tabs;

            S('Cache:XiaoguotuInfo:'.$bm,$xiaoguotuInfo,900);
        }

        $keyword = I("get.keyword");
        if(!checkKeyword($keyword)){
            $this->_error();
        }
        if(!empty($_GET["lx"])){
            $lx = remove_xss($_GET["lx"]);
        }

        if(!empty($_GET["ys"])){
            $ys = remove_xss($_GET["ys"]);
        }

        if(!empty($_GET["sm"])){
            $sm = remove_xss($_GET["sm"]);
        }
        //获取效果图片列表
        $pageIndex = 1;
        $pageCount = 48;
        $result = $this->getCaseImagesList($pageIndex,$pageCount,$classid,$huxing,$fengge,$zaojia,$ys,$sm,$keyword,strval($_SESSION["cityId"]));
        //默认的URL链接
        $param['classid'] = 1;
        //家装选择项
        $xiaoguotuInfo["hx"] = $this->getParams("h","",$xiaoguotuInfo["hx"],1,"");
        $xiaoguotuInfo["fenge"] = $this->getParams("f","",$xiaoguotuInfo["fenge"],1,"");
        $xiaoguotuInfo["jiage"] = $this->getParams("z","",$xiaoguotuInfo["jiage"],1,"");

        //公装选择项
        $xiaoguotuInfo["gzleixing"] = $this->getParams("lx","",$xiaoguotuInfo["leixing"],2,"");
        $xiaoguotuInfo["gzfenge"] = $this->getParams("f","",$xiaoguotuInfo["fenge"],2,"");
        $xiaoguotuInfo["gzjiage"] = $this->getParams("z","",$xiaoguotuInfo["jiage"],2,"");

        //在建工地选择项
        $xiaoguotuInfo["zjhx"] = $this->getParams("h","",$xiaoguotuInfo["hx"],3,"");
        $xiaoguotuInfo["zjfenge"] = $this->getParams("f","",$xiaoguotuInfo["fenge"],3,"");
        $xiaoguotuInfo["zjjiage"] = $this->getParams("z","",$xiaoguotuInfo["jiage"],3,"");
        if(count($result["params"]) > 0){
            //在有搜索条件的情况下
            switch ($result["params"]["type"]) {
                case '1':
                    //家装选择项
                    $xiaoguotuInfo["hx"] = $this->getParams("h",$result["url"],$xiaoguotuInfo["hx"],1,$result["params"]["huxing"]);
                    $xiaoguotuInfo["fenge"] = $this->getParams("f",$result["url"],$xiaoguotuInfo["fenge"],1,$result["params"]["fengge"]);
                    $xiaoguotuInfo["jiage"] = $this->getParams("z",$result["url"],$xiaoguotuInfo["jiage"],1,$result["params"]["jiage"]);
                    break;
             case '2':
                    $param['classid'] = 2;
                    //公装装选择项
                    $xiaoguotuInfo["gzleixing"] = $this->getParams("lx",$result["url"],$xiaoguotuInfo["leixing"],2,$result["params"]["leixing"]);
                    $xiaoguotuInfo["gzfenge"] = $this->getParams("f",$result["url"],$xiaoguotuInfo["fenge"],2,$result["params"]["fengge"]);
                    $xiaoguotuInfo["gzjiage"] = $this->getParams("z",$result["url"],$xiaoguotuInfo["jiage"],2,$result["params"]["jiage"]);
                    break;
            case '3':
                    $param['classid'] = 3;
                    //在建工地选择项
                    $xiaoguotuInfo["zjhx"] = $this->getParams("h",$result["url"],$xiaoguotuInfo["hx"],3,$result["params"]["huxing"]);
                    $xiaoguotuInfo["zjfenge"] = $this->getParams("f",$result["url"],$xiaoguotuInfo["fenge"],3,$result["params"]["fengge"]);
                    $xiaoguotuInfo["zjjiage"] = $this->getParams("z",$result["url"],$xiaoguotuInfo["jiage"],3,$result["params"]["jiage"]);
                    break;
            }
        }

        //添加页面关键字、描述
        $keys    = array();
        $tdk     = array();
        switch ($result["params"]["type"]) {
            case '1':
                $typeName = "家装";
                break;
            case '2':
                 $typeName = "公装";
                break;
            case '3':
                $typeName = "在建工地";
                break;
        }


        //造价
        foreach ($xiaoguotuInfo["jiage"] as $key => $value) {
            if(!empty($value["id"]) &&$value["id"] == $result["params"]["jiage"]){
                $tdk['jiage'] = $value["name"];
            }
        }

        //户型
        foreach ($xiaoguotuInfo["hx"] as $key => $value) {
            if(!empty($value["id"]) &&$value["id"] == $result["params"]["huxing"]){
                $tdk['huxing'] = $value["name"];
            }
        }

         //风格
        foreach ($xiaoguotuInfo["fenge"] as $key => $value) {
            if(!empty($value["id"]) &&$value["id"] == $result["params"]["fengge"]){
                $tdk['fengge'] = $value["name"];
            }
        }

        //类型
        foreach ($xiaoguotuInfo["leixing"] as $key => $value) {
            if(!empty($value["id"]) && $value["id"] == $result["params"]["leixing"]){
                $tdk['leixing'] = $value["name"];
            }
        }

        $info['typeName'] = $typeName;
        $info['typeid'] = $param['classid'];
        $info['selectType'] = $this->selectType;

        if(!empty($result["pageIndex"])){
            $pageContent ="第" . $result["pageIndex"] . "页";
        }

        if(!empty($_GET["keyword"])){
            $keyword = remove_xss($_GET["keyword"]);
            $this->assign("keyword",$keyword);
            $keys["title"]    = '搜索结果页';
            $keys["keywords"] = '';
            $keys["description"] ='';
        }else{
            $keys["title"] = $cityInfo["name"] . $tdk['huxing'] . $tdk['fengge'] . $tdk['jiage'] . $tdk['leixing'] . '装修案例_' . $tdk['leixing'] . '装修设计效果图片 ' . $pageContent . '-' . $cityInfo["name"] . '齐装网';
            $keys["keywords"] = $cityInfo["name"] . $tdk['huxing'] . $tdk['fengge'] . $tdk['jiage'] . $tdk['leixing'] . '装修案例_' . $tdk['leixing'] . '装修设计效果图片 ' . $pageContent . '-' . $cityInfo["name"] .'齐装网';
            $keys["description"] = $cityInfo["name"] . '齐装网为您提供' . date("Y") . '年流行的' . $tdk['huxing'] . $tdk['fengge'] . $tdk['leixing'] . '装修案例设计效果图片,以及' . $tdk['huxing'] . $tdk['fengge'] . '装修样板房图片！找装修案例设计效果图片就上齐装网！';
        }

        if(!empty($result)){
            $xiaoguotuInfo["images"] = $result["images"];
            $xiaoguotuInfo["page"] = $result["page"];
        }

        if($_SERVER['REQUEST_URI'] == '/xgt/'){
            $info['head']['mobile_agent'] = $this->SCHEME_HOST['scheme_full']. $cityInfo['bm'].'.'.C('QZ_YUMING').'/xgt/';
        }

        //页面canonical
        $info['head']['canonical'] = $canonical = $this->SCHEME_HOST['scheme_full'].$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if(strpos($canonical,'p'.$result['pageIndex']) !== false){
            $info['head']['canonical'] = str_replace('p'.$result['pageIndex'],'',$canonical);
        }

        $urlFenzhan = $this->SCHEME_HOST['scheme_full']. $cityInfo['bm'].'.'.C('QZ_YUMING');
        $this->assign("urlFenzhan", $urlFenzhan);

        if(!isset($_SERVER['REQUEST_URI'][5])){
            //获取友情链接
            $friendLink = S("C:FL:XGT:".$cityInfo['id']);
            if (!$friendLink) {
                $friendLink['link'] = D("Friendlink")->getFriendLinkList($cityInfo['id'],1,'xgt');
                S("C:FL:XGT:".$cityInfo['id'], $friendLink, 900);
            }
            $this->assign("friendLink",$friendLink);
        }


        $this->assign("keys",$keys);
        //安全验证码
        $safe = getSafeCode();
        $this->assign("safecode",$safe["safecode"]);
        $this->assign("safekey",$safe["safekey"]);
        $this->assign("ssid",$safe["ssid"]);
        $this->assign("param",$param);

        //获取报价模版
        $this->assign("order_source",103);
        $t = T("Common@Order/orderTmp");
        $orderTmp = $this->fetch($t);
        $this->assign("orderTmp",$orderTmp);

        //获取底部弹层
        $t = T("Common@Order/zb_bottom_s");
        $zb_bottom_s = $this->fetch($t);
        $this->assign("zb_bottom_s",$zb_bottom_s);

        //header搜索框搜索条件绑定
        $this->assign("header_search",1);
        $this->assign("xiaoguotuInfo",$xiaoguotuInfo);
        //显示头部导航栏效果
        $this->assign("nav_show",true);
        $this->assign("info",$info);
        $this->assign("cityinfo",$this->cityInfo);
        $this->display('index_p260');
    }

    // 装修效果图
    public function xgt(){
        $params = array(
            "type" => I("get.type", 1),
            "hx" => I("get.hx"),
            "lx" => I("get.lx"),
            "mj" => I("get.mj"),
            "fg" => I("get.fg"),
            "jg" => I("get.jg")
        );

        //跳转到手机端
        if (ismobile()) {
            header( "HTTP/1.1 301 Moved Permanently");
            header("Location: ".$this->SCHEME_HOST['scheme_full']. C('MOBILE_DONAMES') . "/" . $this->bm . "/xgt");
            exit();
        }

        //判断是否是搜索引擎蜘蛛
        $robotIsTrue = B("Common\Behavior\RobotCheck");
        //$robotIsTrue = true; //debug 警告线上应该为注释状态
        if (true === $robotIsTrue) {
            $this->assign('robot',1);
        }
        $bm =  $this->bm;
        $cityInfo = $this->cityInfo;
        $xiaoguotuInfo = $this->getFilterInfo();

        $keyword = I("get.keyword");
        if(!checkKeyword($keyword)){
            $this->_error();
        }
        if(!empty($_GET["lx"])){
            $lx = remove_xss($_GET["lx"]);
        }

        if(!empty($_GET["ys"])){
            $ys = remove_xss($_GET["ys"]);
        }

        if(!empty($_GET["sm"])){
            $sm = remove_xss($_GET["sm"]);
        }

        //获取效果图片列表
        $pageCount = 20;
        $result = $this->getCaseImagesList2($_GET["p"],$pageCount,$params,$keyword,strval($_SESSION["cityId"]));

        foreach ($result["images"] as $key => $item) {
            if ($item["classid"] == 2) {
                $result["images"][$key]["huxing_name"] = $item["leixing_name"];
            }
        }

        //家装选择项
        $xiaoguotuInfo["hx"] = $this->getParams2("hx",$params,$xiaoguotuInfo["hx"],1,$params["hx"]);
        $xiaoguotuInfo["fenge"] = $this->getParams2("fg",$params,$xiaoguotuInfo["fenge"],1,$params["fg"]);
        $xiaoguotuInfo["jiage"] = $this->getParams2("jg",$params,$xiaoguotuInfo["jiage"],1,$params["jg"]);
        $xiaoguotuInfo["mianji"] = $this->getParams2("mj",$params,$xiaoguotuInfo["mianji"],1,$params["mj"]);

        //公装选择项
        $xiaoguotuInfo["gzleixing"] = $this->getParams2("lx",$params,$xiaoguotuInfo["leixing"],2,$params["lx"]);
        $xiaoguotuInfo["gzfenge"] = $this->getParams2("fg",$params,$xiaoguotuInfo["fenge"],2,$params["fg"]);
        $xiaoguotuInfo["gzjiage"] = $this->getParams2("jg",$params,$xiaoguotuInfo["jiage"],2,$params["jg"]);
        $xiaoguotuInfo["gzmianji"] = $this->getParams2("mj",$params,$xiaoguotuInfo["mianji"],2,$params["mj"]);

        //在建工地选择项
        $xiaoguotuInfo["zjhx"] = $this->getParams2("hx",$params,$xiaoguotuInfo["hx"],3,$params["hx"]);
        $xiaoguotuInfo["zjfenge"] = $this->getParams2("fg",$params,$xiaoguotuInfo["fenge"],3,$params["fg"]);
        $xiaoguotuInfo["zjjiage"] = $this->getParams2("jg",$params,$xiaoguotuInfo["jiage"],3,$params["jg"]);
        $xiaoguotuInfo["zjmianji"] = $this->getParams2("mj",$params,$xiaoguotuInfo["mianji"],3,$params["mj"]);

        //添加页面关键字、描述
        $keys = array();
        $content ="";
        switch ($params["type"]) {
            case '1':
                $info['typeid'] = '1';
                $typeName = "家装";
                break;
            case '2':
                $info['typeid'] = '2';
                $typeName = "公装";
                break;
            case '3':
                $info['typeid'] = '3';
                $typeName = "在建工地";
                break;
        }

        //造价
        foreach ($xiaoguotuInfo["jiage"] as $key => $value) {
            if(!empty($value["id"]) &&$value["id"] == $params["jg"]){
                $content .= $value["name"];
            }
        }

        //户型
        foreach ($xiaoguotuInfo["hx"] as $key => $value) {
            if(!empty($value["id"]) &&$value["id"] == $params["hx"]){
                $content .= $value["name"];
            }
        }

        //户型
        foreach ($xiaoguotuInfo["mianji"] as $key => $value) {
            if(!empty($value["id"]) &&$value["id"] == $params["mj"]){
                $content .= $value["name"];
            }
        }

        //风格
        foreach ($xiaoguotuInfo["fenge"] as $key => $value) {
            if(!empty($value["id"]) &&$value["id"] == $params["fg"]){
                $content .= $value["name"];
            }
        }

        //类型
        foreach ($xiaoguotuInfo["fenge"] as $key => $value) {
            if(!empty($value["id"]) &&$value["id"] == $result["params"]["lx"]){
                $content .= $value["name"];
            }
        }

        $params["type"] = $params["type"] ? : 1;

        $info['typeName'] = $typeName;
        $info['typeid'] = $params["type"];
        $info['selectType'] = $this->selectType;

        if(!empty($result["pageIndex"])){
            $pageContent ="第" . $result["pageIndex"] . "页";
        }

        if(!empty($_GET["keyword"])){
            $keyword = remove_xss($_GET["keyword"]);
            $this->assign("keyword",$keyword);
            $keys["title"]    = '搜索结果页';
            $keys["keywords"] = '';
            $keys["description"] ='';
        }else{
            // $keys["title"] = $cityInfo["name"] . $tdk['huxing'] . $tdk['fengge'] . $tdk['jiage'] . $tdk['leixing'] . '装修案例_' . $tdk['leixing'] . '装修设计效果图片 ' . $pageContent . '-' . $cityInfo["name"] . '齐装网';
            // $keys["keywords"] = $cityInfo["name"] . $tdk['huxing'] . $tdk['fengge'] . $tdk['jiage'] . $tdk['leixing'] . '装修案例_' . $tdk['leixing'] . '装修设计效果图片 ' . $pageContent . '-' . $cityInfo["name"] .'齐装网';
            // $keys["description"] = $cityInfo["name"] . '齐装网为您提供' . date("Y") . '年流行的' . $tdk['huxing'] . $tdk['fengge'] . $tdk['leixing'] . '装修案例设计效果图片,以及' . $tdk['huxing'] . $tdk['fengge'] . '装修样板房图片！找装修案例设计效果图片就上齐装网！';

            $keys["title"] = $cityInfo["name"]."装修设计_".$cityInfo["name"]."装修案例_".$cityInfo["name"]."装修效果图-齐装网";
            $keys["keywords"] = $cityInfo["name"]."装修设计,".$cityInfo["name"]."装修案例,".$cityInfo["name"]."装修效果图";
            $keys["description"] = $cityInfo["name"]."齐装网为大家带来的是".$cityInfo["name"]."装修设计案例和精美的装修案例效果图，".$cityInfo["name"]."装修网为您提供海量的精美装修设计效果图，帮助您了解更多潮流的装修风格案例。";
        }

        if(!empty($result)){
            $xiaoguotuInfo["images"] = $result["images"];
            $xiaoguotuInfo["page"] = $result["page"];
        }

        if($_SERVER['REQUEST_URI'] == '/xgt/'){
            $info['head']['mobile_agent'] = $this->SCHEME_HOST['scheme_full']. $cityInfo['bm'].'.'.C('QZ_YUMING').'/xgt/';
        }

        //页面canonical
        $info['head']['canonical'] = $canonical = $this->SCHEME_HOST['scheme_full'].$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if(strpos($canonical,'p'.$result['pageIndex']) !== false){
            $info['head']['canonical'] = str_replace('p'.$result['pageIndex'],'',$canonical);
        }

        $urlFenzhan = $this->SCHEME_HOST['scheme_full']. $cityInfo['bm'].'.'.C('QZ_YUMING');
        $this->assign("urlFenzhan", $urlFenzhan);

        if(!isset($_SERVER['REQUEST_URI'][5])){
            //获取友情链接
            $friendLink = S("C:FL:XGT:".$cityInfo['id']);
            if (!$friendLink) {
                $friendLink['link'] = D("Friendlink")->getFriendLinkList($cityInfo['id'],1,'xgt');
                S("C:FL:XGT:".$cityInfo['id'], $friendLink, 900);
            }
            $this->assign("friendLink",$friendLink);
        }


        $this->assign("keys",$keys);
        //安全验证码
        $safe = getSafeCode();
        $this->assign("safecode",$safe["safecode"]);
        $this->assign("safekey",$safe["safekey"]);
        $this->assign("ssid",$safe["ssid"]);
        $this->assign("param",$param);

        //获取报价模版
        $this->assign("order_source",103);
        $t = T("Common@Order/orderTmp");
        $orderTmp = $this->fetch($t);
        $this->assign("orderTmp",$orderTmp);

        //获取底部弹层
        $t = T("Common@Order/zb_bottom_s");
        $zb_bottom_s = $this->fetch($t);
        $this->assign("zb_bottom_s",$zb_bottom_s);

        //header搜索框搜索条件绑定
        $this->assign("header_search",1);
        $this->assign("xiaoguotuInfo",$xiaoguotuInfo);
        //显示头部导航栏效果
        $this->assign("nav_show",true);
        $this->assign("info",$info);
        $this->assign("cityinfo",$this->cityInfo);
        $this->assign("params",$params);
        $this->display("index_p260");
    }

    // 获取筛选项
    public function getFilterInfo(){
        $bm = $this->bm;
        $xiaoguotuInfo = S('Cache:XiaoguotuInfo:xgt:'.$bm);
        $cityInfo = $this->cityInfo;
        if(!$xiaoguotuInfo){
            //获取装修案例图片数量
            $xiaoguotuInfo["caseimgsCount"] = releaseCount("caseimgs",strval($_SESSION["cityId"]));
            //获取户型
            $hx = D("Common/Huxing")->gethx();
            $top = array(
                    "id"=>"",
                    "type"=>"hx",
                    "name" =>"不限"
                         );
            array_unshift($hx,$top);
            $xiaoguotuInfo["hx"] = $hx;
            //获取装修风格列表
            $fg = D("Common/Fengge")->getfg();
            $top = array(
                    "id"=>"",
                    "type"=>"fengge",
                    "name" =>"不限"
                         );
            array_unshift($fg,$top);
            $xiaoguotuInfo["fenge"] = $fg;
            //获取造价
            $jiage = D("Common/Jiage")->getJiage();
            $top = array(
                    "id"=>"",
                    "type"=>"zaojia",
                    "name" =>"不限"
                         );
            unset($jiage[count($jiage)-1]);
            array_unshift($jiage,$top);
            $xiaoguotuInfo["jiage"] = $jiage;
            //获取类型
            $leixing = D("Common/Leixing")->getlx();
            $top = array(
                    "id"=>"",
                    "type"=>"leixing",
                    "name" =>"不限"
                         );
            array_unshift($leixing,$top);
            //删除无用的最后三个选项
            array_pop($leixing);
            array_pop($leixing);
            array_pop($leixing);

            foreach ($leixing as $key => $value) {
                $value["name"] = str_replace("装修", "", $value["name"]);
                $leixing[$key] = $value;
            }
            $xiaoguotuInfo["leixing"] = $leixing;

            // 面积
            $mianji = $this->getMianji();
            $top = [
                "id"=>"",
                "type"=>"mianji",
                "name" =>"不限",
                'nofollow' => 'rel="external nofollow"',
            ];
            array_unshift($mianji, $top);
            $xiaoguotuInfo["mianji"] = $mianji;

            //获取选项卡
            $tabs = array(
                    array("id"=>1,"name"=>家装案例, 'link' => '/xgt?type=1'),
                    array("id"=>2,"name"=>公装案例, 'link' => '/xgt?type=2'),
                    array("id"=>3,"name"=>在建工地, 'link' => '/xgt?type=3'),
                          );
            $xiaoguotuInfo["tabs"] =  $tabs;

            S('Cache:XiaoguotuInfo:xgt:'.$bm,$xiaoguotuInfo,900);
        }

        return $xiaoguotuInfo;
    }


    /**
     * 获取装修案例效果图
     * @return [type] [description]
     */
    private function getCaseImagesList($pageIndex = 1,$pageCount = 10,$classid,$huxing,$fengge,$jiage,$ys,$sm,$keyword,$cs,$robotIsTrue = false)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
        $normal_params = array(
                "huxing" => 0,
                "fengge" => 0,
                "jiage" => 0,
                "type" => 0
                );
        import('Library.Org.Page.Page');
        //自定义配置项
        $config  = array("prev","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config,null,$normal_params);
         //解析参数
        $params= $page->analyticalAddress();
        $count =  D("Common/Cases")->getCaseImagesListCount($params["params"]["type"],$params["params"]["huxing"],$params["params"]['fengge'],$params["params"]['jiage'],$ys,$sm,$keyword,$cs,$params["params"]["leixing"]);

        if($count > 0){
            $result =  $page->show_short($params["url"],$count,true);
            $pageTmp = $result;
            $result = D("Common/Cases")->getCaseImagesList(($page->pageIndex-1)*$pageCount,$pageCount,$params["params"]["type"],$params["params"]["huxing"],$params["params"]['fengge'],$params["params"]['jiage'],$ys,$sm,$keyword,$cs,$params["params"]["leixing"]);
            if(count($result) > 0){
                $images = array();
                $index = 0;
                foreach ($result as $key => $value) {
                    if(empty($value['bm'])){
                        $value["bm"] = $value["bmt"];
                    }
                    $date =floor((time()-$value["time"])/3600) <=0?1:floor((time()-$value["time"])/3600);
                    $rand = rand(2000,10000);
                    $value["date"] = $date;
                    $value["looked"] = $rand;
                    $value['writetime'] = timeFormatToMouth($value['time']);
                    $value["writer"] = empty($value["writer"])?"齐装网":$value["writer"];
                    $images[] = $value;
                }
                return array("images"=>$images,"page"=>$pageTmp,"params"=>$params["params"],"url"=>$params["url"],"pageIndex"=>$page->pageIndex);
            }
        }
        return null;
    }

    /**
     * 获取装修案例效果图
     * @return [type] [description]
     */
    private function getCaseImagesList2($pageIndex = 1,$pageCount = 10,$options,$keyword,$cs,$robotIsTrue = false)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $mjMap = $this->getMjMap($options['mj']);
        $count =  D("Common/Cases")->getCaseImagesListCountLimit($options["type"],$options["hx"],$options['fg'],$options['jg'],$options['ys'],$mjMap,$keyword,$cs,$options["lx"]);

        import('Library.Org.Page.LitePage');
        $config  = array("prev","next");
        $page = new \LitePage($pageIndex,$pageCount,$count,$config,null);

        if($count > 0){
            $pageTmp =  $page->show();
            $result = D("Common/Cases")->getCaseImagesList(($page->pageIndex-1)*$pageCount,$pageCount,$options["type"],$options["hx"],$options['fg'],$options['jg'],$options["ys"],$mjMap,$keyword,$cs,$options["lx"]);
            if(count($result) > 0){
                $images = array();
                $index = 0;
                foreach ($result as $key => $value) {
                    if(empty($value['bm'])){
                        $value["bm"] = $value["bmt"];
                    }
                    $date =floor((time()-$value["time"])/3600) <=0?1:floor((time()-$value["time"])/3600);
                    $rand = rand(2000,10000);
                    $value["date"] = $date;
                    $value["looked"] = $rand;
                    $value['writetime'] = timeFormatToMouth($value['time']);
                    $value["writer"] = empty($value["writer"])?"齐装网":$value["writer"];
                    $images[] = $value;
                }
                return array("images"=>$images,"page"=>$pageTmp,"params"=>$params["params"],"url"=>$params["url"],"pageIndex"=>$page->pageIndex);
            }
        }
        return null;
    }

    /**
     * [getParams description]
     * @param  [type] $prefix [前缀]
     * @param  [type] $url    [url]
     * @param  [type] $data   [数据源]
     * @param  [type] $type   [类型]
     * @param  [type] $val    [选中值]
     * @return [type]         [description]
     */
    private function getParams($prefix,$url,$data,$type,$val){
        if($url != ""){
            //获取短链接
            $url = $url["short_url"];
            $reg = '/t\d+/i';
            $url = preg_replace($reg, "t".$type,$url);
        }else{
            switch ($type) {
                 case '1':
                 case '3':
                     $links = "/xgt/list-h0f0z0t".$type;
                     break;
                 case '2':
                     $links = "/xgt/list-lx0f0z0t".$type;
                     break;
             }
        }

        foreach ($data as $key => $value) {
            $reg = '/'.$prefix.'\d+/i';
            if(empty($value["id"])){
                $value["id"] = 0;
            }
            if(!empty($url)){
                $link = preg_replace($reg, $prefix.$value["id"],$url);
                preg_match($reg, $url,$m);
            }else{
                $link = preg_replace($reg, $prefix.$value["id"],$links);
            }
            $data[$key]["link"] = $link;
            $data[$key]["checked"] = 0;
            if($val == $value["id"]){
                $data[$key]["checked"] = 1;
                if(!empty($value['id']) && !empty($value['name'])){
                    $this->selectType[$prefix] = $data[$key];
                }
            }
        }
        return $data;
    }

    public function getParams2($prefix, $params, $data, $type, $val){
        if ($type == 2) {
            unset($params["hx"]);
        } else {
            unset($params["lx"]);
        }

        foreach ($data as $key => $item) {
            $params["type"] = $type;
            $params[$prefix] = $item["id"];

            $param = array_filter($params);
            $data[$key]["link"] = "/xgt?".http_build_query($param);
            $data[$key]["checked"] = 0;
            if ($val == $item["id"]) {
                $data[$key]["checked"] = 1;
                if(!empty($item['id']) && !empty($item['name'])){
                    $this->selectType[$prefix] = $data[$key];
                }
            }
        }

        return $data;
    }
}
