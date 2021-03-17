<?php
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class BiaoqianController extends HomeBaseController
{
    public function _initialize(){
        // 415 删除网站攻略及百科低质量页面，biaoqian页面和tags页面，建材站域名解析
        $this->_empty();
        parent::_initialize();
        $uri = $_SERVER['REQUEST_URI'];
        preg_match('/html$/',$uri,$m);
        if (count($m) == 0) {
            preg_match('/\/$/',$uri,$m);
            $parse = parse_url($uri);
            if (count($m) == 0 && empty($parse["query"])) {
                header( "HTTP/1.1 301 Moved Permanently");
                header("Location:  ".$this->SCHEME_HOST['scheme_full'] . C("QZ_YUMINGWWW").$uri."/");
            }
        }
    }

    public function index()
    {
        $result = $this->getKeyList();
        $this->assign("list",$result["list"]);
        $this->assign("page",$result["page"]);
        $this->display();
    }

    /**
     * 标签页
     * @return [type] [description]
     */
    public function taglist()
    {
        $id = I("get.id");
        $info = S('Cache:Biaoqian:'.$id);
        if (empty($info)) {
            //获取长尾词信息
            $info["word"] = D("LongTailKeywords")->findWordInfo($id);
            //替换TDK
            $info["word"]["title"] = str_replace('{关键词}', $info["word"]["words"], $info["word"]["title"]);
            $info["word"]["keywords"] = str_replace('{关键词}', $info["word"]["words"], $info["word"]["keywords"]);
            $info["word"]["description"] = str_replace('{关键词}', $info["word"]["words"], $info["word"]["description"]);

            $fen_word = array_filter(explode(",", $info["word"]["fen_word"]));
            if (count($fen_word) > 0) {
                //获取分词相关的美图套图
                $info["meitu"] = $this->getMeituList($fen_word);
                //获取分词相关的文章列表
                $info["article"] = $this->getArticleList($fen_word);
                //获取分词相关的问答列表
                $info["ask"] = $this->getWenDaList($fen_word);
                //相关长尾词
                $info["words"] = $this->getWordList($id,$fen_word);
            }
            S('Cache:Biaoqian:'.$id,$info, 60*15);
        }

        if (empty($info["word"]["title"])) {
            $info["word"]["title"] =  $info["word"]["words"]."-齐装网";
        }

        if (empty($info["word"]["description"])) {
            $info["word"]["description"] = str_replace('{关键词}', $info["word"]["words"], '齐装网{关键词}频道为您提供全新的关于{关键词}的图文资讯，以及{关键词}相关问题并一一解决，为您提供全方位参考');
        }

        $this->assign("info",$info);
        //获取报价模版
        $this->assign("order_source",12);
        $t = T("Common@Order/orderTmp");
        $orderTmp = $this->fetch($t);
        $this->assign("orderTmp",$orderTmp);

        $this->display();
    }

    /**
     * 获取关键字列表
     * @return [type] [description]
     */
    private function getKeyList()
    {
        $pageIndex = 1;
        if (I("get.p") !== "") {
            $pageIndex = I("get.p");
        }

        $pageCount = 90;

        $count = D("LongTailKeywords")->getLongTailKeyListCount();
        if ($count > 0) {
            import('Library.Org.Page.LitePage');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \LitePage($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();
            $result = D("LongTailKeywords")->getLongTailKeyList(($page->pageIndex-1)*$pageCount,$pageCount);
            $list = array_chunk($result, 30);
            foreach ($list as $key => $value) {
                $list[$key] =array_chunk($value, 10);
            }
        }
        return array("list"=>$list,"page"=>$pageTmp);
    }

    /**
     * 获取美图分词列表
     * @param  [type] $fen_word [description]
     * @return [type]           [description]
     */
    private function getMeituList($fen_word)
    {
        $list = D("Meitu")->getBiaoQianList($fen_word,1000);
        shuffle($list);
        $list = array_slice($list,0,7);
        //关联数量不足时
        if (count($list) < 7) {
            $offset  = 7 - count($list);
            //随机获取10张点击率最高的美图
            $shuffle = D("Meitu")->getNewMeitu(10);
            shuffle($shuffle);
            $shuffle = array_slice($shuffle,0,$offset);
            foreach ($shuffle as $key => $value) {
                $list[] = array(
                    "id" => $value["id"],
                    "title" => $value["title"],
                    "img_path" => $value["img_path"]
                );
            }
        }
        return $list;
    }

    /**
     * 获取文章列表
     * @param  [type] $fen_word [description]
     * @return [type]           [description]
     */
    private function getArticleList($fen_word)
    {
        $list = D("WwwArticle")->getBiaoQianList($fen_word,1000);
        shuffle($list);
        $list = array_slice($list,0,5);

        //关联数量不足时
        if (count($list) < 5) {
            $offset  = 5 - count($list);
            //随机获取10张点击率最高的美图
            $shuffle = D("WwwArticle")->getTopArticleInfo(10);
            shuffle($shuffle);
            $shuffle = array_slice($shuffle,0,$offset);
            foreach ($shuffle as $key => $value) {
                $list[] = array(
                    "id" => $value["id"],
                    "title" => $value["title"],
                    "img_path" => $value["img_path"],
                    "face" => $value["face"],
                    "shortname" => $value["shortname"]
                );
            }
        }
        return $list;
    }

    /**
     * 获取问答相关列表
     * @param  [type] $fen_word [description]
     * @return [type]           [description]
     */
    private function getWenDaList($fen_word)
    {
        import('Library.Org.Util.Fiftercontact');
        $filter = new \Fiftercontact();
        $list = D("Ask")->getBiaoQianList($fen_word,1000);
        shuffle($list);
        $list = array_slice($list,0,6);

        foreach ($list as $key => $value) {
            $list[$key]["content"] = strip_tags($filter->filter_empty($value["content"]));
        }

        //关联数量不足时
        if (count($list) < 6) {
            $offset  = 6 - count($list);
            //随机获取10条热门回答
            $shuffle = D("Ask")->getHotAsk(10);
            shuffle($shuffle);
            $shuffle = array_slice($shuffle,0,$offset);
            foreach ($shuffle as $key => $value) {
                $list[] = array(
                    "id" => $value["id"],
                    "title" => $value["title"],
                    "content" => strip_tags($filter->filter_empty($value["content"]))
                );
            }
        }
        return $list;
    }

    /**
     * 获取相关长尾词
     * @param  string $value [description]
     * @return [type]        [description]
     */
    private function getWordList($id,$fen_word)
    {
        $list = D("LongTailKeywords")->getRelationWords($id,$fen_word,1000);
        shuffle($list);
        $list = array_slice($list,0,22);
        return $list;
    }
}