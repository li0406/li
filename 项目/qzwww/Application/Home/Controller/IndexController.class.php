<?php
namespace Home\Controller;
use Common\Enums\ArticleCodeEnum;
use Common\Enums\SearchCodeEnum;
use Common\Enums\ThematicWordsCodeEnum;
use Common\Enums\TuCodeEnum;
use Common\Enums\VideoCodeEnum;
use Common\Model\Logic\ArticleLogicModel;
use Common\Model\Logic\AskLogicModel;
use Common\Model\Logic\BaikeLogicModel;
use Common\Model\Logic\CommentLogicModel;
use Common\Model\Logic\DecorationLogicModel;
use Common\Model\Logic\DesignerLogicModel;
use Common\Model\Logic\ThematicWordsLogicModel;
use Common\Model\Logic\TuCategoryLogicModel;
use Common\Model\Logic\UserLogicModel;
use Common\Model\Logic\VideoLogicModel;
use Home\Common\Controller\HomeBaseController;
use Home\Model\CompanyModel;

class IndexController extends HomeBaseController{
    private $stars = array(
          "5"=>array("min"=> 9,"max"=>11),
          "4"=>array("min"=> 8,"max"=> 9),
          "3"=>array("min"=> 4,"max"=> 7),
          "2"=>array("min"=> 2,"max"=> 4),
          "1"=>array("min"=> 1,"max"=>2)
    );

    public function index(){
        $SCHEME_HOST = $this->SCHEME_HOST;

        //处理为0的控制器页面为404
        if (0 === strrpos($_SERVER['REQUEST_URI'], '/0')) {
            $this->_error();
        }
        //检查客户端设备类型 移动版本跳转到
        B("Home\Behavior\MobileBrowserCheck");
        //判断是否是搜索引擎蜘蛛
        $robotIsTrue = B("Common\Behavior\RobotCheck");
        //$robotIsTrue = true; //debug 警告线上应该为注释状态
        if (true === $robotIsTrue) {
            $this->assign('robot', 1);
        }

        $articleLogic = new ArticleLogicModel();
/*************装修公司部分start************/
        //装修公司规模
        $info["guimo"] = S('Cache:Home:Index:Index:CompanyGm');
        if (empty($info["guimo"])) {
            $info["guimo"] = D("Common/Guimo")->gethGm();
            S('Cache:Home:Index:Index:CompanyGm', $info["guimo"], random_int(900, 1080));
        }

        //获取服务保障列表
        $info["baozhang"] = S('Cache:Home:Index:Index:CompanyBz');
        if (empty($info["baozhang"])) {
            $info["baozhang"] = D("Common/Leixing")->getbg();
            S('Cache:Home:Index:Index:CompanyBz', $info["baozhang"], random_int(900, 1080));
        }

        //优质装修公司
        $info["quality_company"] = S('Cache:Home:Index:Index:QualityCompany');
        if (!$info["quality_company"]) {
            //所有会员中案例数量最高的30条会员数据 (745需求)
            $userLgc = new UserLogicModel();
            $brands = $userLgc->getHomeCompanyUser();
            $info['quality_company'] = array_chunk($brands,6);//用于页面数据放
            S('Cache:Home:Index:Index:QualityCompany', $info["quality_company"], random_int(900,1080));
        }

        //人气设计师
        $info["designer"] = S('Cache:Home:Index:Index:Designer');
        if (!$info["designer"]) {
            // 产品说直接取新会员的设计师PV倒序排序
            $designerLogic = new DesignerLogicModel();
            $info['designer'] = $designerLogic->getPopularDesigner("");
            S('Cache:Home:Index:Index:Designer', $info["designer"], random_int(900,1080));
        }

        // 业主评价
        $info['commentlist'] = S('Cache:Home:Index:Index:commentlist');
        if (!$info['commentlist']) {
            $commentLogic = new CommentLogicModel();
            $info['commentlist'] = $commentLogic->getYeZhuComment("", 6);
            S('Cache:Home:Index:Index:commentlist', $info["commentlist"], 86400);
        }


        //口碑榜
        $info["koubei"] = S('Cache:Home:Index:Index:Koubei');
        if (!$info["koubei"]) {
            $companyDb = new CompanyModel();
            $info["koubei"] = $companyDb->getKoubeiRank('', 5);
            S('Cache:Home:Index:Index:Koubei', $info["koubei"], random_int(900,1080));
        }

        //活跃榜
        $info['active_company'] = S('Cache:Home:Index:Index:ActiveCompany');
        if (empty($info['active_company'])) {
            $info['active_company'] = D('Company')->getCompanyActiveRank('',5);
            $info['active_company'] = D('Company')->changeDataRank($info['active_company']);
            S('Cache:Home:Index:Index:ActiveCompany', $info['active_company'], random_int(900,1080));
        }
/*************装修公司部分end************/
/*************装修效果图start************/
        //风格 / 户型 /局部统一获取
        $category = S('Cache:Home:Index:Index:Category');
        if(empty($category)){
            $tuLogic = new TuCategoryLogicModel();
            $category = $tuLogic->getMeituLv2CategoryByType(TuCodeEnum::TU_LX_JIA);
            $category = array_column($category,null,'id');
            S('Cache:Home:Index:Index:Category', $category, random_int(900,1080));
        }
//        $info['fengge'] = $category[TuCodeEnum::TU_LX_JIA_FG];
//        $info['huxing'] = $category[TuCodeEnum::TU_LX_JIA_HX];
//        $info['jubu'] = $category[TuCodeEnum::TU_LX_JIA_JB];

        $decorationLogic = new DecorationLogicModel();
        //新房装修(家装) 查询标签:新房装修效果图
        $info['tu_xinfang'] = S('Cache:Home:Index:Index:Tu:Xinfang');
        if(empty($info['tu_xinfang'])){
            $info['tu_xinfang'] = $decorationLogic->getThematicWordsByModule(SearchCodeEnum::getSceneWord('xfzx'),'', ThematicWordsCodeEnum::MODULE_CODE_JZ, 6);
            S('Cache:Home:Index:Index:Tu:Xinfang',$info['tu_xinfang'],random_int(900,1080));
        }
        //局部装修(家装) 查询标签:局部装修
        $info['tu_jubu'] = S('Cache:Home:Index:Index:Tu:Jubu');
        if(empty($info['tu_jubu'])){
            $info['tu_jubu'] = $decorationLogic->getThematicWordsByModule(SearchCodeEnum::getSceneWord('jbzx'),'', ThematicWordsCodeEnum::MODULE_CODE_JZ, 6);
            S('Cache:Home:Index:Index:Tu:Jubu',$info['tu_jubu'],random_int(900,1080));
        }
        //户型改造(家装) 查询标签:户型改造
        $info['tu_jiufang'] = S('Cache:Home:Index:Index:Tu:Jiufang');
        if(empty($info['tu_jiufang'])){
            $info['tu_jiufang'] = $decorationLogic->getThematicWordsByModule(SearchCodeEnum::getSceneWord('hxgz'),'', ThematicWordsCodeEnum::MODULE_CODE_JZ, 6);
            S('Cache:Home:Index:Index:Tu:Jiufang',$info['tu_jiufang'],random_int(900,1080));
        }
        //商铺(公装) 查询标签:店铺装修
        $info['tu_shop'] = S('Cache:Home:Index:Index:Tu:Shop');
        if(empty($info['tu_shop'])){
            $info['tu_shop'] = $decorationLogic->getThematicWordsByModule(SearchCodeEnum::getSceneWord('dpzx'),'', ThematicWordsCodeEnum::MODULE_CODE_GZ, 6);
            S('Cache:Home:Index:Index:Tu:Shop',$info['tu_shop'],random_int(900,1080));
        }
        //写字楼(公装) 查询标签:写字楼装修
        $info['tu_building'] = S('Cache:Home:Index:Index:Tu:Building');
        if (empty($info['tu_building'])) {
            $info['tu_building'] = $decorationLogic->getThematicWordsByModule(SearchCodeEnum::getSceneWord('xzlzx'), '', ThematicWordsCodeEnum::MODULE_CODE_GZ, 6);
            S('Cache:Home:Index:Index:Tu:Building', $info['tu_building'], random_int(900,1080));
        }
/*************装修效果图end************/
        /*************装修案例start************/
        //装修指南
        //风格/空间
        $info['guide_fg'] = S('Cache:Home:Index:Index:Guide:Fg');
        if (empty($info['guide_fg'])) {
            $info['guide_fg'] = $articleLogic->getArticleByPidClass([ArticleCodeEnum::ARTICLE_ZN_FG, ArticleCodeEnum::ARTICLE_ZN_SPACE], 8, 2);
            S('Cache:Home:Index:Index:Guide:Fg', $info['guide_fg'], random_int(900, 1080));
        }
        /*************装修案例end************/
        /*************装修攻略start************/
        //装修中
        $info['article_zhong'] = S('Cache:Sub:Index:Index:Gonglue:Zhong');
        if(empty($info['article_zhong'])){
            $info['article_zhong'] = $articleLogic->getArticleByPidClass(ArticleCodeEnum::ARTICLE_CLASS_Z, 6, 2);
            S('Cache:Sub:Index:Index:Gonglue:Zhong' , $info['article_zhong'], random_int(900,1080));
        }
        /*************装修攻略end************/
        /*************装修问答start************/
        $wendaLogic = new AskLogicModel();
        //精华问答
        $info['wenda_boutique'] = S('Cache:Home:Index:Index:Wenda:Boutique');
        if(empty($info['wenda_boutique'])){
            $info['wenda_boutique'] = $wendaLogic->getHotAskList(4);
            S('Cache:Home:Index:Index:Wenda:Boutique', $info['wenda_boutique'], random_int(900,1080));
        }
        /*************装修问答end************/
        /*************装修百科start************/
        $baikeLogic = new BaikeLogicModel();
        //精选百科
        $info['baike_boutique'] = S('Cache:Home:Index:Index:Baike:Boutique');
        if(empty($info['baike_boutique'])){
            $info['baike_boutique'] = $baikeLogic->getHomeNewBaike();
            S('Cache:Home:Index:Index:Baike:Boutique', $info['baike_boutique'], random_int(900,1080));
        }
        /*************装修百科end************/
        /*************最新推荐start************/
        //最新攻略
//        $info['article_new'] = S('Cache:Home:Index:Index:Article:New');
//        if(empty($info['article_new'])){
//            $info['article_new'] = $articleLogic->getArticleByPidClass('xinwen', 6,3);
//            S('Cache:Home:Index:Index:Article:New', $info['article_new'], random_int(900,1080));
//        }
        $info['zhishi'] = S('Cache:Home:Index:Index:Zhishi:Hot');
        if(empty($info['zhishi'])){
            $wordsLogic = new ThematicWordsLogicModel();
            $info['zhishi'] = $wordsLogic->getNewList(6);
            S('Cache:Home:Index:Index:Zhishi:Hot', $info['zhishi'], random_int(900,1080));
        }
        /*************最新推荐end************/
        /*************装修学堂start************/
        //科普扫盲
        $videoLogic = new VideoLogicModel();
        $info['video_changjian'] = S('Cache:Home:Index:Index:Video:Cj');
        if(empty($info['video_changjian'])){
            $info['video_changjian'] = $videoLogic->getCategoryVideoList(VideoCodeEnum::VIDEO_CLASS_CJ);
            S('Cache:Home:Index:Index:Video:Cj', $info['video_changjian'], random_int(900,1080));
        }
        //空间魔法
        $info['video_kongjian'] = S('Cache:Home:Index:Index:Video:Kj');
        if(empty($info['video_kongjian'])){
            $info['video_kongjian'] = $videoLogic->getCategoryVideoList(VideoCodeEnum::VIDEO_CLASS_KJ);
            S('Cache:Home:Index:Index:Video:Kj', $info['video_kongjian'], random_int(900,1080));
        }
        //避坑指南
        $info['video_bikeng'] = S('Cache:Home:Index:Index:Video:Bk');
        if(empty($info['video_bikeng'])){
            $info['video_bikeng'] = $videoLogic->getCategoryVideoList(VideoCodeEnum::VIDEO_CLASS_MN);
            S('Cache:Home:Index:Index:Video:Bk', $info['video_bikeng'], random_int(900,1080));
        }
        //软装搭配
        $info['video_ruanzhuang'] = S('Cache:Home:Index:Index:Video:Rz');
        if(empty($info['video_ruanzhuang'])){
            $info['video_ruanzhuang'] = $videoLogic->getCategoryVideoList(VideoCodeEnum::VIDEO_CLASS_RZ);
            S('Cache:Home:Index:Index:Video:Rz', $info['video_ruanzhuang'], random_int(900,1080));
        }
        /*************装修学堂end************/

        //获取轮播
        $info["lunbo"] = S('Cache:Home:Index:lunbo:v3');
        if(!$info["lunbo"]){
            $info["lunbo"] = D("Advbanner")->getAdvList("home_advbanner","000001",5);
            S('Cache:Home:Index:lunbo:v3',$info["lunbo"],random_int(900,1080));
        }

        // 装修公司如果不填写链接，默认公司主页
        // 多域名模式下动态替换 一定要放在缓存外 缓存中替换是不行的
        foreach ($info["lunbo"] as $key => $value) {
            if (empty($value['url']) && !empty($value['company_id'])) {
                $value['url'] = $SCHEME_HOST['scheme_full'].$value["bm"].".".$SCHEME_HOST['domain']."/company_home/".$value["company_id"]."/";
            }
            $value['url'] = urlDomainReplace($value['url'], $SCHEME_HOST['domain']); // 替换为当前访问域名
            $info["lunbo"][$key]['url'] = $value['url'];
        }

        $info["bigbanner_a"] = S('Cache:Home:Index:bigbanner_a');
        if(!$info["bigbanner_a"]){
          //获取通栏A
            $infoBigbannerA = D("Advbanner")->getAdvList("home_bigbanner_a","000001",3);
            $info["bigbanner_a"] = $infoBigbannerA;
            S('Cache:Home:Index:bigbanner_a',$info["bigbanner_a"],random_int(900,1080));
        }
        //设置默认值
        if(empty($info["bigbanner_a"])){
            $info["bigbanner_a"][0] = [
                'img_url'=>'bigadv/20191203/5de5cc320c671-b.jpg',
                'url'=>'/zhaobiao/'
            ];
        }
        $info["bigbanner_b"] = S('Cache:Home:Index:bigbanner_b');
        if(!$info["bigbanner_b"]){
          //获取通栏B
            $infoBigbannerB = D("Advbanner")->getAdvList("home_bigbanner_b","000001",3);
            $info["bigbanner_b"] = $infoBigbannerB;
            S('Cache:Home:Index:bigbanner_b', $info["bigbanner_b"],random_int(900,1080));
        }
        //设置默认值
        if(empty($info["bigbanner_b"])){
            $info["bigbanner_b"][0] = [
                'img_url'=>'bigadv/20191203/5de5cc320c671-b.jpg',
                'url'=>'/zhaobiao/'
            ];
        }
        $info["bigbanner_c"] = S('Cache:Home:Index:bigbanner_c');
        if(!$info["bigbanner_c"]){
          //获取通栏C
            $infoBigbannerC = D("Advbanner")->getAdvList("home_bigbanner_c","000001",3);
            $info["bigbanner_c"] = $infoBigbannerC;
            S('Cache:Home:Index:bigbanner_c', $info["bigbanner_c"],random_int(900,1080));
        }
        //设置默认值
        if(empty($info["bigbanner_c"])){
            $info["bigbanner_c"][0] = [
                'img_url'=>'bigadv/20191203/5de5cc320c671-b.jpg',
                'url'=>'/zhaobiao/'
            ];
            $info["bigbanner_c"][1] = [
                'img_url'=>'bigadv/20191203/5de5cc320c671-b.jpg',
                'url'=>'/zhaobiao/'
            ];
        }
        //友情链接
        $friendLink = S('Cache:Home:Index:friendLink');
        if(!$friendLink){
          $friendLink["link"] = D("Friendlink")->getFriendLinkList('000001',1);
          //获取热门城市
          $result =D("Friendlink")->getFriendLinkList('000001',2);
          foreach ($result as $key => $value) {
             $hotCity[] = $value;
          }
          $friendLink["hotCity"] = $hotCity;

          //获取热门标签
          $hotTag =D("Friendlink")->getFriendLinkList('000001',3,'shouye');
          $friendLink["hotTags"] = $hotTag;
          S('Cache:Home:Index:friendLink',$friendLink,900);
        }

        //获取是否显示获取报价弹层
        if(!isset($_COOKIE["w_index"])){
            $result = $this->getShowWindowTmp();
            if ($result) {
                $this->assign("isOpen",true);
            }
        }

        //首次进入弹窗
        $alert_order = cookie('home_first_order:'.date('Y-m-d'));
        if(empty($alert_order)){
            cookie('home_first_order:'.date('Y-m-d'),1,86400);
            $this->assign('alert_order',1);
        }

        // 底部二维码显示控制
        if (C("QZ_YUMING") == $SCHEME_HOST['domain']) {
            $ShowControlQRcode_n1 = 'on';
        } else {
            $ShowControlQRcode_n1 = 'off';
        }
        $this->assign("ShowControlQRcode", $ShowControlQRcode_n1);

        //累计报价数字
        $total_num = releaseCount('fbzrs');
        $bjNum['total_num'] = str_split($total_num);
        //剩余名额
        $bjNum['surplus_num'] = releaseCount('fbsyrs');

        $this->assign("bjNum",$bjNum);
        $this->assign("info",$info);
        $this->assign("friendLink",$friendLink);
        $this->display("index");
    }

    private function brands($cityid){
        $brands = S('Cache:Home:Index:brands:'.$cityid);
        if(!$brands || count($brands < 56)){
            $brands = D("User")->getAllComapnys('rand()',56,'1');
            foreach ($brands as $key => $value) {
                if(strpos($value['logo'],'http') === false){
                    if(strpos($value['logo'],'static') === false){
                        $value['logo'] = '//static.qizuang.com/'.$value['logo'];
                    }
                    $brands[$key]['logo'] = 'http:'.$value['logo'];
                }
            }
            S('Cache:Home:Index:brands:'.$cityid,$brands,900);
        }
        return $brands;
    }

        /**
     * 通过浏览器获取的城市名称，获取$cityinfo
     * @return [type] JSON [城市信息，状态： 1成功|0失败]
     */
    public function getCityByCityName() {

        $cityname = $_GET['cityname'];
        if (empty($cityname)) {
            $this->ajaxReturn(array("data"=>"","info"=>"","status"=>0));
            exit();
        }

        $cityname = mb_substr($cityname,0,2,"utf-8");
        $city = D("Quyu")->getCityInfoByName($cityname);
        $city["oldName"] = $city["cname"];

        if (count($city) > 0) {
            //查询该城市是否有会员
            $vipcount = D('User')->getRealComapnyCount($city["cid"]);
            if($vipcount > 0){
                //有会员，返回城市信息，并写入session
                //获取城市信息
                $cityInfo = array(
                    "bm"=>$city["bm"],
                    "name"=>$city["oldName"],
                    "id" =>$city["cid"],
                    "pid"=>$city['uid'],
                    "adj_city"=>$city["adj_city"],
                    "lng"=>$city["lng"],
                    "lat"=>$city["lat"],
                    "province"=>str_replace("省","",$city["province"]),
                    "provincefull"=>$city["province"]
                );

                $cityArr = D('Quyu')->getAreaByFatherId($cityInfo['id'])[0];
                $cityInfo['cityarea'] = $cityArr['name'];
                $cityInfo['areaid'] = $cityArr['id'];
                $cityInfo['vipcount'] = $vipcount;//有真会员
                $_SESSION["m_cityInfo"] = $cityInfo;
                if(empty($_SESSION["m_mapUseInfo"])){
                    $_SESSION["m_mapUseInfo"] = $cityInfo;
                    $this->ajaxReturn(array("info"=>$cityInfo,"status"=>1));
                }
                $this->ajaxReturn(array("info"=>$_SESSION["m_mapUseInfo"],"status"=>1));
            }else{
                //没有真实会员，去qz_area表查询父级城市
                $area = D("Common/Quyu")->getAreaInfos($city["cid"]);
                if(empty($area)){
                    //没有父级城市
                    $this->ajaxReturn(array("info"=>"","status"=>0));
                }else{
                    //逻辑有问题，待产品确认，这段代码不会执行，暂时不要删除 by：陈凯
                    // //有父级城市
                    // $cid = $area['fatherid'];
                    // //查询该城市是否有会员
                    // $vipnum = D('User')->getRealComapnyCount($cid);
                    // if($vipnum > 0){
                    //     //有会员，查询该城市信息
                    //     $f_city = D("Common/Quyu")->getCityByCid($cid);
                    //     $city =  D("Common/Quyu")->getCityInfoByBm($f_city['bm']);
                    //     //获取城市信息
                    //     $cityInfo = array(
                    //         "bm"=>$city["bm"],
                    //         "name"=>$city["oldName"],
                    //         "id" =>$city["cid"],
                    //         "pid"=>$city['uid'],
                    //         "adj_city"=>$city["adj_city"],
                    //         "lng"=>$city["lng"],
                    //         "lat"=>$city["lat"],
                    //         "province"=>str_replace("省","",$city["province"]),
                    //         "provincefull"=>$city["province"]
                    //     );
                    //     $cityArr = D('Quyu')->getAreaByFatherId($cityInfo['id'])[0];
                    //     $cityInfo['cityarea'] = $cityArr['name'];
                    //     $cityInfo['areaid'] = $cityArr['id'];
                    //     $cityInfo['vipcount'] = $vipnum;//有真会员

                    //     if(empty($_SESSION["m_mapUseInfo"])){
                    //         $_SESSION["m_mapUseInfo"] = $cityInfo;
                    //         $this->ajaxReturn(array("info"=>$cityInfo,"status"=>1));
                    //     }
                    //     $this->ajaxReturn(array("info"=>$_SESSION["m_mapUseInfo"],"status"=>1));
                    // }else{
                    //     //父级城市没有会员
                    //     $this->ajaxReturn(array("info"=>"","status"=>0));
                    // }

                    //暂时赋值空
                    $this->ajaxReturn(array("info"=>"","status"=>0));
                }
            }
        }else {
            $this->ajaxReturn(array("info"=>"","status"=>0));
        }
    }

    /**
     * 获取验证码
     * @return [type] [description]
     */
    public function verify(){
        getVerify("",4,120,35);
    }

    public function company(){
        $p = I("get.p");
        $url = $this->SCHEME_HOST['scheme_full']. C("QZ_YUMINGWWW")."/company/";
        if(!empty($url)){
            $url .= "?p=".$p;
        }

        header( "HTTP/1.1 301 Moved Permanently" );
        header( "Location:".$url);
    }

     /**
     * 从www主站访问的装修公司文章301到对应的分站上去
     *
     */
    public function zixun(){
        $id = I("get.id");
        if(!empty($id)){
            $info = D("Info")->getSingleInfoById($id);
            if(!empty($info)){
                header( "HTTP/1.1 301 Moved Permanently");
                header("Location:  ".$this->SCHEME_HOST['scheme_full'] .$info["bm"].".".C("QZ_YUMING")."/zixun_info/".$id.".shtml");
                die();
            }else{
                $this->_error();
            }
        }
        $this->_error();
    }

   /**
    * 20190225新增客户端介绍页面
    */
    public function xiazai(){
        $this->assign("tabIndex",7);
        $this->display("");
    }

    /**
     * 老版设计师设计作品
     * @return [type] [description]
     */
    public function works(){
        $id = I("get.id");
        $user = D("User")->getSingleUserInfoById($id);

        if(count($user) > 0){
            $bm = $user["bm"];
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location: ".$this->SCHEME_HOST['scheme_full'] .$bm.".".C("QZ_YUMING")."/blog/".$id);
            die();
        }
        $this->_error();
        die();
    }

    /**
     * 老版装修公司案例路由
     * @return [type] [description]
     */
    public function company_case(){
        $id = I("get.id");
        $p = I("get.p");
        $user = D("User")->getSingleUserInfoById($id);
        if(count($user) > 0){
            $bm = $user["bm"];
            $url = $this->SCHEME_HOST['scheme_full'] .$bm.".".C("QZ_YUMING")."/company_case/".$id;
            if(!empty($p)){
                $url .= "?p=".$p;
            }
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:".$url);
            die();
        }
        $this->_error();
        die();
    }

    /**
     * 老版设计师文章
     * @return [type] [description]
     */
    public function works_info(){
        $id = I("get.id");
        $article = D("Article")->getSingleArticle($id);

        if(count($article) > 0){
            $bm = $article["bm"];
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:  ".$this->SCHEME_HOST['scheme_full'] .$bm.".".C("QZ_YUMING")."/article_info/".$id.".html");
            die();
        }
        $this->_error();
        die();
    }

    /**
     * 老版主站效果图跳转方法
     * @return [type] [description]
     */
    public function caseinfo(){
        $id = I("get.id");
        $case = D("Cases")->getSingleById($id);
        if(count($case) > 0){
            $bm = $case["bm"];
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location: ".$this->SCHEME_HOST['scheme_full'] .$bm.".".C("QZ_YUMING")."/caseinfo/".$id.".shtml");
            die();
        }
        $this->_error();
    }

    /**
     * 老版装修公司路由
     * @return [type] [description]
     */
    public function company_home(){
        $id = I("get.id");
        $user = D("User")->getSingleUserInfoById($id);
        if(count($user) > 0){
            $bm = $user["bm"];
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location: ".$this->SCHEME_HOST['scheme_full'] .$bm.".".C("QZ_YUMING")."/company_home/".$id);
            die();
        }
        $this->_error();
    }

    /**
     * 老版装修公司路由
     * @return [type] [description]
     */
    public function blog(){
        $id = I("get.id");
        $user = D("User")->getSingleUserInfoById($id);
        if(count($user) > 0){
            $bm = $user["bm"];
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location: ".$this->SCHEME_HOST['scheme_full'] .$bm.".".C("QZ_YUMING")."/blog/".$id);
            die();
        }
        $this->_error();
    }

    /**
     * 验证验证码
     * @return [type] [description]
     */
    public function check_verify(){
        if($_POST){
            $code = strtolower($_POST["code"]);
            if(check_verify($code)){
                 $this->ajaxReturn(array("data"=>"","info"=>"验证码正确！","status"=>1));
            }
            $this->ajaxReturn(array("data"=>"","info"=>"验证码不正确！","status"=>0));
        }
    }

      /**
     * 用户登录框
     * @return [type] [description]
     */
    public function login(){
        R("Common/Login/Login");
        die();
    }

    /**
     * 用户登录
     * @return [type] [description]
     */
    public function loginin(){
        R("Common/Login/Loginin");
        die();
    }
    /**
     * 用户退出
     * @return [type] [description]
     */
    public function loginout(){
         R("Common/Login/loginout");
          die();
    }
    /**
     * 用户收藏
     * @return [type] [description]
     */
    public function collect(){
         R("Common/Collect/setCollect");
          die();
    }

    //设置首页弹窗不显示的COOKIE
    public function setwindowswitch(){
        R("Common/Zbfb/setwindowswitch");
        die();
    }

    //设置首页弹窗不显示的COOKIE
    public function refresh(){
        R("Common/Zbfb/refresh");
        die();
    }

    //设置首页弹窗不显示的COOKIE
    public function dispatcher(){
        R("Common/Zbfb/dispatcher");
        die();
    }

    //装修小贴士路由
    public function guide(){
        R("Common/Zbfb/guide");
        die();
    }

    //反馈信息路由
    public function feedback(){
        R("Common/Zbfb/feedback");
        die();
    }


    public function getZbPrice(){
        R("Common/Zbfb/getZbPrice");
        die();
    }

    public function getBJResult(){
        R("Common/Zbfb/getBJResult");
        die();
    }

    public function fb_order(){
        R("Common/Zbfb/fb_order");
        die();
    }

     /**
     * 短信发送
     * @return [type] [description]
     */
    public function sendsms(){
        R("Common/Sms/sendsms");
        die();
    }

    //验证验证码是否正确
    public function verifysmscode(){
        R("Common/Sms/verifysmscode");
        die();
    }

    public function run(){
        R("Common/Login/run");
        die();
    }

    //免费电话咨询
    public function freetel() {
        R("Common/Zbfb/freetel");
        die();
    }

    //评论回复路由
    public function reply()
    {
        R("Common/Comment/reply");
        die();
    }

    public function replyup()
    {
        R("Common/Comment/replyup");
        die();
    }

    public function replydown()
    {
        R("Common/Comment/replydown");
        die();
    }

    /**
     * 装修工具
     * @return [type] [description]
     */
    public function tools(){
        //获取装修咨询分类
        $typeArr = S('Cache:Zixun:typeArrXcdg');
        if(!$typeArr){
            $typeArr = D("WwwArticleClass")->getArticleClassById(143);
            S('Cache:Zixun:typeArrXcdg',$typeArr,900);
        }
        $ids[] = array_unique($typeArr["ids"]);
        $data = D("WwwArticle")->getArticleListByIds($ids,0,3,"",false,true);
        //最新
        $target = empty($_GET["t"]) == true?"qz":I("get.t");
        $tags = array(
                array("key"=>"qz","value"=>"墙砖计算器","src"=>"/tools/qz/"),
                array("key"=>"dz","value"=>"地砖计算器","src"=>"/tools/dz/"),
                array("key"=>"db","value"=>"地板计算器","src"=>"/tools/db/"),
                array("key"=>"bz","value"=>"壁纸计算器","src"=>"/tools/bz/"),
                array("key"=>"tl","value"=>"涂料计算器","src"=>"/tools/tl/"),
                array("key"=>"cl","value"=>"窗帘计算器","src"=>"/tools/cl/"),
                array("key"=>"tfj","value"=>"填缝剂计算器","src"=>"/tools/tfj/")
                      );
        foreach ($tags as $key => $value) {
            if($value["key"] == $target){
                $this->assign("keys",$value);
            }
        }

        $cases = $this->getCasesList(1,3);
        $this->assign("cases",$cases);
        $this->assign("target",$target);
        $this->assign("recommend",$data);
        $this->assign("tags",$tags);
        $this->display("Index/jsq");
    }

        /**
     * 采集数据
     * @return [type] [description]
     */
    public function hm()
    {
        die();
        //导入扩展文件
        import('Library.Org.Util.App');
        $app = new \App();

        $state = 0;
        $engine = "";
        if (I("get.ref") !== "") {
            //判断是否是外链
            $result = preg_match('/'.APP_HTTP_DOMAIN.'/',I("get.ref"));
            if (!$result) {
               //不是本站点击的都算是外链
               $state = 2;
            }

            //查看是否是搜索引擎访问的
            //主流搜索引擎域名
            $engineArray = array("baidu.com"=>"百度","sogou.com"=>"搜狗","so.com"=>"360","youdao.com"=>"有道","soso.com"=>"搜搜","cn.bing.com"=>"必应中国","sm.cn"=>"神马");

            foreach ($engineArray as $key => $value) {
                $result = preg_match('/'.$key.'/',I("get.ref"));
                if ($result) {
                    $engine = $value;
                    break;
                }
            }

            if (!empty($engine)) {
                $state = 1;
                cookie('QZENGINE', $engine, array('expire'=> 3600 ,'domain' => '.'.APP_HTTP_DOMAIN));
            }

            if ($state != 0) {
               cookie('QZSTATE', $state, array('expire'=> 3600 ,'domain' => '.'.APP_HTTP_DOMAIN));
            }

        }

        $host = I("get.host") != ""? I("get.host"):cookie("QZHOST");
        // cookie('QZHOST',$host, array('expire'=> 3600,'path'=>'/','domain' => '.'.APP_HTTP_DOMAIN));
        $engine = cookie('QZENGINE') == null?$engine:cookie('QZENGINE');
        $state = cookie('QZSTATE') == null?$state:cookie('QZSTATE');

        $uuid = I("get.uuid");
        if (empty($uuid)) {
            $tag = cookie("QZUUID") == null?"":cookie("QZUUID");
        }

        $tag =  I("get.tag");
        if (empty($tag)) {
            $tag = cookie("QZSRC") == null?"":cookie("QZSRC");
        }

        $tag = explode("#",$tag);
        $tag = explode("?",$tag[0]);
        $tag = $tag[0];

        $ip = $app->get_client_ip();
        //获取友链信息
        $data = array(
            "user_agent" => $_SERVER["HTTP_USER_AGENT"],
            "ip" => $ip,
            "host" => $host,
            "time_in" => I("get.timeIn"),
            "time_out" => I("get.timeOut"),
            "visit_time" => I("get.visit_time"),
            "ck" => I("get.ck"),
            "referer" => I("get.ref"),
            "tag" => $tag,
            "ua" => md5($ip.$_SERVER["HTTP_USER_AGENT"]),
            "uuid" => $uuid,
            "engine" => $engine,
            "state" => $state,
            "time" => time(),
            "date" => date("Y-m-d")
        );
        M("market_acquisition")->add($data);

        header('HTTP/1.1 200 OK');
        header ('Content-Type: image/gif');
        header('Cache-Control:no-cache');
        $im = imagecreate(1, 1);
        $bg_color = imagecolorallocate($im, 255, 255, 255);
        imagefilledrectangle($im, 0, 0, 1, 1, $bg_color);
        echo imagegif($im);
        imagedestroy($im);
    }

    //月活跃度排行
    private function getRankList(){
        $ranks = D("User")->getRanksList();
        $comids = null;
        foreach ($ranks as $key => $value) {
            $comids[] = $value['id'];
        }
        $comids_count = D("User")->getCompanysCount($comids);  //查询公司 所有统计
        foreach ($ranks as $key => &$value) {
            foreach ($comids_count as $keys => $values) {
                if ($value['id'] == $values['id']) {
                    $value['casecount']    = $values['casecount'];    //所有案例个数统计
                    $value['infocount']    = $values['infocount'];    //所有文章个数统计
                    $value['commentcount'] = $values['commentcount']; //所有业主点评个数统计
                    $value['allCounts'] = $value['casecount'] + $value['infocount'] + $value['commentcount'];
                }
            }
        }
        return $ranks;
    }

     /**
     * 获取装修三步骤
     * @return [type] [description]
     */
    private function getStep($id,$limit,$isTop){
        //根据ID查询相对应的文章类别
        $result =  D("WwwArticleClass")->getArticleClassById($id);
        $category = array();
        foreach ($result["child"] as $key => $value) {
            //获取子类的文章
            $articles = D("WwwArticle")->getArticleListByIds($value["ids"],0,$limit,"",true);

            foreach ($articles as $i => $j) {
              $value["child"][$j["cid"]]["articles"][] = $j;
            }

            foreach ($value["child"] as $k => $val) {
                $category["child"][$key]["classnames"][] = $val["classname"];
                foreach ($val["articles"] as $n => $v) {
                    $category["child"][$key]["child"][$k]["classname"] = $val["classname"];
                    $category["child"][$key]["child"][$k]["shortname"] = $val["shortname"];
                    $category["child"][$key]["child"][$k]["articles"][$n+1]["title"] = $v["title"];
                    $category["child"][$key]["child"][$k]["articles"][$n+1]["id"] = $v["id"];
                    $category["child"][$key]["child"][$k]["articles"][$n+1]["shortname"] = $v["shortname"];
                    if($n == 0){
                      $category["child"][$key]["child"][$k]["articles"][$n+1]["face"] = $v["face"];
                      $category["child"][$key]["child"][$k]["articles"][$n+1]["subtitle"] = $v["subtitle"];
                    }
                }
            }
        }
        return $category;
    }

    /**
     * 获取分站文章
     * @return [type] [description]
     */
    private function getLittleArticle($limit){
        $articles = D("Littlearticle")->getArticleList("","",0,$limit);
        foreach ($articles as $key => $value) {
            $title = strstr($value["title"],"_",true);
            if($title !== false){
                $articles[$key]["title"] = $title;
            }
            //usnet掉不需要的字段数据
            unset($articles[$key]["cs"]);
            unset($articles[$key]["authid"]);
            unset($articles[$key]["state"]);
            unset($articles[$key]["classid"]);
            unset($articles[$key]["keywords"]);
            unset($articles[$key]["content"]);
            unset($articles[$key]["description"]);
            unset($articles[$key]["addtime"]);
            unset($articles[$key]["optime"]);
            unset($articles[$key]["istop"]);
            unset($articles[$key]["tags"]);
            unset($articles[$key]["classtitle"]);
            unset($articles[$key]["littledescription"]);
            unset($articles[$key]["classkeywords"]);
            unset($articles[$key]["classname"]);
        }
        return $articles;
    }

    private function getBrands($city){
       $result = D("Advbanner")->getBrandList($city,14);


        foreach ($result as $key => $value) {
            $result[$key]["star_sj"] = 5;
            $result[$key]["star_fw"] = 5;
            $result[$key]["star_sg"] = 5;
            foreach ($this->stars as $k => $val) {
                if($value["avgsj"] >= $val["min"] &&  $value["avgsj"] < $val["max"]){
                    $result[$key]["star_sj"] = $k;
                    break;
                }
            }
            foreach ($this->stars as $k => $val) {
              if($value["avgfw"] >= $val["min"] &&  $value["avgfw"] < $val["max"]){
                  $result[$key]["star_fw"] = $k;
                  break;
              }
            }

            foreach ($this->stars as $k => $val) {
                if($value["avgsg"] >= $val["min"] &&  $value["avgsg"] < $val["max"]){
                    $result[$key]["star_sg"] = $k;
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * 获取最新的评论
     * @param  [type] $limit     [description]
     * @param  [type] $cs        [description]
     * @param  [type] $userCount [description]
     * @return [type]            [description]
     */
    private function  getNewComment($limit){
        $comments = D("Common/Comment")->getCommentsByCity($limit);

        foreach ($comments as $key => $value) {
            $comments[$key]["star"] = 5;
            $avg = round(($value["avgsj"]+$value["avgfw"]+$value["avgsg"])/3,1);
            foreach ($this->stars as $k => $val) {
                if($avg >= $val["min"] &&  $avg < $val["max"]){
                    $comments[$key]["star"] = $k;
                    break;
                }
            }
        }
        return $comments;
    }

    /**
     * 获取推荐设计师
     * @return [type] [description]
     */
    private function getDesigners($limit){
      $list = D("Advdesigner")->getHomeDesignerList($limit);
      return $list;
    }

    //获取首页问答模块
    private function getWenda(){
      $result = S('Cache:Home:Index:wenda');
      if(!$result){
        //实例化Model
        $Db = D('Common/Ask');
        //取分类
        $category = $Db->getCategorys('',true);
        foreach ($category as $key => $value) {
          $result['category'][] = array('cid' => $value['cid'],'cname' => $value['name']);
        }
        unset($category);
        //精华
        $dist = $Db->getOption('ask_dist',true);
        $result['dist'] = $dist['0'];
        //热门装修问答
        $hotAsk = $Db->getHotQuestion('6');
        $result['hotImg'] = array_shift($hotAsk);
        $result['hot'] = $hotAsk;
        //等你来答
        $result['noAnwser'] = $Db->getQuestionList(array("adopt_time"=>array("EQ",'0')),'rand()',0,'4');
        //帮助人数
        $result['helpPeople'] = R("Wenda/getHelpPeople");
        S('Cache:Home:Index:wenda',$result,900);
      }
      return $result;
    }

    /**
     * 获取装修案例效果图
     * @return [type] [description]
     */
    private function getCasesList($type ='',$limit = 20){
        //type 1.装修类型 2.户型 3.风格
        $result = D("Common/Cases")->getIndexCasesList($type,$limit);

        if(count($result) > 0){
            $rand  = array_rand($result,3);
            $pattern = array('\n','\\');
            foreach ($rand as $key => $value) {
                if(!empty($result[$value]["text"])){
                    $result[$value]["text"] = htmlspecialchars_decode($result[$value]["text"],ENT_QUOTES);
                    $result[$value]["text"] = str_replace($pattern,"",$result[$value]["text"]);
                    if(mb_strlen($result[$value]["text"],"utf-8") > 140){
                        $result[$value]["text"] = mb_substr( $result[$value]["text"],0,140,"utf-8")."...";
                    }
                }
                $cases[] = $result[$value];
            }
            return $cases;
        }
        return null;
    }
}
