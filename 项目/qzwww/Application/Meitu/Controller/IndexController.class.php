<?php

namespace Meitu\Controller;
use Meitu\Common\Controller\MeituBaseController;

class IndexController extends MeituBaseController{

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


        //导航栏标识
        $this->assign("tabIndex",2);

        //判断是否是搜索引擎蜘蛛
        $robotIsTrue = B("Common\Behavior\RobotCheck");
        //$robotIsTrue = true; //debug 警告线上应该为注释状态
        if (true === $robotIsTrue) {
            $this->assign('robot',1);
        }
        //添加顶部搜索栏信息
        $this->assign('serch_uri','meitu');
        $this->assign('serch_type','装修效果图');
        $this->assign('holdercontent','海量精选美图任你选');
        if(empty($this->cityInfo["bm"])){
            $t = T("Meitu@Index:header_two");
        }else{
            if(!$robotIsTrue){
                $t = T("Sub@Index:header");
            }
            //显示头部导航栏效果
            $this->assign("nav_show",true);
        }

        //添加选中效果 (只有首页不需要选中)
        $parse_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if ($parse_uri == '/list/') {
            $this->assign('choose_menu', 'list');
        }
        $pattern = '/(\\/p\\d+\\.html)$/';
        if (preg_match($pattern, $parse_uri )) {
            $this->assign('choose_menu', 'list');
        }
        $pattern = '/(list-[a-z0-9A-Z]+)/';
        if(preg_match($pattern, $parse_uri)) {
            $this->assign('choose_menu', 'list');
        }

        $headerTmp = $this->fetch($t);
        $this->assign("headerTmp",$headerTmp);
    }

    //美图首页
    public function index(){

        if (ismobile()) {
            header("Location: ".$this->SCHEME_HOST['scheme_full'] .C('MOBILE_DONAMES') . '/meitu/');
            exit();
        }

        //导航
        $info['nav'] = $this->getHomeNavMenu();

        //轮播
        $info['lunbo'] = S('Cache:Meitu:Index:lunbo');
        if (!$info['lunbo']) {
            $info['lunbo'] = D('Meitu')->getLunbo();
            S('Cache:Meitu:Index:lunbo', $info['lunbo'], 900);
        }

        //专题
        $info['zt'] = S('Cache:Meitu:Index:zt');
        if (!$info['zt']) {
            $info['zt'] = D('Special')->getList(array('status'=>1,'is_home'=>2),0,8)['result'];
            S('Cache:Meitu:Index:zt', $info['zt'], 900);
        }

        //获取首页 设计 户型 局部
        $meituList = S('Cache:Meitu:Index:meituList');
        if (!$meituList) {
            $result = D('Meitu')->getHomeMeituItemList();
            foreach ($result as $key => $value) {
                $meituList[$value['module']][$value['category']][] = $value;
            }

            S('Cache:Meitu:Index:meituList', $meituList, 900);
        }

        //获取首页 公装
        $pubmeituList = S('Cache:Meitu:Index:pubmeituList');
        if (!$pubmeituList) {
            $result = D('Meitu')->getHomePubMeituItemList();
            foreach ($result as $key => $value) {
                $pubmeituList[$value['category']][] = $value;
            }

            S('Cache:Meitu:Index:pubmeituList', $pubmeituList, 900);
        }

        //获取首页 3D
        $threeDList = S('Cache:Meitu:Index:threeDList');
        if (!$threeDList) {
            $threeDList = D('Meitu')->getHomeThreeDItemList();
            S('Cache:Meitu:Index:threeDList', $threeDList, 900);
        }

        $threeDBig = $threeDList['0'];
        unset($threeDList['0']);

        //获取首页 名师
        $mingshiList = S('Cache:Meitu:Index:mingshiList');
        if (!$mingshiList) {
            $mingshiList = D("Meitu")->getDesignerImg(5);
            S('Cache:Meitu:Index:mingshiList', $mingshiList, 900);
        }

        //seo 标题/描述/关键字
        $info["title"] = "装修效果图_".date("Y")."室内家装装饰设计效果图大全-齐装网装修效果图";
        $info["keywords"] = "装修效果图,装饰效果图,家装效果图,室内装修效果图大全,室内装修效果图,家装效果图大全";
        $info["description"] = "齐装网汇聚".date("Y")."国内外受欢迎的家庭装修效果图片，为您提供新室内装修装饰效果图大全以及丰富的家居设计美图，不一样的装修图片为您带来不一样的房屋装修灵感。找装修美图就上齐装网！";


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

        $this->assign('meituList',$meituList);
        $this->assign('pubmeituList',$pubmeituList);
        $this->assign('threeDList',$threeDList);
        $this->assign('threeDBig',$threeDBig);
        $this->assign('mingshiList',$mingshiList);
        $this->assign("info",$info);
        $this->display('index_p260');
    }

    //美图列表页
    public function meitulist(){
        //跳转到手机端
        if (ismobile()) {
            header( "HTTP/1.1 301 Moved Permanently");
            header("Location: ".$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . '/meitu/list/');
            exit();
        }

        /*S-SEO的canonical标签*/
        $patternq = '/q\d+/i';
        preg_replace($patternq, '',$_SERVER['REQUEST_URI'], -1,$countq);
        $patternp = '/p\d+/i';
        preg_replace($patternp, '',$_SERVER['REQUEST_URI'], -1,$countp);
        if($countp >0 || $countq >0){
            $info["noindex"] = '<meta name="robots" content="noindex,follow"/>';
        }
        /*E-SEO的canonical标签*/

        /* S 排序、单图、套图 Cookie 处理 */

        $orderBy = cookie('meitu_orderby');
        $info['orderby'] =  $orderBy;

        //如果没有设置 单图套图 Cookie
        $multi = cookie('meitu_multi');
        $this->assign("multi", $multi);

        //去除单图还是套图的筛选条件
        $single = 0;
        if(empty($multi) || $multi == 3){
            $single = 99;
        }

        $multi = $multi == '1' ? true : false;
        $info['multi'] =  $multi;


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
        $meitu = $this->getMeiTuList($each, $keyword, $multi, $single);

        //add-myx-start
        if (empty($info["meitu"])) {
            if (!empty($meitu["otherList"])) {
                $info["otherList"] = $meitu["otherList"];
                $info["other"] = 1;
            }
        }
        //add-myx-end
        $info["meitu"] = $meitu["list"];
        $info["page"] = $meitu["page"];
        /*S-导航条件筛选URL生成*/
        //获取导航栏局部短链接
        //第一个参数为该类型下的全部类型，传入当前链接动态参数和静态参数，对对应的参数逐一替换
        $isTop = false;

        //获取导航栏局部短链接
        $info["wz"] = S('Cache:Meitu:Index:location');
        if (!$info["wz"]) {
            $location = D("Meitu")->getLocation($limit,$isTop);
            $info["wz"] = $this->getStaticNavUrl($location,array('statics' => 'l','dynamic' => 'a1'),$meitu['urls'],$multi);
            S('Cache:Meitu:Index:location', $info["wz"], 900);
        }

        //获取导航栏风格短链接
        $info["fg"] = S('Cache:Meitu:Index:fengge');
        if (!$info["fg"]) {
            $fengge = D("Meitu")->getFengge($limit,$isTop);
            $info["fg"] = $this->getStaticNavUrl($fengge,array('statics' => 'f','dynamic' => 'a2'),$meitu['urls'],$multi);
            S('Cache:Meitu:Index:fengge', $info["fg"], 900);
        }

        //获取导航栏户型短链接
        $info["hx"] = S('Cache:Meitu:Index:huxing');
        if (!$info["hx"]) {
            $huxing = D("Meitu")->getHuxing($limit,$isTop);
            $info["hx"] = $this->getStaticNavUrl($huxing,array('statics' => 'h','dynamic' => 'a3'),$meitu['urls'],$multi);
            S('Cache:Meitu:Index:huxing', $info["hx"], 900);
        }

        //获取导航栏颜色短链接
        $info["ys"] = S('Cache:Meitu:Index:color');
        if (!$info["ys"]) {
            $color = D("Meitu")->getColor($limit,$isTop);
            $info["ys"] = $this->getStaticNavUrl($color,array('statics' => 'c','dynamic' => 'a4'),$meitu['urls'],$multi);
            S('Cache:Meitu:Index:color', $info["ys"], 900);
        }

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
                    $sub = $this->getStaticSelectedUrl('l',$value,$meitu["urls"]['statics'],$count,$info["wz"],$multi);
                    $info["params"]['location'] = $sub;
                    break;
                case in_array($key, array('f','a2')):
                    $key = 'fengge';
                    $sub = $this->getStaticSelectedUrl('f',$value,$meitu["urls"]['statics'],$count,$info["fg"],$multi);
                    $info["params"]['fengge'] = $sub;
                    break;
                case in_array($key, array('h','a3')):
                    $key = 'huxing';
                    $sub = $this->getStaticSelectedUrl('h',$value,$meitu["urls"]['statics'],$count,$info["hx"],$multi);
                    $info["params"]['huxing'] = $sub;
                    break;
                case in_array($key, array('c','a4')):
                    $key = 'color';
                    $sub = $this->getStaticSelectedUrl('c',$value,$meitu["urls"]['statics'],$count,$info["ys"],$multi);
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
                $keys["description"] = "齐装网" . $tdk. "装修效果图专区，提供".date("Y")."流行的" . $tdk. "装修效果图以及" . $tdk. "装修样板案例。更多精美" . $tdk. "图片，尽在齐装网" . $tdk. "效果图专区。";
            }
        }
        $this->assign("keys",$keys);
        $info["params"] = array_filter($info["params"]);
        /*E—SEO标题关键字描述相关*/

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
            //将类如/list-l0f0h0c0p1的p1去掉
            $pattern = '/^\/list-(l[\d+]+f[\d+]+h[\d+]+c[\d+]+)/';
            $result = preg_replace($pattern, '', $_SERVER['REQUEST_URI'],'-1',$count);
            if(empty($result) || $count == 0){
                if($count == 0){
                    $canonical = '/list/';
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
                        $canonical = '/list/';
                    } else {
                        $canonical = $meitu['urls']['statics'];
                    }
                }else{
                    $pattern = '/^q[\d+]+/';
                    preg_match($pattern, $result, $matche);
                    if(!empty($matche['0'])){
                        $canonical = $meitu['urls']['statics'];
                    }
                }
            }
            $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'] . 'meitu.'.C('QZ_YUMING').$canonical;
        }

        //图片图集切换URL
        $statics = $meitu['urls']['statics'];
        if ('/list-l0f0h0c0' == $statics && false == $multi) {
            $statics = '/list/';
        }
        $this->assign("statics", $statics);

        //指向移动端的信息
        if("/list/" == $_SERVER["REQUEST_URI"]){
            $info["mobileAgent"] = "/list/";
        }

        $pattern = '/^\/list-(l[\d+]+f[\d+]+h[\d+]+c[\d+])$/';
        $i = preg_match($pattern, $_SERVER['REQUEST_URI']);
        if($i > 0){
            $info["mobileAgent"] = $_SERVER['REQUEST_URI'];
        }

        //Ajax 输出
        if(IS_AJAX){
            foreach ($info['meitu'] as $k => $v) {
                echo '<li class="pic">
                <div class="item-bd">
                    <div class="img-box">
                        <img class="lazy" src="' . $this->SCHEME_HOST['scheme_full'] .C('QINIU_DOMAIN').'/'.$v['img_path'].'-w300.jpg">
                    </div>
                    <div class="btn-fd">
                         <span class="btn-sheji">我要装修成这样</span><span class="btn-baojia">装修成这样花多少钱</span>
                    </div>
                    <div class="item-mark"><a href="/p'.$v['id'].'.html" target="_blank"><span>'.$v['wz'].' '.$v['fg'].' '.$v['hx'].' '.$v['ys'].' </span></a></div>
                </div>
                <div class="item-ft">
                    <p class="item-ft-tit"></p>
                    <p class="item-ft-info"><span><a href="/p'.$v['id'].'.html" target="_blank">'.$v['title'].'</a></span><span class="fr"><i class="fa fa-eye"></i> '.$v['pv'].'</span></p>
                </div>
                </li>';
            }
            die;
        }


        /*S-底部设计浮动框*/
        $t = T("Common@Order/zb_bottom_s");
        $zb_bottom_s = $this->fetch($t);
        $this->assign("zb_bottom_s",$zb_bottom_s);
        //获取是否显示获取报价弹层
        if(!isset($_COOKIE["w_index"])){
            setcookie("w_index",1,time()+(3600*24),'/', '.'.APP_HTTP_DOMAIN);
            $this->assign("openSJBJ",true);
        }
        /* end */

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
            $friendLink['link'] = S('Cache:Meitu:Index:Friendlink:link-' . $type);
            if (!$friendLink['link']) {
                if(!empty($type)){
                    $friendLink['link'] = D('Friendlink')->getFriendLinkList('000001','1',$type);
                }elseif('/list/' == $_SERVER['REQUEST_URI']){
                    $friendLink['link'] = D('Friendlink')->getFriendLinkList('000001','1','meitu-list');
                }
                S('Cache:Meitu:Index:Friendlink:link-' . $type, $friendLink['link'], 900);
            }
        }

        $friendLink['tags'] = S('Cache:Meitu:Index:Friendlink:tags-' . $type);
        if (!$friendLink['tags']) {
            if(!empty($type)){
                $friendLink['tags'] = D('Friendlink')->getFriendLinkList('000001','3',$type);
            }elseif('/list/' == $_SERVER['REQUEST_URI']){
                $friendLink['tags'] = D('Friendlink')->getFriendLinkList('000001','3','meitu-list');
            }
            S('Cache:Meitu:Index:Friendlink:tags-' . $type, $friendLink['tags'], 900);
        }

        //add-myx-start
        $otherText = [];
        foreach ($info['params'] as $val) {
            array_push($otherText, $val['name']);
        }
        $otherText = implode('/', $otherText);
        substr($otherText, 0, -1);
        $info["otherText"] = $otherText;
        if(empty($info['meitu'])){
            $info['page'] = '';
        }
        //add-myx-end
        $this->assign('friendLink',$friendLink);

        $this->assign("info",$info);
        $this->display("list_p260");
    }

    //美图详情页
    public function caseinfo(){
        if (ismobile()) {
            header("Location: ".$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . '/meitu'.$_SERVER['REQUEST_URI']);
            exit();
        }
        $p = intval(I("get.p"));
        
        //判断来源
        $referer = parse_url($_SERVER['HTTP_REFERER'])['path'];

        $params = array();
        if (!empty($referer)) {
            $match = array();
            if (1 === preg_match('/list-(l([\d+]+)f([\d+]+)h([\d+]+)c([\d+]+)(p[\d+]+)?+(q[\d+]+)?)$/', $referer, $match)) {
                $params = array(
                    'location' => intval($match['2']),
                    'fengge' => intval($match['3']),
                    'huxing' => intval($match['4']),
                    'color' => intval($match['5']),
                    'tags' => 0
                );
            } else if ('/' === $referer) {
                $temp = explode(',', cookie('index_meitu_params'));
                if (array_sum($temp) > 0 && 3 == count($temp)) {
                    $params = array(
                        'location' => intval($temp['0']),
                        'fengge' => intval($temp['1']),
                        'huxing' => intval($temp['2']),
                        'color' => 0,
                        'tags' => 0
                    );
                }
            } else if (1 === preg_match('/p(\d+)\.html/', $referer, $match)) {
                $temp = json_decode(cookie('meitu_terminal_params'));

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

        cookie('meitu_terminal_params', null);
        //如果来自美图推荐列表页
        if(intval(I("get.type") == 1)){
            $params = [];
        }
        //美图缓存
        $cacheKey = $p . md5(serialize($params));
        $info = S("Cache:Meitu:Index:caseinfo:info:". $cacheKey);

        if(!$info){
            //查询美图案例信息
            $map = array(
                'id' => intval($p)
            );
            $info["case"]["now"] = $this->getMeituInfo($map, $params);

            if(empty($info["case"]["now"])){
                $this->_error();
            }
            $info["case"]['attribute'] = $this->getMeituAttribute($info["case"]['now']);
            //循环将每张图片的描述格式化
            if($info["case"]['now']['child']){
                $info["case"]['now']['imgdescription'] = html_entity_decode($info["case"]['now']['imgdescription']);
                foreach ($info["case"]['now']['child'] as $k=>$v){
                    if($v['imgdescription']){
                        $info["case"]['now']['child'][$k]['imgdescription'] = html_entity_decode($v['imgdescription']);
                    }
                }
            }
            S("Cache:Meitu:Index:caseinfo:info:".$cacheKey, $info,3600);
        }

        //相关美图缓存
        $relation = S('Meitu:Index:caseInfo:relation:'. $p);
        if (empty($relation)) {
            $relation["relatedMeitu"] = $this->getRelatedMeitu($info["case"]['now']);
            S('Meitu:Index:caseInfo:relation:' . $p, $relation);
        }
        $info = array_merge($info, $relation);

        //公用缓存
        $other = S('Meitu:Index:caseInfo:other');
        if (empty($other)) {
            //获取标签
            $other["newTags"] = D("Common/Tags")->getHotTags('2','15');
            //获取推荐局部信息
            $other["wz"] = $this->getLocation("","","",0, "");
            //unset掉不需要的字段,以免缓存文件过大
            foreach ($other["wz"] as $key => $value) {
                unset($other["wz"][$key]['title']);
                unset($other["wz"][$key]['keywords']);
                unset($other["wz"][$key]['description']);
                unset($other["wz"][$key]['time']);
            }
            //获取风格
            $other["fg"] = $this->getFengge("","","",0, "");
            //unset掉不需要的字段,以免缓存文件过大
            foreach ($other["fg"] as $key => $value) {
                unset($other["fg"][$key]['title']);
                unset($other["fg"][$key]['keywords']);
                unset($other["fg"][$key]['description']);
                unset($other["fg"][$key]['time']);
            }
            //获取户型
            $other["hx"] = $this->getHuxing("","","",0, "");
            //unset掉不需要的字段,以免缓存文件过大
            foreach ($other["hx"] as $key => $value) {
                unset($other["hx"][$key]['title']);
                unset($other["hx"][$key]['keywords']);
                unset($other["hx"][$key]['description']);
                unset($other["hx"][$key]['time']);
            }
            //获取颜色信息
            $other["ys"] = $this->getColor("","","",0, "");
            //unset掉不需要的字段,以免缓存文件过大
            foreach ($other["ys"] as $key => $value) {
                unset($other["ys"][$key]['title']);
                unset($other["ys"][$key]['keywords']);
                unset($other["ys"][$key]['description']);
                unset($other["ys"][$key]['color']);
                unset($other["ys"][$key]['time']);
            }
            S('Meitu:Index:caseInfo:other', $other);
        }
        $info = array_merge($info, $other);

        //查看推荐图集
        $cache_recommend_key_postfix = md5($info['case']['attribute']['location']['id'].'-'.$info['case']['attribute']['fengge']['id'].'-'.$info['case']['attribute']['huxing']['id']); // 推荐图集缓存key后缀
        $info['recommend'] = S('Cache:Meitu:Case:Recommend:' . $cache_recommend_key_postfix);

        if (empty($info['recommend'])) {
            $info['recommend'] = D('Meitu')->getRecommendMeituByAttr($info['case']['attribute']['location']['id'], $info['case']['attribute']['fengge']['id'], $info['case']['attribute']['huxing']['id']);
            //如果数据不够10条 , 取最新的数据
            $count = count($info['recommend']);
            if ($count < 10) {
                $data = D('Meitu')->getRecommendMeituByAttr('', '', '', '', (10 - $count), 'createtime desc');
                $info['recommend'] = array_merge($info['recommend'], $data);
                unset($count, $data);
            }
            S('Cache:Meitu:Case:Recommend:' . $cache_recommend_key_postfix, $info['recommend'], 21600);
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

        //单图
        if($info['case']['now']['is_single'] == '1'){
            if($info["collect"]){
                $info['case']['now']['collect'] = $info['case']['now']['id'];
            }else{
                $info['case']['now']['collect'] = null;
            }
            //定义单图模版
            $template = 'caseinfo_single_p260';
            unset($info['case']['now']['child']);
            //定义要查询的前后图集数量
            $preNum = $nextNum = 7;
            //查询前面的单图（id越来越大）
            $singleCaseList['pre'] = D('Common/Meitu')->getSingleCases($p,'pre',$preNum,$params,$uid);
            krsort($singleCaseList['pre']);

            //上一页，下一页
            $imgList['pre'] = $imgList['next'] = array();

            //如果前面单图数量不足（id大于当前单图）
            if(count($singleCaseList['pre']) < $preNum){

                //需要查询的后面的单图数量
                $nextNum = $nextNum + ($preNum - count($singleCaseList['pre']));

                //查询后面的单图（id小于当前单图）
                $singleCaseList['next'] = D('Common/Meitu')->getSingleCases($p,'next',$nextNum,$params,$uid);

                //如果前面单图数量为空
                if (empty($singleCaseList['pre'])) {
                    //如果后面单图也为空（前后都为空）
                    if (empty($singleCaseList['next'])) {
                        //上一页取当前页
                        $imgList['pre'] = $info['case']['now'];
                    } else {
                        //如果后面单图数量不足（说明所有符合条件的图片都查出来了，上一页就取后面图集（前面的为空了）的最后一个）
                        if (count($singleCaseList['next']) < $nextNum) {
                            $imgList['pre'] = $singleCaseList['next'][count($singleCaseList['next']) - 1];
                        } else {
                            //该条件下的第一张单图
                            $temp = D("Meitu")->getFirstOrLastMeitu("first", $params, 1);
                            if (!empty($temp)) {
                                $imgList['pre'] = array(
                                    'id' => $temp['id'],
                                    'title' => $temp['title'],
                                    'time' => $temp['time'],
                                    'likes' => $temp['likes'],
                                    'img_path' => $temp['child']['0']['img_path'],
                                    'img_host' => $temp['child']['0']['img_host'],
                                    'imgdescription' => $temp['child']['0']['imgdescription'],
                                    'top_title' => $temp['title'] . '-齐装网装修效果图'
                                );
                            }
                        }
                    }
                //如果前面单图数量不为空
                } else {
                    //如果后面单图为空(说明当前单图是ID最小)，上一页取前面的图集
                    if (empty($singleCaseList['next'])) {
                        $imgList['pre'] = $singleCaseList['pre'][0];
                    } else {
                        //如果后面单图数量不足（说明所有符合条件的图片都查出来了，上一页就取前面图集的第一个）
                        if (count($singleCaseList['next']) < $nextNum) {
                            $imgList['pre'] = $singleCaseList['pre'][0];
                        } else {
                            //该条件下的第一张单图
                            $temp = D("Meitu")->getFirstOrLastMeitu("first", $params, 1);
                            if (!empty($temp)) {
                                $imgList['pre'] = array(
                                    'id' => $temp['id'],
                                    'title' => $temp['title'],
                                    'time' => $temp['time'],
                                    'likes' => $temp['likes'],
                                    'img_path' => $temp['child']['0']['img_path'],
                                    'img_host' => $temp['child']['0']['img_host'],
                                    'imgdescription' => $temp['child']['0']['imgdescription'],
                                    'top_title' => $temp['title'] . '-齐装网装修效果图'
                                );
                            }
                        }
                    }
                }
            }else{
                $singleCaseList['next'] = D('Common/Meitu')->getSingleCases($p,'next',$nextNum,$params,$uid);
                //上一个单图
                $tmp_preid = count($singleCaseList['pre']) -1 ;
                $imgList['pre'] = $singleCaseList['pre'][$tmp_preid];
                unset($singleCaseList['pre'][$tmp_preid]);
            }

            //循环将 上一页的 每张图片的描述格式化
            if($singleCaseList['pre']){
                foreach ($singleCaseList['pre'] as $k=>$v){
                    if($v['imgdescription']){
                        $singleCaseList['pre'][$k]['imgdescription'] = html_entity_decode($v['imgdescription']);
                    }
                }
            }
            //循环将 下一页的 每张图片的描述格式化
            if($singleCaseList['next']){
                foreach ($singleCaseList['next'] as $k=>$v){
                    if($v['imgdescription']){
                        $singleCaseList['next'][$k]['imgdescription'] = html_entity_decode($v['imgdescription']);
                    }
                }
            }

            //如果下一单图数量不足（id小于当前单图）
            if(count($singleCaseList['next']) < $nextNum){
                $imgList['next'] = array();
                //取该参数条件下的最后一个单图
                $temp = D("Meitu")->getFirstOrLastMeitu("last", $params, 1);
                if (!empty($temp)) {
                    $imgList['next'] = array(
                        'id' => $temp['id'],
                        'title' => $temp['title'],
                        'time' => $temp['time'],
                        'likes' => $temp['likes'],
                        'img_path' => $temp['child']['0']['img_path'],
                        'img_host' => $temp['child']['0']['img_host'],
                        'imgdescription' => $temp['child']['0']['imgdescription'],
                        'top_title' => $temp['title'] . '-齐装网装修效果图'
                    );
                }
            } else {
                $tmp_lastid = count($singleCaseList['next']) - 1;
                $imgList['next'] = $singleCaseList['next'][$tmp_lastid];
                unset($singleCaseList['next'][$tmp_lastid]);
            }

            $newSingle = array_merge($singleCaseList['pre'],$singleCaseList['next']);
            array_unshift($newSingle,$info['case']['now']);
            $this->assign('singleCaseList',$newSingle);
            $this->assign('imgList',$imgList);
        } else {
            //套图
            $template = 'caseinfo_p260';

            $info['case']['prv'] = S('C:Meitu:Index:caseInfo:prv:' . $cacheKey);
            if (empty($info['case']['prv'])) {
                //上一个图集（id大于当前图集）
                $map = array(
                    'id' => array('GT', $p),
                    'is_single' => 0
                );
                $info['case']['prv'] = $this->getMeituInfo($map, $params, 'asc');
                //如果上一个图集（id越来越大）为空，则获取第一个图集
                if(empty($info['case']['prv'])){
                    $info['case']['prv'] = D("Meitu")->getFirstOrLastMeitu("first", $params, 0);
                }
                S('C:Meitu:Index:caseInfo:prv:' . $cacheKey, $info['case']['prv'], 900);
            }

            $info['case']['next'] = S('C:Meitu:Index:caseInfo:next:' . $cacheKey);
            if (empty($info['case']['next'])) {
                //下一个图集（id小于当前图集）
                $map = array(
                    'id' => array('LT', $p),
                    'is_single' => 0
                );
                $info['case']['next'] = $this->getMeituInfo($map, $params, 'desc');
                //如果下一个图集（id越来越小）为空，则获取最后一个图集
                if(empty($info['case']['next'])){
                    $info['case']['next'] = D("Meitu")->getFirstOrLastMeitu("last", $params, 0);
                }
                S('C:Meitu:Index:caseInfo:next:' . $cacheKey, $info['case']['next'], 900);
            }
        }

        //seo 标题/描述/关键字
        $keys["title"] = $info["case"]["now"]["title"]."-齐装网装修效果图";
        $keys["keywords"] = $info["case"]["now"]["title"];
        $keys["description"] = '齐装网装修效果图频道，提供'.date('Y').'流行的'.$info["case"]["now"]["title"].'，定期更新上百套'.$info["case"]["now"]["title"].'，为您带来精彩的装修设计灵感。';
        $this->assign("keys",$keys);

        //获取是否显示获取报价弹层
        if(!isset($_COOKIE["meitu_tips"])){
            $this->assign("isMeituTip",true);
        }

        //流量部推广统计
        $this->promoStats($p);

        //判断是否是搜索引擎蜘蛛
        $robotIsTrue = B("Common\Behavior\RobotCheck");
        //导入扩展文件
        if($robotIsTrue === false){
            import('Library.Org.Util.App');
            $app = new \App();
            $ip = $app->get_client_ip();
            if($ip != '223.112.69.58'){
                $expireTime = strtotime(date('Y-m-d').' 23:59:59') - time();
                $status = S('Cache:Meitu:'.$p.':'.$ip);
                if($status != 1){
                    D('Meitu')->updateRealView($p);
                    S('Cache:Meitu:'.$p.':'.$ip,1,$expireTime);
                }
            }
        }

//        dump($info);die;
        $this->assign("caseInfo",$info);
        $this->assign("relatedMeitu",$info["relatedMeitu"]);
        $this->assign("html_type", 'jiazhuang');
        $this->assign("params", json_encode(array_values($params)));
        $this->display($template);
    }

    //美图名师列表
    public function mingshi(){
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
            $keys["description"] = "齐装网室内设计师频道，汇集了国内外知名室内设计师众多作品，是业主及室内设计师搜集设计灵感的选择之地。";
        }else{
            $keys["title"] = $dname . "设计作品 - 齐装网装修效果图";
            $keys["keywords"] = $dname . "，设计师，室内设计";
            $keys["description"] = "齐装网名设计师频道提供设计师".$dname."的全新设计作品展示。";
            $this->assign("dname",$dname);
        }

        $this->assign("keys",$keys);
        //导航栏标识
        $this->assign("tabIndex",3);
        $this->assign("info",$info);
        $this->display('mingshi_p260');
    }

    //列表页URL跳转
    private function redirectUrl(){

        $order = I('get.order');
        if(!empty($order)){
            $this->setListCookie($order);
            $url = str_replace('?order='.$order,'',$_SERVER['REQUEST_URI']);
            $url = $this->SCHEME_HOST['scheme_full'] . 'meitu.'.C('QZ_YUMING').$url;
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:".$url);
            die();
        }

        //图集 1 图片 2
        $isMulti = stripos($_SERVER['REQUEST_URI'],'q1');
        if($isMulti != false){
            $this->setListCookie('',2);
            $url = str_replace('q1','',$_SERVER['REQUEST_URI']);
            $url = $this->SCHEME_HOST['scheme_full'] . 'meitu.'.C('QZ_YUMING').$url;
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:".$url);
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
            '/list-l0f0h0c0'  => '/list/',
        );
        if(array_key_exists($_SERVER['REQUEST_URI'], $redirectArray)){
            $url = $this->SCHEME_HOST['scheme_full'] . 'meitu.'.C('QZ_YUMING').$redirectArray[$_SERVER['REQUEST_URI']];
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:".$url);
            die();
        }
        /*E-链接进行301跳转*/

        /*S-将'/list-l0f0h0c0p1/'或者'/list-l0f0h0c0q1/ 的重定向到不带/的*/
        $pattern = '/^\/list-(l[\d+]+f[\d+]+h[\d+]+c[\d+]+(p{1}[\d+]+)?)+(q{1}[\d+]+)?\/$/';
        $i = preg_match($pattern, $_SERVER['REQUEST_URI']);
        if($i > 0){
            $redirect = rtrim($_SERVER['REQUEST_URI'], '/');
            $url = $this->SCHEME_HOST['scheme_full'] . 'meitu.'.C('QZ_YUMING').$redirect;
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:".$url);
            die();
        }
        /*E-将'/list-l0f0h0c0p1/'或者'/list-l0f0h0c0q1/ 的重定向到不带/的*/

        /*S-废除原有?p=1和q=1的链接，比如/list-l5f0h0c0?p=2的，全部跳转到静态url*/
        if(!empty($_GET['p']) && !isset($_GET['a1'])){
            $url = $this->SCHEME_HOST['scheme_full'] . 'meitu.'.C('QZ_YUMING').'/list-l0f0h0c0p'.I('get.p');
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:".$url);
            die();
        }
        if(!empty($_GET['q']) && !isset($_GET['a1'])){
            $url = $this->SCHEME_HOST['scheme_full'] . 'meitu.'.C('QZ_YUMING').'/list-l0f0h0c0q'.I('get.q');
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:".$url);
            die();
        }
        /*E-废除原有?p=1和q=1的链接，比如/list-l5f0h0c0?p=2的，全部跳转到静态url*/

        /*S-跳转到手机端*/
        if (ismobile()) {
            $mobile = '/^\/list-(l[\d+]+f[\d+]+h[\d+]+c[\d+])$/';
            if (preg_match($mobile, $_SERVER['REQUEST_URI']) > 0) {
                header("Location: ".$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') .'/meitu'. $_SERVER['REQUEST_URI']);
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
            $url = $this->SCHEME_HOST['scheme_full'] . 'meitu.'.C('QZ_YUMING').'/list-';
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
    }

    //首页导航菜单
    private function getHomeNavMenu(){

        $homeNav = S('Cache:Meitu:HomeNav');

        if(!$homeNav){

            $more = array('id' => 999,'name' => '更多','link' => '/list');

            //局部
            $info["location"] = $this->getLocation(15,false);
            $hide['location'] = array_slice($info["location"], 6);
            $info["location"] = array_slice($info["location"], 0, 6);
            if(count($hide['location']) == 9){
                $hide['location'][] = $more;
            }

            //风格
            $info["fengge"] = $this->getFengge(15,false);
            $hide['fengge'] = array_slice($info["fengge"], 6);
            $info["fengge"] = array_slice($info["fengge"], 0, 6);
            if(count($hide['fengge']) == 9){
                $hide['fengge'][] = $more;
            }

            //户型
            $info["huxing"] = $this->getHuxing(15,false);
            $hide['huxing'] = array_slice($info["huxing"], 6);
            $info["huxing"] = array_slice($info["huxing"], 0, 6);
            if(count($hide['huxing']) == 9){
                $hide['huxing'][] = $more;
            }

            //颜色
            $info["color"] = $this->getColor(15,false);
            $hide['color'] = array_slice($info["color"], 6);
            $info["color"] = array_slice($info["color"], 0, 6);
            if(count($hide['color']) == 9){
                $hide['color'][] = $more;
            }


            //公装
            $info["public"] = $this->getPublic(16,false);
            $hide['public'] = array_slice($info["public"], 6);
            $info["public"] = array_slice($info["public"], 0, 6);
            if(count($hide['public']) == 9){
                $hide['public'][] = $more;
            }
            $homeNav = array('list' => $info,'more' => $hide);

            S('Cache:Meitu:HomeNav',$homeNav,900);
        }
        return $homeNav;
    }

    //设置筛选Cookie
    public function setSelectCookie(){

        $module = I('get.module');

        if($module == 'meitu'){
            /* S 排序、单图、套图 Cookie 处理 */
            $orderBy = I('get.order');
            if(!empty($orderBy)){
                cookie('meitu_orderby',$orderBy);
            }
            //如果没有设置 单图套图 Cookie
            $multi = I('get.multi');
            if(!empty($multi)){
                cookie('meitu_multi',$multi);
            }
        }

        if($module == 'pubmeitu'){
            $orderBy = I('get.order');
            if(!empty($orderBy)){
                cookie('pubmeitu_orderby',$orderBy);
            }
            $multi = I('get.multi');
            if(!empty($multi)){
                cookie('pubmeitu_multi',$multi);
            }
        }
    }

    //设置筛选Cookie
    public function setListCookie($orderBy='',$multi=''){
        if(!empty($orderBy)){
            cookie('meitu_orderby',$orderBy);
        }
        if(!empty($multi)){
            cookie('meitu_multi',$multi);
        }
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

    //流量部推广统计
    public function promoStats($id){
        //获取Cookie
        $isMark = cookie('contentPromoMark');

        //如果Cookie不存在
        if(empty($isMark['module'])){
            //过期时间 = 今天最后一秒时间戳 - 当前时间戳
            $expireTime = strtotime(date('Y-m-d').' 23:59:59') - time();
            $cookieVar = array('module' => 'meitu','id' => $id);
            //指定cookie保存时间
            cookie('contentPromoMark',$cookieVar, array('expire' => $expireTime,'domain' => '.'.APP_HTTP_DOMAIN));
        }
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
        $map['location'] = $info['location'];
        $map['is_single'] = 0;//只取图集(p.2.12.6)
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

    /**
     * 获取美图信息
     * @param  [type] $map    查询条件
     * @param  [type] $params 额外参数
     * @param  string $order  排序
     */
    private function getMeituInfo($map, $params, $order = 'asc'){
        $temp = D("Meitu")->getMeituInfo($map, $params, $order);
        $result = array();
        foreach ($temp as $key => $value) {
            if (!isset($result['id'])) {
                $result = $value;
            }
            $result["child"][] = $value;
            $result["count"] ++;
        }
        return $result;
    }

    private function getParams($type,$value,$count,$url,$data){
        foreach ($data as $k => $val) {
            if($value == $val["id"]){
                $sub = array(
                        "name" =>$val["name"]
                             );
                if($count == 1){
                    $sub["link"] = "/list/";
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
                 $link ="/list-l".$value["id"]."f0h0c0";
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
                $link ="/list-l0f".$value["id"]."h0c0";
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
                 $link ="/list-l0f0h".$value["id"]."c0";
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
                 $link ="/list-l0f0h0c".$value["id"];
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

    private function getPublic($limit, $isTop, $type, $params_count, $url)
    {
        $result = D("Meitu")->getPublic($limit, $isTop);
        foreach ($result as $key => $value) {
            $result[$key]["link"] = '/gongzhuang-l'.$value['id'].'f0m0' ;
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
                $location['href'] = '/list-l'.$location['id'].'f0h0c0';
                $result['location'] = $location;
            }
        }

        if(!empty($meitu['fengge'])){
            $fengge = M("meitu_fengge")->field($field)->where(['id' => explode(',', $meitu['fengge'])[0]])->find();
            if(!empty($fengge)){
                $fengge['href'] = '/list-l0f'.$fengge['id'].'h0c0';
                $result['fengge'] = $fengge;
            }
        }

        if(!empty($meitu['huxing'])){
            $huxing = M("meitu_huxing")->field($field)->where(['id' => explode(',', $meitu['huxing'])[0]])->find();
            if(!empty($huxing)){
                $huxing['href'] = '/list-l0f0h'.$huxing['id'].'c0';
                $result['huxing'] = $huxing;
            }
        }

        if(!empty($meitu['color'])){
            $color = M("meitu_color")->field($field)->where(['id' => explode(',', $meitu['color'])[0]])->find();
            if(!empty($color)){
                $color['href'] = '/list-l0f0h0c'.$color['id'];
                $result['color'] = $color;
            }
        }
        return $result;
    }

    /**
     * 获取导航URL
     * @param  [type] $datas [该类型下的所有类型]
     * @param  [type] $type  [静态参数和动态参数数组]
     * @param  [type] $urls  [当前页面去掉分页和动态参数之后的URL]
     * @param  [type] $multi [图片图集]
     * @return [type]        [description]
     */
    public function getStaticNavUrl($datas, $type, $urls, $multi)
    {
        //参数替换
        $pattern = '/'.$type['statics'].'\d+/i';
        foreach ($datas as $key => $value) {
            $datas[$key]["link"] = preg_replace($pattern, $type['statics'].$value["id"],$urls['statics']);
            $datas[$key]["nofollow"] = 'follow';
            if ($multi == false) {
                $datas[$key]["link"] = $datas[$key]["link"];
            }
        }
        return $datas;
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
                        $datas[$key]["link"] = $datas[$key]["link"];
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
     * 获取面包屑导航已选择条件
     * @param  [type] $type  [类型]
     * @param  [type] $value [类型的值]
     * @param  [type] $url   [当前页面URL]
     * @param  [type] $count [条件个数]
     * @param  [type] $info  [description]
     * @return [type]        [description]
     */
    public function getStaticSelectedUrl($type,$value,$url,$count,$info,$multi,$link='/list/')
    {
        $result = array();
        //图片URL添加上分页
        if (false == $multi) {
            foreach ($info as $k => $v) {
                if($v['id'] == $value  || $value == 0){   //此处增加不限类型判断
                    $reg = '/'. $type .'\d+/i';
                    $link = preg_replace($reg, $type ."0",$url);
                    $result = array(
                         'name' => $value == 0 ? '' : $v['name'],
                         'link' => $link
                    );
                }
            }
        } else {
            //判断有几个参数，如果只有一个参数直接返回/list/
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
                        $reg = '/'. $type .'\d+/i';
                        $link = preg_replace($reg, $type ."0",$url);
                        $result = array(
                                        'name' => $v['name'],
                                        'link' => $link
                                        );
                    }
                }
            }
        }
        return $result;
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
    public function getSelectedUrl($type,$value,$url,$count,$info,$link='/list/')
    {
        //判断有几个参数，如果只有一个参数直接返回/list/
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
    private function getMeiTuList($each = 40, $keyword, $multi, $single="")
    {
        if ($multi == true) {
            $pageTemp = 'p';
            $isSingle = '0';
        } else {
            $pageTemp = 'p';
            $isSingle = '1';
        }
        if($single == 99){
            $isSingle = '99';
        }

        import('Library.Org.Page.ShortPage');

        $orderby = cookie('meitu_orderby');
        if ($orderby == 'hot') {
            $order = '`likes` desc';
        }

        //获取单图的分页
        if (!isset($_GET['a1'])) {
            $options = array(
                'prefix' => '/list-',
                'dynamic' => '/list/',
                'short' => array('l' => 'a1', 'f' => 'a2', 'h' => 'a3', 'c' => 'a4'),
                'sort' => array('l', 'f', 'h', 'c', $pageTemp)
            );
        }
        $Page = new \Page($each, $options, $sline = false, $dline = true, $p = $pageTemp);

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

        //404判断
        if ( isset($params['l']) && isset($params['f']) && isset($params['h']) && isset($params['c'])){
            if ($params['l'] != 0){         //检测位置
                $mapl = [
                    'enabled' => ['EQ', 1],
                    'id'      => ['EQ', $params['l']],
                ];
                $meituLocation = D("MeituLocation")->where($mapl)->find();
                if (empty($meituLocation)){
                    $this->_empty();exit();
                }
            }
            if ($params['f'] != 0) {   //检测风格
                $mapf = [
                    'enabled' => ['EQ', 1],
                    'id'      => ['EQ', $params['f']],
                ];
                $meituFengge = D("MeituFengge")->where($mapf)->find();
                if (empty($meituFengge)){
                    $this->_empty();exit();
                }
            }
            if ($params['h'] != 0) {   //检测户型
                $maph = [
                    'enabled' => ['EQ', 1],
                    'id'      => ['EQ', $params['h']],
                ];
                $meituHuxing = D("MeituHuxing")->where($maph)->find();
                if (empty($meituHuxing)){
                    $this->_empty();exit();
                }
            }
            if ($params['c'] != 0) {   //检测颜色
                $mapc = [
                    'enabled' => ['EQ', 1],
                    'id'      => ['EQ', $params['c']],
                ];
                $meituColor = D("MeituColor")->where($mapc)->find();
                if (empty($meituColor)){
                    $this->_empty();exit();
                }
            }
        }

        $count = D("Meitu")->getMeiTuListCount($params["l"], $params["f"], $params["h"], $params["c"], $keyword, $isSingle);
        if ($count > 0) {
            $show = $Page->show($count);
            $list = D("Meitu")->getMeiTuList(($Page->nowPage - 1) * $each, $each, $params["l"], $params["f"], $params["h"], $params["c"], $keyword, $isSingle, $order);

            $list = $this->getOtherList($list);
            return array("list" => $list, "page" => $show, "params" => $params, 'urls' => $result['urls'], 'current' => $Page->nowPage);
        } else {
            //去掉色彩
            $count = D("Meitu")->getMeiTuListCount($params["l"], $params["f"], $params["h"], 0, $keyword, $isSingle);
            if ($count > 0) {
                $list = D("Meitu")->getMeiTuList(($Page->nowPage - 1) * $each, $each, $params["l"], $params["f"], $params["h"], 0, $keyword, $isSingle, $order, 1);
                $list = $this->getOtherList($list);

                return array("otherList" => $list, "other" => 1, "params" => $params, 'urls' => $result['urls'], 'current' => $Page->nowPage);

            } else {
                //去掉户型
                $count = D("Meitu")->getMeiTuListCount($params["l"], $params["f"], 0, 0, $keyword, $isSingle);
                if ($count > 0) {
                    $list = D("Meitu")->getMeiTuList(($Page->nowPage - 1) * $each, $each, $params["l"], $params["f"], 0, 0, $keyword, $isSingle, $order, 1);
                    $list = $this->getOtherList($list);
                    return array("otherList" => $list, "other" => 1, "params" => $params, 'urls' => $result['urls'], 'current' => $Page->nowPage);
                } else {
                    //去掉风格
                    $count = D("Meitu")->getMeiTuListCount($params["l"], 0, 0, 0, $keyword, $isSingle);
                    if ($count > 0) {
                        $list = D("Meitu")->getMeiTuList(($Page->nowPage - 1) * $each, $each, $params["l"], 0, 0, 0, $keyword, $isSingle, $order, 1);
                        $list = $this->getOtherList($list);
                        return array("otherList" => $list, "other" => 1, "params" => $params, 'urls' => $result['urls'], 'current' => $Page->nowPage);

                    } else {
                        //去掉局部
                        $count = D("Meitu")->getMeiTuListCount(0, 0, 0, 0, $keyword, $isSingle);
                        if ($count > 0) {
                            $list = D("Meitu")->getMeiTuList(($Page->nowPage - 1) * $each, $each, 0, 0, 0, 0, $keyword, $isSingle, $order, 1);
                            $list = $this->getOtherList($list);
                            return array("otherList" => $list, "other" => 1, "params" => $params, 'urls' => $result['urls'], 'current' => $Page->nowPage);

                        }
                    }
                }
            }
        }
    }

    //对推荐结果进行处理
    private function getOtherList($list){
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
        return $list;
    }

    /**
     * 通过城市名称获取cid
     * @return [type] [description]
     */
    public function getCidByCname(){
        $cname = I('get.city');
        if(!empty($cname)){
            $citys = D("Common/Quyu")->getCityIdByCityName(array($cname));
            if(!empty($citys)){
                cookie('iplookup',$citys['0']['cid'],86400 * 7);
                $this->ajaxReturn(array("data"=>"","info"=>$citys['0']['cid'],"status"=>1));
            }
        }
        $this->ajaxReturn(array("data"=>"","info"=>"","status"=>0));
    }

    public function getDetailsByAjax(){
        R("Home/Zxbj/getDetailsByAjax");
        die();
    }

    public function fb_order(){
        R("Common/Zbfb/fb_order");
        die();
    }

    //设置首页弹窗不显示的COOKIE
    public function dispatcher(){
        R("Common/Zbfb/dispatcher");
        die();
    }

    //设置首页弹窗不显示的COOKIE
    public function setwindowswitch(){
        R("Common/Zbfb/setwindowswitch");
        die();
    }

    public function getBJResult(){
        R("Common/Zbfb/getBJResult");
        die();
    }

    public function getBJData(){
        R("Home/Zxbj/getBJData");
        die();
    }

    //装修小贴士路由
    public function guide(){
        R("Common/Zbfb/guide");
        die();
    }

    public function hm(){
        R("Home/Index/hm");
        die();
    }

    public function login(){
        R("Common/Login/login");
        die();
    }

    public function loginin(){
        R("Common/Login/loginin");
        die();
    }

    //用户退出
    public function loginout(){
        R("Common/Login/loginout");
        die();
    }

    //用户收藏
    public function collect(){
        R("Common/Collect/setCollect");
        die();
    }

    //取消收藏
    public function cancelcollect(){
        R("Common/Collect/cancelcollect");
        die();
    }

    /**
     * 获取对应美图的
     * @return mixed
     */
    public function getMeituTags()
    {
        $id = I('post.id');
        $type = I('post.type') ? I('post.type') : 'pubmeitu';//请求类型
        switch ($type) {
            //家装美图
            case 'meitu':
                $data = D('Common/Meitu')->getMeituTags($id);
                $returnData['fg'] = ['name' => $data['fg'], 'href' => '/list-l0f' . $data['fg_id'] . 'h0c0'];
                $returnData['wz'] = ['name' => $data['wz'], 'href' => '/list-l' . $data['wz_id'] . 'f0h0c0'];
                $returnData['ys'] = ['name' => $data['ys'], 'href' => '/list-l0f0h0c' . $data['ys_id']];
                $returnData['hx'] = ['name' => $data['hx'], 'href' => '/list-l0f0h' . $data['hx_id'] . 'c0'];
                break;
            //公装美图
            case 'pubmeitu':
                $data = D('Common/Meitu')->getPubMeituTags($id);
                $returnData['fg'] = ['name' => $data['fg'], 'href' => '/gongzhuang-l0f' . $data['fg_id'] . 'm0/'];
                $returnData['wz'] = ['name' => $data['wz'], 'href' => '/gongzhuang-l' . $data['wz_id'] . 'f0m0/'];
                $returnData['mj'] = ['name' => $data['mj'], 'href' => '/gongzhuang-l0f0m' . $data['mj_id'] . '/'];
                $returnData['likes'] = isset($data['likes'])?$data['likes']:'';
                break;
        }
        if ($data) {
            $this->ajaxReturn(['status' => 1, 'info' => $returnData]);
        } else {
            $this->ajaxReturn(['status' => 0, 'info' => '']);
        }
    }
}