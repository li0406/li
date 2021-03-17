<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class ArticlespecialController extends HomeBaseController
{
    public function _initialize(){

        parent::_initialize();
        //判断是否是搜索引擎蜘蛛
        $robotIsTrue = B("Common\Behavior\RobotCheck");
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
        $this->assign("tabIndex",4);
    }


    public function index()
    {
        //获取报价模版
        $this->assign("order_source",145);
        $t = T("Common@Order/orderTmp");
        $orderTmp = $this->fetch($t);
        $this->assign("orderTmp",$orderTmp);

        //获取攻略专题列表
        $info["lunbo"] = S("Cache:zt:lunbo");
        if (!$info["lunbo"]) {
            $info["lunbo"] = $this->getSpecialModule(5);
            S("Cache:zt:lunbo",$info["lunbo"],900);
        }

        //家居生活
        $info["jjsh"] = S("Cache:zt:jjsh");
        if (!$info["jjsh"]) {
            $info["jjsh"] = $this->getTopArticleSpecial(9,5);
            S("Cache:zt:jjsh",$info["jjsh"],900);
        }

        //装修扫盲
        $info["zxsm"] = S("Cache:zt:zxsm");
        if (!$info["zxsm"]) {
            $info["zxsm"] = $this->getTopArticleSpecial(10,5);
            S("Cache:zt:zxsm",$info["zxsm"],910);
        }

        //家居导购
        $info["jjdg"] = S("Cache:zt:jjdg");
        if (!$info["jjdg"]) {
            $info["jjdg"] = $this->getTopArticleSpecial(11,5);
            S("Cache:zt:jjdg",$info["jjdg"],920);
        }

        //专题回顾
        $info["history"] = S("Cache:zt:history");
        if (!$info["history"]) {
            $info["history"] = $this->getHistorySpecialModule(5,true);
            S("Cache:zt:history",$info["history"],900);
        }

        //TDK
        $key["title"] = "装修专题_家居装修专题汇总-齐装网装修专题频道";
        $key["description"] = "齐装网装修专题频道不定期针对各种装修知识制作成装修专题，帮助业主快速全面的了解家居装修的小窍门和前沿的潮流趋势，让你从装修菜鸟变成装修达人。";
        $key["keywords"] = "装修专题，装修知识，家居装修";

        //获取底部弹层
        $t = T("Common@Order/zb_bottom_b");
        $adv_bottom = $this->fetch($t);
        $this->assign("adv_bottom",$adv_bottom);

        $this->assign("key",$key);
        $this->assign("info",$info);
        $this->display();
    }

    /**
     * 文章列表页
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function articlelist()
    {
        $type = I("get.type");
        //获取类别信息
        $info = S("Cache:zt:list:".$type);
        if (!$info) {
            $info["classInfo"] = $this->getArticleSpecialClassInfo($type);
            //获取推荐长尾词
            $info['words'] = $this->getLongTailWords();
            S("Cache:zt:list:".$type,$info,900);
        }
        $pageIndex = 1;
        $pageCount = 10;

        if (I("get.p") !== "") {
            $pageIndex = I("get.p");
        }

        //获取文章类表
        $info["list"] = $this->getArticleSpecialList($pageIndex,$pageCount,$type);
        //获取底部弹层
        $t = T("Common@Order/zb_bottom_b");
        $zb_bottom_s = $this->fetch($t);
        $this->assign("zb_bottom_s",$zb_bottom_s);

        //获取报价模版
        $this->assign("order_source",12);
        $t = T("Common@Order/orderTmp");
        $orderTmp = $this->fetch($t);
        $this->assign("orderTmp",$orderTmp);

        //TDK
        $key["title"] = $info["classInfo"]["title"];
        $key["description"] = $info["classInfo"]["description"];
        $key["keywords"] = $info["classInfo"]["keywords"];

        //获取底部弹层
        $t = T("Common@Order/zb_bottom_s");
        $adv_bottom = $this->fetch($t);
        $this->assign("adv_bottom",$adv_bottom);

        $this->assign("words",$info["words"]);
        $this->assign("key",$key);
        $this->assign("info",$info);
        $this->display();
    }

    /**
     * [terminal 专题文章终端页 terminal:终端]
     * @return [type] [description]
     */
    public function terminal()
    {
        $id = I('get.id');
        $class = M('article_special_class')->where(['shortname' => trim(I('get.category'))])->find();
        if(empty($class) || empty($id)){
            $this->_error();
        }
        $info['info'] = $this->getArticleSpecialByIdAndType($id,$class['id']);
        if(empty($info['info'])){
            $this->_error();
        }
        //查看是否被收藏
        $user = session('u_userInfo');
        if(!empty($user)){
            $info['info']['collect'] = D('Usercollect')->getCollectCount($info['info']['id'],$user['id'],6);
        }

        //获取上一个，下一个
        $info['prev'] = D('ArticleSpecial')->getNearArticleSpecial($class['id'],$id,'prev');
        $info['next'] = D('ArticleSpecial')->getNearArticleSpecial($class['id'],$id,'next');

        //获取热门文章
        $info["hotarticles"] = S("Cache:as:hot");
        if (!$info["hotarticles"]) {
           $info["hotarticles"] = D("ArticleSpecial")->getHotArticleSpecial();
           S("Cache:as:hot",$info["hotarticles"],3600);
        }

        //获取相关文章
        $info["recommend"] = S("Cache:as:recommend:".$info['info']['id']);
        if (!$info["recommend"]) {
           $info["recommend"] = $this->getRelationArticleSpecial($class['id'],9,$info['info']['id'],$info['info']['tag_id'],$info['info']['keywords']);
           S("Cache:as:recommend:".$info['info']['id'],$info["hotarticles"],86400);
        }

        //获取热门标签
        /*
        $info["tags"] = S("Cache:as:tags");
        if (!$info["tags"]) {
            $info["tags"] = D("Tags")->getHotTags(1,18);
            S("Cache:as:tags",$info["tags"],3600*3);
        }
        */

        $this->assign('module','articlespecial');
        //获取评论模板
        $count = D("CommentFull")->getCommentCount("articlespecial",$id);
        $this->assign("rel_number",$count);
        $this->assign("rel_id",$id);
        $tmp = $this->fetch(T("Common@Comment/reply"));
        $this->assign("reply",$tmp);
        //获取最新的2条评论
        $comments = D("CommentFull")->getNewCommentFullLists("articlespecial",$id,2);
        $this->assign("comments",$comments);
        $tmp = $this->fetch(T("Common@Comment/comment"));
        $this->assign("comments",trim($tmp));
        //更新文章浏览量
        D('ArticleSpecial')->editArticleSpecialPvById($id);

        /*S-底部设计浮动框*/
        $this->assign("adv_bottom",$this->fetch(T("Common@Order/zb_bottom_b")));
        //获取是否显示获取报价弹层
        if(!isset($_COOKIE["w_index"])){
            setcookie("w_index",1,time()+(3600*24),'/', '.'.APP_HTTP_DOMAIN);
            $info['open'] = true;
        }
        /*E-底部设计浮动框*/

        //获取报价模版
        $this->assign("order_source",147);
        $orderTmp = $this->fetch(T("Common@Order/orderTmp"));
        $this->assign("orderTmp",$orderTmp);

        //TDK
        $info['head']['title'] = $info['info']['title'].'-齐装网';
        $info['head']['keywords'] = $info['info']['keywords'];
        $info['head']['description'] = $info['info']['description'];

        $this->assign('info',$info);
        $this->display();
    }


    /**
     * [getRelationArticleSpecial 获取相关文章]
     * @param  string  $type     [文章类型]
     * @param  integer $limit    [获取数目]
     * @param  integer $tagid    [标签id]
     * @param  string  $keywords [关键字]
     * @return [type]            [description]
     */
    private function getRelationArticleSpecial($type = '', $limit = 6, $notid = 0, $tagid = 0, $keywords= '')
    {
        $map['a.istop'] = 1;
        //如果有标签id或者关键字，先利用分类和(标签或者关键字)搜索,不考虑分类
        if(!empty($tagid) || !empty($keywords)){
            if(!empty($tagid) && !empty($keywords)){
                $map = array(
                                [
                                    't.tag_id'=>$tagid,
                                    '_string'=>"FIND_IN_SET('$keywords',a.keywords)",
                                    '_logic'=>'OR'
                                ]
                            );
            }elseif(!empty($tagid)){
                $map = array(
                                't.tag_id'=>$tagid
                            );
            }elseif(!empty($keywords)){
                $map = array(
                                '_string'=>"FIND_IN_SET('$keywords',a.keywords)"
                            );
            }
        }

        if(empty($limit)){
            $limit = 6;
        }
        $map['a.istop'] = 1;
        if(!empty($type)){
            $map['a.type'] = $type;
        }
        if(!empty($notid)){
            $map['a.id'] = array('NEQ',$notid);
        }
        $result = D("ArticleSpecial")->getArticleSpecialListByMap($map, $limit);

        //如果更加标签或关键字获取的数量少于需要的数量，再次根据分类来获取
        $count = $limit - count($result);
        $other = [];
        if($count > 0){
            $map = [];
            if(!empty($type)){
                $map['a.type'] = $type;
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
            $map['a.istop'] = 1;
            $other = D("ArticleSpecial")->getArticleSpecialListByMap($map, $count);
        }
        $return = array_merge($result,$other);
        return $return;
    }

    /**
     * [comment 评论页]
     * @return [type] [description]
     */
    public function comment()
    {
        $id = I('get.id');
        $class = M('article_special_class')->where(['shortname' => trim(I('get.category'))])->find();
        if(empty($class) || empty($id)){
            $this->_error();
        }
        $info['info'] = D('ArticleSpecial')->getArticleSpecialByIdAndType($id,$class['id']);
        if(empty($info['info'])){
            $this->_error();
        }
        //获取评论模板
        $this->assign("module","articlespecial");
        $count = D("CommentFull")->getCommentCount("articlespecial",$id);
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
        $comments = D("CommentFull")->getCommentFullLists($pageIndex,$pageCount,"articlespecial",$id);
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
            $result = D('ArticleSpecial')->editArticleSpecialLikesById($id);
            if($result){
                $this->ajaxReturn(array('data'=>'','info'=>'操作成功','status'=>1));
            }
        }
        $this->ajaxReturn(array('data'=>'','info'=>'操作失败','status'=>0));
    }

    /**
     * 专题终端页
     * @return [type] [description]
     */
    public function module()
    {
        $id = I("get.id");
        //获取模块信息
        $info = S("Cache:zt:module:".$id);
        if (!$info) {
            //获取模块信息
            $info["module"] = $this->getModuleInfo($id);
            //获取美图模块信息
            $info["meitu"] = $this->getModuleMeituList($id);
            //文章模块1
            $info["article1"] = $this->getModuleArticleList($id,"article_1");
            //文章模块2
            $info["article2"] = $this->getModuleArticleList($id,"article_2");
            //文章模块3
            $info["article3"] = $this->getModuleArticleList($id,"article_3");
            //问答模块
            $info["ask"] = $this->getModuleAskList($id);
            //视频模块
            $info["video"] = $this->getModuleVideoList($id);
            //更多精彩专辑
            $info["more"] = $this->getHistorySpecialModule(5);
            S("Cache:zt:module:".$id,$info,900);
        }

        if (count($info["module"]) ==  0) {
           $this->_error();
           die();
        }

        //TDK
        $key["title"] =  $info["module"]['title']."- 齐装网装修专题";
        $key["description"] = $info["module"]['title']+"专题为业主提供".$info["module"]['title']."的详细装修攻略，帮助业主解决".$info["module"]['title']."相关问题，让业主轻松装修不再上当";
        $key["keywords"] = $info["module"]['title'];
        $this->assign("key",$key);

        //获取底部弹层
        $t = T("Common@Order/zb_bottom_b");
        $adv_bottom = $this->fetch($t);
        $this->assign("adv_bottom",$adv_bottom);
        $this->assign("info",$info);
        $this->display();
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
     *  获取最新推荐到轮播的专题
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    private function getSpecialModule($limit)
    {
        $result = D("ArticleSpecialModule")->getNewSpecialModule($limit);
        return $result;
    }

    /**
     * 获取专题文章
     * @param  [type] $type  [专题类型]
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    private function getTopArticleSpecial($type,$limit)
    {
        $result = D("ArticleSpecialModule")->getTopArticleSpecial($type,$limit);
        return $result;
    }

    /**
     * 获取专题文章类信息
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    private function getArticleSpecialClassInfo($type)
    {
        $result = D("ArticleSpecial")->getArticleSpecialClassInfo($type);
        return $result;
    }

    private function getArticleSpecialList($pageIndex,$pageCount,$type)
    {
        $count = D("ArticleSpecialModule")->getSpecialModuleListCount($type);
        if ($count > 0) {
            import('Library.Org.Page.LitePage');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \LitePage($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();
            $list = D("ArticleSpecialModule")->getSpecialModuleList($type,($page->pageIndex-1)*$pageCount,$pageCount);

            return array("list"=>$list,"page"=>$pageTmp);
        }
    }

    /**
     * 获取模块信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    private function getModuleInfo($id)
    {
        $result = D("ArticleSpecialModule")->getModuleInfo($id);
        return $result;
    }

    /**
     * 获取历史的专题列表
     * @param  string $value [description]
     * @return [type]        [description]
     */
    private function getHistorySpecialModule($limit,$flag = false)
    {
        $result =  D("ArticleSpecialModule")->getHistorySpecialModule($limit,$flag);
        shuffle($result);
        $result = array_slice($result, 0,6);
        return $result;
    }

    private function getArticleSpecialByIdAndType($id,$class)
    {
        $article = D('ArticleSpecial')->getArticleSpecialByIdAndType($id,$class);
        //查询文章关键字，替换成内链
        $keywords = D("Wwwarticlekeywords")->getKeywordsRelate($id,"special");
        foreach ($keywords as $key => $value) {
            $list[] = "/".trim($value["name"])."/";
        }

        //抽出文章中的图片
        $pattern ='/<img.*?\/>/i';
        preg_match_all($pattern, $article["content"], $matches);
        if(count($matches[0]) > 0){
            foreach ($matches[0] as $key => $value) {
                //将图片替换成变量占位符
                $article["content"] = str_replace($value, "%s", $article["content"]);
                $replaceImg[] = $value;
            }
        }

        foreach ($list as $key => $value) {
            preg_match_all($value,$article["content"],$matches);
            if(count($matches[0]) > 0){
                $link =  "<a href='".$keywords[$key]["href"]."' target='_blank' style='color:#dc4146'>".$keywords[$key]["name"]."</a>";
                $article["content"] = preg_replace($value,$link,$article["content"],1);
            }
        }
        //将所有的图片依次填充到原来位置
        foreach ($replaceImg as $key => $value) {
            $article["content"] = preg_replace("/\%s/",$value,$article["content"],1);
        }

        return $article;
    }

    /**
     * 获取最新的长尾词
     * @return [type] [description]
     */
    private function getLongTailWords()
    {
       return D("LongTailKeywords")->getNewWords(10);
    }

    /**
     * 获取美图信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    private function getModuleMeituList($id)
    {
        $result = D("ArticleSpecialModule")->getModuleMeituList($id);
        foreach ($result as $key => $value) {
            if (!array_key_exists($value["type"],$list)) {
                $list["title"] = $value["title"];
            }
            $list["child"][] = $value;
        }
        return $list;
    }

     /**
     * 获取美图信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    private function getModuleArticleList($id,$type)
    {
        $result = D("ArticleSpecialModule")->getModuleArticleList($id,$type);
        foreach ($result as $key => $value) {
            if (!array_key_exists($value["type"],$list)) {
                $list["title"] = $value["title"];
            }
            $value["id"] = $value["article_id"];
            $list["child"][] = $value;
        }
        return $list;
    }

    /**
     * 获取问答信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getModuleAskList($id)
    {
        import('Library.Org.Util.Fiftercontact');
        $filter = new \Fiftercontact();
        $result = D("ArticleSpecialModule")->getModuleAskList($id);
        foreach ($result as $key => $value) {
            if (!array_key_exists($value["type"],$list)) {
                $list["title"] = $value["title"];
            }
            $value["content"] = strip_tags($filter->filter_empty($value["content"]));
            $list["child"][] = $value;
        }
        return $list;
    }

    /**
     * 获取视频模块信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getModuleVideoList($id)
    {
        $result = D("ArticleSpecialModule")->getModuleVideoList($id);
        foreach ($result as $key => $value) {
            if (!array_key_exists($value["type"],$list)) {
                $list["title"] = $value["title"];
            }
            $list["child"][] = $value;
        }
        return $list;
    }

}