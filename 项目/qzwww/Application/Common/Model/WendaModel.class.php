<?php
/**
 * 问答
 */
namespace Common\Model;
use Think\Model;
class WendaModel extends Model{
    protected $autoCheckFields = false;
    public function getListByTagName($tagName,$row,$field,$order){
        $map['b.visible'] = array("EQ",0);
        $map['b.review'] = array("EQ", 1);
        $map['tr.module'] = array("EQ",3);
        $map['t.name'] = array('EQ',$tagName);
        $res = M('ask')->alias('b')
            ->join('INNER JOIN qz_thematic_content_related tr on b.id = tr.content_id')
            ->join('INNER JOIN qz_thematic_words t on tr.thematic_id = t.id')
            ->field($field)
            ->where($map)
            ->order($order)
            ->limit($row)
            ->select();
        return $res;
    }
}