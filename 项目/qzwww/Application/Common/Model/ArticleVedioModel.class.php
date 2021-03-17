<?php

namespace Common\Model;
use Think\Model;

/**
* 攻略视频表
*/

class ArticleVedioModel extends Model
{
    protected $autoCheckFields = false;
    /**
     * 获取视频列表
     * @param  [type] $type  [视频类型]
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    public function getVedioList($type,$limit)
    {
        $map = array(
            "type" => array("EQ",$type),
            "isdelete" => array("EQ",0),
            "istop" => array("EQ",1)
        );
        return M("article_vedio")->where($map)->field("id,title,cover_img")->order("id desc")->limit($limit)->select();
    }

    /**
     * [getArticleVedioById 通过ID获取视频]
     * @param  [type] $id [ID]
     * @return [type]     [description]
     */
    public function getArticleVedioById($id)
    {
        if(empty($id)){
            return false;
        }
        $result = M('article_vedio')->alias('v')
                                    ->join('left join qz_article_users as u on v.teacher = u.id and u.`status` = 1')
                                    ->field('v.*,u.author as tname,u.logo as tlogo')
                                    ->where(['v.id' => $id])
                                    ->find();

        if(empty($result['author'])){
            $data = $this->getAuthorInfo($result['teacher']);
            $result['author'] = $data['author'];
            $result['logo'] = $data['logo'];
        }

        $result['longdescription'] = $result['description'];

        if(strlen($result['description']) > 270){
            $result['description'] = mb_substr($result['description'], 0, 90, "UTF-8") . "...";
        }

        return $result;
    }

    /**
     * [getNearArticleVedio 获取相邻资源]
     * @param  [type] $type  [类型]
     * @param  [type] $id    [id]
     * @param  [type] $extra [prev:上一个,next:下一个]
     * @return [type]        [description]
     */
    public function getNearArticleVedio($type,$id,$extra)
    {
        if ($extra == 'prev') {
            $result = M('article_vedio')->where(['id' => array('LT',$id),'type'=>$type])->order('id DESC')->find();
        }else{
            $result = M('article_vedio')->where(['id' => array('GT',$id),'type'=>$type])->order('id')->find();
        }
        return $result;
    }

    /**
     * [getRecommendArticleVedio 获取推荐视频]
     * @param  [type]  $type    [类型]
     * @param  integer $start   [开始页]
     * @param  integer $end     [结束页]
     * @param  string  $keyword [关键字]
     * @return [type]           [description]
     */
    public function getRecommendArticleVedio($type,$start = 0,$end = 1, $keyword = '')
    {
        $map['v.istop'] = 1;
        if(!empty($keyword)){
            $map["_complex"] = array(
                                     "v.title"=>array("LIKE","%$keyword%"),
                                     "v.description"=>array("LIKE","%$keyword%"),
                                     "_logic"=>"OR"
                                    );
        }
        if(!empty($type)){
            $map['v.type'] = $type;
        }

        $result = M('article_vedio')->alias('v')
                                    ->join('left join qz_article_users as u on v.teacher = u.id and u.`status` = 1')
                                    ->field('v.*,u.author as tname,u.logo as tlogo')
                                    ->where($map)
                                    ->order('v.id DESC')
                                    ->limit($start, $end)
                                    ->select();
        return $result;
    }

    /**
     * [getArticleVedioList 获取视频列表]
     * @param  [type] $type    [类型]
     * @param  [type] $start   [开始页面]
     * @param  [type] $end     [结束页]
     * @param  [type] $keyword [关键字]
     * @return [type]          [description]
     */
    public function getArticleVedioCount($type,$keyword)
    {
        if(!empty($keyword)){
            $map["_complex"] = array(
                                     "v.title"=>array("LIKE","%$keyword%"),
                                     "v.description"=>array("LIKE","%$keyword%"),
                                     "_logic"=>"OR"
                                    );
        }
        if(!empty($type)){
            $map['v.type'] = $type;
        }

        $map['v.isdelete'] = ['NEQ', 1];

        $result = M('article_vedio')->alias('v')->where($map)->count();
        return $result;
    }

    /**
     * [getArticleVedioList 获取视频列表]
     * @param  [type] $type    [类型]
     * @param  [type] $start   [开始页面]
     * @param  [type] $end     [结束页]
     * @param  [type] $keyword [关键字]
     * @return [type]          [description]
     */
    public function getArticleVedioList($type,$start,$end,$keyword,$order)
    {
        if(!empty($keyword)){
            $map["_complex"] = array(
                                     "v.title"=>array("LIKE","%$keyword%"),
                                     "v.description"=>array("LIKE","%$keyword%"),
                                     "_logic"=>"OR"
                                    );
        }
        if(!empty($type)){
            $map['v.type'] = $type;
        }

        $map['v.isdelete'] = ['NEQ', 1];

        if(empty($order)){
            $order = 'v.id DESC';
        }

        $result = M('article_vedio')->alias('v')
                                    ->join('left join qz_article_users as u on v.teacher = u.id and u.`status` = 1')
                                    ->field('v.*,u.author as tname,u.logo as tlogo')
                                    ->where($map)
                                    ->order($order)
                                    ->limit($start, $end)
                                    ->select();
        return $result;
    }

    /**
     * [addArticleVedioLikesById 通过ID增加喜欢量]
     * @param [type] $id [description]
     */
    public function addArticleVedioLikesById($id)
    {
        if(!empty($id)){
            return M('article_vedio')->where(array('id' => $id))->setInc('likes');
        }
        return false;
    }

    /**
     * [decArticleVedioLikesById 通过ID减少喜欢量]
     * @param [type] $id [description]
     */
    public function decArticleVedioLikesById($id)
    {
        if(!empty($id)){
            return M('article_vedio')->where(array('id' => $id))->setDec('likes');
        }
        return false;
    }

    /**
     * [addArticleVedioPvById 通过ID增加PV]
     * @param [type] $id [description]
     */
    public function addArticleVedioPvById($id)
    {
        if(!empty($id)){
            return M('article_vedio')->where(array('id' => $id))->setInc('pv');
        }
        return false;
    }

    /**
     *  [获取点赞量最多的两条数据]
     *  @return [type] $arr [description]
     *
     */
    public function getMaxTwoLikes(){

        $map["isdelete"] = array("EQ", 0);

        return M("article_vedio")->where($map)->order("likes desc")->limit(2)->select();
    }


    /**
     *  [获取装修扫盲下的视频数据]
     *  @param $pid [type] int [一级分类id]
     *  @param $cid [type] int [二级分类id]
     *  @return [type] array [description]
     */
    public function getVideoList($pid = '', $cid = ''){
        if(!empty($pid)){
            $map['b.pid'] = array("EQ", $pid);
        }

        if(!empty($cid)){
            $map['b.cid'] = array("EQ", $cid);
        }

        //未删除数据
        $map['a.isdelete'] = array("EQ", 0);

        $data = M("article_vedio")->alias("a")
                                  ->join("inner join qz_article_video_category as b on a.id = b.vid")
                                  ->field("a.*")
                                  ->where($map)
                                  ->order("a.time desc")
                                  ->limit(6)
                                  ->select();
        return $data;
    }

    /**
     *  [获取装修视频列表页数据按照时间倒序排列]
     *
     *  @return [type] array [description]
     *
     */
    public function getVideoListDataByTime($pageIndex=1, $pageCount=8, $map='',$field = 'a.*'){

        if(empty($map)){
            $map['isdelete'] = 0;
            $data = M("article_vedio")->where($map)->order("time desc")->limit($pageIndex, $pageCount)->select();
            return $data;
        }

        $map['a.isdelete'] = array("EQ", 0);

        $data = M("article_vedio")->alias("a")
            ->join("inner join qz_article_video_category as b on a.id = b.vid")
            ->field($field)
            ->where($map)
            ->order("a.time desc")
            ->limit($pageIndex, $pageCount)
            ->select();

        return $data;
    }

    /**
     *  [获取装修视频列表页数据条数]
     *
     */
    public function getVideoListDataCount($map=""){

        if(empty($map)){
            $map['isdelete'] = 0;
            $count = M("article_vedio")->where($map)->count();
            return $count;
        }

        $map['isdelete'] = array("EQ", 0);

        $count = M("article_vedio")->alias("a")
            ->join("inner join qz_article_video_category as b on a.id = b.vid")
            ->where($map)
            ->count();

        return $count;
    }

    /**
     *  [获取推荐数据,根据浏览量倒序排列]
     *
     */
    public function getVideoListRecommend(){
        $result = M("article_vedio")->order("pv desc")->limit(4)->select();

        foreach($result as $key => $value){
            if(empty($value['author'])){
                $data = $this->getAuthorInfo($value['teacher']);
                $result[$key]['author'] = $data['author'];
                $result[$key]['logo'] = $data['logo'];
            }

            if(strlen($value['description']) > 420){
                $result[$key]['description'] = mb_substr($value['description'], 0, 140, "UTF-8") . "...";
            }
        }

        return $result;
    }


    /**
     *  [默认获取当前视频的下两个视频]
     *
     */
    public function getNextVideoData($id, $limit='2'){

        if(empty($id)){
            return false;
        }

        $result = M('article_vedio')->where(['id' => array('GT',$id)])->order('id')->limit($limit)->select();

        foreach($result as $key => $value){
            if(empty($value['author'])){
                $data = $this->getAuthorInfo($value['teacher']);
                $result[$key]['author'] = $data['author'];
                $result[$key]['logo'] = $data['logo'];
            }
        }

        return $result;
    }

    /**
     *  [ajax获取当前视频的下一个或者上一个视频数据]
     *
     */
    public function getAjaxData($extra, $id)
    {
        if ($extra == 'prev') {
            $result = M('article_vedio')->where(['id' => array('LT',$id)])->order('id DESC')->find();
        }else{
            $result = M('article_vedio')->where(['id' => array('GT',$id)])->order('id')->find();
        }
        return $result;
    }

    /**
     *  [如果article_vedio表中的讲师为空，则关联article_users表获取讲师及讲师头像信息]
     *  @param int $teache [视频表中的teacher字段]
     */
    public function getAuthorInfo($teacher){
        if(empty($teacher)){
            return false;
        }

        $map['a.isdelete'] = array("EQ", 0);
        $map['b.status'] = array("EQ", 1);
        $map['a.teacher'] = array("EQ", $teacher);

        $data = M("article_vedio")->alias("a")
            ->join("inner join qz_article_users as b on a.teacher = b.id")
            ->field("b.author, b.logo")
            ->where($map)
            ->find();

        return $data;
    }


    /**
     *  [获取最后三条视频信息]
     *
     */
    public function getLastThreeVideoInfo($limit=3){
        $data = M("article_vedio")->order("id desc")->limit($limit)->select();

        //把取出的数据按照id正序排序
        for($i=3; $i>0; $i--){
            $result[$limit-$i] = $data[$i-1];
        }

        $ids = [];

        foreach($result as $key => $value){

            $ids[] = $value['id'];

            if(empty($value['author'])){
                $data = $this->getAuthorInfo($value['teacher']);
                $result[$key]['author'] = $data['author'];
                $result[$key]['logo'] = $data['logo'];
            }
        }

        return array("list"=>$result, "ids"=>$ids);

    }


    /**
     *  [返回第一条数据的id]
     *
     */
    public function getFirstId(){
        return M("article_vedio")->field("id")->order("id")->find();
    }


    /**
     *  [判断数据表中是否存在该条数据]
     *
     */
    public function getDataExists($id){
        return M("article_vedio")->field("id")->find($id);
    }


    /**
     *  [装修攻略首页获取最新的四条视频]
     *
     */
    public function getVideoListByGonglue($limit=4){
        return M("article_vedio")->field("id, title, cover_img, url")->where(["isdelete"=>0])->order("id desc")->limit($limit)->select();
    }

    public function getVideoInfoById($ids, $field = 'id')
    {
        $where = [
            'id' => ['in', $ids]
        ];
        return $this->field($field)->where($where)->select();
    }

}