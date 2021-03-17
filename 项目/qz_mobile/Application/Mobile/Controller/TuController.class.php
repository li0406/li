<?php

namespace Mobile\Controller;

use Think\Exception;
use Common\Model\Logic\TuLogicModel;
use Common\Model\Logic\TuCategoryLogicModel;
use Common\Model\Service\ElasticSearchServiceModel;
use Mobile\Common\Controller\MobileBaseController;

class TuController extends MobileBaseController {
    
    protected $tuLogic;

    protected $tuCategoryLogic;

    protected $category_limit = 8;

    public function __construct()
    {
        parent::__construct();
        $this->tuLogic = new TuLogicModel();
        $this->tuCategoryLogic = new TuCategoryLogicModel();

        //导航栏标识
        $this->assign("tabIndex", 2);
        //添加顶部搜索栏信息
        $this->assign('serch_uri', 'meitu');
        $this->assign('serch_type', '装修效果图');
        $this->assign('holdercontent', '海量精选美图任你选');
    }

    public function index(){
        $page = I("get.p", 1);
        $limit = 10;

        if (IS_AJAX) {
            $data = $this->tuLogic->getHomePageList($page, $limit);
            $status = !empty($data["list"]) ? 1 : 0;
            $this->ajaxReturn(["status" => $status, "data" => $data["list"]]);
        }

        // 轮播数据固定
        // 需求1123 M端美图频道页banner图，排序为1234。现修改为：324、1（齐装保）删除。
        $info['lunbo'] = [
            '3' => [
                'id' => '3',
                'title' => '不花冤枉钱 免费领取4份装修预算报价！',
                'link' => $this->SCHEME_HOST['scheme_host'].'/baojia/',
                'img_path' => 'meitu/20171228/FoQvY7c7xSQmjQHdsDu73X4KbKLS',
                'img_host' => 'qiniu',
                'description' => '齐装网，你身边的装修管家，不花一分冤枉钱，让你拥有更省钱省心的装修方案。',
                'rel' => '',
                'enabled' => '1',
                'px' => '3',
                'time' => '1436862611',
            ],
            '2' => [
                'id' => '2',
                'title' => '30分钟出效果图',
                'link' => $this->SCHEME_HOST['scheme_host'].'/gonglue/xinwen/108235.html',
                'img_path' => 'custom/20200703/FjoAKVmBCIraPtHZbN84xyl-cB-1',
                'img_host' => 'qiniu',
                'description' => '30分钟出效果图 远程看装修进度',
                'rel' => '',
                'enabled' => '1',
                'px' => '2',
                'time' => '1437028058',  
            ],
            '4' => [
                'id' => '4',
                'title' => '免费快速装修报价',
                'link' => $this->SCHEME_HOST['scheme_host'].'/baojia?src=ym-m-4',
                'img_path' => 'meitu/20171228/FjfEpDWUhKlGupwQ6w_NbbLxT6IK',
                'img_host' => 'qiniu',
                'description' => '4家装修公司报价对比，装修不花冤枉钱',
                'rel' => 'nofollow',
                'enabled' => '1',
                'px' => '4',
                'time' => '1437028058',
            ],
        ];

        // 家装分类
        $info["jiazhuangcategory"] = S('Cache:MobileTu:Index:jiazhuangcategory');
        if(!$info["jiazhuangcategory"]){
            $category_home = $this->tuCategoryLogic->category_home;
            $info["jiazhuangcategory"] = $this->tuCategoryLogic->getHomeCategoryListByType($category_home, $this->category_limit);
            S('Cache:MobileTu:Index:jiazhuangcategory', $info["jiazhuangcategory"], 60 * 60 * 12);
        }

        // 公装分类
        $info["gongzhuangcategory"] = S('Cache:MobileTu:Index:gongzhuangcategory');
        if(!$info["gongzhuangcategory"]){
            $category_pub = $this->tuCategoryLogic->category_pub;
            $info["gongzhuangcategory"] = $this->tuCategoryLogic->getHomeCategoryListByType($category_pub, $this->category_limit);
            S('Cache:MobileTu:Index:gongzhuangcategory', $info["gongzhuangcategory"], 60 * 60 * 12);
        }

        //seo 标题/描述/关键字
        $year = date('Y');
        $basic["head"]["title"] = "装修效果图 - {$year}室内装修设计效果图大全 - 齐装网装修效果图";
        $basic["head"]["keywords"] = "装修效果图,{$year}装修效果图大全,齐装网效果图";
        $basic["head"]["description"] = "齐装网{$year}装修效果图片专区,提供国内外受欢迎的家装、公装效果图，还有丰富时尚的各类装修设计方案以及3D家庭装修设计案例供您参考，找装修效果图就上齐装网！";
        $basic["body"]["title"] = "装修效果图";

        $data = $this->tuLogic->getHomePageList($page, $limit);

        $this->assign("info", $info);
        $this->assign("data", $data);
        $this->assign("basic", $basic);
        $this->display("index");
    }

    //栏目页
    public function getList()
    {
        try {
            $params = I('get.');

            $url = $_SERVER['REQUEST_URI'];
            //301
            if($url&&substr($url,-1)!=="/"&&!I('get.p')){
                if(strpos($url,'?keyword=') !== false){

                }else{
                    $redirect = $this->SCHEME_HOST['scheme_full'].C('MOBILE_DONAMES') .'/tu/'.$params['category'].'/';
                    header( "HTTP/1.1 301 Moved Permanently" );
                    header( "Location:".$redirect);
                    die();
                }
            }

            if (empty($params['category'])) {
                throw new Exception('没有分类参数');
            }

            //获取数据列表
            $list = $this->tuLogic->getList($params);

            //获取对应的分类数据
            $categoryMap = S('Cache:Mobile:getCategory-' . $params['category'] . ':category');
            if (!$categoryMap) {
                $categoryMap = $this->tuLogic->getCategoryMap($params);
                S('Cache:Mobile:getCategory-' . $params['category'] . ':category', $categoryMap, 900);
            }
            //获取分类别表
            $categoryList = S('Cache:Mobile:getList-' . $categoryMap['type'] . ':categorylist');         //分类缓存
            if (!$categoryList) {
                $categoryList = $this->tuCategoryLogic->getTuCategoryByType($categoryMap['type']);
                S('Cache:Mobile:getList-' . $categoryMap['type'] . ':categorylist', $categoryList, 900);
            }
            //整理页面所需跳转链接
            $categoryList = $this->tuCategoryLogic->neatenMobileTuCategory($categoryMap,$categoryList);
            //根据分类id获取分类数据
            $categoryData = [];
            if (!empty($categoryMap) && !empty($categoryMap['category_id'])) {
                $categoryData = $this->tuCategoryLogic->find($categoryMap['category_id']);
            }

            // 相关推荐
            if (!in_array($categoryMap["original"], ["jiazhuang", "gongzhuang"])) {
                $words = S("Cache:Mobile:Tu:LikeWords:" . $categoryMap["original"]);
                if (empty($words)) {
                    $elasticSearchService = new ElasticSearchServiceModel();
                    $words = $elasticSearchService->getThemticLabels($categoryMap["category_name"], 2, 10);
                    S("Cache:Mobile:Tu:LikeWords:" . $categoryMap["original"], $words, 900);
                }
                $this->assign("wordsList", $words);
            }

            //seo 标题/描述/关键字
            $seo['head'] = $this->tuLogic->getSEO($categoryMap['type'], $params, $categoryData);
            $seo['canonical'] = C("QZ_YUMINGWWW") . '/tu/'.$params['category'].'/';
            $seo['body']['title'] = $categoryMap['type'] == 1 ? '公装效果图' : '家装效果图';
            $this->assign('category_map', $categoryMap);
            $this->assign('categoryData', $categoryData);
            $this->assign('category_nav', $categoryList);
            $this->assign('category', json_encode($categoryList));
            $this->assign('list', $list['list']);
            $this->assign('page', $list['page']);
            $this->assign('basic', $seo);
            $this->assign('params', $params);
            $this->display('tulist');

        } catch (Exception $e) {
            $this->_error();
        }
    }

    public function getPubDetail(){
        $id = I('get.id');
        $data = $this->tuLogic->getDetail($id, 1);
        $data['img_path'] = $data['image_src'];
        $data['img_host'] = 'host';

        if (intval($data['state']) != 3) {
            $this->_error();
        }

        //添加PV值
        $this->tuLogic->meituAddPv($id);

        //seo 标题/描述/关键字
        $seo['head']["title"] = "{$data['title']} - 齐装网装修效果图";
        $seo['head']["keywords"] = $data['keyword'];
        $seo['head']["description"] = $data['description'];

        //最新的图片在上方,兼容原逻辑
        $singleCaseList['pre'] = $this->tuLogic->getSingleCases($id, 1);
        $singleCaseList['next'] = $this->tuLogic->getSingleCases($id, 2);
        $singleCaseList['type'] = 1;//公装类型
        $this->assign('info',$singleCaseList);


        $this->assign('data', $data);
        $this->assign('basic', $seo);
        $this->display("tudetail");
    }

    public function getHomeDetail(){
        $id = I('get.id');
        $data = $this->tuLogic->getDetail($id, 2);
        $data['img_path'] = $data['image_src'];
        $data['img_host'] = 'host';

        if (intval($data['state']) != 3) {
            $this->_error();
        }

        //添加PV值
        $this->tuLogic->meituAddPv($id);

        //seo 标题/描述/关键字
        $seo['head']["title"] = "{$data['title']} - 齐装网装修效果图";
        $seo['head']["keywords"] = $data['keyword'];
        $seo['head']["description"] = $data['description'];

        //最新的图片在上方,兼容原逻辑
        $singleCaseList['pre'] = $this->tuLogic->getSingleCases($id, 1, 2);
        $singleCaseList['next'] = $this->tuLogic->getSingleCases($id, 2, 2);
        $singleCaseList['type'] = 2;//家装类型
        $this->assign('info', $singleCaseList);

        $this->assign('data', $data);
        $this->assign('basic', $seo);
        $this->display('tudetail');
    }

    public function like(){
        $id = I('param.id');
        $res = $this->tuLogic->setLikeInc($id);
        $this->ajaxReturn(['status' => 1]);
    }
}