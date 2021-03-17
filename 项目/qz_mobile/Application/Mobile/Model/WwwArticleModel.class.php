<?php
/**
 * 后台管理的文章 www_article
 */

namespace Mobile\Model;

use Think\Model;

class WwwArticleModel extends Model
{
    protected $autoCheckFields = false;

    /**
     * 获取总站的文章信息
     * @param  string $type [文章类型]
     * @param  [type] $limit [获取数量]
     * @return [type]        [description]
     */
    public function getIndexArticles($type = '', $limit = 1, $istop = '0')
    {
        $map = array(
            "a.state" => array("EQ", 2),
            "c.is_new" => array("EQ", 1)
        );

        if ($istop !== '') {
            $map['a.isTop'] = array("EQ", $istop);
        }

        if (!empty($type)) {
            $map["rel.class_id"] = array("IN", $type);
        }

        return M("www_article_class_rel")->alias("rel")->where($map)
            ->join("join qz_www_article a on a.id = rel.article_id")
            ->join("join qz_www_article_class c on c.id = rel.class_id")
            ->field("a.id,a.title,a.addtime,a.isTop,c.classname,c.shortname")
            ->order("a.addtime DESC,a.isTop DESC")
            ->limit($limit)
            ->select();
    }

    /**
     * 获取文章简略信息
     * @param  integer $id 文章ID
     * @return array|bool
     */
    public function getArticleBriefById($id = 0)
    {
        if (empty($id)) {
            return false;
        }
        $map = array(
            'a.id' => intval($id),
            'c.obsolete' => 0
        );
        return M("www_article")->where($map)
            ->alias("a")
            ->join('INNER JOIN qz_www_article_class_rel as b on a.id = b.article_id')
            ->join("INNER JOIN qz_www_article_class as c on c.id = b.class_id")
            ->field("a.id,a.title,a.face,a.subtitle,c.shortname")
            ->group('a.id')
            ->find();
    }

    /**
     * 根据编号获取文章信息
     * @param  [type] $id [文章编号]
     * @return [type]     [description]
     */
    public function getArticleInfoById($id, $category)
    {
        $map = array(
            "a.id" => array("EQ", $id),
            "b.class_id" => array("EQ", $category),
            "a.state" => array("EQ", 2)
        );
        $prvMap = array(
            "a1.id" => array("LT", $id),
            "_string" => "c1.id = @cid",
            "b1.class_id" => array("EQ", $category),
            "a1.state" => array("EQ", 2)
        );
        $nextMap = array(
            "a1.id" => array("GT", $id),
            "_string" => "c1.id = @cid",
            "b1.class_id" => array("EQ", $category),
            "a1.state" => array("EQ", 2)
        );

        //上一篇文章
        $preSql = D("www_article")->where($prvMap)->alias("a1")
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
            ->union($preSql, true)
            ->union($nextSql, true)
            ->field("'now' as action, a.*, @cid :=c.id as cid,c.classname,c.shortname")
            ->select();

    }

    /**
     * 获取相同类型的文章推荐
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getTopArticleInfo($limit = 10, $id = '')
    {
        if (!empty($id)) {
            $buildSql = M("www_article")->where(array("a.id" => array("EQ", $id)))->alias("a")
                ->join("inner join qz_www_article_class_rel as b on b.article_id = a.id")
                ->field("b.class_id")->buildSql();
            $map["_string"] = "b.class_id In" . $buildSql;
        }
        $map["c.is_new"] = array("EQ", 1);
        $map["a.isTop"] = array("EQ", 1);
        $map["a.state"] = array("EQ", 2);
        return M("www_article")->alias("a")->where($map)
            ->join("inner join qz_www_article_class_rel as b on b.article_id = a.id")
            ->join("inner join qz_www_article_class as c on c.id = b.class_id")
            ->field("a.id,a.title,a.subtitle,a.face,a.addtime,a.imgs,c.shortname")
            ->order("isTop desc,addtime desc")
            ->group("a.id")
            ->limit($limit)
            ->select();
    }

    /**
     * 获取咨询业轮播列表
     * @return [type] [description]
     */
    public function getAdvList($limit = 10)
    {
        $map = array(
            "a.isTop" => array("EQ", 2),
            "a.state" => array("EQ", 2),
            "c.is_new" => array("EQ", 1),
            "c.obsolete" => array("EQ", 0)
        );
        $buildSql = M("www_article")->where($map)->alias("a")
            ->join("INNER JOIN qz_www_article_class_rel as b on b.article_id = a.id")
            ->join("INNER JOIN qz_www_article_class as c on c.id = b.class_id")
            ->field("a.*,c.shortname")->group("a.id")->buildSql();
        return M("www_article")->table($buildSql)->alias("t")
            ->order('t.addtime desc')->limit($limit)
            ->select();
    }

    /**
     * 根据文章类型编号查询文章列表
     * @param  [type] $ids [编号数组]
     * @param  [type] $istop [是否推荐]
     * @return [type]      [description]
     */
    public function getArticleListByIds($ids = '', $pageIndex = 0, $pageCount = 10, $keyword = '', $isTop = false, $order = true, $recommend = false)
    {

        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        if (is_array($ids)) {
            //生成查询语句
            foreach ($ids as $key => $value) {
                $map = array(
                    "c.id" => array("IN", $value),
                    "a.state" => array("EQ", 2)
                );
                if ($isTop) {
                    $map["a.isTop"] = array("EQ", 1);
                }

                if (!empty($keyword)) {
                    $map["a.title"] = array("LIKE", "%$keyword%");
                }

                if ($key != 0) {
                    M("www_article")->where($map)->order("addtime desc")->alias("a")
                        ->join("JOIN qz_www_article_class_rel as b on b.article_id = a.id")
                        ->join("JOIN qz_www_article_class as c on c.id = b.class_id")
                        ->field("a.*,c.id as cid,c.classname as cname,c.shortname,c.pid as cpid")
                        ->limit($pageIndex . "," . $pageCount);
                    if (!$order) {
                        if ($recommend == true) {
                            M("www_article")->order("recommend desc,pv desc");
                        } else {
                            M("www_article")->order("pv desc");
                        }
                    } else {
                        if ($recommend == true) {
                            M("www_article")->order("recommend desc,addtime desc");
                        } else {
                            M("www_article")->order("addtime desc");
                        }
                    }

                    $buildSql = M("www_article")->buildSql();

                    $buildSql = M("www_article")->table($buildSql)->alias("a")
                        ->join("LEFT JOIN qz_www_article_class as d on d.id = a.cpid")
                        ->join("LEFT JOIN qz_www_article_class as e on e.id = d.pid")
                        ->order("cid desc")
                        ->field("a.*,e.classname as firstname,e.id as firsetid,d.classname as secondname,d.id as secondid,d.shortname as secondshortname")->buildSql();

                    M("www_article")->table($buildSql)->group("t" . $key . ".id")->alias("t" . $key);
                    if (!$order) {
                        if ($recommend == true) {
                            M("www_article")->order("recommend desc,pv desc");
                        } else {
                            M("www_article")->order("pv desc");
                        }
                    } else {
                        if ($recommend == true) {
                            M("www_article")->order("recommend desc,addtime desc");
                        } else {
                            M("www_article")->order("addtime desc");
                        }
                    }
                    $sql = M("www_article")->buildSql();
                    $sqls[] = $sql;
                } else {
                    M("www_article")->where($map)->order("addtime desc")->alias("a")
                        ->join("JOIN qz_www_article_class_rel as b on b.article_id = a.id")
                        ->join("JOIN qz_www_article_class as c on c.id = b.class_id")
                        ->field("a.*,c.id as cid,c.classname as cname,c.shortname,c.pid as cpid")
                        ->limit($pageIndex . "," . $pageCount);
                    if (!$order) {
                        if ($recommend == true) {
                            M("www_article")->order("recommend desc,pv desc");
                        } else {
                            M("www_article")->order("pv desc");
                        }
                    } else {
                        if ($recommend == true) {
                            M("www_article")->order("recommend desc,addtime desc");
                        } else {
                            M("www_article")->order("addtime desc");
                        }
                    }

                    $buildSql = M("www_article")->buildSql();

                    $buildSql1 = M("www_article")->table($buildSql)->alias("a")
                        ->join("LEFT JOIN qz_www_article_class as d on d.id = a.cpid")
                        ->join("LEFT JOIN qz_www_article_class as e on e.id = d.pid")
                        ->order("cid desc")
                        ->field("a.*,e.classname as firstname,e.id as firsetid,d.classname as secondname,d.id as secondid,d.shortname as secondshortname")
                        ->buildSql();

                    M("www_article")->table($buildSql1)->group("t.id")->alias("t");

                    if (!$order) {
                        if ($recommend == true) {
                            M("www_article")->order("recommend desc,pv desc");
                        } else {
                            M("www_article")->order("pv desc");
                        }
                    } else {
                        if ($recommend == true) {
                            M("www_article")->order("recommend desc,addtime desc");
                        } else {
                            M("www_article")->order("addtime desc");
                        }
                    }

                    $buildSql1 = M("www_article")->buildSql();
                }
            }
            M("www_article")->table($buildSql1)->alias("t");
            foreach ($sqls as $key => $value) {
                M("www_article")->union($value, true);
            }
            $buildSql = M("www_article")->buildSql();
            return M("www_article")->table($buildSql)->alias("t")->select();
        }
        return null;
    }
    //获得所有分类下的数据，不分推荐的非推荐
    public function getAnyTypeArticleListByIds($ids = "", $pageIndex, $pageCount, $isTop = "false", $keywords = ""){
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $map['a.state'] = ["EQ", 2];
        $map['c.obsolete'] = ['EQ', 0];
        $map['c.is_new'] = ['EQ', 1];

        if (!empty($ids)) {
            if (is_array($ids)) {
                $map['c.id'] = ["in", $ids];
            } else {
                $map['c.id'] = ['EQ', $ids];
            }
        }

        if (!empty($keywords)) {
            $map['a.title'] = ['like', "%" . $keywords . "%"];
        }

        if ($isTop === true) {
            $map['a.isTop'] = ['EQ', 1];
        } elseif ($isTop === false) {
            $map['a.isTop'] = ['EQ', 0];
        }
		$data = M("www_article")->alias("a")
            ->join("inner join qz_www_article_class_rel as b on b.article_id = a.id")
            ->join("inner join qz_www_article_class as c on b.class_id = c.id")
            ->where($map)
            ->order("a.addtime desc")
            ->field("a.id, a.keywords, a.title, a.content, a.face, a.subtitle, a.addtime, a.pv, a.likes, a.isTop, a.imgs, c.shortname")
            ->limit($pageIndex . "," . $pageCount)
            ->select();
        return $data;
    }
    //这个是获得3个推荐和7个非推荐的数据的方法的复制
    public function getTwoTypeArticleListByIds($page_rule, $ids = [], $keyword = '')
    {
        if ($page_rule == false) {
            return [];
        }
        if ($page_rule['b_limit'] == 0) {
            $map = array(
                'a.state' => ['EQ', 2],
                'a.isTop' => ['eq', 0],
            );
            if (!empty($keyword)) {
                $map["a.title"] = array("LIKE", "%$keyword%");
            }
            if (!empty($ids)) {
                $map["c.id"] = array('IN', $ids);
            }
            $data = M("www_article")->where($map)->order("addtime desc")->alias("a")
                ->join("JOIN qz_www_article_class_rel as b on b.article_id = a.id")
                ->join("JOIN qz_www_article_class as c on c.id = b.class_id")
                ->field("a.*,c.id as cid,c.classname as cname,c.shortname,c.pid as cpid")
                ->limit($page_rule['a_skip'] . "," . $page_rule['a_limit'])
                ->select();
            return $data;
        }
        if ($page_rule['a_limit'] == 0) {
            $map = array(
                'a.state' => ['EQ', 2],
                'a.isTop' => ['EQ', 1],
            );
            if (!empty($keyword)) {
                $map["a.title"] = array("LIKE", "%$keyword%");
            }
            if (!empty($ids)) {
                $map["c.id"] = array('IN', $ids);
            }
            $data = M("www_article")->where($map)->order("addtime desc")->alias("a")
                ->join("JOIN qz_www_article_class_rel as b on b.article_id = a.id")
                ->join("JOIN qz_www_article_class as c on c.id = b.class_id")
                ->field("a.*,c.id as cid,c.classname as cname,c.shortname,c.pid as cpid")
                ->limit($page_rule['b_skip'] . "," . $page_rule['b_limit'])
                ->select();
            return $data;
        }

        $map1 = array(
            'a1.state' => ['EQ', 2],
            'a1.isTop' => ['EQ', 0],
        );
        $map2 = array(
            'a2.state' => ['EQ', 2],
            'a2.isTop' => ['EQ', 1],
        );
        if (!empty($keyword)) {
            $map1["a1.title"] = array("LIKE", "%$keyword%");
            $map2["a2.title"] = array("LIKE", "%$keyword%");

        }
        if (!empty($ids)) {
            $map1["c1.id"] = array('IN', $ids);
            $map2["c2.id"] = array('IN', $ids);
        }
        $buildSql1 = M("www_article")->where($map1)->order("addtime desc")->alias("a1")
            ->join("JOIN qz_www_article_class_rel as b1 on b1.article_id = a1.id")
            ->join("JOIN qz_www_article_class as c1 on c1.id = b1.class_id")
            ->field("a1.*,c1.id as cid,c1.classname as cname,c1.shortname,c1.pid as cpid")
            ->limit($page_rule['a_skip'] . "," . $page_rule['a_limit'])
            ->buildSql();
        $buildSql2 = M("www_article")->where($map2)->order("addtime desc")->alias("a2")
            ->join("JOIN qz_www_article_class_rel as b2 on b2.article_id = a2.id")
            ->join("JOIN qz_www_article_class as c2 on c2.id = b2.class_id")
            ->field("a2.*,c2.id as cid,c2.classname as cname,c2.shortname,c2.pid as cpid")
            ->limit($page_rule['b_skip'] . "," . $page_rule['b_limit'])
            ->buildSql();
        $data = M()->query($buildSql2 . 'union' . $buildSql1);
        return $data;
    }

    /**
     * 获取文章数量
     * @return [type] [description]
     */
    public function getArticleListCount($ids = '', $keyword = "")
    {
        $map = array(
            "a.state" => array("EQ", 2)
        );
        if (!empty($ids)) {
            $map["c.id"] = array("IN", $ids);
        }
        if (!empty($keyword)) {
            $map["a.title"] = array("LIKE", "%$keyword%");
        }
        $buildSql = M("www_article")->where($map)->alias("a")
            ->join("INNER JOIN qz_www_article_class_rel as b on b.article_id = a.id")
            ->join("INNER JOIN qz_www_article_class as c on c.id = b.class_id")
            ->field("a.id")
            ->group("a.id")
            ->buildSql();
        return M("www_article")->table($buildSql)->alias("t")
            ->count();
    }
    //不分推荐和非推荐的数量
    public function getAnyTypeArticleCount($ids = "", $keywords = "", $isTop = ""){
        $map = [
            "a.state" => ["EQ", 2],
            "c.obsolete" => ["EQ", 0],
            "c.is_new" => ["EQ", 1],
        ];

        if (!empty($ids)) {
            if (is_array($ids)) {
                $map['c.id'] = ["in", $ids];
            } else {
                $map['c.id'] = ['EQ', $ids];
            }
        }

        if (!empty($isTop)) {
            if ($isTop == 1) {
                $map['a.isTop'] = ["EQ", 1];
            } else {
                $map['a.isTop'] = ["EQ", 0];
            }
        }
        //$map['a.isTop'] = ["NEQ", 2];//数据问题

        if (!empty($keywords)) {
            $map['a.title'] = ["like", "%" . $keywords . "%"];
        }

        $count = M("www_article")->alias("a")
            ->join("inner join qz_www_article_class_rel as b on b.article_id = a.id")
            ->join("inner join qz_www_article_class as c on b.class_id = c.id")
            ->where($map)
            ->count();

        return $count;
    }
    //这是获得3个推荐和7个非推荐数据
    public function getTwoTypeArticleCount($categoryids, $keyword)
    {
        $map1["a.isTop"] = array("eq", 1);
        $map1["a.state"] = array("eq", 2);
        $map2["c.isTop"] = array("eq", 0);
        $map2["c.state"] = array("eq", 2);

        if (!empty($keyword)) {
            $map1["a.title"] = array("LIKE", "%$keyword%");
            $map2["c.title"] = array("LIKE", "%$keyword%");
        }
        if (!empty($categoryids)) {
            $map1["b.class_id"] = array("IN", $categoryids);
            $map2["d.class_id"] = array("IN", $categoryids);
        }
        $buildSql1 = M("www_article")->alias("a")
            ->field("count(a.id) as num")
            ->where($map1)
            ->join("INNER JOIN qz_www_article_class_rel as b on b.article_id = a.id")
            ->join("JOIN qz_www_article_class as e on e.id = b.class_id")
            ->buildSql();
        $buildSql2 = M("www_article")->alias("c")
            ->field("count(c.id) as num")
            ->where($map2)
            ->join("INNER JOIN qz_www_article_class_rel as d on d.article_id = c.id")
            ->join("JOIN qz_www_article_class as f on f.id = d.class_id")
            ->buildSql();
        $data = M()->query($buildSql2 . 'union' . $buildSql1);

        return $data;
    }

    /**
     * 文章阅读量更新
     * @return [type] [description]
     */
    public function updatePv($id)
    {
        $map = array(
            "id" => $id
        );
        M("www_article")->where($map)->setInc("pv", 1);
    }

    /**
     * 更新文章真实阅读量（按IP单倍计算，即单个IP每天只算一次）
     * @return [$id] [文章ID]
     */
    public function updateRealView($id)
    {
        $map = array(
            "id" => $id
        );
        M("www_article")->where($map)->setInc("realview", 1);
    }


    /**
     * 更新文章来自搜索引擎自然流量IP（按IP单倍计算，即单个IP每天只算一次）
     * @return [$id] [文章ID]
     */
    public function updateSearchIP($id)
    {
        $map = array(
            "id" => $id
        );
        M("www_article")->where($map)->setInc("search_ip", 1);
    }


    /**
     * 获取热门的文章
     * @return [type] [description]
     */
    public function getHotArticle($limit)
    {
        $map = array(
            "a.isTop" => array("EQ", 1),
            "a.state" => array("EQ", 2),
            "c.is_new" => array("EQ", 1),
            "c.obsolete" => array("EQ", 0)
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
     * 获取热门的文章
     * @return [type] [description]
     */
    public function getNewArticle($limit)
    {
        $map = array(
            "a.state" => array("EQ", 2),
            "c.is_new" => array("EQ", 1),
            "c.obsolete" => array("EQ", 0)
        );
        return M("www_article")->where($map)->alias("a")
            ->join("INNER JOIN qz_www_article_class_rel as b on b.article_id = a.id")
            ->join("INNER JOIN qz_www_article_class as c on c.id = b.class_id")
            ->field("a.*,c.shortname")
            ->order("a.id desc")
            ->limit($limit)
            ->select();

    }

    /**
     * [getArticleSpecialByMap 直接通过搜索条件获取推荐文章]
     * @param  [type] $map   [条件]
     * @param  [type] $limit [获取数量]
     * @return [type]        [description]
     */
    public function getArticleListByMap($map, $limit)
    {
        if (empty($map)) {
            $map['a.id'] = array('GT', 0);
        }
        $map["a.state"] = array("EQ", 2);
        $result = M('www_article')->alias('a')
            ->field('a.id,a.title,c.id AS cid,a.subtitle,a.face,a.pv,a.addtime,c.classname,c.shortname')
            ->join("INNER JOIN qz_www_article_class_rel as b on b.article_id = a.id")
            ->join("INNER JOIN qz_www_article_class as c on c.id = b.class_id")
            ->where($map)
            ->order('id DESC')
            ->group('a.id')
            ->limit($limit)
            ->select();
        return $result;
    }

    /**
     * 获取点击量最高的文章列表
     * @param  [type] $classid [文章类型]
     * @param  [type] $limit   [description]
     * @return [type]          [description]
     */
    public function getRecommendArticles($classid, $limit = 20)
    {
        $map = array(
            "a.state" => array("EQ", 2),
            "c.is_new" => array("EQ", 1),
            "c.obsolete" => array("EQ", 0)
        );
        if (!empty($classid)) {
            $map["c.id"] = array("IN", $classid);
        }

        if (!empty($limit)) {
            M("www_article")->limit($limit);
        }

        //1.获取点击率最高的文章
        return M("www_article")->where($map)->alias("a")
            ->join("INNER JOIN qz_www_article_class_rel as b on b.article_id = a.id")
            ->join("INNER JOIN qz_www_article_class as c on c.id = b.class_id")
            ->order("a.pv desc")
            ->field("a.title,a.id,c.shortname")
            ->limit('0,' . $limit)
            ->select();
    }

    /**
     * 移动端文章详细信息
     * @param  [type] $id       [文章编号]
     * @param  [type] $category [文章类别]
     * @return [type]           [description]
     */
    public function getMobileArticleInfoById($id, $category)
    {
        $map = array(
            "a.state" => array("EQ",2),
            "a.id" => array("EQ", $id),
            "c.id" => array("EQ", $category),
            "c.obsolete" => array("EQ", 0)
        );
        return M("www_article")->where($map)->alias("a")
            ->join("INNER JOIN qz_www_article_class_rel as b on a.id = b.article_id")
            ->join("INNER JOIN qz_www_article_class as c on c.id = b.class_id")
            ->field("a.*,c.shortname,c.classname,c.description as class_description,c.keywords as class_keywords,c.title AS articletitile")
            ->select();
    }


    //查询文章列表  根据分类
    public function getListByCid($where, $orderBy, $row = 10)
    {
        $map['a.state'] = array("EQ", '2');

        if (!empty($where['pid'])) {
            $map['c.pid'] = array("EQ", $where['pid']);
        }
        if (!empty($where['isTop'])) {
            $map['a.isTop'] = array("EQ", $where['isTop']);
        }
        $Db = M('www_article');
        $result = $Db->alias("a")
            ->join("left join qz_www_article_class_rel as r on r.article_id = a.id")
            ->join("left join qz_www_article_class as c on c.id = r.class_id")
            ->field("a.id,a.title,a.content,a.face,imgs,c.shortname,c.classname")
            ->order($orderBy)
            ->limit("0," . $row)
            ->where($map)
            ->select();
        return $result;
    }

    /**
     * 获取热门文章列表
     * @param  string $ids [description]
     * @param  integer $pageIndex [description]
     * @param  integer $pageCount [description]
     * @param  string $keyword [description]
     * @return [type]             [description]
     */
    public function getHotArticleListByIds($ids = '', $pageIndex = 0, $pageCount = 10, $keyword = '')
    {
        if (is_array($ids)) {
            //生成查询语句
            foreach ($ids as $key => $value) {
                $map = array(
                    "c.id" => array("IN", $value),
                    "a.state" => array("EQ", 2)
                );
                if ($isTop) {
                    $map["a.isTop"] = array("EQ", 1);
                }

                if (!empty($keyword)) {
                    $map["a.title"] = array("LIKE", "%$keyword%");
                }

                if ($key != 0) {
                    $buildSql = M("www_article")->where($map)->alias("a")
                        ->join("INNER JOIN qz_www_article_class_rel as b on b.article_id = a.id")
                        ->join("INNER JOIN qz_www_article_class as c on c.id = b.class_id")
                        ->join("LEFT JOIN qz_www_article_class as d on d.id = c.pid")
                        ->join("LEFT JOIN qz_www_article_class as e on e.id = d.pid")
                        ->order("cid desc")
                        ->field("a.*,c.id as cid,c.classname as cname,c.shortname,e.classname as firstname,e.id as firsetid,d.classname as secondname,d.id as secondid,d.shortname as secondshortname")->buildSql();

                    $sql = M("www_article")->table($buildSql)->group("t" . $key . ".id")
                        ->limit($pageIndex . "," . $pageCount)
                        ->order("pv desc,addtime desc")
                        ->alias("t" . $key)->buildSql();
                    $sqls[] = $sql;
                } else {
                    $buildSql1 = M("www_article")->where($map)->alias("a")
                        ->join("INNER JOIN qz_www_article_class_rel as b on b.article_id = a.id")
                        ->join("INNER JOIN qz_www_article_class as c on c.id = b.class_id")
                        ->join("LEFT JOIN qz_www_article_class as d on d.id = c.pid")
                        ->join("LEFT JOIN qz_www_article_class as e on e.id = d.pid")
                        ->order("cid desc")
                        ->field("a.*,c.id as cid,c.classname as cname,c.shortname,e.classname as firstname,e.id as firsetid,d.classname as secondname,d.id as secondid,d.shortname as secondshortname")->buildSql();
                    $buildSql1 = M("www_article")->table($buildSql1)->group("t.id")
                        ->alias("t")->limit($pageIndex . "," . $pageCount)
                        ->order("pv desc,addtime desc")
                        ->buildSql();
                }
            }
            M("www_article")->table($buildSql1)->alias("t");
            foreach ($sqls as $key => $value) {
                M("www_article")->union($value, true);
            }
            $buildSql = M("www_article")->buildSql();
            return M("www_article")->table($buildSql)->alias("t")->select();
        }
        return null;
    }

    /**
     * 攻略文章喜欢+1
     * @param string $value [description]
     */
    public function setLikes($id)
    {
        $map = array(
            "id" => array("EQ", $id)
        );
        return M("www_article")->where($map)->setInc("likes", 1);
    }

    /**
     * 获取i标签相关的文章列表
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getBiaoQianList($fen_word, $limit)
    {
        if (!is_array($fen_word)) {
            return false;
        }

        foreach ($fen_word as $key => $value) {
            $title[] = array("LIKE", "%$value%");
        }
        $title[] = "OR";

        $map = array(
            "title" => $title,
            "state" => array("EQ", 2),
            "id" => array("GT", 250)
        );

        $buildSql = M("www_article")->where($map)->field("id,title,subtitle,face")->order("id desc")->limit($limit)->buildSql();

        return M("www_article")->table($buildSql)->alias("a")
            ->join("INNER JOIN qz_www_article_class_rel as b on b.article_id = a.id")
            ->join("INNER JOIN qz_www_article_class as c on c.id = b.class_id")
            ->order("a.id desc")
            ->field("a.*,c.shortname")->select();
    }

}