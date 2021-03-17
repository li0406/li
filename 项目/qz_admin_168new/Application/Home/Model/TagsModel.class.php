<?php

namespace Home\Model;
Use Think\Model;

/**
* 标签表*
*/
class TagsModel extends Model
{
    protected $autoCheckFields = false;

    /**
     * 获取所有标签
     * @return [type] [description]
     */
    public function getAllTags($type = 1)
    {
        $map = array(
            "type" => array("EQ",$type)
        );
        return M("tags")->field("id,name")->select();
    }

    /**
     * 添加标签
     * @param [type] $data [description]
     */
    public function addTag($data)
    {
        return  M("tags")->add($data);
    }

    /**
     * 批量添加
     * @param [type] $data [description]
     */
    public function addAllTag($data)
    {
        return  M("tags")->addAll($data);
    }

    /**
     * [deleteTagsById 删除标签]
     * @param  [type] $ids [description]
     * @return [type]     [description]
     */
    public function deleteTagsByIds($ids)
    {
        if(!empty($ids)){
            if(!is_array($ids)){
                $ids = array_filter(explode(',', $ids));
            }
            if(!empty($ids)){
                $result = M("tags")->where(array('id' => array('IN',$ids)))->delete();
            }
            return $result;
        }
        return false;
    }

    public function getTagsById($id)
    {
        return M("tags")->where(array('id' => $id))->find();
    }

    /**
     * [judgeTagExistByName 根据标签名判断标签是否存在]
     * @param  [type] $name [标签名]
     * @return [type]       [bool]
     */
    public function judgeTagExistByName($name){
        //标签名为空为设置为已存在
        if (empty($name)) {
            return true;
        }
        $map = array('name' => array('EQ', $name));
        $count = M("tags")->where($map)->count();
        if (!empty($count)) {
            return true;
        }
        return false;
    }

    //获取标签数量
    public function getTagsCount($keyword='',$istop='', $type='',$location = ''){
        if(!empty($keyword)){
            $map["name"] = array("LIKE","%$keyword%");
        }

        if (!empty($location) && !empty($keyword)) {
            if ($location == 1) {
                $map["name"] = array("LIKE","$keyword%");
            } else {
                $map["name"] = array("LIKE","%$keyword");
            }
        }

        $map["id"] = array('GT',0);
        //注意此处不能使用empty
        if($istop != ''){
            $map["istop"] = $istop;
        }

        $array = array('article_count','meitu_count','diary_count','ask_count','subarticle_count','baike_count');
        if(in_array($type,$array)){
            $map[$type] = array('GT',0);
        }
        return M("tags")->where($map)->count();
    }

    //获取标签列表
    public function getTagsList($pageIndex, $pageCount, $keyword, $istop, $type, $order,$location = ''){

        if(!empty($keyword)){
            $map["name"] = array("LIKE","%$keyword%");
        }

        if (!empty($location) && !empty($keyword)) {
            if ($location == 1) {
                $map["name"] = array("LIKE","$keyword%");
            } else {
                $map["name"] = array("LIKE","%$keyword");
            }
        }

        $map["id"] = array('GT',0);
        //注意此处不能使用empty
        if($istop != ''){
            $map["istop"] = $istop;
        }

        $array = array('article_count','meitu_count','diary_count','ask_count','subarticle_count','baike_count');
        if(in_array($type,$array)){
            $map[$type] = array('GT',0);
        }

        if(!empty($order)){
            $order = $order . " , istop desc";
        }else{
            $order = "istop desc,id desc";
        }

        return M("tags")->where($map)->limit($pageIndex.",".$pageCount)
                        ->order($order)
                        ->select();
    }

    //获取标签信息
    public function getTag($id){
        $map["id"] = $id;
        return M("tags")->where($map)->find();
    }

    //获取标签信息
    public function getTagsInfo($id){
        $map = array(
                "id"=>array("IN",$id)
                     );
         return M("tags")->where($map)->select();
    }

    //编辑标签
    public function editTags($id,$data){
        return M("tags")->where(array('id'=>$id))->save($data);
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
            return M("tags")->where(array('id'=>$id))->setInc($type);
        }else{
            return M("tags")->where(array('id'=>$id))->setDec($type);
        }
    }

    //推荐标签
    public function setTopTags($id,$type){
        $data['istop'] = $type;
        return M("tags")->where(array('id'=>$id))->save($data);
    }

    //删除标签
    public function delTags($id){
        $map["id"] = $id;
        return M("tags")->where($map)->delete();
    }

    //删除标签
    public function delLotTags($ids){
        $map["id"] = array('IN',$ids);
        return M("tags")->where($map)->delete();
    }

    /**
     * 根据文章类别获取推荐标签
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public function getTagsByType($type,$limit){
        $map = array(
                "istop"=>array("EQ",1),
                "type"=>array("EQ",$type)
                     );
        return M("tags")->where($map)->limit($limit)->select();
    }

    //根据名称获取标签信息
    public function findTagsByName($name){
        $map["name"] = array("IN",$name);
        return M("tags")->where($map)->select();
    }

    //根据ID获取标签信息
    public function findTagsById($id,$type = ''){
        $map = array(
                "_string"=>"FIND_IN_SET(id,'$id')"
        );
        if(!empty($type)){
            $map["type"] = array("EQ",$type);
        }
        return M("tags")->where($map)->select();
    }



    /**
     * [getTagIdsByTagNames 通过标签名字获取标签ID]
     * @param  [type] $names [标签名数组]
     * @param  [type] $extra [获取信息内容，1：只获取标签ID，用逗号分开，2：获取标签名字=>标签ID数组，3：获取标签ID=>标签名数组]
     * @return [type]        [description]
     */
    public function getTagIdsByTagNames($names='',$extra = 1)
    {
        //判断是否为空，为空返回false
        if(empty($names)){
            return '';
        }

        //如果传进来的不是数组，则对标签分割，清理
        $names = $this->tagsStringToArray($names);
        //对标签名唯一化
        $names = array_unique($names);

        //遍历标签名，依次获取标签ID，由于此次标签名不可能太多，故采用遍历方式
        //通过标签名获取标签ID，注意，标签名必须唯一化
        $map = array('name' => array('IN',$names));
        $info = M('tags')->field('id,name')->where($map)->group('name')->select();
        $miss = [];
        $return = [];
        foreach ($info as $key => $value) {
            if(!in_array($value['name'], $names)){
                $miss[] = $value['name'];
            }else{
                $return[$value['id']] = $value['name'];
            }
        }

        //对于没有获取到标签ID的标签进行遍历添加处理，
        //这里需要注意，可能会有两个进程同时遍历添加一个标签，所以要判断是否成功，失败的话需要再查询一次该标签名ID
        foreach ($miss as $key => $value) {
            $id = M('tags')->add(array('name' => $value));
            //返回值不为false，说明增加成功
            if($id != false){
                $return[$id] = $value;
            }else{
                //如果返回值为false，说明添加失败，可能的情况是其他进程已经添加过了,故需要再次查询一下
                $info = M('tags')->field('id,name')->where(array('name'=>$name))->find();
                if(is_array($info)){
                    $return[$info['id']] = $info['name'];
                }
            }
        }

        $result = [];
        //判断需要输出的情况
        switch ($extra) {
            case '1':
                foreach ($return as $key => $value) {
                    $result[] = $key;
                }
                $result = implode(',', $result);
                break;
            case '2':
                $result = $return;
                break;
            case '3':
                foreach ($return as $key => $value) {
                    $result[$value] = $key;
                }
                break;
            default:
                # code...
                break;
        }
        return $result;
    }

     private function tagsStringToArray($tags){
        //如果传进来的不是数组，则对标签分割，清理
        if(!is_array($tags)){
            $tags = str_replace(array('，',',','?','？','!','！','~','-','@'),',',$tags);
            $tags = array_filter(explode(",",$tags)); //数组形式
        }
        return $tags;
    }
    /**
     * [editTagCountByTagIds 编辑标签匹配到的文章数量]
     * @param  [type] $oldids [旧的ID]
     * @param  [type] $newids [新的ID]
     * @param  [type] $type   [需要更改标签数量的类型，比如美图等]
     * @return [type]         [description]
     */
    public function editTagCountByTagIds($oldids, $newids, $type = '')
    {
        //如果需要更改标签统计数量的类型不再下面的数组内，或为空，则返回false
        $allow = array('article_count','meitu_count','diary_count','ask_count','subarticle_count','baike_count');
        if(!in_array($type,$allow)){
            return false;
        }
        $oldids = $this->tagsStringToArray($oldids);
        $newids = $this->tagsStringToArray($newids);
        //去掉同时更新的(既更新又减少就是不更新)
        $comids = array_intersect($oldids, $newids);
        $oldids = array_diff($oldids, $comids);
        $newids = array_diff($newids, $comids);
        //旧标签统计数量减少1
        if(!empty($oldids)){
            //判断标签统计数量是否为0，为0则去除
            $param = array(
                        'id' => array('IN', $oldids),
                        "$type" => 0
                    );
            $result = M('tags')->field('id')->where($param)->select();
            if (!empty($result)) {
                $zero = [];
                foreach ($result as $key => $value) {
                    $zero[] = $value['id'];
                }
                $oldids = array_diff($oldids, $zero);
            }
            //旧标签统计数量减少1
            if (!empty($oldids)) {
                $map = array('id' => array('IN', $oldids));
                M('tags')->where($map)->setDec($type);
            }
        }
        //新标签统计数量增加1
        if(!empty($newids)){
            $where = array('id' => array('IN', $newids));
            M('tags')->where($where)->setInc($type);
        }
        return true;
    }

    /**
     * [getTagsByName 根据标签名获取标签]
     * @param  [type]  $name  [标签名]
     * @param  integer $limit [获取条数]
     * @param  string  $istop [是否推荐]
     * @param  string  $order [排序]
     * @return [type]         [标签数组]
     */
    public function getTagsByName($name,$limit = 15,$istop = '',$order = 'istop DESC'){
        $map['name'] =  array('EQ',$name);
        if(!empty($istop) && ($istop == 1 || $istop == 0)){
            $map['istop'] = intval($istop);
        }
        //有限查看是否有完全匹配的数据，如果有完全匹配的数组，模糊匹配查询数量减少一个
        $complete = M('tags')->where($map)->find();
        if (!empty($complete)) {
            $limit = $limit - 1;
        }

        //重新定义名字查询条件
        $map['name'] =  array('like','%'.$name.'%');
        $result = M('tags')->where($map)->limit($limit)->order($order)->select();
        if (!empty($complete)) {
            array_unshift($result, $complete);
        }
        return $result;
    }

    /**
     * 添加标签
     */
    public function addTags($data){
        return M("tags")->add($data);
    }


    /**
     * 根据type获取所有标签
     * @return [type] [description]
     */
    public function getAllTagsbyTape($type = 1)
    {
        $map = array(
            "type" => array("EQ",$type)
        );
        return M("tags")->where($map)->field("id,name")->select();
    }

    /**
     * 根据type获取所有标签
     * @return [type] [description]
     */
    public function getAllTagsbySubarticle_count($count)
    {
        $map = array(
            "subarticle_count" => array("egt",$count)
        );
        return M("tags")->where($map)->field("id,name")->select();
    }
}