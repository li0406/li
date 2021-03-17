<?php
namespace Sub\Controller;
use Sub\Common\Controller\SubBaseController;
class ArticleinfoController extends SubBaseController{
    public function index(){
        $cityInfo = $this->cityInfo;
        $articleInfo = S("Cache:ArticleInfo:Sub:".$cityInfo["bm"]. ":" .I("get.id"));
        if(!$articleInfo){
            $id = I("get.id");
            if(empty($id)){
                $this->_error();
                die();
            }
            //获取设计师的文章信息
            $article = $this->getArticlesInfo($id,$cityInfo["id"]);
            $articleInfo["article"] =$article;
            //获取推荐文章
            $topArticle = $this->getTopArticleInfo();
            $articleInfo["topArticle"] = $topArticle;

            S("Cache:ArticleInfo:Sub:".$cityInfo["bm"]. ":" .I("get.id"),$articleInfo,60*15);
        }



        if(empty($articleInfo["article"]["now"])){
            $this->_error();
            die();
        }

        //seo 标题/描述/关键字
        $keys["title"] = $articleInfo["article"]["now"]["title"].$cityInfo["name"];
        $keys["keywords"] = $articleInfo["article"]["now"]["title"];
        $keys["description"] =$articleInfo["article"]["now"]["title"];
        $this->assign("keys",$keys);

        $this->assign("articleInfo",$articleInfo);
        $this->display("Blog/article");
    }

    private function getArticlesInfo($id,$cs){
        $result = D("Article")->getArticleInfo($id,$cs);

        foreach ($result as $key => $value) {
            if(!array_key_exists($value["action"], $article)){
                $article[$value["action"]] = array();
            }
            $article[$value["action"]] = $value;
        }

        foreach ($article as $key => $value) {
            //过滤文章的超链接标签
            $pattern ='/\<a\\s*href\s*=([\"|\'])?(.*?)([\"|\'])?\>/is';
            preg_match_all($pattern, $value["text"], $matches);
            if(!empty($matches[2])){
                foreach ($matches[2] as $k => $v) {
                    $value["text"] = str_ireplace($v,"javascript:void(0)",$value["text"]);
                }
            }
            $article[$key]["text"] = $value["text"];
        }
        return $article;
    }

     /**
     * 获取推荐的文章列表
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    private function getTopArticleInfo(){
        $result = D("WwwArticle")->getTopArticleInfo(3);
        return $result;
    }
}