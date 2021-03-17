<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

class DiaryController extends HomeBaseController{

    private $mianji = array(
        array('id'=>1,'name'=>'60㎡以下'),
        array('id'=>2,'name'=>'60-80㎡'),
        array('id'=>3,'name'=>'80-100㎡'),
        array('id'=>4,'name'=>'100-120㎡'),
        array('id'=>5,'name'=>'120-150㎡'),
        array('id'=>6,'name'=>'150㎡以上'),
    );

    public function _initialize(){
        parent::_initialize();//先走父类的构造方法
        $uri = $_SERVER['REQUEST_URI'];
        preg_match('/html$/',$uri,$m);

        if (count($m) == 0) {
            preg_match('/\/$/',$uri,$m);
            if (count($m) == 0 && empty($parse["query"])) {
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
        //添加顶部搜索栏信息
        $this->assign('serch_uri','gonglue/search');
        $this->assign('serch_type','装修攻略');
        $this->assign('holdercontent','了解相关的装修资讯知识');
        //日记是选中状态
        $this->assign('choose_gonglue', 'riji');
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
        $headerTmp = $this->fetch($t);
        $this->assign("headerTmp",$headerTmp);
    }

    public function index(){
        //跳转到手机端
        if (ismobile()) {
            header( "HTTP/1.1 301 Moved Permanently");
            header("Location:  ".$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }
        $info = S('Cache:Diary:Home');  // todo 缓存  上线前加上
        if(empty($info)){
            $diaryModel = D('Diary');

            //获取装修达人
            $info['decorate_master'] = $diaryModel->get_decoration_master(6);
            //获取热门日记推荐日记
            $info['hot_diary_list'] = $diaryModel->get_hot_diary_list(4);

            $info['fengge_data'] = D('Meitu')->getFengge(20,true);
            $info['diary_type'] = D('Diary')->get_diary_type();

            S('Cache:Diary:Home',$info,300);
        }

        //获取风格
        $info['fengge'] = $this->get_fengge_url($info['fengge_data']);
        //获取户型
        $huxing = D('Meitu')->getHuxing(20,true);
        foreach ($huxing as $key => $value) {
           if($value["id"] >=12 && $value["id"] <=18 )  {
                unset($huxing[$key]);
           }
        }
        $info['huxing'] = $this->get_huxing_url($huxing);
        //获取面积
        $info['mianji'] = $this->get_mianji_url($this->mianji);
        //查询日记类型
        $info['diary_type'] = $this->get_step_url($info['diary_type']);

        $url = $this->get_url();

        foreach ($url as $key => $value){
            if($key == "f"){
                foreach ($info['fengge'] as $k => $val) {
                    if($value == $val["id"]){
                        $f = $val["name"];
                        break;
                    }
                }
                continue;
            }
            if($key == "h"){
                foreach ($info['huxing'] as $k => $val) {
                    if($value == $val["id"]){
                        $h = $val["name"];
                        break;
                    }
                }
                continue;
            }
            if($key == "m"){
                foreach ($info['mianji'] as $k => $val) {
                    if($value == $val["id"]){
                        $m = $val["name"];
                        break;
                    }
                }
                continue;
            }
        }


        if(I("get.list") !== ""){
            $sstitle = $f.$h.$m;
            if(!empty($sstitle)){
                $keys["title"] = $sstitle."_装修日记-齐装网";
            }else{
                $keys["title"] = "装修设计_装修日记-齐装网";
            }
        }else{
            $keys["title"] = "写装修日记_晒装修日记图片-齐装网";
        }
        $keys["keywords"] = "装修日记,装修日记图片";
        $keys["description"] = "齐装网装修日记汇聚海量业主真实装修过程及业主真实感受，包含各种装修风格装修日记图片。同时可与业主互动了解装修详情。";


        //获取日记列表信息
        $diaryList = D('Diary')->get_all_diary_list();

        if(I("get.p") !== ""){
            $keys["title"] =  "写装修日记_晒装修日记图片-第".intval(I("get.p"))."页-齐装网装修日记";
        }

        $request_uri = I('server.REQUEST_URI');
        if(!empty($request_uri)){
            $staticURI = explode('?',$request_uri);
        }

        //获取canonical属性
        if(!empty($staticURI)){
            $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'] . C('QZ_YUMINGWWW').$staticURI['0'];
        }

        $this->assign('fengge',$info['fengge']);
        $this->assign('huxing',$info['huxing']);
        $this->assign('mianji',$info['mianji']);
        $this->assign("diary_type",$info['diary_type']);
        $this->assign("decorate_master",$info['decorate_master']);

        //赋值装修达人Template
        $this->assign("decorate",$this->fetch("decorate"));
        $this->assign("hot_diary_list",$hot_diary_list);
        $this->assign("hot_diary",$this->fetch("hot_diary"));

        foreach($url as $k=>$v){
            $this->assign($k,$v);//遍历赋值选择的属性分类
        }

        $this->assign('diary_list',$diaryList['list']);
        $this->assign("page",$diaryList['pageTmp']);
        $this->assign("keys",$keys);

        //获取城市信息
        //$info["citys"] = json_encode(getCityArray());
        $this->assign("info",$info);
        //获取报价模版
        $this->assign("order_source",109);
        $this->assign("orderTmp",$this->fetch(T("Common@Order/orderTmp")));
        //获取底部弹层
        $this->assign("zb_bottom_s",$this->fetch(T("Common@Order/zb_bottom_s")));
        $this->display();
    }

    /**
     * 个人日记列表
     */
    public function diary_user_list(){

        $id=I("get.id");

//        $map = array(
//            "classid"=>array("EQ",1),
//            "id"=>array("EQ",$id)
//        );
        $map = array(
            "uuid"=>array("EQ",$id)
        );
//        $user_info=M('user')->where($map)->find();
        $user_info=M('ucenter_profile')->where($map)->field('avatar as login,nickname as name')->find();
        // 如果没有或者为空， 则再查询一下user表
        if(empty($user_info)){
            $map = array(
                "classid"=>array("EQ",1),
                "id"=>array("EQ",$id)
            );
//            $user_info=M('user')->where($map)->field('login,`name`')->find();
            $user_info=M('user')->where($map)->find();
        }

        if (empty($user_info)){
            $this->_error();
            die();
        }

        $diary_model=D('Diary');
        # 查询个人的日记列表
        $list = $diary_model->get_my_diary_list($id);


        $user_info["all_count"] = count($list);
        extract($list);
        $this->assign('list',$list);
        $this->assign('page',$pageTmp);
        if (empty($user_info['logo']))
        {
            $user_info['logo']=OP("DEFAULT_LOGO");
        }

        $decorate_master=$diary_model->get_decoration_master(6);//获取装修达人
        $this->assign("decorate_master",$decorate_master);
        $tmp = $this->fetch("decorate");
        $this->assign("decorate",$tmp);//赋值装修达人

        //获取热门日记推荐日记
        $hot_diary_list=$diary_model->get_hot_diary_list(4);//获取四篇热门推荐日记
        $this->assign("hot_diary_list",$hot_diary_list);//赋值热门推荐日记列表
        $tmp = $this->fetch("hot_diary");
        $this->assign("hot_diary",$tmp);


        $keys["keywords"] = $user_info["name"]."的装修日记";
        $keys["title"] = $user_info["name"]."的装修日记 - 齐装网装修日记";
        $keys["description"] = "齐装网为您提供".$user_info["name"]."的装修日记及图片。";
        $this->assign("keys",$keys);
        //获取报价模版
        $this->assign("order_source",131);
        $t = T("Common@Order/orderTmp");
        $orderTmp = $this->fetch($t);
        $this->assign("orderTmp",$orderTmp);

        //获取canonical属性
        $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'] . C('QZ_YUMINGWWW').$_SERVER['REQUEST_URI'];


        $this->assign("tabIndex",5);
        $this->assign("info",$info);
        $this->assign("user_info",$user_info);
        $this->display();//加载个人日记列表页面
    }
    /**
     * [diary_info_list 单篇日记列表信息]
     * @return [type]            [模版]
     */
    public function diary_info_list(){
        $id=I("get.id");
        $diary_model=D('Diary');
        if(I("get.type")!==""){
            $type =str_replace("-","",I("get.type"));
            switch ($type) {
                case 'zhunbei':
                    $type = 1;
                    break;
                case 'shigong':
                    $type = 2;
                    break;
                case 'ruzhu':
                    $type = 3;
                    break;
                default:
                    $type = "";
                    break;
            }
        }

        $diary_info_list = $diary_model->get_one_diary_info($id,true,$type);

        if(empty($diary_info_list)){
           $this->_error();
           die();
        }
        import('Library.Org.Util.Fiftercontact');
        $filter = new \Fiftercontact();
        foreach ($diary_info_list as $k => $v)
        {
            //过滤内容中的超链接标签
            $v["content"] = $filter->filter_common($v["content"],array("filter_link"));

            if($v['parent_id'] == 0){
               $diaries["now"] = $v;
            }else{
                #获取日记列表后还没获取到日记图集
                $v['img_list']=$diary_model->get_diary_img_by_id($v["id"]);
                if(!empty($type)){
                    if($type == $v["stage"]){
                        $diaries["child"][] = $v;
                    }
                }else{
                    $diaries["child"][] = $v;
                }

                $diaries["all_reply_count"] += $v["reply_count"];
                $diaries["all_page_view"] += $v["page_view"];
                $diaries["all_concern_count"] += $v["concern_count"];
                $diaries["diary_all_count"] ++;
                switch ($v["stage"]) {
                    case '1':
                        $diaries["zhunbei"] ++;
                        break;
                    case '2':
                        $diaries["shigong"] ++;
                        break;
                    case '3':
                        $diaries["ruzhu"] ++;
                        break;
                }
            }
        }


        //获取风格
        $fengge=D('Meitu')->getFengge(20,true);//获取风格
        foreach ($fengge as $key => $value)
        {
            if (in_array($value['id'],$diary_info_list[0]['fengge']))
            {
                $diary_info_list[0]['fengge_name'][]=$value['name'];
            }
        }
        $this->assign('fengge',$fengge);//分配风格变量


        //判断当前用户是否登录来给用户评论的时候设置头像
        if(isset($_SESSION['u_userInfo']['id']))
        {
            $logo=$_SESSION['u_userInfo']['logo'];
        }else
        {
            $logo= C('QINIU_SCHEME')."://".C('QINIU_DOMAIN').'/'.OP('DEFAULT_LOGO');
        }
        $this->assign('logo',$logo);

        // //日记访问量需要加1
        // $diary_model->diary_page_view_add($id);
//        $user_info=M('user')->find($diaries["now"]["user_id"]);
        $user_info=M('ucenter_profile')->where(array('uuid'=>$diaries["now"]["user_id"]))->field('avatar as logo,nickname as name')->find();
        //如果没有或者为空，查询一下qz_user表
        if(empty($user_info)){
            $user_info=M('user')->where(array('id'=>$diaries["now"]["user_id"]))->field('logo,`name`')->find();
        }
        //获取该用户的其他日记数量
        $user_info["other_count"] = $diary_model->getOtherDiaryCount($diaries["now"]["user_id"],$id);

        $keys["title"] = $diaries['now']['mianji'] . '平米' . $diaries['now']['hx'] . $diaries['now']['fg'] . '装修日记-齐装网装修日记';
        $keys["keywords"] = $diaries['now']['mianji'] . '平米,' . $diaries['now']['hx'] . '装修,' . $diaries['now']['fg'] . '风格装修';
        $keys["description"] = '齐装网装修日记频道为您提供' . $diaries['now']['mianji'] . '平米' . $diaries['now']['hx'] . $diaries['now']['fg'] . '风格装修日，包含前期准备、确定设计会所、水电阶段等，分享装修经验，记录装修点滴！';
        $this->assign("keys",$keys);

        //获取相关类型的推荐案例
        $cases = $this->getRecommendCases(8);
        $info["recommendCases"] = $cases;

        //获取canonical属性
        $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'] .C('QZ_YUMINGWWW').$_SERVER['REQUEST_URI'];


        $this->assign("info",$info);

        //获取报价模版
        $this->assign("order_source",132);
        $this->assign("orderTmp",$this->fetch(T("Common@Order/orderTmp")));

        $this->assign("tabIndex",5);
        $this->assign("user_info",$user_info);
        $this->assign("diary_info_list",$diaries);
        $this->display();
    }


    /**
     * [add_diary_comment 添加评论]
     */
    public function add_diary_comment(){
        #添加日记评论
        $ajax_result=array('data'=>'','info'=>'ERROR','status'=>0);
        if(empty($_SESSION['u_userInfo']))
        {
            $ajax_result['info']="您还尚未登录！请登录后发表回复";
            return $this->ajaxReturn($ajax_result);
        }

        // 只有业主可以评论/收藏
        if($_SESSION['u_userInfo']['classid'] != 1){
            $this->ajaxReturn(['data' => '', 'info' => '该账号无操作权限~', 'status' => 0]);
        }

        $verify = S("Cache:Geetest:".session_id());
        if($verify == 1){
            session("geetest_verify",null);
            //已经登录开始对日记进行评论
            $diary_id=I('post.diary_id');//所评论的日记id
            $comment_id=I('post.data_id');//所回复的评论id
            $content=I('post.content');//所回复的内容
            $reply_id = I("post.reply_id");//评论人的ID
            $diary_model=D('Diary');
            $diary_model->startTrans();
            $diary_info=$diary_model->get_one_diary_info($diary_id);//取该日记信息
            if (empty($diary_info))
            {
                $ajax_result['data']="您要评价日记貌似不存在！请刷新重试！";
                return $this->ajaxReturn($ajax_result);
            }

            //发送间隔为5分钟
            $result =  $diary_model->getLastPostComment(session("u_userInfo.id"));

            if (count($result) > 0) {
                $offset = floor((time() - $result["add_time"])%86400/60);
                if ($offset <= 3) {
                    $this->ajaxReturn(array("data"=>"","info"=>"您的操作过于频繁，请休息3分钟后再试！","status"=>0));
                    exit();
                }
            }

            //过滤敏感词
            import('Library.Org.Util.Fiftercontact');
            $filter = new \Fiftercontact();
            $content = $filter->filter_common($content,array("Sbc2Dbc","filter_script",array("filter_sensitive_words",array(2,3,5)),"filter_link","filter_url","filter_html_url"));

            $data = array(
                    "diary_id"=>$diary_id,
                    "logo"=>$_SESSION["u_userInfo"]["logo"],
                    "user_id"=>$_SESSION["u_userInfo"]["id"],
                    "user_name"=>$_SESSION["u_userInfo"]["name"],
                    "author_id"=>$reply_id,
                    "author_name"=>I('post.data_name'),
                    "content"=>$content,
                    "add_time"=>time(),
                    "stat"=>1,
                    "parent_id"=>$comment_id
                          );

            $add_result=$diary_model->add_diary_comment($data);//评论入库
            if($add_result !== false)
            {
                //添加修改日记的评论数
                $diary_model->diary_page_view_add($diary_id,"reply_count");
                $diary_model->commit();
                $ajax_result['info']="添加评论成功!";
                $ajax_result['status']=1;
            }else
            {
                $diary_model->rollback();
                $ajax_result['info']="添加评论失败!";
                $ajax_result['status']=0;
            }

            $this->ajaxReturn($ajax_result);//返回结果
        }else{
            $this->ajaxReturn(array("data"=>'',"info"=>"验证失败！","status"=>0));
        }
    }

    /**
     * [diary_detail_info 日记详情]
     * @return [type]        [模版展示]
     */
    public function diary_detail_info(){
        #展示单个日记的详细信息和图片
        $id=I("get.id");
        //跳转到手机端
        if (ismobile()) {
            header( "HTTP/1.1 301 Moved Permanently");
            header("Location:  ".$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }

        $diary_model=D('Diary');

        $diary_detail_info = S('Cache:W:Home:Diary:diaryDetailInfo:info:' . $id);
        if (!$diary_detail_info) {
            $diary_detail_list = $diary_model->get_one_diary_info($id);
            $diary_detail_info = $diary_detail_list[0];

            $diary_detail_info['img_list'] = $diary_model->get_diary_img_by_id($id);
            $diary_detail_info['diary_type_name'] = $diary_model->get_diary_type_name_by_id($diary_detail_info['second_type']);

            S('Cache:W:Home:Diary:diaryDetailInfo:info:' . $id, $diary_detail_info, 900);
        }

        if(empty($diary_detail_info) || $diary_detail_info["parent_id"] == 0)
        {
            $this->_error();
            die();
        }

        $this->assign('diary_detail_info',$diary_detail_info);

        $user_info = S('Cache:W:Home:Diary:diaryDetailInfo:info:user' . $diary_detail_info['user_id']);
        if (!$user_info) {
            $user_info = M('ucenter_profile')->where(array("uuid"=>$diary_detail_info['user_id']))->field('avatar as logo,nickname as name')->find();
            //如果用户中心查不到，在user表再查一下
            if(empty($user_info)){
                $user_info = M('user')->where(array("id"=>$diary_detail_info['user_id']))->field('logo,`name`')->find();
            }

            S('Cache:W:Home:Diary:diaryDetailInfo:info:user' . $diary_detail_info['user_id'], $user_info, 900);
        }
        $this->assign('user_info',$user_info);

        //获取回复
        $diary_comment = S('Cache:W:Home:Diary:diaryDetailInfo:info:comment' . $diary_detail_info['id']);
        if (!$diary_comment) {
            $diary_comment = $diary_model->get_one_diary_comment($diary_detail_info['id']);
            S('Cache:W:Home:Diary:diaryDetailInfo:info:comment' . $diary_detail_info['id'], $diary_comment, 300);
        }

        $this->assign('diary_comment',$diary_comment);
         //获取评论数目
        // $diary_comment_count=$diary_model->get_one_diary_comment_count($diary_detail_info['id']);
        // $this->assign('diary_comment_count',$diary_comment_count);//日记评论数目

        //判断当前用户是否登录来给用户评论的时候设置头像
        if(isset($_SESSION['u_userInfo']['id'])){
            $logo = $_SESSION['u_userInfo']['logo'];
        }else{
            $logo = C('QINIU_SCHEME')."://".C('QINIU_DOMAIN').'/'.OP('DEFAULT_LOGO');
        }
        $this->assign('logo',$logo);


        //获取热门日记推荐日记.
        $cache_key = $diary_detail_info['parent_id'] . ':' . $id;
        $other_diary_list = S('Cache:W:Home:Diary:diaryDetailInfo:info:otherDiary:' . $cache_key);
        if (!$other_diary_list) {
            $result = $diary_model->getOtherDiary($diary_detail_info['parent_id'],$id,4);//获取四篇热门推荐日记

            if(count($result) > 0){
                foreach ($result as $key => $value) {
                    $value['content'] = strip_tags($content);
                    if(!array_key_exists($value["parent_id"], $other_diary_list)){
                        $other_diary_list[$value["parent_id"]] = $value;
                    }
                    $other_diary_list[$value["parent_id"]]["child"][] = $value;
                }
            }

            //如果其它日记为空
            if(empty($other_diary_list)){
                $other_diary_list = $diary_model->getRandDiary($id,'4');
            }

            S('Cache:W:Home:Diary:diaryDetailInfo:info:otherDiary:' . $cache_key, $other_diary_list, 900);
        }
        $this->assign("other_diary_list",$other_diary_list);//赋值热门推荐日记列表

        //相关装修攻略
        $info['article'] = $this->getArticleByTag(8,$diary_detail_info['tags']);
        $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'] . C('QZ_YUMINGWWW').$_SERVER['REQUEST_URI'];


        $this->assign("info",$info);
        $this->assign("tabIndex",5);

        //获取报价模版
        $this->assign("order_source",133);
        $this->assign("orderTmp",$this->fetch(T("Common@Order/orderTmp")));

        //获取黄历报价模版
        $this->assign("hlBaoJia",$this->fetch(T("Common@Order/hlBaoJia")));

        //免费通话模版
        $this->assign("freetel",$this->fetch(T("Common@Zbfb/freetel")));


        //日记访问量需要加1
        $diary_model->diary_page_view_add($id);
        $keys["title"] = $diary_detail_info['mianji'] . '㎡' . $diary_detail_info['hx'] . $diary_detail_info['fg'] . $diary_detail_info["title"] . '装修日记 - 齐装网装修日记';
        $keys["keywords"] = $diary_detail_info["title"] . '装修日记';
        $keys["description"] =mb_substr($diary_detail_info["content"], 0,50,"utf-8");
        $this->assign("keys",$keys);
        $this->display();//加载模版
    }


    //获取热门回答
    private function getHotAsk($num){
        $result = D("Ask")->getHotAsk(50);
        foreach ($result as $key => $value) {
            $result[$key]['content'] = strip_tags($value['content']);
        }
        shuffle($result);
        $hotAsk = array_slice($result,0,$num);
        return $hotAsk;
    }

    //取随机美图
    private function getRankMeitu($limit){
        $meitu = D("Meitu")->getRankMeitu(30);
        shuffle($meitu);
        $meitu = array_slice($meitu,0,$limit);
        return $meitu;
    }

    //根据日记标签取攻略 没有标签取全部
    private function getArticleByTag($limit,$id){
        $cache_key = $limit . ':' . $id;
        $articleList = S('Cache:W:Home:Diary:tagArticleList:' . $cache_key);
        if (!$articleList) {
            $map['type'] = '3';
            $map['id'] = $id;
            $tags = M('tags')->field('*')->where($map)->find();
            $tagArticle = D("Article")->getArticleByTag($tags['name'],30);

            //如果标签取的数量不够
            if(count($tagArticle) < $limit){
                $num = $limit - count($tagArticle);
                $allArticle = D("Article")->getRandArticle($num);
                $articleList = array_merge($tagArticle,$allArticle);
            }else{
                shuffle($tagArticle);
                $articleList = array_slice($tagArticle,0,$limit);
            }

            S('Cache:W:Home:Diary:tagArticleList:' . $cache_key, $articleList, 900);
        }

        return $articleList;
    }


    /**
     * [getRecommendCases 获取推荐案例]
     * @param  [type] $limit   [取几条]
     * @return [type]          [description]
     */
    private function getRecommendCases($limit)
    {
        $cases = D("Meitu")->getRecommendCases(1);
        shuffle($cases);
        $cases = array_slice($cases,0,$limit);
        return $cases;
    }
    /**
     * [get_url 获取URL]
     * @return [array] [返回数组list]
     */
    private function get_url(){
        $request_url = explode("/",$_SERVER["REQUEST_URI"]);
        $reg  = '/^list-[a-z0-9]+$/i';
        preg_match_all($reg, $request_url[2], $matches);
        if(count($matches[0]) > 0){
            //将条件解析成数组
            $exp = explode("-", $request_url[2]);
            $exp =$exp[1];
            $reg = '/[a-z]\d+/';
            preg_match_all($reg, $exp, $matches);
            //list-f22h4m3s20
            //fengge  huxing mianji  step
            //f       h      m       s
            if($matches){
                $matches=$matches[0];
                $fengge=substr($matches[0],1)+0;//获取风格
                $huxing=substr($matches[1],1)+0;//获取户型
                $mianji=substr($matches[2],1)+0;//获取面积
                $step=substr($matches[3],1)+0;//获取进展阶段
                $array=array(
                             "f"=>$fengge,
                             "h"=>$huxing,
                             "m"=>$mianji,
                             "s"=>$step,
                             );
                return $array;
            }
        }
        return array(
                     "f"=>"0",
                     "h"=>"0",
                     "m"=>"0",
                     "s"=>"0",
        );
    }

    /**
     * [get_home_url 获取URL]
     * @return [id] [返回数组id]
     */
    public function get_home_url()
    {
        $request_url = explode("/",$_SERVER["REQUEST_URI"]);
        $reg  = '/^[0-9]+$/i';
        preg_match_all($reg, $request_url[2], $matches);
        if(count($matches[0]) > 0)
        {
           return $matches[0][0];
        }
        return 0;
    }
    /**
     * [get_home_url 获取URL]
     * @return [id] [返回数组id]
     */
    public function get_list_url()
    {
        $request_url = explode("/",$_SERVER["REQUEST_URI"]);
        $reg  = '/^list-[0-9]+$/i';
        preg_match_all($reg, $request_url[2], $matches);
        if(count($matches[0]) > 0)
        {
            //将条件解析成数组
            $exp = explode("-", $request_url[2]);
            $exp =$exp[1];
            $reg = '/[0-9]\d+/';
            preg_match_all($reg, $exp, $matches);
            //home-11301
            if($matches)
            {
                $matches=$matches[0][0];
                return $matches;
            }
        }
        return 0;
    }
    /**
     * [get_detail_url 获取URL]
     * @return [id] [返回数组id]
     */
    public function get_detail_url()
    {
        $request_url = explode("/",$_SERVER["REQUEST_URI"]);
        $reg  = '/^[0-9]+$/i';
        preg_match_all($reg, $request_url[2], $matches);
        if(count($matches[0]) > 0)
        {
            return $matches[0][0];
        }
        return 0;
    }
    /**
     * [get_fengge_url 获取风格url]
     * @param  array  $fengge [fengge数组]
     * @return [type]         [fengge数组]
     */
    private function get_fengge_url($fengge=array())
    {
        $url=$this->get_url();
        $link_url='list-f%sh'.$url['h'].'m'.$url['m'].'s'.$url['s'];
        foreach ($fengge as $key => $value)
        {
            $fengge[$key]['link']=sprintf($link_url,$value['id']);
        }
        return $fengge;
    }
    /**
     * [get_huxing_url 获取风格url]
     * @param  array  $huxing [huxing数组]
     * @return [type]         [huxing数组]
     */
    private function get_huxing_url($huxing=array())
    {
        $url=$this->get_url();
        $link_url='list-f'.$url['f'].'h%s'.'m'.$url['m'].'s'.$url['s'];
        foreach ($huxing as $key => $value)
        {
            $huxing[$key]['link']=sprintf($link_url,$value['id']);
        }
        return $huxing;
    }
    /**
     * [get_mianji_url 获取面积url]
     * @param  array  $mianji [mianji数组]
     * @return [type]         [mianji数组]
     */
    private function get_mianji_url($mianji=array())
    {
        $url=$this->get_url();
        $link_url='list-f'.$url['f'].'h'.$url['h'].'m%ss'.$url['s'];
        foreach ($mianji as $key => $value)
        {
            $mianji[$key]['link']=sprintf($link_url,$value['id']);
        }
        return $mianji;
    }
    /**
     * [get_step_url 获取阶段URL]
     * @param  array  $step [step数组]
     * @return [type]       [step数组]
     */
    private function get_step_url($step=array())
    {
        $url=$this->get_url();
        $link_url='list-f'.$url['f'].'h'.$url['h'].'m'.$url['m'].'s%s';
        foreach ($step as $key => $value)
        {
            if (!empty($value['child']))
            {
                foreach ($value['child'] as $k => $v) {
                    $step[$key]['child'][$k]['link']=sprintf($link_url,$v['id']);
                }
            }else
            {
                $step[$key]['link']=sprintf($link_url,$value['id']);
            }
        }
        return $step;
    }

    private function getRecommendArticles($classid,$limit){
        //获取相同分类的点击量最高的文章
        $recommendArticles = D("WwwArticle")->getRecommendArticles();
        if(count($recommendArticles) > 0){
            shuffle($recommendArticles);
            $recommendArticles = array_slice($recommendArticles,0,$limit);
        }
        return $recommendArticles;
    }
}