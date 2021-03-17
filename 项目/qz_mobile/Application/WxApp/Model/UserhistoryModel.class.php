<?php

/**
 * 专题页需要用到的Model
 */

namespace Common\Model;
use Think\Model;

class UserhistoryModel extends Model{

    protected $autoCheckFields = false;


    /**
     * 通过UID获取视频收藏记录
     * @param  [string]         $uid        小程序用户ID
     * @return [array|void]     $return     返回我的收藏数组
     */
    public function getMyStoreVideos($uid,$limit){
        $map['s.uid']  = array("EQ",$uid);
        $map['v.isdelete'] = array("EQ",0);
        if(!empty($limit)) $limit = 5;

        $result = M('user_video_store')->alias('s')
                                    ->join('left join qz_weixin_vedio as v on v.id = s.vid')
                                    ->join('left join qz_article_users as u on u.id = v.teacher and u.status = 1')
                                    ->field("s.uid as user_id,s.time as store_time,v.id as video_id,v.title as video_title,v.description as video_desc,v.cover_img,v.pv,v.pnum as likes,v.theme,v.teacher,v.wxapp_url,u.author,u.logo as teacher_logo")
                                    ->where($map)
                                    ->limit($limit)
                                    ->select();

        //var_dump(M()->getLastSql());
        return $result;
    }

    /**
     * 编辑视频收藏记录
     * @param  [string]         $uid        小程序用户ID
     * @param  [string]         $vids       删除的收藏ID
     * @param  [string]         $type       操作方式   del 删除 | add 添加
     * @return [array|void]     $return     返回我的收藏数组
     */
    public function editMyFavStore($uid,$vids,$type)
    {
        if($type == 'del'){
            //删除
            $map['uid'] = array("EQ",$uid);
            $map['vid'] = array("IN",$vids);
            $result = M("user_video_store")->where($map)->delete();
        }else{
            //添加
            $data['uid'] = $uid;
            $data['time'] = time();
            $data['vid'] = $vids;
            $result = M("user_video_store")->add($data);
        }
        return $result;
    }

    /**
     * 通过UID获取视频观看记录
     * @param  [string]         $uid        小程序用户ID
     * @return [array|void]     $return     返回我的收藏数组
     */
    public function getMyHistoryVideos($uid){
        $map['s.uid']  = array("EQ",$uid);
        $map['v.isdelete'] = array("EQ",0);

        $result = M('user_video_history')->alias('s')
                                    ->join('left join qz_article_vedio as v on v.id = s.vid')
                                    ->join('left join qz_article_users as u on u.id = v.teacher and u.status = 1')
                                    ->field("s.uid as user_id,s.time as store_time,v.id as video_id,v.title as video_title,v.description as video_desc,v.cover_img,v.pv,v.likes,v.theme,v.teacher,v.wxapp_url,u.author,u.logo as teacher_logo")
                                    ->where($map)
                                    ->select();


        return $result;
    }


    /**
     * 通过$wx_unionid 获取用户信息
     * @param  [string]         $wx_unionid         小程序用户wx_unionid
     * @return [array|void]     $user               用户信息
     */
    public function getUserInfoByUnionid($wx_unionid)
    {
        $where['wx_unionid'] = array("EQ",$wx_unionid);

        $user = M("user")->where($where)->find();

        return $user;
    }


    /**
     * 通过$wx_openid 获取用户信息
     * @param  [string]         $wx_openid         小程序用户wx_openid
     * @return [array|void]     $user               用户信息
     */
    public function getUserInfoByOpenid($wx_openid)
    {
        $where['wx_openid'] = array("EQ",$wx_openid);

        $user = M("user")->where($where)->find();

        return $user;
    }


}