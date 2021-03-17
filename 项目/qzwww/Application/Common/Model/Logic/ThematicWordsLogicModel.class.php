<?php

namespace Common\Model\Logic;

use Common\Model\Db\TuModel;
use Common\Model\CasesModel;
use Common\Model\Db\ThematicWordsModel;
use Common\Model\Service\ElasticSearchServiceModel;

class ThematicWordsLogicModel
{
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
            import('Library.Org.Page.LitePage');
            $page = new \LitePage($pageIndex,$pageCount,$count);
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

    public function getThematicSearch($name,$pageCount)
    {
        $url = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
        $reg = '/\/p(\d+)/i';
        preg_match($reg,$url,$m);
        $pageIndex =  !isset($m[1]) ? 1 : $m[1];
        $schemeAndHost = getSchemeAndHost();

        $service = new ElasticSearchServiceModel();
        $result = $service->getThematicSearch($name,$pageIndex,$pageCount);
        $list = $result["list"];
        $page = $result["page"];

        foreach ($list as $key => $item) {
            $list[$key]["img_path"] = C("QINIU_SCHEME")."://".C("QINIU_DOMAIN")."/".$item["img_path"]."-w400.jpg";

            switch ($item["logtype"]){
                case "qz_www_article":
                     $list[$key]["url"] = $schemeAndHost["scheme_full"]. "www." . $schemeAndHost["domain"]."/gonglue/".$item["three_short_name"]."/".$item["id"].".html";
                     $labels = $this->getContentLabels($item["id"],1,3);
                     break;
                case "qz_baike":
                    $list[$key]["url"] = $schemeAndHost["scheme_full"]. "www." . $schemeAndHost["domain"]."/baike/".$item["id"].".html";
                    $labels = $this->getContentLabels($item["id"],2,3);
                    break;
                case "qz_ask":
                    $list[$key]["img_path"] = C('QINIU_SCHEME')."://".C('QINIU_DOMAIN')."/custom/20191012/FlDVSysFCowb1M9NDe6eUvH2ajZ2.png";
                    $list[$key]["url"] = $schemeAndHost["scheme_host"]."/wenda/x".$item["id"].".html";
                    $labels = $this->getContentLabels($item["id"],3,3);
                    break;
                case "qz_little_article":
                    $list[$key]["img_path"] = $item["img_path"]."-w400.jpg";
                    //分站文章
                    $list[$key]["url"] = $schemeAndHost["scheme_full"].$item["bm"].".".C("QZ_YUMING")."/zxinfo/".$item["id"].".html";
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
        import('Library.Org.Page.LitePage');
        //自定义配置项
        $page = new \LitePage($pageIndex,$pageCount,$page["total_number"]);
        $show    = $page->staticshow();
        return array("list" => $list,"page" => $show,'pageIndex'=>$pageIndex);
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
                    $list[$key]["img_thumb"] = C("QINIU_SCHEME")."://".C("QINIU_DOMAIN")."/".$item["img_src"]."-w400.jpg";
                    unset($list[$key]["img_src"], $list[$key]["img_host"], $list[$key]["img_path"], $list[$key]["img"]);
                    break;
                case "qz_tu_home":
                case "qz_meitu_home":
                    $homeIds[] = $item["id"];
                    $list[$key]["bm"] = "www";
                    $list[$key]["img_title"] = $item["title"];
                    $list[$key]["link_uri"] = sprintf("tu/j%d.html", $item["id"]);
                    $list[$key]["img_thumb"] = C("QINIU_SCHEME")."://".C("QINIU_DOMAIN")."/".$item["img_src"]."-w400.jpg";
                    unset($list[$key]["img_src"], $list[$key]["img_host"], $list[$key]["img_path"], $list[$key]["img"]);
                    break;
                case "qz_cases":
                case "qz_meitu_case":
                    if ($item["img_host"] == "qiniu") {
                        $list[$key]["img_thumb"] = C("QINIU_SCHEME")."://".C("QINIU_DOMAIN")."/".$item["img_path"]."-w400.jpg";
                    } else {
                        $list[$key]["img_thumb"] = C("QINIU_SCHEME")."://".C("STATIC_HOST1")."/".$item["img_path"]."m_".$item["img"];
                    }

                //     $caseIds[] = $item["id"];
                //     $list[$key]["bm"] = "www";
                //     $list[$key]["img_title"] = $item["title"];
                //     $list[$key]["link_uri"] = sprintf("caseinfo/%d.shtml", $item["id"]);
                //     unset($list[$key]["img_src"], $list[$key]["img_host"], $list[$key]["img_path"], $list[$key]["img"]);
                //     break;
            }
        }


        /*
         * 725 由于标签页所有图库关联装修案例图库数据，导致标签页整体图片质量被拉低。
         * 去除案例图
         *
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
                    break;
            }
        }
        */

        import('Library.Org.Page.LitePage');
        //自定义配置项
        $page = new \LitePage($pageIndex,$pageCount,$page["total_number"]);
        $show = $page->staticshow();
        return array("list" => $list,"page" => $show, "pageIndex" => $pageIndex);
    }

    public function getHotList($limit)
    {
        $model = new ThematicWordsModel();
        $list = $model->getHotList($limit);
        $schemeAndHost = getSchemeAndHost();
        foreach ($list as $key => $item) {
            if ($item["type"] == 1) {
                $list[$key]["url"] = $schemeAndHost["scheme_full"]. "www." . $schemeAndHost["domain"]."/zhishi/".$item["id"]."/";
            } else {
                $list[$key]["url"] = $schemeAndHost["scheme_full"]. "www." . $schemeAndHost["domain"]."/tu/".$item["id"]."/";
            }
        }
        return $list;
    }

    public function getNewList($limit)
    {
        $model = new ThematicWordsModel();
        $list = $model->getNewList($limit);
        $schemeAndHost = getSchemeAndHost();
        foreach ($list as $key => $item) {
            if ($item["type"] == 1) {
                $list[$key]["url"] = $schemeAndHost["scheme_full"]. "www." . $schemeAndHost["domain"] ."/zhishi/".$item["id"]."/";
            } else {
                $list[$key]["url"] = $schemeAndHost["scheme_full"]. "www." . $schemeAndHost["domain"] ."/tu/".$item["id"]."/";
            }
        }
        return $list;
    }

    public function getNewTypeWords($type, $limit = 10){
        $model = new ThematicWordsModel();
        $list = $model->getNewTypeList($type, $limit);
        $schemeAndHost = getSchemeAndHost();
        foreach ($list as $key => $item) {
            if ($item["type"] == 1) {
                $list[$key]["url"] = $schemeAndHost["scheme_full"]. "www." . $schemeAndHost["domain"]."/zhishi/".$item["id"]."/";
            } else {
                $list[$key]["url"] = $schemeAndHost["scheme_full"]. "www." . $schemeAndHost["domain"]."/tu/".$item["id"]."/";
            }
        }
        return $list;
    }

    public function getContentLabels($id, $module, $limit, $type = 1)
    {
        $model = new ThematicWordsModel();
        return $model->getContentLabels($id, $module, $limit, $type);
    }

    public function getNewLabels($id,$limit)
    {
        $model = new ThematicWordsModel();
        return $model->getNewLabels($id,$limit);
    }

    /**
     * 获取相邻关键词
     * @param  [type]  $id    [description]
     * @param  [type]  $type  [description]
     * @param  integer $limit [description]
     * @return [type]         [description]
     */
    public function getNearbyWords($id, $type, $limit = 15){
        $model = new ThematicWordsModel();
        return $model->getNearbyWords($id,$type, $limit);
    }

    // 最热美图
    public function getTypeHotList($type, $limit = 10){
        $model = new ThematicWordsModel();
        $list = $model->getTypeHotList($type, $limit);
        $schemeAndHost = getSchemeAndHost();
        foreach ($list as $key => $item) {
            if ($item["type"] == 1) {
                $list[$key]["url"] = $schemeAndHost["scheme_full"]. "www." . $schemeAndHost["domain"]."/zhishi/".$item["id"]."/";
            } else {
                $list[$key]["url"] = $schemeAndHost["scheme_full"]. "www." . $schemeAndHost["domain"]."/tu/".$item["id"]."/";
            }
        }
        return $list;
    }





}