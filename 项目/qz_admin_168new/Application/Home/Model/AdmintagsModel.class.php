<?php
/**
 * 标签model，从老板copy过来的
 */
namespace Home\Model;
use Think\Model;
class AdmintagsModel extends Model{

    protected $autoCheckFields = false;

    /**
     * 添加标签
     */
    public function addTags($data){
        return M("tags")->add($data);
    }

    public function getTagsByName($name,$limit = 15,$istop = ''){
        $map['name'] =  array('like','%'.$name.'%');
        if(!empty($istop) && ($istop == 1 || $istop == 0)){
            $map['istop'] = intval($istop);
        }
        $result = M('tags')->where($map)->limit($limit)->select();
        return $result;
    }

    //获取标签数量
    public function getTagsCount($keyword = "",$condition=''){
        if(!empty($keyword)){
            $map["name"] = array("LIKE","%$keyword%");
        }
        if(!empty($condition['istop'])){
            $map["istop"] = $condition['istop'];
        }
        if(!empty($condition['type'])){
            $map["type"] = $condition['type'];
        }
        return M("tags")->where($map)->count();
    }

    //获取标签列表
    public function getTags($pageIndex,$pageCount,$keyword,$condition=''){
        if(!empty($keyword)){
            $map["name"] = array("LIKE","%$keyword%");
        }
        if(!empty($condition['istop'])){
            $map["istop"] = $condition['istop'];
        }
        if(!empty($condition['type'])){
            $map["type"] = $condition['type'];
        }
        return M("tags")->where($map)->limit($pageIndex.",".$pageCount)
                        ->order("istop desc,id desc")
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

        if(!empty($newids)){
            //新标签统计数量增加1
            $where = array('id' => array('IN', $newids));
            M('tags')->where($where)->setInc($type);
        }
        return true;
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
            $tags = str_replace(array(' ','，',',','?','？','!','！','~','-','@'),',',$tags);
            $tags = array_filter(explode(",",$tags)); //数组形式
        }
        return $tags;
    }


}