<?php

namespace Home\Model;
use Think\Model;

class AdminaskModel extends Model{

    protected $autoCheckFields = false;

    //取问答列表
    public function getQuestionList($condition,$pagesize= 1,$pageRow = 10){

        if(!empty($condition['cateId'])){
            //Note: 为了减少逻辑，对于一级分类ID直接判断
            $categoryColumn = $condition['cateId'] <= 6 ? 'a.cid' : 'a.sub_category';
            $map[$categoryColumn]  = array("EQ",$condition['cateId']);
        }
        if(isset($condition['status'])){
            $map['a.status']  = array("EQ",$condition['status']);
        }
        if(isset($condition['anwsers'])){
            $map['a.anwsers']  = array("EQ",$condition['anwsers']);
        }
        if(isset($condition['uid'])){
            $map['a.uid']  = array("EQ",$condition['uid']);
        }
        if(isset($condition['keyword'])){
            $map['a.title']  = array('like','%'.$condition['keyword'].'%');
        }
        if(isset($condition['dist'])){
            $map['a.is_distillate']  = array("EQ",$condition['dist']);
        }
        if(isset($condition['remove'])){
            $map['a.visible']  = array("EQ",$condition['remove']);
        }
        if(!empty($condition['start_time'])){
            $map['a.post_time'][] = array('EGT',$condition['start_time']);
        }
        if(!empty($condition['end_time'])){
            $map['a.post_time'][] = array('ELT',$condition['end_time']);
        }
        if(isset($condition['visible'])){
            $map['a.visible']  = array("EQ",$condition['visible']);
        }

        //dump($map);
        $Ask = M('ask');
        $count  = $Ask->alias("a")->where($map)->count();
        $result = $Ask->alias("a")
                      ->field('a.id,a.visible,a.sub_category,a.title,a.content,a.create_time,a.post_time,a.anwsers,a.views,a.status,a.is_distillate,a.reason,a.review,c.cid,c.name category,u.uuid as userid,u.nickname as name')
                      ->join("left join qz_ask_category as c on a.sub_category = c.cid")
                      ->join("left join qz_ucenter_profile as u on a.uid = u.uuid")
                      ->order($condition['orderBy'])
                      ->limit($pagesize.",".$pageRow)
                      ->where($map)
                      ->select();

        //dump($result);

        return array("result"=>$result,"count"=>$count);
    }

    //取答案列表
    public function getAnwserList($condition,$pagesize= 1,$pageRow = 10){
        if(isset($condition['uid'])){
            $map['a.uid']  = array("EQ",$condition['uid']);
        }
        if(isset($condition['keyword'])){
            $map['a.content']  = array('like','%'.$condition['keyword'].'%');
        }
        if(isset($condition['qid'])){
            $map['a.qid']  = array('EQ',$condition['qid']);
        }
        if(isset($condition['remove'])){
            $map['a.visible']  = array("EQ",$condition['remove']);
        }

        $Ask = M('ask_anwser');
        $count  = $Ask->alias("a")->where($map)->count();

        $subSql = $Ask->alias("a")->where($map)
            ->field('a.id,a.uid,a.qid,a.visible,a.post_time,a.content,a.comments,a.agree')
            ->order($condition['orderBy'])
            ->limit($pagesize.",".$pageRow)
            ->buildSql();

        $result = M()->table($subSql)->alias("t")
            ->join("left join qz_ask as c on t.qid = c.id")
            ->join("left join qz_ucenter_profile as u on t.uid = u.uuid")
            ->field("t.*,c.`title`,c.`status`,c.visible qvisible,u.nickname as `name`")
            ->select();

        return array("result" => $result, "count" => $count);
    }

    //取评论列表
    public function getCommentList($condition,$pagesize= 1,$pageRow = 10){
        if(isset($condition['uid'])){
            $map['a.uid']  = array("EQ",$condition['uid']);
        }
        if(isset($condition['keyword'])){
            $map['a.content']  = array('like','%'.$condition['keyword'].'%');
        }
        if(isset($condition['remove'])){
            $map['a.visible']  = array("EQ",$condition['remove']);
        }
        //dump($condition);
        $Ask = M('ask_comment');
        $count  = $Ask->alias("a")
                    ->join("inner join qz_ucenter_profile as u on a.uid = u.uuid")  //用户一致
                    ->where($map)
                    ->count();
        $result = $Ask->alias("a")
                      ->field('a.id,a.qid,a.aid,a.uid,a.content,a.post_time,a.visible,u.nickname as name')
                      ->join("inner join qz_ucenter_profile as u on a.uid = u.uuid")
                      ->order($condition['orderBy'])
                      ->limit($pagesize.",".$pageRow)
                      ->where($map)
                      ->select();
        return array("result"=>$result,"count"=>$count);
    }

    //查询单个问题
    public function getAskByid($id){
        return M('ask')->field('*')->where(array('id'=>$id))->find();
    }

    //查询单个答案
    public function getAnwserByid($id){
        return M('ask_anwser')->field('*')->where(array('id'=>$id))->find();
    }

    //根据UID查询单个答案
    public function getAnwserByuid($qid,$uid){
        $map['uid'] = $uid;
        $map['qid'] = $qid;
        return M('ask_anwser')->field('id')->where($map)->find();
    }
    //查询单个评论
    public function getCommentByid($id){
        return M('ask_comment')->field('*')->where(array('id'=>$id))->find();
    }

    //增加问题
    public function addQuestion($datas){
        return M("ask")->add($datas);
    }

    //编辑问题
    public function editQuestion($id,$data){
        return M("ask")->where(array('id'=>$id))->save($data);
    }

    //审核问题
    public function visibleQuestion($id,$type,$reason = ''){
        $data['visible'] = $type;
        //更新审核时间
        $data['review'] = '1';
        if($reason != ''){
            $data['reason'] = $reason;
        }
        return M("ask")->where(array('id'=>$id))->save($data);
    }

    //删除问题
    public function removeQuestion($id){
        return M("ask")->where(array('id'=>$id))->delete();
    }

    /**
     * [addLog description] 增加日志
     * @param [type] $info [array] array(
     *                                      'action_id' => '操作对象ID',
     *                                      'remark' => '备注',
     *                                      'logtype' => '日志类型，记录炒作的数据',
     *                                      'info' => '操作的数据的数组'
     *                                  )
     */
    public function addLog($info){
        $info['info'] = json_encode($info['info']);
        Load('extend');

        $extra = array(
            'time' => date("Y-m-d H:i:s"),
            'username' => session("uc_userinfo.name"),
            'userid' => session("uc_userinfo.id"),
            'action' => __ACTION__,
            'ip' => get_client_ip(),
            'user_agent' => $_SERVER["HTTP_USER_AGENT"],
        );
        $data = array_merge($info,$extra);
        return M('log_admin')->add($data);
    }

    //设精问题
    public function distillate($id,$type='1'){
        $data['is_distillate'] = $type;
        return M("ask")->where(array('id'=>$id))->save($data);
    }

    //采纳答案
    public function adopt($id,$type=''){
        $data['best_aid'] = $type;

        if($type == 'null'){
            $data['status'] = '0';
        }else{
            $data['status'] = '1';
        }

        $data['adopt_time'] = time();
        return M("ask")->where(array('id'=>$id))->save($data);
    }

    //增加答案
    public function addAnwser($data){
        return M("ask_anwser")->add($data);
    }

    //编辑答案
    public function editAnwser($id,$data){
        return M("ask_anwser")->where(array('id'=>$id))->save($data);
    }

    //删除答案
    public function removeAnwser($id,$type){
        $data['visible'] = $type;
        return M("ask_anwser")->where(array('id'=>$id))->save($data);
    }

    //增加评论
    public function addComment($data){
        return M("ask_comment")->add($data);
    }

    //编辑评论
    public function editComment($id,$data){
        return M("ask_comment")->where(array('id'=>$id))->save($data);
    }

    //删除评论
    public function removeComment($id,$type){
        $data['visible'] = $type;
        return M("ask_comment")->where(array('id'=>$id))->save($data);
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
        $map = array(
            'fid' => $id,
            'type' => $type,
        );
        return M('ask_file')->where($map)->field('path')->order('time')->select();
    }

    //增加上传的图片

    public function addUploadImage($qid,$fid,$type,$file){
        $data['type'] = $type;
        $data['qid'] = $qid;//问题id
        $data['fid'] = $fid;//回答id
        $data['path'] = $file;
        $data['time'] = time();
        return M("ask_file")->add($data);
    }

    //根据用户ID取用户信息
    public function getUserById($id){
        $map['u.id'] = $id;
        return M('user')->alias('u')
            ->field('u.*,c.fake')
            ->join('qz_user_company c on c.userid = u.id')
            ->where($map)->find();
    }

    //取系统帐号
    public function getUserList(){
        $map = array(
            "id"         =>  array("IN",'73479,11301,61779,15157')
        );
        return M('user')->field('*')->where($map)->select();
    }

    //查看用户是否是后台注册的SEO用户
    public function isSEOUser($uid){
        return M('ask_seouser')->field('uid,username')->where(array('uid'=>$uid))->find();
    }

    //获取分类
    public function getCategory($update='0'){
        $category = M('ask_category')->field('cid,pid,name,order_id,count')->order('order_id')->select();
        return $category;
    }

    //根据Cid 获取一条分类
    public function getCategoryById($id){
        return M('ask_category')
                ->field('*')
                ->where(array('cid'=>$id))
                ->order('order_id DESC')
                ->find();
    }

    //编辑分类
    public function editCategory($id,$data){
        return M("ask_category")->where(array('cid'=>$id))->save($data);
    }

    //增加分类
    public function addCategory($data){
        return M("ask_category")->add($data);
    }

    //取配置数据
    public function getOption($name){
        return M('options')->field('*')->where(array('option_name'=>$name))->find();
    }

    //加配置数据
    public function addOption($name,$value){
        $data['option_name'] = $name;
        $data['option_value'] = $value;
        $data['option_group'] = 'ask';
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

    //获取所有城市 有缓存  * $update 为 true 则更新缓存
    public function getCityList($update = false){
        if($update){
            F('allCityList',NULL);
        }else{
            $allCityList = F('allCityList');
        }
        //如果数据为空
        if(empty($allCityList)){
            $result = M('city')->order('id ASC')->select();
            foreach ($result as $k => $v ){
                $citys[$v['fatherid']][] = array(
                    'city' => $v['qz_city'],
                    'cityid' => $v['qz_cityid']
                );
            }
            F('allCityList',$citys);
            return $citys;
        }else{
            return $allCityList;
        }
    }

    //获取所有省份 有缓存
    public function getAreaList($update = false){
        if($update){
            F('provinceList',NULL);
        }else{
            $provinceList = F('provinceList');
        }
        //如果数据为空
        if(empty($provinceList)){
            $provinceList = M('province')->field('id,qz_provinceid,qz_province')->order('id ASC')->select();
            F('provinceList',$provinceList);
        }
        return $provinceList;
    }

    //根据城市ID取省份ID
    public function getProvinceIdByCityId($cid = ''){
        $myProvince = M('city')->where(array('qz_cityid'=>$cid))->field('fatherid')->find();
        return $myProvince['fatherid'];
    }

    //禁封用户
    public function blockedUser($uid,$time){
        $map['id'] = $uid;
        $data['blocked'] = $time;
        M("user")->where($map)->save($data);
    }

    /**
     * 根据问答标题获取列表
     * @param  [type] $title [description]
     * @return [type]        [description]
     */
    public function getAskByTitle($title,$limit = 10)
    {
        $map = array(
            "title" => array("LIKE","%$title%")
        );
        return M("ask")->where($map)->field("id,title")->limit($limit)->select();
    }

    public function getAskListCount($begin,$end,$title,$type,$sub_type)
    {
        $map = array(
            "post_time" => array(
                array("EGT",$begin),
                array("LT",$end),
            )
        );

        if (!empty($type)) {
            $map["cid"] = array("EQ",$type);
        }

        if (!empty($sub_type)) {
            $map["sub_category"] = array("EQ",$sub_type);
        }

        if (!empty($title)) {
            $map["_complex"] = array(
                "title" => array("LIKE","%$title%"),
                "id" => array("LIKE","%$title%"),
                "_logic" => "OR"
            );
        }

        return M("ask")->where($map)->count();
    }

    /**
     * 获取问答时间段内的列表
     * @param  [type] $begin [description]
     * @param  [type] $end   [description]
     * @return [type]        [description]
     */
    public function getAskList($begin,$end,$title,$type,$sub_type,$pageIndex,$pageCount)
    {
        $map = array(
            "post_time" => array(
                array("EGT",$begin),
                array("LT",$end),
            )
        );

        if (!empty($type)) {
            $map["cid"] = array("EQ",$type);
        }

        if (!empty($sub_type)) {
            $map["sub_category"] = array("EQ",$sub_type);
        }

        if (!empty($title)) {
            $map["_complex"] = array(
                "title" => array("LIKE","%$title%"),
                "id" => array("LIKE","%$title%"),
                "_logic" => "OR"
            );
        }

        $buildSql = M("ask")->where($map)->order("id")->field("id,title,post_time,username,cid,FROM_UNIXTIME(post_time,'%Y-%m-%d') as date,sub_category")->buildSql();

        return M("ask")->table($buildSql)->alias("t")
                                         ->join("join qz_ask_category a on a.cid = t.cid")
                                         ->join("join qz_ask_category b on b.cid = t.sub_category")
                                         ->field("t.*,a.name as category_name,b.name as sub_category_name")
                                         ->limit($pageIndex,$pageCount)
                                         ->select();
    }

    public function getListByTime($start,$end)
    {
        $map = array(
            "create_time" => array(
                array("EGT",$start),
                array("ELT",$end),
            )
        );
        return M("ask")->where($map)->field("id,title")->select();

    }
}