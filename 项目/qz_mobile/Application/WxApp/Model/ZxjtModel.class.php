<?php

/**
 * 微信小程序 - 装修讲堂 Model
 */

namespace Common\Model;
use Think\Model;

class ZxjtModel extends Model{

    protected $autoCheckFields = false;


    /**
     * Gets the video List.
     *
     * @param      string          $condition  The condition
     * @param      integer|string  $pagesize   The pagesize
     * @param      integer         $pageRow    The page row
     *
     * @return     <type>          The video.
     */
    public function getVideoList($condition = '',$pageIndex = 1,$pageRow = 10){

        $pageIndex = ($pageIndex - 1) * $pageRow;

        if(!empty($condition['orderby'])){
            $orderby = $condition['orderby'];
            unset($condition['orderby']);
        }

        if(empty($condition['v.theme'])){
            $condition['v.theme'] = array('EGT','1');
        }

        return M('weixin_vedio')->alias('v')
                ->field('v.*,u.author as teacher_name,u.logo as teacher_avatar')
                ->join("left join qz_article_users as u on u.id = v.teacher")
                ->order($orderby)
                ->limit($pageIndex.",".$pageRow)
                ->where($condition)
                ->select();
    }

    /**
     * Gets the video by id.
     *
     * @param      <type>  $id     The identifier
     *
     * @return     <type>  The video by identifier.
     */
    public function getVideoById($id){
        $map['v.id'] = array("EQ",$id);
        $map['v.theme'] = array('EGT','1');

        return M('weixin_vedio')
                ->alias('v')
                ->field('v.id,v.title,v.description,v.cover_img as thumb,v.pv,v.pnum as likes,v.time,wxapp_url,u.author as teacher_name,u.logo as teacher_avatar')
                ->join("left join qz_article_users as u on u.id = v.teacher")
                ->where($map)
                ->find();
    }

    /**
     * Gets the comment by id.
     *
     * @param      <type>  $id     The identifier
     *
     * @return     <type>  The comment by identifier.
     */
    public function getCommentById($id){

        $map['a.ref_id'] = array('EQ',$id);
        $map['a.module'] = array('EQ','wxvideo');
        $map['a.isdelete'] = array('EQ','0');
        $map['a.reply_id'] = array('EQ','0');

        return M('comment_full')
                ->alias('a')
                ->field('a.*,u.logo')
                ->join("join qz_user u on u.id = a.userid")
                ->where($map)
                ->order('id DESC')
                ->select();
    }

    /**
     * Gets the favorite by user.
     *
     * @param      <type>  $url    The url
     *
     * @return     <type>  The fav by uid.
     */
    public function getFavByUser($vid,$uid){

        $map['uid'] = array('EQ',$uid);
        $map['vid'] = array('EQ',$vid);

        return M('user_video_store')->field('*')->where($map)->find();
    }

    /**
     * Adds a comment.
     *
     * @param      mixed  $data   The data
     *
     * @return     mixed
     */
    public function addComment($data){
        return M("comment_full")->add($data);
    }

    /**
     * Gets the user information.
     *
     * @param      int  $uid    The uid
     *
     * @return     array  The user information.
     */
    public function getUserInfo($uid){
        $map = array(
            'id' => array('EQ',$uid)
        );
        return M("user")->where($map)->field('*')->find();
    }

    /**
     * Gets the cid by city.
     *
     * @param      <type>  $city   The city
     *
     * @return     <type>  The cid by city.
     */
    public function getCidByCity($city){
        $map['cid']  = array('EQ', $city);
        $map['cname']  = array('EQ',$city);
        $map['_logic'] = 'or';
        return M('quyu')->where($map)->field('*')->find();
    }

    /**
     * Adds a favorite.
     *
     * @param      <type>  $data   The data
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    public function addFavorite($data){
        return M("user_video_store")->add($data);
    }



}