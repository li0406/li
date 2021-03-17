<?php
/**
 *  设计师文章表 qz_article
 */
namespace Common\Model;
use Think\Model;
class ArticleSpecialModel extends Model{
    protected $autoCheckFields = false;
    /**
     * [getArticleSpecialById 通过ID获取专题文章]
     * @param  [type] $id [专题文章id]
     * @param  [type] $type [专题文章类型id]
     * @return [type]     [description]
     */
    public function getArticleSpecialByIdAndType($id,$type)
    {
        if(empty($id)){
            return false;
        }
        $map = ['a.id' => $id];
        if(!empty($type)){
            $map['a.type'] = $type;
        }
        $result = M('article_special')->alias('a')
                                      ->field('a.*,c.classname,c.shortname,t.tag_id')
                                      ->join('qz_article_special_class AS c ON c.id = a.type')
                                      ->join('LEFT JOIN qz_article_special_tag AS t ON t.article_id = a.id')
                                      ->where($map)
                                      ->group('a.id')
                                      ->find();
        return $result;
    }

    public function getHotArticleSpecial($limit = 8)
    {
        if(empty($limit)){
            $limit = 8;
        }
        $map = ['a.istop' => 1];
        $result = M('article_special')->alias('a')
                                      ->field('a.id,a.title,c.classname,c.shortname')
                                      ->join('qz_article_special_class AS c ON c.id = a.type')
                                      ->where($map)
                                      ->order('RAND()')
                                      ->limit($limit)
                                      ->select();
        return $result;
    }

    /**
     * 获取专题首页文章
     * @param  [type] $type  [文章类型]
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    public function getTopArticleSpecial($type,$limit)
    {
        $map = array(
            "a.istop" => array("EQ",1),
            "a.status" => array("EQ",2),
            "a.type" => array("EQ",$type)
        );

        return M('article_special')->where($map)->alias("a")
                                   ->join("qz_article_special_class b on a.type = b.id")
                                   ->order("a.id desc")->field("a.id,a.title,a.description,a.cover_img,b.shortname")->limit($limit)->select();
    }

    /**
     * 获取专题文章类信息
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public function getArticleSpecialClassInfo($type)
    {
        $map = array(
            "id" => array("EQ",$type)
        );
        return M("article_special_class")->where($map)->find();
    }

    /**
     * 获取专题文章列表数量
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public function getArticleSpecialListCount($type)
    {
        $map = array(
            "type" => array("EQ",$type)
        );
        return M("article_special")->where($map)->count();
    }

    /**
     * 获取专题文章列表
     * @param  string $type [文章类型]
     * @return [type]        [description]
     */
    public function getArticleSpecialList($pageIndex,$pageCount,$type)
    {
        $map = array(
            "a.type" => array("EQ",$type)
        );

        return M("article_special")->where($map)->alias("a")
                                        ->order("a.id desc")
                                        ->join("join qz_article_special_class b on a.type = b.id")
                                        ->field("a.*,b.shortname")
                                        ->limit($pageIndex.",".$pageCount)->select();
    }

    /**
     * 获取专题模块信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getModuleInfo($id)
    {
        $map = array(
            "id" => array("EQ",$id),
            "isdelete" => array("EQ",1)
        );

        $buildSql = M("article_special_module")->where($map)->buildSql();

        return M("article_special_module")->table($buildSql)->alias("a")
                                   ->join("left join qz_article_special_module_class b on a.id = b.module")
                                   ->join("left join qz_article_special c on c.id = b.article_id")
                                   ->join("left join qz_article_special_class d on d.id = c.type")
                                   ->field("a.*,b.title as item_title,c.title article_title,c.id as article_id,d.shortname,d.classname,c.description as article_description,c.cover_img as thumb")
                                   ->select();
    }

    /**
     * [getArticleSpecialByMap 直接通过搜索条件获取推荐文章]
     * @param  [type] $map   [条件]
     * @param  [type] $limit [获取数量]
     * @return [type]        [description]
     */
    public function getArticleSpecialListByMap($map, $limit){
        $map['a.`status`'] = array("EQ",2);
        $result = M('article_special')->alias('a')
                                      ->field('a.id,a.title,c.classname,c.shortname')
                                      ->join('qz_article_special_class AS c ON c.id = a.type')
                                      ->join('left join qz_article_special_tag AS t ON t.article_id = a.id')
                                      ->where($map)
                                      ->order('RAND()')
                                      ->group('a.id')
                                      ->limit($limit)
                                      ->select();
        return $result;
    }

    /**
     * [getNearArticleSpecial 获取相邻资源]
     * @param  [type] $type  [类型]
     * @param  [type] $id    [id]
     * @param  [type] $extra [prev:上一个,next:下一个]
     * @return [type]        [description]
     */
    public function getNearArticleSpecial($type,$id,$extra)
    {
        if ($extra == 'prev') {
            $result = M('article_special')->alias('a')
                                          ->join('qz_article_special_class AS c ON c.id = a.type')
                                          ->where(['a.id' => array('LT',$id),'a.type'=>$type])
                                          ->field('a.id,a.title,c.classname,c.shortname')
                                          ->order('id DESC')
                                          ->find();
        }else{
            $result = M('article_special')->alias('a')
                                          ->join('qz_article_special_class AS c ON c.id = a.type')
                                          ->where(['a.id' => array('GT',$id),'a.type'=>$type])
                                          ->field('a.id,a.title,c.classname,c.shortname')
                                          ->order('id')
                                          ->find();
        }
        return $result;
    }

    /**
     * [editArticleSpecialPvById 更改点击量]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function editArticleSpecialPvById($id)
    {
        if(!empty($id)){
            return M('article_special')->where(array('id' => $id))->setInc('pv');
        }
        return false;
    }

    /**
     * [editArticleSpecialLikesById 更改点赞量]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function editArticleSpecialLikesById($id)
    {
        if(!empty($id)){
            return M('article_special')->where(array('id' => $id))->setInc('likes');
        }
        return false;
    }
}