<?php

namespace Home\Controller;
use Common\Model\Logic\BaikeSubjectLogicModel;
use Common\Model\Logic\CompanyLogicModel;
use Common\Model\Logic\SpecialkeywordLogicModel;
use Common\Model\Logic\ThematicWordsLogicModel;
use Common\Model\Logic\WwwArticleTagsLogicModel;
use Common\Model\UserModel;
use Home\Common\Controller\HomeBaseController;
class WendaController extends HomeBaseController{

    public function _initialize(){
        parent::_initialize();
        $uri = $_SERVER['REQUEST_URI'];
        preg_match('/html$/',$uri,$m);
        if (count($m) == 0) {
            preg_match('/\/$/',$uri,$m);
            $parse = parse_url($uri);
            if (count($m) == 0  && empty($parse["query"])) {
                header( "HTTP/1.1 301 Moved Permanently");
                header("Location:  ".$this->SCHEME_HOST['scheme_full'] . C("QZ_YUMINGWWW").$uri."/");
            }
        }

       //判断是否是搜索引擎蜘蛛
        $robotIsTrue = B("Common\Behavior\RobotCheck");
        //$robotIsTrue = true; //debug 警告线上应该为注释状态
        if (true === $robotIsTrue) {
            $this->assign('robot',1);
        }
        //header搜索框搜索条件绑定
        $this->assign("header_search",4);

        //添加顶部搜索栏信息
        $this->assign('serch_uri','wenda/search');
        $this->assign('serch_type','装修问答');
        $this->assign('holdercontent','专业解决您所有的装修问题');
        $this->assign('choose_gonglue', 'wenda');
        $headerTmp = "";
        if(empty($this->cityInfo["bm"])){
            $t = T("Home@Index:header");
        }else{
            if(!$robotIsTrue){
                $t = T("Sub@Index:header");
            }
        }
        //导航栏标识
        $this->assign("tabIndex",5);
        //安全验证码
        $safe = getSafeCode();
        $this->assign("safecode",$safe["safecode"]);
        $this->assign("safekey",$safe["safekey"]);
        $this->assign("ssid",$safe["ssid"]);

        $headerTmp = $this->fetch($t);
        $this->assign("headerTmp",$headerTmp);
    }

    //问答首页
    public function index(){
        if (ismobile()) {
            header("Location:  ".$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }

        $category = $this->getCategory();

        $Data = D("Common/Ask");

        //查询用户信息
        if(isset($_SESSION["u_userInfo"])){
            $uid = $_SESSION["u_userInfo"]['id'];
            $user = $Data->getUserByAsk($uid);
            $user['name'] = $_SESSION['u_userInfo']['name'];
            $user['logo'] = $_SESSION['u_userInfo']['logo'];
            //计算积分：积分 = (回答 x 2) + (采纳 x 5) + 被赞数  )
            $user['point'] = ($user['ask_anwsers'] * 2 ) + ($user['ask_adopts'] * 5 ) + $user['ask_agrees'];
            $user['is_login'] = '1';
            $this->assign("user",$user);
        }

        $info = S('Cache:Ask:Home');
        if(empty($info)){
            //帮助人数
            $info['helpPeople'] = str_split($this->getHelpPeople());

            //热门
            $info['hotAsk'] = $Data->getHotQuestion('4');
            //精华
            $info['dist'] = $Data->getOption('ask_dist',true);
            //推荐用户
            $topuser = $Data->getOption('ask_topuser');
            //dump($topuser);
            foreach ($topuser as $k => $v) {
                $userIds .= $v['uid'].',';
            }
            //取推荐的三个用户信息 缓存
            $info['userList'] = $Data->getUsers(rtrim($userIds,','),1);

            //根据用户ID取最近回答
            $userAnwser['one'] = $Data->getNewAnwserByUid($topuser['0']['uid'],'a.post_time DESC');
            $userAnwser['two'] = $Data->getNewAnwserByUid($topuser['1']['uid'],'a.post_time DESC');
            $userAnwser['three'] = $Data->getNewAnwserByUid($topuser['2']['uid'],'a.post_time DESC');

            $info['userAnwser'] = $userAnwser;


            //新增回答 - 包括用户和分类信息
            $info['newAnwser'] = $Data->getNewAnwsers(6);

            //没有回答的问题
            $noAnwserList = $this->getNoAnwsers('6');
            foreach ($noAnwserList as $k => $v){
                $noAnwserList[$k]['stitle'] = mbstr($v['title'],0,15);
            }
            unset($topUser);

            $info['noAnwserList'] = $noAnwserList;
            //用户排行榜
            $info['topUser'] = $Data->getTopUser(6);
            //缓存10分钟
            S('Cache:Ask:Home',$info,600);
        }

        //获取精选问答文章
        $this->getCarefullChoosen();

        $this->assign("userAnwser",$info['userAnwser']);
        $this->assign("noAnwserList",$info['noAnwserList']);
        $this->assign("category",$category);
        $this->assign("newAnwser",$info['newAnwser']);
        $this->assign("userList",$info['userList']);
        $this->assign("topUser",$info['topUser']);
        $this->assign("dist",$info['dist']);
        $this->assign("hotAsk",$info['hotAsk']);
        $this->assign("helpPeople",$info['helpPeople']);


        //获取底部弹层
        $this->assign("zb_bottom_s",$this->fetch(T("Common@Order/zb_bottom_s")));

        //获取报价模版
        $this->assign("order_source",25);
        $this->assign("orderTmp",$this->fetch(T("Common@Order/orderTmp")));
        $this->assign("pub",'/assets/home/ask');
        $this->assign("modurl",'/wenda');
        //获取友情链接
        $friendLink = S("C:FL:A:wenda");
        if (!$friendLink) {
            $friendLink['link'] = D("Friendlink")->getFriendLinkList("000001",1,'wenda');
            //获取热门标签
            $hotTag =D("Friendlink")->getFriendLinkList('000001',3,'wenda');
            $friendLink["hotTags"] = $hotTag;
            S("C:FL:A:wenda", $friendLink, 900);
        }
        if(count($friendLink['link']) > 0){
            $this->assign("friendLink",$friendLink);
        }

        $this->display();
    }

    /**
     * 问答分类
     */
    public function category()
    {
        $id = I('get.id');
        //动态分页跳转到静态分页
        if (isset($_GET['p'])) {
            $temp = intval($_GET['p']);
            if ($temp > 1) {
                $url = $this->SCHEME_HOST['scheme_full'] . 'www.' . C('QZ_YUMING') . '/wenda/ask-' . $id . '/p-' . $temp . '.html';
            } else {
                $url = $this->SCHEME_HOST['scheme_full'] . 'www.' . C('QZ_YUMING') . '/wenda/ask-' . $id . '/';
            }
            header("HTTP/1.1 301 Moved Permanently");
            header("Location:" . $url);
            die();
        }
        $page = max(1, I('get.page'));
        if (empty($id)) {
            $this->_error();
        }
        $url = '/wenda/ask-'.I('get.id').'/';
        //对带有/结尾的进行301跳转
        if (1 == $page) {
            if($url != $_SERVER['REQUEST_URI'] && rtrim($url) == $_SERVER['REQUEST_URI']){
                $redirect = $this->SCHEME_HOST['scheme_full'] .  C('QZ_YUMINGWWW') . $url;
                header( "HTTP/1.1 301 Moved Permanently" );
                header( "Location:".$redirect);
                exit();
            }
        }
        //跳转到手机端
        if (ismobile()) {
            header("Location:  ".$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $url);
            exit();
        }
        //如果分类不为空
        if(!is_numeric($id)){
            $this->_error();
        }
        //取所有分类
        $category = $this->getCategory();
        //取当前分类信息
        $theCategory = $this->getCategory($id);
        //取当前分类的父分类,如果父分类是0，说明本身就是父分类
        $parentId = $theCategory['pid'] == '0' ? $id : $theCategory['pid'];
        //根据父分类取子分类并高亮显示
        $subCategory = $category[$parentId]['sub_cate'];
        foreach ($subCategory as $k => $v){
            if($v['cid'] == $id){
                $subCategory[$k]['cls'] = ' class="active"';
            }
        }
        //取所有父分类
        foreach ($category as $k => $v){
            if($v['cid'] == $parentId){
                $info['category_name'] = $v['name'];
                $category[$k]['cls'] = ' class="active"';
            }
            unset($category[$k]['sub_cate']);
        }
        $info['category_id'] = $theCategory['pid'];
        $info['sub_category_name'] = $theCategory['name'];
        $info['sub_category_id'] = $theCategory['cid'];

        if(empty($info['category_name'])){
           $this->_error();
        }

        $info['SEO_title'] = $theCategory['title'];

        //根据徐交SEO方案更改
        if($info['category_name'] == $info['sub_category_name']){
            $info['SEO_category'] = $info['category_name'];
            $info['SEO_level'] = 1;
        }else{
            $info['SEO_category'] = $info['sub_category_name'];
        }

        $condition['id'] = $id;
        $this->assign("category",$category);
        $this->assign("subCategory",$subCategory);

        //对问题排序进行处理
        $orderBy = intval(cookie('wenda_category_sortby'));
        if($orderBy =='2'){
            $condition['orderBy'] = 'a.anwsers DESC';
            $info['orderBy'] = '<span><a rel="nofollow" class="submit-sort" data-sortby="1" href="javascript:void(0)">按时间排序<i class="ml10 icon-sort"></i></a></span><span><a rel="nofollow" class="submit-sort active" data-sortby="2" href="javascript:void(0)">按回答数排序<i class="ml10 icon-sort"></i></a></span>';
        } else {
            $condition['orderBy'] = 'a.post_time DESC ';
            $info['orderBy'] = '<span><a rel="nofollow" class="submit-sort active" data-sortby="1" href="javascript:void(0)">按时间排序<i class="ml10 icon-sort"></i></a></span><span><a rel="nofollow" class="submit-sort" data-sortby="2" href="javascript:void(0)">按回答数排序<i class="ml10 icon-sort"></i></a></span>';
        }

        //对问题状态进行处理
        $status = intval(cookie('wenda_category_status'));
        //状态不为空
        if ($status =='2'){
            $condition['status'] = '0';
            $info['status_menu'] = '<option value="1">全部</option><option value="2" selected>待解决</option><option value="3">已解决</option>';
        } elseif ($status =='3'){
            $condition['status'] = '1';
            $info['status_menu'] = '<option value="1">全部</option><option value="2" >待解决</option><option value="3" selected>已解决</option>';
        } else {
            $info['status_menu'] = '<option value="1">全部</option><option value="2" >待解决</option><option value="3" >已解决</option>';
        }

        //取问题列表
        $pageIndex = $page;
        $pageCount = 15;
        $pageContent = (1 == $pageIndex) ? '' : "-第".$pageIndex."页";
        //$info['SEO_title'] = $info['category_name'] . $pageContent . "-齐装网装修问答";
        $info['SEO_title'] = $theCategory['title'];//2018-1-2 修改问答TDK
        $info['SEO_keywords'] = $theCategory['keywords'];//2018-1-2 修改问答TDK
        $info['SEO_description'] = $theCategory['description'];//2018-1-2 修改问答TDK

        //获取报价模版
        $this->assign("order_source",25);
        $this->assign("orderTmp",$this->fetch(T("Common@Order/orderTmp")));

        //取问题列表
        $result = $this->getAskList($condition,$pageIndex,$pageCount);
        //dump($result);
        $qList = $result['qList'];

        //设置canonical属性    // feature-917分页不改变
        $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'] . C('QZ_YUMINGWWW') . $url;

        foreach ($qList as $k => $v) {
            $content = htmlToText($v['content']);
            $qList[$k]['content'] = mbstr($content,0,240);
        }

        $info['hotAsk'] = $this->getHotAskByCid($id);
        $this->assign("qList",$qList);
        $this->assign('page',$result['page']);
        $this->assign('info',$info);
        $this->assign("modurl",'/wenda');
        $this->assign("pub",'/assets/home/ask');
        $this->display($template);
    }

    //问题列表页
    public function questionList(){

        //将 /wenda/ask-0 定位404
        if('/wenda/ask-0' == $_SERVER['REQUEST_URI'] || '/wenda/ask-0/' == $_SERVER['REQUEST_URI']){
            $this->_error();
        }

        $url = '/wenda/ask-'.I('get.id');
        //对带有/结尾的进行301跳转
        if($url == $_SERVER['REQUEST_URI']){
            $redirect = $this->SCHEME_HOST['scheme_full'] .C('QZ_YUMINGWWW').'/wenda/ask-'.I('get.id')."/";
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:".$redirect);
            die();
        }

        //跳转到手机端
        if (ismobile()) {
            $mobile = '/^\/wenda\/ask-(\d+)$/';
            if (preg_match($mobile, $_SERVER['REQUEST_URI']) > 0) {
                header("Location:  ".$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
                exit();
            }
        }

		$cateId = I('get.id');
        $url = I('get.');
        //设定一个默认模板
        $template = 'category';
        //如果分类不为空
        if(!empty($cateId)){
            if(!is_numeric($cateId)){
                $this->_error();
            }
            //取所有分类
            $category = $this->getCategory();
            //取当前分类信息
            $theCategory = $this->getCategory($cateId);
            //取当前分类的父分类,如果父分类是0，说明本身就是父分类
            $parentId = $theCategory['pid'] == '0' ? $cateId : $theCategory['pid'];
            //根据父分类取子分类并高亮显示
            $subCategory = $category[$parentId]['sub_cate'];
            foreach ($subCategory as $k => $v){
                if($v['cid'] == $cateId){
                    $subCategory[$k]['cls'] = ' class="active"';
                }
            }
            //取所有父分类
            foreach ($category as $k => $v){
                if($v['cid'] == $parentId){
                    $info['category_name'] = $v['name'];
                    $category[$k]['cls'] = ' class="active"';
                }
                unset($category[$k]['sub_cate']);
            }
            $info['category_id'] = $theCategory['pid'];
            $info['sub_category_name'] = $theCategory['name'];
            $info['sub_category_id'] = $theCategory['cid'];

            if(empty($info['category_name'])){
               $this->_error();
            }

            $info['SEO_title'] = $theCategory['title'];

            //根据徐交SEO方案更改
            if($info['category_name'] == $info['sub_category_name']){
                $info['SEO_category'] = $info['category_name'];
                $info['SEO_level'] = 1;
            }else{
                $info['SEO_category'] = $info['sub_category_name'];
            }

            $condition['cateId'] = $cateId;
            $this->assign("category",$category);
            $this->assign("subCategory",$subCategory);
            $template = 'category';
        }

        //如果关键字不为空
        $keyword = I('get.keyword');
        if(!empty($keyword) || isset($url['keyword'])){
            if(!checkKeyword($keyword)){
                $this->_error();
            }
            $condition['keyword'] = $keyword;
            $info['title'] = $keyword;
            $info['position'] = '“'.$keyword.'”的搜索结果';
            $template = 'list';
        }

        //取问题列表
        $pageIndex = 1;
        $pageCount = 15;
        $pageid = I('get.p');

        if(!empty($pageid)){
            if(!is_numeric($pageid)){
                $this->_error();
            }
            $pageIndex = intval($pageid);
            $pageContent ="第".$pageIndex."页";
            $info['SEO_title'] = $info['category_name']."-第".$pageIndex."页-齐装网装修问答";
        }

        //如果动作不为空
        $action = I('get.action');
        //如果请求为：热门，新增，无人，隐藏筛选条件
        if(!empty($action)){
            if($action == 'dist'){
                $condition['orderBy'] = 'a.post_time DESC';
                $condition['dist'] = array("EQ",1);
                $info['title'] = '装修精华问题_装修问题解决方案-齐装网-第'.$pageIndex.'页';
                $info['keywords'] = '装修精华问答,装修问题解决方案,装修攻略';
                $info['description'] = '齐装网装修精华问题平台为业主搜集家庭室内装修以及公共装修过程中主要遇到的各种精华问题，为业主提供即时便捷及专业的装修问题解决方案。';
                $info['position'] = '精华问题';
            }
            if($action == 'hot'){
                $condition['orderBy'] = 'a.anwsers DESC';
                $condition['anwsers'] = array("GT",1);
                $info['title'] = '装修热门回答_装修前必看常见问题-齐装网-第'.$pageIndex.'页';
                $info['keywords'] = '装修热门话题,装修常见问题,装修前必看';
                $info['description'] = '齐装网装修热门问答平台为业主汇总家庭室内装修以及公共装修过程中经常遇到的各种热门问题及解决办法，建议业主装修前必看。';
                $info['position'] = '热门回答';
            }
            if($action == 'new'){
                $condition['orderBy'] = 'a.post_time DESC';
                $info['title'] = '新提问';
                $info['position'] = '新提问';
            }
            if($action == 'unanswer'){
                $condition['adopt_time'] = array("EQ",0);
                $condition['orderBy'] = 'a.post_time DESC';
                $info['title'] = '装修注意问题等你来回答_分享装修经验-齐装网-第'.$pageIndex.'页';
                $info['keywords'] = '装修提醒,装修注意问题,装修温馨提示';
                $info['description'] = '齐装网装修问答平台为业主之间提供沟通互助桥梁，希望你主动分享家庭室内装修以及公共装修宝贵经验。';
                $info['position'] = '等您来回答';
            }

            $template = 'list';

            if($action == 'newanswer'){
                $condition['anwsers'] = array("NEQ",0);
                $condition['orderBy'] = 'a.last_time DESC';
                $info['title'] = '装修新回答_装修回答汇总-齐装网-第'.$pageIndex.'页';
                $info['keywords'] = '装修知识问答,装修问答大全,装修技巧';
                $info['description'] = '齐装网装修全新问答平台为业主提供近阶段家庭室内装修以及公共装修过程中遇到的各种问题及解决办法。为业主提供即时便捷及专业的装修问题解决方法。';
                $template = 'newanswer';
                $info['position'] = '新回答';
            }


        }else{
            //对问题排序进行处理
            $orderBy = I('get.sort');
            if(!empty($orderBy) and $orderBy =='anwsers'){
                $condition['orderBy'] = 'a.anwsers DESC';
                $info['orderBy'] = '<span><a rel="nofollow" href="'.$this->url('orderby','anwsers','').'">按时间排序<i class="ml10 icon-sort"></i></a></span>
                <span><a rel="nofollow" class="active" href="'.$this->url('orderby','anwsers','anwsers').'">按回答数排序<i class="ml10 icon-sort"></i></a></span>';
            }else{
                $condition['orderBy'] = 'a.post_time DESC ';
                $info['orderBy'] = '<span><a rel="nofollow" class="active" href="'.$this->url('orderby','time','').'">按时间排序<i class="ml10 icon-sort"></i></a></span>
                <span><a rel="nofollow" href="'.$this->url('orderby','time','anwsers').'">按回答数排序<i class="ml10 icon-sort"></i></a></span>';
            }
            //对问题状态进行处理
            $status = I('get.status');

            //状态不为空
            if(is_numeric($status) && isset($status) && $status <= 3 && $status >= 0){
                if ($status =='1'){
                    $condition['status'] = '0';
                    $info['status_menu'] = '<option value="0">全部</option><option value="1" selected>待解决</option><option value="2">已解决</option>';
                } elseif ($status =='2'){
                    $condition['status'] = '1';
                    $info['status_menu'] = '<option value="0">全部</option><option value="1" >待解决</option><option value="2"  selected>已解决</option>';
                } else {
                    $info['status_menu'] = '<option value="0">全部</option><option value="1" >待解决</option><option value="2" >已解决</option>';
                }
            }else{
                if (!empty($status)) {
                    $this->_error();
                }else{
                    $info['status_menu'] = '<option value="0" selected>全部</option><option value="1">待解决</option><option value="2">已解决</option>';
                }
            }
        }

        //如果执行搜索，并且关键字为空
        if(isset($url['keyword']) && empty($keyword)){
            $info['title'] = '搜索问答';
            $info['position'] = '请输入您要搜索的关键字';
            $this->assign('info',$info);
            $this->display('search');
            die();
        }

        //获取报价模版
        $this->assign("order_source",25);
        $this->assign("orderTmp",$this->fetch(T("Common@Order/orderTmp")));

        //取问题列表
        $result = $this->getQList($condition,$pageIndex,$pageCount);
        //dump($result);
        $qList = $result['qList'];

        if(!empty($keyword)){
            if(!empty($qList)){
                foreach ($qList as $k => $v) {
                    $qList[$k]['title'] = $this->highlightWords($v['title'],$keyword);
                }
            }else{
                $template = 'search';
            }
        }

        //设置canonical属性
        if(!empty($action)){
            if(I('get.p') == '' || I('get.p') == 1){
                switch ($action) {
                    case 'dist':
                        $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'] . C('QZ_YUMINGWWW').'/wenda/dist';
                        break;
                    case 'newanswer':
                        $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'] . C('QZ_YUMINGWWW').'/wenda/newanswer';
                        break;
                    case 'unanswer':
                        $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'] .C('QZ_YUMINGWWW').'/wenda/unanswer';
                        break;
                    case 'hot':
                        $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'] .C('QZ_YUMINGWWW').'/wenda/hot';
                        break;
                    case 'new':
                        $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'] .C('QZ_YUMINGWWW').'/wenda/new';
                        break;
                    default:
                        break;
                }
            }
        }elseif('search' != $template && !isset($_GET['sort']) && !isset($_GET['status'])){
            if(I('get.p') == '' || I('get.p') == 1){
                $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'] .C('QZ_YUMINGWWW').'/wenda/ask-'.$cateId;
            }else{
                $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'] .C('QZ_YUMINGWWW').'/wenda/ask-'.$cateId.'?p='.I('get.p');
            }
        }


        $info['hotAsk'] = $this->getHotAskByCid($cateId);
        //dump($info['hotAsk']);
        foreach ($qList as $k => $v) {
            $content = htmlToText($v['content']);
            $qList[$k]['content'] = mbstr($content,0,240);
        }

        $this->assign("keyword",$keyword);
        $this->assign("qList",$qList);
        $this->assign('page',$result['page']);
        $this->assign('info',$info);
        $this->assign("modurl",'/wenda');
        $this->assign("pub",'/assets/home/ask');
        $this->display($template);
    }

    //问题查看页
    public function question(){
        //跳转到手机端
        if (ismobile()) {
            header("Location:  ".$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }

        $qid = I('get.id');
        if(empty($qid) || !is_numeric($qid)){
            $this->_error();
        }
        if(isset($_SESSION["u_userInfo"])){
           $uid = $_SESSION["u_userInfo"]['id'];
        }
        $Data = D("Common/Ask");
        $User = new UserModel();
        $ask = S('Cache:Home:Wenda:question:'.$qid);
        if(!$ask){
            $ask = $Data->getAskById($qid);
            if(empty($ask)){
                $this->_error();
            }
            S('Cache:Home:Wenda:question:'.$qid,$ask,900);
        }

        $ask['name'] = empty($ask['jc']) ? $ask['name'] : $ask['jc'];

        //取问题图片列表
        $qImg = S('Cache:Home:Wenda:question:img:'.$qid);
        if(!$qImg){
            $qImg = $Data->getQuestionImg($qid);
            S('Cache:Home:Wenda:question:img:'.$qid,$qImg,900);
        }

        //取一级和二级分类
        $categorys = $this->getCategory();
        $category = $categorys[$ask['cid']];
        unset($categorys);
        $ask['category_name'] = $category['name'];
        foreach ($category['sub_cate'] as $key => $value) {
            if($value['cid'] == $ask['sub_category']){
                $ask['sub_category_name'] = $value['name'];
                break;
            }
        }

        //根据问题ID取所有评论  ! 逻辑要放在 最佳答案和所有答案之前
        $commentList = $Data->getCommentList($qid,'c.post_time DESC');
        foreach ($commentList as $k => $v) {
            $v['post_time'] = timeFormat($v['post_time']);
            $newCommentList[$v['aid']][] = $v;
        }

        //根据问题ID取所有答案图片
        $tempImgList = $Data->getAnwserImg($qid);
        foreach ($tempImgList as $k => $v) {
            $imgList[$v['fid']][] = $v;
        }
        unset($tempImgList);

        //用户登录后处理
        if(!empty($uid)){
            //判断当前登录用户是否已回答
            $isAnwser = $Data->isAnwserByQid($qid,$uid);
            $ask['is_anwser'] = $isAnwser >= 1 ? '0' : '1';
            //如果此问题是当前登录用户
            if($ask['uid'] == $uid){
                //问题状态为未采纳，可以修改问题描述
                if($ask['status'] == '0'){
                    $isAdopt = true;
                    //是自己的提问，发布时间小于一小时，且没有人回答，可以修改
                    if( (time() <= ($ask['post_time'] + 3600 )) && $ask['anwsers'] <= '0'){
                        $ask['is_modify'] = '1';
                        $ask['content'] = htmlspecialchars(strip_tags($ask['content']));
                        //过滤敏感词
                        import('Library.Org.Util.Fiftercontact');
                        $filter = new \Fiftercontact();
                        $ask['content'] = $filter->filter_common($ask['content'],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_link","filter_html_url"));
                    }else{
                        $ask['modify_note'] = '提示：问题发布时间超过一小时，不允许修改。';
                    }
                }
                //当前登录用户不可以回答自己的提问
                $ask['is_anwser'] = '0';
            }
            //根据问题ID和用户ID取所有赞列表
            $agreeList = $Data->getAgreeList($qid,$uid);
        }else{
            $ask['is_anwser'] = '1';
        }
        //取最佳答案
        //此处不应查询数据库，应根据QID取所有答案后再处理
        if($ask['best_aid'] !== null){
            $bestAnwser = $Data->getAnwserById($ask['best_aid']);
            if(empty($bestAnwser)){

            }else{
                $bestAnwser['agree'] = $bestAnwser['agree'] ? $bestAnwser['agree'] : 0;
                $bestAnwser['jc'] = '';
                //如果最佳答案没有被删除
                if(!empty($bestAnwser)){
                    $bestAnwser['post_time'] = timeFormat($bestAnwser['post_time']);
                    if(!empty($agreeList[$bestAnwser['id']])){
                        $bestAnwser['is_agree'] = '<span class="red"><i class="icon-heart"></i> 已赞（'.$bestAnwser['agree'].'）</span>';
                    }else{
                        $bestAnwser['is_agree'] = '<span class="agree_act" id="agree_id_'.$bestAnwser['id'].'"><i class="icon-heart "></i> 赞（<span>'.$bestAnwser['agree'].'</span>）</span>';
                    }
                    $bestAnwser['name'] = empty($bestAnwser['jc']) ? $bestAnwser['name'] : $bestAnwser['jc'];
                    $bestAnwser['url'] = $bestAnwser['classid'] == '3' ? $this->SCHEME_HOST['scheme_full'].$bestAnwser['bm'].'.'. C('QZ_YUMING') .'/company_home/'.$bestAnwser['uid'].'/' : 'javascript:;';
                    //取图片列表
                    $bestAnwser['img'] = $imgList[$bestAnwser['id']];
                    $this->assign("bestAnwserComments",$newCommentList[$bestAnwser['id']]);
                    $this->assign("bestAnwser",$bestAnwser);
                }
            }
        }

        //取答案 - 排除最佳答案
        $anwserList = $Data->getAnwserList($qid,'a.post_time DESC',$ask['best_aid']);
        $idlist = [];
        foreach ($anwserList as $k => $v) {
            $anwserList[$k]['post_time'] = timeFormat($v['post_time']);
            $anwserList[$k]['comment_list'] = $newCommentList[$v['id']];
            $anwserList[$k]['comments'] = count($anwserList[$k]['comment_list']);
            if(!empty($agreeList[$v['id']])){
                $anwserList[$k]['is_agree'] = '<span class="red"><i class="icon-heart"></i> 已赞（'.$v['agree'].'）</span>';
            }else{
                $anwserList[$k]['is_agree'] = '<span class="agree_act" id="agree_id_'.$v['id'].'"><i class="icon-heart"></i> 赞（<span>'.$v['agree'].'</span>）</span>';
            }
            $anwserList[$k]['adopt_btn'] = $isAdopt  ? '<a class="caina adopt_act" id="anwser_id_'.$v['id'].'" href="#"><i class="icon-ok mr10"></i>采纳TA</a>' : '';
            //修改按钮
            $anwserList[$k]['edit_btn'] = $isAdopt  ? '<a class="caina adopt_act" id="anwser_id_'.$v['id'].'" href="#"><i class="icon-ok mr10"></i>采纳TA</a>' : '';
            $anwserList[$k]['img'] = $imgList[$v['id']];
            $anwserList[$k]['name'] =$v['name'] ? $v['name'] : '';
            $anwserList[$k]['url'] = $v['classid' ] == '3' ? $this->SCHEME_HOST['scheme_full'] .$v['bm'].'.'. C('QZ_YUMING') .'/company_home/'.$v['uid'].'/' : 'javascript:;';
            $anwserList[$k]['logo'] = $v['logo'] ? changeImgUrl($v['logo'],2) : '';
            $idlist[] = $v['old_userid'];
        }
        if(!empty($idlist)){
            $userlist = $User->getUserinfoListByIds($idlist);
            foreach ($anwserList as $key => $val) {
                if(empty($val['name']) && empty($val['logo'])){
                    foreach ($userlist as $k => $v){
                        if($v['id'] == $val['old_userid']){
                            $anwserList[$key]['logo'] = $v['logo'] ? $v['logo'] : '';
                            $anwserList[$key]['name'] = $v['name'] ? $v['name'] : '';
                            break;
                        }
                    }
                }
            }
        }


        //总答案数
        $ask['anwsers'] = $ask['best_aid'] == null ? count($anwserList) : count($anwserList);

        if(empty($ask['description'])){
            $ask['description'] = mbstr($ask['content'],0,120);
        }
        $ask['post_time'] = date('Y-m-d H:i:s',$ask['post_time']);
        $ask['anwserCount'] = count($anwserList);
        $ask['modify_time'] = empty($ask['modify_time']) ? '' : '<li>最后修改：'.date('Y-m-d H:i:s',$ask['modify_time']).'</li>';
        $ask['is_login'] = empty($uid) ? 'needlogin' : '';
        //de($ask);

        //相关问题
        $relatedAnwser = D('Ask')->getRelationQuestion($ask['tags'],$ask['keywords'],$ask['sub_category'],$ask['cid'],6,$ask['id']);

        //大家都在问
        $anLeastOneAnswerAsk = S('Wenda:question:anLeastOneAnswerAsk:' . $qid);
        if (empty($anLeastOneAnswerAsk)) {
            $map = array(
                'anwsers' => array('EGT', 1)
            );
            $anLeastOneAnswerAsk = D('Ask')->getQuestionListRand($map, 10);
            S('Wenda:question:anLeastOneAnswerAsk:' . $qid, $anLeastOneAnswerAsk , 7200);
        }
        $this->assign("anLeastOneAnswerAsk",$anLeastOneAnswerAsk);

        // 新增最新问答  feature-917
        $newestAnswer = S('Wenda:question:newestAnswerAsk:' . $qid);
        if (empty($newestAnswer)) {
            $newestAnswer = $this->getNewestAsk(10);
            S('Wenda:question:newestAnswerAsk:' . $qid, $newestAnswer, 7200);
        }
        $this->assign("newestAnswerAsk", $newestAnswer);

        //增加查看数
        $Data->updateViews($qid);


        //获取报价模版
        $this->assign("order_source",12);
        $t = T("Common@Order/orderTmp");
        $orderTmp = $this->fetch($t);
        $this->assign("orderTmp",$orderTmp);

        //获取黄历报价模版
        $this->assign("hlBaoJia",$this->fetch(T("Common@Order/hlBaoJia")));

        //免费通话模版
        $this->assign("freetel",$this->fetch(T("Common@Zbfb/freetel")));

        //设置canonical属性
        $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'].C('QZ_YUMINGWWW').'/wenda/x'.$qid.'.html';

        //dump($ask['sub_category']);
        $info['hotAsk'] = $this->getHotAskByCid($ask['sub_category']);
        if(!empty($ask['city_id'])){
            $bm_tmp = D("Common/Quyu")->getBm($ask['city_id']);
            if(!empty($bm_tmp)){
                $ask['bm'] = $bm_tmp['bm'];
            }
        }

        //获取标题最新三条关键词
        $keywords = S('Cache:Wenda:Q:k:'.$qid);
        if(!$keywords){
            //获取先关标签
            $thematicLogic = new ThematicWordsLogicModel();
            $keywords = $thematicLogic->getContentLabels($qid,3,5);
            S('Cache:Wenda:Q:k:'.$qid,$keywords,900);
        }

        $this->assign("keywords",$keywords);

        //获取底部标签组
        $tags = S("Cache:Home:Wenda:Question:tags");
        if(!$tags){
            $wwwtaglogic = new WwwArticleTagsLogicModel();
            $tags = $wwwtaglogic->getTags(5);
            S("Cache:Home:Wenda:Question:tags",$tags,900);
        }

        // feature-917  4.详情页头部属性keywords和description内容为空。当无keywords和description时，直接调用标题为keywords、description
        $ask['keywords'] = $ask['keywords'] ? $ask['keywords'] : $ask['title'];
        $ask['description'] = $ask['description'] ? $ask['description'] : $ask['title'];

        //获取右侧推荐装修公司
        $cs = $this->cityInfo['id'] ?: '';
        $info['recommend_company_right'] = S('Www:Company:RecommendRight' . $cs);
        if (!$info['recommend_company_right']) {
            $companyLogic = new CompanyLogicModel();
            $info['recommend_company_right'] = $companyLogic->getRecommendCompanyModule($cs);
            S('Www:Company:RecommendRight' . $cs, $info['recommend_company_right'], 900);
        }
        //获取底部推荐装修公司
        $info['recommend_company_bottom'] = S('Www:Company:RecommendBottom' . $cs);
        if (!$info['recommend_company_bottom']) {
            $companyLogic = new CompanyLogicModel();
            $info['recommend_company_bottom'] = $companyLogic->getRecommendCompanyByCaseNum($cs, 3, array_column($info['recommend_company_right'], 'id'));
            S('Www:Company:RecommendBottom' . $cs, $info['recommend_company_bottom'], 900);
        }

        // 1353百度官方号需求
        $baidu['url'] = $this->SCHEME_HOST['scheme_full'].$this->SCHEME_HOST['host'].$_SERVER['REQUEST_URI'];
        $baidu['str'] = mb_substr(strip_tags($ask['description']), 0, 100, 'utf-8');
        $baidu['title'] = $ask["title"];
        if(empty($ask['last_time'])){
            $optimee =date("Y-m-d",$ask['create_time']);
            $optimes =date("H:i:s",$ask['create_time']);
        }else{
            $optimee =date("Y-m-d",$ask['last_time']);
            $optimes =date("H:i:s",$ask['last_time']);
        }
        $baidu['optime'] = $optimee."T".$optimes;
        $baidu['addtime'] = date("Y-m-d",$ask["create_time"])."T".date("H:i:s",$info["create_time"]);
        $this->assign("baidu",$baidu);

        $this->assign("tags",$tags);
        $this->assign("qImg",$qImg);
        $this->assign("category",$category);
        $this->assign("ask",$ask);
        $this->assign("anwserList",$anwserList);
        $this->assign("relatedAnwser",$relatedAnwser);
        $this->assign('info',$info);
        $this->display();
    }

    //问答发布页
    public function addquestion(){
    	//判断是否登录
        //判断是否被封禁
        isBlocked(false);

        //快速发贴处理
        $data = I('post.');
        if(!empty($data['quickask'])){
            $title = !empty($data['title']) ? $data['title'] : '';
        }

        //取用户城市和区域
        $userCityId = $_SESSION["u_userInfo"]['cs'];

        $Db = D("Common/Ask");
        //获取省份列表
        $provinceList = $Db->getAreaList();
        //根据城市ID取省份ID
        $myProvinceId = $Db->getProvinceIdByCityId($userCityId);
        //根据用户城市输出省份信息
        foreach ($provinceList as $k => $v) {
            if($v['qz_provinceid'] == $myProvinceId){
                $provinceList[$k]['selected'] = 'selected';
                break;
            }
        }

        //获取所有城市列表
        $allCityList = $Db->getCityList();
        //取用户城市
        $cityList = $allCityList[$myProvinceId];
        unset($allCityList);
        //根据用户城市输出区域信息
        foreach ($cityList as $k => $v) {
            if($v['cityid'] == $userCityId){
                 $cityList[$k]['selected'] = 'selected';
            }
        }

        //取所有分类
        $category = $this->getCategory('',true);

        //提交了表单 并且不是 快速发贴
        if(IS_POST && empty($data['quickask'])){
            empty($data['area_id']) && die('地区不能为空！');
            empty($data['city_id']) && die('城市不能为空！');
            empty($data['cid']) && die('分类不能为空！');
            empty($data['sub_category']) && die('子分类不能为空！');

            //验证码模块，需要时取消注释

            $check = S("Cache:Geetest:".session_id());
            if(!$check){
                $this->ajaxReturn(array("data"=>"","info"=>"验证码输入错误！","status"=>0));
                die();
            }

            S("Cache:Geetest:".session_id(),null);
            $isUnique = $Db->isUnique($data['title'],$_SESSION["u_userInfo"]['id']);
            if(!empty($isUnique)){
                $this->ajaxReturn(array("data"=>"","info"=>"您已经提问过该问题了哦！","status"=>0));
                die();
            }
            // $badwords_array

            import('Library.Org.Util.Fiftercontact');
            $filter = new \Fiftercontact();
            $data['content'] = $filter->filter_recomment($data['content']);
            //关键词过滤
            $filter = new \Fiftercontact();
            $data['content'] = $filter->filter_common($data['content'],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_link","filter_url"));
            $data['title'] = $filter->filter_common($data['title'],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_link","filter_url"));

            import('Library.Org.Util.App');
            $app = new \App();
            $ip = $app->get_client_ip();

            if(strstr($data['content'],'%$^*&') != ''){
                header("HTTP/1.1 404 Not Found");
                header("Status: 404 Not Found");
                die();
                $data['visible'] = '1';
                $data['title'] = $data['title'].$ip;
                $data['content'] = $data['content'].$_SERVER['HTTP_USER_AGENT'].'               '.$_SERVER["HTTP_REFERER"];
            }

            //发布问答时默认设为通过审核
            $data['visible'] = '1';
            $data['review'] = '0';

            //取省份
            foreach ($provinceList as $k => $v) {
                if($v['qz_provinceid'] == $data['area_id']){
                    $data['area'] = $v['qz_province'];
                    break;
                }
            }
            //取城市
            $cityList = $Db->getCityList();
            foreach ($cityList[$data['area_id']] as $k => $v) {
                if($v['cityid'] == $data['city_id']){
                    $data['city'] = $v['city'];
                    break;
                }
            }

            $images = $_POST['imgId'];
            if(count($images) > 3 ){
                $this->ajaxReturn(array("data"=>"","info"=>"图片最多只能上传3张","status"=>0));
                die();
            }

            $data['uid'] = $_SESSION["u_userInfo"]['id'];
            $data['username'] = $_SESSION["u_userInfo"]['name'];
            $data['post_time'] = time();
            $data['create_time'] = time();
            //问答来源
            $data['source'] = 1;
            if(mb_strlen($data['content'],'utf-8') > 1501){
                $this->ajaxReturn(array("data"=>"","info"=>"内容最多输入1500个字","status"=>0));
                die();
            }
            //如果内容不为空，从内容中取200个字为描述
            if(!empty($data['content'])){
                $data['description'] = mbstr(htmlspecialchars(strip_tags($data['content'])),0,200);
            }
            $data['content'] = nl2br($data['content']);

            $Ask = D("Ask");
            //查询发布间隔时间
            $result = $Ask->checkRate(session("u_userInfo.id"));
            if (count($result) > 0) {
                $offset = floor((time() - $result["post_time"])%86400/60);
                if ($offset <= 5) {
                    $this->ajaxReturn(array("data"=>"$id","info"=>"您的操作过于频繁，请休息5分钟后再试！","status"=>0));
                    exit();
                }
            }

            if (!$Ask->create($data,1)){
                $this->ajaxReturn(array("info"=>$Ask->getError(),"status"=>0));
            }else{
                $result = $Ask->addQuestion($data);
                if(!empty($images)){
                    foreach ($images as $k => $v) {
                        $Ask->addUploadImage($result,$result,'1',$v);
                    }
                    $is_edit = true;
                }
                $this->ajaxReturn(array("data"=>$result,"info"=>"发布成功","status"=>1));
            }

            exit();
        }

        $act = I('get.act');

        if($act == 'verify'){
            //显示验证码
            getVerify("",4,130,35);
            exit();
        }

   		//根据 province ID 输出城市列表 Select # from GET request url
   	    $cityid = intval(I('get.getcity'));
        if(!empty($cityid)){
        	$cityList = D("Common/Ask")->getCityList();
        	$theCity = $cityList[$cityid];
        	unset($cityList);
        	if(!empty($theCity)){
        		$rt = '';
				foreach ($theCity as $k => $v) {
					$rt .= '<option value="'.$v["cityid"].'">'.$v["city"].'</option>';
				}
				exit($rt);
			}
			exit();
        }

        //根据 category ID 输出子分类列表 Select # from GET request url
   	    $subCategoryId = intval(I('get.getsubcate'));
   	    if(!empty($subCategoryId)){
   	    	$theCategory = $category[$subCategoryId]['sub_cate'];
   	    	//dump($theCategory);
   	    	if(!empty($theCategory)){
				$rt = '';
				foreach ($theCategory as $k => $v) {
					$rt .= '<option value="'.$v["cid"].'">'.$v["name"].'</option>';
				}
				exit($rt);
			}
   	    	exit();
   	    }

        $this->assign("asktitle",$title);
      	$this->assign("modurl",'/wenda');
        $this->assign("pub",'/assets/home/ask');
        $this->assign("category",$category);
        $this->assign("provinces",$provinceList);
        $this->assign("citys",$cityList);
        $this->display('add');
    }

    //添加答案
    public function addAnwser(){
        //判断是否登录
        if(!isset($_SESSION["u_userInfo"])){
           die('login');
        }

        //判断是否被封禁
        if(isBlocked() == false){
            exit('您的帐号已被系统封禁！');
        }


        //如果用户已回答过不能再回答
        if(IS_POST){
            $verify = S("Cache:Geetest:".session_id());
            if($verify == 1){
                S("Cache:Geetest:".session_id(),null);
                $tempData = I('post.');
                if(empty($tempData['id']) || !is_numeric($tempData['id'])){
                    exit('数据错误');
                }
                if(empty($tempData['contents'])){
                    exit('必须要填写内容哦。');
                }

                $Ask = D("Common/Ask");

                //查询当前问题信息
                $question = $Ask->getAskByid($tempData['id']);

                //问题所属用户是否是当前操作用户
                if($question['uid'] == $_SESSION["u_userInfo"]['id']){
                    $id = $tempData['id'];
                    $data['content'] = nl2br($tempData['contents']);
                    $uid = $_SESSION["u_userInfo"]['id'];
                    $data['modify_time'] = time();

                    if (!$Ask->edtiQuestion($id,$uid,$data)){
                        die('系统异常！');
                    }else{
                        die('true');
                    }
                }

                //查询回答问题的时间间隔
                $result = $Ask->checkAnswerRate(session("u_userInfo.id"));
                if (count($result) > 0) {
                    $offset = floor((time() - $result["post_time"])%86400/60);
                    if ($offset <= 2) {
                       die("您的操作过于频繁，请休息2分钟后再试！");
                    }
                }

                $data['qid'] = $tempData['id'];
                $data['uid'] = $_SESSION["u_userInfo"]['id'];
                $data['post_time'] = time();
                $data['create_time'] = time();
                $data['content'] = nl2br($tempData['contents']);
                if(mb_strlen($data['content'],'utf-8') > 1501){
                    die('答案最多输入1500个字');
                }

                //过滤敏感词
                import('Library.Org.Util.Fiftercontact');
                $filter = new \Fiftercontact();
                $data['content'] = $filter->filter_common($data['content'],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_link","filter_url","filter_html_url"));

                if (!$Ask->addAnwser($data)){
                    die('系统异常！');
                }else{
                    //todo默认不显示，不做条目更改
                    //更新最后回答时间
                    M("ask")->where(array('id' => $data['qid']))->setInc(array('last_time'=>time()));
                    M("ask")->where(array('id' => $data['qid']))->setInc('anwsers');
                    M("user")->where(array('id' =>$data['uid']))->setInc('ask_anwsers');
                    die('true');
                }
                exit();
            }else{
                exit("验证失败！");
            }
        }
    }

    //添加回复
    public function addComment(){
        //判断是否登录
        if(!isset($_SESSION["u_userInfo"])){
           die('login');
        }

        if($_SESSION['u_userInfo']['classid'] != 1){
            exit('账号无回复权限');
        }

        //判断是否被封禁
        if(isBlocked() == false){
            exit('您的帐号已被系统封禁！');
        }

        if(IS_POST){
            $verify = S("Cache:Geetest:".session_id());
            if($verify == 1){
                S("Cache:Geetest:".session_id(),null);
                $tempData = I('post.');
                $id = $tempData['id'];

                if(empty($id) || !is_numeric($id)){
                    exit('数据错误');
                }
                if(empty($tempData['contents'])){
                    exit('必须要填写回复内容哦。');
                }

                $Ask = D("Common/Ask");

                $anwser = $Ask->getAnwser($id);
                //print_r($anwser);

                //答案为空，操作为回复评论，否则操作为回复答案
                if(empty($anwser)){
                    //回复评论
                    $comment = $Ask->getComment($id);
                    print_r($comment);
                    //$data['parent_id'] = '';
                }
                $data['qid'] = $anwser['qid'];
                $data['aid'] = $tempData['id'];
                $data['uid'] = $_SESSION["u_userInfo"]['id'];
                $data['post_time'] = time();
                $data['content'] = nl2br($tempData['contents']);
                if(mb_strlen($data['content'],'utf-8') > 501){
                    die('回复最多输入500个字');
                }
     /*           import('Library.Org.Util.Fiftercontact');
                $filter = new \Fiftercontact();
                //内容处理
                $data['content'] = $filter->fifter_contact($data["content"]);*/

                $data['useragent'] = $_SERVER['HTTP_USER_AGENT'];
                $data['ip'] = get_client_ip('0',true);
                //过滤敏感词
                import('Library.Org.Util.Fiftercontact');
                $filter = new \Fiftercontact();
                $data['content'] = $filter->filter_common($data['content'],array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_link","filter_html_url"));

                if (!$Ask->addComment($data)){
                    die('系统异常！');
                }else{
                    //增加回复数
                    M("ask_anwser")->where(array('id' => $data['aid']))->setInc('comments');
                    die('true');
                }
                exit();
            }else{
                exit("验证失败！");
            }
        }
    }

    //快速发布提问 - ( Ajax By Home/index )
    public function quickask(){

        //判断是否被封禁
        if(isBlocked() == false){
            $this->ajaxReturn(array("data"=>"","info"=>"您的帐号已被系统封禁！","status"=>0));
        }

        //提交了表单 并且不是 快速发贴
        if(IS_POST){
            //判断是否登录
            if(isset($_SESSION["u_userInfo"])){
                //取用户城市和区域
                $userCityId = $_SESSION["u_userInfo"]['cs'];
                $data['uid'] = $_SESSION["u_userInfo"]['id'];
                $data['username'] = $_SESSION["u_userInfo"]['name'];
            }

            $data['content'] = htmlToText($_POST['content']);
            $data['phone'] = htmlToText($_POST['tel']);
            $data['post_time'] = time();
            $data['city_id'] = session('cityId');
            $data['city_name'] = cookie('w_cityid');

            empty($data['phone']) && die('手机号不能为空');
            empty($data['content']) && die('城市不能为空！');

            if(mb_strlen($data['content'],'utf-8') > 55){
                $this->ajaxReturn(array("data"=>"","info"=>"内容最多输入50个字","status"=>0));
                die();
            }

            $data['content'] = nl2br($data['content']);

            if (D("Common/Ask")->addQuickWenda($data)){
                $this->ajaxReturn(array("data"=>"","info"=>"成功","status"=>1));
            }else{
                $this->ajaxReturn(array("data"=>"","info"=>"失败","status"=>0));
            }
            exit();
        }
        $this->_error();

    }

    //问答动作 - Ajax请求
    public function askAction (){

        //判断是否被封禁
        if(isBlocked() == false){
            exit('您的帐号已被系统封禁！');
        }

        if(IS_POST){
            $tempData = I('post.');
            $action = $tempData['act'];
            $id = str_replace('anwser_id_','',$tempData['id']);
            $id = trim(str_replace('agree_id_','',$id));
            if(empty($id) || !is_numeric($id) || empty($action)){
                exit('数据错误s');
            }

            $Ask = D("Common/Ask");
            $anwser = $Ask->getAnwser($id);
             //答案为空说明不存在
            if(empty($anwser)){
               exit('不存在这个答案！');
            }
            $qid = $anwser['qid'];

            //采纳操作
            if($action == 'adopt'){
                //判断是否登录
                if(!isset($_SESSION["u_userInfo"])){
                   die('login');
                }
                //查询这个问题所有权是否是当前操作用户
                $question = $Ask->getAskByid($qid);
                if($question['uid'] != $_SESSION["u_userInfo"]['id']){
                    exit('您无权限操作!');
                }
                $data['adopt_time'] = time();
                $data['id'] = $anwser['qid'];
                $data['best_aid'] = $id;
                $data['status'] = '1';
                if (!$Ask->adoptAnwser($data)){
                    die('系统异常！');
                }else{
                    //用户采纳数+1
                    M("user")->where(array('id' => $anwser['uid']))->setInc('ask_adopts');
                    die('true');
                }
            }

            //点赞操作
            if($action == 'agree'){

                //答案赞同数+1
                M("ask_anwser")->where(array('id' => $id))->setInc('agree');
                //用户赞同数+1
                M("user")->where(array('id' => $anwser['uid']))->setInc('ask_agrees');
                die('true');

                // //查询这个问题所有权是否是当前操作用户
                // if($anwser['uid'] != $_SESSION["u_userInfo"]['id']){
                //     exit('不能对自己的答案点赞。');
                // }

                /*
                $data['time'] = time();
                $data['qid'] = $qid;
                $data['aid'] = $id;
                $data['uid'] = $_SESSION["u_userInfo"]['id'];

                //查询当前操作用户是否已赞
                $isAgree = $Ask->isAgree($data);
                !empty($isAgree) && exit('您已赞过了!');

                if (!$Ask->setAgree($data)){
                    // print_r(M('ask')->getLastSql());
                    die('系统异常！');
                }else{
                    //答案赞同数+1
                    M("ask_anwser")->where(array('id' => $id))->setInc('agree');
                    //用户赞同数+1
                    M("user")->where(array('id' => $anwser['uid']))->setInc('ask_agrees');
                    die('true');
                }*/
            }
            exit();
        }
    }

    //生成链接URL
    private function url($type='orderby',$str1='',$str2=''){
        $url = $_SERVER['REQUEST_URI'];

        if($type == 'orderby'){
            if(strpos($url,'anwsers') || strpos($url,'time')){
                $url = str_replace($str1,$str2,$url);
            }else{
                if(strpos($url,'?p=')){
                    //带有页码参数时，按时间和按回答数排序会拼接在页码前面
                    $old = '?p=';
                    $new = $str2.'?p=';
                    $url = str_replace($old,$new,$url);
                }else{
                    $parseUrl = parse_url($url);
                    if (!empty($parseUrl["query"])) {
                        $url = $parseUrl["path"].$str2."?".$parseUrl["query"];
                    }else{
                        $url = $url.''.$str2;
                    }
                }
            }

        }
        return $url;
    }

    //根据条件获取列表并分页
    private function getQList($condition,$pageIndex = 1,$pageCount = 10)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        import('Library.Org.Page.Page');
        $result = D("Common/Ask")->getQListByCategory($condition,($pageIndex-1) * $pageCount,$pageCount);
        $count = $result['count'];
        $qList = $result['result'];
        foreach ($qList as $k => $v) {
            //$qList[$k]['description'] = mbstr(htmlspecialchars(strip_tags($v['description'])),0,200);
            $qList[$k]['post_time'] = timeFormat($v['post_time']);
        }
        $config  = array("prev","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        return array("qList"=>$qList,"page"=>$pageTmp);
    }


    //根据条件获取列表并分页
    private function getAskList($condition,$pageIndex = 1,$pageCount = 10)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $result = D("Common/Ask")->getQListByCategory($condition,($pageIndex-1) * $pageCount,$pageCount);

        $count = $result['count'];
        $qList = $result['result'];
        foreach ($qList as $k => $v) {
            $qList[$k]['post_time'] = timeFormat($v['post_time']);
        }
        if ($count > $pageCount) {
            import('Library.Org.Page.SPage');
            $page = new \SPage($count, $pageCount, array(
                'templet' => '/wenda/ask-' . $condition['id'] . '/' . 'p-[PAGE].html',
                'firstUrl' => '/wenda/ask-' . $condition['id'] . '/'
            ));
            $pageTmp =  $page->show();
        }
        return array("qList"=>$qList,"page"=>$pageTmp);
    }

    //取热门回答
    private function getHotAsk($limit){
        $map = array("anwsers" => array("GT",'0'));
        $noAnwserList = D("Common/Ask")->getQuestionList($map,'post_time Desc',0,$limit);
        return $noAnwserList;
    }

    // 获取最新问答
    private function getNewestAsk($limit)
    {
        $newestAnswerList = D("Common/Ask")->getQuestionList([],'post_time Desc',0,$limit);
        return $newestAnswerList;
    }

    //按分类取热门回答
    private function getHotAskByCid($cid){
        $categoryColumn = $cid <= 6 ? 'cid' : 'sub_category';
        $map = array($categoryColumn => array("EQ",$cid));
        return D("Common/Ask")->getQuestionList($map,'anwsers DESC,post_time DESC',0,10);
    }

    //取最近一个月内回答没有被采纳的问题列表
    private function getNoAnwsers($limit,$cid = '',$orderby='id DESC'){
        $map = array(
            "visible"         =>  array("EQ",'0'),
            "adopt_time" => array("EQ",'0'),
            "post_time" => array("GT",strtotime('-1 month')),
        );
        //如果分类不为空
        if(!empty($cid)){
            $category = array("cid" => array("EQ",$cid));
            $map = array_merge($map,$category);
        }
        $noAnwserList = D("Common/Ask")->getQuestionList($map,$orderby,0,$limit);

        //只有指定分类时才处理
        if(!empty($cid)){
            //本分类下没人回答小于3时处理
            $anwserCount = count($noAnwserList);
            if($anwserCount <= 6 ){
                $nMap = array(
                        "anwsers" => array("EQ",'0'),
                        "cid" => array("NEQ",$cid)
                );
                $newResult = D("Common/Ask")->getQuestionList($nMap,$orderby,0,$limit - $anwserCount);
                return array_merge($noAnwserList,$newResult);
            }
        }
        return $noAnwserList;
    }

    //获取当前帮助人数
    public function getHelpPeople(){
        //需求：取当前帮助人数 369281 + 真实问题数 + 随机数 (每天增加30-100)
        /*
        BUG：每当整点时数字会刷新
        取今天随机数，把今天 总随机数 按时间分割成矩阵（每个时间段分配 <平均随机数> ）
        0:00-2:59 和 7:00-9:59 时间段随机数占比为 20%
        3:00-6:59 时间段不做任何处理
        10:00-23:59 时间段随机数占比为 80% (其中，平均随机值剩下的时间差集 平均分布至 19-20 点，剩余分布于21点）
        帮助人数为： 已记录总数（基数 + 当前时间段增加数，每一小时更新一次） + 当前数据库中存在的真实提问数

        */
        $cache = F('wendaHelpPeople');
        //生成今天的基数
        if(empty($cache['todayNum']) || $cache['today'] < date('Y-m-d') || empty($cache['timeHashList'])){
            $cache['todayNum'] = mt_rand(30, 100);
            $cache['today'] = date('Y-m-d');

            //晚上23点至白天10点占比
            $night = round($cache['todayNum'] * 0.2);
             //早晨0 - 10点随机分布矩阵数据定义：
            $nightAverage = round($night/7);
            $timeHashList['00']['num'] = $nightAverage;
            $timeHashList['01']['num'] = $nightAverage;
            $timeHashList['02']['num'] = $nightAverage;
            $timeHashList['07']['num'] = $nightAverage;
            $timeHashList['08']['num'] = $nightAverage;
            $timeHashList['09']['num'] = $night - ($nightAverage * 5);
            //白天占比为总随机数减去晚上占比
            $day = $cache['todayNum'] - $night;
            //白天平均值
            $averageDay = round($day/13);
            //定义白天随机分布矩阵
            for ($i = 10; $i <= 23; $i++) {
                //当前随机从1至平均数
                $num = mt_rand(1,$averageDay);
                $timeHashList[$i] = array(
                    'num' => $num
                );
                //总共使用过的随机数
                $allNum = $allNum + $num;
            }
            //差集
            $diff = $day - $allNum;
            //平均
            $averageDiff = round($diff/3);
            //时间矩阵平均分布至 19-20 点，剩余分布于21点。
            $timeHashList['19']['num'] = $timeHashList['19']['num'] + $averageDiff;
            $timeHashList['20']['num'] = $timeHashList['20']['num'] + $averageDiff;
            $timeHashList['21']['num'] = $timeHashList['21']['num'] + ($diff - $averageDiff * 2);
            $cache['timeHashList'] = $timeHashList;
        }
        $count = M('ask')->count();

        //每小时更新一次总数
        //取最后更新时间和当前时间比较 小于的话就更新总数据 并 排除 3 - 7 点之前的时间
        if($cache['lastUpdate'] < date('YmdH') && !in_array(date('H'),array('03','04','05','06'))){
            $Data = D("Common/Ask");
            //读取配置文件
            $askHelp = $Data->getOption('ask_help_people');
            //如果没有总数，那么重新定义总数，一般在第一次运行时出现
            if(empty($askHelp['allNum'])){
                $askHelp['allNum'] = 370300;
            }
            //过期时重新定义上一个时间段的总数
            $cache['allNum'] = $askHelp['allNum'] + intval($cache['timeHashList'][date('H')]['num']);
            $cache['lastUpdate'] = date('YmdH');
            //把总数写入数据库
            $Data->updateOption('ask_help_people',serialize($cache));
            //写入缓存： 缓存数据为：最后更新时间，总数。
            F('wendaHelpPeople',$cache);
        }

        //帮助人数为： 总记录数（基数 + 当前时间段增加数，每一小时更新一次） + 当前数据库中存在的真实数
        return $cache['allNum'] + $count;
        die();
    }

    //上传图片
    public function upload(){
        //判断是否登录
        if(!isset($_SESSION["u_userInfo"])){
           exit('Need Login');
        }
        //上传图片
        if(!empty($_FILES["file"])){
            $file = $_FILES["file"];
            $fileExt = pathinfo($_FILES["file"]['name'], PATHINFO_EXTENSION);
            $result = $this->uploadToQiNiu($file["tmp_name"],$fileExt);
            if(gettype($result) != "object"){
                echo json_encode(array("error"=>0,"pic"=>$result["key"],"name"=>$result["key"]));
            }else{
                echo json_encode(array("error"=>"图片上传失败,请联系技术部门！"));
            }
        }
        die();
    }

    //获取相关问题，按分类ID缓存15分钟
    private function getRelatedAnwser($cid,$num){
        $result = S('Cache:Ask:related:cate'.$cid);
        if(empty($result)){
            S('Cache:Ask:related:cate'.$cid,null);
            $condition['orderBy'] = 'a.post_time DESC';
            $condition['cateId'] = $cid;
            $relatedAnwser = D("Common/Ask")->getQListByCategory($condition,0,40);
            $result =$relatedAnwser['result'];
            unset($relatedAnwser);
            S('Cache:Ask:related:cate'.$cid,$result,900);
        }
		//如果当前结果小于要取的结果
		if(count($result) <= $num){
			foreach($result as $k->$v){
				$result[$k]['post_time'] = date('Y-m-d H:i:s',$v['post_time']);
			}
			return $result;
		}
        $s = array_rand($result,$num);
        foreach($s as $val){
            $val['post_time'] = date('Y-m-d H:i:s',$val['post_time']);
            $newResult[] = $result[$val];
        }
        return $newResult;
    }

    //获取美图推荐的装修案例
    private function getCases($limit){
        $cases = D("Meitu")->getRecommendCases(1);
        shuffle($cases);
        return array_slice($cases,0,$limit);
    }

    //获取装修日记
    private function getHotDiarys($num){
        if(empty($result)){
            $result = D('Diary')->get_hot_diary_list(20);
        }
        $s = array_rand($result,$num);
        foreach($s as $val){
            $hotDiary[] = $result[$val];
        }
        return $hotDiary;
    }


    //高亮搜索关键词
    private function highlightWords($str,$keywords,$color = "#DE4348") {
        if (empty($keywords)) {
            return $str;
        }
        $keywords = preg_split("/[ \t\r\n,]+/", $keywords);
        foreach($keywords as $val) {
            $tvar = preg_match('/'.$val.'/', $str, $regs);
            $finalrep    = '<font style="color:'.$color.';font-weight:bold">' . $regs[0] . '</font>';
        }
        $str = str_ireplace($regs[0], $finalrep, $str);
        return $str;
    }

    //上传到七牛服务器
    private function uploadToQiNiu($file,$fileExt){
        import("Library.Org.Qiniu.io",'','.php');
        import("Library.Org.Qiniu.rs",'','.php');
        $bucket = OP('QINIU_BUCKET');
        $accessKey = OP('QINIU_AK');
        $secretKey = OP('QINIU_CK');
        Qiniu_SetKeys($accessKey, $secretKey);
        $putPolicy = new \Qiniu_RS_PutPolicy($bucket);

        $putPolicy->MimeLimit = 'image/jpeg;image/png;image/gif';
        $putPolicy->SaveKey = 'ask/$(year)$(mon)$(day)/$(etag)'.'.'.$fileExt;
        $upToken = $putPolicy->Token(null);
        $putExtra = new \Qiniu_PutExtra();
        $putExtra->Crc32 = 1;
        list($ret, $err) = Qiniu_PutFile($upToken, null, $file, $putExtra);
        if($err == null){
            $result = array(
                    "hash"=>$ret["hash"],
                    "key"=> $ret["key"]
                            );
            return $result;
        }
        return $err;
    }

    //获取问题分类 使用缓存
	private function getCategory($cid = '',$update = false){
        //先取所有分类
        $tempCategory = S('Cache:Ask:AllCategory:v2');
        if(empty($tempCategory)){
            $tempCategory = D("Common/Ask")->getCategory();
            S('Cache:Ask:AllCategory:v2',$tempCategory,86400);
        }

        $category = S('Cache:Ask:Category');

        if(empty($category) || $update == true){
    	    $category = array();
    	    if($tempCategory){
    	    	//为了避免这个Bug，进行两次遍历，先取根数组，后期改进
    	        foreach ($tempCategory as $k => $v ){
    	        	if($v['pid'] == '0') {
    	        		$category[$v['cid']] = $v;
    	        		unset($k);
    	        	}
    	        }
    			foreach ($tempCategory as $k => $v ){
    				if($v['pid'] != '0') {
    					$category[$v['pid']]['sub_cate'][] = $v;
    				}
    			}
    	    }
    	    ksort($category);
            S('Cache:Ask:Category',$category,3600);
        }
        //根据 Cid 返回
        if(!empty($cid)){
            if(empty($tempCategory)){
                $tempCategory = D("Common/Ask")->getCategory();
            }
            foreach ($tempCategory as $k => $v ){
                if($v['cid'] == $cid) {
                    unset($tempCategory);
                    return $v;
                    exit;
                }
            }
        }
        unset($tempCategory);
        return $category;
	}

    //curl连接发布问题和答案地址
    public function postquestionsbycurl()
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, True);
        if(!empty($data)){
            //判断是否已存在该问题
            $count = M('ask')->where(array('title' => $data['w_data']['title']))->count();
            if (0 == $count) {
                $result = D("Common/Ask")->addQuestion($data["w_data"]);//添加一条问题
                foreach ($data["a_data"]['content'] as $k => $v) {
                    $answer['qid'] = $result;
                    $answer['uid'] = $data["a_data"]['user'][$k]['uid'];
                    $answer['create_time'] = $data["a_data"]['create_time'];
                    $answer['post_time'] = $data["a_data"]['post_time'];
                    $answer['comments'] = $data["a_data"]['comments'];
                    $answer['agree'] = $data["a_data"]['agree'];
                    $answer['visible'] = $data["a_data"]['visible'];
                    $answer['content'] = $v;
                    $a_id = D("Common/Ask")->addAnwser($answer);//添加一条答案
                    if ($a_id) {
                        //更新最后回答时间
                        M("ask")->where(array('id' => $result))->setInc(array('last_time'=>time()));
                        M("ask")->where(array('id' => $result))->setInc('anwsers');
                        M("user")->where(array('id' =>$answer['uid']))->setInc('ask_anwsers');
                    }
                    unset($answer);
                }
                $this->ajaxReturn(array('data'=>$result,'success'=>1));
            }
        }
        $this->ajaxReturn(array('success'=>0));
    }

    public function getCarefullChoosen()
    {
        $logic = new \Common\Model\Logic\WwwArticleLogicModel();
        $list = $logic->getCarefullChoosenList();

        $this->assign('choosenArticle', $list);
    }
}
