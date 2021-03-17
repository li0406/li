<?php

namespace Home\Model;
Use Think\Model;

/**
*
*/
class ArticleVedioModel extends Model
{
    protected $autoCheckFields = false;

    protected $_validate = array(
        array('title','require','请填写标题',1,"",1),
        array('type','require','请选择类型',1,"",1),
        array('cover_img','require','请上传封面图片',1,"",1),
        array('description','require','请填写描述',1,"",1),
        array('teacher','require','请选择讲师',1,"",1),
        array('url','require','请填写视频链接',1,"",1)
    );

    /**
     * 添加分类
     * @param [type] $data [description]
     */
    public function addCategory($data)
    {
        return M("article_video_category")->add($data);
    }

    /**
     * 删除分类关联
     * @param [type] $data [description]
     */
    public function removeCategoryItem($id)
    {
        return M("article_video_category")->where(array('vid'=>$id))->delete();
    }

    /**
     * 获取分类关联
     * @param [type] $data [description]
     */
    public function getCategoryByItem($id)
    {
        return M("article_video_category")->field('pid,cid')->where(array('vid'=>$id))->select();
    }
    

     /**
     * 添加视频
     * @param [type] $data [description]
     */
    public function addVedio($data)
    {
        return M("article_vedio")->add($data);
    }

    /**
     * 编辑视频
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function editVedio($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("article_vedio")->where($map)->save($data);
    }

    /**
     * [deltags description]
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function deltags($id)
    {
        $map = array(
            "vedio_id" => array("EQ",$id)
        );
        return M("article_vedio_tag")->where($map)->delete();
    }

    /**
     * 添加标签
     * @param [type] $data [description]
     */
    public function addAllTag($data)
    {
        return M("article_vedio_tag")->addAll($data);
    }

    /**
     * 获取视频列表数量
     * @param  [type] $title [description]
     * @return [type]        [description]
     */
    public function getVedioListCount($title, $id)
    {
        if (!empty($title)) {
            $map["title"] = array("LIKE","%$title%");
        }

        if (!empty($id)) {
            $map["id"] = array("EQ", $id);
        }
        return M("article_vedio")->where($map)->count();
    }

    /**
     * 获取视频列表
     * @param  [type] $title [description]
     * @return [type]        [description]
     */
    public function getVedioList($pageIndex,$pageCount,$title, $id)
    {
        if (!empty($title)) {
            $map["title"] = array("LIKE","%$title%");
        }

        if (!empty($id)) {
            $map["id"] = array("EQ", $id);
        }

        return  M("article_vedio")->where($map)->limit($pageIndex.",".$pageCount)->order("isdelete,id desc")->select();
    }

    /**
     * 获取视频信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getVedioInfo($id)
    {
        $map = array(
            "a.id" => array("EQ",$id)
        );
        return M("article_vedio")->where($map)->alias("a")
                                 ->join("left join qz_article_vedio_tag b on a.id = b.vedio_id")
                                 ->join("left join qz_tags as t on t.id = b.tag_id")
                                 ->field("a.*,t.name,b.tag_id")
                                 ->select();
    }

    /**
     * 根据视频标题获取列表
     * @param  [type]  $title [description]
     * @param  integer $limit [description]
     * @return [type]         [description]
     */
    public function getVideoByTitle($title,$limit = 10)
    {
        $map = array(
            "title" => array("LIKE","%$title%")
        );
        return M("article_vedio")->where($map)
                                 ->field("id,title")
                                 ->limit($limit)
                                 ->select();
    }

    /**
     * 获取讲师列表
     * @param  string           $keyword         搜索关键词 
     * @return [array|void]     $result          搜索结果
     */
    public function getUsers($keyword)
    {
        if(!empty($keyword)){
            $map = array(
                array(
                    'author' => array('eq',$keyword),
                    'status' => array('eq',1)//status = 1 可用 （0是已经删除）
                ),
                array(
                    'id' => array('eq',$keyword),
                    'status' => array('eq',1)//status = 1 可用 （0是已经删除）
                ),
                '_logic' => 'OR'
            );
        }else{
            $map['status'] = 1;
        }
        $result = M('article_users')->where($map)->select();
        return $result;
    }

    /**
     * 编辑讲师
     * @param  string           $id              修改讲师的ID 
     * @param  array            $data            修改的系数
     * @return array            $result          修改结果
     */
    public function editUser($id,$data)
    {
        if($id == 0){
            //添加
            $result = M('article_users')->add($data);
        }else{
            //修改
            $map['id'] = $id;
            $result = M('article_users')->where($map)->save($data);
        }
        return $result;
    }

     /**
     * 添加微信视频
     * @param [type] $data [description]
     */
    public function addWxVedio($data)
    {
        return M("weixin_vedio")->add($data);
    }

    /**
     * 获取微信小程视频列表数量
     * @param  array    $map            查询条件数组
     * @return string   $result         返回总条数
     */
    public function getWXVedioCount($map)
    {
        if (!empty($map['title'])) {
            $map["title"] = array("LIKE","%".$map['title']."%");
        }
        if (!empty($map['theme'])) {
            $map["theme"] = array("EQ", $map['theme']);
        }
        if (!empty($map['istop'])) {
            $map["istop"] = array("EQ", $map['istop']);
        }
        if (!empty($map['isdelete'])) {
            $map["isdelete"] = array("EQ", $map['isdelete']);
        }
        unset($map['order']);
        $result =  M("weixin_vedio")->where($map)->count();
        return $result;
    }

    /**
     * 获取微信小程序视频列表
     * @param  array    $map            查询条件数组
     * @return array    $result         返回结果数组
     */
    public function getWXVedioList($pageIndex,$pageCount,$map)
    {
        if (!empty($map['title'])) {
            $map["title"] = array("LIKE","%".$map['title']."%");
        }
        if (!empty($map['theme'])) {
            $map["theme"] = array("EQ", $map['theme']);
        }
        if (!empty($map['istop'])) {
            $map["istop"] = array("EQ", $map['istop']);
        }
        if (!empty($map['isdelete'])) {
            $map["isdelete"] = array("EQ", $map['isdelete']);
        }
        if($map['order'] !== ''){
            $order = substr($map['order'], 0,-1);
        }else{
            $order = 'time desc';
        }
        unset($map['order']);

        $result = M("weixin_vedio")->where($map)->limit($pageIndex.",".$pageCount)->order($order)->select();
        //var_dump(M()->getLastSql());
        return $result;
    }

    /**
     * 获取微信视频信息
     * @param  [string] $id [视频ID]
     * @return [array]     [微信视频数据]
     */
    public function getWXVedioInfo($id)
    {
        $map = array(
            "a.id" => array("EQ",$id)
        );
        return M("weixin_vedio")->where($map)->alias("a")->field("a.*")->select();
    }

    /**
     * 编辑微信视频
     * @param  string $id       要编辑的视频ID
     * @param  array  $id       状态数组
     * @return string $result   修改结果
     */
    public function editWXVedio($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("weixin_vedio")->where($map)->save($data);
    }

    /**
     * 删除微信视频
     * @param  string $id       要删除的视频ID
     * @return string $result   修改结果
     */
    public function delWXVedio($id)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("weixin_vedio")->where($map)->limit('1')->delete();
    }

    /**
     * 获取微信小程视频评论列表数量
     * @param  array    $map            查询条件数组
     * @return string   $result         返回总条数
     */
    public function getWXCommentCount($map)
    {
        if (!empty($map['title'])) {
            $keyword = $map['title'];
            unset($map['title']);
        }
        if (!empty($map['recommend']) || $map['recommend'] === '0') {
            $map["f.recommend"] = array("EQ", $map['recommend']);
        }

        $map["f.module"] = array("EQ", 'wxvideo');
        if(!empty($keyword)){
            $result =  M("comment_full")->alias("f")
                                    ->join("join qz_weixin_vedio as v on f.ref_id = v.id and (f.username = '".$keyword."' or v.title like '%".$keyword."%')")
                                    ->where($map)
                                    ->count();
        }else{
            $result =  M("comment_full")->alias("f")
                                    ->join("join qz_weixin_vedio as v on f.ref_id = v.id")
                                    ->where($map)
                                    ->count();
        }
        
        return $result;
    }

    /**
     * 获取微信小程序视频评论列表
     * @param  string   $pageIndex              分页开始
     * @param  string   $pageCount              分页结束
     * @param  array    $map                    查询条件数组
     * @return array    $result                 返回结果数组
     */
    public function getWXCommentList($pageIndex,$pageCount,$map)
    {
        if (!empty($map['title'])) {
            $keyword = $map['title'];
            unset($map['title']);
        }
        if (!empty($map['recommend']) || $map['recommend'] === '0') {
            $map["recommend"] = array("EQ", $map['recommend']);
        }
        
        if(!empty($keyword)){
            $result =  M("comment_full")->alias("f")
                                    ->join("join qz_weixin_vedio as v on f.ref_id = v.id and (f.username = '".$keyword."' or v.title like '%".$keyword."%')")
                                    ->where($map)
                                    ->field("f.*,v.title")
                                    ->limit($pageIndex.",".$pageCount)
                                    ->order("time desc,likes desc")
                                    ->select();
        }else{
            $result =  M("comment_full")->alias("f")
                                    ->join("join qz_weixin_vedio as v on f.ref_id = v.id")
                                    ->where($map)
                                    ->field("f.*,v.title")
                                    ->limit($pageIndex.",".$pageCount)
                                    ->order("time desc,likes desc")
                                    ->select();
        }

        //$result = M("weixin_vedio")->where($map)->limit($pageIndex.",".$pageCount)->order("time desc,pv desc,pnum desc")->select();
        //var_dump(M()->getLastSql());
        return $result;
    }

    /**
     * 编辑微信小程序视频评论
     * @param  string   $id                     评论ID
     * @param  array    $data                   写入的值
     * @return array    $result                 返回结果数组
     */
    public function editWXComment($id,$data){
        return M("comment_full")->where(array('id'=>$id))->save($data);
    }

    /**
     * 获取视频评论列表数量
     * @param  array    $map            查询条件数组
     * @return string   $result         返回总条数
     */
    public function getWXVideoCommentCount($map)
    {
        if (!empty($map['title'])) {
            $where["f.username"] = array("EQ", $map['title']);
        }
        if (!empty($map['recommend']) || $map['recommend'] === '0') {
            $where["f.recommend"] = array("EQ", $map['recommend']);
        }

        $where["f.ref_id"] = array("EQ", $map["ref_id"]);
        $where["f.module"] = array("EQ", 'wxvideo');
        if(!empty($keyword)){
            $result =  M("comment_full")->alias("f")
                                    ->join("join qz_weixin_vedio as v on f.ref_id = v.id ")
                                    ->where($where)
                                    ->count();
        }else{
            $result =  M("comment_full")->alias("f")
                                    ->join("join qz_weixin_vedio as v on f.ref_id = v.id")
                                    ->where($where)
                                    ->count();
        }
        
        return $result;
    }

    /**
     * 获取微信小程序视频评论列表
     * @param  string   $pageIndex              分页开始
     * @param  string   $pageCount              分页结束
     * @param  array    $map                    查询条件数组
     * @return array    $result                 返回结果数组
     */
    public function getWXVideoCommentList($pageIndex,$pageCount,$map)
    {
        if (!empty($map['title'])) {
            $where["f.username"] = array("EQ", $map['title']);
        }
        if (!empty($map['recommend']) || $map['recommend'] === '0') {
            $where["f.recommend"] = array("EQ", $map['recommend']);
        }
        $where["f.ref_id"] = array("EQ", $map["ref_id"]);
        $where["f.module"] = array("EQ", 'wxvideo');
        if(!empty($keyword)){
            $result =  M("comment_full")->alias("f")
                                    ->join("join qz_weixin_vedio as v on f.ref_id = v.id")
                                    ->where($where)
                                    ->field("f.*,v.title")
                                    ->limit($pageIndex.",".$pageCount)
                                    ->order("time desc,likes desc")
                                    ->select();
        }else{
            $result =  M("comment_full")->alias("f")
                                    ->join("join qz_weixin_vedio as v on f.ref_id = v.id")
                                    ->where($where)
                                    ->field("f.*,v.title")
                                    ->limit($pageIndex.",".$pageCount)
                                    ->order("time desc,likes desc")
                                    ->select();
        }

        //$result = M("weixin_vedio")->where($map)->limit($pageIndex.",".$pageCount)->order("time desc,pv desc,pnum desc")->select();
        //var_dump(M()->getLastSql());
        return $result;
    }
}