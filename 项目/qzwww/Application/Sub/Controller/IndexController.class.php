<?php
// +----------------------------------------------------------------------
// | IndexController  分站首页控制器
// +----------------------------------------------------------------------
namespace Sub\Controller;

use Common\Model\Logic\CommentLogicModel;
use Common\Model\Logic\CompanyLogicModel;
use Common\Model\Logic\DesignerLogicModel;
use Common\Model\Logic\SubthematicLogicModel;
use Common\Model\Logic\ThematicWordsLogicModel;
use Sub\Common\Controller\SubBaseController;
use Common\Enums\ArticleCodeEnum;
use Common\Enums\SearchCodeEnum;
use Common\Enums\CompanyCodeEnum;
use Common\Enums\ThematicWordsCodeEnum;
use Common\Enums\TuCodeEnum;
use Common\Enums\VideoCodeEnum;
use Common\Model\LeixingModel;
use Common\Model\Logic\ArticleLogicModel;
use Common\Model\Logic\AskLogicModel;
use Common\Model\Logic\BaikeLogicModel;
use Common\Model\Logic\DecorationLogicModel;
use Common\Model\Logic\TuCategoryLogicModel;
use Common\Model\Logic\UserLogicModel;
use Common\Model\Logic\VideoLogicModel;
use Common\Model\CompanyModel;

class IndexController extends SubBaseController
{
    public function _initialize()
    {
        parent::_initialize();
        //添加顶部搜索栏信息
        $this->assign('serch_uri','companysearch');
        $this->assign('serch_type','装修公司');
        $this->assign('holdercontent','全国超过十万家装修公司为您免费设计');
    }

    /**
     * 分站首页
     *
     * @return void
     */
    public function index()
    {
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
            $this->assign('robot',1);
        }
        $cityInfo = $this->cityInfo;
        //获取head头关键字keywords描述description标题title
        $keys["title"] =$cityInfo["name"]."装修_".$cityInfo["name"]."装修公司_".$cityInfo["name"]."装修网-齐装网";
        $keys["keywords"] = $cityInfo["name"].'装修,'.$cityInfo["name"].'装修公司,'.$cityInfo["name"].'装修网';
        $keys["description"] = $cityInfo["name"].'齐装网致力于为广大业主提供透明、保障、省心的装修服务，汇集众多优秀的'.$cityInfo["name"].'装修装饰公司和家装工装设计师，并提供权威的'.$cityInfo["name"].'装修公司排名供您参考';

        $articleLogic = new ArticleLogicModel();

        /*************装修公司部分start************/
        //区域
        if(!empty($cityInfo['child'])){
            $sort = array_column($cityInfo['child'],'orders');
            foreach ($sort as $k=>$v){
                $sort[$k] = (int)$v;
            }
            array_multisort($sort,SORT_ASC,$cityInfo['child']);
        }
        //装修公司规模
        $info["guimo"] = S('Cache:Sub:Index:Index:CompanyGm');
        if (empty($info["guimo"])) {
            $info["guimo"] = D("Common/Guimo")->gethGm();
            S('Cache:Sub:Index:Index:CompanyGm', $info["guimo"], random_int(900, 1080));
        }

        //获取服务保障列表
        $info["baozhang"] = S('Cache:Sub:Index:Index:CompanyBz');
        if (empty($info["baozhang"])) {
            $info["baozhang"] = D("Common/Leixing")->getbg();
            S('Cache:Sub:Index:Index:CompanyBz', $info["baozhang"], random_int(900, 1080));
        }

        $companyLogic = new CompanyLogicModel();
        //品牌榜 - 热门装修公司
        $info["quality_company"] = S('Cache:Sub:Index:Index:QualityCompany' . $cityInfo["bm"]);
        if (empty($info['quality_company'])) {
            $brands = $companyLogic->getSubstationRecommend($cityInfo["id"], 30);
            $rank = $companyLogic->getCompanyRankInfo(array_column($brands, 'company_id'));
            foreach ($brands as $k => $v) {
                //真会员才取rank表数据
                if($v['on'] == 2 && $v['fake'] == 0){
                    $brands[$k]['case_count'] = isset($rank[$v['company_id']]['casesnum']) ? $rank[$v['company_id']]['casesnum'] : $v['case_count'];
                    $brands[$k]['comment_count'] = isset($rank[$v['company_id']]['haoping']) ? $rank[$v['company_id']]['haoping'] : $v['comment_count'];
                }
                //只取当前城市的数据
                $brands[$k]['bm'] = $cityInfo['bm'];
            }
            $info['quality_company'] = array_chunk($brands, 6);//用于页面数据放
            S('Cache:Sub:Index:Index:QualityCompany' . $cityInfo["bm"], $info["quality_company"], random_int(900, 1080));
        }
        //人气设计师
        $info["designer"] = S('Cache:Sub:Index:Index:Designer'.$cityInfo["bm"]);
        if (!$info["designer"]) {
            // 产品说直接取新会员的设计师PV倒序排序
            $designerLogic = new DesignerLogicModel();
            $info['designer'] = $designerLogic->getPopularDesigner($cityInfo["id"]);
            S('Cache:Sub:Index:Index:Designer'.$cityInfo["bm"], $info["designer"], random_int(900,1080));
        }

        // 业主评价
        $info['commentlist'] = S('Cache:Sub:Index:Index:commentlist' . $cityInfo["bm"]);
        if (!$info['commentlist']) {
            $commentLogic = new CommentLogicModel();
            $info['commentlist'] = $commentLogic->getYeZhuComment($cityInfo["id"], 6);
            S('Cache:Sub:Index:Index:commentlist' . $cityInfo["bm"], $info["commentlist"], 86400);
        }
        $info['commentcount'] = count($info['commentlist']);


        //装修公司排行榜
        $info["rankCompany"] = S('Cache:Sub:Index:rankCompany:v2:'.$cityInfo["bm"]);
        $companyLogic = new CompanyLogicModel();
        if (!$info['rankCompany']) {
            $map['cs'] = $cityInfo['id'];
            $map['sale'] = 3;

            // feature-1009    合肥首页/分站排除会员40165
            if ($cityInfo['id'] == 340100) {
                $map['hiddenid'] = array('40165');
            }
            $infoLiangfang = D('Common/Company')->getLiangfangList($map, 0, 5);
            $info['rankCompany']['liangfang'] = $infoLiangfang['result'];
            $infoQiandan = D('Common/Company')->getQiandanList($map, 0, 5, "subIndex");
            $info['rankCompany']['qiandan'] = $infoQiandan['result'];
            $trueCompanyLiangfangNumber = count($info['rankCompany']['liangfang']);
            if ($trueCompanyLiangfangNumber < 5) {
                $fakeLiangfangCompany = $companyLogic->getTrueFakeCompany($cityInfo['id'], 5 - $trueCompanyLiangfangNumber, ['sort' => 'asc', 'a.register_time' => 'desc']);
                $info['rankCompany']['liangfang'] = array_merge($info['rankCompany']['liangfang'], $fakeLiangfangCompany);
            }
            $trueCompanyQiandanNumber = count($info['rankCompany']['qiandan']);
            if ($trueCompanyQiandanNumber < 5) {
                $fakeQiandanCompany = $companyLogic->getTrueFakeCompany($cityInfo['id'], 5 - $trueCompanyQiandanNumber);
                $info['rankCompany']['qiandan'] = array_merge($info['rankCompany']['qiandan'], $fakeQiandanCompany);
            }
            S('Cache:Sub:Index:rankCompany:v2:'.$cityInfo['bm'],$info['rankCompany'],900);
        }

        /*************装修公司部分end************/

        /*************装修效果图start************/
        //风格 / 户型 /局部统一获取
        $category = S('Cache:Sub:Index:Index:Category');
        if(empty($category)){
            $tuLogic = new TuCategoryLogicModel();
            $category = $tuLogic->getMeituLv2CategoryByType(TuCodeEnum::TU_LX_JIA);
            $category = array_column($category,null,'id');
            S('Cache:Sub:Index:Index:Category', $category, random_int(900,1080));
        }
//        $info['fengge'] = $category[TuCodeEnum::TU_LX_JIA_FG];
//        $info['huxing'] = $category[TuCodeEnum::TU_LX_JIA_HX];
//        $info['jubu'] = $category[TuCodeEnum::TU_LX_JIA_JB];

        $decorationLogic = new DecorationLogicModel();
        //新房装修(家装) 查询标签:新房装修效果图
        $info['tu_xinfang'] = S('Cache:Sub:Index:Index:Tu:Xinfang');
        if(empty($info['tu_xinfang'])){
            $info['tu_xinfang'] = $decorationLogic->getThematicWordsByModule(SearchCodeEnum::getSceneWord('xfzx'),'', ThematicWordsCodeEnum::MODULE_CODE_JZ, 6);
            S('Cache:Sub:Index:Index:Tu:Xinfang',$info['tu_xinfang'],random_int(900,1080));
        }
        //局部装修(家装) 查询标签:局部装修
        $info['tu_jubu'] = S('Cache:Sub:Index:Index:Tu:Jubu');
        if(empty($info['tu_jubu'])){
            $info['tu_jubu'] = $decorationLogic->getThematicWordsByModule(SearchCodeEnum::getSceneWord('jbzx'),'', ThematicWordsCodeEnum::MODULE_CODE_JZ, 6);
            S('Cache:Sub:Index:Index:Tu:Jubu',$info['tu_jubu'],random_int(900,1080));
        }
        //户型改造(家装) 查询标签:户型改造
        $info['tu_jiufang'] = S('Cache:Sub:Index:Index:Tu:Jiufang');
        if(empty($info['tu_jiufang'])){
            $info['tu_jiufang'] = $decorationLogic->getThematicWordsByModule(SearchCodeEnum::getSceneWord('hxgz'),'', ThematicWordsCodeEnum::MODULE_CODE_JZ, 6);
            S('Cache:Sub:Index:Index:Tu:Jiufang',$info['tu_jiufang'],random_int(900,1080));
        }
        //商铺(公装) 查询标签:店铺装修
        $info['tu_shop'] = S('Cache:Sub:Index:Index:Tu:Shop');
        if(empty($info['tu_shop'])){
            $info['tu_shop'] = $decorationLogic->getThematicWordsByModule(SearchCodeEnum::getSceneWord('dpzx'),'', ThematicWordsCodeEnum::MODULE_CODE_GZ, 6);
            S('Cache:Sub:Index:Index:Tu:Shop',$info['tu_shop'],random_int(900,1080));
        }
        //写字楼(公装) 查询标签:写字楼装修
        $info['tu_building'] = S('Cache:Sub:Index:Index:Tu:Building');
        if (empty($info['tu_building'])) {
            $info['tu_building'] = $decorationLogic->getThematicWordsByModule(SearchCodeEnum::getSceneWord('xzlzx'), '', ThematicWordsCodeEnum::MODULE_CODE_GZ, 6);
            S('Cache:Sub:Index:Index:Tu:Building', $info['tu_building'], random_int(900,1080));
        }
        /*************装修效果图end************/

        /*************装修案例start************/
        //获取案例
        $info["cases"] = S('Cache:Sub:Index:Cases:v2:' . $cityInfo["bm"]);
        if (!$info["cases"]) {
            $info["cases"] = D("Advbanner")->getAdvList("home_cases", $cityInfo["id"], 5);
            $count = count($info["cases"]);
            if ($count < 5) {
                $cases = D("Advbanner")->getAdvList("home_cases", '000001', (5 - $count));
                if(count($cases) > 0){
                    $info["cases"] = array_merge($info["cases"],$cases);
                }
            }
            S('Cache:Sub:Index:Cases:v2:' . $cityInfo["bm"], $info["cases"], 900);
        }
        //装修指南
        //风格/空间
        $info['guide_fg'] = S('Cache:Sub:Index:Index:Guide:Fg');
        if(empty($info['guide_fg'])){
            $info['guide_fg'] = $articleLogic->getArticleByPidClass([ArticleCodeEnum::ARTICLE_ZN_FG, ArticleCodeEnum::ARTICLE_ZN_SPACE], 8, 2);
            S('Cache:Sub:Index:Index:Guide:Fg', $info['guide_fg'], random_int(900,1080));
        }
        //空间
        $info['guide_space'] = S('Cache:Sub:Index:Index:Guide:Space');
        if(empty($info['guide_space'])){
            $info['guide_space'] = $articleLogic->getArticleByPidClass(ArticleCodeEnum::ARTICLE_ZN_SPACE, 8,2);
            S('Cache:Sub:Index:Index:Guide:Space' , $info['guide_space'], random_int(900,1080));
        }
        //风水
        $info['guide_fs'] = S('Cache:Sub:Index:Index:Guide:Fs');
        if(empty($info['guide_fs'])){
            $info['guide_fs'] = $articleLogic->getArticleByPidClass(ArticleCodeEnum::ARTICLE_ZN_FS, 8,2);
            S('Cache:Sub:Index:Index:Guide:Fs', $info['guide_fs'], random_int(900,1080));
        }
        //家居
        $info['guide_jj'] = S('Cache:Sub:Index:Index:Guide:Jj');
        if(empty($info['guide_jj'])){
            $info['guide_jj'] = $articleLogic->getArticleByPidClass(ArticleCodeEnum::ARTICLE_ZN_YY, 8,2);
            S('Cache:Sub:Index:Index:Guide:Jj', $info['guide_jj'], random_int(900,1080));
        }
        /*************装修案例end************/

        /*************装修攻略start************/
        //装修流程
        $info['article_lc'] = S('Cache:Sub:Index:Index:Gonglue:lc');
        if(empty($info['article_lc'])){
            $article_lc_classid = [ArticleCodeEnum::ARTICLE_CLASS_Q, ArticleCodeEnum::ARTICLE_CLASS_Z, ArticleCodeEnum::ARTICLE_CLASS_H];
            $info['article_lc'] = $articleLogic->getArticleByPidClass($article_lc_classid, 6, 2);
            S('Cache:Sub:Index:Index:Gonglue:lc' , $info['article_lc'], random_int(900,1080));
        }
        /*************装修攻略end************/
        /*************装修问答start************/
        $wendaLogic = new AskLogicModel();
        //精华问答
        $info['wenda_boutique'] = S('Cache:Sub:Index:Index:Wenda:Boutique');
        if(empty($info['wenda_boutique'])){
            $info['wenda_boutique'] = $wendaLogic->getHotAskList(4);
            S('Cache:Sub:Index:Index:Wenda:Boutique', $info['wenda_boutique'], random_int(900,1080));
        }
        /*************装修问答end************/
        /*************装修百科start************/
        $baikeLogic = new BaikeLogicModel();
        //精选百科
        $info['baike_boutique'] = S('Cache:Sub:Index:Index:Baike:Boutique');
        if(empty($info['baike_boutique'])){
            $info['baike_boutique'] = $baikeLogic->getHomeNewBaike();
            S('Cache:Sub:Index:Index:Baike:Boutique', $info['baike_boutique'], random_int(900,1080));
        }
        /*************装修百科end************/

        /*************最新推荐start************/
        //最新攻略
//        $info['article_new'] = S('Cache:Home:Sub:Index:Article:New');
//        if(empty($info['article_new'])){
//            $info['article_new'] = $articleLogic->getArticleByPidClass('xinwen', 6,3);
//            S('Cache:Home:Sub:Index:Article:New', $info['article_new'], random_int(900,1080));
//        }
        $info['zhishi'] = S('Cache:Sub:Index:Index:Zhishi:Hot');
        if(empty($info['zhishi'])){
            $wordsLogic = new ThematicWordsLogicModel();
            $info['zhishi'] = $wordsLogic->getNewList(6);
            S('Cache:Home:Index:Index:Zhishi:Hot', $info['zhishi'], random_int(900,1080));
        }
        /*************最新推荐end************/


        /*************装修学堂start************/
        //科普扫盲
        $videoLogic = new VideoLogicModel();
        $info['video_changjian'] = S('Cache:Sub:Index:Index:Video:Cj');
        if(empty($info['video_changjian'])){
            $info['video_changjian'] = $videoLogic->getCategoryVideoList(VideoCodeEnum::VIDEO_CLASS_CJ);
            S('Cache:Sub:Index:Index:Video:Cj', $info['video_changjian'], random_int(900,1080));
        }
        //空间魔法
        $info['video_kongjian'] = S('Cache:Sub:Index:Index:Video:Kj' );
        if(empty($info['video_kongjian'])){
            $info['video_kongjian'] = $videoLogic->getCategoryVideoList(VideoCodeEnum::VIDEO_CLASS_KJ);
            S('Cache:Sub:Index:Index:Video:Kj', $info['video_kongjian'], random_int(900,1080));
        }
        //避坑指南
        $info['video_bikeng'] = S('Cache:Sub:Index:Index:Video:Bk' );
        if(empty($info['video_bikeng'])){
            $info['video_bikeng'] = $videoLogic->getCategoryVideoList(VideoCodeEnum::VIDEO_CLASS_MN);
            S('Cache:Sub:Index:Index:Video:Bk' , $info['video_bikeng'], random_int(900,1080));
        }
        //软装搭配
        $info['video_ruanzhuang'] = S('Cache:Sub:Index:Index:Video:Rz' );
        if(empty($info['video_ruanzhuang'])){
            $info['video_ruanzhuang'] = $videoLogic->getCategoryVideoList(VideoCodeEnum::VIDEO_CLASS_RZ);
            S('Cache:Sub:Index:Index:Video:Rz', $info['video_ruanzhuang'], random_int(900,1080));
        }
        /*************装修学堂end************/

        

        //获取轮播,不限制数量
        $info["lunbo"] = S('Cache:Sub:Index:lunbo:v3:'.$cityInfo["bm"]);
        if(!$info["lunbo"]){

            //1.第一张是全站理轮播
            //2.后面5张分站轮播
            //3.最后一张是全站
            //4.如果分站不够全站补

            //获取分站轮播图片
            $subLunbo = D("Advbanner")->getAdvBannerList($cityInfo["id"]);
            //获取全站轮播图片 上限5张
            $mainLunbo = D("Advbanner")->getAdvBannerList('0',5);

            $info["lunbo"][] = $mainLunbo[0];
            unset($mainLunbo[0]);
            foreach ($subLunbo as $key => $value) {
                $info["lunbo"][] = $value;
            }

            foreach ($mainLunbo as $key => $value) {
                $info["lunbo"][] = $value;
            }

            //每三小时统计一次轮播展示
            $isUpdateLunboLog = S('Cache:Sub:Index:lunboLog:'.$cityInfo["bm"]);
            if(!$isUpdateLunboLog){
                foreach ($info["lunbo"] as $k => $v) {
                    if(!empty($v['company_id'])){
                        $this->advBannerShowLog($v['id'],$v['company_id'],$v['city_id']);
                    }
                }
                S('Cache:Sub:Index:lunboLog:'.$cityInfo["bm"],time(),10800);
            }
            //装修公司如果不填写链接，默认公司主页
            foreach ($info["lunbo"] as $key => $value) {
                if (empty($value['url']) && !empty($value['company_id'])) {
                    $value['url'] = $this->SCHEME_HOST['scheme_full'].$value["bm"].".".C('QZ_YUMING')."/company_home/".$value["company_id"]."/";
                }
                $info["lunbo"][$key]['url'] = $value['url'];
            }
            S('Cache:Sub:Index:lunbo:v3:'.$cityInfo["bm"],$info["lunbo"],random_int(900,1080));
        }

        

        //获取通栏A
        $info["bigbanner_a"] = S('Cache:Sub:Index:bigbanner_a:'.$cityInfo["bm"]);
        if(!$info["bigbanner_a"]){
            $info["bigbanner_a"] = D("Advbanner")->getAdvList("home_bigbanner_a",$cityInfo["id"],3);
            S('Cache:Sub:Index:bigbanner_a:'.$cityInfo["bm"],$info["bigbanner_a"],random_int(900,1080));
        }

        //设置默认值
        if(empty($info["bigbanner_a"])){
            $info["bigbanner_a"][0] = [
                'img_url'=>'bigadv/20191203/5de5cc320c671-b.jpg',
                'url'=>'/zhaobiao/'
            ];
        }


        //获取通栏B
        $info["bigbanner_b"] = S('Cache:Sub:Index:bigbanner_b:'.$cityInfo["bm"]);
        if(!$info["bigbanner_b"]){
            $info["bigbanner_b"] = D("Advbanner")->getAdvList("home_bigbanner_b",$cityInfo["id"],3);
            S('Cache:Sub:Index:bigbanner_b:'.$cityInfo["bm"], $info["bigbanner_b"],random_int(900,1080));
        }

        //设置默认值
        if(empty($info["bigbanner_b"])){
            $info["bigbanner_b"][0] = [
                'img_url'=>'bigadv/20191203/5de5cc320c671-b.jpg',
                'url'=>'/zhaobiao/'
            ];
        }
        
        //获取通栏C
        $info["bigbanner_c"] = S('Cache:Sub:Index:bigbanner_c:'.$cityInfo["bm"]);
        if(!$info["bigbanner_c"]){
            $info["bigbanner_c"] = D("Advbanner")->getAdvList("home_bigbanner_c",$cityInfo["id"],3);
            S('Cache:Sub:Index:bigbanner_c:'.$cityInfo["bm"], $info["bigbanner_c"],random_int(900,1080));
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
        
        //文章分类和文章内容
        $info["cateGory"] = S('Cache:Sub:Index:cateGory:v2:'.$cityInfo["bm"]);
        if(!$info["cateGory"]){
            $cateGoryList = D('Infotype')->getTypes(2);
            foreach ($cateGoryList as $key => $value) {
                $content = D('Littlearticle')->getArticleListNew($value['id'], $cityInfo['id'], 0, 3, '', 'addtime desc');
                if (empty($content)) {
                    unset($cateGoryList[$key]);
                    continue;
                }

                foreach ($content as $k=>$v){
                    $cateGoryList[$key]['content'][$k]['id']  = $v["id"];
                    $cateGoryList[$key]['content'][$k]['title']  = $v["title"];
                    $cateGoryList[$key]['content'][$k]['addtime']  = $v["addtime"];

                    $desc = trim($v['description']);
                    if (empty($desc)){
                        $cateGoryList[$key]['content'][$k]['description'] = mbstr(strip_tags(htmlspecialchars_decode($v['content'])), 0, 45, 'utf-8',false);
                    } else {
                        $cateGoryList[$key]['content'][$k]['description'] = $desc;
                    }
                }
                switch ($value['shortname'])
                {
                    case "bendi":
                        $cateGoryList[$key]['px'] = 1;
                        break;
                    case "xuetang":
                        $cateGoryList[$key]['px'] = 2;
                        break;
                    case "gongsi":
                        $cateGoryList[$key]['px'] = 3;
                        break;
                    case "daogou":
                        $cateGoryList[$key]['px'] = 4;
                        break;
                    case "baojia":
                        $cateGoryList[$key]['px'] = 5;
                        break;
                    case "wenwen":
                        $cateGoryList[$key]['px'] = 6;
                        break;
                    case "jingyan":
                        $cateGoryList[$key]['px'] = 7;
                    break;
                    case "zxsj":
                        $cateGoryList[$key]['px'] = 8;
                        break;
                    default:
                        break;
                }
            }

            if (empty($cateGoryList)) {
                //重新排序
                $edition = array();
                foreach ($cateGoryList as $key => $value) {
                    $edition[] = $value["px"];
                }
                array_multisort($edition, SORT_ASC,$cateGoryList);
            }

            $info["cateGory"] = $cateGoryList;
            S('Cache:Sub:Index:cateGory:v2:'.$cityInfo["bm"],$info["cateGory"],random_int(900,1080));
        }
        
        $total_num = releaseCount('fbzrs');
        $bjNum['total_num'] = str_split($total_num);
        //剩余名额
        $bjNum['surplus_num'] = releaseCount('fbsyrs');

        //获取友情链接
        $friendLink = S('Cache:Sub:Index:friendLink:v2:'.$cityInfo["bm"]);
        if(!$friendLink){
            $friendLink["link"] = D("Friendlink")->getFriendLinkList($cityInfo["id"],1);
            //获取热门城市
            $result =D("Friendlink")->getFriendLinkList($cityInfo["id"],2);
            foreach ($result as $key => $value) {
                $hotCity[] = $value;
            }
            $friendLink["hotCity"] = $hotCity;

            //获取附近城市
            $result =D("Friendlink")->getFriendLinkList($cityInfo["id"],4);
            foreach ($result as $key => $value) {
                $recentCity[] = $value;
            }
            $friendLink["recentCity"] = $recentCity;

            //获取区域装修公司
            $result =D("Friendlink")->getFriendLinkList($cityInfo["id"],5);
            foreach ($result as $key => $value) {
                $areaCompany[] = $value;
            }
            $friendLink["areaCompany"] = $areaCompany;

            //获取地域装修服务
            $logic = new SubthematicLogicModel();
            $friendLink["zhuanti"] = $logic->getIndexZhuantiList($cityInfo["id"],50);

            // 获取最新美图
            $thematicLogic = new ThematicWordsLogicModel();
            $friendLink["newestMeitu"] = $thematicLogic->getNewTypeWords(2, 30);

            //获取最热美图
            $friendLink['hottestMeitu'] = $thematicLogic->getTypeHotList(2, 30);


            S('Cache:Sub:Index:friendLink:v2:'.$cityInfo["bm"],$friendLink,900);
        }

        //首次进入弹窗
        $alert_order = cookie('sub_first_order:'.date('Y-m-d'));
        if(empty($alert_order)){
            cookie('sub_first_order:'.date('Y-m-d'),1,86400);
            $this->assign('alert_order',1);
        }

        // $this->assign("videos", $videos);
        // $this->assign("vRecommend", $vRecommend);
        // $this->assign("tabIndex",0);
        $this->assign('bjNum',$bjNum);
        $this->assign('keys',$keys);
        $this->assign('cityInfo',$cityInfo);
        $this->assign("keys",$keys);
        $this->assign("info",$info);
         $this->assign("friendLink",$friendLink);
        $this->display("index");
    }

    /**
     * 增加轮播显示日志
     * @param  [type] $bid [description]
     * @param  [type] $cid [description]
     * @return [type]      [description]
     */
    private function advBannerShowLog($bid,$cid,$cityid){
        $isCount = array(
            'bid' => $bid,
            'cid' => $cid,
            'dates' => date('Y').date('m').date('d')
        );
        $isCount = M("adv_banner_showlog")->field('id')->where($isCount)->find();
        if(empty($isCount)){
            //插入轮播日志
            $advLog = array(
                'bid' => $bid,
                'cid' => $cid,
                'city_id' => $cityid,
                'year' => date('Y'),
                'month' => date('m'),
                'days' => date('d'),
                'dates' => date('Y').date('m').date('d')
            );
            M("adv_banner_showlog")->add($advLog);
        }
    }

    /**
     * 获取验证码
     * @return [type] [description]
     */
    public function verify(){
        getVerify("",4,120,35);
    }

    /**
     * 装修公司获取验证码
     * @return [type] [description]
     */
    public function verifyzx(){
        getVerify("",4,150,40,15,false,false,true,false,'');

    }

    public function company(){
        $p = I("get.p");
        $url = $this->SCHEME_HOST['scheme_full'].C("QZ_YUMINGWWW")."/company/";
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
            if(count($info)>0){
                header( "HTTP/1.1 301 Moved Permanently");
                header("Location:". $this->SCHEME_HOST['scheme_full']. $info["bm"].".".C("QZ_YUMING")."/zixun_info/".$id.".shtml");
                die();
            }
        }
        $this->_error();
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
            $url = $this->SCHEME_HOST['scheme_full'].$bm.".".C("QZ_YUMING")."/company_case/".$id;
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
            header( "Location:".$this->SCHEME_HOST['scheme_full'].$bm.".".C("QZ_YUMING")."/article_info/".$id.".html");
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
            header( "Location:".$this->SCHEME_HOST['scheme_full'].$bm.".".C("QZ_YUMING")."/caseinfo/".$id.".shtml");
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
            header( "Location:".$this->SCHEME_HOST['scheme_full'].$bm.".".C("QZ_YUMING")."/company_home/".$id);
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
            header( "Location:".$this->SCHEME_HOST['scheme_full'].$bm.".".C("QZ_YUMING")."/blog/".$id);
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
     * 老版设计师案例列表
     * @return [type] [description]
     */
    public function works(){
        $id = I("get.id");
        if(!empty($id)){
            $info = D("User")->getSingleUserInfoById($id);
            if(count($info)>0){
                header( "HTTP/1.1 301 Moved Permanently");
                header("Location:".$this->SCHEME_HOST['scheme_full'].$info["bm"].".".C("QZ_YUMING")."/blog/".$id);
                die();
            }
        }
        $this->_error();
    }

    /**
     * 附近招标模版
     * @return [type] [description]
     */
    public function fujin(){
        //安全验证码
        $safe = getSafeCode();
        $this->assign("safecode",$safe["safecode"]);
        $this->assign("safekey",$safe["safekey"]);
        $this->assign("ssid",$safe["ssid"]);

        $tmp = $this->fetch("fujin");
        $this->ajaxReturn(array("data"=>$tmp,"info"=>"","status"=>1));
    }

    /**
     * 老版设计师列表页
     * @return [type] [description]
     */
    public function article(){
        $id = I("get.id");
        if(!empty($id)){
            $info = D("User")->getSingleUserInfoById($id);
            if(count($info)>0){
                header( "HTTP/1.1 301 Moved Permanently");
                header("Location:".$this->SCHEME_HOST['scheme_full'].$info["bm"].".".C("QZ_YUMING")."/blog/".$id."?extra=2");
                die();
            }
        }
        $this->_error();
    }

    public function goodcase(){
        $cityInfo = $this->cityInfo;
        header( "HTTP/1.1 301 Moved Permanently");
        header("Location:".$this->SCHEME_HOST['scheme_full'].$cityInfo["bm"].".".C("QZ_YUMING")."/xgt/");
        die();
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

    public function getBJData(){
        R("Home/Zxbj/getBJData");
        die();
    }

    public function getBJResult(){
        R("Common/Zbfb/getBJResult");
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

    //反馈信息路由
    public function feedback(){
        R("Common/Zbfb/feedback");
        die();
    }

    public function getZbPrice(){
        R("Common/Zbfb/getZbPrice");
        die();
    }

    public function fb_order(){
        R("Common/Zbfb/fb_order");
        die();
    }
    //装修小贴士路由
    public function guide(){
        R("Common/Zbfb/guide");
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

    /**
     * 短信发送
     *
     * @return [type] [description]
     */
    public function sendsmstx()
    {
        if (IS_POST) {
            $code = strtolower($_POST['code']);
            if (check_verify($code)) {
                //如果是查看装修公司联系方式的短信发送
                if (!empty($_POST['company_id']) && !empty($_POST['tel'])) {
                    $ip = get_client_ip();

                    //是否在查看黑名单当中
                    $logic = new \Common\Model\Logic\CompanyContactViewsLogicModel();
                    if ($logic->hasBlack($_POST['tel'], $ip)) {
                        $this->ajaxReturn(['info' => '查看失败，当前手机/IP已被封禁，请联系客服后再次尝试', 'status' => 2]);
                    }

                    //是否查看次数超过了3次
                    $count = $logic->getCount($_POST['tel']);
                    if ($count >= 2) {
                        $logic->saveBlack(['tel' => $_POST['tel'], 'ip' => $ip]);
                        $this->ajaxReturn(['info' => '查看失败，当前手机/IP已被封禁，请联系客服后再次尝试', 'status' => 2]);
                    }

                    $logData['cid'] = $_SESSION['cityId'];
                    $logData['company_id'] = $_POST['company_id'];
                    $logData['ip'] = $ip;
                    $logData['tel'] = $_POST['tel'];
                    $logic->saveLog($logData);
                }

                R('Common/Sms/sendsms');
            }
            $this->ajaxReturn(['info' => '图形验证码不正确！', 'status' => 9]);
        }
        die();
    }

    //验证验证码是否正确
    public function verifysmscode()
    {
        if (!empty($_POST['company_id']) && !empty($_POST['tel'])) {
            $userModel = new \Common\Model\UserModel();
            $company = $userModel->getUserInfoById($_POST['company_id'], $_SESSION['cityId']);
            $user['qq'] = ($company[0]['on'] == 2 && $company[0]['fake'] == 0) ? $company[0]['qq'] : OP('QZ_CONTACT_QQ1');
            $user['qq1'] = ($company[0]['on'] == 2 && $company[0]['fake'] == 0) ? $company[0]['qq1'] : OP('QZ_CONTACT_QQ2');
            $user['dz'] = $company[0]['dz'];
            $user['cal'] = ($company[0]['on'] == 2 && $company[0]['fake'] == 0) ? $company[0]['cal'] : '';
            $user['cals'] = ($company[0]['on'] == 2 && $company[0]['fake'] == 0) ? $company[0]['cals'] : OP('QZ_CONTACT_TEL400');
            $user['tel'] = ($company[0]['on'] == 2 && $company[0]['fake'] == 0) ? $company[0]['tel'] : OP('QZ_CONTACT_TEL400');
            $user['nickname'] = empty($company[0]['nickname']) == true ? '家装咨询顾问' : $company[0]['nickname'];
            $user['nickname1'] = empty($company[0]['nickname1']) == true ? '公装咨询顾问' : $company[0]['nickname1'];
            $_POST['return_data'] = $user;
        }
        R('Common/Sms/verifysmscode');
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

    public function wenda(){
        R("Home/Wenda/quickask");
        die();
    }

    /**
     * 采集数据
     * @return [type] [description]
     */
    public function hm()
    {
        die();
        $state = 0;
        $engine = "";
        if (I("get.ref") !== "") {
            //判断是否是外链
            $result = preg_match('/qizuang.com/',I("get.ref"));
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
        //导入扩展文件
        import('Library.Org.Util.App');
        $ip = \App::get_client_ip();
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
}
