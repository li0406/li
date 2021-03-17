<?php

namespace Home\Model;
Use Think\Model;

/**
*  文章关键字
*/
class WwwArticleKeywordsModel extends Model
{
    protected $autoCheckFields = false;

    /**
     * 获取所有的关键字
     * @return [type] [description]
     */
    public function getAllKeywords($keyword_module = 0){
        if (!empty($keyword_module)) {
            $map['keyword_module'] = $keyword_module;
        }
        //查询所有的关键字
        $result = M("www_article_keywords")->where($map)->select();
        return $result;
    }

    /**
     * 添加文章内链
     * @param [type] $data [description]
     */
    public function addRelateAll($data)
    {
        return M("keyword_relate")->addAll($data);
    }

    /**
     * [getWwwArticleKeywordsById 根据ID获取文章内链关键字]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getWwwArticleKeywordsById($id){
        if (empty($id)) {
            return false;
        }
        $map = array(
            'id' => $id
        );
        return M('www_article_keywords')->where($map)->find();
    }

    /**
     * [addWwwarticlekeywords 新增内链关键字]
     * @param [type] $save [description]
     */
    public function addWwwarticlekeywords($save){
        if (empty($save)) {
            return false;
        }
        return M('www_article_keywords')->add($save);
    }

    /**
     * [addAllWwwarticlekeywords 批量新增内链关键字]
     * @param [type] $save [description]
     */
    public function addAllWwwarticlekeywords($save){
        if (empty($save)) {
            return false;
        }
        return M('www_article_keywords')->addAll($save);
    }

    /**
     * [editWwwArticleKeywords 编辑内链关键字]
     * @param  [type] $id   [ID]
     * @param  [type] $save [编辑内容]
     * @return [type]       [description]
     */
    public function editWwwArticleKeywords($id, $save){
        if (empty($id)) {
            return false;
        }
        $map = array(
            'id' => $id
        );
        return M('www_article_keywords')->where($map)->save($save);
    }

    public function deleteWwwArticleKeywordsById($id){
        if (empty($id)) {
            return false;
        }
        $map = array(
            'id' => $id
        );
        return M('www_article_keywords')->where($map)->delete();
    }

    /**
     * [getWwwArticleKeywordsCount 获取文章内链关键字数量]
     * @param  [type] $keyword [搜索关键字]
     * @return [type]          [description]
     */
    public function getWwwArticleKeywordsCount($keyword_module = 0, $keyword){
        if (!empty($keyword_module)) {
            $map['keyword_module'] = $keyword_module;
        }
        if(!empty($keyword)){
            $map["_complex"] = array(
                "name" => array("LIKE","%$keyword%"),
                "href" => array("LIKE","%$keyword%"),
                "_logic" => "OR"
            );
        }
        return M("www_article_keywords")->where($map)->count();
    }

    /**
     * [getWwwArticleKeywordsList 获取文章内链关键字列表]
     * @param  [type] $keyword   [搜索关键字]
     * @param  [type] $pageIndex [开始页]
     * @param  [type] $pageCount [结束页]
     * @return [type]            [description]
     */
    public function getWwwArticleKeywordsList($keyword_module = 0, $keyword, $pageIndex = 0, $pageCount = 10){
        if (!empty($keyword_module)) {
            $map['keyword_module'] = $keyword_module;
        }
        if(!empty($keyword)){
            $map["_complex"] = array(
                "name" => array("LIKE","%$keyword%"),
                "href" => array("LIKE","%$keyword%"),
                "_logic" => "OR"
            );
        }
        return M("www_article_keywords")->where($map)->order("time desc")
                                        ->limit($pageIndex, $pageCount)
                                        ->select();
    }


    /**
     * [getWwwArticleKeywordsList 获取文章内链关键字列表]
     * @param  [array]  $data        [数据数组]
     * @return [string] $result      [添加的ID]
     */
    public function addLinkSubmit($data)
    {
        return M("www_article_linksubmit")->add($data);
    }

    public function findWord($word,$module)
    {
        $map = array(
            "name" => array("EQ",$word),
            "keyword_module" => array("EQ",$module)
        );
        return $this->where($map)->find();
    }
}