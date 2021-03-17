<?php
/**
 * 移动版 - 分站资讯
 */
namespace Mobile\Controller;
use Common\Model\Logic\SubthematicLogicModel;
use Mobile\Common\Controller\MobileBaseController;

class ZxinfoController extends MobileBaseController{
    public function _initialize(){
        parent::_initialize();
        $uri = $_SERVER['REQUEST_URI'];
        preg_match('/html$/',$uri,$m);
        if (count($m) == 0) {
            preg_match('/\/$/',$uri,$m);
            $parse = parse_url($uri);
            if (count($m) == 0 && empty($parse["query"])) {
                $SCHEME_HOST = $this->SCHEME_HOST;
                header( "HTTP/1.1 301 Moved Permanently");
                header("Location: ". $SCHEME_HOST['scheme_full'].$SCHEME_HOST['host'].$uri."/");
            }
        }
    }

    public function index(){
        $SCHEME_HOST = $this->SCHEME_HOST;
        $category = I('get.category');
        $pageIndex = I('get.page');

        $pageCount =10;

        if(empty($category)){
            $category = '';
        }

        if(empty($pageIndex)){
            $pageIndex = 1;
        }
        $this->assign('getcategory',$category ? $category : '');


        //添加canonical标签
        $url_arr = explode("/", $_SERVER['REQUEST_URI']);
        if(empty($category)){
			$info['header']['canonical'] = $SCHEME_HOST['scheme_full'] . $url_arr[1] . '.' . $SCHEME_HOST['domain'] . '/' . $url_arr[2].'/' ;
		}else {
			$info['header']['canonical'] = $SCHEME_HOST['scheme_full'] . $url_arr[1] . '.' . $SCHEME_HOST['domain'] . '/' . $url_arr[2] . '/' . $category.'/';
		}

        if($category !=''){
            $categoryInfo = D("Infotype")->getTypeInfoByshortname($category);
            if(count($category) == 0 || !$categoryInfo["enabled"]){
                $this->_error();
            }
        }
        $keys = $this->getSEOMeta($category,$categoryInfo);

        $articles = $this->getZxArticleList($categoryInfo["id"],$this->cityInfo['id'],$pageIndex,$pageCount,$keyword);
        $info['list'] = $articles['articles'];
        $info['pageCount'] = ceil($articles['count'] / $pageCount);
        $info['page'] = $pageIndex;

        // dump($info['list']);exit;

        if($pageIndex > '1'){
            $info['prevUrl'] = '/'.$this->cityInfo['bm'].'/zxinfo/list-'.$category.'-'.($pageIndex - 1) .'.html';
        }
        if($pageIndex < $info['pageCount']){
            $info['nextUrl'] = '/'.$this->cityInfo['bm'].'/zxinfo/list-'.$category.'-'.($pageIndex + 1) .'.html';
        }

        //默认分类的上下页
        if($category == ''){
            $info['prevUrl'] = '/'.$this->cityInfo['bm'].'/zxinfo/list-'.($pageIndex - 1) .'.html';
            $info['nextUrl'] = '/'.$this->cityInfo['bm'].'/zxinfo/list-'.($pageIndex + 1) .'.html';
        }
        //装修资讯页、本地资讯、装修经验、建材导购、装修公司资讯页的顶部添加发单入口,添加1个共同的发单位置标识
        $this->assign("source", "18031401");
 		//获取后台配置的TDK
        $config = [
            'cs' => $this->cityInfo['id'], //城市id
            'model' => 3, //模块 1.首页 2.装修公司 3.装修资讯
            'category' => I("get.category"), //装修资讯子频道栏目
            'location' => 2, //位置 1.pc端 2.移动端
            'page' => I('get.page'), //分页
        ];
        $keys = getCommonManageTdk($keys, $config);
        //增加选中状态
        if (in_array($category,['bendi','jingyan','daogou','gongsi','wenwen','xuetang','baojia','zxsj'])){
            $keys[$category] = ' class="ask-active"';
        }else{
            $keys['new'] = ' class="ask-active"';
        }
        if(empty($category)){
            $cityInfo['name'] = $this->cityInfo['name'];
            $keys['title'] = $cityInfo['name'] . '装修攻略_' . $cityInfo['name'] . '装修经验分享_' . $cityInfo['name'] . '建材导购指南_' . $cityInfo['name'] . '装修公司黑幕';
            $keys['description'] = $cityInfo['name'] . '齐装网官方装修攻略基于广大业主装修真实经历和心得的装修攻略、装修经验分享、建材导购指南、装修公司黑幕等' . date('Y') . '年装修攻略应有尽有。';
            $keys['keywords'] = $cityInfo['name'] . '装修攻略,' . $cityInfo['name'] . '装修经验分享,' . $cityInfo['name'] . '建材导购指南,' . $cityInfo['name'] . '装修公司黑幕';
        }

        $this->assign("info",$info);
        $this->assign("keys",$keys);

        $this->assign("category",$categoryInfo["name"]);
        $this->assign('showlocation',1);//显示页面name为‘location’的meta标签
        $this->display("index_2812");
    }

	/**
	 * 移动版 - 分站资讯详情
	 */
    public function details(){
        $SCHEME_HOST = $this->SCHEME_HOST;
        //dump(I('get.'));
        $qid = I("get.id");
        $cityInfo = $this->cityInfo;
        $id = I('get.id');

        empty($id) && $this->_error();

        $article = $this->getArticleInfo($id,$cityInfo["id"]);

        $this->assign('showlocation',1);//显示页面name为‘location’的meta标签
        if(count($article) > 0){
            empty($article["now"]) && $this->_error();
            $articleInfo["article"] = $article;

            //精彩推荐
            $classid = $article['now']['classid'];
            $topArticle = $this->getTopArticleInfo($id,$classid,$cityInfo["id"]);
            $articleInfo["topArticle"] = $topArticle;

            //获取看过本文的还看过的文章
            // $time = strtotime("-2years");  //time为当前时间的时间戳减去两年时间的时间戳
            // $recommendArticles = $this->getRecommendArticlesByTime($articleInfo["article"]["now"]['classid'], 5, $cityInfo["id"], $time);
            // $articleInfo["recommendArticles"] = $recommendArticles;
        }

        //判断该文章是否是该城市的文章
        if ($articleInfo['article']['now']['cs'] != $this->cityInfo['id']) {
            $this->_error();
        }

        //end
        //计算真实浏览量
        //导入扩展文件
        //判断是否是搜索引擎蜘蛛
        $robotIsTrue = B("Common\Behavior\RobotCheck");
        if($robotIsTrue === false){
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
                $visitid = I("get.id");
                $today = date("Y-m-d",time());
                $timeend = strtotime(($today.' 23:59:59'));
                $timelong = $timeend - time();
                //S('Cache:gonglue:'.$_SERVER['REMOTE_ADDR'].$id,'1',900);
                $status = S('Cache:SubArticle:'.$ip.$visitid);
                if($status != 1){
                    //没有访问记录，浏览量+1，生成新缓存（根据ip.文章ID）
                    D("Littlearticle")->updateRealView($visitid);
                    S('Cache:SubArticle:'.$ip.$visitid,1,$timelong);
                }
            }
        }

        //百度官方号需求
        $baidu['url'] = $this->SCHEME_HOST['scheme_full'].$this->SCHEME_HOST['host'].$_SERVER['REQUEST_URI'];
        $articleInfo['article']['now']['content'] = str_replace(array("&nbsp;","&amp;nbsp;","\t","\r\n","\r","\n"),array("","","","","",""), $articleInfo['article']['now']['content']);
        $baidu['str'] = mb_substr(strip_tags($articleInfo['article']['now']['content']), 0, 100, 'utf-8');
        $optimee =date("Y-m-d",$articleInfo['article']["now"]['addtime']);
        $optimes =date("H:i:s",$articleInfo['article']["now"]['addtime']);
        $baidu['optime'] = $optimee."T".$optimes;
//        $baidu['optime'] = date("Y-m-d H:i:s", $articleInfo['article']['now']['addtime']);
        $images =explode(",",$articleInfo['article']["now"]['img']);
        $baidu_title = $articleInfo["article"]["now"]["title"];
        $this->assign("baidu_title", $baidu_title);
        $this->assign("images", $images);
        $this->assign("baidu",$baidu);

        $cityInfo = $this->cityInfo;

        //获取该分类的分类信息
        $keys["title"] = $articleInfo["article"]["now"]["title"];
        $keys["keywords"] =$articleInfo["article"]["now"]["keywords"];
        $keys["description"] = $articleInfo["article"]["now"]["description"];

        //添加canonical标签
        $info['header']['canonical'] = $SCHEME_HOST['scheme_full'] . $cityInfo['bm'] .'.'. $SCHEME_HOST['domain'] . '/zxinfo/'. $qid . '.html';
        //更新阅读量
        D("Littlearticle")->updatePv(I("get.id"));

        //获取文章关联分站专题
        $logic = new SubthematicLogicModel();
//        $zhuanti = $logic->getArticleListByTag($articleInfo['article']['now']['tags'],$cityInfo["id"]);
        $zhuanti = $logic->getArticleListBySubTag(I("get.id"),$cityInfo["id"]);
        $this->assign('zhuanti', $zhuanti);

        //流量部推广统计
        $this->assign('title', $cityInfo['name'] . "齐装网");
        $this->assign("info", $info);
        $this->assign("keys",$keys);
        $this->assign("articleInfo",$articleInfo);
        // $this->assign("countKey", count($articleInfo['recommendArticles']));
        $this->display();
    }

    //流量部推广统计
    public function promoStats($id){
        //获取Cookie
        $isMark = cookie('contentPromoMark');

        //如果Cookie不存在
        if(empty($isMark['module'])){
            //过期时间 = 今天最后一秒时间戳 - 当前时间戳
            $expireTime = strtotime(date('Y-m-d').' 23:59:59') - time();
            $cookieVar = array('module' => 'subarticle','id' => $id);
            //指定cookie保存时间
            cookie('contentPromoMark',$cookieVar, array('expire' => $expireTime,'domain' => '.'.C('QZ_YUMING')));
        }
    }

     /**
     * 获取推荐的文章列表
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    private function getTopArticleInfo($id,$category,$cs){
        $cache_key = "$id:$category:$cs";
        $result = S('Cache:M:Zxinfo:topArticleInfo:'. $cache_key);
        if (!$result) {
            $result = D("Littlearticle")->getTopArticleInfo(8,$id,$category,$cs);
            foreach ($result as $key => $value) {
                $title = strstr($value["title"],"_",true);
                if($title !== false){
                    $result[$key]["title"] = $title;
                }
            }

            S('Cache:M:Zxinfo:topArticleInfo:'. $cache_key, $result, 900);
        }

        return $result;
    }

    /**
     * 获取推荐的文章
     * @param  [type] $classid [description]
     * @param  [type] $limit   [description]
     * @return [type]          [description]
     */
    private function getRecommendArticles($classid,$limit,$cs){
        //获取相同分类的点击量最高的文章
        $recommendArticles = D("Littlearticle")->getRecommendArticles("",100,$cs);

        if(count($recommendArticles) > 0){
            shuffle($recommendArticles);
            $recommendArticles = array_slice($recommendArticles,0,$limit);
        }
        foreach ($recommendArticles as $key => $value) {
            $title = strstr($value["title"],"_",true);
            if($title !== false){
                $recommendArticles[$key]["title"] = $title;
            }
        }
        return $recommendArticles;
    }

    /**
     * 获取推荐的文章
     * @param  [type] $classid [description]
     * @param  [type] $limit   [description]
     * @return [type]          [description]
     */
    private function getRecommendArticlesByTime($classid, $limit, $cs, $time){
        //获取相同分类的点击量最高的文章
        $cache_key = "$classid:$limit:$cs";
        $recommendArticles = S('Cache:M:Zxinfo:recommendArticles:' . $cache_key);

        if (!$recommendArticles) {
            $recommendArticles = D("Littlearticle")->getRecommendArticlesByTime($classid, $limit, $cs, $time);
            foreach ($recommendArticles as $key => $value) {
                $title = strstr($value["title"],"_",true);
                if($title !== false){
                    $recommendArticles[$key]["title"] = $title;
                }
            }

            S('Cache:M:Zxinfo:recommendArticles:' . $cache_key, $recommendArticles, 900);
        }

        return $recommendArticles;
    }

    /**
     * 获取文章信息
     * @param  [type] $id       [文章编号]
     * @param  [type] $category [文章类别]
     * @return [type]           [description]
     */
    private function getArticleInfo($id,$cs){
        $cache_key = "$id:$cs";
        $articleInfo = S('Cache:M:Zxinfo:articleInfo:' . $cache_key);
        if (!$articleInfo) {
            $result =  D("Littlearticle")->getLittleArticleInfo($id,$cs);
            foreach ($result as $key => $value) {
                if(!array_key_exists($value["action"], $articleInfo)){
                    $title = strstr($value["title"],"_",true);
                    if($title !== false){
                        $value["title"] = $title;
                    }
                    $articleInfo[$value["action"]] = $value;
                }
            }

            //抽出文章中的图片
            $pattern = '/<img.*?\/>/i';
            preg_match_all($pattern, $articleInfo['now']['content'], $matches);
            if (count($matches[0]) > 0) {
                foreach ($matches[0] as $key => $value) {

                    $patterns = '/src=[\"|\'](.*?)[\"|\']/is';
                    preg_match_all($patterns, $value, $m);
                    foreach ($m[1] as $j => $v) {
                        $path = $v;
                        // 加去水印
                        if (strpos($v, '-s5.')) {
                            $path = str_replace('-s5.', '-s3.', $v);
                            $articleInfo['now']["content"] = str_replace($v, $path, $articleInfo['now']["content"]);
                        }else{
                            $path .= '-s3.jpg';
                            $articleInfo['now']["content"] = str_replace($v, $path, $articleInfo['now']["content"]);
                        }
                    }
                }

            }

            S('Cache:M:Zxinfo:articleInfo:' . $cache_key, $articleInfo, 120);
        }
        return $articleInfo;
    }

    /**
     * Gets the seo meta.
     *
     * @param      string  $category  The category
     *
     * @return     array  The seo meta.
     */
    public function getSEOMeta($category,$categoryInfo){
        if (in_array($category,['bendi','jingyan','daogou','gongsi','wenwen','xuetang','baojia','zxsj'])){
            $keys["title"] =  str_ireplace('%s',$this->cityInfo["name"],$categoryInfo["title"]);
            $keys["description"] = str_ireplace('%s',$this->cityInfo["name"],$categoryInfo["description"]);
            $keys["keywords"] = str_ireplace('%s',$this->cityInfo["name"],$categoryInfo["keywords"]);
        }else{
            $keys["title"] = $this->cityInfo["name"] . '装修攻略_' . $this->cityInfo["name"] . '装修经验分享_' . $this->cityInfo["name"] . '建材导购指南_' . $this->cityInfo["name"] . '装修公司黑幕';
            $keys["description"] = $this->cityInfo["name"] . '齐装网官方装修攻略基于广大业主装修真实经历和心得的装修攻略、装修经验分享、建材导购指南、装修公司黑幕等全新装修攻略应有尽有。';
            $keys["keywords"] = $this->cityInfo["name"] . '装修攻略,' . $this->cityInfo["name"] . '装修经验分享,' . $this->cityInfo["name"] . '建材导购指南,' . $this->cityInfo["name"] . '装修公司黑幕';
        }
        return $keys;
    }

    /**
     * 获取资讯列表页文章列表
     * @param  string  $id        [分类编号]
     * @param  integer $pageIndex [description]
     * @param  integer $pageCount [description]
     * @param  boolean $isTop     [是否置顶]
     * @return [type]             [description]
     */
    private function getZxArticleList($category='',$cs,$pageIndex=1,$pageCount=5,$keyword ="",$isTop=true){
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("Littlearticle")->getArticleListCount($category,$cs);

        if($count > 0){
            $result = D("Littlearticle")->getArticleList($category,$cs,($pageIndex-1)*$pageCount,$pageCount);
            foreach ($result as $key => $value) {
                $title = strstr($value["title"],"_",true);
                if($title !== false){
                    $result[$key]["title"] = $title;
                }
                $result[$key]["img_host"] = "qiniu";

                // 图片http重复处理
                $prefix = "http://" . C("QINIU_DOMAIN") . "/";
                $counts = substr_count($value["face"], C("QINIU_DOMAIN"));
                if ($counts > 1 && strpos($value["face"], $prefix) === 0){
                    $result[$key]["face"] = str_replace($prefix, "", $value["face"]);
                }

                if(!empty($value["img"])){
                    $exp = explode(",", $value["img"]);
                    $exp = array_filter($exp);
                    foreach ($exp as $k => $val) {
                        if(!strpos($val,C('QINIU_DOMAIN'))){
                            $path = $this->SCHEME_HOST['scheme_full'].C('STATIC_HOST1')."/".$val;
                            $exp[$k]  = $path;
                        }
                    }
                    $result[$key]["img"] = $exp[0];
                }
            }
            return array("articles"=>$result,"count"=>$count);
        }
    }

}
