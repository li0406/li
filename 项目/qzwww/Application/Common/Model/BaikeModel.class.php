<?php
namespace Common\Model;
use Think\Model;

class BaikeModel extends Model{

    //查询百科列表
    public function getList($condition='',$orderBy,$row = 10,$field = ''){
        if(empty($field)){
            $field = 'id,item,title,tags,tags_name,thumb,post_time,views,content,description';
        }
        $map["visible"] = array("EQ",'0');
        $map["remove"] = array("EQ",'0');
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
        unset($map['cateId']);
        $Db = M('baike');
        $result = $Db->field($field)->order($orderBy)->limit("0,".$row)->where($map)->select();
        return $result;
    }

    //查询列表  根据条件condition
    public function getListByCategory($condition,$pagesize= 1,$pageRow = 10){
        $map['a.remove'] = array("EQ", '0');
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
                      ->field('a.id,a.item,a.sub_category,a.description,a.title,a.post_time,a.views,a.visible,c.cid,c.name')
                      ->join("inner join qz_baike_category as c on a.sub_category = c.cid")
                      ->order($condition['orderBy'])
                      ->limit($pagesize.",".$pageRow)
                      ->where($map)
                      ->select();
        return array("result"=>$result,"count"=>$count);
    }

    //查询推荐百科列表 - 有分类
    public function getTopList($pageRow = 10,$field = ''){
        if(empty($field)){
            $field = 'a.id,a.item,a.description,a.sub_category,a.title,a.thumb,a.content,a.post_time,a.views,c.cid,c.name,c.url';
        }
        $map['a.visible'] = array("EQ",'0');
        $map['a.thumb']  = array("NEQ",'');
        $map['a.choice']  = array("EQ",'1');
        $map['a.remove'] = array("EQ", '0');
        //de($map);
        $Db = M('baike');
        $result = $Db->alias("a")
                      ->field($field)
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
            $subList = M('baike')->field('id,item,title,thumb,content,post_time')->where(array('sub_category' => $cid , 'visible' =>'0','remove'=>'0'))->order('views DESC')->limit("0,4")->select();
            foreach ($subList as $key => $value) {
                if($key == '0'){
                    $category[$k]['bid'] = $value['id'];
                    $category[$k]['title'] = $value['title'];
                    $category[$k]['thumb'] = $value['thumb'];
                    $category[$k]['post_time'] = $value['post_time'];
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
            $subList = M('baike')->field('id,item,title,thumb,content')->where(array('sub_category' => $cid , 'visible' =>'0'))->order('views DESC')->limit("0,3")->select();
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
        $map["remove"] = array("EQ",0);
        return M('baike')->field('*')->where($map)->find();
    }

    //更新浏览量
    public function updateViews($id){
        return M("baike")->where(array('id' => $id))->setInc('views',1,60);
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

    //查询id根据分类
    public function getCategoryById($cid){
        $map["cid"] = array("EQ",$cid);
        return M('baike_category')->where($map)->find();
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

    //大家都在看
    public function getCategoryRecommend($map, $order, $count, $page = 1)
    {
        $map['b.visible'] = array("EQ", '0');
        $map['b.remove'] = array("EQ", '0');
        $map['b.thumb'] = array("NEQ", '');
        return M('baike')->alias("b")
            ->field('b.id,b.title,b.thumb,b.content,b.description,b.recommend,b.views,b.post_time,b.source,u.id as uid,u.name AS register_uname,x.name AS system_uname,u.blocked,q.bm,c.name')
            ->join("inner join qz_baike_category as c on c.cid = b.sub_category")
            ->join("inner join qz_user as u on u.id = b.uid")
            ->join("inner join qz_quyu as q on u.cs = q.cid ")
            ->join("left join qz_adminuser as x on x.id = b.uid")
            ->where($map)
            ->page($page, $count)
            ->order($order)
            ->select();
    }

    public function getBkByCategory($map, $page, $count,$keyword=null)
    {
        if ($keyword) {
            $map['b.title'] = array("like", '%'.$keyword.'%');
        }
        $map['b.visible'] = array("EQ", '0');
        $map['b.remove'] = array("EQ", '0');
        $map['b.thumb'] = array("NEQ", '');
        return M('baike')->alias("b")
            ->field('b.id,b.title,b.thumb,b.description,b.content,b.recommend,b.views,b.post_time,u.id as uid,u.jc,q.bm,c.cid,c.pid')
            ->join("inner join qz_baike_category as c on c.cid = b.sub_category")
            ->join("inner join qz_user as u on u.id = b.uid")
            ->join("inner join qz_quyu as q on u.cs = q.cid ")
            ->where($map)
            ->order("post_time desc")
            ->page($page, $count)
            ->select();
    }

    public function getBkByCategoryCount($map,$keyword=null)
    {
        if ($keyword) {
            $map['b.title'] = array("like", '%'.$keyword.'%');
        }
        $map['b.visible'] = array("EQ", '0');
        $map['b.remove'] = array("EQ", '0');
        $map['b.thumb'] = array("NEQ", '');
        return M('baike')->alias("b")
            ->field('b.id,b.title,b.thumb,b.content,b.recommend,b.views,b.post_time,u.id as uid,u.jc,q.bm,c.cid,c.pid')
            ->join("inner join qz_baike_category as c on c.cid = b.cid")
            ->join("inner join qz_user as u on u.id = b.uid")
            ->join("inner join qz_quyu as q on u.cs = q.cid ")
            ->where($map)
            ->count();
    }

    //攻略首页获取装修百科下的最新两条文章
    public function getArticleByGonglue($limit=2){
        return M("baike")->field("id, title, content, post_time, thumb")->where(['visible'=>0, 'remove'=>0])->order("post_time desc")->limit($limit)->select();
    }

    //根据id获取百科分类
    public function getRealPid($cid){
        return M('baike_category')->where(array('cid'=>$cid))->find();
    }

    public function getCategoryByBaikeId($ids,$field = 'b.id')
    {
        $where = [
            'b.id' => ['in', $ids]
        ];
        return $this->alias('b')
            ->field($field)
            ->where($where)
            ->join('qz_baike_category c on c.cid = b.cid')
            ->select();
    }

    //查询最新百科
    public function getNewBaikeList($pageRow = 10,$field = '',$pass_category_id = [], $only_category_id = []){
        if(empty($field)){
            $field = 'a.id,a.item,a.description,a.sub_category,a.title,a.thumb,a.content,a.post_time,a.views,c.cid,c.name,c.url';
        }
        $map['a.visible'] = array("EQ",'0');
        $map['a.remove'] = array("EQ", '0');
        if(count($pass_category_id) > 0){
            $map['a.cid'] = array("not in", $pass_category_id);
        }

        if(count($only_category_id) > 0){
            $map['a.cid'] = array();
            foreach ($only_category_id as $v) {
                $map['a.cid'][] = array('EQ', $v);
            }

            $map['a.cid'][] = 'OR';
        }
        
        $Db = M('baike');
        $result = $Db->alias("a")
            ->field($field)
            ->join("inner join qz_baike_category as c on a.cid = c.cid")
            ->order('a.post_time DESC')
            ->limit("0,".$pageRow)
            ->where($map)
            ->select();
        return $result;
    }
    public function getListByTagName($tagName,$row,$field,$order){
        $map['b.visible'] = array("EQ",0);
        $map['b.remove'] = array("EQ", 0);
        $map['b.review'] = array("EQ",1);
        $map['tr.module'] = array("EQ", 2);
        $map['t.name'] = array('EQ',$tagName);
        $res = M('baike')->alias('b')
            ->join('INNER JOIN qz_thematic_content_related tr on b.id = tr.content_id')
            ->join('INNER JOIN qz_thematic_words t on tr.thematic_id = t.id')
            ->field($field)
            ->where($map)
            ->order($order)
            ->limit($row)
            ->select();
        return $res;
    }

    public function getBaikeClass($id, $field = "b.*")
    {
        $map['b.id'] = ['in', $id];
        return M('baike')->alias('b')
            ->join('qz_baike_category c on c.cid = b.cid')
            ->where($map)
            ->field($field)
            ->select();
    }
}
