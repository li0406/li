<?php

namespace Home\Controller;

use Common\Model\Logic\CompanyLogicModel;
use Common\Model\Logic\TuLogicModel;
use Common\Model\Logic\CasesLogicModel;
use Common\Model\Logic\SearchLogicModel;
use Common\Model\Logic\TuCategoryLogicModel;
use Common\Model\Service\ElasticSearchServiceModel;
use Home\Common\Controller\HomeBaseController;

use Common\Enums\XgtCodeEnum;
use Common\Enums\SearchCodeEnum;

class SearchController extends HomeBaseController {

    // 商业装修场景标识
    const SCENE_SYZX = "syzx";

    public function index(){
        $keyword = I("get.keyword");
        $page = I("get.p");
        $searchLogic = new SearchLogicModel();
        $result = $searchLogic->getZxkxList($keyword,$page,10);

        $meta = [
            "title" => "搜索结果页 - 齐装网"
        ];
        if (!empty($keyword)) {
            $meta["title"] = $keyword." - ".$meta["title"];
        }
        $this->assign("meta",$meta);
        $this->assign("tab",0);
        $this->assign("keyword",$keyword);
        $this->assign("list",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->assign("total",$result["total"]);
        $this->display("zxkx");
    }

    // 获取城市
    private function getCityInfo(){
        if (!empty($this->cityInfo["id"])) {
            $cityInfo = $this->cityInfo;
        } else if (cookie("QZ_CITY") != "") {
            $cityInfo = json_decode(cookie("QZ_CITY"), true);
        } else {
            $cityInfo = [];
        }

        $this->assign("cityInfo", $cityInfo);
        return $cityInfo;
    }

    /**
     * 搜索-全部
     */
    public function colligate(){
        $module = I("get.module");
        $cityInfo = $this->getCityInfo();

        $data = array(
            "module" => $module,
            "keyword" => trim(I("get.keyword")),
            "scene_word" => SearchCodeEnum::getSceneWord($module),
            "scene_word_show" => SearchCodeEnum::getSceneWordShow($module),
        );

        $searchLogic = new SearchLogicModel();

        // 美图搜索
        $cache_key = sprintf("Cache:Search:%s:Meitu:%s", $data["module"], md5($data["keyword"]));
        $data["meitu"] = S($cache_key);
        if (empty($data["meitu"])) {
            $tuInput = [
                "search_word" => $data["keyword"],
                "scene_word" => $data["scene_word"],
                "type" => TuLogicModel::TU_TYPE_HOME
            ];

            // 商业装修不匹配场景词
            if ($data["module"] == static::SCENE_SYZX) {
                $tuInput["type"] = TuLogicModel::TU_TYPE_PUB;
                unset($tuInput["scene_word"]);
            }

            $meitudata = $searchLogic->getTuList($tuInput, 1, 8);
            $data["meitu"] = $meitudata["list"];
            S($cache_key, $data["meitu"], get_cache_time());
        }

        // 案例图搜索
        $cache_key = sprintf("Cache:Search:%s:Cases:%s:%s", $data["module"], $cityInfo["id"], md5($data["keyword"]));
        $data["cases"] = S($cache_key);
        if (empty($data["cases"])) {
            $caseInput = [
                "search_word" => $data["keyword"],
                "scene_word" => $data["scene_word"],
                "cs" => $cityInfo["id"] ? : ""
            ];

            // 商业装修不匹配场景词
            if ($data["module"] == static::SCENE_SYZX) {
                $caseInput["type"] = 2;
                unset($caseInput["scene_word"]);
            }

            $casedata = $searchLogic->getCasesList($caseInput, 1, 8);
            $data["cases"] = $casedata["list"];
            S($cache_key, $data["cases"], get_cache_time());
        }

        // 装修攻略搜索
        $cache_key = sprintf("Cache:Search:%s:Gonglue:%s", $data["module"], md5($data["keyword"]));
        $data["gonglue"] = S($cache_key);
        if (empty($data["gonglue"])) {
            if ($data["module"] == static::SCENE_SYZX) {
                $gongluedata = $searchLogic->getSenceArticleList($data["scene_word"], $data["keyword"], 1, 10);
            } else {
                $gongluedata = $searchLogic->getSenceContentList($data["scene_word"], $data["keyword"], 1, 10);
            }

            $data["gonglue"] = $gongluedata["list"];
            S($cache_key, $data["gonglue"], get_cache_time());   
        }

        // 判断综合搜索是否全为空
        $allempty = true;
        $keyList = ["meitu", "cases", "gonglue"];
        foreach ($keyList as $key => $value) {
            if (!empty($data[$value])) {
                $allempty = false;
                break;
            }
        }

        $this->assign("data", $data);
        $this->assign("allempty", $allempty);
        $this->assign("module", $data["module"]);
        $this->assign("keyword", $data["keyword"]);
        $this->assign("navidx", "all");
        $this->display();
    }


    /**
     * 美图搜索
     */
    public function tu(){
        $module = I("param.module");
        $search_word = I("param.keyword");
        $category = I("get.category");
        $pageIndex = I("get.p", 1);
        $pageCount = 24;

        $cityInfo = $this->getCityInfo();

        // 获取对应的分类数据
        $tuLogic = new TuLogicModel();
        $categoryMap = $tuLogic->getSearchCategoryMap($category);

        $input = [
            "search_word" => trim($search_word),
            "scene_word" => SearchCodeEnum::getSceneWord($module),
            "cate_id" => $categoryMap["cate_id"],
            "cate_field" => $categoryMap["cate_field"],
            "type" => TuLogicModel::TU_TYPE_HOME
        ];

        // 商业装修不匹配场景词
        if ($module == static::SCENE_SYZX) {
            $input["type"] = TuLogicModel::TU_TYPE_PUB;
            unset($input["scene_word"]);
        }

        $searchLogic = new SearchLogicModel();
        $data = $searchLogic->getTuList($input, $pageIndex, $pageCount);

        // 获取家装分类
        $categoryList = S('Cache:getList-'.$input["type"].':categorylist'); //分类缓存
        if(!$categoryList){
            $tuCategoryLogic = new TuCategoryLogicModel();
            $categoryList = $tuCategoryLogic->getMeituLv2CategoryByType($input["type"]);
            S('Cache:getList-'.$input["type"].':categorylist', $categoryList, 60 * 60 * 12);
        }

        import("Library.Org.Page.LitePage");
        $config  = array("prev", "next");
        $litePage = new \LitePage($pageIndex, $pageCount, $data["page"]["total_number"], $config, null);
        $pageshow = $litePage->show();

        $this->assign("data", $data);
        $this->assign("pageshow", $pageshow);
        $this->assign("categoryList", $categoryList);
        $this->assign("module", $module);
        $this->assign("keyword", $search_word);
        $this->assign("input", $input);
        $this->assign("navidx", "tu");
        $this->display();
    }


    /**
     * 案例搜索
     */
    public function xgt(){
        $module = I("param.module");
        $search_word = I("param.keyword");
        $pageIndex = I("get.p");
        $pageCount = 24;

        $cityInfo = $this->getCityInfo();

        $input = [
            "cs" => $cityInfo["id"],
            "search_word" => trim($search_word),
            "scene_word" => SearchCodeEnum::getSceneWord($module),
            "type" => I("get.type"),
            "hx" => I("get.hx"),
            "lx" => I("get.lx"),
            "mj" => I("get.mj"),
            "fg" => I("get.fg"),
            "jg" => I("get.jg")
        ];

        // 商业装修不匹配场景词
        if ($module == static::SCENE_SYZX) {
            $input["type"] = 2;
            unset($input["scene_word"]);
        }

        // 处理默认类型
        if ( empty($input["type"]) ) {
            if ($module == static::SCENE_SYZX) {
                $input["type"] = CasesLogicModel::CLASSID_PUB;
            } else {
                $input["type"] = CasesLogicModel::CLASSID_HOME;
            }
        }

        $mianjiMap = XgtCodeEnum::getMjSearchMap($input["mj"]);
        $input["mianji_min"] = $mianjiMap["min_value"];
        $input["mianji_max"] = $mianjiMap["max_value"];

        // 获取筛选项
        $casesLogicModel = new CasesLogicModel();
        $xiaoguotuInfo = $casesLogicModel->getFilterInfo();
        $xiaoguotuInfo = $casesLogicModel->setFilterParam($xiaoguotuInfo, $input);

        // 商业装修不显示类型tabs
        if ($module == static::SCENE_SYZX) {
            // 商业装修只显示公装筛选项
            $xiaoguotuInfo["tabs"] = array_column($xiaoguotuInfo["tabs"], null, "id");
            unset($xiaoguotuInfo["tabs"][1], $xiaoguotuInfo["tabs"][3]);
            unset($xiaoguotuInfo["tabs"]);
        }

        // 搜索
        $searchLogic = new SearchLogicModel();
        $data = $searchLogic->getCasesList($input, $pageIndex, $pageCount);

        import("Library.Org.Page.LitePage");
        $config  = array("prev", "next");
        $litePage = new \LitePage($pageIndex, $pageCount, $data["page"]["total_number"], $config, null);
        $pageshow = $litePage->show();

        $this->assign("data", $data);
        $this->assign("pageshow", $pageshow);
        $this->assign("module", $module);
        $this->assign("input", $input);
        $this->assign("xiaoguotuInfo", $xiaoguotuInfo);
        $this->assign("baseuri", sprintf("/search/%s/xgt/?keyword=%s", $module, $input["search_word"]));
        $this->assign("keyword", $search_word);
        $this->assign("navidx", "xgt");
        $this->display();
    }

    /**
     * 攻略搜索
     */
    public function scenegonglue()
    {
        $module = I("param.module");
        $keyword = I("get.keyword");
        $scene_word = SearchCodeEnum::getSceneWord($module);

        $page = I("get.p");
        $locic = new SearchLogicModel();
        if ($module == static::SCENE_SYZX) {
            $result = $locic->getSenceArticleList($scene_word, $keyword, $page, 10);
        } else {
            $result = $locic->getSenceContentList($scene_word, $keyword, $page, 10);
        }

        $cityInfo = $this->getCityInfo();

        $this->assign("module",$module);
        $this->assign("keyword",$keyword);
        $this->assign("scene_word",$scene_word);
        $this->assign("list",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->assign("total",$result["total"]);
        $this->assign("navidx", "gonglue");
        $this->display("scenegonglue");
    }

    /**
     * 选材导购
     */
    public function xcdg()
    {
        $keyword = I("get.keyword");
        $page = I("get.p");
        $locic = new SearchLogicModel();
        $result = $locic->getContentSubList("xcdg",$keyword,$page,10);
        $meta = [
            "title" => "选材导购 - 装修快讯 - 搜索结果页 - 齐装网"
        ];
        if (!empty($keyword)) {
            $meta["title"] = $keyword." - ".$meta["title"];
        }
        $this->assign("meta",$meta);
        $this->assign("tab",5);
        $this->assign("keyword",$keyword);
        $this->assign("list",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->assign("total",$result["total"]);
        $this->display();
    }

    /**
     * 攻略
     */
    public function gonglue()
    {
        $keyword = I("get.keyword");
        $page = I("get.p");
        $locic = new SearchLogicModel();
        $result = $locic->getArticleList($keyword,$page,10);
        $meta = [
            "title" => "装修攻略 - 搜索结果页 - 齐装网"
        ];
        if (!empty($keyword)) {
            $meta["title"] = $keyword." - ".$meta["title"];
        }
        $this->assign("meta",$meta);
        $this->assign("tab",1);
        $this->assign("keyword",$keyword);
        $this->assign("list",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->assign("total",$result["total"]);
        $this->display();
    }

    /**
     * 百科
     */
    public function baike()
    {
        $keyword = I("get.keyword");
        $page = I("get.p");
        $locic = new SearchLogicModel();
        $result = $locic->getBaikeList($keyword,$page,10);
        $meta = [
            "title" => "装修百科 - 搜索结果页 - 齐装网"
        ];
        if (!empty($keyword)) {
            $meta["title"] = $keyword." - ".$meta["title"];
        }
        $this->assign("meta",$meta);
        $this->assign("tab",2);
        $this->assign("keyword",$keyword);
        $this->assign("list",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->assign("total",$result["total"]);
        $this->display();
    }

    /**
     * 问答
     */
    public function wenda()
    {
        $keyword = I("get.keyword");
        $page = I("get.p");
        $locic = new SearchLogicModel();
        $result = $locic->getWendaList($keyword,$page,10);
        $meta = [
            "title" => "装修问答 - 搜索结果页 - 齐装网"
        ];
        if (!empty($keyword)) {
            $meta["title"] = $keyword." - ".$meta["title"];
        }
        $this->assign("meta",$meta);
        $this->assign("tab",3);
        $this->assign("keyword",$keyword);
        $this->assign("list",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->assign("total",$result["total"]);
        $this->display();
    }

    /**
     * 流程
     */
    public function lc()
    {
        $keyword = I("get.keyword");
        $page = I("get.p");
        $locic = new SearchLogicModel();
        $result = $locic->getContentSubList("lc",$keyword,$page,10);
        $meta = [
            "title" => "装修流程 - 装修快讯 - 搜索结果页 - 齐装网"
        ];
        if (!empty($keyword)) {
            $meta["title"] = $keyword." - ".$meta["title"];
        }
        $this->assign("meta",$meta);
        $this->assign("tab",1);
        $this->assign("keyword",$keyword);
        $this->assign("list",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->assign("total",$result["total"]);
        $this->display();
    }

    /**
     * 指南
     */
    public function zhinan()
    {
        $keyword = I("get.keyword");
        $page = I("get.p");
        $locic = new SearchLogicModel();
        $result = $locic->getContentSubList("zhinan",$keyword,$page,10);
        $meta = [
            "title" => "装修指南 - 装修快讯 - 搜索结果页 - 齐装网"
        ];
        if (!empty($keyword)) {
            $meta["title"] = $keyword." - ".$meta["title"];
        }
        $this->assign("meta",$meta);
        $this->assign("tab",4);
        $this->assign("keyword",$keyword);
        $this->assign("list",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->assign("total",$result["total"]);
        $this->display();
    }

    /**
     * 装修快讯
     */
    public function zxkx()
    {
        $keyword = I("get.keyword");
        $page = I("get.p");
        $locic = new SearchLogicModel();
        $result = $locic->getZxkxList($keyword,$page,10);

        $meta = [
            "title" => "装修流程 - 装修快讯 - 搜索结果页 - 齐装网"
        ];
        if (!empty($keyword)) {
           $meta["title"] = $keyword." - ".$meta["title"];
        }

        $this->assign("meta",$meta);
        $this->assign("tab",0);
        $this->assign("keyword",$keyword);
        $this->assign("list",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->assign("total",$result["total"]);
        $this->display();
    }

    /**
     * 装修公司
     */
    public function company()
    {
        $keyword = I("get.keyword");
        $page = I("get.p");
        $region = I("get.region");
        $gm = I("get.gm");
        $bz = I("get.bz");

        if (!empty($this->cityInfo["id"])) {
            $cityInfo = $this->cityInfo;
        } elseif(cookie("QZ_CITY") !== "") {
            $cityInfo = json_decode(cookie("QZ_CITY"),true);
        } else {
            $cityInfo = [];
        }
       
        if(empty($cityInfo)){
            $this->_error();
        }

        $info["cityInfo"] = $cityInfo;
        if (count($cityInfo) > 0) {
            //获取城市的区县信息
            $info["area"] = S("Cache:Search:area:".$cityInfo["bm"]);
            if (!$info["area"]) {
                $info["area"] = D("Common/Quyu")->getAreaByFatherId($cityInfo["id"]);
                S("Cache:Search:area:".$cityInfo["bm"],$info["area"],random_int(900,1800));
            }

            //获取活跃榜
            //[地域]公司活跃榜
            $activitycompanylist = S('Cache:SubCompany:NotVipActivityCompany2'.$cityInfo['bm']);
            $startime = strtotime(date("Y-m-d",time()));
            $middletime = strtotime(date("Y-m-d",time()).' 12:00:00');
            $endtime = strtotime(date("Y-m-d",time()).' 12:59:59');
            $companyloigic = new CompanyLogicModel();
            if(!$activitycompanylist['time']){
                $savecache['list'] = $companyloigic->getCompanyActivityRank($cityInfo['id'],10);
                $savecache['time'] = time();
                $activitycompanylist = $savecache;
                S('Cache:SubCompany:NotVipActivityCompany2'.$cityInfo['bm'],$savecache,43200);
            }else{
                if($activitycompanylist['time'] < $startime){
                    $savecache['list'] = $companyloigic->getCompanyActivityRank($this->cityInfo['id'],10);
                    $savecache['time'] = time();
                    $activitycompanylist = $savecache;
                    S('Cache:SubCompany:NotVipActivityCompany2'.$cityInfo['bm'],$savecache,43200);
                }elseif($activitycompanylist['time'] > $startime && $activitycompanylist['time'] < $middletime){
                    if(time() >= $middletime){
                        $savecache['list'] = $companyloigic->getCompanyActivityRank($cityInfo['id'],10);
                        $savecache['time'] = time();
                        $activitycompanylist = $savecache;
                        S('Cache:SubCompany:NotVipActivityCompany2'.$cityInfo['bm'],$savecache,43200);
                    }
                }elseif($activitycompanylist['time'] >= $middletime && $activitycompanylist['time'] <= $endtime){
                    if(time() > $endtime){
                        $savecache['list'] = $companyloigic->getCompanyActivityRank($cityInfo['id'],10);
                        $savecache['time'] = time();
                        $activitycompanylist = $savecache;
                        S('Cache:SubCompany:NotVipActivityCompany2'.$cityInfo['bm'],$savecache,43200);
                    }
                }
            }

            $info["activitycompany"] = $activitycompanylist['list'];

            //推荐文章（从缓存中获取）
            $info['recomArticle'] = S('Cache:Sub:recomArticle:city:v1:'.$cityInfo["id"]);
            if (empty($info['recomArticle'])){
                $result = \Common\Model\Logic\LittlearticleLogicModel::getRecomArticleList($cityInfo["id"], 1, 8);
                if (!empty($result)){
                    foreach ($result as $item) {
                        $info['recomArticle'][] = [
                            "title" => $item["title"],
                            "id" => $item["id"]
                        ];
                    }
                    S('Cache:Sub:recomArticle:city:v1:'.$cityInfo["id"], $info['recomArticle'],900);
                }
            }
        }

        $guimo = D("Common/Guimo")->gethGm();
        $info["guimo"] = $guimo;

        //获取服务保障列表
        $baozhang = D("Common/Leixing")->getbg();
        $info["baozhang"] = $baozhang;

        //tdk
        $info["title"] = "装修公司 - 搜索结果页 - 齐装网";

        if (!empty($keyword)) {
            $info["title"]  = $keyword ." - ". $info["title"];
        }

        $logic = new SearchLogicModel();
        $info["list"] = $logic->getCompanyList($region,$gm,$bz,$cityInfo["id"],$keyword,$page,12);
        $this->assign("total",$info["list"]["total"]);
        $this->assign("info",$info);
        $this->assign("keyword",$keyword);
        $this->display();
    }
}
