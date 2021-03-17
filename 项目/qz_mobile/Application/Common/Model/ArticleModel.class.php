<?php
/**
 *  设计师文章表 qz_article
 */
namespace Common\Model;
use Think\Model;
class ArticleModel extends Model{
    protected $autoCheckFields = false; //设置autoCheckFields属性为false后，就会关闭字段信息的自动检测，因为ThinkPHP采用的是惰性数据库连接，只要你不进行数据库查询操作，是不会连接数据库的。

    /**
     * 获取设计师发布的文章数量
     * @param  [type] $id [description]
     * @param  [type] $cs [description]
     * @return [type]     [description]
     */
    public function getArticleListCount($id,$cs){
        $map = array(
                "a.id"=>array("EQ",$id),
                "a.cs"=>array("EQ",$cs)
                     );
        return M("user")->where($map)->alias("a")
                        ->join("INNER JOIN qz_article as b on a.id = b.userid")
                        ->count();
    }
    /**
     * 获取设计师发布的文章列表
     * @param  [type] $id [设计师编号]
     * @param  [type] $cs [所在城市]
     * @return [type]     [description]
     */
    public function getArticleList($id,$cs,$pageIndex,$pageCount)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $map = array(
                "a.id"=>array("EQ",$id),
                "a.cs"=>array("EQ",$cs)
                     );
        return M("user")->where($map)->alias("a")
                        ->join("INNER JOIN qz_article as b on a.id = b.userid")
                        ->field("b.*")->limit($pageIndex.",".$pageCount)
                        ->select();
    }

    /**
     *  获取设计师的文章详情
     * @param  [type] $id [文章编号]
     * @param  [type] $cs [城市信息]
     * @return [type]     [description]
     */
    public function getArticleInfo($id,$cs){
        $map = array(
                "a.id"=>array("EQ",$id),
                "b.cs"=>array("EQ",$cs)
                     );
        $prvMap = array(
                "a.id"=>array("LT",$id),
                 "b.cs"=>array("EQ",$cs),
                 "_string"=>"b.id = @uid"
                        );
        $nextMap = array(
                "a.id"=>array("GT",$id),
                 "b.cs"=>array("EQ",$cs),
                 "_string"=>"b.id = @uid"
                        );

        //1.查询出当前的文章
        $buildSql = M("article")->where($map)->alias("a")
                           ->join("inner join qz_user as b on b.id = a.userid")
                           ->field("a.*,b.name,@uid:=b.id as bid,'now' as action")
                           ->buildSql();
        //2.查询出上一篇文章
        $preSql =  M("article")->where($prvMap)->alias("a")
                               ->join("inner join qz_user as b on b.id = a.userid")
                               ->order("a.id desc")
                               ->field("a.*,b.name,b.id as bid,'prv' as action")
                               ->limit(1)
                               ->buildSql();

        //3.查询出下一篇文章
        $nextSql =  M("article")->where($nextMap)->alias("a")
                               ->join("inner join qz_user as b on b.id = a.userid")
                               ->order("a.id desc")
                               ->field("a.*,b.name,b.id as bid,'next' as action")
                               ->limit(1)
                               ->buildSql();

        return M("article")->table($buildSql)->alias("t")
                           ->union($preSql,true)
                           ->union($nextSql,true)
                           ->select();
    }

    /**
     * 查询文章及设计师信息
     * @return [type] [description]
     */
    public function getSingleArticle($id){
        $map = array(
                "a.id"=>array("EQ",$id)
                     );
        return M("article")->where($map)->alias("a")
                           ->join("INNER JOIN  qz_user as b on a.userid = b.id")
                           ->join("INNER JOIN qz_quyu as c on c.cid = b.cs")
                           ->field("a.*,c.bm")
                           ->find();
    }

    //根据传入的Tag对文章标题模糊查询
    public function getArticleByTag($tag,$num){
        $map['a.title'] = array('like','%'.$tag.'%');
        return M("www_article")->alias("a")
                                ->where($map)
                                ->join("inner join qz_www_article_class_rel as r on r.article_id = a.id")
                                ->join("inner join qz_www_article_class as c on c.id = r.class_id")
                                ->field("a.id,a.title,a.addtime,c.shortname,c.classname")
                                ->order("rand()")
                                ->limit('0,'.$num)
                                ->select();
    }

    //取随机文章
    public function getRandArticle($num){
        $map['a.state'] = array('eq','2');
        return M("www_article")->alias("a")
                ->where($map)
                ->join("inner join qz_www_article_class_rel as r on r.article_id = a.id")
                ->join("inner join qz_www_article_class as c on c.id = r.class_id")
                ->field("a.id,a.title,a.addtime,c.shortname,c.classname")
                ->order("rand()")
                ->limit('0,'.$num)
                ->select();
    }

    //取随机文章
    public function getArticle($num){
        $map['a.state'] = array('eq','2');
        return M("www_article")->alias("a")
                ->where($map)
                ->join("inner join qz_www_article_class_rel as r on r.article_id = a.id")
                ->join("inner join qz_www_article_class as c on c.id = r.class_id")
                ->field("a.id,a.title,a.addtime,a.face,c.shortname,c.classname")
                ->order("addtime DESC ")
                ->limit('0,'.$num)
                ->select();
    }

}
