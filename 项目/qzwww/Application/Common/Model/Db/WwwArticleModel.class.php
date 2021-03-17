<?php

namespace Common\Model\Db;

use Think\Model;

class WwwArticleModel extends Model
{
    /**
     * 某字段数量+1
     * @param $id
     * @param $field
     * @param $step
     * @return bool
     */
    public function incField($id,$field,$step)
    {
        $map['id'] = ['eq', $id];
        $ret = $this->where($map)->setInc($field, $step);
        return $ret;
    }

    public function getArticle($where, $field = 'a.*')
    {
        return $this->alias('a')
            ->field($field)
            ->where($where)
            ->select();
    }

    public function getArticleByShort($class_short_id, $field = 'a.*', $page = 0, $page_count = 4, $order = 'a.id desc')
    {
        $where = [
            'a.state' => ['eq', 2],
            'c.id' => ['in', $class_short_id]
        ];
        return $this->alias('a force index(idx_state)')
            ->field($field)
            ->where($where)
            ->join("qz_www_article_class_rel rel on rel.article_id = a.id")
            ->join("qz_www_article_class c on c.id = rel.class_id")
            ->order($order)
            ->limit($page, $page_count)
            ->select();
    }


    public function getArticleInfo($id, $category)
    {
        $map = array(
            "a.id" => array("EQ", $id),
            "b.class_id" => array("EQ", $category),
            "a.state" => array("EQ", 2)
        );
        return D("www_article")->where($map)->alias("a")
            ->join("qz_www_article_class_rel as b on b.article_id = a.id")
            ->join("qz_www_article_class as c on c.id = b.class_id")
            ->field("a.*, c.id as cid,c.classname,c.shortname")
            ->find();
    }

    /**
     * 获取攻略属性
     * @param $map
     * @param string $field
     * @param int $limit
     * @return mixed
     */
    public function getWwwArticleClass($short_name, $field = 'c.*')
    {
        $map['c.shortname'] = ['in', $short_name];
        return M('www_article_class')->alias('c')
            ->field($field)
            ->where($map)
            ->select();
    }

    public function getClassIdByPShort($short)
    {
        $where = [
            'c.is_new' => ['eq', 1]
        ];
        if(is_array($short)){
            $where['c1.shortname'] = ['in', $short];
        }else{
            $where['c1.shortname'] = ['eq', $short];
        }
        return M('www_article_class')->alias('c')
            ->field('c.id')
            ->join('qz_www_article_class c1 on c1.id = c.pid')
            ->where($where)
            ->select();
    }

    public function getClassIdByShort($short)
    {
        $where = [
            'c.is_new' => ['eq', 1]
        ];
        if(is_array($short)){
            $where['c.shortname'] = ['in', $short];
        }else{
            $where['c.shortname'] = ['eq', $short];
        }
        return M('www_article_class')->alias('c')
            ->field('c.id')
            ->where($where)
            ->select();
    }


    /**
     * 根据 第一级的分类的id获取三级分类下的文章列表
     * @param $grandClass
     * @param $count
     * @return mixed
     */
    public function getByGrandClass($grandClass, $count)
    {
        $map['a.state'] = ['eq', 2];
        $map['wac_g.id'] = ['eq', $grandClass];

        $field = 'a.id,a.title,a.item_name,a.face,a.imgs,wac.shortname';

        $data = $this->alias('a')
            ->join('qz_www_article_class_rel wacr on wacr.article_id=a.id')
            ->join('qz_www_article_class wac on wacr.class_id = wac.id and wac.obsolete=0 and wac.level=3 and wac.is_new=1')
            ->join('qz_www_article_class wac_p on wac.pid = wac_p.id and wac_p.obsolete=0 and wac_p.level=2 and wac_p.is_new=1')
            ->join('qz_www_article_class wac_g on wac_p.pid = wac_g.id and wac_g.obsolete=0 and wac_g.level=1 and wac_g.is_new=1')
            ->where($map)
            ->field($field)
            ->order('a.istop desc,a.addtime desc')
            ->limit($count)
            ->select();
        return $data;
    }

    /**
     * 全屋定制首页-根据全屋定制的id获取不在某些中的数据
     * @param $grandClass
     * @param $notIds
     * @param $count
     * @return mixed
     */
    public function getHotNotIdByGrandClass($grandClass,$notIds, $count)
    {
        $map2['t.state'] = ['eq', 2];
        if(!empty($notIds)){
            $map2['t.id'] = ['not in', $notIds];
        }
        $map2['wac_g.id'] = ['eq', $grandClass];
        $field = 't.id,t.title,t.item_name,t.face,t.imgs,t.content,wac.shortname';
        $data = $this->alias('t')
            ->join('qz_www_article_class_rel wacr on wacr.article_id=t.id')
            ->join('qz_www_article_class wac on wacr.class_id = wac.id and wac.obsolete=0 and wac.level=3 and wac.is_new=1')
            ->join('qz_www_article_class wac_p on wac.pid = wac_p.id and wac_p.obsolete=0 and wac_p.level=2 and wac_p.is_new=1')
            ->join('qz_www_article_class wac_g on wac_p.pid = wac_g.id and wac_g.obsolete=0 and wac_g.level=1 and wac_g.is_new=1')
            ->where($map2)
            ->field($field)
            ->limit($count)
            ->order('t.pv desc,t.addtime desc')
            ->select();
        return $data;
    }

    /**
     * 根据第一级分类获取今日数据
     * @param $grandClass
     * @param $count
     * @return mixed
     */
    public function getTodayHotByGrandClass($grandClass, $count)
    {
        $today = date('Y-m-d');
        $map2['t.state'] = ['eq', 2];
        $map2['t.addtime'] = ['exp', "= '{$today}'"];
        $map2['wac_g.id'] = ['eq', $grandClass];
        $field = 't.id,t.title,t.item_name,t.face,t.imgs,t.content,wac.shortname';
        $data = $this->alias('t')
            ->join('qz_www_article_class_rel wacr on wacr.article_id=t.id')
            ->join('qz_www_article_class wac on wacr.class_id = wac.id and wac.obsolete=0 and wac.level=3 and wac.is_new=1')
            ->join('qz_www_article_class wac_p on wac.pid = wac_p.id and wac_p.obsolete=0 and wac_p.level=2 and wac_p.is_new=1')
            ->join('qz_www_article_class wac_g on wac_p.pid = wac_g.id and wac_g.obsolete=0 and wac_g.level=1 and wac_g.is_new=1')
            ->where($map2)
            ->field($field)
            ->limit($count)
            ->order('t.pv desc,t.addtime desc')
            ->select();
        return $data;
    }

    /**
     * 根据第一级分类获取数据
     * @param $grandClass
     * @param $count
     * @return mixed
     */
    public function getHotByGrandClass($grandClass, $count)
    {
        $map2['t.state'] = ['eq', 2];
        $map2['wac_g.id'] = ['eq', $grandClass];
        $field = 't.id,t.title,t.item_name,t.face,t.imgs,t.content,wac.shortname';
        $data = $this->alias('t')
            ->join('qz_www_article_class_rel wacr on wacr.article_id=t.id')
            ->join('qz_www_article_class wac on wacr.class_id = wac.id and wac.obsolete=0 and wac.level=3 and wac.is_new=1')
            ->join('qz_www_article_class wac_p on wac.pid = wac_p.id and wac_p.obsolete=0 and wac_p.level=2 and wac_p.is_new=1')
            ->join('qz_www_article_class wac_g on wac_p.pid = wac_g.id and wac_g.obsolete=0 and wac_g.level=1 and wac_g.is_new=1')
            ->where($map2)
            ->field($field)
            ->limit($count)
            ->order('t.pv desc,t.addtime desc')
            ->select();
        return $data;
    }


    /**
     * 根据一级和第二级分类获取数据
     * @param $grandClass
     * @param $parentClass
     * @param $count
     * @return mixed
     */
    public function getByParentCLass($grandClass, $parentClass, $count)
    {
        $map['a.state'] = ['eq', 2];
        $map['wac_g.id'] = ['eq', $grandClass];
        $map['wac_p.id'] = ['eq', $parentClass];

        $field = 'a.id,a.title,a.item_name,a.face,a.imgs,wac.shortname';

        $data = $this->alias('a')
            ->join('qz_www_article_class_rel wacr on wacr.article_id=a.id')
            ->join('qz_www_article_class wac on wacr.class_id = wac.id and wac.obsolete=0 and wac.level=3 and wac.is_new=1')
            ->join('qz_www_article_class wac_p on wac.pid = wac_p.id and wac_p.obsolete=0 and wac_p.level=2 and wac_p.is_new=1')
            ->join('qz_www_article_class wac_g on wac_p.pid = wac_g.id and wac_g.obsolete=0 and wac_g.level=1 and wac_g.is_new=1')
            ->where($map)
            ->field($field)
            ->order('a.addtime desc')
            ->limit($count)
            ->select();
        return $data;
    }

    /**
     * 根据第一级分类和第三级分类获取文章的数量
     * @param $params
     * @return mixed
     */
    public function getCountByGrandClassAndClass($params)
    {
        $map['a.state'] = ['eq', 2];
        $map['wacr.class_id'] = ['eq', $params['class']];
        $map['wac_g.id'] = ['eq', $params['grand_class']];
        $count = $this->alias('a')
            ->join('qz_www_article_class_rel wacr on wacr.article_id=a.id')
            ->join('qz_www_article_class wac on wacr.class_id = wac.id and wac.obsolete=0 and wac.level=3 and wac.is_new=1')
            ->join('qz_www_article_class wac_p on wac.pid = wac_p.id and wac_p.obsolete=0 and wac_p.level=2 and wac_p.is_new=1')
            ->join('qz_www_article_class wac_g on wac_p.pid = wac_g.id and wac_g.obsolete=0 and wac_g.level=1 and wac_g.is_new=1')
            ->where($map)
            ->count();
        return $count;
    }

    /**
     * 根据第一级分类和第三级分类获取文章的数据
     * @param $page
     * @param $perCount
     * @param $params
     * @return mixed
     */
    public function getListByGrandClassAndClass($page, $perCount, $params)
    {
        $map['a.state'] = ['eq', 2];
        $map['wacr.class_id'] = ['eq', $params['class']];
        $map['wac_g.id'] = ['eq', $params['grand_class']];

        $field = 'a.id,a.pv,a.title,a.content,a.face,a.imgs,a.addtime,wac.shortname';

        $data = $this->alias('a')
            ->join('qz_www_article_class_rel wacr on wacr.article_id=a.id')
            ->join('qz_www_article_class wac on wacr.class_id = wac.id and wac.obsolete=0 and wac.level=3 and wac.is_new=1')
            ->join('qz_www_article_class wac_p on wac.pid = wac_p.id and wac_p.obsolete=0 and wac_p.level=2 and wac_p.is_new=1')
            ->join('qz_www_article_class wac_g on wac_p.pid = wac_g.id and wac_g.obsolete=0 and wac_g.level=1 and wac_g.is_new=1')
            ->where($map)
            ->field($field)
            ->order('a.addtime desc,a.id desc')
            ->limit($page, $perCount)
            ->select();
        return $data;
    }

    /**
     * 根据第一级分类和第二级分类后去下面的所有子分类的文章数量
     * @param $params
     * @return mixed
     */
    public function getCountByGrandClassAndParentClass($params)
    {
        $map['a.state'] = ['eq', 2];
        $map['wac_p.id'] = ['eq', $params['class']];
        $map['wac_g.id'] = ['eq', $params['grand_class']];
        $count = $this->alias('a')
            ->join('qz_www_article_class_rel wacr on wacr.article_id=a.id')
            ->join('qz_www_article_class wac on wacr.class_id = wac.id and wac.obsolete=0 and wac.level=3 and wac.is_new=1')
            ->join('qz_www_article_class wac_p on wac.pid = wac_p.id and wac_p.obsolete=0 and wac_p.level=2 and wac_p.is_new=1')
            ->join('qz_www_article_class wac_g on wac_p.pid = wac_g.id and wac_g.obsolete=0 and wac_g.level=1 and wac_g.is_new=1')
            ->where($map)
            ->count();
        return $count;
    }

    /**
     * 根据第一级分类和第二级分类后去下面的所有子分类的文章数据
     * @param int $page
     * @param int $perCount
     * @param $params
     * @return mixed
     */
    public function getListByGrandClassAndParentClass($page, $perCount, $params)
    {
        $map['a.state'] = ['eq', 2];
        $map['wac_p.id'] = ['eq', $params['class']];
        $map['wac_g.id'] = ['eq', $params['grand_class']];

        $field = 'a.id,a.pv,a.title,a.content,a.face,a.imgs,a.addtime,wac.shortname';

        $data = $this->alias('a')
            ->join('qz_www_article_class_rel wacr on wacr.article_id=a.id')
            ->join('qz_www_article_class wac on wacr.class_id = wac.id and wac.obsolete=0 and wac.level=3 and wac.is_new=1')
            ->join('qz_www_article_class wac_p on wac.pid = wac_p.id and wac_p.obsolete=0 and wac_p.level=2 and wac_p.is_new=1')
            ->join('qz_www_article_class wac_g on wac_p.pid = wac_g.id and wac_g.obsolete=0 and wac_g.level=1 and wac_g.is_new=1')
            ->where($map)
            ->field($field)
            ->order('a.addtime desc,a.id desc')
            ->limit($page, $perCount)
            ->select();
        return $data;
    }

    /**
    * 根据分类别名(缩写)获取分类信息
    * @param $shortname 分类别名(缩写)
    * @return array
    */
    public function getClassInfoByShortName($shortname)
    {
        $map['shortname'] = $shortname;
        $map['obsolete'] = 0;
        $classInfo = M('www_article_class')->where($map)->find();

        return $classInfo ?: array();
    }

    /**
    * 获取某个分类下最新发布的N条
    * @param $cid 最下级的分类ID
    * @param $limit 限制显示的条数，默认10条
    * @return array
    */
    public function getNewLimitArticleList($cid, $limit=10)
    {
        $map['wacr.class_id'] = $cid;
        $map['a.state'] = 2;

        $data = $this->alias('a')->where($map)
            ->join('RIGHT JOIN qz_www_article_class_rel wacr ON wacr.article_id=a.id')
            ->field(["a.id", "a.title"])
            ->order('a.id DESC')
            ->limit($limit)
            ->select();
        
        return $data ?: array();
    }
}