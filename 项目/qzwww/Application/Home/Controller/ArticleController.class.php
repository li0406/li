<?php
namespace Home\Controller;
use Common\Model\Logic\ArticleLogicModel;
use Common\Model\Logic\CompanyLogicModel;
use Common\Model\Logic\ThematicWordsLogicModel;
use Common\Model\Service\ElasticSearchServiceModel;
use Home\Common\Controller\HomeBaseController;

class ArticleController extends HomeBaseController{

    public function _initialize(){

        parent::_initialize();
        //判断是否是搜索引擎蜘蛛
        $robotIsTrue = B("Common\Behavior\RobotCheck");
        $this->robotIsTrue = $robotIsTrue;
        //$robotIsTrue = true; //debug 警告线上应该为注释状态
        if (true === $robotIsTrue) {
            $this->assign('robot',1);
        }

        if(!empty($this->cityInfo["bm"])){
            if(!$robotIsTrue){
                $this->assign("headerTmp",1);
            }
        }

        $this->assign("header_search",3);
        $this->assign("tabIndex",5);
        //添加顶部搜索栏信息
        $this->assign('serch_uri','gonglue/search');
        $this->assign('serch_type','装修攻略');
        $this->assign('holdercontent','了解相关的装修资讯知识');
    }

    public function index(){
        $preg = '/\/gonglue\/(\w+)\/(\d+).html/';
        $arr = [];
        preg_match($preg, $_SERVER['REQUEST_URI'], $arr);
        if($arr[1] == 'jiaotixian'){
            header( "HTTP/1.1 301 Moved Permanently");
            header("Location: ".$this->SCHEME_HOST['scheme_full'] . C("QZ_YUMINGWWW") . '/gonglue/tijiaoxian/'. $arr[2] . '.html');
            exit;
        }

        //a.18.09.16需求， 选材导购栏目详情页-异常显示处理
        if($arr[1] == 'xcdg' && $arr[2]){
            $this->_error();
        }
        //跳转到手机端
        if (ismobile()) {
            header( "HTTP/1.1 301 Moved Permanently");
            header("Location: ".$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . $_SERVER['REQUEST_URI']);
            exit();
        }
        $id = I("get.id");


        //判断是否是搜索引擎蜘蛛
        //导入扩展文件
        if($this->robotIsTrue === false){
            $ips = OP("ignore_ips");
            $iparr = explode(',', $ips);
            foreach ($iparr as $k => $v) {
                $iparr[$k] = ip2long($v);
            }
            import('Library.Org.Util.App');
            $app = new \App();
            $ip = $app->get_client_ip();
            //本地IP  223.112.69.58 屏蔽掉，不统计
            $ip1 = ip2long($ip);
            //$ip2 = ip2long("223.112.69.58");
            if(!in_array($ip1, $iparr)){
                $today = date("Y-m-d",time());
                $timeend = strtotime(($today.' 23:59:59'));
                $timelong = $timeend - time();
                //S('Cache:gonglue:'.$_SERVER['REMOTE_ADDR'].$id,'1',900);
                $status = S('Cache:gonglue:'.$ip.$id);
                if($status != 1){
                    //没有访问记录，浏览量+1，生成新缓存（根据ip.文章ID）
                    D("WwwArticle")->updateRealView($id);
                    S('Cache:gonglue:'.$ip.$id,1,$timelong);
                    //来自搜索引擎自然流量IP
                    $isNatureSearch = D("Common/Logic/NatureSearchVisitLogic")->isFromNatureSearch();
                    if($isNatureSearch === true) {
                        D("WwwArticle")->updateSearchIP($id);
                    }
                }
            }
        }

        if(!empty($id) && preg_match('/\d+/i', I("get.id"))){

            $category =  I("get.category");
            //获取微信专享文章
            $info["weixinarticle"] = S("Home:Article:index:weixinarticle");
            if (!$info["weixinarticle"]) {
                $info["weixinarticle"] = D('WeixinArticle')->getWeixinArticleList(1);
                S("Home:Article:index:weixinarticle", $info["weixinarticle"], 600);
            }

            //获取最新文章
            $info["newarticles"] = S("Cache:a:new");
            if (!$info["newarticles"]) {
                $info["newarticles"] = D("WwwArticle")->getNewArticle(8);
                S("Cache:a:new",$info["newarticles"],3600);
            }

            //获取相关标签
            $thematicLogic = new ThematicWordsLogicModel();
            $keywords = $thematicLogic->getContentLabels($id,1,5);

            //大家都在问
            $articleLogic = new ArticleLogicModel();
            $info['ask'] = S('Cache:Article:Index:EveryoneAsk' . $id);
            if (!$info['ask']) {
                $ask = $articleLogic->getAskListByKeywords($keywords);
                $info['ask'] = $ask;
                S('Cache:Article:Index:EveryoneAsk' . $id, $ask, 900);
            }

            //获取报价模版
            $this->assign("order_source",12);
            $t = T("Common@Order/orderTmp");
            $orderTmp = $this->fetch($t);
            $this->assign("orderTmp",$orderTmp);

            //获取黄历报价模版
            $this->assign("hlBaoJia",$this->fetch(T("Common@Order/hlbaojia2")));

            //获取热门标签
            $info["tags"] = S("Cache:a:tags");
            if (!$info["tags"]) {
                $info["tags"] = D("Tags")->getHotTags(1,18);
                S("Cache:a:tags",$info["tags"],3600*3);
            }


            //获取分类
            $cache_cate_key_postfix = $category . ':' . $id;
            $cate = S('Cache:Home:Article:Index:cate:' . $cache_cate_key_postfix);
            if (!$cate) {
                if(!empty($category) && strtolower($category) != "history"){
                    //新分类
                    $cate = D("WwwArticleClass")->getArticleClassByShortname($category);
                }else{
                    //老版文章
                    //获取根据文章的编号获取老版的分类
                    $cate = D("WwwArticleClass")->getArticleClassByArticleId($id,'old');
                }

                S('Cache:Home:Article:Index:cate:' . $cache_cate_key_postfix, $cate, 900);
            }

            if(empty($cate)){
                $this->_error();
                die();
            }

            $articleInfo = S('Cache:articleInfo:'.$category.':'.$id);
            if (!$articleInfo) {
                //文章内容
//                $article = $this->getArticleInfo($id,$cate["id"]);
                $article = $articleLogic->getArticleInfo($id,$cate["id"]);
                $articleInfo["article"]['now'] = $article;//兼容老数据
                S("Cache:articleInfo:like:".$id,$article["likes"],1000);
                S('Cache:articleInfo:'.$category.':'.$id,$articleInfo,120);
            }
            $articleInfo["article"]["now"]["likes"] = S("Cache:articleInfo:like:".$id);

            //相关攻略(该文章所属子分类下其他文章)
            $articleInfo['about'] = S('Cache:Home:Article:Index:About:' . $id);
            if (empty($articleInfo['about'])) {
                $aboutArticle = [];
                $articleInfo['about'] = $articleLogic->getArticleByPidClass($cate['shortname'], 7, 3);
                foreach ($articleInfo['about'] as $key => $value) {
                    //多查一条,过滤自身
                    if ($value['id'] != $articleInfo["article"]["now"]['id'] && count($aboutArticle) < 6) {
                        $aboutArticle[] = $value;
                    }
                }
                $articleInfo['about'] = $aboutArticle;
                S('Cache:Home:Article:Index:About:' . $id, $articleInfo['about'], 900);
            }

            if(empty($articleInfo['article']['now']['id'])){
                $this->_error();
                die();
            }

            //获取该分类的分类信息
            $keys["title"] = $articleInfo["article"]["now"]["title"];
            $keys["keywords"] =$articleInfo["article"]["now"]["keywords"];
            $keys["description"] = $articleInfo["article"]["now"]["subtitle"];

            //获取canonical属性
            $articleInfo['canonical'] =  $this->SCHEME_HOST['scheme_full'] . C('QZ_YUMINGWWW').'/gonglue/'.$category.'/'.$id.'.html';

            $articleInfo["collect"] = false;
            //查询用户是否关注过该文章
            if(isset($_SESSION["u_userInfo"])){
                $count =  D("Usercollect")->getCollectCount(I("get.id"),$_SESSION['u_userInfo']['id'],1);
                if($count > 0){
                    $articleInfo["collect"] = true;
                }
            }

            // 获取看过本文的还看过的文章
            $recommend = S('Article:Index:RecommendList:v1:'.$articleInfo["article"]["now"]['id']);
            if(empty($recommend)){
                $search = new ElasticSearchServiceModel();
                $searchArticles = $search->searchWwwArticle($articleInfo["article"]["now"]["title"],5);
                foreach ($searchArticles as $key => $value) {
                    //多查一条,过滤自身
                    if($value['id'] != $articleInfo["article"]["now"]['id']){
                        $recommend[] = $searchArticles[$key];
                    }
                }
                S('Article:Index:RecommendList:v1:'.$articleInfo["article"]["now"]['id'],$recommend, 120);
            }
            $articleInfo["recommendArticles"] = $recommend;

            //获取评论模板
            //获取评论人数
//            $count = D("CommentFull")->getCommentCount("wwwarticle",$id);
//            $this->assign("rel_number",$count);
            $this->assign("rel_id",$id);
            $this->assign("module","wwwarticle");
            $t = T("Common@Comment/reply");
            $tmp = $this->fetch($t);
            $this->assign("reply",$tmp);
            //获取最新的2条评论
//            $comments = $this->getNewCommentsList("wwwarticle",$id,2);
//            if (empty($comments)) {
//                $this->assign("comments",$comments);
//            } else {
//                $this->assign("comments",$comments);
//                $t = T("Common@Comment/comment");
//                $tmp = $this->fetch($t);
//                $this->assign("comments",$tmp);
//            }

            //获取推荐文章的数量
            $this->assign("countKey", count($articleInfo['recommendArticles']));
            $this->assign("articleInfo",$articleInfo);

            //更新文章阅读量
            D("WwwArticle")->updatePv($id);

            //流量部推广统计
            $this->promoStats($id);

            //底部标签组
            $info['bottomtags'] = S('Cache:WwwArticleTags:Bottom');
            if (!$info['bottomtags']) {
                $articleTagsModel = new \Common\Model\Logic\ArticleTagLogicModel();
                $info['bottomtags'] = $tags = $articleTagsModel->getTagData(0,1);
                if (!empty($tags)) {
                    S('Cache:WwwArticleTags:Bottom', $tags, 900);
                }
            }

            // 最新美图标签组
            $info['newestMeitu'] = S('Cache:WwwArticleTags:newestMeitu');
            if (!$info['newestMeitu']) {
                $info['newestMeitu'] = $thematicLogic->getNewTypeWords(2, 30);
                S('Cache:WwwArticleTags:newestMeitu', $info['newestMeitu'], 900);
            }
            $info["newestMeituCount"] = count($info['newestMeitu']);


            //最热美图
            $info["hotmeitu"] = S('Cache:WwwArticleTags:hotmeitu');
            if (!$info['hotmeitu']) {
                $info['hotmeitu'] = $thematicLogic->getTypeHotList(2, 30);
                S('Cache:WwwArticleTags:hotmeitu', $info['hotmeitu'], 900);
            }
            $info["hotmeituCount"] = count($info['hotmeitu']);


            //右侧标签组
            $info['righttags'] = S('Cache:WwwArticleTags:Right');
            if (!$info['righttags']) {
                $articleTagsModel = new \Common\Model\Logic\ArticleTagLogicModel();
                $info['righttags'] = $tags = $articleTagsModel->getTagData(0,2);
                if (!empty($tags)) {
                    S('Cache:WwwArticleTags:Right', $tags, 900);
                }
            }

            //判断是否选中
            $choose_menu = $this->selectProcess();
            if($choose_menu) $this -> assign('choose_gonglue', 'lc');
            //获取底部弹层
            $t = T("Common@Order/zb_bottom_b");

            //获取先关标签
            $thematicLogic = new ThematicWordsLogicModel();
            $keywords = $thematicLogic->getContentLabels($id,1,5);

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
            $baidu['str'] = mb_substr(strip_tags($articleInfo['article']['now']['short_introduction']), 0, 100, 'utf-8');
            $baidu['title'] = $articleInfo["article"]['now']['title'];
            $optimee =date("Y-m-d",$articleInfo["article"]['now']['addtime']);
            $optimes =date("H:i:s",$articleInfo["article"]['now']['addtime']);

            $baidu['optime'] = $optimee."T".$optimes;
            $baidu['addtime'] = $optimee."T".$optimes;
            $this->assign("baidu",$baidu);

            // 1353
            $images =explode(",", $articleInfo["article"]['now']['imgs']);
            foreach ($images as &$v) {
                $v = str_replace('-s3.jpg', '-s5.jpg', changeImgUrl($v));
            }
            $this->assign("images",$images);

            $adv_bottom = $this->fetch($t);
            $this->assign("adv_bottom",$adv_bottom);
            $this->assign("keywords",$keywords);
            $this->assign("keys",$keys);
            $this->assign("info",$info);
            $this->assign("cate",$cate);

            $this->display();
        }else{
            $this->_error();
        }
    }
    /**
     * 只有装修流程祥情页才默认选中
     */
    protected function selectProcess()
    {
        $process = ['shoufang','gongsi','shejiyusuan','kanfengshui','xuancai','chagai','shuidian','fangshui','niwa','mugong','youqi','jianche','baoyang','peishi','jjsh'];
        $parse = explode('/', parse_url($_SERVER['REQUEST_URI'])['path']);
        if(in_array($parse[2], $process)) return true;
    }
    /**
     * 获取主站文章微信二维码
     */
    public function getWwwArticleErWeiMa()
    {
        $id = I('get.id');
        if (!empty($id)) {
            import('Library.Org.Util.Weixin');
            $weixin = new \Weixin('ZGZX');
            $ticket = $weixin->getTicket($weixin->implodeEventKey('1001', $id));
            if (empty($ticket['qrcode_url'])||$ticket['qrcode_url'] == 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='){
                $this->ajaxReturn(array('status' => 0, 'info' => '二维码获取失败'));
            }else{
                $this->ajaxReturn(array('status' => 1, 'data' => $ticket['qrcode_url']));
            }
        }
        $this->ajaxReturn(array('status' => 0, 'info' => 'ID为空'));
    }

    /**
     * 获取微信文章微信二维码
     */
    public function getWeixinArticleErWeiMa()
    {
        $id = I('get.id');
        if (!empty($id)) {
            import('Library.Org.Util.Weixin');
            $weixin = new \Weixin('ZGZX');
            $ticket = $weixin->getTicket($weixin->implodeEventKey('1002', $id));
            if (empty($ticket['qrcode_url'])||$ticket['qrcode_url'] == 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='){
                $this->ajaxReturn(array('status' => 0, 'info' => '二维码获取失败'));
            }else{
                $this->ajaxReturn(array('status' => 1, 'data' => $ticket['qrcode_url']));
            }
        }
        $this->ajaxReturn(array('status' => 0, 'info' => 'ID为空'));
    }


    //流量部推广统计
    public function promoStats($id){
        //获取Cookie
        $isMark = cookie('contentPromoMark');
        //如果Cookie不存在
        if(empty($isMark['module'])){
            //过期时间 = 今天最后一秒时间戳 - 当前时间戳
            $expireTime = strtotime(date('Y-m-d').' 23:59:59') - time();
            $cookieVar = array('module' => 'article','id' => $id);
            //指定cookie保存时间
            cookie('contentPromoMark',$cookieVar, array('expire' => $expireTime,'domain' => '.'.APP_HTTP_DOMAIN));
        }
    }

    /**
     * 更多评论
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function more()
    {
        $id = I("get.id");
        $category =  I("get.category");
        if(!empty($category) && strtolower($category) != "history"){
            //新分类
            $cate = D("WwwArticleClass")->getArticleClassByShortname($category);
        }else{
            //老版文章
            //获取根据文章的编号获取老版的分类
            $cate = D("WwwArticleClass")->getArticleClassByArticleId($id,'old');
        }

        if(empty($cate)){
            $this->_error();
            die();
        }

        //获取文章信息
        $article = $this->getArticleInfo($id,$cate["id"]);
        $this->assign("article",$article);
        //获取评论模板
        $count = D("CommentFull")->getCommentCount("wwwarticle",$id);
        $this->assign("module","wwwarticle");
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
        $comments = $this->getCommentsList($pageIndex,$pageCount,"wwwarticle",$id);
        $this->assign("module","wwwarticle");
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
        $this->assign("order_source",140);
        $t = T("Common@Order/orderTmp");
        $orderTmp = $this->fetch($t);
        $this->assign("orderTmp",$orderTmp);

        $this->display();
    }

    /**
     * 获取文章的评论
     * @return [type] [description]
     */
    public function getComment(){
        if($_POST){
            $id = I("post.id");
            $index = I("post.index");
            $result = $this->getComments($id,$index+1);
            $this->ajaxReturn(array("data"=>$result,"info"=>"","status"=>1));
        }
    }

    /**
     * 添加文章评论
     */
    public function addArticleComment(){
        if($_POST){
            $code = I("post.code");
            //检验验证码
            if(check_verify($code)){
                import('Library.Org.Util.Fiftercontact');
                $filter = new \Fiftercontact();
                import('Library.Org.Util.App');
                $app = new \App();
                $id = $_POST["id"];
                $data = array(
                    "text"=>$filter->filter_text(I("post.content")),
                    "articleid"=>I("post.id"),
                    "time"=>time(),
                    "img"=>"",
                    "uname"=>"齐装网网友",
                    "userid"=>"0",
                    "isverify"=>1,
                    "ip"=>$app->get_client_ip()
                );
                //用户登录后
                if(isset($_SESSION["u_userInfo"])){
                    // 只有业主可以评论/收藏
                    if($_SESSION['u_userInfo']['classid'] != 1){
                        $this->ajaxReturn(['data' => '', 'info' => '该账号无操作权限~', 'status' => 0]);
                    }

                    $data["img"] = $_SESSION["u_userInfo"]["logo"];
                    $data["userid"] = $_SESSION["u_userInfo"]["id"];
                    $data["uname"] = $_SESSION["u_userInfo"]["name"];
                }
                //添加评论
                $result = D("WwwArticleComment")->addComment($data);
                if($result !== false){
                    $tmp = $this->getComments(I("post.id"),1);
                    $this->ajaxReturn(array("data"=>$tmp,"info"=>"","status"=>1));
                }else{
                    $this->ajaxReturn(array("data"=>"","info"=>"评论添加失败,请稍后再试!","status"=>0));
                }
            }
            $this->ajaxReturn(array("data"=>"","info"=>"验证码输入错误,请刷新验证码","status"=>0));
        }
        $this->ajaxReturn(array("data"=>"","info"=>"非法提交","status"=>0));
    }

    public function dolike()
    {
        if ($_POST) {
            $id = I("post.id");
            $i = D("WwwArticle")->setLikes($id);
            if ($i !== false) {
                $likes = S("Cache:articleInfo:like:".$id);
                $likes ++ ;
                S("Cache:articleInfo:like:".$id,$likes,1000);
                $this->ajaxReturn(array("status"=>1));
            }
            $this->ajaxReturn(array("status"=>0));
        }
    }

    /**
     * 获取验证码
     */
    public function verify(){
        getVerify("",4,120,30);
    }

    //获取热门回答
    private function getHotAsk($num){
        $result = S('Cache:Ask:articleHotAsk');
        if(empty($result)){
            S('Cache:Ask:articleHotAsk',null);
            $result = D("Ask")->getHotAsk(50);
            S('Cache:Ask:articleHotAsk',$result,900);
        }
        shuffle($result);
        return array_slice($result,0,$num);
    }

    //获取装修日记
    private function getHotDiary($num){
        $result = S('Cache:Diary:articleHotDiary');
        if(empty($result)){
            S('Cache:Diary:articleHotDiary',null);
            $result = D('Diary')->getHotDiaryUser(30);
            S('Cache:Diary:articleHotDiary',$result,900);
        }
        shuffle($result);
        return array_slice($result,0,$num);
    }

    private function getRankMeitu($limit){
        $result = S('Cache:Article:RankMeitu');
        if(empty($result)){
            S('Cache:Article:RankMeitu',null);
            $result = D("Meitu")->getNewMeitu(30);
            S('Cache:Article:RankMeitu',$result,900);
        }
        shuffle($result);
        return array_slice($result,0,$limit);
    }

    /**
     * 获取文章信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    private function getArticleInfo($id,$category){
        $result = D("WwwArticle")->getArticleInfoById($id,$category);
        $article = array();
        foreach ($result as $key => $value) {
            if(!array_key_exists($value["action"], $article)){
                $article[$value["action"]] = array();
            }
            if(empty($value["shortname"])){
                $value["shortname"] = "history";
                $value["classname"] ="历史资讯";
                $value["title"] = str_replace("_齐装网", "", $value["title"]);
            }
            $article[$value["action"]] = $value;
        }

        foreach ($article as $key => $value) {
            if($key == "now"){
                //处理文章中的图片
                $pattern ='/<img.*?\/>/is';
                preg_match_all($pattern,$value["content"], $matches);
                if(count($matches[0]) > 0){
                    foreach ($matches[0] as $k => $val) {
                        $pattern ='/src=[\"|\'](.*?)[\"|\']/is';
                        preg_match_all($pattern,$val, $m);
                        foreach ($m[1] as $j => $v) {
                            if(!strpos($v,C('QINIU_DOMAIN'))){
                                $path = C('QINIU_SCHEME')."://".C('STATIC_HOST1').$v;
                                $article[$key]["content"]  = str_replace($v,$path,$article[$key]["content"]);
                            }
                            //去水印
                            if(strpos($v, '-s3.')) {
                                $path = str_replace('-s3.', '-s5.', $v);
                                $article[$key]["content"] = str_replace($v, $path, $article[$key]["content"]);
                            }
                        }
                    }
                }
            }
        }

        if (isset($article["now"])) {
            //查询文章关键字，替换成内链
            $keywords = D("Wwwarticlekeywords")->getKeywordsRelate($id,"wwwarticle");
            foreach ($keywords as $key => $value) {
                $list[] = "/".trim($value["name"])."/";
            }

            //抽出文章中的所有链接，避免替换链接出现重叠现象(链接套链接)
            $linkPattern = '/<a.*?>.*?<\/a>/i';
            preg_match_all($linkPattern, $article["now"]["content"], $linkMatches);
            if(count($linkMatches[0]) > 0){
                foreach ($linkMatches[0] as $key => $value) {
                    //将图片替换成变量占位符
                    $article["now"]["content"] = str_replace($value, "#&!&#", $article["now"]["content"]);
                    $replaceLink[] = $value;
                }
            }

            //抽出文章中的图片
            $pattern ='/<img.*?\/>/i';
            preg_match_all($pattern, $article["now"]["content"], $matches);
            if(count($matches[0]) > 0){
                foreach ($matches[0] as $key => $value) {
                    //将图片替换成变量占位符
                    $article["now"]["content"] = str_replace($value, "%s", $article["now"]["content"]);
                    $replaceImg[] = $value;
                }
            }

            foreach ($list as $key => $value) {
                preg_match_all($value,$article["now"]["content"],$matches);
                if(count($matches[0]) > 0){
                    $link =  "<a href='".$keywords[$key]["href"]."' target='_blank' class='inlink-word-color'>".$keywords[$key]["name"]."</a>";
                    $article["now"]["content"] = preg_replace($value,$link,$article["now"]["content"],1);
                }
            }
            //将所有的图片依次填充到原来位置
            foreach ($replaceImg as $key => $value) {
                $article["now"]["content"] = preg_replace("/\%s/",$value,$article["now"]["content"],1);
            }

            //将所有的链接依次填充到原来位置
            foreach ($replaceLink as $key => $value) {
                $article["now"]["content"] = preg_replace("/#&!&#/",$value,$article["now"]["content"],1);
            }
        }
        return $article;
    }

    /**
     * 获取推荐的文章列表
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    private function getTopArticleInfo($id){
        $result = D("WwwArticle")->getTopArticleInfo(3,$id);
        return $result;
    }

    /**
     * 获取文章评论列表
     * @return [type] [description]
     */
    private function getComments($id ='',$pageIndex = 1)
    {
        $pageIndex = intval($pageIndex)<=0?1:intval($pageIndex);
        $pageCount = 3;
        $count = D("WwwArticleComment")->getCommentListCount($id);
        if($count>0){
            $comments = D("WwwArticleComment")->getCommentList($pageCount,($pageIndex-1)*$pageCount,$id);
            $this->assign("comments",$comments);
        }

        $tmp = $this->fetch("Article/commentTpl");
        return array("tmp"=>$tmp,"totalCount"=>$count,"count"=>count($comments),"index"=>$pageIndex);
    }

    private function getRecommendArticles($classid,$limit){
        //获取相同分类的点击量最高的文章
        $result = S('Cache:Article:Recommend');
        if(empty($result)){
            S('Cache:Article:Recommend',null);
            $result = D("WwwArticle")->getRecommendArticles($classid);
            S('Cache:Article:Recommend',$result,900);
        }
        if(count($result) > 0){
            shuffle($result);
            $result = array_slice($result,0,$limit);
        }
        return $result;
    }

    /**
     * [getRelationArticle 获取相关文章]
     * @param  string  $type     [文章类型]
     * @param  integer $limit    [获取数目]
     * @param  integer $tagid    [标签id]
     * @param  string  $keywords [关键字]
     * @return [type]            [description]
     */
    private function getRelationArticle($type = '', $limit = 9, $notid = 0, $tagid = 0, $keywords= '')
    {
        //如果有标签id或者关键字，先利用分类和(标签或者关键字)搜索,不考虑分类
        //文章属于不同分类，但是标签或者关键字只要有一个相同，就可以调用
        //如果文章有多个关键字或者标签（有空格需要过滤空格），只要其中有一个相同，就调用
        if(!empty($tagid) || !empty($keywords)){
            $string = '';
            //拼接标签查询
            if(!empty($tagid)){
                $tags = array_filter(explode(',', $tagid));
                foreach ($tags as $key => $value) {
                    $string = $string . 'FIND_IN_SET("' . $value . '",a.tags) OR ';
                }
            }
            //拼接关键字查询
            if(!empty($keywords)){
                $keys = array_filter(explode(',', $keywords));
                foreach ($keys as $key => $value) {
                    $string = $string . 'FIND_IN_SET("' . $value . '",a.keywords) OR ';
                }
            }
            $string = rtrim($string, 'OR ');
            if(!empty($string)){
                $map = array(
                    [
                        '_string' => $string,
                        '_logic' => 'OR'
                    ]
                );
            }
        }

        if(empty($limit)){
            $limit = 6;
        }

        //排除某个标签，避免调用了相同文章
        if(!empty($notid)){
            $map['a.id'] = array('NEQ',$notid);
        }
        $result = D("WwwArticle")->getArticleListByMap($map, $limit);

        //如果更加标签或关键字获取的数量少于需要的数量，再次根据分类来获取
        $count = $limit - count($result);
        $other = [];
        if($count > 0){
            $map = [];
            if(!empty($type)){
                $map['b.class_id'] = $type;
            }
            //排除相同ID的文章
            if(!empty($result)){
                $ids = '';
                if(!empty($notid)){
                    $ids = $notid . ',';
                }
                foreach ($result as $key => $value) {
                    $ids = $ids . $value['id'] . ',';
                }
                $map['a.id'] = array('NOT IN',trim($ids,','));
            }
            $other = D("WwwArticle")->getArticleListByMap($map, $count);
        }
        $return = array_merge($result,$other);
        return $return;
    }

    private function getRecommendCases($cs,$classid,$limit){
        $cases = D("Cases")->getRecommendCases($cs,$classid,$limit);
        shuffle($cases);
        $cases = array_slice($cases,0,$limit);
        return $cases;
    }

    /**
     * 获取评论列表
     * @param  [type] $ref_id [description]
     * @param  [type] $limit  [description]
     * @return [type]         [description]
     */
    private function getNewCommentsList($module,$ref_id,$limit)
    {
        $result = D("CommentFull")->getNewCommentsList($module,$ref_id,$limit);
        foreach ($result as $key => $value) {
            if (!array_key_exists($value["id"],$list)) {
                $list[$value["id"]] = array(
                    "logo" => $value["logo"],
                    "username" => $value["username"],
                    "time" => $value["time"],
                    "likes" => $value["likes"],
                    "dislike" => $value["dislike"],
                    "id" => $value["id"],
                    "ref_id" => $value["ref_id"],
                    "content" => $value["content"]
                );
            }

            if (!empty($value["reply_name"])) {
                $list[$value["id"]]["reply"][] = array(
                    "logo" => $value["logo"],
                    "username" => $value["reply_name"],
                    "time" => $value["reply_time"],
                    "likes" => $value["reply_likes"],
                    "dislike" => $value["reply_dislike"],
                    "content" => $value["reply_content"]
                );
            }
        }
        return $list;
    }

    /**
     * 获取评论列表
     * @param  [type] $pageIndex [description]
     * @param  [type] $pageCount [description]
     * @param  [type] $module    [description]
     * @param  [type] $ref_id    [description]
     * @return [type]            [description]
     */
    private function getCommentsList($pageIndex,$pageCount,$module,$ref_id)
    {
        $count = D("CommentFull")->getCommentListCount($module,$ref_id);
        if ($count > 0) {
            import('Library.Org.Page.LitePage');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \LitePage($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();
            $result = D("CommentFull")->getCommentList(($page->pageIndex-1)*$pageCount,$pageCount,$module,$ref_id);
            foreach ($result as $key => $value) {
                if (!array_key_exists($value["id"],$list)) {
                    $list[$value["id"]] = array(
                        "logo" => $value["logo"],
                        "username" => $value["username"],
                        "time" => $value["time"],
                        "likes" => $value["likes"],
                        "dislike" => $value["dislike"],
                        "id" => $value["id"],
                        "ref_id" => $value["ref_id"],
                        "content" => $value["content"]
                    );
                }

                if (!empty($value["reply_name"])) {
                    $list[$value["id"]]["reply"][] = array(
                        "username" => $value["reply_name"],
                        "time" => $value["reply_time"],
                        "likes" => $value["reply_likes"],
                        "dislike" => $value["reply_dislike"],
                        "content" => $value["reply_content"]
                    );
                }
            }
            return array("list"=>$list,"page"=>$pageTmp,"count"=>$count);
        }
    }
    /**
     * 获取关键列表
     * @param  string $value [description]
     * @return [type]        [description]
     */
    private function getSpecialkeyword($pageIndex,$pageCount,$keyword_module,$is_hot)
    {
        $count = D('Common/Logic/SpecialkeywordLogic')->getSpecialkeywordCount($keyword_module,$is_hot);
        if ($count > 0) {
            import('Library.Org.Page.LitePage');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \LitePage($pageIndex,$pageCount,$count,$config);
            $show    = $page->show();//,$p->firstRow,$p->listRows
            $list = D('Common/Logic/SpecialkeywordLogic')->getSpecialkeywordList($keyword_module, $is_hot,($page->pageIndex-1)*$pageCount,$pageCount);
            foreach ($list as $key => $value) {
                if($value["keyword_module"] == 1){
                    $list[$key]["url"] = $this->SCHEME_HOST['scheme_full'] . C("QZ_YUMINGWWW") ."/gonglue/zs/".$value["href"]."/";
                }else{
                    $list[$key]["url"] = $this->SCHEME_HOST['scheme_full'] . C("QZ_YUMINGWWW") . "/baike/zs/".$value["href"]."/";
                }
            }
        }
        return array("list"=>$list,"page"=>$show);
    }

}
