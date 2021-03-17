<?php

namespace Common\Model\Logic;

use Common\Model\AskModel;
use Common\Model\BaikeModel;
use Common\Model\Db\TuModel;
use Common\Model\ArticleModel;
use Common\Model\CasesModel;
use Common\Model\Service\ElasticSearchServiceModel;

/**
 *
 */
class SearchLogicModel
{
    private $pageMaxNumber = 10;
    private  $contentDefaultKeyWord = "设计";

    public function getZxkxList($keyword,$pageIndex,$pageCount)
    {
        $keyword = empty($keyword)?$this->contentDefaultKeyWord:$keyword;

        if ($pageIndex > 10) {
            $pageIndex = 10;
        }

        $service = new ElasticSearchServiceModel();
        $result = $service->getZxkxList($keyword,$pageIndex,$pageCount);
        $list = $result["list"];
        $page = $result["page"];
        $list = $this->contentResultProcessing($list);

        $total = empty($page["total_number"])?0:$page["total_number"];

        if (count($list) == 0) {
            $total =  0;
        }

        if ($page["total_number"] > 100) {
            $total = 100;
        }

        import("Library.Org.Page.LitePage");

        $config  = array("prev","next");
        $p = new \LitePage($pageIndex,$pageCount,$total,$config,null);
        $show    = $p->show();
        return array("list" => $list,"page" => $show,"total"=> $total);
    }

    public function getContentSubList($moudle,$keyword,$pageIndex,$pageCount)
    {
        $keyword = empty($keyword)?$this->contentDefaultKeyWord:$keyword;
        $service = new ElasticSearchServiceModel();
        $result = $service->getContentSubList($moudle,$keyword,$pageIndex,$pageCount);
        $list = $result["list"];
        $page = $result["page"];

        $list = $this->contentResultProcessing($list);

        $total = $page["total_number"];

        if ($page["total_number"] > 100) {
            $total = 100;
        }

        if (count($list) == 0) {
            $total = 0;
        }

        import("Library.Org.Page.LitePage");

        $config  = array("prev","next");
        $p = new \LitePage($pageIndex,$pageCount,$total,$config,null);
        $show    = $p->show();
        return array("list" => $list,"page" => $show,"total"=> $total);
    }

    public function getArticleList($keyword,$pageIndex,$pageCount)
    {
        $keyword = empty($keyword)?$this->contentDefaultKeyWord:$keyword;
        
        if ($pageIndex > 10) {
            $pageIndex = 10;
        }
        $service = new ElasticSearchServiceModel();
        $result = $service->getWwwArticleList($keyword,$pageIndex,$pageCount);

        $list = $result["list"];
        $page = $result["page"];
        $list = $this->contentResultProcessing($list);

        $total = $page["total_number"];

        if ($page["total_number"] > 100) {
            $total = 100;
        }

        if (count($list) == 0) {
            $total = 0;
        }

        import("Library.Org.Page.LitePage");

        $config  = array("prev","next");
        $p = new \LitePage($pageIndex,$pageCount,$total,$config,null);
        $show    = $p->show();
        return array("list" => $list,"page" => $show,"total"=> $total);
    }

    public function getWendaList($keyword,$pageIndex,$pageCount)
    {
        $keyword = empty($keyword)?$this->contentDefaultKeyWord:$keyword;

        if ($pageIndex > 10) {
            $pageIndex = 10;
        }

        $service = new ElasticSearchServiceModel();
        $result = $service->getWendaList($keyword,$pageIndex,$pageCount);
        $list = $result["list"];
        $page = $result["page"];
        $list = $this->contentResultProcessing($list);

        $total = $page["total_number"];

        if ($page["total_number"] > 100) {
            $total = 100;
        }

        if (count($list) == 0) {
            $total = 0;
        }

        import("Library.Org.Page.LitePage");

        $config  = array("prev","next");
        $p = new \LitePage($pageIndex,$pageCount,$total,$config,null);
        $show    = $p->show();
        return array("list" => $list,"page" => $show,"total"=> $total);
    }

    public function getBaikeList($keyword,$pageIndex,$pageCount)
    {
        $keyword = empty($keyword)?$this->contentDefaultKeyWord:$keyword;

        if ($pageIndex > 10) {
            $pageIndex = 10;
        }

        $service = new ElasticSearchServiceModel();
        $result = $service->getBaikeList($keyword,$pageIndex,$pageCount);
        $list = $result["list"];
        $page = $result["page"];
        $list = $this->contentResultProcessing($list);

        $total = $page["total_number"];

        if ($page["total_number"] > 100) {
            $total = 100;
        }

        if (count($list) == 0) {
            $total = 0;
        }

        import("Library.Org.Page.LitePage");

        $config  = array("prev","next");
        $p = new \LitePage($pageIndex,$pageCount,$total,$config,null);
        $show    = $p->show();
        return array("list" => $list,"page" => $show,"total"=> $total);
    }

    public function getSenceContentList($sence,$keyword,$pageIndex,$pageCount)
    {
        if ($pageIndex > 10) {
            $pageIndex = 10;
        }
        $service = new ElasticSearchServiceModel();
        $result = $service->getSenceContentList($sence,$keyword,$pageIndex,$pageCount);
        $list = $result["list"];
        $page = $result["page"];
        $list = $this->contentResultProcessing($list);

        $total = $page["total_number"];

        if ($page["total_number"] > 100) {
            $total = 100;
        }

        if (count($list) == 0) {
            $total = 0;
        }

        import("Library.Org.Page.LitePage");

        $config  = array("prev","next");
        $p = new \LitePage($pageIndex,$pageCount,$total,$config,null);
        $show    = $p->show();
        return array("list" => $list,"page" => $show,"total"=> $total);
    }

    public function getSenceArticleList($sence,$keyword,$pageIndex,$pageCount)
    {
        if ($pageIndex > 10) {
            $pageIndex = 10;
        }
        $service = new ElasticSearchServiceModel();
        $result = $service->getSenceArticleList($sence,$keyword,$pageIndex,$pageCount);
        $list = $result["list"];
        $page = $result["page"];
        $list = $this->contentResultProcessing($list);

        $total = $page["total_number"];

        if ($page["total_number"] > 100) {
            $total = 100;
        }

        if (count($list) == 0) {
            $total = 0;
        }

        import("Library.Org.Page.LitePage");

        $config  = array("prev","next");
        $p = new \LitePage($pageIndex,$pageCount,$total,$config,null);
        $show    = $p->show();
        return array("list" => $list,"page" => $show,"total"=> $total);
    }

    public function getCompanyList($region,$gm,$bz,$cs,$keyword,$pageIndex,$pageCount)
    {
        if (empty($pageIndex)) {
            $pageIndex = 1;
        }

        // 综合实力
        if (!empty($cs)) {
            $map["cs"] = $cs;
        }

        if (!empty($region)) {
            $map["fw"] = $region;
        }

        if (!empty($bz)) {
           $map["bz"] = $bz;
        }

        if (!empty($gm)) {
            $map["gm"] = $gm;
        }

        if (!empty($keyword)) {
            $map["keyword"] =  $keyword;
        }

        $count = D("Common/Company")->getCompanyCount($map);

        $list = [];
        if ($count > 0) {
            import("Library.Org.Page.LitePage");
            $config  = array("prev","next");
            $p = new \LitePage($pageIndex,$pageCount,$count,$config,null);
            $show = $p->show();
            $result = D("Common/Company")->getShiliList($map, ($pageIndex - 1) * $pageCount, $pageCount);

            foreach ($result["result"] as $item) {
                $list[$item["id"]] = $item;
            }
            CompanyLogicModel::bindTags($list);
            // 绑定 计算装修公司星星
            CompanyLogicModel::bindStar($list);
        }
        return array("list" => $list,"page" => $show,"total"=> $count);
    }

    private function contentResultProcessing($list)
    {
        $thematicLogic = new ThematicWordsLogicModel();
        $article = new ArticleModel();
        $ask = new AskModel();
        $baike = new BaikeModel();
        foreach ($list as $key => $item) {
            switch ($item["logtype"]){
                case "gonglue":
                    $result =  $article->getArticleClassInfo($item["id"]);
                    $list[$key]["first_class_name"] = $result["first_class_name"];
                    $list[$key]["three_class_name"] = $result["three_class_name"];
                    $list[$key]["views"] = $result["pv"];
                    $list[$key]["createtime"] = date("Y-m-d",$result["addtime"]);
                    $labels = $thematicLogic->getContentLabels($item["id"],1,3);
                    break;
                case "baike":
                    $result = $baike->getCategoryByBaikeId([$item["id"]],"b.views,c.name as class_name,b.post_time");

                    $list[$key]["views"] = $result[0]["views"];
                    $list[$key]["class_name"] = $result[0]["class_name"];
                    $list[$key]["createtime"] = date("Y-m-d",$result[0]["post_time"]);
                    $labels = $thematicLogic->getContentLabels($item["id"],2,3);
                    break;
                case "wenda":
                    $result =  $ask->getAskById($item["id"]);
                    $list[$key]["views"] = $result["views"];
                    $list[$key]["anwsers"] = $result["anwsers"];
                    $list[$key]["createtime"] = date("Y-m-d",$result["post_time"]);
                    $wenda[] = $item["id"];
                    $labels = $thematicLogic->getContentLabels($item["id"],3,3);
                    break;
            }

            if (count($labels) > 0) {
                $list[$key]["labels"] = $labels;
            }
            if (empty($item["description"])) {
                $list[$key]["description"] = $item["content"];
            }
            $list[$key]["description"] = mb_substr(strip_tags($list[$key]["description"]),0,138,"utf-8")."...";
        }
        return $list;
    }

    // 获取案例图数据
    public function getCasesList($input, $pageIndex, $pageCount){
        $elasticSearchService = new ElasticSearchServiceModel();
        $data = $elasticSearchService->searchCases($input, $pageIndex, $pageCount);

        // 最大页数限制
        if (isset($data["page"])) {
            if ($data["page"]["total_number"] > $this->pageMaxNumber * $pageCount) {
                $data["page"]["total_number"] = $this->pageMaxNumber * $pageCount;
            }
        }

        $casesLogic = new CasesLogicModel();
        $data["list"] = $casesLogic->getListMaininfo($data["list"]);

        return $data;
    }

    // 获取美图数据
    public function getTuList($input, $pageIndex, $pageCount){
        $elasticSearchService = new ElasticSearchServiceModel();
        if ($input["type"] == TuLogicModel::TU_TYPE_HOME) {
            $data = $elasticSearchService->searchTuHome($input, $pageIndex, $pageCount);
        } else {
            $data = $elasticSearchService->searchTuPub($input, $pageIndex, $pageCount);
        }

        // 最大页数限制
        if (isset($data["page"])) {
            if ($data["page"]["total_number"] > $this->pageMaxNumber * $pageCount) {
                $data["page"]["total_number"] = $this->pageMaxNumber * $pageCount;
            }
        }

        if (!empty($data["list"])) {
            $tuIds = array_column($data["list"], "id");
            if (!empty($tuIds)) {
                $tuModel = new TuModel();
                if ($input["type"] == TuLogicModel::TU_TYPE_HOME) {
                    $detail_flag = "j";
                    $tuList = $tuModel->getHomeListByIds($tuIds);
                } else {
                    $detail_flag = "g";
                    $tuList = $tuModel->getPubListByIds($tuIds);
                }

                $tuList = array_column($tuList, null, "id");
                foreach ($data["list"] as $key => $item) {
                    $id = $item["id"];
                    $data["list"][$key]["maininfo"] = [];
                    if (array_key_exists($id, $tuList)) {
                        $data["list"][$key]["maininfo"] = $tuList[$id];
                    }

                    $data["list"][$key]["detail_flag"] = $detail_flag;
                    $data["list"][$key]["img_src_full"] = $this->transformImage($item["img_src"]);
                }
            }
        }

        return $data;
    }


    /**
     * 清洗图片
     * @param string $url
     * @return string|string
     */
    private function transformImage(string $url)
    {
        //有qizuang.com 和协议头
        if (strstr($url, 'http:') !== false || strstr($url, 'https:') !== false) {
            return $url;
        } else {
            $url = 'https://' . C('QINIU_DOMAIN') . '/' . $url;
            return $url;
        }
    }

}