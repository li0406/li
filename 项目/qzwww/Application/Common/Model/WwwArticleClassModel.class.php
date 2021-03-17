<?php
/**
 *  文章类别表 qz_www_article_class
 */

namespace Common\Model;

use Think\Model;

class WwwArticleClassModel extends Model
{
    private $articleClass = null;

    public function _initialize()
    {
        $this->articleClass = S("Cache:Article:Class:new");
        if (!$this->articleClass) {
            $map = array(
                "is_new" => array("EQ", 1),
                "obsolete" => array("EQ", 0)
            );
            $class = M("www_article_class")->order("pid,level,id")->where($map)->select();
            $class = $this->getArticleClass($class);
            $this->articleClass = $class;
            S("Cache:Article:Class:new", $class, 3600 * 24);
        }
    }

    /**
     * 获取所有文章的分类编号
     * @return [type] [description]
     */
    public function getAllArticleClass()
    {
        $ids = array();
        foreach ($this->articleClass as $key => $value) {
            foreach ($value["ids"] as $k => $val) {
                $ids[] = $val;
            }
        }
        return array_unique($ids);
    }

    /**
     * 根据ID获取该分类及子类信息非树模式
     * @return [type] [description]
     */
    public function getArticleClassById($id)
    {
        return $this->articleClass[$id];
    }

    /**
     * [getArticleClassChildrenById 根据id获取子分类]
     * @param  [type] $id [id]
     * @return [type]     [description]
     */
    public function getArticleClassChildrenById($id, $order = '')
    {
        if (empty($id)) {
            return false;
        }
        $map = array(
            'pid' => array('EQ', $id),
            'is_new' => array('EQ', 1),
            'obsolete' => array('EQ', 0)
        );
        $result = M('www_article_class')->where($map)->order($order)->select();
        return $result;
    }

    private function getArticleClass($list, $parent = 0)
    {
        $tree = array();
        $nodes = array();
        foreach ($list as $row) {
            $nodes[$row['id']] = $row;
        }

        // $id =0;
        foreach ($nodes as $k => $v) {
            if ($v['pid'] == '0') {
                $tree[$v['id']] = $v;
            } else {
                if ($v["level"] == 2) {
                    $p = $nodes[$v['pid']];
                    $tree[$p['id']]["ids"][] = $v["id"];
                    $tree[$p['id']]["child"][$v["id"]] = $v;
                } else {
                    //3级分类
                    $p1 = $nodes[$v['pid']];
                    $p2 = $nodes[$p1['pid']];
                    $tree[$p2['id']]["child"][$p1["id"]]["ids"][] = $v["id"];
                    $tree[$p2['id']]["child"][$p1["id"]]["classnames"][] = $v["classname"];
                    $tree[$p2['id']]['ids'][] = $v['id'];
                    $tree[$p2['id']]["child"][$p1["id"]]["child"][$v["id"]] = $v;
                }
            }
        }

        return $tree;
    }

    public function getClassByShortName($shortname)
    {
        $map = array(
            "shortname" => array("EQ", $shortname),
            "obsolete" => array('EQ', 0)
        );
        $result = M("www_article_class")->where($map)->find();
        return $result;
    }

    /**
     * 根据类别简称获取类别信息
     * @param  [type] $shortname [description]
     * @return [type]            [description]
     */
    public function getArticleClassByShortname($shortname)
    {
        $map = array(
            "shortname" => array("EQ", $shortname),
            "obsolete" => array('EQ', 0)
        );
        $result = M("www_article_class")->where($map)->find();
        if (!empty($result['pid'])) {
            $result['parent'] = M("www_article_class")->where(['id' => $result['pid'], 'obsolete' => 0])->find();
            if (!empty($result['parent']['pid'])) {
                $result['parent']['parent'] = M("www_article_class")->where(['id' => $result['parent']['pid'], 'obsolete' => 0])->find();
            }
        }
        return $result;
    }

    /**
     * 根据类别id获取类别信息
     * @param  [type] $shortname [description]
     * @return [type]            [description]
     */
    public function getArticleClassByCatagoryId($id)
    {
        $map = array(
            "id" => array("EQ", $id),
            "obsolete" => array('EQ', 0)
        );
        $result = M("www_article_class")->where($map)->find();
        if (!empty($result['pid'])) {
            $result['parent'] = M("www_article_class")->where(['id' => $result['pid'], 'obsolete' => 0])->find();
            if (!empty($result['parent']['pid'])) {
                $result['parent']['parent'] = M("www_article_class")->where(['id' => $result['parent']['pid'], 'obsolete' => 0])->find();
            }
        }
        return $result;
    }

    /**
     * 获取老站的目录
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getOldArticleClassId($id)
    {
        $map = array(
            "id" => array("EQ", $id),
            "pid" => array("EQ", $id),
            "_logic" => "OR"
        );
        return M("www_article_class")->where($map)->field("id")->select();
    }

    /**
     * 获取所有老的文章分类
     * @return [type] [description]
     */
    public function getAllOldArticleClassId()
    {
        $map = array(
            "is_new" => array("EQ", 0),
            "obsolete" => array("EQ", 0)
        );
        return M("www_article_class")->order("id")->where($map)->select();
    }

    /**
     * 根据文章的编号获取文章分类信息
     * @param int id 文章id
     * @param str type old老文章分配, new 新文章分类
     * @return mysql
     */
    public function getArticleClassByArticleId($id, $type = "old")
    {
        $map = array(
            "b.article_id" => array("EQ", $id)
        );
        switch ($type) {
            case 'old':
                $map["a.id"] = array("ELT", "86");
                break;
            case 'new':
                $map["a.id"] = array("GT", "86");
                break;
            default:
                # code...
                break;
        }
        return M("www_article_class")->where($map)->alias("a")
            ->join("INNER JOIN qz_www_article_class_rel as b on a.id = b.class_id")
            ->find();
    }

    /**
     * [getArticleClassIdsByClass 更加分类id获取属于该分类的所有分类]
     * @param  [type] $groupid [description]
     * @return [type]          [description]
     */
    public function getArticleClassIdsByClass($groupid)
    {
        $result = array();
        if (empty($groupid)) {
            return $result;
        }

        $map = array();
        $map["is_new"] = array("EQ", 1);
        $map["_complex"] = array(
            "pid" => array("EQ", $groupid),
            "id" => array("EQ", $groupid),
            "_logic" => "OR"
        );
        $sub = M("www_article_class")->where($map)->select();
        if (count($sub) > 0) {
            foreach ($sub as $key => $value) {
                $ids[] = $value["id"];
            }
            $submap = array();
            $submap["is_new"] = array("EQ", 1);
            $submap["_complex"] = array(
                "pid" => array("IN", $ids),
                "id" => array("IN", $ids),
                "_logic" => "OR"
            );

            $result = M("www_article_class")->where($submap)->select();
        }
        return $result;
    }

    /**
     *  [获取装修攻略分类]
     *
     *
     */
    public function getZxglClass($ids, $limit = 10)
    {
        if (!empty($ids)) {
            $map['obsolete'] = ["EQ", 0];
            $map['is_new'] = ["EQ", 1];
            $map['pid'] = ["in", $ids];
            $data = M("www_article_class")->field("id, classname, shortname")->where($map)->limit($limit)->select();
            return $data;
        }
    }

    /**
     * 根据装修阶段id获取下面所有层级的文章分类
     * author: mcj
     * @param $id 阶段ID
     * @return mixed
     */

    public function getZxlcChildren($id)
    {
        $tree = $this->articleClass;
        if (isset($tree[87]['child'][$id])) {
            return $tree[87]['child'][$id]['ids'];
        }
        return [];
    }

    public function getClassByShort($short, $field = '*')
    {
        if (is_array($short)) {
            $where = [
                'shortname' => ['in', $short]
            ];
        } else {
            $where = [
                'shortname' => ['eq', $short]
            ];
        }
        return $this->field($field)->where($where)->select();
    }

    /**
     * 根据第一级分类获取第三级分类列表
     * @param $grandClass
     * @param $count
     * @return mixed
     */
    public function getByGrandParent($grandClass, $count)
    {
        $map['wac.obsolete'] = ['eq', 0];
        $map['wac.is_new'] = ['eq', 1];
        $map['wac.level'] = ['eq', 3];
        $map['wac_g.id'] = ['eq', $grandClass];
        $data = $this->alias('wac')
            ->join('qz_www_article_class wac_p on wac.pid = wac_p.id and wac_p.obsolete=0 and wac_p.level=2 and wac_p.is_new=1')
            ->join('qz_www_article_class wac_g on wac_p.pid = wac_g.id and wac_g.obsolete=0 and wac_g.level=1 and wac_g.is_new=1')
            ->where($map)
            ->field('wac.id,wac.classname,wac.shortname')
            ->order('wac.seq desc,wac.id desc')
            ->limit($count)
            ->select();
        return $data;
    }

    /**
     * 根据第一级分类和第三级分类的shortname获取第三级分类详情
     * @param $grandClass
     * @param $shortname
     * @return mixed
     */
    public function getByGrandClassAndShortName($grandClass, $shortname)
    {
        $map['wac.obsolete'] = ['eq', 0];
        $map['wac.is_new'] = ['eq', 1];
        $map['wac.shortname'] = ['eq', $shortname];
        $map['wac_g.id'] = ['eq', $grandClass];
        $data = $this->alias('wac')
            ->join('qz_www_article_class wac_p on wac.pid = wac_p.id and wac_p.obsolete=0 and wac_p.level=2 and wac_p.is_new=1')
            ->join('qz_www_article_class wac_g on wac_p.pid = wac_g.id and wac_g.obsolete=0 and wac_g.level=1 and wac_g.is_new=1')
            ->where($map)
            ->field('wac.id,wac.pid,wac.classname,wac.level,wac.shortname,wac.title,wac.keywords,wac.description,wac_g.id grand_class,wac_p.id parent_class,wac_p.classname parent_name,wac_p.shortname parent_shortname')
            ->find();
        if(empty($data)){
            $data = $this->alias('wac')
                ->join('qz_www_article_class wac_g on wac.pid = wac_g.id and wac_g.obsolete=0 and wac_g.level=1 and wac_g.is_new=1')
                ->where($map)
                ->field('wac.id,wac.pid,wac.classname,wac.level,wac.shortname,wac.title,wac.keywords,wac.description,wac_g.id grand_class,wac_g.classname grand_name,wac_g.shortname grand_shortname')
                ->find();
        }
        return $data;
    }

    /**
     * 根据子类获取父类
     * @param $id
     * @return mixed
     */
    public function getParentClassBySonClass($id)
    {
        $map['wac.id'] = ['eq', $id];
        $map['wac.obsolete'] = ['eq', 0];
        $map['wac.is_new'] = ['eq', 1];
        $data = $this->alias('wac')
            ->join('qz_www_article_class wac_p on wac.pid = wac_p.id and wac_p.obsolete=0 and wac_p.level=2 and wac_p.is_new=1')
            ->where($map)
            ->field('wac_p.id,wac_p.shortname,wac_p.classname')
            ->find();
        return $data;
    }
}