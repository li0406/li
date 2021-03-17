<?php

namespace WxApp\Controller;
use WxApp\Common\Controller\WxAppBaseController;

class WxjiangtangController extends WxAppBaseController{

    /**
     * 移动端微信小程序 -- 我的收藏
     * @return [type] [description]
     */
    public function likeStore()
    {

        $uid = I('get.uid');
        $limit = I('get.limit');

        //查询我的收藏记录数组
        $result = D('Userhistory')->getMyStoreVideos($uid,$limit);

        $scheme_host = getSchemeAndHost();
        foreach ($result as $k => $v) {
            $result[$k]['store_time'] = date("Y-m-d",$v['store_time']);
            $result[$k]['cover_img'] = $scheme_host["scheme_full"].C('QINIU_DOMAIN').'/'.$v['cover_img'];
            $result[$k]['teacher_logo'] = $scheme_host["scheme_full"].C('QINIU_DOMAIN').'/'.$v['teacher_logo'];

            $time_arr[] = $result[$k]['store_time'];
        }
        if(empty($limit)){
            $time_arr = array_unique($time_arr);
            arsort($time_arr);//时间倒序
            foreach ($time_arr as $k => $v) {
                foreach ($result as $key => $value) {
                    if($v == $value['store_time']){
                        $videos[$v][] = $value;
                    }
                }
            }
            //echo json_encode($videos);
            $this->ajaxReturn(array('data' => $videos,'status' => 1));
        }else{
            //echo json_encode($result);
            $this->ajaxReturn(array('data' => $result,'status' => 1));
        }
    }

    /**
     * 移动端微信小程序 -- 编辑收藏内容（添加/删除）
     * @param  [string]         $uid                小程序用户ID
     * @param  [string]         $vids          操作的视频ID
     * @param  [string]         $type               操作类型（del 删除 | edit 编辑）
     * @return [type] [description]
     */
    public function editfav()
    {
        $uid = I("get.uid");
        $vids = I("get.vids");
        $type = I("get.type");

        $result = D('Userhistory')->editMyFavStore($uid,$vids,$type);
        //echo json_encode($result);
        $this->ajaxReturn(array('data' => $result,'status' => 1));
    }

    
}