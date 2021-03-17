<?php

namespace Common\Model\Db;

use Think\Model;

class TuCategoryModel extends Model
{
    protected $tableName = 'tu_category';


    /**
     * 根据条件获取二级分类
     * @param $map
     * @return mixed
     */
    public function getMeituLv2Category($map, $field = 'id,name,url' , $sort = 'sort desc,created_at desc,id desc',$limit = '')
    {
        if(!$map['is_enable']){
            $map['is_enable'] = array('EQ',1);
        }
        return $this->where($map)->field($field)->order($sort)->limit($limit)->select();
    }


    /**
     * 首页获取家装分类的父级分类
     */
    public function getHomeTuParentCategory()
    {
        // 2表示家装， 家装有空间、风格、局部、户型
        $map = [
            "parent" => array("eq", 2)
        ];
        return $this->where($map)->field("id,parent,name")->select();
    }


    /**
     * 获取子分类下发布图片的数量
     * @param int $parent  父级分类   7，8，9，10    7:空间，8:局部, 9:风格, 10:户型   分类是固定了的
     * @return array
     */
    public function getHomeTuChildCategoryInfo($parent = 0)
    {
        if (!$parent) {
            return [];
        }
        $map = [
            "t.parent" => array("eq", $parent),
            "t.is_enable" => array("eq", 1)
        ];

        if ($parent == 7) { // 空间
            $buildSql = $this->alias("t")
                ->field("t.id,t.url,t.parent,t.`name` as category_name,t.sort,t.created_at,count(tu.id) as tu_count")
                ->where($map)
                ->join("LEFT JOIN qz_tu tu on tu.home_space_category = t.id and tu.type = 2 and tu.state = 3")
                ->where("tu.id is not null")
                ->group("t.id")
                ->order("t.sort desc,t.created_at desc,t.id desc")
                ->buildSql();

        } elseif ($parent == 8) {   // 局部
            $buildSql = $this->alias("t")
                ->field("t.id,t.url,t.parent,t.`name` as category_name,t.sort,t.created_at,count(tu.id) as tu_count")
                ->where($map)
                ->join("LEFT JOIN qz_tu tu on tu.home_part_category = t.id and tu.type = 2 and tu.state = 3")
                ->where("tu.id is not null")
                ->group("t.id")
                ->order("t.sort desc,t.created_at desc,t.id desc")
                ->buildSql();

        } elseif ($parent == 9) {   // 风格
            $buildSql = $this->alias("t")
                ->field("t.id,t.url,t.parent,t.`name` as category_name,t.sort,t.created_at,count(tu.id) as tu_count")
                ->where($map)
                ->join("LEFT JOIN qz_tu tu on tu.home_style_category = t.id and tu.type = 2 and tu.state = 3")
                ->where("tu.id is not null")
                ->group("t.id")
                ->order("t.sort desc,t.created_at desc,t.id desc")
                ->buildSql();

        } elseif ($parent == 10) {  // 户型
            $buildSql = $this->alias("t")
                ->field("t.id,t.url,t.parent,t.`name` as category_name,t.sort,t.created_at,count(tu.id) as tu_count")
                ->where($map)
                ->join("LEFT JOIN qz_tu tu on tu.home_layout_category = t.id and tu.type = 2 and tu.state = 3")
                ->where("tu.id is not null")
                ->group("t.id")
                ->order("t.sort desc,t.created_at desc,t.id desc")
                ->buildSql();

        } else {
            return [];
        }

        $list = $this->table($buildSql)->alias("t")
            ->order("t.sort desc,t.created_at desc,t.id desc")
            ->limit(10)
            ->select();

        return  $list;

    }


    public function getCategoryTuImg($categoryId, $parent, $imgid = [])
    {
        if ($parent == 7) { // 空间
            $map = [
                "home_space_category" => array("eq", $categoryId),
                "type" => array("eq", 2),
                "state" => array("eq", 3),
            ];
        } elseif ($parent == 8) {   // 局部
            $map = [
                "home_part_category" => array("eq", $categoryId),
                "type" => array("eq", 2),
                "state" => array("eq", 3),
            ];
        } elseif ($parent == 9) {   // 风格
            $map = [
                "home_style_category" => array("eq", $categoryId),
                "type" => array("eq", 2),
                "state" => array("eq", 3),
            ];
        } elseif ($parent == 10) {  // 户型
            $map = [
                "home_layout_category" => array("eq", $categoryId),
                "type" => array("eq", 2),
                "state" => array("eq", 3),
            ];
        } else {
            return [];
        }
        if (count($imgid) > 0) {
            $map["id"] = array("not in", $imgid);
        }

        $buildSql = M("tu")->where($map)->field("id")->order("publish_time desc,id desc")->limit(1)->buildSql();

        return $this->table($buildSql)->alias("t")
            ->field("t.id,ti.src as img_url")
            ->join("INNER JOIN qz_tu_image ti on ti.meitu_id = t.id")
            ->limit(1)
            ->find();


    }



}
