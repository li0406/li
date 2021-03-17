<?php
/**
 * 移动版 - 美图
 */

namespace Mobile\Controller;

use Common\Model\Logic\MeituLogicModel;
use Mobile\Common\Controller\MobileBaseController;

class MeituController extends MobileBaseController
{
    public function _initialize(){
        parent::_initialize();
        if (IS_GET) {
            $uri = $_SERVER['REQUEST_URI'];
            preg_match('/html$/', $uri, $m);
            if (count($m) == 0) {
                preg_match('/\/$/', $uri, $m);
                $parse = parse_url($uri);
                if (count($m) == 0 && empty($parse["query"])) {
                    header("HTTP/1.1 301 Moved Permanently");
                    if (isSsl()) {
                        $http = "https://";
                    } else {
                        $http = "http://";
                    }
                    header("Location: " . $http . $_SERVER["HTTP_HOST"] . $uri . "/");
                    die();
                }
            }
        }
    }

    public function index()
    {
        //轮播数据固定
        $info['lunbo'] = [
            '0' => [
                'id' => '4',
                'title' => '现代简约风格复式楼室内装修效果图',
                'link' => $this->SCHEME_HOST['scheme_full'].'meitu.'.$this->SCHEME_HOST['domain'].'/p48106.html',
                'img_path' => 'meitu/20171228/FkKnJnDMFY8d45_Pr4ajydpoIlWB',
                'img_host' => 'qiniu',
                'description' => '现代简约风格复式楼室内装修效果图',
                'enabled' => '1',
                'px' => '4',
                'time' => '1474515736',
            ],
            '1' => [
                'id' => '3',
                'title' => '简约风格时尚单身公寓装修效果图',
                'link' => $this->SCHEME_HOST['scheme_full'].'meitu.'.$this->SCHEME_HOST['domain'].'/p44090.html',
                'img_path' => 'meitu/20171228/FgfcexYM8bUS_p3s3sUW7jYXIGUG',
                'img_host' => 'qiniu',
                'description' => '简约风格时尚单身公寓装修效果图',
                'enabled' => '1',
                'px' => '3',
                'time' => '1474515513',
            ],
            '2' => [
                'id' => '1',
                'title' => '不花冤枉钱 免费领取4份装修预算报价！',
                'link' => $this->SCHEME_HOST['scheme_host'].'/baojia/',
                'img_path' => 'meitu/20171228/FoQvY7c7xSQmjQHdsDu73X4KbKLS',
                'img_host' => 'qiniu',
                'description' => '齐装网，你身边的装修管家，不花一分冤枉钱，让你拥有更省钱省心的装修方案。',
                'enabled' => '1',
                'px' => '2',
                'time' => '1436862611',
            ],
            '3' => [
                'id' => '2',
                'title' => '装修怕上当  就找齐装网！',
                'link' => $this->SCHEME_HOST['scheme_host'].'/sheji/',
                'img_path' => 'meitu/20171228/FjfEpDWUhKlGupwQ6w_NbbLxT6IK',
                'img_host' => 'qiniu',
                'description' => '不方便，不装修！不专业，不装修！不靠谱，不装修！装修怕上当  就找齐装网！',
                'enabled' => '1',
                'px' => '1',
                'time' => '1437028058',
            ],
        ];

        //如果没有设置 单图套图 Cookie(获取图片或图集)
        $multi = cookie('meitu_multi');
        $multi = $multi == '1' ? true : false;
        $info['multi'] = $multi;

        //获取美图列表
        $pageIndex = isset($_GET['p']) ? $_GET['p'] : 1;
        $PageCount = 10;

        //搜索功能
        $keyword = I("get.keyword");
        if (!empty($keyword)) {
            if (!checkKeyword($keyword)) {
                $this->_error();
            }
            $keyword = remove_xss($keyword);
        }

        //获取图集数据
        $meitu = $this->getHotMeiTuList($pageIndex, $PageCount, $keyword, "true");

        $info["meitu"] = $meitu["list"];
        $info["page"] = $meitu["page"];

        //判断是否为 ajax 请求
        if (IS_AJAX) {
            if (!empty($info['meitu'])) {
                $this->ajaxReturn(array('status' => 1, 'data' => $info['meitu']));
            }
            $this->ajaxReturn(array('status' => 0));
            exit();
        }


        //seo 标题/描述/关键字
        $basic["head"]["title"] = "装修效果图_" . date("Y") . "室内家装装饰设计效果图大全-齐装网装修效果图";
        $basic["head"]["keywords"] = "装修效果图,装饰效果图,家装效果图,室内装修效果图大全,室内装修效果图,家装效果图大全";
        $basic["head"]["description"] = "齐装网汇聚" . date("Y") . "国内外受欢迎的家庭装修效果图片，为您提供全新室内装修装饰效果图大全以及丰富的家居设计美图，不一样的装修图片为您带来不一样的房屋装修灵感。找装修美图就上齐装网！";
        $basic["body"]['title'] = "效果图";

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $info['canonical'] = $_SERVER['REQUEST_URI'];

        $this->assign('source', 320);//设置发单入口标识
        $this->assign("info", $info);
        $this->assign("basic", $basic);
        $this->display("index");
    }

    //美图列表
    public function meitulist(){
        //个人处理方案
        if ($_SERVER['REQUEST_URI'] == '/meitu/list/') {
            $_SERVER['REQUEST_URI'] = '/meitu/list-l0f0h0c0/';
        }
        /*S-动态参数分页跳转到静态参数分页*/
        if (isset($_GET['a1'])) {
            $arr = array(
                'l' => 'a1',
                'f' => 'a2',
                'h' => 'a3',
                'c' => 'a4'
            );
            //拼接静态URL
            $url = $this->SCHEME_HOST['scheme_full'].'m.'.C('QZ_YUMING').'/meitu/list-';
            foreach ($arr as $key => $value) {
                $url = $url . $key . intval($_GET[$value]);
            }
            if (isset($_GET['q'])) {
                $url = $url . 'q' . intval($_GET['q']);
            } else if (intval($_GET['p']) > 1) {
                $url = $url . 'p' . intval($_GET['p']);
            }
            $url = $url . '?';
            foreach ($_GET as $key => $value) {
                if (!in_array($key, array('a1', 'a2', 'a3', 'a4', 'p', 'q'))) {
                    $url = $url . $key . '=' . $_GET[$key] . '&';
                }
            }
            $url = rtrim($url, '&');
            $url = rtrim($url, '?');
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:".$url);
            die();
        }
        /*E-动态参数分页跳转到静态参数分页*/

        //获取美图列表
        $pageIndex = 1;
        $pageCount = 40;
        $keyword = I('get.keyword');
        //去除空格
        $keyword = str_replace(" ", "", $keyword);
        unset($_GET["keyword"]);

        $meitu = $this->getMeiTuList($pageIndex,$pageCount,$keyword);

        $info["meitu"] = $meitu["list"];
        $info["page"] = $meitu["page"];
        $this->assign('search_keyword',$keyword);
        //判断是否为 ajax 请求
        if(IS_AJAX){
            $this->assign('info', $info);
            $content = $this->fetch('list-content');
            echo $content;
            die();
        }

        //没有获取到相应参数跳转404
        /*$keywordFlag = 0;
        if(empty($meitu['params']) && empty($keyword)){
            $keywordFlag = 1;
            //$this->_error();
        }
        $this->assign("keywordFlag", $keywordFlag);*/

        $items = $meitu["count"];
        $totalpage = ceil($items/$pageCount);
        foreach ($meitu["params"] as $key => $value) {
            if($value > 0){
                $count ++;
                $params[]= $key;
            }
        }
        $params_count = count($params);
        //如果是1个参数
        if(count($params) == 1){
            $type = $params[0];
        }

        //获取推荐局部信息
        $location = D("Meitu")->getLocation();
        $info["wz"] = $this->getStaticNavUrl($location, 'l', $meitu['url']);
        //获取风格
        $fengge = D("Meitu")->getFengge();
        $info["fg"] = $this->getStaticNavUrl($fengge, 'f', $meitu['url']);
        //获取户型
        $huxing = D("Meitu")->getHuxing();
        $info["hx"] = $this->getStaticNavUrl($huxing, 'h', $meitu['url']);
        //获取颜色信息
        $color = D("Meitu")->getColor();
        $info["ys"] = $this->getStaticNavUrl($color, 'c', $meitu['url']);
        foreach ($info["ys"] as $key => $value) {
            if ($value['color'] == '#FFF') {
                $info["ys"][$key]['style'] = 'background: #fff;border:1px solid #ccc';
            } else if (empty($value['color'])){
                $info["ys"][$key]['style'] = 'background:url(/assets/mobile/meitu/img/colorful.png)no-repeat 0 0px;padding:1px;';
            } else {
                $info["ys"][$key]['style'] = 'background: '. $value['color'] .';border:1px solid '. $value['color'] .';';
            }
        }
        //动态生成绑定参数
        foreach ($meitu["params"] as $key => $value) {
            switch ($key) {
                case 'location':
                    $sub = $this->getParams($key,$value,$count,$meitu["url"],$info["wz"],'sheji');
                    break;
                case 'fengge':
                    $sub = $this->getParams($key,$value,$count,$meitu["url"],$info["fg"],'sheji');
                    break;
                case 'huxing':
                    $sub = $this->getParams($key,$value,$count,$meitu["url"],$info["hx"],'sheji');
                    break;
                case 'color':
                    $sub = $this->getParams($key,$value,$count,$meitu["url"],$info["ys"],'sheji');
                    break;
            }
            $info["params"][] = $sub;
            $info["navParams"][$key] = $value;
        }

        //获取推荐数据
        if (empty($info["meitu"])) {
            $info["recommend"] = S('m:meitu:list:recommend');
            if (empty($info["recommend"])) {
                $info["recommend"] = D("Meitu")->getMeiTuList(0,3,"","","","",'','99','likes DESC, id DESC');
                foreach ($info["recommend"] as $key => $value) {
                    //取每个分类的第一个元素
                    $exp =array_filter(explode(",", $value["wz"]));
                    $info["recommend"][$key]["wz"] = $exp[0];
                    $exp =array_filter(explode(",", $value["fg"]));
                    $info["recommend"][$key]["fg"] = $exp[0];
                    $exp =array_filter(explode(",", $value["hx"]));
                    $info["recommend"][$key]["hx"] = $exp[0];
                    $exp =array_filter(explode(",", $value["ys"]));
                    $info["recommend"][$key]["ys"] = $exp[0];
                }
                S('m:meitu:list:recommend', $info["recommend"], 900);
            }
        }

        //seo 标题/描述/关键字
        $content ="";
        $i = 0;
        $only = '';
        foreach ($info["params"] as $key => $value) {
            if(!empty($value["name"])){
                $i++;
                $only =  $value["name"];
                $content .= $value["name"];
            }
        }

        $s_type = array_filter($info['navParams']);
        if($i == 1 && count($s_type) == 1){
            foreach ($s_type as $key => $value) {
                $s_type = $key['0'];
            }
            $tdk = D('Meitu')->getMeituListDescription($only,$s_type);
            if(!empty($tdk['title'])){
                $basic["head"]["title"] = $tdk['title'];
            }else{
                $basic["head"]["title"] = $only . '装修效果图_'. $only .'设计_'. $only .'图片 - 齐装网装修效果图';
            }
            if(!empty($tdk['keywords'])){
                $basic["head"]["keywords"] = $tdk['keywords'];
            }else{
                $basic["head"]["keywords"] = $only . '装修效果图，'. $only .'设计效果图，'. $only .'图片';
            }
            if(!empty($tdk['description'])){
                $basic["head"]["description"] = $tdk['description'];
            }else{
                $basic["head"]["description"] = "齐装网汇聚".date("Y").$content."家庭室内装修装饰风格效果图大全，为您提供".date("Y").$content."效果图大全以及丰富的家居设计美图。找".$content."美图就上齐装网！";
            }
        }else{
            $basic["head"]["keywords"] = $content."装修效果图,".$content."装修效果图大全,".$content."家庭装修效果图,".$content."室内装修效果图,".$content."装饰效果图,";
            $basic["head"]["description"] = "齐装网汇聚".date("Y").$content."家庭室内装修装饰风格效果图大全，为您提供".date("Y")."全新".$content."效果图大全以及丰富的家居设计美图。找".$content."美图就上齐装网！";
            $basic["head"]["title"] = date("Y").$content."家庭室内装修装饰风格美图大全-齐装网装修效果图";
        }

        if(!empty($keyword)){
            $this->assign("keyword",$keyword);
            $basic["head"]["title"] = date("Y").' '.$keyword." 相关装修美图大全-齐装网装修效果图";
        }

        $basic["body"]["title"] = '家装美图';

        $info["params"] = array_filter($info["params"]);
        $info['pageid'] = empty($info['navParams']['p']) ? $pageIndex : $info['navParams']['p'];

        //不限URL替换
        $url = $_SERVER['REQUEST_URI'];
        if ('meitu/list' != trim($url, '/')) {
            $info['select']['location'] = str_replace(
                array('a1='.$info['navParams']['location'],'l'.$info['navParams']['location']),
                array('a1=0','l0'),
                $url
            );
            $info['select']['fengge'] = str_replace(
                array('a2='.$info['navParams']['fengge'],'f'.$info['navParams']['fengge']),
                array('a2=0','f0'),
                $url
            );
            $info['select']['huxing'] = str_replace(
                array('a3='.$info['navParams']['huxing'],'h'.$info['navParams']['huxing']),
                array('a3=0','h0'),
                $url
            );
            $info['select']['color'] = str_replace(
                array('a4='.$info['navParams']['color'],'c'.$info['navParams']['color']),
                array('a4=0','c0'),
                $url
            );
        }
        foreach ($info['select'] as $key => $value) {
            if ('meitu/list-l0f0h0c0' == trim($value, '/')) {
                $info['select'][$key] = '/meitu/list/';
            }
        }

        //处理当前选中项
        if(!empty($meitu["params"]['location'])){
            foreach ($info["wz"] as $key => $value) {
                if($meitu["params"]['location'] == $value['id']){
                    $info['nav']['wz'] = $value['name'];
                }
            }
        }
        //风格
        if(!empty($meitu["params"]['fengge'])){
            foreach ($info["fg"] as $key => $value) {
                if($meitu["params"]['fengge'] == $value['id']){
                    $info['nav']['fg'] = $value['name'];
                }
            }
        }
        //户型
        if(!empty($meitu["params"]['huxing'])){
            foreach ($info["hx"] as $key => $value) {
                if($meitu["params"]['huxing'] == $value['id']){
                    $info['nav']['hx'] = $value['name'];
                }
            }
        }
        if(!empty($meitu["params"]['color'])){
            foreach ($info["ys"] as $key => $value) {
                if($meitu["params"]['color'] == $value['id']){
                    $info['nav']['ys'] = $value['name'];
                }
            }
        }

        /*由于此处没有分页，该属性可以简单如下这样处理，生成canonical标签属性值*/
        if(!isset($_GET['a1'])){
            $position = strpos($_SERVER['REQUEST_URI'], '?');
            if($position > 0){
                $info['canonical'] = $this->SCHEME_HOST['scheme_full'].'meitu.'.C('QZ_YUMING').substr($_SERVER['REQUEST_URI'], 0, $position);
            }else{
                $info['canonical'] = $this->SCHEME_HOST['scheme_full'].'meitu.'.C('QZ_YUMING').$_SERVER['REQUEST_URI'];
            }
            $info['canonical'] = str_replace('meitu/','',$info['canonical']);
        }

        //生成没有分页的当前请求的链接，用于ajax请求
        $url_no_page = '/meitu/list-l' . $info['navParams']['location'] . 'f' . $info['navParams']['fengge'] . 'h' . $info['navParams']['huxing'] . 'c' . $info['navParams']['color'];

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign('source',320);//设置发单入口标识
        $this->assign('totalpage',$totalpage);
        $this->assign("info",$info);
        $this->assign("basic",$basic);
        $this->assign("keyword", $keyword);
        $this->assign("url_no_page", $url_no_page);
        $this->assign("redPacket",array('source' => 315));
        $this->display("list");
    }


    //装修攻略查看更多免费设计（新的美图列表页）
    public function glMeituList(){

        //个人处理方案
        if ($_SERVER['REQUEST_URI'] == '/gonglue/sheji/') {
            $_SERVER['REQUEST_URI'] = '/gonglue/sheji/list-l0f0h0c0/';
        }
        //获取美图列表
        $pageIndex = 1;
        $pageCount = 40;
        $keyword = I('get.keyword');
        //去除空格
        $keyword = str_replace(" ", "", $keyword);
        unset($_GET["keyword"]);
        $meitu = $this->getMeiTuList($pageIndex, $pageCount, $keyword,'sheji');
        $info["meitu"] = $meitu["list"];
        $info["page"] = $meitu["page"];
        $this->assign('search_keyword', $keyword);


        $items = $meitu["count"];
        $totalpage = ceil($items / $pageCount);
        foreach ($meitu["params"] as $key => $value) {
            if ($value > 0) {
                $count++;
                $params[] = $key;
            }
        }
        $params_count = count($params);
        //如果是1个参数
        if (count($params) == 1) {
            $type = $params[0];
        }

        //获取推荐局部信息
        $location = D("Meitu")->getLocation();
        $info["wz"] = $this->getStaticNavUrl($location, 'l', $meitu['url'],'sheji');

        //获取风格
        $fengge = D("Meitu")->getFengge();
        $info["fg"] = $this->getStaticNavUrl($fengge, 'f', $meitu['url'],'sheji');
        //获取户型
        $huxing = D("Meitu")->getHuxing();
        $info["hx"] = $this->getStaticNavUrl($huxing, 'h', $meitu['url'],'sheji');
        //获取颜色信息
        $color = D("Meitu")->getColor();
        $info["ys"] = $this->getStaticNavUrl($color, 'c', $meitu['url'],'sheji');
        foreach ($info["ys"] as $key => $value) {
            if ($value['color'] == '#FFF') {
                $info["ys"][$key]['style'] = 'background: #fff;border:1px solid #ccc';
            } else if (empty($value['color'])){
                $info["ys"][$key]['style'] = 'background:url(/assets/mobile/meitu/img/colorful.png)no-repeat 0 0px;padding:1px;';
            } else {
                $info["ys"][$key]['style'] = 'background: '. $value['color'] .';border:1px solid '. $value['color'] .';';
            }
        }

        //动态生成绑定参数
        foreach ($meitu["params"] as $key => $value) {
            switch ($key) {
                case 'location':
                    $sub = $this->getParams($key, $value, $count, $meitu["url"], $info["wz"]);
                    break;
                case 'fengge':
                    $sub = $this->getParams($key, $value, $count, $meitu["url"], $info["fg"]);
                    break;
                case 'huxing':
                    $sub = $this->getParams($key, $value, $count, $meitu["url"], $info["hx"]);
                    break;
                case 'color':
                    $sub = $this->getParams($key, $value, $count, $meitu["url"], $info["ys"]);
                    break;
            }
            $info["params"][] = $sub;
            $info["navParams"][$key] = $value;
        }

        //获取推荐数据
        if (empty($info["meitu"])) {
            $info["recommend"] = S('m:meitu:list:recommend');
            if (empty($info["recommend"])) {
                $info["recommend"] = D("Meitu")->getMeiTuList(0, 3, "", "", "", "", '', '99', 'likes DESC, id DESC');
                foreach ($info["recommend"] as $key => $value) {
                    //取每个分类的第一个元素
                    $exp = array_filter(explode(",", $value["wz"]));
                    $info["recommend"][$key]["wz"] = $exp[0];
                    $exp = array_filter(explode(",", $value["fg"]));
                    $info["recommend"][$key]["fg"] = $exp[0];
                    $exp = array_filter(explode(",", $value["hx"]));
                    $info["recommend"][$key]["hx"] = $exp[0];
                    $exp = array_filter(explode(",", $value["ys"]));
                    $info["recommend"][$key]["ys"] = $exp[0];
                }
                S('m:meitu:list:recommend', $info["recommend"], 900);
            }
        }

        //seo 标题/描述/关键字
        $content = "";
        $i = 0;
        $only = '';
        foreach ($info["params"] as $key => $value) {
            if (!empty($value["name"])) {
                $i++;
                $only = $value["name"];
                $content .= $value["name"];
            }
        }

        $s_type = array_filter($info['navParams']);
        //以后可能用到
//        if ($i == 1 && count($s_type) == 1) {
//            foreach ($s_type as $key => $value) {
//                $s_type = $key['0'];
//            }
//            $tdk = D('Meitu')->getMeituListDescription($only, $s_type);
//            if (!empty($tdk['title'])) {
//                $basic["head"]["title"] = $tdk['title'];
//            } else {
//                $basic["head"]["title"] = $only . '装修效果图_' . $only . '设计_' . $only . '图片 - 齐装网装修效果图';
//            }
//            if (!empty($tdk['keywords'])) {
//                $basic["head"]["keywords"] = $tdk['keywords'];
//            } else {
//                $basic["head"]["keywords"] = $only . '装修效果图，' . $only . '设计效果图，' . $only . '图片';
//            }
//            if (!empty($tdk['description'])) {
//                $basic["head"]["description"] = $tdk['description'];
//            } else {
//                $basic["head"]["description"] = "齐装网汇聚" . date("Y") . $content . "家庭室内装修装饰风格效果图大全，为您提供" . date("Y") . $content . "效果图大全以及丰富的家居设计美图。找" . $content . "美图就上齐装网！";
//            }
//        } else {
//            $basic["head"]["keywords"] = $content . "装修效果图," . $content . "装修效果图大全," . $content . "家庭装修效果图," . $content . "室内装修效果图," . $content . "装饰效果图,";
//            $basic["head"]["description"] = "齐装网汇聚" . date("Y") . $content . "家庭室内装修装饰风格效果图大全，为您提供2015新" . $content . "效果图大全以及最富的家居设计美图。找" . $content . "美图就上齐装网！";
//            $basic["head"]["title"] = date("Y") . $content . "家庭室内装修装饰风格美图大全-齐装网装修效果图";
//        }
        //设置tdk
        $basic["head"]["title"] = '免费装修设计_2018网上免费家装设计效果图-齐装网';

        if (!empty($keyword)) {
            $this->assign("keyword", $keyword);
            $basic["head"]["title"] = date("Y") . ' ' . $keyword . " 相关装修美图大全-齐装网装修效果图";
        }

        $basic["body"]["title"] = '家装美图';

        $info["params"] = array_filter($info["params"]);
        $info['pageid'] = empty($info['navParams']['p']) ? $pageIndex : $info['navParams']['p'];

        //不限URL替换
        $url = $_SERVER['REQUEST_URI'];
        if ('meitu/list' != trim($url, '/')) {
            $info['select']['location'] = str_replace(
                array('a1=' . $info['navParams']['location'], 'l' . $info['navParams']['location']),
                array('a1=0', 'l0'),
                $url
            );
            $info['select']['fengge'] = str_replace(
                array('a2=' . $info['navParams']['fengge'], 'f' . $info['navParams']['fengge']),
                array('a2=0', 'f0'),
                $url
            );
            $info['select']['huxing'] = str_replace(
                array('a3=' . $info['navParams']['huxing'], 'h' . $info['navParams']['huxing']),
                array('a3=0', 'h0'),
                $url
            );
            $info['select']['color'] = str_replace(
                array('a4=' . $info['navParams']['color'], 'c' . $info['navParams']['color']),
                array('a4=0', 'c0'),
                $url
            );
        }
        foreach ($info['select'] as $key => $value) {
            if ('meitu/list-l0f0h0c0' == trim($value, '/')) {
                $info['select'][$key] = '/meitu/list/';
            }
        }

        //处理当前选中项
        if (!empty($meitu["params"]['location'])) {
            foreach ($info["wz"] as $key => $value) {
                if ($meitu["params"]['location'] == $value['id']) {
                    $info['nav']['wz'] = $value['name'];
                }
            }
        }
        //风格
        if (!empty($meitu["params"]['fengge'])) {
            foreach ($info["fg"] as $key => $value) {
                if ($meitu["params"]['fengge'] == $value['id']) {
                    $info['nav']['fg'] = $value['name'];
                }
            }
        }
        //户型
        if (!empty($meitu["params"]['huxing'])) {
            foreach ($info["hx"] as $key => $value) {
                if ($meitu["params"]['huxing'] == $value['id']) {
                    $info['nav']['hx'] = $value['name'];
                }
            }
        }

        if (!empty($meitu["params"]['color'])) {
            foreach ($info["ys"] as $key => $value) {
                if ($meitu["params"]['color'] == $value['id']) {
                    $info['nav']['ys'] = $value['name'];
                }
            }
        }

        /*由于此处没有分页，该属性可以简单如下这样处理，生成canonical标签属性值*/
        if (!isset($_GET['a1'])) {
            $position = strpos($_SERVER['REQUEST_URI'], '?');
            if ($position > 0) {
                $info['canonical'] = $this->SCHEME_HOST['scheme_full'].'meitu.' . C('QZ_YUMING') . substr($_SERVER['REQUEST_URI'], 0, $position);
            } else {
                $info['canonical'] = $this->SCHEME_HOST['scheme_full'].'meitu.' . C('QZ_YUMING') . $_SERVER['REQUEST_URI'];
            }
            $info['canonical'] = str_replace('meitu/', '', $info['canonical']);
        }

        //生成没有分页的当前请求的链接，用于ajax请求
        $url_no_page = '/gonglue/sheji/list-l' . $info['navParams']['location'] . 'f' . $info['navParams']['fengge'] . 'h' . $info['navParams']['huxing'] . 'c' . $info['navParams']['color'];

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign('source', 320);//设置发单入口标识
        $this->assign('totalpage', $totalpage);
        $this->assign("info", $info);
        $this->assign("basic", $basic);
        $this->assign("keyword", $keyword);
        $this->assign("url_no_page", $url_no_page);
        $this->assign("redPacket", array('source' => 315));


        $this->display("free_list");
    }

    /**
     * 获取导航链接
     * @param array $datas
     * @param string $type
     * @param array $urls
     * @return array
     */

    public function getStaticNavUrl($datas = array(), $type = '', $urls = array())
    {
        if ('meitu/list' == trim($urls['short_url'], '/')) {
            $urls['short_url'] = '/meitu/list-l0f0h0c0/';
        }
        //参数替换
        $pattern = '/' . $type . '\d+/i';
        foreach ($datas as $key => $value) {
            $datas[$key]["link"] = preg_replace($pattern, $type . $value["id"], $urls['short_url']);
            $datas[$key]["nofollow"] = 'follow';
        }
        return $datas;
    }

    //美图详情页
    public function show(){
        $p = intval(I("get.p"));

        //判断来源
        $referer = parse_url($_SERVER['HTTP_REFERER'])['path'];
        $params = array();
        if (!empty($referer)) {
            $match = array();
            if (1 === preg_match('/meitu\/list-(l([\d+]+)f([\d+]+)h([\d+]+)c([\d+]+)(p[\d+]+)?+(q[\d+]+)?)$/', $referer, $match)) {
                $params = array(
                    'location' => intval($match['2']),
                    'fengge' => intval($match['3']),
                    'huxing' => intval($match['4']),
                    'color' => intval($match['5']),
                    'tags' => 0
                );
            } else if (1 === preg_match('/meitu\/p(\d+)\.html/', $referer, $match)) {
                $temp = json_decode(cookie('meitu_terminal_params_mobile'));
                if (array_sum($temp) > 0) {
                    $params = array(
                        'location' => intval($temp['0']),
                        'fengge' => intval($temp['1']),
                        'huxing' => intval($temp['2']),
                        'color' => intval($temp['3']),
                        'tags' => intval($temp['4'])
                    );
                }
            }
        }

        cookie('meitu_terminal_params_mobile', null);

        //美图缓存
        $cacheKey = $p . md5(serialize($params));
        $info = S("Cache:Mobile:Meitu:show:info:". $cacheKey);
        if(empty($info)){
            //查询美图信息
            $map = array(
                'id' => intval($p)
            );
            $info['now'] = $this->getMeituInfoByMap($map, $params);
            if (empty($info['now'])) {
                $this->_error();
            }
            //获取上一个美图
            $map = array(
                'id' => array('GT', $p)
            );
            $info['prv'] = $this->getMeituInfoByMap($map, $params, 'asc');
            if (empty($info['prv'])) {
                $map = array(
                    'id' => array('GT', 0)
                );
                $info['prv'] = $this->getMeituInfoByMap($map, $params, 'asc');
            }
            //获取下一个美图
            $map = array(
                'id' => array('LT', $p)
            );
            $info['next'] = $this->getMeituInfoByMap($map, $params, 'desc');
            if (empty($info['next'])) {
                $map = array(
                    'id' => array('GT', 0)
                );
                $info['next'] = $this->getMeituInfoByMap($map, $params, 'desc');
            }
            S("Cache:Mobile:Meitu:show:info:". $cacheKey, $info, 300);
        }
        //熊掌号
        $baidu['optime'] = date("Y-m-d",$info['now']['time'])."T".date("H:i:s",$info['now']['time']);
        $this->assign('baidu',$baidu);

        //seo 标题/描述/关键字
        $basic["head"]["title"] = $info["now"]["title"]."-齐装网装修效果图";
        $basic["head"]["keywords"] = $info["now"]["keyword"];
        $basic["head"]["description"] = $info["now"]["description"];
        $basic["body"]["title"] = '装修美图';
        $this->assign("info",$info);
        $this->assign('basic',$basic);
        $this->assign("params", json_encode(array_values($params)));
        $this->display('show');
    }

    //计算星星
    private function getStar($list)
    {
        foreach ($list as $key => $value) {
            if (empty($value['logo'])) {
                $list[$key]["logo"] = $this->SCHEME_HOST['scheme_full']. C('QINIU_DOMAIN') . '/' . OP('DEFAULT_LOGO');
            }
            if ($value["comment_score"] >= 9) {
                $list[$key]["star"] = 5;
            } elseif ($value["comment_score"] >= 8 && $value["comment_score"] < 9) {
                $list[$key]["star"] = 4;
            } elseif ($value["comment_score"] >= 6 && $value["comment_score"] < 8) {
                $list[$key]["star"] = 3;
            } elseif ($value["comment_score"] >= 4 && $value["comment_score"] < 6) {
                $list[$key]["star"] = 2;
            } else {
                $list[$key]["star"] = 1;
            }
        }
        return $list;
    }

    //美图专题列表页
    public function meituztlist()
    {
        $this->_error();
        die();
        $pageCount = 10;
        $pageIndex = I('get.p');
        $pageIndex = empty($pageIndex) ? 1 : $pageIndex;
        $map['status'] = 1;

        //获取结果
        $result = D("Special")->getList($map, ($pageIndex - 1) * $pageCount, $pageCount);

        //标题截取十个字符的长度
        $result_new = $result['result'];
        foreach ($result_new as $key => $value) {
            $result_new[$key]['title'] = mbstr($value['title'], 0, 10, 'utf-8', false);
        }
        $result['result'] = $result_new;

        //判断是否为 ajax 请求
        if (IS_AJAX) {
            if (!empty($result['result'])) {
                $this->ajaxReturn(array('status' => 1, 'data' => $result['result']));
            }
            $this->ajaxReturn(array('status' => 0));
            exit();
        }

        import('Library.Org.Page.SPage');
        $page = new \SPage($result['count'], $pageCount, array(
            'templet' => '/zt-p[PAGE].html',
            'firstUrl' => '/zt/'
        ));
        $pageTmp = $page->show();

        $banner = D('Special')->getBannerList(array('status' => '1','type'=>2));
        $banner = multi_array_sort($banner, 'order_id',SORT_DESC);
        //banner图中的url跳转pc端替换成跳转移动端
        foreach ($banner as $key => $value) {
            $url = explode('/', $value['url']);
            $banner[$key]['url'] = $this->SCHEME_HOST['scheme_host']."/zt/" . $url[count($url) - 1];
        }

        //关键字、描述、标题
        $basic["head"]["title"] = "美图专题";
        $basic["head"]["keywords"] = "美图 专题";
        $basic["head"]["description"] = "美图专题";
        $this->assign("basic",$basic);

        $this->assign('banner', $banner);
        $this->assign("list", $result['result']);
        $this->assign('page', $page->show());
        $this->display();
    }


    //美图专题详情页
    public function meituztdetails()
    {
        $id = I('get.id');
        if (empty($id)) {
            $this->_error('数据错误！');
        }

        $info = D('Special')->getSpecial($id);

        $result = D('Special')->getSpecialItem(array('zid' => $id));
        foreach ($result as $key => $value) {
            $itemList[$value['type']][] = $value['item_id'];
        }

        //获取美图列表
        if (!empty($itemList['1'])) {
            $this->assign('meituList', D('Special')->getMeituList($itemList['1']));
        }

        //获取案例列表
        if (!empty($itemList['2'])) {
            $caseList = D('Special')->getCaseList($itemList['2']);
            $this->assign('caseList', $caseList);
        }

        //获取攻略列表
        if (!empty($itemList['3'])) {
            $articleList = D('Special')->getArticleList($itemList['3']);
            if (!empty($articleList)) {
                foreach ($articleList as $key => $value) {
                    $articleList[$key]['content'] = $this->getArticle($value['id'], $value['shortname']);
                }
            }
            $this->assign('articleList', $articleList);
        }
        $basic["head"]["title"] = "专题详情";
        $basic["head"]["keywords"] = "专题详情";
        $basic["head"]["description"] = "专题详情";
        $this->assign("basic",$basic);

        //熊掌号
        $baidu['optime'] = date("Y-m-d",$info['time'])."T".date("H:i:s",$info['time']);
        $this->assign('baidu',$baidu);

        $this->assign('info', $info);
        $this->display();
    }


    //移动端装修未来-开启装修
    public function zxfeature()
    {
        $this->display();
    }


    //获取文章详情页
    public function getArticle($id, $category)
    {

        //获取分类
        if (!empty($category) && strtolower($category) != "history") {
            //新分类
            $cate = D("WwwArticleClass")->getArticleClassByShortname($category);
        } else {
            //老版文章
            //获取根据文章的编号获取老版的分类
            $cate = D("WwwArticleClass")->getArticleClassByArticleId($id, 'old');
        }
        if (empty($cate)) {
            $this->_error();
            die();
        }

        $articleInfo = S('Cache:articleInfo:' . $category . ':' . $id);
        if (!$articleInfo) {
            //文章内容
            $article = $this->getArticleInfo($id, $cate["id"]);
            $articleInfo["article"] = $article;
            S('Cache:articleInfo:' . $category . ':' . $id, $articleInfo, 900);
        }

        if (empty($articleInfo['article']['now']['id'])) {
            $this->_error();
            die();
        }
        //去除换行和空格
        $content_now = str_replace(array("\r\n", "\r", "\n", "&nbsp;", " ", "\t", "  "), '', strip_tags($articleInfo['article']['now']['content']));
        return $content_now;

    }


    //移动端装修未来-选择
    public function zxfeaturexuanz()
    {
        $this->display();
    }


    /**
     * 通过条件获取单个美图
     * @param  array $map 条件
     * @param  array $params 参数
     * @param  string $order 排序
     * @return
     */
    private function getMeituInfoByMap($map, $params, $order = 'asc')
    {
        $temp = D("Meitu")->getMeituInfoByMap($map, $params, $order);
        $result = array();
        foreach ($temp as $key => $value) {
            if (!isset($result['id'])) {
                $result = $value;
            }
            $result["child"][] = $value;
            $result["count"]++;
        }
        return $result;
    }

    /**
     * 点赞喜欢
     */
    public function like()
    {
        $id = I('post.id');
        if (empty($id) || !is_numeric($id)) {
            $this->ajaxReturn(array("data" => "", "info" => "数据错误", "status" => 0));
        } else {
            //喜欢数+1
            M("meitu")->where(array('id' => $id))->setInc('likes');
            $this->ajaxReturn(array("data" => "", "info" => "成功", "status" => 1));
        }
    }

    private function getMeituListByPart($type, $limit)
    {
        $imgs = D("Meitu")->getMeituListByPart($type, $limit);
        foreach ($imgs as $key => $value) {
            //取每个分类的第一个元素
            $exp = array_filter(explode(",", $value["wz"]));
            $imgs[$key]["wz"] = $exp[0];

            $exp = array_filter(explode(",", $value["fg"]));
            $imgs[$key]["fg"] = $exp[0];

            $exp = array_filter(explode(",", $value["hx"]));
            $imgs[$key]["hx"] = $exp[0];

            $exp = array_filter(explode(",", $value["ys"]));
            $imgs[$key]["ys"] = $exp[0];
        }
        return $imgs;
    }


    private function getMeituInfo($id)
    {
        $meitu = D("Meitu")->getMeituInfo($id);
        foreach ($meitu as $key => $value) {
            if (!array_key_exists($value["action"], $meitu)) {
                $meitu[$value["action"]] = $value;
            }
            $meitu[$value["action"]]["child"][] = $value;
            $meitu[$value["action"]]["count"]++;
            unset($meitu[$key]);
        }
        return $meitu;
    }

    private function getParams($type, $value, $count, $url, $data,$leixin='')
    {
        foreach ($data as $k => $val) {
            if ($value == $val["id"]) {
                $sub = array(
                    "name" => $val["name"]
                );
                if ($count == 1) {
                    if($leixin == 'sheji '){
                        $sub["link"] = "/gonglue/sheji";
                    }else{
                        $sub["link"] = "/meitu/list";
                    }
                } else {
                    switch ($type) {
                        case 'location':
                            //替换当前的参数
                            $reg = '/a1=\d+/i';
                            preg_match($reg, $url["url"], $m);
                            $link = preg_replace($reg, "a1=0", $url["url"]);
                            break;
                        case 'fengge':
                            //替换当前的参数
                            $reg = '/a2=\d+/i';
                            preg_match($reg, $url["url"], $m);
                            $link = preg_replace($reg, "a2=0", $url["url"]);
                            break;
                        case 'huxing':
                            //替换当前的参数
                            $reg = '/a3=\d+/i';
                            preg_match($reg, $url["url"], $m);
                            $link = preg_replace($reg, "a3=0", $url["url"]);
                            break;
                        case 'color':
                            //替换当前的参数
                            $reg = '/a4=\d+/i';
                            preg_match($reg, $url["url"], $m);
                            $link = preg_replace($reg, "a4=0", $url["url"]);
                            break;

                    }
                    $sub["link"] = $link;
                }
                break;
            }
        }
        return $sub;
    }

    private function getLocation($limit, $isTop, $type, $params_count, $url)
    {
        $result = D("Meitu")->getLocation($limit, $isTop);
        foreach ($result as $key => $value) {
            if (empty($type) && $params_count == 0) {
                $link = "/meitu/list-l" . $value["id"] . "f0h0c0";
                $result[$key]["nofollow"] = 'follow';
            } elseif (!empty($type) && $params_count == 1) {
                if ($type == "location") {
                    //替换当前的参数
                    $reg = '/l\d+/i';
                    $link = preg_replace($reg, "l" . $value["id"], $url["short_url"]);
                    $result[$key]["nofollow"] = 'follow';
                } else {
                    //替换当前的参数
                    $reg = '/a1=\d+/i';
                    preg_match($reg, $url["url"], $m);
                    $link = preg_replace($reg, "a1=" . $value["id"], $url["url"]);
                }
            } else {
                //替换当前的参数
                $reg = '/a1=\d+/i';
                $link = preg_replace($reg, "a1=" . $value["id"], $url["url"]);
            }
            $result[$key]["link"] = $link;
        }
        return $result;
    }

    private function getFengge($limit, $isTop, $type, $params_count, $url)
    {
        $result = D("Meitu")->getFengge($limit, $isTop);
        foreach ($result as $key => $value) {
            if (empty($type) && $params_count == 0) {
                $link = "/meitu/list-l0f" . $value["id"] . "h0c0";
                $result[$key]["nofollow"] = 'follow';
            } elseif (!empty($type) && $params_count == 1) {
                if ($type == "fengge") {
                    //替换当前的参数
                    $reg = '/f\d+/i';
                    $link = preg_replace($reg, "f" . $value["id"], $url["short_url"]);
                    $result[$key]["nofollow"] = 'follow';
                } else {
                    //替换当前的参数
                    $reg = '/a2=\d+/i';
                    preg_match($reg, $url["url"], $m);
                    $link = preg_replace($reg, "a2=" . $value["id"], $url["url"]);
                }
            } else {
                //替换当前的参数
                $reg = '/a2=\d+/i';
                $link = preg_replace($reg, "a2=" . $value["id"], $url["url"]);
            }
            $result[$key]["link"] = $link;
        }
        return $result;
    }

    private function getHuxing($limit, $isTop, $type, $params_count, $url)
    {
        $result = D("Meitu")->getHuxing($limit, $isTop);
        foreach ($result as $key => $value) {
            if (empty($type) && $params_count == 0) {
                $link = "/meitu/list-l0f0h" . $value["id"] . "c0";
                $result[$key]["nofollow"] = 'follow';
            } elseif (!empty($type) && $params_count == 1) {
                if ($type == "huxing") {
                    //替换当前的参数
                    $reg = '/h\d+/i';
                    $link = preg_replace($reg, "h" . $value["id"], $url["short_url"]);
                    $result[$key]["nofollow"] = 'follow';
                } else {
                    //替换当前的参数
                    $reg = '/a3=\d+/i';
                    preg_match($reg, $url["url"], $m);
                    $link = preg_replace($reg, "a3=" . $value["id"], $url["url"]);
                }
            } else {
                //替换当前的参数
                $reg = '/a3=\d+/i';
                $link = preg_replace($reg, "a3=" . $value["id"], $url["url"]);
            }
            $result[$key]["link"] = $link;
        }
        return $result;
    }

    private function getColor($limit, $isTop, $type, $params_count, $url)
    {
        $result = D("Meitu")->getColor($limit, $isTop);
        foreach ($result as $key => $value) {
            if (empty($type) && $params_count == 0) {
                $link = "/meitu/list-l0f0h0c" . $value["id"];
                $result[$key]["nofollow"] = 'follow';
            } elseif (!empty($type) && $params_count == 1) {
                if ($type == "color") {
                    //替换当前的参数
                    $reg = '/c\d+/i';
                    $link = preg_replace($reg, "c" . $value["id"], $url["short_url"]);
                    $result[$key]["nofollow"] = 'follow';
                } else {
                    //替换当前的参数
                    $reg = '/a4=\d+/i';
                    preg_match($reg, $url["url"], $m);
                    $link = preg_replace($reg, "a4=" . $value["id"], $url["url"]);
                }
            } else {
                //替换当前的参数
                $reg = '/a4=\d+/i';
                $link = preg_replace($reg, "a4=" . $value["id"], $url["url"]);
            }
            $result[$key]["link"] = $link;
        }
        return $result;
    }

    /**
     * @param $pageIndex
     * @param $pageCount
     * @param $keyword
     * @param string $type 默认美图，为sheji时处理 装修攻略免费设计模块
     * @return array
     */
    private function getMeiTuList($pageIndex,$pageCount,$keyword,$type=''){
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
        import('Library.Org.Page.Page');
        $page = new \Page();
        //解析参数
        $params = $page->analyticalAddress($type);
        //判断是否有分页
        $pageIndex = !empty($params['params']['p'])?$params['params']['p']:$pageIndex;

        //过滤无效分类参数
        if($type == 'sheji'){
            $url = str_replace('/gonglue/sheji/list-','',parse_url($_SERVER["REQUEST_URI"])['path']);
            preg_match_all('/[a-z]+/', $url, $matches);
            $str = array('l','f','h','c','p','a');
            //此处添加攻略美图列表页逻辑
            if(!$keyword && (strpos(parse_url($_SERVER["REQUEST_URI"])['path'],'/gonglue/sheji/list-') != 0)){
                foreach ($matches['0'] as $key => $value) {
                    if(!in_array($value,$str)){
                        $this->_error();
                    }
                }
                //过滤无效分类值
                if(!empty($params['params']['location'])){
                    $location = D("Meitu")->getLocation();
                    foreach ($location as $key => $value) {
                        $locationIds[$value['id']] = $value['name'];
                    }
                    if(empty($locationIds[$params['params']['location']])){
                        $this->_error();
                    }
                }
                if(!empty($params['params']['fengge'])){
                    $fengge = D("Meitu")->getFengge();
                    foreach ($fengge as $key => $value) {
                        $fenggeIds[$value['id']] = $value['name'];
                    }
                    if(empty($fenggeIds[$params['params']['fengge']])){
                        $this->_error();
                    }
                }
                if(!empty($params['params']['huxing'])){
                    $huxing = D("Meitu")->getHuxing();
                    foreach ($huxing as $key => $value) {
                        $huxingIds[$value['id']] = $value['name'];
                    }
                    if(empty($huxingIds[$params['params']['huxing']])){
                        $this->_error();
                    }
                }
                if(!empty($params['params']['color'])){
                    $color = D("Meitu")->getColor();
                    foreach ($color as $key => $value) {
                        $colorIds[$value['id']] = $value['name'];
                    }
                    if(empty($colorIds[$params['params']['color']])){
                        $this->_error();
                    }
                }
            }
        }else{
            $url = str_replace('/meitu/list-','',parse_url($_SERVER["REQUEST_URI"])['path']);
            preg_match_all('/[a-z]+/', $url, $matches);
            $str = array('l','f','h','c','p','a');
            if(!$keyword && (strpos(parse_url($_SERVER["REQUEST_URI"])['path'],'/meitu/list-') != 0)){
                foreach ($matches['0'] as $key => $value) {
                    if(!in_array($value,$str)){
                        $this->_error();
                    }
                }
                //过滤无效分类值
                if(!empty($params['params']['location'])){
                    $location = D("Meitu")->getLocation();
                    foreach ($location as $key => $value) {
                        $locationIds[$value['id']] = $value['name'];
                    }
                    if(empty($locationIds[$params['params']['location']])){
                        $this->_error();
                    }
                }
                if(!empty($params['params']['fengge'])){
                    $fengge = D("Meitu")->getFengge();
                    foreach ($fengge as $key => $value) {
                        $fenggeIds[$value['id']] = $value['name'];
                    }
                    if(empty($fenggeIds[$params['params']['fengge']])){
                        $this->_error();
                    }
                }
                if(!empty($params['params']['huxing'])){
                    $huxing = D("Meitu")->getHuxing();
                    foreach ($huxing as $key => $value) {
                        $huxingIds[$value['id']] = $value['name'];
                    }
                    if(empty($huxingIds[$params['params']['huxing']])){
                        $this->_error();
                    }
                }
                if(!empty($params['params']['color'])){
                    $color = D("Meitu")->getColor();
                    foreach ($color as $key => $value) {
                        $colorIds[$value['id']] = $value['name'];
                    }
                    if(empty($colorIds[$params['params']['color']])){
                        $this->_error();
                    }
                }
            }
        }




        $count = D("Meitu")->getMeiTuListCount($params["params"]["location"],$params["params"]["fengge"],$params["params"]["huxing"],$params["params"]["color"],$keyword,99);
        if($count > 0){
            //自定义配置项
            $config  = array("prev","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config);
            $result =  $page->show_short($params["url"],$count);
            $pageTmp = $result;
            $list = D("Meitu")->getMeiTuList(($page->pageIndex-1)*$pageCount,$pageCount,$params["params"]["location"],$params["params"]["fengge"],$params["params"]["huxing"],$params["params"]["color"],$keyword,99);
            //dump(M()->getLastSql());
            foreach ($list as $key => $value) {
                //取每个分类的第一个元素
                $exp =array_filter(explode(",", $value["wz"]));
                $list[$key]["wz"] = $exp[0];
                $exp =array_filter(explode(",", $value["fg"]));
                $list[$key]["fg"] = $exp[0];
                $exp =array_filter(explode(",", $value["hx"]));
                $list[$key]["hx"] = $exp[0];
                $exp =array_filter(explode(",", $value["ys"]));
                $list[$key]["ys"] = $exp[0];
            }
        }
        $pageList = $page->showMeitu();
        return array("list"=>$list,"page"=>$pageTmp,"params"=>$params["params"],"url"=>$params["url"],'count'=>$count,'page'=>$pageList);
    }



    /**
     * [getHotMeiTuList 获取美图列表]
     * @param  integer $each [每页显示数目]
     * @param  [type]  $keyword [搜索关键字]
     * @param  [type]  $multi   [单图还是套图]
     * @return [type]           [description]
     */
    private function getHotMeiTuList($pageIndex, $pageCount, $keyword, $multi)
    {
        if ($multi == true) {
            $pageTemp = 'p';
            $isSingle = '0';
        } else {
            $pageTemp = 'p';
            $isSingle = '1';
        }
        import('Library.Org.Page.ShortPage');

        /*$orderby = cookie('meitu_orderby');
        if($orderby == 'hot'){
            $order = '`likes` desc';
        }*/

        //根据PV倒序
        $order = '`likes` desc';

        //获取单图的分页
        if (!isset($_GET['a1'])) {
            $options = array(
                'prefix' => '/list-',
                'dynamic' => '/list/',
                'short' => array('l' => 'a1', 'f' => 'a2', 'h' => 'a3', 'c' => 'a4'),
                'sort' => array('l', 'f', 'h', 'c', $pageTemp)
            );
        }
        $Page = new \Page($pageCount, $options, $sline = false, $dline = true, $p = $pageTemp);

        $Page->setConfig('theme', '%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
        $result = $Page->analyse();

        if (array_key_exists('a1', $result['param'])) {
            $params['l'] = $result['param']['a1'];
            $params['f'] = $result['param']['a2'];
            $params['h'] = $result['param']['a3'];
            $params['c'] = $result['param']['a4'];
        } else {
            $params = $result['param'];
        }
        $count = D("Meitu")->getMeiTuListCount($params["l"], $params["f"], $params["h"], $params["c"], $keyword, $isSingle);
        if ($count > 0) {
            $show = $Page->show($count);
            $list = D("Meitu")->getMeiTuList(($pageIndex - 1) * $pageCount, $pageCount, $params["l"], $params["f"], $params["h"], $params["c"], $keyword, $isSingle, $order);

            foreach ($list as $key => $value) {
                //取每个分类的第一个元素
                $exp = array_filter(explode(",", $value["wz"]));
                $list[$key]["wz"] = $exp[0];

                $exp = array_filter(explode(",", $value["fg"]));
                $list[$key]["fg"] = $exp[0];

                $exp = array_filter(explode(",", $value["hx"]));
                $list[$key]["hx"] = $exp[0];

                $exp = array_filter(explode(",", $value["ys"]));
                $list[$key]["ys"] = $exp[0];
            }
        }
        return array("list" => $list, "page" => $show, "params" => $params, 'urls' => $result['urls'], 'current' => $Page->nowPage);
    }


    //列表页URL跳转
    private function redirectUrl()
    {
        $SCHEME_HOST = $this->SCHEME_HOST;
        $order = I('get.order');
        if (!empty($order)) {
            $this->setListCookie($order);
            $url = str_replace('?order=' . $order, '', $_SERVER['REQUEST_URI']);
            $url = $SCHEME_HOST['scheme_full'].'meitu.'.$SCHEME_HOST['domain'] . $url;
            header("HTTP/1.1 301 Moved Permanently");
            header("Location:" . $url);
            die();
        }

        //图集 1 图片 2
        $isMulti = stripos($_SERVER['REQUEST_URI'], 'q1');
        if ($isMulti != false) {
            $this->setListCookie('', 2);
            $url = str_replace('q1', '', $_SERVER['REQUEST_URI']);
            $url = $SCHEME_HOST['scheme_full'].'meitu.'.$SCHEME_HOST['domain'] . $url;
            header("HTTP/1.1 301 Moved Permanently");
            header("Location:" . $url);
            die();
        }


        /*S-链接进行301跳转*/
        $redirectArray = array(
            '/list-l0f0h29c0' => '/gongzhuang-l8f0m0',
            '/list-l0f0h26c0' => '/gongzhuang-l6f0m0',
            '/list-l0f0h35c0' => '/gongzhuang-l7f0m0',
            '/list-l0f0h34c0' => '/gongzhuang-l5f0m0',
            '/list-l0f0h25c0' => '/gongzhuang-l4f0m0',
            '/list-l0f0h30c0' => '/gongzhuang-l3f0m0',
            '/list-l0f0h33c0' => '/gongzhuang-l2f0m0',
            '/list-l0f26c0c0' => '/list-l0f26h0c0',
            '/list-l0f15c0c0' => '/list-l0f15h0c0',
            '/list-l0f23c0c0' => '/list-l0f23h0c0',
            '/list-l0f0h0c0' => '/list/',
        );
        if (array_key_exists($_SERVER['REQUEST_URI'], $redirectArray)) {
            $url = $SCHEME_HOST['scheme_full'].'meitu.'.$SCHEME_HOST['domain'] . $redirectArray[$_SERVER['REQUEST_URI']];
            header("HTTP/1.1 301 Moved Permanently");
            header("Location:" . $url);
            die();
        }
        /*E-链接进行301跳转*/

        /*S-将'/list-l0f0h0c0p1/'或者'/list-l0f0h0c0q1/ 的重定向到不带/的*/
        $pattern = '/^\/list-(l[\d+]+f[\d+]+h[\d+]+c[\d+]+(p{1}[\d+]+)?)+(q{1}[\d+]+)?\/$/';
        $i = preg_match($pattern, $_SERVER['REQUEST_URI']);
        if ($i > 0) {
            $redirect = rtrim($_SERVER['REQUEST_URI'], '/');
            $url = $SCHEME_HOST['scheme_full'].'meitu.'.$SCHEME_HOST['domain'] . $redirect;
            header("HTTP/1.1 301 Moved Permanently");
            header("Location:" . $url);
            die();
        }
        /*E-将'/list-l0f0h0c0p1/'或者'/list-l0f0h0c0q1/ 的重定向到不带/的*/

        /*S-废除原有?p=1和q=1的链接，比如/list-l5f0h0c0?p=2的，全部跳转到静态url*/
        if (!empty($_GET['p']) && !isset($_GET['a1'])) {
            $url = $SCHEME_HOST['scheme_full'].'meitu.'.$SCHEME_HOST['domain']. '/list-l0f0h0c0p' . I('get.p');
            header("HTTP/1.1 301 Moved Permanently");
            header("Location:" . $url);
            die();
        }
        if (!empty($_GET['q']) && !isset($_GET['a1'])) {
            $url = $SCHEME_HOST['scheme_full'].'meitu.'.$SCHEME_HOST['domain'] . '/list-l0f0h0c0q' . I('get.q');
            header("HTTP/1.1 301 Moved Permanently");
            header("Location:" . $url);
            die();
        }
        /*E-废除原有?p=1和q=1的链接，比如/list-l5f0h0c0?p=2的，全部跳转到静态url*/

        /*S-跳转到手机端*/
        if (ismobile()) {
            $mobile = '/^\/list-(l[\d+]+f[\d+]+h[\d+]+c[\d+])$/';
            if (preg_match($mobile, $_SERVER['REQUEST_URI']) > 0) {
                header("Location: " . $SCHEME_HOST['scheme_full'].$SCHEME_HOST['host'] . '/meitu' . $_SERVER['REQUEST_URI']);
                exit();
            }
        }
        /*S-跳转到手机端*/

        /*S-动态参数分页跳转到静态参数分页*/
        if (isset($_GET['a1'])) {
            $arr = array(
                'l' => 'a1',
                'f' => 'a2',
                'h' => 'a3',
                'c' => 'a4'
            );
            //拼接静态URL
            $url = $SCHEME_HOST['scheme_full'].'meitu.'.$SCHEME_HOST['domain'] . '/list-';
            foreach ($arr as $key => $value) {
                $url = $url . $key . intval($_GET[$value]);
            }
            if (isset($_GET['q'])) {
                $url = $url . 'q' . intval($_GET['q']);
            } else if (intval($_GET['p']) > 1) {
                $url = $url . 'p' . intval($_GET['p']);
            }
            $url = $url . '?';
            foreach ($_GET as $key => $value) {
                if (!in_array($key, array('a1', 'a2', 'a3', 'a4', 'p', 'q'))) {
                    $url = $url . $key . '=' . $_GET[$key] . '&';
                }
            }
            $url = rtrim($url, '&');
            $url = rtrim($url, '?');
            header("HTTP/1.1 301 Moved Permanently");
            header("Location:" . $url);
            die();
        }
        /*E-动态参数分页跳转到静态参数分页*/
    }

    /**
     * 获取文章信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    private function getArticleInfo($id, $category)
    {
        $result = D("WwwArticle")->getArticleInfoById($id, $category);
        $article = array();
        foreach ($result as $key => $value) {
            if (!array_key_exists($value["action"], $article)) {
                $article[$value["action"]] = array();
            }
            if (empty($value["shortname"])) {
                $value["shortname"] = "history";
                $value["classname"] = "历史资讯";
                $value["title"] = str_replace("_齐装网", "", $value["title"]);
            }
            $article[$value["action"]] = $value;
        }

        foreach ($article as $key => $value) {
            if ($key == "now") {
                //处理文章中的图片
                $pattern = '/<img.*?\/>/is';
                preg_match_all($pattern, $value["content"], $matches);
                if (count($matches[0]) > 0) {
                    foreach ($matches[0] as $k => $val) {
                        $pattern = '/src=[\"|\'](.*?)[\"|\']/is';
                        preg_match_all($pattern, $val, $m);
                        foreach ($m[1] as $j => $v) {
                            if (!strpos($v, C('QINIU_DOMAIN'))) {
                                $path = $this->SCHEME_HOST['scheme_full'] . C('STATIC_HOST1') . $v;
                                $article[$key]["content"] = str_replace($v, $path, $article[$key]["content"]);
                            }
                        }
                    }
                }
            }
        }

        if (isset($article["now"])) {
            //查询文章关键字，替换成内链
            $keywords = D("Wwwarticlekeywords")->getKeywordsRelate($id, "wwwarticle");
            foreach ($keywords as $key => $value) {
                $list[] = "/" . trim($value["name"]) . "/";
            }

            //抽出文章中的所有链接，避免替换链接出现重叠现象(链接套链接)
            $linkPattern = '/<a.*?>.*?<\/a>/i';
            preg_match_all($linkPattern, $article["now"]["content"], $linkMatches);
            if (count($linkMatches[0]) > 0) {
                foreach ($linkMatches[0] as $key => $value) {
                    //将图片替换成变量占位符
                    $article["now"]["content"] = str_replace($value, "#&!&#", $article["now"]["content"]);
                    $replaceLink[] = $value;
                }
            }

            //抽出文章中的图片
            $pattern = '/<img.*?\/>/i';
            preg_match_all($pattern, $article["now"]["content"], $matches);
            if (count($matches[0]) > 0) {
                foreach ($matches[0] as $key => $value) {
                    //将图片替换成变量占位符
                    $article["now"]["content"] = str_replace($value, "%s", $article["now"]["content"]);
                    $replaceImg[] = $value;
                }
            }

            foreach ($list as $key => $value) {
                preg_match_all($value, $article["now"]["content"], $matches);
                if (count($matches[0]) > 0) {
                    $link = "<a href='" . $keywords[$key]["href"] . "' target='_blank' class='inlink-word-color'>" . $keywords[$key]["name"] . "</a>";
                    $article["now"]["content"] = preg_replace($value, $link, $article["now"]["content"], 1);
                }
            }
            //将所有的图片依次填充到原来位置
            foreach ($replaceImg as $key => $value) {
                $article["now"]["content"] = preg_replace("/\%s/", $value, $article["now"]["content"], 1);
            }

            //将所有的链接依次填充到原来位置
            foreach ($replaceLink as $key => $value) {
                $article["now"]["content"] = preg_replace("/#&!&#/", $value, $article["now"]["content"], 1);
            }
        }
        return $article;
    }

    public function getFenggeMeitu(){
        $logic = new MeituLogicModel();
        $temp = $logic->getFenggeMeitu();
        $return['error_code'] = 0;
        $return['error_msg'] = '获取成功';
        $return['data']['list'] = $temp ? $temp : [];
        $this->ajaxReturn($return);
    }
}