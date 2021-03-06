<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class MeituController extends HomeBaseController{

    public function _initialize(){
        parent::_initialize();

        //强行进行301跳转
        $uri = $_SERVER['REQUEST_URI'];
        if(!empty($uri)){
            $url = $this->SCHEME_HOST['scheme_full'] . 'meitu.'.C('QZ_YUMING').str_ireplace('/meitu','',$uri);
            header("HTTP/1.1 301 Moved Permanently");
            header("Location:".$url);
            die();
        }

        //导航栏标识
        $this->assign("tabIndex",3);

        //判断是否是搜索引擎蜘蛛
        $robotIsTrue = B("Common\Behavior\RobotCheck");
        //$robotIsTrue = true; //debug 警告线上应该为注释状态
        if (true === $robotIsTrue) {
            $this->assign('robot',1);
        }
        //添加顶部搜索栏信息
        $this->assign('serch_uri','meitu/list');
        $this->assign('serch_type','装修效果图');
        $this->assign('holdercontent','海量精美家居美图任你选');
        if(empty($this->cityInfo["bm"])){


            $t = T("Home@Index:header");
        }else{
            if(!$robotIsTrue){
                $t = T("Sub@Index:header");
            }
            //显示头部导航栏效果
            $this->assign("nav_show",true);
        }

        $headerTmp = $this->fetch($t);
        $this->assign("headerTmp",$headerTmp);

    }

    public function index(){

        if (ismobile()) {
            header("Location: ".$this->SCHEME_HOST['scheme_full'] .  C('MOBILE_DONAMES') . '/meitu/');
            exit();
        }

        $info = S("Cache:Meitu:Info");
        $hide = S("Cache:Meitu:hide");
        if(!$info && !$hide){
           //获取局部信息
            $info["location"] = $this->getLocation(15,false);
            $hide['location'] = array_slice($info["location"], 6);
            $info["location"] = array_slice($info["location"], 0, 6);
            if(count($hide['location']) == 9){
                $more['id'] = 999;
                $more['name'] = '更多';
                $more['link'] = '/meitu/list/';
                $hide['location'][] = $more;
            }
            //获取风格
            $info["fengge"] = $this->getFengge(15,false);
            $hide['fengge'] = array_slice($info["fengge"], 6);
            $info["fengge"] = array_slice($info["fengge"], 0, 6);
            if(count($hide['fengge']) == 9){
                $more['id'] = 999;
                $more['name'] = '更多';
                $more['link'] = '/meitu/list/';
                $hide['fengge'][] = $more;
            }
            //获取户型
            $info["huxing"] = $this->getHuxing(15,false);
            $hide['huxing'] = array_slice($info["huxing"], 6);
            $info["huxing"] = array_slice($info["huxing"], 0, 6);
            if(count($hide['huxing']) == 9){
                $more['id'] = 999;
                $more['name'] = '更多';
                $more['link'] = '/meitu/list/';
                $hide['huxing'][] = $more;
            }
            //获取颜色信息
            $info["color"] = $this->getColor(15,false);
            $hide['color'] = array_slice($info["color"], 6);
            $info["color"] = array_slice($info["color"], 0, 6);
            if(count($hide['color']) == 9){
                $more['id'] = 999;
                $more['name'] = '更多';
                $more['link'] = '/meitu/list/';
                $hide['color'][] = $more;
            }
            S("Cache:Meitu:hide",$hide,900);
            //获取轮播信息
            $info["lunbo"] = D("Meitu")->getLunbo();
            //获取推荐热图
            $info["HotImg"] = D("Meitu")->getGoodImg();
            //获取热图排行
            $info["rankImg"] = D("Meitu")->getRankMeitu(6);

            //获取格局之美
            $info["gjzm"] = $this->getMeituListByPart("location",3);
            //获取潮流设计
            $info["clsj"] = $this->getMeituListByPart("cl",3);
            //获取大师设计
            $info["designer"] = $this->getDesignerImg(5);

            //获取家居风格
            $fgzm = array(
                    array("src"=>"/assets/common/img/fg1.jpg","link"=>"/meitu/list-l0f4h0c0","title"=>"中式风格","subtitle"=>"Chinese Style"),//中式
                    array("src"=>"/assets/common/img/fg2.jpg","link"=>"/meitu/list-l0f8h0c0","title"=>"欧式风格","subtitle"=>"European Style"),//欧式
                    array("src"=>"/assets/common/img/fg3.jpg","link"=>"/meitu/list-l0f12h0c0","title"=>"现代简约风格","subtitle"=>"Simple Style"),//现代简约风格
                    array("src"=>"/assets/common/img/fg4.jpg","link"=>"/meitu/list-l0f6h0c0","title"=>"地中海风格","subtitle"=>"Mediterranean Style"),//地中海
                    array("src"=>"/assets/common/img/fg5.jpg","link"=>"/meitu/list-l0f13h0c0","title"=>"田园风格","subtitle"=>"Pastoral Style"),//田园
                    array("src"=>"/assets/common/img/fg6.jpg","link"=>"/meitu/list-l0f0h0c0","title"=>"美式风格","subtitle"=>"American Style"),//美式
            );
            $info["fgzm"] = $fgzm;
            //公装效果图
            $gzxg = array(
                    array("title"=>"办公室","src"=>"/assets/common/img/office.jpg","href"=>"/meitu/list-l0f0h29c0"),
                    array("title"=>"服装店","src"=>"/assets/common/img/fu.jpg","href"=>"/meitu/list-l0f0h26c0"),
                    array("title"=>"酒店","src"=>"/assets/common/img/jiu.jpg","href"=>"/meitu/list-l0f0h35c0"),
                    array("title"=>"餐厅","src"=>"/assets/common/img/can.jpg","href"=>"/meitu/list-l6f0h0c0"),
                    array("title"=>"厂房","src"=>"/assets/common/img/chang.jpg","href"=>"/meitu/list-l0f0h34c0"),
                    array("title"=>"水果店","src"=>"/assets/common/img/sgd.jpg","href"=>"/meitu/list-l0f0h25c0"),
                    array("title"=>"美容院","src"=>"/assets/common/img/mry.jpg","href"=>"/meitu/list-l0f0h30c0"),
                    array("title"=>"宾馆","src"=>"/assets/common/img/bin.jpg","href"=>"/meitu/list-l0f0h33c0"),
            );
            $info["gzxg"] = $gzxg;
            //家居局部
            $jubu = array(
                    array("title"=>"客厅装修","src"=>"/assets/common/img/keting.jpg","href"=>"/meitu/list-l4f0h0c0"),
                    array("title"=>"厨房装修","src"=>"/assets/common/img/chufang.jpg","href"=>"/meitu/list-l7f0h0c0"),
                    array("title"=>"卫生间装修","src"=>"/assets/common/img/wsj.jpg","href"=>"/meitu/list-l8f0h0c0"),
                    array("title"=>"卧室装修","src"=>"/assets/common/img/ws.jpg","href"=>"/meitu/list-l5f0h0c0"),
                    array("title"=>"阳台装修","src"=>"/assets/common/img/ys.jpg","href"=>"/meitu/list-l9f0h0c0"),
                    array("title"=>"吊顶装修","src"=>"/assets/common/img/diaoding.jpg","href"=>"/meitu/list-l21f0h0c0")
            );
            $info["jubu"] = $jubu;

             //获取局部信息 location fengge huxing color
            $info["jubuketin"] = D("Meitu")->getMeituByCategory('location',4,4);
            $info["jububeijin"] = D("Meitu")->getMeituByCategory('location',30,4);

            //获取专题
            $info["zt"] = D("Meitu")->getZhuanti();
            S("Cache:Meitu:Info",$info,900);
        }

        //seo 标题/描述/关键字
        $keys["title"] = "装修效果图_".date("Y")."室内家装装饰设计效果图大全-齐装网装修效果图";
        $keys["keywords"] = "装修效果图,装饰效果图,家装效果图,室内装修效果图大全,室内装修效果图,家装效果图大全";
        $keys["description"] = "齐装网汇聚".date("Y")."国内外受欢迎的家庭装修效果图片，为您提供新室内装修装饰效果图大全以及丰富的家居设计美图，不一样的装修图片为您带来不一样的房屋装修灵感。找装修美图就上齐装网！";
        $this->assign("keys",$keys);

        //获取友情链接
        $friendLink = S("C:FL:A:meitu");
        if (!$friendLink) {
            $friendLink['link'] = D("Friendlink")->getFriendLinkList("000001",1,'meitu');
            $friendLink['tags'] = D("Friendlink")->getFriendLinkList("000001",3,'meitu');
            S("C:FL:A:meitu", $friendLink, 900);
        }
        if(count($friendLink['link']) > 0){
            $this->assign("friendLink",$friendLink);
        }

        //底部设计浮动框
        $this->assign("zb_bottom_s",$this->fetch(T("Common@Order/zb_bottom_b")));
        $this->assign('hide',$hide);
        $this->assign("info",$info);
        $this->display();
    }

    //美图列表页
    public function meitulist(){
        /*S-链接进行301跳转*/
        $redirectArray = array(
            '/meitu/list-l0f0h29c0' => '/meitu/gongzhuang-l8f0m0',
            '/meitu/list-l0f0h26c0' => '/meitu/gongzhuang-l6f0m0',
            '/meitu/list-l0f0h35c0' => '/meitu/gongzhuang-l7f0m0',
            '/meitu/list-l0f0h34c0' => '/meitu/gongzhuang-l5f0m0',
            '/meitu/list-l0f0h25c0' => '/meitu/gongzhuang-l4f0m0',
            '/meitu/list-l0f0h30c0' => '/meitu/gongzhuang-l3f0m0',
            '/meitu/list-l0f0h33c0' => '/meitu/gongzhuang-l2f0m0',
            '/meitu/list-l0f26c0c0' => '/meitu/list-l0f26h0c0',
            '/meitu/list-l0f15c0c0' => '/meitu/list-l0f15h0c0',
            '/meitu/list-l0f23c0c0' => '/meitu/list-l0f23h0c0',
            '/meitu/list-l0f0h0c0'  => '/meitu/list/',
        );
        if(array_key_exists($_SERVER['REQUEST_URI'], $redirectArray)){
            $url = $this->SCHEME_HOST['scheme_full'].C('QZ_YUMINGWWW').$redirectArray[$_SERVER['REQUEST_URI']];
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:".$url);
            die();
        }
        /*E-链接进行301跳转*/

        /*S-将'/meitu/list-l0f0h0c0p1/'或者'/meitu/list-l0f0h0c0q1/ 的重定向到不带/的*/
        $pattern = '/^\/meitu\/list-(l[\d+]+f[\d+]+h[\d+]+c[\d+]+(p{1}[\d+]+)?)+(q{1}[\d+]+)?\/$/';
        $i = preg_match($pattern, $_SERVER['REQUEST_URI']);
        if($i > 0){
            $redirect = rtrim($_SERVER['REQUEST_URI'], '/');
            $url = $this->SCHEME_HOST['scheme_full'] . C('QZ_YUMINGWWW').$redirect;
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:".$url);
            die();
        }
        /*E-将'/meitu/list-l0f0h0c0p1/'或者'/meitu/list-l0f0h0c0q1/ 的重定向到不带/的*/

        /*S-废除原有?p=1和q=1的链接，比如/meitu/list-l5f0h0c0?p=2的，全部跳转到静态url*/
        if(!empty($_GET['p']) && !isset($_GET['a1'])){
            $url = $this->SCHEME_HOST['scheme_full'].C('QZ_YUMINGWWW').'/meitu/list-l0f0h0c0p'.I('get.p');
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:".$url);
            die();
        }
        if(!empty($_GET['q']) && !isset($_GET['a1'])){
            $url = $this->SCHEME_HOST['scheme_full'] . C('QZ_YUMINGWWW').'/meitu/list-l0f0h0c0q'.I('get.q');
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:".$url);
            die();
        }
        /*E-废除原有?p=1和q=1的链接，比如/meitu/list-l5f0h0c0?p=2的，全部跳转到静态url*/

        /*S-跳转到手机端*/
        if (ismobile()) {
            $mobile = '/^\/meitu\/list-(l[\d+]+f[\d+]+h[\d+]+c[\d+])$/';
            if (preg_match($mobile, $_SERVER['REQUEST_URI']) > 0) {
                header("Location: ". $this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
                exit();
            }
        }
        /*S-跳转到手机端*/

        /*S-SEO的canonical标签*/
        $patternq = '/q\d+/i';
        preg_replace($patternq, '',$_SERVER['REQUEST_URI'], -1,$countq);
        $patternp = '/p\d+/i';
        preg_replace($patternp, '',$_SERVER['REQUEST_URI'], -1,$countp);
        if($countp >0 || $countq >0){
            $info["noindex"] = '<meta name="robots" content="noindex,follow"/>';
        }
        /*E-SEO的canonical标签*/

        /*S-判断访问的是单图还是套图*/
        //默认套图
        $multi = true;
        $pattern = '/^\/meitu\/list-(l[\d+]+f[\d+]+h[\d+]+c[\d+]+q[\d+])/';
        if (!empty($_GET['q']) || (preg_match($pattern, $_SERVER['REQUEST_URI'])) > 0) {
            $multi = false;
        }
        $info['multi'] = $multi;
        /*E-判断访问的是单图还是套图*/

        //获取美图列表
        $each = 40;
        //搜索功能
        $keyword = I("get.keyword");
        if(!empty($keyword)){
            if(!checkKeyword($keyword)){
                $this->_error();
            }
            $keyword = remove_xss($keyword);
        }
        $meitu = $this->getMeiTuList($each, $keyword, $multi);

        $info["meitu"] = $meitu["list"];
        $info["page"] = $meitu["page"];
        /*S-导航条件筛选URL生成*/
        //获取导航栏局部短链接
        //第一个参数为该类型下的全部类型，传入当前链接动态参数和静态参数，对对应的参数逐一替换
        $isTop = false;
        $location = D("Meitu")->getLocation($limit,$isTop);
        $info["wz"] = $this->getNavUrl($location,array('statics' => 'l','dynamic' => 'a1'),$meitu['urls'],$multi);
        //获取导航栏风格短链接
        $fengge = D("Meitu")->getFengge($limit,$isTop);
        $info["fg"] = $this->getNavUrl($fengge,array('statics' => 'f','dynamic' => 'a2'),$meitu['urls'],$multi);
        //获取导航栏户型短链接
        $huxing = D("Meitu")->getHuxing($limit,$isTop);
        $info["hx"] = $this->getNavUrl($huxing,array('statics' => 'h','dynamic' => 'a3'),$meitu['urls'],$multi);
        //获取导航栏颜色短链接
        $color = D("Meitu")->getColor($limit,$isTop);
        $info["ys"] = $this->getNavUrl($color,array('statics' => 'c','dynamic' => 'a4'),$meitu['urls'],$multi);
        /*E-导航条件筛选URL生成*/

        /*S-面包屑导航动态生成绑定参数*/
        $arrays = explode('?', $meitu['urls']['dynamic']);
        $arrays = array_filter(explode('&', $arrays[1]));
        $count = count(array_filter($meitu['params']));
        //下面的foreach循环：$key为该参数 //$value为该参数的值，根据该参数，传入函数将该参数的值设置为0，就是绑定的参数对应的链接
        foreach ($meitu["params"] as $key => $value) {
            switch ($key) {
                case in_array($key, array('l','a1')):
                    $key = 'location';
                    $sub = $this->getSelectedUrl('a1',$value,$meitu["urls"]['dynamic'],$count,$info["wz"]);
                    $info["params"]['location'] = $sub;
                    break;
                case in_array($key, array('f','a2')):
                    $key = 'fengge';
                    $sub = $this->getSelectedUrl('a2',$value,$meitu["urls"]['dynamic'],$count,$info["fg"]);
                    $info["params"]['fengge'] = $sub;
                    break;
                case in_array($key, array('h','a3')):
                    $key = 'huxing';
                    $sub = $this->getSelectedUrl('a3',$value,$meitu["urls"]['dynamic'],$count,$info["hx"]);
                    $info["params"]['huxing'] = $sub;
                    break;
                case in_array($key, array('c','a4')):
                    $key = 'color';
                    $sub = $this->getSelectedUrl('a4',$value,$meitu["urls"]['dynamic'],$count,$info["ys"]);
                    $info["params"]['color'] = $sub;
                    break;
            }
            $info["navParams"][$key] = $value;
        }
        /*E-面包屑导航动态生成绑定参数*/

        /*S—SEO标题关键字描述相关*/
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

        if(!empty($keyword)){
            $this->assign("keyword",$keyword);
            $keys["title"] = "搜索结果页 - 齐装网";
            $keys["keywords"] = "";
            $keys["description"] = "";
        }else{
            //翻页显示分页
            $tkd_page = $meitu['current'] > 1 ? '(第' . $meitu['current'] . '页)' : '';
            if($i == 0){
                //a.17.05.06 SEO需求调整
                //如果是图集页
                if ($info['multi'] == true && $meitu['current'] == 1) {
                    $keys["title"] = '家居效果图册_家装设计效果图册_装修图册-齐装网装修效果图';
                    $keys["keywords"] = '家装效果图册，家装设计效果图册，家装装修图册';
                    $keys["description"] = '齐装网家装效果图专区，提供国内外实用高清装修案例图册，每日更新上百套装修案例图册，齐装网装修图集栏目包含各类家居装修图片和设计图片。';
                } else {
                    $keys["title"] = '家居装修效果图_家庭装修效果图 - 齐装网装修效果图' . $tkd_page;
                    $keys["keywords"] = '家居装修效果图， 家庭装修效果图';
                    $keys["description"] = '齐装网家居美图频道，汇集了数十万装修家庭装修效果图，包含空间图片(客厅、卧室、卫生间等)、风格图片(中式、欧式、美式、现代简约等)， 海量家庭装修美图尽在齐装网。';
                }
            }else if($i == 1){
                $arrayc = array_filter($meitu["params"]);
                //只筛选了一个条件的时候获取数据库的TDK标签
                if(count($arrayc) == 1){
                    $arraykey =array_keys($arrayc);
                }
                $tdkresult = D('Meitu')->getMeituListDescription($only,$arraykey[0]);
                if(!empty($tdkresult['title'])){
                    $keys["title"] = $tdkresult['title'] . $tkd_page;
                }else{
                    $keys["title"] = $only . '装修效果图_'. $only .'设计_'. $only .'图片 - 齐装网装修效果图';
                }
                if(!empty($tdkresult['keywords'])){
                    $keys["keywords"] = $tdkresult['keywords'];
                }else{
                    $keys["keywords"] = $only . '装修效果图，'. $only .'设计效果图，'. $only .'图片';
                }
                if(!empty($tdkresult['description'])){
                    $keys["description"] = $tdkresult['description'];
                }else{
                    $keys["description"] = "齐装网汇聚".date("Y").$content."家庭室内装修装饰风格效果图大全，为您提供".date("Y").$content."效果图大全以及丰富的家居设计美图。找".$content."美图就上齐装网！";
                }
            }else{

                $tdk = $info['params']['color']['name'] . $info['params']['huxing']['name'] . $info['params']['fengge']['name'] . $info['params']['location']['name'];

                $keys["title"] = $tdk. "装修效果图-齐装网装修效果图";
                $keys["keywords"] = $tdk. "装修效果图，" . $tdk. "设计," . $tdk. "图片";
                $keys["description"] = "齐装网" . $tdk. "装修效果图专区，提供".date('Y')."新的" . $tdk. "装修效果图以及" . $tdk. "装修样板案例。更多精美" . $tdk. "图片，尽在齐装网" . $tdk. "效果图专区。";
            }
        }

        //20170505TKD改回之前的
        /*if(!empty($keyword)){
            $this->assign("keyword",$keyword);
            $keys["title"] = "搜索结果页 - 齐装网";
            $keys["keywords"] = "";
            $keys["description"] = "";
        }else{
            //套图与单图tkd分开
            if ($multi == true) {
                $keys["title"] = '装修案例_装修案例图_装修图册 第' . $meitu['current'] . '页 - 齐装网站装修效果图';
                $keys["keywords"] = '装修案例,装修案例图,装修图册';
                $keys["description"] = '齐装网装修效果图提供国内外实用高清装修案例图，每日更新上百套装修案例图，齐装网装修图集栏目包含各类家居装修图片和设计图片。';
            } else {
                $keys["title"] = '家居装修效果图_家庭装修效果图 第' . $meitu['current'] . '页 - 齐装网装修效果图';
                $keys["keywords"] = '家居装修效果图,家庭装修效果图';
                $keys["description"] = '齐装网家居美图频道，汇集了数十万装修家庭装修效果图，包含空间图片(客厅、卧室、卫生间等)、风格图片(中式、欧式、美式、现代简约等)， 海量家庭装修美图尽在齐装网。';
            }
        }*/

        $this->assign("keys",$keys);
        $info["params"] = array_filter($info["params"]);
        /*E—SEO标题关键字描述相关*/

        /*S-底部设计浮动框*/
        $t = T("Common@Order/zb_bottom_s");
        $zb_bottom_s = $this->fetch($t);
        $this->assign("zb_bottom_s",$zb_bottom_s);
        //获取是否显示获取报价弹层
        if(!isset($_COOKIE["w_index"])){
            setcookie("w_index",1,time()+(3600*24),'/', '.'.APP_HTTP_DOMAIN);
            $this->assign("openSJBJ",true);
        }
        /*E-底部设计浮动框*/

        /*S-友情链接模块：以下链接添加友情链接模块*/
        $linktypes = S('Home:Meitu:FriendLinkCategory');
        if(empty($linktypes)){
            $linkcategory = D('FriendLinkCategory')->getFriendLinkCategoryList(['link_page' => ['like','meitu%']]);
            foreach ($linkcategory as $key => $value) {
                if(!empty($value['link_page_url'])){
                    $linktypes[$value['link_page_url']] = $value['link_page'];
                }
            }
            S('Home:Meitu:FriendLinkCategory',$linktypes,360000);
        }
        $type = '';
        foreach ($linktypes as $key => $value) {
            $count = 0;
            str_ireplace($key, '&###&', $_SERVER['REQUEST_URI'],$count);
            if($count >0){
                $type = $value;
                break;
            }
        }
        if($meitu['current'] == 1){
            if(!empty($type)){
                $friendLink['link'] = D('Friendlink')->getFriendLinkList('000001','1',$type);
            }elseif('/meitu/list/' == $_SERVER['REQUEST_URI']){
                $friendLink['link'] = D('Friendlink')->getFriendLinkList('000001','1','meitu-list');
            }
        }
        if(!empty($type)){
            $friendLink['tags'] = D('Friendlink')->getFriendLinkList('000001','3',$type);
        }elseif('/meitu/list/' == $_SERVER['REQUEST_URI']){
            $friendLink['tags'] = D('Friendlink')->getFriendLinkList('000001','3','meitu-list');
        }
        $this->assign('friendLink',$friendLink);
        /*E-友情链接模块：以下链接添加友情链接模块*/


        /*S-时间和人气排序URL拼接*/
        if(false === strpos($_SERVER['REQUEST_URI'], '?')){
            $info['order']['hot'] = $_SERVER['REQUEST_URI']. '?order=hot';
            $info['order']['new'] = $_SERVER['REQUEST_URI']. '?order=new';
        }else{
            if(false != strpos($_SERVER['REQUEST_URI'], 'order=hot')){
                $info['order']['hot'] = $_SERVER['REQUEST_URI'];
                $info['order']['new'] = str_ireplace('order=hot', 'order=new', $_SERVER['REQUEST_URI']);
            }elseif(false != strpos($_SERVER['REQUEST_URI'], 'order=new')){
                $info['order']['hot'] = str_ireplace('order=new', 'order=hot', $_SERVER['REQUEST_URI']);
                $info['order']['new'] = $_SERVER['REQUEST_URI'];
            }else{
                $info['order']['hot'] = $_SERVER['REQUEST_URI']. '&order=hot';
                $info['order']['new'] = $_SERVER['REQUEST_URI']. '&order=new';
            }
        }
        /*E-时间和人气排序URL拼接*/

        if(!isset($_GET['keyword']) && !isset($_GET['a1'])){
            //将类如/meitu/list-l0f0h0c0p1的p1去掉
            $pattern = '/^\/meitu\/list-(l[\d+]+f[\d+]+h[\d+]+c[\d+]+)/';
            $result = preg_replace($pattern, '', $_SERVER['REQUEST_URI'],'-1',$count);
            if(empty($result) || $count == 0){
                if($count == 0){
                    $canonical = '/meitu/list/';
                }else{
                    $canonical = $meitu['urls']['statics'];
                }
            }else{
                //20170510 a.17.05.06需求调整
                $pattern = '/^p[\d+]+/';
                $canonical = $meitu['urls']['statics'];
                preg_match($pattern, $result, $matche);
                if(!empty($matche['0'])){
                    if ($i == 0) {
                        $canonical = '/meitu/list/';
                    } else {
                        $canonical = $meitu['urls']['statics'];
                    }
                }else{
                    $pattern = '/^q[\d+]+/';
                    preg_match($pattern, $result, $matche);
                    if(!empty($matche['0'])){
                        $canonical = $meitu['urls']['statics'].'q1';
                    }
                }
            }
            $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'] . C('QZ_YUMINGWWW').$canonical;
        }

        //指向移动端的信息
        if("/meitu/list/" == $_SERVER["REQUEST_URI"]){
            $info["mobileAgent"] = "/meitu/list/";
        }
        $pattern = '/^\/meitu\/list-(l[\d+]+f[\d+]+h[\d+]+c[\d+])$/';
        $i = preg_match($pattern, $_SERVER['REQUEST_URI']);
        if($i > 0){
            $info["mobileAgent"] = $_SERVER['REQUEST_URI'];
        }
        $this->assign("info",$info);
        $this->display("list");
    }

    //美图详情页
    public function caseinfo(){
        if (ismobile()) {
            header("Location: ". $this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }
        $p = I("get.p");
        //取条件筛选Cookie
        $params = cookie('meitu_params');
        $info = S("Cache:Meitu:Case:".$p);
        if(!$info){
            //查询美图案例信息
            $meitu = $this->getMeituInfo($p,$params);
            //var_dump($meitu);
            $meitu['attribute'] = $this->getMeituAttribute($meitu['now']);
            if(empty($meitu["now"])){
                $this->_error();
            }
            $info["case"] = $meitu;

            //获取 标签
            $newTags = D("Common/Tags")->getHotTags('2','15');
            $info["newTags"] = $newTags;

            //获取推荐局部信息
            $params_count = 0;
            $info["wz"] = $this->getLocation("","","",$params_count, "");
            //获取风格
            $info["fg"] = $this->getFengge("","","",$params_count, "");
            //获取户型
            $info["hx"] = $this->getHuxing("","","",$params_count, "");
            //获取颜色信息
            $info["ys"] = $this->getColor("","","",$params_count, "");

            //获取相关美图
            $info["relatedMeitu"] = $this->getRelatedMeitu($info["case"]['now']);
            S("Cache:Meitu:Case:".$p,$info,10800); //标签缓存3个小时
        }
        //查看推荐图集
        $info['recommend'] = S('Cache:Meitu:Case:Recommend:'.md5($info['case']['attribute']['location']['id'].$info['case']['attribute']['fengge']['id']));
        if(empty($info['recommend'])){
            $info['recommend'] = D('Meitu')->getRecommendMeituByAttr($info['case']['attribute']['location']['id'],$info['case']['attribute']['fengge']['id']);
            S('Cache:Meitu:Case:Recommend:'.md5($info['case']['attribute']['location']['id'].$info['case']['attribute']['fengge']['id']),$info['recommend'],21600);
        }

        $info["collect"] = false;
        //查询用户是否关注过该案例
        if(isset($_SESSION["u_userInfo"])){
            $uid = $_SESSION['u_userInfo']['id'];
            $count =  D("Usercollect")->getCollectCount($p,$uid,4);
            if($count > 0){
                $info["collect"] = true;
            }
        }else{
            $uid = '';
        }
        $template = 'caseinfo';

        //单个图集
        if($info['case']['now']['is_single'] == '1'){

            if($info["collect"]){
                $info['case']['now']['collect'] = $info['case']['now']['id'];
            }else{
                $info['case']['now']['collect'] = null;
            }

            $template = 'caseinfo_single';
            unset($info['case']['now']['child']);
            //查询单个图片前后图集
            $singleCaseList['pre'] = D('Common/Meitu')->getSingleCases($p,'pre',7,$params,$uid);
            $preNum = count($singleCaseList['pre']);
            krsort($singleCaseList['pre']);

            //如果前面小于9个
            if($preNum < 7){
                $nextNum = 7 + (6 - $preNum);
                $singleCaseList['next'] = D('Common/Meitu')->getSingleCases($p,'next',$nextNum,$params,$uid);
                $imgList['pre'] = '000000';
            }else{
                $singleCaseList['next'] = D('Common/Meitu')->getSingleCases($p,'next',7,$params,$uid);
                //取第一条为上一页
                $tmp_preid = count($singleCaseList['pre']) -1 ;
                $imgList['pre'] = $singleCaseList['pre'][$tmp_preid];
                unset($singleCaseList['pre'][$tmp_preid]);
            }

            //如果下一图集小于9个
            if(count($singleCaseList['next']) > 6){
                $tmp_lastid = count($singleCaseList['next']) -1 ;
                $imgList['next'] = $singleCaseList['next'][$tmp_lastid];
                unset($singleCaseList['next'][$tmp_lastid]);
            }

            $newSingle = array_merge($singleCaseList['pre'],$singleCaseList['next']);

            array_unshift($newSingle,$info['case']['now']);

            $this->assign('singleCaseList',$newSingle);
        }


        $this->assign("relatedMeitu",$info["relatedMeitu"]);

        //seo 标题/描述/关键字
        $keys["title"] = $info["case"]["now"]["title"]."-齐装网装修效果图";
        $keys["keywords"] = $info["case"]["now"]["title"];
        $keys["description"] = '齐装网装修效果图频道，提供'.date('Y').'新的'.$info["case"]["now"]["title"].'，定期更新上百套'.$info["case"]["now"]["title"].'，为您带来精彩的装修设计灵感。';
        $this->assign("keys",$keys);


        if(empty($info['case']['prv'])){
            $info['case']['prv'] = D("Meitu")->getFirstMeitu("desc",$params);
        }
        if(empty($info['case']['next'])){
            $info['case']['next'] = D("Meitu")->getFirstMeitu();
        }


        if($imgList['pre'] == '000000'){
            $imgList['pre'] = $info['case']['prv'];
        }

        //获取是否显示获取报价弹层
        if(!isset($_COOKIE["meitu_tips"])){
            $this->assign("isMeituTip",true);
        }
/*
        dump($newSingle);
        die;*/

        $this->assign('imgList',$imgList);

        //设置canonical属性
        $header['canonical'] = $this->SCHEME_HOST['scheme_full'] . C('QZ_YUMINGWWW').'/meitu/p'.$p.'.html';
        $this->assign('header',$header);
        $this->assign("caseInfo",$info);
        $this->display($template);
    }

    //美图名师列表
    public function mingshi(){
        //将/mingshi重定向到/mingshi/
        if("/mingshi" == $_SERVER["REQUEST_URI"]){
            $url = '/mingshi/';
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:".$url);
            die();
        }
        //获取名师列表
        $info = S("Cache:Meitu:MingshiInfo");
        if(!$info){
            //获取名师列表
            $mingshilist = D("Meitu")->getMingshiList();
            $info["mingshilist"] = $mingshilist;
            S("Cache:Meitu:MingshiInfo",$info,3600);
        }

        if(I("get.shortname") !== ""){
            $name = I("get.shortname");
            $info["mingshi"] = $name;
            foreach ($info["mingshilist"] as $key => $value) {
                if($value["shortname"] == $name){
                    $dname .=$value["name"];
                    $info["mingshiname"] =$dname;
                    break;
                }
            }
        }

        //获取名师美图列表
        $pageIndex = 1;
        $pageCount = 60;
        if(I("get.p") !== ""){
            $pageIndex = I("get.p");
        }

        $meitu = $this->getMingshiCaseList($name,$pageIndex,$pageCount);
        $info["meitu"] = $meitu["meitu"];
        $info["page"] = $meitu["page"];

        //seo 标题/描述/关键字
        if(empty($dname)){
            $keys["title"] = "知名室内设计师作品大全 - 齐装网装修效果图";
            $keys["keywords"] = "室内设计师，装修设计师";
            $keys["description"] = "齐装网室内设计师频道，汇集了国内外知名室内设计师众多作品，是业主及室内设计师搜集设计灵感的首选之地。";
        }else{
            $keys["title"] = $dname . "设计作品 - 齐装网装修效果图";
            $keys["keywords"] = $dname . "，设计师，室内设计";
            $keys["description"] = "齐装网名设计师频道提供设计师".$dname."的新的设计作品展示。";
            $this->assign("dname",$dname);
        }

        $this->assign("keys",$keys);
        //导航栏标识
        $this->assign("tabIndex",3);
        $this->assign("info",$info);
        $this->display();
    }

    public function download(){

        function getHttp($url) {
            if (!function_exists('file_get_contents')) {
                $file_content = file_get_contents($url);
            } elseif (ini_get('allow_url_fopen') && ($file = @fopen($url, 'rb'))){
                $curl_handle = curl_init();
                curl_setopt($curl_handle, CURLOPT_URL, $url);
                curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT,2);
                curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER,1);
                curl_setopt($curl_handle, CURLOPT_FAILONERROR,1);

                curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.66 Safari/537.36');
                //curl_setopt($curl_handle, CURLOPT_USERAGENT, 'msnbot/2.0b (+http://search.msn.com/msnbot.htm)');
                curl_setopt($curl_handle, CURLOPT_REFERER,'-');
                $file_content = curl_exec($curl_handle);
                curl_close($curl_handle);
            } else {
                $file_content = '';
            }
            return $file_content;
        }

        function getDomain($url) {
            $arr_url = parse_url($url);
            $parseurl =  $arr_url['host'];
            if(empty($parseurl)){
                preg_match("/^(http:\/\/)?([^\/]+)/i", $url, $arr_domain);
                $parseurl = $arr_domain['2'];
            }
            return $parseurl;
        }

        function fileSend($filepath,$filename = '',$filetype) {
            if(!$filename) $filename = basename($filepath);
            $filename = rawurlencode($filename);
            $filesize = sprintf("%u", filesize($filepath));
            if(ob_get_length() !== false) @ob_end_clean();
            header('Pragma: public');
            header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: pre-check=0, post-check=0, max-age=0');
            header('Content-Transfer-Encoding: binary');
            header('Content-Encoding: none');
            header('Content-type: '.$filetype);
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            header('Content-length: '.$filesize);
            readfile($filepath);
            exit;
        }

        $file = I('get.url');

        $domain = getDomain($file);
        if($domain != 'staticqn.qizuang.com'){
            exit('System Error!');
        }

        $pathinfo = pathinfo($file);
        fileSend($file,$pathinfo['basename'],$pathinfo['extension']);

    }



    /**
     * 设置美图提示显示
     * @return [type] [description]
     */
    public function closetip(){
        setcookie("meitu_tips",1,time()+(3600*24),'/', '.'.APP_HTTP_DOMAIN);
        $this->ajaxReturn(array("ok"));
    }

    //喜欢
    public function like(){
        //判断是否登录
        if(!isset($_SESSION["u_userInfo"])){
           //die('login');
        }
        $tempData = I('post.');
        $id = $tempData['id'];

        if(empty($id) || !is_numeric($id)){
            $this->ajaxReturn(array("data"=>"","info"=>"数据错误","status"=>0));
        }else{
            //喜欢数+1
            M("meitu")->where(array('id' => $id))->setInc('likes');
            $this->ajaxReturn(array("data"=>"","info"=>"成功","status"=>1));
        }
    }

    //取装修案例
    private function getNewMeitu($limit){
        $result = S('Cache:Meitu:NewMeitu');
        if(empty($result)){
            S('Cache:Meitu:NewMeitu',null);
            $result = D("Meitu")->getNewMeitu(30);
            S('Cache:Meitu:NewMeitu',$result,900);
        }
        shuffle($result);
        return array_slice($result,0,$limit);
    }

    //获取装修日记
    private function getHotDiary($num){
        $result = S('Cache:Meitu:HotDiary');
        if(empty($result)){
            $result = D('Diary')->getHotDiaryUser(30,false,false);
            S('Cache:Meitu:HotDiary',$result,900);
        }
        shuffle($result);
        return array_slice($result,0,$num);
    }

    //获取相关美图
    private function getRelatedMeitu($info){
        $map['fengge'] = $info['fengge'];
        $map['huxing'] = $info['huxing'];
        $map['is_single'] = $info['is_single'];
        $id = $info['id'];
        $result = D('Meitu')->getRelatedMeitu($map,$id);
        return $result;
    }

    private function getMingshiCaseList($name,$pageIndex,$pageCount)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("Meitu")->getMingshiCaseListCount($name);
        if($count > 0){
            import('Library.Org.Page.Page');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \Page($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();
            $meitu = D("Meitu")->getMingshiCaseList($name,($page->pageIndex-1)*$pageCount,$pageCount);
            foreach ($meitu as $key => $value) {
                //取每个分类的第一个元素
                $exp =array_filter(explode(",", $value["wz"]));
                $meitu[$key]["wz"] = $exp[0];

                $exp =array_filter(explode(",", $value["fg"]));
                $meitu[$key]["fg"] = $exp[0];

                $exp =array_filter(explode(",", $value["hx"]));
                $meitu[$key]["hx"] = $exp[0];

                $exp =array_filter(explode(",", $value["ys"]));
                $meitu[$key]["ys"] = $exp[0];
             }
            return array("meitu"=>$meitu,"page"=>$pageTmp);
        }
        return null;
    }

    private function getRecommendCases($classid,$limit){
        $cases = D("Meitu")->getRecommendCases($classid);
        if(count($cases) > 0){
            shuffle($cases);
            $cases = array_slice($cases,0,$limit);
            return $cases;
        }
        return null;
    }

    private function getRecommendArticles($classid,$limit){
        //获取相同分类的点击量最高的文章
        $recommendArticles = D("WwwArticle")->getRecommendArticles($classid);
        if(count($recommendArticles) > 0){
            shuffle($recommendArticles);
            $recommendArticles = array_slice($recommendArticles,0,$limit);
        }
        return $recommendArticles;
    }

    private function getMeituInfo($id,$params){
        $meitu = D("Meitu")->getMeituInfo($id,$params);
        foreach ($meitu as $key => $value) {
            if(!array_key_exists($value["action"], $meitu)){
                $meitu[$value["action"]] = $value;
            }
            $meitu[$value["action"]]["child"][] = $value;
            $meitu[$value["action"]]["count"] ++;
            unset($meitu[$key]);
        }
        return $meitu;
    }

    private function getParams($type,$value,$count,$url,$data){
        foreach ($data as $k => $val) {
            if($value == $val["id"]){
                $sub = array(
                        "name" =>$val["name"]
                             );
                if($count == 1){
                    $sub["link"] = "/meitu/list/";
                }else{
                    switch ($type) {
                        case 'location':
                           //替换当前的参数
                            $reg = '/a1=\d+/i';
                            preg_match($reg,  $url["url"],$m);
                            $link = preg_replace($reg, "a1=0",$url["url"]);
                            break;
                         case 'fengge':
                           //替换当前的参数
                            $reg = '/a2=\d+/i';
                            preg_match($reg,  $url["url"],$m);
                            $link = preg_replace($reg, "a2=0",$url["url"]);
                            break;
                         case 'huxing':
                           //替换当前的参数
                            $reg = '/a3=\d+/i';
                            preg_match($reg,  $url["url"],$m);
                            $link = preg_replace($reg, "a3=0",$url["url"]);
                            break;
                         case 'color':
                           //替换当前的参数
                            $reg = '/a4=\d+/i';
                            preg_match($reg,  $url["url"],$m);
                            $link = preg_replace($reg, "a4=0",$url["url"]);
                            break;

                    }
                    $sub["link"] = $link;
                }
                break;
            }
        }

        return $sub;
    }

    private function getLocation($limit,$isTop,$type,$params_count,$url){
        $result = D("Meitu")->getLocation($limit,$isTop);
        foreach ($result as $key => $value) {
            if(empty($type) && $params_count == 0){
                 $link ="/meitu/list-l".$value["id"]."f0h0c0";
                 $result[$key]["nofollow"] = 'follow';
            }elseif(!empty($type) && $params_count == 1){
                if($type == "location"){
                    //替换当前的参数
                    $reg = '/l\d+/i';
                    $link =preg_replace($reg, "l".$value["id"],$url["short_url"]);
                    $result[$key]["nofollow"] = 'follow';
                }else{
                    //替换当前的参数
                    $reg = '/a1=\d+/i';
                    preg_match($reg,  $url["url"],$m);
                    $link = preg_replace($reg, "a1=".$value["id"],$url["url"]);
                }
            }else{
                 //替换当前的参数
                $reg = '/a1=\d+/i';
                $link = preg_replace($reg, "a1=".$value["id"],$url["url"]);
            }
            $result[$key]["link"] = $link;
        }
        return $result;
    }

    private function getFengge($limit,$isTop,$type,$params_count,$url){
        $result = D("Meitu")->getFengge($limit,$isTop);
        foreach ($result as $key => $value) {
            if(empty($type) && $params_count == 0){
                $link ="/meitu/list-l0f".$value["id"]."h0c0";
                $result[$key]["nofollow"] = 'follow';
            }elseif(!empty($type) && $params_count == 1){
                if($type == "fengge"){
                    //替换当前的参数
                    $reg = '/f\d+/i';
                    $link =preg_replace($reg, "f".$value["id"],$url["short_url"]);
                    $result[$key]["nofollow"] = 'follow';
                }else{
                    //替换当前的参数
                    $reg = '/a2=\d+/i';
                    preg_match($reg,  $url["url"],$m);
                    $link = preg_replace($reg, "a2=".$value["id"],$url["url"]);
                }
            }else{
                //替换当前的参数
                $reg = '/a2=\d+/i';
                $link = preg_replace($reg, "a2=".$value["id"],$url["url"]);
            }
            $result[$key]["link"] = $link;
        }
         return $result;
    }

    private function getHuxing($limit,$isTop,$type,$params_count,$url){
        $result = D("Meitu")->getHuxing($limit,$isTop);
        foreach ($result as $key => $value) {
            if(empty($type) && $params_count == 0){
                 $link ="/meitu/list-l0f0h".$value["id"]."c0";
                 $result[$key]["nofollow"] = 'follow';
            }elseif(!empty($type) && $params_count == 1){
                if($type == "huxing"){
                    //替换当前的参数
                    $reg = '/h\d+/i';
                    $link =preg_replace($reg, "h".$value["id"],$url["short_url"]);
                    $result[$key]["nofollow"] = 'follow';
                }else{
                    //替换当前的参数
                    $reg = '/a3=\d+/i';
                    preg_match($reg,  $url["url"],$m);
                    $link = preg_replace($reg, "a3=".$value["id"],$url["url"]);
                }
            }else{
                 //替换当前的参数
                $reg = '/a3=\d+/i';
                $link = preg_replace($reg, "a3=".$value["id"],$url["url"]);
            }
            $result[$key]["link"] = $link;
        }
         return $result;
    }

    private function getColor($limit,$isTop,$type,$params_count,$url){
        $result = D("Meitu")->getColor($limit,$isTop);
        foreach ($result as $key => $value) {
            if(empty($type) && $params_count == 0){
                 $link ="/meitu/list-l0f0h0c".$value["id"];
                 $result[$key]["nofollow"] = 'follow';
            }elseif(!empty($type) && $params_count == 1){
                if($type == "color"){
                    //替换当前的参数
                    $reg = '/c\d+/i';
                    $link =preg_replace($reg, "c".$value["id"],$url["short_url"]);
                    $result[$key]["nofollow"] = 'follow';
                }else{
                    //替换当前的参数
                    $reg = '/a4=\d+/i';
                    preg_match($reg,  $url["url"],$m);
                    $link = preg_replace($reg, "a4=".$value["id"],$url["url"]);
                }
            }else{
                 //替换当前的参数
                $reg = '/a4=\d+/i';
                $link = preg_replace($reg, "a4=".$value["id"],$url["url"]);
            }
            $result[$key]["link"] = $link;
        }
         return $result;
    }



    private function getDesignerImg($limit){
        $img = D("Meitu")->getDesignerImg($limit);
        return $img;
    }

    private function getMeituListByPart($type,$limit){
         $imgs = D("Meitu")->getMeituListByPart($type,$limit);
         foreach ($imgs as $key => $value) {
            //取每个分类的第一个元素
            $exp =array_filter(explode(",", $value["wz"]));
            $imgs[$key]["wz"] = $exp[0];

            $exp =array_filter(explode(",", $value["fg"]));
            $imgs[$key]["fg"] = $exp[0];

            $exp =array_filter(explode(",", $value["hx"]));
            $imgs[$key]["hx"] = $exp[0];

            $exp =array_filter(explode(",", $value["ys"]));
            $imgs[$key]["ys"] = $exp[0];
         }
         return $imgs;
    }

    private function getHotMeitu($limit){
        $imgs = D("Meitu")->getHotMeitu($limit);
        foreach ($imgs as $key => $value) {
            //取每个分类的第一个元素
            $exp =array_filter(explode(",", $value["wz"]));
            $imgs[$key]["wz"] = $exp[0];

            $exp =array_filter(explode(",", $value["fg"]));
            $imgs[$key]["fg"] = $exp[0];

            $exp =array_filter(explode(",", $value["hx"]));
            $imgs[$key]["hx"] = $exp[0];

            $exp =array_filter(explode(",", $value["ys"]));
            $imgs[$key]["ys"] = $exp[0];
        }
        return $imgs;
    }

    /**
     * [getMeituAttribute 获取美图属性的第一个属性的详细信息]
     * @param  [type] $meitu [description]
     * @return [type]        [description]
     */
    private function getMeituAttribute($meitu){
        $field = 'id,name';
        if(!empty($meitu['location'])){
            $location = M("meitu_location")->field($field)->where(['id' => explode(',', $meitu['location'])[0]])->find();
            if(!empty($location)){
                $location['href'] = '/meitu/list-l'.$location['id'].'f0h0c0';
                $result['location'] = $location;
            }
        }

        if(!empty($meitu['fengge'])){
            $fengge = M("meitu_fengge")->field($field)->where(['id' => explode(',', $meitu['fengge'])[0]])->find();
            if(!empty($fengge)){
                $fengge['href'] = '/meitu/list-l0f'.$fengge['id'].'h0c0';
                $result['fengge'] = $fengge;
            }
        }

        if(!empty($meitu['huxing'])){
            $huxing = M("meitu_huxing")->field($field)->where(['id' => explode(',', $meitu['huxing'])[0]])->find();
            if(!empty($huxing)){
                $huxing['href'] = '/meitu/list-l0f0h'.$huxing['id'].'c0';
                $result['huxing'] = $huxing;
            }
        }

        if(!empty($meitu['color'])){
            $color = M("meitu_color")->field($field)->where(['id' => explode(',', $meitu['color'])[0]])->find();
            if(!empty($color)){
                $color['href'] = '/meitu/list-l0f0h0c'.$color['id'];
                $result['color'] = $color;
            }
        }
        return $result;
    }

    /**
     * [getNavUrl 获取导航URL]
     * @param  [type] $datas [该类型下的所有类型]
     * @param  [type] $type  [静态参数和动态参数数组]
     * @param  [type] $urls  [当前页面去掉分页和动态参数之后的URL]
     * @param  [type] $multi [图片图集]
     * @return [type]        [description]
     */
    public function getNavUrl($datas, $type, $urls, $multi)
    {
        //去掉图集的参数当前的
        $pattern = '/q\d+/i';
        $urls['statics'] =preg_replace($pattern, '',$urls['statics'], $limit = -1,$count);
        $pattern = '/&q=\d+/i';
        $urls['dynamic'] =preg_replace($pattern, '',$urls['dynamic'], $limit = -1,$count);

        //判断是否带有参数a1
        if(!isset($_GET['a1'])){
            //去掉当前的
            $pattern = '/'.$type['statics'].'\d+/i';
            //去掉自己之后的链接
            $str =preg_replace($pattern, '',$urls['statics'], $limit = -1,$count);
            //如果去掉自己之后的分类数后分类ID组合小于等于零,说明是初始化
            if(preg_replace('/\D/s', '', $str) >0){
                $reg = '/'. $type['dynamic'] .'=\d+/i';
                foreach ($datas as $key => $value) {
                    $datas[$key]["link"] = preg_replace($reg, $type['dynamic']. '=' .$value["id"],$urls['dynamic']);
                    if ($multi == false) {
                        $datas[$key]["link"] = $datas[$key]["link"] . '&q=1';
                    }
                }
            }else{
                $reg = '/'.$type['statics'].'\d+/i';
                foreach ($datas as $key => $value) {
                    $datas[$key]["link"] = preg_replace($reg, $type['statics'].$value["id"],$urls['statics']);
                    $datas[$key]["nofollow"] = 'follow';
                    if ($multi == false) {
                        $datas[$key]["link"] = $datas[$key]["link"] . 'q1';
                    }
                }
            }
        }else{
            $reg = '/'. $type['dynamic'] .'=\d+/i';
            $page = '/&p=\d+/i';
            $urls['dynamic'] = preg_replace($page, '',$urls['dynamic']);
            foreach ($datas as $key => $value) {
                $datas[$key]["link"] = preg_replace($reg, $type['dynamic']. '=' .$value["id"],$urls['dynamic']);
                if ($multi == false) {
                    $datas[$key]["link"] = $datas[$key]["link"] . '&q=1';
                }
            }
        }
        return $datas;
    }

    /**
     * [getSelectedUrl 获取面包屑导航已选择条件]
     * @param  [type] $type  [类型]
     * @param  [type] $value [类型的值]
     * @param  [type] $url   [当前页面URL]
     * @param  [type] $count [条件个数]
     * @param  [type] $info  [description]
     * @return [type]        [description]
     */
    public function getSelectedUrl($type,$value,$url,$count,$info,$link='/meitu/list/')
    {
        //判断有几个参数，如果只有一个参数直接返回/meitu/list/
        $result = array();
        if($count <= 1){
            foreach ($info as $k => $v) {
                if($v['id'] == $value){
                    $result = array(
                                    'name' => $v['name'],
                                    'link' => $link
                                    );
                }
            }
        }else{
            foreach ($info as $k => $v) {
                if($v['id'] == $value){
                    $reg = '/'. $type .'=\d+/i';
                    $link = preg_replace($reg, $type ."=0",$url);
                    $result = array(
                                    'name' => $v['name'],
                                    'link' => $link
                                    );
                }
            }
        }
        return $result;
    }

    /**
     * [getMeiTuList 获取美图列表]
     * @param  integer $each    [每页显示数目]
     * @param  [type]  $keyword [搜索关键字]
     * @param  [type]  $multi   [单图还是套图]
     * @return [type]           [description]
     */
    private function getMeiTuList($each = 40, $keyword, $multi)
    {
        if ($multi == true) {
            $pageTemp = 'p';
            $isSingle = '0';
        } else {
            $pageTemp = 'q';
            $isSingle = '1';
        }
        import('Library.Org.Page.ShortPage');
        if($_GET['order'] == 'hot'){
            $order = '`likes` desc';
        }

        //获取单图的分页
        if(!isset($_GET['a1'])){
            $options = array(
                'prefix' => '/meitu/list-',
                'dynamic' => '/meitu/list/',
                'short' => array('l'=>'a1' ,'f'=> 'a2','h'=> 'a3','c'=> 'a4'),
                'sort' => array('l','f','h','c',$pageTemp)
            );
        }
        $Page = new \Page($each, $options, $sline = false, $dline = true, $p=$pageTemp);

        $Page->setConfig('theme','%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
        $result = $Page->analyse();

        if(array_key_exists('a1', $result['param'])){
            $params['l'] = $result['param']['a1'];
            $params['f'] = $result['param']['a2'];
            $params['h'] = $result['param']['a3'];
            $params['c'] = $result['param']['a4'];
        }else{
            $params = $result['param'];
        }
        $count = D("Meitu")->getMeiTuListCount($params["l"],$params["f"],$params["h"],$params["c"],$keyword,$isSingle);
        if($count > 0){
            $show = $Page->show($count);
            $list = D("Meitu")->getMeiTuList(($Page->nowPage-1)*$each,$each,$params["l"],$params["f"],$params["h"],$params["c"],$keyword,$isSingle,$order);

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
        return array("list"=>$list,"page"=>$show,"params"=>$params,'urls' => $result['urls'],'current' =>$Page->nowPage);
    }
}