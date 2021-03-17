<?php
/**
 * 移动版分站首页
 */
namespace Mobile\Controller;
use Common\Model\Logic\SubIndexLogicModel;
use Common\Model\Logic\UserLogicModel;
use Mobile\Common\Controller\MobileBaseController;
class SubIndexController extends MobileBaseController
{

    public function index(){
        // 最新改版： 1059 移动端首页改版

        $subIndexLogic = new SubIndexLogicModel();


        $SCHEME_HOST = $this->SCHEME_HOST;
        //判断是否有传参数
        if(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY)){
            if (substr(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), -1) != '/') {
                header("HTTP/1.1 301 Moved Permanently");
                header("Location: " . $SCHEME_HOST['scheme_full'].$SCHEME_HOST['host'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) . "/?" . parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY));
                exit();
            }
        }else{
            //如果url末尾没有 '/' 就跳转加
            if (substr($_SERVER['REQUEST_URI'], -1) != '/') {
                header("HTTP/1.1 301 Moved Permanently");
                header("Location: " . $SCHEME_HOST['scheme_full'].$SCHEME_HOST['host'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) . "/");
                exit();
            }
        }

        //canonical 对应跳转
        $cityInfo = $this->cityInfo;
        $SCHEME_HOST = $this->SCHEME_HOST;
        $bm = $cityInfo['bm'];
        if(!empty($bm)){
            $fenzhan = $SCHEME_HOST['scheme_full'].$bm.'.'.$SCHEME_HOST['domain'];
        }

        //获取轮播
        $info["lunbo"] = S("Cache:m:lunbo:".$cityInfo["bm"].":".md5($SCHEME_HOST['domain']));
        if (!$info["lunbo"]) {
            $info["lunbo"] = $this->getNewLunboAdv("home_advbanner",$cityInfo["id"]);
            S("Cache:m:lunbo:".$cityInfo["bm"].":".md5($SCHEME_HOST['domain']),$info["lunbo"],900);
        }

        // 广告位管理
        $info["adv_space"] = S("MOBILE:SUBINDEX:" . $cityInfo["bm"] . ":INDEX:ADVSPACE");
        if (!$info["adv_space"]) {
            $info["adv_space"] = $subIndexLogic->advSpaceListForIndex($cityInfo["id"]);
            S("MOBILE:SUBINDEX:" . $cityInfo["bm"] . ":INDEX:ADVSPACE", $info["adv_space"], 300);
        }


        // 开工大吉
        $info["workstart"] = S("MOBILE:SUBINDEX:" . $cityInfo["bm"] . ":INDEX:WORKSTART");
        if (!$info["workstart"]) {
            $info["workstart"] = $subIndexLogic->getPassTheaAuditOrder($cityInfo["id"]);
            S("MOBILE:SUBINDEX:" . $cityInfo["bm"] . ":INDEX:WORKSTART", $info["workstart"], 300);
        }


        // 装修案例
        $info["homecases"] = S("MOBILE:SUBINDEX:" . $cityInfo["bm"] . ":INDEX:HOMECASES");
        if (!$info["homecases"]) {
            $info["homecases"] = $subIndexLogic->getHomeCases($cityInfo["id"]);
            S("MOBILE:SUBINDEX:" . $cityInfo["bm"] . ":INDEX:HOMECASES", $info["homecases"], 300);
        }

        // 装修效果图
        $info["hometu"] = S("MOBILE:SUBINDEX:" . $cityInfo["bm"] . ":INDEX:HOMETU");
        if (!$info["hometu"]) {
            $info["hometu"] = $subIndexLogic->getHomeTuCategoryInfo();
            S("MOBILE:SUBINDEX:" . $cityInfo["bm"] . ":INDEX:HOMETU", $info["hometu"], 300);
        }

        // 装修资讯
        $info["homezixun"] = S("MOBILE:SUBINDEX:" . $cityInfo["bm"] . ":INDEX:HOMEZIXUN");
        if (!$info["homezixun"]) {
            $info["homezixun"] = $subIndexLogic->getHomeZixun($cityInfo["id"]);
            S("MOBILE:SUBINDEX:" . $cityInfo["bm"] . ":INDEX:HOMEZIXUN", $info["homezixun"], 300);
        }

        // 装修学堂
        $info["homexuetang"] = S("MOBILE:SUBINDEX:" . $cityInfo["bm"] . ":INDEX:HOMEXUETANG");
        if (!$info["homexuetang"]) {
            $info["homexuetang"] = $subIndexLogic->getHomeXueTang();
            S("MOBILE:SUBINDEX:" . $cityInfo["bm"] . ":INDEX:HOMEXUETANG", $info["homexuetang"], 300);
        }

        // 装修公司
        $info["company"] = S("MOBILE:SUBINDEX:" . $cityInfo["bm"] . ":INDEX:HOMECOMPANY");
        if (!$info["company"]) {
            $info["company"] = $subIndexLogic->getHomeCompanyList($cityInfo["id"]);
            S("MOBILE:SUBINDEX:" . $cityInfo["bm"] . ":INDEX:HOMECOMPANY", $info["company"], 300);
        }

        // 查询是否有地图数据
//        $info["mapcount"] = S("MOBILE:SUBINDEX:" . $cityInfo["bm"] . ":INDEX:MAPCOUNT");
//        if (!$info["mapcount"]) {
//            $info["mapcount"] = $subIndexLogic->getDecorationMaoCount($cityInfo["id"]);
//            S("MOBILE:SUBINDEX:" . $cityInfo["bm"] . ":INDEX:homecompany", $info["mapcount"], 300);
//        }


        //关键字、描述、标题
        $basic["head"]["title"] = $cityInfo["name"] . "装修_".$cityInfo["name"]."装修网_".$cityInfo["name"]."装修公司-".$cityInfo["name"]."齐装网";
        $basic["head"]["keywords"] = $cityInfo["name"]."装修，".$cityInfo["name"]."装修网，".$cityInfo["name"]."装修公司";
        $basic["head"]["description"] = $cityInfo["name"]."齐装网致力于为广大业主提供透明、保障、省心的装修服务！汇集".$cityInfo["name"]."各大装饰公司，并有权威".$cityInfo["name"]."装修公司排名可供参考。";

        $config = [
            'cs' => $this->cityInfo['id'], //城市id
            'model' => 1, //模块 1.首页 2.装修公司 3.装修资讯
            'category' => '', //装修资讯子频道栏目
            'location' => 2, //位置 1.pc端 2.移动端
        ];
        $basic["head"] = getCommonManageTdk($basic["head"],$config);

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];

        $this->assign("bm", $cityInfo["bm"]);
        $this->assign("info",$info);
        $this->assign("basic",$basic);
        $this->assign("remainNumber", remainNumber('sheji', 2));
        $this->assign("fenzhan", $fenzhan);
        $this->assign('showlocation',1);//显示页面name为‘location’的meta标签
        $this->display("index_1059");
    }

    /**
     * 获取轮播列表
     * @param  string $city [description]
     * @return [type]       [description]
     */
    private function getLunboAdv($city = "",$bm){
        $result =  D("Common/Adv")->getIndexAdv($city);
        foreach ($result as $key => $value) {
            $sub["company_name"] = $value["comname"];
            $sub["company_id"] = $value["comid"];
            $sub["img_url"] = $value["img"];
            $host_full = $this->SCHEME_HOST['scheme_full'].$this->SCHEME_HOST['host'];
            if($value["href"] == "#"){
                $sub["url"] = $host_full."/".$value["bm"]."/company_home/".$value["comid"];
            }elseif('/zhaobiao/' == $value['href']){
                $sub['url'] = $host_full."/sheji/";
            }elseif(!empty($value['href'])){
                $sub['url'] = str_replace($this->SCHEME_HOST['scheme_full'].'www.'.$this->SCHEME_HOST['domain'].'/gonglue/',$host_full.'/gonglue/',$value['href']);
            }else{
                //如果bm为空，查询bm
                if(empty($value["bm"])){
                    $cityInfo = $this->cityInfo;
                    $sub["url"] = $host_full."/".$cityInfo["bm"]."/company_home/".$value["comid"];
                }else{
                    $sub["url"] = $host_full."/".$value["bm"]."/company_home/".$value["comid"];
                }
            }
            $sub["url"] = str_replace(C("QZ_YUMING"),$SCHEME_HOST['domain'],$sub["url"]); // 替换为当前访问域名
            $sub["title"] = $value["comname"];
            $adv[] = $sub;
        }
        return $adv;
    }

    /**
     * 获取最新订单
     * @param  [type] $limit [description]
     * @param  [type] $on    [description]
     * @param  [type] $cs    [description]
     * @return [type]        [description]
     */
    private function getOrders($limit,$on,$cs){
        $orders = D("Common/Orders")->getNewOrders($limit,$on,$cs);
        foreach ($orders as $key => $value) {
            $min   =  rand(1,10);
            $orders[$key]["time"] = $min."分钟之前";
            //业主名称处理,取第一个字符
            $orders[$key]["name"] = mb_substr($value["name"], 0,1,"utf-8");
        }
        return $orders;
    }

    /**
     * 获取装修公司LOGO
     * @return [type] [description]
     */
    private function getLogos($cityId,$limit){
        $logos = D("Common/Advs")->getMobileLogoList($cityId,$limit);
        if(count($logos) < $limit){
            //如果当月的更新不足，取当前站发布案例数最多的公司
            $offset = $limit - count($logos);
            $allLogos = D("Common/Advs")->getMobileLogoList($cityId,$offset,true);
            $logos = array_merge($logos,$allLogos);
        }
        return $logos;
    }

     /**
     * 获取最新装修案例
     * @return [type] [description]
     */
    private function getNewCasesList($cs="",$limit=3){
        $cases = D("Common/Cases")->getIndexNewsCases($limit,$cs);
        if(count($cases) > 0){
            return $cases;
        }
        return null;
    }

    /**
     * 获取最新的文章信息
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    private function getIndexArticles($limit){
        $article = D("WwwArticle")->getIndexArticles("",$limit);
        return $article;
    }

    /**
     * 获取新版轮播
     * @param  [type] $module  [模块名]
     * @param  [type] $city_id [城市编号]
     * @param  [type] $limit   [description]
     * @return [type]          [description]
     */
    private function getNewLunboAdv($module,$city_id,$limit = 10)
    {
        $SCHEME_HOST = $this->SCHEME_HOST;
        $list = D("Advbanner")->getAdvList($module,$city_id,$limit);
        foreach ($list as $key => $value) {
            //移动端图片链接优先获取移动端的，没有的情况下获取默认的
            if (!empty($value['img_url_mobile'])) {
                $list[$key]['img_url'] = $value['img_url_mobile'];
            }
           //装修公司默认如果没有链接,替换链接到公司主页
            if (empty($value['url_mobile']) && !empty($value['company_id'])) {
                $value['url_mobile'] = $SCHEME_HOST['scheme_full'].$SCHEME_HOST['host']."/".$value["bm"]."company_home/".$value["company_id"]."/";
            }
            $value['url_mobile'] = urlDomainReplace($value['url_mobile'], $SCHEME_HOST['domain']); // 替换为当前访问域名
            $list[$key]['url'] = $value['url_mobile'];
        }
        return $list;
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
            shuffle($articles);
            $articles = array_slice($articles, 0,$limit);
            foreach ($articles as $k => $val) {
               unset($articles[$k]["content"]);
               unset($articles[$k]["keywords"]);
               unset($articles[$k]["face"]);
               unset($articles[$k]["subtitle"]);
               unset($articles[$k]["imgs"]);
            }
            $category["child"][$key]["child"] = $articles;
        }
        return $category;
    }

    private function getMeituList($fengge = "",$location = array())
    {
        if (empty($fengge)) {
            return false;
        }
        //获取最新的美图
        $info = D("Meitu")->getTopNewMeiTuInfo($fengge);
        //获取分类列表
        $result = D("Meitu")->getTopMeiTuList($fengge,$location,$info["id"]);

        foreach ($result as $key => $value) {
            $list[$value["location"]] = $value;
        }

        return array("info"=>$info,"list"=>$list);
    }

    private function getZxInfo($count){
        $order = 'addtime desc';
        $data = D("Littlearticle")->getArticleList('', $this->cityInfo['id'], 0, $count, '', $order);
        $arr = [];
        foreach ($data as $k=>$v){
            $arr[$k]['id'] = $v['id'];
            if (strlen($v['title']) > 22) {
                $arr[$k]['title'] = mb_substr($v['title'], 0, 22, 'utf-8') . '...';
            } else {
                $arr[$k]['title'] = $v['title'];
            }
        }
        return $arr;
    }
}
