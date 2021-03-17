<?php

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Monolog\Handler\IFTTTHandler;

class VideoController extends HomeBaseController{

    public function _initialize(){
        parent::_initialize();
        $uri = $_SERVER['REQUEST_URI'];
        preg_match('/html$/',$uri,$m);
        if (count($m) == 0) {
            preg_match('/\/$/',$uri,$m);
            $parse = parse_url($uri);
            if (count($m) == 0 && empty($parse["query"])) {
                header( "HTTP/1.1 301 Moved Permanently");
                header("Location: ".$this->SCHEME_HOST['scheme_full'] .  C("QZ_YUMINGWWW").$uri."/");
            }
        }
        //添加顶部搜索栏信息
        $this->assign('serch_uri','video/jiangtang');
        $this->assign('serch_type','装修视频');
        $this->assign('holdercontent','视频学装修更简单');
        //导航栏标识
        $this->assign("tabIndex",5);
    }
    /**
     * [category 视频列表页,视频讲堂，视频头条]
     * @return [type] [description]
     */
    public function category()
    {
        $type = I('get.type');
        $keyword = I('get.keyword');
        $info['type'] = $type == 1 ? '装修讲堂' : '装修头条';
        //带有动态分页跳转到静态分页
        if (!empty($_GET['p'])) {
            $temp = intval($_GET['p']);
            $category = ($type == 1) ? 'jiangtang' : 'toutiao';
            if ($temp > 1) {
                $url = $this->SCHEME_HOST['scheme_full'] .'www.' . C('QZ_YUMING') . '/video/' . $category . '/p-' . $temp . '.html';
            } else {
                $url = $this->SCHEME_HOST['scheme_full'] .'www.' . C('QZ_YUMING') . '/video/' . $category . '/';
            }
            header("HTTP/1.1 301 Moved Permanently");
            header("Location:" . $url);
            die();
        }
        $start = 1;
        $each = 10;
        if(!empty($_GET["page"])){
            $start = I('get.page');
            $pageContent = $start > 1 ? "-第".$start."页" : "";
        }

        //TDK
        $info['head']['title'] = $info['type'] . $pageContent . '-齐装网';
        $info['head']['keywords'] = $info['type'];
        $info['head']['description'] = $info['type'];

        //获取推荐视频
        if(empty($keyword)){
            $info['recommend'] = D('ArticleVedio')->getRecommendArticleVedio($type)[0];
        }else{
            $info['recommend'] = '';
        }
        //获取视频列表
        $info['info'] = $this->getArticleVedioList($type,$start,$each,$keyword);
        /*S-底部设计浮动框*/
        $this->assign("adv_bottom",$this->fetch(T("Common@Order/zb_bottom_s")));
        //获取是否显示获取报价弹层
        if(!isset($_COOKIE["w_index"])){
            setcookie("w_index",1,time()+(3600*24),'/', '.'.APP_HTTP_DOMAIN);
            $info['open'] = true;
        }
        /*E-底部设计浮动框*/

        if(1 == $start){
            $info['canonical'] = ($type == 1) ? 'jiangtang' : 'toutiao';
        }

        if ($type == 1) {
            //装修讲堂
            $info['head']['title'] = '【装修大讲堂】装修验房,装修知识,装修步骤_齐装网装修讲堂';
            $info['head']['keywords'] = '装修验房,装修知识,装修步骤,墙面装修材料,水电改造,装修材料';
            $info['head']['description'] = '齐装网家装大讲堂频道提供原创装修知识讲解类视频。主要针对的群体是有装修需求的观众，为观众提供建议、答疑解惑。讲解内容围绕从装修前的收房、验房到装修后软装等。';
        } else {
            $info['head']['title'] = '【装修头条】装修经验,装修误区,装修知识_齐装网装修头条';
            $info['head']['keywords'] = '装修头条,装修经验,装修误区,装修知识';
            $info['head']['description'] = '装修误区那么多，处处小心是真理！齐装网装修头条为您详细介绍装修中可能遇到的装修误区，让您在装修的路上少走弯路！';
        }
        $this->assign('info',$info);
        //视屏讲堂默认选中
        $this->assign('choose_gonglue','video');

        $this->display();
    }

    /**
     * [terminal 视频终端页]
     * @return [type] [description]
     */
    public function terminal()
    {
        $id = I('get.id');
        if(empty($id)){
            $this->_error();
        }
        $info['info'] = S("CACHE:VIDEO:".$id);
        if (!$info['info']) {
            $info['info'] = D('ArticleVedio')->getArticleVedioById($id);
            if(empty($info['info'])){
                $this->_error();
            }
            S("CACHE:VIDEO:".$id,$info['info'],900);
        }

        //设置浏览量
        D('ArticleVedio')->addArticleVedioPvById($id);

        $info['type'] = $info['info']['type'] == 1 ? '装修讲堂' : '装修头条';
        $info['link'] = $info['info']['type'] == 1 ? 'jiangtang' : 'toutiao';

        //TDK
        $info['head']['title'] = $info['info']['title'].'-'.$info['type'].'-齐装网';
        $info['head']['keywords'] = $info['info']['title'];
        $info['head']['description'] = $info['info']['description'];

        //获取上一个视频，下一个视频
        $info['prev'] = D('ArticleVedio')->getNearArticleVedio($info['info']['type'],$info['info']['id'],'prev');
        $info['next'] = D('ArticleVedio')->getNearArticleVedio($info['info']['type'],$info['info']['id'],'next');

        //获取推荐视频
        $info['recommend'] = S("CACHE:VIDEO:RECOMMEND:".$info['info']['type']);
        if (!$info['recommend']) {
            $info['recommend'] = D('ArticleVedio')->getArticleVedioList($info['info']['type'],0,8,'','rand()');
            S("CACHE:VIDEO:RECOMMEND:".$info['info']['type'],$info['recommend'],900);
        }

        //获取评论模板获取评论人数
        $this->assign("module","video");
        $count = D("CommentFull")->getCommentCount("video",$info['info']['id']);
        $this->assign("rel_number",$count);
        $this->assign("rel_id",$info['info']['id']);
        $t = T("Common@Comment/reply");
        $tmp = $this->fetch($t);
        $this->assign("reply",$tmp);


        //获取最新的2条评论
        $comments = S("CACHE:VIDEO:COMMENTS:".$info['info']['id']);
        if (!$comments) {
            $comments = D("CommentFull")->getNewCommentFullLists("video",$info['info']['id'],2);
            S("CACHE:VIDEO:COMMENTS:".$info['info']['id'],$comments,900);
        }

        $this->assign("comments",$comments);
        $t = T("Common@Comment/comment");
        $tmp = $this->fetch($t);
        $this->assign("comments",trim($tmp));

        /*S-底部设计浮动框*/
        $this->assign("adv_bottom",$this->fetch(T("Common@Order/zb_bottom_s")));
        //获取是否显示获取报价弹层
        if(!isset($_COOKIE["w_index"])){
            setcookie("w_index",1,time()+(3600*24),'/', '.'.APP_HTTP_DOMAIN);
            $info['open'] = true;
        }
        /*E-底部设计浮动框*/
        //获取报价模版
        $this->assign("order_source",146);
        $info['orderTmp'] = $this->fetch(T("Common@Order/orderTmp"));
        //获取黄历报价模版
        //$info['hlBaoJia'] = $this->fetch(T("Common@Order/hlbaojia2"));
        $this->assign('info',$info);
        $this->display();
    }


    /**
     * [comment 视频评论页]
     * @return [type] [description]
     */
    public function comment()
    {
        $id = I("get.id");
        $info['info'] = D('ArticleVedio')->getArticleVedioById($id);
        if(empty($info['info'])){
            $this->_error();
        }

        //获取评论模板
        $this->assign("module","video");
        $count = D("CommentFull")->getCommentCount("video",$id);
        $this->assign("rel_number",$count);
        $this->assign("rel_id",$id);
        $t = T("Common@Comment/reply");
        $tmp = $this->fetch($t);
        $this->assign("reply",$tmp);

        $pageIndex = 1;
        $pageCount = 10;
        if(I("get.p") != ""){
            $pageIndex = I("get.p");
        }
        //获取评轮
        $comments = D("CommentFull")->getCommentFullLists($pageIndex,$pageCount,"video",$id);
        $this->assign("comments",$comments["list"]);
        $t = T("Common@Comment/comment");
        $tmp = $this->fetch($t);
        $this->assign("comments",$tmp);
        $this->assign("page", $comments["page"]);
        //获取总评论数量
        $this->assign("count",$comments["count"]);
        //获取报价模板

        //获取底部弹层
        $t = T("Common@Order/zb_bottom_b");
        $zb_bottom_s = $this->fetch($t);
        $this->assign("zb_bottom_s",$zb_bottom_s);

        //获取报价模版
        $this->assign("order_source",150);
        $t = T("Common@Order/orderTmp");
        $orderTmp = $this->fetch($t);
        $this->assign("orderTmp",$orderTmp);
        $this->assign("info",$info);
        $this->display();
    }



    /**
     * [likeAction AJAX请求喜欢]
     * @return [type] [description]
     */
    public function likeAction()
    {
        $id = I('post.id');
        if(!empty($id)){
            $result = D('ArticleVedio')->addArticleVedioLikesById($id);
            if($result){
                $this->ajaxReturn(array('data'=>'','info'=>'操作成功','status'=>1));
            }
        }
        $this->ajaxReturn(array('data'=>'','info'=>'操作失败','status'=>0));
    }

    /**
     * [nolikeAction AJAX请求喜欢减1]
     * @return [type] [description]
     */
    public function nolikeAction()
    {
        $id = I('post.id');
        if(!empty($id)){
            $result = D('ArticleVedio')->decArticleVedioLikesById($id);
            if($result){
                $this->ajaxReturn(array('data'=>'','info'=>'操作成功','status'=>1));
            }
        }
        $this->ajaxReturn(array('data'=>'','info'=>'操作失败','status'=>0));
    }

    /**
     * [getArticleVedioList 获取列表]
     * @param  [type] $type    [description]
     * @param  [type] $start   [description]
     * @param  [type] $end     [description]
     * @param  [type] $keyword [description]
     * @return [type]          [description]
     */
    public function getArticleVedioList($type,$start,$each = 10,$keyword)
    {
        $count = D('ArticleVedio')->getArticleVedioCount($type,$keyword);
        $result = D('ArticleVedio')->getArticleVedioList($type,($start-1)*$each,$each,$keyword);
        $category = ($type == 1) ? 'jiangtang' : 'toutiao';
        if ($count > $each) {
            import('Library.Org.Page.SPage');
            $page = new \SPage($count, $each, array(
                'templet' => '/video/' . $category . '/p-[PAGE].html',
                'firstUrl' => '/video/' . $category . '/'
            ));
            $pageTmp =  $page->show();
        }
        return array("list"=>$result,"show"=>$pageTmp);
    }

    /*
    *装修视频改版首页
    *
     */
    public function index(){

        //获取点赞量最多的两条数据
        $likes = D("ArticleVedio")->getMaxTwoLikes();

        //ajax请求装修扫盲,装修设计,局部装修,选材导购分类下的数据
        if(!empty($_GET['pid'])  && !empty($_GET['cid'])){
            $ajaxData = $this->getVideoDataList($_GET['pid'], $_GET['cid']);
            if(!empty($ajaxData)){
                foreach($ajaxData as $key => $value){
                    $ajaxData[$key]['time'] = date("Y-m-d", $value['time']);
                }
                $this->ajaxReturn(array("status"=>1, "info"=>"success", "data"=>$ajaxData));
            }
            $this->ajaxReturn(array("status"=>0, "info"=>"error", "data"=>""));
        }


        //装修扫盲下的数据,装修扫盲分类为pid=3
        $zxsmData = $this->getVideoDataList(3);

        //装修设计下的数据
        $zxsjData = $this->getVideoDataList(1);

        //局部装修下的数据
        $jbsjData = $this->getVideoDataList(2);

        //选材导购下的数据
        $xcdgData = $this->getVideoDataList(4);

        $head = [
            "title" => "装修视频_室内装修视频_装修视频教程-齐装网",
            "keywords" => "装修视频,室内装修视频,装修视频教程",
            "description" => "齐装网装修视频栏目免费提供大量原创装修视频教程，为广大业主在进行室内装修过程中提供宝贵建议，同时也希望您从中学习到有关装修的知识。",
        ];

        $this->assign("head", $head);
        $this->assign("gonglue", trim($_SERVER['REQUEST_URI'], "/"));
        $this->assign("zxsmData", $zxsmData);
        $this->assign("zxsjData", $zxsjData);
        $this->assign("jbsjData", $jbsjData);
        $this->assign("xcdgData", $xcdgData);
        $this->assign("likes", $likes);
        //视屏为选中状态
        $this->assign('choose_gonglue', 'video');
        $this->display();
    }


    /*
     *装修视频列表页改版
     */
    public function videolist(){
        //用于前端样式判断
        $select_class = ['pid'=>0, 'cid'=>0];

        if(!empty($_GET['pid'])){
            $map['b.pid'] = array("EQ", $_GET['pid']);
            $select_class['pid'] = $_GET['pid'];
        }
        if(!empty($_GET['cid'])){
            $map['b.cid'] = array("EQ", $_GET['cid']);
            $select_class['cid'] = $_GET['cid'];
        }

        $pageIndex = 1;
        if(!empty($_GET['p'])){
            $pageIndex = remove_xss($_GET["p"]);
        }

        //按照时间倒序排列获取和数据
        $result = $this->getVideoListData($pageIndex, 8, $map);

        // $noresult 用于判断根据相关条件是否搜索到数据
        $noresult = 0;
        if(empty($result['list']) || $result == "false"){
            //获取推荐数据
            $result['list'] = D("ArticleVedio")->getVideoListRecommend();
            $noresult = 1;
        }

        $url = explode("/", rtrim($_SERVER["REQUEST_URI"], "/"));
        $new_url = "/" . $url[1];

        //装修视频列表页TDK
        $head = [];
        if($_GET['pid'] == 1){
            if($_GET['cid'] == "1"){
                $tdk = "空间利用";
            }elseif($_GET['cid'] == "2"){
                $tdk = "动线设计";
            }elseif($_GET['cid'] == "3"){
                $tdk = "案例分享";
            }
            $head = [
                "title" => "【装修视频】装修" . $tdk . "在线教学视频_齐装网装修讲堂",
                "keywords" => "装修" . $tdk . "教学视频",
                "description" => "齐装网装修讲堂为你提供装修过程中" . $tdk . "的各类教学视频。",
            ];
        }

        if($_GET['pid'] == 2){
            if($_GET['cid'] == 4){
                $tdk = "客厅";
            }elseif($_GET['cid'] == 5){
                $tdk = "卫生间";
            }elseif($_GET['cid'] == 6){
                $tdk = "厨房";
            }elseif($_GET['cid'] == 7){
                $tdk = "阳台";
            }elseif($_GET['cid'] == 8){
                $tdk = "卧室";
            }
            $head = [
                "title" => "【装修视频】" . $tdk . "装修在线教学视频_齐装网装修讲堂",
                "keywords" => $tdk . "装修教学视频",
                "description" => "齐装网装修讲堂为你提供" . $tdk . "装修过程中各类教学视频。",
            ];
            if($_GET['cid'] == 9){
                $head = [
                    "title" => "【其他局部装修视频】书房阳台等局部装修在线教学视频_齐装网装修讲堂",
                    "keywords" => "书房装修教学视频，阳台装修教学视频，其他局部装修视频装修教学视频",
                    "description" => "齐装网装修讲堂为你提供书房、阳台、走廊等局部装修过程中各类教学视频。",
                ];
            }
        }

        if($_GET['pid'] == 3){
            if($_GET['cid'] == 10){
                $head = [
                    "title" => "【装修视频】装修合同签订-装修施工合同签订细节-齐装网装修讲堂",
                    "keywords" => "装修合同签订,装修施工合同,装修合同细节讲解",
                    "description" => "齐装网装修讲堂为大家详细讲解装修过程中合同签订流程，施工合同签订细节及注意事项，让您了解装修合同签订过程中的常见问题，房子装修更放心。",
                ];
            }elseif($_GET['cid'] == 11){
                $head = [
                    "title" => "【装修视频】装修增减项-装修增减项知识详解-齐装网装修讲堂",
                    "keywords" => "装修增减项,装修增减项对策，水电路改造",
                    "description" => "齐装网装修讲堂为大家详细讲解装修过程中增减项相关事宜，包括水电路改造，木工项目增减等装修增减项知识，并为您提出有效的装修增减项对策，让你装修不花冤枉钱。",
                ];
            }elseif($_GET['cid'] == 12){
                $head = [
                    "title" => "【装修视频】装修猫腻-装修猫腻大盘点-齐装网装修讲堂",
                    "keywords" => "装修猫腻,装修报价猫腻，装修施工猫腻",
                    "description" => "齐装网装修讲堂为大家详细讲解装修过程中存在的猫腻，包括装修合同猫腻，装修报价猫腻，装修施工猫腻等，让您了解到装修过程中那些不为人知的猫腻，装修更轻松。",
                ];
            }elseif($_GET['cid'] == 13){
                $head = [
                    "title" => "【装修视频】装修常见问题-装修常见问题解决办法-齐装网装修讲堂",
                    "keywords" => "装修常见问题,装修问题集锦，装修常见问题怎么解决",
                    "description" => "齐装网装修讲堂为大家详细讲解装修过程中的常见问题，包括装修质量问题，装修工期问题，装修报价问题等装修问题集锦，并给您针对这些装修常见问题解决办法的合理建议。",
                ];
            }
        }

        if($_GET['pid'] == 4){
            if($_GET['cid'] == 14){
                $head = [
                    "title" => "【装修视频】装修材料-家居装修建材-装修材料品牌价格-齐装网装修讲堂",
                    "keywords" => "装修材料,装修材料品牌,装修材料价格",
                    "description" => "齐装网装修讲堂为大家介绍装修相关材料知识，包括装修材料品牌，装修材料报价，五金建材等，让大家对装修建材有更深入的了解，更好的了解装修材料清单价格表等相关装修知识。",
                ];
            }elseif($_GET['cid'] == 15){
                $head = [
                    "title" => "【装修视频】软装搭配-软装搭配技巧-齐装网装修讲堂",
                    "keywords" => "软装搭配,软装色彩搭配,软装搭配技巧",
                    "description" => "齐装网装修讲堂为大家介绍软装搭配及相关技巧，并给大家推荐实用的软装搭配方案，让你的房间装修看起来更潮流，更和谐，当然也更省钱。",
                ];
            }
        }
        //seo需求：更改部分页面的tdk，其他不变
		$module = str_replace('video','',trim($_SERVER['PATH_INFO'],'/'));
        switch (trim($module,'/')){
			case 'zhuangxiusm':
				$head = [
					'title' => '【装修视频】装修扫盲-齐装网装修讲堂',
					'keywords' => '装修扫盲视频',
					'description' => '齐装网家装大讲堂频道提供原创装修扫盲类视频。为有装修需求的观众提供装修合同签订/装修增减项/装修猫腻等常见问题。本栏目装修视频讲解内容丰富，通俗易懂，是装修业主了解装修入门知识的好帮手',
				];
				break;
			case 'zhuangxiusj':
				$head = [
					'title' => '【装修视频】装修设计-齐装网装修讲堂',
					'keywords' => '装修设计视频',
					'description' => '齐装网家装大讲堂频道提供原创装修设计类视频。为有装修需求的观众提供装修空间利用/动线设计/装修案例分享/ 等内容。本栏目装修视频讲解内容全面，装修案例丰富多变，为装修业主提供多样化装修设计参考',
				];
				break;
			case 'jubuzx':
				$head = [
					'title' => '【装修视频】局部设计-齐装网装修讲堂',
					'keywords' => '局部设计视频',
					'description' => '齐装网家装大讲堂频道提供原创装修局部设计类视频。为有装修需求的观众提供客厅装修/卫生间装修/厨房装修/阳台装修/卧室装修/等其他常见局部装修设计知识案例。本栏目装修视频讲解涵盖装修房子的角角落落，满足装修业主的实际需要',
				];
				break;
			case 'xuancaidg':
				$head = [
					'title' => '【装修视频】选材导购-齐装网装修讲堂',
					'keywords' => '选材导购视频',
					'description' => '齐装网家装大讲堂频道提供原创装修选材导购类视频。为有装修需求的观众提供装修材料选购知识/软装搭配技巧等选材内容。本栏目装修视频讲解材料类型丰富，软装搭配样式新颖使用，为装修业主在选购建材、装饰搭配方面提供参考',
				];
				break;
			default:
				break;
		}

        //给前端判断样式使用
        if(strstr($_SERVER['REQUEST_URI'], 'video')){
            $choose_url = "video";
        }

        $this->assign("gonglue", $choose_url);
        $this->assign("head", $head);
        $this->assign("selectClassPid", $select_class['pid']);
        $this->assign("selectClassCid", $select_class['cid']);
        $this->assign("noresult", $noresult);
        $this->assign("url", $new_url);
        $this->assign("result", $result);
        $this->display();
    }

    /*
     *装修视频搜索结果页
     */
    public function videodetail(){

        $id = I('get.id');
        //判断当前是不是最后三条视频信息
        $lastThreeVedio = D("ArticleVedio")->getLastThreeVideoInfo();
        $lastThreeVedioFlag = 0;
        if(in_array($id, $lastThreeVedio['ids'])){
            $lastThreeVedioFlag = 1;
            $this->assign("lastThreeVedio", $lastThreeVedio['list']);
        }
        //获取当前视频的详细信息
        $info = S("CACHE:VIDEO:DETAIL:".$id);
        if (!$info) {
            $info['info'] = D('ArticleVedio')->getArticleVedioById($id);
            if(empty($info['info'])){
                $this->_error();
            }
            //获取当前视频id正序排列的下两个的数据
            $info['nextList'] = D("ArticleVedio")->getNextVideoData($id);
            //为您推荐数据
            $info['recommend'] = D("ArticleVedio")->getVideoListRecommend();
            S("CACHE:VIDEO:DETAIL:".$id,$info,900);
        }

        //设置浏览量+1
        D('ArticleVedio')->addArticleVedioPvById($id);

        //给前端判断是否为第一个视频,用于左右滑动
        $dataFirst = 0;
        $idResult = D("ArticleVedio")->getFirstId();

        if($id == $idResult['id']){
            $dataFirst = 1;
        }

        //获取评论模板获取评论人数
        $this->assign("module","video");
        $count = D("CommentFull")->getCommentCount("video",$info['info']['id']);
        $this->assign("rel_number",$count);
        $this->assign("rel_id",$info['info']['id']);

        $t = T("Common@Comment/reply");
        $tmp = $this->fetch($t);
        $this->assign("reply",$tmp);

        //获取最新的3条评论
        $comments = S("CACHE:VIDEO:COMMENT:".$info['info']['id']);
        if (!$comments) {
            $comments = D("CommentFull")->getNewCommentFullLists("video", $info['info']['id'], 3);
            S("CACHE:VIDEO:COMMENT:".$info['info']['id'],$comments,900);
        }

        $this->assign("comments",$comments);

        $t = T("Common@Comment/comment");
        $tmp = $this->fetch($t);
        $this->assign("comments",trim($tmp));

        // 1353百度官方号需求
        $baiduInfo = $info['info'];
        $baidu['url'] = $this->SCHEME_HOST['scheme_full'].$this->SCHEME_HOST['host'].$_SERVER['REQUEST_URI'];
        $baidu['str'] = mb_substr(strip_tags($baiduInfo['description']), 0, 100, 'utf-8');
        $baidu['title'] = $baiduInfo["title"];
        $optimee =date("Y-m-d",$baiduInfo['time']);
        $optimes =date("H:i:s",$baiduInfo['time']);

        $baidu['optime'] = $optimee."T".$optimes;
        $baidu['addtime'] = $optimee."T".$optimes;
        $this->assign("baidu",$baidu);


        //给前端判断样式使用
        if(strstr($_SERVER['REQUEST_URI'], 'video')){
            $choose_url = "video";
        }

        $this->assign("dataFirst", $dataFirst);
        $this->assign("lastThreeVedioFlag", $lastThreeVedioFlag);
        $this->assign("gonglue", $choose_url);
        $this->assign("nextList", $info['nextList']);
        $this->assign("recommend", $info['recommend']);
        $this->assign("videoInfo", $info['info']);
        //添加默认选中效果
        $this->assign('choose_gonglue', 'video');
        $this->display();
    }

    //ajax获取当前视频的上一条或者下一条数据
    public function getAjaxData(){

        if(empty($_GET['extra']) || empty($_GET['id'])){
            return false;
        }

        $ajaxResult = D("ArticleVedio")->getAjaxData($_GET['extra'], $_GET['id']);

        if(!empty($ajaxResult)){
            $this->ajaxReturn(array("data"=>$ajaxResult, "info"=>"success", "status"=>1));
        }

        $this->ajaxReturn(array('data'=>'','info'=>'操作失败','status'=>0));
    }


    //装修视频首页数据获取
    private function getVideoDataList($pid, $cid=''){
        if(empty($cid)){
            $data = D("ArticleVedio")->getVideoList($pid);
            return $data;
        }

        $new_data = D("ArticleVedio")->getVideoList($pid, $cid);

        return $new_data;
    }


    //获取装修视频列表页数据
    private function getVideoListData($pageIndex = 1, $pageCount = 8, $map = [])
    {
        $count = D("ArticleVedio")->getVideoListDataCount($map);
        if (empty($count)) {
            return ['list' => [], 'page' => '', 'num' => 0];
        }

        //分页
        import('Library.Org.Page.LitePage');
        $page = new \LitePage($pageIndex, $pageCount, $count, ['prev', 'next']);

        $result = D("ArticleVedio")->getVideoListDataByTime(($page->pageIndex - 1) * $page->pageCount, $page->pageCount, $map);
        foreach ($result as $key => $value) {
            if (empty($value['author'])) {
                $authorInfo = D("ArticleVedio")->getAuthorInfo($value['teacher']);
                $result[$key]['author'] = $authorInfo['author'];
                $result[$key]['logo'] = $authorInfo['logo'];
            }

            if (strlen($value['description']) > 420) {
                $result[$key]['description'] = mb_substr($value['description'], 0, 140, "UTF-8") . "...";
            }
        }

        return ['list' => $result, 'page' => $page->show(), 'num' => $count];
    }

}