<?php

/**
 * 首页需要用到的文章Model
 */

namespace Common\Model;

use Think\Model;

class ArticleModel extends Model
{

    protected $autoCheckFields = false;
    const ARTICLESHOW = 1; //未审核
    const ARTICLEHIDE = 2; //发布
    const ARTICLEDEL = -1; //删除

    public function getArticleList($map, $order, $page, $pageSize)
    {
        $map['a.state'] = array('eq', self::ARTICLEHIDE);
        return M('www_article')->alias('a')
            ->where($map)
            ->field('a.id,a.title,a.face,a.pv as realview,a.addtime,r.class_id')
            ->join('LEFT JOIN qz_www_article_class_rel as r on r.article_id = a.id')
            ->order($order . ' desc')
            ->page($page, $pageSize)
            ->select();
    }

    public function getArticleById($id)
    {
        $map = array(
            "a.id" => array("EQ",$id)
        );
        return M('www_article')->alias('a')
            ->field('a.id,a.title,a.content,a.face,a.likes,a.realview,a.pv,a.addtime,a.tags,a.keywords,c.shortname,c.id as category_id')
            ->join("INNER JOIN qz_www_article_class_rel as b on a.id = b.article_id")
            ->join("INNER JOIN qz_www_article_class as c on c.id = b.class_id")
            ->where($map)->find();
    }

    public function getMobileArticleInfoById($id)
    {
        $map = array(
            "a.id" => array("EQ",$id)
        );
        return M('www_article')->alias('a')
            ->field('a.id,a.title,a.content,a.face,a.likes,a.realview,a.pv,a.addtime')
            ->where( $map )->find();
    }

    public function changeArticleLike($id,$data)
    {
        $map = array(
            "a.id" => array("EQ",$id)
        );
        return M('www_article')->alias('a')
            ->where($map)
            ->save($data);
    }

    public  function getArticleClassIdsByClass($groupid)
    {
        $result = array();
        if (empty($groupid)){
            return  $result;
        }

        $map = array();
        $map["is_new"] = array("EQ",1);
        $map["_complex"] = array(
            "pid"=>array("IN",$groupid),
            "id"=>array("IN",$groupid),
            "_logic"=>"OR"
        );
        $sub = M("www_article_class")->field('id,pid')->where($map)->select();
        if(count($sub) > 0){
            foreach ($sub as $key => $value) {
                $ids[] = $value["id"];
            }
            $submap = array();
            $submap["is_new"] = array("EQ",1);
            $submap["_complex"] = array(
                "pid"=>array("IN",$ids),
                "id"=>array("IN",$ids),
                "_logic"=>"OR"
            );

            $result = M("www_article_class")->field('id,pid')->where($submap)->select();
        }
        return  $result;
    }
}