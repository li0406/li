<?php
/**
 * 移动版文章页
 */
namespace Mobile\Controller;
use Common\Model\Logic\ThematicWordsLogicModel;
use Common\Model\Service\ElasticSearchServiceModel;
use Mobile\Common\Controller\MobileBaseController;
class ArticleController extends MobileBaseController{

    public function index(){
        $preg = '/\/gonglue\/(\w+)\/(\d+).html/';
        $arr = [];
        preg_match($preg, $_SERVER['REQUEST_URI'], $arr);
        if($arr[1] == 'jiaotixian'){
            $SCHEME_HOST = $this->SCHEME_HOST;
            header( "HTTP/1.1 301 Moved Permanently");
            header("Location: ". $SCHEME_HOST['scheme_full'].$SCHEME_HOST['host'] . '/gonglue/tijiaoxian/'. $arr[2] . '.html');
            exit;
        }
        //a.18.09.16需求， 选材导购栏目详情页-异常显示处理
        if($arr[1] == 'xcdg' && $arr[2]){
             $this->_error();
        }
        if ($arr[1] == "jxwd" && $arr[2])
        {
            $jsshow = 2;        // 不显示
        } else {
            $jsshow = 1;
        }

        $this->assign("jsshow", $jsshow);

        $category =  I("get.category");
        $id = I("get.id");

        //判断是否是搜索引擎蜘蛛
        $robotIsTrue = B("Common\Behavior\RobotCheck");
        //$robotIsTrue = true; //debug 警告线上应该为注释状态
        if ($robotIsTrue === false) {
            //导入扩展文件
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
                $status = S('Cache:gonglue:' . $ip . $id);
                if($status != 1){
                    //没有访问记录，浏览量+1，生成新缓存（根据ip.文章ID）
                    D("WwwArticle")->updateRealView($id);
                    S('Cache:gonglue:' . $ip . $id, 1, $timelong);
                    //来自搜索引擎自然流量IP
                    // https://m.baidu.com/from=0/b
                    $isNatureSearch = D("Common/Logic/NatureSearchVisitLogic")->isFromNatureSearch();
                    if($isNatureSearch === true) {
                        D("WwwArticle")->updateSearchIP($id);
                    }
                }
            }
        }

        if (preg_match('/\d+/i', I("get.id"))) {
            //获取分类
            $categoryClass = S('Cache:M:Article:Index:categoryClass:' . $category);
            if (!$categoryClass) {
                if(!empty($category) && strtolower($category) != "history"){
                    //新分类
                    $categoryClass = D("WwwArticleClass")->getArticleClassByShortname($category);
                }else{
                    //老版文章
                    //获取根据文章的编号获取老版的分类
                    $categoryClass = D("WwwArticleClass")->getArticleClassByArticleId($id,'old');
                }

                S('Cache:M:Article:Index:categoryClass:' . $category, $categoryClass, 900);
            }

            if(empty($categoryClass)){
                $this->_error();
                die();
            }

            $articleInfo = S('Cache:Mobile:ArticleInfo:v1:'.$category.':'.$id);
            if(empty($articleInfo)){
                //文章内容
                $article = $this->getArticleInfo($id,$categoryClass["id"]);
                $articleInfo["article"] = $article;

                S('Cache:Mobile:ArticleInfo:likes:'.$id, $articleInfo["article"]["likes"],120);
                S('Cache:Mobile:ArticleInfo:v1:'.$category.':'.$id,$articleInfo,120);
            }

            //获取推荐文章，缓存key和PC端的一样-m.1.5.0 移动端-装修攻略文章终端页数据项逻辑调整
            $recommend = S('Article:Index:RecommendList:v1:'.$articleInfo["article"]['id']);
            if(empty($recommend)){
                $search = new ElasticSearchServiceModel();
                $searchArticles = $search->searchWwwArticle($articleInfo["article"]["title"]);
                foreach ($searchArticles as $key => $value) {
                    //多查一条,过滤自身
                    if($value['id'] != $articleInfo["article"]['id'] ){
                        $recommend[] = $searchArticles[$key];
                    }
                }
                S('Article:Index:RecommendList:v1:'.$articleInfo["article"]['id'],$recommend,120);
            }
            $articleInfo["recommendArticles"] = $recommend;

            if(empty($articleInfo["article"])){
                $this->_error();
            }

            $articleInfo["article"]["likes"] =  S('Cache:Mobile:ArticleInfo:likes:'.$id);
            $articleInfo['article']['content'] = str_replace('www.qizuang.com/gonglue','m.qizuang.com/gonglue',$articleInfo['article']['content']);

            if (empty($articleInfo["article"]["from_ruanwen"])) {
                //处理文章中的图片的宽高
                $pattern ='/<img.*?\/>/is';
                preg_match_all($pattern,$articleInfo['article']['content'], $matches);
                if(count($matches[0]) > 0){
                    foreach ($matches[0] as $k => $val) {
                        $pattern ='/src=[\"|\'](.*?)[\"|\']/is';
                        preg_match_all($pattern,$val, $m);
                        foreach ($m[1] as $j => $v) {
                            if(!strpos($v,C('QZ_YUMING'))){
                                $path =$this->SCHEME_HOST['scheme_full'].C('STATIC_HOST1').$v;
                                $articleInfo['article']['content']  = str_replace($v,$path,$articleInfo['article']['content']);
                            }
                        }

                        $pattern = '/width\=[\"|\'].*?[\"|\']/is';
                        $articleInfo['article']['content'] = preg_replace_callback($pattern, function(){
                            return "";
                        }, $articleInfo['article']['content']);

                        $pattern = '/height\=[\"|\'].*?[\"|\']/is';
                        $articleInfo['article']['content'] = preg_replace_callback($pattern, function(){
                            return "";
                        }, $articleInfo['article']['content']);
                    }

                    foreach ($matches[0] as $k => $val) {
                        $pattern ='/src=[\"|\'](.*?)[\"|\']/is';
                        preg_match_all($pattern,$val, $m);
                        foreach ($m[1] as $j => $v) {
                            if(!strpos($v,C('QZ_YUMING'))){
                                $path = $this->SCHEME_HOST['scheme_full'].C('STATIC_HOST1').$v;
                                $articleInfo['article']['content']  = str_replace($v,$path,$articleInfo['article']['content']);
                            }
                        }

                        $pattern = '/width: [\d]+px;/is';
                        $articleInfo['article']['content'] = preg_replace_callback($pattern, function(){
                            return "";
                        }, $articleInfo['article']['content']);

                        $pattern = '/height: [\d]+px;/is';
                        $articleInfo['article']['content'] = preg_replace_callback($pattern, function(){
                            return "";
                        }, $articleInfo['article']['content']);
                    }
                }
            }

            //底部标签
            $articleInfo['tags'] = S('Cache:Mobile:Gonglue:ArticleTags');
            if (!$articleInfo['tags']) {
                $articleTagsModel = new \Common\Model\Logic\ArticleTagLogicModel();
                $articleInfo['tags'] = $tags = $articleTagsModel->getTagData(0);
                if (!empty($tags)) {
                    S('Cache:Mobile:Gonglue:ArticleTags', $tags, 900);
                }
            }
            //更新文章阅读量
            D("WwwArticle")->updatePv($id);
            //流量部推广统计
            $this->promoStats($id);
            $basic["body"]["title"] = "装修攻略";
            $basic["head"]["title"] = $articleInfo["article"]["title"] . '-齐装网';
            $basic["head"]["keywords"] =$articleInfo["article"]["keywords"];
            $basic["head"]["description"] = $articleInfo["article"]["subtitle"];

            //百度官方号需求
            $baidu['url'] = $this->SCHEME_HOST['scheme_full'].$this->SCHEME_HOST['host'].$_SERVER['REQUEST_URI'];
            $baidu['str'] = mb_substr(strip_tags($articleInfo['article']['content']), 0, 100, 'utf-8');
            if(empty($articleInfo['article']['addtime'])){
                $optimee =date("Y-m-d",$articleInfo["article"]['optime']);
                $optimes =date("H:i:s",$articleInfo["article"]['optime']);
            }else{
                $optimee =date("Y-m-d",$articleInfo["article"]['addtime']);
                $optimes =date("H:i:s",$articleInfo["article"]['addtime']);
            }
            $baidu['optime'] = $optimee."T".$optimes;
            $this->assign("baidu",$baidu);

            /*生成canonical标签属性值*/
            if(!isset($_GET['a1'])){
                $SCHEME_HOST = $this->SCHEME_HOST;
                $articleInfo['canonical'] = $SCHEME_HOST['scheme_full'].'www.'.$SCHEME_HOST['domain'].$_SERVER['REQUEST_URI'];
            }
            $articleInfo['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_cityInfo.id'))[0];

            $images =explode(",", $articleInfo["article"]['imgs']);
            foreach ($images as &$v) {
                $v = str_replace('-s3.jpg', '-s5.jpg', $v);
            }
            //去除文章详情页中的图片的水印
            $articleInfo['article']['content'] = str_replace("-s3.jpg", "-s5.jpg", $articleInfo['article']['content']);
            //获取先关标签
            $thematicLogic = new ThematicWordsLogicModel();
            $keywords = $thematicLogic->getContentLabels($id,1,5);

            $this->assign("keywords",$keywords);
            $this->assign("fi",322);
            $this->assign("basic",$basic);
            $this->assign("images",$images);
            $this->assign("category",$categoryClass);
            $this->assign("info",$articleInfo);
            $this->assign("countKey", count($articleInfo['recommendArticles']));

            $this->display();
        }else{
            $this->_error();
        }
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
            cookie('contentPromoMark',$cookieVar, array('expire' => $expireTime,'domain' => '.'.C('QZ_YUMING')));
        }
    }


    public function dolike()
    {
        if ($_POST) {
            $id = I("post.id");
            $i = D("WwwArticle")->setLikes($id);
            if ($i !== false) {
               $likes = S('Cache:Mobile:ArticleInfo:likes:'.$id);
               $likes++;
               S('Cache:Mobile:ArticleInfo:likes:'.$id, $likes, 120);
               $this->ajaxReturn(array("status"=>1));
            }
            $this->ajaxReturn(array("status"=>0));
        }
        $this->ajaxReturn(array("status"=>0));
    }



     /**
     * 获取文章信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    private function getArticleInfo($id,$category){
        $result = D("WwwArticle")->getMobileArticleInfoById($id,$category);
        $article = array();
        foreach ($result as $key => $value) {
            if(empty($value["shortname"])){
                $value["shortname"] = "history";
                $value["classname"] ="历史资讯";
                $value["title"] = str_replace("_齐装网", "", $value["title"]);
            }

            // 如果文章来自软文城不处理img标签
            if (empty($value["from_ruanwen"])) {
                //处理文章中的图片
                $pattern ='/<img.*?\/>/is';
                preg_match_all($pattern,$value["content"], $matches);

                if(count($matches[0]) > 0){
                    foreach ($matches[0] as $k => $val) {
                        $pattern ='/src=[\"|\'](.*?)[\"|\']/is';
                        preg_match_all($pattern,$val, $m);
                        foreach ($m[1] as $j => $v) {
                            if(!strpos($v,C('QINIU_DOMAIN'))){
                                $path = $this->SCHEME_HOST['scheme_full'].C('STATIC_HOST1').$v;
                                $value["content"]  = str_replace($v,$path,$value["content"]);
                            }
                        }

                        $pattern = '/width\=[\"|\'].*?[\"|\']/is';
                        $value["content"] = preg_replace_callback($pattern, function(){
                            return "";
                        }, $value["content"]);

                        $pattern = '/height\=[\"|\'].*?[\"|\']/is';
                        $value["content"] = preg_replace_callback($pattern, function(){
                            return "";
                        }, $value["content"]);
                    }
                }
            }

            $value['brand_model'] = !empty($value['brand_model']) ? $value['brand_model'] : 'NULL';
            $value['app_version'] = !empty($value['app_version']) ? $value['app_version'] : 'NULL';
            $value['system_version'] = !empty($value['system_version']) ? $value['system_version'] : 'NULL';

            $article = $value;
        }
        return $article;
    }

    /**
     * 获取推荐的文章列表
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    private function getTopArticleInfo($id){
        $result = D("WwwArticle")->getTopArticleInfo(5,$id);
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

    public function getLegal(){
        $this->display('legal');
    }


   //复制免责声明
    public function getLegal_zst(){
        $this->display("legal_zst");
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
                    $list[$key]["url"] = $this->SCHEME_HOST['scheme_host']."/gonglue/zs/".$value["href"]."/";
                }else{
                    $list[$key]["url"] = $this->SCHEME_HOST['scheme_host']."/baike/zs/".$value["href"]."/";
                }
            }
        }
        return array("list"=>$list,"page"=>$show);
    }

}
