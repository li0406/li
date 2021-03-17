<?php
/**
 *  首页文章评论  www_article_comment
 */
namespace Common\Model;
use Think\Model;
class WwwArticleCommentModel extends Model{
    /**
     * 添加文章评论
     */
    public function addComment($data){
        return M('www_article_comment')->add($data);
    }

    /**
     * 获取文章的评论列表的数量
     * @param  [type] $id [文章编号]
     * @return [type]     [description]
     */
    public function getCommentListCount($id){
        $map = array(
                "articleid"=>array("EQ",$id),
                "isverify"=>array("EQ",1)
                    );
        return M("www_article_comment")->where($map)->count();
    }
    /**
     * 获取文章的评论列表
     * @param  integer $pageCount [显示数量]
     * @param  string  $id    [文章编号]
     * @return [type]         [description]
     */
    public function getCommentList($pageCount = 10 ,$pageIndex, $id ='')
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        if(!empty($id)){
            $map["a.articleid"] = array("EQ",$id);
        }
        $map = array(
                "a.isverify"=>array("EQ",1)
                    );
        return M("www_article_comment")->where($map)->alias("a")
                                       ->join("INNER JOIN qz_www_article as b on a.articleid = b.id")
                                       ->join("INNER JOIN qz_www_article_class_rel as c on c.article_id = a.articleid")
                                       ->join("INNER JOIN qz_www_article_class as d on c.class_id = d.id ")
                                       ->field("a.*,b.title,d.shortname")
                                       ->order("time desc")
                                       ->limit($pageIndex.",".$pageCount)
                                       ->select();
    }
}