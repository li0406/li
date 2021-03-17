<?php

namespace Home\Controller;

use Common\Model\AskModel;
use Common\Model\Logic\ThematicWordsLogicModel;
use Common\Model\Logic\WwwArticleClassLogicModel;
use Common\Model\Service\ElasticSearchServiceModel;
use Home\Common\Controller\HomeBaseController;
use Common\Model\Logic\TuLogicModel;

class ZixunController extends HomeBaseController
{
    protected $tuLogic;

    public function __construct()
    {
        parent::__construct();
        $this->tuLogic = new TuLogicModel();
    }

    public function _initialize()
    {
        parent::_initialize();
        //判断是否是搜索引擎蜘蛛
        $robotIsTrue = B("Common\Behavior\RobotCheck");
        //$robotIsTrue = true; //debug 警告线上应该为注释状态
        if (true === $robotIsTrue) {
            $this->assign('robot', 1);
        }

        $uri = $_SERVER['REQUEST_URI'];
        preg_match('/html$/',$uri,$m);
        if (count($m) == 0) {
            preg_match('/\/$/',$uri,$m);
            $parse = parse_url($uri);
            if (count($m) == 0 && empty($parse["query"])) {
                header( "HTTP/1.1 301 Moved Permanently");
                header("Location:  ".$this->SCHEME_HOST['scheme_full'] . C("QZ_YUMINGWWW").$uri."/");
            }
        }

        if (!empty($this->cityInfo["bm"])) {
            if (!$robotIsTrue) {
                $this->assign("headerTmp", 1);
            }
        }
        if(empty($this->cityInfo["bm"])){
            $t = T("Home@Index:header");
        }else{
            if(!$robotIsTrue){
                $t = T("Sub@Index:header");
            }
        }
        $headerTmp = $this->fetch($t);
        $this->assign("headerTmp",$headerTmp);
        $this->assign("header_search", 3);
        //添加顶部搜索栏信息
        $this->assign('serch_uri', 'gonglue/search');
        $this->assign('serch_type', '装修攻略');
        $this->assign('holdercontent', '了解相关的装修资讯知识');
    }

    public function index()
    {
        $SCHEME_HOST = $this->SCHEME_HOST;

        //跳转到手机端
        if (ismobile() && parse_url($_SERVER['REQUEST_URI'])['path'] == '/gonglue/') {
            header("Location: " . $SCHEME_HOST['scheme_full'].'m.'.$SCHEME_HOST['domain'] . '/gonglue/');
            exit();
        }
        $wwwArticle = new \Common\Model\WwwArticleModel();
        //装修指南
        $zxznList = S("Cache:zx:zhinan");
        if (!$zxznList) {
            $zxfgList = $wwwArticle->getGonglueIndexList(121);//装修风格
            $jubuList = $wwwArticle->getGonglueIndexList(105);//局部装修
            $zxfsList = $wwwArticle->getGonglueIndexList(114);//装修风水
            $jushList = $wwwArticle->getGonglueIndexList(141);//家居生活
            $hyzxList = $wwwArticle->getGonglueIndexList(138);//行业资讯
            $zxznList = [
                "zxfgList" => $zxfgList,
                "jubuList" => $jubuList,
                "zxfsList" => $zxfsList,
                "jushList" => $jushList,
                "hyzxList" => $hyzxList,
            ];
            S("Cache:zx:zhinan", $zxznList, 900);
        }

        //房产知识
        $fczsList = S("Cache:zx:fangchanzhishi");
        if (!$fczsList) {
            $mfzsList = $wwwArticle->getGonglueIndexList(439);//买房知识
            $maifangzhishiList = $wwwArticle->getGonglueIndexList(440);//卖房知识
            $zfzsList = $wwwArticle->getGonglueIndexList(441);//租房知识
            $fczsList = [
                'mfzsList' => $mfzsList,
                'maifangzhishiList' => $maifangzhishiList,
                'zfzsList' => $zfzsList
            ];
            S("Cache:zx:fangchanzhishi", $fczsList, 900);
        }

        //获取banner
        $banner = S("Cache:gonglue:index:banner");
        if (!$banner) {
            $banner = D("Meitu")->getGonglueBanner();
            $banner = multi_array_sort($banner, 'order_id',SORT_DESC);
            //foreach ($banner as $glbkey => &$glbvalue) {
            //    $glbvalue['url'] = str_replace(C("QZ_YUMING"),$SCHEME_HOST['domain'],$glbvalue['url']);
            //}
            S("Cache:gonglue:index:banner", $banner, 900);
        }
        $this->assign("banner", $banner);

        //选材导购
        $xuancaidgList = S("Cache:xc:xcdg");
        if (!$xuancaidgList) {
            $jiancaiIds = ['145', '146', '147', '148', '149', '150', '151', '152', '153', '154', '155'];
            $jiancaiList = $wwwArticle->getGonglueIndexList($jiancaiIds);//建材
            $rzIds = ['156', '157', '158', '159', '160', '161', '245'];
            $rzList = $wwwArticle->getGonglueIndexList($rzIds);//软装
            $dianqiIds = ['162', '163', '164', '165'];
            $dianqiList = $wwwArticle->getGonglueIndexList($dianqiIds);//电器
            $jiajuIds = ['166', '167'];
            $jiajuList = $wwwArticle->getGonglueIndexList($jiajuIds);//家居
            $xuancaidgList = [
                "jiancaiList" => $jiancaiList,
                "rzList" => $rzList,
                "dianqiList" => $dianqiList,
                "jiajuList" => $jiajuList,
            ];
            S("Cache:xc:xcdg", $xuancaidgList, 900);
        }


        //装修设计
        $zxsjList = S("Cache:zx:zxsj");
        if (!$zxsjList) {
            $zsList = $this->tuLogic->getMeiTuListByFg(11, 0, 4);    //中式
            $osList = $this->tuLogic->getMeiTuListByFg(15, 0, 4);    //欧式
            $dzList = $this->tuLogic->getMeiTuListByFg(13, 4, 4);    //地中海
            $hdList = $this->tuLogic->getMeiTuListByFg(23, 0, 4);    //混搭
            $zxsjList = [
                "zhongshiList" => $zsList,
                "oushiList" => $osList,
                "dizhonghaiList" => $dzList,
                "hundaList" => $hdList,
            ];

            // 只保留部分字段防止缓存内容过大
            foreach ($zxsjList as $key => $item) {
                foreach ($item as $k => $val) {
                    $zxsjList[$key][$k] = [
                        "id" => $val["id"],
                        "type" => $val["type"],
                        "title" => $val["title"],
                        "image_src" => $val["image_src"],
                    ];
                }
            }

            S("Cache:zx:zxsj", $zxsjList, 900);
        }

        //视频学装修
        $videoList = S("Cache:sp:spxzx");
        if (!$videoList) {
            $videoList = D("ArticleVedio")->getVideoListByGonglue();
            S("Cache:sp:spxzx", $videoList, 900);
        }

        //装修知识点
        $zxzsdList = S("Cache:zx:zxzsd");
        if (!$zxzsdList) {
            $zxzsdList = D("Baike")->getArticleByGonglue();
            foreach ($zxzsdList as $key => $value) {
                $zxzsdList[$key]['content'] = strip_tags($value['content']);
                $zxzsdList[$key]['post_time'] = date("Y-m-d", $value['post_time']);
            }
            S("Cache:zx:zxzsd", $zxzsdList, 900);
        }

        //一起看问答
        $wendaList = S("Cache:zx:wenda");
        if (!$wendaList) {
            $wendaList = $this->gerWendaList(3, 'a.post_time desc');
            S('Cache:zx:wenda', $wendaList, 900);
        }

        $this->assign("zxznList", $zxznList);
        $this->assign("fczsList", $fczsList);
        $this->assign("xuancaidgList", $xuancaidgList);
        $this->assign("zxsjList", $zxsjList);
        $this->assign("videoList", $videoList);
        $this->assign("zxzsdList", $zxzsdList);
        $this->assign("wendaList", $wendaList);

        //获取报价模版
        $this->assign("order_source", 20091532);

        //获取友情链接
        $friendLink = S("C:FL:A:gonglue");
        if (!$friendLink) {
            $friendLink['link'] = D("Friendlink")->getFriendLinkList("000001", 1, 'gonglue');
            //获取热门标签
            $hotTag =D("Friendlink")->getFriendLinkList('000001',3,'gonglue');
            $friendLink["hotTags"] = $hotTag;
            S("C:FL:A:gonglue", $friendLink, 900);
        }

        //获取底部弹层
        $this->assign("friendLink", $friendLink);
        $this->assign("tabIndex", 5);
        $this->display("index_p2111");
    }


    /**
     *  [装修流程频道页]
     *
     */
    public function lcpindao()
    {
        $data = S("Cache:gonglue:lcpindao");
        if (!$data) {
            $ids = [88, 93, 101];
            $recommendTags = D("WwwArticleClass")->getZxglClass($ids);

            $zhunbei = D("WwwArticle")->getGonglueList(88, 6);//收房验收
            $shigong = D("WwwArticle")->getGonglueList(93, 6);//装修选材
            $ruzhu = D("WwwArticle")->getGonglueList(101, 6);//检测验收

            $data = [
                'recommendTags' => $recommendTags,
                'zhunbei' => $zhunbei,
                'shigong' => $shigong,
                'ruzhu' => $ruzhu,
            ];
            S("Cache:gonglue:lcpindao", $data, 900);
        }

        $head = [
            "title" => "装修流程_装修步骤_装修知识-齐装网",
            "keywords" => "装修流程,装修施工,装修检测",
            "description" => "齐装网为提供全新的装修流程知识分享，装修流程包括收房验收、找装修公司、设计与预算、装修选材、拆改、水电、防水、泥瓦、木工、油漆、竣工、检测验收、后期配饰、装修保养等。",
        ];
        //获取友情链接
//        $friendLink = S("C:FL:A:gonglue");
//        if (!$friendLink) {
//            $friendLink['link'] = D("Friendlink")->getFriendLinkList("000001", 1, 'gonglue');
//            S("C:FL:A:gonglue", $friendLink, 900);
//        }

        //选中状态
        $this->assign('choose_gonglue', 'lc');
        $this->assign("recommendTags", $data['recommendTags']);
        $this->assign("zhunbei", $data['zhunbei']);
        $this->assign("shigong", $data['shigong']);
        $this->assign("ruzhu", $data['ruzhu']);
        $this->assign("head", $head);
//        $this->assign("friendLink", $friendLink);
        $this->assign("tabIndex", 5);
        $this->display();
    }

    /**
     *  [装修指南频道页]
     *
     */
    public function zxznpindao()
    {

        $data = S("Cache:gonglue:zxznpindao");
        if (!$data) {
            $ids = [121, 105, 114, 370];
            $recommendTags = D("WwwArticleClass")->getZxglClass($ids);

            $fg = D("WwwArticle")->getGonglueList(121, 6);//装修风格
            $jubuzx = D("WwwArticle")->getGonglueList(105, 6);//局部装修
            $fs = D("WwwArticle")->getGonglueList(114, 6);//装修风水
            $yingyang = D("WwwArticle")->getGonglueList(370, 6);//营养
            $hyzx = D("WwwArticle")->getGonglueList(138, 6);//行业资讯
            $data = [
                'recommendTags' => $recommendTags,
                'fg' => $fg,
                'jubuzx' => $jubuzx,
                'fs' => $fs,
                'yingyang' => $yingyang,
                'hyzx' => $hyzx,
            ];
            S("Cache:gonglue:zxznpindao", $data, 900);
        }

        $head = [
            "title" => "家庭装修指南_装修风水_装修风格_提供最有用的装修知识-齐装网",
            "keywords" => "家庭装修、装修风水、装修风格、装修知识",
            "description" => "齐装网装修指南为您提供最实用、最有价值的家庭装修资讯，教您整体和局部装修风格搭配，装修风水禁忌，家居生活小技巧，为业主全面系统地讲解装修知识，避免各类装修误区。",
        ];

        $this->assign("recommendTags", $data['recommendTags']);
        $this->assign("fg", $data['fg']);
        $this->assign("jubuzx", $data['jubuzx']);
        $this->assign("fs", $data['fs']);
        $this->assign("yingyang", $data['yingyang']);
        $this->assign("hyzx", $data['hyzx']);
        $this->assign("head", $head);
        $this->display();
    }


    /**
     *  [选材导购频道页]
     *
     */
    public function xcdgpindao()
    {

        $data = S("Cache:gonglue:xcdgpindao");
        if (!$data) {
            $ids = [143];
            $recommendTags = D("WwwArticleClass")->getZxglClass($ids);

            $jiancai = D("WwwArticle")->getGonglueList([145, 146, 147, 148, 149, 150, 151, 152, 153], 6, 1);//建材选购
            $ruanzhuang = D("WwwArticle")->getGonglueList([156, 245, 160, 161], 6, 1);//软装
            $dianqi = D("WwwArticle")->getGonglueList([162, 163, 164, 165], 6, 1);//家用电器
            $jiaju = D("WwwArticle")->getGonglueList([166, 167], 6, 1);//家具

            $data = [
                'recommendTags' => $recommendTags,
                'jiancai' => $jiancai,
                'ruanzhuang' => $ruanzhuang,
                'dianqi' => $dianqi,
                'jiaju' => $jiaju,
            ];
            S("Cache:gonglue:xcdgpindao", $data, 900);
        }

        $head = [
            "title" => "选材导购_选材知识_选材价格及注意事项-齐装网",
            "keywords" => "建材导购，家具导购，选材技巧，装修评测",
            "description" => "齐装网选材导购专区是一个全方位产品导购知识平台，提供建材、家具、软装、电器这四大方面的选购知识和技巧，解决业主在装修选材上面的难题，让装修变得轻松简单。",
        ];

        $this->assign("recommendTags", $data['recommendTags']);
        $this->assign("jiancai", $data['jiancai']);
        $this->assign("ruanzhuang", $data['ruanzhuang']);
        $this->assign("dianqi", $data['dianqi']);
        $this->assign("jiaju", $data['jiaju']);
        $this->assign("head", $head);
        $this->display();
    }

    /**
     * 装修流程页
     * @return [type] [description]
     */
    public function zxlc()
    {
        //检查静态URL
        $this->checkStaticUrl();
        //跳转到手机端
        if (ismobile()) {
            if (($_SERVER['REQUEST_URI'] == '/gonglue/lc/') || ('/gonglue/list-lc-' != substr($_SERVER['REQUEST_URI'], 0, 17))) {
                header("Location:  ".$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
                exit();
            }
        }

        $zxlc_nav = S('Cache:Zixun:zxlcInfo');
        if (!$zxlc_nav) {
            //左侧菜单栏
            $zxlc_category = D('WwwArticleClass')->getArticleClassById(87);
            $zxq = array_shift($zxlc_category['child']);
            $zxz = array_shift($zxlc_category['child']);
            $zxh = array_shift($zxlc_category['child']);
            $zxlc_nav['zxq'] = !empty($zxq['child']) ? $zxq['child'] : [];
            $zxlc_nav['zxz'] = !empty($zxz['child']) ? $zxz['child'] : [];
            $zxlc_nav['zxh'] = !empty($zxh['child']) ? $zxh['child'] : [];
            S('Home:Zixun:zxlc:nav:lc', $zxlc_nav, 10800);
        }
        $this->assign("zxlc_nav", $zxlc_nav);


        $category = I('get.category', '', 'trim');
        if (!empty($category)) {
            if ($category == 'zhunbei' || $category == 'shigong' || $category == 'ruzhu'){
            	//用户页面无此路由地址--SEO需求
                $categoryClass = S('Cache:Home:Zixun:Zxlc:category:' . $category);
                if (!$categoryClass) {
    				$categoryClass = D("WwwArticleClass")->getClassByShortName($category);
                    S('Cache:Home:Zixun:Zxlc:category:' . $category, $categoryClass, 900);
                }

                $category_ids = S('Cache:Home:Zixun:Zxlc:category:' . $category . ':category_ids');
                if (!$category_ids) {
				    $category_ids = D("WwwArticleClass")->getZxlcChildren($categoryClass['id']);
                    $category_ids[] = $categoryClass['id'];
                    S('Cache:Home:Zixun:Zxlc:category:' . $category . ':category_ids', $category_ids, 900);
                }
			} else {
                //获取该类别的编号
                $categoryClass = S('Cache:Home:Zixun:Zxlc:category:' . $category);
                if (!$categoryClass) {
                    $categoryClass = D("WwwArticleClass")->getArticleClassByShortname($category);
                    S('Cache:Home:Zixun:Zxlc:category:' . $category, $categoryClass, 900);
                }

                $category_ids[] = $categoryClass["id"];
                $this->assign("category", $categoryClass);
            }
        } else {
            $category = 'lc';
        }

        if (!empty($categoryClass['parent'])) {
            $info['hotarticles'] = S('Home:Zixun:zxlc:hotarticles:' . $categoryClass['parent']);
            if (empty($info['hotarticles'])) {
                $classes = D('WwwArticleClass')->getArticleClassChildrenById($categoryClass['parent']['id']);
                if (!empty($classes)) {
                    $siblings = [];
                    foreach ($classes as $key => $value) {
                        $siblings[] = $value['id'];
                    }
                }
                $info['hotarticles'] = D("WwwArticle")->getRecommendArticles($siblings, 8);
            }
            S('Home:Zixun:zxlc:hotarticles:' . $categoryClass['parent'], $info['hotarticles'], 10800);
        } else {
            $info['hotarticles'] = S('Home:Zixun:All:hotarticles');
            if (empty($info['hotarticles'])) {
                $info['hotarticles'] = D("WwwArticle")->getRecommendArticles('', 8);
            }
            S('Home:Zixun:All:hotarticles', $info['hotarticles'], 10800);
        }

        //获取咨询文章
        $newArticles = $this->getNewZxlcArticleList($category_ids, $category);
        $zxlcInfo["articles"] = $newArticles["articles"];
        $zxlcInfo["page"] = $newArticles["page"];

        //20160824 获取canonical属性,分页不添加canonical
        // if($articles['nowPage'] == 1){
        if (I('get.category') == '') {
            $info['canonical'] = '/gonglue/lc/';
        } else {
            $info['canonical'] = '/gonglue/' . I('get.category') . '/';
        }
        // }
        //TKD
        $pageContent = $newArticles['nowPage'] > 1 ? "-第" . $newArticles['nowPage'] . "页" : "";
        if (I("get.category") != "") {
            //获取该分类的分类信息
            $keys["title"] = $categoryClass["title"] . $pageContent;
            $keys["keywords"] = $categoryClass["keywords"];
            $keys["description"] = $categoryClass["description"];
        } else if ('lc' == $category) {
            //动态获取流程的tkd
            $keys = S('Home:Zixun:zxlc:keys');
            if (empty($keys)) {
                $temp = D("WwwArticleClass")->getArticleClassByShortname('lc');
                $keys["title"] = $temp['title'];
                $keys["keywords"] = $temp['keywords'];;
                $keys["description"] = $temp['description'];
                S('Home:Zixun:zxlc:keys', $keys, 300);
            }
            $keys["title"] = $keys["title"] . $pageContent;
        } else {
            //添加关键字、描述、标题
            $keys["title"] = "收房注意事项_验房注意事项_收房攻略" . $pageContent;
            $keys["keywords"] = "装修流程,装修施工,装修检测";
            $keys["description"] = "齐装网为提供全新的装修流程知识分享，装修流程包括收房验收、找装修公司、设计与预算、装修选材、拆改、水电、防水、泥瓦、木工、油漆、竣工、检测验收、后期配饰、装修保养等";
        }
        $this->assign("keys", $keys);

        //获取底部弹层
        $t = T("Common@Order/zb_bottom_s");
        $adv_bottom = $this->fetch($t);
        $this->assign("adv_bottom", $adv_bottom);

        //获取报价模版
        $this->assign("order_source", 104);
        $t = T("Common@Order/orderTmp");
        $orderTmp = $this->fetch($t);
        $this->assign("orderTmp", $orderTmp);

        //获取友情链接
        if ($_SERVER['REQUEST_URI'] == '/gonglue/lc/') {
            $friendLink = S("C:FL:A:lc");
            if (!$friendLink) {
                $friendLink['link'] = D("Friendlink")->getFriendLinkList("000001", 1, 'lc');
                S("C:FL:A:lc", $friendLink, 900);
            }
            if (count($friendLink['link']) > 0) {
                $this->assign("friendLink", $friendLink);
            }
        }

        $this->assign("listInfo", $zxlcInfo);

        //添加顶部搜索栏信息
        $this->assign('serch_uri', 'gonglue/search');
        $this->assign('serch_type', '装修攻略');
        $this->assign('holdercontent', '了解相关的装修资讯知识');
        //添加选中效果
        $this->assign('choose_gonglue', 'lc');
        //导航栏标识
        $this->assign("tabIndex", 5);
        $this->assign('info', $info);
        $this->display("zxlc_p2111");
    }

    //选材导购页面，单独提出来
    public function xcdg()
    {
        //检查静态URL
        $this->checkStaticUrl();

        //跳转到手机端
        if (ismobile()) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location:  ".$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }

        $zxlcInfo = array();
        //获取左侧分类
        $typeArr = S('Cache:Zixun:typeArrXcdg');
        if (!$typeArr) {
            //左侧菜单栏
            $typeArr = D("WwwArticleClass")->getArticleClassById(143);
            S('Cache:Zixun:typeArrXcdg', $typeArr, 900);
        }

        //由于SEO添加分类出现问题，要求强行添加二级菜单，故此处文章分类进行强行分配处理，输出的内容已经写死
        $outPutTypes[0]['title'] = "建材";
        $outPutTypes[0]['content'] = [
            0 => $typeArr['child'][145],
            1 => $typeArr['child'][146],
            2 => $typeArr['child'][147],
            3 => $typeArr['child'][148],
            4 => $typeArr['child'][149],
            5 => $typeArr['child'][150],
            6 => $typeArr['child'][151],
            7 => $typeArr['child'][152],
            8 => $typeArr['child'][153],
            9 => $typeArr['child'][154],
            10 => $typeArr['child'][155]
        ];
        $outPutTypes[1]['title'] = "软装";
        $outPutTypes[1]['content'] = [
            0 => $typeArr['child'][156],
            1 => $typeArr['child'][245],
            2 => $typeArr['child'][157],
            3 => $typeArr['child'][158],
            4 => $typeArr['child'][159],
            5 => $typeArr['child'][160],
            6 => $typeArr['child'][161]
        ];
        $outPutTypes[2]['title'] = "电器";
        $outPutTypes[2]['content'] = [
            0 => $typeArr['child'][162],
            1 => $typeArr['child'][163],
            2 => $typeArr['child'][164],
            3 => $typeArr['child'][165]
        ];
        $outPutTypes[3]['title'] = "家具";
        $outPutTypes[3]['content'] = [
            0 => $typeArr['child'][166],
            1 => $typeArr['child'][167]
        ];
        $this->assign("newlist", $outPutTypes);//左侧菜单栏数据

        $category = I("get.category");
        $categoryId = I("get.categoryId");

        //获取该类别的编号,根据ID或者shortname获得
        if (!empty($categoryId)) {
            $categoryClass = D("WwwArticleClass")->getArticleClassByCatagoryId($categoryId);
        } else {
            $categoryClass = D("WwwArticleClass")->getArticleClassByShortname($category);
        }
        //输出当前分类信息
        $this->assign("category", $categoryClass);
        if ($categoryClass['level'] == 1) {
            $result = $typeArr['ids'];
            $secTypes = $typeArr['child'][$categoryClass['id']]['child'];
        } elseif ($categoryClass['level'] == 2) {
            $result = $typeArr['child'][$categoryClass["id"]]['ids'];
            $secTypes = $typeArr['child'][$categoryClass['id']]['child'];
        } else {
            $result[] = $categoryClass["id"];
            //查询父级
            $fatherCategory = D("WwwArticleClass")->getArticleClassByCatagoryId($categoryClass["pid"]);
            $secTypes = $typeArr['child'][$fatherCategory['id']]['child'];
            //文章列表顶部菜单标识
            $nowType['id'] = $categoryClass["id"];
            $nowType['classname'] = $categoryClass["classname"];
            $this->assign('nowType', $nowType);
        }
        $secTypesNum = count($secTypes);
        $this->assign('secTypesNum', $secTypesNum);
        $this->assign('secTypes', $secTypes);//顶部子菜单数据

        /*if (!empty($categoryClass['parent'])) {
            $info['hotarticles'] = S('Home:Zixun:xcdg:hotarticles:' . $categoryClass['parent']);
            if (empty($info['hotarticles'])) {
                $classes = D('WwwArticleClass')->getArticleClassChildrenById($categoryClass['parent']['id']);
                if (!empty($classes)) {
                    $siblings = [];
                    foreach ($classes as $key => $value) {
                        $siblings[] = $value['id'];
                    }
                }
                $info['hotarticles'] = D("WwwArticle")->getRecommendArticles($siblings, 8);
            }
            S('Home:Zixun:xcdg:hotarticles:' . $categoryClass['parent'], $info['hotarticles'], 10800);
        } else {
            $info['hotarticles'] = S('Home:Zixun:All:hotarticles');
            if (empty($info['hotarticles'])) {
                $info['hotarticles'] = D("WwwArticle")->getRecommendArticles('', 8);
            }
            S('Home:Zixun:All:hotarticles', $info['hotarticles'], 10800);
        }*/


        if (!empty($_GET['type'])) {
            unset($result);
            $result[] = $_GET['type'];
        }


        //获取咨询文章
        $newArticles = $this->getNewXcdgArticleList($result, $categoryClass['id'], $categoryClass['shortname']);
        $zxlcInfo["articles"] = $newArticles["articles"];
        $zxlcInfo["page"] = $newArticles["page"];

        //canonical
        // if($articles['nowPage'] == 1){
        $info['canonical'] = '/gonglue/' . I('get.category') . '/';
        // }
        //TKD
        $pageContent = $newArticles['nowPage'] > 1 ? "-第" . $newArticles['nowPage'] . "页" : "";
        $keys["title"] = $categoryClass["title"] . $pageContent;
        $keys["keywords"] = $categoryClass["keywords"];
        $keys["description"] = $categoryClass["description"];
        $this->assign("keys", $keys);

        //获取底部弹层
        $t = T("Common@Order/zb_bottom_b");
        $adv_bottom = $this->fetch($t);
        $this->assign("adv_bottom", $adv_bottom);
        //添加顶部搜索栏信息
        $this->assign('serch_uri', 'gonglue/search');
        $this->assign('serch_type', '装修攻略');
        $this->assign('holdercontent', '了解相关的装修资讯知识');

        //获取报价模版
        $this->assign("order_source", 104);
        $t = T("Common@Order/orderTmp");
        $orderTmp = $this->fetch($t);
        $this->assign("orderTmp", $orderTmp);

        $this->assign("listInfo", $zxlcInfo);
        //导航栏标识
        $this->assign("tabIndex", 5);
        $this->assign('info', $info);
        $this->display("xcdg_p2111");
    }

    /**
     * 装修列表页
     * @return [type] [description]
     */
    public function zxlist()
    {
        //检查静态URL
        $this->checkStaticUrl();

        //跳转到手机端
        if (ismobile()) {
            header("Location:  ".$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }
        $category = I('get.category', '', 'trim');
        if (!empty($category)) {
            $pathInfo = pathinfo($_SERVER["PATH_INFO"]);
            if ((is_numeric($_GET["category"]) || $pathInfo["basename"] == "history")) {
                //如果是老站的链接，通过ID查询该类的ID
            } else {
                //获取该类别的编号
                $categoryClass = D("WwwArticleClass")->getArticleClassByShortname($category);

                //判断类别
                if ($categoryClass["pid"] == 0) {
                    $categoryClassId = $categoryClass["id"];
                } else {
                    if (empty($categoryClass["parent"]["parent"])) {
                        $categoryClassId = $categoryClass["pid"];
                    } else {
                        $categoryClassId = $categoryClass["parent"]["pid"];
                    }
                }

                $cat = D("WwwArticleClass")->getArticleClassById($categoryClassId);
                $this->assign("cat", $cat);

                if (!empty($categoryClass["parent"]["parent"])) {
                    $cat = $cat["child"][$categoryClass["pid"]];
                }
                foreach ($cat["child"] as $key => $value) {
                    $catList[] = array(
                        "classname" => $value["classname"],
                        "shortname" => $value["shortname"]
                    );
                }

                $bread = array(
                    "classname" => $cat["classname"],
                    "shortname" => $cat["shortname"]
                );

                if ($categoryClass["pid"] == 0) {
                    $result = $cat["ids"];
                } else {
                    if (count($cat["child"][$categoryClass["id"]]["ids"]) > 0) {
                        $result = $cat["child"][$categoryClass["id"]]["ids"];
                    } else {
                        $result = $categoryClass['id'];
                    }
                    $bread["child"] = array(
                        "classname" => $cat["child"][$categoryClass["id"]]["classname"],
                        "shortname" => $cat["child"][$categoryClass["id"]]["shortname"]
                    );
                }
                $this->assign("bread", $bread);
                $this->assign("catList", $catList);
                $this->assign("category", $categoryClass);

                //导航栏 原始逻辑太乱，没有时间整理，暂时在此处增加 产品调整逻辑 ，导航栏缓存和移动端一致
                //装修风格
                $fengge = S('Cache:m:zxlc:fengge');
                if (!$fengge) {
                    $fengge = D("WwwArticleClass")->getArticleClassChildrenById(121,'seq,id');
                    S('Cache:m:zxzn:fengge', $fengge, 900);
                }
                $this->assign("fengge", $fengge);
                //空间搭配（局部装修）
                $kongjian = S('Cache:m:zxlc:kongjian');
                if (!$kongjian) {
                    $kongjian = D("WwwArticleClass")->getArticleClassChildrenById(105,'seq,id');
                    S('Cache:m:zxzn:kongjian', $kongjian, 900);
                }
                $this->assign("kongjian", $kongjian);
                //家居风水
                $fengshui = S('Cache:m:zxlc:fengshui');
                if (!$fengshui) {
                    $fengshui = D("WwwArticleClass")->getArticleClassChildrenById(114,'seq,id');
                    S('Cache:m:zxzn:fengshui', $fengshui, 900);
                }
                $this->assign("fengshui", $fengshui);

                //新闻资讯
                $xinwenzixun = S('Cache:m:zxlc:xinwenzixun');
                if (!$xinwenzixun) {
                    $xinwenzixun = D("WwwArticleClass")->getArticleClassChildrenById(138,'seq,id');
                    S('Cache:m:zxzn:xinwenzixun', $xinwenzixun, 900);
                }
                $this->assign("xinwenzixun", $xinwenzixun);
            }
        }

        if (empty($result)) {
            $this->error();
        }

        if (!empty($categoryClass['parent'])) {
            $info['hotarticles'] = S('Home:Zixun:zxlist:hotarticles:' . $categoryClass['parent']);
            if (empty($info['hotarticles'])) {
                $classes = D('WwwArticleClass')->getArticleClassChildrenById($categoryClass['parent']['id']);
                $siblings = array_column(empty($classes) ? [] : $classes, 'id');
                $info['hotarticles'] = D("WwwArticle")->getRecommendArticles($siblings, 8);
            }
            S('Home:Zixun:zxlist:hotarticles:' . $categoryClass['parent'], $info['hotarticles'], 10800);
        } else {
            $info['hotarticles'] = S('Home:Zixun:All:hotarticles');
            if (empty($info['hotarticles'])) {
                $info['hotarticles'] = D("WwwArticle")->getRecommendArticles('', 8);
            }
            S('Home:Zixun:All:hotarticles', $info['hotarticles'], 10800);
        }

        //获取资讯文章
        $newArticles = $this->getNewZxArticleList($result, $category);
        $listInfo["articles"] = $newArticles["articles"];
        $listInfo["page"] = $newArticles["page"];
        $this->assign("listInfo", $listInfo);

        //canonical
        if ($newArticles['nowPage'] == 1) {
            $info['canonical'] = '/gonglue/' . $category . '/';
        }
        //TKD
        $pageContent = $newArticles['nowPage'] > 1 ? "-第" . $newArticles['nowPage'] . "页" : "";
        $keys["title"] = $categoryClass["title"] . $pageContent;
        $keys["keywords"] = $categoryClass["keywords"];
        $keys["description"] = $categoryClass["description"];
        $this->assign("keys", $keys);

        //获取报价模版
        $this->assign("order_source", 104);
        $t = T("Common@Order/orderTmp");
        $orderTmp = $this->fetch($t);
        $this->assign("orderTmp", $orderTmp);

        //装修风水添加友链
        if ($category == "fs") {
            //获取友情链接
            $friendLink = S("C:FL:A:fengshui");
            if (!$friendLink) {
                $friendLink['link'] = D("Friendlink")->getFriendLinkList("000001", 1, 'fengshui');
                S("C:FL:A:fengshui", $friendLink, 900);
            }
            if (count($friendLink['link']) > 0) {
                $this->assign("friendLink", $friendLink);
            }
        }

        //canonical标签
        if (!empty($category)) {
            $page = I("get.p");
            if (empty($page) || $page == 1) {
                $info['canonical'] = '/gonglue/' . $category . '/';
            }
        }

        //添加顶部搜索栏信息
        $this->assign('serch_uri', 'gonglue/search');
        $this->assign('serch_type', '装修攻略');
        $this->assign('holdercontent', '了解相关的装修资讯知识');

        //获取底部弹层
        $t = T("Common@Order/zb_bottom_s");
        $adv_bottom = $this->fetch($t);
        $this->assign("adv_bottom", $adv_bottom);

        //header搜索框搜索条件绑定
        $this->assign("header_search", 2);
        $this->assign("info", $info);
        $this->display("zxlist_p2111");
    }


    function getJiaJuShenHuo()
    {
        $data = [
            [
                'id' => '375',
                'classname' => '饮食资讯',
                'shortname' => 'yszx',
            ],
            [
                'id' => '377',
                'classname' => '食疗养生',
                'shortname' => 'slys',
            ],
            [
                'id' => '378',
                'classname' => '营养指南',
                'shortname' => 'yyzn',
            ],
            [
                'id' => '372',
                'classname' => '孕产指南',
                'shortname' => 'yzzn',
            ],
        ];
        return $data;
    }

    /**
     * 文章搜索页
     */
    public function search()
    {
        $pageIndex = 1;
        $pageCount = 10;
        if (I("get.p") != "") {
            $pageIndex = I("get.p");
        }

        if (I("get.keyword") != "") {
            $keyword = I("get.keyword");
            if (!checkKeyword($keyword)) {
                $this->_error();
            }
            $keyword = remove_xss($keyword);
            $this->assign("keyword", $keyword);
        }

        if (empty($keyword)) {
            $this->_error();
        }

        //热门文章
        $info['hotarticles'] = S('Home:Zixun:All:hotarticles');
        if (empty($info['hotarticles'])) {
            $info['hotarticles'] = D("WwwArticle")->getRecommendArticles('', 8);
        }
        S('Home:Zixun:All:hotarticles', $info['hotarticles'], 10800);

        //获取最新的4篇推荐文章
        $newRecommendArticles = S("Cache:new:recommendArticles");
        if (!$newRecommendArticles) {
            $newRecommendArticles = D("WwwArticle")->getNewRecommendArticles();
            foreach ($newRecommendArticles as $key => $value) {
                $newRecommendArticles[$key]['content'] = mb_substr(strip_tags($value['content']), 0, 140, "UTF-8");
                $newRecommendArticles[$key]['addtime'] = date("Y-m-d H:i:s", $value['addtime']);
            }
            S("Cache:new:recommendArticles", $newRecommendArticles, 900);
        }
        $this->assign("newRecommendArticles", $newRecommendArticles);

        //获取资讯文章
        /*$articles = $this->getSearchArticleList($pageIndex,$pageCount,$keyword,true);
        $listInfo["articles"] = $articles["articles"];
        $listInfo["page"] = $articles["page"];
        $listInfo["hotarticles"] = $articles["hotarticles"];*/

        $newArticles = $this->getNewSearchArticleList($pageIndex, $pageCount, $keyword);
        $resultFlag = 1;
        if (empty($newArticles)) {
            $resultFlag = 0;
        }
        $this->assign("resultFlag", $resultFlag);
        $listInfo["articles"] = $newArticles["articles"];
        $listInfo["page"] = $newArticles["page"];

        $this->assign("listInfo", $listInfo);

        //tkd
        $keys["title"] = "搜索结果页";
        $keys["keywords"] = "";
        $keys["description"] = "";
        $this->assign("keys", $keys);

        //获取报价模版
        $this->assign("order_source", 104);
        $t = T("Common@Order/orderTmp");
        $orderTmp = $this->fetch($t);
        $this->assign("orderTmp", $orderTmp);

        //添加顶部搜索栏信息
        $this->assign('serch_uri', 'gonglue/search');
        $this->assign('serch_type', '装修攻略');
        $this->assign('holdercontent', '了解相关的装修资讯知识');

        //获取底部弹层
        $t = T("Common@Order/zb_bottom_s");
        $adv_bottom = $this->fetch($t);
        $this->assign("adv_bottom", $adv_bottom);

        //header搜索框搜索条件绑定
        $this->assign("header_search", 2);
        $this->assign("info", $info);
        $this->display("search_p2111");
    }

    /**
     * 攻略子分类列表页：选材导购/装修流程/装修指南
     * @return void
     */
    public function lclist()
    {
        $category = I('get.category', '');
        //脚踢线改成踢脚线,对应URL修改
        if ($_SERVER['REQUEST_URI'] == '/gonglue/jiaotixian' || $_SERVER['REQUEST_URI'] == '/gonglue/jiaotixian/') {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location:  ".$this->SCHEME_HOST['scheme_full'] . C("QZ_YUMINGWWW") . '/gonglue/tijiaoxian/');
            exit;
        }

        if (empty($category)) {
            $this->_empty();
            exit;
        }

        //获取该类别的编号
        $categoryClass = D("WwwArticleClass")->getArticleClassByShortname($category);
        if (empty($categoryClass)) {
            $this->_empty();
            exit;
        }

        // 获取选材导购分类下的子分类
        $sonIds = D("WwwArticleClass")->getArticleClassById(143);
        $xcdg_Ids = $sonIds['ids'];

        // 获取装修流程分类下的子分类
        $lcIds = D("WwwArticleClass")->getArticleClassById(87);
        $lc_Ids = $lcIds['ids'];

        //查询当前栏目是否有分词
        $model = new WwwArticleClassLogicModel();
        $word = $model->getArticleClassParticiple($categoryClass['id'],1);

        if ($word != "") {
            $service = new ElasticSearchServiceModel();
            $participle_words =  $service->getContentLabel($word,1,10);
            $this->assign("participle_words",$participle_words);
        }

        if (in_array($categoryClass['id'], array_merge(['143'], $xcdg_Ids))) {
            $this->xcdg();
        } elseif (in_array($categoryClass['id'], array_merge(['87'], $lc_Ids))) {
            $this->zxlc();
        } else {

            $this->zxlist();
        }
    }

    /**
     * 检测并跳转到静态URL规则
     */
    private function checkStaticUrl()
    {
        $info = parse_url($_SERVER['REQUEST_URI']);
        $path = array_filter(explode('/', trim($info['path'], '/')));
        $query = array_filter(explode('&', $info['query']));
        $p = I("get.p");
        //1.只有分页参数；2.URL类似'/gonglue/lc'的
        if (count($query) == 1 && !empty($p) && count($path) == 2 && $path[0] == 'gonglue' && false === strpos($path['1'], '.html')) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: '.$this->SCHEME_HOST["scheme_full"] . C('QZ_YUMINGWWW') . '/gonglue/list-' . $path['1'] . '-' . $p . '.html');
            exit();
        }
    }

    /**
     * 获取选材导购页面文章列表
     * @return [type] [description]
     */
    private function getXcdgArticleList($category_ids = '', $category, $pageCount = 5, $isTop = true, $order = false)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
        if (empty($category_ids)) {
            //根据ID查询相对应的文章类别
            $class = D("WwwArticleClass")->getArticleClassById(87);
        } else {
            $class["ids"] = $category_ids;
        }
        $count = D("WwwArticle")->getArticleListCount($class["ids"]);
        if ($count > 0) {
            import('Library.Org.Page.SPage');
            $page = new \SPage($count, $pageCount, array(
                'templet' => '/gonglue/list-' . $category . '-[PAGE].html',
                'firstUrl' => '/gonglue/' . $category . '/'
            ));
            $pageTmp = $page->show();
            $ids[] = array_unique($class["ids"]);
            $result = D("WwwArticle")->getArticleListByIds($ids, ($page->nowPage - 1) * $pageCount, $pageCount, "", false, true);
            foreach ($result as $key => $value) {
                $value['subtitle'] = rtrim($value["subtitle"]);
                $value['subtitle'] = substr($value['subtitle'], 0, -6);

                if (!empty($value["imgs"])) {
                    $exp = explode(",", $value["imgs"]);
                    $exp = array_filter($exp);
                    foreach ($exp as $k => $val) {
                        if (!strpos($val, C('QINIU_DOMAIN'))) {
                            $path = $this->SCHEME_HOST['scheme_full'] .C('STATIC_HOST1') . "/" . $val;
                            $exp[$k] = $path;
                        }
                    }
                    $value["imgs"] = $exp;
                }
                $list[$key] = $value;
            }
            $result = D("WwwArticle")->getArticleListByIds($ids, ($page->nowPage - 1) * $pageCount, $pageCount, "", false, $order);
            foreach ($result as $key => $value) {
                $result[$key]["subtitle"] = rtrim($value["subtitle"]);
                $result[$key]["subtitle"] = substr($result[$key]["subtitle"], 0, -6);

                if (!empty($value["imgs"])) {
                    $exp = explode(",", $value["imgs"]);
                    $exp = array_filter($exp);
                    foreach ($exp as $k => $val) {
                        if (!strpos($val, C('QINIU_DOMAIN'))) {
                            $path = $this->SCHEME_HOST['scheme_full'] . C('STATIC_HOST1') . "/" . $val;
                            $exp[$k] = $path;
                        }
                    }
                    $result[$key]["imgs"] = $exp;
                }
            }
            return array("articles" => $list, "page" => $pageTmp, "hotarticles" => $result, "nowPage" => $page->nowPage);
        }
    }


    /**
     * 获取选材导购页面文章列表-改版
     * @param $categoryIds [当前分类下的二级分类的id集合]
     * @param $categoryId [当前分类的id]
     * @param $category [当前分类的shortname]
     * @param int $pageIndex [当前页面]
     * @param int $pageCount [一页显示数据量]
     * @return array
     *
     */
    public function getNewXcdgArticleList($categoryIds, $categoryId, $category, $pageIndex = 1, $pageCount = 10)
    {

        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        if (empty($categoryIds)) {
            //根据ID查询相对应的文章类别
            $class = D("WwwArticleClass")->getArticleClassById(87);
        } else {
            array_unshift($categoryIds, $categoryId);
            $class["ids"] = $categoryIds;
        }

        $count = D("WwwArticle")->getNewXcdgArticleCount($class["ids"]);
        $pageNum = (int)ceil($count / $pageCount);
        if ($pageNum == 0) {
            $pageNum = 1;
        }

        if ($count > 0) {
            import('Library.Org.Page.SPage');
            $page = new \SPage($count, $pageCount, array(
                'templet' => '/gonglue/list-' . $category . '-[PAGE].html',
                'firstUrl' => '/gonglue/' . $category . '/'
            ));
            $pageTmp = $page->show();
            $ids = array_unique($class["ids"]);

            //获取分页的数据
            if ($page->nowPage <= $pageNum) {
                $result = D("WwwArticle")->getNewXcdgArticleList($ids, ($page->nowPage - 1) * $pageCount, $pageCount, '');
            }

            //没有非推荐数据时的数据处理
            if ($page->nowPage > $pageNum) {
                $result = D("WwwArticle")->getNewXcdgArticleList($ids, ($pageNum - 1) * $pageCount, $pageCount, '');
            }

            foreach ($result as $key => $value) {
                $result[$key]["subtitle"] = rtrim($value["subtitle"]);
                $result[$key]["subtitle"] = substr($result[$key]["subtitle"], 0, -6);

                if (!empty($value["imgs"])) {
                    $exp = explode(",", $value["imgs"]);
                    $exp = array_filter($exp);
                    foreach ($exp as $k => $val) {
                        if (!strpos($val, C('QINIU_DOMAIN'))) {
                            $path = $this->SCHEME_HOST['scheme_full'] . C('STATIC_HOST1') . "/" . $val;
                            $exp[$k] = $path;
                        }
                    }
                    $result[$key]["imgs"] = $exp;
                }
            }
            return array("page" => $pageTmp, "articles" => $result, "nowPage" => $page->nowPage);
        }
    }


    /**
     * 获取浏览量最大的文章
     * @return [type] [description]
     */
    private function getHotArticle($limit)
    {
        $articles = D("WwwArticle")->getHotArticle($limit);
        foreach ($articles as $key => $value) {
            $articles[$key]['subtitle'] = mbstr($articles[$key]['subtitle'], 0, 55, "utf-8", false);
            unset($articles[$key]['content']);
        }
        return $articles;
    }

    /**
     * 获取资讯列表页文章列表
     * @param  string $id [分类编号]
     * @param  integer $pageIndex [description]
     * @param  integer $pageCount [description]
     * @param  boolean $isTop [是否置顶]
     * @return [type]             [description]
     */
    private function getZxArticleList($category_ids = '', $category = '', $pageCount = 5, $keyword = "", $isTop = true)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        //获取文章的分类
        if (empty($category_ids)) {
            $ids = D("WwwArticleClass")->getAllArticleClass();
        } else {
            $ids = $category_ids;
        }
        $count = D("WwwArticle")->getArticleListCount($ids, $keyword);
        if ($count > 0) {
            import('Library.Org.Page.SPage');
            $page = new \SPage($count, $pageCount, array(
                'templet' => '/gonglue/list-' . $category . '-[PAGE].html',
                'firstUrl' => '/gonglue/' . $category . '/'
            ));
            $pageTmp = $page->show();
            $result = D("WwwArticle")->getArticleListByIds(array($ids), ($page->nowPage - 1) * $pageCount, $pageCount, $keyword, false, true);
            foreach ($result as $key => $value) {
                $result[$key]["img_host"] = "qiniu";
                //如果是老站链接过来的文章，同一用history代替
                if (empty($value["shortname"])) {
                    $result[$key]["shortname"] = "history";
                    $result[$key]["title"] = str_replace("_齐装网", "", $value["title"]);
                    $result[$key]["img_host"] = "";
                }
                if (!empty($value["imgs"])) {
                    $exp = explode(",", $value["imgs"]);
                    $exp = array_filter($exp);
                    foreach ($exp as $k => $val) {
                        if (!strpos($val, C('QINIU_DOMAIN'))) {
                            $path = $this->SCHEME_HOST['scheme_full'] . C('STATIC_HOST1') . "/" . $val;
                            $exp[$k] = $path;
                        }
                    }
                    $result[$key]["imgs"] = $exp;
                }
            }
            $list = $result;
            // 获取热门文章列表
            $result = D("WwwArticle")->getHotArticleListByIds(array($ids), ($page->nowPage - 1) * $pageCount, $pageCount, $keyword);
            foreach ($result as $key => $value) {
                $result[$key]["img_host"] = "qiniu";
                //如果是老站链接过来的文章，同一用history代替
                if (empty($value["shortname"])) {
                    $result[$key]["shortname"] = "history";
                    $result[$key]["title"] = str_replace("_齐装网", "", $value["title"]);
                    $result[$key]["img_host"] = "";
                }
                if (!empty($value["imgs"])) {
                    $exp = explode(",", $value["imgs"]);
                    $exp = array_filter($exp);
                    foreach ($exp as $k => $val) {
                        if (!strpos($val, C('QINIU_DOMAIN'))) {
                            $path = $this->SCHEME_HOST['scheme_full'] . C('STATIC_HOST1') . "/" . $val;
                            $exp[$k] = $path;
                        }
                    }
                    $result[$key]["imgs"] = $exp;
                }
            }
            return array("articles" => $list, "page" => $pageTmp, "hotarticles" => $result, "nowPage" => $page->nowPage);
        }
    }


    /**
     * @param string $category_ids [当前分类的id]
     * @param string $category [当前分类的shortname]
     * @param int $pageIndex
     * @param int $pageCount
     * @return array
     *
     */
    private function getNewZxArticleList($category_ids = '', $category = '', $pageIndex = 1, $pageCount = 10)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        //获取文章的分类
        if (empty($category_ids)) {
            $ids = D("WwwArticleClass")->getAllArticleClass();
        } else {
            $ids = $category_ids;
        }

        $count = D("WwwArticle")->getNewXcdgArticleCount($ids);
        $pageNum = (int)ceil($count / $pageCount);//非推荐数据不足时的所在页数
        if ($pageNum == 0) {
            $pageNum = 1;
        }

        if ($count > 0) {
            import('Library.Org.Page.SPage');
            $page = new \SPage($count, $pageCount, array(
                'templet' => '/gonglue/list-' . $category . '-[PAGE].html',
                'firstUrl' => '/gonglue/' . $category . '/'
            ));
            $pageTmp = $page->show();
            if ($page->nowPage <= $pageNum) {
                $result = D("WwwArticle")->getNewXcdgArticleList($ids, ($page->nowPage - 1) * $pageCount, $pageCount, '');
            }

            //没有非推荐数据时的数据处理
            if ($page->nowPage > $pageNum) {
                $result = D("WwwArticle")->getNewXcdgArticleList($ids, ($pageNum - 1) * $pageCount, $pageCount, '');
            }

            foreach ($result as $key => $value) {
                $result[$key]["img_host"] = "qiniu";
                //如果是老站链接过来的文章，同一用history代替
                if (empty($value["shortname"])) {
                    $result[$key]["shortname"] = "history";
                    $result[$key]["title"] = str_replace("_齐装网", "", $value["title"]);
                    $result[$key]["img_host"] = "";
                }
                if (!empty($value["imgs"])) {
                    $exp = explode(",", $value["imgs"]);
                    $exp = array_filter($exp);
                    foreach ($exp as $k => $val) {
                        if (!strpos($val, C('QINIU_DOMAIN'))) {
                            $path = $this->SCHEME_HOST['scheme_full'] . C('STATIC_HOST1') . "/" . $val;
                            $exp[$k] = $path;
                        }
                    }
                    $result[$key]["imgs"] = $exp;
                }
            }
            return array("articles" => $result, "page" => $pageTmp, "nowPage" => $page->nowPage);
        }
    }

    /**
     * 这个是getNewZxArticleList改版前方法的复制，包含前三个推荐和后七个非推荐数据
     * @param string $category_ids
     * @param string $category
     * @param int $pageIndex
     * @param int $pageCount
     * @param int $recommendPage
     * @param int $norecommendPage
     * @return array
     */
    private function Copy_getNewZxArticleList($category_ids = '', $category = '', $pageIndex = 1, $pageCount = 10, $recommendPage = 3, $norecommendPage = 7)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        //获取文章的分类
        if (empty($category_ids)) {
            $ids = D("WwwArticleClass")->getAllArticleClass();
        } else {
            $ids = $category_ids;
        }

        $count = D("WwwArticle")->getNewXcdgArticleCount($ids);
        $norecommendCount = D("WwwArticle")->getNewXcdgArticleCount($ids, '', 2);//获取不是推荐文章共有多少条数据
        $num = $norecommendCount % $norecommendPage; //每页显示7条非推荐文章,当某页非推荐文章数据不足时,显示的非推荐文章数据量
        $pageNum = (int)ceil($norecommendCount / $norecommendPage);//非推荐数据不足时的所在页数
        if ($pageNum == 0) {
            $pageNum = 1;
        }

        if ($count > 0) {
            import('Library.Org.Page.SPage');
            $page = new \SPage($count, $pageCount, array(
                'templet' => '/gonglue/list-' . $category . '-[PAGE].html',
                'firstUrl' => '/gonglue/' . $category . '/'
            ));
            $pageTmp = $page->show();
            if ($page->nowPage <= $pageNum) {
                $recommendResult = D("WwwArticle")->getNewXcdgArticleList($ids, ($page->nowPage - 1) * $recommendPage, $recommendPage, true);
                $norecommendResult = D("WwwArticle")->getNewXcdgArticleList($ids, ($page->nowPage - 1) * ($pageCount - count($recommendResult)), $pageCount - count($recommendResult), false);
                $result = array_merge($recommendResult, $norecommendResult);
            }

            //非推荐数据不足时的那页数据处理
            if (count($recommendResult) == $recommendPage && count($norecommendResult) < $norecommendPage && $pageNum == ($page->nowPage)) {
                $recommendResult = D("WwwArticle")->getNewXcdgArticleList($ids, ($page->nowPage) * $recommendPage, ($pageCount - $recommendPage - $num), true);
                $result = array_merge($result, $recommendResult);
            }
            $recommendNum = $pageNum * $recommendPage + ($pageCount - $recommendPage - $num);
            //没有非推荐数据时的数据处理
            if ($page->nowPage > $pageNum) {
                $result = D("WwwArticle")->getNewXcdgArticleList($ids, ($page->nowPage - $pageNum - 1) * $pageCount + $recommendNum, $pageCount, true);
            }

            foreach ($result as $key => $value) {
                $result[$key]["img_host"] = "qiniu";
                //如果是老站链接过来的文章，同一用history代替
                if (empty($value["shortname"])) {
                    $result[$key]["shortname"] = "history";
                    $result[$key]["title"] = str_replace("_齐装网", "", $value["title"]);
                    $result[$key]["img_host"] = "";
                }
                if (!empty($value["imgs"])) {
                    $exp = explode(",", $value["imgs"]);
                    $exp = array_filter($exp);
                    foreach ($exp as $k => $val) {
                        if (!strpos($val, C('QINIU_DOMAIN'))) {
                            $path = $this->SCHEME_HOST['scheme_full'] . C('STATIC_HOST1') . "/" . $val;
                            $exp[$k] = $path;
                        }
                    }
                    $result[$key]["imgs"] = $exp;
                }
            }
            return array("articles" => $result, "page" => $pageTmp, "nowPage" => $page->nowPage);
        }
    }
    /**
     * 获取文章搜索列表
     * @param  integer $pageIndex 开始页
     * @param  integer $pageCount 每页数量
     * @param  boolean $isTop 是否置顶
     * @return array              搜索结果数组
     */
    private function getSearchArticleList($pageIndex = 1, $pageCount = 5, $keyword = "", $isTop = true)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        //获取文章的分类
        $ids = D("WwwArticleClass")->getAllArticleClass();
        $count = D("WwwArticle")->getArticleListCount($ids, $keyword);
        if ($count > 0) {
            import('Library.Org.Page.LitePage');
            //自定义配置项
            $config = array("prev", "next", 'last');
            $page = new \LitePage($pageIndex, $pageCount, $count, $config);
            $pageTmp = $page->show();
            $result = D("WwwArticle")->getArticleListByIds(array($ids), ($page->pageIndex - 1) * $pageCount, $pageCount, $keyword, false, true);
            foreach ($result as $key => $value) {
                $result[$key]["img_host"] = "qiniu";
                //如果是老站链接过来的文章，同一用history代替
                if (empty($value["shortname"])) {
                    $result[$key]["shortname"] = "history";
                    $result[$key]["title"] = str_replace("_齐装网", "", $value["title"]);
                    $result[$key]["img_host"] = "";
                }
                if (!empty($value["imgs"])) {
                    $exp = explode(",", $value["imgs"]);
                    $exp = array_filter($exp);
                    foreach ($exp as $k => $val) {
                        if (!strpos($val, C('QINIU_DOMAIN'))) {
                            $path = $this->SCHEME_HOST['scheme_full'] . C('STATIC_HOST1') . "/" . $val;
                            $exp[$k] = $path;
                        }
                    }
                    $result[$key]["imgs"] = $exp;
                }
            }
            return array("articles" => $result, "page" => $pageTmp);
        }
    }

    /**
     * @param int $pageIndex
     * @param int $pageCount
     * @param string $keyword
     * @return array
     */
    private function getNewSearchArticleList($pageIndex = 1, $pageCount = 10, $keyword = "")
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("WwwArticle")->getNewXcdgArticleCount('', $keyword);
        $pageNum = (int)ceil($count / $pageCount);//非推荐数据不足时的所在页数
        if ($pageNum == 0) {
            $pageNum = 1;
        }

        if ($count > 0) {
            import('Library.Org.Page.LitePage');
            //自定义配置项
            $config = array("prev", "next", 'last');
            $page = new \LitePage($pageIndex, $pageCount, $count, $config);
            $pageTmp = $page->show();

            if ($page->pageIndex <= $pageNum) {
                $result = D("WwwArticle")->getNewXcdgArticleList('', ($page->pageIndex - 1) * $pageCount, $pageCount, '', $keyword);
            }
            //没有非推荐数据时的数据处理
            if ($page->pageIndex > $pageNum) {
                $result = D("WwwArticle")->getNewXcdgArticleList('', ($pageNum - 1) * $pageCount, $pageCount, '', $keyword);
            }

            foreach ($result as $key => $value) {
                $result[$key]["img_host"] = "qiniu";
                //如果是老站链接过来的文章，同一用history代替
                if (empty($value["shortname"])) {
                    $result[$key]["shortname"] = "history";
                    $result[$key]["title"] = str_replace("_齐装网", "", $value["title"]);
                    $result[$key]["img_host"] = "";
                }
                if (!empty($value["imgs"])) {
                    $exp = explode(",", $value["imgs"]);
                    $exp = array_filter($exp);
                    foreach ($exp as $k => $val) {
                        if (!strpos($val, C('QINIU_DOMAIN'))) {
                            $path = $this->SCHEME_HOST['scheme_full'] . C('STATIC_HOST1') . "/" . $val;
                            $exp[$k] = $path;
                        }
                    }
                    $result[$key]["imgs"] = $exp;
                }
            }
            return array("articles" => $result, "page" => $pageTmp);
        }
    }


    /**
     * 获取装修流程页面文章列表
     * @return [type] [description]
     */
    private function getZxlcArticleList($category_ids = array(), $category = '', $pageCount = 5, $isTop = true, $order = false)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        if (empty($category_ids)) {
            //根据ID查询相对应的文章类别
            $class = D("WwwArticleClass")->getArticleClassById(87);
        } else {
            $class["ids"] = $category_ids;
        }
        $count = D("WwwArticle")->getArticleListCount($class["ids"]);
        if ($count > 0) {
            import('Library.Org.Page.SPage');
            $page = new \SPage($count, $pageCount, array(
                'templet' => '/gonglue/list-' . $category . '-[PAGE].html',
                'firstUrl' => '/gonglue/' . $category . '/'
            ));
            $pageTmp = $page->show();

            $ids[] = array_unique($class["ids"]);
            $result = D("WwwArticle")->getArticleListByIds($ids, ($page->nowPage - 1) * $pageCount, $pageCount, "", false, true);

            foreach ($result as $key => $value) {
                $value['subtitle'] = rtrim($value["subtitle"]);
                $value['subtitle'] = substr($value['subtitle'], 0, -6);

                if (!empty($value["imgs"])) {
                    $exp = explode(",", $value["imgs"]);
                    $exp = array_filter($exp);
                    foreach ($exp as $k => $val) {
                        if (!strpos($val, C('QINIU_DOMAIN'))) {
                            $path = $this->SCHEME_HOST['scheme_full'] . C('STATIC_HOST1') . "/" . $val;
                            $exp[$k] = $path;
                        }
                    }
                    $value["imgs"] = $exp;
                }
                $list[$key] = $value;
            }
            $result = D("WwwArticle")->getArticleListByIds($ids, ($page->nowPage - 1) * $pageCount, $pageCount, "", false, $order);

            foreach ($result as $key => $value) {
                $result[$key]["subtitle"] = rtrim($value["subtitle"]);
                $result[$key]["subtitle"] = substr($result[$key]["subtitle"], 0, -6);

                if (!empty($value["imgs"])) {
                    $exp = explode(",", $value["imgs"]);
                    $exp = array_filter($exp);
                    foreach ($exp as $k => $val) {
                        if (!strpos($val, C('QINIU_DOMAIN'))) {
                            $path = $this->SCHEME_HOST['scheme_full'] . C('STATIC_HOST1') . "/" . $val;
                            $exp[$k] = $path;
                        }
                    }
                    $result[$key]["imgs"] = $exp;
                }
            }

            return array("articles" => $list, "page" => $pageTmp, "hotarticles" => $result, 'nowPage' => $page->nowPage);
        }
    }


    private function getNewZxlcArticleList($category_ids = array(), $category = '', $pageIndex = 1, $pageCount = 10, $recommendPage = 3, $norecommendPage = 7)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        if (empty($category_ids)) {
            //根据ID查询相对应的文章类别
            $class = D("WwwArticleClass")->getArticleClassById(87);
        } else {
            $class["ids"] = $category_ids;
        }

        $count = D("WwwArticle")->getNewXcdgArticleCount($class['ids']);

        if ($count > 0) {
            import('Library.Org.Page.SPage');
            $page = new \SPage($count, $pageCount, array(
                'templet' => '/gonglue/list-' . $category . '-[PAGE].html',
                'firstUrl' => '/gonglue/' . $category . '/'
            ));
            $pageTmp = $page->show();

            $ids = array_unique($class["ids"]);

            $result = D("WwwArticle")->getNewXcdgArticleList($ids, ($page->nowPage - 1) * $pageCount, $pageCount, '');

            foreach ($result as $key => $value) {
                $result[$key]["subtitle"] = rtrim($value["subtitle"]);
                $result[$key]["subtitle"] = mbstr($result[$key]["subtitle"], 0, -6);

                if (!empty($value["imgs"])) {
                    $exp = explode(",", $value["imgs"]);
                    $exp = array_filter($exp);
                    foreach ($exp as $k => $val) {
                        if (!strpos($val, C('QINIU_DOMAIN'))) {
                            $path = $this->SCHEME_HOST['scheme_full'] . C('STATIC_HOST1') . "/" . $val;
                            $exp[$k] = $path;
                        }
                    }
                    $result[$key]["imgs"] = $exp;
                }
            }
            return array("articles" => $result, "page" => $pageTmp, 'nowPage' => $page->nowPage);
        }
    }

    /**
     * 获取首页的轮播列表
     * @return [type] [description]
     */
    private function getAdvList()
    {
        $adv = D("WwwArticle")->getAdvList(5);
        foreach ($adv as $key => $value) {
            unset($adv[$key]['content']);
        }
        return $adv;
    }

    /**
     * 获取装修三步骤
     * @return [type] [description]
     */
    private function getStep($id, $limit, $isTop)
    {
        //根据ID查询相对应的文章类别
        $result = D("WwwArticleClass")->getArticleClassById($id);

        $ids = array();
        //获取子类的类别编号
        foreach ($result["child"] as $k => $val) {
            $ids[] = $val["ids"];
        }
        //根据文章类别查询出所有的文章
        $articles = D("WwwArticle")->getArticleListByIds($ids, 0, $limit, "", true);
        foreach ($articles as $key => $value) {
            foreach ($result["child"] as $k => $val) {
                if (in_array($value["cid"], $val["ids"])) {
                    $result["child"][$k]["articles"][] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * 获取文章分类及文章
     * @return [type] [description]
     */
    private function getArticleList($id, $pageIndex, $pageCount, $isTop)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        //根据ID查询相对应的文章类别
        $result = D("WwwArticleClass")->getArticleClassById($id);

        $ids[] = array_unique($result["ids"]);

        //根据文章类别查询出所有的文章
        $articles = D("WwwArticle")->getArticleListByIds($ids, ($pageIndex - 1) * $pageCount, $pageCount, '', $isTop);

        foreach ($articles as $key => $value) {
            unset($value['content']);
            if (in_array($value["cid"], $result["ids"])) {
                $result["articles"][] = $value;
            }
        }
        return $result;
    }

    /**
     * 获取最新的有最佳答案的问答列表
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function gerWendaList($limit, $order = '')
    {
        import('Library.Org.Util.Fiftercontact');
        $filter = new \Fiftercontact();
        $askModel = new AskModel();
        $result = $askModel->getNewQuestionAndAnswerList($limit, $order);
        foreach ($result as $key => $value) {
            $value['content'] = $filter->filter_common(
                $value['content'],
                array('Sbc2Dbc',
                    'filter_script',
                    array('filter_sensitive_words', array(2, 3, 5)),
                    'filter_link',
                    'filter_url',
                    'filter_html_url',
                    'filter_empty'
                )
            );
            $result[$key]['content'] = str_replace('<br />', '', $value['content']);
        }
        return $result;
    }

    /**
     * 获取最新的浏览量最高的日记
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    public function getDiaryList($limit)
    {
        import('Library.Org.Util.Fiftercontact');
        $filter = new \Fiftercontact();
        $result = D("Diary")->getTopPVDirayList($limit);
        foreach ($result as $key => $value) {
            switch ($value["stage"]) {
                case '1':
                    $value["stage"] = "准备阶段";
                    break;
                case '2':
                    $value["stage"] = "施工阶段";
                    break;
                case '3':
                    $value["stage"] = "入住阶段";
                    break;
            }
            $result[$key]["stage"] = $value["stage"];
            $result[$key]["content"] = $filter->filter_common($value["content"], array("Sbc2Dbc", "filter_script", array("filter_sensitive_words", array(2, 3, 5)), "filter_link", "filter_url", "filter_html_url", "filter_empty"));

        }
        return $result;
    }

    /**
     * 获取视频列表
     * @param  [type] $type  [description]
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    public function getVieoList($type, $limit)
    {
        $list = D("ArticleVedio")->getVedioList($type, $limit);
        return $list;
    }
    public function zhishidaquan()
    {
        if (ismobile()) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: '.$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') .$_SERVER['REQUEST_URI']);
            exit();
        }
        $pageIndex = 1;
        $pageCount = 58;

        if (I("get.p") !== "") {
            $pageIndex = I("get.p");
        }
        //TDK
        $basic['head']['title'] = '房屋装修知识大全 - 房屋装修攻略 - 齐装网';
        $basic['head']['keywords'] = "房屋装修，装修知识，装修攻略，房屋装修设计，房屋装修步骤，房屋装修风水，房屋装修注意事项";
        $basic['head']['description'] = '齐装网房屋装修知识大全，不仅为业主提供好看的房屋装修设计、房屋装修流程、房屋装修风水、房屋装修注意事项等房屋装修攻略知识内容，还能免费获得房屋装修报价清单和设计方案';
        $basic['body']['title'] = '装修攻略';

        $logic = new ThematicWordsLogicModel();
        $result = $logic->getList(1,$pageIndex,$pageCount);
        $hot = $logic->getHotList(1,20);

        $this->assign('basic',$basic);
        $this->assign("hot",$hot);
        $this->assign("page",$result["page"]);
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
            }
        }
        return array("list"=>$list,"page"=>$show);
    }

    public function thematic()
    {
        if (ismobile()) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: '.$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') .$_SERVER['REQUEST_URI']);
            exit();
        }

        $id = I("get.id");
        $logic = new ThematicWordsLogicModel();

        $info = S("Cache:Them:".$id);
        if (!$info) {
            $info = $logic->getThematicInfoById($id,1);
            if (count($info) == 0) {
               $this->_error();
               die();
            }
            S("Cache:Them:".$id,$info,900);
        }

        $pageIndex = max(1, I('get.p'));
        $pageCount = 12;
        //TDK
        $basic['head']['title'] = $info['title'].' - 齐装网装修攻略';
        $basic['head']['keywords'] = $info['keywords'];
        $basic['head']['description'] = $info['description'];
        $basic['body']['title'] = '装修攻略';
        //获取列表
        $result = $logic->getArticleSearch($info["name"],$pageIndex,$pageCount);

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
    private function getActriceBySpecialkeyword($keyword_module,$pageIndex,$pageCount,$name,$tempword=[])
    {
        $count = D('Common/Logic/SpecialkeywordLogic')->getActriceBySpecialkeywordCount($keyword_module,$name,$tempword);
        if ($count > 0) {
            import('Library.Org.Page.LitePage');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \LitePage($pageIndex,$pageCount,$count,$config);
            $show    = $page->pindaoshow();
            $list = D('Common/Logic/SpecialkeywordLogic')->getActriceBySpecialkeywordList($keyword_module,$name,$tempword,($page->pageIndex-1)*$pageCount,$pageCount);
            foreach ($list as $key => $value) {
                $list[$key]["url"] = $this->SCHEME_HOST['scheme_full'] ."www.qizuang.com/gonglue/".$value["shortname"]."/".$value["id"].".html";
                $list[$key]["src"] = C("QINIU_SCHEME")."://".C("QINIU_DOMAIN")."/".$value["face"];
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
                $list[$key]["url"] = $this->SCHEME_HOST['scheme_full'] ."www.qizuang.com/gonglue/zs/".$value["href"]."/";
            }else{
                $list[$key]["url"] = $this->SCHEME_HOST['scheme_full'] ."www.qizuang.com/baike/zs/".$value["href"]."/";
            }
        }
        return $list;

    }
    /**
     * 获取最新关键列表
     * @param  string $value [description]
     * @return [type]        [description]
     */
    private function getNewSpecialkeyword($keyword_module,$name,$time,$id)
    {
        $list = D('Common/Logic/SpecialkeywordLogic')->getNewSpecialkeyword($keyword_module,$name,$time,$id);
        foreach ($list as $key => $value) {
            if($value["keyword_module"] == 1){
                $list[$key]["url"] = $this->SCHEME_HOST['scheme_full'] ."www.qizuang.com/gonglue/zs/".$value["href"]."/";
            }else{
                $list[$key]["url"] = $this->SCHEME_HOST['scheme_full'] ."www.qizuang.com/baike/zs/".$value["href"]."/";
            }
        }
        return array("list"=>$list);
    }
}
