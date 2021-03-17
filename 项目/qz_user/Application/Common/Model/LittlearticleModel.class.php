<?php
/**
 * 小站文章表
 */
namespace Common\Model;
use Think\Model;
class LittlearticleModel extends Model{
    protected $tableName = "little_article";

    /**
     * 获取小站文章信息
     * @param  [type] $id       [文章编号]
     * @param  [type] $category [文章类别]
     * @return [type]           [description]
     */
    public function getArticleInfo($id,$category,$cs){
        $map = array(
                "a.id"=>array("EQ",$id),
                "a.classid"=>array("EQ",$category),
                "a.state" =>array("EQ",2),
                "a.cs"=>array("EQ",$cs)
                     );
        $prevMap = array(
                "a.id"=>array("LT",$id),
                "a.classid"=>array("EQ",$category),
                "a.state" =>array("EQ",2),
                "a.cs"=>array("EQ",$cs)
                     );
        $nextMap = array(
                "a.id"=>array("GT",$id),
                "a.classid"=>array("EQ",$category),
                "a.state" =>array("EQ",2),
                "a.cs"=>array("EQ",$cs)
                     );
        //1.查询出文章信息
        $buildSql = M("little_article")->where($map)->alias("a")
                                       ->join("INNER JOIN qz_infotype as b on a.classid = b.id and b.type = 2")
                                       ->field("a.*,b.shortname,b.title as classtitle,b.description  as littledescription,b.keywords as classkeywords,b.name as classname,'now' as action ")
                                       ->buildSql();
        //2.查询出同类别的上一篇文章
        $prevSql =  M("little_article")->where($prevMap)->alias("a")
                                       ->join("INNER JOIN qz_infotype as b on a.classid = b.id  and b.type = 2")
                                       ->field("a.*,b.shortname,b.title as classtitle,b.description as littledescription,b.keywords as classkeywords,b.name as classname,'prv' as action")
                                       ->order("a.id desc")
                                       ->limit(1)
                                       ->buildSql();
        //3.查询出同类别的下一篇文章
        $nextSql =  M("little_article")->where($nextMap)->alias("a")
                                       ->join("INNER JOIN qz_infotype as b on a.classid = b.id  and b.type = 2")
                                       ->field("a.*,b.shortname,b.title as classtitle,b.description  as littledescription,b.keywords as classkeywords,b.name as classname,'next' as action")
                                       ->limit(1)
                                       ->buildSql();
        //4.查询出文章信息
        return  M("little_article")->table($buildSql)->alias("t")
                                       ->union($prevSql,true)
                                       ->union($nextSql,true)
                                       ->select();
    }

   /**
    * [getTypeInfoByshortname description]
    * @param  [type] $limit    [description]
    * @param  [type] $id       [description]
    * @param  [type] $category [description]
    * @return [type]           [description]
    */
    public function getTopArticleInfo($limit,$id,$category,$cs){
        $map = array(
                "a.id"=>array("NEQ",$id),
                "a.classid"=>array("EQ",$category),
                "a.istop"=>array("EQ",1),
                "a.cs"=>array("EQ",$cs)
                     );
        return M("little_article")->where($map)->alias("a")
                                  ->join("INNER JOIN qz_infotype as b on b.id = a.classid")
                                  ->field("a.*,b.shortname")
                                  ->limit($limit)->order("id desc")->select();
    }


   /**
     * 获取点击量最高的文章列表
     * @param  [type] $classid [文章类型]
     * @param  [type] $limit   [description]
     * @return [type]          [description]
     */
    public function getRecommendArticles($classid,$limit,$cs){
        $map = array(
                "a.state" => array("EQ",2),
                "c.enabled"=>array("EQ",1),
                "a.cs"=>array("EQ",$cs)
                     );
        if(!empty($classid)){
            $map["c.id"] = array("EQ",$classid);
        }
        //1.获取点击率最高的文章
        return M("little_article")->where($map)->alias("a")
                         ->join("INNER JOIN qz_infotype as c on c.id = a.classid")
                         ->order("a.pv desc")
                         ->field("a.title,a.id,c.shortname")
                         ->limit($limit)
                         ->select();
    }

    /**
     * 文章阅读量更新
     * @return [type] [description]
     */
    public function updatePv($id){
        $map = array(
                    "id"=>$id
                          );
        M("little_article")->where($map)->setInc("pv",1);
    }

    /**
     * 根据文章编号获取文章分类
     * @param  [type] $id [文章编号]
     * @return [type] [description]
     */
    public function getInfoType($id){
        $map = array(
                "a.id"=>array("EQ",$id)
                     );
        return M("little_article")->where($map)->alias("a")
                                  ->join("INNER JOIN qz_infotype as b on b.id = a.classid")
                                  ->field("b.shortname")
                                  ->find();
    }

    /**
     * 获取文章列表的数量
     * @param  [type] $classid [文章类别]
     * @param  [type] $keyword [搜索关键字]
     * @return [type]          [description]
     */
    public function getArticleListCount($classid="",$cs,$keyword=""){
        $map = array(
                "cs"=>array("EQ",$cs),
                "state"=>array("EQ",2)
                     );
        if(!empty($classid)){
            $map["classid"] = array("EQ",$classid);
        }
        if(!empty($keyword)){
            $map["title"] = array("LIKE","%$keyword%");
        }
        return M("little_article")->where($map)->count();
    }

   /**
    * 获取文章列表
    * @param  string $classid   [文章类别]
    * @param  [type] $pageIndex [description]
    * @param  [type] $pageCount [description]
    * @param  string $keyword   [搜索关键字]
    * @return [type]            [description]
    */
    public function getArticleList($classid="",$cs,$pageIndex,$pageCount,$keyword="")
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $map = array(
                "a.cs"=>array("EQ",$cs),
                "a.state"=>array("EQ",2)
                     );
        if(!empty($classid)){
            $map["a.classid"] = array("EQ",$classid);
        }
        if(!empty($keyword)){
            $map["a.title"] = array("LIKE","%$keyword%");
        }

        return M("little_article")->where($map)->alias("a")
                                  ->join("INNER JOIN qz_infotype as b on b.id = a.classid")
                                  ->field("a.*,b.shortname,b.title as classtitle,b.description  as littledescription,b.keywords as classkeywords,b.name as classname")
                                  ->order("id desc")
                                  ->limit($pageIndex.",".$pageCount)
                                  ->select();
    }
}