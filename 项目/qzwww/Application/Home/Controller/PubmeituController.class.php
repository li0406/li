<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class PubmeituController extends HomeBaseController{

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
        $this->assign('serch_uri','xgt');
        $this->assign('serch_type','案例');
        $this->assign('holdercontent','海量装修案例任你挑选');

        $t = T("Home@Index:header");
        $headerTmp = $this->fetch($t);
        $this->assign("headerTmp",$headerTmp);

    }

    //美图列表页
    public function pubMeituList(){
        /*S-SEO的canonical标签生成以及301跳转*/
        //废除原有?p=1 全部跳转到静态url
        if(!empty($_GET['p']) && !isset($_GET['a1'])){
            $url = $this->SCHEME_HOST['scheme_full'].C('QZ_YUMINGWWW').'/meitu/gongzhuang-l0f0m0p'.I('get.p');
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:".$url);
            die();
        }
        //将 '/meitu/gongzhuang-l0f0m0p1/' 或'/meitu/gongzhuang-l0f0m0q1/' 带有/的重定向到不带/的
        $pattern = '/^\/meitu\/gongzhuang-(l[\d+]+f[\d+]+m[\d+])+(p[\d+]+)?+(p[\d+]+)?\/$/';
        $i = preg_match($pattern, $_SERVER['REQUEST_URI']);
        if($i > 0){
            $redirect = rtrim($_SERVER['REQUEST_URI'], '/');
            $url = $this->SCHEME_HOST['scheme_full'].C('QZ_YUMINGWWW').$redirect;
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:".$url);
            die();
        }

        //跳转到手机端
        if (ismobile()) {
            $mobile = '/^\/meitu\/gongzhuang-(l[\d+]+f[\d+]+m[\d+])$/';
            if (preg_match($mobile, $_SERVER['REQUEST_URI']) > 0) {
                header("Location:  ".$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
                exit();
            }
        }

        $patternq = '/q\d+/i';
        preg_replace($patternq, '',$_SERVER['REQUEST_URI'], -1,$countq);
        $patternp = '/p\d+/i';
        preg_replace($patternp, '',$_SERVER['REQUEST_URI'], -1,$countp);
        if($countp >0 || $countq >0){
            $info["noindex"] = '<meta name="robots" content="noindex,follow"/>';
        }
        /*E-SEO的canonical标签生成以及301跳转*/

        /*S-判断访问的是单图还是套图*/
        //默认单图
        $multi = false;
        $pattern = '/^\/meitu\/gongzhuang-(l[\d+]+f[\d+]+m[\d+]+q[\d+])/';
        if (!empty($_GET['q']) || (preg_match($pattern, $_SERVER['REQUEST_URI'])) > 0) {
            $multi = true;
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

        //分配图片列表和分页
        $info["meitu"] = $meitu["list"];
        $info["page"] = $meitu["page"];

        /*S-导航条件筛选URL生成*/
        //获取导航栏局部短链接
        //第一个参数为该类型下的全部类型，传入当前链接动态参数和静态参数，对对应的参数逐一替换
        $location = D("Pubmeitu")->getPubMeituAttr('1');
        $info["wz"] = $this->getNavUrl($location,array('statics' => 'l','dynamic' => 'a1'),$meitu['urls'],$multi);
        //获取导航栏风格短链接
        $fengge = D("Pubmeitu")->getPubMeituAttr('2');
        $info["fg"] = $this->getNavUrl($fengge,array('statics' => 'f','dynamic' => 'a2'),$meitu['urls'],$multi);
        //获取导航栏户型短链接
        $mianji = D("Pubmeitu")->getPubMeituAttr('3');
        $info["mj"] = $this->getNavUrl($mianji,array('statics' => 'm','dynamic' => 'a3'),$meitu['urls'],$multi);
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
                case in_array($key, array('m','a3')):
                    $key = 'mianji';
                    $sub = $this->getSelectedUrl('a3',$value,$meitu["urls"]['dynamic'],$count,$info["mj"]);
                    $info["params"]['mianji'] = $sub;
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
            if ($i == 0 && $meitu['current'] == 1 && $info['multi'] == true) {
                $keys["title"] = "工装效果图册_工装设计效果图册_装修图册-齐装网装修效果图" . $tkd_page;
                $keys["keywords"] = "工装效果图册，工装设计效果图册，工装装修图册";
                $keys["description"] = "齐装网工装效果图专区，提供国内外全新的工装效果图册和工装设计装修图册，包括办公室、服装店、酒店、厂房、美容院等工装效果图大全。";
            }else if($i == 1){
                $keys["title"] = $only . '装修效果图_'. $only .'设计' . '-齐装网装修效果图' . $tkd_page;
                $keys["keywords"] = $only . '装修效果图，'. $only .'装修图片，'. $only .'工装图片';
                $keys["description"] = "齐装网".$only."装修效果图专区，提供国内外新流行的".$only."工装设计效果图，更多".$only."装修效果图片尽在齐装网装修效果图频道。";
            }else{
                $keys["title"] = "工装效果图_工装设计效果图-齐装网装修效果图" . $tkd_page;
                $keys["keywords"] = "工装效果图，工装效果图，工装设计效果图";
                $keys["description"] = "齐装网工装效果图专区，提供国内外全新的工装效果图和工装设计效果图，包括办公室、服装店、酒店、厂房、美容院等工装效果图大全。";
            }
        }
        /*
        if(!empty($keyword)){
            $this->assign("keyword",$keyword);
            $keys["title"] = "搜索结果页 - 齐装网";
            $keys["keywords"] = "";
            $keys["description"] = "";
        }else{
            if ($multi == true) {
                $keys["title"] = '工装效果图_工装设计效果图_装修图册第' . $meitu['current'] . '页-齐装网装修效果图';
                $keys["keywords"] = '工装效果图,工装设计效果图,工装装修图册';
                $keys["description"] = '齐装网工装效果图专区，提供国内外新的工装效果图和工装设计装修图册，包括办公室、服装店、酒店、厂房、美容院等工装效果图大全。';
            } else {
                $keys["title"] = '工装效果图_工装设计效果图第' . $meitu['current'] . '页-齐装网装修效果图';
                $keys["keywords"] = '工装效果图,工装设计效果图';
                $keys["description"] = '齐装网工装效果图专区，提供国内外新的工装效果图和工装设计效果图，包括办公室、服装店、酒店、厂房、美容院等工装效果图大全。';
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
        $linktypes = S('Home:Pubmeitu:FriendLinkCategory');
        if(empty($linktypes)){
            $linkcategory = D('FriendLinkCategory')->getFriendLinkCategoryList(['link_page' => ['like','gongzhuang%']]);
            foreach ($linkcategory as $key => $value) {
                if(!empty($value['link_page_url'])){
                    $linktypes[$value['link_page_url']] = $value['link_page'];
                }
            }
            S('Home:Pubmeitu:FriendLinkCategory',$linktypes,360000);
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
        if(trim($_SERVER['REQUEST_URI'],'/') == 'meitu/gongzhuang'){
            $type = 'gongzhuang';
        }
        if(!empty($type)){
            if($meitu['current'] == 1 && $meitu['current'] == 1){
                $friendLink['link'] = D('Friendlink')->getFriendLinkList('000001','1',$type);
            }
            $friendLink['tags'] = D('Friendlink')->getFriendLinkList('000001','3',$type);
            $this->assign('friendLink',$friendLink);
        }
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
            $pattern = '/^\/meitu\/gongzhuang-(l[\d+]+f[\d+]+m[\d+]+)/';
            $result = preg_replace($pattern, '', $_SERVER['REQUEST_URI'],'-1',$count);
            if(empty($result) || $count == 0){
                if($count == 0){
                    $canonical = '/meitu/gongzhuang/';
                }else{
                    $canonical = $meitu['urls']['statics'];
                }
            }else{
                $canonical = $meitu['urls']['statics'];
                $pattern = '/^p[\d+]+/';
                preg_match($pattern, $result, $matche);
                if(!empty($matche['0'])){
                    if ($i == 0) {
                        $canonical = '/meitu/gongzhuang/';
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
            $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'].C('QZ_YUMINGWWW').$canonical;
        }

        /*S-初始化图片图集Tab框*/
        $pattern = '/q\d+/i';
        preg_replace($pattern, '',$_SERVER['REQUEST_URI'], $limit = -1,$count);
        if(isset($_GET['q']) || $count >0){
            $info['tab'] = 'map';
        }
        /*E-初始化图片图集Tab框*/

        $this->assign("info",$info);
        //不可去掉pubmeitulist，否则会出现大小写问题
        $this->display("pubmeitulist");
    }


    //美图详情页
    public function pubMeituInfo(){
        if (ismobile()) {
            header("Location:  ".$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }
        $p = I("get.id");
        //取条件筛选Cookie
        $params = cookie('meitu_params');

        //$info = S("Cache:Meitu:PubMeituInfo:".$p);
        if(!$info){
            //查询美图案例信息
            $info["case"] = $this->getMeituInfo($p,$params);

            if(empty($info['case'])){
                $this->_error();
            }

            if(empty($info["case"]["now"])){
                $this->_error();
            }

            $info['case']['attribute'] = $this->getMeituAttribute($info["case"]['now']);

            //获取标签
            $newTags = D("Common/Tags")->getHotTags('2','15');
            $info["newTags"] = $newTags;

            //获取相关美图
            $info["relatedMeitu"] = $this->getRelatedMeitu($info["case"]['now']);
            S("Cache:Meitu:PubMeituInfo:".$p,$info,10800); //标签缓存3个小时
        }

        //获取小导航
        $info['attrhref'] = S('Home:PubMeitu:PubMeituInfo:Attrhref');
        if(empty($info['attrhref'])){
            $info['attrhref'] = $this->getMeituAttributeHref(1);
            S('Home:PubMeitu:PubMeituInfo:Attrhref',$info['attrhref'],86400);
        }

        //查看推荐图集
        $info['recommend'] = S('Cache:Pubmeitu:Case:Recommend:'.$info['case']['attribute']['location']['id'].':'.$info['case']['attribute']['fengge']['id']);
        if(empty($info['recommend'])){
            $info['recommend'] = D('Pubmeitu')->getRecommendMeituByAttr($info['case']['attribute']['location']['id'],$info['case']['attribute']['fengge']['id']);
            S('Cache:Pubmeitu:Case:Recommend:'.$info['case']['attribute']['location']['id'].':'.$info['case']['attribute']['fengge']['id'],$info['recommend'],21600);
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


            unset($info['case']['now']['child']);
            //查询单个图片前后图集
            $singleCaseList['pre'] = D('Pubmeitu')->getSingleCases($p,'pre',7,$params,$uid);
            $preNum = count($singleCaseList['pre']);
            krsort($singleCaseList['pre']);

            //如果前面小于9个
            if($preNum < 7){
                $nextNum = 7 + (6 - $preNum);
                $singleCaseList['next'] = D('Pubmeitu')->getSingleCases($p,'next',$nextNum,$params,$uid);
                $imgList['pre'] = '000000';
            }else{
                $singleCaseList['next'] = D('Pubmeitu')->getSingleCases($p,'next',7,$params,$uid);
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

            $template = 'caseinfo_single';
            $this->assign('singleCaseList',$newSingle);
            $this->assign('imgList',$imgList);
        }

        //seo 标题/描述/关键字
        $keys["title"] = $info["case"]["now"]["title"]."-齐装网装修效果图";
        $keys["keywords"] = $info["case"]["now"]["title"];
        $keys["description"] = '齐装网装修效果图频道，提供'.date('Y').'新'.$info["case"]["now"]["title"].'，定期更新上百套'.$info["case"]["now"]["title"].'，为您带来精彩的装修设计灵感。';
        $this->assign("keys",$keys);


        if(empty($info['case']['prv'])){
            $info['case']['prv'] = D("Pubmeitu")->getFirstMeitu("desc",$params);
        }
        if(empty($info['case']['next'])){
            $info['case']['next'] = D("Pubmeitu")->getFirstMeitu();
        }

        //获取是否显示获取报价弹层
        if(!isset($_COOKIE["meitu_tips"])){
            $this->assign("isMeituTip",true);
        }

        //设置canonical属性
        $header['canonical'] = $this->SCHEME_HOST['scheme_full'].C('QZ_YUMINGWWW').'/meitu/g'.$p.'.html';

        $this->assign("relatedMeitu",$info["relatedMeitu"]);
        $this->assign('header',$header);
        $this->assign("caseInfo",$info);
        $this->display($template);
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
            M("pubmeitu")->where(array('id' => $id))->setInc('likes');
            $this->ajaxReturn(array("data"=>"","info"=>"成功","status"=>1));
        }
    }


    private function getMeituInfo($id,$params){
        $meitu = D("Pubmeitu")->getMeituInfo($id,$params);
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

    //获取相关美图
    private function getRelatedMeitu($info){
        $map['fengge'] = $info['fengge'];
        $map['huxing'] = $info['huxing'];
        $map['is_single'] = $info['is_single'];
        $id = $info['id'];
        $result = D('Pubmeitu')->getRelatedMeitu($map,$id);
        return $result;
    }

    /**
     * [getNavUrl 获取导航URL]
     * @param  [type] $datas [该类型下的所有类型]
     * @param  [type] $type  [静态参数和动态参数数组]
     * @param  [type] $urls  [当前页面去掉分页和动态参数之后的URL]
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
                    if ($multi == true) {
                        $datas[$key]["link"] = $datas[$key]["link"] . '&q=1';
                    }
                }
            }else{
                $reg = '/'.$type['statics'].'\d+/i';
                foreach ($datas as $key => $value) {
                    $datas[$key]["link"] = preg_replace($reg, $type['statics'].$value["id"],$urls['statics']);
                    $datas[$key]["nofollow"] = 'follow';
                    if ($multi == true) {
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
                if ($multi == true) {
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
    public function getSelectedUrl($type,$value,$url,$count,$info,$link='/meitu/gongzhuang/')
    {
        //判断有几个参数，如果只有一个参数直接返回/meitu/gongzhuang/
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
     * [getMeituAttributeHref 获取美图属性的链接]
     * @param  integer $type [属性，值为0,1,2,3]
     * @return [type]        [description]
     */
    private function getMeituAttributeHref($type = 0){
        if(!empty($type)){
            $return = [];
            $field = 'id,name';
            $map['enabled'] = 1;
            switch ($type) {
                case 1:
                    $map['type'] = 1;
                    $result = M("pubmeitu_att")->field($field)->where($map)->select();
                    foreach ($result as $key => $value) {
                        $return[] = ['name' => $value['name'],'href' => '/meitu/gongzhuang-l'.$value['id'].'f0m0'];
                    }
                    break;
                case 2:
                    $map['type'] = 2;
                    $result = M("pubmeitu_att")->field($field)->where($map)->select();
                    foreach ($result as $key => $value) {
                        $return[] = ['name' => $value['name'],'href' => '/meitu/gongzhuang-l0f'.$value['id'].'m0'];
                    }
                    break;
                case 3:
                    $map['type'] = 3;
                    $result = M("pubmeitu_att")->field($field)->where($map)->select();
                    foreach ($result as $key => $value) {
                        $return[] = ['name' => $value['name'],'href' => '/meitu/gongzhuang-l0f0m'.$value['id']];
                    }
                    break;
                default:
                    break;
            }
            return $return;
        }
        return false;
    }

    /**
     * [getMeituAttribute 获取美图属性的第一个属性的详细信息]
     * @param  [type] $meitu [description]
     * @return [type]        [description]
     */
    private function getMeituAttribute($meitu){
        $field = 'id,name';
        if(!empty($meitu['location'])){
            $location = M("pubmeitu_att")->field($field)->where(['id' => explode(',', $meitu['location'])[0]])->find();
            if(!empty($location)){
                $location['href'] = '/meitu/gongzhuang-l'.$location['id'].'f0m0';
                $result['location'] = $location;
            }
        }

        if(!empty($meitu['fengge'])){
            $fengge = M("pubmeitu_att")->field($field)->where(['id' => explode(',', $meitu['fengge'])[0]])->find();
            if(!empty($fengge)){
                $fengge['href'] = '/meitu/gongzhuang-l0f'.$fengge['id'].'m0';
                $result['fengge'] = $fengge;
            }
        }
        if(!empty($meitu['mianji'])){
            $mianji = M("pubmeitu_att")->field($field)->where(['id' => explode(',', $meitu['mianji'])[0]])->find();
            if(!empty($mianji)){
                $mianji['href'] = '/meitu/gongzhuang-l0f0m'.$mianji['id'];
                $result['mianji'] = $mianji;
            }
        }
        return $result;
    }

    /**
     * [getMeiTuList 获取美图列表]
     * @param  integer $each    [每页显示数目]
     * @param  [type]  $keyword [搜索关键字]
     * @return [type]           [description]
     */
    private function getMeiTuList($each = 40, $keyword, $multi)
    {
        if ($multi == true) {
            $pageTemp = 'q';
            $isSingle = '0';
        } else {
            $pageTemp = 'p';
            $isSingle = '1';
        }

        import('Library.Org.Page.ShortPage');
        if($_GET['order'] == 'hot'){
            $order = '`likes` desc';
        }

        //获取单图的分页
        if(!isset($_GET['a1'])){
            $options = array(
                'prefix' => '/meitu/gongzhuang-',
                'dynamic' => '/meitu/gongzhuang/',
                'short' => array('l'=>'a1' ,'f'=> 'a2','m'=> 'a3'),
                'sort' => array('l','f','m',$pageTemp)
            );
        }
        $Page = new \Page($each, $options, $sline = false, $dline = true, $p=$pageTemp);

        $Page->setConfig('theme','%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
        $result = $Page->analyse();

        if(array_key_exists('a1', $result['param'])){
            $params['l'] = $result['param']['a1'];
            $params['f'] = $result['param']['a2'];
            $params['m'] = $result['param']['a3'];
        }else{
            $params = $result['param'];
        }
        $count = D("Pubmeitu")->getPubMeiTuListCount($params["l"],$params["f"],$params["m"],$keyword,$isSingle);
        if($count > 0){
            $show = $Page->show($count);

            $list = D("Pubmeitu")->getPubMeiTuList(($Page->nowPage-1)*$each,$each,$params["l"],$params["f"],$params["m"],$keyword,$isSingle,$order);

            foreach ($list as $key => $value) {
                //取每个分类的第一个元素
                $exp =array_filter(explode(",", $value["wz"]));
                $list[$key]["wz"] = $exp[0];

                $exp =array_filter(explode(",", $value["fg"]));
                $list[$key]["fg"] = $exp[0];

                $exp =array_filter(explode(",", $value["mj"]));
                $list[$key]["mj"] = $exp[0];
            }
        }
        return array("list"=>$list,"page"=>$show,"params"=>$params,'urls' => $result['urls'], 'current' =>$Page->nowPage);
    }
}