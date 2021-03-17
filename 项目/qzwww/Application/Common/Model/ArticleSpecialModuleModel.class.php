<?php


namespace Common\Model;
use Think\Model;

/**
* 专题模块表
*/
class ArticleSpecialModuleModel extends Model
{
    protected $autoCheckFields = false;

    /**
     * 获取
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    public function getIndexSpecialModule($limit)
    {
        $map = array(
            "a.istop" => array("EQ",1),
            "a.isdelete" => array("EQ",1)
        );
        return M("article_special_module")->alias("a")->where($map)
                                          ->join("join qz_article_special_class b on b.id = a.type")
                                          ->field("a.`id`,a.`title`,a.`description`,a.`cover_bigimg`,b.classname,b.shortname")
                                          ->limit($limit)
                                          ->order("a.id desc")->select();
    }

    /**
     * 获取最新的推荐到专题首页的专题模块
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    public function getNewSpecialModule($limit)
    {
        $map = array(
            "a.isbanner" => array("EQ",1),
            "a.isdelete" => array("EQ",1)
        );
        return M("article_special_module")->alias("a")->where($map)
                                          ->join("join qz_article_special_class b on b.id = a.type")
                                          ->field("a.`id`,a.`title`,a.`description`,a.`cover_bigimg`,b.classname,b.shortname")
                                          ->limit($limit)
                                          ->order("a.id desc")->select();
    }

    /**
     * 获取历史回顾专题
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    public function getHistorySpecialModule($limit,$flag = true)
    {
        $map = array(
            "a.istop" => array("EQ",1),
            "a.isdelete" => array("EQ",1)
        );
        M("article_special_module")->alias("a")->where($map)
                                          ->join("join qz_article_special_class b on b.id = a.type")
                                          ->field("a.`id`,a.`title`,a.`description`,a.`cover_img`,b.classname,b.shortname")
                                          ->order("a.id desc");
        if ($flag) {
            M("article_special_module")->limit("5,",$limit);
        }
        return M("article_special_module")->select();
    }

    /**
     * 获取专题模块数量
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public function getSpecialModuleListCount($type)
    {
        $map = array(
            "isdelete" => array("EQ",1),
            "type" => array("EQ",$type)
        );
        return M("article_special_module")->where($map)->count();
    }

     /**
     * 获取专题模块数量
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public function getSpecialModuleList($type,$pageIndex,$pageCount)
    {
        $map = array(
            "isdelete" => array("EQ",1),
            "type" => array("EQ",$type)
        );
        return M("article_special_module")->where($map)
                                          ->limit($pageIndex.",".$pageCount)
                                          ->order("id desc")
                                          ->select();
    }

    /**
     * 获取推荐的专题模块
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getTopArticleSpecial($type,$limit)
    {
        $map = array(
            "isdelete" => array("EQ",1),
            "type" => array("EQ",$type),
            "istop" => array("EQ",1)
        );
        return M("article_special_module")->where($map)
                                          ->limit($limit)
                                          ->order("id desc")
                                          ->select();
    }

    /**
     * 获取模块信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getModuleInfo($id)
    {
        $map = array(
            "a.isdelete" => array("EQ",1),
            "a.id" => array("EQ",$id)
        );

        return M("article_special_module")->where($map)->alias("a")
                                          ->join("join qz_article_special_class b on a.type = b.id")
                                          ->field("a.title,b.shortname,b.classname,a.cover_bigimg,a.description")
                                          ->find();
    }

    /**
     * 获取模块子分类
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getModuleMeituList($id)
    {
        $map = array(
            "a.module" => array("EQ",$id),
            "a.type" => array("EQ","meitu")
        );

        $buildSql = M("article_special_module_class")->where($map)->alias("a")
                                                ->field("a.title,a.subtitle,a.article_id,a.sort,c.img_path")
                                                ->join("join qz_meitu as b on b.id = a.article_id")
                                                ->join("join qz_meitu_img c on c.caseid = b.id")
                                                ->order("c.img_on,px")->buildSql();
        return M("article_special_module_class")->table($buildSql)->alias("t")
                                                ->group("t.article_id")
                                                ->order("t.sort")->select();
    }

    /**
     * 获取模块文章列表
     * @param  [type] $id   [description]
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public function getModuleArticleList($id,$type)
    {
        $map = array(
            "a.module" => array("EQ",$id),
            "a.type" => array("EQ",$type)
        );

        return  M("article_special_module_class")->where($map)->alias("a")
                                                     ->join("join qz_www_article b on a.article_id = b.id")
                                                     ->join("join qz_www_article_class_rel c on c.article_id = b.id")
                                                     ->join("join qz_www_article_class d on d.id = c.class_id")
                                                     ->field("a.title,b.title as subtitle,a.article_id,b.subtitle as description,d.shortname,b.face")
                                                     ->select();

    }

    /**
     * 获取问答模块列表
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getModuleAskList($id)
    {
        $map = array(
            "a.module" => array("EQ",$id),
            "a.type" => array("EQ","ask")
        );

        return  M("article_special_module_class")->where($map)->alias("a")
                                                 ->join("join qz_ask b on a.article_id = b.id")
                                                 ->join("left join qz_ask_anwser c on c.id = b.best_aid")
                                                 ->field("a.title,b.title as subtitle,c.content,a.article_id")
                                                 ->select();
    }

    /**
     * 获取视频模块列表
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getModuleVideoList($id)
    {
        $map = array(
            "a.module" => array("EQ",$id),
            "a.type" => array("EQ","video")
        );

        return  M("article_special_module_class")->where($map)->alias("a")
                                                 ->join("join qz_article_vedio b on a.article_id = b.id")
                                                 ->field("a.title, a.article_id,b.title as subtitle,b.cover_img")
                                                 ->select();
    }
}