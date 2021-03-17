<?php


namespace Home\Controller;

use Org\Util\Date;
use Think\Exception;

use Common\Enums\ApiConfig;
use Common\Model\Logic\TuLogicModel;
use Common\Model\Logic\TuHomeLogicModel;
use Common\Model\Logic\ThreeDLogicModel;
use Common\Model\Logic\TuCategoryLogicModel;
use Common\Model\Logic\ThematicWordsLogicModel;
use Home\Common\Controller\HomeBaseController;
use Common\Model\Service\ElasticSearchServiceModel;

class TuController extends HomeBaseController
{
    protected $tuLogic;

    protected $tuCategoryLogic;

    protected $tuHomeLogic;

    protected $threeDLogic;

    public function __construct()
    {
        parent::__construct();

        // 跳转到手机端
        if (ismobile()) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location:".$this->SCHEME_HOST["scheme_full"] . C("MOBILE_DONAMES") . $_SERVER["REQUEST_URI"]);
            exit();
        }

        $this->tuLogic = new TuLogicModel();
        $this->tuCategoryLogic = new TuCategoryLogicModel();
        $this->tuHomeLogic = new TuHomeLogicModel();
        $this->threeDLogic = new ThreeDLogicModel();

        //导航栏标识
        $this->assign("tabIndex", 2);
        //添加顶部搜索栏信息
        $this->assign('serch_uri', 'meitu');
        $this->assign('serch_type', '装修效果图');
        $this->assign('holdercontent', '海量精选美图任你选');
    }

    //首页
    public function index()
    {

        $url = $_SERVER['REQUEST_URI'];
        //301
        if($url&&substr($url,-1)!=="/"&&!I('get.p')){
            $redirect = $this->SCHEME_HOST['scheme_full'] . C('QZ_YUMINGWWW') .'/tu/';
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:".$redirect);
            die();
        }

        //获取家装效果图
        $homeImages = S('Cache:WwwTu:Index:homeimages');
        if(!$homeImages){
            $homeImages = $this->tuHomeLogic->getHome(8);
            S('Cache:WwwTu:Index:homeimages', $homeImages, 60 * 60);
        }


        //获取公装效果图
        $pubImages = S('Cache:WwwTu:Index:pubimages');
        if(!$pubImages){
            $pubImages = $this->tuHomeLogic->getPub(8);
            S('Cache:WwwTu:Index:pubimages', $pubImages, 60 * 60);
        }
        //3d效果图
        $threeDImages = S('Cache:WwwTu:Index:threedimages');
        if(!$threeDImages){
            $threeDImages = $this->threeDLogic->getHome();
            S('Cache:WwwTu:Index:threedimages', $threeDImages, 60 * 60);
        }

        //装修风格
        $recomment = S('Cache:WwwTu:Index:recomment');
        if(!$recomment){
            $recomment = $this->tuHomeLogic->getRecommentList();
            S('Cache:WwwTu:Index:recomment', $recomment, 60 * 60);
        }

        $data = [
            'homeImages' => $homeImages,
            'pubImages' => $pubImages,
            'threeDImages' => $threeDImages,
            "recomment" => $recomment
        ];

        //seo 标题/描述/关键字
        $year = date('Y');
        $info["title"] = "装修效果图 - {$year}室内装修设计效果图大全 - 齐装网装修效果图";
        $info["keywords"] = "装修效果图,{$year}装修效果图大全,齐装网效果图";
        $info["description"] = "齐装网{$year}装修效果图片专区,提供国内外受欢迎的家装、公装效果图，还有丰富时尚的各类装修设计方案以及3D家庭装修设计案例供您参考，找装修效果图就上齐装网！";

        //获取友情链接
        $friendLink = S("C:FL:A:WwwTu");
        if (!$friendLink) {
            $friendLink['link'] = D("Friendlink")->getFriendLinkList("000001", 1, 'meitu');

            $thematicWordsLogicModel = new ThematicWordsLogicModel();
            $friendLink['hotMeitu'] = $thematicWordsLogicModel->getNewTypeWords(2, 25);

            S("C:FL:A:WwwTu", $friendLink, 900);
        }

        //轮播
        $info['lunbo'] = S('Cache:WwwTu:Index:lunbo');
        if (!$info['lunbo']) {
            $info['lunbo'] = D('Meitu')->getLunbo();
            S('Cache:WwwTu:Index:lunbo', $info['lunbo'], 900);
        }

        //家装分类
        $jiazhuangCategory = S('Cache:WwwTu:Index:jiazhuangcategory');
        if(!$jiazhuangCategory){
            $jiazhuangCategory = $this->tuCategoryLogic->getHomeCategoryListByType(2);
            S('Cache:WwwTu:Index:jiazhuangcategory', $jiazhuangCategory, 60 * 60 * 12);
        }

        //公装分类
        $gongzhuangCategory = S('Cache:WwwTu:Index:gongzhuangcategory');
        if(!$gongzhuangCategory){
            $gongzhuangCategory = $this->tuCategoryLogic->getHomeCategoryListByType(1);
            S('Cache:WwwTu:Index:gongzhuangcategory', $gongzhuangCategory, 60 * 60 * 12);
        }

        $this->assign('jiazhuangcategory', $jiazhuangCategory);
        $this->assign('gongzhuangcategory', $gongzhuangCategory);

        $this->assign("friendLink", $friendLink);
        $this->assign('data', $data);
        $this->assign('info', $info);
        $this->display();
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
                    $redirect = $this->SCHEME_HOST['scheme_full'] .C('QZ_YUMINGWWW') .'/tu/'.$params['category'].'/';
                    header( "HTTP/1.1 301 Moved Permanently" );
                    header( "Location:".$redirect);
                    die();
                }
            }

            if (empty($params['category'])) {
                throw new Exception('没有分类参数');
            }

            //获取对应的分类数据
            $categoryMap = $this->tuLogic->getCategoryMap($params);

            //获取分类别表
            $categoryList = S('Cache:getList-'.$categoryMap['type'].':categorylist');         //分类缓存
            if(!$categoryList){
                $categoryList = $this->tuCategoryLogic->getMeituLv2CategoryByType($categoryMap['type']);
                S('Cache:getList-'.$categoryMap['type'].':categorylist', $categoryList, 60 * 60 * 12);
            }

            $data = $this->tuLogic->getList($params);

            //根据分类id获取分类数据
            $categoryData = [];
            if (!empty($categoryMap) && !empty($categoryMap['category_id'])) {
                $categoryData = $this->tuCategoryLogic->find($categoryMap['category_id']);
            }

            // 相关推荐
            if (!in_array($categoryMap["original"], ["jiazhuang", "gongzhuang"])) {
                $words = S("Cache:WWW:Tu:LikeWords:" . $categoryMap["original"]);
                if (empty($words)) {
                    $elasticSearchService = new ElasticSearchServiceModel();
                    $words = $elasticSearchService->getThemticLabels($categoryMap["category_name"], 2, 10);
                    S("Cache:WWW:Tu:LikeWords:" . $categoryMap["original"], $words, 900);
                }
                $this->assign("wordsList", $words);
            }

            //友情链接
            $friendLinks = $this->tuLogic->getHomeFriendLink();
            if ($categoryMap['type'] == 1) {
                $defaultContent['url'] = '/tu/gongzhuang/';
                $defaultContent['url_reverse'] = '/tu/jiazhuang/';
                $defaultContent['name'] = '公装';
                $defaultContent['name_reverse'] = '家装';

                //友情链接
                $friendLinks = $this->tuLogic->getPubFriendLink();

                //列表标题
                $listTitle = '公装效果图';
                if ($categoryMap['category_name']) {
                    $listTitle = $categoryMap['category_name'] . '装修效果图';
                }
            } else {
                //判断是否是公装/家装
                $defaultContent['url'] = '/tu/jiazhuang/';
                $defaultContent['url_reverse'] = '/tu/gongzhuang/';
                $defaultContent['name'] = '家装';
                $defaultContent['name_reverse'] = '公装';

                $listTitle = '家装效果图';

                if ($categoryMap['category_name']) {
                    $listTitle = $categoryMap['category_name'] . '装修效果图';
                }
            }
            
            //keyword长度大于10显示...
            if(mb_strlen($params['keyword'])>10){
                $params['keyword'] = mb_substr($params['keyword'],0,10).'...';
            }

            //seo 标题/描述/关键字
            $seo = $this->tuLogic->getSEO($categoryMap['type'], $params, $categoryData);
            $seo['canonical'] = $_SERVER['HTTP_HOST'] . '/tu/'.$params['category'].'/';

            //保留第一页的友链，后面的分页不需要友链
            if(intval($params['p']) > 1){
                $friendLinks = [];
            }

            $this->assign('defaultContent', $defaultContent);
            $this->assign('categoryMap', $categoryMap);
            $this->assign('categoryData', $categoryData);
            $this->assign('categoryList', $categoryList);
            $this->assign('data', $data);
            $this->assign('seo', $seo);
            $this->assign('friendLink', $friendLinks);
            $this->assign('listTitle', $listTitle);
            $this->assign('params', $params);
            $this->display('tulist');

        } catch (Exception $e) {
            $this->_error();
        }
    }

    public function getPubDetail()
    {
        $id = I('get.id');
        $uid = '';
        $data = $this->tuLogic->getDetail($id, 1);
        $data['img_path'] = $data['image_src'];
        $data['img_host'] = 'host';

        if (intval($data['state']) != 3) {
            $this->_error();
        }

        //添加PV值
        $this->tuLogic->meituAddPv($id);

        //seo 标题/描述/关键字
        $info["title"] = "{$data['title']} - 齐装网装修效果图";
        $info["keywords"] = $data['keyword'];
        $info["description"] = $data['description'];


        //家装分类
        $jiazhuangCategory = $this->tuCategoryLogic->getMeituLv2CategoryByType(1);
        $this->assign('jiazhuangcategory', $jiazhuangCategory);

        //相关图片
        $relatepic = $this->tuLogic->getRelevantImgsByImgId($id);
        $this->assign('relatepic', $relatepic['list']);


        /*********************************** 图片 /图片列表 ***************************************/
        //获取后面的图片？
        //定义要查询的前后图集数量
        $preNum  = 18;      //取19条  加上本身 有20条
        //查询前面的单图（发布时间越来越大? ）
        $singleCaseList['pre'] = $this->tuLogic->getSingleCases($id, 'pre', $preNum, 1, $uid);//分页，前后，上页分页数量，
        //上一页，下一页
        $imgList['pre'] = $imgList['next'] = array();


        //循环将 上一页的 每张图片的描述格式化
        if ($singleCaseList['pre']) {
            foreach ($singleCaseList['pre'] as $k => $v) {
                if ($v['imgdescription']) {
                    $singleCaseList['pre'][$k]['imgdescription'] = html_entity_decode($v['imgdescription']);
                }
            }
        }
        $newSingle = $singleCaseList['pre'];
        $getminormax = $this->getMinOrMaxal($newSingle);
        if($getminormax['min'] > 0){
            $prelist = $this->tuLogic->getSingleCases($getminormax['min'] , 'pre', 1, 1, $uid);//分页，前后，上页分页数量，
        }else{
            $prelist = [];
        }
        $nextlist = $this->tuLogic->getSingleCases($id, 'next', 19, 1, $uid);//分页，前后，上页分页数量，ist);
        $getminormax2 = $this->getMinOrMaxal($nextlist);


        //图片跳转所需数据
        $imgList['pre']['id'] = $getminormax2['max'];
        $imgList['next']['id'] = $prelist[0]['id'];


        array_unshift($newSingle, $data);
        $this->assign('singleCaseList', $newSingle);
        $this->assign('imgList', $imgList);

        //获取是否显示获取报价弹层
        if(!isset($_COOKIE["meitu_tips"])){
            $this->assign("isMeituTip",true);
        }

        // 图片标签相关
        $wordsList = S("Cache:WWW:TuDetail:Words:" . $data["id"]);
        if (empty($wordsList)) {
            // 猜你喜欢
            $thematicWordsLogic = new ThematicWordsLogicModel();
            $wordsList["like"] = $thematicWordsLogic->getContentLabels($data["id"], 7, 10, 2);

            // 热门图片（全匹配）
            $elasticSearchService = new ElasticSearchServiceModel();
            $wordsList["hot"] = $elasticSearchService->getContentWords($data["category"], 2, 10);
            S("Cache:WWW:TuDetail:Words:" . $data["id"], $wordsList, 900);
        }

        $baidu['url'] = $this->SCHEME_HOST['scheme_full'].$this->SCHEME_HOST['host'].$_SERVER['REQUEST_URI'];
        $baidu['str'] = mb_substr(strip_tags($data['description']), 0, 100, 'utf-8');
        $baidu['title'] = $data["title"];
        $optimee =date("Y-m-d",strtotime($data['created_at']));
        $optimes =date("H:i:s",strtotime($data['created_at']));
        $baidu['optime'] = $optimee."T".$optimes;
        $baidu['addtime'] = $optimee."T".$optimes;
        $this->assign("baidu",$baidu);

        $this->assign('data', $data);
        $this->assign('info', $info);
        $this->assign("wordsList", $wordsList);
        $this->display('tudetail');
    }

    public function getHomeDetail()
    {
        $id = I('get.id');
        $uid = '';
        $data = $this->tuLogic->getDetail($id, 2);
        $data['img_path'] = $data['image_src'];
        $data['img_host'] = 'host';

        if (intval($data['state']) != 3) {
            $this->_error();
        }

        //添加PV值
        $this->tuLogic->meituAddPv($id);

        //seo 标题/描述/关键字
        $info["title"] = "{$data['title']} - 齐装网装修效果图";
        $info["keywords"] = $data['keyword'];
        $info["description"] = $data['description'];

        //家装分类
        $jiazhuangCategory = $this->tuCategoryLogic->getMeituLv2CategoryByType(2);
        $this->assign('jiazhuangcategory', $jiazhuangCategory);
        //家装分类后面的二级分类
        $oneLv2Category = $this->tuCategoryLogic->getMeituOneLv2Category();
        $this->assign('onelv2category', $oneLv2Category);

        //相关图片
        $relatepic = $this->tuLogic->getRelevantImgsByImgId($id);
        $this->assign('relatepic', $relatepic['list']);

        /*********************************** 图片 /图片列表 ***************************************/
        //获取后面的图片？
        //定义要查询的前后图集数量
        $preNum  = 18;      //取19条  加上本身 有20条
        //查询前面的单图（发布时间越来越大? ）
        $singleCaseList['pre'] = $this->tuLogic->getSingleCases($id, 'pre', $preNum, 2, $uid);//分页，前后，上页分页数量，
        //上一页，下一页
        $imgList['pre'] = $imgList['next'] = array();

        //循环将 上一页的 每张图片的描述格式化
        if ($singleCaseList['pre']) {
            foreach ($singleCaseList['pre'] as $k => $v) {
                if ($v['imgdescription']) {
                    $singleCaseList['pre'][$k]['imgdescription'] = html_entity_decode($v['imgdescription']);
                }
            }
        }
        $newSingle = $singleCaseList['pre'];
        $getminormax = $this->getMinOrMaxal($newSingle);
        if($getminormax['min'] > 0){
            $prelist = $this->tuLogic->getSingleCases($getminormax['min'] , 'pre', 1, 2, $uid);//分页，前后，上页分页数量，
        }else{
            $prelist = [];
        }
        $nextlist = $this->tuLogic->getSingleCases($id, 'next', 19, 2, $uid);//分页，前后，上页分页数量，ist);
        $getminormax2 = $this->getMinOrMaxal($nextlist);

        //图片跳转所需数据
        $imgList['pre']['id'] = $getminormax2['max'];
        $imgList['next']['id'] = $prelist[0]['id'];

        $data['imgdescription'] = $data["image_title"] ? $data["image_title"] : "";
        array_unshift($newSingle, $data);
        $this->assign('singleCaseList', $newSingle);
        $this->assign('imgList', $imgList);

        //获取是否显示获取报价弹层
        if(!isset($_COOKIE["meitu_tips"])){
            $this->assign("isMeituTip",true);
        }

        // 图片标签相关
        $wordsList = S("Cache:WWW:TuDetail:Words:" . $data["id"]);
        if (empty($wordsList)) {
            // 猜你喜欢
            $thematicWordsLogic = new ThematicWordsLogicModel();
            $wordsList["like"] = $thematicWordsLogic->getContentLabels($data["id"], 5, 10, 2);

            // 热门图片（不分词匹配）
            $elasticSearchService = new ElasticSearchServiceModel();
            $wordsList["hot"] = $elasticSearchService->getContentWords($data["category_space"], 2, 10);
            S("Cache:WWW:TuDetail:Words:" . $data["id"], $wordsList, 900);
        }

        // 1353百度官方号需求
        $baidu['url'] = $this->SCHEME_HOST['scheme_full'].$this->SCHEME_HOST['host'].$_SERVER['REQUEST_URI'];
        $baidu['str'] = mb_substr(strip_tags($data['description']), 0, 100, 'utf-8');
        $baidu['title'] = $data["title"];
        $optimee =date("Y-m-d",strtotime($data['created_at']));
        $optimes =date("H:i:s",strtotime($data['created_at']));
        $baidu['optime'] = $optimee."T".$optimes;
        $baidu['addtime'] = $optimee."T".$optimes;
        $this->assign("baidu",$baidu);

        $this->assign('data', $data);
        $this->assign('info', $info);
        $this->assign('wordsList', $wordsList);
        $this->display('tudetail');
    }


    //获取最大值/最小值
    private function getMinOrMaxal($data)
    {
        $i=0;
        foreach ($data as $key => $val){
            if($i ==0){
                $min = intval($val['id']);
                $max = intval($val['id']);
                $i++;
            }

            if($min > intval($val['id'])){
                $min = intval($val['id']);
            }
            if($max < intval($val['id'])){
                $max = intval($val['id']);
            }
        }
        $result['min'] = $min ? $min : 0;
        $result['max'] = $max ? $max : 0;
        return $result;
    }


    //根据图片id获取相关图片
    public function getRelevantImgsByImgId()
    {
        $id = I('get.id');
        if(!$id){
            $this->ajaxReturn(array('error_msg'=>'未获取到美图id','error_code'=>ApiConfig::LOSE_MISS_PARAMETERS));
        }
        //获取当前美图的分类信息
        $categoryinfo = $this->tuLogic->getMeituCategroys($id);


        //获取相关图片信息
        $list = $this->tuLogic->getRelevantImgsByImgId($id);

        $this->ajaxReturn(array(
            'error_msg'=>'获取成功',
            'error_code'=>ApiConfig::REQUEST_SUCCESS,
            'categoryinfo'=> $categoryinfo,
            'publish_time'=>$list['publish_time'],
            'data'=>$list['list'] ? $list['list'] : array()
        ));
    }


    /**
     * 设置美图提示显示
     * @return [type] [description]
     */
    public function closetip(){
        setcookie("meitu_tips",1,time()+(3600*24),'/', '.'.APP_HTTP_DOMAIN);
        $this->ajaxReturn(array("ok"));
    }

}