<?php

/**
 * 微信小程序 - 装修讲堂
 */

namespace WxApp\Controller;
use WxApp\Common\Controller\WxAppBaseController;

class ZxjtController extends WxAppBaseController {

    protected $scheme_host;

    public function _initialize(){
        parent::_initialize();
        $this->scheme_host = getSchemeAndHost();
    }

    /**
     * 首页
     *
     * http://wxapp.qizuang.com/zxjt/index
     */
    public  function index(){
        $condition['orderby'] = 'v.time DESC';
        $pagesize= 1;
        $pageRow = 5;

        $list = D('Zxjt')->getVideoList($condition,$pagesize,$pageRow);

        foreach ($list as $k => $v) {
            $output['videoList'][] = array(
                'id' => $v['id'],
                'img' => $this->scheme_host["scheme_full"].C('QINIU_DOMAIN').'/'.$v['cover_img'],
                'title' => $v['title'],
                'detail' => $v['description'],
                'teacher' => $v['teacher_name'],
                'videoAdress' => $v['wxapp_url'],
            );
        }
        echo json_encode($output);
        die;
    }

    /**
     * 搜索功能
     *
     * http://wxapp.qizuang.com/zxjt/search/keyword/[keyword]
     */
    public function search(){

        $keyword = I('get.keyword');

        if(empty($keyword)){
            $this->ajaxReturn(array('data' => '','status' => 0));
        }

        $condition['v.title'] = array('like','%'.$keyword.'%');
        $condition['orderby'] = 'v.time DESC';
        $pagesize= 1;
        $pageRow = 5;

        $list = D('Zxjt')->getVideoList($condition,$pagesize,$pageRow);

        foreach ($list as $k => $v) {
            $output['videoList'][] = array(
                'id' => $v['id'],
                'img' => $this->scheme_host["scheme_full"].C('QINIU_DOMAIN').'/'.$v['cover_img'],
                'title' => $v['title'],
                'detail' => $v['description'],
                'teacher' => $v['teacher_name'],
                'videoAdress' => $v['wxapp_url'],
            );
        }
        echo json_encode($output);
        die;
    }

    /**
     * 分类
     *
     * http://wxapp.qizuang.com/zxjt/category/id/[category_id]/start/[start_number]
     */
    public function category(){

        //获取分类ID
        $cid = I('get.id');
        if(empty($cid)){
            $this->ajaxReturn(array('data' => '','status' => 0));
        }

        //获取分页ID
        $pageIndex = 1;
        $pageRow = 15;
        $pageid = I('get.start');
        if(!empty($pageid)){
            if(!is_numeric($pageid)){
                $this->ajaxReturn(array('data' => '','status' => 0));
            }
            $pageIndex = intval($pageid);
        }


        $condition['v.theme'] = array('EQ',$cid );
        $condition['orderby'] = 'v.time DESC';

        $list = D('Zxjt')->getVideoList($condition,$pageIndex,$pageRow);


        foreach ($list as $k => $v) {
            $result[] = array(
                'id' => $v['id'],
                'img' => $this->scheme_host["scheme_full"].C('QINIU_DOMAIN').'/'.$v['cover_img'],
                'title' => $v['title'],
                'pv' => $v['pv'],
            );
        }
        $this->ajaxReturn(array('data' => $result,'status' => 1));
    }

    /**
     * 视频详情页
     *
     * http://wxapp.qizuang.com/zxjt/video/id/[video_id]/
     */
    public function video(){
        $id = I('get.id');
        if(empty($id)){
            $this->ajaxReturn(array('data' => '','status' => 0));
        }

        $result = D('Zxjt')->getVideoById($id);
        //对应视频表pv(浏览PV)+1
        $w_where['id'] = $id;
        M('weixin_vedio')->where($w_where)->setInc("pv");

        $this->ajaxReturn(array('data' => $result,'status' => 1));
    }

    /**
     * 评论列表
     *
     * http://wxapp.qizuang.com/zxjt/comment/act/[action]/id/[id]/
     */
    public function comment(){

        $act = I('get.act');
        $id = I('get.id');
        if(empty($id) || empty($act)){
            $this->ajaxReturn(array('data' => '','status' => 0));
        }

        //获取评论信息
        if($act == 'get'){
            $result = D('Zxjt')->getCommentById($id);
            $this->ajaxReturn(array('data' => $result,'status' => 1));
        }

        //提交评论
        if($act == 'add'){
            $uid = I('post.uid');
            $content = I('post.content');
            if(empty($id) || empty($act) || empty($content)){
                $this->ajaxReturn(array('data' => '','status' => 0));
            }

            $userInfo = D('Zxjt')->getUserInfo($uid);
            if(empty($userInfo)){
                $this->ajaxReturn(array('data' => '没有此用户','status' => 0));
            }

            //判断是否有内容 TODO

            //对应模块
            $data['module'] = 'wxvideo';
            //用户ID
            $data['userid'] = $uid;
            //用户名称
            $data['username'] = $userInfo['name'];
            //所在城市
            $data['cs'] = empty($userInfo['cs']) ? '0' : $userInfo['cs'];
            //对应的内容编号
            $data['ref_id'] = $id;
            //评论内容
            $data['content'] = $content;
            //发布时间
            $data['time'] = time();

            $add = D('Zxjt')->addComment($data);
            if($add){
                //对应视频表pnum(评论数字段)+1
                $w_where['id'] = $id;
                M('weixin_vedio')->where($w_where)->setInc("pnum");
                $this->ajaxReturn(array('data' => '发布成功','status' => 1));
            }else{
                $this->ajaxReturn(array('data' => '发布失败','status' => 0));
            }
        }
    }

    /**
     * 收藏
     *
     * http://wxapp.qizuang.com/zxjt/favorite/act/[action]/vid/[video_id]/uid/[user_id]
     */
    public function favorite(){
        $act = I('get.act');
        $uid = I('get.uid');
        $vid = I('get.vid');

        if(!is_numeric($uid) || !is_numeric($vid)){
            $this->ajaxReturn(array('data' => '','status' => 0));
        }

        //查询是否收藏此视频
        //http://wxapp.qizuang.com/zxjt/favorite/act/check/vid/[video_id]/uid/[user_id]
        if($act == 'check'){
            $result = D('Zxjt')->getFavByUser($vid,$uid);

            if(!empty($result)){
                $this->ajaxReturn(array('data' => '','status' => 1));
            }
        }

        //加入收藏
        //http://wxapp.qizuang.com/zxjt/favorite/act/add/vid/[video_id]/uid/[user_id]
        if($act == 'add'){
            $data['uid'] = $uid;
            $data['vid'] = $vid;
            $data['time'] = time();

            if(D('Zxjt')->addFavorite($data)){
                $this->ajaxReturn(array('data' => '','status' => 1));
            }
        }


        $this->ajaxReturn(array('data' => '','status' => 0));
    }

    /**
     * 获取城市数据URL
     *
     * http://wxapp.qizuang.com/zxjt/getcityurl/
     */
    public function getcityurl(){

        $result = $this->scheme_host["scheme_full"].OP('QINIU_DOMAIN').'/common/js/'.OP('ALL_REAL_VIP_PCA_JSON');

        $this->ajaxReturn(array('data' => $result,'status' => 1));
    }


      /**
     * 装修报价详细页面
     * @return [type] [description]
     */
    public function details () {

        $orderid = I('get.id');

        $order = D("Orders")->getOrderInfoById($orderid);

        if(count($order) > 0){

            switch ($order["huxing"]) {
                case '38':
                    $order["shi"] = "1";
                    $order["ting"] = "1";
                    $order["wei"] = "1";
                    break;
                case '39':
                    $order["shi"] = "2";
                    $order["ting"] = "1";
                    $order["wei"] = "1";
                    break;
                case '40':
                    $order["shi"] = "2";
                    $order["ting"] = "2";
                    $order["wei"] = "1";
                    break;
                case '41':
                    $order["shi"] = "2";
                    $order["ting"] = "2";
                    $order["wei"] = "2";
                    break;
                case '42':
                    $order["shi"] = "3";
                    $order["ting"] = "2";
                    $order["wei"] = "1";
                    break;
                case '43':
                    $order["shi"] = "3";
                    $order["ting"] = "2";
                    $order["wei"] = "2";
                    break;
                case '46':
                    $order["shi"] = "3";
                    $order["ting"] = "1";
                    $order["wei"] = "1";
                    break;
                case '47':
                    $order["shi"] = "3";
                    $order["ting"] = "2";
                    $order["wei"] = "1";
                    break;
                case '48':
                    $order["shi"] = "4";
                    $order["ting"] = "2";
                    $order["wei"] = "2";
                    break;
                case '49':
                    $order["shi"] = "5";
                    $order["ting"] = "2";
                    $order["wei"] = "2";
                    break;
                case '50':
                    $order["shi"] = "6";
                    $order["ting"] = "2";
                    $order["wei"] = "3";
                    break;
            }

            //如果户型为空,根据面积添加默认的户型
            if (empty($order["huxing"])) {
                if ($order["mianji"] < 50) {
                    $order["shi"] = "1";
                    $order["ting"] = "1";
                    $order["wei"] = "1";
                } else if ($order["mianji"] >= 50 && $order["mianji"] < 80) {
                    $order["shi"] = "2";
                    $order["ting"] = "1";
                    $order["wei"] = "1";
                } else if ($order["mianji"] >= 80 && $order["mianji"] < 110) {
                    $order["shi"] = "2";
                    $order["ting"] = "2";
                    $order["wei"] = "1";
                } else if ($order["mianji"] >= 120 && $order["mianji"] < 150) {
                    $order["shi"] = "3";
                    $order["ting"] = "2";
                    $order["wei"] = "1";
                } else if ($order["mianji"] >= 150 ) {
                    $order["shi"] = "4";
                    $order["ting"] = "2";
                    $order["wei"] = "1";
                } else {
                    $order["shi"] = "2";
                    $order["ting"] = "1";
                    $order["wei"] = "1";
                }
            }

            //没有装修档次默认精装
            if (empty($order["zxdc"])) {
               $order["zxdc"] = 2;
            }

            $location = R("Mobile/Zb/getZxInfo",array($order));
            $result = getPricesTmp($order["mianji"],$order["zxdc"],$location["data"],$orderid,$order["cs"]);

            $this->ajaxReturn(array('data' => $result,'status' => 1));

        }

        $this->ajaxReturn(array('data' => '','status' => 0));
    }

    /**
     * 发布订单
     */
    public function submit_order(){
        //获取城市ID
        $cs = I('post.cs');
        if(!empty($cs)){
            $cs = explode(',',$cs);
            $cs = str_replace('市','',$cs['1']);

            $cityInfo = D('Zxjt')->getCidByCity($cs);
            if(!empty($cityInfo)){
                $_POST['cs'] = $cityInfo['cid'];
            }else{
                $_POST['cs'] = '000001';
            }
        }

        $_POST['step'] = 'wxapp';

        R("Common/Zbfb/fb_order");
        die();
    }

    /**
     * 发布订单
     */
    public function submit_order_v2(){
        //获取城市ID
        $cs = I('post.cs');
        if(!empty($cs)){
            $cs = explode(',',$cs);
            $qx = $cs['2'];
            $cs = str_replace('市','',$cs['1']);

            $cityInfo = D('Zxjt')->getCidByCity($cs);
            if(!empty($cityInfo)){
                $_POST['cs'] = $cityInfo['cid'];
            }else{
                $_POST['cs'] = '000001';
            }
            if(!empty($qx) && is_numeric($qx)){
                $_POST['qx'] = $qx;
            }
        }

        $_POST['step'] = 'wxapp';

        R("Common/Zbfb/fb_order");
        die();
    }



}