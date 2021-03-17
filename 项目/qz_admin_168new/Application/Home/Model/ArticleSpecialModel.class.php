<?php

namespace Home\Model;
Use Think\Model;

/**
*  专题文章表
*/

class ArticleSpecialModel extends Model
{
    protected $autoCheckFields = false;

    protected $_validate = array(
        array('title','require','请填写文章标题',1,"",1),
        array('type','require','请选择文章类型',1,"",1),
        array('content','require','请填写文章内容',1,"",1)
    );

    /**
     * 添加文章
     * @param [type] $data [description]
     */
    public function addArticle($data)
    {
        return M("article_special")->add($data);
    }

    /**
     * 编辑文章
     * @param [type] $data [description]
     */
    public function editArticle($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("article_special")->where($map)->save($data);
    }

    /**
     * 添加文章标签
     */
    public function addAllTag($data)
    {
       return M("article_special_tag")->addAll($data);
    }

    /**
     * 删除文章标签
     * @param  [type] $articleid [description]
     * @return [type]            [description]
     */
    public function delTag($articleid)
    {
        $map = array(
            "article_id" => array("EQ",$articleid)
        );
        return M("article_special_tag")->where($map)->delete();
    }

    /**
     * 获取文章类表数量
     * @param  [type] $content [description]
     * @return [type]          [description]
     */
    public function getArticleListCount($content, $status)
    {
        if (!empty($content)) {
            $map["title"] = array("LIKE","%$content%");
        }

        if (!empty($status)) {
            $map["status"] = array("EQ",$status);
        }

        return M("article_special")->where($map)->count();
    }

    /**
     * 获取文章列表
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getArticleList($pageIndex,$pageCount,$content, $status)
    {
        if (!empty($content)) {
            $map["a.title"] = array("LIKE","%$content%");
        }

        if (!empty($status)) {
            $map["status"] = array("EQ",$status);
        }


        $buildSql =  M("article_special")->where($map)->alias("a")
                            ->join("join qz_article_special_class as b on a.type = b.id")
                            ->field("a.*,b.classname,b.shortname")
                            ->order("status, id desc")
                            ->limit($pageIndex.",".$pageCount)->buildSql();
        return  M("article_special")->table($buildSql)->alias("t")
                                    ->join("left join qz_article_special_tag tag on tag.article_id = t.id")
                                    ->join("left join qz_tags c on c.id = tag.tag_id")
                                    ->field("t.*,GROUP_CONCAT(c.name) as tagnames")
                                    ->group("t.id")->order("id desc")->select();
    }


    /**
     * 查询文章信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function findArticleInfo($id)
    {
        $map = array(
            "t.id" => array("EQ",$id)
        );
        return M("article_special")->where($map)->alias("t")
                                   ->join("left join qz_article_special_tag tag on tag.article_id = t.id")
                                   ->join("left join qz_tags c on c.id = tag.tag_id")
                                   ->field("t.*,GROUP_CONCAT(c.id) tags, GROUP_CONCAT(c.name) as tagnames")
                                   ->find();
    }

    /**
     * 查询文章信息
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getarticle($str,$type,$limit)
    {
        if (!empty($str)) {
            $map["_complex"] = array(
                "title" =>  array("LIKE","%$str%"),
                "id" =>  array("LIKE","%$str%"),
                "_logic" => "OR"
            );
        }

        if (!empty($type)) {
            $map["type"] = array("EQ",$type);
        }
        return M("article_special")->where($map)
                                   ->field("id,title")->limit($limit)->select();
    }
}