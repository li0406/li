<?php

namespace Home\Model;

use Think\Model;

/**
*   主站文章表
*/
class WwwArticleModel extends Model
{
    /**
     * [addWwwArticle 新增文章]
     * @param string $value [description]
     */
    public function addWwwArticle($class, $data)
    {
        $result = M('www_article')->add($data);
        if($result){
            //关联分类
            M('www_article_class_rel')->add(array('article_id' => $result, 'class_id' => $class));
        }
        return $result;
    }

    /**
     * [editWwwArticle 编辑文章]
     * @param  [type] $id   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function editWwwArticle($id, $class, $data)
    {
        if(empty($id)){
            return false;
        }
        $result = M('www_article')->where(array('id' => $id))->save($data);
        if($result){
            //删除原有分类
            M('www_article_class_rel')->where(array('article_id' => $id))->delete();
            //关联分类
            M('www_article_class_rel')->add(array('article_id' => $id, 'class_id' => $class));
        }
        return $result;
    }

    /**
     * 编辑文章
     * @param  integer $id   文章ID
     * @param  array   $save 保存的内容
     */
    public function editWwwArticleById($id = 0, $save = array())
    {
        if (empty($id)) {
            return false;
        }
        return M('www_article')->where(array('id' => $id))->save($save);
    }

    /**
     * [editWwwArticleIsTopById 通过文章ID设置文章是否推荐]
     * @param  [type] $id    [description]
     * @param  [type] $istop [description]
     * @return [type]        [description]
     */
    public function editWwwArticleIsTopById($id, $istop)
    {
        if(empty($id)){
            return false;
        }
        $result = M('www_article')->where(array('id' => $id))->save(array('isTop' => $istop));
        return $result;
    }

    /**
     * [editWwwArticleStateById 通过文章ID设置文章是否推荐]
     * @param  [type] $id    [description]
     * @param  [type] $state [description]
     * @return [type]        [description]
     */
    public function editWwwArticleStateById($id, $state)
    {
        if(empty($id)){
            return false;
        }
        $result = M('www_article')->where(array('id' => $id))->save(array('state' => $state));
        return $result;
    }

    /**
     * [editWwwArticleRecommendById 通过文章ID设置文章是否列表页推荐]
     * @param  [type] $id        [文章id]
     * @param  [type] $recommend [推荐]
     * @return [type]            [description]
     */
    public function editWwwArticleRecommendById($id, $recommend){
        if(empty($id)){
            return false;
        }
        $result = M('www_article')->where(array('id' => $id))->save(array('recommend' => $recommend));
        return $result;
    }

    public function getWwwArticleByTitle($title,$id = '')
    {
        $where = [
            'title' => ['eq', $title]
        ];
        if (!empty($id)) {
            $where['id'] = ['neq', $id];
        }
        $result = M('www_article')->where($where)->field("id")->find();
        return $result;
    }


    /**
     * [getWwwArticleCount 获取文章数量]
     * @param  array  $map [description]
     * @return [type]      [description]
     */
    public function getWwwArticleCount($map = [])
    {
        if(empty($map['a.state'])){
            $map['a.state'] = array('NEQ','-1');
        }
        $result = M('www_article')->alias('a')
                                  ->join('INNER JOIN qz_www_article_class_rel as r on r.article_id = a.id')
                                  ->join('INNER JOIN qz_www_article_class as c on c.id = r.class_id')
                                  ->where($map)
                                  ->count();
        return $result;
    }


    /**
     * [getTotalNum 获取文章总点击量]
     * @param  array  $map [description]
     * @return [type]      [description]
     */
    public function getTotalNum($map = [])
    {
        if(empty($map['a.state'])){
            $map['a.state'] = array('NEQ','-1');
        }
        unset($map['order']);
        $result = M('www_article')->alias('a')
                                  ->join('INNER JOIN qz_www_article_class_rel as r on r.article_id = a.id')
                                  ->join('INNER JOIN qz_www_article_class as c on c.id = r.class_id')
                                  ->where($map)
                                  ->field('sum(a.realview) AS totalnum')
                                  ->select();
        return $result;
    }

    /**
     *
     *  获取文章搜索引擎自然搜索IP
     *
     * @param array $map
     *
     * @return array|false|mixed|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTotalSearchIP($map = [])
    {
        if(empty($map['a.state'])){
            $map['a.state'] = array('NEQ','-1');
        }
        unset($map['order']);
        $result = M('www_article')->alias('a')
            ->join('INNER JOIN qz_www_article_class_rel as r on r.article_id = a.id')
            ->join('INNER JOIN qz_www_article_class as c on c.id = r.class_id')
            ->where($map)
            ->field('sum(a.search_ip) AS totalnum')
            ->select();
        return $result;
    }

    /**
     * [getWwwArticleList 获取文章列表]
     * @param  array  $map   [description]
     * @param  [type] $start [description]
     * @param  [type] $each  [description]
     * @param  string $order [description]
     * @return [type]        [description]
     */
    public function getWwwArticleList($map = [],$start,$each,$order = 'a.id DESC')
    {
        if(empty($map['a.state'])){
            $map['a.state'] = array('NEQ','-1');
        }
        $buidsql = M('www_article')->alias('a')
                                  ->field('a.id,a.userid,a.state,a.keywords,a.title,a.tags,a.isTop,a.recommend,a.createtime,a.addtime,r.class_id,a.realview,a.from_ruanwen')
                                  ->join('LEFT JOIN qz_www_article_class_rel as r on r.article_id = a.id')
                                  ->where($map)
                                  ->limit($start .','. $each)
                                  ->order($order)
                                  ->buildSql();

        $result = M()->table($buidsql)->alias('b')
                                      ->field('b.*,u.name AS uname,c.shortname,c.classname,GROUP_CONCAT(t.name) as tagname')
                                      ->join('LEFT JOIN qz_www_article_class as c on c.id = b.class_id ')
                                      ->join('LEFT JOIN qz_tags as t on FIND_IN_SET(t.id,b.tags)')
                                      ->join('LEFT JOIN qz_adminuser as u on u.id = b.userid')
                                      ->group('b.id')
                                      ->order('b.id DESC')
                                      ->select();
        return $result;
    }

    public function getWwwArticleById($id='')
    {
        if(empty($id)){
            return '';
        }
        $map = array('a.id' => $id);
        $result = M('www_article')->alias('a')
                                  ->field('a.*,c.shortname,c.classname,c.id AS classid')
                                  ->join('LEFT JOIN qz_www_article_class_rel as r on r.article_id = a.id')
                                  ->join('LEFT JOIN qz_www_article_class as c on c.id = r.class_id ')
                                  ->where($map)
                                  ->group('a.id')
                                  ->select();

        foreach ($result as $key => $value) {
            $tags = $value["tags"];
            //查询标签的信息
            $map = array(
                    "_string"=>"FIND_IN_SET(id,'$tags')"
                         );
            $tagData = M("tags")->where($map)
                                ->field("GROUP_CONCAT(name) as tagname")
                                ->find();
            if(count($tagData) > 0){
                $result[$key]["tagname"] = array_filter(explode(',', $tagData["tagname"]));
            }
        }
        return $result[0];
    }

    /**
     * 根据文章标题获取文章列表
     * @param  [type] $title [description]
     * @return [type]        [description]
     */
    public function getArticleListByTitle($title,$limit = 10)
    {
        $map = array(
            "title" => array("LIKE","%$title%"),
            "id" => array("GT",206)
        );

        return M("www_article")->where($map)->field("id,title")->limit($limit)->select();
    }

    /**
     * 获取主站文章统计数量
     * @return [type] [description]
     */
    public function getArticleStat()
    {
        $sql = 'select
                t.*,d.article_id
                from (
                    select a.id,a.classname, b.id as second_id,b.classname as second_name, c.id as third_id , c.classname as third_name from  qz_www_article_class a
                    join qz_www_article_class b on b.pid = a.id and b.obsolete = 0
                    left join qz_www_article_class c on c.pid = b.id and c.obsolete = 0
                    where a.is_new = 1 and a.pid = 0 and a.obsolete = 0 order by a.id
                ) t
                left join qz_www_article_class_rel d on (d.class_id = t.second_id and t.third_id is null)  or (d.class_id = t.third_id)
                join qz_www_article e on e.id = d.article_id and e.state <> -1';

        return M()->query($sql);
    }

    public function getAllArticle($pageIndex,$pageCount)
    {
        $map = array(
            "a.state" => array("EQ",2),
            "a.id" => array("GT",206)
        );
        return  M("www_article")->where($map)->alias("a")
                        ->join("join qz_www_article_class_rel b on a.id = b.article_id")
                        ->join("join qz_www_article_class c on c.id = b.class_id")
                        ->field("a.id,c.shortname,a.title,a.subtitle")->order("a.id")
                        ->limit($pageIndex,$pageCount)
                        ->select();
    }

    public function getAllArticleCount()
    {
        $map = array(
            "a.state" => array("EQ",2),
            "a.id" => array("GT",206)
        );
        $buildSql =  M("www_article")->where($map)->alias("a")
            ->join("join qz_www_article_class_rel b on a.id = b.article_id")
            ->join("join qz_www_article_class c on c.id = b.class_id")->field("a.id")
            ->buildSql();
        return M("www_article")->table($buildSql)->alias("t")
            ->count();
    }

    public function getArticleListByTime($start,$end)
    {
        $map = array(
            "createtime" => array(
                array("EGT",$start),
                array("ELT",$end),
            )
        );
        return M("www_article")->where($map)->field("id,title")->select();
    }


    // feature-702
    /**
     * [getWwwArticleCount 获取文章数量]
     * @param  array  $map [description]
     * @return [type]      [description]
     */
    public function getWwwArticleCountV2($map = [])
    {
        if(empty($map['a.state'])){
            $map['a.state'] = array('NEQ','-1');
        }
        $result = M('www_article')->alias('a')
            ->join('INNER JOIN qz_www_article_class_rel as r on r.article_id = a.id')
            ->join('INNER JOIN qz_www_article_class as c on c.id = r.class_id')
            ->join('LEFT JOIN  qz_adminuser as u on u.id = a.opid')
            ->where($map)
            ->count();
        return $result;
    }

    /**
     * [getWwwArticleList 获取文章列表]
     * @param  array  $map   [description]
     * @param  [type] $start [description]
     * @param  [type] $each  [description]
     * @param  string $order [description]
     * @return [type]        [description]
     */
    public function getWwwArticleListV2($map = [],$start,$each,$order = 'a.id DESC')
    {
        if(empty($map['a.state'])){
            $map['a.state'] = array('NEQ','-1');
        }
        $buidsql = M('www_article')->alias('a')
            ->field('a.id,a.userid,a.state,a.keywords,a.title,a.tags,a.isTop,a.recommend,a.createtime,a.addtime,r.class_id,a.realview,a.from_ruanwen,u.name AS uname')
            ->join('LEFT JOIN qz_www_article_class_rel as r on r.article_id = a.id')
            ->join('LEFT JOIN  qz_adminuser as u on u.id = a.opid')
            ->where($map)
            ->limit($start .','. $each)
            ->order($order)
            ->buildSql();

        $result = M()->table($buidsql)->alias('b')
            ->field('b.*,c.shortname,c.classname,GROUP_CONCAT(t.name) as tagname')
            ->join('LEFT JOIN qz_www_article_class as c on c.id = b.class_id ')
            ->join('LEFT JOIN qz_tags as t on FIND_IN_SET(t.id,b.tags)')
            ->group('b.id')
            ->order('b.id DESC')
            ->select();
        return $result;
    }

}