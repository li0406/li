<?php
namespace Common\Model;
use Think\Model;

class AdminbaikeModel extends Model{

    protected $autoCheckFields = false;

    //取列表
    public function getList($map,$pagesize= 1,$pageRow = 10){
        $count  = M('baike')->alias("a")->where($map)->count();
        $result = M('baike')->alias("a")
                            ->field('a.*,u.name AS register_uname,x.name AS system_uname,u.blocked, z.name AS category,z.url AS category_url,c.name sub_category,c.url sub_category_url')
                            ->join("left join qz_baike_category as z on z.cid = a.cid")
                            ->join("left join qz_baike_category as c on c.cid = a.sub_category")
                            ->join("left join qz_user as u on u.id = a.uid")
                            ->join("left join qz_adminuser as x on x.id = a.uid")
                            ->order("a.post_time DESC")
                            ->limit($pagesize.",".$pageRow)
                            ->where($map)
                            ->select();
        return array("result"=>$result,"count"=>$count);
    }

    //取需要的列表
    public function getDetailList($map,$pagesize= 1,$pageRow = 10,$field = 'a.*'){
        return M('baike')->alias("a")
            ->field($field)
            ->join("left join qz_baike_category as z on z.cid = a.cid")
            ->join("left join qz_baike_category as c on c.cid = a.sub_category")
            ->join("left join qz_user as u on u.id = a.uid")
            ->join("left join qz_adminuser as x on x.id = a.uid")
            ->order("a.post_time DESC")
            ->limit($pagesize.",".$pageRow)
            ->where($map)
            ->select();
    }

    //查询单个
    public function getBaikeByid($id){
        return M('baike')->alias('a')
                        ->join("left join qz_baike_category as z on z.cid = a.cid")
                        ->join("left join qz_baike_category as c on c.cid = a.sub_category")
                        ->join("left join qz_user as u on u.id = a.uid")
                        ->join("left join qz_adminuser as x on x.id = a.uid")
                        ->field('a.*,a.sub_category AS sub_category_id, u.name AS register_uname,x.name AS system_uname,u.blocked, z.name AS category,z.url AS category_url,c.name sub_category,c.url sub_category_url')
                        ->where(array('a.id'=>$id))
                        ->find();
    }

    /**
     * 获取预览百科
     * @param  array  $map   查询条件
     * @param  string $order 排序
     * @return array
     */
    public function getBaikeForPreview($map = array(), $order = 'a.id ASC'){
        return M('baike')->alias('a')
                        ->join("left join qz_baike_category as z on z.cid = a.cid")
                        ->join("left join qz_baike_category as c on c.cid = a.sub_category")
                        ->join("left join qz_user as u on u.id = a.uid")
                        ->join("left join qz_adminuser as x on x.id = a.uid")
                        ->field('a.*,u.name AS register_uname,x.name AS system_uname,u.blocked, z.name AS category,z.url AS category_url,c.name sub_category,c.url sub_category_url')
                        ->where($map)
                        ->order($order)
                        ->find();
    }

    public function getFrequentReview()
    {
        $map['_string'] = "review <> '1' AND review <> '0' AND review <> ''";
        return M('baike')->field('review')
                         ->where($map)
                         ->order('id DESC')
                         ->group('review')
                         ->limit(10)
                         ->select();
    }


    //查询单个
    public function getBaikeByMap($map){
        return M('baike')->field('*')->where($map)->select();
    }

    //增加
    public function addBaike($data){
        return M("baike")->add($data);
    }

    //编辑
    public function editBaike($id,$data){
        return M("baike")->where(array('id'=>$id))->save($data);
    }

    //删除
    public function removeBaike($id,$type){
        $data['remove'] = $type;
        return M("baike")->where(array('id'=>$id))->save($data);
    }

    //审核
    public function visibleBaike($id,$type,$reason = ''){
        $data['visible'] = $type;
        if(!empty($reason)){
            $data['review'] = $reason;
        }else{
            $data['review'] = '1';
        }
        return M("baike")->where(array('id'=>$id))->save($data);
    }

    //推荐
    public function distillate($id,$type='1'){
        $data['is_top'] = $type;
        return M("baike")->where(array('id'=>$id))->save($data);
    }

    //获取分类
    public function getCategorys(){
        $category = M('baike_category')->field('*')->order('order_id')->select();
        return $category;
    }

    //获取分类
    public function getCategoryById($id){
        return M('baike_category')->field('*')->where(array('cid'=>$id))->find();
    }

   //增加分类
    public function addCategory($data){
        return M("baike_category")->add($data);
    }

    //编辑分类
    public function editCategory($id,$data){
        return M("baike_category")->where(array('cid'=>$id))->save($data);
    }

    //推荐分类
    public function setTopCategory($id,$type='1'){
        $data['is_top'] = $type;
        return M("baike_category")->where(array('cid'=>$id))->save($data);
    }

    //删除分类
    public function removeCategory($id){
        return M("baike_category")->where(array('cid'=>$id))->delete();
    }

    //根据Uid和关键词取Tag列表
    public function getTags($uid,$keywords){
        $map['uid'] = $uid;
        $map['type'] = '4';
        if(stristr($keywords,',')){
            $map['name'] =  array("IN",$keywords);
        }else{
            $map['name'] =  $keywords;
        }
        return M('tags')->where($map)->field('*')->order('time')->select();
    }

    //增加Tags
    public function addTags($data){
        return M("tags")->add($data);
    }

    //取图片列表
    public function getImages($id,$type){
        //        $map = [
        //                'fid'=>['EQ',$id],
        //                'type'=>['EQ',$type],
        //                ];
        //return M('baike_file')->where($map)->field('path')->order('time')->select();

    }

    //增加上传的图片
    public function addUploadImage($id,$qid,$type,$file){
        $data['type'] = $type;
        $data['qid'] = $qid;
        $data['fid'] = $id;
        $data['path'] = $file;
        $data['time'] = time();
        return M("baike_file")->add($data);
    }

    //取用户信息
    public function getUserById($id){
        $map['id'] = $id;
        return M('user')->field('*')->where($map)->find();
    }


    //取配置数据
    public function getOption($name){
        return M('options')->field('*')->where(array('option_name'=>$name))->find();
    }

    //增加配置数据
    public function addOption($name,$value){
        $data['option_name'] = $name;
        $data['option_value'] = $value;
        $data['option_group'] = 'baike';
        $data['autoload'] = 'yes';
        $data['option_remark'] = 'Ask Module';
        return M("options")->add($data);
    }

    //改配置数据
    public function editOption($name,$value){
        $data['option_value'] = $value;
        return M("options")->where(array('option_name'=>$name))->save($data);
    }

    //删配置数据
    public function delOption($name){
        $data['option_name'] = $name;
        return M("options")->where(array('option_name'=>$name))->del();
    }



    //查询所有省份 有缓存 $update 为 true 则更新缓存
    public function getAreaList($update = false){
        return M('province')->field('id,qz_provinceid,qz_province')->order('id ASC')->select();
    }

    //查询所有城市 有缓存  * $update 为 true 则更新缓存
    public function getCityList($update = false){
        $result = M('city')->order('id ASC')->select();
        foreach ($result as $k => $v ){
            $citys[$v['fatherid']][] = array(
                'city' => $v['qz_city'],
                'cityid' => $v['qz_cityid']
            );
        }
        return $citys;
    }

    //根据城市ID取省份ID
    public function getProvinceIdByCityId($cid = ''){
        $myProvince = M('city')->where(array('qz_cityid'=>$cid))->field('fatherid')->find();
        return $myProvince['fatherid'];
    }

}