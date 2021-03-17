<?php

namespace Common\Model\Logic;

use Common\Model\CasesModel;
use Common\Model\Db\ThematicWordsModel;
use Common\Model\Service\ElasticSearchServiceModel;

class ThematicWordsLogicModel
{
    public $scheme_host;

    public function __construct(){
        $this->scheme_host = getSchemeAndHost();
    }

    public function getList($pageCount)
    {
        $url = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
        $reg = '/\/p(\d+)/i';
        preg_match($reg,$url,$m);
        $pageIndex =  !isset($m[1]) ? 1 : $m[1];

        $type = [1, 2];

        $model = new ThematicWordsModel();
        $count = $model->getListCount($type);

        $list = [];
        $show = "";
        if ($count > 0) {
            import('Library.Org.Page.MobilePage');
            $config  = array("prev","next");
            $page = new \MobilePage($pageIndex,$pageCount,$count,$config,"html");
            $show    = $page->staticshow();
            $list = $model->getList($type,($page->pageIndex-1)*$pageCount,$pageCount);

        }
        return array("list" => $list,"page"=>$show,"pageIndex" => $pageIndex);
    }

    public function getThematicInfoById($id)
    {
        $model = new ThematicWordsModel();
        return $model->getThematicInfoById($id);
    }

    public function getHotList($limit)
    {
        $model = new ThematicWordsModel();
        $list = $model->getHotList($limit);
        foreach ($list as $key => $item) {
            $list[$key]["url"] = $this->scheme_host["scheme_full"].C("QZ_YUMINGWWW")."/zhishi/".$item["id"]."/";
        }
        return $list;
    }

    public function getThematicSearch($name,$pageCount)
    {
        $url = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
        $reg = '/\/p(\d+)/i';
        preg_match($reg,$url,$m);
        $pageIndex =  !isset($m[1]) ? 1 : $m[1];

        $service = new ElasticSearchServiceModel();
        $result = $service->getThematicSearch($name,$pageIndex,$pageCount);
        $list = $result["list"];
        $page = $result["page"];

        foreach ($list as $key => $item) {
            $list[$key]["img_path"] = $this->scheme_host["scheme_full"].C("QINIU_DOMAIN")."/".$item["img_path"]."-w400.jpg";

            switch ($item["logtype"]){
                case "qz_www_article":
                    $list[$key]["url"] = $this->scheme_host["scheme_full"].C("MOBILE_DONAMES")."/gonglue/".$item["three_short_name"]."/".$item["id"].".html";
                    $labels = $this->getContentLabels($item["id"],1,3);
                    break;
                case "qz_baike":
                    $list[$key]["url"] = $this->scheme_host["scheme_full"].C("MOBILE_DONAMES")."/baike/".$item["id"].".html";
                    $labels = $this->getContentLabels($item["id"],2,3);
                    break;
                case "qz_ask":
                    $list[$key]["img_path"] = $this->scheme_host["scheme_full"].C("QINIU_DOMAIN")."/custom/20191012/FlDVSysFCowb1M9NDe6eUvH2ajZ2.png";
                    $list[$key]["title"] = $item["title"];
                    $list[$key]["url"] = $this->scheme_host["scheme_full"].C("MOBILE_DONAMES")."/wenda/x".$item["id"].".html";
                    $labels = $this->getContentLabels($item["id"],3,3);
                    break;
                case "qz_little_article":
                    $list[$key]["img_path"] = $item["img_path"]."-w400.jpg";
                    //分站文章
                    $list[$key]["url"] = $this->scheme_host["scheme_full"].C("MOBILE_DONAMES")."/".$item["bm"]."/zxinfo/".$item["id"].".html";
                    $labels = $this->getContentLabels($item["id"],4,3);
                    break;
            }

            if (count($labels) > 0) {
                $list[$key]["labels"] = $labels;
            }

            if (empty($item["description"])) {
                $list[$key]["description"] = $item["content"];
            }
            $list[$key]["description"] = mb_substr(strip_tags($list[$key]["description"]),0,138,"utf-8");
        }
        import('Library.Org.Page.MobilePage');
        $config  = array("prev","next");
        $page = new \MobilePage($pageIndex,$pageCount,$page["total_number"],$config,"html");
        $show    = $page->staticshow();

        return array("list" => $list,"page" => $show,"pageIndex" => $pageIndex);
    }

    public function getContentLabels($id,$module,$limit)
    {
        $model = new ThematicWordsModel();
        return $model->getContentLabels($id,$module,$limit);
    }


    public function getThematicTuSearch($name, $pageCount)
    {
        $url = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
        $reg = '/\/p(\d+)/i';
        preg_match($reg,$url,$m);
        $pageIndex =  !isset($m[1]) ? 1 : $m[1];

        $service = new ElasticSearchServiceModel();
        $result = $service->getThematicTuSearch($name,$pageIndex,$pageCount);

        $list = $result["list"];
        $page = $result["page"];

        $pubIds = [];
        $homeIds = [];
        $caseIds = [];
        foreach ($list as $key => $item) {
            switch ($item["logtype"]){
                case "qz_tu_pub":
                case "qz_meitu_pub":
                    $pubIds[] = $item["id"];
                    $list[$key]["bm"] = "www";
                    $list[$key]["img_title"] = $item["title"];
                    $list[$key]["link_uri"] = sprintf("tu/g%d.html", $item["id"]);
                    $list[$key]["img_thumb"] = $this->scheme_host["scheme_full"].C("QINIU_DOMAIN")."/".$item["img_src"]."-w400.jpg";
                    unset($list[$key]["img_src"], $list[$key]["img_host"], $list[$key]["img_path"], $list[$key]["img"]);
                    break;
                case "qz_tu_home":
                case "qz_meitu_home":
                    $homeIds[] = $item["id"];
                    $list[$key]["bm"] = "www";
                    $list[$key]["img_title"] = $item["title"];
                    $list[$key]["link_uri"] = sprintf("tu/j%d.html", $item["id"]);
                    $list[$key]["img_thumb"] = $this->scheme_host["scheme_full"].C("QINIU_DOMAIN")."/".$item["img_src"]."-w400.jpg";
                    unset($list[$key]["img_src"], $list[$key]["img_host"], $list[$key]["img_path"], $list[$key]["img"]);
                    break;
                case "qz_cases":
                case "qz_meitu_case":
                    if ($item["img_host"] == "qiniu") {
                        $list[$key]["img_thumb"] = $this->scheme_host["scheme_full"].C("QINIU_DOMAIN")."/".$item["img_path"]."-w400.jpg";
                    } else {
                        $list[$key]["img_thumb"] = $this->scheme_host["scheme_full"].C("STATIC_HOST1")."/".$item["img_path"]."m_".$item["img"];
                    }

                    $caseIds[] = $item["id"];
                    $list[$key]["bm"] = "www";
                    $list[$key]["img_title"] = $item["title"];
                    $list[$key]["link_uri"] = sprintf("caseinfo/%d.shtml", $item["id"]);
                    unset($list[$key]["img_src"], $list[$key]["img_host"], $list[$key]["img_path"], $list[$key]["img"]);
                    break;
            }
        }

        // 案例图片分站、图片alt处理
        $caseBmList = [];
        if (count($caseIds) > 0) {
            $casesModel = new CasesModel();
            $caseBmList = $casesModel->getBmByIds($caseIds);
            $caseBmList = array_column($caseBmList, null, "id");
        }

        foreach ($list as $key => $item) {
            $id = $item["id"];
            switch ($item["logtype"]){
                case "qz_cases":
                case "qz_meitu_case":
                    if (array_key_exists($id, $caseBmList)) {
                        $cases = $caseBmList[$id];
                        $list[$key]["bm"] = $cases["bm"];
                        $title = sprintf("%s%s装修设计案例", $cases["cname"], $item["title"]);
                        $list[$key]["title"] = $title;
                        $list[$key]["img_title"] = $title;
                    }
                    $list[$key]["link_uri"] = $cases["bm"] . "/" . $item["link_uri"];
                    break;
            }
        }

        import('Library.Org.Page.MobilePage');
        $config  = array("prev","next");
        $page = new \MobilePage($pageIndex,$pageCount,$page["total_number"],$config,"html");
        $show = $page->staticshow();
        return array("list" => $list,"page" => $show, "pageIndex" => $pageIndex);
    }

}