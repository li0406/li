<?php

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class CompanyController extends HomeBaseController
{

    public function _initialize()
    {
        parent::_initialize();
        //添加顶部搜索栏信息
        $this->assign('serch_uri', 'companysearch');
        $this->assign('serch_type', '装修公司');
        $this->assign('holdercontent', '全国超过十万家装修公司为您免费设计');
    }

    public function index()
    {

        //跳转到手机端
        if (ismobile()) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location:".$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }
        $url = $_SERVER['REQUEST_URI'];
        //301
        if($url&&substr($url,-1)!=="/"&&!I('get.p')){
            $redirect = $this->SCHEME_HOST['scheme_full'] .C('QZ_YUMINGWWW') .'/company/';
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:".$redirect);
            die();
        }

        //分站定位
        $iplookup = I('cookie.iplookup');
        $city_id = I('cookie.city_id');

        //如果有上次分站访问
        if (!empty($city_id)) {
            $city = D('Quyu')->getCityById($city_id);
        } elseif (!empty($iplookup)) {
            $city = D('Quyu')->getCityById($iplookup);
        }
        $info['city'] = $city;

        //装修公司-全国  轮播广告
        $info["trust"] = S('Cache:Company:Index:trust');
        if (!$info["trust"]) {
            $info["trust"] = D('Advbanner')->getCarousel(10);
            S('Cache:Company:Index:trust', $info["trust"], 900);
        }

        //全国装修公司活跃度排行榜
        $info["activerank"] = S('Cache:Company:Index:activerank');
        if (!$info["activerank"]) {
            $info['activerank'] = D('Company')->getCompanyActiveRank('',10);
            $info['activerank'] = D('Company')->changeDataRank($info['activerank']);
            S('Cache:Company:Index:activerank', $info["activerank"], 900);
        }


        //全国装修公司口碑排行榜.
        $info["koubei"] = S('Cache:Company:Index:koubei');
        if (!$info["koubei"]) {
            $info["koubei"] = D('Company')->getKoubeiRank('', 10);
            $info['koubei'] = D('Company')->changeKouBeiRank($info['koubei']);
            S('Cache:Company:Index:koubei', $info["koubei"], 900);
        }

//        $info["askUser"] = S('Cache:Company:Index:askUser');
//        if (!$info["askUser"]) {
//            $info["askUser"] = D('Company')->getAskTopUser(10);
//            S('Cache:Company:Index:askUser', $info["askUser"], 900);
//        }

        $info["cases"] = S('Cache:Company:Index:cases');
        if (!$info["cases"]) {
            $info["cases"] = D('Company')->getCases(10);
            S('Cache:Company:Index:cases', $info["cases"], 900);
        }

        if (!isset($_SERVER['REQUEST_URI'][9])) {
            //获取友情链接
            $friendLink['link'] = S("C:FL:CL:000001");
            if (!$friendLink['link']) {
                $friendLink['link'] = D("Friendlink")->getFriendLinkList("000001", 1, 'company-list');
                S("C:FL:CL:000001", $friendLink['link'], 900);
            }
            //获取热门装修公司
            $friendLink['recommendCompany'] = S("C:FL:RecommendCompany:000001");
            if (!$friendLink['recommendCompany']) {
                $articleTagsModel = new \Common\Model\Logic\ArticleTagLogicModel();
                $friendLink['recommendCompany'] = $tags = $articleTagsModel->getTagData(2,1);
                S("C:FL:RecommendCompany:000001", $friendLink['recommendCompany'], 900);
            }
            $this->assign("friendLink", $friendLink);
        }
        $this->assign('info', $info);

        //顶部
        $keys["keywords"] = $c["oldName"] . "装修公司," . $c["oldName"] . "装修公司排名," . $c["oldName"] . "装修网";
        $keys["title"] = $c["oldName"] . "装修公司_" . $c["oldName"] . "装修公司排名_" . $c["oldName"] . "装修公司大全" . $pageContent;
        $keys["description"] = "找装修公司频道齐装网为您提供:深圳,上海,北京,广州,苏州,郑州,成都,沈阳,武汉,重庆,合肥,西安,厦门,大连等全国各城市装修公司口碑排名信息。";

        $this->assign("orderTmp", $this->fetch(T("Common@Order/orderTmp")));

        $this->assign("keys", $keys);
        $this->assign("tabIndex", 3);
        $this->assign("header_search", 0);
        $this->display();
    }

    public function companylandpage()
    {
        //跳转到手机端
        if (ismobile()) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: ".$this->SCHEME_HOST['scheme_full'] .  C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }

        $info['brands'] = S('Cache:Company:zxgstj:hotBrands');
        if (empty($info['brands'])) {
            //全国站直接获取4条
            $brands = D("Advbanner")->getBrandList('000001', 4);
            $counts = count($brands);
            //如果数据小于20,就按照排序去分站会员
            if ($counts < 4) {
                foreach ($brands as $k => $v) {
                    $ids[] = $v['company_id'];
                }
                $brandsWhere['company_id'] = ['notin', $ids];
                $other_brands = D("Advbanner")->getBrandList('', (4 - $counts));
                foreach ($other_brands as $k => $v) {
                    array_push($brands, $v);
                }
            }
            foreach ($brands as $k => $v) {
                $ids[] = $v['company_id'];
            }
            //查出每家装修公司的最新案列
            $anli = D("Cases")->getNewsCasesByCompanyId($ids);
            foreach ($anli as $kk => $vv) {
                $dd[$vv['uid']] = $vv;
            }
            //将案例添加到数组中
            foreach ($brands as $k => $v) {
                $brands[$k]['cases'] = $dd[$v['company_id']];
            }
            $info['brands'] = $brands;
            S('Cache:Company:zxgstj:hotBrands', $info['brands'], 900);
        }
        $info["cases"] = S('Cache:Company:zxgstj:cases');
        if (!$info["cases"]) {
            //获取案例
            $info["cases"] = D("Advbanner")->getAdvList("home_cases", "000001", 5);
            S('Cache:Company:zxgstj:cases', $info["cases"], 900);
        }
        $src = I('get.src');
        //根据渠道来源 来选择二维码
        $orderSourceResult = D("OrderSource")->getOne($src);

        //根据sourceid获取微信管理信息
        $result = D("YySrcWeixin")->getOneBySourceid($orderSourceResult['id']);

        if (!$result) {
            $result = D("YySrcWeixin")->getDefaultData();
        }
        if (!empty($result)) {
            $this->assign("wx_title", $result['title']);
            $this->assign("wx_img", $result['img']);
        }
        $this->assign("info", $info);
        $this->assign("source", 18012250);
        $this->display();
    }

    /**
     * 空间装修
     */
    public function spacelandpage()
    {
        //跳转到手机端
        if (ismobile()) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " . $this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }
        $zbInfo = S('Cache:spacelandpage');
        if (!$zbInfo) {
            //户型
            $zbInfo['huxing'] = D("Huxing")->gethx(false);
            //获取申请装修服务列表
            $zbInfo["orders"] = $this->getOrderList();
            //获取最新业主点评
            $zbInfo["comments"] = $this->getComment();
            S("Cache:spacelandpage", $zbInfo, 3600);
        }
        $info["orders"] = $zbInfo["orders"];
        $info["huxing"] = $zbInfo["huxing"];
        //随机取6条评论信息
        $info["comment"] = array_slice($zbInfo["comments"], mt_rand(1, count($zbInfo["comments"]) - 6), 6);
        $this->assign("zbInfo", $info);
        $this->display();
    }

    /**
     * 平台装修
     */
    public function platformlandpage()
    {
        //跳转到手机端
        if (ismobile()) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: ".$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }

        //提交新仓库
        $head = [
            'title' => '好的装修公司推荐_家装公司排名_装修公司哪家好-齐装网',
            'keywords' => '装修哪家公司好,家装公司排名,好的装修公司推荐',
            'description' => '齐装网汇集了6万多靠谱的家装公司排名,让您更快捷更全面的了解装修哪家公司好,帮助业主根据自身需求推荐好的装修公司,体验贴心的一站式服务,享受靠谱的六大保障,轻松装修乐无忧,就上齐装网吧!',
        ];
        $this->assign("head", $head);
        $this->display();
    }

    //品牌落地页
    public function pplandpage()
    {
        //跳转到手机端
        if (ismobile()) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " .$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }
        //随机生成获取设计与报价的人数并且生成缓存数据
        $randPeople = S("pc:company:landpage");
        if (empty($randPeople)) {
            $randPeople = rand(200, 1000);
        }
        if (!empty($_GET['key'])) {
            //ajax传入的数据写入缓存
            S("pc:company:landpage", $_GET['key'], 900);
            $this->ajaxReturn(array("status" => "1", "info" => "success", "data" => $_GET['key']));
        }

        //获取该城市第一个区，用于显示默认城市
        $cityarea['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign("info", $cityarea);

        //根据src获得当前一条记录
        $src = $_GET['src'];
        if (!empty($src)) {
            $source = D("OrderSource")->getOne($src);
            $weixinResult = D("YySrcWeixin")->getOneBySourceid($source['id']);
        }

        if (empty($weixinResult)) {
            $weixinResult = D("YySrcWeixin")->getDefault();
        }

        $head = [
            "title" => "专业靠谱的互联网装修平台_找满意的装修公司就上齐装网",
            "keywords" => "齐装网,专业装修平台,找装修公司",
            "description" => "齐装网，中国知名大型装修平台，专业靠谱的互联网装修平台。入驻全国6万家装修公司与设计师,为全国500多个城市业主提供专业的装修服务:五大审核标准，为您找到满意的装修公司。六大装修服务，为您整个装修过程省钱。2018年我们追求的是优质的用户口碑，找满意的装修公司就上齐装网。",
        ];

        $this->assign("randPeople", $randPeople);
        $this->assign("img", $weixinResult['img']);
        $this->assign("head", $head);
        $this->display();
    }


    //局部装修落地页
    public function jubuzxlandpage()
    {

        //跳转到手机端
        if (ismobile()) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: ".$this->SCHEME_HOST['scheme_full'] .  C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }

        //pc移动端数据需要一致故去移动端缓存
        $today = date('Y-m-d');
        $today_key = 'm:ldy:baojianum:' . $today;
        $randPeople = S($today_key);
        //随机生成获取设计与报价的人数并且生成缓存数据
        if (empty($randPeople)) {
            $randPeople = rand(1000, 2000);
            S($today_key, $randPeople);
            //删除昨日缓存，防止垃圾数据堆积
            $yesterday = date("Y-m-d", strtotime("-1 day"));
            $yesterday_key = 'm:ldy:baojianum:' . $yesterday;
            S($yesterday_key, null);
        }
        $this->assign("randPeople", $randPeople);

        //pc大咖设计发单人数
        $todaydaka_key = 'pc:ldy:dakashejinum:' . $today;
        $randPeopleDaka = S($todaydaka_key);
        //随机生成获取设计与报价的人数并且生成缓存数据
        if (empty($randPeopleDaka)) {
            $randPeopleDaka = rand(1000, 2000);
            S($todaydaka_key, $randPeopleDaka);
            //删除昨日缓存，防止垃圾数据堆积
            $yesterday = date("Y-m-d", strtotime("-1 day"));
            $yesterdaydaka_key = 'm:ldy:dakashejinum:' . $yesterday;
            S($yesterdaydaka_key, null);
        }
        $this->assign("randPeopleDaka", $randPeopleDaka);


        if (!empty($_GET['key'])) {
            //ajax传入的数据写入缓存
            S($today_key, $_GET['key']);
            $this->ajaxReturn(array("status" => "1", "info" => "success", "data" => $_GET['key']));
        }
        if (!empty($_GET['keydaka'])) {
            //ajax传入的数据写入缓存
            S($todaydaka_key, $_GET['keydaka']);
            $this->ajaxReturn(array("status" => "1", "info" => "success", "data" => $_GET['keydaka']));
        }

        if (!empty($_GET['key'])) {
            //ajax传入的数据写入缓存
            S("pc:company:jubuzxlandpage", $_GET['key'], 86400);
            $this->ajaxReturn(array("status" => "1", "info" => "success", "data" => $_GET['key']));
        }

        //获取该城市第一个区，用于显示默认城市
        $cityarea['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign("info", $cityarea);

        //根据src获得当前一条记录
        $src = $_GET['src'];

        if (!empty($src)) {
            $source = D("OrderSource")->getOne($src);
            $weixinResult = D("YySrcWeixin")->getOneBySourceid($source['id']);
        }

        if (empty($weixinResult)) {
            $weixinResult = D("YySrcWeixin")->getDefault();
        }

        $head = [
            "title" => "局部装修_局部装修效果图_局部装修装饰设计案例-齐装网",
            "keywords" => "局部装修,局部装修效果图,局部装饰,局部装修案例",
            "description" => "装网拥有局部装修案例,包括厨房,卫生间,餐厅,客厅及卧室等装修设计案例,精选2018局部装修装修效果图，每个局部多张实景装饰效果。全网口碑装修平台为您装修省时,省力,省心,最重要的是省钱，90%的业主选择齐装网。",
        ];

        //获取风格下拉菜单
        $fengge = D("Fengge")->getfg();

        //获取装修公司信息
        $companys = S('m:ldy:company:info');
        if (!$companys) {
            $map = [
                'a.qc' => [
                    ['EQ', '蚌埠美丽间装饰工程设计有限公司'],
                    ['EQ', '山东仁义装饰工程有限公司'],
                    ['EQ', '天津居联峰尚装饰工程有限公司济南分公司'],
                    'or'
                ]
            ];
            $res = D('Common/Company')->getCompanys($map);
            $companys = [
                'bb' => [
                    'id' => 34904,
                    'case_count' => 776,
                ],
                'sd' => [
                    'id' => 34904,
                    'case_count' => 776,
                ],
                'jn' => [
                    'id' => 34904,
                    'case_count' => 776,
                ],
            ];
            foreach ($res as $v) {
                switch ($v['qc']) {
                    case '蚌埠美丽间装饰工程设计有限公司':
                        $companys['bb']['case_count'] = $v['case_count'];
                        $companys['bb']['id'] = $v['id'];
                        break;
                    case '山东仁义装饰工程有限公司':
                        $companys['sd']['case_count'] = $v['case_count'];
                        $companys['sd']['id'] = $v['id'];
                        break;
                    case '天津居联峰尚装饰工程有限公司济南分公司':
                        $companys['jn']['case_count'] = $v['case_count'];
                        $companys['jn']['id'] = $v['id'];

                        break;
                    default:
                        break;
                }
            }
        }
        $this->assign('companys', $companys);

        $this->assign("img", $weixinResult['img']);
        $this->assign("fengge", $fengge);
        $this->assign("head", $head);
        $this->display();
    }

    private
    function getList($map, $pageIndex = 1, $pageCount = 10)
    {
        import('Library.Org.Page.LitePage');
        $result = D("Company")->getList($map, ($pageIndex - 1) * $pageCount, $pageCount);
//        $count = D("Common/Company")->getCompanyCount($map);
        $count = 1000;
        $config = array("prev", "next", 'last');
        $page = new \LitePage($pageIndex, $pageCount, $count, $config);
        $pageTmp = $page->show();
        return array("list" => $result['result'], "page" => $pageTmp, "num" => $count);
    }

    private
    function getOrderList()
    {
        // 获取风格
        $fengge = D("Fengge")->getfg();

        import('Library.Org.Util.App');
        $app = new \App();
        $halfCount = 0;
        for ($i = 0; $i < 50; $i++) {
            $xing = $app->getRandXing();
            $sex_array = array("先生", "女士");
            $sex = $sex_array[rand(0, 1)];
            $sub["name"] = $xing . $sex;
            $mianji = rand(80, 120);
            $sub["mianji"] = $mianji;
            $leixing_array = array("半包", "全包");
            $seed = rand(0, 1);
            $halfCount = $seed == 0 ? $halfCount + 1 : $halfCount;
            if ($halfCount > 10) {
                $seed = 1;
            }
            $jiage = $seed == 0 ? $mianji * 368 : $mianji * 688;
            $sub["leixing"] = $leixing_array[$seed];
            $sub["jiage"] = round(($jiage / 10000), 1) . "万元";
            $sub["fengge"] = $fengge[rand(0, count($fengge) - 1)]["name"];
            $show_time = mt_rand(10, 600);
            if ($show_time >= 60) {
                $sub["time"] = floor($show_time / 60) . '分钟前';
            } else {
                $sub["time"] = $show_time . '秒前';
            }
            $data[] = $sub;
        }

        return $data;
    }

    private
    function getComment()
    {
        $comment = D("Comment")->getNewComment(50);
        foreach ($comment as $key => $value) {
            $rand = rand(1, 10);
            $comment[$key]["uptime"] = $rand . "分钟前";
        }
        shuffle($comment);
        return $comment;
    }

    //ajax获取
    public function getLandList()
    {
        if (IS_AJAX) {
            $type = I("get.type");
            $info = I('get.info');
            $param = explode('|', $info);

            $location = intval($param[0]);
            $fengge = intval($param[1]);
            $huxing = intval($param[2]);
            $color = intval($param[3]);

            $html = S('Cache:Company:jxxgt:pc' . ":" . $location . ":" . $fengge . ":" . $huxing . ":" . $color);
            if (!$html) {

                $list = D('Meitu')->getLandPageMeiTuList($location, $fengge, $huxing, $color, 12, $type);
                if (!empty($list)) {
                    $html = '';
                    foreach ($list as $key => $val) {

                        $html .= '<li class="rank-active" data-name="li' . ++$key . '"> <div class="img"><a href="'.$this->SCHEME_HOST['scheme_full'].'meitu.qizuang.com/p' . $val["id"] . '.html" target="_blank"><img src="' .$this->SCHEME_HOST['scheme_full'] . C('QINIU_DOMAIN') . '/' . $val["img_path"] . '-w300.jpg" ></a><div class="small"><span href="javascript:;" class="zhuangxiu">装修成这样花多少钱</span></div><p>' . $val["title"] . '</p></div></li>';
                    }
                    S('Cache:Company:jxxgt:pc' . ":" . $location . ":" . $fengge . ":" . $huxing . ":" . $color, $html, 900);

                    $this->ajaxReturn(array("data" => $html, "info" => "", "status" => 1));

                } else {
                    $this->ajaxReturn(array("status" => 0));
                }
            } else {
                $this->ajaxReturn(array("data" => $html, "info" => "", "status" => 1));
            }


        }

    }

    public function addNum()
    {
        if (IS_AJAX) {
            $num = S('Cache:Company:jxxgt');
            S('Cache:Company:jxxgt', $num + 1, strtotime(date("Y-m-d" . "23:59:59")) - time());
            $this->ajaxReturn(array("data" => $num + 1, "info" => "", "status" => 1));
        }
    }

    public function jxxgtLandPage()
    {
        //跳转到手机端
        if (ismobile()) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: ".$this->SCHEME_HOST['scheme_full'] .  C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }

        //发单人数
        //获取缓存中的数据
        $num = S('Cache:Company:jxxgt');
        if (!$num) {
            //不存在 则缓存中没有数据 当天第一次申请
            $num = rand(200, 2000);
            S('Cache:Company:jxxgt', $num, strtotime(date("Y-m-d" . "23:59:59")) - time());
        }
        //底部户型
        $zbInfo["huxing"] = D("Huxing")->gethx(false);
        //推荐
        $reFirst = D('Meitu')->getLandPageMeiTuList(0, 0, 10, 0, 12, 1);//小户型
        $category["recommand"] = array('xh', 'yj', 'bo', 'ds', 'ty', 'et', 'bj', 'pm', 'cf', 'jg', 'dh');
        //获取户型
        $fix = '4,5,6,10,11,12,14,15,7,8,9,16,17,18,19';//指定户型id
        $category['hx'] = D('Meitu')->getHuxing('', false, $fix);

        //获取风格
        $fix = '4,5,12,31,6,7,8,9,13,15,24,10,11,16,17';//指定风格
        $category['fg'] = D('Meitu')->getFengge('', false, $fix);
        //获取颜色
        $fix = '12,11,6,8,9,10,15,14,5,4,7,13';//指定风格
        $category['cl'] = D('Meitu')->getColor('', false, $fix);

        //获取局部
        $fix = '4,5,9,6,7,8,10,11,12，22,25，13,14，15,16';//指定风格
        $category['lt'] = D('Meitu')->getLocation('', false, $fix);

        //获取首页 3D
        $threeDList = S('Cache:Company:jxxgt:3d');
        if (!$threeDList) {
            $threeDList = D('XiaoguotuThreedimension')->getList(0, '', 0, 0, 0, 6, 1);
            S('Cache:Company:jxxgt:3d', $threeDList, 3600);
        }

        //获取案例
        $huxing = array(10, 11, 12, 14, 15);
        $caseList = S('Cache:Company:jxxgt:cases');
        if (!$caseList) {
            foreach ($huxing as $val) {
                $caseList[$val] = D("Common/Cases")->getCaseImagesList(0, 10, 1, $val, 0, 0, $ys = "", $sm = "", $keyword = '', $city = '', $leixing = '', 0, 6);//普通
            }
            S('Cache:Company:jxxgt:cases', $caseList, 3600);
        }

        //微信部分
        $src = I('get.src');
        //根据渠道来源 来选择二维码
        $orderSourceResult = D("OrderSource")->getOne($src);

        //根据sourceid获取微信管理信息
        $result = D("YySrcWeixin")->getOneBySourceid($orderSourceResult['id']);

        if (!$result) {
            $result = D("YySrcWeixin")->getDefaultData();
        }

        if (!empty($result)) {
            $this->assign("wx_title", $result['title']);
            $this->assign("wx_img", $result['img']);
        }
        $keys["title"] = "2018精选装修效果图_家居装修效果图欣赏_3D实景装修案例效果图-齐装网装修效果图";
        $keys["keywords"] = "装修效果图,2018家居装修效果图,装修效果图案例,小户型装修效果图";
        $keys["description"] = "齐装网为您提供精选2018年家居装修效果图及新款家居装修搭配图片欣赏,精选大小户型装修效果图，田园北欧等风格3D实景装 修效果图，还有时尚的客厅、卧室、卫生间等各类2018装修效果图。";
        $this->assign("keys", $keys);
        $this->assign("zbInfo", $zbInfo);
        $this->assign("num", $num);
        $this->assign("reFirst", $reFirst);
        $this->assign("category", $category);
        $this->assign("threeDList", $threeDList);
        $this->assign("caseList", $caseList);
        $this->assign("source", 18032341);
        $this->display('jxxgt');
    }

    public function quanbao(){
        //跳转到手机端
        if (ismobile()) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " .$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }

        //pc移动端数据需要一致故去移动端缓存
        $today = date('Y-m-d');
        $today_key = 'm:ldy:baojianum:' . $today;
        $randPeople = S($today_key);
        //随机生成获取设计与报价的人数并且生成缓存数据
        if (empty($randPeople)) {
            $randPeople = rand(200, 1000);
            S($today_key, $randPeople,strtotime('tomorrow')-time());
        }
        $this->assign("randPeople", $randPeople);

        //pc大咖设计发单人数
        $todayguwen_key = 'pc:ldy:guwennum:' . $today;
        $randPeopleGuwen = S($todayguwen_key);
        //随机生成获取设计与报价的人数并且生成缓存数据
        if (empty($randPeopleGuwen)) {
            $randPeopleGuwen = rand(200, 1000);
            S($todayguwen_key, $randPeopleGuwen,strtotime('tomorrow')-time());
        }
        //便于ajax请求
        if (!empty($_GET['keyguwen'])) {
            //ajax传入的数据写入缓存
            S($todayguwen_key, $_GET['keyguwen']);
            $this->ajaxReturn(array("status" => "1", "info" => "success", "data" => $_GET['keyguwen']));
        }
        $this->assign("randPeopleGuwen", $randPeopleGuwen);

        //根据src获得当前一条记录获取二维码
        $src = $_GET['src'];
        if (!empty($src)) {
            $source = D("OrderSource")->getOne($src);
            $weixinResult = D("YySrcWeixin")->getOneBySourceid($source['id']);
        }
        if (empty($weixinResult)) {
            $weixinResult = D("YySrcWeixin")->getDefault();
        }
        //TDK
        $keys["title"] = "装修公司全包还是半包好_装修公司选择标准_齐装一站式装修服务解决方案平台-齐装网";
        $keys["keywords"] = "装修公司全包还是半包好,装修公司选择标准,装修服务平台";
        $keys["description"] = "齐装网装修服务平台为广大业主提供齐装一站式装修服务解决方案，帮助业主合理选择全包、半包和清包装修方式，严格按照标准筛选装修公司，注重高品质的装修服务体验，解决业主普遍面临的各种装修问题。上齐装平台，只需七步，装修无忧！";

        $this->assign('keys', $keys);
        $this->assign('source', '18052344');
        $this->assign("img", $weixinResult['img']);
        $this->display();
    }
}