<?php
namespace Common\Model;
use Think\Model;

class TagsModel extends Model{

    //查询Tags信息
    public function getTags($id){
        return M('tags')->field('*')->where(array('id' => $id))->find();
    }

    //查询最新标签列表
    public function getNewTags($type,$num){
        if(!empty($type)){
            $map[$this->filterTags($type)] =array("GT",'0');
        }else{
            $map['id'] = ['GT','0'];
        }
        $result = M('Tags')->field('id,name,type')->where($map)->order('time DESC')->limit("0,".$num)->select();
        return $result;
    }

    //查询推荐标签列表
    public function getTopTags($type,$num){
        $map['istop'] ='1';
        if(!empty($type)){
            $map[$this->filterTags($type)] =array("GT",'0');
        }
        $result = M('Tags')->field('id,name')->where($map)->order('time DESC')->limit("0,".$num)->select();
        return $result;
    }

    //查询热门标签列表
    public function getHotTags($type,$num){
        $map['istop'] ='1';
        if(!empty($type)){
            $map[$this->filterTags($type)] =array("GT",'0');
        }
        $result = M('Tags')->field('id,name')->where($map)->order('rand() ')->limit("0,".$num)->select();
        return $result;
    }

    //查询问答列表
    public function getAskList($condition,$pagesize= 1,$pageRow = 10){
        $map['a.visible'] = '0';
        //如果关键词不为空
        if(isset($condition['tags'])){
            $tags = $condition['tags'];
            $tagName = M('tags')->field('id,name')->where(array('id' =>$tags))->find();
            $tagName = $tagName['name'];
            //$map['a.tags_name']  = array('like',''.$tagName.',%');
            $map['_string']= " find_in_set('$tagName',REPLACE(tags_name,',',',')) ";
        }
        $DB = M('ask');
        $count = $DB->alias("a")->where($map)->count();
        $result = $DB->alias("a")
                      ->field('a.id,a.sub_category,a.title,a.description,a.tags,a.tags_name,a.post_time,a.anwsers,a.views,a.status,c.cid,c.name')
                      ->join("inner join qz_ask_category as c on a.sub_category = c.cid")
                      ->order($condition['orderBy'])
                      ->limit($pagesize.",".$pageRow)
                      ->where($map)
                      ->select();
        return array("result"=>$result,"count"=>$count);
    }

    //查询攻略列表
    public function getArticleList($condition,$pagesize= 1,$pageRow = 10){
        $map['a.state'] = '2';
        if(isset($condition['tags'])){
            $tags = $condition['tags'];
            $map['_string']= " find_in_set('$tags',REPLACE(a.tags,',',',')) ";
        }
        $condition['orderby'] = 'addtime DESC';
        $Db = M('www_article');
        $count = $Db->alias("a")->where($map)->count();
        $result = $Db->alias("a")
                        ->join("left join qz_www_article_class_rel as r on r.article_id = a.id")
                        ->join("left join qz_www_article_class as c on c.id = r.class_id")
                        //->field("a.id,a.title,a.addtime")
                        ->field("a.*,c.shortname,c.classname")
                        ->order($condition['orderBy'])
                        ->limit($pagesize.",".$pageRow)
                        ->where($map)
                        ->select();
        return array("result"=>$result,"count"=>$count);
    }

    //查询美图列表
    public function getMeituList($condition,$pagesize= 1,$pageRow = 10){
        if(isset($condition['tags'])){
            $tags = $condition['tags'];
            $map['_string']= " find_in_set('$tags',REPLACE(tags,',',',')) ";
        }
        $DB = M('meitu');
        //限制状态
        $map['state'] = 1;
        $count = $DB->where($map)->count();
        $result = $DB->field('id,title,tags')
                      ->order('time DESC')
                      ->limit($pagesize.",".$pageRow)
                      ->where($map)
                      ->select();
        //循环取图片列表
        foreach ($result as $k => $v) {
            $maps['caseid'] = $v['id'];
            $result[$k]['imgs'] = M('meitu_img')->field('img_path')->order('px')->limit('0,6')->where($maps)->select();
        }
        return array("result"=>$result,"count"=>$count);
    }

    //查询日记列表
    public function getDiaryList($condition,$pagesize= 1,$pageRow = 10){
        $map['d.stat'] = '1';
        if(isset($condition['tags'])){
            $tags = $condition['tags'];
            $map['_string']= " find_in_set('$tags',REPLACE(tags,',',',')) ";
        }
        $DB = M('diary_info');
        $count = $DB->alias("d")->where($map)->count();
        $result = $DB->alias("d")
                        ->field('d.id,d.user_id,d.title,d.diary_count,d.img_logo,d.content,d.page_view,d.concern_count,d.reply_count,u.id uid,u.name username,u.logo')
                        ->join("left join qz_user as u on u.id = d.user_id")
                        ->order('diary_time DESC')
                        ->limit($pagesize.",".$pageRow)
                        ->where($map)
                        ->select();
        return array("result"=>$result,"count"=>$count);
    }


    //查询百科列表
    public function getBaikeList($condition,$pagesize= 1,$pageRow = 10){
        $map['b.visible'] = '0';
        $map['b.remove'] = '0';
        //如果关键词不为空
        if(isset($condition['tags'])){
            $tags = $condition['tags'];
            $tagName = M('tags')->field('id,name')->where(array('id' => $tags))->find();
            $tagName = $tagName['name'];
            $map['_string']= " find_in_set('$tagName',REPLACE(tags_name,',',',')) ";
        }
        $DB = M('baike');
        $count = $DB->alias("b")->where($map)->count();
        $result = $DB->alias("b")
                      ->field('b.id,b.sub_category,b.title,b.description,b.tags,b.tags_name,b.post_time,c.cid,c.name')
                      ->join("inner join qz_baike_category as c on b.sub_category = c.cid")
                      ->order($condition['orderBy'])
                      ->limit($pagesize.",".$pageRow)
                      ->where($map)
                      ->select();
        return array("result"=>$result,"count"=>$count);
    }

    //获取统计
    public function getCount($type,$tags){
        $map['_string']= " find_in_set('$tags',REPLACE(tags,',',',')) ";
        if($type == 'diary'){
            return M('diary_info')->where($map)->count();
        }
        if($type == 'meitu'){
            $map['state'] = 1;
            return M('meitu')->where($map)->count();
        }
        if($type == 'article'){
            return M('www_article')->where($map)->count();
        }
        if($type == 'ask'){
            $tagName = M('tags')->field('id,name')->where(array('id' => $tags))->find();
            $tagName = $tagName['name'];
            $map['_string']= " find_in_set('$tagName',REPLACE(tags_name,',',',')) ";
            return M('ask')->where($map)->count();
        }
    }

    //根据名称获取标签信息
    public function findTagsByName($name,$type = ''){
        $map = array(
                "_string"=>"FIND_IN_SET(name,'$name')"
                     );
        if(!empty($type)){
            $map["type"] = array("EQ",$type);
        }
        return M("tags")->where($map)->select();
    }

    //更新标签使用数
    public function setTagsNum($id,$act,$type){
        if(empty($id) || empty($act) || empty($type)){
            return false;
        }
        //如果传入的type是数字
        if(is_numeric($type)){
            //规定字段
            $countField = array('1'=>'article_count','2'=>'meitu_count','3'=>'diary_count','4'=>'ask_count','5'=>'subarticle_count','6'=>'baike_count');
            $type = $countField[$type];
        }

        if($act == 'Inc'){
            return M("tags")->where(array('id' => $id))->setInc($type);
        }else{
            return M("tags")->where(array('id' => $id))->setDec($type);
        }
    }

    /**
     * 添加标签
     */
    public function addTags($data){
        return M("tags")->add($data);
    }

    //用于过滤空标签，增加新的标签类型需要在此加入类型
    public function filterTags($type){
        if($type == '1'){
            return 'article_count';
        }
        if($type == '2'){
            return 'meitu_count';
        }
        if($type == '3'){
            return 'diary_count';
        }
        if($type == '4'){
            return 'ask_count';
        }
        if($type == '5'){
            return 'subarticle_count';
        }
        if($type == '6'){
            return 'baike_count';
        }
    }

    /**
     * 获取美图数量
     * @param  array  $map 查询条件
     * @return int
     */
    public function getTagsCount($map = array())
    {
        return M('tags')->where($map)->count();
    }

    /**
     * 获取美图列表
     * @param  array   $map   查询条件
     * @param  string  $field 查询字段
     * @param  string  $order 排序
     * @param  integer $start 查询开始位置
     * @param  integer $each  查询数量
     * @return array
     */
    public function getTagsList($map = array(), $field = '*', $order = 'id DESC', $start = 0, $each = 90)
    {
        return M('tags')->field($field)->where($map)->limit($start, $each)->select();
    }

    /**
     * 根据标签ID获取标签
     * @param  string $ids 标签ID
     * @return array
     */
    public function getTagsByTagsId($ids = '')
    {
        if (empty($ids)) {
            return false;
        }
        if (!is_array($ids)) {
            $ids = array_filter(explode(',', $ids));
        }
        $map['id'] = array('IN', $ids);
        return M('tags')->where($map)->select();
    }

}