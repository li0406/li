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
            "is_delete" => array("EQ",1),
            "is_show" => array("EQ",1),
            "type" => array("IN", $type)
        );
        return $this->where($map)->count();
    }

    public function getList($type = 1,$pageIndex,$pageCount)
    {
        if (!is_array($type)) {
            $type = explode(",", $type);
        }

        $map = array(
            "is_delete" => array("EQ",1),
            "is_show" => array("EQ",1),
            "type" => array("IN",$type)
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

    public function getHotList($module,$limit)
    {
        $map = array(
            "is_delete" => array("EQ",1),
            "is_show" => array("EQ",1),
            "module" => array("EQ",$module),
            "is_hot" => array("EQ",1)
        );
        return $this->where($map)->field("id,name")->order("id desc")->limit($limit)->select();
    }

    public function getContentLabels($id,$module,$limit)
    {
        $map = array(
            "a.content_id" => array("EQ",$id),
            "a.module" => array("EQ",$module),
        );

        return M("thematic_content_related")->where($map)->alias("a")
            ->join("join qz_thematic_words b on a.thematic_id = b.id and b.is_show = 1")
            ->field("b.id,b.name")->limit($limit)->select();
    }

    public function getGonglueContentByIds($where, $field, $page = 0, $page_count = 3, $order = 't.addtime desc,t.id desc')
    {
        $buildSql = M('www_article')->alias('w')
            ->field($field)
            ->where($where)
            ->join('qz_thematic_content_related rel on rel.content_id = w.id')
            ->group('w.id')
            ->buildSql();
        return M()->table($buildSql)->alias('t')
            ->order($order)
            ->limit($page, $page_count)
            ->select();
    }

    public function getBaikeContentByIds($where, $field, $page = 0, $page_count = 3, $order = 't.post_time desc,t.id desc')
    {
        $buildSql = M('baike')->alias('b')
            ->field($field)
            ->where($where)
            ->join('qz_thematic_content_related rel on rel.content_id = b.id')
            ->group('b.id')
            ->buildSql();

        return M()->table($buildSql)->alias('t')
            ->order($order)
            ->limit($page, $page_count)
            ->select();
    }

    public function getWendaContentByIds($where, $field, $page = 0, $page_count = 3, $order = 't.anwsers desc,t.post_time desc,t.id desc')
    {
        $buildSql = M('ask')->alias('a')
            ->field($field)
            ->where($where)
            ->join('qz_thematic_content_related rel on rel.content_id = a.id')
            ->group('a.id')
            ->buildSql();

        return M()->table($buildSql)->alias('t')
            ->order($order)
            ->limit($page, $page_count)
            ->select();
    }


    public function getGonglueContent($where,$field,$page = 0,$page_count = 3,$order = 'w.addtime desc,w.id desc'){
        return M('www_article')->alias('w')
            ->field($field)
            ->where($where)
            ->order($order)
            ->limit($page,$page_count)
            ->select();
    }

    public function getBaikeContent($where,$field,$page = 0,$page_count = 3,$order = 'b.post_time desc,b.id desc'){
        return M('baike')->alias('b')
            ->field($field)
            ->where($where)
            ->order($order)
            ->limit($page,$page_count)
            ->select();
    }
    public function getWendaContent($where,$field,$page = 0,$page_count = 3,$order = 'a.anwsers desc,a.post_time desc,a.id desc'){
        return M('ask')->alias('a')
            ->field($field)
            ->where($where)
            ->order($order)
            ->limit($page,$page_count)
            ->select();
    }

    public function getContentIdsInfo($where){
        return M('thematic_content_related')
            ->where($where)
            ->select();
    }
}