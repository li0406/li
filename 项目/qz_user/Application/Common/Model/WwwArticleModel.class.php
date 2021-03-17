<?php
/**
 * 后台管理的文章 www_article
 */
namespace Common\Model;
use Think\Model;
class WwwArticleModel extends Model{
    /**
     * 获取总站的文章信息
     * @param  string $type  [文章类型]
     * @param  [type] $limit [获取数量]
     * @return [type]        [description]
     */
    public function getIndexArticles($type='',$limit = 1,$istop='0'){
        $map = array(
          "a.state" => array("EQ",2),
          "c.is_new"=>array("EQ",1),
          "a.isTop"=>array("EQ",$istop)
        );
        if(!empty($type)){
            $map["b.class_id"] = array("IN",$type);
        }
        $buildSql = M("www_article")->alias("a")->where($map)
                        ->join("inner join qz_www_article_class_rel as b on b.article_id = a.id")
                        ->join("inner join qz_www_article_class as c on c.id = b.class_id")
                        ->field("a.*,c.classname,c.shortname")
                        ->buildSql();
        return M("www_article")->table($buildSql)->alias("t")
                               ->order("t.addtime desc,t.isTop desc")
                               ->group("id")
                               ->limit($limit)
                               ->select();
    }

    /**
     * 根据编号获取文章信息
     * @param  [type] $id [文章编号]
     * @return [type]     [description]
     */
    public function getArticleInfoById($id,$category){
        $map = array(
                "a.id"=>array("EQ",$id),
                "b.class_id"=>array("EQ",$category)
                     );
        $prvMap = array(
                "a1.id"=>array("LT",$id),
                "_string"=>"c1.id = @cid",
                "b1.class_id"=>array("EQ",$category)
                        );
        $nextMap = array(
                "a1.id"=>array("GT",$id),
                "_string"=>"c1.id = @cid",
                "b1.class_id"=>array("EQ",$category)
                        );

        //上一篇文章
        $preSql  = D("www_article")->where($prvMap)->alias("a1")
                                   ->join("inner join qz_www_article_class_rel as b1 on b1.article_id = a1.id")
                                   ->join("inner join qz_www_article_class as c1 on c1.id = b1.class_id")
                                   ->order("a1.id desc")
                                   ->field("'prv' as action, a1.*, c1.id as cid,c1.classname,c1.shortname")
                                   ->limit(1)->buildSql();
        //下一篇文章
        $nextSql = D("www_article")->where($nextMap)->alias("a1")
                                   ->field("'next' as action, a1.*, c1.id as cid,c1.classname,c1.shortname")
                                   ->join("inner join qz_www_article_class_rel as b1 on b1.article_id = a1.id")
                                   ->join("inner join qz_www_article_class as c1 on c1.id = b1.class_id")
                                   ->limit(1)->buildSql();

         //1.查询当前文章信息及上下文章的信息
         return D("www_article")->where($map)->alias("a")
                                     ->join("inner join qz_www_article_class_rel as b on b.article_id = a.id")
                                     ->join("inner join qz_www_article_class as c on c.id = b.class_id")
                                     ->union($preSql,true)
                                     ->union($nextSql,true)
                                     ->field("'now' as action, a.*, @cid :=c.id as cid,c.classname,c.shortname")
                                     ->select();

    }

    /**
     * 获取相同类型的文章推荐
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getTopArticleInfo($limit = 10,$id=''){
        if(!empty($id)){
          $buildSql = M("www_article")->where(array("a.id"=>array("EQ",$id)))->alias("a")
                                      ->join("inner join qz_www_article_class_rel as b on b.article_id = a.id")
                                      ->field("b.class_id")->buildSql();
          $map["_string"] = "b.class_id In".$buildSql;
        }
        $map["c.is_new"] = array("EQ",1);
        $map["a.isTop"] = array("EQ",1);
        return M("www_article")->alias("a")->where($map)
                               ->join("inner join qz_www_article_class_rel as b on b.article_id = a.id")
                               ->join("inner join qz_www_article_class as c on c.id = b.class_id")
                               ->field("a.*,c.shortname")
                               ->order("isTop desc,addtime desc")
                               ->group("a.id")
                               ->limit($limit)
                               ->select();
    }

    /**
     * 获取咨询业轮播列表
     * @return [type] [description]
     */
    public function getAdvList($limit = 10){
        $map = array(
          "a.isTop"=>array("EQ",1),
          "a.state" => array("EQ",2),
          "c.is_new"=>array("EQ",1),
          "c.obsolete"=>array("EQ",0)
        );
        $buildSql = M("www_article")->where($map)->alias("a")
                               ->join("INNER JOIN qz_www_article_class_rel as b on b.article_id = a.id")
                               ->join("INNER JOIN qz_www_article_class as c on c.id = b.class_id")
                               ->field("a.*,c.shortname")->group("a.id")->buildSql();
        return  M("www_article")->table($buildSql)->alias("t")
                                ->order('t.addtime desc')->limit($limit)
                                ->select();
    }

   /**
    * 根据文章类型编号查询文章列表
    * @param  [type] $ids [编号数组]
    * @param  [type] $istop [是否推荐]
    * @return [type]      [description]
    */
    public function getArticleListByIds($ids='',$pageIndex = 0,$pageCount = 10,$keyword='',$isTop = false)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

          if(is_array($ids)){
            //生成查询语句
            foreach ($ids as $key => $value) {
                $map = array(
                        "c.id"=>array("IN",$value),
                        "a.state" => array("EQ",2)
                             );
                if($isTop){
                    $map["a.isTop"] = array("EQ",1);
                }

                if(!empty($keyword)){
                    $map["a.title"] = array("LIKE","%$keyword%");
                }

                if($key  != 0){
                    $buildSql = M("www_article")->where($map)->alias("a")
                               ->join("INNER JOIN qz_www_article_class_rel as b on b.article_id = a.id")
                               ->join("INNER JOIN qz_www_article_class as c on c.id = b.class_id")
                               ->join("LEFT JOIN qz_www_article_class as d on d.id = c.pid")
                               ->join("LEFT JOIN qz_www_article_class as e on e.id = d.pid")
                               ->order("cid desc")
                               ->field("a.*,c.id as cid,c.classname as cname,c.shortname,e.classname as firstname,e.id as firsetid,d.classname as secondname,d.id as secondid,d.shortname as secondshortname")->buildSql();

                    $sql = M("www_article")->table($buildSql)->group("t".$key.".id")
                                           ->limit($pageIndex.",".$pageCount)
                                           ->order("addtime desc")
                                           ->alias("t".$key)->buildSql();
                    $sqls[] = $sql;
                }else{
                    $buildSql1 = M("www_article") ->where($map)->alias("a")
                       ->join("INNER JOIN qz_www_article_class_rel as b on b.article_id = a.id")
                       ->join("INNER JOIN qz_www_article_class as c on c.id = b.class_id")
                       ->join("LEFT JOIN qz_www_article_class as d on d.id = c.pid")
                       ->join("LEFT JOIN qz_www_article_class as e on e.id = d.pid")
                       ->order("cid desc")
                       ->field("a.*,c.id as cid,c.classname as cname,c.shortname,e.classname as firstname,e.id as firsetid,d.classname as secondname,d.id as secondid,d.shortname as secondshortname")->buildSql();
                    $buildSql1 =   M("www_article")->table($buildSql1)->group("t.id")
                                                   ->alias("t")->limit($pageIndex.",".$pageCount)
                                                   ->order("addtime desc")
                                                   ->buildSql();
                }
            }
            M("www_article")->table($buildSql1)->alias("t");
            foreach ($sqls as $key => $value) {
                 M("www_article")->union($value,true);
            }
            $buildSql = M("www_article")->buildSql();
            return M("www_article")->table($buildSql)->alias("t")->select();
        }
        return null;
    }

    /**
     * 获取文章数量
     * @return [type] [description]
     */
    public function getArticleListCount($ids='',$keyword =""){
        $map = array(
            "a.state" => array("EQ",2)
                 );
        if(!empty($ids)){
            $map["c.id"] = array("IN",$ids);
        }
        if(!empty($keyword)){
            $map["a.title"] = array("LIKE","%$keyword%");
        }
        $buildSql = M("www_article") ->where($map)->alias("a")
                   ->join("INNER JOIN qz_www_article_class_rel as b on b.article_id = a.id")
                   ->join("INNER JOIN qz_www_article_class as c on c.id = b.class_id")
                   ->field("a.id")
                   ->group("a.id")
                   ->buildSql();
         return M("www_article")->table($buildSql)->alias("t")
                   ->count();
    }

    /**
     * 文章阅读量更新
     * @return [type] [description]
     */
    public function updatePv($id){
        $map = array(
                    "id"=>$id
                          );
        M("www_article")->where($map)->setInc("pv",1);
    }

    /**
     * 获取热门的文章
     * @return [type] [description]
     */
    public function getHotArticle($limit){
        $map = array(
                "a.isTop"=>array("EQ",1),
                "a.state" => array("EQ",2),
                "c.is_new"=>array("EQ",1),
                "c.obsolete"=>array("EQ",0)
                     );
        return M("www_article")->where($map)->alias("a")
                               ->join("INNER JOIN qz_www_article_class_rel as b on b.article_id = a.id")
                               ->join("INNER JOIN qz_www_article_class as c on c.id = b.class_id")
                               ->field("a.*,c.shortname")
                               ->order("a.pv desc")
                               ->limit($limit)
                               ->select();

    }

    /**
     * 获取点击量最高的文章列表
     * @param  [type] $classid [文章类型]
     * @param  [type] $limit   [description]
     * @return [type]          [description]
     */
    public function getRecommendArticles($classid,$limit = 20){
        $map = array(
                "a.state" => array("EQ",2),
                "c.is_new"=>array("EQ",1),
                "c.obsolete"=>array("EQ",0)
                     );
        if(!empty($classid)){
            $map["c.id"] = array("EQ",$classid);
        }

        if(!empty($limit)){
           M("www_article")->limit($limit);
        }

        //1.获取点击率最高的文章
        return M("www_article")->where($map)->alias("a")
                         ->join("INNER JOIN qz_www_article_class_rel as b on b.article_id = a.id")
                         ->join("INNER JOIN qz_www_article_class as c on c.id = b.class_id")
                         ->order("a.pv desc")
                         ->field("a.title,a.id,c.shortname")
                         ->limit('0,'.$limit)
                         ->select();
    }

    /**
     * 移动端文章详细信息
     * @param  [type] $id       [文章编号]
     * @param  [type] $category [文章类别]
     * @return [type]           [description]
     */
    public function getMobileArticleInfoById($id,$category){
        $map = array(
                "a.id"=>array("EQ",$id),
                "c.id"=>array("EQ",$category),
                "c.obsolete"=>array("EQ",0)
                     );
        return M("www_article")->where($map)->alias("a")
                                    ->join("INNER JOIN qz_www_article_class_rel as b on a.id = b.article_id")
                                    ->join("INNER JOIN qz_www_article_class as c on c.id = b.class_id")
                                    ->field("a.*,c.shortname,c.classname,c.description,c.keywords,c.title AS articletitile")
                                    ->select();
    }




    //查询文章列表  根据分类
    public function getListByCid($where,$orderBy,$row=10){
        $map['a.state'] = array("EQ",'2');

        if(!empty($where['pid'])){
            $map['c.pid'] = array("EQ",$where['pid']);
        }
        if(!empty($where['isTop'])){
            $map['a.isTop'] = array("EQ",$where['isTop']);
        }
        $Db = M('www_article');
        $result = $Db->alias("a")
                        ->join("left join qz_www_article_class_rel as r on r.article_id = a.id")
                        ->join("left join qz_www_article_class as c on c.id = r.class_id")
                        ->field("a.id,a.title,a.content,a.face,imgs,c.shortname,c.classname")
                        ->order($orderBy)
                        ->limit("0,".$row)
                        ->where($map)
                        ->select();
        return $result;
    }

}