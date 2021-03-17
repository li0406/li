<?php

namespace Common\Model\Logic;

use Common\Enums\ThematicWordsCodeEnum;
use Common\Model\ArticleVedioModel;
use Common\Model\BaikeModel;
use Common\Model\Db\ThematicWordsModel;
use Common\Model\Db\TuHomeModel;
use Common\Model\Db\TuModel;
use Common\Model\Db\CompanyModel;
use Common\Model\ArticleModel;
use Common\Model\Db\WwwArticleModel;
use Common\Model\MeituModel;
use Common\Model\DesignerModel;
use Common\Model\CasesModel;
use Common\Model\Service\ElasticSearchServiceModel;
use Common\Model\WwwArticleClassModel;

class DecorationLogicModel
{
    /**
     * 查询指定标签的内容id
     * @param string $keywords 关键词
     * @param int $module 栏目类型
     * @param int $num 查询数量
     * @return array
     */
    public function getThematicWordsByModule($scene_word = '',$keywords = '', $module = ThematicWordsCodeEnum::MODULE_CODE_GL,$num = 4)
    {
        $searchService = new ElasticSearchServiceModel();
        $list = [];

        switch ($module) {
            //攻略(主站文章) new
            case ThematicWordsCodeEnum::MODULE_CODE_GL:
                $field = 'c.classname,c.shortname';
                $list = $searchService->searchWwwArticle($keywords,1,$num);
                if(count($list) > 0){
                    $articleDb = new WwwArticleModel();
                    $info = $articleDb->getWwwArticleClass(array_column($list,'three_short_name'),$field);
                    $info = array_column($info,null,'shortname');
                    foreach ($list as $k => $v) {
                        $list[$k]['classname'] = $info[$v['three_short_name']]['classname'];
                        $list[$k]['shortname'] = $v['three_short_name'];
                    }
                }
                break;
                //百科new
            case ThematicWordsCodeEnum::MODULE_CODE_BK:
                $list = $searchService->searchBaike($keywords,1,$num);
                if(count($list) > 0){
                    //获取百科分类
                    $baikeDb = new BaikeModel();
                    $info = $baikeDb->getBaikeClass(array_column($list,'id'),'b.id,c.cid,c.name');
                    $info = array_column($info,null,'id');
                    foreach ($list as $k=>$v){
                        $list[$k]['cid'] = $info[$v['id']]['cid'];
                        $list[$k]['name'] = $info[$v['id']]['name'];
                    }
                }
                break;
            //问答new
            case ThematicWordsCodeEnum::MODULE_CODE_WD:
                $info = $searchService->searchWenda($keywords,1,$num);
                foreach ($info as $k=>$v){
                    $list[$k]['id'] = $v['id'];
                    $list[$k]['title'] = $v['title'];
                }
                break;
            //家装new
            case ThematicWordsCodeEnum::MODULE_CODE_JZ :
                $search = new SearchLogicModel();
                $param = [
                    'scene_word'=>$scene_word,
                    'type'=>2,
                ];
                $data = $search->getTuList($param,1,$num);
                $list = $data['list'];
                break;
            case ThematicWordsCodeEnum::MODULE_CODE_GZ :
                //公装
                $search = new SearchLogicModel();
                $param = [
                    'scene_word'=>$scene_word,
                    'type'=>1,
                ];
                $data = $search->getTuList($param,1,$num);
                $list = $data['list'];
                break;
        }
        return $list;
    }

    public function getVideoById($ids){
        $videoDb = new ArticleVedioModel();
        $video = $videoDb->getVideoInfoById($ids,'id,title,description,cover_img');
        return $video;
    }

    /**
     * 获取装修案例效果图
     * @return [type] [description]
     */
   public function getCaseImagesList($pageCount = 10,$options,$cs)
    {
        $options["type"] = 1;
        $caseModel = new CasesModel();
        $result = $caseModel->getRestCaseImages($pageCount,$options["type"],$cs,null,$options["hx"],$options['fg'],$options['jg'],$options["ys"]);
        if(count($result) > 0){
            $images = array();
            foreach ($result as $key => $value) {
                if(empty($value['bm'])){
                    $value["bm"] = $value["bmt"];
                }
                $date =floor((time()-$value["time"])/3600) <=0?1:floor((time()-$value["time"])/3600);
                $rand = rand(2000,10000);
                $value["date"] = $date;
                $value["looked"] = $rand;
                $value['writetime'] = timeFormatToMouth($value['time']);
                $value["writer"] = empty($value["writer"])?"齐装网":$value["writer"];
                $images[] = $value;
                
            }
        }
        return array("images"=>$images);
    }
    /**
     * 获取带标签和分类名称的文章列表
     * @author GodJob
     * @Date   2019-11-25
     * @param  array     $list [description]
     * @return array           [description]
     */
    public function getFullArticleList($list)
    {
        if(empty($list))
        {
            return [];
        }
        $ids = empty($list)?[0]:array_column($list,'id');
        $articleModel = new ArticleModel();
        $classes = $articleModel->getArticleClass($ids);
        $tags = $articleModel->getArticleTags($ids);
        $res = [];
        foreach($list as $v){
            $res[$v['id']] = $v;
            $res[$v['id']]['tags'] = [];
        }
        foreach($classes as $v){
            $res[$v['id']]['classname'] = $v['classname'];
            $res[$v['id']]['pclassname'] = $v['pclassname'];
            $res[$v['id']]['shortname'] = $v['shortname'];
            $res[$v['id']]['date'] = date('Y-m-d',$res[$v['id']]['createtime']);
        }
        foreach($tags as $v){
            array_push($res[$v['id']]['tags'],$v['name']);
        }
        $sortkey = array_column($res,'createtime');
        array_multisort($sortkey,SORT_DESC,$res);
        return $res;
    }
    public function getDesigners($cityCode){
        return D('Common/Designer')->getCaseDesigner($cityCode);
    }
    public function getVideoListData($pageIndex = 1, $pageCount = 8, $cid = '')
    {
        $map['b.pid'] = ['eq', 3];
        $map['b.cid'] = ['eq', $cid];
        $count = D("ArticleVedio")->getVideoListDataCount($map);

        if($count > 0){
            $result = D("ArticleVedio")->getVideoListDataByTime(($pageIndex-1)*$pageCount, $pageCount, $map,'a.id,a.title');
            return $result;
        }
        return [];
    }
    public function getNewsByTagName($tagName,$gonglue = 4,$baike = 3,$wenda = 3)
    {
        // $gonglue = D('Common/WwwArticle')->getListByTagName($tagName,$gonglue,'a.id,a.title,a.createtime as time,a.subtitle,a.pv,a.face','a.createtime DESC');
        // $baike = D('Common/Baike')->getListByTagName($tagName,$baike,'b.id,b.title,b.post_time as time','b.post_time DESC');
        // $wenda = D('Common/Wenda')->getListByTagName($tagName,$wenda,'b.id,b.title,b.post_time as time','b.post_time DESC');
        $es = new ElasticSearchServiceModel();
        $gonglue = $es->searchWwwArticle($tagName,1,$gonglue);
        $baike = $es->searchBaike($tagName,1,$baike);
        $wenda = $es->searchWenda($tagName,1,$wenda);
        $res = [];
        foreach($gonglue as $v){
            $v['module'] = 'gonglue';
            array_push($res,$v);
        }
        foreach($baike as $v){
            $v['module'] = 'baike';
            array_push($res,$v);
        }
        foreach($wenda as $v){
            $v['module'] = 'wenda';
            array_push($res,$v);
        }
        $sortIndex = array_column($res,'time');
        array_multisort($sortIndex,SORT_DESC,$res);
        return $res;
    }
    public function getTuByTagname($page = 1,$size = 5,$tagName,$type){
        $meituModule = new MeituModel();
        return $meituModule->getMeituByTagName($page,$size,$tagName,2,'a.title,a.id,a.title,ti.src image_src,ti.title image_title,ti.width image_width,ti.height image_height');
    }
    public function getCaseDesigner($cityCode = [],$row){
        $designer = new DesignerModel();
        return $designer->getCaseDesigner($cityCode,$row);
    }
    public function getRecoomendDesigner($cs,$row = 9,$no_ids){
        $designer = new DesignerModel();
        $db = new CompanyModel();
        $list1 = [];
        $list2 = [];
        $comids = [];
        if(!empty($cs)){
            $comids = $db->getRankCompany($cs,0);
            $list1 = $designer->getDesignerByComids(array_column($comids,'id'),$row);
        }

        $no_comids = array_column($comids, 'id');


        if(count($list1)<$row)
        {
            $comids = $db->getRankCompany('',5,$no_comids);
            $list1 = $designer->getDesignerByComids(array_column($comids,'id'),$row);
        }
        $list = array_merge($list1,$list2);
        array_multisort(array_column($list, 'pv'),SORT_DESC,$list);
        return $list;
    }
}