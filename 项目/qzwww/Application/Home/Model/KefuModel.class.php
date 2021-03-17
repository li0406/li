<?php
namespace Common\Model;
use Think\Model;

class KefuModel extends Model{

    //查询列表
    public function getList($condition='',$pagesize= 1,$pageRow = 10){
        $map["is_remove"] = array("EQ",'0');
        //按分类
        if(!empty($condition['cid'])){
            $cid = $condition['cid'];
            $map['cid']  = array("EQ",$cid);
        }
        //如果关键词不为空
        if(isset($condition['keyword'])){
            $map['title']  = array('like','%'.$condition['keyword'].'%');
        }
        $Db = M('kefu');
        $count = $Db->where($map)->count();
        $result = $Db->field('id,cid,title,content,post_time')
                    ->order($condition['orderBy'])
                    ->limit($pagesize.",".$pageRow)
                    ->where($map)
                    ->select();
        return array("result"=>$result,"count"=>$count);
    }

    //查询单个
    public function getKefu($id){
        $map['visible'] = array("EQ",'0');
        $map["id"] = array("EQ",$id);
        return M('kefu')->field('*')->where($map)->find();
    }

    //查询分类 根据URL
    public function getCategoryByUrl($url){
        $map["url"] = array("EQ",$url);
        return M('kefu_category')->field('*')->where($map)->find();
    }

    //查询分类 根据Cid
    public function getCategoryByCid($cid){
        $map["pid"] = array("EQ",$cid);
        return M('kefu_category')->field('*')->where($map)->order('sort')->select();
    }

    //查询分类
    public function getCategory($map=''){
        return M('kefu_category')->field('*')->where($map)->order('pid,sort')->select();
    }
}