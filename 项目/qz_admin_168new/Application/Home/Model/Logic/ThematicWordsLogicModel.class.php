<?php

namespace Home\Model\Logic;

use Home\Model\Db\SearchWordStatisticsModel;
use Home\Model\Db\ThematicWordsModel;
use Home\Model\Service\ElasticsearchServiceModel;

class ThematicWordsLogicModel
{
    public function getWordsList($name,$module,$type,$start,$end,$pageIndex,$pageCount)
    {
        $model = new ThematicWordsModel();
        $count = $model->getWordsListCount($name,$module,$type,$start,$end);

        if ($count > 0) {
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $page = $page->show();
            $list = $model->getWordsList($name,$module,$type,$start,$end,($pageIndex-1)*$pageCount,$pageCount);
            foreach ($list as $key => $value) {
                if($value['type'] == 1){
                    $list[$key]["link"] = "http://".C("QZ_YUMINGWWW")."/zhishi/".$value["id"]."/";
                }elseif ($value['type'] == 2){
                    $list[$key]["link"] = "http://".C("QZ_YUMINGWWW")."/tu/".$value["id"]."/";
                }
            }
        }
        return array("list" => $list,"page" => $page);
    }

    public function findWordInfoById($id)
    {
        $model = new ThematicWordsModel();
        if(is_array($id)){
            return $model->getWordInfoByIds($id);
        }else{
            return $model->findWordInfoById($id);
        }
    }

    public function findWordInfoByName($word)
    {
        $word = trim($word);
        $model = new ThematicWordsModel();
        return $model->findWordInfoByName($word);
    }

    public function findWordInfoByNames($word,$type)
    {
        $model = new ThematicWordsModel();
        return $model->findWordInfoByNames($word,$type);
    }

    public function addInfo($data)
    {
        $model = new ThematicWordsModel();
        return $model->addInfo($data);
    }

    public function editInfo($id,$data)
    {
        $model = new ThematicWordsModel();
        return $model->editInfo($id,$data);
    }

    public function addAllInfo($data)
    {
        $model = new ThematicWordsModel();
        return $model->addAllInfo($data);
    }

    public function delInfo($ids){
        $model = new ThematicWordsModel();
        return $model->delWordsInfo($ids);
    }

    public function getExportList($module,$type,$name,$start,$end)
    {
        $model = new ThematicWordsModel();
        return $model->getExportList($module,$type,$name,$start,$end);
    }

    public function addThematicRelated($data)
    {
        $model = new ThematicWordsModel();
        return $model->addThematicRelated($data);
    }

    public function delThematicRelated($content_id,$module)
    {
        $model = new ThematicWordsModel();
        return $model->delThematicRelated($content_id,$module);
    }

    public function getContentThematicWords($name,$module)
    {
        $model = new ThematicWordsModel();
        switch ($module){
            case 1:
                //攻略
                $result = $model->getArticleList($name,1);
                break;
            case 2:
                //百科
                $result = $model->getBaikeList($name,2);
                break;
            case 3:
                //问答
                $result = $model->getAskList($name,3);
                break;
            case 6:
                //标签
                $result = $model->getThematicList($name,6);
                break;
        }

        foreach ($result as $item) {
            $list["id"] = $item["id"];
            $list["title"] = $item["title"];

            if (!empty($item["label"])) {
                $list["labelList"][] = [
                    "id" => $item["label_id"],
                    "label" => $item["label"],
                ];
                $list["labels"] .= $item["label"].",";
            }

        }
        return $list;
    }

    public function findlabel($name)
    {
        $model = new ThematicWordsModel();
        return $model->findlabel($name);
    }

    // 匹配并且关联关键词
    public function searchAndRelatedThematic($title, $content_id, $module, $type = 1, $limit = 5){
        // 关联标签
        $searchService = new ElasticsearchServiceModel();
        $thematicList = $searchService->getContentLabel($title, $type, $limit);
        if (count($thematicList) > 0) {
            $allLabel = [];
            foreach ($thematicList as $item) {
                $allLabel[] = [
                    "thematic_id" => $item["id"],
                    "content_id" => $content_id,
                    "module" => $module
                ];
            }
            if (count($allLabel) > 0) {
                $this->addThematicRelated($allLabel);
            }
        }

        return true;
    }

    public function getSearchWordsList($date,$word,$exact)
    {
        $pageCount = 10;
        $model = new SearchWordStatisticsModel();
        $count = $model->getSearchWordsListCount($date,$word,$exact);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count,$pageCount);
            $page = $p->show();
            $list = $model->getSearchWordsList($date,$word,$exact,$p->firstRow,$p->listRows);

        }
        return array("list" => $list,"page" => $page);
    }

    public function exportsearchword($date,$word,$exact)
    {
        $model = new SearchWordStatisticsModel();
        return $model->getSearchWordsList($date,$word,$exact);
    }

}