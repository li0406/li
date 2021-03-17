<?php
namespace Common\Model;
use Think\Model;

class BaikeModel extends Model{

    //查询百科列表
    public function getList($condition='',$orderBy,$row = 10){
        $map["visible"] = array("EQ",'0');
        if(!empty($condition)){
            $map = array_merge($map,$condition);
        }
        //按分类
        if(!empty($condition['cateId'])){
            $cid = $condition['cateId'];
            //Note: 为了减少逻辑，对于一级分类ID直接判断
            $categoryColumn = $cid <= 6 ? 'cid' : 'sub_category';
            $map[$categoryColumn]  = array("EQ",$cid);
        }
        $Db = M('baike');
        $result = $Db->field('id,item,title,tags,tags_name,thumb,post_time,views')->order($orderBy)->limit("0,".$row)->where($map)->select();
        return $result;
    }

    //查询列表  根据条件condition
    public function getListByCategory($condition,$pagesize= 1,$pageRow = 10){
        if(isset($condition['visible'])){
            $map['a.visible'] = $condition['visible'];
        }else{
            $map['a.visible'] = array("EQ",'0');
        }
        if(isset($condition['uid'])){
            $map['a.uid'] = $condition['uid'];
        }
        //如果 cid 不为空
        if(!empty($condition['cateId'])){
            $cid = $condition['cateId'];
            //Note: 为了减少逻辑，对于一级分类ID直接判断
            $categoryColumn = $cid <= 6 ? 'a.cid' : 'a.sub_category';
            $map[$categoryColumn]  = array("EQ",$cid);
        }
        //如果关键词不为空
        if(isset($condition['keyword'])){
            $map['a.title']  = array('like','%'.$condition['keyword'].'%');
        }
        if(isset($condition['dist'])){
            $map['a.is_top']  = $condition['dist'];
        }
        //dump($map);
        $Db = M('baike');
        $count = $Db->alias("a")
                  ->where($map)
                  ->count();
        $result = $Db->alias("a")
                      ->field('a.id,a.item,a.sub_category,a.description,a.title,a.post_time,a.views,a.visible,a.review,a.remove,c.cid,c.name')
                      ->join("inner join qz_baike_category as c on a.sub_category = c.cid")
                      ->order($condition['orderBy'])
                      ->limit($pagesize.",".$pageRow)
                      ->where($map)
                      ->select();
        return array("result"=>$result,"count"=>$count);
    }

    //查询推荐百科列表 - 有分类
    public function getTopList($pageRow = 10){
        $map['a.visible'] = array("EQ",'0');
        $map['a.thumb']  = array("NEQ",'');
        $map['a.is_top']  = array("EQ",'1');
        //de($map);
        $Db = M('baike');
        $result = $Db->alias("a")
                      ->field('a.id,a.item,a.sub_category,a.title,a.thumb,a.content,a.post_time,a.views,c.cid,c.name,c.url')
                      ->join("inner join qz_baike_category as c on a.cid = c.cid")
                      ->order('a.post_time DESC')
                      ->limit("0,".$pageRow)
                      ->where($map)
                      ->select();
        return $result;
    }

    //取首页推荐分类
    public function getTopCategory($num){
        $map['is_top'] = '1';
        $category = M('baike_category')->field('cid,url,name')->where($map)->limit("0,".$num)->select();
        foreach ($category as $k => $v) {
            $cid = $v['cid'];
            $map = array(
                "sub_category" => array("EQ",$cid),
                "visible" => array("EQ",0)
            );
            $subList = M('baike')->field('id,item,title,thumb,content')->where($map)->order('views DESC')->limit("0,4")->select();
            foreach ($subList as $key => $value) {
                if($key == '0'){
                    $category[$k]['bid'] = $value['id'];
                    $category[$k]['title'] = $value['title'];
                    $category[$k]['thumb'] = $value['thumb'];
                    $category[$k]['content'] = htmlToText($value['content']);
                }else{
                    $value['content'] = htmlToText($value['content']);
                    $category[$k]['subList'][] = $value;
                }
            }
        }
        return $category;
    }

    //取手机版首页推荐
    public function getMobileTopCategory($num){
        $map['is_top'] = '1';
        $category = M('baike_category')->field('cid,url,name')->where($map)->limit("0,".$num)->select();
        foreach ($category as $k => $v) {
            $cid = $v['cid'];
            $map = array(
                "sub_category" => array("EQ",$cid),
                "visible" => array("EQ",0)
            );
            $subList = M('baike')->field('id,item,title,thumb,content')->where($map)->order('views DESC')->limit("0,3")->select();
            foreach ($subList as $key => $value) {
                $value['content'] = htmlToText($value['content']);
                $category[$k]['subList'][] = $value;
            }
        }
        return $category;
    }

    //查询推荐百科列表
    public function getTopBaike($pageRow = 10,$orderby = ''){
        $map['visible'] = array("EQ",'0');
        //$map['is_top']  = array("EQ",'1');
        $Db = M('baike');
        $orderby = empty($orderby) ? 'post_time DESC' : $orderby;

        $result = $Db->field('id,item,title,description,thumb,post_time,views')
                      ->order($orderby)
                      ->limit("0,".$pageRow)
                      ->where($map)
                      ->select();
        return $result;
    }

    //查询单个百科
    public function getBaike($id,$visible= true){
        if($visible == true){
            $map['visible'] = array("EQ",'0');
        }
        $map["id"] = array("EQ",$id);
        return M('baike')->field('*')->where($map)->find();
    }

    //更新浏览量
    public function updateViews($id){
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("baike")->where($map)->setInc('views');
    }

    //查询分类按URL
    public function getCategoryByUrl($url){
        $map["url"] = array("EQ",$url);
        return M('baike_category')->field('*')->where($map)->find();
    }

    //查询分类根据Cid
    public function getCategoryByCid($cid){
        $map["pid"] = array("EQ",$cid);
        return M('baike_category')->field('*')->where($map)->order('order_id')->select();
    }

    //查询分类
    public function getCategory($map=''){
        if(!empty($map)){
            return M('baike_category')->field('*')->where($map)->order('order_id')->select();
        }
        return M('baike_category')->field('*')->order('order_id')->select();
    }

    //删除
    public function remove($id,$uid){
        $map = array(
            "id"=>array("EQ",$id),
            "uid"=>array("EQ",$uid),
        );
        return M("baike")->where($map)->delete();
    }

    /**
     * 验证发布间隔
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function checkRate($uid)
    {
        $map = array(
            "uid" => array("EQ",$uid)
        );
        return M('baike')->field("post_time")->where($map)->order("id desc")->find();
    }
}