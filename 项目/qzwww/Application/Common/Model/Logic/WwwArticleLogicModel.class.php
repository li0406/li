<?php

namespace Common\Model\Logic;

use Common\Model\Service\ElasticSearchServiceModel;

class WwwArticleLogicModel
{
    public function getAnalyzeWord($word)
    {
        $service = new ElasticSearchServiceModel();
        $list = $service->analyzeWord($word);
        return $list;
    }

    /*
    * 获取精选问答文章的最新十条
    */
    public function getCarefullChoosenList()
    {

        $classInfo = S('Cache:Ask:classInfo');
        if(!$classInfo){
            $classInfo = D('Common/Db/WwwArticle')->getClassInfoByShortName('jxwd');
            S('Cache:Ask:classInfo', $classInfo, 60*15);
        }

        $careNew10 = S('Cache:Ask:careNew10');
        if(!$careNew10){
            $careNew10 = D('Common/Db/WwwArticle')->getNewLimitArticleList($classInfo['id']);
            S('Cache:Ask:careNew10', $careNew10, 60*15);        
        }

        return $careNew10 ?: array();
    }
}