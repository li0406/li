<?php

namespace Common\Model\Db;
use Think\Model;


class ThematicWordsModel extends Model
{
    public function getListCount($type = 1)
    {
        if (!is_array($type)) {
            $type = explode(",", $type);
        }

        $map = array(
            "is_delete" => array("EQ", 1),
            "is_show" => array("EQ", 1),
            "type" => array("IN", $type)
        );

        return $this->where($map)->count();
    }

    public function getList($type = 1, $pageIndex = 0, $pageCount = 0)
    {
        if (!is_array($type)) {
            $type = explode(",", $type);
        }

        $map = array(
            "is_delete" => array("EQ", 1),
            "is_show" => array("EQ", 1),
            "type" => array("IN", $type)
        );
        return $this->where($map)->field("id,name,type")->order("id desc")->limit($pageIndex,$pageCount)->select();
    }

    public function getThematicInfoById($id)
    {
        $map = array(
            "is_delete" => array("EQ",1),
            "is_show" => array("EQ",1),
            "id" => array("EQ",$id)
        );
        return $this->where($map)->field("id,name,title,keywords,description,type,createtime,uptime")->find();
    }

    public function getHotList($limit)
    {
        $map = array(
            "is_delete" => array("EQ",1),
            "is_show" => array("EQ",1),
            "is_hot" => array("EQ",1)
        );
        return $this->where($map)->field("id,name,type")->order("id desc")->limit($limit)->select();
    }

    public function getNewList($limit)
    {
        $map = array(
            "is_delete" => array("EQ",1),
            "is_show" => array("EQ",1)
        );
        return $this->where($map)->field("id,name,type")->order("id desc")->limit($limit)->select();
    }

    public function getNewTypeList($type, $limit = 0)
    {
        $map = array(
            "is_delete" => array("EQ", 1),
            "is_show" => array("EQ", 1),
            "type" => array("EQ", $type),
        );
        return $this->where($map)->field("id,name,type")->order("id desc")->limit($limit)->select();
    }

    public function getContentLabels($id, $module, $limit, $type = 1)
    {
        $map = array(
            "a.content_id" => array("EQ",$id),
            "a.module" => array("EQ",$module),
            "b.type" => array("EQ", $type)
        );

        return M("thematic_content_related")->where($map)->alias("a")
            ->join("join qz_thematic_words b on a.thematic_id = b.id and b.is_show = 1")
            ->field("b.id,b.`name`")->limit($limit)->select();
    }

    public function getNewLabels($id,$limit)
    {
        $map = array(
            "id" => array("ELT",$id),
            "is_delete" => array("EQ",1),
            "is_show" => array("EQ",1),
        );
        return $this->where($map)->order("id desc")->limit($limit)->select();
    }

    /**
     * 获取相邻关键词
     * @param  [type]  $id    [description]
     * @param  [type]  $type  [description]
     * @param  integer $limit [description]
     * @return [type]         [description]
     */
    public function getNearbyWords($id, $type, $limit = 15){
        $field = ["id", "name"];

        $map1 = array(
            "id" => array("GT", $id),
            "type" => array("EQ", $type),
            "is_delete" => array("EQ", 1),
            "is_show" => array("EQ", 1),
        );
        $subSql1 = $this->where($map1)->order("id asc")->field($field)->limit($limit)->buildSql();

        $map2 = array(
            "id" => array("LT", $id),
            "type" => array("EQ", $type),
            "is_delete" => array("EQ", 1),
            "is_show" => array("EQ", 1),
        );
        $subSql2 = $this->where($map2)->order("id desc")->field($field)->limit($limit)->buildSql();
        
        $sql = sprintf("(%s UNION ALL %s)", $subSql1, $subSql2);
        return M()->table($sql)->alias("t")
            ->order("t.id desc")->select();
    }

    public function getRelated($map, $field = "a.*", $limit)
    {
        return M("thematic_content_related")->alias("a")
            ->where($map)
            ->field($field)
            ->limit($limit)
            ->order('a.content_id desc')
            ->select();
    }

    public function getContentByThematicWords($map, $field = "r.*", $limit = 5)
    {
        $map['w.is_delete'] = ['eq', 1];
        $map['w.is_show'] = ['eq', 1];
        return $this->alias('w')
            ->join('qz_thematic_content_related r on r.thematic_id = w.id')
            ->where($map)
            ->field($field)
            ->limit($limit)
            ->order('r.content_id desc')
            ->select();
    }

    /**
     * 获取标签的家装
     * @param $map
     * @param string $field
     * @param int $limit
     * @return mixed
     */
    public function getThematicWordsContentByJz($map, $field = "r.*", $limit = 5)
    {
        $map['w.is_delete'] = ['eq', 1];
        $map['w.is_show'] = ['eq', 1];
        $map['t.type'] = ['eq', 2];
        $map['t.state'] = ['eq', 3];
        return $this->alias('w')
            ->join('qz_thematic_content_related r on r.thematic_id = w.id')
            ->join('qz_tu t on t.id = r.content_id')
            ->join('qz_tu_category c1 on c1.id = t.home_space_category')
            ->join('qz_tu_category c2 on c2.id = t.home_part_category')
            ->join('qz_tu_category c3 on c3.id = t.home_style_category')
            ->join('qz_tu_category c4 on c4.id = t.home_layout_category')
            ->join('qz_tu_image ti on ti.meitu_id = t.id')
            ->where($map)
            ->field($field)
            ->limit($limit)
            ->order('r.content_id desc')
            ->select();
    }

    /**
     * 获取标签的公装
     * @param $map
     * @param string $field
     * @param int $limit
     * @return mixed
     */
    public function getThematicWordsContentByGz($map, $field = "r.*", $limit = 5)
    {
        $map['w.is_delete'] = ['eq', 1];
        $map['w.is_show'] = ['eq', 1];
        $map['t.type'] = ['eq', 1];
        $map['t.state'] = ['eq', 3];
        return $this->alias('w')
            ->join('qz_thematic_content_related r on r.thematic_id = w.id')
            ->join('qz_tu t on t.id = r.content_id')
            ->join('qz_tu_category c ON c.id = t.pub_category')
            ->join('qz_tu_image ti on ti.meitu_id = t.id')
            ->where($map)
            ->field($field)
            ->limit($limit)
            ->order('r.content_id desc')
            ->select();
    }

    /**
     * 获取标签的攻略
     * @param $map
     * @param string $field
     * @param int $limit
     * @return mixed
     */
    public function getThematicWordsContentByGL($map, $field = "r.*", $limit = 5)
    {
        $map['w.is_delete'] = ['eq', 1];
        $map['w.is_show'] = ['eq', 1];
        $map['a.state'] = ['eq', 2];
        return $this->alias('w')
            ->join('qz_thematic_content_related r on r.thematic_id = w.id')
            ->join('qz_www_article a on a.id = r.content_id')
            ->join('qz_www_article_class_rel rel on rel.article_id = r.content_id')
            ->join('qz_www_article_class c on c.id = rel.class_id')
            ->where($map)
            ->field($field)
            ->limit($limit)
            ->order('r.content_id desc')
            ->select();
    }

    public function getThematicWordsContentByWenda($map, $field = "r.*", $limit = 5)
    {
        $map['w.is_delete'] = ['eq', 1];
        $map['w.is_show'] = ['eq', 1];
        $map['a.visible'] = ['eq', 0];
        $map['a.review'] = ['eq', 1];
        return $this->alias('w')
            ->join('qz_thematic_content_related r on r.thematic_id = w.id')
            ->join('qz_ask a on a.id = r.content_id')
            ->where($map)
            ->field($field)
            ->limit($limit)
            ->order('r.content_id desc')
            ->select();
    }
    public function getThematicWordsContentByBaike($map, $field = "r.*", $limit = 5)
    {
        $map['w.is_delete'] = ['eq', 1];
        $map['w.is_show'] = ['eq', 1];
        $map['b.visible'] = array("EQ",'0');
        $map['b.remove'] = array("EQ", '0');
        return $this->alias('w')
            ->join('qz_thematic_content_related r on r.thematic_id = w.id')
            ->join('qz_baike b on b.id = r.content_id')
            ->join('qz_baike_category c on c.cid = b.cid')
            ->where($map)
            ->field($field)
            ->limit($limit)
            ->order('r.content_id desc')
            ->select();
    }

    public function getTypeHotList($type, $limit = 0)
    {
        $map = array(
            "is_delete" => array("EQ", 1),
            "is_show" => array("EQ", 1),
            "type" => array("EQ", $type),
            "is_hot" => array("EQ", 1),
        );
        return $this->where($map)->field("id,name,type")->order("id desc")->limit($limit)->select();
    }

}