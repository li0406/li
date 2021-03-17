<?php

namespace Home\Controller;
use Boris\DumpInspector;
use Common\Model\BaikeModel;
use Common\Model\Logic\BaikeLogicModel;
use Common\Model\Logic\CompanyLogicModel;
use Common\Model\Logic\SpecialkeywordLogicModel;
use Common\Model\Logic\ThematicWordsLogicModel;
use Common\Model\Logic\WwwArticleClassLogicModel;
use Common\Model\Service\ElasticSearchServiceModel;
use Home\Common\Controller\HomeBaseController;
use Monolog\Handler\IFTTTHandler;

class BaikeController extends HomeBaseController{

    public function _initialize(){
        parent::_initialize();
        //判断是否是搜索引擎蜘蛛
        $robotIsTrue = B("Common\Behavior\RobotCheck");
        if (true === $robotIsTrue) {
            $this->assign('robot',1);
        }
        $headerTmp = "";

        //添加顶部搜索栏信息
        $this->assign('serch_uri','gonglue/search');
        $this->assign('serch_type','装修攻略');
        $this->assign('holdercontent','了解相关的装修知识');
        if(empty($this->cityInfo["bm"])){
            $t = T("Home@Index:header");
        }else{
            if(!$robotIsTrue){
                $t = T("Sub@Index:header");
            }
        }
        //导航栏标识
        $this->assign("tabIndex",5);
        $safe = getSafeCode();
        $this->assign("safecode",$safe["safecode"]);
        $this->assign("safekey",$safe["safekey"]);
        $this->assign("ssid",$safe["ssid"]);
        //百科默认选择状态
        $this->assign('choose_gonglue', 'baike');
        $headerTmp = $this->fetch($t);
        $this->assign("headerTmp",$headerTmp);

    }

    //首页
    public function index(){
        //跳转到手机端
        if (ismobile()) {
            header( "HTTP/1.1 301 Moved Permanently");
            header("Location: ".$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }

        $info = S('Cache:Baike:Home');
        if(empty($info)){
            $DB = D("Common/Baike");

            //最新百科
            $info['newBaike'] = $DB->getList('','post_time DESC','10');
            foreach ($info['newBaike'] as $k=>$v){
                $info['newBaike'][$k]['content'] = htmlToText(html_entity_decode($v['content']));
            }

            //精选百科
            $topBaike = $DB->getTopList('9');
            $i = $s = 0;
            foreach ($topBaike as $key => $value) {
                $value['content'] = htmlToText(html_entity_decode($value['content']));
                if($i >= 3){
                    $i = 0;
                    $s++;
                }
                if($s == 0 ){
                    $newTopBaike[$s]['style'] =  'style="display:block" ';
                }
                $newTopBaike[$s]['sub'][] = $value;
                $i++;
            }
            $info['topBaike'] = $newTopBaike;

            //大家都在看
            $where['recommend'] = ['eq',1];
            $order = 'post_time desc';
            $info['topCategory'] = $DB->getCategoryRecommend($where,$order,'8');
            foreach ($info['topCategory'] as $i=>$val){
                unset($info['topCategory'][$i]['content']);
                // $info['topCategory'][$i]['content'] = htmlToText($val['content']);
            }

            //百科动态
            $where = [];
            $order = 'b.post_time desc';
            $info['trendsCategory'] = $DB->getCategoryRecommend($where,$order,'5');
            foreach ($info['trendsCategory'] as $i => $val){
                unset($info['trendsCategory'][$i]['content']);
            }

            //百科热搜
            $map = [];
            $map["baike"] = array("EQ",'1');
            $info['weekHot'] = $DB->getList($map,'post_time DESC',12);
            foreach ($info['weekHot'] as $i => $val){
                unset($info['weekHot'][$i]['content']);
            }

            //总条数
            $info['countList'] = $this->getCount();
            S('Cache:Baike:Home',$info,300);
        }

        $this->assign("category",$this->getCategory(1));
        $this->assign("newBaike",$info['newBaike']);
        $this->assign("topBaike",$info['topBaike']);
        $this->assign("trendsCategory",$info['trendsCategory']);
        $this->assign("topCategory",$info['topCategory']);
        $this->assign("weekHot",$info['weekHot']);
        //百科知识
        $this->assign("baikezhishi",$info['baikezhishi']);

        //获取城市信息
        $this->assign("order_source",12);
        $this->assign("orderTmp",$this->fetch(T("Common@Order/orderTmp")));
        //获取底部弹层
        $this->assign("zb_bottom_s",$this->fetch(T("Common@Order/zb_bottom_s")));

        //获取友情链接
        $friendLink = S("C:FL:A:baike");

        if (!$friendLink) {
            $friendLink['link'] = D("Friendlink")->getFriendLinkList("000001",1,'baike');
            //获取热门标签
            $hotTag =D("Friendlink")->getFriendLinkList('000001',3,'baike');
            $friendLink["hotTags"] = $hotTag;
            S("C:FL:A:baike", $friendLink, 900);
        }
        if(count($friendLink['link']) > 0){
            $this->assign("friendLink",$friendLink);
        }
        //设置canonical属性
        $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'] .C('QZ_YUMINGWWW').'/baike/';
        //设置tdk
        $headerInfo = [
            'title'=>'家居百科-装修知识',
            'keywords'=>'家居百科,家居装修知识',
            'description'=>'齐装网装修百科是一部内容开放、自由的装修百科全书，旨在创造一个涵盖所有装修领域知识、服务所有互联网用户的中文知识性装修百科全书。',
        ];
        $new = $this->getNewSpecialkeyword(2,'','','',10);
        $this->assign('new',$new["list"]);
        $this->assign('keys',$headerInfo);
        $this->assign('info',$info);
        $this->display();
    }

    //分类
    public function category(){
        $DB = D('Common/Baike');
        //根据URL取分类ID
        $url = I('get.id');

         //所有分类
        $category = $this->getCategory();
        $template = 'category';

        //如果关键字不为空
        $keyword = I('get.keyword');

        if(!empty($keyword)){
            $condition['keyword'] = $keyword;
            $cateInfo['name'] = $keyword;
            $cateInfo['url'] = 'search?keyword='.$keyword;
            $cateInfo['position'] = '“'.$keyword.'”的搜索结果';
        }

        //设置canonical属性
        if(!isset($_GET['keyword'])){
            $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'] .C('QZ_YUMINGWWW').'/baike/'.$url;
        }

        //如果分类URL不为空
        if(!empty($url) && empty($keyword)){
            $cateInfo = $DB->getCategoryByUrl($url);
            if(empty($cateInfo)){
                $this->_error();
            }
            //如果是一级分类
            if($cateInfo['pid'] == '0'){
                //取下面分类
                $subCate = $DB->getCategoryByCid($cateInfo['cid']);
                foreach ($subCate as $key => $value) {
                    $cid = $value['cid'];
                    $subCate[$key]['subList'] = M('baike')->field('id,item,title')->where(array('sub_category' => $cid , 'visible' => '0'))->order('post_time DESC')->limit("0,50")->select();
                }
                $this->assign('bigCate',$subCate);
                $template = 'bigcate';
                $cateInfo['thisUrl'] = $url;
            }else{
                foreach ($category as $key => $value) {
                    if($value['cid'] == $cateInfo['pid']){
                        $cateInfo['thisUrl'] = $value['url'];
                        $cateInfo['bigCateName'] = $value['name'];
                    }
                }
            }
            $condition['cateId'] = $cateInfo['cid'];
            $cateInfo['position'] = $cateInfo['name'];
        }


        //不是大分类
        if(empty($subCate)){
            //取问题列表
            $pageIndex = 1;
            $pageCount = 15;
            if(!empty($_GET["p"])){
                $pageIndex = remove_xss($_GET["p"]);
                $pageContent ="第".$pageIndex."页";
            }
            $result = $this->getList($condition,$pageIndex,$pageCount);
            //dump($result);
            $this->assign("list",$result["list"]);
            $this->assign("page",$result["page"]);
        }

        //最新百科
        $newBaike = $DB->getList('','post_time DESC','20');



        $this->assign("order_source",12);
        $t = T("Common@Order/orderTmp");
        $orderTmp = $this->fetch($t);
        $this->assign("citys",$citys);
        $this->assign("orderTmp",$orderTmp);

        $this->assign('category',$category);
        $this->assign('newBaike',$newBaike);
        $this->assign('cateInfo',$cateInfo);
        $this->assign('info',$info);

        $this->display($template);
    }

    //列表页
    public function baikeList()
    {
        //跳转到手机端
        if (ismobile()) {
            header( "HTTP/1.1 301 Moved Permanently");
            header("Location: ".$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }
        $page = max(1, I('get.page'));
        $count = 20;
        if (1 == $page) {
            //判断是否有传参数
            if(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY)){
                if (substr(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), -1) != '/') {
                    header("HTTP/1.1 301 Moved Permanently");
                    header("Location: ".$this->SCHEME_HOST['scheme_full'] . $this->SCHEME_HOST['host']  . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) . "/?" . parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY));
                    exit();
                }
            }else{
                //如果url末尾没有 '/' 就跳转加
                if (substr($_SERVER['REQUEST_URI'], -1) != '/') {
                    header("HTTP/1.1 301 Moved Permanently");
                    header("Location: ".$this->SCHEME_HOST['scheme_full'] . $this->SCHEME_HOST['host']  . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) . "/");
                    exit();
                }
            }
        }
        $DB = D('Common/Baike');
        //导航栏
        $category = S('Cache:Baike:baikeList');
        if(!$category){
            $tempCategory = $DB->getCategory();
            $category = array();
            if ($tempCategory) {
                foreach ($tempCategory as $k => $v) {
                    if ($v['pid'] == '0') {
                        $category[$v['cid']] = $v;
                        unset($k);
                    }
                }
                foreach ($tempCategory as $k => $v) {
                    if ($v['pid'] != '0') {
                        $category[$v['pid']]['sub_cate'][] = $v;
                    }
                }
            }
            ksort($category);
            S('Cache:Baike:baikeList',$category,300);
        }
        //页面数据
        $where = [];
        //默认选中一级分类排序第一的全部数据
        $pid = I('get.pid');
        $cid = I('get.cid');
        //通过判断url来默认选中
        if(I('get.url')){
            $data = $parseUrl = $DB->getCategoryByUrl(I('get.url'));
            //没有查询到类别的,404页面
            if(empty($data)){
                $this->_error();
                die();
            }

            if($data['pid'] == 0){
                $pid = $data['cid'];
                //表示是一级标签
                $this->assign('taglevel1',1);
                $this->assign('taglevel1_name',$data['name']);
                $this->assign('taglevel1_url'.$this->SCHEME_HOST['scheme_full'] .'www.' . C('QZ_YUMING') . '/baike/' . $data['url'] . '/');
            }else{
                $cid = $data['cid'];
                //表示是二级标签
                $this->assign('taglevel2',1);
                $this->assign('taglevel2_name',$data['name']);
                $this->assign('taglevel2_url'.$this->SCHEME_HOST['scheme_full'] .'www.' . C('QZ_YUMING') . '/baike/' . $data['url'] . '/');
                //获取一级标签
                $level1 = $DB->getCategoryById($data['pid']);
                //表示是一级标签
                $this->assign('taglevel1',1);
                $this->assign('taglevel1_name',$level1['name']);
                $this->assign('taglevel1_url',$this->SCHEME_HOST['scheme_full'] .'www.' . C('QZ_YUMING') . '/baike/' . $level1['url'] . '/');
            }
        }

        //动态分页跳转到静态分页
        if (isset($_GET['p'])) {
            $temp = intval($_GET['p']);
            if ($temp > 1) {
                $url = $this->SCHEME_HOST['scheme_full'] .'www.' . C('QZ_YUMING') . '/baike/' . $parseUrl['url'] . '/p-' . $temp . '.html';
            } else {
                $url = $this->SCHEME_HOST['scheme_full'] .'www.' . C('QZ_YUMING') . '/baike/' . $parseUrl['url'] . '/';
            }
            header("HTTP/1.1 301 Moved Permanently");
            header("Location:" . $url);
            die();
        }
        //用来选中导航栏
        $menuInfo = ['pid' => $pid, 'cid' => 0];

        //用来显示页面 mobile-agent 参数
        $mobile_agent = '';
        //1.先判断是否有cid,如果有 就直接将他的pid一并查出
        if ($cid) {
            //如果选中二级分类,则修改二级分类名称的tdk
            $c_name = $DB->getCategory(['cid' => $cid]);
            $p_name = $DB->getCategory(['cid' => $c_name[0]['pid']]); //查出二级分类的父分类名称(cid代表每条数据的id)
            $mobile_agent = $c_name[0]['url'] .'/';
            $where['b.sub_category'] = array('in', $cid);
            //设置tdk
            $header['title'] = empty($c_name[0]['title'])?"{$c_name[0]['name']}-{$p_name[0]['name']}知识大全":$c_name[0]['title'];
            $header['keywords'] = empty($c_name[0]['keywords'])?"{$c_name[0]['name']}，{$c_name[0]['name']}知识，{$p_name[0]['name']}知识大全":$c_name[0]['keywords'];
            $header['description'] = empty($c_name[0]['description'])?"齐装网{$c_name[0]['name']}栏目为用户提供专业的{$c_name[0]['name']}知识，专业打造整个装修行业内高端的{$p_name[0]['name']}知识全书。":$c_name[0]['description'];
        }
        //2.判断是否有pid, 如果有说明未选中二级分类
        if ($pid) {
            $cids = '';
            $pdata = $DB->getCategory(['pid' => ['eq', $pid]]);
            $data = $DB->getCategory(['cid' => ['eq', $pid]]);
            $mobile_agent = $data[0]['url'] .'/';
            foreach ($pdata as $p) {
                $cids[] = $p['cid'];
            }
            $where['b.sub_category'] = array('in', $cids);
            //设置tdk
            $header['title'] = empty($data[0]['title'])?"{$data[0]['name']}-{$data[0]['name']}知识大全":$data[0]['title'];
            $header['keywords'] = empty($data[0]['keywords'])?"{$data[0]['name']}，{$data[0]['name']}知识，{$data[0]['name']}知识大全":$data[0]['keywords'];
            $header['description'] = empty($data[0]['description'])?"齐装网{$data[0]['name']}频道为用户提供专业的{$data[0]['name']}知识，专业打造整个装修行业内高端的{$data[0]['name']}知识全书。":$data[0]['description'];
        }

        //如果是搜索过来的,则直接去查询对应分类的数据,否则查询关键字数据
        $baikeList = [];
        $keyword = I('get.keyword');
        if (!$keyword) {
            $baikeList = $DB->getBkByCategory($where, $page, $count);
            //如果有数据
            if($baikeList){
                foreach ($baikeList as $i => $val) {
                    $baikeList[$i]['content'] = htmlToText(html_entity_decode($val['content']));
                    if (strlen($val['jc']) > 7) {
                        $baikeList[$i]['jc'] = mb_substr($val['jc'], 0, 6, 'utf-8');
                    } else {
                        $baikeList[$i]['jc'] = ['jc'];
                    }
                }
                //选中二级分类,导航栏才选择二级菜单
                if ($cid) {
                    $menuInfo = ['pid' => $baikeList[0]['pid'], 'cid' => $baikeList[0]['cid']];
                }
            }else{
                //如果没有数据，则获取菜单栏数据
                //获取pid
                $getrealpid = $DB->getRealPid($cid);
                $menuInfo = ['pid' => $getrealpid['pid'], 'cid' => $getrealpid['cid']];
            }
            $baikeCount = $DB->getBkByCategoryCount($where);
        } else {
            //搜索关键字数据
            $where = [];
            $baikeList = $DB->getBkByCategory($where, $page, $count,$keyword);
            foreach ($baikeList as $i => $val) {
                $baikeList[$i]['content'] = htmlToText(html_entity_decode($val['content']));
                if (strlen($val['jc']) > 7) {
                    $baikeList[$i]['jc'] = mb_substr($val['jc'], 0, 6, 'utf-8');
                } else {
                    $baikeList[$i]['jc'] = ['jc'];
                }
            }
            $baikeCount = $DB->getBkByCategoryCount($where,$keyword);
            $menuInfo = ['keyword' => $keyword];
        }

        if ($baikeCount > $count) {
            import('Library.Org.Page.SPage');
            $page = new \SPage($baikeCount, $count, array(
                'templet' => '/baike/' . $parseUrl['url'] . '/p-[PAGE].html',
                'firstUrl' => '/baike/' . $parseUrl['url'] . '/'
            ));
            $pageTmp =  $page->show();
        }

        //百科热搜
        $map = [];
        $map["lanmu"] = array("EQ",'1');
        if($pid){
            $map["cid"] = array("EQ",$pid);
        }else{
            $map["sub_category"] = array("EQ",$cid);
        }
        $baikeHot = $DB->getList($map, 'post_time DESC', 12);

        //查询当前栏目是否有分词
        $model = new WwwArticleClassLogicModel();
        $word = $model->getArticleClassParticiple($data['cid'],2);

        if ($word != "") {
            $service = new ElasticSearchServiceModel();
            $participle_words =  $service->getContentLabel($word,1,10);
            $this->assign("participle_words",$participle_words);
        }


        $this->assign('keys',$header);
        $this->assign('mobile_agent',$mobile_agent);
        $this->assign("pageList", $pageTmp);
        $this->assign("menuInfo", $menuInfo);
        $this->assign("baikeList", $baikeList);
        $this->assign("category", $category);
        $this->assign("baikeHot", $baikeHot);
        $this->assign("baikeHotCount", count($baikeHot));
        $this->assign("order_source",20091536);
        $this->assign("orderTmp", $this->fetch(T("Common@Order/orderTmp")));
        $this->display('baikelist');
    }

    public function baikeUrlJump(){
        $pid = I('get.pid');
        $cid = I('get.cid');
        if ($pid) {
            $data = D('Common/Baike')->getCategoryById($pid);
            $url = '/baike/' . $data['url'] . '/';
            header("HTTP/1.1 301 Moved Permanently");
            header("Location:" . $url);
            die();
        }
        if ($cid) {
            $data = D('Common/Baike')->getCategoryById($cid);
            $url = '/baike/' . $data['url'] . '/';
            header("HTTP/1.1 301 Moved Permanently");
            header("Location:" . $url);
            die();
        }
    }

    //推荐页
    public function baike_recommend()
    {
        //大家都在看
        $DB = D("Common/Baike");
        $page = max(1,I('post.page'));
        $count = 20;
        $where['recommend'] = ['eq',1];
        $order = 'post_time desc';
        $category = $DB->getCategoryRecommend($where,$order,$count,$page);
        foreach ($category as $i=>$val){
            $category[$i]['content'] = htmlToText(html_entity_decode($val['content']));
        }
        if(IS_POST){
            $this->ajaxReturn(['info'=>$category,'status'=>1]);
        }
        //设置tdk
        $headerInfo = [
            'title'=>'齐装网装修百科推荐 ',
            'keywords'=>'装修百科,装修设计知识,装修百科大全',
            'description'=>'齐装网装修百科推荐为用户提供专业的装修设计百科知识，你所需要的装修百科知识这里都有。',
        ];
        $this->assign('keys',$headerInfo);
        $this->assign("category",$category);
        $this->display('recommend');
    }

    //百科详情页
    public function show(){
        //跳转到手机端
        if (ismobile()) {
            header( "HTTP/1.1 301 Moved Permanently");
            header("Location: ".$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }
        $DB = new BaikeModel();

        $id = I('get.id');
        //百科
        $info = S("CACHE:BAIKE:".$id);
        if (!$info) {
            $info = $DB->getBaike($id);
            if(empty($info)){
                $this->_error();
            }
            /*E-没有获取到的情况下取公用部分*/
            //开始替换关键字(新增的功能g.1.11.12)
            $info['content'] = D('Common/Logic/BaikeLogic')->replaseKeywords($info['id'], $info['content']);
            //结束替换关键字
            S("CACHE:BAIKE:".$id,$info,900);
        }

        //最新知识
        $zhishi = S('Cache:Home:Baike:Index:zhishi');
        if (!$zhishi) {
            $zhishi= $this->getSpecialkeyword(1, 20,2);
            S('Cache:Home:Baike:Index:zhishi', $zhishi, 900);
        }
        $this->assign('zhishi',$zhishi["list"]);

        //获取标题最新三条关键词
        if(!empty($info['item'])){
            $titleKeywords = S('Cache:Home:Baike:Keywords:'.md5($info['item']));
            if(!$titleKeywords){
                $keyword_logic = new SpecialkeywordLogicModel();
                $titleKeywords = $keyword_logic->getTitleKeywords($info['item'],2);
                S('Cache:Home:Baike:Keywords:'.md5($info['item']),$titleKeywords,900);
            }
            $this->assign("title_keywords",$titleKeywords);
        }

        //取第一段关键词
        if (empty($info['description'])) {
            $desc = str_replace('<br />','<br>',strip_tags($info['content'],'<br>'));
            $desc = array_filter(explode('<br',$desc));
            foreach ($desc as $key => $value) {
                $value = trim($value);
                if(!empty($value)){
                    $info['description'] = $value;
                    break;
                }
            }
        }
        //截取90的字符，如果有90个字符，则在末尾加上...
        $info['description'] = mb_substr($info['description'],0,160,"UTF-8");
        if(mb_strlen($info['description'],'UTF8') >= 160){
            $info['description'] = $info['description'].' ...';
        }

        //增加查看数
        $DB->updateViews($id);

        $keywords = S('Cache:BaikeDetail:keywords'.$id);
        if(!$keywords){
            //获取先关标签
            $thematicLogic = new ThematicWordsLogicModel();
            $keywords = $thematicLogic->getContentLabels($id,2,5);
            S('Cache:BaikeDetail:keywords'.$id,$keywords,600);
        }
        $this->assign('keywords',$keywords);


        $otherBaike = S("CACHE:BAIKE:OTHER:".$id);
        if (!$otherBaike) {
            $map['cateId'] = $info['sub_category'];
            $map['id'] = array('NOTIN', $info['id']);
            $otherBaike = $DB->getList($map, 'post_time desc', '3');

            foreach ($otherBaike as $k => $v) {
                if (!empty($v['description'])) {
                    $otherBaike[$k]['des'] = htmlToText($v['description'], 60);
                } else {
                    $otherBaike[$k]['des'] = htmlToText($v['content'], 60);
                }
                unset($otherBaike[$k]['description'], $otherBaike[$k]['content']);
            }
            S("CACHE:BAIKE:OTHER:".$id,$otherBaike,900);
        }

        $category = $this->getCategory();
        foreach ($category as $k => $v) {
            if($v['cid'] == $info['cid']){
                $info['bigCate'] = $v['name'];
                $info['bigCateUrl'] = $v['url'];
                foreach ($v['sub_cate'] as $key => $value) {
                    if($value['cid'] == $info['sub_category']){
                        $info['categoryName'] = $value['name'];
                        $info['categoryUrl'] = $value['url'];
                    }
                }
            }
        }

        $this->assign("category",$category);
        $this->assign("otherBaike",$otherBaike);

        //获取报价模版
        $this->assign("order_source",12);
        $this->assign("orderTmp", $this->fetch(T("Common@Order/orderTmp")));

        $content = '$#@' . $info['content'];
        $reg ='/<strong[^>]*>([\w\W]*?)<\/strong\s*>/';
        preg_match_all($reg, $content, $matches);
        $main['catalog'] = $matches[1];
        $temp = array_filter(preg_split($reg, $content));

        //如果目录数量和分割的数量一致，则说明内容的最后面有strong标签，此种情况直接采用原来形式
        if (count($main['catalog']) == count($temp)) {
            unset($main['catalog']);
        } else {
            $i = 1;
            foreach ($temp as $key => $value) {
                $value = trim($value);
                if ($i == 1) {
                    $value = trim(ltrim($value, '$#@'));
                    //说明在截取的第一个目录之上还有内容，此内容不属于任何目录，暂归为简介
                    if (!empty($value)) {
                        $main['brief'] = $value;
                    }
                } else {
                    $value = trim($value);
                    if (0 === strpos($value, '</p>')) {
                        $value = mb_substr($value, 4);
                    }
                    $last = mb_strlen($value) - 3;
                    if ($last === strpos($value, '<p>')) {
                        $value = mb_substr($value, 0, $last);
                    }
                    if (0 === strpos($value, '<br />')) {
                        $value = mb_substr($value, 6);
                    }
                    $main['content'][] = $value;
                }
                $i++;
            }
        }

        //如果目录和内容不匹配，直接采用原来样式
        if (count($main['catalog']) != count($main['content'])) {
            unset($main['catalog']);
            unset($main['content']);
        }
        //取出内容中的图片
        foreach ($main['content'] as $k => $v) {
            $pattern = '/<img([\w\W]*?)\/>/';
            preg_match_all($pattern, $v, $arr);
            //取出src  $arr[0][0]
            $reg = "/src[=\"\'\s]+([^\"\']+)[\"\']/i";
            preg_match_all($reg, $arr[0][0], $src_arr);
            $src = $src_arr[1][0];
            $main['imgs'][$k] = $src;
            $main['content'][$k] = preg_replace('/<p [^>]+>\s*(<img[^>]+>)\s*<\/p>/s', '', $v);  //去除带img的那一行P标签
            //$main['content'][$k] = preg_replace('/<p [^>]+>\s*[^\x00-\xff]\s*<\/p>/$', '', $main['content'][$k]);  //去除带img的那一行P标签
            $main['strlength'][$k] = strlen(htmlToText($main['content'][$k]));
            $main['countp'][$k] = mb_substr_count($main['content'][$k],'<p ') + mb_substr_count($main['content'][$k],'<p>');
            unset($arr);
        }
        /*S-底部设计浮动框*/
        $t = T("Common@Order/zb_bottom_s");
        $adv_bottom = $this->fetch($t);
        $this->assign("adv_bottom",$adv_bottom);
        /*E-底部设计浮动框*/

        //兼容头部导航
        if(empty($this->cityInfo["bm"])){
            $this->assign("headerTmp",null);
        }

        //设置canonical属性
        $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'] .C('QZ_YUMINGWWW').'/baike/'.$id.'.html';
        //新的TDK，如果有目录，拼接新的title keywords ,description不变
        if(!empty($main['catalog'])){
            for ($i=0; $i < 4; $i++) {
                if(!empty($main['catalog'][$i])){
                    $str = htmlToText($main['catalog'][$i],0);
                    $title_str .= $str.'_';
                }
            }
            $title_str = substr($title_str,0,-1);
            $title_str = trim(strip_tags(htmlspecialchars_decode($title_str)));
            $title_str = str_replace('/\s+/',"",$title_str);
            $info['header']['title'] = $title_str.'-齐装网';
            $info['header']['keywords'] = $title_str;
            $info['header']['description'] = htmlToText($info['description'],140);
        }
        if(empty($info['header']['keywords'])){
            $info['header']['keywords'] = $info['header']['title'];
        }

        //右侧标签组
        $info['righttags'] = S('Cache:WwwArticleBaikeTags:Right');
        if (!$info['righttags']) {
            $articleTagsModel = new \Common\Model\Logic\ArticleTagLogicModel();
            $info['righttags'] = $tags = $articleTagsModel->getTagData(1,2);
            if (!empty($tags)) {
                S('Cache:WwwArticleBaikeTags:Right', $tags, 900);
            }
        }

        $info['header']['title'] = preg_replace('# #','',$info['header']['title'] );
        $info['header']['keywords'] =  str_replace('_','，',$info['header']['keywords']);
        $info['header']['keywords'] = str_replace('、','，',$info['header']['keywords']);

        //去水印
        // foreach ($main['imgs'] as $key => $value) {
        //     $main['imgs'][$key] = str_replace('-s3.', '-s5.', $value);
        // }

        //底部标签组
        $info['bottomtags'] = S('Cache:WwwArticleBaikeTags:Bottom');
        if (!$info['bottomtags']) {
            $articleTagsModel = new \Common\Model\Logic\ArticleTagLogicModel();
            $info['bottomtags'] = $tags = $articleTagsModel->getTagData(1,1);


            $thematicLogic = new ThematicWordsLogicModel();
            //最新美图
            $newestMeitu = [];
            $newestMeitu["id"] = "";
            $newestMeitu["name"] = "最新美图";
            $newestMeitu['sub_tags'] = $thematicLogic->getNewTypeWords(2, 30);
            if (count($newestMeitu['sub_tags']) > 0) {
                $info['bottomtags'][] = $newestMeitu;
            }
            //最热美图
            $hottestMeitu = [];
            $hottestMeitu["id"] = "";
            $hottestMeitu["name"] = "最热美图";
            $hottestMeitu['sub_tags'] = $thematicLogic->getTypeHotList(2, 30);
            if (count($hottestMeitu['sub_tags']) > 0) {
                $info['bottomtags'][] = $hottestMeitu;
            }


            if (count($info['bottomtags']) > 0) {
                S('Cache:WwwArticleBaikeTags:Bottom', $info['bottomtags'], 900);
            }
        }

        //获取右侧推荐装修公司
        $cs = $this->cityInfo['id'] ?: '';
        $info['recommend_company_right'] = S('Www:Company:RecommendRight' . $cs);
        if (!$info['recommend_company_right']) {
            $companyLogic = new CompanyLogicModel();
            $info['recommend_company_right'] = $companyLogic->getRecommendCompanyModule($cs);
            S('Www:Company:RecommendRight' . $cs, $info['recommend_company_right'], 900);
        }
        //获取底部推荐装修公司
        $info['recommend_company_bottom'] = S('Www:Company:RecommendBottom' . $cs);
        if (!$info['recommend_company_bottom']) {
            $companyLogic = new CompanyLogicModel();
            $info['recommend_company_bottom'] = $companyLogic->getRecommendCompanyByCaseNum($cs, 3, array_column($info['recommend_company_right'], 'id'));
            S('Www:Company:RecommendBottom' . $cs, $info['recommend_company_bottom'], 900);
        }

        // 1353百度官方号需求
        $baidu['url'] = $this->SCHEME_HOST['scheme_full'].$this->SCHEME_HOST['host'].$_SERVER['REQUEST_URI'];
        $baidu['str'] = mb_substr(strip_tags($info['description']), 0, 100, 'utf-8');
        $baidu['title'] = $info["title"];
        $optimee =date("Y-m-d",$info['post_time']);
        $optimes =date("H:i:s",$info['post_time']);

        $baidu['optime'] = $optimee."T".$optimes;
        $baidu['addtime'] = $optimee."T".$optimes;
        $this->assign("baidu",$baidu);

        $this->assign('info',$info);
        $this->assign('main',$main);
        $this->display();
    }

    //百科详情页
    public function newBaiKe(){
        $DB = D("Common/Baike");
        $id = I('get.id');
        //百科
        $info = $DB->getBaike($id);
        if(empty($info)){
            $this->_error();
        }
        //最新百科
        $newBaike = $DB->getList('','post_time DESC','20');
        //取第一段关键词
        if (empty($info['description'])) {
            $desc = str_replace('<br />','<br>',strip_tags($info['content'],'<br>'));
            $desc = array_filter(explode('<br',$desc));
            foreach ($desc as $key => $value) {
                $value = trim($value);
                if(!empty($value)){
                    $info['description'] = $value;
                    break;
                }
            }
        }

        if($info['cid'] == '35'){
            $info['content'] = '<strong>公司介绍 </strong>'.$info['content'];
        }

        //最新百科 取本百科一级分类，不包括本百科 数量多了可以二级分类
        $map['cateId'] = $info['cid'];
        $map['id'] = array('NOTIN',$info['id']);

        $otherBaike = $DB->getList($map,'post_time','20');

         //增加查看数
        $DB->updateViews($id);

        $category = $this->getCategory();
        foreach ($category as $k => $v) {
            if($v['cid'] == $info['cid']){
                $info['bigCate'] = $v['name'];
                $info['bigCateUrl'] = $v['url'];
                foreach ($v['sub_cate'] as $key => $value) {
                    if($value['cid'] == $info['sub_category']){
                        $info['categoryName'] = $value['name'];
                        $info['categoryUrl'] = $value['url'];
                    }
                }
            }
        }

        $this->assign("category",$category);
        $this->assign("newBaike",$newBaike);
        $this->assign("otherBaike",$otherBaike);

        //获取报价模版
        $this->assign("order_source",12);
        $this->assign("orderTmp", $this->fetch(T("Common@Order/orderTmp")));


        if (!empty($info['keywords'])) {
            $other = S('Home:Baike:Show:Other:'. $id);
            if (empty($other)) {
                $keywords = array_filter(explode(',', $info['keywords']));
                if (!empty($keywords)) {
                    //问答筛选条件
                    $ask['_complex']     = array();
                    //美图筛选条件
                    $meitu['_complex']   = array();
                    //文章筛选条件、
                    $article['_complex'] = array();
                    foreach ($keywords as $key => $value) {
                        $value = trim($value);
                        $ask['_complex'][]     = "find_in_set('$value',keywords)";
                        $meitu['_complex'][]   = "find_in_set('$value',keyword)";
                        $article['_complex'][] = "find_in_set('$value',a.keywords)";
                    }
                    $ask['_complex']['_logic']     = 'OR';
                    $meitu['_complex']['_logic']   = 'OR';
                    $article['_complex']['_logic'] = 'OR';
                    //获取相关问答
                    $other['ask'] = D('Ask')->getQuestionList($ask);
                    if (empty($other['ask'])) {
                        $other['ask'] = D('Ask')->getNewQuestion(10);
                    }
                    //获取相关美图
                    $other['meitu'] = D('Meitu')->getMeituByMap($meitu, 4);
                    if (empty($other['meitu'])) {
                        $other['meitu'] = D('Meitu')->getMeiTuList(0, 4);
                    }
                    //获取相关文章
                    $other['article'] = D('WwwArticle')->getArticleListByMap($article, 10);
                    if (empty($other['article'])) {
                        $other['article'] = D('WwwArticle')->getNewArticle(10);
                    }
                    S('Home:Baike:Show:Other:'. $id, $other, 900);
                }
            }
        }

        /*S-没有获取到的情况下取公用部分*/
        if (empty($other['ask'])) {
            $other['ask'] = S('Home:Baike:Show:Common:Ask');
            if (empty($other['ask'])) {
                $other['ask'] = D('Ask')->getNewQuestion(10);
                S('Home:Baike:Show:Common:Ask', $other['ask'], 3600);
            }
        }
        if (empty($other['meitu'])) {
            $other['meitu'] = S('Home:Baike:Show:Common:Meitu');
            if (empty($other['meitu'])) {
                $other['meitu'] = D('Meitu')->getMeiTuList(0, 4);
                S('Home:Baike:Show:Common:Meitu', $other['meitu'], 3600);
            }
        }
        if (empty($other['article'])) {
            $other['article'] = S('Home:Baike:Show:Common:Article');
            if (empty($other['article'])) {
                $other['article'] = D('WwwArticle')->getNewArticle(10);
                S('Home:Baike:Show:Common:Article', $other['article'], 3600);
            }
        }
        /*E-没有获取到的情况下取公用部分*/

        $content = '$#@' . $info['content'];
        $reg ='/<strong[^>]*>([\w\W]*?)<\/strong\s*>/';
        preg_match_all($reg, $content, $matches);
        $main['catalog'] = $matches[1];
        $temp = array_filter(preg_split($reg, $content));

        //如果目录数量和分割的数量一致，则说明内容的最后面有strong标签，此种情况直接采用原来形式
        if (count($main['catalog']) == count($temp)) {
            unset($main['catalog']);
        } else {
            $i = 1;
            foreach ($temp as $key => $value) {
                $value = trim($value);
                if ($i == 1) {
                    $value = trim(ltrim($value, '$#@'));
                    //说明在截取的第一个目录之上还有内容，此内容不属于任何目录，暂归为简介
                    if (!empty($value)) {
                        $main['brief'] = $value;
                    }
                } else {
                    $main['content'][] = $value;
                }
                $i++;
            }
        }
        //如果目录和内容不匹配，直接采用原来样式
        if (count($main['catalog']) != count($main['content'])) {
            unset($main['catalog']);
            unset($main['content']);
        }

        //取出内容中的图片
        foreach ($main['content'] as $k => $v) {
            $pattern = '/<img([\w\W]*?)\/>/';
            preg_match_all($pattern, $v, $arr);
            $main['imgs'][$k] = $arr[0];
            unset($arr);
        }

        /*S-底部设计浮动框*/
        $t = T("Common@Order/zb_bottom_s");
        $adv_bottom = $this->fetch($t);
        $this->assign("adv_bottom",$adv_bottom);
        /*E-底部设计浮动框*/

        //兼容头部导航
        if(empty($this->cityInfo["bm"])){
            $this->assign("headerTmp",null);
        }

        //设置canonical属性
        $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'].C('QZ_YUMINGWWW').'/baike/'.$id.'.html';

        //新的TDK，如果有目录，拼接新的title keywords ,description不变
        if(!empty($main['catalog'])){
            for ($i=0; $i < 3; $i++) {
                if(!empty($main['catalog'][$i])){
                    $title_str .= '_'.$main['catalog'][$i];
                }
            }
            $keywords_str = implode(',', $main['catalog']);
            $info['title'] = $info['title'].$title_str.' - 齐装网';
            $info['keywords'] = $keywords_str;
        }
        if(empty($info['keywords'])){
            $info['keywords'] = $info['title'];
        }

        $this->assign('info',$info);
        $this->assign('main',$main);
        $this->assign('other',$other);
        $this->display();
    }

    //取列表
    private function getList($condition,$pageIndex = 1,$pageCount = 10){
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        import('Library.Org.Page.Page');
        $result = D("Common/Baike")->getListByCategory($condition,($pageIndex-1) * $pageCount,$pageCount);
        $count = $result['count'];
        $list = $result['result'];
        $config  = array("prev","first","last","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        return array("list"=>$list,"page"=>$pageTmp,"num"=>$count);
    }

    //获取分类
    private function getCategory($cid = '',$update = false){
        if($update != true){
           $category = S('Cache:Baike:Category');
        }
        //如果数据为空 - 基本上不会出现
        if(empty($category)){
            // $where['is_top'] = ['neq',''];
            // $where['pid'] = ['eq',0];
            // $where['_logic'] = 'OR';
            $tempCategory = D("Common/Baike")->getCategory([]);
            $category = array();
            if($tempCategory){
                //为了避免这个Bug，进行两次遍历，先取根数组，后期改进
                foreach ($tempCategory as $k => $v ){
                    if($v['pid'] == '0') {
                        $category[$v['cid']] = $v;
                        unset($k);
                    }
                }
                foreach ($tempCategory as $k => $v ){
                    if($v['pid'] != '0') {
                        $category[$v['pid']]['sub_cate'][] = $v;
                    }
                }
            }
            ksort($category);
            $cateIcon = array(
                '装修百科' => 'wrench','建材百科' => 'book','房产百科' => 'building','设计百科' => 'leaf','家具百科' => 'home',
                '家电百科' => 'lightbulb','品牌百科' => 'trophy','装修公司百科' => 'user'
            );
            foreach ($category as $k => $v) {
                $v['icon'] = $cateIcon[$v['name']];
                $newCategory[] = $v;
            }
            $newCategory = multi_array_sort($newCategory,'order_id');
            S('Cache:Baike:Category',$newCategory,900);
            return $newCategory;
        }
        //根据 Cid 返回
        if(!empty($cid)){
            foreach ($tempCategory as $k => $v ){
                if($v['cid'] == $cid) {
                    return $v;
                    exit;
                }
            }
        }
        return $category;
    }

    private function getCount(){
        $data = D('baike')->getListByCategory();
        unset($data['result']);
        return $data;
    }

    public function zhishidaquan() {
        if (ismobile()) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: '.$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }
        $pageIndex = 1;
        $pageCount = 58;

        if (I("get.p") !== "") {
            $pageIndex = I("get.p");
        }
        //TDK
        $ptdk = I('get.p') > 1 ? ' - 第'.I('get.p').'页' : "";
        $basic['head']['title'] = '家居装修百科知识大全'.$ptdk.' - 齐装网';
        $basic['head']['keywords'] = '百科知识，家居装修，装修百科，百科全书,百科词条,百科知识大全';
        $basic['head']['description'] = '齐装网家居装修百科知识平台通过装修百科词条旨在为用户提供全面的家居装修、装修百科、家居知识等家居装修百科知识问题。学装修，看齐装网百科知识词条。';
        $basic['body']['title'] = '装修攻略';

        $logic = new ThematicWordsLogicModel();
        $result = $logic->getList(2,$pageIndex,$pageCount);
        $hot = $logic->getHotList(2,20);

        $this->assign('basic',$basic);
        $uri = explode('?',__SELF__);
        $this->assign('uri',$uri[0]);
        $this->assign("page",$result["page"]);
        $this->assign("hot",$hot);
        $this->assign("list",$result["list"]);
        $this->display();
    }

    /**
     * 获取关键列表
     * @param  string $value [description]
     * @return [type]        [description]
     */
    private function getSpecialkeyword($pageIndex,$pageCount,$keyword_module,$is_hot)
    {
        $count = D('Common/Logic/SpecialkeywordLogic')->getSpecialkeywordCount($keyword_module,$is_hot);
        if ($count > 0) {
            import('Library.Org.Page.LitePage');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \LitePage($pageIndex,$pageCount,$count,$config);
            $show    = $page->pindaoshow();//,$p->firstRow,$p->listRows
            $list = D('Common/Logic/SpecialkeywordLogic')->getSpecialkeywordList($keyword_module, $is_hot,($page->pageIndex-1)*$pageCount,$pageCount);
            foreach ($list as $key => $value) {
                if($value["keyword_module"] == 1){
                    $list[$key]["url"] = $this->SCHEME_HOST['scheme_full'] ."www.qizuang.com/gonglue/zs/".$value["href"]."/";
                }else{
                    $list[$key]["url"] = $this->SCHEME_HOST['scheme_full'] ."www.qizuang.com/baike/zs/".$value["href"]."/";
                }
                $list[$key]['name'] = mb_substr($value["name"],0,12,'utf-8');       // 关键词名称最多显示12个字符；
            }
        }
        return array("list"=>$list,"page"=>$show);
    }

    public function thematic() {
        if (ismobile()) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: '.$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }

        $id = I("get.id");
        $logic = new ThematicWordsLogicModel();

        $info = S("Cache:Them:".$id);
        if (!$info) {
            $info = $logic->getThematicInfoById($id,2);
            if (count($info) == 0) {
                $this->_error();
                die();
            }
            S("Cache:Them:".$id,$info,900);
        }

        $pageIndex = max(1, I('get.p'));
        $pageCount = 10;
        //TDK
        $basic['head']['title'] = $info['title'].' - 齐装网装修百科';
        $basic['head']['keywords'] = $info['keywords'];
        $basic['head']['description'] = $info['description'];
        $basic['body']['title'] = '装修百科';

        //获取列表
        $result = $logic->getBaikeSearch($info["name"],$pageIndex,$pageCount);

        $uri = explode('?',__SELF__);
        $this->assign('uri',$uri[0]);
        $this->assign('basic',$basic);
        $this->assign("thematic",$info["name"]);
        $this->assign("list",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->display();
    }

    /**
     * 获取关键列表
     * @param  string $value [description]
     * @return [type]        [description]
     */
    private function getActriceBySpecialkeyword($keyword_module,$pageIndex,$pageCount,$name,$tempword=[],$notid = 0)
    {
        $logic = new SpecialkeywordLogicModel();
        $count = $logic->getActriceBySpecialkeywordCount($keyword_module,$name,$tempword,$notid);
        if ($count > 0) {
            import('Library.Org.Page.LitePage');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \LitePage($pageIndex,$pageCount,$count,$config);
            $show    = $page->pindaoshow();
            $list = $logic->getActriceBySpecialkeywordList($keyword_module,$name,$tempword,($page->pageIndex-1)*$pageCount,$pageCount,$notid);
            foreach ($list as $key => $value) {
                $list[$key]["url"] =  $this->SCHEME_HOST['scheme_host']."/baike/".$value["id"].".html";
                $list[$key]["src"] =  C('QINIU_SCHEME')."://".C('QINIU_DOMAIN')."/".$value["thumb"];
            }
        }
        return array("list"=>$list,"page"=>$show);
    }
    /**
     * 获取关键列表
     * @param  string $value [description]
     * @return [type]        [description]
     */
    private function getSpecialkeywordByAbout($keyword_module,$name,$tempword=[])
    {
        $list = D('Common/Logic/SpecialkeywordLogic')->getSpecialkeywordByAbout($keyword_module,$name,$tempword);
        foreach ($list as $key => $value) {
            if($value["keyword_module"] == 1){
                $list[$key]["url"] = $this->SCHEME_HOST['scheme_full'] .C('QZ_YUMINGWWW')."/gonglue/zs/".$value["href"]."/";
            }else{
                $list[$key]["url"] = $this->SCHEME_HOST['scheme_full'] .C('QZ_YUMINGWWW')."/baike/zs/".$value["href"]."/";
            }
        }
        return $list;

    }
    /**
     * 获取最新关键列表
     * @param  string $value [description]
     * @return [type]        [description]
     */
    private function getNewSpecialkeyword($keyword_module,$name,$time,$id,$limit=20)
    {
        $list = D('Common/Logic/SpecialkeywordLogic')->getNewSpecialkeyword($keyword_module,$name,$time,$id,$limit);
        foreach ($list as $key => $value) {
            if($value["keyword_module"] == 1){
                $list[$key]["url"] =$this->SCHEME_HOST['scheme_full'] .C('QZ_YUMINGWWW')."/gonglue/zs/".$value["href"]."/";
            }else{
                $list[$key]["url"] = $this->SCHEME_HOST['scheme_full'] .C('QZ_YUMINGWWW')."/baike/zs/".$value["href"]."/";
            }
        }
        return array("list"=>$list);
    }

    /**
     * 根据数组中的词获取三个相关的关键字
     * @param  string $value [description]
     * @return [type]        [description]
     */
    private function getSpecialkeyByArray($keyword_module,$data)
    {
        $list = D('Common/Logic/SpecialkeywordLogic')->getSpecialkeyByArray($keyword_module,$data);
        foreach ($list as $key => $value) {
            if($value["keyword_module"] == 1){
                $list[$key]["url"] = $this->SCHEME_HOST['scheme_full'] .C('QZ_YUMINGWWW')."/gonglue/zs/".$value["href"]."/";
            }else{
                $list[$key]["url"] = $this->SCHEME_HOST['scheme_full'] .C('QZ_YUMINGWWW')."/baike/zs/".$value["href"]."/";
            }
        }
        return array("list"=>$list);
    }
}
